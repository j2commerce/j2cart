ALTER TABLE `#__j2store_orderitems`
    CHANGE `orderitem_per_item_tax` `orderitem_per_item_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `orderitem_tax` `orderitem_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `orderitem_discount` `orderitem_discount` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `orderitem_discount_tax` `orderitem_discount_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `orderitem_price` `orderitem_price` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `orderitem_option_price` `orderitem_option_price` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `orderitem_finalprice` `orderitem_finalprice` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `orderitem_finalprice_with_tax` `orderitem_finalprice_with_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `orderitem_finalprice_without_tax` `orderitem_finalprice_without_tax` DECIMAL(15,5) NULL DEFAULT NULL /** CAN FAIL **/;

ALTER TABLE `#__j2store_ordertaxes`
    CHANGE `ordertax_percent` `ordertax_percent` DECIMAL(15,5) NULL DEFAULT 0.0,
    CHANGE `ordertax_amount` `ordertax_amount` DECIMAL(15,5) NULL DEFAULT 0.0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_orderdiscounts`
    CHANGE `discount_amount` `discount_amount` DECIMAL(15,5) NULL DEFAULT 0.0,
    CHANGE `discount_tax` `discount_tax` DECIMAL(15,5) NULL DEFAULT 0.0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_orderfees`
    CHANGE `amount` `amount` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `tax` `tax` DECIMAL(15,5) NULL DEFAULT NULL /** CAN FAIL **/;

ALTER TABLE `#__j2store_orderitemattributes`
    CHANGE `orderitemattribute_price` `orderitemattribute_price` DECIMAL(15,5) NULL DEFAULT 0.0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_orders`
    CHANGE `order_total` `order_total` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `order_subtotal` `order_subtotal` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `order_subtotal_ex_tax` `order_subtotal_ex_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `order_tax` `order_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `order_shipping` `order_shipping` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `order_shipping_tax` `order_shipping_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `order_discount` `order_discount` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `order_discount_tax` `order_discount_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `order_credit` `order_credit` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `order_refund` `order_refund` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `order_surcharge` `order_surcharge` DECIMAL(15,5) NULL DEFAULT NULL,
    CHANGE `order_fees` `order_fees` DECIMAL(15,5) NULL DEFAULT NULL /** CAN FAIL **/;

ALTER TABLE `#__j2store_productprice_index`
    CHANGE `min_price` `min_price` DECIMAL(15,5) NOT NULL,
    CHANGE `max_price` `max_price` DECIMAL(15,5) NOT NULL /** CAN FAIL **/;

ALTER TABLE `#__j2store_shippingrates`
    CHANGE `shipping_rate_price` `shipping_rate_price` DECIMAL(15,5) NULL DEFAULT 0.0,
    CHANGE `shipping_rate_handling` `shipping_rate_handling` DECIMAL(15,5) NULL DEFAULT 0.0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_customfields`
    CHANGE `enabled` `enabled` INT NULL DEFAULT 1,
    CHANGE `ordering` `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_filtergroups`
    CHANGE `enabled` `enabled` INT NULL DEFAULT 1,
    CHANGE `ordering` `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_lengths`
    CHANGE `enabled` `enabled` INT NULL DEFAULT 1,
    CHANGE `ordering` `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_options`
    CHANGE `enabled` `enabled` INT NULL DEFAULT 1,
    CHANGE `ordering` `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_product_options`
    CHANGE `ordering` `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_weights`
    CHANGE `enabled` `enabled` INT NULL DEFAULT 1,
    CHANGE `ordering` `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_geozones`
    CHANGE `enabled` `enabled` INT NULL DEFAULT 1 /** CAN FAIL **/;

ALTER TABLE `#__j2store_products`
    CHANGE `enabled` `enabled` INT NULL DEFAULT 1 /** CAN FAIL **/;
