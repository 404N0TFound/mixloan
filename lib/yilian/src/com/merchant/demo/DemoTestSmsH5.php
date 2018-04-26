<?php

/**
 * 【短信+H5页面版本】的接口测试demo； 分别对以下接口进行测试
 * 1、【短信+H5页面版本】的商户下单接口（MerchantOrderSmsH5PayTest）
 * 2、【短信+H5页面版本】的重新发送支付短信接口（OrderSmsSendAgainTest）
 */
require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/Constants.php';
require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Xml.php';
require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Tools.php';
require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/client/TransactionClientSmsH5.php';

class DemoTestSmsH5 {

	/**
	 * 商户下订单接口测试
	 */
	static function MerchantOrderSmsH5PayTest() {
		// 设置参数
		$amount = "1.00";
		$orderDesc = "通用商户充值";
		$extData = "充值测试"; //
		//以下扩展参数是按互联网金融行业填写的；其他行业请参考接口文件说明进行填写
		$miscData = "13922897656|0||张三|440121197511140912|62220040001154868428||PAYECO2015111976876||2|";  //互联网金融
		$merchOrderId = Tools::currentTimeMillis(); // 订单号
		$merchantId = Constants::getMerchantId();
		$notifyUrl = Constants::getMerchantNotifyUrl(); // 封装的API会自动做URLEncode
		$returnUrl = Constants::getMerchantReturnUrl(); // 封装的API会自动做URLEncode
		$tradeTime = Tools::getSysTime();
		$expTime = ""; // 采用系统默认的订单有效时间
		$notifyFlag = "0";
	
		// 调用下单接口
		Log::logFile("-------【短信+H5页面版本】商户下单接口测试-------------------------");
		try {
			$retXml = new Xml();
			
			//接口参数请参考TransactionClient的参数说明
			$ret = TransactionClientSmsH5::MerchantOrderSmsH5Pay($merchantId,
								$merchOrderId, $amount, $orderDesc, $tradeTime, $expTime,
								$notifyUrl, $returnUrl, $extData, $miscData, $notifyFlag,
								Constants::getMerchantRsaPrivateKey(), Constants::getPayecoRsaPublicKey(),
								Constants::getPayecoUrl(), $retXml);
			if(strcmp("0000", $ret)){
				Log::logFile("【短信+H5页面版本】商户下单接口测试失败！：retCode=".$ret."; msg=".$retXml->getRetMsg());
				return;
			}
		} catch (Exception $e) {
			Log::logFile("【短信+H5页面版本】商户下单接口测试失败！：".$e->getMessage());
			return;
		}
		Log::logFile("【短信+H5页面版本】商户下单接口测试----ok");
		Log::logFile("------------------------------------------------");
	}
	
	/**
	 * 重新发送支付短信接口测试
	 */
	static function OrderSmsSendAgainTest($orderId) {
		// 设置参数
		$merchantId = Constants::getMerchantId();
		$merchOrderId = $orderId;
		$tradeTime = Tools::getSysTime();
	
		// 调用查询接口
		Log::logFile("-------【短信+H5页面版本】重新发送支付短信接口测试-------------------------");
		try {
			$retXml = new Xml();
			//接口参数请参考TransactionClient的参数说明
			$ret = TransactionClientSmsH5::OrderSmsSendAgain($merchantId, $merchOrderId, $tradeTime,
					Constants::getMerchantRsaPrivateKey(), Constants::getPayecoRsaPublicKey(),
					Constants::getPayecoUrl(), $retXml);
			if(strcmp("0000", $ret)){
				Log::logFile("【短信+H5页面版本】重新发送支付短信接口测试失败！：retCode=".$ret."; msg=".$retXml->getRetMsg());
				return;
			}
		} catch (Exception $e) {
			Log::logFile("【短信+H5页面版本】重新发送支付短信接口测试失败！：".$e->getMessage());
			return;
		}
		Log::logFile("【短信+H5页面版本】重新发送支付短信接口测试----ok");
		Log::logFile("------------------------------------------------");
	}
	
	static function Test() {
		//设置打印日志
		Log::setLogFlag(true);
	
		// 商户下单接口测试
		DemoTestSmsH5::MerchantOrderSmsH5PayTest();  //短信+H5页面版本下订单接口
		echo "【短信+H5页面版本】商户下单接口接口调用完成，请到日志文件查询调用结果";
		echo "<br/>";
		
		// 重新发送短信测试
		$orderId = "1449483307405";//填写已经成功下单的商户订单号；需要间隔60秒后才能重发
		DemoTestSmsH5::OrderSmsSendAgainTest($orderId);
		echo "【短信+H5页面版本】重新发送短信接口调用完成，请到日志文件查询调用结果";
		echo "<br/>";
	}

}
?>