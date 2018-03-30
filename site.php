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
		$condition =  array(
			strexists($_SERVER['REQUEST_URI'], '/app/'),
			!strexists($_SERVER['REQUEST_URI'], 'allProduct'),
			!strexists($_SERVER['REQUEST_URI'], 'apply'),
			!strexists($_SERVER['REQUEST_URI'], 'queue'),
		);
		foreach ($condition as $value) {
			if ($value == false) {
				$con = false;
				break;
			} else {
				$con = true;
			}
		}
		if ($con) {
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
				$agent = m('member')->checkAgent($member['id'], $config);
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
				if ($fee == $config['buy_init_vip_price']) {
					pdo_update("xuan_mixloan_member", array('level'=>1), array('id'=>$member['id']));
				} else {
					pdo_update("xuan_mixloan_member", array('level'=>2), array('id'=>$member['id']));
				}
				$inviter = m('member')->getInviter($member['phone'], $openid);
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
						're_bonus'=>$config['inviter_fee_one'],
						'status'=>2,
						'createtime'=>time(),
						'degree'=>1,
						'type'=>2
					);
					pdo_insert('xuan_mixloan_bonus', $insert_i);
					//二级
					$man = m('member')->getInviterInfo($inviter);
					$inviter = m('member')->getInviter($man['phone'], $man['openid']);
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
							'degree'=>2,
							'type'=>2
						);
						pdo_insert('xuan_mixloan_bonus', $insert_i);
						//三级
						$man = m('member')->getInviterInfo($inviter);
						$inviter = m('member')->getInviter($man['phone'], $man['openid']);
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
								'degree'=>3,
								'type'=>2
							);
							pdo_insert('xuan_mixloan_bonus', $insert_i);
						}
					}
				}
				message("支付成功", $this->createMobileUrl('user'), "success");
			} else if ($type == '10002') {
				if (empty($_SESSION['channel_id'])) {
					message("发起支付失效，请重新支付", "", "error");
				}
				$cid = (int)$_SESSION['channel_id'];
				$is_pay = m('channel')->checkPayArtical($cid, $member['id']);
				if ($is_pay) {
					message("您已经购买过此文章，无需再次购买", "", "error");
				}
				$insert = array(
					'uid'=>$member['id'],
					'cid'=>$cid,
					'uniacid'=>$_W['uniacid'],
					'createtime'=>time(),
					'tid'=>$params['tid'],
					'fee'=>$fee,
				);
				pdo_insert('xuan_mixloan_channel_pay', $insert);
				$ext_info = pdo_fetchcolumn('select ext_info from '.tablename('xuan_mixloan_channel').' where id=:id', array(':id'=>$cid));
				$ext_info = json_decode($ext_info, 1);
				$inviter = m('member')->getInviter($member['phone'], $member['openid']);
				$inviter_agent = m('member')->checkAgent($inviter);
				if ($inviter_agent['level'] == 1) {
					$fee_back = $ext_info['init_fee_back'] * 0.01 * $fee;
				} else if ($inviter_agent['level'] == 2) {
					$fee_back = $ext_info['mid_fee_back'] * 0.01 * $fee;
				}
				if ($inviter && $fee_back) {
					$insert_i = array(
						'uniacid' => $_W['uniacid'],
						'uid' => $member['id'],
						'phone' => $member['phone'],
						'certno' => $member['certno'],
						'realname' => $member['realname'],
						'inviter' => $inviter,
						'extra_bonus'=>$fee_back,
						'done_bonus'=>0,
						're_bonus'=>0,
						'status'=>2,
						'createtime'=>time(),
						'degree'=>1,
						'type'=>3,
						'relate_id'=>$cid
					);
					pdo_insert('xuan_mixloan_bonus', $insert_i);
				}
				message("支付成功", $this->createMobileUrl('channel', array('op'=>'artical', 'id'=>$cid)), "success");
			}
		}
		if (empty($params['result']) || $params['result'] != 'success') {
			//此处会处理一些支付失败的业务代码
			message("出错啦", $this->createMobileUrl('user'), "error");
		}
	}
}