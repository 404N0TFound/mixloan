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
    if (!empty($_GPC['status'])) {
        $wheres.= " AND status = {$_GPC['status']}";
    }
    $sql = 'select id,name,createtime from ' . tablename('xuan_mixloan_loan') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_loan') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_loan', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('loan', array('op' => '')), "sccuess");
} else if ($operation == 'add') {
    //添加
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['uniacid'] = $_W['uniacid'];
        $data['type'] = implode(',', $data['type']); 
        $data['createtime'] = time();
        $data['ext_info']['conditions'] = htmlspecialchars_decode($data['ext_info']['conditions']);
        $data['ext_info']['reminds'] = htmlspecialchars_decode($data['ext_info']['reminds']);
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_loan', $data);
        message("提交成功", $this->createWebUrl('loan', array('op' => '')), "sccuess");
    }
} else if ($operation == 'update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_loan"). " where id={$id}");
    $item['type'] = array_values(explode(',', $item['type']));
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $_GPC['data']['type'] = implode(',', $_GPC['data']['type']);
        $_GPC['data']['ext_info']['conditions'] = htmlspecialchars_decode($_GPC['data']['ext_info']['conditions']);
        $_GPC['data']['ext_info']['reminds'] = htmlspecialchars_decode($_GPC['data']['ext_info']['reminds']);
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_loan', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('loan', array('op' => '')), "sccuess");
    }
} else if ($operation == 'advs_list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    $sql = 'select id,name,createtime from ' . tablename('xuan_mixloan_loan_advs') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_loan_advs') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'advs_delete') {
    pdo_delete('xuan_mixloan_loan_advs', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('loan', array('op' => 'advs_list')), "sccuess");
} else if ($operation == 'advs_add') {
    //添加
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_loan_advs', $data);
        message("提交成功", $this->createWebUrl('loan', array('op' => 'advs_list')), "sccuess");
    }
} else if ($operation == 'advs_update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_loan_advs"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_loan_advs', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('loan', array('op' => 'advs_list')), "sccuess");
    }
}
include $this->template('loan');
?>