ALTER TABLE `#__j2store_orderitems`
    MODIFY `orderitem_per_item_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `orderitem_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `orderitem_discount` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `orderitem_discount_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `orderitem_price` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `orderitem_option_price` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `orderitem_finalprice` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `orderitem_finalprice_with_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `orderitem_finalprice_without_tax` DECIMAL(15,5) NULL DEFAULT NULL /** CAN FAIL **/;

ALTER TABLE `#__j2store_ordertaxes`
    MODIFY `ordertax_percent` DECIMAL(15,5) NULL DEFAULT 0.0,
    MODIFY `ordertax_amount` DECIMAL(15,5) NULL DEFAULT 0.0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_orderdiscounts`
    MODIFY `discount_amount` DECIMAL(15,5) NULL DEFAULT 0.0,
    MODIFY `discount_tax` DECIMAL(15,5) NULL DEFAULT 0.0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_orderfees`
    MODIFY `amount` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `tax` DECIMAL(15,5) NULL DEFAULT NULL /** CAN FAIL **/;

ALTER TABLE `#__j2store_orderitemattributes`
    MODIFY `orderitemattribute_price` DECIMAL(15,5) NULL DEFAULT 0.0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_orders`
    MODIFY `order_total` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `order_subtotal` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `order_subtotal_ex_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `order_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `order_shipping` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `order_shipping_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `order_discount` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `order_discount_tax` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `order_credit` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `order_refund` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `order_surcharge` DECIMAL(15,5) NULL DEFAULT NULL,
    MODIFY `order_fees` DECIMAL(15,5) NULL DEFAULT NULL /** CAN FAIL **/;

ALTER TABLE `#__j2store_productprice_index`
    MODIFY `min_price` DECIMAL(15,5) NOT NULL,
    MODIFY `max_price` DECIMAL(15,5) NOT NULL /** CAN FAIL **/;

ALTER TABLE `#__j2store_shippingrates`
    MODIFY `shipping_rate_price` DECIMAL(15,5) NULL DEFAULT 0.0,
    MODIFY `shipping_rate_handling` DECIMAL(15,5) NULL DEFAULT 0.0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_customfields`
    MODIFY `enabled` INT NULL DEFAULT 1,
    MODIFY `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_filtergroups`
    MODIFY `enabled` INT NULL DEFAULT 1,
    MODIFY `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_lengths`
    MODIFY `enabled` INT NULL DEFAULT 1,
    MODIFY `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_options`
    MODIFY `enabled` INT NULL DEFAULT 1,
    MODIFY `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_product_options`
    MODIFY `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_weights`
    MODIFY `enabled` INT NULL DEFAULT 1,
    MODIFY `ordering` INT NULL DEFAULT 0 /** CAN FAIL **/;

ALTER TABLE `#__j2store_geozones`
    MODIFY `enabled` INT NULL DEFAULT 1 /** CAN FAIL **/;

ALTER TABLE `#__j2store_products`
    MODIFY `enabled` INT NULL DEFAULT 1 /** CAN FAIL **/;
