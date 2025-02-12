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

class JFormFieldCustomNotice extends FormField
{
	protected $type = 'customnotice';

	public function getInput()
    {
		$html = '';
		$html .= '<div class="alert alert-block alert-info"><strong>'.$this->getTitle().'</strong></div>';
		return  $html;
	}

	public function getLabel()
    {
		return '';
	}
}
