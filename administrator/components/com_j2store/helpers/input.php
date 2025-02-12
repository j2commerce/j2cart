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

use Joomla\CMS\Language\Text;

/**
 * J2Store helper.
  */
class J2StoreInput
{
	public static $input;
	public static $name;
	protected $class;
	protected $element;
	protected $value;
	protected $type;
	protected $validate;

	function __construct()
  {

	}

	public static function getText($label,$name,$value,$type,$pholder,$options)
	{
		$class=$options['class'];
		$required=$options['required'];
		return "<input type='".$type."'  name='".$name."' placeholder='".$pholder."'  class='".$class."'  value='".htmlspecialchars($value, ENT_COMPAT, 'UTF-8')."'   $required/>";
	}

	public static function getLabel($name, $options)
	{
        $html = '';
        $html .= '<div class="control-label">';
        $html .= '<label for="'.Text::_($name).'">';
        $html .= Text::_($name);
        $html .= '</label>';
        $html .= '</div>';
		return $html;
	}

	public static function getTextarea($label,$name,$value,$type, $options)
	{
		$class=$options['class'] ? $options['class']:'';
		$required=$options['required'] ? $options['required'] :'';

        $html = '';
        $html .= '<div class="controls">';
        $html .= '<textarea type="'.$type.'" name="'.$name.'" class="'.$class.'" '.$required.'>'.$value.'</textarea>';
        $html .= '</div>';
        return $html;
	}
	public static function getControlGroup($label,$name,$value,$type,$pholder,$options)
	{
		$class=$options['class'] ? $options['class'] :'';
		$required=$options['required'] ? $options['required'] : '';
        $html = '';
        $html .= '<div class="control-group">';
        $html .= '<div class="control-label">';
        $html .= '<label for="'.Text::_($name).'>';
        $html .= Text::_($label);
        $html .= '</label>';
        $html .= '<div class="controls">';
        $html .= '<input  type="'.$type.'"  name="'.$name.'" placeholder="'.$pholder.'"  class="'.$class.'"  value="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'"  '.$required.'/>';
        $html .= '</div>';
        $html .= '</div>';
		return $html;
	}
}
