<?php


	require_once("lib/YiLian.class.php");
	$yilian = new YiLian();

    $data = array(
		'SN'=>'',
//		'MER_ORDER_NO'=>'123456',
     	'QUERY_NO_FLAG'=>'0',
     	'PAY_STATE'=>'',
     	'ACC_NO'=>'',
     	'ACC_NAME'=>'',
     	'AMOUNT'=>'',
     	'CNY'=>''
    );
    $batchNo = 'IMYB20150203YT4LA';
    $res = $yilian->gather_query($data,$batchNo);
    var_dump($res);