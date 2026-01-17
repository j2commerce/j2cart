<?php
/**
 * @package     Joomla.Component
 * @subpackage  J2Store
 *
 * @copyright Copyright (C) 2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2024-26 J2Commerce, LLC. All rights reserved.
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

class J2Help {

	public static $instance = null;

	public function __construct($properties=null) {

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
    {
        $html = '';

        $video_url = 'https://www.youtube.com/@j2commerce';

        $html .= '<div class="video-tutorial panel panel-solid-info alert alert-info" role="alert">';
        $html .= '<span class="me-2">';
        $html .= '<span class="fas fa-solid fa-info-circle flex-shrink-0 me-2" area-hidden="true"></span>';
        $html .= Text::_('J2STORE_VIDEO_TUTORIALS_HELP_TEXT');
        $html .= '</span>';
        $html .= '<a class="btn btn-sm btn-primary text-light text-nowrap" target="_blank" href="' . $video_url . '">';
        $html .= Text::_('J2STORE_WATCH');
        $html .= '</a>';
        $html .= '</div>';

    return $html;
  }

	public function free_topbar() {
		$html = '';
		if ( J2Store::isPro() ) {
			return $html;
		}

        $free_topbar_url = J2Store::buildSiteLink('download', 'prolink');

        $html .= '<div class="free-topbar panel panel-solid-info alert alert-info" role="alert">';
        $html .= '<span class="me-2">';
        $html .= '<span class="fas fa-solid fa-info-circle flex-shrink-0 me-2" area-hidden="true"></span>';
        $html .= Text::_('J2STORE_FREE_TOPBAR_HELP_TEXT');
        $html .= '</span>';
        $html .= '<a class="btn btn-sm btn-primary text-light text-nowrap" target="_blank" href="' . $free_topbar_url . '">';
        $html .= Text::_('J2STORE_UPGRADE_PRO');
        $html .= '</a>';
        $html .= '</div>';

		return $html;
	}

	public function info_j2commerce()
	{
	    $html = '';

	    $type = 'j2commerce_isnew';

        // Check if this alert to be shown.
	    $params = J2Store::config();
	    if ($params->get($type, 0)) {
	        return $html;
	    }

	    $url = Route::_ ('index.php?option=com_j2store&view=cpanels&task=notifications&message_type=' . $type . '&' . Session::getFormToken() . '=1');

        $html .= '<div class="user-notifications alert alert-info ' . $type . '" role="alert">';
	    $html .= '<p>';
        $html .= '<span class="fas fa-solid fa-info-circle flex-shrink-0 me-2" area-hidden="true"></span>';
	    $html .= Text::_('J2STORE_TAKEOVER_INFO');
	    $html .= '</p>';
	    $html .= '<a class="btn btn-sm btn-dark text-light text-nowrap me-3" href="' . $url . '">' . Text::_('J2STORE_GOT_IT_AND_HIDE') . '</a>';
	    $html .= '<a href="https://www.j2commerce.com/j2store" class="btn btn-sm btn-primary text-light text-nowrap me-3" title="'.Text::_('J2STORE_VISIT_J2COMMERCE').'" target="_blank"><span class="fas fa-solid fa-external-link-alt fa-arrow-up-right-from-square me-2"></span>'.Text::_('J2STORE_FIND_OUT_MORE').'</a>';
	    $html .= '</div>';

	    return $html;
	}

	public function alert($type, $title, $message) {

		$html = '';

        // Check if this alert to be shown.
		$params = J2Store::config();
        if ($params->get($type, 0)) {
            return $html;
        }

		$url = Route::_ ( 'index.php?option=com_j2store&view=cpanels&task=notifications&message_type=' . $type . '&' . Session::getFormToken() . '=1' );

        $html .= '<div class="user-notifications alert alert-info ' . $type . '" role="alert">';
        $html .= '<h4 class="alert-heading">' . $title . '</h4>';
		$html .= '<p>' . $message . '</p>';
        $html .= '<a class="btn btn-sm btn-dark text-light text-nowrap" href="' . $url . '">' . Text::_('J2STORE_GOT_IT_AND_HIDE') . '</a>';
		$html .= '</div>';

		return $html;
	}

	public function alert_with_static_message($type, $title, $message) {

		$html = '';

        // Message never hidden
        $html .= '<div class="user-notifications alert alert-' . $type . '" role="alert">';
        $html .= '<h4 class="alert-heading">' . $title . '</h4>';
        $html .= '<p>' . $message . '</p>';
		$html .= '</div>';

		return $html;
	}

}
