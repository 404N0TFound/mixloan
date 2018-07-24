<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
$operation = $_GPC['op'] ? : '';
if($operation=='login') {
	//登陆
	include $this->template('partner/login');
}  else if($operation=='find_pass') {
	//找回密码
	include $this->template('partner/find_pass');
} else if ($operation == 'login_submit') {
	//登陆提交

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
	message('找回密码成功', $this->createMobileUrl('partner', array('op' => 'login')), 'error');
}