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

class J2StoreControllerShippingPlugin extends F0FController
{
	// the same as the plugin's one!
	var $_element = '';

	/**
	 * Overrides the getView method, adding the plugin's layout path
	 */
 	public function getView( $name = '', $type = '', $prefix = '', $config = array() )
    {
    	$view = parent::getView( $name, $type, $prefix, $config );
    	$view->addTemplatePath(JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/tmpl/');
    	return $view;
    }

    /**
     * Overrides the delete method, to include the custom models and tables.
     */
    public function delete()
    {
    	$this->includeCustomModel('ShippingRates');
    	parent::delete();
    }

    protected function includeCustomModel( $name )
    {
    	$dispatcher = J2Store::platform()->application();
		$dispatcher->triggerEvent('includeCustomModel', array($name, $this->_element) );
    }

    protected function baseLink()
    {
    	$id = Factory::getApplication()->input->getInt('id', '');
    	return "index.php?option=com_j2store&view=shippings&task=view&id={$id}";
    }
}
