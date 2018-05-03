<?php

require_once("lib/YiLian.class.php");

$yilian = new YiLian();

$data = array(
    'SN'=>'SN140723110738',
    'BANK_CODE'=>'',
    'ACC_NO'=>'6227003811930126389',
    'ACC_NAME'=>'笪飞亚',
//    'ACC_PROVINCE'=>'',
//    'ACC_CITY'=>'',
    'AMOUNT'=>'1.18',
    'ID_NO'=>'321123197809210107',
    'MOBILE_NO'=>'2022132743',
    'CNY'=>'CNY',
	'RETURN_URL'=>'',
	'PAY_STATE'=>'',
	'MER_ORDER_NO'=>'123456',
	'TRANS_DESC'=>'外呼验密'//订单描述,外呼语音播报内容,由商户自定义
);

$res = $yilian->verify($data);
var_dump($res);