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
		if (strexists($_SERVER['REQUEST_URI'], '/app/') && !strexists($_SERVER['REQUEST_URI'], 'allProduct') && !strexists($_SERVER['REQUEST_URI'], 'apply')) {
			m('member')->checkMember();
		}
	}
	//付款结果返回
	public function payResult($params){
		global $_W, $_GPC;
		$uniacid=$_W['uniacid'];
		$fee = $params['fee'];
		$openid = m('user')->getOpenid();
		$member = m('member')->getMember($openid);
		$config = $this -> module['config'];
		if ($params['result'] == 'success' && $params['from'] == 'return') {
			$type = substr($params['tid'],0,5);
			if ($type=='10001') {
				//认证付费
				$agent = m('member')->checkAgent($member['id']);
				if ($agent['code'] == 1) {
					message("您已经是会员，请不要重复提交", $this->createMobileUrl('user'), "error");
				}
				$insert = array(
						"uniacid"=>$_W["uniacid"],
						"uid"=>$member['id'],
						"createtime"=>time(),
						"tid"=>$params['tid'],
						"fee"=>$fee,
				);
				pdo_insert("xuan_mixloan_payment", $insert);
				$inviter = m('member')->getInviter($member['phone'], $openid);
				if ($inviter && $config['inviter_fee_one']) {
					$insert_i = array(
						'uniacid' => $_W['uniacid'],
						'uid' => $member['id'],
						'phone' => $member['phone'],
						'certno' => $member['certno'],
						'realname' => $member['realname'],
						'inviter' => $inviter,
						'extra_bonus'=>0,
						'done_bonus'=>0,
						're_bonus'=>$config['inviter_fee_one'],
						'status'=>2,
						'createtime'=>time(),
						'degree'=>1
					);
					pdo_insert('xuan_mixloan_product_apply', $insert_i);
					//二级
					$man_phone = m('member')->getInviterPhone($inviter);
					$inviter = m('member')->getInviter($man_phone);
					if ($inviter && $config['inviter_fee_two']) {
						$insert_i = array(
							'uniacid' => $_W['uniacid'],
							'uid' => $member['id'],
							'phone' => $member['phone'],
							'certno' => $member['certno'],
							'realname' => $member['realname'],
							'inviter' => $inviter,
							'extra_bonus'=>0,
							'done_bonus'=>0,
							're_bonus'=>$config['inviter_fee_two'],
							'status'=>2,
							'createtime'=>time(),
							'degree'=>2
						);
						pdo_insert('xuan_mixloan_product_apply', $insert_i);
						//三级
						$man_phone = m('member')->getInviterPhone($inviter);
						$inviter = m('member')->getInviter($man_phone);
						if ($inviter && $config['inviter_fee_three']) {
							$insert_i = array(
								'uniacid' => $_W['uniacid'],
								'uid' => $member['id'],
								'phone' => $member['phone'],
								'certno' => $member['certno'],
								'realname' => $member['realname'],
								'inviter' => $inviter,
								'extra_bonus'=>0,
								'done_bonus'=>0,
								're_bonus'=>$config['inviter_fee_three'],
								'status'=>2,
								'createtime'=>time(),
								'degree'=>3
							);
							pdo_insert('xuan_mixloan_product_apply', $insert_i);
						}
					}
				}
				message("支付成功", $this->createMobileUrl('user'), "success");
			}
		}
		if (empty($params['result']) || $params['result'] != 'success') {
			//此处会处理一些支付失败的业务代码
			message("出错啦", $this->createMobileUrl('user'), "error");
		}
	}
}