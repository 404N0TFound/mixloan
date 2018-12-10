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
    if ($config['loan_vip']) {
        if (empty($openid)) {
            header("location:{$this->createMobileUrl('index', array('op' => 'login'))}");
        }
        $agent = m('member')->checkAgent($member['id']);
        if ($agent['code'] != 1) {
            header("location:{$this->createMobileUrl('vip', array('op' => 'buy'))}");
        }
    }
    $category = pdo_fetchall('select id,name,ext_info from ' . tablename('xuan_mixloan_loan_category') . "
        where uniacid={$_W['uniacid']} ORDER BY sort DESC");
    foreach ($category as &$row) {
        $row['ext_info'] = json_decode($row['ext_info'], 1);
    }
    unset($row);
	$list = m('loan')->getList([], [], 'apply_nums desc', 10);
	$advs = m('loan')->getAdvs();
	$barrages = m('loan')->getBarrage($list);
	include $this->template('loan/index');
} else if ($operation == 'loan_select') {
	//全部贷款
    $category = pdo_fetchall('select id,name from ' . tablename('xuan_mixloan_loan_category') . "
        where uniacid={$_W['uniacid']} ORDER BY sort DESC");
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
        header("location:{$this->createMobileUrl('product', array('op' => 'allProduct', 'inviter' => $inviter))}");
        exit();
    }
    $is_close = pdo_fetchcolumn('select is_close from ' . tablename('xuan_mixloan_agent_close') . '
        where uid=:uid', array(':uid' => $inviter));
    include $this->template('loan/apply');
} else if ($operation == 'apply_submit') {
    //申请提交
    $id = intval($_GPC['id']);
    if (empty($id)) {
        show_json(-1, [], "出错了");
    }
    $inviter_uid = m('member')->getInviter(trim($_GPC['phone']), $openid);
    $inviter = $inviter_uid ? : intval($_GPC['inviter']);
    if (sha1(md5(strtolower($_GPC['cache']))) != $_COOKIE['authcode']) {
        show_json(-1, [], "图形验证码不正确");
    }
    if ($inviter == $member['id']) {
        show_json(-1, [], "您不能自己邀请自己");
    }
    if(!trim($_GPC['name']) || !trim($_GPC['phone']) || !trim($_GPC['idcard'])) {
        show_json(-1, [], '资料不能为空');
    }
    $info = m('product')->getList(['id', 'name', 'type', 'relate_id','is_show'],['id'=>$id])[$id];
    if (empty($info['is_show'])) {
        show_json(-1, [], "该产品已被下架");
    }
    if ($info['type'] == 1) {
        $pro = m('bank')->getCard(['id', 'ext_info'], ['id'=>$info['relate_id']])[$info['relate_id']];
    } else {
        $pro = m('loan')->getList(['id', 'ext_info'], ['id'=>$info['relate_id']])[$info['relate_id']];
    }
    $record = m('product')->getApplyList(['id'], ['pid'=>$id, 'phone'=>$_GPC['phone']]);
    if ($record) {
        show_json(1,$pro['ext_info']['url']);
    }
    // if ($config['jdwx_open'] == 1) {
    //     $res = m('jdwx')->jd_credit_three($config['jdwx_key'], trim($_GPC['name']), trim($_GPC['phone']), trim($_GPC['idcard']));
    //     if ($res['code'] == -1) {
    //         show_json($res['code'], [], $res['msg']);
    //     }
    // }
    if ($inviter) {
        $inviter_openid = pdo_fetchcolumn("SELECT openid FROM ".tablename("xuan_mixloan_member") . " WHERE id=:id", array(':id'=>$inviter));
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
        $account->sendTplNotice($inviter_openid, $config['tpl_notice1'], $datam, $url);
        $ext_info = array('content' => "尊敬的用户您好，" . $_GPC['name'] . "通过您的邀请申请了" . $info['name'] . "，请及时跟进。", 'remark' => "点击查看详情", 'url' => $url);
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
        if (!$inviter_uid) {
            $check = m('member')->checkIfRelation($inviter, $member['id']);
            if ($check == false) {
                $insert_i = array(
                    'uniacid' => $_W['uniacid'],
                    'uid' => $inviter,
                    'phone' => trim($_GPC['phone']),
                    'createtime' => time()
                );
                pdo_insert('xuan_mixloan_inviter', $insert_i);
            }
        }
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
        'createtime'=>time()
    );
    pdo_insert('xuan_mixloan_product_apply', $insert);
    //二级
    $inviter_info = m('member')->getInviterInfo($inviter);
    $second_inviter = m('member')->getInviter($inviter_info['phone'], $inviter_info['openid']);
    if ($second_inviter) {
        $insert['inviter'] = $second_inviter;
        $insert['degree'] = 2;
        pdo_insert('xuan_mixloan_product_apply', $insert);
        $inviter_two = pdo_fetch("SELECT openid,nickname FROM ".tablename("xuan_mixloan_member") . " WHERE id=:id", array(':id'=>$second_inviter));
        $datam = array(
            "first" => array(
                "value" => "尊敬的用户您好，有一个用户通过您下级{$inviter_info['nickname']}的邀请申请了{$info['name']}，请及时跟进。",
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
        $ext_info = array('content' => "尊敬的用户您好，" . $_GPC['name'] . "通过您下级 " . $inviter_info['nickname'] . " 的邀请申请了" . $info['name'] . "，请及时跟进。", 'remark' => "点击查看详情", 'url' => $url);
        $insert = array(
            'is_read'=>0,
            'uid'=>$member['id'],
            'type'=>2,
            'createtime'=>time(),
            'uniacid'=>$_W['uniacid'],
            'to_uid'=>$second_inviter,
            'ext_info'=>json_encode($ext_info),
        );
        pdo_insert('xuan_mixloan_msg', $insert);
    }
    show_json(1,$pro['ext_info']['url']);
}
?>