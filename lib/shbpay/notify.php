<?php
/**
* 后台通知
*/

require 'config.php';
require 'common.php';


$data = file_get_contents('php://input');

mylog('收到通知：' . $data);

$result = @json_decode($data, true);
if ( !$result ) {
	echo '没有收到任何内容';
	die;

} else if ( @$result['message']['code'] != '200' ) {
	echo @$result['message']['content'] ?: '未知错误';
	die;
}

$result = rsa_private_decrypt($result['context'], $my_private_key);

mylog('收到通知-解密：' . $result);

$result = @json_decode($result, true);
if ( !$result ) {
	echo '解密失败';
	die;

} else if ( @$result['businessContext']['orderStatus'] != 'SUC' ) {
	echo '订单状态非SUC';
	die;

}

// 订单金额
$tradeAmount = $result['businessContext']['tradeAmount'];

// 商户单号
$merchantOrderNumber = $result['businessContext']['merchantOrderNumber'];

// 速汇宝单号
$shbOrderNumber = $result['businessContext']['shbOrderNumber'];




// TODO 商户处理订单

mylog('收到通知-单号：' . $merchantOrderNumber.', 金额：' . $tradeAmount);




// 支付成功时，输出SUC
echo 'SUC';