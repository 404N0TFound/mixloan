<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_Checknum
{
    function check($mobile)
    {
        $mobile = trim($mobile);
        $url = 'https://api.253.com/open/unn/ucheck';
        $params = [
            'appId' => 'q7oEdeef', // appId,登录万数平台查看
            'appKey' => '4r6fn2w5', // appKey,登录万数平台查看
            'mobile' => $mobile, // 要检测的手机号，限单个，仅支持11位国内号码
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $result = curl_exec($ch);
        $result = json_decode($result, 1);
        if ($result['code'] != "200000") {
            return array('code' => -1, 'msg' => $result['message']);
        }
        if ($result['data']['status'] == 0) {
            return array('code' => -1, 'msg' => "您所填写的号码是空号");
        } 
        return array('code' => 1, 'msg' => '成功');
    }
    function wcheck($mobile)
    {
        $url = 'https://api.253.com/open/wool/wcheck';
        $params = [
            'appId' => 'q7oEdeef', // appId,登录万数平台查看
            'appKey' => '4r6fn2w5', // appKey,登录万数平台查看
            'mobile' => $mobile, // 要检测的手机号，限单个，仅支持11位国内号码
            'ip' => $this->getRealIp() // 检测手机号的IP地址，非必传(重要，建议传入)
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $result = curl_exec($ch);
        $result = json_decode($result, 1);
        if ($result['code'] != "200000") {
            return array('code' => -1, 'msg' => $result['message']);
        }
        if ($result['data']['status'] == "B1" || $result['data']['status'] == "B2") {
            return array('code' => -1, 'msg' => "该号码可信度低，无法申请");
        } 
        return array('code' => 1, 'msg' => '成功');
    }
    /**
     * 获取Ip
     */
    function getRealIp()
    {
        $ip=false;
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi ("^(10│172.16│192.168).", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }
}
