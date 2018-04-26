<?php

/**
 * 【纯API接口版本】的接口测试demo； 分别对以下接口进行测试
 * 1、【纯API接口版本】的订单快捷支付接口（MerchantOrderPayQuickByAccTest）
 */

require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/Constants.php';
require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Xml.php';
require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/client/TransactionClientApi.php';

class DemoTestApi {
	/**
	 * 【纯API接口版本】的订单快捷支付接口测试
	 */
	static function MerchantOrderPayQuickByAccTest() {
		// 设置参数
		$amount = "1.00";
		$orderDesc = "通用商户充值";
		$extData = "充值测试"; //
		//以下扩展参数是按互联网金融行业填写的；其他行业请参考接口文件说明进行填写
		$miscData = "13922897656|0||张三|440121197511140912|62220040001154868428||PAYECO2015111945234||2|";  //互联网金融
		$merchOrderId = Tools::currentTimeMillis(); // 订单号
		$merchantId = Constants::getMerchantId();
		$notifyUrl = Constants::getMerchantNotifyUrl(); // 封装的API会自动做URLEncode
		$tradeTime = Tools::getSysTime();
		$expTime = ""; // 采用系统默认的订单有效时间
		$notifyFlag = "0";
		$industryId = "";
	
		// 调用下单接口
		Log::logFile("-------【纯API接口版本】的订单快捷支付接口测试-------------------------");
		try {
			$retXml = new Xml();
			
			//接口参数请参考TransactionClient的参数说明
			$ret = TransactionClientApi::MerchantOrderPayQuickByAcc($merchantId, $industryId, $merchOrderId, $amount, $orderDesc,
					$tradeTime, $expTime, $notifyUrl, $extData, $miscData, $notifyFlag, 
					Constants::getMerchantRsaPrivateKey(), Constants::getPayecoRsaPublicKey(), 
					Constants::getPayecoUrl(), $retXml);
			if(strcmp("0000", $ret)){
				Log::logFile("【纯API接口版本】的订单快捷支付接口测试失败！：retCode=".$ret."; msg=".$retXml->getRetMsg());
				return;
			}
		} catch (Exception $e) {
			Log::logFile("【纯API接口版本】的订单快捷支付接口测试失败！：".$e->getMessage());
			return;
		}
		Log::logFile("【纯API接口版本】的订单快捷支付接口测试----ok");
		Log::logFile("------------------------------------------------");
	}
	
	static function Test() {
		//设置打印日志
		Log::setLogFlag(true);
		
		// 【纯API接口版本】的订单快捷支付接口测试
		DemoTestApi::MerchantOrderPayQuickByAccTest();
		echo "【纯API接口版本】的订单快捷支付接口测试调用完成，请到日志文件查询调用结果";
		echo "<br/>";
	}	

}
?>