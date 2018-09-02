<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='service';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
if($operation=='service'){
	//客服服务
	$inviter = m('member')->getInviter($member['phone'], $member['openid']);
	if ($inviter) {
		$inviterInfo = pdo_fetch("SELECT phone,avatar,qrcode,nickname FROM ".tablename("xuan_mixloan_member"). " WHERE id={$inviter}");
	}
	include $this->template('mix/service');
} else if ($operation == 'tutorials') {
	//新手指南
	include $this->template('mix/tutorials');
} else if ($operation == 'question') {
	//常见问题
	$questions = pdo_fetchall("SELECT * FROM ".tablename('xuan_mixloan_help')." WHERE uniacid=:uniacid AND type=1 ORDER BY id DESC", array(':uniacid'=>$_W['uniacid']));
	foreach ($questions as &$question) {
		$question['ext_info'] = json_decode($question['ext_info'], 1);
	}
	unset($question);
	include $this->template('mix/question');
} else if ($operation == 'announce') {
	//常见问题
	$announces = pdo_fetchall("SELECT * FROM ".tablename('xuan_mixloan_help')." WHERE uniacid=:uniacid AND type=2 ORDER BY id DESC", array(':uniacid'=>$_W['uniacid']));
	foreach ($announces as &$announce) {
		$announce['ext_info'] = json_decode($announce['ext_info'], 1);
	}
	unset($announce);
	include $this->template('mix/announce');
} else if ($operation == 'bonus') {
    // 领取奖励
    $bonus  = array();
    $random = array();
    $list = pdo_fetchall('select id,ext_info from ' . tablename('xuan_mixloan_bonus') . '
        where uniacid=:uniacid order by sort asc', array(':uniacid' => $_W['uniacid']));
    $temp_time  = date('Y-m-d');
    $today = strtotime($temp_time);
    $starttime = strtotime("{$temp_time} -2 days");
    $endtime   = strtotime("{$temp_time} -1 days");
    $count_bonus = pdo_fetchcolumn('select sum(re_bonus+done_bonus+extra_bonus) from ' . tablename('xuan_mixloan_product_apply') . '
        where inviter=:inviter and type<>4 and createtime>=' . $starttime . ' and createtime<' . $endtime, array(':inviter' => $member['id'])) ? : 0;
    foreach ($list as &$row) {

        $record = pdo_fetchcolumn("select count(*) from " . tablename('xuan_mixloan_product_apply') . '
            where inviter=:inviter and type=4 and pid=' . $row['id'] . ' and createtime>=' . $today, array(':inviter' => $member['id']));
        $row['ext_info'] = json_decode($row['ext_info'], 1);
        if (!$record) {
            $row['if_get'] = $count_bonus >= $row['ext_info']['bonus'] ? 1 :0;
        } else {
            $row['if_get'] = 2;
        }
        $bonus[] = $row['ext_info']['money'];
    }
    unset($row);
    for ($i=0; $i < 10; $i++) {
        $rand = array();
        $rand['phone'] = rand(1111,9999);
        $number = rand(0, count($bonus) - 1);
        $rand['bonus'] = $bonus[$number];
        $random[] = $rand;
    }
    include $this->template('mix/bonus');
} else if ($operation == 'get_bonus') {
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select id,ext_info from ' . tablename('xuan_mixloan_bonus') . '
        where id=:id', array(':id' => $id));
    $item['ext_info'] = json_decode($item['ext_info'], 1);
    $temp_time  = date('Y-m-d');
    $today = strtotime($temp_time);
    $starttime = strtotime("{$temp_time} -2 days");
    $endtime   = strtotime("{$temp_time} -1 days");
    $record = pdo_fetchcolumn("select count(*) from " . tablename('xuan_mixloan_product_apply') . '
        where inviter=:inviter and type=4 and pid=' . $id . ' and createtime>=' . $today, array(':inviter' => $member['id']));
    if ($record) {
        show_json(-1, [], '您已领取过今日奖励');
    }
    $count_bonus = pdo_fetchcolumn('select sum(re_bonus+done_bonus+extra_bonus) from ' . tablename('xuan_mixloan_product_apply') . '
        where inviter=:inviter and type<>4 and createtime>=' . $starttime . ' and createtime<' . $endtime, array(':inviter' => $member['id'])) ? : 0;
    if ($count_bonus < $row['ext_info']['bonus']) {
        show_json(-1, [], '条件没有达到无法领取哦');
    }
    $insert = array(
        'uniacid' => $_W['uniacid'],
        'uid' => $member['id'],
        'pid' => $id,
        'inviter' => $member['id'],
        'extra_bonus'=>$item['ext_info']['money'],
        'status'=>2,
        'createtime'=>time(),
        'type'=>4
    );
    pdo_insert('xuan_mixloan_product_apply', $insert);
    show_json(1, [], '领取成功');
} else if ($operation == 'bonus_record') {
    // 领取记录
    $list = pdo_fetchall('select extra_bonus,createtime from ' . tablename('xuan_mixloan_product_apply') . '
        where inviter=:inviter and type=4 order by id desc', array(':inviter' => $member['id']));
    include $this->template('mix/bonus_record');
}