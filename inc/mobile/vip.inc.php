<?php  
session_start();
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$agent = m('member')->checkAgent($member['id']);
if($operation=='buy'){
	//购买会员
	if ($agent['code']==1) {
		$verify = 1;
	} else {
		$verify = 0;
	}
	$inviter = m('member')->getInviter($member['phone'], $openid);
	if ($inviter) {
		$agent_fee = pdo_fetchcolumn('select fee from ' . tablename('xuan_mixloan_agent_fee') . '
			where uid=:uid', array(':uid' => $inviter)) ? : 0;
	} else {
		$agent_fee = 0;
	}
	$config['buy_vip_price'] = floatval($config['buy_vip_price']) + $agent_fee;
	include $this->template('vip/buy');
} else if ($operation == 'pay') {
	//付钱
	if (!$member['phone']) {
		message('请先绑定手机号', $this->createMobileUrl('index'), 'error');
	}
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
    exit;
} else if ($operation == 'createPost') {
	if ($agent['code'] != 1) {
	    show_json(-1, [], '您不是会员');
	}
	$type = intval($_GPC['type']);//1是关联产品,2是直接全部代理
	if ($type == 1) {
		$id = intval($_GPC['id']);
		$product = m('product')->getList(['id','ext_info', 'type', 'relate_id'], ['id'=>$id])[$id];
		$cfg = [];
		$cfg['logo'] = $config['logo'];
		$cfg['poster_avatar'] = $product['ext_info']['poster_avatar'];
		$cfg['poster_image'] = $product['ext_info']['poster_image'];
		$cfg['poster_color'] = $product['ext_info']['poster_color'];
        if ($product['type'] == 1){
            $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'apply', 'id'=>$id, 'inviter'=>$member['id'], 'rand' => 1));
        } else {
            $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$product['relate_id'], 'pid'=>$id, 'inviter'=>$member['id'], 'rand' => 1));
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
	$all =  m('member')->sumBonus($member['id']);
	$used = m('member')->sumWithdraw($member['id']);
	$can_use = $all - $used;
	$bonus = formatMoney($all);
	$can_use = formatMoney($can_use);
	$percent_list = m('product')->getApplyList([], ['inviter'=>$member['id'], 'la_status'=>0], false, 50);
	foreach ($percent_list as $row) {
		$ids[] = $row['pid'];
	}
	$pros = m('product')->getList(['id', 'count_time', 'name', 'ext_info'], ['id'=>$ids]);
	foreach ($percent_list as &$row) {
		if ($row['type'] == 2){
			$row['name'] = '邀请购买代理';
			$row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
		} else if ($row['type'] == 1) {
			$row['name'] = $pros[$row['pid']]['name'];
			$row['logo'] = $pros[$row['pid']]['ext_info']['logo'];
		} else if ($row['type'] == 4) {
			$row['name'] = '昨日佣金奖励';
			$row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
		}
		if ($pros[$row['pid']]['count_time'] == 1) {
			$row['type'] = '日结';
		} else if ($pros[$row['pid']]['count_time'] == 7) {
			$row['type'] = '周结';
		} else if ($pros[$row['pid']]['count_time'] == 7) {
			$row['type'] = '月结';
		}
		if ($row['type'] == 2 || $row['type'] == 4) {
			$row['type'] = '现结';
		}
		$row['tid'] = date('Ymd',$row['createtime']) . $row['id'];
		$row['count_money'] = number_format($row['re_bonus'] + $row['done_bonus'] + $row['extra_bonus'], 2);
	}
	unset($row);
	$accounts_list = pdo_fetchall("SELECT a.id,a.bonus,a.createtime,b.banknum,b.bankname,b.type FROM ".tablename("xuan_mixloan_withdraw")." a LEFT JOIN ".tablename("xuan_mixloan_creditCard")." b ON a.bank_id=b.id WHERE a.uid={$member['id']} ORDER BY id DESC LIMIT 20");
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
    if ($member['status'] == 0) {
    	message('你已被冻结，请联系管理员', $this->createMobileUrl('user'), 'error');
    }
    $date = date('Y-m-d');
    $today = strtotime("{$date}");
    $times = pdo_fetchcolumn('select count(*) from ' .tablename('xuan_mixloan_withdraw'). "
		where uid=:uid and createtime>{$today}", array(':uid'=>$member['id']));
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
	$all =  m('member')->sumBonus($member['id']);
	$used = m('member')->sumWithdraw($member['id']);
	$can_use = bcsub($all, $used, 2);
    include $this->template('vip/withdraw');
} else if ($operation == 'withdraw_submit') {
	//提现提交
    if ($member['status'] == 0) {
		show_json(-1, null, "你已被冻结，请联系管理员");
    }
    if (!$config['withdraw_open']) {
		show_json(-1, null, $config['withdraw_close_tips']);
    }
	$bonus = trim($_GPC['money']);
	$bank_id = intval($_GPC['card_id']);
    $pass = trim($_GPC['pass']);
	if (!$bonus) {
		show_json(-1, null, "提现金额不能为0");
	}
	if ($config['withdraw_money_limit'] && $bonus < $config['withdraw_money_limit']) {
		show_json(-1, null, "提现金额不能小于" . $config['withdraw_money_limit'] . "元");
	}
    if (empty($pass)) {
        show_json(-1, null, "支付密码不能为空");
    }
    $pay_pass = pdo_fetchcolumn('select pass from ' . tablename('xuan_mixloan_paypass') . '
    	where uid=:uid', array(':uid' => $member['id']));
    if (md5(sha1($pass)) != $pay_pass) {
        show_json(-1, null, "支付密码不符合");
    }
    $date = date('Y-m-d');
    $today = strtotime($date);
    $times = pdo_fetchcolumn('select count(*) from ' .tablename('xuan_mixloan_withdraw'). "
		where uid=:uid and createtime>{$today}", array(':uid'=>$member['id']));
    if ($config['withdraw_day_limit'] && $config['withdraw_day_limit'] <= $times) {
        show_json(-1, null, "一天只能提现" . $config['withdraw_day_limit'] . "次");
    }
	if (!$bank_id) {
		show_json(-1, null, "请选择提现银行卡");
	}
	$all =  m('member')->sumBonus($member['id']);
	$used = m('member')->sumWithdraw($member['id']);
	$can_use = bcsub($all, $used, 2);
	if ($bonus > $can_use) {
		show_json(-1, null, "可提现余额不足");
	}
	if (!$times) {
		$money = $bonus;
	} else {
		if ($bonus < 1) {
			show_json(-1, null, "可提现余额不足");
		}
		$money = $bonus - 1;
	}
	$insert = array(
		'uniacid'=>$_W['uniacid'],
		'uid'=>$member['id'],
		'bank_id'=>$bank_id,
		'bonus'=>$bonus,
		'money'=>$money,
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
}  else if ($operation == 'followList') {
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
    $count = m('member')->sumBonus($member['id']);
    $count = $count ? : 0;
    $cTime = getTime();
    $star_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]}");
    $end_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]} +1 day");
    $today_count = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply_b")." WHERE inviter={$member['id']} AND status>0 AND createtime>{$star_time} AND createtime<{$end_time}");
    $today_count = $today_count ? : 0;
    $star_time = strtotime("{$cTime[0]}-{$cTime[1]}-01");
    $end_time = strtotime("{$cTime[0]}-{$cTime[1]}-01 +1 month");
    $month_count = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply_b")." WHERE inviter={$member['id']} AND status>0 AND createtime>{$star_time} AND createtime<{$end_time}");
    $month_count = $month_count ? : 0;
    $follow_count = count($follow_list) ? : 0;
    $buy_count = pdo_fetchcolumn("SELECT count(1) FROM ".tablename("xuan_mixloan_product_apply_b")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.uid=b.id WHERE a.inviter={$member['id']} AND a.status>0 AND pid=0") ? : 0;
    include $this->template('vip/followList');
} else if ($operation == 'extendList') {
    //推广成功
    $extend_list = pdo_fetchall("SELECT a.uid,a.createtime,a.degree,a.re_bonus,b.nickname FROM ".tablename("xuan_mixloan_product_apply_b")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.uid=b.id WHERE a.inviter={$member['id']} AND a.status>0 AND pid=0 ORDER BY a.id DESC");
    $backup_list = pdo_fetchall("SELECT a.uid,a.createtime,a.degree,a.re_bonus,b.nickname FROM ".tablename("xuan_mixloan_product_apply_a")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.uid=b.id WHERE a.inviter={$member['id']} AND a.status>0 AND pid=0 ORDER BY a.id DESC");
    $extend_list = array_merge($extend_list, $backup_list);
    $count = m('member')->sumBonus($member['id']);;
    $cTime = getTime();
    $star_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]}");
    $end_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]} +1 day");
    $today_count = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply_b")." WHERE inviter={$member['id']} AND status>0 AND createtime>{$star_time} AND createtime<{$end_time}");
    $today_count = $today_count ? : 0;
    $star_time = strtotime("{$cTime[0]}-{$cTime[1]}-01");
    $end_time = strtotime("{$cTime[0]}-{$cTime[1]}-01 +1 month");
    $month_count = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply_b")." WHERE inviter={$member['id']} AND status>0 AND createtime>{$star_time} AND createtime<{$end_time}");
    $month_count = $month_count ? : 0;
    $follow_count = pdo_fetchcolumn("SELECT count(1) FROM ".tablename("qrcode_stat")." a LEFT JOIN ".tablename("mc_mapping_fans"). " b ON a.openid=b.openid WHERE a.qrcid={$member['id']} AND a.type=1 ORDER BY id DESC") ? : 0;
    $buy_count = count($extend_list) ? : 0;
    include $this->template('vip/extendList');
} else if ($operation == 'degreeDetail') {
    //对应等级
    $uid = intval($_GPC['uid']);
    $list = pdo_fetchall("SELECT a.degree,b.nickname,b.avatar FROM ".tablename("xuan_mixloan_product_apply_b")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.inviter=b.id WHERE a.uid={$uid} AND a.pid=0 ORDER BY a.degree ASC");
    $brother = pdo_fetch("SELECT nickname,avatar FROM ".tablename("xuan_mixloan_member")." WHERE id={$uid}");
    $wheres = '';
    $status = $_GPC['status'];
    if ($status == '0' || $status == '-1' || $status == '2') {
    	$wheres .= 'and status='.$status;
    }
    $apply = pdo_fetchall('select realname,pid,status from ' . tablename('xuan_mixloan_product_apply_b') . "
    	where inviter=:inviter and degree=1 {$wheres} and type=1
    	order by id desc limit 30", array(':inviter' => $uid));
    foreach ($apply as &$row) {
    	$row['pro_name'] = pdo_fetchcolumn('select name from ' . tablename('xuan_mixloan_product') . '
    		where id=:id', array(':id' => $row['pid']));
    }
    unset($row);
    include $this->template('vip/degreeDetail');
} else if ($operation == 'alipay') {
	//支付宝支付
	if ($config['buy_vip_price'] == 0) {
		$out_trade_no = "10001" . date('YmdHis', time());
	    $insert = array(
	        "uniacid"=>$_W["uniacid"],
	        "uid"=>$member['id'],
	        "createtime"=>time(),
	        "tid"=>$out_trade_no,
	        "fee"=>0,
	    );
	    pdo_insert("xuan_mixloan_payment", $insert);
	    message('购买成功', $this->createMobileUrl('user'), 'success');
	}
	include $this->template('vip/alipay');
} else if ($operation == 'alipay_params') {
	$inviter = m('member')->getInviter($member['phone'], $openid);
	if ($inviter) {
		$agent_fee = pdo_fetchcolumn('select fee from ' . tablename('xuan_mixloan_agent_fee') . '
			where uid=:uid', array(':uid' => $inviter)) ? : 0;
	} else {
		$agent_fee = 0;
	}
	$total = floatval($config['buy_vip_price']) + $agent_fee;
	// 商品网址
	$base_path = urlencode( $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'buy')) );
	// 异步通知地址
	$notify_url = $_W['siteroot'] . '/addons/xuan_mixloan/lib/payment/payResult.php';
	$out_trade_no = "10001" . date('YmdHis', time());
	$insert = array(
		'notify_id' => $out_trade_no,
		'tid' => $out_trade_no,
		'uid' => $member['id'],
		'createtime' => time(),
		'is_pay' => 0,
		'uniacid' => $_W['uniacid'],
		'type' => 1,
		'fee' => $total
	);
	pdo_insert('xuan_mixloan_paylog', $insert);
	require_once(IA_ROOT . '/addons/xuan_mixloan/lib/payment/alipay.php');
} else if ($operation == 'set_vip') {
	//设置会员
    show_json(1);
} else if ($operation == 'alipay_notify') {
	//支付宝异步回调

	if ($_GPC['notify_id']) {
    	$params = pdo_fetch('select * from ' . tablename('xuan_mixloan_paylog') . '
        	where notify_id=:notify_id', array(':notify_id' => $_GPC['notify_id']));
	} else {
		$params = pdo_fetch('select * from ' . tablename('xuan_mixloan_paylog') . '
        	where uid=:uid order by id desc', array(':uid' => $member['id']));
	}
    if ($params['is_pay'] != 1) {
    	show_json(-1, [], '未支付订单');
    }
    $member = pdo_fetch('select * from ' . tablename('xuan_mixloan_member') . '
        where id=:id', array(':id' => $params['uid']));
    $agent = m('member')->checkAgent($member['id']);;
    if ($agent['code'] == 1) {
        show_json(-1, [], '您已经是会员，请不要重复提交');
    }
    $tid = $params['tid'];
    $fee = $params['fee'];
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
    //一级
    $inviter = m('member')->getInviter($member['phone'], $member['openid']);
    if ($inviter) {
    	$agent_fee = $params['fee'] - $config['buy_vip_price'];
    	if ($agent_fee) {
    		$agent_fee = $agent_fee * (1 - 0.01 * $config['agent_charge_fee']);
    	} else {
    		$agent_fee = 0;
    	}
        $re_bonus = $config['inviter_fee_one'] + $agent_fee;
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
            pdo_insert('xuan_mixloan_product_apply_b', $insert_i);
        }
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
                pdo_insert('xuan_mixloan_product_apply_b', $insert_i);
            }
            //模板消息提醒
            $two_openid = m('user')->getOpenid($inviter_two);
            $datam = array(
                "first" => array(
                    "value" => "您好，您的徒弟{$man_one['nickname']}邀请了{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
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
                        'degree'=>3,
                        'type'=>2
                    );
                    pdo_insert('xuan_mixloan_product_apply_b', $insert_i);
                }
                //模板消息提醒
                $thr_openid = m('user')->getOpenid($inviter_thr);
                $datam = array(
                    "first" => array(
                        "value" => "您好，您的徒弟{$man_two['nickname']}的徒弟{$man_one['nickname']}邀请了{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
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
            }
        }
    }
    show_json(1, [], '购买成功');
} else if ($operation == 'app_register') {
    //邀请注册
    $inviter = m('member')->getInviterInfo($_GPC['inviter']);
    require_once(IA_ROOT . '/addons/xuan_mixloan/inc/model/cache.php');
    $cache = new Xuan_mixloan_Cache();
    $cache_img = $cache->doimg();
    if (!$cache_img['result'])
    {
        message('生成验证码失败', '', 'error');
    }
    $code = $cache->getCode();
    setcookie('authcode', sha1(md5($code)), time()+300);
    include $this->template('vip/register');
} else if ($operation == 'filed_withdraw') {
	// 提现失败
	pdo_update('xuan_mixloan_withdraw_delete', array('is_read' => 1), array('uid' => $member['id']));
	header("location:{$this->createMobileUrl('vip', array('op' => 'withdraw'))}");
} else if ($operation == 'createPoster') {
    //生成邀请二维码
    $uid = intval($_GPC['uid']) ? : $member['id'];
    $type = intval($_GPC['type']) ? : 1;
    $pid = intval($_GPC['pid']) ? : 0;
    $member = m('member')->getInfo($uid);
    $posterArr = pdo_fetchall('SELECT poster,prefix_text FROM '.tablename('xuan_mixloan_poster').' WHERE uid=:uid AND type=:type AND pid=:pid', array(':uid'=>$member['id'], ':type'=>$type, ':pid'=>$pid));
    $created = true;
    if ($type == 3) {
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'app_register', 'inviter'=>$member['id']));
        $share_url = shortUrl( $url );
        if (!$posterArr) {
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
                    "poster_path" => $poster_path,
                );
                $invite_res = m('poster')->createNewPoster($params);
                if (!$invite_res) {
                    message('生成海报失败，请检查海报背景图上传是否正确', '', 'error');
                } else {
                    $posterArr[] = $invite_res;
                }
            }
        }
    } else if ($type == 2) {
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'allProduct', 'inviter'=>$member['id']));
        $share_url = shortUrl( $url );
        $tips = "{$config['title']}—我的随身银行：{$share_url}";
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
                $invite_res = m('poster')->createNewPoster($params);
                if (!$invite_res) {
                    message('生成海报失败，请检查海报背景图上传是否正确', '', 'error');
                } else {
                    $posterArr[] = $invite_res;
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
                $invite_res = m('poster')->createNewPoster($params);
                if (!$invite_res) {
                    message('生成海报失败，请检查海报背景图上传是否正确', '', 'error');
                } else {
                    $posterArr[] = $invite_res;
                }
            }
        }
    }
    foreach ($posterArr as &$row) {
    	if (!$row['prefix_text']) {
    		$row['prefix_text'] = '';
    	}
    	$row['prefix_text'] = $row['prefix_text'].$share_url;
    }
    unset($row);
    $ret = array('tips'=>$tips, 'posterArr'=>$posterArr, 'created'=>$created);
    message($ret, '', 'success');
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
} else if ($operation == 'set_agent_fee') {
    // 设置支付密码
    $agent_fee = pdo_fetchcolumn('select fee from ' . tablename('xuan_mixloan_agent_fee') . '
    	where uid=:uid', array(':uid' => $member['id']));
    if ($_GPC['post']) {
    	$fee = floatval($_GPC['agent_fee']);
    	if (empty($fee)) {
        	show_json(-1, null, "不允许为0");
    	}
    	if ($fee > floatval($config['agent_fee_limit'])) {
        	show_json(-1, null, "不允许超过{$config['agent_fee_limit']}元");
    	}
    	if (!$agent_fee) {
	    	$insert = array();
	    	$insert['uid'] = $member['id'];
	    	$insert['fee'] = $fee;
	    	pdo_insert('xuan_mixloan_agent_fee', $insert);
    	} else {
    		pdo_update('xuan_mixloan_agent_fee', array('fee'=>$fee), array('uid'=>$member['id']));
    	}
        show_json(1, null, "设置成功");
    }
    include $this->template('vip/set_agent_fee');
}
