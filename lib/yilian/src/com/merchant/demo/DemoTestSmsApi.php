<?php

/**
 * 【短信+API接口版本】的接口测试demo； 分别对以下接口进行测试
 * 1、【短信+API接口版本】的发送短信验证码接口（MerchantOrderSendSmCodeTest）
 * 2、【短信+API接口版本】的无磁无密交易接口（MerchantOrderPayByAccTest）
 */

require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/Constants.php';
require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Xml.php';
require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/client/TransactionClientSmsApi.php';

class DemoTestSmsApi {
	private static $MerchOrderId_smId = "13323664"; //短信凭证号，发送和验证时要保持一致
	
	/**
	 * 【短信+API接口版本】的发送短信验证码接口测试
	 */
	static function MerchantOrderSendSmCodeTest() {
		// 设置参数
		$smId = self::$MerchOrderId_smId;
		$mobileNo = "13922897656";
		$verifyTradeCode = "PayByAcc";
		$smParam = "|张三|440121197511140912|62220040001154868428|1.00";  //参数格式：行业编号|姓名|证件号码|银行卡号|交易金额
		$merchantId = Constants::getMerchantId();
		$merchOrderId = ""; // 订单号
		$tradeTime = Tools::getSysTime();
	
		// 调用下单接口
		Log::logFile("-------【短信+API接口版本】发送短信验证码接口测试-------------------------");
		try {
			$retXml = new Xml();
			
			//接口参数请参考TransactionClient的参数说明
			$ret = TransactionClientSmsApi::OrderSendSmCode($merchantId, $smId, $merchOrderId, $tradeTime, $mobileNo, $verifyTradeCode, $smParam,
							Constants::getMerchantRsaPrivateKey(), Constants::getPayecoRsaPublicKey(), 
							Constants::getPayecoUrl(), $retXml);
			if(strcmp("0000", $ret)){
				Log::logFile("【短信+API接口版本】发送短信验证码接口测试失败！：retCode=".$ret."; msg=".$retXml->getRetMsg());
				return;
			}
		} catch (Exception $e) {
			Log::logFile("【短信+API接口版本】发送短信验证码接口测试失败！：".$e->getMessage());
			return;
		}
		Log::logFile("【短信+API接口版本】发送短信验证码接口测试----ok");
		Log::logFile("------------------------------------------------");
	}
	
	/**
	 * 【短信+API接口版本】的无磁无密交易接口测试
	 */
	static function MerchantOrderPayByAccTest($smCodePay) {
		// 设置参数
		$amount = "1.00";
		$orderDesc = "通用商户充值";
		$extData = "充值测试"; //
		//以下扩展参数是按互联网金融行业填写的；其他行业请参考接口文件说明进行填写
		$miscData = "13922897656|0||张三|440121197511140912|62220040001154868428||PAYECO20151119452334||2|";  //互联网金融
		$merchOrderId = Tools::currentTimeMillis(); // 订单号
		$merchantId = Constants::getMerchantId();
		$notifyUrl = Constants::getMerchantNotifyUrl(); // 封装的API会自动做URLEncode
		$tradeTime = Tools::getSysTime();
		$expTime = ""; // 采用系统默认的订单有效时间
		$notifyFlag = "0";
		$industryId = "";
		$smId = self::$MerchOrderId_smId;
		$smCode = $smCodePay;
	
		// 调用下单接口
		Log::logFile("-------【短信+API接口版本】无磁无密交易接口测试-------------------------");
		try {
			$retXml = new Xml();
			
			//接口参数请参考TransactionClient的参数说明
			$ret = TransactionClientSmsApi::MerchantOrderPayByAcc($merchantId, $industryId, $merchOrderId, $amount, $orderDesc,
					$tradeTime, $expTime, $notifyUrl, $extData, $miscData, $notifyFlag, $smId, $smCode,
					Constants::getMerchantRsaPrivateKey(), Constants::getPayecoRsaPublicKey(), 
					Constants::getPayecoUrl(), $retXml);
			if(strcmp("0000", $ret)){
				Log::logFile("【短信+API接口版本】无磁无密交易接口测试失败！：retCode=".$ret."; msg=".$retXml->getRetMsg());
				return;
			}
		} catch (Exception $e) {
			Log::logFile("【短信+API接口版本】无磁无密交易接口测试失败！：".$e->getMessage());
			return;
		}
		Log::logFile("【短信+API接口版本】无磁无密交易接口测试----ok");
		Log::logFile("------------------------------------------------");
	}
	
	static function Test() {
		//设置打印日志
		Log::setLogFlag(true);
	
		// 商户发送短信验证码接口测试
		DemoTestSmsApi::MerchantOrderSendSmCodeTest();  //短信+API接口版本的发送短信验证码接口
		echo "商户发送短信验证码接口调用完成，请到日志文件查询调用结果";
		echo "<br/>";
		
		// 【短信+API接口版本】的无磁无密交易接口测试
		$smCode = "000000";		//测试环境的短信码为 ： 000000
		DemoTestSmsApi::MerchantOrderPayByAccTest($smCode);
		echo "【短信+API接口版本】的无磁无密交易接口调用完成，请到日志文件查询调用结果";
		echo "<br/>";
	}	

}
?>