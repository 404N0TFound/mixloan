<?php


	require_once("lib/YiLian.class.php");
	$yilian = new YiLian();

    $data = array(
		'SN'=>$SN,
		'MER_ORDER_NO'=>$MER_ORDER_NO,
     	'QUERY_NO_FLAG'=>'0',
     	'PAY_STATE'=>'',
     	'ACC_NO'=>'',
     	'ACC_NAME'=>'',
     	'AMOUNT'=>'',
     	'CNY'=>''
    );
    $res = $yilian->pay_query($data,$batchNo);
    var_dump($res);