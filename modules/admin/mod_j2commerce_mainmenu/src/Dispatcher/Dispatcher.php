<?php
/**
 * @package     Joomla.Module
 * @subpackage  J2Commerce.mod_j2commerce_adminmenu
 *
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

namespace J2Commerce\Module\AdminMenu\Administrator\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;

/**
 * Dispatcher class for mod_j2commerce_adminmenu
 */
class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Runs the dispatcher.
     *
     * @return  void
     */
    public function dispatch()
    {        
        // The module can't show if J2Commerce is not enabled.
        if (!ComponentHelper::isEnabled('com_j2store')) {
            return;
        }
        
        Factory::getLanguage()->load('com_j2store', JPATH_ADMINISTRATOR);
        
        parent::dispatch();
    }
}
