<?php
	header("Content-Type:text/html; charset=utf-8");

	require_once 'src/com/payeco/tools/HttpClient.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Log.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Signatory.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Tools.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/tools/Xml.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/payeco/client/ConstantsClient.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/Constants.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/DemoTest.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/DemoTestH5.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/DemoTestSdk.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/DemoTestSmsApi.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/DemoTestSmsH5.php';
	require_once IA_ROOT . '/addons/xuan_mixloan/lib/yilian/src/com/merchant/demo/DemoTestApi.php';
	
	Log::setLogFlag(true);
// 	DemoTest::Test();
// 	DemoTestH5::Test();
// 	DemoTestSdk::Test();
// 	DemoTestSmsApi::Test();
	DemoTestSmsH5::Test();
// 	DemoTestApi::Test();
	echo 'DemoTest ok';
?>

