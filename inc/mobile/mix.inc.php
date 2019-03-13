<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='service';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
if($operation=='service'){
	//客服服务
	include $this->template('mix/service');
} else if ($operation == 'tutorials') {
	//新手指南
	include $this->template('mix/tutorials');
} else if ($operation == 'apply_cache') {
    require_once('../addons/xuan_mixloan/inc/model/cache.php');
    $cache = new Xuan_mixloan_Cache();
    $cache_img = $cache->doimg();
    if (!$cache_img['result']) {
        show_json(-1,[],'生成验证码失败');
    }
    $code = $cache->getCode();
    setcookie('authcode', sha1(md5($code)), time()+300);
    show_json(1, ['img' => $cache_img['file']]);
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
        where inviter=:inviter and type<>5 and createtime>=' . $starttime . ' and createtime<' . $endtime, array(':inviter' => $member['id'])) ? : 0;
    foreach ($list as &$row) {

        $record = pdo_fetchcolumn("select count(*) from " . tablename('xuan_mixloan_product_apply') . '
            where inviter=:inviter and type=5 and pid=' . $row['id'] . ' and createtime>=' . $today, array(':inviter' => $member['id']));
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
        where inviter=:inviter and type=5 and pid=' . $id . ' and createtime>=' . $today, array(':inviter' => $member['id']));
    if ($record) {
        show_json(-1, [], '您已领取过今日奖励');
    }
    $count_bonus = pdo_fetchcolumn('select sum(re_bonus+done_bonus+extra_bonus) from ' . tablename('xuan_mixloan_product_apply') . '
        where inviter=:inviter and type<>5 and createtime>=' . $starttime . ' and createtime<' . $endtime, array(':inviter' => $member['id'])) ? : 0;
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
        'type'=>5
    );
    pdo_insert('xuan_mixloan_product_apply', $insert);
    show_json(1, [], '领取成功');
} else if ($operation == 'bonus_record') {
    // 领取记录
    $list = pdo_fetchall('select extra_bonus,createtime from ' . tablename('xuan_mixloan_product_apply') . '
        where inviter=:inviter and type=5 order by id desc', array(':inviter' => $member['id']));
    include $this->template('mix/bonus_record');
} else if ($operation == 'announce') {
    //公告
    $cid = trim($_GPC['cid']);
    $announce = pdo_fetch('select id,ext_info from ' . tablename('xuan_mixloan_announce') . '
		where uniacid=:uniacid order by id desc', array(':uniacid' => $_W['uniacid']));
    if ($announce) {
        $announce['ext_info'] = json_decode($announce['ext_info'], 1);
        if (!$cid) {
            show_json(1, [], $announce['ext_info']['content']);
        }
        $record = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_announce_record') . '
			where relate_id=:relate_id and cid=:cid', array(':relate_id'=>$announce['id'], ':cid'=>$cid));
        if (!$record) {
            $insert = array();
            $insert['cid'] = $cid;
            $insert['relate_id'] = $announce['id'];
            pdo_insert('xuan_mixloan_announce_record', $insert);
            show_json(1, [], $announce['ext_info']['content']);
        }
    } else {
        show_json(-1);
    }
} else if ($operation == 'feedback') {
    // 返佣反馈
    $list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_feedback') . '
		where uid=:uid order by id desc', array(':uid' => $member['id']));
    foreach ($list as &$row) {
        $row['ext_info'] = json_decode($row['ext_info'], 1);
    }
    unset($row);
    if ($_GPC['post']) {
        $phone = trim($_GPC['phone']);
        $name = trim($_GPC['name']);
        $pro_name = trim($_GPC['pro_name']);
        $get_money_pic = trim($_GPC['get_money_pic']);
        $sms_pic = trim($_GPC['sms_pic']);
        $apply_date = trim($_GPC['apply_date']);
        $agent_phone = trim($_GPC['agent_phone']);
        $ext_info = array();
        $ext_info['sms_pic'] = $sms_pic;
        $ext_info['apply_date'] = $apply_date;
        $ext_info['agent_phone'] = $agent_phone;
        $ext_info['get_money_pic'] = $get_money_pic;
        $insert = array();
        $insert['uid'] = $member['id'];
        $insert['pro_name'] = $pro_name;
        $insert['name'] = $name;
        $insert['phone'] = $phone;
        $insert['ext_info'] = json_encode($ext_info);
        $insert['createtime'] = time();
        pdo_insert('xuan_mixloan_feedback', $insert);
        show_json(1, [], '成功');
    }
    include $this->template('mix/feedback');
}