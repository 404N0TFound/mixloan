<?php

include_once 'TripleDES.class.php';
include_once 'Md5RSA.class.php';
include_once 'RSA.class.php';
include_once 'Crypt3Des.class.php';
include_once 'reqBean.class.php';

class YiLian{

    public $URL = ""; //易联接口下单地址
    public $pub_key = ""; //公钥
    public $pfx_key = ""; //私钥路径
    public $pfx_pass = ""; //私钥密码
    public $des = null; // 3des 加密类
    public $rsa = null; // rsa 加密类
    public $log = null; //log 日志类
    public $USER_NAME = ""; //易联系统后台用户名
    public $VERSION = ""; //版本
    public $RETURN_URL = ""; //异步通知地址

    public function __construct(){

        $this->pub_key = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCqWSfUW3fSyoOYzOG8joy3xldpBanLVg8gEDcvm9KxVjqvA/qJI7y0Rmkc1I7l9vAfWtNzphMC+wlulpaAsa/4PbfVj+WhoNQyhG+m4sP27BA8xuevNT9/W7/2ZVk4324NSowwWkaqo1yuZe1wQMcVhROz2h+g7j/uZD0fiCokWwIDAQAB';
        $this->pfx_key = "/www/wwwroot/pl.fuziyo.cn/addons/xuan_mixloan/lib/yilian.pfx";
        $this->pfx_pass = "11111111";
        $this->URL = "https://testagent.payeco.com:9444/service";
        $this->VERSION = "2.0";
        $this->USER_NAME = "13728096874";
        $this->RETURN_URL = "";
        $this->log['batchNo'] = $this->create_batch_no();

        $this->rsa = new RSA($this->pfx_key, $this->pfx_pass);
        $this->des = new Crypt3Des();
        $this->des->setKey(base64_decode($this->generateKey(9999, 24)));

        //组装公钥匙
        $pem = chunk_split($this->pub_key, 64, "\n"); //转换为pem格式的公钥
        $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
        $this->pub_key = openssl_get_publickey($pem); //获取公钥内容
    }

    //生成BATCH_NO，每笔订单不可重复，建议：公司简称缩写+yymmdd+流水号
    public function create_batch_no(){
        return "XYZ" . date("Ymd")  . $this->random_numbers(6);
    }

    //生成随机字母+数字
    public function random_numbers($size = 4){
        $str = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code = "";

        $len = strlen($str);
        for ($i = 0; $i < $size; $i++)
        {
            $code .= $str{rand(0, $len)};
        }
        return $code;
    }

    //设置是代付、代收、认证
    public function getMsgType($type){
        if ($type == "pay"){//批量代付
            return "100001";
        }elseif ($type == "pay_query"){//批量代付查询
            return "100002";
		}elseif ($type == "send_message"){//发送短信验证码
        	return "500001";
        }elseif ($type == "gather"){//批量代收
            return "200001";
        }elseif ($type == "gather_query"){//批量代收查询
            return "200002";
        }elseif($type == 'verify'){//认证
            return "300001";
        }elseif($type == 'verify_query'){//认证查询
            return "300002";
        }
        return '';
    }

//    /* 发送数据返回接收数据 */
    public function postXmlUrl($url, $xmlStr, $ssl = false, $type = "Content-type: text/xml")
    {
        $ch = curl_init();
        $params = array();
        if ($type)
            $params[] = $type; //定义content-type为xml
        curl_setopt($ch, CURLOPT_URL, $url); //定义表单提交地址
        if ($ssl)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_POST, 1);   //定义提交类型 1：POST ；0：GET
        curl_setopt($ch, CURLOPT_HEADER, 0); //定义是否显示状态头 1：显示 ； 0：不显示
        if ($params)
            curl_setopt($ch, CURLOPT_HTTPHEADER, $params); //定义请求类型
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //定义是否直接输出返回流
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlStr); //定义提交的数据，这里是XML文件
        //封禁"Expect"头域
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $xml_data = curl_exec($ch);
        if (curl_errno($ch))
        {
            throw new Exception(curl_error($ch));
        }
        else
        {
            curl_close($ch);
        }

        return $xml_data;
    }

	/**
	 * 短信验证码功能 - 发送短信验证码到客户绑定手机号
	 * @param $data
	 */
	public function send_message($data){
		$bean = new reqBean();
		$bean->VERSION = $this->VERSION;
		$bean->USER_NAME = $this->USER_NAME;
		$bean->BATCH_NO = $this->log['batchNo'];
		$bean->MSG_TYPE = $this->getMsgType("send_message");

		//body
		$bean->addDetail($data);
		//私钥 对签名加密rsa 然后放到xml中
		$bean->MSG_SIGN = $this->rsa->sign($bean->toSign(), $this->rsa->priKey);
		$queryXml = $bean->classToXml();

		//代收记录信息
		$log_data = array();
		$log_data['acc_no'] = $data['ACC_NO'];
		$log_data['mobile_no'] = $data['MOBILE_NO'];
		$log_data['trans_desc'] = $data['TRANS_DESC'];

		//des 加密
		$req_body_enc = $this->des->encrypt($queryXml);
		//公钥 rsa  加密
		$req_key_enc = $this->rsa->encrypt(base64_encode($this->des->key), $this->pub_key);
		$sendTxt = $req_body_enc . "|" . $req_key_enc;
		$data = $this->postXmlUrl($this->URL, $sendTxt, true);
		$result = explode("|", $data);
		$key_3des = $this->rsa->decrypt($result[1], $this->rsa->priKey); //私钥匙 rsa  解密
		$this->des->setKey(base64_decode($key_3des));
		$receiveXml = $this->des->decrypt($result[0]);
		$bean->xmlToClass($receiveXml);

		if ($this->rsa->verify($bean->toSign(), $bean->MSG_SIGN, $this->pub_key)){
			$res_state = array();
			$res_state['TRANS_STATE'] = $bean->TRANS_STATE;
			$res_state['TRANS_DETAILS'] = $bean->TRANS_DETAILS;
			$res_state['PAY_STATE'] = $res_state['TRANS_DETAILS'][0]['PAY_STATE'];
			return $res_state;
		}
		return "";
	}

    /**
     * 代收功能 - 客户付款给我商户
     * @param $data
     */
    public function gather($data){
        $bean = new reqBean();
        $bean->VERSION = $this->VERSION;
        $bean->USER_NAME = $this->USER_NAME;
        $bean->BATCH_NO = $this->log['batchNo'];
        $bean->MSG_TYPE = $this->getMsgType("gather");

        //body
        $data['SN'] = 'SN'.date('YmdHis');
        $bean->addDetail($data);
        //私钥 对签名加密rsa 然后放到xml中
        $bean->MSG_SIGN = $this->rsa->sign($bean->toSign(), $this->rsa->priKey);
        $queryXml = $bean->classToXml();

        //代收记录信息
        $log_data = array();
        $log_data['orderid'] = $data['MER_ORDER_NO'];
        $log_data['acc_no'] = $data['ACC_NO'];
        $log_data['acc_name'] = $data['ACC_NAME'];
        $log_data['idcard'] = $data['ID_NO'];
        $log_data['amount'] = $data['AMOUNT'];
        $log_data['createtime'] = date('Y-m-d H:i:s',time());
        $log_data['sn'] = $data['SN'];//默认

        //des 加密
        $req_body_enc = $this->des->encrypt($queryXml);
        //公钥 rsa  加密
        $req_key_enc = $this->rsa->encrypt(base64_encode($this->des->key), $this->pub_key);
        $sendTxt = $req_body_enc . "|" . $req_key_enc;
        $data = $this->postXmlUrl($this->URL, $sendTxt, true);
        $result = explode("|", $data);
        $key_3des = $this->rsa->decrypt($result[1], $this->rsa->priKey); //私钥匙 rsa  解密
        $this->des->setKey(base64_decode($key_3des));
        $receiveXml = $this->des->decrypt($result[0]);
        $bean->xmlToClass($receiveXml);

        if ($this->rsa->verify($bean->toSign(), $bean->MSG_SIGN, $this->pub_key)){
            $res_state = array();
            $res_state['TRANS_STATE'] = $bean->TRANS_STATE;
            $res_state['TRANS_DETAILS'] = $bean->TRANS_DETAILS;
            $res_state['PAY_STATE'] = $res_state['TRANS_DETAILS'][0]['PAY_STATE'];
            return $res_state;
        }
        return "";
    }

    /**
     * 代收查询 查询一条记录
     * @param $data
     * @return array|string
     * @throws Exception
     */
    public function gather_query($data,$batchNo=''){
        $bean = new reqBean();
        $bean->VERSION = $this->VERSION;
        $bean->USER_NAME = $this->USER_NAME;
        $bean->BATCH_NO = $batchNo;
        $bean->MSG_TYPE = $this->getMsgType("gather_query");

        //body
        $bean->addDetail($data);
        //私钥 对签名加密rsa 然后放到xml中
        $bean->MSG_SIGN = $this->rsa->sign($bean->toSign(), $this->rsa->priKey);
        $queryXml = $bean->classToXml();

        $req_body_enc = $this->des->encrypt($queryXml); //des 加密
        $req_key_enc = $this->rsa->encrypt(base64_encode($this->des->key), $this->pub_key); //公钥 rsa  加密
        $sendTxt = $req_body_enc . "|" . $req_key_enc;
        $data = $this->postXmlUrl($this->URL, $sendTxt, true);
        $result = explode("|", $data);
        $key_3des = $this->rsa->decrypt($result[1], $this->rsa->priKey); //私钥匙 rsa  解密
        $this->des->setKey(base64_decode($key_3des));
        $receiveXml = $this->des->decrypt($result[0]);
        $bean->xmlToClass($receiveXml);


        if ($this->rsa->verify($bean->toSign(), $bean->MSG_SIGN, $this->pub_key)){
            $res_state = array();
            $res_state['TRANS_STATE'] = $bean->TRANS_STATE;
            $res_state['TRANS_DETAILS'] = $bean->TRANS_DETAILS;
            $res_state['PAY_STATE'] = $res_state['TRANS_DETAILS'][0]['PAY_STATE'];

            return $res_state;
        }

        return "";
    }

    /**
     * 认证
     * @param $data
     * @return array|string
     * @throws
     */
    public function verify($data){
        //req_bean
        $bean = new reqBean();
        $bean->VERSION = $this->VERSION;
        $bean->USER_NAME = $this->USER_NAME;
        $bean->BATCH_NO = $this->log['batchNo'];
        $bean->MSG_TYPE = $this->getMsgType("verify");
        //body
        $data['SN'] = 'SN'.date('YmdHis');
        $bean->addDetail($data);

        //私钥 对签名加密rsa 然后放到xml中
        $bean->MSG_SIGN = $this->rsa->sign($bean->toSign(), $this->rsa->priKey);

        $queryXml = $bean->classToXml();

        //绑卡记录信息
        $log_data = array();
        $log_data['orderid'] = $data['MER_ORDER_NO'];
        $log_data['bank_icon'] = $data['BANK_CODE'];
        $log_data['bank_code'] = $data['ACC_NO'];
        $log_data['idcard'] = $data['ID_NO'];
        $log_data['bank_name'] = $data['ACC_NAME'];
        $log_data['mobile'] = $data['MOBILE_NO'];
        $log_data['createtime'] = date('Y-m-d H:i:s',time());
        $log_data['sn'] = $data['SN'];//默认

        $req_body_enc = $this->des->encrypt($queryXml); //des 加密
        $req_key_enc = $this->rsa->encrypt(base64_encode($this->des->key), $this->pub_key); //公钥 rsa  加密
        $sendTxt = $req_body_enc . "|" . $req_key_enc;

        $data = $this->postXmlUrl($this->URL, $sendTxt, true);

        $result = explode("|", $data);
        $key_3des = $this->rsa->decrypt($result[1], $this->rsa->priKey); //私钥匙 rsa  解密
        $this->des->setKey(base64_decode($key_3des));
        $receiveXml = $this->des->decrypt($result[0]);
        $bean->xmlToClass($receiveXml);

        if ($res = $this->rsa->verify($bean->toSign(), $bean->MSG_SIGN, $this->pub_key)){
            $res_state = array();
            $res_state['TRANS_STATE'] = $bean->TRANS_STATE;
            $res_state['TRANS_DETAILS'] = $bean->TRANS_DETAILS;
            $res_state['PAY_STATE'] = $res_state['TRANS_DETAILS'][0]['PAY_STATE'];

            return $res_state;
        }

        return "";
    }

    /**
     * 认证查询
     * @param $data
     */
    public function verify_query($data){
        //req_bean
        $bean = new reqBean();
        $bean->VERSION = $this->VERSION;
        $bean->USER_NAME = $this->USER_NAME;
        $bean->BATCH_NO = $this->log['batchNo'];
        $bean->MSG_TYPE = $this->getMsgType("verify_query");
        //body
        $bean->addDetail($data);
        //私钥 对签名加密rsa 然后放到xml中
        $bean->MSG_SIGN = $this->rsa->sign($bean->toSign(), $this->rsa->priKey);

        $queryXml = $bean->classToXml();
        $req_body_enc = $this->des->encrypt($queryXml); //des 加密
        $req_key_enc = $this->rsa->encrypt(base64_encode($this->des->key), $this->pub_key); //公钥 rsa  加密
        $sendTxt = $req_body_enc . "|" . $req_key_enc;

        $data = $this->postXmlUrl($this->URL, $sendTxt, true);

        $result = explode("|", $data);
        $key_3des = $this->rsa->decrypt($result[1], $this->rsa->priKey); //私钥匙 rsa  解密
        $this->des->setKey(base64_decode($key_3des));
        $receiveXml = $this->des->decrypt($result[0]);
        $bean->xmlToClass($receiveXml);

        if ($res = $this->rsa->verify($bean->toSign(), $bean->MSG_SIGN, $this->pub_key)){
            $res_state = array();
            $res_state['TRANS_STATE'] = $bean->TRANS_STATE;
            $res_state['TRANS_DETAILS'] = $bean->TRANS_DETAILS;
            $res_state['PAY_STATE'] = $res_state['TRANS_DETAILS'][0]['PAY_STATE'];

            return $res_state;
        }

        return "";
    }

	/**
	 * 代付功能 - 商户付款给客户
	 * @param $data
	 */
	public function pay($data){
		$bean = new reqBean();
		$bean->VERSION = '2.1';
		$bean->USER_NAME = $this->USER_NAME;
		$bean->BATCH_NO = $data['BATCH_NO'];
		$bean->MSG_TYPE = $this->getMsgType("pay");

		//body
		$data['SN'] = 'SN'.date('YmdHis');
		$bean->addDetail($data);
		//私钥 对签名加密rsa 然后放到xml中
		$bean->MSG_SIGN = $this->rsa->sign($bean->toSign(), $this->rsa->priKey);
		$queryXml = $bean->classToXml();

		//代收记录信息
		$log_data = array();
		$log_data['orderid'] = $data['MER_ORDER_NO'];
		$log_data['acc_no'] = $data['ACC_NO'];
		$log_data['acc_name'] = $data['ACC_NAME'];
		$log_data['idcard'] = $data['ID_NO'];
		$log_data['amount'] = $data['AMOUNT'];
		$log_data['createtime'] = date('Y-m-d H:i:s',time());
		$log_data['sn'] = $data['SN'];//默认
		//des 加密
		$req_body_enc = $this->des->encrypt($queryXml);
		//公钥 rsa  加密
		$req_key_enc = $this->rsa->encrypt(base64_encode($this->des->key), $this->pub_key);
		$sendTxt = $req_body_enc . "|" . $req_key_enc;
		$data = $this->postXmlUrl($this->URL, $sendTxt, true);
		$result = explode("|", $data);
		$key_3des = $this->rsa->decrypt($result[1], $this->rsa->priKey); //私钥匙 rsa  解密
		$this->des->setKey(base64_decode($key_3des));
		$receiveXml = $this->des->decrypt($result[0]);
		$bean->xmlToClass($receiveXml);

		if ($this->rsa->verify($bean->toSign(), $bean->MSG_SIGN, $this->pub_key)){
			$res_state = array();
			$res_state['TRANS_STATE'] = $bean->TRANS_STATE;
			$res_state['TRANS_DETAILS'] = $bean->TRANS_DETAILS;
			$res_state['PAY_STATE'] = $res_state['TRANS_DETAILS'][0]['PAY_STATE'];
			return $res_state;
		}
		return "";
	}

	/**
	 * 代付查询 查询一条记录
	 * @param $data
	 * @return array|string
	 * @throws Exception
	 */
	public function pay_query($data,$batchNo=''){
		$bean = new reqBean();
		$bean->VERSION = '2.1';
		$bean->USER_NAME = $this->USER_NAME;
		$bean->BATCH_NO = $batchNo;
		$bean->MSG_TYPE = $this->getMsgType("pay_query");

		//body
		$bean->addDetail($data);
		//私钥 对签名加密rsa 然后放到xml中
		$bean->MSG_SIGN = $this->rsa->sign($bean->toSign(), $this->rsa->priKey);
		$queryXml = $bean->classToXml();

		$req_body_enc = $this->des->encrypt($queryXml); //des 加密
		$req_key_enc = $this->rsa->encrypt(base64_encode($this->des->key), $this->pub_key); //公钥 rsa  加密
		$sendTxt = $req_body_enc . "|" . $req_key_enc;
		$data = $this->postXmlUrl($this->URL, $sendTxt, true);
		$result = explode("|", $data);
		$key_3des = $this->rsa->decrypt($result[1], $this->rsa->priKey); //私钥匙 rsa  解密
		$this->des->setKey(base64_decode($key_3des));
		$receiveXml = $this->des->decrypt($result[0]);
		$bean->xmlToClass($receiveXml);


		if ($this->rsa->verify($bean->toSign(), $bean->MSG_SIGN, $this->pub_key)){
			$res_state = array();
			$res_state['TRANS_STATE'] = $bean->TRANS_STATE;
			$res_state['TRANS_DETAILS'] = $bean->TRANS_DETAILS;
			$res_state['PAY_STATE'] = $res_state['TRANS_DETAILS'][0]['PAY_STATE'];

			return $res_state;
		}

		return "";
	}

    //生成24位随机码
    public function generateKey($round, $length)
    {
        $key = "";
        for ($i = 0; $i < $length; $i++)
        {
            $random = rand(0, $round) % 16;
            switch ($random)
            {
                case 0: $key .= "0";
                    break;
                case 1: $key .= "1";
                    break;
                case 2: $key .= "2";
                    break;
                case 3: $key .= "3";
                    break;
                case 4: $key .= "4";
                    break;
                case 5: $key .= "5";
                    break;
                case 6: $key .= "6";
                    break;
                case 7: $key .= "7";
                    break;
                case 8: $key .= "8";
                    break;
                case 9: $key .= "9";
                    break;
                case 10: $key .= "A";
                    break;
                case 11: $key .= "B";
                    break;
                case 12: $key .= "C";
                    break;
                case 13: $key .= "D";
                    break;
                case 14: $key .= "E";
                    break;
                case 15: $key .= "F";
                    break;
                default: $i--;
            }
        }

        return base64_encode($key);
    }

    //获取所有银行卡信息
    public function getAllBankType(){
        $types['104']['icon'] = "BOC";
        $types['104']['name'] = "中国银行";

        $types['1025']['icon'] = "ICBC";
        $types['1025']['name'] = "中国工商银行";
        $types['3080']['icon'] = "CMB";
        $types['3080']['name'] = "招商银行";

        $types['105']['icon'] = "CCB";
        $types['105']['name'] = "中国建设银行";

        $types['103']['icon'] = "ABC";
        $types['103']['name'] = "中国农业银行";

        $types['3230']['icon'] = "PSBC";
        $types['3230']['name'] = "中国邮政储蓄银行";

        // $types['305']['icon'] = "CMBC";
        //$types['305']['name'] = "中国民生银行";

        $types['307']['icon'] = "PINGAN";
        $types['307']['name'] = "平安银行";
        $types['307']['isneedcity'] = "1";

        $types['301']['icon'] = "COMM";
        $types['301']['name'] = "交通银行";
        //$types['313']['icon'] = "CITIC";
        // $types['313']['name'] = "中信银行";
        //$types['314']['icon'] = "SPDB";
        //$types['314']['name'] = "浦发银行";
        $types['309']['icon'] = "CIB";
        $types['309']['name'] = "兴业银行";

        //$types['311']['icon'] = "HXBANK";
        //$types['311']['name'] = "华夏银行";
        $types['306']['icon'] = "CGB";
        $types['306']['name'] = "广发银行";
        $types['312']['icon'] = "CEBBANK";
        $types['312']['name'] = "中国光大银行";
        //$types['310']['icon'] = "BJBANK";
        //$types['310']['name'] = "北京银行";
        $types['10001']['icon'] = "GZCB";
        $types['10001']['name'] = "广州银行";
        $types['10001']['isneedcity'] = "1";

        $types['10002']['icon'] = "JSYH";
        $types['10002']['name'] = "江苏银行";
        //$types['10002']['icon'] = "GRCB";
        //$types['10002']['name'] = "广州农商银行";
        //$types['10003']['icon'] = "HRCC";
        //$types['10003']['name'] = "海南省农村信用社";
        return $types;
    }

}