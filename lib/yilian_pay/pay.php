<?php

require_once("lib/YiLian.class.php");

$yilian = new YiLian();

$data = array(
    'ACC_NO'=>$ACC_NO,
    'ACC_NAME'=>$ACC_NAME,
	'ID_NO'=>'',
	'MOBILE_NO'=>'',
//    'ACC_PROVINCE'=>'',
//    'ACC_CITY'=>'',
    'AMOUNT'=>$AMOUNT,
    'CNY'=>'CNY',
    'PAY_STATE'=>'',
    'BATCH_NO'=> $BATCH_NO,
    'BANK_NAME'=>$BANK_NAME,
    'MER_ORDER_NO'=>'RHB1000' . date('YmdHis')
);

$res = $yilian->pay($data);
