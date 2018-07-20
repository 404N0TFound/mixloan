<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_Pay
{
    private $appid = "wxb4ca9c39087e3977";
    private $mchid = "1510075851";
    private $secrect_key = "ab123456789001234567899874561230";
    private $pay_url= "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
    private $pay_url_bank = "https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank";
    private $H5pay_url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    private $publickey_url = "https://fraud.mch.weixin.qq.com/risk/getpublickey";
    private $publickey_path = "/www/wwwroot/wx.uohengwangluo.com/addons/xuan_mixloan/data/key/ras.pub";
    private $apiclient_cert = "/www/wwwroot/wx.uohengwangluo.com/addons/xuan_mixloan/data/cert/apiclient_cert.pem";
    private $apiclient_key = "/www/wwwroot/wx.uohengwangluo.com/addons/xuan_mixloan/data/cert/apiclient_key.pem";
    function __construct()
    {
        // if (!file_exists($this->publickey_path)) {
        //     $this->GetPubRsa();
        // }
    }
    /**
     * H5支付
     * @param $amount 单位：分
     * @param $notify_url
     * @return array
     */
    function H5pay($trade_no, $amount, $notify_url)
    {
        if (empty($amount)) {
            return ["code"=>-1, "msg"=>"amount不能为空"];
        }
        if (empty($notify_url)) {
            return ["code"=>-1, "msg"=>"notify_url不能为空"];
        }
        $params["appid"] = $this->appid;
        $params["mch_id"] = $this->mchid;
        $params['out_trade_no'] = $trade_no;
        $params["nonce_str"] = strtoupper(md5($trade_no));
        $params['body'] = '亿融官方充值';
        $params["spbill_create_ip"] = $this->getRealIp();
        $params["total_fee"] = intval($amount*100);
        $params["notify_url"] = $notify_url;
        $params["trade_type"] = "MWEB";
        $params["scene_info"] = '{"h5_info": {"type":"Wap","wap_url": "http://crmj168.com","wap_name": "亿融官方充值"}}';
        $string = $this->GetHttpQueryString($params);
        $sign = $this->GetSign($string);
        $params["sign"] = $sign;
        $result = $this->curl($this->H5pay_url, $params, false);
        if ($result['result_code'] != "FAIL") {
            $data = array(
                "url"=>$result['mweb_url']
            );
            return ["code"=>1, "msg"=>$result["return_msg"], "data"=>$data];
        } else {
            return ["code"=>-1, "msg"=>$result["return_msg"]];
        }
    }
    /**
     * 获取签名
     * @param $string
     * @return string
     */
    function GetSign($string)
    {
        $string .= "&key=" . $this->secrect_key;
        $sign = strtoupper(md5($string));
        return $sign;
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
     * array转xml
     * @param $data
     * @return string
     */
    function arraytoxml($data)
    {
        $str='<xml>';
        foreach($data as $k=>$v) {
            $str.='<'.$k.'>'.$v.'</'.$k.'>';
        }
        $str.='</xml>';
        return $str;
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
    /**
     * post请求
     * @param string $param
     * @param $url
     * @return array
     */
    function curl($url, $param=array(), $cert=false)
    {
        $postUrl = $url;
        $curlPost = $this->arraytoxml($param);
        $ch = curl_init();                                      //初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);                 //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);           // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        if ($cert) {
            curl_setopt($ch,CURLOPT_SSLCERT,$this->apiclient_cert); //这个是证书的位置
            curl_setopt($ch,CURLOPT_SSLKEY,$this->apiclient_key); //这个也是证书的位置
        }
        $data = curl_exec($ch);                                 //运行curl
        curl_close($ch);
        return $this->xmltoarray($data);
    }
    /**
     * 加密字段
     * @param $str
     * @return string
     */
    public function rsa_encrypt($str)
    {
        $pu_key = openssl_pkey_get_public(file_get_contents($this->publickey_path)); //读取公钥内容
        $encryptedBlock = '';
        $encrypted = '';
        openssl_public_encrypt($str,$encryptedBlock,$pu_key,OPENSSL_PKCS1_OAEP_PADDING);
        $str_base64  = base64_encode($encrypted.$encryptedBlock);
        return $str_base64;
    }
    /**
     * 获取公钥
     */
    public function GetPubRsa()
    {
        $params = array(
            'mch_id'    => $this->mchid,
            'nonce_str' => strtoupper(md5(time())),
            'sign_type' => 'MD5'
        );
        $string = $this->GetHttpQueryString($params);
        $signature = $this->GetSign($string); //生成sign
        $params["sign"] = $signature;
        $xml = $this->curl($this->publickey_url, $params, true);
        if($xml["return_code"] == 'SUCCESS' && !empty($xml["pub_key"]))
        {
            $handle = fopen($this->publickey_path, w) or die("写入文件没有权限");
            fwrite($handle, $xml["pub_key"]);
            fclose($handle);
        } else {
            die($xml["return_msg"]);
        }
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