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
            !strexists($_SERVER['REQUEST_URI'], 'login'),
            !strexists($_SERVER['REQUEST_URI'], 'wechat_app'),
            !strexists($_SERVER['REQUEST_URI'], 'getCode'),
            !strexists($_SERVER['REQUEST_URI'], 'temp'),
            !strexists($_SERVER['REQUEST_URI'], 'find_pass'),
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
		if ($params['result'] == 'success') {
			if ($params['from']=='notify') {
				$user_id = pdo_fetchcolumn('select openid from '.tablename('core_paylog').'
					where tid=:tid', array(':tid'=>$params['tid']));
                if (intval($user_id) == $user_id) {
                    $openid = pdo_fetchcolumn('select openid from '.tablename('xuan_mixloan_member').'
                    where id=:id', array(':id'=>$user_id));
                } else {
                    $openid = $user_id;
                }
				$member = m('member')->getMember($openid);
			}
			if (empty($openid)) {
				message('请不要重复提交', $this->createMobileUrl('user'), 'error');
			}
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
				//模板消息提醒
		        $account = WeAccount::create($_W['acid']);
		        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
				$datam = array(
		            "first" => array(
		                "value" => "您好，您已购买成功",
		                "color" => "#173177"
		            ) ,
		            "name" => array(
		                "value" => "{$config['title']}代理会员",
		                "color" => "#173177"
		            ) ,
		            "remark" => array(
		                "value" => '点击查看详情',
		                "color" => "#4a5077"
		            ) ,
		        );
		        $account->sendTplNotice($openid, $config['tpl_notice2'], $datam, $url);
				$inviter = m('member')->getInviter($member['phone'], $openid);
				$man = m('member')->getInviterInfo($inviter);
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
						're_bonus'=>$config['inviter_fee_one']*$fee*0.01,
						'status'=>2,
						'createtime'=>time(),
						'degree'=>1,
						'type'=>2
					);
					pdo_insert('xuan_mixloan_bonus', $insert_i);
					$datam = array(
			            "first" => array(
			                "value" => "您好，您的徒弟{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
			                "color" => "#173177"
			            ) ,
			            "order" => array(
			                "value" => $params['tid'],
			                "color" => "#173177"
			            ) ,
			            "money" => array(
			                "value" => $config['inviter_fee_one']*$fee*0.01,
			                "color" => "#173177"
			            ) ,
			            "remark" => array(
			                "value" => '点击查看详情',
			                "color" => "#4a5077"
			            ) ,
			        );
			        $account->sendTplNotice($man['openid'], $config['tpl_notice5'], $datam, $url);
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
							're_bonus'=>$config['inviter_fee_two']*$fee*0.01,
							'status'=>2,
							'createtime'=>time(),
							'degree'=>2,
							'type'=>2
						);
						pdo_insert('xuan_mixloan_bonus', $insert_i);
						$datam = array(
				            "first" => array(
				                "value" => "您好，您的徒弟{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
				                "color" => "#173177"
				            ) ,
				            "order" => array(
				                "value" => $params['tid'],
				                "color" => "#173177"
				            ) ,
				            "money" => array(
				                "value" => $config['inviter_fee_two']*$fee*0.01,
				                "color" => "#173177"
				            ) ,
				            "remark" => array(
				                "value" => '点击查看详情',
				                "color" => "#4a5077"
				            ) ,
				        );
				        $account->sendTplNotice($man['openid'], $config['tpl_notice5'], $datam, $url);
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
								're_bonus'=>$config['inviter_fee_three']*$fee*0.01,
								'status'=>2,
								'createtime'=>time(),
								'degree'=>3,
								'type'=>2
							);
							pdo_insert('xuan_mixloan_bonus', $insert_i);
							$datam = array(
					            "first" => array(
					                "value" => "您好，您的徒弟{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
					                "color" => "#173177"
					            ) ,
					            "order" => array(
					                "value" => $params['tid'],
					                "color" => "#173177"
					            ) ,
					            "money" => array(
					                "value" => $config['inviter_fee_three']*$fee*0.01,
					                "color" => "#173177"
					            ) ,
					            "remark" => array(
					                "value" => '点击查看详情',
					                "color" => "#4a5077"
					            ) ,
					        );
					        $account->sendTplNotice($man['openid'], $config['tpl_notice5'], $datam, $url);
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
		        $account = WeAccount::create($_W['acid']);
		        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
				$man = m('member')->getInviterInfo($inviter);
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
					$datam = array(
			            "first" => array(
			                "value" => "您好，您的徒弟{$member['nickname']}成功购买了口子文章，奖励您推广佣金，继续推荐口子文章，即可获得更多佣金奖励",
			                "color" => "#173177"
			            ) ,
			            "order" => array(
			                "value" => $params['tid'],
			                "color" => "#173177"
			            ) ,
			            "money" => array(
			                "value" => $fee_back,
			                "color" => "#173177"
			            ) ,
			            "remark" => array(
			                "value" => '点击查看详情',
			                "color" => "#4a5077"
			            ) ,
			        );
			        $account->sendTplNotice($man['openid'], $config['tpl_notice5'], $datam, $url);
				}
				message("支付成功", $this->createMobileUrl('channel', array('op'=>'artical', 'id'=>$cid)), "success");
			} else if ($type=='10003') {
				//信用查询付费
				$id = $_SESSION['credit_id'];
				if (empty($id)) {
					message('id失效','','error');
				}
				pdo_update("xuan_mixloan_credit_data", array('status'=>1, 'pay_type'=>1, 'fee'=>$fee), array('id'=>$id));
				//模板消息提醒
		        $account = WeAccount::create($_W['acid']);
		        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('credit', array('op'=>'report_list'));
				$datam = array(
		            "first" => array(
		                "value" => "您好，您已付费成功",
		                "color" => "#173177"
		            ) ,
		            "name" => array(
		                "value" => "{$config['title']}信用查询",
		                "color" => "#173177"
		            ) ,
		            "remark" => array(
		                "value" => '点击查看详情',
		                "color" => "#4a5077"
		            ) ,
		        );
		        $account->sendTplNotice($openid, $config['tpl_notice2'], $datam, $url);
		        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
				$inviter = m('member')->getInviter($member['phone'], $openid);
				$man = m('member')->getInviterInfo($inviter);
				if ($inviter && $config['credit_fee_one']) {
					$insert_i = array(
						'uniacid' => $_W['uniacid'],
						'uid' => $member['id'],
						'phone' => $member['phone'],
						'certno' => $member['certno'],
						'realname' => $member['realname'],
						'inviter' => $inviter,
						'extra_bonus'=>$config['credit_fee_one'],
						'done_bonus'=>0,
						're_bonus'=>0,
						'status'=>2,
						'createtime'=>time(),
						'degree'=>1,
						'type'=>4
					);
					pdo_insert('xuan_mixloan_bonus', $insert_i);
					$datam = array(
			            "first" => array(
			                "value" => "您好，您的一级徒弟{$member['nickname']}成功付费了信用查询，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
			                "color" => "#173177"
			            ) ,
			            "order" => array(
			                "value" => $params['tid'],
			                "color" => "#173177"
			            ) ,
			            "money" => array(
			                "value" => $config['credit_fee_one'],
			                "color" => "#173177"
			            ) ,
			            "remark" => array(
			                "value" => '点击查看详情',
			                "color" => "#4a5077"
			            ) ,
			        );
			        $account->sendTplNotice($man['openid'], $config['tpl_notice5'], $datam, $url);
					//二级
					$man = m('member')->getInviterInfo($inviter);
					$inviter = m('member')->getInviter($man['phone'], $man['openid']);
					if ($inviter && $config['credit_fee_two']) {
						$insert_i = array(
							'uniacid' => $_W['uniacid'],
							'uid' => $member['id'],
							'phone' => $member['phone'],
							'certno' => $member['certno'],
							'realname' => $member['realname'],
							'inviter' => $inviter,
							'extra_bonus'=>$config['credit_fee_two'],
							'done_bonus'=>0,
							're_bonus'=>0,
							'status'=>2,
							'createtime'=>time(),
							'degree'=>2,
							'type'=>4
						);
						pdo_insert('xuan_mixloan_bonus', $insert_i);
						$datam = array(
				            "first" => array(
				                "value" => "您好，您的二级徒弟{$member['nickname']}成功付费了信用查询，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
				                "color" => "#173177"
				            ) ,
				            "order" => array(
				                "value" => $params['tid'],
				                "color" => "#173177"
				            ) ,
				            "money" => array(
				                "value" => $config['credit_fee_two'],
				                "color" => "#173177"
				            ) ,
				            "remark" => array(
				                "value" => '点击查看详情',
				                "color" => "#4a5077"
				            ) ,
				        );
				        $account->sendTplNotice($man['openid'], $config['tpl_notice5'], $datam, $url);
						//三级
						$man = m('member')->getInviterInfo($inviter);
						$inviter = m('member')->getInviter($man['phone'], $man['openid']);
						if ($inviter && $config['credit_fee_three']) {
							$insert_i = array(
								'uniacid' => $_W['uniacid'],
								'uid' => $member['id'],
								'phone' => $member['phone'],
								'certno' => $member['certno'],
								'realname' => $member['realname'],
								'inviter' => $inviter,
								'extra_bonus'=>$config['credit_fee_three'],
								'done_bonus'=>0,
								're_bonus'=>0,
								'status'=>2,
								'createtime'=>time(),
								'degree'=>3,
								'type'=>4
							);
							pdo_insert('xuan_mixloan_bonus', $insert_i);
							$datam = array(
					            "first" => array(
					                "value" => "您好，您的徒弟{$member['nickname']}成功付费了信用查询，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
					                "color" => "#173177"
					            ) ,
					            "order" => array(
					                "value" => $params['tid'],
					                "color" => "#173177"
					            ) ,
					            "money" => array(
					                "value" => $config['credit_fee_three'],
					                "color" => "#173177"
					            ) ,
					            "remark" => array(
					                "value" => '点击查看详情',
					                "color" => "#4a5077"
					            ) ,
					        );
					        $account->sendTplNotice($man['openid'], $config['tpl_notice5'], $datam, $url);
						}
					}
				}
				message("支付成功", $this->createMobileUrl('credit', array('op'=>'report_list')), "success");
			}
		}

		if (empty($params['result']) || $params['result'] != 'success') {
			//此处会处理一些支付失败的业务代码
			message("出错啦", $this->createMobileUrl('user'), "error");
		}
	}
}