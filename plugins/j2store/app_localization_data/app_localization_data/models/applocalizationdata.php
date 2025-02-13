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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

class J2StoreModelAppLocalizationdata extends F0FModel
{
    /**
     * Method to truncate and insert the values into requested to table
     * @param string $tablename
     * @return boolean
     */
    function getInstallerTool($tablename)
    {
        $status = false;

        //Get database
        $db = Factory::getContainer()->get('DatabaseDriver');

        //incase table is metrics
        if ($tablename == 'metrics') {
            if (!$this->getTruncateTable('lengths')) {
                $status = false;
            }

            if (!$this->getTruncateTable('weights')) {
                $status = false;
            }

        } else {
            if (!$this->getTruncateTable($tablename)) {
                $status = false;
            }
        }
        return $status;
    }

    /**
     * Method to truncated the table completely
     * @param string $tablename
     * @return boolean
     */
    public function getTruncateTable($tablename)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = "TRUNCATE TABLE " . $db->quoteName('#__j2store_' . $tablename);
        $db->setQuery($query);
        $status = true;
        $app = J2Store::platform()->application();
        if (!$db->execute()) {
            $app->enqueueMessage(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), 'error');
            $status = false;
        }
        if ($status) {
            if (!$this->getInserted($tablename)) {
                $status = false;
            }
        }
        return $status;
    }

    /**
     * Method to insert the values from the .sql file
     * @param string $tablename
     * @return boolean
     * @throws Exception
     */
    public function getInserted($tablename)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $status = true;

        //Force parsing of SQL file since Joomla! does that only in install mode, not in upgrades
        $sql = JPATH_ADMINISTRATOR . '/components/com_j2store/sql/install/mysql/' . $tablename . '.sql';
        $queries = DatabaseDriver::splitSql(file_get_contents($sql));
        $app = J2Store::platform()->application();
        foreach ($queries as $query) {
            $query = trim($query);
            if ($query != '' && $query[0] != '#') {
                $db->setQuery($query);
                if (!$db->execute()) {
                    $app->enqueueMessage(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), 'error');
                    $status = false;
                }
            }
        }
        return $status;
    }
}
