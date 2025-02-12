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

use Joomla\Input\Input;

class Calculator
{
	public $calculator;

	public function __construct($type, $config=array())
    {
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		$suffix = 'Calculator';

		if(empty($type)) $type = 'standard';
		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$calculatorClass = ucfirst($type).$suffix;

		if (array_key_exists('input', $config))
		{
			if (!($config['input'] instanceof Input))
			{
				if (!is_array($config['input']))
				{
					$config['input'] = (array) $config['input'];
				}

				$config['input'] = array_merge($_REQUEST, $config['input']);
				$config['input'] = new Input($config['input']);
			}
		}
		else
		{
			$config['input'] = new Input;
		}

		if (!class_exists($calculatorClass))
		{
			$path = JPATH_ADMINISTRATOR.'/components/com_j2store/library/calculators/'.strtolower($calculatorClass).'.php';
			if(file_exists($path)) {
				require_once $path;
			}else {
				require_once JPATH_ADMINISTRATOR.'/components/com_j2store/library/calculators/standard.php';
			}
		}

		$result = new $calculatorClass($config);
		$this->setPricingCalculator($result);
	}

	public function setPricingCalculator($class)
    {
		$this->calculator = $class;
	}

	public function getPricingCalculator()
    {
		return $this->calculator;
	}

	public function calculate()
    {
		return $this->getPricingCalculator()->calculate();
	}
}
