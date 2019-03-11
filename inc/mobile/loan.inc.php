<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='index';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$member['user_type'] = m('member')->checkAgent($member['id']);
if($operation=='index'){
	//贷款中心首页
    header("location:{$this->createMobileUrl('product', array('op' => ''))}");
	$list = m('loan')->getList([], [], ' apply_nums desc', 10);
	$advs = m('loan')->getAdvs();
	$barrages = m('loan')->getBarrage($list);
	include $this->template('loan/index');
} else if ($operation == 'loan_select') {
	//全部贷款
	include $this->template('loan/loan_select');
} else if ($operation == 'recommend') {
	//智能推荐
	$recommends = m('loan')->getRecommends();
	if (empty($recommends)) {
		show_json(-1);
	}
	show_json(1, array_values($recommends));
} else if ($operation == 'loanView') {
	//更新申请人数
	$id = intval($_GPC['id']);
	$sql = "UPDATE ".tablename("xuan_mixloan_loan")." SET `apply_nums`=`apply_nums`+1 WHERE id={$id}";
	pdo_run($sql); 
	show_json(1);
} else if ($operation == 'getLoan') {
	//获取贷款列表
	$conditon = [];
	if (isset($_GPC['order']) && !empty($_GPC['order'])) {
		$orderBy = $_GPC['order'];
	} else {
		$orderBy = FALSE;
	}
	if (isset($_GPC['begin']) && !empty($_GPC['begin'])) {
		$condition['begin'] = $_GPC['begin'];
	}
	if (isset($_GPC['end']) && !empty($_GPC['end'])) {
		$condition['end'] = $_GPC['end'];
	}
	if (isset($_GPC['least']) && !empty($_GPC['least'])) {
		$condition['least'] = $_GPC['least'];
	}
	if (isset($_GPC['high']) && !empty($_GPC['high'])) {
		$condition['high'] = $_GPC['high'];
	}
	if (isset($_GPC['type']) && !empty($_GPC['type'])) {
		$condition['type'] = $_GPC['type'];
	}
	$list = m('loan')->getList([], $condition, $orderBy);
	if (empty($list)) {
		show_json(-1);
	} else {
		foreach ($list as &$row) {
			$row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
		}
		unset($row);
		show_json(1, array_values($list));
	}
} else if ($operation == 'display') {
    //贷款展示页
    $id = intval($_GPC['id']);
    if (empty($id)) {
        message('出错了', '', 'error');
    }
    $item = m('loan')->getList(['id', 'ext_info'], ['id'=>$id])[$id];
    if (empty($item)) {
        message('出错了', '', 'error');
    }
    if (is_weixin()) {
        header("location:{$item['ext_info']['url']}");
        exit();
    }
    include $this->template('loan/display');
} else if ($operation == 'apply') {
    //申请详情
    $id = intval($_GPC['id']);
    if (empty($id)) {
        message("出错了", "", "error");
    }
    $pid = intval($_GPC['pid']);
    $inviter = intval($_GPC['inviter']);
    $item = m('loan')->getList(['*'], ['id'=>$id])[$id];
    $info = m('product')->getList(['id','is_show'], ['id'=>$pid])[$pid];
    if (empty($info['is_show'])){
        message('该产品已被下架');
    }
    include $this->template('loan/apply');
}else if ($operation == 'apply_submit') {
    //申请产品
    $id = intval($_GPC['id']);
    $inviter_uid = m('member')->getInviter(trim($_GPC['phone']), $member['openid']);
    $inviter = $inviter_uid ? : intval($_GPC['inviter']);
    if ($inviter == $member['id']) {
        show_json(-1, [], "您不能自己邀请自己");
    }
    if ($id <= 0) {
        show_json(-1, [], "id为空");
    }
    if(!trim($_GPC['name']) || !trim($_GPC['phone'])) {
        show_json(-1, [], '资料不能为空');
    }
    $info = m('product')->getList(['id', 'name', 'type', 'relate_id', 'is_show'],['id'=>$id])[$id];
    if ( empty($info['is_show']) ) {
        show_json(-1, [], '该代理产品已被下架');
    }
    if ($info['type'] == 1) {
        $pro = m('bank')->getCard(['id', 'ext_info'], ['id'=>$info['relate_id']])[$info['relate_id']];
    } else {
        $pro = m('loan')->getList(['id', 'ext_info'], ['id'=>$info['relate_id']])[$info['relate_id']];
    }
    $record = pdo_fetchcolumn('select id from ' . tablename('xuan_mixloan_product_apply') . '
             where phone=:phone and pid=:pid and degree=1', array(':phone' => trim($_GPC['phone']), ':pid' => $id));
    if ($record) {
        $location = $_W['siteroot'] . 'app/' . $this->createMobileUrl('loan', array('op' => 'middleware', 'id' => $record));
        show_json(1, $location);
    }
    if (sha1(md5(strtolower($_GPC['cache']))) != $_COOKIE['authcode']) {
        show_json(-1, [], "图形验证码不正确");
    }
    if ($inviter) {
        $inviter_one = pdo_fetch("SELECT openid,nickname FROM ".tablename("xuan_mixloan_member") . " WHERE id=:id", array(':id'=>$inviter));
        $url = $_W['siteroot'] . 'app/' . $this->createMobileUrl('vip', array('op' => 'salary'));
        $ext_info = array('content' => "尊敬的用户您好，" . $_GPC['name'] . "通过您的邀请申请了" . $info['name'] . "，请及时跟进。", 'remark' => "点击查看详情", 'url' => $url);
        $msg = array(
            'is_read'=>0,
            'uid'=>$member['id'],
            'type'=>2,
            'createtime'=>time(),
            'uniacid'=>$_W['uniacid'],
            'to_uid'=>$inviter,
            'ext_info'=>json_encode($ext_info),
        );
        pdo_insert('xuan_mixloan_msg', $msg);
        $datam = array(
            "first" => array(
                "value" => "尊敬的用户您好，有一个用户通过您的邀请申请了{$info['name']}，请及时跟进。",
                "color" => "#173177"
            ) ,
            "keyword1" => array(
                'value' => trim($_GPC['name']),
                "color" => "#4a5077"
            ) ,
            "keyword2" => array(
                'value' => date('Y-m-d H:i:s', time()),
                "color" => "#4a5077"
            ) ,
            "remark" => array(
                "value" => '点击查看详情',
                "color" => "#4a5077"
            ) ,
        );
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
        $account = WeAccount::create($_W['acid']);
        $account->sendTplNotice($inviter_one['openid'], $config['tpl_notice1'], $datam, $url);
        $status = 0;
    } else {
        $status = -2;
    }
    $insert = array(
        'uniacid' => $_W['uniacid'],
        'uid' => $member['id'],
        'phone' => trim($_GPC['phone']),
        'certno' => trim($_GPC['idcard']),
        'realname' => trim($_GPC['name']),
        'pid' => $id,
        'inviter' => $inviter,
        're_bonus'=>0,
        'done_bonus'=>0,
        'extra_bonus'=>0,
        'status'=>$status,
        'createtime'=>time(),
        'ip'=>getServerIp(),
        'device_type'=>getDeviceType(),
    );
    $insert['browser_type'] = is_weixin() ? 1 : 2;
    pdo_insert('xuan_mixloan_product_apply', $insert);
    $insert_id = pdo_insertid();
    //二级
    $inviter_info = m('member')->getInviterInfo($inviter);
    $second_inviter = m('member')->getInviter($inviter_info['phone'], $inviter_info['openid']);
    if ($second_inviter) {
        $insert['inviter'] = $second_inviter;
        $insert['degree'] = 2;
        pdo_insert('xuan_mixloan_product_apply', $insert);
        $inviter_two = pdo_fetch("SELECT openid,nickname FROM ".tablename("xuan_mixloan_member") . " WHERE id=:id", array(':id'=>$second_inviter));
        $url = $_W['siteroot'] . 'app/' . $this->createMobileUrl('vip', array('op' => 'salary'));
        $ext_info = array('content' => "尊敬的用户您好，" . $_GPC['name'] . "通过您下级 " . $inviter_info['nickname'] . " 的邀请申请了" . $info['name'] . "，请及时跟进。", 'remark' => "点击查看详情", 'url' => $url);
        $msg = array(
            'is_read'=>0,
            'uid'=>$member['id'],
            'type'=>2,
            'createtime'=>time(),
            'uniacid'=>$_W['uniacid'],
            'to_uid'=>$second_inviter,
            'ext_info'=>json_encode($ext_info),
        );
        pdo_insert('xuan_mixloan_msg', $msg);
        $datam = array(
            "first" => array(
                "value" => "尊敬的用户您好，有一个用户通过您下级{$inviter_one['nickname']}的邀请申请了{$info['name']}，请及时跟进。",
                "color" => "#173177"
            ) ,
            "keyword1" => array(
                'value' => trim($_GPC['name']),
                "color" => "#4a5077"
            ) ,
            "keyword2" => array(
                'value' => date('Y-m-d H:i:s', time()),
                "color" => "#4a5077"
            ) ,
            "remark" => array(
                "value" => '点击查看详情',
                "color" => "#4a5077"
            ) ,
        );
        $account->sendTplNotice($inviter_two['openid'], $config['tpl_notice1'], $datam, $url);
    }
    //三级
    $inviter_info = m('member')->getInviterInfo($second_inviter);
    $third_inviter = m('member')->getInviter($inviter_info['phone'], $inviter_info['openid']);
    if ($third_inviter) {
        $insert['inviter'] = $third_inviter;
        $insert['degree'] = 3;
        pdo_insert('xuan_mixloan_product_apply', $insert);
        $inviter_thr = pdo_fetch("SELECT openid,nickname FROM ".tablename("xuan_mixloan_member") . " WHERE id=:id", array(':id'=>$third_inviter));
        $url = $_W['siteroot'] . 'app/' . $this->createMobileUrl('vip', array('op' => 'salary'));
        $ext_info = array('content' => "尊敬的用户您好，" . $_GPC['name'] . "通过您团队的邀请申请了" . $info['name'] . "，请及时跟进。", 'remark' => "点击查看详情", 'url' => $url);
        $msg = array(
            'is_read'=>0,
            'uid'=>$member['id'],
            'type'=>2,
            'createtime'=>time(),
            'uniacid'=>$_W['uniacid'],
            'to_uid'=>$third_inviter,
            'ext_info'=>json_encode($ext_info),
        );
        pdo_insert('xuan_mixloan_msg', $msg);
        $datam = array(
            "first" => array(
                "value" => "尊敬的用户您好，有一个用户通过您团队的邀请申请了的邀请申请了{$info['name']}，请及时跟进。",
                "color" => "#173177"
            ) ,
            "keyword1" => array(
                'value' => trim($_GPC['name']),
                "color" => "#4a5077"
            ) ,
            "keyword2" => array(
                'value' => date('Y-m-d H:i:s', time()),
                "color" => "#4a5077"
            ) ,
            "remark" => array(
                "value" => '点击查看详情',
                "color" => "#4a5077"
            ) ,
        );
        $account->sendTplNotice($inviter_thr['openid'], $config['tpl_notice1'], $datam, $url);
    }
    $location = $_W['siteroot'] . 'app/' . $this->createMobileUrl('loan', array('op' => 'middleware', 'id' => $insert_id));
    show_json(1, $location);
} else if ($operation == 'middleware') {
    // 跳转中间组件
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select id,inviter,pid from ' . tablename('xuan_mixloan_product_apply') . '
        where id=:id', array(':id' => $id));
    $info = m('product')->getList(['id', 'name', 'middleware', 'relate_id'], ['id' => $item['pid']])[$item['pid']];
    if ($info['type'] == 1) {
        $product = m('bank')->getCard(['id', 'ext_info'], ['id'=>$info['relate_id']])[$info['relate_id']];
    } else {
        $product = m('loan')->getList(['id', 'ext_info'], ['id'=>$info['relate_id']])[$info['relate_id']];
    }
    $url = $product['ext_info']['url'];
    if ((!is_weixin() && !is_qq()) || !$info['middleware'])
    {
        header("location:{$url}");
        exit();
    }
    include $this->template('loan/middleware');
} else if ($operation == 'count_time') {
    // 统计时间
    $id      = intval($_GPC['id']);
    $time    = intval($_GPC['time']);
    $inviter = intval($_GPC['inviter']);
    $update  = array('last_time' => $time);
    $result  = pdo_update('xuan_mixloan_apply_time', $update, array('relate_id'=>$id));
    if (!$result)
    {
        $record = pdo_fetchcolumn('select count(1) from ' . tablename('xuan_mixloan_apply_time') . '
			    		where relate_id=:relate_id
			    		limit 1', array(':relate_id' => $id));
        if (empty($record))
        {
            $insert = array('last_time' => $time, 'relate_id' => $id, 'inviter' => $inviter, 'createtime' => time());
            pdo_insert('xuan_mixloan_apply_time', $insert);
        }
    }
}