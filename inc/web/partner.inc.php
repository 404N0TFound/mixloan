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
    if (!empty($_GPC['status'])) {
        $wheres.= " AND status = '{$_GPC['status']}'";
    }
    if (!empty($_GPC['phone'])) {
        $wheres.= " AND phone LIKE '%{$_GPC['phone']}%'";
    }
    if (!empty($_GPC['register_time'])) {
        $register['starttime'] = $_GPC['register_time']['start'];
        $register['endtime'] = $_GPC['register_time']['end'];
    } else {
        $register['starttime'] = date('Y-m-d');
        $register['endtime'] = date('Y-m-d H:i:s');
    }
    $starttime = strtotime($register['starttime']);
    $endtime = strtotime($register['endtime']);
    $sql = 'select * from ' . tablename('xuan_mixloan_partner') . "
            ORDER BY id DESC";
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        $row['register_count'] = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_member') . "
                                where inviter=:inviter
                                and createtime>{$starttime} and createtime<={$endtime}", array(':inviter' => $row['id'])) ? : 0;
    }
    unset($row);
    $total = pdo_fetchcolumn( 'select * from ' . tablename('xuan_mixloan_partner') . "
                        ORDER BY id DESC");
    $pager = pagination($total, $pindex, $psize);
    $login_url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('partner', array('op'=>'login'));
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloann_partner', array("id" => $_GPC["id"]));
    message("提交成功", referer(), "sccuess");
} else if ($operation == 'update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_partner"). " where id={$id}");
    if ($_GPC['post'] == 1) {
    	$data = $_GPC['data'];
        pdo_update('xuan_mixloan_partner', $data, array('id'=>$item['id']));
        message("提交成功", referer(), "sccuess");
    }
} else if ($operation == 'add') {
    //编辑
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['createtime'] = time();
        $data['uniacid'] = $_W['uniacid'];
        pdo_insert('xuan_mixloan_partner', $data);
        message("提交成功", $this->createWebUrl('partner'), "sccuess");
    }
}
include $this->template('partner');
