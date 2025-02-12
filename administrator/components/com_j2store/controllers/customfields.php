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

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';

class J2StoreControllerCustomfields extends F0FController
{
    use list_view;

	public function __construct($config = [])
    {
		parent::__construct($config);
		$this->cacheableTasks = [];
	}

    public function browse()
    {
        $app = Factory::getApplication();
        $model = $this->getThisModel();
        $state = [];
        $state['field_namekey'] = $app->input->getString('field_namekey', '');
        $state['field_name'] = $app->input->getString('field_name', '');
        $state['filter_order'] = $app->input->getString('filter_order', 'j2store_customfield_id');
        $state['filter_order_Dir'] = $app->input->getString('filter_order_Dir', 'ASC');
        foreach ($state as $key => $value) {
            $model->setState($key, $value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $this->addBrowseToolBar();
        $header = array(
            'j2store_customfield_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_CUSTOM_FIELDS_ID'
            ),
            'field_namekey' => array(
                'sortable' => 'true',
                'show_link' => 'true',
                'type' => 'fieldsearchable',
                'url' => "index.php?option=com_j2store&amp;view=customfield&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_customfield_id',
                'label' => 'J2STORE_CUSTOM_FIELDS_NAMEKEY'
            ),
            'field_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'label' => 'J2STORE_CUSTOM_FIELDS_NAME'
            ),
            'field_core' => array(
                'type' => 'corefieldtypes',
                'sortable' => 'true',
                'label' => 'J2STORE_CUSTOM_FIELDS_CORE'
            ),
            'enabled' => array(
                'type' => 'published',
                'sortable' => 'true',
                'label' => 'J2STORE_ENABLED'
            )
        );
        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        echo $this->_getLayout('default',$vars);
    }

    /**
     * Delete selected item(s)
     *
     * @return  bool
     */
    public function remove()
    {
        $some_core_fields = false;
        $no_core_deleted = false;
        $deletion_status = true;

        // Initialise the App variables
        $app = Factory::getApplication();
        $cids = $app->input->get('cid',array(),'array');
        if (!empty($cids)) {
            $model = $this->getThisModel();
            foreach ($cids as $cid) {
				$item = $model->getItem($cid);
				if (!$item->get('field_core', 1)) {
				    $field = J2Store::fof()->loadTable('Customfields', 'J2StoreTable')->getClone();
				    if (!$field->delete($cid)) {
				        $deletion_status = false;
				        break;
				    }
				    $no_core_deleted = true;
				} else {
				    $some_core_fields = true;
				}
            }
        }

        if ($some_core_fields) {
            if ($no_core_deleted) {
                $msg = Text::_('J2STORE_CUSTOM_FIELD_DELETED_BUT_NOT_CORE_FIELDS');
            } else {
                $msg = Text::_('J2STORE_CUSTOM_FIELD_CANNOT_DELETE_CORE_FIELDS');
            }
        } else {
            $msg = Text::_('J2STORE_ITEMS_DELETED');
        }

        $link = 'index.php?option=com_j2store&view=customfields';

        if (!$deletion_status) {
            $this->setRedirect($link, Text::_('J2STORE_CUSTOM_FIELD_DELETED_ERROR'), 'error');
        } else {
			$this->setRedirect($link, $msg);
        }
    }

	/**
	 * Makes a customfield required
	 */
	public function public_publish()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			if($this->_csrfProtection() === false) return false;
		}
		$this->setpublic(1);
	}

	/**
	 * Makes a customfield not required
	 */
	public function public_unpublish()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			if($this->_csrfProtection() === false) return false;
		}
		$this->setpublic(0);
	}

	/**
	 * Sets the visibility status of a customfields
	 *
	 * @param int $state 0 = not require, 1 = require
	 */
	protected final function setpublic($state = 0)
	{
		$model = $this->getThisModel();

		if(!$model->getId()) $model->setIDsFromRequest();

		$status = $model->visible($state);

		// redirect

		if($customURL = $this->input->getString('returnurl','')) $customURL = base64_decode($customURL);
		$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.F0FInflector::pluralize($this->view);
		if(!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
		$this->redirect();
	}
}
