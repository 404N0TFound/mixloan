<?php

require_once("lib/YiLian.class.php");

$yilian = new YiLian();

$data = array(
	'SN'=>'',
    'ACC_NO'=>'6227003811930123783',
    'ACC_NAME'=>'',
	'ID_NO'=>'',
	 'MOBILE_NO'=>'2022132743',
    'AMOUNT'=>'',
    'CNY'=>'',
    'PAY_STATE'=>'',
    'MER_ORDER_NO'=>'',
	'TRANS_DESC'=>'短信内容'
);

$res = $yilian->send_message($data);
var_dump($res);