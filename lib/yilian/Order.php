<?php
	/**
	 * 商户下单接口测试，接收客户端对接demo的下单请求，并返回下单结果数据给客户端对接demo
	 * 手机访问本下单接口地址为： http://ip:端口/phpdemo/Order.php
	 * 如： http://127.0.0.1:8080/phpdemo/Order.php
	 */
	header("Content-Type:text/html; charset=utf-8");
	
	require_once './src/com/payeco/tools/HttpClient.php';
	require_once './src/com/payeco/tools/Log.php';
	require_once './src/com/payeco/tools/Signatory.php';
	require_once './src/com/payeco/tools/Tools.php';
	require_once './src/com/payeco/tools/Xml.php';
	require_once './src/com/payeco/client/ConstantsClient.php';
	require_once './src/com/merchant/demo/Constants.php';
	require_once './src/com/merchant/demo/DemoTestSdk.php';

	//设置订单数据；  商户在实际使用情况会有部分数据为手机端提交的数据
	$amount = $_POST['Amount'];
	$orderDesc = $_POST['OrderDesc'];
	$extData = "测试";
	$miscData = "";
	//以下扩展参数是按互联网金融行业填写的；其他行业请参考接口文件说明进行填写
	$miscData = "13922897656|0||张三|440121197511140912|62220040001154868428||PAYECO2015102854345||2|";  //互联网金融
	
	if(Tools::checkAmount($amount) == false){
		$retMsgJson = "{\"RetCode\":\"E105\",\"RetMsg\":\"金额格式错!\"}";
		echo $retMsgJson;
		return; 
	}

	//下订单处理自动设置的参数
	$merchOrderId = Tools::currentTimeMillis();  //订单号；本例子按时间产生； 商户请按自己的规则产生
	$merchantId = Constants::getMerchantId();
	$notifyUrl = Constants::getMerchantNotifyUrl();  //需要做URLEncode
	$tradeTime =  Tools::getSysTime();
	$expTime = ""; //采用系统默认的订单有效时间
	$notifyFlag = "0";

	// 调用下单接口
	$retXml = new Xml();
	$retMsgJson = "";
	$bOK = true;
	try {
		Log::setLogFlag(true);
		Log::logFile("--------商户下单接口测试(SDK版本)---------------");
		$ret = TransactionClientSdk::MerchantOrder($merchantId,
				$merchOrderId, $amount, $orderDesc, $tradeTime, $expTime,
				$notifyUrl, $extData, $miscData, $notifyFlag,
				Constants::getMerchantRsaPrivateKey(), Constants::getPayecoRsaPublicKey(),
				Constants::getPayecoUrl(), $retXml);
		if(strcmp("0000", $ret)){
			$bOK=false;
			$retMsgJson = "{\"RetCode\":\"".$ret."\",\"RetMsg\":\"下订单接口返回错误!\"}";
		}
	} catch (Exception $e) {
		$bOK=false;
		$errCode  = $e->getMessage();
		if(strcmp("E101", $errCode) == 0){
			$retMsgJson = "{\"RetCode\":\"E101\",\"RetMsg\":\"下订单接口无返回数据!\"}";
		}else if(strcmp("E102", $errCode) == 0){
			$retMsgJson = "{\"RetCode\":\"E102\",\"RetMsg\":\"验证签名失败!\"}";
		}else if(strcmp("E103", $errCode) == 0){
			$retMsgJson = "{\"RetCode\":\"E103\",\"RetMsg\":\"进行订单签名失败!\"}";
		}else{
			$retMsgJson = "{\"RetCode\":\"E100\",\"RetMsg\":\"下订单通讯失败!\"}";
		}
	}
	
	//设置返回给手机Json数据
	if($bOK){
       	$retMsgJson = "{\"RetCode\":\"0000\",\"RetMsg\":\"下单成功\","
       					."\"Version\":\"".$retXml->getVersion()
                      	."\",\"MerchOrderId\":\"".$retXml->getMerchOrderId()
                        ."\",\"MerchantId\":\"".$retXml->getMerchantId()
                        ."\",\"Amount\":\"".$retXml->getAmount()
                        ."\",\"TradeTime\":\"".$retXml->getTradeTime()
                        ."\",\"OrderId\":\"".$retXml->getOrderId()
                        ."\",\"Sign\":\"".$retXml->getSign()."\"}"; 
	}
	
	//输出数据
	Log::logFile("retMsgJson=".$retMsgJson);
	echo $retMsgJson;
?>