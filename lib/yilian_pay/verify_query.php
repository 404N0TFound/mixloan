<?php

    require_once("lib/YiLian.class.php");
    $yilian = new YiLian();

    $data = array(
    	'SN'=>'',
        'ACC_NO'=>'6227003811930126389',
        'ACC_NAME'=>'',
        'RESERVE'=>'Y',
    	'PAY_STATE'=>'',
   		'AMOUNT'=>'',
    	'CNY'=>''
    );

    $res = $yilian->verify_query($data);
    var_dump($res);
