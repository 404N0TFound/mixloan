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
			!strexists($_SERVER['REQUEST_URI'], 'temp'),
            !strexists($_SERVER['REQUEST_URI'], 'login'),
            !strexists($_SERVER['REQUEST_URI'], 'wechat_app'),
            !strexists($_SERVER['REQUEST_URI'], 'getCode'),
            !strexists($_SERVER['REQUEST_URI'], 'temp'),
            !strexists($_SERVER['REQUEST_URI'], 'find_pass'),
            !strexists($_SERVER['REQUEST_URI'], 'notify_url'),
            !strexists($_SERVER['REQUEST_URI'], 'exit'),
            !strexists($_SERVER['REQUEST_URI'], 'do=loan'),
            !strexists($_SERVER['REQUEST_URI'], 'register'),
            !strexists($_SERVER['REQUEST_URI'], 'announce'),
            !strexists($_SERVER['REQUEST_URI'], 'upload_file'),
		);
		foreach ($condition as $value) {
			if ($value == false) {
				$con = false;
				break;
			} else {
                $con = true;
			}
		}
		if (strexists($_SERVER['REQUEST_URI'], 'weixin.rod3bi.cn')) {
			 die("<!DOCTYPE html>
		    <html>
		        <head>
		            <meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'>
		            <title>抱歉，出错了</title><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'><link rel='stylesheet' type='text/css' href='https://res.wx.qq.com/connect/zh_CN/htmledition/style/wap_err1a9853.css'>
		        </head>
		        <body>
		        <div class='page_msg'><div class='inner'><span class='msg_icon_wrp'><i class='icon80_smile'></i></span><div class='msg_content'><h4>请下载最新APP</h4></div></div></div>
		        </body>
		    </html>");
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
		$config = $this -> module['config'];
		if ($params['result'] == 'success') {
			if ($params['from'] == 'notify') {
	            $openid = pdo_fetchcolumn('select openid from '.tablename('core_paylog').'
					where tid=:tid', array(':tid'=>$params['tid']));
	            $member = m('member')->getMember($openid);
			} else {
	            $openid = m('user')->getOpenid();
	            $member = m('member')->getMember($openid);
			}
            if (empty($member['id'])) {
                message('请不要重复提交', $this->createMobileUrl('user'), 'error');
            }
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
				//模板消息提醒
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
		        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
		        $account = WeAccount::create($_W['acid']);
		        $account->sendTplNotice($openid, $config['tpl_notice2'], $datam, $url);
				$inviter = m('member')->getInviter($member['phone'], $member['openid']);
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
					//模板消息提醒
					$one_openid = m('user')->getOpenid($inviter);
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
			                "value" => $config['inviter_fee_one'],
			                "color" => "#173177"
			            ) ,
			            "remark" => array(
			                "value" => '点击查看详情',
			                "color" => "#4a5077"
			            ) ,
			        );
			        $account = WeAccount::create($_W['acid']);
			        $account->sendTplNotice($one_openid, $config['tpl_notice5'], $datam, $url);
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
							'degree'=>2
						);
						pdo_insert('xuan_mixloan_product_apply', $insert_i);
						//模板消息提醒
						$two_openid = m('user')->getOpenid($inviter);
						$datam = array(
				            "first" => array(
				                "value" => "您好，您的徒弟{$man['nickname']}邀请了{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
				                "color" => "#173177"
				            ) ,
				            "order" => array(
				                "value" => $params['tid'],
				                "color" => "#173177"
				            ) ,
				            "money" => array(
				                "value" => $config['inviter_fee_two'],
				                "color" => "#173177"
				            ) ,
				            "remark" => array(
				                "value" => '点击查看详情',
				                "color" => "#4a5077"
				            ) ,
				        );
				        $account = WeAccount::create($_W['acid']);
				        $account->sendTplNotice($two_openid, $config['tpl_notice5'], $datam, $url);
					}
				}
				message("支付成功", $this->createMobileUrl('user'), "success");
			} else if ($type == '30001') {
			    $order = pdo_fetch('select pid from ' . tablename('xuan_mixloan_mall_order') . '
			        where tid=:tid', array(':tid' => $params['tid']));
			    $item = pdo_fetch('select id,ext_info from ' . tablename('xuan_mixloan_mall') . '
			    	where id=:id', array(':id' => $item['id']));
			    $item['ext_info'] = json_decode($item['ext_info'], 1);
				pdo_update('xuan_mixloan_mall_order', array('is_pay'=>1), array('tid'=>$params['tid']));
				$inviter = m('member')->getInviter($member['phone'], $member['openid']);
				if ($inviter && $item['ext_info']['one_bonus']) {
					$insert_i = array(
						'uniacid' => $_W['uniacid'],
						'uid' => $member['id'],
						'phone' => $member['phone'],
						'certno' => $member['certno'],
						'realname' => $member['realname'],
						'inviter' => $inviter,
						'extra_bonus'=>0,
						'done_bonus'=>0,
						're_bonus'=>$item['ext_info']['one_bonus'],
						'status'=>2,
						'createtime'=>time(),
						'degree'=>1,
						'type'=>5
					);
					pdo_insert('xuan_mixloan_product_apply', $insert_i);
				}
		        //二级
				$man_one = m('member')->getInviterInfo($inviter);
				$inviter_two = m('member')->getInviter($man_one['phone'], $man_one['openid']);
				if ($inviter_two && $item['ext_info']['two_bonus']) {
					$insert_i = array(
						'uniacid' => $_W['uniacid'],
						'uid' => $member['id'],
						'phone' => $member['phone'],
						'certno' => $member['certno'],
						'realname' => $member['realname'],
						'inviter' => $inviter_two,
						'extra_bonus'=>0,
						'done_bonus'=>0,
						're_bonus'=>$item['ext_info']['two_bonus'],
						'status'=>2,
						'createtime'=>time(),
						'degree'=>2,
						'type'=>5
					);
					pdo_insert('xuan_mixloan_product_apply', $insert_i);
				}
		        //三级
				$man_two = m('member')->getInviterInfo($inviter_two);
				$inviter_thr = m('member')->getInviter($man_two['phone'], $man_two['openid']);
				if ($inviter_thr && $item['ext_info']['thr_bonus']) {
					$insert_i = array(
						'uniacid' => $_W['uniacid'],
						'uid' => $member['id'],
						'phone' => $member['phone'],
						'certno' => $member['certno'],
						'realname' => $member['realname'],
						'inviter' => $inviter_thr,
						'extra_bonus'=>0,
						'done_bonus'=>0,
						're_bonus'=>$item['ext_info']['thr_bonus'],
						'status'=>2,
						'createtime'=>time(),
						'degree'=>3,
						'type'=>5
					);
					pdo_insert('xuan_mixloan_product_apply', $insert_i);
				}
				message("支付成功", $this->createMobileUrl('mall', array('op' => 'person')), "success");
			}
		}
		if (empty($params['result']) || $params['result'] != 'success') {
			//此处会处理一些支付失败的业务代码
			message("出错啦", $this->createMobileUrl('user'), "error");
		}
	}
}