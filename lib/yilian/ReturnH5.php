<?php
	/**
	 * 描述：接收同步通知(H5)，完成签名验证； 业务逻辑需要商户自行补充
	 */
	header("Content-Type:text/html; charset=utf-8");
	
	require_once './src/com/payeco/tools/HttpClient.php';
	require_once './src/com/payeco/tools/Log.php';
	require_once './src/com/payeco/tools/Signatory.php';
	require_once './src/com/payeco/tools/Tools.php';
	require_once './src/com/payeco/tools/Xml.php';
	require_once './src/com/payeco/client/ConstantsClient.php';
	require_once './src/com/merchant/demo/Constants.php';
	require_once './src/com/merchant/demo/DemoTest.php';

	// 结果通知参数，易联异步通知采用GET提交
	$version = $_POST["Version"];
	$merchantId = $_POST["MerchantId"];
	$merchOrderId = $_POST["MerchOrderId"];
	$amount = $_POST["Amount"];
	$extData = $_POST["ExtData"];
	$orderId = $_POST["OrderId"];
	$status = $_POST["Status"];
	$payTime = $_POST["PayTime"];
	$settleDate = $_POST["SettleDate"];
	$sign = $_POST["Sign"];

	// 需要对必要输入的参数进行检查，本处省略...

	// 订单结果逻辑处理
	$retMsgJson = "";
	try {
		Log::setLogFlag(true);
		//验证订单结果通知的签名
		Log::logFile("------交易： 同步通知(H5版本)-----------------");
		$b = TransactionClient::bCheckNotifySign($version, $merchantId, $merchOrderId, 
				$amount, $extData, $orderId, $status, $payTime, $settleDate, $sign, 
				Constants::getPayecoRsaPublicKey());
		if (!$b) {
			$retMsgJson = "{\"RetCode\":\"E101\",\"RetMsg\":\"验证签名失败!\"}";
			Log::logFile("验证签名(H5)失败!");
		}else{
			// 签名验证成功后，需要对订单进行后续处理
			if (strcmp("02", $status) == 0) { // 订单已支付
     		//if ("0000".equals(status)) { // 若是互联金融行业, 订单已支付的状态为【0000】
				// 1、检查Amount和商户系统的订单金额是否一致
				// 2、订单支付成功的业务逻辑处理请在本处增加（订单通知可能存在多次通知的情况，需要做多次通知的兼容处理）；
				// 3、返回响应内容
				$retMsgJson = "{\"RetCode\":\"0000\",\"RetMsg\":\"订单已支付\"}";
				Log::logFile("订单已支付(H5)!");
			} else {
				// 1、订单支付失败的业务逻辑处理请在本处增加（订单通知可能存在多次通知的情况，需要做多次通知的兼容处理，避免成功后又修改为失败）；
				// 2、返回响应内容
				$retMsgJson = "{\"RetCode\":\"E102\",\"RetMsg\":\"订单支付失败".status."\"}";
				Log::logFile("订单支付(H5)失败!status=".status);
			}
		}
	} catch (Exception $e) {
		$retMsgJson = "{\"RetCode\":\"E103\",\"RetMsg\":\"处理通知结果异常\"}";
		Log::logFile("处理通知(H5)结果异常!e=".$e->getMessage());
	}
	Log::logFile("-----同步通知(H5)完成----");
	//返回数据
	echo $retMsgJson;
?>