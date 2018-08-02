<?php
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$config = $this->module['config'];
if (empty($_GPC['op'])) {
    $operation = 'list';
} else {
    $operation = $_GPC['op'];
}
if ($operation == 'list')
{
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    if (!empty($_GPC['title'])) {
        $wheres .= " and title like '%{$_GPC['title']}%'";
    }
    $sql = 'select id,title,createtime from ' . tablename('xuan_mixloan_help') . "
        where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_help') . "
        where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
}
else if ($operation == 'delete')
{
    pdo_delete('xuan_mixloan_help', array("id" => $_GPC["id"]));
    message("提交成功", referer(), "success");
}
else if ($operation == 'add')
{
    //添加
    $category = pdo_fetchall('select id,name from ' . tablename('xuan_mixloan_help_category') . "
        where uniacid={$_W['uniacid']} ORDER BY ID DESC");
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['ext_info']['content'] = htmlspecialchars_decode($data['ext_info']['content']);
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_help', $data);
        message("提交成功", $this->createWebUrl('help', array('op' => 'list')), "success");
    }
}
else if ($operation == 'update')
{
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_help"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    $category = pdo_fetchall('select id,name from ' . tablename('xuan_mixloan_help_category') . "
        where uniacid={$_W['uniacid']} ORDER BY ID DESC");
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['ext_info']['content'] = htmlspecialchars_decode($data['ext_info']['content']);
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_update('xuan_mixloan_help', $data, array('id'=>$item['id']));
        message("提交成功", referer(), "success");
    }
}
else if ($operation == 'category_list')
{
    // 分类
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = ' AND type=1';
    $sql = 'select * from ' . tablename('xuan_mixloan_help_category') . "
        where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_help_category') . "
        where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
}
else if ($operation == 'category_add')
{
    // 添加分类
    if ($_GPC['post'])
    {
        $data = $_GPC['data'];
        $data['createtime'] = time();
        $data['uniacid'] = $_W['uniacid'];
        $data['type'] = 1;
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_help_category', $data);
        message('添加成功', $this->createWebUrl('help', array('op' => 'category_list')), 'success');
    }
}
else if ($operation == 'category_update')
{
    // 编辑分类
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_help_category"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_update('xuan_mixloan_help_category', $data, array('id'=>$item['id']));
        message("提交成功", referer(), "success");
    }
}
else if ($operation == 'category_delete')
{
    // 分类删除
    pdo_delete('xuan_mixloan_help_category', array("id" => $_GPC["id"]));
    message("提交成功", referer(), "success");
}
include $this->template('help');
?>