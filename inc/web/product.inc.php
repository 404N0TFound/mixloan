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
    $sql = 'select id,name,createtime from ' . tablename('xuan_mixloan_product') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_product') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_product', array("id" => $_GPC["id"]));
    pdo_delete('xuan_mixloan_product_apply', array("pid" => $_GPC["id"]));
    message("删除功能已取消", $this->createWebUrl('product', array('op' => '')), "sccuess");
} else if ($operation == 'add') {
    //添加
    $cates = pdo_fetchall('select id,name from ' . tablename('xuan_mixloan_product_category') . " where uniacid={$_W['uniacid']} ORDER BY sort DESC");
    if (empty($cates)) {
        message('请先添加小分类', $this->createWebUrl('product', array('op' => 'category_add')));
    }
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        if (empty($data['relate_id'])) {
            message("请关联产品", '', "error");
        }
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_product', $data);
        message("提交成功", $this->createWebUrl('product', array('op' => '')), "sccuess");
    }
} else if ($operation == 'update') {
    //编辑
    $id = intval($_GPC['id']);
    $cates = pdo_fetchall('select id,name from ' . tablename('xuan_mixloan_product_category') . " where uniacid={$_W['uniacid']} ORDER BY sort DESC");
    if (empty($cates)) {
        message('请先添加小分类', $this->createWebUrl('product', array('op' => 'category_add')));
    }
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_product"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($item['type'] == 1) {
        $temp = m('bank')->getCard(['id', 'name'], ['id' => $item['relate_id']])[$item['relate_id']];
        $relate_name = $temp['name'];
    } else {
        $temp = m('loan')->getList(['id', 'name'], ['id' => $item['relate_id']])[$item['relate_id']];
        $relate_name = $temp['name'];
    }
    if ($_GPC['post'] == 1) {
        if (empty($_GPC['data']['relate_id'])) {
            message("请关联产品", '', "error");
        }
        pdo_delete('xuan_mixloan_poster', array('pid'=>$item['id']));
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_product', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('product', array('op' => '')), "sccuess");
    }
} else if ($operation == 'advs_list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    $sql = 'select id,name,createtime from ' . tablename('xuan_mixloan_product_advs') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_product_advs') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'advs_delete') {
    pdo_delete('xuan_mixloan_product_advs', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('product', array('op' => 'advs_list')), "sccuess");
} else if ($operation == 'advs_add') {
    //添加
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_product_advs', $data);
        message("提交成功", $this->createWebUrl('product', array('op' => 'advs_list')), "sccuess");
    }
} else if ($operation == 'advs_update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_product_advs"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_product_advs', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('product', array('op' => 'advs_list')), "sccuess");
    }
} else if ($operation == 'getRelate') {
    //获取关联产品
    $name = trim($_GPC['name']);
    $type = intval($_GPC['type']);
    if ($type == 1) {
        $items = m('bank')->getCard(['id', 'name'], ['name' => $name]);
    } else if ($type == 2) {
        $items = m('loan')->getList(['id', 'name'], ['name' => $name]);
    }
    if ($items) {
        show_json(1, ['items' => array_values($items)]);
    } else {
        show_json(-1);
    }
} else if ($operation == 'category') {
    //产品分类
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    $sql = 'select id,name,createtime from ' . tablename('xuan_mixloan_product_category') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_product_category') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'category_add') {
    //产品分类添加
    if ($_GPC['post'] == 1) {
        $insert = $_GPC['data'];
        $insert['createtime'] = time();
        $insert['uniacid'] = $_W['uniacid'];
        $insert['ext_info'] = json_encode($insert['ext_info']);
        pdo_insert('xuan_mixloan_product_category', $insert);
        message("提交成功", $this->createWebUrl('product', array('op' => 'category')), "sccuess");
    }
} else if ($operation == 'category_update') {
    //产品分类更新
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from ' . tablename('xuan_mixloan_product_category') . "
        where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $update = $_GPC['data'];
        $update['ext_info'] = json_encode($update['ext_info']);
        pdo_update('xuan_mixloan_product_category', $update, array('id' => $id));
        message("提交成功", $this->createWebUrl('product', array('op' => 'category')), "sccuess");
    }
}
include $this->template('product');
?>