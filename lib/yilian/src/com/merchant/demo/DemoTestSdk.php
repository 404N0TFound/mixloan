<?php

/**
 * 【SDK标准版】和【纯SDK密码键盘版】下单接口测试demo,分别对以下接口进行测试
 * 1、【SDK标准版】和【纯SDK密码键盘版】的商户下单接口（MerchantOrderTest）
 */
require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/Constants.php';
require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Xml.php';
require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/client/TransactionClientSdk.php';

class DemoTestSdk {
	/**
	 * 商户下订单接口测试(SDK版本)
	 */
	static function MerchantOrderTest() {
		// 设置参数
		$amount = "1.00";
		$orderDesc = "测试";
		$extData = "充值测试"; //
		//以下扩展参数是按互联网金融行业填写的；其他行业请参考接口文件说明进行填写
		$miscData = "13922897656|0||张三|440121197511140912|62220040001154868428||PAYECO201510285445||2|";  //互联网金融
		$merchOrderId = Tools::currentTimeMillis(); // 订单号
		$merchantId = Constants::getMerchantId();
		$notifyUrl = Constants::getMerchantNotifyUrl(); // 需要做URLEncode???
		$tradeTime = Tools::getSysTime();
		$expTime = ""; // 采用系统默认的订单有效时间
		$notifyFlag = "0";

		// 调用下单接口
		Log::logFile("-------订单下单接口测试(SDK版本)-------------------------");
		try {
			$retXml = new Xml();
			
			//接口参数请参考TransactionClient的参数说明
			$ret = TransactionClientSdk::MerchantOrder($merchantId,
					$merchOrderId, $amount, $orderDesc, $tradeTime, $expTime,
					$notifyUrl, $extData, $miscData, $notifyFlag,
					Constants::getMerchantRsaPrivateKey(), Constants::getPayecoRsaPublicKey(), 
					Constants::getPayecoUrl(), $retXml);
			if(strcmp("0000", $ret)){
				Log::logFile("商户下单接口测试失败！：retCode=".$ret."; msg=".$retXml->getRetMsg());
				return;
			}
		} catch (Exception $e) {
			Log::logFile("商户下单接口测试失败！：".$e->getMessage());
			return;
		}
		Log::logFile("商户下单接口测试----ok");
		Log::logFile("merchOrderId=".$merchOrderId);
		Log::logFile("------------------------------------------------");
	}

	static function Test() {
		//设置打印日志
		Log::setLogFlag(true);
		
		// 商户下单接口测试
		DemoTestSdk::MerchantOrderTest();
		echo "商户下单接口(SDK版本)调用完成，请到日志文件查询调用结果";
		echo "<br/>";
	}
}
?>