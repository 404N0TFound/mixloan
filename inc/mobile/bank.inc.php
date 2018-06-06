<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='extend_limit';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$member['user_type'] = m('member')->checkAgent($member['id']);
if ($operation == 'extend_limit') {
	//提升额度
	$bank = array();
	$temp_list = m('bank')->getList();
	$count = 0;
	foreach ($temp_list as $value) {
		$count++;
		$temp[] = $value;
		if ($count==3) {
			$banks[] = $temp;
			$temp = array();
			$count = 0;
		}
	}
	include $this->template('bank/extend_limit');
} else if ($operation == 'extend_by_phone') {
	//获取电话提额
	$bank = pdo_fetch('SELECT * FROM '.tablename("xuan_mixloan_bank")." WHERE id=:id", array(':id'=>$_GPC['id']));
	if (!$bank) {
		show_json(-1, null, '银行资料丢失');
	}
	$bank['ext_info'] = json_decode($bank['ext_info'], true);
	$arr['title'] = $bank['name'];
	$arr['intro'] = $bank['ext_info']['extend_tips'];
	$arr['tel'] = $bank['ext_info']['extend_phone'];
	show_json(1, $arr);
} else if ($operation == 'getBank') {
	//随机获取银行
	$sql = "SELECT * 
			FROM ".tablename('xuan_mixloan_bank')." AS t1 JOIN (SELECT ROUND(RAND() * (SELECT MAX(id) FROM ".tablename('xuan_mixloan_bank').")) AS id) AS t2 
			WHERE t1.id >= t2.id AND t1.uniacid=:uniacid
			ORDER BY t1.id ASC LIMIT 6";
	$banks = pdo_fetchall($sql, array(':uniacid'=>$_W['uniacid']));
	if (!$banks) {
		show_json(1, null, '暂时没有数据哦');
	}
	foreach ($banks as $row) {
		if($row['ext_info']) $row['ext_info'] = json_decode($row['ext_info'], true);
		$res['id'] 		= $row['id'];
		$res['title1'] 	= $row['name'];
		$res['title2'] 	= $row['ext_info']['subscribe_tips'];
		$res['imgs']	= tomedia($row['ext_info']['logo']);
		$res['url'] 	= $row['ext_info']['subscribe_url'];
		$ret[] 			= $res;
	}
	show_json(1, $ret);
} else if ($operation == 'extend_query') {
	//办卡查询
	$bank = array();
	$temp_list = m('bank')->getList();
	$count = 0;
	foreach ($temp_list as $value) {
		$count++;
		$temp[] = $value;
		if ($count==3) {
			$banks[] = $temp;
			$temp = array();
			$count = 0;
		}
	}
	include $this->template('bank/extend_query');
} else if ($operation == 'extend_tips') {
	//提额技巧
	$banks = m('bank')->getList();
	$tips = m('bank')->getArtical(['id', 'title', 'bank_id'], ['is_hot'=>1]);
	foreach ($tips as &$v) {
		$v['logo'] = $banks[$v['bank_id']]['ext_info']['logo'];
	}
	unset($v);
	include $this->template('bank/extend_tips');
} else if ($operation == 'getArtical') {
	//获取文章
	$bank_id = intval($_GPC['bank_id']);
	if (!$bank_id) {
		show_json(-1, null, '银行id为空');
	}
	$get = ['id', 'title'];
	$condition = ['bank_id'=>$bank_id];
	$list = m('bank')->getArtical($get, $condition);
	show_json(1, array_values($list));
} else if ($operation == 'want_subscribe') {
	//我要办卡
	$temp_list = m('bank')->getList();
	$count = 0;
	foreach ($temp_list as $value) {
		$count++;
		$temp[] = $value;
		if ($count==4) {
			$banks[] = $temp;
			$temp = array();
			$count = 0;
		}
	}
	$get = ['id', 'name', 'apply_nums', 'ext_info'];
	$list = m('bank')->getCard($get);
	$recommends = m('bank')->getRecommendCard($list);
	include $this->template('bank/want_subscribe');
} else if ($operation =='cardView') {
	//更新查看人数
	$id = intval($_GPC['id']);
	$sql = "UPDATE ".tablename("xuan_mixloan_bank_card")." SET `apply_nums`=`apply_nums`+1 WHERE id={$id}";
	pdo_run($sql); 
	show_json(1);
} else if ($operation == 'bank_card') {
	//查看全部银行卡
	$banks = m('bank')->getList();
	include $this->template('bank/bank_card');
} else if ($operation == 'bank_card_list') {
	//根据条件获取银行卡
	$conditon = [];
	if (isset($_GPC['bank_id']) && !empty($_GPC['bank_id'])) {
		$conditon['bank_id'] = intval($_GPC['bank_id']);
	}
	if (isset($_GPC['card_type']) && !empty($_GPC['card_type'])) {
		$conditon['card_type'] = intval($_GPC['card_type']);
	}
	if (isset($_GPC['icon_type']) && !empty($_GPC['icon_type'])) {
		$conditon['icon_type'] = intval($_GPC['icon_type']);
	}
	if (isset($_GPC['year_fee']) && !empty($_GPC['year_fee'])) {
		$conditon['year_fee'] = intval($_GPC['year_fee']);
	}
	$get = ['id', 'name', 'apply_nums', 'ext_info'];
	$list = m('bank')->getCard($get, $conditon);
	if ($list) {
		foreach ($list as &$row) {
			$row['ext_info']['pic'] = tomedia($row['ext_info']['pic']);
			$row['ext_info']['tag'] = array_values($row['ext_info']['tag']);
		}
		unset($row);
	}
	if (!empty($list)) {
		$list = array_values($list);
		show_json(1, $list);
	} else {
		show_json(-1);
	}
} else if ($operation == 'first_card') {
	$get = ['id', 'name', 'apply_nums', 'ext_info'];
	$cards = m('bank')->getCard($get, ['recommend_type'=>1], FALSE, 6);
	include $this->template('bank/first_card');
} else if ($operation == 'fast_card') {
	$get = ['id', 'name', 'apply_nums', 'ext_info'];
	$cards = m('bank')->getCard($get, ['recommend_type'=>2], FALSE, 10);
	include $this->template('bank/fast_card');
} else if ($operation == 'hot_card') {
	$get = ['id', 'name', 'apply_nums', 'ext_info'];
	$hots = m('bank')->getCard($get, [], 'apply_nums DESC', 6);
	$cards = m('bank')->getCard($get, ['recommend_type'=>3], FALSE, 6);
	include $this->template('bank/hot_card');
} else if ($operation == 'high_card') {
	$get = ['id', 'name', 'apply_nums', 'ext_info'];
	$cards = m('bank')->getCard($get, ['recommend_type'=>4], FALSE, 6);
	include $this->template('bank/high_card');
} else if ($operation == 'artical') {
	//详情
	$id = intval($_GPC['id']);
	if (!$id) {
		message('id不能为空', '', 'error');
	}
	$res = m('bank')->getArtical([],['id'=>$id]);
	if (!$res) {
		message('抱歉，文章已不存在', '', 'error');
	}
	$item = $res[$id];
	if (preg_match('/src=[\'\"]?([^\'\"]*)[\'\"]?/i', $item['ext_info']['content'], $result)) {
		$share_image = $result[1];
	} else {
		$share_image = tomedia($config['share_image']);
	}
	include $this->template('bank/artical');
} else if ($operation == 'display') {
    //银行展示页
    $id = intval($_GPC['id']);
    if (empty($id)) {
        message('出错了', '', 'error');
    }
    $item = m('bank')->getCard(['id', 'ext_info'], ['id'=>$id])[$id];
    if (empty($item)) {
        message('出错了', '', 'error');
    }
    if (is_weixin()) {
        header("location:{$item['ext_info']['url']}");
        exit();
    }
    include $this->template('bank/display');
}