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

class J2StoreAppController extends F0FController
{

    // the same as the plugin's one!
    var $_element = '';
    protected $cacheableTasks = array();

    function __construct($config = array())
    {
        parent::__construct($config);
        $this->registerTask('apply', 'save');
        $this->includeCustomModels();
    }

    /**
     * Overrides the getView method, adding the plugin's layout path
     */
    public function getView($name = '', $type = '', $prefix = '', $config = array())
    {
        $view = parent::getView($name, $type, $prefix, $config);
        $view->addTemplatePath(JPATH_SITE . '/plugins/j2store/' . $this->_element . '/' . $this->_element . '/tmpl/');
        return $view;
    }

    function save()
    {
        $platform = J2Store::platform();
        $app = $platform->application();
        $data = $app->input->getArray($_POST);
        $data_params = $app->input->post->get('params', array(), 'array');
        $save_params = $platform->getRegistry($data_params,true);
        $json = $save_params->toString();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)->update($db->qn('#__extensions'))->set($db->qn('params') . ' = ' . $db->q($json))->where($db->qn('element') . ' = ' . $db->q($this->_element))->where($db->qn('folder') . ' = ' . $db->q('j2store'))->where($db->qn('type') . ' = ' . $db->q('plugin'));
        $db->setQuery($query);
        $db->execute();
        if ($data ['appTask'] == 'apply' && isset ($data ['app_id'])) {
            $url = 'index.php?option=com_j2store&view=apps&task=view&id=' . $data ['app_id'];
        } else {
            $url = 'index.php?option=com_j2store&view=apps';
        }
        $cache = Factory::getCache();
        $cache->clean();
        $platform->redirect($url);
    }

    /**
     * Overrides the delete method, to include the custom models and tables.
     */
    public function delete()
    {
        parent::delete();
    }

    protected function includeCustomModels()
    {
        J2Store::fof()->loadModelFilePath(JPATH_SITE . '/plugins/j2store/' . $this->_element . '/' . $this->_element . '/models');
    }

    protected function baseLink()
    {
        $platform = J2Store::platform();
        $app = $platform->application();
        $id = $app->input->getInt('id', 0);
        return "index.php?option=com_j2store&view=apps&task=view&id={$id}";
    }
}
