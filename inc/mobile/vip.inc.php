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
    if ($member['id'] == 2) {
        $config['buy_vip_price'] = 0.01;
    }
    if ($config['buy_vip_price'] == 0) {
        $tid = "10001" . date('YmdHis', time());
        $title = "购买{$config['title']}代理会员";
        $fee = $config['buy_vip_price'];
        $params = array(
            'tid' => $tid,
            'ordersn' => $tid,
            'title' => $title,
            'fee' => $fee,
            'user' => $openid,
        );
        //调用pay方法
        $this->pay($params);
        exit();
    }
    if ($_GPC['way'] == 'alipay')
    {
        $tid = "10001" . date('YmdHis', time());
        $title = "购买{$config['title']}代理会员";
        $fee = $config['buy_vip_price'];
        $params = array(
            'tid' => $tid,
            'ordersn' => $tid,
            'title' => $title,
            'fee' => $fee,
            'user' => $member['id'],
            'module' => 'xuan_mixloan'
        );
        $insert = array(
            'openid' => $openid,
            'uniacid' => $_W['uniacid'],
            'acid' => $_W['uniacid'],
            'tid' => $tid,
            'fee' => $fee,
            'status' => 0,
            'module' => 'xuan_mixloan',
            'card_fee' => $fee,
        );
        pdo_insert('core_paylog', $insert);
        $url = url('mc/cash/alipay') . "&params=" . base64_encode(json_encode($params));
        include $this->template('vip/openHref');
        //调用pay方法
        // $this->pay($params);
    }
    else if ($_GPC['way'] == 'wechat')
    {
        $notify_url = 'http://mfcj.bjhantangyuanlin.com/addons/xuan_mixloan/lib/wechat/payResult.php';
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
                'fee'=>$config['buy_vip_price'],
                'is_pay'=>0,
                'type'=>1
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
                    'fee'=>$config['buy_vip_price'],
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
        $result = m('pay')->H5pay($trade_no, $config['buy_vip_price'], $notify_url);
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
                    'type'=>2
                );
                pdo_insert('xuan_mixloan_product_apply', $insert_i);
                $one_insert_id = pdo_insertid();
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
                        'degree'=>2,
                        'type'=>2
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
            }
        }
        message("支付成功", $this->createMobileUrl('user'), "success");
    }
} else if ($operation == 'createPost') {
	if ($agent['code'] != 1) {
	    show_json(-1, [], '您不是会员');
	}
	$type = intval($_GPC['type']);//1是关联产品,2是直接全部代理
	if ($type == 1) {
		$id = intval($_GPC['id']);
		$product = m('product')->getList(['id','ext_info', 'relate_id', 'type', 'name'], ['id'=>$id])[$id];
		$cfg = [];
		$cfg['logo'] = $config['logo'];
		$cfg['poster_avatar'] = $product['ext_info']['poster_avatar'];
		$cfg['poster_image'] = $product['ext_info']['poster_image'];
		$cfg['poster_color'] = $product['ext_info']['poster_color'];
        if ($product['type'] == 1){
            $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'apply', 'id'=>$id, 'inviter'=>$member['id'], 'rand'=>time()));
        } else {
            $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$product['relate_id'], 'pid'=>$id, 'inviter'=>$member['id'], 'rand'=>time()));
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
        where pid=:pid and uid=:uid', array(':pid' => $id, ':uid' => $member['id']));
    if (!$poster) {
        $params = array(
            "url" => $url,
            "member" => $member,
            "type" => $type,
            "pid" => $id,
            "out" => $out,
            "poster_path" => $poster_path,
        );
        if ($type == 1) {
            $params['product_name'] = $product['name'];
        }
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
	$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/{$member['id']}.png";
	include $this->template('vip/posterAll');
} else if ($operation == 'salary') {
	//我的工资
	if ($agent['code']==1) {
		$verify = 1;
	} else {
		$verify = 0;
	}
	$bonus = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE uniacid={$_W['uniacid']} AND inviter={$member['id']}");
	$can_use = $bonus - m('member')->sumWithdraw($member['id']);
	$bonus = formatMoney($bonus);
	$can_use = formatMoney($can_use);
	$percent_list = m('product')->getApplyList([], ['inviter'=>$member['id'], 'la_status'=>0], ' id desc', 50);
	foreach ($percent_list as $row) {
		$ids[] = $row['pid'];
	}
	$pros = m('product')->getList(['id', 'count_time', 'name', 'ext_info'], ['id'=>$ids]);
	foreach ($percent_list as &$row) {
		if ($row['type'] == 2){
			$row['name'] = '邀请购买代理';
			$row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
		} else if ($row['type'] == 6){
            $row['name'] = '满单奖励';
            $row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
        } else if ($row['type'] == 4){
            $row['name'] = '合伙人工资';
            $row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
        } else {
			$row['name'] = $pros[$row['pid']]['name'];
			$row['logo'] = $pros[$row['pid']]['ext_info']['logo'];
		}
        if ($row['type'] == 1){
            if ( $pros[$row['pid']]['count_time'] == 1) {
                $row['type'] = '日结';
            } else if ($pros[$row['pid']]['count_time'] == 7) {
                $row['type'] = '周结';
            } else if ($pros[$row['pid']]['count_time'] == 7) {
                $row['type'] = '月结';
            }
        } else {
            $row['type'] = '日结';
        }
		$row['tid'] = date('YmdHis',$row['createtime']) . $row['id'];
		$row['count_money'] = number_format($row['re_bonus'] + $row['done_bonus'] + $row['extra_bonus'], 2);
	}
	unset($row);
	$accounts_list = pdo_fetchall("SELECT a.id,a.bonus,a.createtime,b.banknum,b.bankname,b.phone,b.type FROM ".tablename("xuan_mixloan_withdraw")." a LEFT JOIN ".tablename("xuan_mixloan_creditCard")." b ON a.bank_id=b.id WHERE a.uid={$member['id']} ORDER BY id DESC LIMIT 50");
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
    $record = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_verify_data') . '
        where uid=:uid', array(':uid' => $member['id']));
    if (!$record) {
        header("location:{$this->createMobileUrl('user', array('op' => 'verify'))}");
    }
//    $banks = pdo_fetchall("SELECT * FROM ".tablename("xuan_mixloan_creditCard")." WHERE uid=:uid and status=1", array(':uid'=>$member['id']));
//    foreach ($banks as &$row) {
//        if ($row['type'] == 1) {
//            if (count($row['banknum']) == 16) {
//                $row['numbers_type'] = 1;
//                $row['numbers'][0] = substr($row['banknum'], 0, 4);
//                $row['numbers'][1] = substr($row['banknum'], 4, 4);
//                $row['numbers'][2] = substr($row['banknum'], 8, 4);
//                $row['numbers'][3] = substr($row['banknum'], 12, 4);
//            } else {
//                $row['numbers_type'] = 2;
//                $row['numbers'][0] = substr($row['banknum'], 0, 6);
//                $row['numbers'][1] = substr($row['banknum'], 6);
//            }
//        } else if ($row['type'] == 2) {
//
//        }
//    }
//    unset($row);
    $qrcodes = pdo_fetchall("SELECT id,name,img_url FROM ".tablename('xuan_mixloan_withdraw_qrcode'). " WHERE uid=:uid AND status=1", array(':uid'=>$member['id']));
    $bonus = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE uniacid={$_W['uniacid']} AND inviter={$member['id']}");
    $can_use = $bonus - m('member')->sumWithdraw($member['id']);
    include $this->template('vip/withdraw');
} else if ($operation == 'withdraw_submit') {
	//提现提交
	$bonus = trim($_GPC['money']);
	$bank_id = intval($_GPC['card_id']);
	$pass = trim($_GPC['pass']);
	if ($bonus<0) {
		show_json(-1, null, "提现金额不能为0");
	}
    if ($config['withdraw_money_limit'] && $bonus < $config['withdraw_money_limit']) {
        show_json(-1, null, "提现金额不能小于" . $config['withdraw_money_limit'] . "元");
    }
	if (!$bank_id) {
		show_json(-1, null, "请选择提现银行卡");
	}
	if (empty($pass)) {
		show_json(-1, null, "支付密码不能为空");
	}
    $pay_pass = pdo_fetchcolumn('select pass from ' . tablename('xuan_mixloan_paypass') . '
    	where uid=:uid', array(':uid' => $member['id']));
    if (md5(sha1($pass)) != $pay_pass) {
		show_json(-1, null, "支付密码不符合");
    }
	$all = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply")."
		WHERE uniacid={$_W['uniacid']} AND inviter={$member['id']}");
	$used = m('member')->sumWithdraw($member['id']);
	$use = $all - $used;
	if ($bonus > $use) {
		show_json(-1, null, "可提现余额不足");
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
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op' => 'app_register','inviter'=>$member['id'], 'rand'=>time()));
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
	$list = pdo_fetchall("SELECT a.degree,b.nickname,b.avatar FROM ".tablename("xuan_mixloan_product_apply")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.inviter=b.id WHERE a.uid={$uid} ORDER BY a.degree ASC");
	$brother = pdo_fetch("SELECT nickname,avatar FROM ".tablename("xuan_mixloan_member")." WHERE id={$uid}");
	include $this->template('vip/degreeDetail');
} else if ($operation == 'followList') {
    $follow_list = pdo_fetchall(
        "SELECT a.createtime,a.openid,b.nickname,b.avatar,b.id as uid FROM " .tablename("qrcode_stat"). " a
		LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.openid=b.openid
		WHERE a.qrcid={$member['id']} AND a.type=1
		GROUP BY a.openid
		ORDER BY a.id DESC");
    foreach ($follow_list as &$row) {
        if (empty($row['uid'])) {
            $temp = pdo_fetch('select nickname from ' .tablename('mc_mapping_fans'). '
				where openid=:openid', array(':openid'=>$row['openid']));
            if ($temp) {
                $row['nickname'] = $temp['nickname'];
                $row['avatar']   = $temp['avatar'];
            } else {
                $row['nickname'] = '未更新';
                $row['avatar'] = '';
            }
            $row['uid'] = 0;
        }
        $row['agent'] = m('member')->checkAgent($row['uid']);
        $row['bonus'] = pdo_fetchcolumn("SELECT re_bonus FROM " . tablename("xuan_mixloan_product_apply") . "
			WHERE inviter={$member['id']} AND uid={$row['uid']} AND pid=0") ? : '无';
    }
    unset($row);
    $count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM " . tablename("xuan_mixloan_product_apply") . "
		WHERE inviter={$member['id']} AND status>0") ? : 0;
    $follow_count = count($follow_list) ? : 0;
    include $this->template('vip/followList');
} else if ($operation == 'app_register') {
    //邀请注册
    $inviter = m('member')->getInviterInfo($_GPC['inviter']);
    include $this->template('vip/register');
} else if ($operation == 'choose_pay_type') {
    //选择付款方式
    include $this->template('vip/choose_pay_type');
} else if ($operation == 'checkPay') {
    //检测有没有付款成功
    include $this->template('vip/checkPay');
} else if ($operation == 'partner_upgrade') {
    //满足条件自动升级
    $partner = m('member')->checkPartner($member['id']);
    if ($partner['code']) {
        message('您已经是合伙人了', $this->createMobileUrl('user'), 'error');
    }
    if ($_GPC['post']) {
	    $list = pdo_fetchall('select b.id as uid from ' . tablename('qrcode_stat'). ' a 
			left join ' . tablename('xuan_mixloan_member') . ' b on a.openid=b.openid
			where a.qrcid=:qrcid and a.type=1 group by a.openid', array(':qrcid' => $member['id']));
	    $uids = array();
	    foreach ($list as $row) {
	        if ($row['uid']) {
	            $uids[] = $row['uid'];
	        }
	    }
	    if ($uids) {
	        $uid_string = '(' . implode(',', $uids) . ')';
	        $count = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_payment') . "
				where uid in {$uid_string}");
	        if ($count >= $config['partner_vip_nums']) {
	            $tid = "30002" . date('YmdHis', time());
	            $insert['uid'] = $member['id'];
	            $insert['createtime'] = time();
	            $insert['uniacid'] = $_W['uniacid'];
	            $insert['tid'] = $tid;
	            $insert['fee'] = 0;
	            pdo_insert('xuan_mixloan_partner', $insert);
	            message('升级合伙人成功', $this->createMobileUrl('user'), 'sccuess');
	        } else {
	            message('您还没达到升级条件呢~', $this->createMobileUrl('user'), 'error');
	        }
	    } else {
	        message('您还没有邀请小伙伴呢~', $this->createMobileUrl('user'), 'error');
	    }
    }
    include $this->template('vip/partner_upgrade');
} else if ($operation == 'partner_center') {
    //合伙人中心
    $list = pdo_fetchall('select * from ' .tablename('xuan_mixloan_product_apply'). '
		where inviter=:inviter and pid<>0 and degree=2 and status>0 
        order by id desc', array(':inviter'=>$member['id']));
    foreach ($list as &$row) {
    	$pro = m('product')->getList(['id', 'ext_info'], ['id' => $row['pid']])[$row['pid']];
    	$re_bonus = $pro['ext_info']['re_two_init_reward_money'] ? : 0;
        $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
        $row['avatar'] = '../addons/xuan_mixloan/template/style/new_user_index/images/tixian.png';
        $row['phone'] = substr($row['phone'], 0, 4) . '****' . substr($row['phone'], -3, 3);
        $row['bonus'] = $row['re_bonus'] - $re_bonus;
    }
    unset($row);
    include $this->template('vip/partner_center');
} else if ($operation == 'filed_withdraw') {
    // 提现失败
    pdo_update('xuan_mixloan_withdraw_delete', array('is_read' => 1), array('uid' => $member['id']));
    header("location:{$this->createMobileUrl('vip', array('op' => 'withdraw'))}");
} else if ($operation == 'set') {
    // 设置
    include $this->template('vip/set');
} else if ($operation == 'set_pay_pass') {
    // 设置支付密码
    if ($_GPC['post']) {
    	$pass = trim($_GPC['pass']);
    	$smscode = trim($_GPC['smscode']);
    	if (empty($pass)) {
	        show_json(-1, null, "请填写支付密码");
    	}
    	if (empty($smscode)) {
	        show_json(-1, null, "请填写验证码");
    	}
	    if (md5($member['phone'].$smscode) != $_COOKIE['cache_code']) {
	        show_json(-1, null, "验证码不符或验证码已失效");
	    }
	    $record = pdo_fetchcolumn('select id from ' . tablename('xuan_mixloan_paypass') . '
	    	where uid=:uid', array(':uid' => $member['id']));
	    $encryption = md5(sha1($pass));
	    if ($record) {
	    	pdo_update('xuan_mixloan_paypass', array('pass' => $encryption), array('id' => $record));
	    } else {
	    	$insert = array();
	    	$insert['uid'] = $member['id'];
	    	$insert['pass'] = $encryption;
	    	pdo_insert('xuan_mixloan_paypass', $insert);
	    }
	    show_json(1, null, "设置成功");
    }
    include $this->template('vip/set_pay_pass');
} else if ($operation == 'partner_bonus') {
    // 合伙人奖励
    include $this->template('vip/partner_bonus');
} else if ($operation == 'partner_salary') {
    // 底薪领取
    $check = pdo_fetch('SELECT id,createtime FROM ' . tablename("xuan_mixloan_partner") . "
        WHERE uid=:uid ORDER BY id DESC", array(':uid' => $member['id']));
    if (empty($check)) {
        message('抱歉，您还不是合伙人哦', '', 'error');
    }
    if (time() - $check['createtime'] < 86400 * 20) {
        message('由于您成为合伙人不足20天，下月25号才可以领工资哦', '', 'error');
    }
    if (empty($config['partner_salary'])) {
        message('合伙人工资没有设置', '', 'error');
    }
    if ($_COOKIE['get_bonus']) {
        message('您的操作太快啦', '', 'error');
    }
    $date = explode('-', date('Y-m-d'));
    if ($date[2] != $config['partner_days']) {
        message('抱歉，每月25号才可以领工资哦', '', 'error');
    }
    $starttime = strtotime(date('Y-m'));
    $record = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_product_apply') . "
        where inviter=:inviter and type=4 and createtime>{$starttime}", array(':inviter'=>$member['id']));
    if ($record) {
        message('您已经申请过领取工资啦', '', 'error');
    }
    setcookie('get_bonus', 1, time() + 60);
    $insert = array();
    $insert['uniacid'] = $_W['uniacid'];
    $insert['uid'] = $member['id'];
    $insert['phone'] = $member['phone'];
    $insert['certno'] = $member['certno'];
    $insert['realname'] = $member['realname'];
    $insert['inviter'] = $member['id'];
    // $insert['extra_bonus'] = $config['partner_salary'];
    $insert['status'] = 0;
    $insert['createtime'] = time();
    $insert['degree'] = 1;
    $insert['type'] = 4;
    pdo_insert('xuan_mixloan_product_apply', $insert);
    message('申请成功', $this->createMobileUrl('user'), 'success');
} else if ($operation == 'partner_tourism') {
    // 旅游套票
    $record = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_product_apply') . '
        where inviter=:inviter and type=1 and status>0 and degree=1', array(':inviter' => $member['id']));
    if ($record < $config['tourism_count']) {
        message("直推满{$config['tourism_count']}单才能领取奖励哦", '', 'error');
    }
    $record = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_tourism') . "
        where uid=:uid", array(':uid'=>$member['id']));
    if ($record) {
        message('您已经填写过啦', '', 'error');
    }
    include $this->template('vip/partner_tourism');
} else if ($operation == 'partner_tourism_submit') {
    // 旅游套票提交
    $record = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_product_apply') . '
        where inviter=:inviter and type=1 and status>0 and degree=1', array(':inviter' => $member['id']));
    if ($record < $config['tourism_count']) {
        show_json(-1, [], "直推满{$config['tourism_count']}单才能领取奖励哦");
    }
    $realname = trim($_GPC['realname']);
    $phone = trim($_GPC['phone']);
    $address = trim($_GPC['address']);
    if (empty($realname)) {
        show_json(-1, [], '姓名不能为空');
    }
    if (empty($phone)) {
        show_json(-1, [], '手机不能为空');
    }
    if (empty($address)) {
        show_json(-1, [], '地址不能为空');
    }
    $insert = array();
    $ext_info = array();
    $ext_info['address'] = $address;
    $insert['uid'] = $member['id'];
    $insert['phone'] = $phone;
    $insert['realname'] = $realname;
    $insert['status'] = 0;
    $insert['createtime'] = time();
    $insert['ext_info'] = json_encode($ext_info);
    pdo_insert('xuan_mixloan_tourism', $insert);
    show_json(1, [], '提交成功');
}

