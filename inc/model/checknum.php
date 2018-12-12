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
}
