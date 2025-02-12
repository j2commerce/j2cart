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

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class JFormFieldcronlasthit extends FormField
{
	protected $type = 'cronlasthit';

	public function getInput() {
		$cron_hit = J2Store::config ()->get('cron_last_trigger','');

		if(empty( $cron_hit )){
			$note = Text::_('J2STORE_STORE_CRON_LAST_TRIGGER_NOT_FOUND');
		}elseif(J2Store::utilities ()->isJson ( $cron_hit )){
			$cron_hit = json_decode ( $cron_hit );
			$date =  isset( $cron_hit->date ) ? $cron_hit->date: '';
			$url = isset( $cron_hit->url ) ? $cron_hit->url:'';
			$ip = isset( $cron_hit->ip ) ? $cron_hit->ip:'';
			$note = Text::sprintf('J2STORE_STORE_CRON_LAST_TRIGGER_DETAILS',$date,$url,$ip);
		}
		return  '<strong>'.$note.'</strong>';
	}
}
