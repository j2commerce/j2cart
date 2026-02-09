<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
/**
 * @package J2Store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
?>
<?php $row = $this->item;?>
    <!-- shipping plg name -->
    <h3><?php echo Text::_($row->name); ?></h3>
<?php
$app = Factory::getApplication();

PluginHelper::importPlugin('j2store');

$results = $app->triggerEvent( 'onJ2StoreGetShippingView',[$row]);

for ($i=0; $i<(is_countable($results) ? count($results) : 0); $i++)
{
    $result = $results[$i];
    echo $result;
}
