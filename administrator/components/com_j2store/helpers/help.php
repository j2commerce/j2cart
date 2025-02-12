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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * J2Store help texts and videos.
 */

class J2Help
{
	public static $instance = null;

	public function __construct($properties=null)
  {

	}

	public static function getInstance(array $config = array())
	{
		if (!self::$instance)
		{
			self::$instance = new self($config);
		}

		return self::$instance;
	}

  public function watch_video_tutorials()
  { // update later with J2Commerce YouTube channel
    $video_url = J2Store::buildHelpLink('support/video-tutorials.html', 'dashboard');
      $html = '';
      $html .= '<div class="video-tutorial panel panel-solid-info">';
      $html .= '<p class="panel-body">';
      $html .= Text::_('J2STORE_VIDEO_TUTORIALS_HELP_TEXT');
      $html .= '<a class="btn btn-success" target="_blank" href="'.$video_url.'">';
      $html .= Text::_('J2STORE_WATCH');
      $html .= '</a>';
      $html .= '</p>';
      $html .= '</div>';
    return $html;
  }

	public function free_topbar()
  {
		$html = '';
		if ( J2Store::isPro() ) {
			return $html;
		}
		$free_topbar_url = J2Store::buildHelpLink('/j2store-pro-features.html', 'dashboard');
        $html .= '<div class="video-tutorial free-topbar panel panel-solid-info">';
        $html .= '<p class="panel-body">';
        $html .= Text::_('J2STORE_FREE_TOPBAR_HELP_TEXT');
        $html .= '<a class="btn btn-success" target="_blank" href="'.$free_topbar_url.'">'.Text::_('J2STORE_UPGRADE_PRO').'</a>';
        $html .= '</p>';
        $html .= '</div>';
		return $html;
	}

	public function info_j2commerce()
	{
	    $html = '';

	    $type = 'j2commerce_isnew';

	    //check if this alert to be shown.
	    $params = J2Store::config();
	    if ($params->get($type, 0)) {
	        return $html;
	    }

	        $class = 'alert alert-info';

	    $url = Route::_ ('index.php?option=com_j2store&view=cpanels&task=notifications&message_type=' . $type . '&' . Session::getFormToken() . '=1');

	    $html .= '<div class="user-notifications ' . $type . ' ' . $class . '" role="alert">';
	    $html .= '<p>';
	    $html .= '<span class="fas fa-solid fa-info-circle flex-shrink-0 me-2"></span>';
	    $html .= Text::_('J2STORE_TAKEOVER_INFO');
	    $html .= '</p>';
	    $html .= '<a class="btn btn-sm btn-dark text-light text-nowrap me-3" href="' . $url . '">' . Text::_('J2STORE_GOT_IT_AND_HIDE') . '</a>';
	    $html .= '<a href="https://www.j2commerce.com/j2store" class="btn btn-sm btn-primary text-light text-nowrap me-3" title="'.Text::_('J2STORE_VISIT_J2COMMERCE').'" target="_blank"><span class="fas fa-solid fa-external-link-alt fa-arrow-up-right-from-square me-2"></span>'.Text::_('J2STORE_FIND_OUT_MORE').'</a>';
	    $html .= '</div>';

	    return $html;
	}

	public function alert($type, $title, $message)
  {
		$html = '';

		//check if this alert to be shown.
		$params = J2Store::config();
		if($params->get($type, 0)) return $html;
            $class = 'alert alert-info';
		//message not hidden
		$url = Route::_ ( 'index.php?option=com_j2store&view=cpanels&task=notifications&message_type=' . $type . '&' . Session::getFormToken() . '=1' );
		$html .= '<div class="user-notifications ' . $type . ' '.$class.'">';
		$html .= '<h3>' . $title . '</h3>';
		$html .= '<p>' . $message . '</p>';
		$html .= '<br>';
		$html .= '<a class="btn btn-danger" href="' . $url . '">' . Text::_ ( 'J2STORE_GOT_IT_AND_HIDE' ) . '</a>';
		$html .= '</div>';
		return $html;
	}

	public function alert_with_static_message($type, $title, $message)
  {
		$html = '';
		//message not hidden
		$html .= '<div class="user-notifications alert alert-info alert-' . $type . ' ">';
		$html .= '<h3>' . $title . '</h3>';
		$html .= '<p><strong>' . $message . '</strong></p>';
		$html .= '<br>';
		$html .= '</div>';
		return $html;
	}
}
