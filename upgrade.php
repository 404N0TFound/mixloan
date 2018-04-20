<?php
$sql = "
ALTER TABLE `ims_xuan_mixloan_product_apply` CHANGE re_bonus re_bonus decimal(7,2) NOT NULL;
ALTER TABLE `ims_xuan_mixloan_product_apply` CHANGE done_bonus done_bonus decimal(7,2) NOT NULL;
ALTER TABLE `ims_xuan_mixloan_product_apply` CHANGE extra_bonus extra_bonus decimal(7,2) NOT NULL;
ALTER TABLE `ims_xuan_mixloan_channel` CHANGE ext_info ext_info MediumText NOT NULL;
ALTER TABLE `ims_xuan_mixloan_bank_artical` CHANGE ext_info ext_info MediumText NOT NULL;
ALTER TABLE `ims_uni_account_modules` CHANGE settings settings MediumText NOT NULL;
ALTER TABLE `ims_xuan_mixloan_product_apply` ADD `degree` tinyint(2) DEFAULT 1;
";

pdo_run($sql);

if(!pdo_fieldexists('xuan_mixloan_product_apply', 'degree')) {
	pdo_run("ALTER TABLE `ims_xuan_mixloan_product_apply` ADD `degree` tinyint(2) DEFAULT 1 ");
}