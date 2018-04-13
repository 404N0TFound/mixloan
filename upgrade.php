<?php
$sql = "
DELETE FROM `ims_qrcode_stat` WHERE qrcid in (SELECT a.qrcid FROM `ims_qrcode_stat` a right join `ims_xuan_mixloan_member` b on a.openid=b.openid AND a.qrcid=b.id WHERE a.qrcid is not null GROUP by a.qrcid)

ALTER TABLE `ims_xuan_mixloan_product_apply` CHANGE re_bonus re_bonus decimal(7,2) NOT NULL;
ALTER TABLE `ims_xuan_mixloan_product_apply` CHANGE done_bonus done_bonus decimal(7,2) NOT NULL;
ALTER TABLE `ims_xuan_mixloan_product_apply` CHANGE extra_bonus extra_bonus decimal(7,2) NOT NULL;
ALTER TABLE `ims_xuan_mixloan_channel` CHANGE ext_info ext_info MediumText NOT NULL;
ALTER TABLE `ims_xuan_mixloan_bank_artical` CHANGE ext_info ext_info MediumText NOT NULL;
ALTER TABLE `ims_uni_account_modules` CHANGE settings settings MediumText NOT NULL;
ALTER TABLE `ims_xuan_mixloan_product_apply` ADD `degree` tinyint(2) DEFAULT 1;

CREATE TABLE IF NOT EXISTS `ims_xuan_mixloan_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) NOT NULL,
  `openid` varchar(50) NOT NULL COMMENT '发送的openid',
  `template_id` varchar(50) NOT NULL COMMENT '模板消息id',
  `data` TEXT NOT NULL COMMENT '发送的信息',
  `createtime` int(11) NOT NULL,
  `status` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uniacid` (`uniacid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
";

pdo_run($sql);

if(!pdo_fieldexists('xuan_mixloan_product_apply', 'degree')) {
	pdo_run("ALTER TABLE `ims_xuan_mixloan_product_apply` ADD `degree` tinyint(2) DEFAULT 1 ");
}