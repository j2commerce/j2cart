function doFlexiAjaxPrice(product_id, id) {
    // Get the form element closest to the specified ID
    var form = document.querySelector(id).closest('form');
    if (!form || form.dataset.product_id != product_id) return;

    // Serialize the form data into an array
    var formData = new FormData(form);
    var values = Array.from(formData.entries()).map(([name, value]) => ({ name, value }));

    // Remove task and view params from the values
    values = values.filter(item => item.name !== 'task' && item.name !== 'view');

    // Add custom params
    values.push({ name: "product_id", value: product_id });

    // Trigger a custom event before making the AJAX request
    document.body.dispatchEvent(new CustomEvent('before_doAjaxPrice', { detail: { form, values } }));

    // Remove any existing error messages
    document.querySelectorAll('.j2error').forEach(el => el.remove());

    // Convert the values array into a query string
    var queryString = values.map(item => encodeURIComponent(item.name) + '=' + encodeURIComponent(item.value)).join('&');

    // Make the AJAX request
    fetch(j2storeURL + 'index.php?option=com_j2store&view=product&task=update&' + queryString, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
        .then(response => response.json())
        .then(data => {
            var product = document.querySelector('.product-' + product_id);

            if (product && typeof data.error === 'undefined') {
                // Update SKU
                if (data.sku) {
                    var skuElement = product.querySelector('.sku');
                    if (skuElement) skuElement.innerHTML = data.sku;
                }

                // Update pricing
                if (data.pricing) {
                    if (data.pricing.base_price) {
                        var basePrice = product.querySelector('.base-price');
                        if (basePrice) {
                            basePrice.innerHTML = data.pricing.base_price;
                            if (data.pricing.class === 'show') {
                                basePrice.style.display = 'block';
                                basePrice.classList.add('strike');
                            } else {
                                basePrice.style.display = 'none';
                            }
                        }
                    }

                    if (data.pricing.price) {
                        var salePrice = product.querySelector('.sale-price');
                        if (salePrice) salePrice.innerHTML = data.pricing.price;
                    }

                    var discountText = product.querySelector('.discount-percentage');
                    if (discountText) discountText.innerHTML = data.pricing.discount_text;
                }

                // Update additional details
                if (data.afterDisplayPrice) {
                    var afterDisplayPrice = product.querySelector('.afterDisplayPrice');
                    if (afterDisplayPrice) afterDisplayPrice.innerHTML = data.afterDisplayPrice;
                }

                if (data.quantity) {
                    var qtyInput = product.querySelector('input[name="product_qty"]');
                    if (qtyInput) {
                        qtyInput.value = data.quantity;
                        if (['variable', 'advancedvariable', 'variablesubscriptionproduct'].includes(form.dataset.product_type)) {
                            qtyInput.setAttribute('value', data.quantity);
                        }
                    }
                }

                if (data.main_image) {
                    if (data.thumb_image) {
                        var thumbImage = document.querySelector('.j2store-product-thumb-image-' + product_id);
                        if (thumbImage) thumbImage.setAttribute('src', data.thumb_image);
                    }

                    var mainImage = document.querySelector('.j2store-product-main-image-' + product_id);
                    if (mainImage) mainImage.setAttribute('src', data.main_image);

                    var additionalImages = product.querySelectorAll('.j2store-product-additional-images .additional-mainimage');
                    additionalImages.forEach(img => img.setAttribute('src', data.main_image));
                }

                // Update stock status
                if (typeof data.stock_status !== 'undefined') {
                    var stockContainer = product.querySelector('.product-stock-container');
                    if (stockContainer) {
                        if (data.availability == 1) {
                            stockContainer.innerHTML = '<span class="instock">' + data.stock_status + '</span>';
                        } else {
                            stockContainer.innerHTML = '<span class="outofstock">' + data.stock_status + '</span>';
                        }
                    }
                }

                // Update dimensions
                if (data.dimensions) {
                    var dimensions = product.querySelector('.product-dimensions');
                    if (dimensions) dimensions.innerHTML = data.dimensions;
                }

                // Update weight
                if (data.weight) {
                    var weight = product.querySelector('.product-weight');
                    if (weight) weight.innerHTML = data.weight;
                }

                // Trigger custom events after the AJAX call
                document.body.dispatchEvent(new CustomEvent('after_doAjaxFilter', { detail: { product, data } }));
                document.body.dispatchEvent(new CustomEvent('after_doAjaxPrice', { detail: { product, data } }));
            } else {
                var variableOptions = product.querySelector('#variable-options-' + product_id);
                if (variableOptions) {
                    variableOptions.insertAdjacentHTML('afterend', '<div class="j2error">' + data.error + '</div>');
                }
            }
        })
        .catch(error => {
            console.error(error);
        });
}
