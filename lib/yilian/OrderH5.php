<?php
	/**
	 * 商户下单接口测试，接收客户端对接demo的下单请求，并返回下单结果数据给客户端对接demo
	 * 手机访问本下单接口地址为： http://ip:端口/phpdemo/OrderH5.php
	 * 如： http://127.0.0.1:8080/phpdemo/Order.php
	 */
	header("Content-Type:text/html; charset=utf-8");
	
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/HttpClient.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Log.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Signatory.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Tools.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Xml.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/client/ConstantsClient.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/Constants.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/DemoTestH5.php';

	//设置订单数据；  商户在实际使用情况会有部分数据为手机端提交的数据
	$amount = 0.01;
	$orderDesc = 'sdadsa';
	$clientIp = $_SERVER["REMOTE_ADDR"]; //商户用户访问IP地址
	$extData = "H5测试";
	//以下扩展参数是按互联网金融行业填写的；其他行业请参考接口文件说明进行填写
	$miscData = "13922897656|0||张三|440121197511140912|62220040001154868428||PAYECO20151028543445||2|";  //互联网金融
	
	if(Tools::checkAmount($amount) == false){
		$retMsgJson = "{\"RetCode\":\"E105\",\"RetMsg\":\"金额格式错!\"}";
		echo $retMsgJson;
		return; 
	}

	//下订单处理自动设置的参数
	$merchOrderId = Tools::currentTimeMillis();  //订单号；本例子按时间产生； 商户请按自己的规则产生
	$merchantId = Constants::getMerchantId();
	$notifyUrl = Constants::getMerchantNotifyUrl();  //需要做URLEncode
	$returnUrl = Constants::getMerchantReturnUrl();  //需要做URLEncode
	$tradeTime =  Tools::getSysTime();
	$expTime = ""; //采用系统默认的订单有效时间
	$notifyFlag = "0";

	// 调用下单接口
	$retXml = new Xml();
	$retMsgJson = "";
	$bOK = true;
	try {
		Log::setLogFlag(true);
		Log::logFile("--------商户下单接口测试---------------");
		$ret = TransactionClientH5::MerchantOrderH5($merchantId,
				$merchOrderId, $amount, $orderDesc, $tradeTime, $expTime,
				$notifyUrl, $returnUrl, $extData, $miscData, $notifyFlag, $clientIp, 
				Constants::getMerchantRsaPrivateKey(), Constants::getPayecoRsaPublicKey(),
				Constants::getPayecoUrl(), $retXml);
		if(strcmp("0000", $ret)){
			$bOK=false;
			$retMsgJson = "{\"RetCode\":\"".$ret."\",\"RetMsg\":\"下订单接口返回错误!\"}";
		}
	} catch (Exception $e) {
		var_dump($e);die;
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
	
	//重定向到订单支付
	if($bOK){
		//根据返回的参数组织向易联支付平台提交支付申请的URL
		$redirectUrl = TransactionClientH5::getPayInitRedirectUrl(Constants::getPayecoUrl(), $retXml);
		Log::logFile("PayURL : ".redirectUrl);
		
		//针对【支付申请URL】，可以采用直接sendRedirect转跳的方式；也可以采用页面确认后再转跳的方式；
		//商户根据自己的业务逻辑选择，建议在正式使用时采用sendRedirect转跳方式；
// 		echo "<script language='javascript' type='text/javascript'>";
// 		echo "window.location.href='$redirectUrl'";  
// 		echo "</script>";
		
		//--页面确认后再转跳的方式
		$retMsgJson = "<html><head><title>易联支付H5测试-支付请求</title></head><body>支付请求URL:".$redirectUrl."<br/>"
				." <a href=\"".$redirectUrl."\">立即支付</a></body></html>";
		echo $retMsgJson;
	}else{
		//输出数据
		Log::logFile("retMsgJson=".$retMsgJson);
		echo $retMsgJson;
	}
?>