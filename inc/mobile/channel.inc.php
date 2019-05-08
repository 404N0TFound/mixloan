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
	$advs = m('channel')->getAdvs();
	$subjects = m('channel')->getSubjectList(['id', 'name', 'ext_info'], ['type'=>1]);
	$channel_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>1], 'sort DESC', 3);
	$channel_low_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>1], 'id DESC', 6);
	$credit_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>2], 'sort DESC', 3);
	$course_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>3], 'sort DESC', 3);
	$hot_list = m('channel')->getList(['id', 'title', 'apply_nums'], ['type'=>1, 'is_hot'=>1], 'sort DESC', 3);
	include $this->template('channel/index');
} elseif ($operation == 'credit_card') {
	//信用卡
	$permission = pdo_fetch('select endtime from ' . tablename('xuan_mixloan_channel_permission') . '
						where uid=:uid 
						order by id desc', array(':uid' => $member['id']));
	if ($permission['endtime'] < time()) {
		message('请先购买阅读权限', $this->createMobileUrl('channel', array('op' => 'pay')), 'error');
	}
	$advs = m('channel')->getAdvs();
	$subjects = m('channel')->getSubjectList(['id', 'name', 'ext_info'], ['type'=>2]);
	$channel_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>1], 'sort DESC', 3);
	$credit_low_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>2], 'id DESC', 6);
	$credit_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>2], 'sort DESC', 3);
	$course_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>3], 'sort DESC', 3);
	$hot_list = m('channel')->getList(['id', 'title', 'apply_nums'], ['type'=>2, 'is_hot'=>1], 'sort DESC', 3);
	include $this->template('channel/credit_card');
} elseif ($operation == 'course') {
	//新手教程
	$course_list = m('channel')->getList(['id', 'title'], ['type'=>3], 'sort DESC');
	include $this->template('channel/course');
} else if ($operation == 'getNew') {
	//ajax获取新数据
	$type = intval($_GPC['type']);
	$offset = intval($_GPC['rollcount']);
	// $subject = m('channel')->getSubjectList(['id', 'ext_info'], ['type'=>$type], FALSE, 1, $offset);
	// if (empty($subject)) {
	// 	show_json(-1);
	// } else {
	// 	$ids = array_keys($subject);
	// 	$subjectRes = $subject[$ids[0]];
	// }
	// $list = m('channel')->getList(['id', 'title', 'subject_id', 'createtime', 'ext_info', 'apply_nums'], ['subject_id'=>$subjectRes['id']], 'sort DESC', 4);
	$list = m('channel')->getList(['id', 'title', 'subject_id', 'createtime', 'ext_info', 'apply_nums'], ['type'=>$type], 'id DESC', 4, $offset);
	if (empty($list)) {
		show_json(-1);
	}
	// $min_k = min(array_keys($list));
	// $list[$min_k]['stress'] = 1;
	// $list[$min_k]['ext_info']['pic'] = tomedia($subjectRes['ext_info']['pic']);
	show_json(1,array_values($list));
} else if ($operation == 'artical') {
	//详情
	if ($config['vip_channel']) {
		$agent = m('member')->checkAgent($member['id']);
		if ($agent['code']!=1) {
	        header("location:{$this->createMobileUrl('vip', array('op'=>'buy'))}");
		}
	}
	$id = intval($_GPC['id']);
	if (!$id) {
		message('id不能为空', '', 'error');
	}
	$res = m('channel')->getList([],['id'=>$id]);
	if (!$res) {
		message('抱歉，文章已不存在', '', 'error');
	}
	$item = $res[$id];
	pdo_update('xuan_mixloan_channel', array('apply_nums'=>$item['apply_nums']+1), array('id'=>$item['id']));
	if (preg_match('/src=[\'\"]?([^\'\"]*)[\'\"]?/i', $item['ext_info']['content'], $result)) {
		$share_image = $result[1];
	} else {
		$share_image = tomedia($config['share_image']);
	}
	include $this->template('channel/artical');
} else if ($operation == 'search') {
	//搜索
	if ($_GPC['post'] == 1) {
		if ($_GPC['keyword']) {
			$keyword = trim($_GPC['keyword']);
		}
		$subjects = m('channel')->getSubjectList(['id'], ['name'=>$keyword]);
		if (!empty($subjects)) {
			$subjectIds = array_keys($subjects);
			$list =  m('channel')->getList(['id', 'title', 'apply_nums', 'createtime', 'ext_info'], ['subject_id'=>$subjectIds]);
		} else {
			$list = m('channel')->getList(['id', 'title', 'apply_nums', 'createtime', 'ext_info'], ['title'=>$keyword]);
		}
		if (!empty($list)) {
			show_json(1, array_values($list));
		}
		show_json(-1);
	}
	include $this->template('channel/search');
} else if ($operation == 'keyword') {
	//关键词联想
	if ($_GPC['keyword']) {
		$keyword = trim($_GPC['keyword']);
	}
	$list = m('channel')->getList(['id', 'title'], ['title'=>$keyword]);
	if (!empty($list)) {
		show_json(1, array_values($list));
	} else {
		show_json(-1);
	}
} else if ($operation == 'getCommendSubjects') {
	//随机出专题
	$subjects = m('channel')->getCommendSubjects();
	if (!empty($subjects)) {
		show_json(1, array_values($subjects));
	} else {
		show_json(-1);
	}
} else if ($operation == 'pay') {
	//支付
	include $this->template('channel/pay');
} else if ($operation == 'choose_pay_type') {
	//选择支付
	$type = trim($_GPC['type']);
	include $this->template('channel/choose_pay_type');
} else if ($operation == 'pay_submit') {
	// 支付提交
	$type = intval($_GPC['type']);
	if (!$member['phone']) {
		message('请先绑定手机号', $this->createMobileUrl('index'), 'error');
	}
    if ($type == 1) {
    	$fee = $config['buy_read_price_a'];
		$endtime = time() + 30 * 86400;
    } else if ($type == 2) {
    	$fee = $config['buy_read_price_b'];
		$endtime = time() + 365 * 86400;
    } else if ($type == 3) {
    	$fee = $config['buy_read_price_c'];
		$endtime = time() + 36500 * 86400;
    }
    if ($fee == 0) {
        $tid = "20002" . date('YmdHis', time());
		$insert = array();
		$insert['uid'] = $member['id'];
		$insert['uniacid'] = $_W['uniacid'];
		$insert['createtime'] = time();
		$insert['endtime'] = $endtime;
		$insert['type'] = $type;
		$insert['fee'] = $fee;
		$insert['tid'] = $tid;
		pdo_insert('xuan_mixloan_channel_permission', $insert);
		message('支付成功', $this->createMobileUrl('channel', array('op' => 'credit_card')), 'sccuess');
    }
    if ($_GPC['way'] == 'alipay')
    {
        $tid = "20001" . date('YmdHis', time());
        $title = "购买{$config['title']}借条专区阅读权限";
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
        $notify_url = 'http://hqpu.zdkjlm.com/addons/xuan_mixloan/lib/wechat/payResult.php';
        $record = pdo_fetch('select * from ' .tablename('xuan_mixloan_paylog'). '
		    where type=1 and is_pay=0 and uid=:uid order by id desc', array(':uid'=>$member['id']));
        if (empty($record)) {
            $tid = "20001" . date('YmdHis', time());
            $trade_no = "ZML".date("YmdHis");
            $insert = array(
                'notify_id'=>$trade_no,
                'tid'=>$tid,
                'createtime'=>time(),
                'uid'=>$member['id'],
                'uniacid'=>$_W['uniacid'],
                'fee'=>$fee,
                'is_pay'=>0,
                'type'=>1
            );
            pdo_insert('xuan_mixloan_paylog', $insert);
        } else {
            if ($record['createtime']+60 < time())
            {
                //超过1分钟重新发起订单
                $tid = "20001" . date('YmdHis', time());
                $trade_no = "ZML".date("YmdHis");
                $insert = array(
                    'notify_id'=>$trade_no,
                    'tid'=>$tid,
                    'createtime'=>time(),
                    'uid'=>$member['id'],
                    'uniacid'=>$_W['uniacid'],
                    'fee'=>$fee,
                    'is_pay'=>0,
                    'type'=>2
                );
                pdo_insert('xuan_mixloan_paylog', $insert);
            }
            else
            {
                $trade_no = $record['notify_id'];
            }
        }
        $result = m('pay')->H5pay($trade_no, $fee, $notify_url);
        if ($result['code'] == 1) {
            $redirect_url = urlencode($_W['siteroot'] . 'app/' .
                $this->createMobileUrl('vip', array('op'=>'checkPay')));
            $url = "{$result['data']['url']}&redirect_url={$redirect_url}";
        } else {
            message('请稍后再试', $this->createMobileUrl('user'), 'error');
        }
        include $this->template('vip/openHref');
    }
    exit;
}