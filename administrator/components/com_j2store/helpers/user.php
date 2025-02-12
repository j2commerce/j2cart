<?php
/**
 * @package     Joomla.Component
 * @subpackage  J2Store
 *
 * @copyright Copyright (C) 2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactory;
use Joomla\CMS\User\UserHelper;

class J2User
{
	public static $instance;

	public static function getInstance($properties=null)
  {
		if (!self::$instance)
		{
			self::$instance = new self($properties);
		}

		return self::$instance;
	}

	function addCustomer($post)
  {
		$app = Factory::getApplication();
		$db = Factory::getContainer()->get('DatabaseDriver');
		$user = Factory::getApplication()->getIdentity();

		//first save data to the address table
		$row = J2Store::fof()->loadTable('Address', 'J2StoreTable');

		//set the id so that it updates the record rather than changing
		if (!$row->bind($post)) {
			$row->setError($row->getError());
			return false;
		}

		if($user->id) {
			$row->user_id = $user->id;
		}

		$row->type = 'billing';

		if (!$row->store()) {
			$row->setError($row->getError());
			return false;
		}

		return $row->id;

	}

	/**
	 *
	 * @param $string
	 * @return unknown_type
	 */
	function usernameExists( $string )
	{
		// TODO Make this use ->load()

		$success = false;
		$database = Factory::getContainer()->get('DatabaseDriver');
		$query = "SELECT * FROM #__users WHERE username = ".$database->quoteuote($string)." LIMIT 1";
		$database->setQuery($query);
		$result = $database->loadObject();
		if ($result) {
			$success = true;
		}
		return $success;
	}

	/**
	 *
	 * @param $string
	 * @return unknown_type
	 */
	function emailExists( $string, $table='users'  ) {
		switch($table)
		{
			case  'users':
			default     :
				$table = '#__users';
		}

		$success = false;
		$database = Factory::getContainer()->get('DatabaseDriver');

		$query = "SELECT * FROM $table WHERE email = ".$database->quote($string)." LIMIT 1";
		$database->setQuery($query);
		$result = $database->loadObject();
		if ($result) {
			$success = true;
		}
		return $result;
	}

	/**
	 * Returns yes/no
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	function createNewUser( $details, &$msg )
	{
    $userFactory = Factory::getContainer()->get(UserFactory::class);
    $user = $userFactory->loadUserById(0);
		$config = ComponentHelper::getParams('com_users');
		// Default to Registered.
		$defaultUserGroup = $config->get('new_usertype', 2);
    $md5_pass = UserHelper::hashPassword($details['password']);

        $user->set('id'         , 0);
        $user->set('name'           , $details['name']);
        $user->set('username'       , $details['email']);
        $user->set('password' 		, $md5_pass );
        $user->set('email'          , $details['email']);  // Result should contain an email (check)
        $user->set('usertype'       , 'deprecated');
        $user->set('groups'     , array($defaultUserGroup));
        $useractivation = 0;
		//If autoregister is set let's register the user
		$autoregister = isset($details['autoregister']) ? $details['autoregister'] :  $config->get('autoregister', 1);
		J2Store::plugin ()->event ( 'BeforeRegisterUserSave', array(&$user,&$details) );
		if ($autoregister) {
			if (!$user->save()) {
                J2Store::platform()->raiseError(403,$user->getError());
			}
		}
		else {
			// No existing user and autoregister off, this is a temporary user.
            $user->set('tmp_user', true);
		}

		// Send registration confirmation mail
		$this->_sendMail( $user, $details, $useractivation );

		return $user;
	}
    /**
     * Save joomla privacy consent
     * */
	function savePrivacyConsent()
  {
	    $app = Factory::getApplication();
        $privacy_plugin = $app->input->post->get('privacyconsent',0);
        $user = Factory::getApplication()->getIdentity();
        if($privacy_plugin && $user->id){
            $db = Factory::getContainer()->get('DatabaseDriver');
            // Get the user's IP address
            $ip = $_SERVER['REMOTE_ADDR'];

            // Get the user agent string
            $userAgent = $_SERVER['HTTP_USER_AGENT'];

            // Create the user note
            $userNote = (object) array(
                'user_id' => $user->id,
                'subject' => 'PLG_SYSTEM_PRIVACYCONSENT_SUBJECT',
                'body'    => Text::sprintf('PLG_SYSTEM_PRIVACYCONSENT_BODY', $ip, $userAgent),
                'created' => Factory::getDate()->toSql(),
            );

            try
            {
                $db->insertObject('#__privacy_consents', $userNote);
            }
            catch (Exception $e)
            {
                // Do nothing if the save fails
            }


            $message = array(
                'action'      => 'consent',
                'id'          => $user->id,
                'title'       => $user->name,
                'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
                'userid'      => $user->id,
                'username'    => $user->username,
                'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            );
            $model = Factory::getApplication()->bootComponent('com_actionlogs')->getMVCFactory()->createModel('Actionlog', 'Administrator');
            //JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');
            //* @var ActionlogsModelActionlog $model */
            // $model = JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
            $model->addLog(array($message), 'PLG_SYSTEM_PRIVACYCONSENT_CONSENT', 'plg_system_privacyconsent', $user->id);
        }
    }

	/**
	 * Returns yes/no
	 * @param array [username] & [password]
	 * @param mixed Boolean
	 *
	 * @return array
	 */
	function login( $credentials, $remember=true, $return='' )
  {
		$mainframe  = Factory::getApplication();

		if (strpos( $return, 'http' ) !== false && strpos( $return, Uri::base() ) !== 0) {
			$return = '';
		}

		// $credentials = array();
		// $credentials['username'] = JRequest::getVar('username', '', 'method', 'username');
		// $credentials['password'] = JRequest::getString('passwd', '', 'post', JREQUEST_ALLOWRAW);

		$options = array();
		$options['remember'] = (boolean)$remember;
		//$options['return'] = $return;

		//preform the login action
		$success = $mainframe->login($credentials);

		if ( $return ) {
			$mainframe->redirect( $return );
		}

		return $success;
	}

	/**
	 * Returns yes/no
	 * @param mixed Boolean
	 * @return array
	 */
	function logout( $return='' )
  {
		$mainframe  = Factory::getApplication();

		//preform the logout action//check to see if user has a joomla account
		//if so register with joomla userid
		//else create joomla account
		$success = $mainframe->logout();

		if (strpos( $return, 'http' ) !== false && strpos( $return, Uri::base() ) !== 0) {
			$return = '';
		}

		if ( $return ) {
			$mainframe->redirect( $return );
		}

		return $success;
	}

	/**
	 * Unblocks a user
	 *
	 * @param int $user_id
	 * @param int $unblock
	 * @return boolean
	 */
	function unblockUser($user_id, $unblock = 1)
	{
		$userFactory = Factory::getContainer()->get(UserFactory::class);
        $user = $userFactory->loadUserById((int)$user_id);

		if ($user->get('id')) {
			$user->set('block', !$unblock);

			if (  ! $user->save()) {
				return false;
			}

			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Returns yes/no
	 * @param object
	 * @param mixed Boolean
	 * @return array
	 */
	function _sendMail( &$user, $details, $useractivation )
  {
		$mainframe  = Factory::getApplication();
		$config = Factory::getApplication()->getConfig();

		$name 		= $user->get('name');
		if(empty($name)) {
			$name 		= $user->get('email');
		}
		$email 		= $user->get('email');
		$username 	= $user->get('username');
		$activation	= $user->get('activation');
		$password 	= $details['password2']; // using the original generated pword for the email

		$usersConfig 	= ComponentHelper::getParams( 'com_users' );

		$sitename 		=$config->get( 'sitename' );
		$mailfrom 		= $config->get( 'mailfrom' );
		$fromname 		= $config->get( 'fromname' );
		$siteURL		= Uri::base();

		$subject 	= Text::sprintf('J2STORE_ACCOUNT_DETAILS', $name, $sitename);
		$subject 	= html_entity_decode($subject, ENT_QUOTES);

		$send_password = $usersConfig->get('sendpassword', 0);

		if ( $useractivation == 1 ){
			$message = Text::sprintf( 'J2STORE_SEND_MSG_ACTIVATE', $name, $sitename, $siteURL."index.php?option=com_users&task=registration.activate&token=".$activation, $siteURL, $email, $password);
		} else {
			if($send_password) {
				$message = Text::sprintf('J2STORE_SEND_MSG', $name, $sitename, $siteURL, $email, $password );
			}else {
				$message = Text::sprintf('J2STORE_SEND_MSG_NOPW', $name, $sitename, $siteURL, $email );
			}
		}

		$message = html_entity_decode($message, ENT_QUOTES);

		$success = $this->_doMail($mailfrom, $fromname, $email, $subject, $message);

		return $success;
	}

    /**
     *
     * @return boolean
     * @throws \PHPMailer\PHPMailer\Exception
     */
	function _doMail( $from, $fromname, $recipient, $subject, $body, $actions=NULL, $mode=NULL, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL )
	{
		$success = false;

		$message = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
		$message->addRecipient( $recipient );
		$message->setSubject( $subject );
		$message->setBody( $body );
		$sender = array( $from, $fromname );
		$message->setSender($sender);
		try {
			$sent = $message->send();
		}catch(Exception $e) {
			//do nothing. Joomla has botched up the entire mail system from 3.5.1
            $sent = 0;
		}

		if ($sent == '1') {
			$success = true;
		}

		return $success;
	}

	/**
	 * Method to validate the password based on the password rules set under user manager options
	 * @param string $password 			password
	 * @param string $confirm_password 	confirm password
	 * @param array  $json 				json as a reference
	 * */
	function validatePassword($password,$confirm_password,&$json)
  {
		$config = J2Store::config();
		$minimumLength    =  4;
		$minimumIntegers  =  0;
		$minimumSymbols   =  0;
		$minimumUppercase =  0;
		$is_joomla_validate = $config->get('allow_password_validation',1);
		if($is_joomla_validate){
			$params = ComponentHelper::getParams('com_users');
			if (!empty($params))
			{
				$minimumLengthp    = $params->get('minimum_length');
				$minimumIntegersp  = $params->get('minimum_integers');
				$minimumSymbolsp   = $params->get('minimum_symbols');
				$minimumUppercasep = $params->get('minimum_uppercase');
				empty($minimumLengthp) ? : $minimumLength = (int) $minimumLengthp;
				empty($minimumIntegersp) ? : $minimumIntegers = (int) $minimumIntegersp;
				empty($minimumSymbolsp) ? : $minimumSymbols = (int) $minimumSymbolsp;
				empty($minimumUppercasep) ? : $minimumUppercase = (int) $minimumUppercasep;
			}

			$valueLength = strlen($password);

			// We set a maximum length to prevent abuse since it is unfiltered.
			if ($valueLength > 4096)
			{
				$json['error']['password'] = Text::_('J2STORE_PASSWORD_TOO_LONG');//Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_MSG_PASSWORD_TOO_LONG'), 'warning');
			}

			// We don't allow white space inside passwords
			$valueTrim = trim($password);

			if (strlen($valueTrim) != $valueLength)
			{
				$json['error']['password'] = Text::_('J2STORE_SPACES_IN_PASSWORD');
			}

		}

		// Minimum number of integers required
		if (!empty($minimumIntegers))
		{
			$nInts = preg_match_all('/[0-9]/', $password, $imatch);

			if ($nInts < $minimumIntegers)
			{
				$json['error']['password'] = Text::plural('J2STORE_NOT_ENOUGH_INTEGERS_N', $minimumIntegers);
			}
		}

		// Minimum number of symbols required
		if (!empty($minimumSymbols))
		{
			$nsymbols = preg_match_all('[\W]', $password, $smatch);

			if ($nsymbols < $minimumSymbols)
			{
				$json['error']['password'] = Text::plural('J2STORE_NOT_ENOUGH_SYMBOLS_N', $minimumSymbols);
			}
		}

		// Minimum number of upper case ASCII characters required
		if (!empty($minimumUppercase))
		{
			$nUppercase = preg_match_all("/[A-Z]/", $password, $umatch);

			if ($nUppercase < $minimumUppercase)
			{
				$json['error']['password'] = Text::plural('J2STORE_NOT_ENOUGH_UPPERCASE_LETTERS_N', $minimumUppercase);
			}
		}

		// Minimum length option
		if (!empty($minimumLength))
		{
			if (strlen((string) $password) < $minimumLength)
			{
				$json['error']['password'] = Text::plural('J2STORE_PASSWORD_TOO_SHORT_N', $minimumLength);
			}
		}

		if(empty($password)){
			$json['error']['password'] = Text::_('J2STORE_PASSWORD_REQUIRED');
		}
		if(empty($confirm_password)){
			$json['error']['confirm'] = Text::_('J2STORE_PASSWORD_REQUIRED');
		}
		if ($password != $confirm_password) {
			$json['error']['confirm'] = Text::_('J2STORE_PASSWORDS_DOESTNOT_MATCH');
		}
	}

	/**
	 * Method to get the Joomla user group name values
	 * @param int $user_id joomla user id
	 * @return array customer group names in an array of key value pairs
	 * */
	function getUserGroupNames($user_id = 0){
		if ($user_id == 0 ) {
			return array();
		}
        $userFactory = Factory::getContainer()->get(UserFactory::class);
        $user = $userFactory->loadUserById($user_id);
		$user_groups = $user->getAuthorisedGroups();
		$groupNames = array();
		if ( is_array($user_groups) && count($user_groups) > 0 ) {
			foreach ($user_groups as $groupId ){
				// remove public as it is global
				if ($groupId == 1) {
					continue;
				}
                $db = Factory::getContainer()->get('DatabaseDriver');
			    $db->setQuery(
			        'SELECT `title`' .
			        ' FROM `#__usergroups`' .
			        ' WHERE `id` = '. (int) $groupId
			    );
			    $groupNames[$groupId]= $db->loadResult();
			}
		}
		return $groupNames;
	}
}
