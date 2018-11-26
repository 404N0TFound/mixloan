<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$agent = m('member')->checkAgent($member['id']);
if ($member['status'] == '0') {
    // 冻结
    die("<!DOCTYPE html>
    <html>
        <head>
            <meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'>
            <title>抱歉，出错了</title><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'><link rel='stylesheet' type='text/css' href='https://res.wx.qq.com/connect/zh_CN/htmledition/style/wap_err1a9853.css'>
        </head>
        <body>
        <div class='page_msg'><div class='inner'><span class='msg_icon_wrp'><i class='icon80_smile'></i></span><div class='msg_content'><h4>账号已冻结，联系客服处理</h4></div></div></div>
        </body>
    </html>");
}
if($operation=='buy'){
	//购买会员
	if (!$member['phone']) {
		message('请先绑定手机号', $this->createMobileUrl('index'), 'error');
	}
	if ($agent['code']==1) {
		$verify = 1;
	} else {
		$verify = 0;
	}
	include $this->template('vip/buy');
} else if ($operation == 'pay') {
    //付钱
    if (!$member['phone']) {
        message('请先绑定手机号', $this->createMobileUrl('index'), 'error');
    }
    if (is_weixin())
    {
        $tid = "10001" . date('YmdHis', time());
        $title = "购买{$config['title']}代理会员";
        $fee = $config['buy_mid_vip_price'];
        $params = array(
            'tid' => $tid,
            'ordersn' => $tid,
            'title' => $title,
            'fee' => $fee,
            'user' => $member['id'],
        );
        //调用pay方法
        $this->pay($params);
    }
    else
    {
        $notify_url = 'http://wwxx.huodong007.cn/addons/xuan_mixloan/lib/wechat/payResult.php';
        $record = pdo_fetch('select * from ' .tablename('xuan_mixloan_paylog'). '
		    where type=1 and is_pay=0 and uid=:uid order by id desc', array(':uid'=>$member['id']));
        if ($member['id'] == '20798') {
            $config['buy_mid_vip_price'] = 0.1;
        }
        if (empty($record)) {
            $tid = "10001" . date('YmdHis', time());
            $trade_no = "ZML".date("YmdHis");
            $insert = array(
                'notify_id'=>$trade_no,
                'tid'=>$tid,
                'createtime'=>time(),
                'uid'=>$member['id'],
                'uniacid'=>$_W['uniacid'],
                'fee'=>$config['buy_mid_vip_price'],
                'is_pay'=>0,
                'type'=>1
            );
            pdo_insert('xuan_mixloan_paylog', $insert);
        } else {
            if ($record['createtime']+60 < time())
            {
                //超过半小时重新发起订单
                $tid = "10001" . date('YmdHis', time());
                $trade_no = "ZML".date("YmdHis");
                $insert = array(
                    'notify_id'=>$trade_no,
                    'tid'=>$tid,
                    'createtime'=>time(),
                    'uid'=>$member['id'],
                    'uniacid'=>$_W['uniacid'],
                    'fee'=>$config['buy_mid_vip_price'],
                    'is_pay'=>0,
                    'type'=>1
                );
                pdo_insert('xuan_mixloan_paylog', $insert);
            }
            else
            {
                $trade_no = $record['notify_id'];
            }
        }
        $result = m('pay')->H5pay($trade_no, $config['buy_mid_vip_price'], $notify_url);
        if ($result['code'] == 1) {
            $redirect_url = urlencode($_W['siteroot'] . 'app/' .
                $this->createMobileUrl('vip', array('op'=>'checkPay')));
            $url = "{$result['data']['url']}&redirect_url={$redirect_url}";
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
        if ($fee == $config['buy_init_vip_price']) {
            pdo_update("xuan_mixloan_member", array('level'=>1), array('id'=>$member['id']));
        } else {
            pdo_update("xuan_mixloan_member", array('level'=>2), array('id'=>$member['id']));
        }
        //模板消息提醒
        $account = WeAccount::create($_W['acid']);
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
        $inviter_one = m('member')->getInviter($member['phone'], $openid);
        $man_one = m('member')->getInviterInfo($inviter_one);
        if ($inviter_one && $config['inviter_fee_one']) {
            $insert_i = array(
                'uniacid' => $_W['uniacid'],
                'uid' => $member['id'],
                'phone' => $member['phone'],
                'certno' => $member['certno'],
                'realname' => $member['realname'],
                'inviter' => $inviter_one,
                'extra_bonus'=>0,
                'done_bonus'=>0,
                're_bonus'=>$config['inviter_fee_one']*$fee*0.01,
                'status'=>2,
                'createtime'=>time(),
                'degree'=>1,
                'type'=>2
            );
            pdo_insert('xuan_mixloan_bonus', $insert_i);
            $one_insert_id = pdo_insertid();
            $re_bonus = $config['inviter_fee_one']*$fee*0.01;
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
            m('member')->upgradePartner($inviter_one, $config);
            //二级
            $inviter_two = m('member')->getInviter($man_one['phone'], $man_one['openid']);
            $man_two = m('member')->getInviterInfo($inviter_two);
            if ($man_two['partner']) {
                $partner_bonus = $config['inviter_fee_one']*$fee*0.01*$config['partner_bonus']*0.01;
                $insert_i = array(
                    'uniacid' => $_W['uniacid'],
                    'uid' => $man_one['id'],
                    'phone' => $man_one['phone'],
                    'inviter' => $inviter_two,
                    'extra_bonus'=>$partner_bonus,
                    'status'=>2,
                    'relate_id'=>$one_insert_id,
                    'createtime'=>time(),
                    'degree'=>1,
                    'type'=>5
                );
                pdo_insert('xuan_mixloan_bonus', $insert_i);
            }
            if ($inviter_two && $config['inviter_fee_two']) {
                $insert_i = array(
                    'uniacid' => $_W['uniacid'],
                    'uid' => $member['id'],
                    'phone' => $member['phone'],
                    'certno' => $member['certno'],
                    'realname' => $member['realname'],
                    'inviter' => $inviter_two,
                    'extra_bonus'=>0,
                    'done_bonus'=>0,
                    're_bonus'=>$config['inviter_fee_two']*$fee*0.01,
                    'status'=>2,
                    'createtime'=>time(),
                    'degree'=>2,
                    'type'=>2
                );
                pdo_insert('xuan_mixloan_bonus', $insert_i);
                $two_insert_id = pdo_insertid();
                $re_bonus = $config['inviter_fee_two']*$fee*0.01;
                $ext_info = array('content' => "您好，您的团队{$member['nickname']}成功购买了代理会员，奖励您推广佣金" . $re_bonus . "元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", "url" => $salary_url);
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
                $inviter_thr = m('member')->getInviter($man_two['phone'], $man_two['openid']);
                $man_thr = m('member')->getInviterInfo($inviter_thr);
                if ($man_thr['partner']) {
                    $partner_bonus = $config['inviter_fee_two']*$fee*0.01*$config['partner_bonus']*0.01;
                    $insert_i = array(
                        'uniacid' => $_W['uniacid'],
                        'uid' => $man_two['id'],
                        'phone' => $man_two['phone'],
                        'inviter' => $inviter_thr,
                        'extra_bonus'=>$partner_bonus,
                        'status'=>2,
                        'relate_id'=>$two_insert_id,
                        'createtime'=>time(),
                        'degree'=>1,
                        'type'=>5
                    );
                    pdo_insert('xuan_mixloan_bonus', $insert_i);
                }
                if ($inviter_thr && $config['inviter_fee_three']) {
                    $insert_i = array(
                        'uniacid' => $_W['uniacid'],
                        'uid' => $member['id'],
                        'phone' => $member['phone'],
                        'certno' => $member['certno'],
                        'realname' => $member['realname'],
                        'inviter' => $inviter_thr,
                        'extra_bonus'=>0,
                        'done_bonus'=>0,
                        're_bonus'=>$config['inviter_fee_three']*$fee*0.01,
                        'status'=>2,
                        'createtime'=>time(),
                        'degree'=>3,
                        'type'=>2
                    );
                    pdo_insert('xuan_mixloan_bonus', $insert_i);
                    $thr_insert_id = pdo_insertid();
                    $re_bonus = $config['inviter_fee_three']*$fee*0.01;
                    $ext_info = array('content' => "您好，您的团队{$member['nickname']}成功购买了代理会员，奖励您推广佣金" . $re_bonus . "元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", "url" => $salary_url);
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
                    //四级
                    $inviter_four = m('member')->getInviter($man_thr['phone'], $man_thr['openid']);
                    $man_four = m('member')->getInviterInfo($inviter_four);
                    if ($man_four['partner']) {
                        $partner_bonus = $config['inviter_fee_three']*$fee*0.01*$config['partner_bonus']*0.01;
                        $insert_i = array(
                            'uniacid' => $_W['uniacid'],
                            'uid' => $man_thr['id'],
                            'phone' => $man_thr['phone'],
                            'inviter' => $inviter_four,
                            'extra_bonus'=>$partner_bonus,
                            'status'=>2,
                            'relate_id'=>$thr_insert_id,
                            'createtime'=>time(),
                            'degree'=>1,
                            'type'=>5
                        );
                        pdo_insert('xuan_mixloan_bonus', $insert_i);
                    }
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
    }
} else if ($operation == 'createPost') {
	if ($agent['code'] != 1) {
	    show_json(-1, [], '您不是代理');
	}
	$type = intval($_GPC['type']);//1是关联产品,2是直接全部代理
	if ($type == 1) {
		$id = intval($_GPC['id']);
		$product = m('product')->getList(['id', 'type', 'relate_id', 'ext_info'], ['id'=>$id])[$id];
		$cfg = [];
		$cfg['logo'] = $config['logo'];
		$cfg['poster_avatar'] = $product['ext_info']['poster_avatar'];
		$cfg['poster_image'] = $product['ext_info']['poster_image'];
		$cfg['poster_color'] = $product['ext_info']['poster_color'];
		if ($product['type'] == 1){
            $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'apply', 'id'=>$id, 'inviter'=>$member['id']));
        } else {
            $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$product['relate_id'], 'pid'=>$id, 'inviter'=>$member['id']));
        }
    	$out = XUAN_MIXLOAN_PATH."data/poster/{$id}_{$member['id']}.png";
    	$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/{$id}_{$member['id']}.png";
	} else {
		$id = 0;
		$cfg = $config;
		$url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'allProduct', 'inviter'=>$member['id']));
    	$out = XUAN_MIXLOAN_PATH."data/poster/{$member['id']}.png";
    	$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/{$member['id']}.png";
	}
    $poster = pdo_fetch('select poster from ' . tablename('xuan_mixloan_poster') . ' 
        where pid=:pid and uid=:uid and type=:type', array(':pid' => $id, ':type' => $type, ':uid' => $member['id']));
    if (!$poster) {
        $params = array(
            "url" => shortUrl($url),
            "member" => $member,
            "type" => $type,
            "pid" => $id,
            "out" => $out,
            "poster_path" => $poster_path
        );
        $res = m('poster')->createPoster($cfg, $params);
        if ($res) {
            show_json(1, ['post_url'=>$res, 'agent_url'=>$url]);
        } else {
            show_json(-1, [], '生成海报失败，请检查海报背景图上传是否正确');
        }
    } else {
        show_json(2, ['post_url'=>$poster['poster'], 'agent_url'=>$url]);
    }
	
} else if ($operation == 'createPostAllProduct') {
	//我的代理店
	if ($agent['code']==1) {
		$verify = 1;
	} else {
		$verify = 0;
	}
	$pids = pdo_fetchall("SELECT pid FROM ".tablename("xuan_mixloan_poster")." WHERE uid=:uid", array(":uid"=>$member['id']));
	if ($pids) {
		foreach ($pids as $value) {
			$res[] = $value['pid'];
		}
		$pids_string = '(' . implode(',', $res) . ')';
		$re = pdo_fetch("SELECT id,name FROM ".tablename("xuan_mixloan_product"). " WHERE id NOT IN {$pids_string} LIMIT 1");
	} else {
		$re = pdo_fetch("SELECT id,name FROM ".tablename("xuan_mixloan_product"). " LIMIT 1");
	}
	include $this->template('vip/createPostAllProduct');
} else if ($operation == 'posterAll') {
	//全部海报图片
	$url = shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'allProduct', 'inviter'=>$member['id'])) );
    $poster_path = pdo_fetchcolumn('select poster from ' . tablename('xuan_mixloan_poster'). '
        where uid=:uid and type=2', array(':uid' => $member['id']));
    include $this->template('vip/posterAll');
} else if ($operation == 'salary') {
	//我的工资
	if ($agent['code']==1) {
		$verify = 1;
	} else {
		$verify = 0;
	}
	$bonus = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_bonus")." WHERE uniacid={$_W['uniacid']} AND inviter={$member['id']}");
	$can_use = $bonus - m('member')->sumWithdraw($member['id']);
	$bonus = formatMoney($bonus);
	$can_use = formatMoney($can_use);
	$percent_list = m('product')->getApplyList([], ['inviter'=>$member['id'], 'la_status'=>0]);
	foreach ($percent_list as $row) {
		if ($row['relate_id'] && $row['type'] == 1) {
			$ids[] = $row['relate_id'];
		}
	}
	$pros = m('product')->getList(['id', 'count_time', 'name', 'ext_info'], ['id'=>$ids]);
	foreach ($percent_list as &$row) {
		if ($row['type'] == 1) {
			$row['name'] = $pros[$row['relate_id']]['name'];
			$row['logo'] = $pros[$row['relate_id']]['ext_info']['logo'];
		} else if ($row['type'] == 2) {
			$row['name'] = '邀请购买代理';
			$row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
		} else if ($row['type'] == 5) {
			$row['name'] = '合伙人分红';
			$row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
		} else if ($row['type'] == 3) {
			$row['name'] = '邀请购买文章';
			$row['logo'] = '../addons/xuan_mixloan/template/style/images/chaxun16.png';
		} else if ($row['type'] == 4) {
			$row['name'] = '信用查询奖励';
			$row['logo'] = '../addons/xuan_mixloan/template/style/images/chaxun16.png';
		}
		if ($row['type'] != 1) {
			$row['type'] = '现结';
		}else if ($pros[$row['relate_id']]['count_time'] == 1) {
			$row['type'] = '日结';
		} else if ($pros[$row['relate_id']]['count_time'] == 7) {
			$row['type'] = '周结';
		} else if ($pros[$row['relate_id']]['count_time'] == 30) {
			$row['type'] = '月结';
		}
		$row['tid'] = date('YmdHis',$row['createtime']) . $row['id'];
		$row['count_money'] = number_format($row['re_bonus'] + $row['done_bonus'] + $row['extra_bonus'], 2);
	}
	unset($row);
	$accounts_list = pdo_fetchall("SELECT a.id,a.bonus,a.createtime,b.banknum,b.bankname FROM ".tablename("xuan_mixloan_withdraw")." a LEFT JOIN ".tablename("xuan_mixloan_creditCard")." b ON a.bank_id=b.id WHERE a.uid={$member['id']} ORDER BY id DESC");
    foreach ($accounts_list as &$row) {
        $row['tid'] = date('YmdHis', $row['createtime']) . $row['id'];
        $row['year'] = date('m-d', $row['createtime']);
        $row['hour'] = date('H:i', $row['createtime']);
        if ($row['type'] == 1) {
            $row['bankmes'] =  "{$row['bankname']} 尾号(" . substr($row['banknum'], -4) . ")";
        } else {
            if ($row['phone']) {
                $row['bankmes'] =  "支付宝 尾号(" . substr($row['phone'], -4) . ")";
            } else {
                $row['bankmes'] =  "收款二维码";
            }
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
    $bonus = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_bonus")." WHERE uniacid={$_W['uniacid']} AND inviter={$member['id']}");
    $can_use = $bonus - m('member')->sumWithdraw($member['id']);
    include $this->template('vip/withdraw');
} else if ($operation == 'withdraw_submit') {
	//提现提交
	$bonus = trim($_GPC['money']);
	$bank_id = intval($_GPC['card_id']);
	if (!$bonus) {
		show_json(-1, null, "提现金额不能为0");
	}
	if (!$bank_id) {
		show_json(-1, null, "请选择提现银行卡");
	}
	$all = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_bonus")." WHERE uniacid={$_W['uniacid']} AND inviter={$member['id']}");
	$used = m('member')->sumWithdraw($member['id']);
	$use = $all - $used;
	if ($bonus > $use) {
		show_json(-1, null, "可提现余额不足");
	}
	if ($bonus < 10) {
		show_json(-1, null, "提现余额必须大于10");
	}
	$date = date('Y-m-d');
	$today = strtotime("{$date}");
	$times = pdo_fetchcolumn('select count(*) from ' .tablename('xuan_mixloan_withdraw'). "
		where uid=:uid and createtime>{$today}", array(':uid'=>$member['id']));
	if ($times>1) {
		show_json(-1, null, "一天只能提现2次");
	}
	$insert = array(
		'uniacid'=>$_W['uniacid'],
		'uid'=>$member['id'],
		'bank_id'=>$bank_id,
		'bonus'=>$bonus,
		'createtime'=>time(),
		'status'=>0
	);
	pdo_insert('xuan_mixloan_withdraw', $insert);
	show_json(1, null, "提现成功");
} else if ($operation == 'inviteCode') {
	//邀请二维码
    if ($agent['code'] != 1) {
        message('您不是会员', '', 'error');
    }
    $poster_path = pdo_fetchcolumn('SELECT poster FROM '.tablename('xuan_mixloan_poster').' WHERE uid=:uid AND type=:type', array(':uid'=>$member['id'], ':type'=>3));
    if ($config['wx_invite_code']) {
        $wx = WeAccount::create();
        $barcode = array(
            'action_name'=>"QR_LIMIT_SCENE",
            'action_info'=> array(
                'scene' => array(
                    'scene_id'=>$member['id'],
                )
            )
        );
        $res = $wx->barCodeCreateDisposable($barcode);
        $url = $res['url'];
    } else {
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op' => 'app_register','inviter'=>$member['id']));
    }
    $shortUrl = shortUrl($url);
    if (!$poster_path) {
        $cfg['logo'] = $config['logo'];
        $cfg['poster_avatar'] = $config['invite_avatar'];
        $cfg['poster_image'] = $config['invite_image'];
        $cfg['poster_color'] = $config['invite_color'];
        $out = XUAN_MIXLOAN_PATH."data/poster/invite_{$member['id']}.png";
        $poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/invite_{$member['id']}.png";
        $params = array(
            "url" => $url,
            "member" => $member,
            "type" => 3,
            "pid" => 0,
            "out" => $out,
            "poster_path" => $poster_path
        );
        $invite_res = m('poster')->createPoster($cfg, $params);
        if (!$invite_res) {
            message('生成海报失败，请检查海报背景图上传是否正确', '', 'error');
        }
        $poster_path = $invite_res;
    }
    include $this->template('vip/inviteCode');
} else if ($operation == 'followList') {
	//关注列表
    $follow_list = pdo_fetchall(
        "SELECT a.createtime,a.openid,b.nickname FROM " .tablename("qrcode_stat"). " a
		LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.openid=b.openid
		WHERE a.qrcid={$member['id']} AND a.type=1
		GROUP BY a.openid
		ORDER BY a.id DESC");
    foreach ($follow_list as &$row) {
        if (empty($row['nickname'])) {
            $row['nickname'] = pdo_fetchcolumn(
                'select nickname from ' .tablename('mc_mapping_fans'). '
				where openid=:openid', array(':openid'=>$row['openid']));
        }
    }
    unset($row);
    $count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_bonus")." WHERE inviter={$member['id']} AND status>0 AND type=2");
	$count = $count ? : 0;
	$cTime = getTime();
	$star_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]}");
	$end_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]} +1 day");
	$today_count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_bonus")." WHERE inviter={$member['id']} AND status>0 AND type=2 AND createtime>{$star_time} AND createtime<{$end_time}");
	$today_count = $today_count ? : 0;
	$star_time = strtotime("{$cTime[0]}-{$cTime[1]}-01");
	$end_time = strtotime("{$cTime[0]}-{$cTime[1]}-01 +1 month");
	$month_count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_bonus")." WHERE inviter={$member['id']} AND status>0 AND type=2 AND createtime>{$star_time} AND createtime<{$end_time}");
	$month_count = $month_count ? : 0;
	$follow_count = count($follow_list) ? : 0;
	$buy_count = pdo_fetchcolumn("SELECT count(1) FROM ".tablename("xuan_mixloan_bonus")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.uid=b.id WHERE a.inviter={$member['id']} AND a.status>0 AND type=2") ? : 0;
	include $this->template('vip/followList');
} else if ($operation == 'extendList') {
	//推广成功
	$extend_list = pdo_fetchall("SELECT a.uid,a.createtime,a.degree,a.re_bonus,b.nickname FROM ".tablename("xuan_mixloan_bonus")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.uid=b.id WHERE a.inviter={$member['id']} AND a.status>0 AND a.type=2 ORDER BY a.id DESC");
	$count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_bonus")." WHERE inviter={$member['id']} AND status>0 AND type=2");
	$count = $count ? : 0;
	$cTime = getTime();
	$star_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]}");
	$end_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]} +1 day");
	$today_count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_bonus")." WHERE inviter={$member['id']} AND status>0 AND type=2 AND createtime>{$star_time} AND createtime<{$end_time}");
	$today_count = $today_count ? : 0;
	$star_time = strtotime("{$cTime[0]}-{$cTime[1]}-01");
	$end_time = strtotime("{$cTime[0]}-{$cTime[1]}-01 +1 month");
	$month_count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_bonus")." WHERE inviter={$member['id']} AND status>0 AND type=2 AND createtime>{$star_time} AND createtime<{$end_time}");
	$month_count = $month_count ? : 0;
	$follow_count = pdo_fetchcolumn("
        SELECT COUNT(*) FROM " .tablename("qrcode_stat"). "
        WHERE qrcid={$member['id']} AND type=1") ? : 0;
	$buy_count = count($extend_list) ? : 0;
	include $this->template('vip/extendList');
} else if ($operation == 'degreeDetail') {
	//对应等级
	$uid = intval($_GPC['uid']);
	$list = pdo_fetchall("SELECT a.degree,b.nickname,b.avatar,b.id FROM ".tablename("xuan_mixloan_bonus")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.inviter=b.id WHERE a.uid={$uid} ORDER BY a.degree ASC");
	$brother = pdo_fetch("SELECT id,nickname,avatar FROM ".tablename("xuan_mixloan_member")." WHERE id={$uid}");
	include $this->template('vip/degreeDetail');
} else if ($operation == 'rank_list') {
	//排行榜
    if ($_GPC['day'] == 1) {
        $strattime = strtotime(date('Y-m-d') . ' -1 days');
        $endtime = strtotime(date('Y-m-d'));
        $list = pdo_fetchall("select inviter as uid,SUM(re_bonus+done_bonus+extra_bonus) as count_bonus from " . tablename('xuan_mixloan_bonus') . "
             WHERE createtime>{$strattime} AND createtime<{$endtime}
             GROUP BY inviter HAVING count_bonus<>0
             ORDER BY count_bonus DESC LIMIT 15");
    } else {
        $temp_time = date('Y-m') . '-1';
        $start_time = strtotime($temp_time);
        $end_time = strtotime("+1 month {$temp_time}");
        // $list = pdo_fetchall("SELECT inviter,SUM(re_bonus+done_bonus+extra_bonus) AS bonus FROM ".tablename('xuan_mixloan_bonus')." WHERE relate_id=0 AND createtime>{$start_time} AND createtime<{$end_time} GROUP BY inviter HAVING bonus<>0 ORDER BY bonus DESC LIMIT 15");
        $list = pdo_fetchall("SELECT uid,SUM(bonus) as count_bonus FROM " .tablename('xuan_mixloan_withdraw'). "
            WHERE createtime>{$start_time} AND createtime<{$end_time}
            GROUP BY uid HAVING count_bonus<>0
            ORDER BY count_bonus DESC LIMIT 15");
    }
    if (!empty($list)) {
        foreach ($list as &$row) {
            $temp_member = pdo_fetch("SELECT nickname,avatar,phone FROM ".tablename('xuan_mixloan_member').' WHERE id=:id', array(':id'=>$row['uid']));
            $row['nickname'] = $temp_member['nickname'];
            $row['avatar'] = $temp_member['avatar'];
            $row['phone'] = substr($temp_member['phone'], 0, 4) . '****' . substr($temp_member['phone'], -3, 3);
        }
        unset($row);
    }
	include $this->template('vip/rank_list');
} else if ($operation == 'partner_center') {
    //分佣中心
    $uid = intval($_GPC['uid']);
    if (empty($uid)) {
    	message('出错啦', '', 'error');
    }
    $list = pdo_fetchall('select * from ' .tablename('xuan_mixloan_bonus'). '
		where inviter=:inviter and status>0 order by id desc', array(':inviter'=>$uid));
    $man = pdo_fetch('select nickname,avatar from '.tablename('xuan_mixloan_member').'
			where id=:id', array(':id'=>$uid));
    foreach ($list as &$row) {
        $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
        if ($row['type'] == 1) {
        	$row['bonus_name'] = pdo_fetchcolumn('select name from ' .tablename('xuan_mixloan_product'). '
        		where id=:id', array(':id'=>$row['relate_id']));
        } else if ($row['type'] == 2) {
        	$row['bonus_name'] = '购买代理奖励';
        } else if ($row['type'] == 3) {
        	$row['bonus_name'] = '购买文章奖励';
        } else if ($row['type'] == 4) {
        	$row['bonus_name'] = '信用查询奖励';
        } else if ($row['type'] == 5) {
        	$row['bonus_name'] = '合伙人奖励';
        }
        if ($row['phone']) {
        	$row['phone'] = substr($row['phone'], 0, 4) . '****' . substr($row['phone'], -3, 3);
        } else {
        	$row['phone'] = '无';
        }
        $row['bonus'] = $row['extra_bonus'] + $row['re_bonus'] + $row['done_bonus'] ? : 0;
    }
    unset($row);
    include $this->template('vip/partner_center');
}else if ($operation == 'app_register') {
    //邀请注册
    $inviter = m('member')->getInviterInfo($_GPC['inviter']);
    include $this->template('vip/register');
} else if ($operation == 'checkPay') {
    //检测有没有付款成功
    include $this->template('vip/checkPay');
} else if ($operation == 'partner_bonus') {
    //合伙人分佣
    $list = pdo_fetchall('select * from ' .tablename('xuan_mixloan_bonus'). '
        where inviter=:inviter and status>0 and type=5 order by id desc', array(':inviter'=>$member['id']));
    foreach ($list as &$row) {
        $row['man'] = pdo_fetch('select nickname,avatar from ' . tablename('xuan_mixloan_member') . '
            where id=:id', array(':id' => $row['uid']));
        $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
        if ($row['phone']) {
            $row['phone'] = substr($row['phone'], 0, 4) . '****' . substr($row['phone'], -3, 3);
        } else {
            $row['phone'] = '无';
        }
        $row['bonus'] = $row['extra_bonus'] + $row['re_bonus'] + $row['done_bonus'] ? : 0;
    }
    include $this->template('vip/partner_bonus');
} else if ($operation == 'set') {
    // 设置
    $is_close = pdo_fetchcolumn('select is_close from ' . tablename('xuan_mixloan_agent_close') . '
		where uid=:uid', array(':uid' => $member['id']));
    include $this->template('vip/set');
} else if ($operation == 'set_submit') {
    // 设置提交
    $active = $_GPC['active'];
    $id = pdo_fetchcolumn('select id from ' . tablename('xuan_mixloan_agent_close') . '
		where uid=:uid', array(':uid' => $member['id']));
    if ($active == 'false') {
        if ($id) {
            pdo_update('xuan_mixloan_agent_close', array('is_close' => 1), array('id' => $id));
        } else {
            $insert = array();
            $insert['uid'] = $member['id'];
            $insert['is_close'] = 1;
            pdo_insert('xuan_mixloan_agent_close', $insert);
        }
    } else {
        if ($id) {
            pdo_update('xuan_mixloan_agent_close', array('is_close' => 0), array('id' => $id));
        }
    }
}
