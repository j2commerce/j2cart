<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.j2store
 *
 * @copyright   Copyright (c) 2014-17 Ramesh Elamathi / J2Store.org
 * @copyright   Copyright (c) 2024-2026 J2Commerce. All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Finder\J2Store\Extension\J2Store;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new J2Store(
                    //$container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('finder', 'j2store')
                );
                $plugin->setApplication(Factory::getApplication());
                $plugin->setDatabase($container->get(DatabaseInterface::class));

                return $plugin;
            }
        );
    }
};
