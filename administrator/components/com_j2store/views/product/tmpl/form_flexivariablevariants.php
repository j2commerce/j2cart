<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


$global_config = Factory::getApplication()->getConfig();
//get the config
$limit = $global_config->get('list_limit',20);


?>
<fieldset class="options-form">
    <legend><?php echo Text::_('J2STORE_PRODUCT_VARIANTS');?></legend>

    <div id="variant_add_block" class="mb-3">
        <input type="hidden" name="flexi_product_id" value="<?php echo $this->item->j2store_product_id;?>"/>
        <div class="input-group">
			<?php foreach ($this->item->product_options as $product_option): ?>
                <select name="variant_combin[<?php echo $product_option->j2store_productoption_id;?>]" class="form-select me-2">
                    <option value="0"><?php echo substr(Text::_('J2STORE_ANY').' '.$this->escape($product_option->option_name),0,10).'...';?></option>
					<?php foreach ($product_option->option_values as $option_value): ?>
                        <option value="<?php echo $option_value->j2store_optionvalue_id;?>"><?php echo $this->escape($option_value->optionvalue_name);?></option>
					<?php endforeach; ?>
                </select>
			<?php endforeach; ?>
            <a onclick="addFlexiVariant()" class="btn btn-primary"><span class="fas fa-solid fa-plus me-1"></span><?php echo Text::_('J2STORE_ADD_VARIANT');?></a>
            <a onclick="removeFlexiAllVariant()" class="btn btn-outline-danger"><span class="fas fa-solid fa-trash me-1"></span><?php echo Text::_('J2STORE_REMOVE_ALL_VARIANT');?></a>
        </div>
    </div>
    <div id="variant_display_block">
        <!-- Advanced variable starts here  -->
        <div class="j2store-advancedvariants-settings">
            <a class="btn btn-outline-danger btn-sm mb-3" href="javascript:;" id="deleteCheckedVariants">Delete Checked Variants</a>
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
				<?php  echo $this->loadTemplate('ajax_flexivariableoptions');?>
            </div>
        </div>
    </div>
</fieldset>


<script type="text/javascript">
    var currentPage = <?php echo $this->item->variant_pagination->pagesCurrent; ?>;
    var total_flexivariants = <?php echo $this->item->variant_pagination->total; ?>;
    var flexi_limit = <?php echo $limit; ?>;
    var product_id = <?php echo $this->item->j2store_product_id; ?>;

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
            var numPages = Math.ceil(total_flexivariants / flexi_limit);
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

    function createFooterList(numPages) {
        var limitstart = 0;
        var paginationList = document.querySelector('#nav .pagination-list');
        if (!paginationList) {
            console.error("Pagination list element not found!");
            return;
        }
        for (var i = 0; i < numPages; i++) {
            var pageNum = i + 1;
            limitstart = i * flexi_limit;
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
    function getVariantList(element) {
        var getPage = element.getAttribute('data-get_page');
        var limitstart = element.getAttribute('data-get_limitstart');
        var data = {
            option: 'com_j2store',
            view: 'products',
            task: 'getVariantListAjax',
            limitstart: limitstart,
            product_id: product_id,
            limit: limit,
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
    function addFlexiVariant() {
        var data = [];
        var inputs = document.querySelectorAll('#variant_add_block select, #variant_add_block input');
        inputs.forEach(function (input) {
            if (input.name) {
                data.push(encodeURIComponent(input.name) + '=' + encodeURIComponent(input.value));
            }
        });
        var serializedData = data.join('&');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?option=com_j2store&view=apps&task=view&id=<?php echo $this->item->app_detail->extension_id; ?>&appTask=addFlexiVariant&form_prefix=<?php echo $this->form_prefix; ?>', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        // Handle success and failure
        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var json = JSON.parse(xhr.responseText);
                    if (json['html']) {
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Error parsing JSON response:', error);
                }
            } else {
                console.error('Request failed with status:', xhr.status);
            }
        };
        xhr.onerror = function () {
            console.error('Request failed. Please check the network connection.');
        };
        xhr.send(serializedData);
    }

    function removeFlexiAllVariant() {
        var deleteVarData = {
            option: 'com_j2store',
            view: 'apps',
            task: 'view',
            id: '<?php echo $this->item->app_detail->extension_id; ?>',
            appTask: 'deleteAllVariant',
            product_id: '<?php echo $this->item->j2store_product_id; ?>'
        };

        // Build the query string from deleteVarData
        var queryString = Object.keys(deleteVarData)
            .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(deleteVarData[key]))
            .join('&');

        // Create an XMLHttpRequest object
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '<?php echo Route::_('index.php'); ?>?' + queryString, true);

        // Set up the beforeSend equivalent
        xhr.onloadstart = function () {
            // Add any "before send" actions here if needed
        };

        // Handle the AJAX response
        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                var json = JSON.parse(xhr.responseText);
                if (json) {
                    window.location.reload();
                }
            }
        };
        xhr.send();
    }
    document.addEventListener("DOMContentLoaded", function () {
        var deleteButton = document.getElementById("deleteCheckedVariants");
        if (deleteButton) {
            deleteButton.addEventListener("click", function (e) {
                var checkboxes = document.querySelectorAll('input[name="vid[]"]:checked');
                checkboxes.forEach(function (checkbox) {
                    var variant_id = checkbox.value;
                    var xhr = new XMLHttpRequest();
                    xhr.open("GET", 'index.php?option=com_j2store&view=apps&task=view&appTask=deletevariant&id=<?php echo $this->item->app_detail->extension_id;?>&variant_id=' + variant_id, true);
                    xhr.onloadstart = function () {
                    };
                    xhr.onload = function () {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            location.reload(true);
                        }
                    };
                    xhr.send();
                });
            });
        }
    });
</script>
