<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='index';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
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
	//首页
    $new_list = m('product')->getList([], ['is_show'=>1, 'is_new'=>1], ' id desc', 9);
    $new_list = m('product')->packupItems($new_list);
    $hot_list = m('product')->getList([], ['day_hot'=>1, 'is_show'=>1], FALSE);
    $hot_list = m('product')->packupItems($hot_list);
    $credit_list = m('product')->getList([], ['type'=>1, 'is_show'=>1], FALSE);
    $credit_list = m('product')->packupItems($credit_list);
    $loan_day_list = m('product')->getList([], ['type'=>2, 'is_show'=>1, 'count_time'=>1], ' sort desc');
    $loan_day_list = m('product')->packupItems($loan_day_list);
    $loan_week_list = m('product')->getList([], ['type'=>2, 'is_show'=>1, 'count_time'=>7], ' sort desc');
    $loan_week_list = m('product')->packupItems($loan_week_list);
    $loan_month_list = m('product')->getList([], ['type'=>2, 'is_show'=>1, 'count_time'=>30], ' sort desc');
    $loan_month_list = m('product')->packupItems($loan_month_list);
    $loan_ready_list = m('product')->getList([], ['type'=>2, 'is_show'=>1, 'ready'=>1], ' sort desc');
    $loan_ready_list = m('product')->packupItems($loan_ready_list);
	include $this->template('product/index');
}  else if ($operation == 'getProduct') {
	//得到产品
    $banner = m('product')->getAdvs();
    $new = m('product')->getRecommends();
    $new = m('product')->packupItems($new);
    // $card = m('product')->getList([], ['type'=>1, 'is_show'=>1], FALSE);
    // $loan = m('product')->getList([], ['type'=>2, 'is_show'=>1], FALSE);
    // $card = m('product')->packupItems($card);
    // $loan = m('product')->packupItems($loan);
    $card = $loan = array();
    $arr = array(
        'banner'=>$banner,
        'new'=>$new,
        'card'=>$card,
        'loan'=>$loan
    );
	show_json(1, $arr);
} else if ($operation == 'info') {
	//产品详情
	// $agent = m('member')->checkAgent($member['id']);
	// if ($agent['code']==1) {
	// 	$verify = 1;
	// } else {
	// 	$verify = 0;
	// }
    $verify = 1;
	$id = intval($_GPC['id']);
	$info = m('product')->getList([],['id'=>$id])[$id];
    if ( empty($info['is_show']) ) {
        message('该代理产品已被下架', $this->createMobileUrl('user'), 'info');
    }
    if ($info['day_hot']) {
        $agent = m('member')->checkAgent($member['id']);
        if ($agent['code'] != 1) {
            $verify = 0;
        }
    }
    $record = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_verify_data') . '
        where uid=:uid', array(':uid' => $member['id']));
    if (!$record) {
        header("location:{$this->createMobileUrl('user', array('op' => 'verify'))}");
    }
    if ($info['type'] == 1) {
        $poster_short_url = shortUrl($_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'apply', 'id'=>$id, 'inviter'=>$member['id'])));
        $poster_long_url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'apply', 'id'=>$id, 'inviter'=>$member['id']));
    } else {
        $poster_short_url = shortUrl($_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$info['relate_id'], 'inviter'=>$member['id'], 'pid'=>$info['id'])));
        $poster_long_url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$info['relate_id'], 'inviter'=>$member['id'], 'pid'=>$info['id']));
    }
	$poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/{$id}_{$member['id']}.png?v=" . time();
	$top_list = m('product')->getTopBonus($id);
	include $this->template('product/info');
} else if ($operation == 'allProduct') {
	//全部产品
	$inviter = intval($_GPC['inviter']);
    $credit_list = m('product')->getList([], ['type'=>1, 'is_show'=>1], FALSE);
    $credit_list = m('product')->packupItems($credit_list);
    $loan_small_list = m('product')->getList([], ['type'=>2, 'is_show'=>1, 'count_time'=>1], ' sort desc');
    $loan_small_list = m('product')->packupItems($loan_small_list);
    $loan_large_list = m('product')->getList([], ['type'=>2, 'is_show'=>1, 'count_time'=>30], ' sort desc');
    $loan_large_list = m('product')->packupItems($loan_large_list);
    foreach ($credit_list as &$row) {
        $info = m('bank')->getCard(['id', 'ext_info'], ['id' => $row['relate_id']])[$row['relate_id']];
        $row['tag'] = $info['ext_info']['v_name'];
    }
    unset($row);
    foreach ($loan_small_list as &$row) {
        $info = m('loan')->getList(['id', 'money_high'], ['id' => $row['relate_id']])[$row['relate_id']];
        $row['tag'] = '最高额度' . $info['money_high'];
    }
    unset($row);
    foreach ($loan_large_list as &$row) {
        $info = m('loan')->getList(['id', 'money_high'], ['id' => $row['relate_id']])[$row['relate_id']];
        $row['tag'] = '最高额度' . $info['money_high'];
    }
    unset($row);
	include $this->template('product/allProduct');
} else if ($operation == 'apply') {
	//申请产品
	$id = intval($_GPC['id']);
	$inviter = intval($_GPC['inviter']);
    $info = m('product')->getList(['id', 'ext_info', 'is_show', 'relate_id'],['id'=>$id])[$id];
    if ( empty($info['is_show']) ) {
        header("location:{$this->createMobileUrl('product', array('op' => 'allProduct', 'inviter' => $inviter))}");
        exit();
    }
    $is_close = pdo_fetchcolumn('select is_close from ' . tablename('xuan_mixloan_agent_close') . '
        where uid=:uid', array(':uid' => $inviter));
    $item = m('bank')->getCard(['id', 'name'], ['id' => $info['relate_id']])[$info['relate_id']];
	include $this->template('product/apply');
} else if ($operation == 'apply_submit') {
	//申请产品
	$id = intval($_GPC['id']);
	$inviter_uid = m('member')->getInviter(trim($_GPC['phone']), $member['openid']);
	$inviter = $inviter_uid ? : intval($_GPC['inviter']);
    if (sha1(md5(strtolower($_GPC['cache']))) != $_COOKIE['authcode']) {
        show_json(-1, [], "图形验证码不正确");
    }
	if ($id <= 0) {
		show_json(-1, [], "id为空");
	}
	if(!trim($_GPC['name']) || !trim($_GPC['phone']) || !trim($_GPC['idcard'])) {
		show_json(-1, [], '资料不能为空');
	}
	$record = m('product')->getApplyList(['id'], ['pid'=>$id, 'phone'=>$_GPC['phone']]);
    $info = m('product')->getList(['id', 'name', 'type', 'relate_id', 'is_show'],['id'=>$id])[$id];
    if ( empty($info['is_show']) ) {
        show_json(-1, [], '该代理产品已被下架');
    }
	if ($info['type'] == 1) {
		$pro = m('bank')->getCard(['id', 'ext_info'], ['id'=>$info['relate_id']])[$info['relate_id']];
	} else {
		$pro = m('loan')->getList(['id', 'ext_info'], ['id'=>$info['relate_id']])[$info['relate_id']];
	}
    $record = pdo_fetchcolumn('select id from ' . tablename('xuan_mixloan_product_apply') . '
             where phone=:phone and pid=:pid and degree=1', array(':phone' => trim($_GPC['phone']), ':pid' => $id));
    if ($record) {
        // $location = $_W['siteroot'] . 'app/' . $this->createMobileUrl('loan', array('op' => 'middleware', 'id' => $record));
        $location = $pro['ext_info']['url'];
        show_json(1, $location);
    }
	if ($inviter) {
		$inviter_one = pdo_fetch("SELECT openid,nickname FROM ".tablename("xuan_mixloan_member") . " WHERE id=:id", array(':id'=>$inviter));
		$datam = array(
            "first" => array(
                "value" => "尊敬的用户您好，有一个用户通过您的邀请申请了{$info['name']}，请及时跟进。",
                "color" => "#173177"
            ) ,
            "keyword1" => array(
                'value' => trim($_GPC['name']),
                "color" => "#4a5077"
            ) ,
            "keyword2" => array(
                'value' => date('Y-m-d H:i:s', time()),
                "color" => "#4a5077"
            ) ,
            "remark" => array(
                "value" => '点击查看详情',
                "color" => "#4a5077"
            ) ,
        );
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
        $account = WeAccount::create($_W['acid']);
        $account->sendTplNotice($inviter_one['openid'], $config['tpl_notice1'], $datam, $url);
        $ext_info = array('content' => "尊敬的用户您好，" . $_GPC['name'] . "通过您的邀请申请了" . $info['name'] . "，请及时跟进。", 'remark' => "点击查看详情", 'url' => $url);
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
		if (!$inviter_uid) {
            $check = m('member')->checkIfRelation($inviter, $member['id']);
            if ($check == false) {
                $insert_i = array(
                    'uniacid' => $_W['uniacid'],
                    'uid' => $inviter,
                    'phone' => trim($_GPC['phone']),
                    'createtime' => time()
                );
                pdo_insert('xuan_mixloan_inviter', $insert_i);
            }
		}
		$status = 0;
	} else {
		$status = -2;
	}
	$insert = array(
		'uniacid' => $_W['uniacid'],
		'uid' => $member['id'],
		'phone' => trim($_GPC['phone']),
		'certno' => trim($_GPC['idcard']),
		'realname' => trim($_GPC['name']),
		'pid' => $id,
		'inviter' => $inviter,
		're_bonus'=>0,
		'done_bonus'=>0,
		'extra_bonus'=>0,
		'status'=>$status,
		'createtime'=>time(),
        'ip'=>getServerIp(),
        'device_type'=>getDeviceType(),
	);
    $insert['browser_type'] = is_weixin() ? 1 : 2;
    $agent = m('member')->checkAgent($inviter);
    if ($agent['code'] != 1) {
        $insert['agent'] = 0;
    }
	pdo_insert('xuan_mixloan_product_apply', $insert);
    $insert_id = pdo_insertid();
	//二级
	$inviter_info = m('member')->getInviterInfo($inviter);
    $second_inviter = m('member')->getInviter($inviter_info['phone'], $inviter_info['openid']);
    if ($second_inviter) {
        $insert['inviter'] = $second_inviter;
        $insert['degree'] = 2;
        pdo_insert('xuan_mixloan_product_apply', $insert);
        $inviter_two = pdo_fetch("SELECT openid,nickname FROM ".tablename("xuan_mixloan_member") . " WHERE id=:id", array(':id'=>$second_inviter));
		$datam = array(
            "first" => array(
                "value" => "尊敬的用户您好，有一个用户通过您下级{$inviter_one['nickname']}的邀请申请了{$info['name']}，请及时跟进。",
                "color" => "#173177"
            ) ,
            "keyword1" => array(
                'value' => trim($_GPC['name']),
                "color" => "#4a5077"
            ) ,
            "keyword2" => array(
                'value' => date('Y-m-d H:i:s', time()),
                "color" => "#4a5077"
            ) ,
            "remark" => array(
                "value" => '点击查看详情',
                "color" => "#4a5077"
            ) ,
        );
        $account->sendTplNotice($inviter_two['openid'], $config['tpl_notice1'], $datam, $url);
        $ext_info = array('content' => "尊敬的用户您好，" . $_GPC['name'] . "通过您下级 " . $inviter_info['nickname'] . " 的邀请申请了" . $info['name'] . "，请及时跟进。", 'remark' => "点击查看详情", 'url' => $url);
        $insert = array(
            'is_read'=>0,
            'uid'=>$member['id'],
            'type'=>2,
            'createtime'=>time(),
            'uniacid'=>$_W['uniacid'],
            'to_uid'=>$second_inviter,
            'ext_info'=>json_encode($ext_info),
        );
        pdo_insert('xuan_mixloan_msg', $insert);
    }
    // $location = $_W['siteroot'] . 'app/' . $this->createMobileUrl('loan', array('op' => 'middleware', 'id' => $insert_id));
    $location = $pro['ext_info']['url'];
    show_json(1, $location);
} else if ($operation == 'customer') {
	//客户列表
	include $this->template('product/customer');
} else if ($operation == 'get_list') {
    // 获取产品
    $list = m('product')->getList(['id', 'name', 'type'], ['is_show' => 1]);
    show_json(1, ['list' => array_values($list)], '获取成功');
} else if ($operation == 'get_customer') {
    // 获取客户
    $ids       = trim($_GPC['ids']);
    $status    = intval($_GPC['status']);
    $degree    = intval($_GPC['degree']);
    $page      = intval($_GPC['page']) ? : 1;
    $pageSize  = intval($_GPC['pageSize']) ? : 10;
    if (empty($member['id'])) {
        show_json(-1, [], '还没登陆呢');
    }
    $wheres    = '';
    if (!empty($ids)) {
        $ids = rtrim($ids, ',');
        $wheres .= " and pid in ({$ids})";
    }
    if ($status == 1) {
        $wheres .= " and status >= 1";
    } else {
        $wheres .= " and status = {$status}";
    }
    $wheres .= " and degree = {$degree}";
    $sql = 'select * from ' . tablename('xuan_mixloan_product_apply') . " 
                    where inviter={$member['id']} and type=1 " . $wheres . ' ORDER BY id DESC';
    $sql.= " limit " . ($page - 1) * $pageSize . ',' . $pageSize;
    $list = pdo_fetchall($sql);
    foreach ($list as &$row)
    {
        $product = pdo_fetch('select name,ext_info from ' . tablename('xuan_mixloan_product') . '
                        where id=:id', array(':id' => $row['pid']));
        $product['ext_info'] = json_decode($product['ext_info'], 1);
        $row['pro_logo'] = tomedia($product['ext_info']['logo']);
        $row['pro_name'] = $product['name'];
        $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
    }
    unset($row);
    $total = pdo_fetchcolumn( 'select count(*) from ' . tablename('xuan_mixloan_product_apply') . " 
                            where inviter={$member['id']} and type=1 " . $wheres . ' ORDER BY id DESC');
    $totalPage = ceil($total / $pageSize);
    show_json(1, ['list' => $list, 'totalPage' => $totalPage], '获取成功');
} else if ($operation == 'customer_list') {
	//客户列表接口
    $month = (int)$_GPC['month'];
    $year = (int)$_GPC['year'];
    $params['begin'] = "{$year}-{$month}-01";
    $params['inviter'] = $member['id'];
    $condition_days = ['count_time'=>1, 'is_show'=>1];
    $condition_weeks = ['count_time'=>7, 'is_show'=>1];
    $condition_months = ['count_time'=>30, 'is_show'=>1];
    $days_list = m('product')->getList(['id', 'name', 'type'], $condition_days);
    $weeks_list = m('product')->getList(['id', 'name', 'type'], $condition_weeks);
    $months_list = m('product')->getList(['id', 'name', 'type'], $condition_months);
    $days_ids = m('product')->getIds($days_list);
    $weeks_ids = m('product')->getIds($weeks_list);
    $months_ids = m('product')->getIds($months_list);
    $applys = m('product')->getApplys($params);
    $days_count_list = m('product')->getNums($days_ids, $params, 1);
    $weeks_count_list = m('product')->getNums($weeks_ids, $params, 1);
    $months_count_list = m('product')->getNums($months_ids, $params, 1);
    $days_succ_list = m('product')->getNums($days_ids, $params, 2);
    $weeks_succ_list = m('product')->getNums($weeks_ids, $params, 2);
    $months_succ_list = m('product')->getNums($months_ids, $params, 2);
    $days_bonus_list = m('product')->getNums($days_ids, $params, 3);
    $weeks_bonus_list = m('product')->getNums($weeks_ids, $params, 3);
    $months_bonus_list = m('product')->getNums($months_ids, $params, 3);
    foreach ($days_list as &$row) {
        $row['count_num'] = $days_count_list[$row['id']]['count'] ? : 0;
        if ($row['type'] == 1) {
            $row['succ'] = $days_succ_list[$row['id']]['count'] ? $days_succ_list[$row['id']]['count'].'位' : '0'.'位';
        } else {
            $row['succ'] = $days_succ_list[$row['id']]['relate_money'] ? $days_succ_list[$row['id']]['relate_money'].'元' : '0'.'元';
        }
        $row['count_bonus'] = $days_bonus_list[$row['id']]['bonus'] ? : 0;
    }
    unset($row);
    foreach ($weeks_list as &$row) {
        $row['count_num'] = $weeks_count_list[$row['id']]['count'] ? : 0;
        if ($row['type'] == 1) {
            $row['succ'] = $weeks_succ_list[$row['id']]['count'] ? $weeks_succ_list[$row['id']]['count'].'位' : '0'.'位';
        } else {
            $row['succ'] = $weeks_succ_list[$row['id']]['relate_money'] ? $weeks_succ_list[$row['id']]['relate_money'].'元' : '0'.'元';
        }
        $row['count_bonus'] = $weeks_bonus_list[$row['id']]['bonus'] ? : 0;
    }
    unset($row);
    foreach ($months_list as &$row) {
        $row['count_num'] = $months_count_list[$row['id']]['count'] ? : 0;
        if ($row['type'] == 1) {
            $row['succ'] = $months_succ_list[$row['id']]['count'] ? $months_succ_list[$row['id']]['count'].'位' : '0'.'位';
        } else {
            $row['succ'] = $months_succ_list[$row['id']]['relate_money'] ? $months_succ_list[$row['id']]['relate_money'].'元' : '0'.'元';
        }
        $row['count_bonus'] = $months_bonus_list[$row['id']]['bonus'] ? : 0;
    }
    unset($row);
    $arr = ['days_list'=>array_values($days_list), 'months_list'=>array_values($months_list), 'weeks_list'=>array_values($weeks_list), 'applys'=>$applys];
    show_json(1, $arr);
} else if ($operation == 'customer_detail') {
    //详情
    $pid = intval($_GPC['pid']);
    $inviter = intval($_GPC['inviter']);
    $degree = intval($_GPC['degree']) ? : 1;
    $type = $_GPC['type'] ? : 1;
    if (empty($pid) || empty($inviter)) {
        message('查询出错', '', 'error');
    }
    $arr = array(':pid'=>$pid, ':inviter'=>$inviter);
    if ($type == 1) {
        $condition = ' WHERE inviter=:inviter AND pid=:pid';
    } else if ($type == 2) {
        $condition = ' WHERE inviter=:inviter AND pid=:pid AND status>0';
    } else if ($type == 3) {
        $condition = ' WHERE inviter=:inviter AND pid=:pid AND status=-1';
    } else if ($type == 4) {
        $condition = ' WHERE inviter=:inviter AND pid=:pid AND status=0';
    }
    $condition .= " and degree={$degree}";
    $count_num = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('xuan_mixloan_product_apply') . "
        WHERE inviter=:inviter AND pid=:pid and degree={$degree}", $arr) ? : 0;
    $count_succ_num = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('xuan_mixloan_product_apply') . "
        WHERE inviter=:inviter AND pid=:pid AND status>0 and degree={$degree}", $arr) ? : 0;
    $count_succ_bonus = pdo_fetchcolumn('SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ' . tablename('xuan_mixloan_product_apply') . "
        WHERE inviter=:inviter AND pid=:pid and degree={$degree}", $arr) ? : 0;
    $sql = 'SELECT id,re_bonus,done_bonus,extra_bonus,pid,status,phone,createtime,degree FROM ' . tablename('xuan_mixloan_product_apply'). $condition;
    $list = pdo_fetchall($sql, $arr);
    if (!empty($list)) {
        foreach ($list as &$row) {
            $row['product'] = m('product')->getList(['id','ext_info','name'],['id'=>$row['pid']])[$row['pid']];
            $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
            $row['bonus'] = $row['re_bonus'] + $row['done_bonus'] + $row['extra_bonus'];
            if ($row['status'] == 1) {
                $row['state'] = '已注册';
            } else if ($row['status'] == -1) {
                $row['state'] = '已失效';
            } else if ($row['status'] == 0) {
                $row['state'] = '申请中';
            } else if ($row['status'] == 2) {
                $row['state'] = '已成功';
            }
            if ($row['degree'] == 1) {
                $row['degree'] = '团队';
            } else if ($row['degree'] == 2) {
                $row['degree'] = '连队';
            }
            $row['phone'] = substr($row['phone'], 0, 4) . '****' . substr($row['phone'], -3);
        }
        unset($row);
    }
    include $this->template('product/customer_detail');
} else if ($operation == 'copy_short') {
    // 复制链接
    if (empty($member['id'])) {
        show_json(-1, [], '请先登陆');
    }
    $agent = m('member')->checkAgent($member['id']);
    if ($agent['code'] != 1) {
        // show_json(-1, [], '您还不是代理哦');
    }
    $type = trim($_GPC['type']);
    if (empty($type)) {
        show_json(-1, [], '请选择产品');
    }
    $urls = array();
    if ($type == 'is_new') {
        $list = m('product')->getList(['id', 'name', 'relate_id'], ['is_show'=>1, 'is_new'=>1], ' id desc', 9);
    } else if ($type == 'hot') {
        $agent = m('member')->checkAgent($member['id']);
        if ($agent['code'] != 1) {
            show_json(-1, [], '您还不是代理哦');
        }
        $list = m('product')->getList(['id', 'name', 'relate_id'], ['day_hot'=>1, 'is_show'=>1], FALSE);
    } else if ($type == 'credit') {
        $list = m('product')->getList(['id', 'name', 'relate_id'], ['type'=>1, 'is_show'=>1], FALSE);
    } else if ($type == 'loan_day') {
        $list = m('product')->getList(['id', 'name', 'relate_id'], ['type'=>2, 'is_show'=>1, 'count_time'=>1], ' sort desc');
    } else if ($type == 'loan_week') {
        $list = m('product')->getList(['id', 'name', 'relate_id'], ['type'=>2, 'is_show'=>1, 'count_time'=>7], ' sort desc');
    } else if ($type == 'loan_month') {
        $list = m('product')->getList(['id', 'name', 'relate_id'], ['type'=>2, 'is_show'=>1, 'count_time'=>30], ' sort desc');
    } else if ($type == 'loan_ready') {
        $list = m('product')->getList(['id', 'name', 'relate_id'], ['type'=>2, 'is_show'=>1, 'ready'=>1], ' sort desc');
    }
    foreach ($list as $item) {
        $urls[] = $item['name'] . ':' .  shortUrl($_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$item['relate_id'], 'inviter'=>$member['id'], 'pid'=>$item['id'], 'rand' => 1)));
    }
    show_json(1, ['urls' => $urls], '获取成功');
}