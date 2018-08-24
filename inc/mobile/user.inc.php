<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='index';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$member['user_type'] = m('member')->checkAgent($member['id']);
if ($member['status'] == '0') {
    // 冻结
    die("<!DOCTYPE html>
    <html>
        <head>
            <meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'>
            <title>抱歉，出错了</title><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'><link rel='stylesheet' type='text/css' href='https://res.wx.qq.com/connect/zh_CN/htmledition/style/wap_err1a9853.css'>
        </head>
        <body>
        <div class='page_msg'><div class='inner'><span class='msg_icon_wrp'><i class='icon80_smile'></i></span><div class='msg_content'><h4>账号已冻结，联系客服处理</h4></div></div></div>
        </body>
    </html>");
}
if($operation=='index'){
	//会员中心
    if ($_GPC['inviter']) {
        m('member')->checkFirstInviter($openid, $_GPC['inviter']);
    }
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
}else if ($operation == 'set') {
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
	if(!trim($_GPC['realname']) || !trim($_GPC['phone']) || !trim($_GPC['idcard']) || !trim($_GPC['wechatnum'])) {
		show_json(-1, [], '资料不能留空');
	}
	if ($config['jdwx_open'] == 1) {
		// $res = m('jdwx')->jd_credit_three($config['jdwx_key'], trim($_GPC['realname']), trim($_GPC['phone']), trim($_GPC['idcard']));
		// if ($res['code'] == -1) {
		// 	show_json($res['code'], [], $res['msg']);
		// }
	}
	pdo_update('xuan_mixloan_member', array(
		'avatar'=>trim($_GPC['headimgurl']),
		'nickname'=>trim($_GPC['nickname']),
		'wechat'=>trim($_GPC['wechatnum']),
		'realname'=>trim($_GPC['realname']),
		'phone'=>trim($_GPC['phone']),
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
} else if ($operation == 'delete_qrcode') {
    //删除二维码
    $id = intval($_GPC['id']);
    if (empty($id)) {
        show_json(-1, [], '出错了');
    }
    pdo_update('xuan_mixloan_creditCard', array('status' => 0), array('id' => $id));
    show_json(1, [], '删除成功');
} else if ($operation == 'bind_alipay') {
    //绑支付宝
    include $this->template('user/bind_alipay');
} else if ($operation == 'bind_alipay_submit') {
    //验证银行卡
    $realname = trim($_GPC['realname']);
    $phone = trim($_GPC['phone']);
    if (!$realname || !$phone) {
        show_json(-1, [], '参数不能为空');
    }
    $insert = array(
        'uniacid'=>$_W['uniacid'],
        'realname'=>$realname,
        'phone' =>$phone,
        'createtime'=>time(),
        'uid'=>$member['id'],
        'type'=>2
    );
    pdo_insert('xuan_mixloan_creditCard', $insert);
    show_json(1);
}