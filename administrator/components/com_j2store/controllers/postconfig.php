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
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

class J2StoreControllerPostconfig extends F0FController
{

	public function execute($task)
	{
		if ($task != 'saveConfig')
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	public function browse()
  {
		if(parent::browse()) {
			$config = J2Store::config();
			$complete = $config->get('installation_complete', 0);
			$platform = J2Store::platform();
			if($complete) {
                $platform->redirect('index.php?option=com_j2store&view=cpanel', Text::_('J2STORE_POSTCONFIG_STORE_SETUP_DONE_ALREADY'));
			}
			return true;
		}

		return false;
	}

	public function saveConfig()
  {
		//first CSRF check
		Session::checkToken() or die( 'Invalid Token' );

		$app = J2Store::platform()->application();
		$json = array();

		$values = $app->input->getArray($_POST);

		//NOT a PRO version ? check if the mandatory terms are accepted.
		if(J2Store::isPro() != 1) {
			if(!isset($values['acceptlicense'])) {
				$json['error']['acceptlicense'] = Text::_('J2STORE_POSTCONFIG_ERR_ACCEPTLICENSE');
			}

			if(!isset($values['acceptsupport'])) {
				$json['error']['acceptsupport'] = Text::_('J2STORE_POSTCONFIG_ERR_ACCEPTSUPPORT');
			}
		}

		//now we need a store name
		if(!$this->validate_field('store_name', $values)) {
			$json['error']['store_name'] = Text::_('J2STORE_FIELD_REQUIRED');
		}

		if(!$this->validate_field('store_zip', $values)) {
			$json['error']['store_zip'] = Text::_('J2STORE_FIELD_REQUIRED');
		}

		if(!$this->validate_field('country_id', $values)) {
			$json['error']['country_id'] = Text::_('J2STORE_FIELD_REQUIRED');
		}

		if(!$this->validate_field('config_currency', $values)) {
			$json['error']['config_currency'] = Text::_('J2STORE_FIELD_REQUIRED');
		}

		if(strlen($values['config_currency']) != 3) {
			$json['error']['config_currency'] = Text::_('J2STORE_CURRENCY_CODE_ERROR');
		}

		$currency_code = $values['config_currency'];
		$currency_symbol = isset($values['config_currency_symbol']) ? $values['config_currency_symbol'] : $currency_code;
		unset($values['config_currency_symbol']);

		if(!$json) {
            $db = Factory::getContainer()->get('DatabaseDriver');
			$query = 'REPLACE INTO #__j2store_configurations (config_meta_key,config_meta_value) VALUES ';

			$filter = InputFilter::getInstance(array(), array(), 1, 1);
			$conditions = array();
			foreach ($values as $metakey=>$value) {
				//now clean up the value

				if($metakey === 'tax_rate') {

					if(!empty($value)) {
						$rate = (float) $value;
						if($rate > 0) {
							try {
							$this->set_default_taxrate($rate, $values);
							} catch(Exception $e) {
								//do nothing. User can always set tax later
							}
						}
					}

					continue;
				}

				$clean_value = $filter->clean($value, 'string');
				$conditions[] = '('.$db->q(strip_tags($metakey)).','.$db->q($clean_value).')';
			}
			//add the admin email
			$conditions[] = '('.$db->quote('admin_email').','.$db->quote(Factory::getApplication()->getIdentity()->email).')';

			//set installation complete
			$conditions[] = '('.$db->q('installation_complete').','.$db->q('1').')';

			$query .= implode(',',$conditions);
			try {
				$db->setQuery($query);
				$db->execute();

				J2Store::fof()->getModel('Currencies', 'J2StoreModel')->create_currency_by_code($currency_code, $currency_symbol);
				$msg = Text::_('J2STORE_CHANGES_SAVED');
				$json['redirect'] = 'index.php?option=com_j2store&view=cpanel';
			}catch (Exception $e) {
                $json['error']['config_currency_symbol'] = $e->getMessage();
			}
		}

		echo json_encode($json);
		$app->close();
	}

	protected function validate_field($field, $values)
  {
		if(!isset($values[$field]) || empty($values[$field])) {
			return false;
		}
		return true;
	}

	protected function set_default_taxrate($rate, $values)
  {
		// get the country id
		$country_id = $values ['country_id'];

		//first check if taxrates were already set up. So that we can ignore

		$list = J2Store::fof()->getModel('Taxrates', 'J2StoreModel')->getList();
		if(count($list) > 0) return false;

		// first create a geozone.
		$geozone = J2Store::fof()->loadTable('Geozone', 'J2StoreTable')->getClone();
		$geozone->geozone_name = 'Default Geozone';
		$geozone->enabled = 1;

		try {
			$geozone->store ();
		} catch ( Exception $e ) {
			return false;
		}

		// create geozone rules
		if ($geozone->j2store_geozone_id) {
			$geozonerule = J2Store::fof()->loadTable('Geozonerule', 'J2StoreTable')->getClone();
			$geozonerule->geozone_id = $geozone->j2store_geozone_id;
			$geozonerule->country_id = $country_id;
			$geozonerule->zone_id = 0;

			try {
				$geozonerule->store ();
			} catch ( Exception $e ) {
				return false;
			}

			// now create a tax rate
			$taxrate = J2Store::fof()->loadTable('Taxrate', 'J2StoreTable')->getClone();
			$taxrate->geozone_id = $geozone->j2store_geozone_id;
			$taxrate->taxrate_name = 'VAT';
			$taxrate->tax_percent = $rate;
			$taxrate->enabled = 1;
			$taxrate->store ();

			// now create a tax profile
			$taxprofile = J2Store::fof()->loadTable('Taxprofile', 'J2StoreTable')->getClone();
			$taxprofile->taxprofile_name = 'Default tax profile';
			$taxprofile->enabled = 1;
			$taxprofile->store ();

			// now create the tax rule
			if ($taxrate->j2store_taxrate_id && $taxprofile->j2store_taxprofile_id) {
				$taxrule = J2Store::fof()->loadTable('Taxrule', 'J2StoreTable')->getClone();
				$taxrule->taxprofile_id = $taxprofile->j2store_taxprofile_id;
				$taxrule->taxrate_id = $taxrate->j2store_taxrate_id;
				$taxrule->address = 'billing';
				$taxrule->store ();
			}
		}
	}
}
