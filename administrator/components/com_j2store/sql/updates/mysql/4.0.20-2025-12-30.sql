UPDATE `#__j2store_orderstatuses` SET `orderstatus_cssclass` = 'label-success text-bg-success' WHERE `orderstatus_name` = 'J2STORE_CONFIRMED';
UPDATE `#__j2store_orderstatuses` SET `orderstatus_cssclass` = 'label-info text-bg-info' WHERE `orderstatus_name` = 'J2STORE_PROCESSED';
UPDATE `#__j2store_orderstatuses` SET `orderstatus_cssclass` = 'label-important text-bg-danger' WHERE `orderstatus_name` = 'J2STORE_FAILED';
UPDATE `#__j2store_orderstatuses` SET `orderstatus_cssclass` = 'label-warning text-bg-warning' WHERE `orderstatus_name` = 'J2STORE_PENDING';
UPDATE `#__j2store_orderstatuses` SET `orderstatus_cssclass` = 'label-warning text-bg-warning' WHERE `orderstatus_name` = 'J2STORE_NEW';
UPDATE `#__j2store_orderstatuses` SET `orderstatus_cssclass` = 'label-warning text-bg-warning' WHERE `orderstatus_name` = 'J2STORE_CANCELLED';
