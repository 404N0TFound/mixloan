<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='register';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
if($operation=='register'){
	//注册
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
    if ($config['backup']) {
        //开启备份
        $record = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename("xuan_mixloan_member")."
            WHERE phone=:phone", array(':phone'=>$phone));
        if ($record) {
            show_json(1, ['url'=>$this->createMobileUrl('index', ['op'=>'find_user'])], "查找到此手机绑定过用户信息，建议使用找回账号功能");
        }
    }
	//更新操作
	$arr = ['phone'=>$phone, 'pass'=>$pwd];
	pdo_update('xuan_mixloan_member', $arr, ['id'=>$member['id']]);
	show_json(1, ['url'=>$this->createMobileUrl('vip', ['op'=>'buy'])], "注册成功");
} else if ($operation == 'find_user') {
    //找回账号
    if (!$config['backup']) {
        message('找回账号暂未开放', $this->createMobileUrl('user'), 'error');
    }
    include $this->template('index/find_user');
} else if ($operation == 'find_user_submit') {
    //找回账号提交
    $phone = trim($_GPC['phone']);
    if (!$config['backup']) {
        show_json(-1, [], '找回账号暂未开放');
    }
    if (!empty($member['phone'])) {
        show_json(-1, [], '您的手机已绑定，无法使用此功能');
    }
    $smsCode = $_GPC['smsCode'];
    if (md5($phone.$smsCode) != $_COOKIE['cache_code']) {
        show_json(-1, [], "验证码不符或验证码已失效");
    }
    $old_man = pdo_fetch('SELECT id,nickname FROM ' .tablename('xuan_mixloan_member'). '
        WHERE phone=:phone ORDER BY id DESC', array(':phone'=>$phone));
    if (empty($old_man)) {
        show_json(-1, [], '该手机号未绑定任何信息');
    }
    show_json(1, [], "你要找回的账号昵称为{$old_man['nickname']}");
} else if ($operation == 'post_find') {
    //找回账号提交
    $phone = trim($_GPC['phone']);
    if (!$config['backup']) {
        show_json(-1, [], '找回账号暂未开放');
    }
    if (!empty($member['phone'])) {
        show_json(-1, [], '您的手机已绑定，无法使用此功能');
    }
    $smsCode = $_GPC['smsCode'];
    if (md5($phone.$smsCode) != $_COOKIE['cache_code']) {
        show_json(-1, [], "验证码不符或验证码已失效");
    }
    $old_man = pdo_fetch('SELECT id,openid,uniacid,uid FROM ' .tablename('xuan_mixloan_member'). '
        WHERE phone=:phone ORDER BY id DESC', array(':phone'=>$phone));
    if (empty($old_man)) {
        show_json(-1, [], '该手机号未绑定任何信息');
    }
    pdo_update('xuan_mixloan_member', array('openid'=>$openid, 'uniacid'=>$_W['uniacid'], 'uid'=>$member['uid']), array('id'=>$old_man['id']));
    pdo_update('qrcode_stat', array('openid'=>$openid, 'uniacid'=>$_W['uniacid']), array('openid'=>$old_man['openid']));
    pdo_update('xuan_mixloan_inviter', array('uniacid'=>$_W['uniacid']), array('uid'=>$old_man['id']));
    pdo_update('xuan_mixloan_friend', array('openid'=>$openid), array('openid'=>$old_man['openid']));
    pdo_update('xuan_mixloan_post_looks', array('openid'=>$openid), array('openid'=>$old_man['openid']));
    pdo_update('xuan_mixloan_friend_comment', array('openid'=>$openid), array('openid'=>$old_man['openid']));
    pdo_update('xuan_mixloan_member', array('openid'=>$old_man['openid'], 'uniacid'=>$old_man['uniacid'], 'uid'=>$old_man['uid']), array('id'=>$member['id']));
    show_json(1, ['url'=>$this->createMobileUrl('user', ['op'=>''])], "找回账户成功");
}