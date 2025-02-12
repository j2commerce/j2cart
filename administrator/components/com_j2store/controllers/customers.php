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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';

class J2StoreControllerCustomers extends F0FController
{
    use list_view;

	  public function __construct($config = [])
	  {
		    parent::__construct($config);
		    $this->registerTask('confirmchangeEmail','changeEmail');
	  }

    public function browse()
    {
        $app = Factory::getApplication();
        $option = $app->input->getCmd('option', '');
        $msg = Text::_($option . '_CONFIRM_DELETE');
        ToolBarHelper::deleteList(strtoupper($msg));
        $this->exportButton('customers');
        $model = $this->getThisModel();
        $state = [];
        $state['customer_name'] = $app->input->getstring('customer_name','');
        $state['email'] = $app->input->getString('email','');
        $state['address_1'] = $app->input->getString('address_1','');
        $state['country_name']= $app->input->getstring('country_name','');
        $state['company']= $app->input->getstring('company','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_address_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $header = array(
            'j2store_address_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_CUSTOMER_ID'
            ),
            'customer_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=customer&amp;task=viewOrder&amp;email_id=[ITEM:ID]",
                'url_id' => 'email',
                'label' => 'J2STORE_CUSTOMER_NAME'
            ),
            'email' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'label' => 'J2STORE_EMAIL'
            ),
            'address_1' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_LINE1'
            ),
            'address_2' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_LINE2'
            ),
            'country_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_COUNTRY'
            ),
            'zone_name' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ZONE'
            ),
            'company' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'label' => 'J2STORE_EMAILTEMPLATE_TAG_BILLING_COMPANY'
            ),
            'zip' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_ZIP'
            ),
            'phone_1' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_TELEPHONE'
            ),
            'phone_2' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_MOBILE'
            )
        );
        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        $format = $app->input->get('format','html');
        if($format === 'csv'){
            $this->display();
        }else{
            echo $this->_getLayout('default',$vars);
        }
    }

	/**
	 *
	 * @return boolean
	 */
	function viewOrder()
  {
		$email  = $this->input->getString('email_id');
		$user_id = $this->input->getInt('user_id');
		$this->layout='view';
		$this->display();
		return true;
	}

	/**
	 * Delete selected item(s)
	 *
	 * @return  bool
	 */
	public function remove()
	{
		// Initialise the App variables
		$app = Factory::getApplication();
		$cids = $app->input->get('cid',array(),'ARRAY');
		if(!empty( $cids ) && J2Store::platform()->isClient('administrator') ){
			foreach ($cids as $cid){
				// store the table in the variable
				$address = J2Store::fof()->loadTable('Address', 'J2StoreTable')->getClone ();
				$address->load($cid);
				$addresses = J2Store::fof()->getModel('Addresses','J2StoreModel')->email($address->email)->getList();

				foreach ($addresses as $e_address){
					$address = J2Store::fof()->loadTable('Address', 'J2StoreTable')->getClone ();
					$address->load($e_address->j2store_address_id);
					$address->delete ();
				}
			}
		}
		$msg = Text::_('J2STORE_ITEMS_DELETED');
		$link = 'index.php?option=com_j2store&view=customers';
		$this->setRedirect($link, $msg);
	}

	/**
	 * Method to delete customer
	 */
	function delete()
	{
		// Initialise the App variables
		$app = Factory::getApplication();
		// Assign the get Id to the Variable
		$id=$app->input->getInt('id');

		if($id && J2Store::platform()->isClient('administrator'))
		{	// store the table in the variable
			$address = J2Store::fof()->loadTable('Address', 'J2StoreTable');
			$address->load($id);
			$email = $address->email;
			try {
				$address->delete();
				$msg = Text::_('J2STORE_ITEMS_DELETED');
			} catch (Exception $error) {
				$msg = $error->getMessage();
			}
		}

		$link = 'index.php?option=com_j2store&view=customer&task=viewOrder&email_id='.$email;
		$this->setRedirect($link, $msg);
	}

	function editAddress()
  {
		// Initialise the App variables
		$app = Factory::getApplication();
		// Assign the get Id to the Variable
		$id = $app->input->getInt('id',0);
		if($id && J2Store::platform()->isClient('administrator')) {    // store the table in the variable
			$address = J2Store::fof()->loadTable('Address','J2StoreTable');
			$address->load($id);
			$address_type = $address->type;
			if(empty( $address_type )){
				$address_type = 'billing';
			}
			$model = J2Store::fof()->getModel('Customers','J2StoreModel');
			$view = $this->getThisView();
			$view->setModel($model, true);
			$view->addTemplatePath(JPATH_ADMINISTRATOR.'/components/com_j2store/views/customer/tmpl/');
			$view->set('address_type',$address_type);
			$fieldClass  = J2Store::getSelectableBase();
			$view->set('fieldClass' , $fieldClass);
			$view->set('address',$address);
			$view->set('item',$address);
			$view->setLayout('editaddress');
			$view->display();
			//$this->display();
			return true;

		}else{
			$this->redirect ('index.php?option=com_j2store&view=customers');
		}
	}

    function saveCustomer()
    {
        $app = Factory::getApplication ();
        $data = $app->input->getArray($_POST);
        $address_id = $app->input->getInt('j2store_address_id');
        $address = J2Store::fof()->loadTable('Address','J2StoreTable');
        $address->load($address_id);
        $data['id'] = $data['j2store_address_id'];
        unset( $data['j2store_address_id'] );
        $data['user_id'] = $address->user_id;
        $data['email'] = $address->email;
        $selectableBase = J2Store::getSelectableBase();
        if(!in_array($data['type'],array('billing','shipping'))){
            $data['type'] = 'billing';
        }
        $data['admin_display_error'] = true;
        $json = $selectableBase->validate($data, $data['type'], 'address');
        if(empty($json['error'])){
            $msg = Text::_('J2STORE_ADDRESS_SAVED_SUCCESSFULLY');
            $msgType='message';
            $address->bind($data);
            if($address->save($data)){
                $json['success']['url'] = "index.php?option=com_j2store&view=customer&task=editAddress&id=".$address->j2store_address_id."&tmpl=component";
                $json['success']['msg'] = Text::_('J2STORE_ADDRESS_SAVED_SUCCESSFULLY');
                $json['success']['address_id'] = $address->j2store_address_id;
                $json['success']['msgType']='success';
            }else{
                $json['error']['message'] = $address->getError ();
                $json['error']['msgType']='error';
            }
        }
        echo json_encode($json);
        $app->close();
    }

	function changeEmail()
  {
		// Initialise the App variables
		$app = Factory::getApplication();
		if(J2Store::platform()->isClient('administrator')){
			$json = [];
			$model = $this->getThisModel();
			// Assign the get Id to the Variable
			$email_id=$app->input->getString('email');
			$new_email=$app->input->getString('new_email');

			if(empty($new_email) && !MailHelper::isEmailAddress($new_email) ){
				$json = array('msg' => Text::_('Invalid Email Address'), 'msgType' => 'warning');
			}else{
				//incase an account already exists ?
				if($app->input->getString('task') === 'changeEmail'){

					$json = array('msg' => Text::_('J2STORE_EMAIL_UPDATE_NO_WARNING'), 'msgType' => 'message');
					$json = $this->validateEmailexists($new_email);

				}elseif($app->input->getString('task') === 'confirmchangeEmail'){

					$json = array( 'redirect' => Uri::base().'index.php?option=com_j2store&view=customer&task=viewOrder&email_id='.$new_email, 'msg' => Text::_('J2STORE_SUCCESS_SAVING_EMAIL'), 'msgType' => 'message');
					if(!$model->savenewEmail()){
						$json = array('msg' => Text::_('J2STORE_ERROR_SAVING_EMAIL'), 'msgType' => 'warning' );
					}
				}

			}
			echo json_encode($json);
			$app->close();
		}
	}

	function validateEmailexists($new_email)
  {
		$json = [];
		$success = true;
		$model = $this->getThisModel();

		if(J2Store::user()->emailExists($new_email)){
			$success = false;
			$json = array('msg' => Text::_('J2STORE_EMAIL_UPDATE_ERROR_WARNING'), 'msgType' => 'warning');
		}

		if($success){
			$json = array( 'redirect' => Uri::base().'index.php?option=com_j2store&view=customer&task=viewOrder&email_id='.$new_email, 'msg' => Text::_('J2STORE_SUCCESS_SAVING_EMAIL'), 'msgType' => 'message');
			if(!$model->savenewEmail()){
				$json = array('msg' => Text::_('J2STORE_ERROR_SAVING_EMAIL'), 'msgType' => 'warning' );
			}
		}
		return $json;
	}
}
