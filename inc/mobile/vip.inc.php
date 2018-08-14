<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$agent = m('member')->checkAgent($member['id'], $config);
if($operation=='buy'){
	//购买会员
	if (!$member['phone']) {
		message('请先绑定手机号', $this->createMobileUrl('index'), 'error');
	}
	if ($agent['code']==1) {
		$verify = 1;
		if ($agent['level'] == 1) {
			$upgrade['mid_vip_fee'] = $config['mid_vip_fee'] - $config['init_vip_fee'];
			$upgrade['height_vip_fee'] = $config['height_vip_fee'] - $config['init_vip_fee'];
		} else if ($agent['level'] == 2) {
			$upgrade['height_vip_fee'] = $config['height_vip_fee'] - $config['mid_vip_fee'];
		}
	} else {
		$verify = 0;
	}
	include $this->template('vip/buy');
} else if ($operation == 'pay') {
	//付钱
    if ($_GPC['type'] == 1) {
        $fee = $config['init_vip_fee'];
        $_SESSION['buy_level'] = 1;
    } else if ($_GPC['type'] == 2) {
        $fee = $config['mid_vip_fee'];
        $_SESSION['buy_level'] = 2;
    } else if ($_GPC['type'] == 3) {
        $fee = $config['height_vip_fee'];
        $_SESSION['buy_level'] = 3;
    }
    if ($member['id'] == 1) {
    	$fee = 0.01;
    }
    if (is_weixin()) {
        $tid = "10001" . date('YmdHis', time());
        $title = "购买{$config['title']}代理会员";
        $params = array(
            'tid' => $tid,
            'ordersn' => $tid,
            'title' => $title,
            'fee' => $fee,
            'user' => $member['id'],
        );
        //调用pay方法
        $this->pay($params);
    } else {
        $notify_url = 'https://wx.wyhrkj.com/addons/xuan_mixloan/lib/wechat/payResult.php';
        $record = pdo_fetch('select * from ' .tablename('xuan_mixloan_paylog'). '
		    where type=1 and is_pay=0 and uid=:uid order by id desc', array(':uid'=>$member['id']));
        if (empty($record)) {
            $tid = "10001" . date('YmdHis', time());
            $trade_no = "ZML".date("YmdHis");
            $insert = array(
                'notify_id'=>$trade_no,
                'tid'=>$tid,
                'createtime'=>time(),
                'uid'=>$member['id'],
                'uniacid'=>$_W['uniacid'],
                'fee'=>$fee,
                'is_pay'=>0,
                'type'=>1,
                'level'=>$_SESSION['buy_level']
            );
            pdo_insert('xuan_mixloan_paylog', $insert);
        } else {
            if ($record['createtime']+60 < time())
            {
                //超过1分钟重新发起订单
                $tid = "10001" . date('YmdHis', time());
                $trade_no = "ZML".date("YmdHis");
                $insert = array(
                    'notify_id'=>$trade_no,
                    'tid'=>$tid,
                    'createtime'=>time(),
                    'uid'=>$member['id'],
                    'uniacid'=>$_W['uniacid'],
                    'fee'=>$fee,
                    'is_pay'=>0,
                    'type'=>1,
                	'level'=>$_SESSION['buy_level']
                );
                pdo_insert('xuan_mixloan_paylog', $insert);
            }
            else
            {
                $trade_no = $record['notify_id'];
            }
        }
        $result = m('pay')->H5pay($trade_no, $fee, $notify_url);
        if ($result['code'] == 1) {
            $redirect_url = urlencode($_W['siteroot'] . 'app/' .
                $this->createMobileUrl('vip', array('op'=>'checkPay')));
            $url = "{$result['data']['url']}&redirect_url={$redirect_url}";
        } else {
            message('请稍后再试', $this->createMobileUrl('user'), 'error');
        }
        include $this->template('vip/openHref');
    }
	exit;
} else if ($operation == 'upgrade') {
	//升级会员
	if ($agent['code']!=1) {
		message('您还不是会员','','error');
	}
	if ($agent['level'] == 1) {
		$upgrade['mid_vip_fee'] = $config['mid_vip_fee'] - $config['init_vip_fee'];
		$upgrade['height_vip_fee'] = $config['height_vip_fee'] - $config['init_vip_fee'];
	} else if ($agent['level'] == 2) {
		$upgrade['height_vip_fee'] = $config['height_vip_fee'] - $config['mid_vip_fee'];
	}
	if ($_GPC['type'] == 2) {
		$fee = $upgrade['mid_vip_fee'];
	} elseif ($_GPC['type'] == 3) {
		$fee = $upgrade['height_vip_fee'];
	}
	if (!$fee) {
		message('没有有效升级方式','','error');
	}
    if ($member['id'] == 1) {
    	$fee = 0.01;
    }
	$_SESSION['upgrade_level'] = intval($_GPC['type']);
	if (is_weixin()) {
		$tid = "10002" . date('YmdHis', time());
		$title = "升级{$config['title']}代理会员";
		$params = array(
		    'tid' => $tid, 
		    'ordersn' => $tid, 
		    'title' => $title, 
		    'fee' => $fee, 
		    'user' => $member['id'], 
		);
		//调用pay方法
		$this->pay($params);
	} else {
		$notify_url = 'https://wx.wyhrkj.com/addons/xuan_mixloan/lib/wechat/payResult.php';
        $record = pdo_fetch('select * from ' .tablename('xuan_mixloan_paylog'). '
		    where type=2 and is_pay=0 and uid=:uid order by id desc', array(':uid'=>$member['id']));
        if (empty($record)) {
            $tid = "10002" . date('YmdHis', time());
            $trade_no = "ZML".date("YmdHis");
            $insert = array(
                'notify_id'=>$trade_no,
                'tid'=>$tid,
                'createtime'=>time(),
                'uid'=>$member['id'],
                'uniacid'=>$_W['uniacid'],
                'fee'=>$fee,
                'is_pay'=>0,
                'type'=>2,
                'level'=>$_SESSION['upgrade_level']
            );
            pdo_insert('xuan_mixloan_paylog', $insert);
        } else {
            if ($record['createtime']+60 < time())
            {
                //超过1分钟重新发起订单
                $tid = "10002" . date('YmdHis', time());
                $trade_no = "ZML".date("YmdHis");
                $insert = array(
                    'notify_id'=>$trade_no,
                    'tid'=>$tid,
                    'createtime'=>time(),
                    'uid'=>$member['id'],
                    'uniacid'=>$_W['uniacid'],
                    'fee'=>$fee,
                    'is_pay'=>0,
                    'type'=>2,
                	'level'=>$_SESSION['upgrade_level']
                );
                pdo_insert('xuan_mixloan_paylog', $insert);
            }
            else
            {
                $trade_no = $record['notify_id'];
            }
        }
        $result = m('pay')->H5pay($trade_no, $fee, $notify_url);
        if ($result['code'] == 1) {
            $redirect_url = urlencode($_W['siteroot'] . 'app/' .
                $this->createMobileUrl('vip', array('op'=>'checkPay')));
            $url = "{$result['data']['url']}&redirect_url={$redirect_url}";
        } else {
            message('请稍后再试', $this->createMobileUrl('user'), 'error');
        }
        include $this->template('vip/openHref');
	}
	exit;
} else if ($operation == 'notify_url') {
    $notify_id = $_GPC['notify_id'];
    if (empty($notify_id)) {
        message('notify_id为空', '', 'error');
    }
    $params = pdo_fetch('select * from ' .tablename('xuan_mixloan_paylog'). '
		where notify_id=:notify_id', array(':notify_id'=>$notify_id));
    if (empty($params)) {
        header("location:{$this->createMobileUrl('user')}");
        exit();
    }
    $fee = $params['fee'];
    $tid = $params['tid'];
    if ($params['is_pay'] != 1) {
        message('订单未支付', '', 'error');
    }
    $member = pdo_fetch('select * from ' .tablename('xuan_mixloan_member'). '
		where id=:id', array(':id'=>$params['uid']));
    $openid = $member['openid'];
    if (empty($member['id'])) {
        header("location:{$this->createMobileUrl('user')}");
        exit();
    }
    $type = substr($params['tid'],0,5);
    if ($type=='10001') {
        //购买会员付费
        $agent = m('member')->checkAgent($member['id'], $config);;
        if ($agent['code'] == 1) {
            message("您已经是会员，请不要重复提交", $this->createMobileUrl('user'), "error");
        }
        if (!$_SESSION['buy_level']) {
        	$_SESSION['buy_level'] = $params['level'];
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
        $ext_info = array('content' => "您好，您已购买成功", 'remark' => "点击查看详情", 'url' => $url);
        $insert = array(
            'is_read'=>0,
            'type'=>1,
            'createtime'=>time(),
            'uniacid'=>$_W['uniacid'],
            'to_uid'=>$member['id'],
            'ext_info'=>json_encode($ext_info),
        );
        pdo_insert('xuan_mixloan_msg', $insert);
        //一级
        $inviter = m('member')->getInviter($member['phone'], $member['openid']);
        if ($inviter) {
            $agent = m('member')->checkAgent($inviter, $config);
            if ($agent['level'] == 1) {
                $re_bonus = $config['inviter_fee_one_init'] * 0.01 * $fee;
            } else if ($agent['level'] == 2) {
                $re_bonus = $config['inviter_fee_one_mid'] * 0.01 * $fee;
            } else if ($agent['level'] == 3) {
                $re_bonus = $config['inviter_fee_one_height'] * 0.01 * $fee;
            }
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
                    'degree'=>1
                );
                pdo_insert('xuan_mixloan_product_apply', $insert_i);
            }
            //模板消息提醒
            $one_openid = m('user')->getOpenid($inviter);
            $datam = array(
                "first" => array(
                    "value" => "您好，您的下级代理{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
                    "color" => "#173177"
                ) ,
                "order" => array(
                    "value" => $params['tid'],
                    "color" => "#173177"
                ) ,
                "money" => array(
                    "value" => $re_bonus,
                    "color" => "#173177"
                ) ,
                "remark" => array(
                    "value" => '点击查看详情',
                    "color" => "#4a5077"
                ) ,
            );
            $account = WeAccount::create($_W['acid']);
            $account->sendTplNotice($one_openid, $config['tpl_notice5'], $datam, $url);
            $ext_info = array('content' => "您好，您的下级代理{$member['nickname']}成功购买了代理会员，奖励您推广佣金{$re_bonus}元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", 'url' => $url);
            $insert = array(
                'is_read'=>0,
                'type'=>1,
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
                $agent = m('member')->checkAgent($inviter_two, $config);
                if ($agent['level'] == 1) {
                    $re_bonus = $config['inviter_fee_two_init'] * 0.01 * $fee;
                } else if ($agent['level'] == 2) {
                    $re_bonus = $config['inviter_fee_two_mid'] * 0.01 * $fee;
                } else if ($agent['level'] == 3) {
                    $re_bonus = $config['inviter_fee_two_height'] * 0.01 * $fee;
                }
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
                //模板消息提醒
                $two_openid = m('user')->getOpenid($inviter_two);
                $datam = array(
                    "first" => array(
                        "value" => "您好，您的下级代理{$man_one['nickname']}邀请了{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
                        "color" => "#173177"
                    ) ,
                    "order" => array(
                        "value" => $params['tid'],
                        "color" => "#173177"
                    ) ,
                    "money" => array(
                        "value" => $re_bonus,
                        "color" => "#173177"
                    ) ,
                    "remark" => array(
                        "value" => '点击查看详情',
                        "color" => "#4a5077"
                    ) ,
                );
                $account = WeAccount::create($_W['acid']);
                $account->sendTplNotice($two_openid, $config['tpl_notice5'], $datam, $url);
                $ext_info = array('content' => "您好，您的下级代理{$man_one['nickname']}邀请了{$member['nickname']}成功购买了代理会员，奖励您推广佣金{$re_bonus}元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", 'url' => $url);
                $insert = array(
                    'is_read'=>0,
                    'type'=>1,
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
                    $agent = m('member')->checkAgent($inviter_thr, $config);
                    if ($agent['level'] == 1) {
                        $re_bonus = $config['inviter_fee_thr_init'] * 0.01 * $fee;
                    } else if ($agent['level'] == 2) {
                        $re_bonus = $config['inviter_fee_thr_mid'] * 0.01 * $fee;
                    } else if ($agent['level'] == 3) {
                        $re_bonus = $config['inviter_fee_thr_height'] * 0.01 * $fee;
                    }
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
                    //模板消息提醒
                    $thr_openid = m('user')->getOpenid($inviter_thr);
                    $datam = array(
                        "first" => array(
                            "value" => "您好，您的团队邀请了{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
                            "color" => "#173177"
                        ) ,
                        "order" => array(
                            "value" => $params['tid'],
                            "color" => "#173177"
                        ) ,
                        "money" => array(
                            "value" => $re_bonus,
                            "color" => "#173177"
                        ) ,
                        "remark" => array(
                            "value" => '点击查看详情',
                            "color" => "#4a5077"
                        ) ,
                    );
                    $account = WeAccount::create($_W['acid']);
                    $account->sendTplNotice($thr_openid, $config['tpl_notice5'], $datam, $url);
                    $ext_info = array('content' => "您好，您的团队邀请了{$member['nickname']}成功购买了代理会员，奖励您推广佣金{$re_bonus}元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", 'url' => $url);
                    $insert = array(
                        'is_read'=>0,
                        'type'=>1,
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
    } else if ($type == '10002') {
        //升级会员付费
        $agent = m('member')->checkAgent($member['id'], $config);;
        if ($agent['level'] == 3) {
            message("您已经是最高级会员，请不要重复升级", $this->createMobileUrl('user'), "error");
        }
        if (!$_SESSION['upgrade_level']) {
        	$_SESSION['upgrade_level'] = $params['level'];
        }
        if ($_SESSION['upgrade_level'] == 2) {
            $upgrade_name = $config['mid_vip_name'];
        } else if ($_SESSION['upgrade_level'] == 3) {
            $upgrade_name = $config['height_vip_name'];
        }
        $insert = array(
            'uniacid' => $_W['uniacid'],
            'uid' => $member['id'],
            'createtime' => time(),
            'fee' => $fee
        );
        pdo_insert('xuan_mixloan_upgrade', $insert);
        pdo_update('xuan_mixloan_member', array('level'=>$_SESSION['upgrade_level']), array('id'=>$member['id']));
        //模板消息提醒
        $datam = array(
            "first" => array(
                "value" => "您好，您已升级成功",
                "color" => "#173177"
            ) ,
            "name" => array(
                "value" => "升级{$upgrade_name}代理会员",
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
        $ext_info = array('content' => "您好，您已成功升级{$upgrade_name}代理会员", 'remark' => "点击查看详情", 'url' => $url);
        $insert = array(
            'is_read'=>0,
            'type'=>1,
            'createtime'=>time(),
            'uniacid'=>$_W['uniacid'],
            'to_uid'=>$member['id'],
            'ext_info'=>json_encode($ext_info),
        );
        pdo_insert('xuan_mixloan_msg', $insert);
        $inviter = m('member')->getInviter($member['phone'], $member['openid']);
        if ($inviter) {
            $agent = m('member')->checkAgent($inviter, $config);
            if ($agent['level'] == 1) {
                $re_bonus = $config['inviter_fee_one_init'] * 0.01 * $fee;
            } else if ($agent['level'] == 2) {
                $re_bonus = $config['inviter_fee_one_mid'] * 0.01 * $fee;
            } else if ($agent['level'] == 3) {
                $re_bonus = $config['inviter_fee_one_height'] * 0.01 * $fee;
            }
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
                    'pid'=>-1,
                    'createtime'=>time(),
                    'degree'=>1
                );
                pdo_insert('xuan_mixloan_product_apply', $insert_i);
            }
            //模板消息提醒
            $one_openid = m('user')->getOpenid($inviter);
            $datam = array(
                "first" => array(
                    "value" => "您好，您的下级代理{$member['nickname']}成功升级了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
                    "color" => "#173177"
                ) ,
                "order" => array(
                    "value" => $params['tid'],
                    "color" => "#173177"
                ) ,
                "money" => array(
                    "value" => $re_bonus,
                    "color" => "#173177"
                ) ,
                "remark" => array(
                    "value" => '点击查看详情',
                    "color" => "#4a5077"
                ) ,
            );
            $account = WeAccount::create($_W['acid']);
            $account->sendTplNotice($one_openid, $config['tpl_notice5'], $datam, $url);
            $ext_info = array('content' => "您好，您的下级代理{$member['nickname']}成功升级了代理会员，奖励您推广佣金{$re_bonus}元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", 'url' => $url);
            $insert = array(
                'is_read'=>0,
                'type'=>1,
                'createtime'=>time(),
                'uniacid'=>$_W['uniacid'],
                'to_uid'=>$inviter,
                'ext_info'=>json_encode($ext_info),
            );
            pdo_insert('xuan_mixloan_msg', $insert);
            //二级
            $man = m('member')->getInviterInfo($inviter);
            $inviter = m('member')->getInviter($man['phone'], $man['openid']);
            if ($inviter) {
                $agent = m('member')->checkAgent($inviter, $config);
                if ($agent['level'] == 1) {
                    $re_bonus = $config['inviter_fee_two_init'] * 0.01 * $fee;
                } else if ($agent['level'] == 2) {
                    $re_bonus = $config['inviter_fee_two_mid'] * 0.01 * $fee;
                } else if ($agent['level'] == 3) {
                    $re_bonus = $config['inviter_fee_two_height'] * 0.01 * $fee;
                }
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
                        'pid'=>-1,
                        'createtime'=>time(),
                        'degree'=>2
                    );
                    pdo_insert('xuan_mixloan_product_apply', $insert_i);
                }
                //模板消息提醒
                $two_openid = m('user')->getOpenid($inviter);
                $datam = array(
                    "first" => array(
                        "value" => "您好，您的下级代理{$man['nickname']}邀请了{$member['nickname']}成功升级了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
                        "color" => "#173177"
                    ) ,
                    "order" => array(
                        "value" => $params['tid'],
                        "color" => "#173177"
                    ) ,
                    "money" => array(
                        "value" => $re_bonus,
                        "color" => "#173177"
                    ) ,
                    "remark" => array(
                        "value" => '点击查看详情',
                        "color" => "#4a5077"
                    ) ,
                );
                $account = WeAccount::create($_W['acid']);
                $account->sendTplNotice($two_openid, $config['tpl_notice5'], $datam, $url);
                $ext_info = array('content' => "您好，您的下级代理{$man['nickname']}邀请了{$member['nickname']}成功升级了代理会员，奖励您推广佣金{$re_bonus}元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", 'url' => $url);
                $insert = array(
                    'is_read'=>0,
                    'type'=>1,
                    'createtime'=>time(),
                    'uniacid'=>$_W['uniacid'],
                    'to_uid'=>$inviter,
                    'ext_info'=>json_encode($ext_info),
                );
                pdo_insert('xuan_mixloan_msg', $insert);
            }
        }
        message("支付成功", $this->createMobileUrl('user'), "success");
    } else if ($type == '10003') {
        //购买专员资格
        $service = m('member')->checkService($member['id']);
        if ($service['code'] == 1 || empty($member['id'])) {
            message('请不要重复提交', $this->createMobileUrl('user'), "success");
        }
        $insert = array(
            "uniacid"=>$_W["uniacid"],
            "uid"=>$member['id'],
            "createtime"=>time(),
            "tid"=>$params['tid'],
            "fee"=>$fee,
        );
        pdo_insert("xuan_mixloan_service_payment", $insert);
        message("支付成功", $this->createMobileUrl('user'), "success");
    }
} else if ($operation == 'createPost') {
	$type = intval($_GPC['type']);//1是关联产品,2是直接全部代理
	if ($type == 1) {
		$id = intval($_GPC['id']);
		$product = m('product')->getList(['id','ext_info'], ['id'=>$id])[$id];
		$cfg = [];
		$cfg['logo'] = $config['logo'];
		$cfg['poster_avatar'] = $product['ext_info']['poster_avatar'];
		$cfg['poster_image'] = $product['ext_info']['poster_image'];
		$cfg['poster_color'] = $product['ext_info']['poster_color'];
		$url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'apply', 'id'=>$id, 'inviter'=>$member['id']));
    	$out = XUAN_MIXLOAN_PATH."data/poster/{$id}_{$member['id']}.png";
    	$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/{$id}_{$member['id']}.png";
	} else {
		$id = 0;
		$cfg = $config;
		$url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'allProduct', 'inviter'=>$member['id']));
    	$out = XUAN_MIXLOAN_PATH."data/poster/{$member['id']}.png";
    	$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/{$member['id']}.png";
	}
	$poster = m('poster')->getPoster(["COUNT(1) AS count"], ["pid"=>$id, "type"=>$type, "uid"=>$member['id']]);
	if (!$poster["count"]) {
		$params = array(
			"url" => $url,
			"member" => $member,
			"type" => $type,
			"pid" => $id,
			"out" => $out,
			"poster_path" => $poster_path
		);
		$res = m('poster')->createPoster($cfg, $params);
		if ($res) {
	        show_json(1, ['post_url'=>$poster_path, 'agent_url'=>$url]);
		} else {
	        show_json(-1, [], '生成海报失败，请检查海报背景图上传是否正确');
		}
	} else {
		show_json(2, ['post_url'=>$poster_path, 'agent_url'=>$url]);
	}
	
} else if ($operation == 'createPostAllProduct') {
	//我的代理店
	if ($agent['code']==1) {
		$verify = 1;
	} else {
		$verify = 0;
	}
	$pids = pdo_fetchall("SELECT pid FROM ".tablename("xuan_mixloan_poster")." WHERE uid=:uid AND pid<>0 GROUP BY pid", array(":uid"=>$member['id']));
	if ($pids) {
		foreach ($pids as $value) {
			$res[] = $value['pid'];
		}
		$pids_string = '(' . implode(',', $res) . ')';
		$re = pdo_fetch("SELECT id,name FROM ".tablename("xuan_mixloan_product"). " WHERE uniacid={$_W['uniacid']} AND id NOT IN {$pids_string} LIMIT 1");
	} else {
		$re = pdo_fetch("SELECT id,name FROM ".tablename("xuan_mixloan_product"). " WHERE uniacid={$_W['uniacid']}  LIMIT 1");
	}
	include $this->template('vip/createPostAllProduct');
} else if ($operation == 'posterAll') {
	//全部海报图片
	$url = shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'allProduct', 'inviter'=>$member['id'])) );
	$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/{$member['id']}.png";
	include $this->template('vip/posterAll');
} else if ($operation == 'salary') {
	//我的工资
	if ($agent['code']==1) {
		$verify = 1;
	} else {
        header("location:{$this->createMobileUrl('vip', array('op' => 'buy'))}");
        exit();
		$verify = 0;
	}
	$bonus = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE uniacid={$_W['uniacid']} AND inviter={$member['id']}");
	$can_use = $bonus - m('member')->sumWithdraw($member['id']);
	$bonus = formatMoney($bonus);
	$can_use = formatMoney($can_use);
	$percent_list = m('product')->getApplyList([], ['inviter'=>$member['id'], 'la_status'=>0]);
	foreach ($percent_list as $row) {
		$ids[] = $row['pid'];
	}
	$pros = m('product')->getList(['id', 'count_time', 'name', 'ext_info'], ['id'=>$ids]);
	foreach ($percent_list as &$row) {
		if ($row['pid'] == 0){
			$row['name'] = '邀请购买代理';
			$row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
		} else if ($row['pid'] == -1) {
			$row['name'] = '邀请升级代理';
			$row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
		} else {
			$row['name'] = $pros[$row['pid']]['name'];
			$row['logo'] = $pros[$row['pid']]['ext_info']['logo'];
		}
		if ($row['pid'] <= 0 || $pros[$row['pid']]['count_time'] == 1) {
			$row['type'] = '日结';
		} else if ($pros[$row['pid']]['count_time'] == 7) {
			$row['type'] = '周结';
		} else if ($pros[$row['pid']]['count_time'] == 7) {
			$row['type'] = '月结';
		}
		$row['tid'] = date('YmdHis',$row['createtime']) . $row['id'];
		$row['count_money'] = number_format($row['re_bonus'] + $row['done_bonus'] + $row['extra_bonus'], 2);
	}
	unset($row);
	$accounts_list = pdo_fetchall("SELECT a.id,a.bonus,a.createtime,b.banknum,b.bankname,b.type,b.phone FROM ".tablename("xuan_mixloan_withdraw")." a LEFT JOIN ".tablename("xuan_mixloan_creditCard")." b ON a.bank_id=b.id WHERE a.uid={$member['id']} ORDER BY id DESC");
	foreach ($accounts_list as &$row) {
		$row['tid'] = date('YmdHis', $row['createtime']) . $row['id'];
		$row['year'] = date('m-d', $row['createtime']);
		$row['hour'] = date('H:i', $row['createtime']);
		if ($row['type'] == 1) {
			$row['bankmes'] =  "{$row['bankname']} 尾号(" . substr($row['banknum'], -4) . ")";
		} else {
			$row['bankmes'] =  "支付宝 尾号(" . substr($row['phone'], -4) . ")";
		}
		switch ($row['status']) {
			case '0':
				$row['status'] = '申请中';
				break;
			case '1':
				$row['status'] = '提现成功';
				break;
			case '-1':
				$row['status'] = '提现失败';
				break;
		}
	}
	unset($row);
	include $this->template('vip/salary');
} else if ($operation == 'withdraw') {
	//提现
	$banks = pdo_fetchall("SELECT * FROM ".tablename("xuan_mixloan_creditCard")." WHERE uid=:uid and status=1", array(':uid'=>$member['id']));
	foreach ($banks as &$row) {
		if ($row['type'] == 1) {
			if (count($row['banknum']) == 16) {
				$row['numbers_type'] = 1;
				$row['numbers'][0] = substr($row['banknum'], 0, 4);
				$row['numbers'][1] = substr($row['banknum'], 4, 4);
				$row['numbers'][2] = substr($row['banknum'], 8, 4);
				$row['numbers'][3] = substr($row['banknum'], 12, 4);
			} else {
				$row['numbers_type'] = 2;
				$row['numbers'][0] = substr($row['banknum'], 0, 6);
				$row['numbers'][1] = substr($row['banknum'], 6);
			}	
		} else if ($row['type'] == 2) {
			
		}
	}
	unset($row);
	$bonus = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE uniacid={$_W['uniacid']} AND inviter={$member['id']}");
	$can_use = $bonus - m('member')->sumWithdraw($member['id']);
	include $this->template('vip/withdraw');
} else if ($operation == 'withdraw_submit') {
	//提现提交
	$bonus = trim($_GPC['money']);
	$bank_id = intval($_GPC['card_id']);
	if (!$bonus) {
		show_json(-1, null, "提现金额不能为0");
	}
    if ($bonus < 10) {
        show_json(-1, null, "提现金额不能低于10元");
    }
	if (!$bank_id) {
		show_json(-1, null, "请选择提现银行卡");
	}
	$all = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE uniacid={$_W['uniacid']} AND inviter={$member['id']}");
	$used = m('member')->sumWithdraw($member['id']);
	$use = $all - $used;
	if ($bonus > $use) {
		show_json(-1, null, "可提现余额不足");
	}
    $date = date('Y-m-d');
    $today = strtotime("{$date}");
    $times = pdo_fetchcolumn('select count(*) from ' .tablename('xuan_mixloan_withdraw'). "
		where uid=:uid and createtime>{$today}", array(':uid'=>$member['id']));
    if ($times>0) {
        show_json(-1, null, "一天只能提现1次");
    }
    $week = date("w"); 
    if ($week == 6 || $week == 0) {
        show_json(-1, null, "请在工作日提现哦");
    }
    $now_time = explode(':', date('H:i'));
    if ($now_time[0] >= 9 && $now_time[0] <= 17) {
        if ($now_time[0] == 17 && $now_time[1] > 30) {
            show_json(-1, null, "请在上午9点到下午5点半点之间提现哦");
        } else {
            $insert = array(
                'uniacid'=>$_W['uniacid'],
                'uid'=>$member['id'],
                'bank_id'=>$bank_id,
                'bonus'=>$bonus,
                'createtime'=>time(),
                'status'=>0
            );
            pdo_insert('xuan_mixloan_withdraw', $insert);
            show_json(1, null, "提现成功，T+1日到账");
        }
    } else {
        show_json(-1, null, "请在上午9点到下午5点半点之间提现哦");
    }
} else if ($operation == 'inviteCode') {
	//邀请二维码
	$type = intval($_GPC['type']);
	if ($type == 1) {

	} else if ($type == 2) {
		$title = $config['share_title'];
		$imgUrl = tomedia($config['share_image']);
		$desc = $config['share_desc'];
		$link = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'allProduct', 'inviter'=>$member['id']));
	} else if ($type == 3) {
		$title = $config['share_title'];
		$imgUrl = tomedia($config['share_image']);
		$desc = $config['share_desc'];
		$link = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'allProduct', 'inviter'=>$member['id']));
	}
	include $this->template('vip/inviteCode');
} else if ($operation == 'createPoster') {
	//生成邀请二维码
	$uid = intval($_GPC['uid']) ? : $member['id'];
	$type = intval($_GPC['type']) ? : 1;
	$pid = intval($_GPC['pid']) ? : 0;
	$member = m('member')->getInfo($uid);
	$posterArr = pdo_fetchall('SELECT poster FROM '.tablename('xuan_mixloan_poster').' WHERE uid=:uid AND type=:type AND pid=:pid', array(':uid'=>$member['id'], ':type'=>$type, ':pid'=>$pid));
	$created = true;
	if ($type == 3) {
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'app_register', 'inviter'=>$member['id']));
        $share_url = shortUrl( $url );
        $tips = "汇容客—我的随身银行:{$share_url}";
		if (!$posterArr) {
			$created = false;
			// $wx = WeAccount::create();
		 //    $barcode = array(
		 //        'action_name'=>"QR_LIMIT_SCENE",
		 //        'action_info'=> array(
		 //            'scene' => array(
		 //                'scene_id'=>$member['id'],
		 //            )
		 //        )
		 //    );
		 //    $res = $wx->barCodeCreateDisposable($barcode);
			// $url = $res['url'];
			if (empty($config['inviter_poster'])) {
				message("请检查海报是否上传", "", "error");
			}
			foreach ($config['inviter_poster'] as $row) {
				$out = XUAN_MIXLOAN_PATH."data/poster/invite_{$member['id']}_{$row}.png";
				$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/invite_{$member['id']}_{$row}.png";
				$params = array(
					"poster_id" => $row,
					"url" => $url,
					"member" => $member,
					"type" => 3,
					"pid" => 0,
					"out" => $out,
					"poster_path" => $poster_path
				);
				$invite_res = m('poster')->createPoster($params);
			    if (!$invite_res) {
			    	message('生成海报失败，请检查海报背景图上传是否正确', '', 'error');
			    } else {
			    	$temp = [];
			    	$temp['poster'] = $poster_path;
			    	$posterArr[] = $temp;
			    }
			}
		}
	} else if ($type == 2) {
		$url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'allProduct', 'inviter'=>$member['id']));
		$share_url = shortUrl( $url );
		$tips = "汇容客—我的随身银行:{$share_url}";
		if (!$posterArr) {
			$created = false;
			if (empty($config['product_poster'])) {
				message("请检查海报是否上传", "", "error");
			}
			foreach ($config['product_poster'] as $row) {
				$out = XUAN_MIXLOAN_PATH."data/poster/product_{$member['id']}_{$row}.png";
				$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/product_{$member['id']}_{$row}.png";
				$params = array(
					"poster_id" => $row,
					"url" => $url,
					"member" => $member,
					"type" => 2,
					"pid" => 0,
					"out" => $out,
					"poster_path" => $poster_path
				);
				$invite_res = m('poster')->createPoster($params);
			    if (!$invite_res) {
			    	message('生成海报失败，请检查海报背景图上传是否正确', '', 'error');
			    } else {
			    	$temp = [];
			    	$temp['poster'] = $poster_path;
			    	$posterArr[] = $temp;
			    }
			}
		}
	} else if ($type == 1){
		$pid = intval($_GPC['pid']);
        $product = m('product')->getList(['id', 'type', 'ext_info', 'relate_id'], ['id'=>$pid])[$pid];
        if ($product['type'] == 1) {
            $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'apply', 'id'=>$pid, 'inviter'=>$member['id']));
        } else {
            $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$product['relate_id'], 'inviter'=>$member['id'], 'pid'=>$pid));
        }
		$share_url = shortUrl( $url );
		$tips = "汇容客—我的随身银行{$share_url}";
		if (!$posterArr) {
			$created = false;
			if (empty($product['ext_info']['poster'])) {
				message("请检查海报是否上传", "", "error");
			}
			foreach ($product['ext_info']['poster'] as $row) {
				$out = XUAN_MIXLOAN_PATH."data/poster/product_{$pid}_{$member['id']}_{$row}.png";
				$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/product_{$pid}_{$member['id']}_{$row}.png";
				$params = array(
					"poster_id" => $row,
					"url" => $url,
					"member" => $member,
					"type" => 1,
					"pid" => $pid,
					"out" => $out,
					"poster_path" => $poster_path
				);
				$invite_res = m('poster')->createPoster($params);
			    if (!$invite_res) {
			    	message('生成海报失败，请检查海报背景图上传是否正确', '', 'error');
			    } else {
			    	$temp = [];
			    	$temp['poster'] = $poster_path;
			    	$posterArr[] = $temp;
			    }
			}
		}
	} 
	$ret = array('tips'=>$tips, 'posterArr'=>$posterArr, 'created'=>$created);
	message($ret, '', 'success');
} else if ($operation == 'followList') {
	//关注列表
	$follow_list = pdo_fetchall("SELECT a.createtime,b.nickname FROM ".tablename("qrcode_stat")." a LEFT JOIN ".tablename("mc_mapping_fans"). " b ON a.openid=b.openid WHERE a.qrcid={$member['id']} AND a.type=1 ORDER BY id DESC");
	$count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$member['id']} AND status>0 AND pid=0");
	$count = $count ? : 0;
	$cTime = getTime();
	$star_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]}");
	$end_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]} +1 day");
	$today_count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$member['id']} AND status>0 AND pid=0 AND createtime>{$star_time} AND createtime<{$end_time}");
	$today_count = $today_count ? : 0;
	$star_time = strtotime("{$cTime[0]}-{$cTime[1]}-01");
	$end_time = strtotime("{$cTime[0]}-{$cTime[1]}-01 +1 month");
	$month_count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$member['id']} AND status>0 AND pid=0 AND createtime>{$star_time} AND createtime<{$end_time}");
	$month_count = $month_count ? : 0;
	include $this->template('vip/followList');
} else if ($operation == 'extendList') {
	//推广成功
	$extend_list = pdo_fetchall("SELECT a.uid,a.createtime,a.degree,a.re_bonus,b.nickname FROM ".tablename("xuan_mixloan_product_apply")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.uid=b.id WHERE a.inviter={$member['id']} AND a.status>0 AND pid=0 ORDER BY a.id DESC");
	$count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$member['id']} AND status>0 AND pid=0");
	$count = $count ? : 0;
	$cTime = getTime();
	$star_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]}");
	$end_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]} +1 day");
	$today_count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$member['id']} AND status>0 AND pid=0 AND createtime>{$star_time} AND createtime<{$end_time}");
	$today_count = $today_count ? : 0;
	$star_time = strtotime("{$cTime[0]}-{$cTime[1]}-01");
	$end_time = strtotime("{$cTime[0]}-{$cTime[1]}-01 +1 month");
	$month_count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$member['id']} AND status>0 AND pid=0 AND createtime>{$star_time} AND createtime<{$end_time}");
	$month_count = $month_count ? : 0;
	include $this->template('vip/extendList');
} else if ($operation == 'degreeDetail') {
	//对应等级
	$uid = intval($_GPC['uid']);
    $list = pdo_fetchall("SELECT a.degree,b.nickname,b.avatar FROM ".tablename("xuan_mixloan_product_apply")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.inviter=b.id WHERE a.uid={$uid} AND a.pid=0 ORDER BY a.degree ASC");
	$brother = pdo_fetch("SELECT nickname,avatar FROM ".tablename("xuan_mixloan_member")." WHERE id={$uid}");
	include $this->template('vip/degreeDetail');
} else if ($operation == 'extend_list') {
	//新下级列表
	include $this->template('vip/extend_list');
} else if ($operation == 'checkPay') {
    //检测有没有付款成功
    include $this->template('vip/checkPay');
} else if ($operation == 'app_register') {
    //邀请注册
    $inviter = m('member')->getInviterInfo($_GPC['inviter']);
    include $this->template('vip/register');
}
