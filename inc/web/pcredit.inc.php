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
    $sql = 'select * from ' . tablename('xuan_mixloan_pcredit') . "
            where 1 " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
    }
    unset($row);
    $total = pdo_fetchcolumn( 'select count(*) from ' . tablename('xuan_mixloan_pcredit') . "
            where 1 " . $wheres . ' ORDER BY ID DESC');
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_pcredit', array('id'=>$_GPC['id']));
    message("提交成功", $this->createWebUrl('pcredit', array('op' => '')), "sccuess");
} else if ($operation == 'update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_pcredit"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_update('xuan_mixloan_pcredit', $data, array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('pcredit', array('op' => '')), "sccuess");
    }
} else if ($operation == 'add') {
    //添加
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['createtime'] =time();
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_pcredit', $data);
        message("提交成功", $this->createWebUrl('pcredit', array('op' => '')), "sccuess");
    }
}
include $this->template('pcredit');
?>