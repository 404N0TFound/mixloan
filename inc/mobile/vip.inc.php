<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$agent = m('member')->checkAgent($member['id']);
if($operation=='buy'){
	//购买会员
	if ($agent['code']==1) {
		$verify = 1;
	} else {
		$verify = 0;
	}
	include $this->template('vip/buy');
} else if ($operation == 'pay') {
	//付钱
	if (!$member['phone']) {
		message('请先绑定手机号', $this->createMobileUrl('index'), 'error');
	}
	$tid = "10001" . date('YmdHis', time());
	$title = "购买{$config['title']}代理会员";
	$fee = $config['buy_vip_price'];
	$params = array(
	    'tid' => $tid, 
	    'ordersn' => $tid, 
	    'title' => $title, 
	    'fee' => $fee, 
	    'user' => $member['id'], 
	);
	//调用pay方法
	$this->pay($params);
	exit;
} else if ($operation == 'createPost') {
	if ($agent['code'] != 1) {
	    show_json(-1, [], '您不是会员');
	}
	$type = intval($_GPC['type']);//1是关联产品,2是直接全部代理
	if ($type == 1) {
		$id = intval($_GPC['id']);
		$product = m('product')->getList(['id','ext_info'], ['id'=>$id])[$id];
		$cfg = [];
		$cfg['logo'] = $config['logo'];
		$cfg['poster_avatar'] = $product['ext_info']['poster_avatar'];
		$cfg['poster_image'] = $product['ext_info']['poster_image'];
		$cfg['poster_color'] = $product['ext_info']['poster_color'];
		$url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'apply', 'id'=>$id, 'inviter'=>$member['id']));
    	$out = XUAN_MIXLOAN_PATH."data/poster/{$id}_{$member['id']}.png";
    	$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/{$id}_{$member['id']}.png";
	} else {
		$id = 0;
		$cfg = $config;
		$url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'allProduct', 'inviter'=>$member['id']));
    	$out = XUAN_MIXLOAN_PATH."data/poster/{$member['id']}.png";
    	$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/{$member['id']}.png";
	}
	$poster = m('poster')->getPoster(["COUNT(1) AS count"], ["pid"=>$id, "type"=>$type, "uid"=>$member['id']]);
	if (!$poster["count"]) {
		$params = array(
			"url" => $url,
			"member" => $member,
			"type" => $type,
			"pid" => $id,
			"out" => $out,
			"poster_path" => $poster_path
		);
		$res = m('poster')->createPoster($cfg, $params);
		if ($res) {
	        show_json(1, ['post_url'=>$poster_path, 'agent_url'=>$url]);
		} else {
	        show_json(-1, [], '生成海报失败，请检查海报背景图上传是否正确');
		}
	} else {
		show_json(2, ['post_url'=>$poster_path, 'agent_url'=>$url]);
	}
	
} else if ($operation == 'createPostAllProduct') {
	//我的代理店
	if ($agent['code']==1) {
		$verify = 1;
	} else {
		$verify = 0;
	}
	$pids = pdo_fetchall("SELECT pid FROM ".tablename("xuan_mixloan_poster")." WHERE uid=:uid", array(":uid"=>$member['id']));
	if ($pids) {
		foreach ($pids as $value) {
			$res[] = $value['pid'];
		}
		$pids_string = '(' . implode(',', $res) . ')';
		$re = pdo_fetch("SELECT id,name FROM ".tablename("xuan_mixloan_product"). " WHERE id NOT IN {$pids_string} LIMIT 1");
	} else {
		$re = pdo_fetch("SELECT id,name FROM ".tablename("xuan_mixloan_product"). " LIMIT 1");
	}
	include $this->template('vip/createPostAllProduct');
} else if ($operation == 'posterAll') {
	//全部海报图片
	$url = shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'allProduct', 'inviter'=>$member['id'])) );
	$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/{$member['id']}.png";
	include $this->template('vip/posterAll');
} else if ($operation == 'salary') {
	//我的工资
	if ($agent['code']==1) {
		$verify = 1;
	} else {
		$verify = 0;
	}
	$bonus = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE uniacid={$_W['uniacid']} AND inviter={$member['id']}");
	$can_use = $bonus - m('member')->sumWithdraw($member['id']);
	$bonus = formatMoney($bonus);
	$can_use = formatMoney($can_use);
	$percent_list = m('product')->getApplyList([], ['inviter'=>$member['id'], 'la_status'=>0]);
	foreach ($percent_list as $row) {
		$ids[] = $row['pid'];
	}
	$pros = m('product')->getList(['id', 'count_time', 'name', 'ext_info'], ['id'=>$ids]);
	foreach ($percent_list as &$row) {
		if ($row['pid'] == 0){
			$row['name'] = '邀请购买代理';
			$row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
		} else {
			$row['name'] = $pros[$row['pid']]['name'];
			$row['logo'] = $pros[$row['pid']]['ext_info']['logo'];
		}
		if ($row['pid'] == 0 || $pros[$row['pid']]['count_time'] == 1) {
			$row['type'] = '日结';
		} else if ($pros[$row['pid']]['count_time'] == 7) {
			$row['type'] = '周结';
		} else if ($pros[$row['pid']]['count_time'] == 7) {
			$row['type'] = '月结';
		}
		$row['tid'] = date('YmdHis',$row['createtime']) . $row['id'];
		$row['count_money'] = number_format($row['re_bonus'] + $row['done_bonus'] + $row['extra_bonus'], 2);
	}
	unset($row);
	$accounts_list = pdo_fetchall("SELECT a.id,a.bonus,a.createtime,b.banknum,b.bankname FROM ".tablename("xuan_mixloan_withdraw")." a LEFT JOIN ".tablename("xuan_mixloan_creditCard")." b ON a.bank_id=b.id WHERE a.uid={$member['id']} ORDER BY id DESC");
	foreach ($accounts_list as &$row) {
		$row['tid'] = date('YmdHis', $row['createtime']) . $row['id'];
		$row['year'] = date('m-d', $row['createtime']);
		$row['hour'] = date('H:i', $row['createtime']);
		if ($row['banknum']) {
			$row['bankmes'] =  "{$row['bankname']} 尾号(" . substr($row['banknum'], -4) . ")";
		} else {
			$row['bankmes'] = '微信零钱';
		}
		switch ($row['status']) {
			case '0':
				$row['status'] = '申请中';
				break;
			case '1':
				$row['status'] = '提现成功';
				break;
			case '-1':
				$row['status'] = '提现失败';
				break;
		}
	}
	unset($row);
	include $this->template('vip/salary');
} else if ($operation == 'withdraw') {
	//提现
	// $banks = pdo_fetchall("SELECT id,bankname,banknum FROM ".tablename("xuan_mixloan_creditCard")." WHERE uid=:uid", array(':uid'=>$member['id']));
	// foreach ($banks as &$row) {
	// 	if (count($row['banknum']) == 16) {
	// 		$row['numbers_type'] = 1;
	// 		$row['numbers'][0] = substr($row['banknum'], 0, 4);
	// 		$row['numbers'][1] = substr($row['banknum'], 4, 4);
	// 		$row['numbers'][2] = substr($row['banknum'], 8, 4);
	// 		$row['numbers'][3] = substr($row['banknum'], 12, 4);
	// 	} else {
	// 		$row['numbers_type'] = 2;
	// 		$row['numbers'][0] = substr($row['banknum'], 0, 6);
	// 		$row['numbers'][1] = substr($row['banknum'], 6);
	// 	}
	// }
	// unset($row);
	$qrcodes = pdo_fetchall("SELECT id,name,img_url FROM ".tablename('xuan_mixloan_withdraw_qrcode'). " WHERE uid=:uid", array(':uid'=>$member['id']));
	$bonus = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE uniacid={$_W['uniacid']} AND inviter={$member['id']}");
	$can_use = $bonus - m('member')->sumWithdraw($member['id']);
	include $this->template('vip/withdraw');
} else if ($operation == 'withdraw_submit') {
	//提现提交
	$bonus = trim($_GPC['money']);
	$bank_id = intval($_GPC['card_id']);
	if (!$bonus) {
		show_json(-1, null, "提现金额不能为0");
	}
	if (!$bank_id) {
		show_json(-1, null, "请选择提现银行卡");
	}
	$all = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE uniacid={$_W['uniacid']} AND inviter={$member['id']}");
	$used = m('member')->sumWithdraw($member['id']);
	$use = $all - $used;
	if ($bonus > $use) {
		show_json(-1, null, "可提现余额不足");
	}
	$insert = array(
		'uniacid'=>$_W['uniacid'],
		'uid'=>$member['id'],
		'bank_id'=>$bank_id,
		'bonus'=>$bonus,
		'createtime'=>time(),
		'status'=>0
	);
	pdo_insert('xuan_mixloan_withdraw', $insert);
	show_json(1, null, "提现成功");
} else if ($operation == 'inviteCode') {
	//邀请二维码
	if ($agent['code'] != 1) {
	    message('您不是会员', '', 'error');
	}
	$poster_path = pdo_fetchcolumn('SELECT poster FROM '.tablename('xuan_mixloan_poster').' WHERE uid=:uid AND type=:type', array(':uid'=>$member['id'], ':type'=>3));
	if (!$poster_path) {
        if ($config['wx_invite_code']) {
            $wx = WeAccount::create();
            $barcode = array(
                'action_name'=>"QR_LIMIT_SCENE",
                'action_info'=> array(
                    'scene' => array(
                        'scene_id'=>$member['id'],
                    )
                )
            );
            $res = $wx->barCodeCreateDisposable($barcode);
            $url = $res['url'];
        } else {
            $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('user', array('inviter'=>$member['id']));
        }
		$cfg['logo'] = $config['logo'];
		$cfg['poster_avatar'] = $config['invite_avatar'];
		$cfg['poster_image'] = $config['invite_image'];
		$cfg['poster_color'] = $config['invite_color'];
		$out = XUAN_MIXLOAN_PATH."data/poster/invite_{$member['id']}.png";
		$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/invite_{$member['id']}.png";
		$params = array(
			"url" => $url,
			"member" => $member,
			"type" => 3,
			"pid" => 0,
			"out" => $out,
			"poster_path" => $poster_path
		);
		$invite_res = m('poster')->createPoster($cfg, $params);
	    if (!$invite_res) {
	    	message('生成海报失败，请检查海报背景图上传是否正确', '', 'error');
	    }
	}
	include $this->template('vip/inviteCode');
} else if ($operation == 'extendList') {
	//推广成功
	$extend_list = pdo_fetchall("SELECT a.uid,a.createtime,a.degree,a.re_bonus,b.nickname FROM ".tablename("xuan_mixloan_product_apply")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.uid=b.id WHERE a.inviter={$member['id']} AND a.status>0 AND pid=0 ORDER BY a.id DESC");
	$count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$member['id']} AND status>0 AND pid=0");
	$count = $count ? : 0;
	$cTime = getTime();
	$star_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]}");
	$end_time = strtotime("{$cTime[0]}-{$cTime[1]}-{$cTime[2]} +1 day");
	$today_count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$member['id']} AND status>0 AND pid=0 AND createtime>{$star_time} AND createtime<{$end_time}");
	$today_count = $today_count ? : 0;
	$star_time = strtotime("{$cTime[0]}-{$cTime[1]}-01");
	$end_time = strtotime("{$cTime[0]}-{$cTime[1]}-01 +1 month");
	$month_count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$member['id']} AND status>0 AND pid=0 AND createtime>{$star_time} AND createtime<{$end_time}");
	$month_count = $month_count ? : 0;
	include $this->template('vip/extendList');
} else if ($operation == 'degreeDetail') {
	//对应等级
	$uid = intval($_GPC['uid']);
	$list = pdo_fetchall("SELECT a.degree,b.nickname,b.avatar FROM ".tablename("xuan_mixloan_product_apply")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.inviter=b.id WHERE a.uid={$uid} ORDER BY a.degree ASC");
	$brother = pdo_fetch("SELECT nickname,avatar FROM ".tablename("xuan_mixloan_member")." WHERE id={$uid}");
	include $this->template('vip/degreeDetail');
} else if ($operation == 'followList') {
    $follow_list = pdo_fetchall(
        "SELECT a.createtime,a.openid,b.nickname,b.avatar,b.id as uid FROM " .tablename("qrcode_stat"). " a
		LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.openid=b.openid
		WHERE a.qrcid={$member['id']} AND a.type=1
		GROUP BY a.openid
		ORDER BY a.id DESC");
    foreach ($follow_list as &$row) {
        if (empty($row['uid'])) {
            $temp = pdo_fetch('select nickname from ' .tablename('mc_mapping_fans'). '
				where openid=:openid', array(':openid'=>$row['openid']));
            if ($temp) {
                $row['nickname'] = $temp['nickname'];
                $row['avatar']   = $temp['avatar'];
            } else {
                $row['nickname'] = '未更新';
                $row['avatar'] = '';
            }
            $row['uid'] = 0;
        }
        $row['agent'] = m('member')->checkAgent($row['uid']);
        $row['bonus'] = pdo_fetchcolumn("SELECT re_bonus FROM " . tablename("xuan_mixloan_product_apply") . "
			WHERE inviter={$member['id']} AND uid={$row['uid']} AND pid=0") ? : '无';
    }
    unset($row);
    $count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM " . tablename("xuan_mixloan_product_apply") . "
		WHERE inviter={$member['id']} AND status>0 AND pid=0") ? : 0;
    $follow_count = count($follow_list) ? : 0;
    include $this->template('vip/followList');
}
