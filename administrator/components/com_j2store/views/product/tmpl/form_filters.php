<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

$style = '.autocomplete-list{background: var(--form-control-bg);max-height: 200px;overflow-y: auto;width: 100%;}.autocomplete-list.autocomplete-active{border: var(--form-control-border);}.autocomplete-item{padding: 8px;cursor: pointer;font-size: .8rem;}.autocomplete-item:hover {background-color: #f0f0f0;}';
$wa->addInlineStyle($style, [], []);


?>

<fieldset class="options-form">
    <legend><?php echo Text::_('COM_J2STORE_TITLE_FILTERGROUPS'); ?></legend>
    <div class="j2store-product-filters" id="j2store-product-filter-blog">
		<?php  echo $this->loadTemplate('ajax_avfilter');?>
    </div>
</fieldset>


<script type="text/javascript">
    var total_variants = <?php echo $this->item->productfilter_pagination->total; ?>;
    var limit = <?php echo $this->filter_limit; ?>;
    var product_id = <?php echo $this->item->j2store_product_id; ?>;

    document.addEventListener("DOMContentLoaded", function () {
        var filterBlock = document.getElementById("j2store-product-filter-blog");
        if (filterBlock) {
            var paginationWrapper = document.createElement('nav');
            paginationWrapper.className = 'pagination__wrapper';
            paginationWrapper.setAttribute('aria-label', '<?php echo Text::_('JLIB_HTML_PAGINATION'); ?>');
            paginationWrapper.innerHTML = `
            <div class="text-end">
                <span class="me-1"><?php echo $this->item->productfilter_pagination->total; ?></span><?php echo Text::_('J2STORE_PRODUCT_FILTERS'); ?>
            </div>
            <div id="filterNav" class="pagination pagination-toolbar text-center mt-0 mx-0">
                <ul class="pagination pagination-list text-center ms-auto me-0"></ul>
            </div>
        `;
            filterBlock.parentNode.insertBefore(paginationWrapper, filterBlock.nextSibling);
            var numPages = Math.ceil(total_variants / limit);
            if (numPages > 1) {
                createFilterFooterList(numPages);
            }
        }
    });

    function createFilterFooterList(numPages) {
        var paginationList = document.querySelector('#filterNav .pagination-list');
        if (!paginationList) {
            console.error("Pagination list element not found!");
            return;
        }
        var limitstart = 0;
        for (var i = 0; i < numPages; i++) {
            var pageNum = i + 1;
            limitstart = i * limit;
            var listItem = document.createElement('li');
            listItem.className = 'page-item';
            var link = document.createElement('a');
            link.className = 'page-link';
            link.href = 'javascript:void(0);';
            link.setAttribute('data-get_limitstart', limitstart);
            link.setAttribute('data-get_page', i);
            link.setAttribute('rel', i);
            link.textContent = pageNum;
            link.addEventListener('click', function () {
                var paginationItems = document.querySelectorAll('#filterNav .pagination-list li');
                paginationItems.forEach(function (item) {
                    item.classList.remove('active');
                });
                this.parentNode.classList.add('active');
                getProductFilterList(this);
            });
            listItem.appendChild(link);
            paginationList.appendChild(listItem);
        }
        var firstListItem = paginationList.querySelector('li.page-item');
        if (firstListItem) {
            firstListItem.classList.add('active');
        }
    }

    function getProductFilterList(element) {
        var limitstart = element.getAttribute('data-get_limitstart');
        var data = {
            option: 'com_j2store',
            view: 'products',
            task: 'getProductFilterListAjax',
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
                        // Update the product filter blog content
                        document.getElementById('j2store-product-filter-blog').innerHTML = json['html'];
                    }
                } catch (error) {
                    console.error('Error parsing JSON response:', error);
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

    function removeFilter(filter_id, product_id) {
        // Prepare the data for the request
        var rem_filter = {
            option: 'com_j2store',
            view: 'products',
            task: 'deleteproductfilter',
            filter_id: filter_id,
            product_id: product_id
        };

        // Serialize the data into a query string
        var formData = Object.keys(rem_filter)
            .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(rem_filter[key]))
            .join('&');

        // Remove all existing notifications
        var notifications = document.querySelectorAll('.j2notify');
        notifications.forEach(function (notification) {
            notification.remove();
        });

        // Create an XMLHttpRequest
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo Route::_('index.php'); ?>', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        // Handle the response
        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                var data;
                try {
                    data = JSON.parse(xhr.responseText);
                } catch (e) {
                    console.error('Failed to parse JSON response:', xhr.responseText);
                    return;
                }

                if (data.success) {
                    // Remove the filter element
                    var filterElement = document.getElementById('product_filter_current_option_' + filter_id);
                    if (filterElement) {
                        filterElement.remove();
                    }
                }

                // Add the notification message
                var productFiltersTable = document.getElementById('product_filters_table');
                if (productFiltersTable) {
                    var notificationDiv = document.createElement('div');
                    notificationDiv.className = 'j2notify alert alert-block';
                    notificationDiv.textContent = data.msg;
                    productFiltersTable.parentNode.insertBefore(notificationDiv, productFiltersTable);
                }
            } else {
                console.error('Request failed with status:', xhr.status);
            }
        };

        xhr.onerror = function () {
            console.error('Request failed due to a network error.');
        };

        // Send the serialized data
        xhr.send(formData);
    }

    document.addEventListener("DOMContentLoaded", function () {
        var productFilterInput = document.getElementById('J2StoreproductFilter');

        if (productFilterInput) {
            let autocompleteList;

            function createAutocompleteContainer() {
                autocompleteList = document.createElement('div');
                autocompleteList.className = 'autocomplete-list';
                autocompleteList.style.position = 'absolute';
                autocompleteList.style.width = '350px';
                autocompleteList.style.zIndex = '1000';
                productFilterInput.parentNode.appendChild(autocompleteList);
            }

            function updateAutocompleteListState() {
                if (autocompleteList.children.length > 0) {
                    autocompleteList.classList.add('autocomplete-active');
                } else {
                    autocompleteList.classList.remove('autocomplete-active');
                }
            }

            createAutocompleteContainer();
            productFilterInput.addEventListener('input', function () {
                var term = this.value;
                if (term.length < 2) {
                    autocompleteList.innerHTML = '';
                    updateAutocompleteListState();
                    return;
                }

                var searchFilter = {
                    option: 'com_j2store',
                    view: 'products',
                    task: 'searchproductfilters',
                    q: term
                };

                var formData = new URLSearchParams(searchFilter).toString();

                fetch('<?php echo Route::_('index.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        productFilterInput.classList.remove('optionsLoading');
                        autocompleteList.innerHTML = '';

                        data.forEach(item => {
                            var option = document.createElement('div');
                            option.className = 'autocomplete-item';
                            option.textContent = `${item.group_name} > ${item.filter_name}`;
                            option.dataset.value = item.j2store_filter_id;

                            // Handle item selection
                            option.addEventListener('click', function () {
                                var label = this.textContent;
                                var value = this.dataset.value;

                                var newRow = `
                            <tr>
                                <td class="addedFilter">${label}</td>
                                <td class="text-center">
                                    <span class="filterRemove" onclick="this.closest('tr').remove();">
                                        <span class="icon icon-trash text-danger"></span>
                                    </span>
                                    <input type="hidden" value="${value}" name="<?php echo $this->form_prefix.'[productfilter_ids]' ;?>[]">
                                </td>
                            </tr>
                        `;
                                document.querySelector('.j2store_a_filter').insertAdjacentHTML('beforebegin', newRow);
                                productFilterInput.value = '';
                                autocompleteList.innerHTML = '';
                                updateAutocompleteListState();
                            });
                            autocompleteList.appendChild(option);
                        });

                        updateAutocompleteListState();
                    })
                    .catch(error => {
                        console.error('Error fetching autocomplete data:', error);
                    });
                productFilterInput.classList.add('optionsLoading');
            });

            document.addEventListener('click', function (e) {
                if (!productFilterInput.contains(e.target) && !autocompleteList.contains(e.target)) {
                    autocompleteList.innerHTML = '';
                    updateAutocompleteListState();
                }
            });
        }
    });
</script>
