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
    $type = intval($_GPC['type']);
    if (empty($id)) {
        message("出错了", "", "error");
    }
    if ($item['type'] == 1) {
        $item = m('loan')->getList(['*'], ['id'=>$id])[$id];
        include $this->template('loan/apply');
    } else {
        $item = m('bank')->getList(['*'], ['id'=>$id])[$id];
        include $this->template('loan/card_apply');
    }
} else if ($operation == 'apply_submit') {
    //申请提交
    $id = intval($_GPC['id']);
    $type = intval($_GPC['type']);
    if (sha1(md5(strtolower($_GPC['cache']))) != $_COOKIE['authcode']) {
        show_json(-1, [], "图形验证码不正确");
    }
    if(!trim($_GPC['name']) || !trim($_GPC['phone']) || !trim($_GPC['idcard'])) {
        show_json(-1, [], '资料不能为空');
    }
    if ($info['type'] == 1) {
        $pro = m('bank')->getCard(['id', 'ext_info'], ['id'=>$id])[$id];
    } else {
        $pro = m('loan')->getList(['id', 'ext_info'], ['id'=>$id])[$id];
    }
    $record = m('product')->getApplyList(['id'], ['pid'=>$id, 'phone'=>$_GPC['phone'], 'type'=>$type]);
    if ($record) {
        show_json(1,$pro['ext_info']['url']);
    }
    $insert = array(
        'uniacid' => $_W['uniacid'],
        'uid' => $member['id'],
        'phone' => trim($_GPC['phone']),
        'certno' => trim($_GPC['idcard']),
        'realname' => trim($_GPC['name']),
        'relate_id' => $id,
        'status'=>0,
        'createtime'=>time(),
        'type'=>$type
    );
    pdo_insert('xuan_mixloan_apply', $insert);
    show_json(1,$pro['ext_info']['url']);
} else if ($operation == 'search') {
    // 搜索
    $keyword = trim($_GPC['keyword']);
    $loan_list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_loan') . "
                where name like :keyword", array(':keyword' => "%" . $keyword . "%"));
    foreach ($loan_list as &$row) {
        $row['ext_info'] = json_decode($row['ext_info'], 1);
    }
    unset($row);
    $card_list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_bank_card') . "
                where name like :keyword", array(':keyword' => "%" . $keyword . "%"));
    foreach ($card_list as &$row) {
        $row['ext_info'] = json_decode($row['ext_info'], 1);
    }
    unset($row);
    include $this->template('loan/search');
}