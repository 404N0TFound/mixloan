<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
define('XUAN_MIXLOAN_DEBUG', false);
!defined('XUAN_MIXLOAN_PATH') && define('XUAN_MIXLOAN_PATH', IA_ROOT . '/addons/xuan_mixloan/');
!defined('XUAN_MIXLOAN_INC') && define('XUAN_MIXLOAN_INC', XUAN_MIXLOAN_PATH . 'inc/');
!defined('MODULE_NAME') && define('MODULE_NAME','xuan_mixloan');
!defined('STYLE_PATH') && define('STYLE_PATH', '../addons/'.MODULE_NAME.'/template/style/');
!defined('NEW_PATH') && define('NEW_PATH', STYLE_PATH.'new/');
!defined('CSS_PATH') && define('CSS_PATH', STYLE_PATH.'css/');
!defined('JS_PATH') && define('JS_PATH', STYLE_PATH.'js/');
!defined('IMG_PATH') && define('IMG_PATH', STYLE_PATH.'images/');
!defined('PIC_PATH') && define('PIC_PATH', STYLE_PATH.'picture/');
require_once XUAN_MIXLOAN_INC.'functions.php'; 
class Xuan_mixloanModuleSite extends WeModuleSite {
	public function __construct(){
	}
	//付款结果返回
	public function payResult($params){
		global $_W, $_GPC;
		$uniacid=$_W['uniacid'];
		$fee = $params['fee'];
		$openid = m('user')->getOpenid();
		$member = m('member')->getMember($openid);
		$config = $this -> module['config'];
		if ($params['result'] == 'success') {
            if ($params['from']=='notify') {
                $user_id = pdo_fetchcolumn('select openid from '.tablename('core_paylog').'
					where tid=:tid', array(':tid'=>$params['tid']));
                $openid = pdo_fetchcolumn('select openid from '.tablename('xuan_mixloan_member').'
					where id=:id', array(':id'=>$user_id));
                $member = m('member')->getMember($openid);
            }
            if (empty($openid)) {
                message('请不要重复提交', $this->createMobileUrl('user'), 'error');
            }
			$type = substr($params['tid'],0,5);
			
		}
		if (empty($params['result']) || $params['result'] != 'success') {
			//此处会处理一些支付失败的业务代码
			message("出错啦", $this->createMobileUrl('user'), "error");
		}
	}
}