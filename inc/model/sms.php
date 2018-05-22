<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}
class Xuan_Mixloan_Sms
{  
    private $host = "http://47.92.69.199:8088/v2sms.aspx";
    private $userid = '384510';
    private $username = '3SDK-GHJ-0130-JJSOT';
    private $password = '708228';
    private $timestamp;
    private $sign;
    public function __construct()
    {
       $this->timestamp = time();
       $sign = $this->username . $this->password . $this->timestamp;
       $this->sign = md5($sign);
    }
    /**
     * 发送短信
     * @param $mobile
     * @param $code
     * @return string
     */
    public function send($mobile, $content)
    {
        $params = array();
        $params['userid'] = $this->userid;
        $params['timestamp'] = $this->timestamp;
        $params['sign'] = $this->sign;
        $params['mobile'] = $mobile;
        $params['content'] = urlencode($content);
        $params['action'] = 'send';
        // $queryString = $this->GetHttpQueryString($params);
        $result = $this->post($this->host, $params);
        var_dump($result);
        if ($result['returnstatus'] == 'Faild') {
            return array('code'=>-1, 'msg'=>$result['message']);
        }

    }
    /**
     * 数组转get方式
     * @param $params
     * @return string
     */
    function GetHttpQueryString($params)
    {
        if (!empty($params) && !is_array($params)) {
            return "";
        }
        ksort($params);
        $str = "";
        foreach ($params as $k => $v)
        {
            $str .= $k ."=". $v ."&";
        }
        $str = rtrim($str, "&");
        return $str;
    }

    /**
     * post方法
     * @param $url
     * @param $post_data
     * @return arary
     */
    public function post($url, $post_data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);//用post方法传送参数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = $this->xmltoarray($response);
        return $result;
    }

    /**
     * xml转array
     * @param $xml
     * @return arary
     */
    function xmltoarray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }
}