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

class J2Plugins
{
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

	/**
	 * Only returns plugins that have a specific event
	 *
	 * @param $eventName
	 * @param $folder
	 * @return array of JTable objects
	 */
  public function getPluginsWithEvent( $eventName, $folder='J2Store' )
	{
		$return = array();
		if ($plugins = $this->getPlugins( $folder ))
		{
			foreach ($plugins as $plugin)
			{
				if ($this->hasEvent( $plugin, $eventName ))
				{
					$return[] = $plugin;
				}
			}
		}
        JPluginHelper::importPlugin('j2store');
        $app = JFactory::getApplication();
        $app->triggerEvent('onJ2StoreAfterGetPluginsWithEvent', array(&$return));
		return $return;
	}

	/**
	 * Returns Array of active Plugins
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	public static function getPlugins( $folder='J2Store' )
	{
		$database = JFactory::getDBO();

		$order_query = " ORDER BY ordering ASC ";
		$folder = strtolower( $folder );

		$query = "
		SELECT
		*
		FROM
		#__extensions
		WHERE  enabled = '1'
		AND
		LOWER(`folder`) = '{$folder}'
		{$order_query}
		";

		$database->setQuery( $query );
		$data = $database->loadObjectList();
		return $data;
	}

	/**
	 * Returns an active Plugin
	 *
	 * @param
	 *        	mixed Boolean
	 * @param
	 *        	mixed Boolean
	 * @return array
	 */
	public static function getPlugin($element, $folder = 'j2store')
  {
		if (empty ( $element ))
			return false;
		$row = false;
		$db = Factory::getContainer()->get('DatabaseDriver');

		$folder = strtolower ( $folder );
		$query = $db->getQuery ( true )->select ( '*' )->from ( '#__extensions' )
						->where ( $db->qn ( 'enabled' ) . ' = ' . $db->q ( 1 ) )
						->where ( $db->qn ( 'folder' ) . ' = ' . $db->q ( $folder ) )
						->where ( $db->qn ( 'element' ) . ' = ' . $db->q ( $element ) );

		$db->setQuery ( $query );
		try {
			$row = $db->loadObject ();
		} catch ( Exception $e ) {
			return false;
		}
		return $row;
	}

	/**
	 * Returns HTML
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	public function getPluginsContent( $event, $options, $method='vertical' )
	{
		$text = "";
		jimport('joomla.html.pane');

		if (!$event) {
			return $text;
		}

		$args = array();

		$results = Factory::getApplication()->triggerEvent( $event, $options );

		if ((!count($results)) > 0) {
			return $text;
		}

		// grab content
		switch( strtolower($method) ) {
			case "vertical":
				for ($i=0, $iMax = count($results); $i< $iMax; $i++) {
					$result = $results[$i];
					$title = $result[1] ? Text::_( $result[1] ) : Text::_( 'Info' );
					$content = $result[0];

					// Vertical
					$text .= '<p>'.$content.'</p>';
				}
				break;
			case "tabs":
				break;
		}

		return $text;
	}

	/**
	 * Checks if a plugin has an event
	 *
	 * @param obj      $element    the plugin JTable object
	 * @param string   $eventName  the name of the event to test for
	 * @return unknown_type
	 */
	public function hasEvent( $element, $eventName )
	{
		$success = false;
		if (!$element || !is_object($element)) {
			return $success;
		}

		if (!$eventName || !is_string($eventName)) {
			return $success;
		}

		// Check if they have a particular event
		$import = PluginHelper::importPlugin( strtolower('J2Store'), $element->element );

		$result = Factory::getApplication()->triggerEvent( $eventName, array( $element ) );
		if (in_array(true, $result, true))
		{
			$success = true;
		}
		return $success;
	}

	public function enableJ2StorePlugin()
  {
		$db = Factory::getContainer()->get('DatabaseDriver');

		$folder = strtolower( 'j2store');

		$query = $db->getQuery(true)->update('#__extensions')->set('enabled=1')
					->where($db->qn('folder').' = '.$db->q('system'))
					->where($db->qn('element').' = '.$db->q('j2store'));
		$db->setQuery($query);
		$db->execute();
		return true;
	}

	public function importCatalogPlugins()
  {
		PluginHelper::importPlugin('content');
	}

	public function event($event, $args=array(), $prefix='onJ2Store')
  {
		if(empty($event)) return '';
		$this->importCatalogPlugins();
		PluginHelper::importPlugin('j2store');
        $platform = J2Store::platform();
        $result = $platform->eventTrigger($prefix.$event, $args);
		/*$app = Factory::getApplication();
        $result = $app->triggerEvent($prefix.$event, $args);*/
		return $result;
	}

	/**
	 * Method to get the html output of an event
	 * @param string $event
	 * @param array $args
	 * @return string
	 */
	public function eventWithHtml($event, $args=array(), $prefix='onJ2Store')
  {
		if(empty($event)) return '';
		PluginHelper::importPlugin('j2store');
		$app = Factory::getApplication();
		$html = '';
        $platform = J2Store::platform();
        $results = $platform->eventTrigger($prefix.$event, $args);
		foreach($results as $result) {
			$html .= $result;
		}
		return $html;
	}

	public function eventWithArray($event, $args=array(), $prefix='onJ2Store')
  {
		if(empty($event)) return '';
		PluginHelper::importPlugin('j2store');
		$app = Factory::getApplication();
        $platform = J2Store::platform();
        $results = $platform->eventTrigger($prefix.$event, $args);
		$array = array();
		if(isset($results[0])) {
			$array = $results[0];
		}
		return $array;
	}
}
