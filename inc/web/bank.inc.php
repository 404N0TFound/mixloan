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
    $sql = 'select id,name,createtime from ' . tablename('xuan_mixloan_bank') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    unset($row);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_bank') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_bank', array("id" => $_GPC["id"]));
    pdo_delete('xuan_mixloan_bank_artical', array('bank_id'=>$_GPC['id']));
    pdo_delete('xuan_mixloan_bank_card', array('bank_id'=>$_GPC['id']));
    message("提交成功", $this->createWebUrl('bank', array('op' => '')), "sccuess");
} else if ($operation == 'add') {
    //添加
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_bank', $data);
        message("提交成功", $this->createWebUrl('bank', array('op' => '')), "sccuess");
    }
} else if ($operation == 'update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_bank"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_bank', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('bank', array('op' => '')), "sccuess");
    }
} else if ($operation == 'book_list') {
    $bank_id = intval($_GPC['bank_id']);
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    $sql = 'select id,title,createtime from ' . tablename('xuan_mixloan_bank_artical') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    unset($row);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_bank_artical') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'book_delete') {
    $bank_id = intval($_GPC['bank_id']);
    pdo_delete('xuan_mixloan_bank_artical', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('bank', array('op' => 'book_list', 'bank_id'=>$bank_id)), "sccuess");
} else if ($operation == 'book_add') {
    //添加广告
    load()->func('tpl');
    $bank_id = intval($_GPC['bank_id']);
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['bank_id'] = $bank_id;
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['ext_info']['content'] = htmlspecialchars_decode($data['ext_info']['content']);
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_bank_artical', $data);
        message("提交成功", $this->createWebUrl('bank', array('op' => 'book_list', 'bank_id'=>$bank_id)), "sccuess");
    }
} else if ($operation == 'book_update') {
    //编辑广告
    load()->func('tpl');
    $bank_id = intval($_GPC['bank_id']);
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_bank_artical"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $_GPC['data']['ext_info']['content'] = htmlspecialchars_decode($_GPC['data']['ext_info']['content']);
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_bank_artical', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('bank', array('op' => 'book_list', 'bank_id'=>$bank_id)), "sccuess");
    }
} else if ($operation == 'card_list') {
    $bank_id = intval($_GPC['bank_id']);
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = ' and bank_id='.$bank_id;
    $sql = 'select id,name,createtime from ' . tablename('xuan_mixloan_bank_card') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    unset($row);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_bank_card') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'card_delete') {
    $bank_id = intval($_GPC['bank_id']);
    pdo_delete('xuan_mixloan_bank_card', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('bank', array('op' => 'card_list', 'bank_id'=>$bank_id)), "sccuess");
} else if ($operation == 'card_add') {
    //添加广告
    load()->func('tpl');
    $bank_id = intval($_GPC['bank_id']);
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['bank_id'] = $bank_id;
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['card_type'] = implode(',', $data['card_type']);
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_bank_card', $data);
        message("提交成功", $this->createWebUrl('bank', array('op' => 'card_list', 'bank_id'=>$bank_id)), "sccuess");
    }
} else if ($operation == 'card_update') {
    //编辑广告
    load()->func('tpl');
    $bank_id = intval($_GPC['bank_id']);
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_bank_card"). " where id={$id}");
    $item['card_type'] = array_values(explode(',', $item['card_type']));
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $_GPC['data']['card_type'] = implode(',', $_GPC['data']['card_type']);
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_bank_card', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('bank', array('op' => 'card_list', 'bank_id'=>$bank_id)), "sccuess");
    }
}
include $this->template('bank');
?>