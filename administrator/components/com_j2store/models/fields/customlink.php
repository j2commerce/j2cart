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

class JFormFieldCustomLink extends FormField
{
	protected $type = 'customlink';

	public function getInput()
    {
		$html = '';
		$html .= '<btn type="button" class="btn btn-dark btn-sm" id="'.$this->id.'">';
		$html .= Text::_($this->element['text']);
		$html .= '</button>';
		return  $html;
	}

	public function getLabel()
    {
		return '';
	}
}
