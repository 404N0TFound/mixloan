<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$agent = m('member')->checkAgent($member['id']);
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
if($operation=='buy'){
	//购买会员
	if (!$member['phone']) {
		message('请先绑定手机号', $this->createMobileUrl('index'), 'error');
	}
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
    if ($member['id'] == 7881) {
        $config['buy_vip_price'] = 0.01;
    }
    if (is_weixin()) {
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
    } else {
        if ($_GPC['way'] == 'alipay')
        {
            $tid = "10001" . date('YmdHis', time());
            $title = "购买{$config['title']}代理会员";
            $fee = $config['buy_vip_price'];
            $params = array(
                'tid' => $tid,
                'ordersn' => $tid,
                'title' => $title,
                'fee' => $fee,
                'user' => $member['id'],
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
        else if ($_GPC['way'] == 'wechat')
        {
            $notify_url = 'http://juxinwangluo.xin/addons/xuan_mixloan/lib/wechat/payResult.php';
            $record = pdo_fetch('select * from ' .tablename('xuan_mixloan_paylog'). '
		    where type=1 and is_pay=0 and uid=:uid', array(':uid'=>$member['id']));
            if (empty($record)) {
                $tid = "10001" . date('YmdHis', time());
                $trade_no = "ZML".date("YmdHis");
                $insert = array(
                    'notify_id'=>$trade_no,
                    'tid'=>$tid,
                    'createtime'=>time(),
                    'uid'=>$member['id'],
                    'uniacid'=>$_W['uniacid'],
                    'fee'=>$config['buy_vip_price'],
                    'is_pay'=>0,
                    'type'=>1
                );
                pdo_insert('xuan_mixloan_paylog', $insert);
            } else {
                if ($record['createtime']+60 < time())
                {
                    //超过1分钟重新发起订单
                    $tid = "10001" . date('YmdHis', time());
                    $trade_no = "ZML".date("YmdHis");
                    $insert = array(
                        'notify_id'=>$trade_no,
                        'tid'=>$tid,
                        'createtime'=>time(),
                        'uid'=>$member['id'],
                        'uniacid'=>$_W['uniacid'],
                        'fee'=>$config['buy_vip_price'],
                        'is_pay'=>0,
                        'type'=>1
                    );
                    pdo_insert('xuan_mixloan_paylog', $insert);
                }
                else
                {
                    $trade_no = $record['notify_id'];
                }
            }
            $result = m('pay')->H5pay($trade_no, $config['buy_vip_price'], $notify_url);
            if ($result['code'] == 1) {
                $redirect_url = urlencode($_W['siteroot'] . 'app/' .
                    $this->createMobileUrl('vip', array('op'=>'checkPay')));
                $url = "{$result['data']['url']}&redirect_url={$redirect_url}";
            }
            include $this->template('vip/openHref');
        }
    }
	exit;
} else if ($operation == 'notify_url') {
    $notify_id = $_GPC['notify_id'];
    if (empty($notify_id)) {
        message('notify_id为空', '', 'error');
    }
    $params = pdo_fetch('select * from ' .tablename('xuan_mixloan_paylog'). '
		where notify_id=:notify_id', array(':notify_id'=>$notify_id));
    if (empty($params)) {
        header("location:{$this->createMobileUrl('user')}");
        exit();
    }
    $fee = $params['fee'];
    $tid = $params['tid'];
    if ($params['is_pay'] != 1) {
        message('订单未支付', '', 'error');
    }
    $member = pdo_fetch('select * from ' .tablename('xuan_mixloan_member'). '
		where id=:id', array(':id'=>$params['uid']));
    $openid = $member['openid'];
    if (empty($member['id'])) {
        header("location:{$this->createMobileUrl('user')}");
        exit();
    }
    $type = substr($params['tid'],0,5);
    if ($type=='10001') {
        //购买会员付费
        if (empty($member['id'])) {
            header("location:{$this->createMobileUrl('user')}");
        }
        $agent = m('member')->checkAgent($member['id']);;
        if ($agent['code'] == 1) {
            message("您已经是会员，请不要重复提交", $this->createMobileUrl('user'), "error");
        }
        pdo_update("xuan_mixloan_member", array('level'=>$_SESSION['buy_level']), array('id'=>$member['id']));
        $insert = array(
            "uniacid"=>$_W["uniacid"],
            "uid"=>$member['id'],
            "createtime"=>time(),
            "tid"=>$params['tid'],
            "fee"=>$fee,
        );
        pdo_insert("xuan_mixloan_payment", $insert);
        //消息提醒
        $ext_info = array('content'=>"您好，您已成功购买会员", 'remark'=>"推广成功奖励丰富，赶快进行推广吧");
        $insert = array(
            'is_read'=>0,
            'uid'=>0,
            'createtime'=>time(),
            'uniacid'=>$_W['uniacid'],
            'to_uid'=>$member['id'],
            'ext_info'=>json_encode($ext_info),
        );
        pdo_insert('xuan_mixloan_msg', $insert);
        $salary_url = $_W['siteroot'] . 'app/' . $this->createMobileUrl('vip', array('op' => 'salary'));
        //一级
        $inviter = m('member')->getInviter($member['phone'], $member['openid']);
        if ($inviter) {
            $re_bonus = $config['inviter_fee_one'];
            if ($re_bonus) {
                $insert_i = array(
                    'uniacid' => $_W['uniacid'],
                    'uid' => $member['id'],
                    'phone' => $member['phone'],
                    'certno' => $member['certno'],
                    'realname' => $member['realname'],
                    'inviter' => $inviter,
                    'extra_bonus'=>0,
                    'done_bonus'=>0,
                    're_bonus'=>$re_bonus,
                    'status'=>2,
                    'createtime'=>time(),
                    'degree'=>1,
                    'type'=>2,
                );
                pdo_insert('xuan_mixloan_product_apply', $insert_i);
                $one_insert_id = pdo_insertid();
            }
            //消息提醒
            $ext_info = array('content' => "您好，您的徒弟{$member['nickname']}成功购买了代理会员，奖励您推广佣金" . $re_bonus . "元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", "url" => $salary_url);
            $insert = array(
                'is_read'=>0,
                'uid'=>$member['id'],
                'type'=>2,
                'createtime'=>time(),
                'uniacid'=>$_W['uniacid'],
                'to_uid'=>$inviter,
                'ext_info'=>json_encode($ext_info),
            );
            pdo_insert('xuan_mixloan_msg', $insert);
            //二级
            $man_one = m('member')->getInviterInfo($inviter);
            $inviter_two = m('member')->getInviter($man_one['phone'], $man_one['openid']);
            if ($inviter_two) {
                $partner_bonus = $config['inviter_fee_one']*0.01*$config['partner_bonus'];
                if ($partner_bonus) {
                    $partner = m('member')->checkPartner($inviter_two);
                    if ($partner['code'] == 1) {
                        $insert_i = array(
                            'uniacid' => $_W['uniacid'],
                            'uid' => $inviter,
                            'phone' => $man_one['phone'],
                            'inviter' => $inviter_two,
                            'extra_bonus'=>$partner_bonus,
                            'status'=>2,
                            'pid'=>$one_insert_id,
                            'createtime'=>time(),
                            'degree'=>1,
                            'type'=>3
                        );
                        pdo_insert('xuan_mixloan_product_apply', $insert_i);
                    }
                }
                $re_bonus = $config['inviter_fee_two'];
                if ($re_bonus) {
                    $insert_i = array(
                        'uniacid' => $_W['uniacid'],
                        'uid' => $member['id'],
                        'phone' => $member['phone'],
                        'certno' => $member['certno'],
                        'realname' => $member['realname'],
                        'inviter' => $inviter_two,
                        'extra_bonus'=>0,
                        'done_bonus'=>0,
                        're_bonus'=>$re_bonus,
                        'status'=>2,
                        'createtime'=>time(),
                        'degree'=>2,
                        'type'=>2,
                    );
                    pdo_insert('xuan_mixloan_product_apply', $insert_i);
                    $two_insert_id = pdo_insertid();
                }
                //消息提醒
                $ext_info = array('content' => "您好，您的徒弟{$man_one['nickname']}邀请了{$member['nickname']}成功购买了代理会员，奖励您推广佣金" . $re_bonus . "元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", "url" => $salary_url);
                $insert = array(
                    'is_read'=>0,
                    'uid'=>$member['id'],
                    'type'=>2,
                    'createtime'=>time(),
                    'uniacid'=>$_W['uniacid'],
                    'to_uid'=>$inviter_two,
                    'ext_info'=>json_encode($ext_info),
                );
                pdo_insert('xuan_mixloan_msg', $insert);
                //三级
                $man_two = m('member')->getInviterInfo($inviter_two);
                $inviter_thr = m('member')->getInviter($man_two['phone'], $man_two['openid']);
                if ($inviter_thr) {
                    $partner_bonus = $config['inviter_fee_two']*0.01*$config['partner_bonus'];
                    if ($partner_bonus) {
                        $partner = m('member')->checkPartner($inviter_thr);
                        if ($partner['code'] == 1) {
                            $insert_i = array(
                                'uniacid' => $_W['uniacid'],
                                'uid' => $inviter_two,
                                'phone' => $man_two['phone'],
                                'inviter' => $inviter_thr,
                                'extra_bonus'=>$partner_bonus,
                                'status'=>2,
                                'pid'=>$two_insert_id,
                                'createtime'=>time(),
                                'degree'=>1,
                                'type'=>3
                            );
                            pdo_insert('xuan_mixloan_product_apply', $insert_i);
                        }
                    }
                    $re_bonus = $config['inviter_fee_two'];
                }
            }
        }
        message("支付成功", $this->createMobileUrl('user'), "success");
    }  else if ($type == '10002') {
        //合伙人购买
        $partner = m('member')->checkPartner($member['id']);;
        if ($partner['code'] == 1) {
            message("您已经是合伙人，请不要重复提交", $this->createMobileUrl('user'), "error");
        }
        $insert = array(
            "uniacid"=>$_W["uniacid"],
            "uid"=>$member['id'],
            "createtime"=>time(),
            "tid"=>$params['tid'],
            "fee"=>$fee,
        );
        pdo_insert("xuan_mixloan_partner", $insert);
        message("支付成功", $this->createMobileUrl('user'), "success");
    }
} else if ($operation == 'createPost') {
    $type = intval($_GPC['type']);//1是关联产品,2是直接全部代理
    if ($type == 1) {
        $id = intval($_GPC['id']);
        $product = m('product')->getList(['id','ext_info','type','relate_id'], ['id'=>$id])[$id];
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
    $poster = pdo_fetch('select poster from ' . tablename('xuan_mixloan_poster') . ' 
        where pid=:pid and uid=:uid', array(':pid' => $id, ':uid' => $member['id']));
    if (!$poster) {
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
            show_json(1, ['post_url'=>$res, 'agent_url'=>$url]);
        } else {
            show_json(-1, [], '生成海报失败，请检查海报背景图上传是否正确');
        }
    } else {
        show_json(2, ['post_url'=>$poster['poster'], 'agent_url'=>$url]);
    }
	
} else if ($operation == 'createPostAllProduct') {
	//我的代理店
	// if ($agent['code']==1) {
	// 	$verify = 1;
	// } else {
	// 	$verify = 0;
	// }
    $verify = 1;
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
	$percent_list = m('product')->getApplyList([], ['inviter'=>$member['id'], 'la_status'=>0], ' id DESC');
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
		} else if ($row['type'] == 3){
            $row['name'] = '合伙人分佣';
            $row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
        } else if ($row['type'] == 4){
            $row['name'] = '信用查询分佣';
            $row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
        } else if ($row['type'] == 5){
            $row['name'] = '每日佣金奖励';
            $row['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
        }
        if($row['type'] == 1) {
            if ($pros[$row['pid']]['count_time'] == 1) {
                $row['type'] = '日结';
            } else if ($pros[$row['pid']]['count_time'] == 7) {
                $row['type'] = '周结';
            } else if ($pros[$row['pid']]['count_time'] == 30) {
                $row['type'] = '月结';
            }
        } else {
            $row['type'] = '现结';
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
    $record = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_verify_data') . '
        where uid=:uid', array(':uid' => $member['id']));
    if (!$record) {
        header("location:{$this->createMobileUrl('user', array('op' => 'verify'))}");
    }
	$banks = pdo_fetchall("SELECT id,bankname,banknum FROM ".tablename("xuan_mixloan_creditCard")." WHERE uid=:uid and status=1", array(':uid'=>$member['id']));
	foreach ($banks as &$row) {
		if (count($row['banknum']) == 16) {
			$row['numbers_type'] = 1;
			$row['numbers'][0] = substr($row['banknum'], 0, 4);
			$row['numbers'][1] = substr($row['banknum'], 4, 4);
			$row['numbers'][2] = substr($row['banknum'], 8, 4);
			$row['numbers'][3] = substr($row['banknum'], 12, 4);
		} else {
			$row['numbers_type'] = 2;
			$row['numbers'][0] = substr($row['banknum'], 0, 6);
			$row['numbers'][1] = substr($row['banknum'], 6);
		}
	}
	unset($row);
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
	if ($bonus < 30) {
		show_json(-1, null, "提现最低限额为30元哦");
	}
	$bank = pdo_fetch('SELECT * FROM '.tablename('xuan_mixloan_creditCard').' WHERE id=:id', array(':id'=>$bank_id));
	$bank_code = m('pay')->getBankCode($bank['bankname']);
	if (!$bank_code) {
		show_json(-1, null, "该银行卡暂时不支持快速提现，请换一张试试");
	}
    // $pay = m('pay')->pay($bank['banknum'], $bank['realname'], $bank_code, $bonus, '代理申请结算工资');
  //   if ($pay['code']>1) {
  //   	show_json(-1, null, $pay['msg']);
  //   } else {
		// $insert = array(
		// 	'uniacid'=>$_W['uniacid'],
		// 	'uid'=>$member['id'],
		// 	'bank_id'=>$bank_id,
		// 	'bonus'=>$bonus,
		// 	'createtime'=>time(),
		// 	'status'=>1
		// );
		// $insert['ext_info']['bank_code'] = $bank_code;
		// $insert['ext_info']['reason'] = '代理申请结算工资';
  //       $insert['ext_info']['partner_trade_no'] = $pay['data']['partner_trade_no'];
  //       $insert['ext_info']['payment_no'] = $pay['data']['payment_no'];
  //       $insert['ext_info'] = json_encode($insert['ext_info']);
		// pdo_insert('xuan_mixloan_withdraw', $insert);
		// show_json(1, null, "提现成功");
  //   }
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
    // if ($agent['code'] != 1) {
    //     message('您不是会员', '', 'error');
    // }
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
} else if ($operation == 'followList') {
    //关注列表
    $follow_list = pdo_fetchall("SELECT a.createtime,a.openid,b.nickname FROM ".tablename("qrcode_stat")." a
        LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.openid=b.openid
        WHERE a.qrcid={$member['id']} AND a.type=1
        GROUP BY a.openid
        ORDER BY a.id DESC");
    foreach ($follow_list as &$row)
    {
        if (empty($row['nickname']))
        {
            $row['nickname'] = pdo_fetchcolumn('select nickname from ' . tablename('mc_mapping_fans') . '
                where openid=:openid', array(':openid' => $row['openid'])) ? : '未更新';
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
    include $this->template('vip/followList');
} else if ($operation == 'extendList') {
    //推广成功
    $extend_list = pdo_fetchall("SELECT a.uid,a.createtime,a.degree,a.re_bonus,b.nickname FROM ".tablename("xuan_mixloan_product_apply")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.uid=b.id WHERE a.inviter={$member['id']} AND a.status>0 AND pid=0 ORDER BY a.id DESC");
    foreach ($extend_list as &$row) {
    	if ($row['degree'] == 1) {
    		$row['degree'] = '团队';
    	} else if ($row['degree'] == 2) {
    		$row['degree'] = '连队';
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
    $follow_count = pdo_fetchcolumn("SELECT count(1) FROM ".tablename("qrcode_stat")." a LEFT JOIN ".tablename("mc_mapping_fans"). " b ON a.openid=b.openid WHERE a.qrcid={$member['id']} AND a.type=1 ORDER BY id DESC") ? : 0;
    $buy_count = count($extend_list) ? : 0;
    include $this->template('vip/extendList');
} else if ($operation == 'degreeDetail') {
    //对应等级
    $uid = intval($_GPC['uid']);
    $list = pdo_fetchall("SELECT a.degree,b.nickname,b.avatar FROM ".tablename("xuan_mixloan_product_apply")." a LEFT JOIN ".tablename("xuan_mixloan_member"). " b ON a.inviter=b.id WHERE a.uid={$uid} AND a.pid=0 ORDER BY a.degree ASC");
    $brother = pdo_fetch("SELECT nickname,avatar FROM ".tablename("xuan_mixloan_member")." WHERE id={$uid}");
    $wheres = '';
    $status = $_GPC['status'];
    if ($status == '0' || $status == '-1' || $status == '2') {
        $wheres .= 'and status='.$status;
    }
    $apply = pdo_fetchall('select realname,pid,status from ' . tablename('xuan_mixloan_product_apply') . "
    	where inviter=:inviter and degree=1 {$wheres} and type=1
    	order by id desc limit 30", array(':inviter' => $uid));
    foreach ($apply as &$row) {
        $row['pro_name'] = pdo_fetchcolumn('select name from ' . tablename('xuan_mixloan_product') . '
    		where id=:id', array(':id' => $row['pid']));
    }
    unset($row);
    include $this->template('vip/degreeDetail');
} else if ($operation == 'app_register') {
    //邀请注册
    $advs_list = pdo_fetchall('select nickname from ' . tablename('xuan_mixloan_member') . '
        order by rand()
        limit 10');
    foreach ($advs_list as &$row) {
        $row['pro_name'] = pdo_fetchcolumn('select name from ' . tablename('xuan_mixloan_product') . '
            where is_show=1
            order by rand()');
        $row['name'] = func_substr_replace($row['nickname']);
        $row['pro_name'] = func_substr_replace($row['pro_name']);
        $row['money'] = rand(100000, 9999999) / 100;
    }
    unset($row);
    $inviter = m('member')->getInviterInfo($_GPC['inviter']);
    include $this->template('vip/register');
} else if ($operation == 'createPoster') {
    //生成邀请二维码
    $uid = intval($_GPC['uid']) ? : $member['id'];
    $type = intval($_GPC['type']) ? : 1;
    $pid = intval($_GPC['pid']) ? : 0;
    $member = m('member')->getInfo($uid);
    $posterArr = pdo_fetchall('SELECT poster,prefix_text FROM '.tablename('xuan_mixloan_poster').' WHERE uid=:uid AND type=:type AND pid=:pid', array(':uid'=>$member['id'], ':type'=>$type, ':pid'=>$pid));
    $created = true;
    if ($type == 3) {
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'app_register', 'inviter'=>$member['id']));
        $share_url = shortUrl( $url );
        if (!$posterArr) {
            if (empty($config['inviter_poster'])) {
                message("请检查海报是否上传", "", "error");
            }
            foreach ($config['inviter_poster'] as $row) {
                $out = XUAN_MIXLOAN_PATH."data/poster/invite_{$member['id']}_{$row}.png";
                $poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/invite_{$member['id']}_{$row}.png";
                $params = array(
                    "poster_id" => $row,
                    "url" => $url,
                    "member" => $member,
                    "type" => 3,
                    "pid" => 0,
                    "out" => $out,
                    "poster_path" => $poster_path,
                );
                $invite_res = m('poster')->createNewPoster($params);
                if (!$invite_res) {
                    message('生成海报失败，请检查海报背景图上传是否正确', '', 'error');
                } else {
                    $posterArr[] = $invite_res;
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
                    "url" => $url,
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
                    $posterArr[] = $invite_res;
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
                    "url" => $url,
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
                    $posterArr[] = $invite_res;
                }
            }
        }
    }
    foreach ($posterArr as &$row) {
        if (!$row['prefix_text']) {
            $row['prefix_text'] = '';
        }
        $row['long_text'] = $row['prefix_text'].$url;
        $row['prefix_text'] = $row['prefix_text'].$share_url;
    }
    unset($row);
    $ret = array('tips'=>$tips, 'posterArr'=>$posterArr, 'created'=>$created);
    message($ret, '', 'success');
} else if ($operation == 'register') {
    //邀请注册
    $inviter = m('member')->getInviterInfo($_GPC['inviter']);
    include $this->template('vip/register');
} else if ($operation == 'checkPay') {
    //检测有没有付款成功
    include $this->template('vip/checkPay');
} else if ($operation == 'choose_pay_type') {
    //选择付款方式
    include $this->template('vip/choose_pay_type');
} else if ($operation == 'openHref') {
    //打开链接
    include $this->template('vip/openNew');
}  else if ($operation == 'partner_join_type') {
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
    $partner = m('member')->checkPartner($member['id']);
	if ($partner['code'] == 1) {
		header("location:{$this->createMobileUrl('vip', array('op'=>'partner_center'))}");
	}
	if (!$member['phone']) {
		message('请先绑定手机号', $this->createMobileUrl('index'), 'error');
	}
	if ($member['id'] == 18) {
		$config['buy_partner_price'] = 0.01;
	}
	if (is_weixin()) {
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
	} else {
		$notify_url = 'http://juxinwangluo.xin/addons/xuan_mixloan/lib/wechat/payResult.php';
		$record = pdo_fetch('select * from ' .tablename('xuan_mixloan_paylog'). '
			where type=2 and is_pay=0 and uid=:uid', array(':uid'=>$member['id']));
		if (empty($record)) {
			$tid = "10002" . date('YmdHis', time());
		    $trade_no = "ZML".date("YmdHis");
			$insert = array(
				'notify_id'=>$trade_no,
				'tid'=>$tid,
				'createtime'=>time(),
				'uid'=>$member['id'],
				'uniacid'=>$_W['uniacid'],
				'fee'=>$config['buy_partner_price'],
				'is_pay'=>0,
				'type'=>2
			);
			pdo_insert('xuan_mixloan_paylog', $insert);
		} else {
			if ($record['createtime'] + 5 < time()) {
				$tid = "10002" . date('YmdHis', time());
			    $trade_no = "ZML".date("YmdHis");
				$insert = array(
					'notify_id'=>$trade_no,
					'tid'=>$tid,
					'createtime'=>time(),
					'uid'=>$member['id'],
					'uniacid'=>$_W['uniacid'],
					'fee'=>$config['buy_partner_price'],
					'is_pay'=>0,
					'type'=>2
				);
				pdo_insert('xuan_mixloan_paylog', $insert);
			} else {
		    	$trade_no = $record['notify_id'];
			}
		}
		$result = m('pay')->H5pay($trade_no, $config['buy_partner_price'], $notify_url);
		if ($result['code'] == 1) {
			$redirect_url = urlencode($_W['siteroot'] . 'app/' .
				$this->createMobileUrl('vip', array('op'=>'checkPay', 'notify_id'=>$trade_no)));
			$url = "{$result['data']['url']}&redirect_url={$redirect_url}";
		}
		include $this->template('vip/openHref');
	}
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
            where uid in {$uid_string}") ? : 0;
    } else {
        $count = 0;
    }
    if ($_GPC['post']) {
        if ($count >= $config['partner_vip_nums']) {
            $tid = "30002" . date('YmdHis', time());
            $insert['uid'] = $member['id'];
            $insert['createtime'] = time();
            $insert['uniacid'] = $_W['uniacid'];
            $insert['tid'] = $tid;
            $insert['fee'] = 0;
            pdo_insert('xuan_mixloan_partner', $insert);
            show_json(1, ['url' => $this->createMobileUrl('user')], 'success');
        } else {
            show_json(-1, [], '您还没达到升级条件呢');
        }
    }
    include $this->template('vip/partner_upgrade');
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
} else if ($operation == 'set') {
    // 设置
    $is_close = pdo_fetchcolumn('select is_close from ' . tablename('xuan_mixloan_agent_close') . '
		where uid=:uid', array(':uid' => $member['id']));
    include $this->template('vip/set');
} else if ($operation == 'set_submit') {
    // 设置提交
    $active = $_GPC['active'];
    $id = pdo_fetchcolumn('select id from ' . tablename('xuan_mixloan_agent_close') . '
		where uid=:uid', array(':uid' => $member['id']));
    if ($active == 'false') {
        if ($id) {
            pdo_update('xuan_mixloan_agent_close', array('is_close' => 1), array('id' => $id));
        } else {
            $insert = array();
            $insert['uid'] = $member['id'];
            $insert['is_close'] = 1;
            pdo_insert('xuan_mixloan_agent_close', $insert);
        }
    } else {
        if ($id) {
            pdo_update('xuan_mixloan_agent_close', array('is_close' => 0), array('id' => $id));
        }
    }
} else if ($operation == 'rank_list') {
    //排行榜
    
    $temp_time = date('Y-m-d H:i:s');
    $end_time = strtotime($temp_time);
    $start_time = strtotime("-3 month {$temp_time}");
    // $list = pdo_fetchall("SELECT inviter,SUM(re_bonus+done_bonus+extra_bonus) AS bonus FROM ".tablename('xuan_mixloan_bonus')." WHERE relate_id=0 AND createtime>{$start_time} AND createtime<{$end_time} GROUP BY inviter HAVING bonus<>0 ORDER BY bonus DESC LIMIT 15");
    $list = pdo_fetchall("SELECT uid,SUM(bonus) as count_bonus FROM " .tablename('xuan_mixloan_withdraw'). "
        WHERE createtime>{$start_time} AND createtime<{$end_time}
        GROUP BY uid HAVING count_bonus<>0
        ORDER BY count_bonus DESC LIMIT 20");
    if (!empty($list)) {
        foreach ($list as &$row) {
            $temp_member = pdo_fetch("SELECT nickname,avatar,phone FROM ".tablename('xuan_mixloan_member').' WHERE id=:id', array(':id'=>$row['uid']));
            $row['nickname'] = $temp_member['nickname'];
            $row['avatar'] = $temp_member['avatar'];
            $row['phone'] = substr($temp_member['phone'], 0, 4) . '****' . substr($temp_member['phone'], -3, 3);
        }
        unset($row);
    }
    include $this->template('vip/rank_list');
} else if ($operation == 'upgrade_free') {
    if ($agent['code'] == 1) {
        header("location:{$this->createMobileUrl('user')}");
        exit();
    }
    $upgrade_free_a = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_product_apply') . '
                    where inviter=:inviter and degree=1 and type=1 and status>0', array(':inviter' => $member['id'])) ? : 0;
    $list = pdo_fetchall('select openid from ' . tablename('qrcode_stat') . ' 
                    where qrcid=:qrcid 
                    group by openid', array(':qrcid' => $member['id']));
    if ($list) {
        foreach ($list as $row) {
            $openid_arr[] = $row['openid'];
        }
        $openid_string = "'" . implode("','", $openid_arr) . "'";
        $upgrade_free_b = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_payment') . ' a 
                                    left join ' . tablename('xuan_mixloan_member') . " b on a.uid=b.id
                                    where b.openid in ({$openid_string})") ? : 0;
    } else {
        $upgrade_free_b = 0;
    }
    if ($_GPC['submit'] == 1) {
        if ($_GPC['type'] == 1) {
            if ($upgrade_free_a >= $config['upgrade_free_a']) {
                $tid = "20001" . date('YmdHis', time());
                $insert = array(
                        "uniacid"=>$_W["uniacid"],
                        "uid"=>$member['id'],
                        "createtime"=>time(),
                        "tid"=>$tid,
                        "fee"=>0,
                );
                pdo_insert("xuan_mixloan_payment",$insert);
                message('升级完毕', $this->createMobileUrl('user'), 'success');
            } else {
                message('您还没达到升级条件呢', '', 'error');
            }
        } else {
            if ($upgrade_free_b >= $config['upgrade_free_b']) {
                $tid = "20001" . date('YmdHis', time());
                $insert = array(
                        "uniacid"=>$_W["uniacid"],
                        "uid"=>$member['id'],
                        "createtime"=>time(),
                        "tid"=>$tid,
                        "fee"=>0,
                );
                pdo_insert("xuan_mixloan_payment",$insert);
                message('升级完毕', $this->createMobileUrl('user'), 'success');
            } else {
                message('您还没达到升级条件呢', '', 'error');
            }
        }
    }

    include $this->template('vip/upgrade_free');
}
