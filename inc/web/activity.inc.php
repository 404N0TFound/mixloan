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
    $sql = 'select * from ' . tablename('xuan_mixloan_activity') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        $row['ext_info'] = json_decode($row['ext_info'], 1);
    }
    unset($row);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_activity') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_activity', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('activity', array('op' => '')), "sccuess");
} else if ($operation == 'add') {
    //添加
    $starttime = date('Y-m-d');
    $endtime = date('Y-m-d', strtotime("{$starttime} +1 week"));
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $starttime = $_GPC['time']['start'];
        $endtime = $_GPC['time']['end'];
        $data['ext_info']['starttime'] = strtotime($starttime);
        $data['ext_info']['endtime'] =  strtotime($endtime);
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_activity', $data);
        message("提交成功", $this->createWebUrl('activity', array('op' => '')), "sccuess");
    }
} else if ($operation == 'update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_activity"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    $starttime = date('Y-m-d H:i:s', $item['ext_info']['starttime']);
    $endtime = date('Y-m-d H:i:s', $item['ext_info']['endtime']);
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $starttime = $_GPC['time']['start'];
        $endtime = $_GPC['time']['end'];
        $data['ext_info']['starttime'] = strtotime($starttime);
        $data['ext_info']['endtime'] =  strtotime($endtime);
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_update('xuan_mixloan_activity', $data, array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('activity', array('op' => '')), "sccuess");
    }
} else if ($operation == 'rank_list') {
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_activity"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    $starttime = $item['ext_info']['starttime'];
    $endtime = $item['ext_info']['endtime'];
    if ($item['type'] == 1) {
        //挑战代理
        $list = pdo_fetchall('select COUNT(*) AS count,inviter from ' . tablename('xuan_mixloan_product_apply') . "
            where uniacid={$_W['uniacid']} and createtime>{$starttime} and createtime<={$endtime} and type=2 and degree=1
            group by inviter
            order by count desc limit 10");
    } else if ($item['type'] == 2) {
        //挑战佣金
        $list = pdo_fetchall('select SUM(re_bonus+done_bonus+extra_bonus) AS sum,inviter from ' . tablename('xuan_mixloan_product_apply') . "
            where uniacid={$_W['uniacid']} and createtime>{$starttime} and createtime<={$endtime} and type=2
            group by inviter having sum <> 0
            order by sum desc limit 10");
    }
    foreach ($list as &$row) {
        $type = $item['type'] == 1 ? 3 : 4;
        $man = pdo_fetch('select nickname,avatar from ' . tablename('xuan_mixloan_member') . '
            where id=:id', array(':id' => $row['inviter']));
        $row['avatar'] = $man['avatar'];
        $row['nickname'] = $man['nickname'];
        $row['is_pay'] = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_product_apply'). "
            where pid={$item['id']} and type={$type} and inviter={$row['inviter']}") ? : 0;
    }
    unset($row);
} else if ($operation == 'rank_update') {
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_activity"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post']) {
        $data = $_GPC['data'];
        $data['createtime'] = time();
        $data['uniacid'] = $_W['uniacid'];
        $data['inviter'] = $_GPC['inviter'];
        $data['pid'] = $id;
        $data['type'] = $item['type'] == 1 ? 3 : 4;
        $data['degree'] = 1;
        $data['status'] = 2;
        pdo_insert('xuan_mixloan_product_apply', $data);
        message('发放成功', '', 'sccuess');
    }
}
include $this->template('activity');
?>