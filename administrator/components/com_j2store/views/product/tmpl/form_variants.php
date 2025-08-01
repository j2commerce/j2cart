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
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.tooltip', '[data-bs-toggle="tooltip"]', ['placement' => 'top']);

$global_config = Factory::getApplication()->getConfig();
//get the config
$limit = $global_config->get('list_limit',20);

$wa  = Factory::getApplication()->getDocument()->getWebAssetManager();
$style = '.com_j2store .fa-stack.small {width: 1.25rem;height: 1.25rem;line-height: 1.25rem;}.com_j2store .fa-stack.small .fa-stack-2x {font-size:1rem;}.com_j2store .fa-stack.small .fa-stack-1x {font-size:0.5rem;top: 50%;left: 50%;transform: translate(-50%, -50%);}';
$wa->addInlineStyle($style, [], []);
?>
<div class="j2store-product-variants">
    <fieldset class="options-form">
        <legend><?php echo Text::_('J2STORE_PRODUCT_VARIANTS');?></legend>
        <?php if(isset($this->item->product_options) && !empty($this->item->product_options)):?>
            <?php if(isset($this->item->variants) && count($this->item->variants)):?>
                <div class="d-flex justify-content-start align-items-center mb-3">
                    <div class="form-check pt-0 me-2">
                        <input class="form-check-input" type="checkbox" value="" id="toggleAllCheckboxes">
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm me-2" id="deleteCheckedVariants" data-bs-toggle="tooltip" title="<?php echo Text::_('J2STORE_PRODUCT_VARIANTS_DELETE_CHECKED');?>" disabled>
                        <span class="fa-stack small">
                          <span class="fas fa-solid fa-trash fa-stack-2x"></span>
                          <span class="fas fa-solid fa-check fa-stack-1x text-danger small"></span>
                        </span>
                    </button>
                    <button type="button" id="j2store-regenerate-variants" class="btn btn-sm btn-outline-primary me-2 launchConfirm"
                            data-confirm_type="regenerateVariants" data-product_id="<?php echo $this->item->j2store_product_id; ?>" data-disable_msg="<?php echo Text::_('J2STORE_REGENERATING_VARIANTS');?>"
                            data-message="<?php echo Text::_('J2STORE_PRODUCT_VARIANT_REGENERATE_HELP');?>" data-yes_text="<?php echo Text::_('JYES');?>" data-no_text="<?php echo Text::_('JNO');?>" data-title="<?php echo Text::_('J2STORE_WARNING');?>">
                        <span class="fa-stack small">
                          <span class="fas fa-solid fa-recycle fa-stack-2x"></span>
                        </span>
                        <?php echo Text::_('J2STORE_REGENERATE_VARIANTS'); ?>
                    </button>
                    <button type="button" id="j2store-delete-variants" class="btn btn-sm btn-danger launchConfirm"
                            data-confirm_type="deleteAllVariants" data-product_id="<?php echo $this->item->j2store_product_id; ?>" data-disable_msg="<?php echo Text::_('J2STORE_DELETE_VARIANTS_CONTINUE');?>"
                            data-message="<?php echo Text::_('J2STORE_PRODUCT_VARIANT_REGENERATE_HELP');?>" data-yes_text="<?php echo Text::_('JYES');?>" data-no_text="<?php echo Text::_('JNO');?>" data-title="<?php echo Text::_('J2STORE_WARNING');?>">
                        <span class="fas fa-solid fa-trash me-2"></span><?php echo Text::_('J2STORE_DELETE_ALL_VARIANTS'); ?>
                    </button>
                    <button type="button" id="openAll-panel" class="btn btn-outline-primary btn-sm ms-auto" onclick="setExpandAll();" data-bs-toggle="tooltip" title="<?php echo Text::_('J2STORE_OPEN_ALL');?>">
                        <span class="fas fa-solid fa-chevron-down"></span>
                    </button>
                    <button type="button" id="closeAll-panel" class="btn btn-outline-primary btn-sm ms-2" onclick="setCloseAll();" data-bs-toggle="tooltip" title="<?php echo Text::_('J2STORE_CLOSE_ALL');?>">
                        <span class="fas fa-solid fa-chevron-up"></span>
                    </button>
                </div>
                <div id="regenerateConfirm" class="modal fade j2storeConfirmChange" style="display: none;">
                    <div class="modal-header">
                        <button class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                        <h4 id="myModalLabel" class="modal-title"><?php echo Text::_('J2STORE_WARNING');?></h4>
                    </div>
                    <div class="modal-body">
                        <p><?php echo Text::_('J2STORE_PRODUCT_VARIANT_REGENERATE_HELP');?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" class="btn btn-warning" data-value="1"><?php echo Text::_('JYES');?></button>
                        <button type="button" data-dismiss="modal" class="btn btn-default" data-value="0"><?php echo Text::_('JNO');?></button>
                    </div>
                </div>
            <?php else:?>
                <input type="button" id="j2store-generate-variants" class="btn btn-primary launchConfirm" value="<?php echo Text::_('J2STORE_GENERATE_VARIANTS');	?>"
                       data-confirm_type="generateVariants" data-product_id="<?php echo $this->item->j2store_product_id; ?>" data-disable_msg="<?php echo Text::_('J2STORE_GENERATING_VARIANTS');?>"
                       data-message="<?php echo Text::_('J2STORE_PRODUCT_VARIANT_REGENERATE_HELP');?>" data-yes_text="<?php echo Text::_('JYES');?>" data-no_text="<?php echo Text::_('JNO');?>" data-title="<?php echo Text::_('J2STORE_WARNING');?>"
                />
            <?php endif;?>
        <?php endif;?>
        <div class="j2store-advancedvariants-settings">
            <div class="accordion" id="accordion">
                <?php
                /* to get ajax advanced variable list need to
                 *  assign these variables
                 */
                $this->variant_list = $this->item->variants;
                $this->variant_pagination =$this->item->variant_pagination;
                $this->weights = $this->item->weights;
                $this->lengths = $this->item->lengths;

                ?>
                <?php  echo $this->loadTemplate('ajax_avoptions');?>
            </div>
        </div>
    </fieldset>
</div>
<script type="text/javascript">
    var currentPage = <?php echo $this->item->variant_pagination->pagesCurrent;?>;
    var j2_total_variants =<?php echo $this->item->variant_pagination->total;?>;
    var j2_limit  = <?php echo $limit;?>;
    var product_id = <?php echo $this->item->j2store_product_id;?>;

    document.addEventListener("DOMContentLoaded", function () {
        // Create the footer navigation dynamically
        var accordion = document.getElementById("accordion");

        if (accordion) {
            // Insert pagination wrapper after the accordion
            var paginationWrapper = document.createElement("nav");
            paginationWrapper.className = "pagination__wrapper";
            paginationWrapper.setAttribute("aria-label", "<?php echo Text::_('JLIB_HTML_PAGINATION'); ?>");
            paginationWrapper.innerHTML = '<div class="text-end"><?php echo $this->item->variant_pagination->total; ?> <?php echo Text::_('J2STORE_PRODUCT_TAB_VARIANTS'); ?></div><div id="nav" class="text-center mt-0 mx-0"><ul class="pagination pagination-toolbar pagination-list text-center mt-0 mx-0"></ul>';
            accordion.parentNode.insertBefore(paginationWrapper, accordion.nextSibling);
            var numPages = Math.ceil(j2_total_variants / j2_limit);
            if (numPages > 1) {
                createFooterList(numPages);
                var paginationLinks = document.querySelectorAll('#nav .pagination-list a');
                paginationLinks.forEach(function (link) {
                    link.addEventListener('click', function () {
                        // Remove 'active' class from all pagination items
                        var paginationItems = document.querySelectorAll('#nav .pagination-list li');
                        paginationItems.forEach(function (item) {
                            item.classList.remove('active');
                        });
                        // Add 'active' class to the clicked item's parent <li>
                        this.parentNode.classList.add('active');
                    });
                });
            }
        }
    });

    /***
     *  This method will append pagination li to parent Ul
     */
    function createFooterList(numPages){
        var limitstart = 0;
        var paginationList = document.querySelector('#nav .pagination-list');
        if (!paginationList) {
            console.error("Pagination list element not found!");
            return;
        }
        for (var i = 0; i < numPages; i++) {
            var pageNum = i + 1;
            limitstart = i * j2_limit;
            var listItem = document.createElement('li');
            listItem.className = 'page-item';
            var link = document.createElement('a');
            link.className = 'page-link';
            link.href = 'javascript:void(0);';
            link.setAttribute('data-get_limitstart', limitstart);
            link.setAttribute('data-get_page', i);
            link.setAttribute('rel', i);
            link.onclick = function () {
                getVariantList(this);
            };
            // Set the link text
            link.textContent = pageNum;
            // Append the link to the list item
            listItem.appendChild(link);
            // Append the list item to the pagination list
            paginationList.appendChild(listItem);
        }

        // Add the 'active' class to the first list item
        var firstListItem = paginationList.querySelector('li');
        if (firstListItem) {
            firstListItem.classList.add('active');
        }
    }

    /**
     * Method to run ajax request to get the list of variants based on the page requested
     */
    function getVariantList(element){
        var getPage = element.getAttribute('data-get_page');
        var limitstart = element.getAttribute('data-get_limitstart');
        var data = {
            option: 'com_j2store',
            view: 'products',
            task: 'getVariantListAjax',
            limitstart: limitstart,
            product_id: product_id,
            limit: j2_limit,
            form_prefix: '<?php echo $this->form_prefix; ?>'
        };
        var serializedData = Object.keys(data)
            .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(data[key]))
            .join('&');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('Cache-Control', 'no-cache');

        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var json = JSON.parse(xhr.responseText);
                    if (json['html']) {
                        document.getElementById('accordion').innerHTML = json['html'];
                    }
                } catch (error) {
                    console.error('Error parsing JSON:', error);
                }
            } else {
                console.error('Request failed with status:', xhr.status);
            }
        };

        xhr.onerror = function () {
            console.error('Request failed due to a network error.');
        };
        xhr.send(serializedData);
    }

    /**
     * Method to Expand All Accordion Panel
     */
    function setExpandAll() {
        const accordionPanels = document.querySelectorAll('.accordion .collapse');
        accordionPanels.forEach(panel => {
            if (!panel.classList.contains('show')) {
                const collapseInstance = new bootstrap.Collapse(panel, { toggle: true });
                collapseInstance.show();
            }
        });
    }

    /**
     * Method to Close All Accordion Panel
     */
    function setCloseAll() {
        const accordionPanels = document.querySelectorAll('.accordion .collapse.show');
        accordionPanels.forEach(panel => {
            const collapseInstance = new bootstrap.Collapse(panel, { toggle: true });
            collapseInstance.hide();
        });
    }

    function getOptionValue(element,variant_id){
        (function($) {
            $(element).closest('.opvalues').remove();
            var productoption_id = $(element).attr('value');
            var ajOptions = {
                type : 'post',
                url :  'index.php',
                cache: false,
                dataType : 'json',
                data:{'option':'com_j2store','view' :'products','task' :'getProductOptionValue','productoption_id' : productoption_id}
            };
            $.ajax(ajOptions)
                .done( function(json) {
                    var html ='';
                    html += '<option value=""><?php echo Text::_('J2STORE_SELECT_OPTION'); ?></option>';
                    for(var i=0;  i < json['value'].length; i++ ){
                        html +='<option value="'+json['value'][i]['id'] +'">'+ json['value'][i]['name'] +'</option>';
                    }
                    $(element).closest('.variant-option-values').find('.opvalues').html(html);
                })
        })(j2store.jQuery);
    }

    var counter ;
    function addVariant(){
        (function($) {
            var ajOptions = {
                type : 'post',
                url :  'index.php',
                cache: false,
                dataType : 'json',
                data:{
                    'option':'com_j2store',
                    'view' :'products',
                    'task' :'createVariant',
                    'product_id' : <?php echo $this->item->j2store_product_id; ?>}
            };

            $.ajax(ajOptions)
                .done( function(json) {
                    if(json['html']){
                        var last_element_counter = $('.j2store-panel-default').data('variant_id');
                        counter = last_element_counter ++ ;
                        //console.log(json['html']);
                        var html = $(json['html']).find('.j2store-panel-default').last();
                        //	$(html).find('a').attr("href",'collapse-'+counter);
                        $('#accordion').append(json['html']);
                    }

                });

            //var clone = image_div.clone();
        })(j2store.jQuery);
    }

    document.addEventListener("DOMContentLoaded", function () {
        let toggleCheckbox = document.getElementById("toggleAllCheckboxes");

        if (toggleCheckbox) {
            toggleCheckbox.addEventListener("change", function () {
                let checkboxes = document.querySelectorAll('input[type="checkbox"][id^="cid"]');

                checkboxes.forEach(checkbox => {
                    checkbox.checked = toggleCheckbox.checked;
                });
            });
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        const deleteButton = document.getElementById("deleteCheckedVariants");
        const checkboxes = document.querySelectorAll('input[type="checkbox"][name="vid[]"]');
        const toggleAllCheckbox = document.getElementById("toggleAllCheckboxes");

        function toggleButtonState() {
            const isAnyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
            deleteButton.disabled = !isAnyChecked;
        }
        function toggleAll() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = toggleAllCheckbox.checked;
            });
            toggleButtonState();
        }
        function updateToggleAllCheckbox() {
            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
            const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
            toggleAllCheckbox.checked = allChecked;
            toggleButtonState();
        }
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener("change", updateToggleAllCheckbox);
        });
        toggleAllCheckbox.addEventListener("change", toggleAll);
        updateToggleAllCheckbox();
    });

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelector('#deleteCheckedVariants').addEventListener('click', function (e) {
            const checkedVariants = document.querySelectorAll('input[name="vid[]"]:checked');
            checkedVariants.forEach(function (element) {
                const variant_id = element.value;
                fetch(`index.php?option=com_j2store&view=products&task=deletevariant&variant_id=${variant_id}`, {
                    method: 'GET',
                })
                    .then(response => response.json())
                    .then(json => {
                        if (json) {
                            location.reload(true); // Reload the page if the server responds successfully
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    });
</script>
