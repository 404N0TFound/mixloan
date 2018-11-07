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
            !strexists($_SERVER['REQUEST_URI'], 'find_pass'),
            !strexists($_SERVER['REQUEST_URI'], 'notify_url'),
            !strexists($_SERVER['REQUEST_URI'], 'exit'),
            !strexists($_SERVER['REQUEST_URI'], 'do=loan'),
            !strexists($_SERVER['REQUEST_URI'], 'upload_file'),
		);
		foreach ($condition as $value) {
			if ($value == false) {
				$con = false;
				break;
			} else {
                if (strexists($_SERVER['REQUEST_URI'], 'register') && !is_weixin()) {
                    $con = false;
                } else {
                    $con = true;
                }
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
		$config = $this -> module['config'];
		if ($params['result'] == 'success') {
		    $openid = pdo_fetchcolumn('select openid from '.tablename('core_paylog').'
					where tid=:tid', array(':tid'=>$params['tid']));
		    $member = m('member')->getMember($openid);
            if (empty($member['id'])) {
                message('请不要重复提交', $this->createMobileUrl('user'), 'error');
            }
			$type = substr($params['tid'],0,5);
            if ($type=='10001') {
                //购买会员付费
                if (empty($member['id'])) {
                    header("location:{$this->createMobileUrl('user')}");
                }
                $agent = m('member')->checkAgent($member['id']);;
                if ($agent['code'] == 1) {
                    message("您已经是会员，请不要重复提交", $this->createMobileUrl('user'), "error");
                }
                pdo_update("xuan_mixloan_member", array('level'=>$_SESSION['buy_level']), array('id'=>$member['id']));
                $insert = array(
                    "uniacid"=>$_W["uniacid"],
                    "uid"=>$member['id'],
                    "createtime"=>time(),
                    "tid"=>$params['tid'],
                    "fee"=>$fee,
                );
                pdo_insert("xuan_mixloan_payment", $insert);
                //消息提醒
                $ext_info = array('content'=>"您好，您已成功购买会员", 'remark'=>"推广成功奖励丰富，赶快进行推广吧");
                $insert = array(
                    'is_read'=>0,
                    'uid'=>0,
                    'createtime'=>time(),
                    'uniacid'=>$_W['uniacid'],
                    'to_uid'=>$member['id'],
                    'ext_info'=>json_encode($ext_info),
                );
                pdo_insert('xuan_mixloan_msg', $insert);
                $salary_url = $_W['siteroot'] . 'app/' . $this->createMobileUrl('vip', array('op' => 'salary'));
                //一级
                $inviter = m('member')->getInviter($member['phone'], $member['openid']);
                if ($inviter) {
                    $re_bonus = $config['inviter_fee_one'];
                    if ($re_bonus) {
                        $insert_i = array(
                            'uniacid' => $_W['uniacid'],
                            'uid' => $member['id'],
                            'phone' => $member['phone'],
                            'certno' => $member['certno'],
                            'realname' => $member['realname'],
                            'inviter' => $inviter,
                            'extra_bonus'=>0,
                            'done_bonus'=>0,
                            're_bonus'=>$re_bonus,
                            'status'=>2,
                            'createtime'=>time(),
                            'degree'=>1,
                        );
                        pdo_insert('xuan_mixloan_product_apply', $insert_i);
                    }
                    //消息提醒
                    $ext_info = array('content' => "您好，您的徒弟{$member['nickname']}成功购买了代理会员，奖励您推广佣金" . $re_bonus . "元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", "url" => $salary_url);
                    $insert = array(
                        'is_read'=>0,
                        'uid'=>$member['id'],
                        'type'=>2,
                        'createtime'=>time(),
                        'uniacid'=>$_W['uniacid'],
                        'to_uid'=>$inviter,
                        'ext_info'=>json_encode($ext_info),
                    );
                    pdo_insert('xuan_mixloan_msg', $insert);
                    //二级
                    $man_one = m('member')->getInviterInfo($inviter);
                    $inviter_two = m('member')->getInviter($man_one['phone'], $man_one['openid']);
                    if ($inviter_two) {
                        $re_bonus = $config['inviter_fee_two'];
                        if ($re_bonus) {
                            $insert_i = array(
                                'uniacid' => $_W['uniacid'],
                                'uid' => $member['id'],
                                'phone' => $member['phone'],
                                'certno' => $member['certno'],
                                'realname' => $member['realname'],
                                'inviter' => $inviter_two,
                                'extra_bonus'=>0,
                                'done_bonus'=>0,
                                're_bonus'=>$re_bonus,
                                'status'=>2,
                                'createtime'=>time(),
                                'degree'=>2
                            );
                            pdo_insert('xuan_mixloan_product_apply', $insert_i);
                        }
                        //消息提醒
                        $ext_info = array('content' => "您好，您的徒弟{$man_one['nickname']}邀请了{$member['nickname']}成功购买了代理会员，奖励您推广佣金" . $re_bonus . "元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", "url" => $salary_url);
                        $insert = array(
                            'is_read'=>0,
                            'uid'=>$member['id'],
                            'type'=>2,
                            'createtime'=>time(),
                            'uniacid'=>$_W['uniacid'],
                            'to_uid'=>$inviter_two,
                            'ext_info'=>json_encode($ext_info),
                        );
                        pdo_insert('xuan_mixloan_msg', $insert);
                        //三级
                        $man_two = m('member')->getInviterInfo($inviter_two);
                        $inviter_thr = m('member')->getInviter($man_two['phone'], $man_two['openid']);
                        if ($inviter_thr) {
                            $re_bonus = $config['inviter_fee_thr'];
                            if ($re_bonus) {
                                $insert_i = array(
                                    'uniacid' => $_W['uniacid'],
                                    'uid' => $member['id'],
                                    'phone' => $member['phone'],
                                    'certno' => $member['certno'],
                                    'realname' => $member['realname'],
                                    'inviter' => $inviter_thr,
                                    'extra_bonus'=>0,
                                    'done_bonus'=>0,
                                    're_bonus'=>$re_bonus,
                                    'status'=>2,
                                    'createtime'=>time(),
                                    'degree'=>3
                                );
                                pdo_insert('xuan_mixloan_product_apply', $insert_i);
                            }
                            //消息提醒
                            $ext_info = array('content' => "您好，您的徒弟{$man_one['nickname']}邀请了{$member['nickname']}成功购买了代理会员，奖励您推广佣金" . $re_bonus . "元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", "url" => $salary_url);
                            $insert = array(
                                'is_read'=>0,
                                'uid'=>$member['id'],
                                'type'=>2,
                                'createtime'=>time(),
                                'uniacid'=>$_W['uniacid'],
                                'to_uid'=>$inviter_thr,
                                'ext_info'=>json_encode($ext_info),
                            );
                            pdo_insert('xuan_mixloan_msg', $insert);
                        }
                    }
                }
                message("支付成功", $this->createMobileUrl('user'), "success");
            } else if ($type=='10003') {
                //信用查询付费
                $id = $_SESSION['credit_id'];
                if (empty($id)) {
                    message('id失效','','error');
                }
                $item = pdo_fetch('SELECT status FROM ' . tablename('xuan_mixloan_credit_data') . ' WHERE id=:id', array(':id'=>$id));
                if ($item['status'] == 1) {
                	message('请不要重复提交', $this->createMobileUrl('credit', array('op'=>'report_list')), 'success');
                }
                pdo_update("xuan_mixloan_credit_data", array('status'=>1, 'pay_type'=>1), array('id'=>$id));
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
                        'pid'=>-1,
                        'degree'=>1,
                    );
                    pdo_insert('xuan_mixloan_product_apply', $insert_i);
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
                    $inviter = m('member')->getInviter($man['phone'], $man['openid']);
                    $man = m('member')->getInviterInfo($inviter);
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
                            'pid'=>-1,
                            'degree'=>2,
                        );
                        pdo_insert('xuan_mixloan_product_apply', $insert_i);
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
                        $inviter = m('member')->getInviter($man['phone'], $man['openid']);
                        $man = m('member')->getInviterInfo($inviter);
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
                                'pid'=>-1,
                                'degree'=>3,
                            );
                            pdo_insert('xuan_mixloan_product_apply', $insert_i);
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
                message("支付成功", $this->createMobileUrl('credit', array('op'=>'report_info', 'id'=>$id)), "success");
            }
		}
		if (empty($params['result']) || $params['result'] != 'success') {
			//此处会处理一些支付失败的业务代码
			message("出错啦", $this->createMobileUrl('user'), "error");
		}
	}
}