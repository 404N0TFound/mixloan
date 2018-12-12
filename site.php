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
				if (intval($fee) == intval($config['buy_vip_a_price'])) {
					$type = 1;
					$inviter_fee_one = $config['inviter_fee_a_one'];
					$inviter_fee_two = $config['inviter_fee_a_two'];
					$inviter_fee_thr = $config['inviter_fee_a_thr'];
					$inviter_fee_four = $config['inviter_fee_a_four'];
					$inviter_fee_five = $config['inviter_fee_a_five'];
					$inviter_fee_six = $config['inviter_fee_a_six'];
					$inviter_fee_sev = $config['inviter_fee_a_sev'];
				} else if (intval($fee) == intval($config['buy_vip_b_price'])) {
					$type = 2;
					$inviter_fee_one = $config['inviter_fee_b_one'];
					$inviter_fee_two = $config['inviter_fee_b_two'];
					$inviter_fee_thr = $config['inviter_fee_b_thr'];
					$inviter_fee_four = $config['inviter_fee_b_four'];
					$inviter_fee_five = $config['inviter_fee_b_five'];
					$inviter_fee_six = $config['inviter_fee_b_six'];
					$inviter_fee_sev = $config['inviter_fee_b_sev'];
				} else if (intval($fee) == intval($config['buy_vip_c_price'])) {
					$type = 3;
					$inviter_fee_one = $config['inviter_fee_c_one'];
					$inviter_fee_two = $config['inviter_fee_c_two'];
					$inviter_fee_thr = $config['inviter_fee_c_thr'];
					$inviter_fee_four = $config['inviter_fee_c_four'];
					$inviter_fee_five = $config['inviter_fee_c_five'];
					$inviter_fee_six = $config['inviter_fee_c_six'];
					$inviter_fee_sev = $config['inviter_fee_c_sev'];
				}
				$agent = m('member')->checkAgent($member['id']);
				if ($agent['code'] == 1) {
					if ($agent['msg'] == $type) {
						message("您已经是该等级会员了", $this->createMobileUrl('user'), "error");
					}
				}
				$insert = array(
						"uniacid"=>$_W["uniacid"],
						"uid"=>$member['id'],
						"createtime"=>time(),
						"tid"=>$params['tid'],
						"fee"=>$fee,
						"msg"=>$type
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
				$one_inviter = m('member')->getInviter($member['phone'], $member['openid']);
				$record = pdo_fetchcolumn('select count(DISTINCT openid) from ' . tablename('qrcode_stat') . '
							where qrcid=:qrcid and type=1', array(':qrcid' => $one_inviter));
				if ($one_inviter && $inviter_fee_one && $record>=5) {
					$insert_i = array(
						'uniacid' => $_W['uniacid'],
						'uid' => $member['id'],
						'phone' => $member['phone'],
						'certno' => $member['certno'],
						'realname' => $member['realname'],
						'inviter' => $one_inviter,
						'extra_bonus'=>0,
						'done_bonus'=>0,
						're_bonus'=>$inviter_fee_one,
						'status'=>2,
						'createtime'=>time(),
						'degree'=>1,
						'type'=>2
					);
					pdo_insert('xuan_mixloan_product_apply', $insert_i);
				}
				$inviter_one = m('member')->getInviterInfo($one_inviter);
				$two_inviter = m('member')->getInviter($inviter_one['phone'], $inviter_one['openid']);
				$record = pdo_fetchcolumn('select count(DISTINCT openid) from ' . tablename('qrcode_stat') . '
							where qrcid=:qrcid and type=1 ', array(':qrcid' => $two_inviter));
				if ($two_inviter && $inviter_fee_two && $record>=5) {
					$insert_i = array(
						'uniacid' => $_W['uniacid'],
						'uid' => $member['id'],
						'phone' => $member['phone'],
						'certno' => $member['certno'],
						'realname' => $member['realname'],
						'inviter' => $two_inviter,
						'extra_bonus'=>0,
						'done_bonus'=>0,
						're_bonus'=>$inviter_fee_two,
						'status'=>2,
						'createtime'=>time(),
						'degree'=>2,
						'type'=>2
					);
					pdo_insert('xuan_mixloan_product_apply', $insert_i);
				}
				$inviter_two = m('member')->getInviterInfo($two_inviter);
				$thr_inviter = m('member')->getInviter($inviter_two['phone'], $inviter_two['openid']);
				$record = pdo_fetchcolumn('select count(DISTINCT openid) from ' . tablename('qrcode_stat') . '
							where qrcid=:qrcid and type=1 ', array(':qrcid' => $thr_inviter));
				if ($thr_inviter && $inviter_fee_thr && $record>=5) {
					$insert_i = array(
						'uniacid' => $_W['uniacid'],
						'uid' => $member['id'],
						'phone' => $member['phone'],
						'certno' => $member['certno'],
						'realname' => $member['realname'],
						'inviter' => $thr_inviter,
						'extra_bonus'=>0,
						'done_bonus'=>0,
						're_bonus'=>$inviter_fee_thr,
						'status'=>2,
						'createtime'=>time(),
						'degree'=>3,
						'type'=>2
					);
					pdo_insert('xuan_mixloan_product_apply', $insert_i);
				}
				$inviter_thr = m('member')->getInviterInfo($thr_inviter);
				$four_inviter = m('member')->getInviter($inviter_thr['phone'], $inviter_thr['openid']);
				$record = pdo_fetchcolumn('select count(DISTINCT openid) from ' . tablename('qrcode_stat') . '
							where qrcid=:qrcid and type=1 ', array(':qrcid' => $four_inviter));
				if ($four_inviter && $inviter_fee_four && $record>=5) {
					$insert_i = array(
						'uniacid' => $_W['uniacid'],
						'uid' => $member['id'],
						'phone' => $member['phone'],
						'certno' => $member['certno'],
						'realname' => $member['realname'],
						'inviter' => $four_inviter,
						'extra_bonus'=>0,
						'done_bonus'=>0,
						're_bonus'=>$inviter_fee_four,
						'status'=>2,
						'createtime'=>time(),
						'degree'=>4,
						'type'=>2
					);
					pdo_insert('xuan_mixloan_product_apply', $insert_i);
				}
				$inviter_four = m('member')->getInviterInfo($four_inviter);
				$five_inviter = m('member')->getInviter($inviter_four['phone'], $inviter_four['openid']);
				$record = pdo_fetchcolumn('select count(DISTINCT openid) from ' . tablename('qrcode_stat') . '
							where qrcid=:qrcid and type=1 ', array(':qrcid' => $five_inviter));
				if ($five_inviter && $inviter_fee_five && $record>=5) {
					$insert_i = array(
						'uniacid' => $_W['uniacid'],
						'uid' => $member['id'],
						'phone' => $member['phone'],
						'certno' => $member['certno'],
						'realname' => $member['realname'],
						'inviter' => $five_inviter,
						'extra_bonus'=>0,
						'done_bonus'=>0,
						're_bonus'=>$inviter_fee_five,
						'status'=>2,
						'createtime'=>time(),
						'degree'=>5,
						'type'=>2
					);
					pdo_insert('xuan_mixloan_product_apply', $insert_i);
				}
				$inviter_five = m('member')->getInviterInfo($five_inviter);
				$six_inviter = m('member')->getInviter($inviter_five['phone'], $inviter_five['openid']);
				$record = pdo_fetchcolumn('select count(DISTINCT openid) from ' . tablename('qrcode_stat') . '
							where qrcid=:qrcid and type=1 ', array(':qrcid' => $six_inviter));
				if ($six_inviter && $inviter_fee_six && $record>=5) {
					$insert_i = array(
						'uniacid' => $_W['uniacid'],
						'uid' => $member['id'],
						'phone' => $member['phone'],
						'certno' => $member['certno'],
						'realname' => $member['realname'],
						'inviter' => $six_inviter,
						'extra_bonus'=>0,
						'done_bonus'=>0,
						're_bonus'=>$inviter_fee_six,
						'status'=>2,
						'createtime'=>time(),
						'degree'=>6,
						'type'=>2
					);
					pdo_insert('xuan_mixloan_product_apply', $insert_i);
				}
				$inviter_six = m('member')->getInviterInfo($six_inviter);
				$sev_inviter = m('member')->getInviter($inviter_six['phone'], $inviter_six['openid']);
				$record = pdo_fetchcolumn('select count(DISTINCT openid) from ' . tablename('qrcode_stat') . '
							where qrcid=:qrcid and type=1 ', array(':qrcid' => $sev_inviter));
				if ($sev_inviter && $inviter_fee_sev && $record>=5) {
					$insert_i = array(
						'uniacid' => $_W['uniacid'],
						'uid' => $member['id'],
						'phone' => $member['phone'],
						'certno' => $member['certno'],
						'realname' => $member['realname'],
						'inviter' => $sev_inviter,
						'extra_bonus'=>0,
						'done_bonus'=>0,
						're_bonus'=>$inviter_fee_sev,
						'status'=>2,
						'createtime'=>time(),
						'degree'=>7,
						'type'=>2
					);
					pdo_insert('xuan_mixloan_product_apply', $insert_i);
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