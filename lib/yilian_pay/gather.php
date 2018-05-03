<?php

require_once("lib/YiLian.class.php");

$yilian = new YiLian();

$data = array(
    'ACC_NO'=>'6227003811930123783',
    'ACC_NAME'=>'笪飞亚',
	'ID_NO'=>'321123197809210107',
	 'MOBILE_NO'=>'2022132743',
//    'ACC_PROVINCE'=>'',
//    'ACC_CITY'=>'',
    'AMOUNT'=>'1000.00',
    'CNY'=>'CNY',
    'PAY_STATE'=>'',
    'RETURN_URL'=>'',
    'MER_ORDER_NO'=>'123456',
	'TRANS_DESC'=>'',//代收订单描述内容
	'SMS_CODE'=>'123456'
);

$res = $yilian->gather($data);
var_dump($res);