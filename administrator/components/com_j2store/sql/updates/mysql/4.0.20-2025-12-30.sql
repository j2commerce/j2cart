UPDATE `#__j2store_orderstatuses` SET `orderstatus_cssclass` = 'label-success text-bg-success' WHERE `orderstatus_name` = 'J2STORE_CONFIRMED' AND `orderstatus_cssclass` = 'label-success';
UPDATE `#__j2store_orderstatuses` SET `orderstatus_cssclass` = 'label-info text-bg-info' WHERE `orderstatus_name` = 'J2STORE_PROCESSED' AND `orderstatus_cssclass` = 'label-info';
UPDATE `#__j2store_orderstatuses` SET `orderstatus_cssclass` = 'label-important text-bg-danger' WHERE `orderstatus_name` = 'J2STORE_FAILED' AND `orderstatus_cssclass` = 'label-important';
UPDATE `#__j2store_orderstatuses` SET `orderstatus_cssclass` = 'label-warning text-bg-warning' WHERE `orderstatus_name` = 'J2STORE_PENDING' AND `orderstatus_cssclass` = 'label-warning';
UPDATE `#__j2store_orderstatuses` SET `orderstatus_cssclass` = 'label-warning text-bg-warning' WHERE `orderstatus_name` = 'J2STORE_NEW' AND `orderstatus_cssclass` = 'label-warning';
UPDATE `#__j2store_orderstatuses` SET `orderstatus_cssclass` = 'label-warning text-bg-warning' WHERE `orderstatus_name` = 'J2STORE_CANCELLED' AND `orderstatus_cssclass` = 'label-warning';
