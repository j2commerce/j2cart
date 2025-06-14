ALTER TABLE `#__j2store_orderitems`
    MODIFY COLUMN `orderitem_per_item_tax` DECIMAL(15,5),
    MODIFY COLUMN `orderitem_tax` DECIMAL(15,5),
    MODIFY COLUMN `orderitem_discount` DECIMAL(15,5),
    MODIFY COLUMN `orderitem_discount_tax` DECIMAL(15,5),
    MODIFY COLUMN `orderitem_price` DECIMAL(15,5),
    MODIFY COLUMN `orderitem_option_price` DECIMAL(15,5),
    MODIFY COLUMN `orderitem_finalprice` DECIMAL(15,5),
    MODIFY COLUMN `orderitem_finalprice_with_tax` DECIMAL(15,5),
    MODIFY COLUMN `orderitem_finalprice_without_tax` DECIMAL(15,5) /** CAN FAIL **/;

ALTER TABLE `#__j2store_ordertaxes`
    MODIFY COLUMN `ordertax_percent` DECIMAL(15,5),
    MODIFY COLUMN `ordertax_amount` DECIMAL(15,5) /** CAN FAIL **/;

ALTER TABLE `#__j2store_orderdiscounts`
    MODIFY COLUMN `discount_amount` DECIMAL(15,5),
    MODIFY COLUMN `discount_tax` DECIMAL(15,5) /** CAN FAIL **/;

ALTER TABLE `#__j2store_orderfees`
    MODIFY COLUMN `amount` DECIMAL(15,5),
    MODIFY COLUMN `tax` DECIMAL(15,5) /** CAN FAIL **/;

ALTER TABLE `#__j2store_orderitemattributes`
    MODIFY COLUMN `orderitemattribute_price` DECIMAL(15,5) /** CAN FAIL **/;

ALTER TABLE `#__j2store_orders`
    MODIFY COLUMN `order_total` DECIMAL(15,5),
    MODIFY COLUMN `order_subtotal` DECIMAL(15,5),
    MODIFY COLUMN `order_subtotal_ex_tax` DECIMAL(15,5),
    MODIFY COLUMN `order_tax` DECIMAL(15,5),
    MODIFY COLUMN `order_shipping` DECIMAL(15,5),
    MODIFY COLUMN `order_shipping_tax` DECIMAL(15,5),
    MODIFY COLUMN `order_discount` DECIMAL(15,5),
    MODIFY COLUMN `order_discount_tax` DECIMAL(15,5),
    MODIFY COLUMN `order_credit` DECIMAL(15,5),
    MODIFY COLUMN `order_refund` DECIMAL(15,5),
    MODIFY COLUMN `order_surcharge` DECIMAL(15,5),
    MODIFY COLUMN `order_fees` DECIMAL(15,5) /** CAN FAIL **/;

ALTER TABLE `#__j2store_productprice_index`
    MODIFY COLUMN `min_price` DECIMAL(15,5),
    MODIFY COLUMN `max_price` DECIMAL(15,5) /** CAN FAIL **/;

ALTER TABLE `#__j2store_shippingrates`
    MODIFY COLUMN `shipping_rate_price` DECIMAL(15,5),
    MODIFY COLUMN `shipping_rate_handling` DECIMAL(15,5) /** CAN FAIL **/;

ALTER TABLE `#__j2store_customfields`
    MODIFY COLUMN `enabled` INT NULL DEFAULT 1,
    MODIFY COLUMN `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_filtergroups`
    MODIFY COLUMN `enabled` INT NULL DEFAULT 1,
    MODIFY COLUMN `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_lengths`
    MODIFY COLUMN `enabled` INT NULL DEFAULT 1,
    MODIFY COLUMN `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_options`
    MODIFY COLUMN `enabled` INT NULL DEFAULT 1,
    MODIFY COLUMN `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_product_options`
    MODIFY COLUMN `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_weights`
    MODIFY COLUMN `enabled` INT NULL DEFAULT 1,
    MODIFY COLUMN `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_geozones`
    MODIFY COLUMN `enabled` INT NULL DEFAULT 1 /** CAN FAIL **/;

ALTER TABLE `#__j2store_products`
    MODIFY COLUMN `enabled` INT NULL DEFAULT 1 /** CAN FAIL **/;
