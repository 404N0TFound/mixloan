<?php

/**
 * 各版本通用接口测试demo,分别对以下接口进行测试
 * 1、订单查询接口（OrderQueryTest）
 * 2、订单冲正接口（OrderReverseTest）
 * 3、订单退款申请接口（OrderRefundReqTest）
 * 4、订单退款查询接口（OrderRefundQueryTest）
 * 5、互联网金融行业银行卡解除绑定接口（UnboundBankCardTest）
 */

require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/Constants.php';
require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Xml.php';
require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/client/TransactionClient.php';

class DemoTest {
	/**
	 * 订单查询接口测试
	 */
	static function OrderQueryTest($orderId) {
		// 设置参数
		$merchantId = Constants::getMerchantId();
		$merchOrderId = $orderId;   //需要填写已经存在的商户订单号
		$tradeTime = Tools::getSysTime();

		// 调用查询接口
		Log::logFile("-------订单查询接口测试-------------------------");
		Log::logFile("orderId=".$orderId);
		try {
			$retXml = new Xml();
			//接口参数请参考TransactionClient的参数说明
			$ret = TransactionClient::OrderQuery($merchantId, $merchOrderId, $tradeTime,
					Constants::getMerchantRsaPrivateKey(), Constants::getPayecoRsaPublicKey(), 
					Constants::getPayecoUrl(), $retXml);
			if(strcmp("0000", $ret)){
				Log::logFile("商户下单接口测试失败！：retCode=".$ret."; msg=".$retXml->getRetMsg());
				return;
			}
		} catch (Exception $e) {
			Log::logFile("订单查询接口测试失败！：".$e->getMessage());
			return;
		}
		Log::logFile("订单查询接口测试----ok");
		Log::logFile("------------------------------------------------");
	}
	
	/**
	 * 订单冲正接口测试
	 */
	static function OrderReverseTest($orderId, $amt) {
		// 设置参数
		$amount = $amt;
		$merchantId = Constants::getMerchantId();
		$merchOrderId = $orderId;  //需要填写已经存在的商户订单号
		$tradeTime = Tools::getSysTime();

		// 调用冲正接口
		Log::logFile("-------订单冲正接口测试-------------------------");
		Log::logFile("orderId=".$orderId);
		try {
			$retXml = new Xml();
			//接口参数请参考TransactionClient的参数说明
			$ret = TransactionClient::OrderReverse($merchantId, $merchOrderId, $amount, $tradeTime, 
					Constants::getMerchantRsaPrivateKey(), Constants::getPayecoRsaPublicKey(),
					Constants::getPayecoUrl(), $retXml);
			if(strcmp("0000", $ret)){
				Log::logFile("商户下单接口测试失败！：retCode=".$ret."; msg=".$retXml->getRetMsg());
				return;
			}
		} catch (Exception $e) {
			Log::logFile("订单冲正接口测试失败！：".$e->getMessage());
			return;
		}
		Log::logFile("订单冲正接口测试----ok");
		Log::logFile("------------------------------------------------");
	}
	
	/**
	 * 订单退款申请接口测试
	 */
	static function OrderRefundReqTest($orderId, $amt) {
		// 设置参数
		$amount = $amt;
		$merchantId = Constants::getMerchantId();
		$merchOrderId = $orderId;  //需要填写已经存在的商户订单号
		$merchRefundId = Tools::currentTimeMillis(); // 退款申请号
		$tradeTime = Tools::getSysTime();
	
		// 调用冲正接口
		Log::logFile("-------订单退款申请接口测试-------------------------");
		try {
			$retXml = new Xml();
			
			//接口参数请参考TransactionClient的参数说明
			$ret = TransactionClient::OrderRefundReq($merchantId, $merchOrderId, $merchRefundId, $amount, $tradeTime,
							Constants::getMerchantRsaPrivateKey(), Constants::getPayecoRsaPublicKey(), 
							Constants::getPayecoUrl(), $retXml);
			if(strcmp("0000", $ret)){
				Log::logFile("订单退款申请接口测试失败！：retCode=".$ret."; msg=".$retXml->getRetMsg());
				return;
			}
		} catch (Exception $e) {
			Log::logFile("订单退款申请接口测试失败！：".$e->getMessage());
			return;
		}
		Log::logFile("订单退款申请接口测试----ok");
		Log::logFile("------------------------------------------------");
	}
	
	/**
	 * 订单退款结果查询接口测试
	 */
	static function OrderRefundQueryTest($orderId, $refundId) {
		// 设置参数
		$merchantId = Constants::getMerchantId();
		$merchOrderId = $orderId;  //需要填写已经存在的商户订单号
		$merchRefundId = $refundId; // 退款申请号
		$tradeTime = Tools::getSysTime();
	
		// 调用冲正接口
		Log::logFile("-------订单退款结果查询接口测试-------------------------");
		try {
			$retXml = new Xml();
			//接口参数请参考TransactionClient的参数说明
			$ret = TransactionClient::OrderRefundQuery($merchantId, $merchOrderId, $merchRefundId, $tradeTime,
					Constants::getMerchantRsaPrivateKey(), Constants::getPayecoRsaPublicKey(), 
					Constants::getPayecoUrl(), $retXml);
			if(strcmp("0000", $ret)){
				Log::logFile("订单退款结果查询接口测试失败！：retCode=".$ret."; msg=".$retXml->getRetMsg());
				return;
			}
		} catch (Exception $e) {
			Log::logFile("订单退款结果查询接口测试失败！：".$e->getMessage());
			return;
		}
		Log::logFile("订单退款结果查询接口测试----ok");
		Log::logFile("------------------------------------------------");
	}
	
	////==================以下接口为特殊行业或定制化的接口========================
	/**
	 * 互联网金融行业银行卡解除绑定接口测试
	 */
	static function UnboundBankCardTest($bankAccNo) {
		// 设置参数
		$merchantId = Constants::getMerchantId();
		$tradeTime = Tools::getSysTime();
	
		// 调用查询接口
		Log::logFile("-------互联网金融行业银行卡解除绑定接口测试-------------------------");
		try {
			$retXml = new Xml();
			//接口参数请参考TransactionClient的参数说明
			$ret = TransactionClient::UnboundBankCard($merchantId, $bankAccNo, $tradeTime,
					Constants::getMerchantRsaPrivateKey(), Constants::getPayecoRsaPublicKey(), 
					Constants::getPayecoUrl(), $retXml);
			if(strcmp("0000", $ret)){
				Log::logFile("互联网金融行业银行卡解除绑定接口测试失败！：retCode=".$ret."; msg=".$retXml->getRetMsg());
				return;
			}
			Log::logFile("Status=".$retXml->getStatus());
		} catch (Exception $e) {
			Log::logFile("互联网金融行业银行卡解除绑定接口测试失败！：".$e->getMessage());
			return;
		}
		Log::logFile("互联网金融行业银行卡解除绑定接口测试----ok;");
		Log::logFile("------------------------------------------------");
	}
	
	static function Test() {
		//设置打印日志
		Log::setLogFlag(true);
		
		$orderId = "1445935379582"; //填写已经存在成功下单的商户订单号
		DemoTest::OrderQueryTest($orderId);
		echo "订单查询接口调用完成，请到日志文件查询调用结果";
		echo "<br/>";
		
		// 订单冲正接口测试
		$orderId = "1445935379582"; //填写已经存在成功下单的商户订单号，只有支付成功才能冲正
		$amount = "25";	   //冲正额金额需要和订单金额一样
// 		DemoTest::OrderReverseTest($orderId, $amount);
// 		echo "订单冲正接口调用完成，请到日志文件查询调用结果";
// 		echo "<br/>";
		
		// 订单退款申请接口测试
// 		$orderId = "1446610513853"; //填写已经存在成功下单的商户订单号，只有支付成功才能退款申请
// 		$amount = "1";	   //退款的金额不能大于订单金额
// 		DemoTest::OrderRefundReqTest($orderId, $amount);
// 		echo "订单退款申请接口调用完成，请到日志文件查询调用结果";
// 		echo "<br/>";
		
		// 订单退款结果查询接口测试
// 		$orderId = "1446610513853"; //填写已经存在成功下单的商户订单号，只有支付成功才能退款申请
// 		$refundId = "1446718774394";      //退款申请号，需要填写退款申请时提交的申请号，退款结果需要在申请的隔天后才能查询到结果
// 		DemoTest::OrderRefundQueryTest($orderId, $refundId);
// 		echo "订单退款结果查询接口调用完成，请到日志文件查询调用结果";
// 		echo "<br/>";
		
		//互联网金融行业银行卡解除绑定接口测试
// 		$bankAccNo="6227003324126059872";
// 		DemoTest::UnboundBankCardTest($bankAccNo);		
// 		echo "互联网金融行业银行卡解除绑定接口调用完成，请到日志文件查询调用结果";
// 		echo "<br/>";
	}
}
?>