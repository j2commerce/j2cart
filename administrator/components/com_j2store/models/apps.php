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
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

class J2StoreModelApps extends F0FModel
{
	/**
	 * Method to buildQuery to return list of data
	 * @see F0FModel::buildQuery()
	 * @return query
	 */
	public function buildQuery($overrideLimits = false)
    {
		$app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$this->getSelectQuery($query);
		$this->getWhereQuery($query);
		$query->order('app.name ASC, app.extension_id');
		return $query;
	}

	/**
	 * Method to getSelect query
	 * @param unknown_type $query
	 */
	protected function getSelectQuery(&$query)
	{
		$query->select("app.extension_id,app.name,app.type,app.folder,app.element,app.params,app.enabled,app.ordering, app.manifest_cache")
		->from("#__extensions as app");
	}

	protected function getWhereQuery(&$query)
	{
		$db = $this->_db;
		$query->where("app.type=".$db->q('plugin'));
		$query->where("app.element LIKE 'app_%'");
		$query->where("app.folder='j2store'");

		$search = $this->getState('search', '');
		if($search){
			$query->where(
					$db->qn('app.').'.'.$db->qn('name').' LIKE '.$db->q('%'.$search .'%')
			);
		}
	}

	public function getInserted($tablename)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
		$status = true;
        $application = Factory::getApplication();
		//Force parsing of SQL file since Joomla! does that only in install mode, not in upgrades
		$sql = 'components/com_j2store/sql/install/mysql/'.$tablename.'.sql';
		$queries = DatabaseDriver::splitSql(file_get_contents($sql));

		foreach ($queries as $query)
		{
			$query = trim($query);
			if ($query != '' && $query[0] != '#')
			{
				$db->setQuery($query);
				if (!$db->execute())
				{
					$application->enqueueMessage(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), 'error');
					$status  = false;
				}
			}
		}

		return $status;
	}
}
