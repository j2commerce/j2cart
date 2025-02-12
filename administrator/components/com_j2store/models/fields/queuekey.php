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
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class JFormFieldQueuekey extends FormField
{
	protected $type = 'queuekey';

	public function getInput()
    {
		$config = J2Store::config();
		$queue_key = $config->get ( 'queue_key','' );
		$url = 'index.php?option=com_j2store&view=configuration&task=regenerateQueuekey';
		if(empty( $queue_key )){
			$queue_string = Factory::getApplication()->getConfig()->get('sitename','').time();
			$queue_key = md5 ( $queue_string );
			$config->saveOne ( 'queue_key', $queue_key );
		}
        $script = "<script>
		function regenerateQueueKey() {
            fetch('".$url."', {
                method: 'GET',
                cache: 'no-cache'
            })
            .then(response => response.json())
            .then(json => {
                if (json && json['queue_key']) {
                    document.getElementById('j2store_queue_key').innerHTML = json['queue_key'];
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
		</script>";
		$html = '';
        $html .= '<div class="alert alert-block alert-info">';
        $html .= '<strong id="j2store_queue_key">';
        $html .= $queue_key;
        $html .= '</strong>';
        $html .= '<a onclick="regenerateQueueKey()" class="btn btn-danger">';
        $html .= Text::_ ( 'J2STORE_STORE_REGENERATE' );
        $html .= '</a>';
        $html .= $script;
        $html .= '<input type="hidden" name="'.$this->name.'" value="'.$queue_key.'"/>';
        $html .= '</div>';
		return  $html;
	}
}
