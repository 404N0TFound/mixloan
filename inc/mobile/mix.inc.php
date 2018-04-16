<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='service';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
if($operation=='service'){
	//客服服务
	$inviter = m('member')->getInviter($member['phone'], $member['openid']);
	if ($inviter) {
		$inviterInfo = pdo_fetch("SELECT phone,avatar,qrcode,nickname FROM ".tablename("xuan_mixloan_member"). " WHERE id={$inviter}");
	}
	include $this->template('mix/service');
} else if ($operation == 'tutorials') {
	//新手指南
	include $this->template('mix/tutorials');
} else if ($operation == 'question') {
	//常见问题
	$questions = pdo_fetchall("SELECT * FROM ".tablename('xuan_mixloan_help')." WHERE uniacid=:uniacid AND type=1", array(':uniacid'=>$_W['uniacid']));
	foreach ($questions as &$question) {
		$question['ext_info'] = json_decode($question['ext_info'], 1);
	}
	unset($question);
	include $this->template('mix/question');
} else if ($operation == 'announce') {
	//常见问题
	$announces = pdo_fetchall("SELECT * FROM ".tablename('xuan_mixloan_help')." WHERE uniacid=:uniacid AND type=2", array(':uniacid'=>$_W['uniacid']));
	foreach ($announces as &$announce) {
		$announce['ext_info'] = json_decode($announce['ext_info'], 1);
	}
	unset($announce);
	include $this->template('mix/announce');
}
?>