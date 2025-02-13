<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  J2Commerce.app_localization_data
 *
 * @copyright Copyright (C) 2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/library/appcontroller.php');

class J2StoreControllerAppLocalization_data extends J2StoreAppController
{
    var $_element = 'app_localization_data';

    /**
     * constructor
     */
    function __construct()
    {
        parent::__construct();
    }

    public function insertTableValues()
    {
        $platform = J2Store::platform();
        $app = $platform->application();
        $model = $this->getModel('AppLocalizationdata', 'J2StoreModel');
        $tablename = $app->input->getString('table');
        $msgType = 'message';
        $msg = Text::_('J2STORE_TABLE_VALUE_INSERTED_SUCCESSFULLY');
        try {
            $model->getInstallerTool($tablename);
        } catch (Exception $e) {
            $msgType = 'warning';
            $msg = Text::_('J2STORE_TABLE_VALUE_INSERTION_ERROR');
        }
        $platform->redirect($this->baseLink(), $msg, $msgType);
    }
}
