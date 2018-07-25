<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
session_start();
$config = $this->module['config'];
$operation = $_GPC['op'] ? : '';
$user_id = $_SESSION['user_id'];
if($operation=='login') {
	//登陆
	include $this->template('partner/login');
}  else if($operation=='find_pass') {
	//找回密码
	include $this->template('partner/find_pass');
} else if ($operation == 'login_submit') {
	//登陆提交
	if (!empty($user_id)) {
		header("location:{$this->createMobileUrl('partner', array('op' => 'default'))}");
	}
	$phone = trim($_GPC['phone']);
	$password = trim($_GPC['password']);
	if (empty($phone)) {
		message('手机号不能为空', '', 'error');
	}
	if (empty($password)) {
		message('密码不能为空', '', 'error');
	}
	$record = pdo_fetch('select id,pass from ' . tablename('xuan_mixloan_member') . '
	 	where phone=:phone', array(':phone' => $phone));
	if (empty($record)) {
		message('用户不存在', '', 'error');
	}
	if ($record['pass'] != $password) {
		message('密码不正确', '', 'error');
	}
	if ($member['backstage'] != 1) {
		message('该用户没有权限，请联系管理员设置权限', '', 'error');
	}
	$_SESSION['user_id'] = $record['id'];
	header("location:{$this->createMobileUrl('partner', array('op' => 'default'))}");
} else if ($operation == 'find_pass_submit') {
	//找回密码提交
	$phone = trim($_GPC['phone']);
	$password = trim($_GPC['password']);
	$smscode = trim($_GPC['smscode']);
	if (empty($phone)) {
		message('手机号不能为空', '', 'error');
	}
	if (empty($password)) {
		message('密码不能为空', '', 'error');
	}
	if (empty($smscode)) {
		message('短信不能为空', '', 'error');
	}
	if (md5($smscode.$phone) != $_COOKIE['cache_code']) {
		message('验证码不符或验证码已失效', '', 'error');
	}
	$record = pdo_fetch('select id from ' . tablename('xuan_mixloan_member') . '
	 	where phone=:phone', array(':phone' => $phone));
	if (empty($record)) {
		message('用户不存在', '', 'error');
	}
	pdo_update('xuan_mixloan_member', array('pass' => $password), array('id' => $record['id']));
	message('找回密码成功', $this->createMobileUrl('partner', array('op' => 'login')), 'sccuess');
} else if ($operation == 'loginout') {
	// 退出
	unset($_SESSION['user_id']);
	header("location:{$this->createMobileUrl('partner', array('op' => 'login'))}");
} else if ($operation == 'default') {
	// 首页
	if (empty($user_id)) {
		message('用户不存在', '', 'error');
	}
	$member = pdo_fetch('select * from ' . tablename('xuan_mixloan_member') . '
		where id=:id', array(':id' => $user_id));
	$count_all = pdo_fetchcolumn('select sum(done_bonus+re_bonus+extra_bonus) from ' . tablename('xuan_mixloan_member') . '
		where inviter=:inviter', array(':inviter' => $user_id)) ? : 0;
	$withdraw_all = pdo_fetchcolumn('select sum(bonus) from ' . tablename('xuan_mixloan_member') . '
		where uid=:uid', array(':uid' => $user_id)) ? : 0;
	$can_withdraw = $count_all - $withdraw_all;
	$one_degree_apply = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_product_apply') . '
		where inviter=:inviter and degree=1 and pid<>0', array(':inviter' => $user_id)) ? : 0;
	$all_degree_apply = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_product_apply') . '
		where inviter=:inviter and pid<>0', array(':inviter' => $user_id)) ? : 0;
	include $this->template('partner/default');
} else if ($operation == 'apply_data') {
	// 首页
    $psize = 20;
    $pindex = max(1, intval($_GPC['page']));
	$where = '';
	$realname = trim($_GPC['realname']);
	$phone = trim($_GPC['phone']);
	$product = trim($_GPC['product']);
	if (empty($user_id)) {
		message('用户不存在', '', 'error');
	}
	$member = pdo_fetch('select * from ' . tablename('xuan_mixloan_member') . '
		where id=:id', array(':id' => $user_id));
	$cond = array(':inviter' => $member['id']);
	if ($realname) {
		$where .= " and realname like :realname";
		$cond[':realname'] = '%' . $realname . '%'; 
	}
	if ($phone) {
		$where .= " and phone like :phone";
		$cond[':phone'] = '%' . $phone . '%'; 
	}
	if ($product) {
		$pids = array();
		$products = pdo_fetchall('select id from ' . tablename('xuan_mixloan_product') . "
			where name like :name ", array(':name' => '%' . $product . '%'));
		foreach ($products as $value) {
			$pids[] = $value['id'];
		}
		if ($pids) {
			$pid_string = implode(',', $pids);
			$where .= " and pid in ({$pid_string})";
		}
	}
	$sql = 'select * from ' . tablename('xuan_mixloan_product_apply') . ' where 
			inviter=:inviter ' . $where . ' order by id desc';
	$sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
	$list = pdo_fetchall($sql, $cond);
	foreach ($list as &$row) {
		$pro = m('product')->getList(['id', 'name', 'ext_info'], ['id' => $row['pid']])[$row['pid']];
		$row['product_name'] = $pro['name'];
		$row['product_logo'] = tomedia($pro['ext_info']['logo']);
		$row['bonus'] = $row['re_bonus'] + $row['done_bonus'] + $row['extra_bonus'];
	}
	unset($row);
    $total = pdo_fetchcolumn( 'select count(*) from ' . tablename('xuan_mixloan_product_apply') . ' where 
			inviter=:inviter ' . $where, $cond);
    $pager = pagination($total, $pindex, $psize);
	include $this->template('partner/apply_data');
}