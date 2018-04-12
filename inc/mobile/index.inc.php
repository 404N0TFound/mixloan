<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='register';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
if($operation=='register'){
	//注册
	header("location:{$this->createMobileUrl('user')}");
	include $this->template('index/register');
} elseif ($operation == 'register_contract') {
	//注册协议
	include $this->template('index/register_contract');
} else if ($operation == 'register_ajax') {
	//注册提交
	$phone = $_GPC['phone'];
	$pwd = $_GPC['pwd'];
	$smsCode = $_GPC['smsCode'];
	if (md5($smsCode) != $_COOKIE['cache_code']) {
		show_json(-1, null, "验证码不符或验证码已失效");
	}
	if (!empty($member['phone'])) {
		show_json(-1, null, "您的手机已绑定");
	}
	$res = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename("xuan_mixloan_member")." WHERE phone=:phone AND uniacid=:uniacid", array(':phone'=>$phone, ':uniacid'=>$_W['uniacid']));
	if ($res) {
		show_json(-1, null, "手机已绑定");
	}
	//邀请处理
	$qrcid = pdo_fetchcolumn("SELECT `qrcid` FROM ".tablename("qrcode_stat")." WHERE openid=:openid AND uniacid=:uniacid AND type=1 ORDER BY id DESC",array(":openid"=>$openid,":uniacid"=>$_W["uniacid"]));
	if ($qrcid) {
		$res_i = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename("xuan_mixloan_inviter")." WHERE phone=:phone AND uid=:uid ORDER BY id DESC",array(":uid"=>$qrcid,":phone"=>$phone));
		if (!$res_i && $qrcid!=$member['id']) {
			$insert_i = array(
				'uniacid' => $_W['uniacid'],
				'uid' => $qrcid,
				'phone' => $phone,
				'createtime' => time(),
			);
			pdo_insert('xuan_mixloan_inviter', $insert_i);
		}
	}
	//更新操作
	$arr = ['phone'=>$phone, 'pass'=>$pwd];
	pdo_update('xuan_mixloan_member', $arr, ['id'=>$member['id']]);
	show_json(1, ['url'=>$this->createMobileUrl('vip', ['op'=>'buy'])], "注册成功");
}
?>