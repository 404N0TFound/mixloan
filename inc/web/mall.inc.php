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
    $sql = 'select id,title,createtime from ' . tablename('xuan_mixloan_mall') . "
        where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_mall') . "
        where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
}
else if ($operation == 'delete')
{
    pdo_delete('xuan_mixloan_mall', array("id" => $_GPC["id"]));
    message("提交成功", referer(), "success");
}
else if ($operation == 'add')
{
    //添加
    $category = pdo_fetchall('select id,name from ' . tablename('xuan_mixloan_mall_category') . "
        where uniacid={$_W['uniacid']} ORDER BY ID DESC");
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['ext_info']['content'] = htmlspecialchars_decode($data['ext_info']['content']);
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_mall', $data);
        message("提交成功", $this->createWebUrl('mall', array('op' => 'list')), "success");
    }
}
else if ($operation == 'update')
{
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_mall"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    $category = pdo_fetchall('select id,name from ' . tablename('xuan_mixloan_mall_category') . "
        where uniacid={$_W['uniacid']} ORDER BY ID DESC");
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['ext_info']['content'] = htmlspecialchars_decode($data['ext_info']['content']);
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_update('xuan_mixloan_mall', $data, array('id'=>$item['id']));
        message("提交成功", referer(), "success");
    }
}
else if ($operation == 'category_list')
{
    // 分类
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    $sql = 'select * from ' . tablename('xuan_mixloan_mall_category') . "
        where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_mall_category') . "
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
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_mall_category', $data);
        message('添加成功', $this->createWebUrl('mall', array('op' => 'category_list')), 'success');
    }
}
else if ($operation == 'category_update')
{
    // 编辑分类
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_mall_category"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_update('xuan_mixloan_mall_category', $data, array('id'=>$item['id']));
        message("提交成功", referer(), "success");
    }
}
else if ($operation == 'category_delete')
{
    // 分类删除
    pdo_delete('xuan_mixloan_category', array("id" => $_GPC["id"]));
    message("提交成功", referer(), "success");
}
else if ($operation == 'adv_list')
{
    // 分类
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    $sql = 'select * from ' . tablename('xuan_mixloan_mall_adv') . "
        where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_mall_adv') . "
        where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
}
else if ($operation == 'adv_add')
{
    // 添加分类
    if ($_GPC['post'])
    {
        $data = $_GPC['data'];
        $data['createtime'] = time();
        $data['uniacid'] = $_W['uniacid'];
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_mall_adv', $data);
        message('添加成功', $this->createWebUrl('mall', array('op' => 'adv_list')), 'success');
    }
}
else if ($operation == 'adv_update')
{
    // 编辑分类
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_mall_adv"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_update('xuan_mixloan_mall_adv', $data, array('id'=>$item['id']));
        message("提交成功", referer(), "success");
    }
}
else if ($operation == 'adv_delete')
{
    // 分类删除
    pdo_delete('xuan_mixloan_mall_adv', array("id" => $_GPC["id"]));
    message("提交成功", referer(), "success");
}
else if ($operation == 'order_list')
{
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    if (!empty($_GPC['pid'])) {
        $wheres .= " and pid = '{$_GPC['pid']}'";
    }
    if (!empty($_GPC['phone'])) {
        $wheres .= " and phone like '%{$_GPC['phone']}%'";
    }
    $sql = 'select * from ' . tablename('xuan_mixloan_mall_order') . "
        where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    foreach ($list as &$row)
    {
        $row['man'] = m('member')->getList(['id', 'nickname', 'avatar'], ['id' => $row['uid']])[$row['uid']];
        $row['pro'] = m('credit')->getList(['id', 'title', 'ext_info'], ['id' => $row['pid']])[$row['pid']];
    }
    unset($row);
    $total = pdo_fetchcolumn( 'select count(*) from ' . tablename('xuan_mixloan_mall_order') . "
        where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC');
    $pager = pagination($total, $pindex, $psize);
}
else if ($operation == 'order_update')
{
    // 编辑分类
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_mall_order"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_update('xuan_mixloan_mall_order', $data, array('id'=>$item['id']));
        message("提交成功", referer(), "success");
    }
}
include $this->template('mall');
?>