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
	$list = m('loan')->getList([], ['status' => 1]);
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
	} else {
        foreach ($recommends as &$row) {
        }
        unset($row);
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
	$condition['status'] = 1;
	$list = m('loan')->getList([], $condition, $orderBy);
	if (empty($list)) {
		show_json(-1);
	} else {
		foreach ($list as &$row) {
			$row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
            // $row['ext_info']['url'] = $this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$row['id']));
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
    if ($item['status'] != 1) {
        message("该产品已下架", "", "error");
    }
	include $this->template('loan/apply');
} else if ($operation == 'apply_submit') {
    //申请提交
    $id = intval($_GPC['id']);
    if (empty($id)) {
        show_json(-1, [], "出错了");
    }
    $phone = trim($_GPC['phone']);
    $realname = trim($_GPC['name']);
    if(empty($phone) || empty($realname)) {
        show_json(-1, [], '资料不能为空');
    }
    $info = m('loan')->getList([],['id'=>$id])[$id];
    $record = m('loan')->checkRecord(1, $id, $phone);
    if ($record) {
        show_json(1, $info['ext_info']['url']);
    }
    $insert = array(
        'uniacid' => $_W['uniacid'],
        'relate_id' => $id,
        'phone' => $phone,
        'realname' => $realname,
        'type' => 1,
        'createtime' => time(),
    );
    pdo_insert('xuan_mixloan_apply', $insert);
    show_json(1,$info['ext_info']['url']);
} else if ($operation == 'get_index') {
	// 获取首页信息
	$ret = array();
	$barrage = m('loan')->getBarrageB();
	$ret['barrage'] = $barrage;
	show_json(1, $ret, 'success');
}
