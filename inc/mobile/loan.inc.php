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
	$list = m('loan')->getList();
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
} else if ($operation == 'apply_submit') {
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
    $record = m('product')->getApplyList(['id'], ['relate_id'=>$id, 'phone'=>$_GPC['phone']]);
    if ($record) {
        show_json(-1, [], "你已经申请过啦");
        // show_json(1, $pro['ext_info']['url']);
    }
    if (md5($_GPC['phone'] . $_GPC['smscode']) != $_COOKIE['cache_code']) {
        //show_json(-1, [], "短信验证码不符");
    } else {
        //setcookie('cache_code', 'outdate', time()+10);
    }
    if ($inviter) {
        $inviter_one = pdo_fetch("SELECT openid,nickname FROM ".tablename("xuan_mixloan_member") . " WHERE id=:id", array(':id'=>$inviter));
        $status = 0;
        $url = $_W['siteroot'] . 'app/' . $this->createMobileUrl('vip', array('op' => 'salary'));
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
        $url = $_W['siteroot'] . 'app/' . $this->createMobileUrl('vip', array('op' => 'salary'));

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

    }
    show_json(1, $pro['ext_info']['url']);
}