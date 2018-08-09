<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='index';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$member['user_type'] = m('member')->checkAgent($member['id']);
if($operation=='index'){
	//会员中心
    if ($_GPC['inviter']) {
        m('member')->checkFirstInviter($openid, $_GPC['inviter']);
    }
    if (empty($member['phone'])) {
        message('请先绑定手机', $this->createMobileUrl('index', array('op'=>'register')), 'error');
    }
    $partner = m('member')->checkPartner($member['id']);
    if ($partner['code'] == 1) {
        $member['user_type']['name'] = '合伙人';
    }
    $inviter = m('member')->getInviter($member['phone'], $openid);
    $inviterInfo = m('member')->getInviterInfo($inviter);
	$all = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE uniacid={$_W['uniacid']} AND inviter={$member['id']}");
	$used = m('member')->sumWithdraw($member['id']);
	$use = $all - $used;
	if (!$all) $all = 0;
	if (!$used) $used = 0;
	if (!$use) $use = 0;
	$all = number_format($all, 2);
	$used = number_format($used, 2);
	$use = number_format($use, 2);
	include $this->template('user/index');
} else if ($operation == 'bind_card') {
	//绑卡
	include $this->template('user/bind_card');
} else if ($operation == 'bind_qrcode') {
	//绑卡
	include $this->template('user/bind_card_qrcode');
} else if ($operation == 'checkBank') {
	//查银行卡是哪家的
	$bankno = trim($_GPC['cardNo']);
	if ($config['jdwx_open']) {
		$res = m('jdwx')->bankcardinfo($config['jdwx_key'], $bankno);
		if ($res['code']==1) {
			show_json(1, ['bankname'=>$res['data']['bank_name']]);
		} else {
			show_json(-1, [], $res['msg']);
		}
	} else {
		show_json(1, ['bankname'=>'未知']);
	}
} else if ($operation == 'bind_card_submit') {
	//验证银行卡
	$user_name = trim($_GPC['user_name']);
	$id_card = trim($_GPC['id_card']);
	$bank_num = trim($_GPC['bank_num']);
	$bank_name = trim($_GPC['bank_name']);
	$phone = trim($_GPC['phone']);
	if (!$user_name || !$id_card || !$bank_num || !$bank_name || !$phone) {
			show_json(-1, [], '参数不能为空');
	}
	if ($config['jdwx_open']) {
		$res = m('jdwx')->QryBankCardBy4Element($config['jdwx_key'], $bank_num, $user_name, $id_card, $phone);
		if ($res['code']!=1) {
			show_json(-1, [], $res['msg']);
		}
	} 
	$insert = array(
		'uniacid'=>$_W['uniacid'],
		'realname'=>$user_name,
		'phone' =>$phone,
		'bankname'=>$bank_name,
		'certno'=>$id_card,
		'banknum'=>$bank_num,
		'createtime'=>time(),
		'uid'=>$member['id']
	);
	pdo_insert('xuan_mixloan_creditCard', $insert);
	show_json(1);
} else if ($operation == 'bank_img') {
	//上传收款二维码接口
	$name = trim($_GPC['name']);
	$headimgurl = trim($_GPC['headimgurl']);
	if (empty($name) || empty($headimgurl)) {
		show_json(-1, [], "缺少上传参数");
	}
	$insert = array(
		'name'=>$name,
		'uid'=>$member['id'],
		'img_url'=>$headimgurl,
		'createtime'=>time(),
		'uniacid'=>$_W['uniacid'],
	);
	pdo_insert('xuan_mixloan_withdraw_qrcode', $insert);
	show_json(1);
} else if ($operation == 'set') {
	//修改资料
	$agent = pdo_fetch('SELECT id FROM '.tablename('xuan_mixloan_payment').' WHERE uid=:uid', array(':uid'=>$member['id']));
	if ($agent) {
		$inviter = pdo_fetchcolumn('SELECT b.nickname FROM '.tablename('xuan_mixloan_product_apply').' a LEFT JOIN '.tablename('xuan_mixloan_member').' b ON a.inviter=b.id WHERE a.uid=:uid ORDER BY a.id ASC', array(':uid'=>$member['id']));
		$agent['inviter'] = $inviter ? : '平台';
	}
	include $this->template('user/set');
} else if ($operation == 'uploadImage') {
	//上传图片
	$base_64 = trim($_GPC['carousel']);
	$res = base64_image_content($base_64, XUAN_MIXLOAN_PATH."data/avatar");
	if ($res) {
		$avatar_url = getNowHostUrl()."/addons/xuan_mixloan/data/avatar/{$res}";
		show_json(1, ['avatar_url'=>$avatar_url]);
	} else {
		show_json(-1);
	}
} else if ($operation == 'setData') {
	//上传资料
	if(!trim($_GPC['realname']) || !trim($_GPC['idcard']) || !trim($_GPC['wechatnum'])) {
		show_json(-1, [], '资料不能留空');
	}
	if ($config['jdwx_open'] == 1) {
//		$res = m('jdwx')->jd_credit_three($config['jdwx_key'], trim($_GPC['realname']), trim($_GPC['phone']), trim($_GPC['idcard']));
//		if ($res['code'] == -1) {
//			show_json($res['code'], [], $res['msg']);
//		}
	}
	pdo_update('xuan_mixloan_member', array(
		'avatar'=>trim($_GPC['headimgurl']),
		'nickname'=>trim($_GPC['nickname']),
		'wechat'=>trim($_GPC['wechatnum']),
		'realname'=>trim($_GPC['realname']),
		'certno'=>trim($_GPC['idcard']),
	), array('id'=>$member['id']));
	show_json(1);
} else if ($operation == 'send_msg') {
	//是否发送信息
	$res = pdo_update('xuan_mixloan_payment', array('msg'=>$_GPC['template_type']), array('id'=>$member['user_type']['id']));
	if ($res) {
		show_json(1);		
	} else {
		show_json(-1);
	}
} else if ($operation == 'extend_bonus') {
    $agent = m('member')->checkAgent($member['id']);
    $temp_time = date('Y-m-d',time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600);
    $start_time = strtotime($temp_time);
    $end_time = strtotime("+1 weeks {$temp_time}");
    $phones = [];
    for ($i=0; $i < 10; $i++) {
        $phones[] = rand(1111,9999);
    }
    $list = pdo_fetchall("SELECT inviter,SUM(re_bonus) AS bonus FROM ".tablename('xuan_mixloan_product_apply')." WHERE pid=0 AND createtime>{$start_time} AND createtime<{$end_time} GROUP BY inviter HAVING bonus<>0 ORDER BY bonus DESC");
    if (!empty($list)) {
        foreach ($list as &$row) {
            $temp_member = pdo_fetch("SELECT nickname,avatar,phone FROM ".tablename('xuan_mixloan_member').' WHERE id=:id', array(':id'=>$row['inviter']));
            $row['nickname'] = $temp_member['nickname'];
            $row['avatar'] = $temp_member['avatar'];
            $row['phone'] = substr($temp_member['phone'], 0, 4) . '****' . substr($temp_member['phone'], -3, 3);
        }
        unset($row);
    }
    $follow_count = pdo_fetchcolumn("SELECT count(1) FROM ".tablename("qrcode_stat")." a LEFT JOIN ".tablename("mc_mapping_fans"). " b ON a.openid=b.openid WHERE a.qrcid={$member['id']} AND a.type=1 ORDER BY id DESC") ? : 0;
    $money_count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$member['id']} AND pid=0") ? : 0;
    include $this->template('user/extend_bonus');
}