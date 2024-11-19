<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;


$is_Pro = J2Store::isPro();
$row_class = 'row';
$col_class = 'col-md-';

$this->tab_name = 'j2storetab';
$this->useCoreUI = true;

?>
<div class="<?php echo $row_class;?>">
    <div class="<?php echo $col_class;?>12">
        <div class="alert alert-block alert-info">
            <h4 class="alert-heading"><?php echo Text::_('J2STORE_QUICK_HELP'); ?></h4>
            <?php echo Text::_('J2STORE_FLEXIVARIANT_PRODUCT_HELP_TEXT'); ?>
        </div>
        
        <?php echo HTMLHelper::_('uitab.startTabSet', $this->tab_name, ['active' => 'generalTab', 'recall' => true, 'breakpoint' => 768]); ?>
        <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'generalTab', Text::_('J2STORE_PRODUCT_TAB_GENERAL')); ?>
        <div class="row">
            <div class="col-lg-12">
                <input type="hidden" name="<?php echo $this->form_prefix.'[j2store_variant_id]'; ?>" value="<?php echo isset($this->variant->j2store_variant_id) && !empty($this->variant->j2store_variant_id) ? $this->variant->j2store_variant_id: 0; ?>" />
                <?php if($is_Pro):?>
                <?php echo $this->loadTemplate('flexivariable_general');?>
                <?php else:?>
                    <?php echo J2Html::pro(); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'imagesTab', Text::_('J2STORE_PRODUCT_TAB_IMAGES')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php if($is_Pro):?>
                    <?php echo $this->loadTemplate('images');?>
                <?php else:?>
                    <?php echo J2Html::pro(); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'variantsTab', Text::_('J2STORE_PRODUCT_TAB_VARIANTS')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php if($is_Pro):?>
                    <?php echo $this->loadTemplate('flexivariable_options');?>
                    <?php echo $this->loadTemplate('flexivariablevariants');?>
                <?php else:?>
                    <?php echo J2Html::pro(); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'filterTab', Text::_('J2STORE_PRODUCT_TAB_FILTER')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php if($is_Pro):?>
                    <?php echo $this->loadTemplate('filters');?>
                <?php else:?>
                    <?php echo J2Html::pro(); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'relationsTab', Text::_('J2STORE_PRODUCT_TAB_RELATIONS')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php if($is_Pro):?>
                    <?php echo $this->loadTemplate('relations');?>
                <?php else:?>
                    <?php echo J2Html::pro(); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'appsTab', Text::_('J2STORE_PRODUCT_TAB_APPS')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php if($is_Pro):?>
                    <?php echo $this->loadTemplate('apps');?>
                <?php else:?>
                    <?php echo J2Html::pro(); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
        
    </div>
</div>
