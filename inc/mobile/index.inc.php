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
	$inviter = pdo_fetchcolumn("SELECT uid FROM ".tablename("xuan_mixloan_inviter")." WHERE phone=:phone ORDER BY id DESC",array(":phone"=>$phone));
	$qrcid = pdo_fetchcolumn("SELECT `qrcid` FROM ".tablename("qrcode_stat")." WHERE openid=:openid AND uniacid=:uniacid AND type=1 ORDER BY id ASC",array(":openid"=>$openid,":uniacid"=>$_W["uniacid"]));
	if ($inviter) {
		if ($inviter != $qrcid) {
			pdo_update('qrcode_stat', array('type'=>2), array('openid'=>$openid));
			$insert =array(
                'uniacid'=>$_W['uniacid'],
                'acid'=>0,
                'qid'=>0,
                'openid'=>$openid,
                'type'=>1,
                'qrcid'=>$inviter,
                'scene_str'=>$inviter,
                'createtime'=>time(),
            );
            pdo_insert('qrcode_stat', $insert);
		}
	} else {
		$insert_i = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $qrcid,
			'phone' => $phone,
			'createtime' => time(),
		);
		pdo_insert('xuan_mixloan_inviter', $insert_i);
	}
    //更新操作
    if ($config['backup'] == 1) {
        //开启备份
        $old_man = pdo_fetch('SELECT id,openid,uniacid,uid FROM '.tablename('xuan_mixloan_member').' WHERE phone=:phone ORDER BY id DESC', array(':phone'=>$phone));
        if (!empty($old_man['openid']) && $old_man['openid'] != $openid) {
            pdo_update('xuan_mixloan_member', array('openid'=>$openid, 'pass'=>$pwd, 'uniacid'=>$_W['uniacid'], 'uid'=>$member['uid']), array('id'=>$old_man['id']));
            pdo_update('xuan_mixloan_friend', array('openid'=>$openid), array('openid'=>$old_man['openid']));
            pdo_update('qrcode_stat', array('openid'=>$openid, 'uniacid'=>$_W['uniacid']), array('openid'=>$old_man['openid']));
            pdo_update('xuan_mixloan_post_looks', array('openid'=>$openid), array('openid'=>$old_man['openid']));
            pdo_update('xuan_mixloan_friend_comment', array('openid'=>$openid), array('openid'=>$old_man['openid']));
            pdo_update('xuan_mixloan_member', array('openid'=>$old_man['openid'], 'pass'=>$pwd, 'uniacid'=>$old_man['uniacid'], 'uid'=>$old_man['uid']), array('id'=>$member['id']));
            show_json(1, ['url'=>$this->createMobileUrl('user', ['op'=>''])], "找回账户成功");
        } else {
            $arr = ['phone'=>$phone, 'pass'=>$pwd];
            pdo_update('xuan_mixloan_member', $arr, ['id'=>$member['id']]);
            show_json(1, ['url'=>$this->createMobileUrl('vip', ['op'=>'buy'])], "注册成功");
        }
    } else {
        //更新操作
        $arr = ['phone'=>$phone, 'pass'=>$pwd];
        pdo_update('xuan_mixloan_member', $arr, ['id'=>$member['id']]);
        show_json(1, ['url'=>$this->createMobileUrl('vip', ['op'=>'buy'])], "注册成功");
    }
}
?>