<?php
	/**
	 * 接收订单结果通知处理例子；本例子只举例了订单结果的参数获取；签名验证；订单状态的判断。 测试接口可以通过以下URL进行测试：
	 * http://127.0.0.18080/phpdemo/Notify.php?Version=2.0.0&MerchantId=302020000058&MerchOrderId=1407893794150&Amount=1.00&ExtData=5rWL6K+V&OrderId=302014081300038222&Status=02&PayTime=20140814111645&SettleDate=20140909&Sign=iDQ6gBAebnh1kzSb4XN0PP3bTIXTkwG9iE8PDnNZBEiTWpBknH4XoBAotC5G/RF4E+HUa7f9esJWEI1mKw84EMDt+gBY2KABe7fejIdzqS8AH5niJEJkWAKwm4qYQTkT4Ate9lshcOZDfcyZ7eqblXXHUYOFBsYtslANOsb+/IA=
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
	$version = $_GET["Version"];
	$merchantId = $_GET["MerchantId"];
	$merchOrderId = $_GET["MerchOrderId"];
	$amount = $_GET["Amount"];
	$extData = $_GET["ExtData"];
	$orderId = $_GET["OrderId"];
	$status = $_GET["Status"];
	$payTime = $_GET["PayTime"];
	$settleDate = $_GET["SettleDate"];
	$sign = $_GET["Sign"];

	// 需要对必要输入的参数进行检查，本处省略...

	// 订单结果逻辑处理
	$retMsgJson = "";
	try {
		Log::setLogFlag(true);
		//验证订单结果通知的签名
		Log::logFile("------订单结果通知验证-----------------");
		$b = TransactionClient::bCheckNotifySign($version, $merchantId, $merchOrderId, 
				$amount, $extData, $orderId, $status, $payTime, $settleDate, $sign, 
				Constants::getPayecoRsaPublicKey());
		if (!$b) {
			$retMsgJson = "{\"RetCode\":\"E101\",\"RetMsg\":\"验证签名失败!\"}";
			Log::logFile("验证签名失败!");
		}else{
			// 签名验证成功后，需要对订单进行后续处理
			if (strcmp("02", $status) == 0) { // 订单已支付
				// 1、检查Amount和商户系统的订单金额是否一致
				// 2、订单支付成功的业务逻辑处理请在本处增加（订单通知可能存在多次通知的情况，需要做多次通知的兼容处理）；
				// 3、返回响应内容
				$retMsgJson = "{\"RetCode\":\"0000\",\"RetMsg\":\"订单已支付\"}";
				Log::logFile("订单已支付!");
			} else {
				// 1、订单支付失败的业务逻辑处理请在本处增加（订单通知可能存在多次通知的情况，需要做多次通知的兼容处理，避免成功后又修改为失败）；
				// 2、返回响应内容
				$retMsgJson = "{\"RetCode\":\"E102\",\"RetMsg\":\"订单支付失败".status."\"}";
				Log::logFile("订单支付失败!status=".status);
			}
		}
	} catch (Exception $e) {
		$retMsgJson = "{\"RetCode\":\"E103\",\"RetMsg\":\"处理通知结果异常\"}";
		Log::logFile("处理通知结果异常!e=".$e->getMessage());
	}
	Log::logFile("-----处理完成----");
	//返回数据
	echo $retMsgJson;
?>