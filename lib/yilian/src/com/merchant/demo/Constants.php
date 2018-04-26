<?php

class Constants {

  //----商户信息：商户根据对接的实际情况对下面数据进行修改； 以下数据在测试通过后，部署到生产环境，需要替换为生产的数据----
  //商户编号，由易联产生，邮件发送给商户
  // private static $MERCHANT_ID = "444420000074";		//内部测试商户号，商户需要替换该参数
  public static $MERCHANT_ID = "002020000008";     //互联网金融行业的商户号
  //商户接收订单通知接口地址（异步通知）；；
  private static  $MERCHANT_NOTIFY_URL = "http://www.xxxxx.com/Notify.do";
  //商户接收订单通知接口地址(同步通知),H5版本对接需要该参数；
  public static $MERCHANT_RETURN_URL = "http://127.0.0.1/phpdemo/ReturnH5.php";
  //商户RSA私钥，商户自己产生（可采用易联提供RSA工具产生）
  private static $MERCHANT_RSA_PRIVATE_KEY = '/www/wwwroot/pl.fuziyo.cn/addons/xuan_mixloan/lib/yilian/key/rsa_private_key.pem';
  //---互联网金融测试商户RSA私钥（从证书文件导出）
  //private static $MERCHANT_RSA_PRIVATE_KEY = "C:\\AppServ\\www\\phpdemo\\key\\rsa_pri_key.pem";  //互联网金融行业

  //----易联信息： 以下信息区分为测试环境和生产环境，商户根据自己对接情况进行数据选择----
  //易联服务器地址_测试环境
  // private static  $PAYECO_URL = "https://testmobile.payeco.com";
  //易联服务器地址_生产环境
  private static $PAYECO_URL = "https://tmobile.payeco.com";

  //订单RSA公钥（易联提供）_测试环境
  private static $PAYECO_RSA_PUBLIC_KEY = '/www/wwwroot/pl.fuziyo.cn/addons/xuan_mixloan/lib/yilian/key/rsa_public_key_product.pem';
  //订单RSA公钥（易联提供）_生产环境
  //private static $PAYECO_RSA_PUBLIC_KEY = "C:\\AppServ\\www\\phpdemo\\key\\rsa_public_key_product.pem";

  static function getMerchantId() {
  	return self::$MERCHANT_ID;
  }
  static function getMerchantNotifyUrl() {
  	return self::$MERCHANT_NOTIFY_URL;
  }
  static function getMerchantReturnUrl() {
  	return self::$MERCHANT_RETURN_URL;
  }
  static function getMerchantRsaPrivateKey() {
  	return self::$MERCHANT_RSA_PRIVATE_KEY;
  }
  static function getPayecoUrl() {
  	return self::$PAYECO_URL;
  }
  static function getPayecoRsaPublicKey() {
  	return self::$PAYECO_RSA_PUBLIC_KEY;
  }
}

?>
