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
    $sql = 'select id,name,createtime from ' . tablename('xuan_mixloan_service') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_service') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_service', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('service', array('op' => '')), "sccuess");
} else if ($operation == 'add') {
    //添加
    if ($_GPC['post'] == 1) {
        $insert = $_GPC['data'];
        $insert['uniacid'] = $_W['uniacid'];
        $insert['createtime'] = time();
        $insert['ext_info']['conditions'] = htmlspecialchars_decode($insert['ext_info']['conditions']);
        $insert['ext_info']['reminds'] = htmlspecialchars_decode($insert['ext_info']['reminds']);
        $insert['ext_info'] = json_encode($insert['ext_info']);
        pdo_insert('xuan_mixloan_service', $insert);
        message("提交成功", $this->createWebUrl('service', array('op' => '')), "sccuess");
    }
} else if ($operation == 'update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_service"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if (!empty($item['area_city'])) {
        $area = pdo_fetchcolumn('select text from '.tablename('xuan_mixloan_area').'
            where value=:value', array(':value' => $item['area_city']));
    } else {
        $area = pdo_fetchcolumn('select text from '.tablename('xuan_mixloan_area').'
            where value=:value', array(':value' => $item['area_province']));
    }
    if ($_GPC['post'] == 1) {
        $update = $_GPC['data'];
        $update['ext_info']['conditions'] = htmlspecialchars_decode($update['ext_info']['conditions']);
        $update['ext_info']['reminds'] = htmlspecialchars_decode($update['ext_info']['reminds']);
        $update['ext_info'] = json_encode($update['ext_info']);
        pdo_update('xuan_mixloan_service', $update, array('id'=>$id));
        message("提交成功", $this->createWebUrl('service', array('op' => '')), "sccuess");
    }
} 

include $this->template('service');
?>