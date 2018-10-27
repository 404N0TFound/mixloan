<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
class Xuan_mixloan_Aliyun
{


    /**
     * APISTORE 获取数据
     * @param $url 请求地址
     * @param array $params 请求的数据
     * @param $appCode 您的APPCODE
     * @param $method
     * @return array|mixed
     */
    function bank4($params = array(), $method = "GET")
    {
        $url = 'https://aliyun-bankcard4-verify.apistore.cn/bank4';
        $appCode = 'e443ad855f5345859330fbf5483055fc';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $method == "POST" ? $url : $url . '?' . http_build_query($params));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization:APPCODE ' . $appCode
        ));
        //如果是https协议
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            //CURL_SSLVERSION_TLSv1
            curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        }
        //超时时间
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //通过POST方式提交
        if ($method == "POST") {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        //返回内容
        $callbcak = curl_exec($curl);
        //http status
        $CURLINFO_HTTP_CODE = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        //关闭,释放资源
        curl_close($curl);
        //如果返回的不是200,请参阅错误码 https://help.aliyun.com/document_detail/43906.html
        if ($CURLINFO_HTTP_CODE == 200) {
            $result =  json_decode($callbcak, true);
            if ($result['error_code'] == 0) {
                return array('code' => 1, 'msg' => '验证成功');
            } else {
                return array('code' => -1, 'msg' => $result['reason']);
            }
        }
        else if ($CURLINFO_HTTP_CODE == 403) {
            return array("code" => -1, "msg" => "剩余次数不足");
        }
        else if ($CURLINFO_HTTP_CODE == 400) {
            return array("code" => -1, "msg" => "APPCODE错误");
        }
        else {
            return array("code" => -1, "msg" => "APPCODE错误");
        }
    }
}
