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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$image_counter = 0;

HTMLHelper::_('bootstrap.popover', '[data-bs-toggle="popover"]', ['placement' => 'top']);

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

$style = '#additionalImages .field-media-wrapper{display:flex;align-items: center;}#additionalImages .field-media-wrapper .field-media-preview{width: 64px;padding: 0;border: none;background: transparent;flex-shrink: 1;margin-right:1rem;height: auto;}#additionalImages .field-media-wrapper .field-media-preview img{height: 64px;max-width: 100%;}#additionalImages .field-media-wrapper .input-group{width_100%;}#additionalImages .field-media-wrapper .input-group .form-control{border-radius:.25rem;border-top-right-radius: 0;border-bottom-right-radius: 0;}#additionalImages .tr-additional-image joomla-field-media .field-media-preview-icon{width:58px;height:58px;background-size:58px;}#additionalImages .tr-additional-image.tr-new-additional-image joomla-field-media .input-group{display:flex;flex-wrap: nowrap;}#additionalImages .tr-additional-image joomla-field-media .button-clear{border-top-right-radius:var(--btn-border-radius);}.popover-body img{max-width:150px;height:auto;}#additionalImages .tr-additional-image.tr-new-additional-image joomla-field-media .button-select{border-top-right-radius:var(--btn-border-radius);border-bottom-right-radius:var(--btn-border-radius);}';
$wa->addInlineStyle($style, [], []);
?>
<div class="j2store-product-images">
    <div class="row">
        <div class="col-lg-6 mb-4">
            <fieldset class="options-form">
                <legend><?php echo Text::_('J2STORE_PRODUCT_THUMB_IMAGE');?></legend>
                <div class="control-group">
                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_THUMB_IMAGE'), 'thumb_image'); ?></div>
                    <div class="controls">
                        <?php echo J2Html::media($this->form_prefix . '[thumb_image]', $this->item->thumb_image, array('id' => 'thumb_image', 'image_id' => 'input-thumb-image', 'no_hide' => '')); ?>
                    </div>
                </div>
                <div class="control-group align-items-center">
                    <div class="control-label"><?php echo J2Html::label(Text::_('JFIELD_MEDIA_ALT_LABEL'), 'thumb_image_alt'); ?></div>
                    <div class="controls">
                        <?php echo J2Html::text($this->form_prefix . '[thumb_image_alt]', $this->item->thumb_image_alt, array('id' => 'thumb_image_alt', 'class'=>'form-control')); ?>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="col-lg-6 mb-4">
            <fieldset class="options-form">
                <legend><?php echo Text::_('J2STORE_PRODUCT_MAIN_IMAGE');?></legend>
                <div class="control-group">
                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_MAIN_IMAGE'), 'main_image'); ?></div>
                    <div class="controls">
                        <?php echo J2Html::media($this->form_prefix . '[main_image]', $this->item->main_image, array('id' => 'main_image', 'image_id' => 'input-main-image', 'no_hide' => '')); ?>
                        <?php echo J2Html::hidden($this->form_prefix . '[j2store_productimage_id]', $this->item->j2store_productimage_id); ?>
                    </div>
                </div>
                <div class="control-group align-items-center">
                    <div class="control-label"><?php echo J2Html::label(Text::_('JFIELD_MEDIA_ALT_LABEL'), 'main_image_alt'); ?></div>
                    <div class="controls">
                        <?php echo J2Html::text($this->form_prefix . '[main_image_alt]', $this->item->main_image_alt, array('id' => 'main_image_alt', 'class'=>'form-control')); ?>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="col-12">
            <fieldset class="options-form">
                <legend><?php echo Text::_('J2STORE_PRODUCT_ADDITIONAL_IMAGES');?></legend>
                <div class="table-responsive">
                    <table class="table align-middle" id="additionalImages">
                        <caption class="visually-hidden">
                            <?php echo Text::_('J2STORE_PRODUCT_ADDITIONAL_IMAGES'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">
                                <?php echo J2Html::label(Text::_('J2STORE_PRODUCT_ADDITIONAL_IMAGE'), 'additioanl_image_label'); ?>
                            </th>
                            <th scope="col">
                                <?php echo J2Html::label(Text::_('JFIELD_MEDIA_ALT_LABEL'), 'additioanl_image_label'); ?>
                            </th>
                            <th scope="col" class="text-end">
                                <button type="button" id="addImagBtn" class="btn btn-success btn-sm"><span class="fas fa-solid fa-plus me-2"></span><?php echo Text::_('J2STORE_PRODUCT_ADDITIONAL_IMAGES_ADD') ?></button>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (isset($this->item->additional_images) && !empty($this->item->additional_images)):?>
                            <?php
                            $add_image = json_decode($this->item->additional_images);
                            $add_image_alt = json_decode($this->item->additional_images_alt,true);
                            ?>
                        <?php endif;
                        if (isset($add_image) && !empty($add_image)):
                            foreach ($add_image as $key => $img):
                                ?>
                                <tr class="tr-additional-image" id="additional-image-<?php echo $key; ?>">
                                    <td>
                                        <?php if($img){?>
                                            <span class="fas fa-solid fa-image cursor-pointer"
                                                  data-bs-toggle="popover"
                                                  data-bs-html="true"
                                                  data-bs-trigger="click focus"
                                                  data-bs-customClass="additional-popup-image"
                                                  data-bs-title=""
                                                  data-bs-content="<img src='<?php echo Uri::root().htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>'>">

                                                </span>
                                        <?php } else { ?>
                                            <span class="fas fa-solid fa-image opacity-50"></span>
                                        <?php } ?>

                                    </td>
                                    <td colspan="1">
                                        <?php echo J2Html::media($this->form_prefix . '[additional_images][' . $key . ']', $img, array('id' => 'additional_image_' . $key, 'class' => 'image-input', 'image_id' => 'input-additional-image-' . $key, 'no_hide' => '')); ?>
                                    </td>
                                    <td>
                                        <?php echo J2Html::text($this->form_prefix . '[additional_images_alt][' . $key . ']', isset($add_image_alt[$key])?$add_image_alt[$key]:'', array('id' => 'additional_image_alt_' . $key, 'class'=>'form-control w-100')); ?>
                                    </td>
                                    <td class="text-end">
                                        <input type="button" onclick="deleteImageRow(this)" class="btn btn-danger btn-sm" value="<?php echo Text::_('J2STORE_DELETE') ?>"/>
                                    </td>
                                </tr>
                                <?php if ($key >= $image_counter)
                            {
                                $image_counter = $key;
                            }
                                $image_counter++;
                                ?>
                            <?php endforeach;?>
                        <?php else: ?>
                            <tr class="tr-additional-image" id="additional-image-0">
                                <td>

                                </td>
                                <td colspan="1">
                                    <?php echo J2Html::media($this->form_prefix . '[additional_images][0]', '', array('id' => 'additional_image_0', 'class' => 'image-input', 'image_id' => 'input-additional-image-0', 'no_hide' => '')); ?>
                                </td>
                                <td>
                                    <?php echo J2Html::text($this->form_prefix . '[additional_images_alt][0]', '', array('id' => 'additional_image_alt_0', 'class'=>'form-control w-100')); ?>
                                </td>
                                <td><input type="button" onclick="deleteImageRow(this)" class="btn btn-success" value="<?php echo Text::_('J2STORE_DELETE')?>"/></td>
                            </tr>
                        <?php endif; ?>
                        <input type="hidden" id="additional_image_counter" name="additional_image_counter" value="<?php echo $image_counter; ?>"/>
                    </table>
                    <template id="additional-image-template">
                        <tr class="tr-additional-image">
                            <td></td>
                            <td colspan="1">
                                <?php echo J2Html::media('additional_image_tmpl', '', array('id' => 'additional_image_', 'class' => 'image-input', 'image_id' => 'input-additional-image-', 'no_hide' => '')); ?>
                            </td>
                            <td>
                                <?php echo J2Html::text('additional_images_alt_tmpl', '', array('id' => 'additional_image_alt_', 'class' => 'image-alt-text')); ?>
                            </td>
                            <td class="text-end"><input type="button" onclick="deleteImageRow(this)" class="btn btn-danger btn-sm" value="<?php echo Text::_('J2STORE_DELETE') ?>"/></td>
                        </tr>
                    </template>
                </div>
            </fieldset>
        </div>
    </div>

    <div class="alert alert-info mt-3">
        <h4 class="alert-heading"><?php echo Text::_('J2STORE_QUICK_HELP'); ?></h4>
        <p><b><?php echo Text::_('J2STORE_FEATURE_AVAILABLE_IN_J2STORE_PRODUCT_LAYOUTS_AND_ARTICLES'); ?></b></p>
        <p><?php echo Text::_('J2STORE_PRODUCT_IMAGES_HELP_TEXT'); ?></p>
    </div>
</div>
<script type="text/javascript">

    function deleteImageRow(element) {
        (function ($) {
            var tr = $(element).closest('.tr-additional-image');
            // If this is the last visible row, add an empty replacement before removing
            if ($('.tr-additional-image').length <= 1) {
                var newCounter = parseInt(jQuery('#additional_image_counter').val(), 10) + 1;
                addAdditionalImage(newCounter);
                jQuery('#additional_image_counter').val(newCounter);
            }
            tr.remove();
        })(j2store.jQuery);
    }

    var counter = <?php echo $image_counter;?>;

    jQuery('#addImagBtn').click(function () {
        counter = parseInt(jQuery('#additional_image_counter').val(), 10);
        counter++;
        addAdditionalImage(counter);
        jQuery('#additional_image_counter').val(counter);
    });

    /**
     * Add a new additional-image row by cloning the inert <template> element.
     *
     * Using <template> (instead of a hidden <tr>) keeps the joomla-field-media
     * web component inert until the row is actually inserted into the document.
     * This prevents connectedCallback from firing on the template itself, which
     * previously caused updatePreview() to remove the .button-clear child —
     * making every subsequent clone throw "Misconfiguaration".
     */
    function addAdditionalImage(counter) {
        var template = document.getElementById('additional-image-template');
        var fragment = document.importNode(template.content, true);
        var tr = fragment.querySelector('tr');

        tr.id = 'additional-image-' + counter;
        tr.classList.add('tr-new-additional-image');

        // Update name/id on every text input before the element enters the document
        // (joomla-field-media's connectedCallback fires on insertion, not before)
        fragment.querySelectorAll('input[type="text"]').forEach(function (input) {
            var isAlt = input.classList.contains('image-alt-text');
            var fieldName = isAlt ? 'additional_images_alt' : 'additional_images';
            input.setAttribute('name', '<?php echo $this->form_prefix ?>[' + fieldName + '][' + counter + ']');
            input.setAttribute('id', 'jform_image_additional_image_' + counter);
            input.value = '';
            input.classList.add('form-control', 'w-100');
            input.setAttribute('image_id', 'input-additional-image-' + counter);
        });

        // Append into the table's tbody (browser auto-creates tbody if absent)
        var table = document.getElementById('additionalImages');
        var tbody = table.querySelector('tbody') || table;
        tbody.appendChild(fragment);
    }
</script>
