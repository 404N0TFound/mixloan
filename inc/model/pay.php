<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_Pay
{
    private $appid = "wxeb603cfa7d123e70";
    private $mchid = "1484265262";
    private $secrect_key = "lyet24ceael03aj2vo5wq251nu3lf2kn";
    private $pay_url= "https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank";
    private $H5pay_url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    private $publickey_url = "https://fraud.mch.weixin.qq.com/risk/getpublickey";
    private $publickey_path = "/www/wwwroot/wx.91sckj.top/addons/xuan_mixloan/data/key/ras.pub";
    private $apiclient_cert = "/www/wwwroot/wx.91sckj.top/addons/xuan_mixloan/data/cert/apiclient_cert.pem";
    private $apiclient_key = "/www/wwwroot/wx.91sckj.top/addons/xuan_mixloan/data/cert/apiclient_key.pem";
    function __construct()
    {
        if (!file_exists($this->publickey_path)) {
            $this->GetPubRsa();
        }
    }
    /**
     * 打款
     * @param $bank_no
     * @param $true_name
     * @param $bank_code
     * @param $amount 单位：分
     * @param $desc
     * @return array
     */
    function pay($bank_no, $true_name, $bank_code, $amount, $desc, $trade_no="")
    {
        if (empty($bank_no)) {
            return ["code"=>-1, "msg"=>"银行卡号不能为空"];
        }
        if (empty($true_name)) {
            return ["code"=>-1, "msg"=>"姓名不能为空"];
        }
        if (empty($bank_code)) {
            return ["code"=>-1, "msg"=>"银行代码不能为空"];
        }
        if (empty($desc)) {
            return ["code"=>-1, "msg"=>"说明不能为空"];
        }
        if ($trade_no == "") {
            $trade_no = "ZML".date("YmdHis");
        }
        $params["mch_id"] = $this->mchid;
        $params["partner_trade_no"] = $trade_no;
        $params["nonce_str"] = strtoupper(md5($trade_no));
        $params["enc_bank_no"] = $this->rsa_encrypt($bank_no);
        $params["enc_true_name"] = $this->rsa_encrypt($true_name);
        $params["bank_code"] = $bank_code;
        $params["amount"] = intval($amount*100);
        $params["desc"] = $desc;
        $string = $this->GetHttpQueryString($params);
        $sign = $this->GetSign($string);
        $params["sign"] = $sign;
        $result = $this->curl($this->pay_url, $params, true);
        if ($result['return_code'] == "SUCCESS") {
            $data = array(
                "partner_trade_no"=>$result["partner_trade_no"],
                "payment_no"=>$result["payment_no"],
            );
            return ["code"=>1, "msg"=>$result["err_code_des"], "data"=>$data];
        } else {
            return ["code"=>-1, "msg"=>$result["err_code_des"]];
        }
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
        $params['body'] = '代客吧官方充值';
        $params["spbill_create_ip"] = $this->getRealIp();
        $params["total_fee"] = intval($amount*100);
        $params["notify_url"] = $notify_url;
        $params["trade_type"] = "MWEB";
        $params["scene_info"] = '{"h5_info": {"type":"Wap","wap_url": "http://juxinwangluo.xin","wap_name": "代客吧官方充值"}}';
        $string = $this->GetHttpQueryString($params);
        $sign = $this->GetSign($string);
        $params["sign"] = $sign;
        $result = $this->curl($this->H5pay_url, $params, true);
        if ($result['return_code'] == "SUCCESS") {
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
        }
    }
    /**
    *   获取银行卡代号
    **/
    public function getBankCode($name)
    {
        $result = array(
            '工商银行'=>1002,
            '农业银行'=>1005,
            '中国银行'=>1026,
            '建设银行'=>1003,
            '招商银行'=>1001,
            '邮储银行'=>1066,
            '交通银行'=>1020,
            '浦发银行'=>1004,
            '民生银行'=>1006,
            '兴业银行'=>1009,
            '平安银行'=>1010,
            '中信银行'=>1021,
            '华夏银行'=>1025,
            '广发银行'=>1027,
            '光大银行'=>1022,
            '北京银行'=>1032,
            '宁波银行'=>1056,
        );
        if (!empty($result[$name])) {
            return $result[$name];
        } else {
            return false;
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