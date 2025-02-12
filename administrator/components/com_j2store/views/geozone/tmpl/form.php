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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.formvalidator');
jimport('joomla.filesystem.file');
$countries = J2StoreHelperSelect::getCountries();
$row_class = 'row';
$col_class = 'col-md-';
$btn_class = 'btn-sm';
?>
<div class="j2store">
    <div class="main-card card">
        <div class="card-body">
    <form class="form-horizontal" id="adminForm" name="adminForm" method="post" action="index.php">
        <input type="hidden" name="option" value="com_j2store">
        <input type="hidden" name="view" value="geozone">
        <input type="hidden" name="task" value="">
        <input type="hidden" name="j2store_geozone_id" value="<?php echo $this->item->j2store_geozone_id; ?>"/>
                <?php echo HTMLHelper::_('form.token'); ?>
                <fieldset class="options-form mb-4">
                    <legend><?php echo Text::_('J2STORE_GEOZONE'); ?></legend>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-grid">
                <div class="control-group">
                                    <div class="control-label">
                                        <label><?php echo Text::_('J2STORE_GEOZONE_NAME'); ?></label>
                                    </div>
                    <div class="controls">
                                        <?php echo J2Html::text('geozone_name', $this->item->geozone_name, array('class'=>'form-control')); ?>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">
                                        <?php echo Text::_('J2STORE_ENABLED') ?>
                    </label>
                    <div class="controls">
                        <?php
                        echo J2Html::select()->clearState()
                            ->type('genericlist')
                            ->name('enabled')
                            ->value($this->item->enabled)
                                            ->attribs(array('class'=>'form-select'))
                            ->setPlaceHolders(
                                                array(0 => Text::_('J2STORE_DISABLE'), 1 => Text::_('J2STORE_ENABLED'))
                            )->getHtml();

                        ?>
                    </div>
                </div>
            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="j2storehelp alert alert-info"><?php echo Text::_('J2STORE_GEOZONE_HELP_TEXT'); ?></div>
            </div>
        </div>
                </fieldset>
                <fieldset class="options-form">
                    <legend><?php echo Text::_('J2STORE_GEOZONE_COUNTRIES_AND_ZONES'); ?></legend>
                <?php if ($this->item->j2store_geozone_id): ?>
                        <div class="btn-toolbar gap-2">
                        <div class="btn-wrapper">
                                <?php echo J2StorePopup::popupAdvanced('index.php?option=com_j2store&view=countries&layout=modal&task=elements&tmpl=component&geozone_id=' . $this->item->j2store_geozone_id, '<i class="icon icon-download"></i> ' . Text::_('J2STORE_IMPORT_COUNTRIES'), array('class' => 'btn btn-sm btn-success', 'width' => 800, 'height' => 600, 'refresh' => true,'id'=>'fancybox')); ?>
                        </div>
                        <div class="btn-wrapper">
                                <?php echo J2StorePopup::popupAdvanced('index.php?option=com_j2store&view=zones&layout=modal&task=elements&tmpl=component&geozone_id=' . $this->item->j2store_geozone_id, '<i class="icon icon-download"></i> ' . Text::_('J2STORE_IMPORT_ZONES'), array('class' => 'btn btn-sm btn-success', 'width' => 800, 'height' => 600, 'refresh' => true,'id'=>'fancybox')); ?>
                        </div>
                    </div>
                <?php endif; ?>
                    <div class="table-responsive">
                        <table id="geozone_rule_table" class="table itemList">
                    <thead>
                    <tr>
                                    <th scope="col"><?php echo Text::_('J2STORE_COUNTRY'); ?></th>
                                    <th scope="col"><?php echo Text::_('J2STORE_ZONE'); ?></th>
                        <th></th>
                    </tr>
                    </thead>

                    <?php $zone_to_geo_zone_row = 0;
                    if (isset($this->item->geoRuleList) && !empty($this->item->geoRuleList)):
                        ?>
                        <tbody>
                        <?php foreach ($this->item->geoRuleList as $geozonerule) : ?>
                            <tr id="zone-display-row-<?php echo $zone_to_geo_zone_row; ?>">
                                        <td><?php echo $geozonerule->country; ?></td>
                                        <td><?php echo !empty($geozonerule->zone) ? $geozonerule->zone : Text::_('J2STORE_ALL_ZONES'); ?></td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-primary" onclick="toggleEditZone('<?php echo $zone_to_geo_zone_row ?>','<?php echo $geozonerule->country_id; ?>','<?php echo $geozonerule->zone_id; ?>')"><span class="icon icon-edit me-1"></span><?php echo Text::_('J2STORE_EDIT'); ?></a>
                                            <a class="btn btn-sm btn-danger" onclick="j2storeRemoveZone(<?php echo $geozonerule->j2store_geozonerule_id; ?>, <?php echo $zone_to_geo_zone_row; ?>);"><span class="icon icon-trash me-1"></span> <?php echo Text::_('J2STORE_REMOVE'); ?>
                                    </a>
                                </td>
                            </tr>
                            <tr id="zone-to-geo-zone-row<?php echo $zone_to_geo_zone_row; ?>" style="display: none;">
                                        <td>
                                    <?php
                                            $attr = array("onchange" => "getZones($zone_to_geo_zone_row,this.value)","class"=>"form-select");
                                    echo J2Html::select()->clearState()
                                        ->type('genericlist')
                                        ->name('zone_to_geo_zone[' . $zone_to_geo_zone_row . '][country_id]')
                                        ->value($geozonerule->country_id)
                                        ->attribs($attr)
                                        ->setPlaceHolders(
                                                    array('' => Text::_('J2STORE_SELECT_OPTION'))
                                        )
                                        ->hasOne('Countries')
                                        ->setRelations(array(
                                                'fields' => array(
                                                    'key' => 'j2store_country_id',
                                                    'name' => array('country_name')
                                                )
                                            )
                                        )->getHtml();


                                    ?>
                                </td>
                                        <td>
                                            <select name="zone_to_geo_zone[<?php echo $zone_to_geo_zone_row; ?>][zone_id]" class="form-select" id="zone<?php echo $zone_to_geo_zone_row; ?>">
                                    </select>
                                    <?php echo J2Html::hidden('zone_to_geo_zone[' . $zone_to_geo_zone_row . '][j2store_geozonerule_id]', $geozonerule->j2store_geozonerule_id); ?>
                                </td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-danger" onclick="j2storeRemoveZone(<?php echo $geozonerule->j2store_geozonerule_id; ?>, <?php echo $zone_to_geo_zone_row; ?>);"><span class="icon icon-trash me-1"></span> <?php echo Text::_('J2STORE_REMOVE'); ?>
                                    </a></td>
                            </tr>

                            <?php $zone_to_geo_zone_row++; ?>
                        <?php endforeach; ?>
                        </tbody>
                    <?php endif; ?>
                    <tfoot>
                    <tr>
                                <td colspan="2" class="text-start">
                                    <a class="btn btn-primary" onclick="j2storeAddGeoZone();"><?php echo Text::_('J2STORE_GEOZONE_ADD_COUNTRY_OR_ZONE'); ?></a>
                        </td>
                    </tr>
                    </tfoot>
                </table>
                    </div>
                </fieldset>
            </form>
            </div>
        </div>
</div>

<script type="text/javascript">
    function toggleEditZone(id, country_id, zone_id) {
       // Hide the display row
       document.getElementById('zone-display-row-' + id).style.display = 'none';

       // Show the geo-zone row
       document.getElementById('zone-to-geo-zone-row' + id).style.display = 'block';

       // Load the zones via an AJAX request
       fetch('index.php?option=com_j2store&view=geozones&task=getZone&country_id=' + country_id + '&zone_id=' + zone_id)
           .then(response => response.text())
           .then(html => {
               const zoneElement = document.getElementById('zone' + id);
               if (zoneElement) {
                   zoneElement.innerHTML = html;
               }
           })
           .catch(error => console.error('Error loading zones:', error));
    }

    var zone_to_geo_zone_row = <?php echo $zone_to_geo_zone_row; ?>;

    function j2storeAddGeoZone() {
       // Create the HTML content
       let html = '<tbody id="zone-to-geo-zone-row' + zone_to_geo_zone_row + '">';
            html += '<tr>';
       html += '<td><select name="zone_to_geo_zone[' + zone_to_geo_zone_row + '][country_id]" class="form-select" id="country' + zone_to_geo_zone_row + '" onchange="getZones(' + zone_to_geo_zone_row + ', this.value)">';
            <?php   foreach ($countries as $key=>$value) { ?>
            html += '<option value="<?php echo $key; ?>"><?php  echo addslashes($value); ?></option>';
            <?php } ?>
            html += '</select></td>';
       html += '<td><select name="zone_to_geo_zone[' + zone_to_geo_zone_row + '][zone_id]" class="form-select" id="zone' + zone_to_geo_zone_row + '"></select></td>';
            html += '<input type="hidden" name="zone_to_geo_zone[' + zone_to_geo_zone_row + '][j2store_geozonerule_id]" value="" /></td>';
       html += '<td class="text-end"><a onclick="document.getElementById(\'zone-to-geo-zone-row' + zone_to_geo_zone_row + '\').remove();" class="btn-sm btn btn-danger"><span class="icon icon-trash me-1"></span><?php echo Text::_('J2STORE_REMOVE'); ?></a></td>';
            html += '</tr>';
            html += '</tbody>';

       // Insert the new HTML before the table footer
       const geozoneRuleTable = document.getElementById('geozone_rule_table');
       const tableFoot = geozoneRuleTable.querySelector('tfoot');
       tableFoot.insertAdjacentHTML('beforebegin', html);

       // Load zones via AJAX
       const countrySelect = document.getElementById('country' + zone_to_geo_zone_row);
       const zoneSelect = document.getElementById('zone' + zone_to_geo_zone_row);
       const countryId = countrySelect ? countrySelect.value : 0;

       fetch(`index.php?option=com_j2store&view=geozone&task=getZone&country_id=${countryId}&zone_id=0`)
           .then(response => response.text())
           .then(html => {
               if (zoneSelect) {
                   zoneSelect.innerHTML = html;
               }
           })
           .catch(error => console.error('Error loading zones:', error));

       // Increment the row counter
            zone_to_geo_zone_row++;
    }

    function j2storeRemoveZone(geozonerule_id, zone_to_geo_zone_row) {
        (function ($) {
            $('.j2storealert').remove();
            $.ajax({
                method: 'post',
                url: 'index.php?option=com_j2store&view=geozones&task=removeGeozoneRule',
                data: {'rule_id': geozonerule_id},
                dataType: 'json'
            }).done(function (response) {
                $('#zone-to-geo-zone-row' + zone_to_geo_zone_row).remove();
                $('#zone-display-row-' + zone_to_geo_zone_row).remove();
                $('#geozone_rule_table').before('<div class="j2storealert alert alert-danger">' + response.msg + '</div>');
            });
        })(j2store.jQuery);
    }

    function getZones(zone_id, country_id) {
        j2store.jQuery('#zone' + zone_id).load('index.php?option=com_j2store&view=geozones&task=getZone&country_id=' + country_id + '&zone_id=0');
    }
   </script>
