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

$row_class = 'row';
$col_class = 'col-md-';
?>
<div class="<?php echo $row_class;?>">
    <div class="<?php echo $col_class;?>12">

        <div class="alert alert-block alert-info">
            <h4 class="alert-heading"><?php echo Text::_('J2STORE_QUICK_HELP'); ?></h4>
            <?php echo Text::_('J2STORE_VARIANT_PRODUCT_HELP_TEXT'); ?>
        </div>
        <?php echo HTMLHelper::_('uitab.startTabSet', 'j2storetab', ['active' => 'generalTab', 'recall' => true, 'breakpoint' => 768]); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'generalTab', Text::_('J2STORE_PRODUCT_TAB_GENERAL')); ?>
        <div class="row">
            <div class="col-lg-12">
                <input type="hidden" name="<?php echo $this->form_prefix.'[j2store_variant_id]'; ?>" value="<?php echo isset($this->item->variant->j2store_variant_id) && !empty($this->item->variant->j2store_variant_id) ? $this->item->variant->j2store_variant_id: 0; ?>" />
                <?php echo $this->loadTemplate('variable_general');?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'imagesTab', Text::_('J2STORE_PRODUCT_TAB_IMAGES')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php  echo $this->loadTemplate('images');?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'variantsTab', Text::_('J2STORE_PRODUCT_TAB_VARIANTS')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php  echo $this->loadTemplate('variable_options');?>
                <?php  echo $this->loadTemplate('variants');?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'filterTab', Text::_('J2STORE_PRODUCT_TAB_FILTER')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php  echo $this->loadTemplate('filters');?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'relationsTab', Text::_('J2STORE_PRODUCT_TAB_RELATIONS')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php  echo $this->loadTemplate('relations');?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'appsTab', Text::_('J2STORE_PRODUCT_TAB_APPS')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php  echo $this->loadTemplate('apps');?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>
</div>
