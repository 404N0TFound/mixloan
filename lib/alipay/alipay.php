<?php
header('Content-type: text/plain');

// 对签名字符串转义
function createLinkstring($para) {
    $arg  = "";
    while (list ($key, $val) = each ($para)) {
        $arg.=$key.'="'.$val.'"&';
    }
    //去掉最后一个&字符
    $arg = substr($arg,0,count($arg)-2);
    //如果存在转义字符，那么去掉转义
    if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
    return $arg;
}
// 签名生成订单信息
function rsaSign($data) {
    $priKey = "-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAoaVeAfGr/CDT+gq9WwWGTv7X5cwrccX7wAnyAmKCBVUHO6xG
glCQ16QX+3WXMZHyOzoDP51iNyUwRwnCz0h7dkxa6+4qBNxnwWJ8ELD4LCoAmpnm
ckO0SDNXbaj7lW/i7DnjjrVqnOLBhpuQ74fssO5ckXF2LuROUEyPS6j48y6RLmcR
ySqUkGRwNC2Gd1uZY2+f+UyyPhbzSTuFzdRnEeYLtozNf3AwHOCTaRW1GiFyY1sY
MASmnYTe4eLK/vJ1A4PcUkBA+0+fJt9kpPC/8gK4uqhTu8egIJ8FYWj/sKq+LWFI
zqehu+7YasijzY9B+RTpG9cFjZVPIgIaBglTTwIDAQABAoIBAE2JUQqFrgWGiaeK
t0GN6NrDizQDN2OfoB6BpsBWGCAOpSWOgVPI6XFGmOpZgWiZpSObtCszhwUEpZ+t
ovBiyDX6cjJxT159ipdMck5fVOt6SkfeQpfUuglX9zv2rpcD0EmXivvNMZKHgmTb
Ai6jeHl2HJj3X8UmZhpGXwXfy2p5zJgGcza70iupAWUbgDiKLwj752JOpsDDq/+9
hhim5XNgV4OhkLXgI/urhlMq6nNP+TJbjVf9DZsjcDltjT5DtC2hS4C1MqTHZ902
u7W13Yd3JELEbjHlm9qqAaOadAz5jhoXe6zFFIEEASqbj/LsWOtl1KzaVS2tpj5k
FSbIt2ECgYEA0QD2n+dWVXXhJbkUhIzt2hm+1Fwh7JvX7D9WIHJdl9S4Y5POIsyw
RTdMJWv95Qs7tHx7AyQqfQr24hKUbu23c3dKy5bKkDTTloz0lOIwPmIGgKvZP65b
pn9paa71IdY8mJxQKbMqvSrpl8Od1b1dr1HWF0BS5q07s3wrVx0swOMCgYEAxf5P
8SwSyAykmw1Qy89OtSGqaGhhY+Izt9K8VmutjKvqbs40xxuASQFFlYL+wAN6DDDp
AoxFveGBe/jbMDlapfA+/BBf77FDFru9l1a5LuDk1T64RMOs8juxfC9O9oQ532/9
whLZfZ1+BQVl8E+c8D2meFJrcgm6jJkmDO1fy6UCgYA0Af1cxQAiu/aOoIOOiFMX
lph514NJkW4lh40y/cJ0aaaIgNsmpbCnSJ9WII1JVYZB30fs/C7mdrgAgYcWI2km
/mRKTPeS8tJEAEdMVQyUOWhM1HZ29jgwMjxU5Ahzpw/lGeCIv+C+udLuxOqdqUWK
vt57YrI+XJUikJ9oSgY86QKBgQCe/bgOT7kJQfXQuOGfuGpY05721pMWVWf4flZV
A4TKyKapshb5qGDcvxO0mwuc/227am9CZ4f9kZ+cANtqnzPmusSpPzD61pqsH7iA
VdjBB0Fa6FGqjoNLxZmhwo+jL80VWuYoOWDDGXw/5fTVA+lflfIe/vhfC+bsznKa
wOdDLQKBgDGPL3bbIXa7KgPB1HNfM6sJGJArGHEw7LJPSOBYmOKyyoptuVfadX5x
I7T/2HjAMIdyQOVQVg20PSFZppoB3KOX+U9aFg64pSGAMCr0znXSKo/En8BoZNYJ
EHyBxKprYtEMr3dxyNxA27xxpPvDfnBP0B2W9NqC+Davuo//9RhW
-----END RSA PRIVATE KEY-----";   // 生成密钥时获取，直接使用pem文件的字符串
    $res = openssl_get_privatekey($priKey);
    openssl_sign($data, $sign, $res);
    openssl_free_key($res);
    $sign = base64_encode($sign);
    $sign = urlencode($sign);
    return $sign;
}
// 支付宝合作身份者ID，以2088开头的16位纯数字 
$partner = "2088231130368872";  // 支付宝开通快捷支付功能后可获取
// 支付宝账号
$seller_id = "2685740593@qq.com"; 
// 订单标题
$subject = '购买代理';
// 订单详情
$body = '付费购买代理资格'; 
// 订单号，示例代码使用时间值作为唯一的订单ID号
// $out_trade_no = date('YmdHis', time());
$parameter = array(
    'service'        => 'mobile.securitypay.pay',   // 必填，接口名称，固定值
    'partner'        => $partner,                   // 必填，合作商户号
    '_input_charset' => 'UTF-8',                    // 必填，参数编码字符集
    'out_trade_no'   => $out_trade_no,              // 必填，商户网站唯一订单号
    'subject'        => $subject,                   // 必填，商品名称
    'payment_type'   => '1',                        // 必填，支付类型
    'seller_id'      => $seller_id,                 // 必填，卖家支付宝账号
    'total_fee'      => $total,                     // 必填，总金额，取值范围为[0.01,100000000.00]
    'body'           => $body,                      // 必填，商品详情
    'it_b_pay'       => '1d',                       // 可选，未付款交易的超时时间
    'notify_url'     => $notify_url,                // 可选，服务器异步通知页面路径
    'show_url'       => $base_path                  // 可选，商品展示网站
 );
//生成需要签名的订单
$orderInfo = createLinkstring($parameter);
//签名
$sign = rsaSign($orderInfo);
//生成订单
echo $orderInfo.'&sign="'.$sign.'"&sign_type="RSA"';
?>