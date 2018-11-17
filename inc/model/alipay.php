<?php
defined('IN_IA') or exit('Access Denied');
require_once('/www/wwwroot/wx_twoplus_top/addons/xuan_mixloan/lib/alipay/aop/AopClient.php');
require_once('/www/wwwroot/wx_twoplus_top/addons/xuan_mixloan/lib/alipay/aop/request/AlipayFundTransToaccountTransferRequest.php');
class Xuan_mixloan_Alipay
{
    private $gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    private $appId = '';
    private $rsaPrivateKey = 'MIIEowIBAAKCAQEAoaVeAfGr/CDT+gq9WwWGTv7X5cwrccX7wAnyAmKCBVUHO6xGglCQ16QX+3WXMZHyOzoDP51iNyUwRwnCz0h7dkxa6+4qBNxnwWJ8ELD4LCoAmpnmckO0SDNXbaj7lW/i7DnjjrVqnOLBhpuQ74fssO5ckXF2LuROUEyPS6j48y6RLmcRySqUkGRwNC2Gd1uZY2+f+UyyPhbzSTuFzdRnEeYLtozNf3AwHOCTaRW1GiFyY1sYMASmnYTe4eLK/vJ1A4PcUkBA+0+fJt9kpPC/8gK4uqhTu8egIJ8FYWj/sKq+LWFIzqehu+7YasijzY9B+RTpG9cFjZVPIgIaBglTTwIDAQABAoIBAE2JUQqFrgWGiaeKt0GN6NrDizQDN2OfoB6BpsBWGCAOpSWOgVPI6XFGmOpZgWiZpSObtCszhwUEpZ+tovBiyDX6cjJxT159ipdMck5fVOt6SkfeQpfUuglX9zv2rpcD0EmXivvNMZKHgmTbAi6jeHl2HJj3X8UmZhpGXwXfy2p5zJgGcza70iupAWUbgDiKLwj752JOpsDDq/+9hhim5XNgV4OhkLXgI/urhlMq6nNP+TJbjVf9DZsjcDltjT5DtC2hS4C1MqTHZ902u7W13Yd3JELEbjHlm9qqAaOadAz5jhoXe6zFFIEEASqbj/LsWOtl1KzaVS2tpj5kFSbIt2ECgYEA0QD2n+dWVXXhJbkUhIzt2hm+1Fwh7JvX7D9WIHJdl9S4Y5POIsywRTdMJWv95Qs7tHx7AyQqfQr24hKUbu23c3dKy5bKkDTTloz0lOIwPmIGgKvZP65bpn9paa71IdY8mJxQKbMqvSrpl8Od1b1dr1HWF0BS5q07s3wrVx0swOMCgYEAxf5P8SwSyAykmw1Qy89OtSGqaGhhY+Izt9K8VmutjKvqbs40xxuASQFFlYL+wAN6DDDpAoxFveGBe/jbMDlapfA+/BBf77FDFru9l1a5LuDk1T64RMOs8juxfC9O9oQ532/9whLZfZ1+BQVl8E+c8D2meFJrcgm6jJkmDO1fy6UCgYA0Af1cxQAiu/aOoIOOiFMXlph514NJkW4lh40y/cJ0aaaIgNsmpbCnSJ9WII1JVYZB30fs/C7mdrgAgYcWI2km/mRKTPeS8tJEAEdMVQyUOWhM1HZ29jgwMjxU5Ahzpw/lGeCIv+C+udLuxOqdqUWKvt57YrI+XJUikJ9oSgY86QKBgQCe/bgOT7kJQfXQuOGfuGpY05721pMWVWf4flZVA4TKyKapshb5qGDcvxO0mwuc/227am9CZ4f9kZ+cANtqnzPmusSpPzD61pqsH7iAVdjBB0Fa6FGqjoNLxZmhwo+jL80VWuYoOWDDGXw/5fTVA+lflfIe/vhfC+bsznKawOdDLQKBgDGPL3bbIXa7KgPB1HNfM6sJGJArGHEw7LJPSOBYmOKyyoptuVfadX5xI7T/2HjAMIdyQOVQVg20PSFZppoB3KOX+U9aFg64pSGAMCr0znXSKo/En8BoZNYJEHyBxKprYtEMr3dxyNxA27xxpPvDfnBP0B2W9NqC+Davuo//9RhW';
    private $alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAgwCMhgTwyDVhC6svYEBwOM1BvAgNdTCzyaHA8JqQSNABaHKAaPV8AHkmgCUHtwPkuTrFt/zvOZgaHt72/y1DVlXekxCOR72u9MzukcMKgmEjTwkSGApZi98P2MUX9Fuxwi2iE7Pm9aDx/cndZqghZSl2ZM7wOSyDCorUBaTYmPzFacZyprIOBjzN0HpAEerbi/xMwox55HDgr8U1P+8jemq5NiU9tl3phxxv8XVRRusPZflX4niAph+5kkR7mgihU6mDIGtP4n2Bjezvn7/ZJIIsdwnvKNqw+CFjTGV0uqtt2QAa2X7ZicZJZGOt9PH66EoB8JeqzJRcmdea8cwrLQIDAQAB';
    /**
     * 转账到个人账户
     * @param $out_biz_no 订单号
     * @param $amount 单位：元
     * @param $payee_account 对方账号
     * @param $payee_real_name 对方真实姓名
     * @return boolean
     */
    public function transfer($out_biz_no, $amount, $payee_account, $payee_real_name)
    {
        $payer_show_name        = '益友智推';
        $remark                 = '用户提现';
        $aop                    = new \AopClient();
        $aop->gatewayUrl        = $this->gatewayUrl;
        $aop->appId             = $this->appId;
        $aop->rsaPrivateKey     = $this->rsaPrivateKey;
        $aop->alipayrsaPublicKey= $this->alipayrsaPublicKey;
        $aop->apiVersion        = '1.0';
        $aop->signType          = 'RSA2';
        $aop->postCharset       = 'utf-8';
        $aop->format            = 'json';
        $request                = new AlipayFundTransToaccountTransferRequest();
        $account = md5($payee_account . floatval($amount));
        $record = pdo_fetch('select id,createtime from ' . tablename('xuan_mixloan_alipay_log') . ' where 
            account=:account order by id desc', array(':account' => $account));
        if (!empty($record)) {
            if ($record['createtime'] + 86400 > time()) {
                return array('code' => -1, 'msg' => '不允许重复打款');
            }
        } else {
            $insert = array();
            $insert['account'] = $account;
            $insert['createtime'] = time();
            pdo_insert('xuan_mixloan_alipay_log', $insert);
        }
        $request->setBizContent("{" .
            "\"out_biz_no\":\"$out_biz_no\"," .
            "\"payee_type\":\"ALIPAY_LOGONID\"," .
            "\"payee_account\":\"$payee_account\"," .
            "\"amount\":\"$amount\"," .
            "\"payer_show_name\":\"$payer_show_name\"," .
            "\"payee_real_name\":\"$payee_real_name\"," .
            "\"remark\":\"$remark\"" .
            "}");
        $result = $aop->execute($request);
        if ($result->alipay_fund_trans_toaccount_transfer_response->code == '10000') {
            return array('code' => 1, 'msg' => '提现成功', 'order_id' => $result->alipay_fund_trans_toaccount_transfer_response->order_id);
        } else {
            return array('code' => -1, 'msg' => $result->alipay_fund_trans_toaccount_transfer_response->sub_msg);
        }
    }
}