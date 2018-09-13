<?php
define('IN_MOBILE', true);
require_once( '/www/wwwroot/ss-k.cn/addons/xuan_mixloan/lib/payment/alipaySdk/aop/AopClient.php');
$aop = new AopClient;
$aop->alipayrsaPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';
$flag = $aop->rsaCheckV1($_POST, NULL, "RSA");
$con = mysqli_connect("127.0.0.1","ss_k_cn","BxTSHEBZaMxB6R6H","ss_k_cn");
if($flag){
    if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
        $sql = "SELECT fee FROM ims_xuan_mixloan_paylog WHERE notify_id='{$_POST['out_trade_no']}'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($result);
        if (floatval($row['fee']) == floatval($_POST['price'])) {
            $sql = "UPDATE `ims_xuan_mixloan_paylog` SET is_pay=1 WHERE notify_id='{$_POST['out_trade_no']}'";
            mysqli_query($con, $sql);
            mysqli_close($con);
            file_get_contents("http://jrbd.tejiazu.com/app/index.php?i=191&c=entry&op=alipay_notify&do=vip&m=xuan_mixloan&notify_id={$_POST['out_trade_no']}");
        }
    }
} else {
    $sql = "UPDATE `ims_xuan_mixloan_paylog` SET is_pay=-1 WHERE notify_id='{$_POST['out_trade_no']}'";
    mysqli_query($con, $sql);
    mysqli_close($con);
    echo 'fail';
}