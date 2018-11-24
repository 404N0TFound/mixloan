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
	if ($member['id'] == 5223) {
		$config['buy_vip_price'] = 0.01;
	}
    if (is_weixin())
    {
        $tid = "10001" . date('YmdHis', time());
        $title = "购买{$config['title']}代理会员";
        $fee = $config['buy_vip_price'];
        $params = array(
            'tid' => $tid,
            'ordersn' => $tid,
            'title' => $title,
            'fee' => $fee,
            'user' => $openid,
        );
        //调用pay方法
        $this->pay($params);
    }
    else
    {
        $tid = "10001" . date('YmdHis', time());
        $title = "购买{$config['title']}代理会员";
        $fee = $config['buy_vip_price'];
        $params = array(
            'tid' => $tid,
            'ordersn' => $tid,
            'title' => $title,
            'fee' => $fee,
            'user' => $openid,
            'module' => 'xuan_mixloan'
        );
        $insert = array(
            'openid' => $openid,
            'uniacid' => $_W['uniacid'],
            'acid' => $_W['uniacid'],
            'tid' => $tid,
            'fee' => $fee,
            'status' => 0,
            'module' => 'xuan_mixloan',
            'card_fee' => $fee,
        );
        pdo_insert('core_paylog', $insert);
        $url = url('mc/cash/alipay') . "&params=" . base64_encode(json_encode($params));
        include $this->template('vip/openHref');
    }
    exit;
} else if ($operation == 'createPost') {
	if ($agent['code'] != 1) {
	    show_json(-1, [], '您不是会员');
	}
	$type = intval($_GPC['type']);//1是关联产品,2是直接全部代理
	if ($type == 1) {
		$id = intval($_GPC['id']);
		$product = m('product')->getList(['id','ext_info', 'type', 'relate_id'], ['id'=>$id])[$id];
		$cfg = [];
		$cfg['logo'] = $config['logo'];
		$cfg['poster_avatar'] = $product['ext_info']['poster_avatar'];
		$cfg['poster_image'] = $product['ext_info']['poster_image'];
		$cfg['poster_color'] = $product['ext_info']['poster_color'];
        if ($product['type'] == 1){
            $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'apply', 'id'=>$id, 'inviter'=>$member['id']));
        } else {
            $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$product['relate_id'], 'pid'=>$id, 'inviter'=>$member['id']));
        }
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
			"url" => shortUrl($url),
			"member" => $member,
			"type" => $type,
			"pid" => $id,
			"out" => $out,
			"poster_path" => $poster_path
		);
		$res = m('poster')->createPoster($cfg, $params);
		if ($res) {
	        show_json(1, ['post_url'=>$poster_path, 'agent_url'=>shortUrl($url)]);
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
	$percent_list = m('product')->getApplyList([], ['inviter'=>$member['id'], 'la_status'=>0], 'id desc', 50);
	foreach ($percent_list as $row) {
		$ids[] = $row['pid'];
	}
	$pros = m('product')->getList(['id', 'count_time', 'name', 'ext_info'], ['id'=>$ids]);
	foreach ($percent_list as &$row) {
        if ($row['type'] == 2){
            $row['name'] = '邀请购买代理';
            $row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
        } else if ($row['type'] == 1) {
            $row['name'] = $pros[$row['pid']]['name'];
            $row['logo'] = $pros[$row['pid']]['ext_info']['logo'];
        } else if ($row['type'] == 4) {
            $row['name'] = '前日佣金奖励';
            $row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
        }
        if ($pros[$row['pid']]['count_time'] == 1) {
            $row['type'] = '日结';
        } else if ($pros[$row['pid']]['count_time'] == 7) {
            $row['type'] = '周结';
        } else if ($pros[$row['pid']]['count_time'] == 30) {
            $row['type'] = '月结';
        }
        if ($row['type'] == 2 || $row['type'] == 4 || $row['type'] == 3) {
            $row['type'] = '现结';
        }
		$row['tid'] = date('Ymd',$row['createtime']) . $row['id'];
		$row['count_money'] = number_format($row['re_bonus'] + $row['done_bonus'] + $row['extra_bonus'], 2);
	}
	unset($row);
	$accounts_list = pdo_fetchall("SELECT a.id,a.bonus,a.createtime,b.banknum,b.bankname FROM ".tablename("xuan_mixloan_withdraw")." a LEFT JOIN ".tablename("xuan_mixloan_creditCard")." b ON a.bank_id=b.id WHERE a.uid={$member['id']} ORDER BY id DESC limit 50");
	foreach ($accounts_list as &$row) {
		$row['tid'] = date('YmdHis', $row['createtime']) . $row['id'];
		$row['year'] = date('m-d', $row['createtime']);
		$row['hour'] = date('H:i', $row['createtime']);
		$row['bankmes'] =  "{$row['bankname']} 尾号(" . substr($row['banknum'], -4) . ")";
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
	if ($bonus < 10) {
		show_json(-1, null, "提现金额要大于10");
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
    $type = intval($_GPC['type']);
    if ($type == 1) {

    } else if ($type == 2) {
        $title = $config['share_title'];
        $imgUrl = tomedia($config['share_image']);
        $desc = $config['share_desc'];
        $link = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'allProduct', 'inviter'=>$member['id']));
    } else if ($type == 3) {
        $title = $config['share_title'];
        $imgUrl = tomedia($config['share_image']);
        $desc = $config['share_desc'];
        $link = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'allProduct', 'inviter'=>$member['id']));
    }
    include $this->template('vip/inviteCode');
} else if ($operation == 'followList_bp') {
	//关注列表
    $follow_list = pdo_fetchall(
        "SELECT a.createtime,a.openid,b.nickname FROM " .tablename("qrcode_stat"). " a
		LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.openid=b.openid
		WHERE a.qrcid={$member['id']} AND a.type=1
		GROUP BY a.openid
		ORDER BY a.id DESC");
    foreach ($follow_list as &$row) {
        if (empty($row['nickname'])) {
            $row['nickname'] = pdo_fetchcolumn(
                'select nickname from ' .tablename('mc_mapping_fans'). '
				where openid=:openid', array(':openid'=>$row['openid']));
        }
    }
    unset($row);
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
    $follow_count = count($follow_list) ? : 0;
    $buy_count = pdo_fetchcolumn("SELECT count(1) FROM ".tablename("xuan_mixloan_product_apply")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.uid=b.id WHERE a.inviter={$member['id']} AND a.status>0 AND pid=0") ? : 0;
    include $this->template('vip/followList_bp');
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
} else if ($operation == 'partner_join_type') {
    //选择合伙人加入方式
    $partner = m('member')->checkPartner($member['id']);
    if ($partner['code']) {
        header("location:{$this->createMobileUrl('vip', array('op' => 'partner_center'))}");
    }
    include $this->template('vip/partner_join_type');
} else if ($operation == 'partner_buy') {
    //购买合伙人
    if (!$member['phone']) {
        message('请先绑定手机号', $this->createMobileUrl('index'), 'error');
    }
    if ($member['id'] == 5223) {
        $config['buy_partner_price'] = 0.01;
    }
    $tid = "10002" . date('YmdHis', time());
    $title = "购买{$config['title']}合伙人";
    $fee = $config['buy_partner_price'];
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
} else if ($operation == 'createPoster') {
    //生成邀请二维码
    $uid = intval($_GPC['uid']) ? : $member['id'];
    $type = intval($_GPC['type']) ? : 1;
    $pid = intval($_GPC['pid']) ? : 0;
    $member = m('member')->getInfo($uid);
    $posterArr = pdo_fetchall('SELECT poster FROM '.tablename('xuan_mixloan_poster').' WHERE uid=:uid AND type=:type AND pid=:pid', array(':uid'=>$member['id'], ':type'=>$type, ':pid'=>$pid));
    $created = true;
    if ($type == 3) {
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('user', array('op'=>'', 'inviter'=>$member['id']));
        $share_url = shortUrl( $url );
        $tips = "HI，朋友，为你介绍一款赚钱神器，推荐他人办卡办贷，日日领工资，邀你一起体验：{$share_url}";
        if (!$posterArr) {
            $created = false;
            // $wx = WeAccount::create();
            // $barcode = array(
            //     'action_name'=>"QR_LIMIT_SCENE",
            //     'action_info'=> array(
            //         'scene' => array(
            //             'scene_id'=>$member['id'],
            //         )
            //     )
            // );
            // $res = $wx->barCodeCreateDisposable($barcode);
            // $url = $res['url'];
            if (empty($config['inviter_poster'])) {
                message("请检查海报是否上传", "", "error");
            }
            foreach ($config['inviter_poster'] as $row) {
                $out = XUAN_MIXLOAN_PATH."data/poster/invite_{$member['id']}_{$row}.png";
                $poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/invite_{$member['id']}_{$row}.png";
                $params = array(
                    "poster_id" => $row,
                    "url" => $share_url,
                    "member" => $member,
                    "type" => 3,
                    "pid" => 0,
                    "out" => $out,
                    "poster_path" => $poster_path
                );
                $invite_res = m('poster')->createNewPoster($params);
                if (!$invite_res) {
                    message('生成海报失败，请检查海报背景图上传是否正确', '', 'error');
                } else {
                    $temp = [];
                    $temp['poster'] = $invite_res;
                    $posterArr[] = $temp;
                }
            }
        }
    } else if ($type == 2) {
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'allProduct', 'inviter'=>$member['id']));
        $share_url = shortUrl( $url );
        $tips = "{$config['title']}—我的随身银行：{$share_url}";
        if (!$posterArr) {
            $created = false;
            if (empty($config['product_poster'])) {
                message("请检查海报是否上传", "", "error");
            }
            foreach ($config['product_poster'] as $row) {
                $out = XUAN_MIXLOAN_PATH."data/poster/product_{$member['id']}_{$row}.png";
                $poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/product_{$member['id']}_{$row}.png";
                $params = array(
                    "poster_id" => $row,
                    "url" => $share_url,
                    "member" => $member,
                    "type" => 2,
                    "pid" => 0,
                    "out" => $out,
                    "poster_path" => $poster_path
                );
                $invite_res = m('poster')->createNewPoster($params);
                if (!$invite_res) {
                    message('生成海报失败，请检查海报背景图上传是否正确', '', 'error');
                } else {
                    $temp = [];
                    $temp['poster'] = $invite_res;
                    $posterArr[] = $temp;
                }
            }
        }
    } else if ($type == 1){
        $pid = intval($_GPC['pid']);
        $product = m('product')->getList(['id', 'type', 'ext_info', 'relate_id'], ['id'=>$pid])[$pid];
        if ($product['type'] == 1) {
            $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'apply', 'id'=>$pid, 'inviter'=>$member['id']));
        } else {
            $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$product['relate_id'], 'inviter'=>$member['id'], 'pid'=>$pid));
        }
        $share_url = shortUrl( $url );
        $tips = "{$config['title']}—我的随身银行：{$share_url}";
        if (!$posterArr) {
            $created = false;
            if (empty($product['ext_info']['poster'])) {
                message("请检查海报是否上传", "", "error");
            }
            foreach ($product['ext_info']['poster'] as $row) {
                $out = XUAN_MIXLOAN_PATH."data/poster/product_{$pid}_{$member['id']}_{$row}.png";
                $poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/product_{$pid}_{$member['id']}_{$row}.png";
                $params = array(
                    "poster_id" => $row,
                    "url" => $share_url,
                    "member" => $member,
                    "type" => 1,
                    "pid" => $pid,
                    "out" => $out,
                    "poster_path" => $poster_path
                );
                $invite_res = m('poster')->createNewPoster($params);
                if (!$invite_res) {
                    message('生成海报失败，请检查海报背景图上传是否正确', '', 'error');
                } else {
                    $temp = [];
                    $temp['poster'] = $invite_res;
                    $posterArr[] = $temp;
                }
            }
        }
    }
    $ret = array('tips'=>$tips, 'posterArr'=>$posterArr, 'created'=>$created);
    message($ret, '', 'success');
} else if ($operation == 'partner_upgrade') {
    //满足条件自动升级
    $partner = m('member')->checkPartner($member['id']);
    if ($partner['code']) {
        message('您已经是合伙人了', $this->createMobileUrl('user'), 'error');
    }
    $list = pdo_fetchall('select b.id as uid from ' . tablename('qrcode_stat'). ' a 
		left join ' . tablename('xuan_mixloan_member') . ' b on a.openid=b.openid
		where a.qrcid=:qrcid and a.type=1 group by a.openid', array(':qrcid' => $member['id']));
    $uids = array();
    foreach ($list as $row) {
        if ($row['uid']) {
            $uids[] = $row['uid'];
        }
    }
    if ($uids) {
        $uid_string = '(' . implode(',', $uids) . ')';
        $count = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_payment') . "
			where uid in {$uid_string}");
        if ($count >= $config['partner_vip_nums']) {
            $tid = "30002" . date('YmdHis', time());
            $insert['uid'] = $member['id'];
            $insert['createtime'] = time();
            $insert['uniacid'] = $_W['uniacid'];
            $insert['tid'] = $tid;
            $insert['fee'] = 0;
            pdo_insert('xuan_mixloan_partner', $insert);
            message('升级合伙人成功', $this->createMobileUrl('user'), 'sccuess');
        } else {
            message('您还没达到升级条件呢~', $this->createMobileUrl('user'), 'error');
        }
    } else {
        message('您还没有邀请小伙伴呢~', $this->createMobileUrl('user'), 'error');
    }
} else if ($operation == 'partner_center') {
    //合伙人中心
    $list = pdo_fetchall('select * from ' .tablename('xuan_mixloan_product_apply'). '
		where inviter=:inviter and type=3 order by id desc', array(':inviter'=>$member['id']));
    foreach ($list as &$row) {
        $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
        $man = pdo_fetch('select nickname,avatar from '.tablename('xuan_mixloan_member').'
			where id=:id', array(':id'=>$row['uid']));
        $row['avatar'] = $man['avatar'];
        $row['nickname'] = $man['nickname'];
        $row['phone'] = substr($row['phone'], 0, 4) . '****' . substr($row['phone'], -3, 3);
    }
    unset($row);
    include $this->template('vip/partner_center');
} else if ($operation == 'followList') {
    //下级列表
    $count = pdo_fetchcolumn("SELECT SUM(re_bonus) FROM " . tablename("xuan_mixloan_product_apply") . "
		WHERE inviter={$member['id']} AND status>0 AND type=2") ? : 0;
    $follow_count = pdo_fetchcolumn("SELECT count(DISTINCT openid) FROM " . tablename("qrcode_stat") . "
		WHERE qrcid={$member['id']} AND type=1") ? : 0;
    include $this->template('vip/followList');
} else if ($operation == 'getFollowList') {
    //获取下级列表
    $id = intval($_GPC['id']);
    $follow_list = pdo_fetchall(
        "SELECT a.id,a.createtime,a.openid,b.nickname,b.avatar,b.id as uid FROM " .tablename("qrcode_stat"). " a
		LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.openid=b.openid
		WHERE a.qrcid={$member['id']} AND a.type=1 AND a.id<{$id}
		GROUP BY a.openid
		ORDER BY a.id DESC LIMIT 5");
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
        $row['createtime1'] = date('Y-m-d', $row['createtime']);
        $row['createtime2'] = date('Y-m-d H:i:s', $row['createtime']);
        $row['agent'] = m('member')->checkAgent($row['uid']);
        $row['bonus'] = pdo_fetchcolumn("SELECT re_bonus FROM " . tablename("xuan_mixloan_product_apply") . "
			WHERE inviter={$member['id']} AND uid={$row['uid']} AND type=2") ? : '无';
    }
    unset($row);
    if (!empty($follow_list)) {
        show_json(1, ['list' => array_values($follow_list)], '获取成功');
    } else {
        show_json(-1);
    }
} else if ($operation == 'app_register') {
    //邀请注册
    $inviter = m('member')->getInviterInfo($_GPC['inviter']);
    include $this->template('vip/register');
}

