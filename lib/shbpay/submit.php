<?php
/**
* 发起支付
*/

require 'config.php';
require 'common.php';

// 支付通道
$channelMapped = 'HB_CIB_ONLINE';

// 支付类型
$defrayalType = 'ALI_H5';






$data = array();

$data['businessHead'] = array();
$data['businessHead']['charset'] = '00';
$data['businessHead']['version'] = 'V1.0.0';
$data['businessHead']['method'] = 'payment';
$data['businessHead']['merchantNumber'] = $merchantNumber;
$data['businessHead']['requestTime'] = date('Ymdhis');
$data['businessHead']['signType'] = '';
$data['businessHead']['sign'] = '';

$data['businessContext'] = array();
$data['businessContext']['defrayalType'] = $defrayalType;
$data['businessContext']['subMerchantNumber'] = $merchantNumber;
$data['businessContext']['channelMapped'] = $channelMapped;
$data['businessContext']['merchantOrderNumber'] = $merchantOrderNumber;
$data['businessContext']['tradeCheckCycle'] = 'T1';
$data['businessContext']['orderTime'] = date('Ymdhis');
$data['businessContext']['currenciesType'] = 'CNY';
$data['businessContext']['tradeAmount'] = $tradeAmount;
$data['businessContext']['commodityBody'] = '购买会员';
$data['businessContext']['commodityDetail'] = '购买水滴会员';
$data['businessContext']['commodityRemark'] = '购买水滴会员';
$data['businessContext']['returnUrl'] = $result_url;
$data['businessContext']['notifyUrl'] = $notify_url;
$data['businessContext']['terminalId'] = '';
$data['businessContext']['terminalIP'] = '127.0.0.1';
$data['businessContext']['userId'] = '';
$data['businessContext']['remark'] = '';
$data['businessContext']['attach'] = '';

ksort($data['businessContext']);

// 计算sign s
$originalData = json_encode($data['businessContext'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$data['businessHead']['sign'] = rsa_private_sign($originalData, $my_private_key);
if ( !$data['businessHead']['sign'] ) {
	echo '计算签名失败';
	die;
}
// 计算sign e


// 加密 s
$originalData = json_encode($data);
$encryptData = rsa_public_encrypt($originalData, $shbapi_public_key);
$post = '{"context":"'.$encryptData.'"}';
// 加密 e


$s = post_json($gateway_url, $post);
if ( !$s ) {
	echo '接口请求失败';
	die;
}

$result = @json_decode($s, true);
if ( @$result['message']['code'] != '200' ) {
	echo @$result['message']['content'] ?: '未知错误';
	die;
}

$result = rsa_private_decrypt($result['context'], $my_private_key);
$result = @json_decode($result, true);
if ( !$result ) {
	echo '解密失败';
	die;
}

// url
$content = $result['businessContext']['content'];


echo '<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">';
echo '生成二维码或跳转到该地址即可发起支付：<br>';
echo '手机访问：<a href="'.$content.'" target="_blank">'.htmlspecialchars($content).'</a><br/>';
echo '或手机扫二维码：<img src="https://pan.baidu.com/share/qrcode?w=210&h=210&url='.urlencode($content).'" />';


