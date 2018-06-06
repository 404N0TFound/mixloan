<?php
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$config = $this->module['config'];
if (empty($_GPC['op'])) {
    $operation = 'list';
} else {
    $operation = $_GPC['op'];
}
if ($operation == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    if (!empty($_GPC['name'])) {
        $wheres.= " AND name LIKE '%{$_GPC['name']}%'";
    }
    $sql = 'select id,name,createtime from ' . tablename('xuan_mixloan_advs') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_advs') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_advs', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('advs', array('op' => '')), "sccuess");
} else if ($operation == 'add') {
    //添加
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_advs', $data);
        message("提交成功", $this->createWebUrl('advs', array('op' => '')), "sccuess");
    }
} else if ($operation == 'update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = m('advs')->getList([],['id'=>$id])[$id];
    if ($_GPC['post'] == 1) {
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_advs', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('advs', array('op' => '')), "sccuess");
    }
} else if ($operation == 'statics') {
    //点击统计
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = " uniacid={$_W['uniacid']}";
    $id = intval($_GPC['id']);
    $labels = array();
    $datas = array();
    $wheres .= " AND adv_id={$id}";
    if (!empty($_GPC['time'])) {
        $starttime = $_GPC['time']['start'];
        $endtime = $_GPC['time']['end'];
        $start = strtotime($starttime);
        $end = strtotime($endtime);
        $wheres .= " and createtime>{$start} and createtime<={$end}";
    } else {
        $endtime = date("Y-m-d H:i:s");
        $starttime = date("Y-m-d H:i:s", strtotime("{$endtime} -1 month"));
    }
    $sql = 'select id,createtime,uid from ' . tablename('xuan_mixloan_advs_click') . "
        where  " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        $man = pdo_fetch('select nickname,avatar from '.tablename('xuan_mixloan_member').'
            where id=:id', array(':id'=>$row['uid']));
        $row['nickname'] = $man['nickname'];
        $row['avatar'] = $man['avatar'];
    }
    unset($row);
    $start_hour_time = strtotime($starttime);
    $end_hour_time = strtotime($endtime);
    $average = ceil(($end_hour_time - $start_hour_time) / 12);
    for ($i = 0; $i < 12; $i++) {
        $temp_time_end = $start_hour_time + $average;
        $num = pdo_fetchcolumn("SELECT COUNT(1) FROM " . tablename('xuan_mixloan_advs_click') . "
            WHERE createtime >= {$start_hour_time} AND createtime<= {$temp_time_end}
            AND uniacid={$_W['uniacid']} AND adv_id={$id}");
        $labels[] = date('Y-m-d',$start_hour_time);
        $datas[] = $num;
        $start_hour_time += $average;
    }
    $labels = json_encode($labels);
    $datas = json_encode($datas);
    $total = pdo_fetchcolumn( 'select count(*) from ' . tablename('xuan_mixloan_advs_click') . "
        where " . $wheres );
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'click_delete') {
    pdo_delete('xuan_mixloan_advs_click', array("id" => $_GPC["id"]));
    message("提交成功", referer(), "sccuess");
}
include $this->template('advs');
?>