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

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';

class JFormFieldReceiverTypes extends ListField
{
	protected $type = 'ReceiverTypes';

	public function getRepeatable()
	{
		$html ='';

		$list = array(
			'*' => Text::_( 'J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_BOTH' ),
		 'admin'=> Text::_( 'J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_ADMIN' ),
		'customer'=>Text::_( 'J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_CUSTOMER')
		);
		if(empty($this->item->receiver_type)) $this->item->receiver_type = '*';
		$html .= $list[$this->item->receiver_type];
		return $html;
	}
}
