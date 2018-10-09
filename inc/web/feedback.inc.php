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
    if (!empty($_GPC['phone'])) {
        $wheres.= " AND phone LIKE '%{$_GPC['phone']}%'";
    }
    if (!empty($_GPC['pro_name'])) {
        $wheres.= " AND pro_name LIKE '%{$_GPC['pro_name']}%'";
    }
    $sql = 'select * from ' . tablename('xuan_mixloan_feedback') . " where  1 " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_feedback') . " where  1 " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_feedback', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('feedback', array('op' => '')), "sccuess");
}else if ($operation == 'update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_feedback"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        pdo_delete('xuan_mixloan_poster', array('pid'=>$item['id']));
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_feedback', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('feedback', array('op' => '')), "sccuess");
    }
}
include $this->template('feedback');
?>