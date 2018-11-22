<?php
define('IN_MOBILE', true);

$xmlData = file_get_contents('php://input');
libxml_disable_entity_loader(true);
$data = json_decode(json_encode(simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
ksort($data);
$buff = '';
foreach ($data as $k => $v){
    if($k != 'sign'){
        $buff .= $k . '=' . $v . '&';
    }
}
$stringSignTemp = $buff . 'key=drsxv3zc50snlctize5cpjlybqfx2r5t';//key为证书密钥
$sign = strtoupper(md5($stringSignTemp));
//判断算出的签名和通知信息的签名是否一致
$json = json_encode($data);
$con = mysqli_connect("127.0.0.1","gst8_cn","JEGmKCH2KK","gst8_cn");
if($sign == $data['sign']){
    if ($data['result_code'] == 'SUCCESS') {
        $sql = "INSERT INTO ims_xuan_mixloan_log (ext_info) VALUES ('{$json}')";
        mysqli_query($con, $sql);
        $sql = "UPDATE `ims_xuan_mixloan_paylog` SET is_pay=1 WHERE notify_id='{$data['out_trade_no']}'";
        mysqli_query($con, $sql);
        mysqli_close($con);
        file_get_contents("http://54fdy.com/app/index.php?i=3&c=entry&op=notify_url" .
            "&do=vip&m=xuan_mixloan&notify_id={$data['out_trade_no']}");
    } else {
        $sql = "UPDATE `ims_xuan_mixloan_paylog` SET is_pay=-1 WHERE notify_id='{$data['out_trade_no']}'";
        mysqli_query($con, $sql);
        mysqli_close($con);
    }
} else {
    $sql = "INSERT INTO ims_xuan_mixloan_log (ext_info) VALUES ('1')";
    mysqli_query($con, $sql);
    mysqli_close($con);
}