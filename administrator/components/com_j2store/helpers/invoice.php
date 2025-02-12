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

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactory;

class J2Invoice
{
	public static $instance = null;
	protected $state;

	public function __construct($properties=null)
  {

	}

	public static function getInstance(array $config = array())
	{
		if (!self::$instance)
		{
			self::$instance = new self($config);
		}

		return self::$instance;
	}

	public function loadInvoiceTemplate($order)
  {
		// Initialise
		$templateText = '';
		$subject = '';
		$loadLanguage = null;
		$isHTML = false;

		// Look for desired languages
		$jLang = Factory::getApplication()->getLanguage();
		$userLang = $order->customer_language;
        $userFactory = Factory::getContainer()->get(UserFactory::class);
        $user = $userFactory->loadUserById($order->user_id);
		if(empty($userLang) && ($user->id > 0)){
			$userLang = $user->getParam('language','');
		}

		$languages = [$userLang, $jLang->getTag(), $jLang->getDefault(), 'en-GB', '*'];

		//load all templates
		$allTemplates = $this->getInvoiceTemplates($order);

		if(count($allTemplates)){

			// Pass 1 - Give match scores to each template
			$preferredIndex = null;
			$preferredScore = 0;

			foreach($allTemplates as $idx => $template)
			{
				// Get the language and level of this template
				$myLang = $template->language;

				// Make sure the language matches one of our desired languages, otherwise skip it
				$langPos = array_search($myLang, $languages);
				if ($langPos === false)
				{
					continue;
				}
				$langScore = (5 - $langPos);


				// Calculate the score
				$score = $langScore;
				if ($score > $preferredScore)
				{
					J2Store::plugin ()->event ( 'InvoiceFileTemplate',array(&$template,$order) );
					$templateText = $template->body;
					$preferredScore = $score;
				}
			}
		} else {
			$templateText = Text::_('J2STORE_DEFAULT_INVOICE_TEMPLATE_TEXT');
		}
		return $templateText;
	}

	public function getInvoiceTemplates($order)
  {
        $db = Factory::getContainer()->get('DatabaseDriver');

			$query = $db->getQuery(true)
			->select('*')
			->from('#__j2store_invoicetemplates')
			->where($db->qn('enabled').'='.$db->q(1))
			->where(' CASE WHEN orderstatus_id = '.$db->q($order->order_state_id) .' THEN orderstatus_id = '.$db->q($order->order_state_id) .'
							ELSE orderstatus_id ="*" OR orderstatus_id =""
						END
					');
			if(isset($order->customer_group) && !empty($order->customer_group)) {
            $app = Factory::getApplication();
				if(J2Store::platform()->isClient('site')){
					$query->where(' CASE WHEN group_id IN( '.$order->customer_group.') THEN group_id IN('.$order->customer_group.')
									ELSE group_id ="*" OR group_id ="1" OR group_id =""
								END
					');
				}
			}
			$query->where(' CASE WHEN paymentmethod ='.$db->q($order->orderpayment_type).' THEN paymentmethod ='.$db->q($order->orderpayment_type).'
							ELSE paymentmethod="*" OR paymentmethod=""
						END
					');
			J2Store::plugin ()->event ( 'AfterInvoiceQuery',array(&$query,$order) );
			$db->setQuery($query);
			try {
				$allTemplates = $db->loadObjectList();
			} catch (Exception $e) {
				$allTemplates = array();
			}

		return $allTemplates;
	}

	public function	getFormatedInvoice($order)
  {
		$text = $this->loadInvoiceTemplate($order);
		$template =  J2Store::email()->processTags($text, $order, $extras = []);
		return $template;
	}

	public function processInlineImages($templateText)
  {
		$baseURL = str_replace('/administrator', '', Uri::base());
		//replace administrator string, if present
		$baseURL = ltrim($baseURL, '/');
		// Include inline images
		$pattern = '/(src)=\"([^"]*)\"/i';
		$number_of_matches = preg_match_all($pattern, $templateText, $matches, PREG_OFFSET_CAPTURE);
		if($number_of_matches > 0) {
			$substitutions = $matches[2];
			$last_position = 0;
			$temp = '';

			// Loop all URLs
			$imgidx = 0;
			$imageSubs = array();
			foreach($substitutions as &$entry)
			{
				// Copy unchanged part, if it exists
				if($entry[1] > 0)
					$temp .= substr($templateText, $last_position, $entry[1]-$last_position);
				// Examine the current URL
				$url = $entry[0];
				if( (substr($url,0,7) == 'http://') || (substr($url,0,8) == 'https://') ) {
					// External link, skip
					$temp .= $url;
				} else {
					$ext = strtolower(File::getExt($url));
					if(!file_exists($url)) {
						// Relative path, make absolute
						$url = $baseURL.ltrim($url,'/');
					}
					if( !file_exists($url) || !in_array($ext, array('jpg','png','gif','webp')) ) {
						// Not an image or nonexistent file
						$temp .= $url;
					} else {
						// Image found, substitute
						if(!array_key_exists($url, $imageSubs)) {
							// First time I see this image, add as embedded image and push to
							// $imageSubs array.
							$imgidx++;
							//$mailer->AddEmbeddedImage($url, 'img'.$imgidx, basename($url));
							$imageSubs[$url] = $imgidx;
						}
						// Do the substitution of the image
						$temp .= 'cid:img'.$imageSubs[$url];
					}
				}
				// Calculate next starting offset
				$last_position = $entry[1] + strlen($entry[0]);
			}
			// Do we have any remaining part of the string we have to copy?
			if($last_position < strlen($templateText))
				$temp .= substr($templateText, $last_position);
			// Replace content with the processed one
			$templateText = $temp;
		}
		return $templateText;
	}
}
