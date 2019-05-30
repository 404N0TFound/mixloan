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
    if (!empty($_GPC['name'])) {
        $wheres .= " and name like '%{$_GPC['name']}%'";
    }
    $sql = 'select id,name,createtime from ' . tablename('xuan_mixloan_smallloan') . "
        where 1 " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_smallloan') . "
        where 1 " . $wheres);
    $pager = pagination($total, $pindex, $psize);
}
else if ($operation == 'delete')
{
    pdo_delete('xuan_mixloan_smallloan', array("id" => $_GPC["id"]));
    message("提交成功", referer(), "success");
}
else if ($operation == 'add')
{
    //添加
    $category = pdo_fetchall('select id,name from ' . tablename('xuan_mixloan_category') . "
        where 1 AND type=4 ORDER BY ID DESC");
    $posters = pdo_fetchall("SELECT * FROM " . tablename('xuan_mixloan_poster_data') . "
        ORDER BY id DESC
        LIMIT 100");
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        // $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['ext_info']['content'] = htmlspecialchars_decode($data['ext_info']['content']);
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_smallloan', $data);
        message("提交成功", $this->createWebUrl('smallloan', array('op' => 'list')), "success");
    }
}
else if ($operation == 'update')
{
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_smallloan"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    $category = pdo_fetchall('select id,name from ' . tablename('xuan_mixloan_poster_data') . "
        where 1 AND type=4 ORDER BY ID DESC");
    $posters = pdo_fetchall("SELECT * FROM " . tablename('xuan_mixloan_poster') . "
        ORDER BY id DESC
        LIMIT 100");
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['ext_info']['content'] = htmlspecialchars_decode($data['ext_info']['content']);
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_update('xuan_mixloan_smallloan', $data, array('id'=>$item['id']));
        message("提交成功", referer(), "success");
    }
}
else if ($operation == 'category_list')
{
    // 分类
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = ' AND type=4';
    $sql = 'select * from ' . tablename('xuan_mixloan_category') . "
        where 1 " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_category') . "
        where 1 " . $wheres);
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
        $data['type'] = 4;
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_category', $data);
        message('添加成功', $this->createWebUrl('smallloan', array('op' => 'category_list')), 'success');
    }
}
else if ($operation == 'category_update')
{
    // 编辑分类
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_category"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_update('xuan_mixloan_category', $data, array('id'=>$item['id']));
        message("提交成功", referer(), "success");
    }
}
else if ($operation == 'category_delete')
{
    // 分类删除
    pdo_delete('xuan_mixloan_category', array("id" => $_GPC["id"]));
    message("提交成功", referer(), "success");
}
else if ($operation == 'feedback_list')
{
    // 反馈列表
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    $status = trim($_GPC['status']);
    if ($status != '') {
        $wheres .= " and status={$status}";
    }
    $sql = 'select * from ' . tablename('xuan_mixloan_smallloan_feedback') . "
        where 1 " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        $row['nickname'] = pdo_fetchcolumn('select nickname from ' . tablename('xuan_mixloan_member') . '
                                    where id=:id', array(':id' => $row['uid']));
    }
    unset($row);
    $total = pdo_fetchcolumn( 'select count(*) from ' . tablename('xuan_mixloan_smallloan_feedback') . "
                        where 1 " . $wheres . ' ORDER BY ID DESC');
    $pager = pagination($total, $pindex, $psize);
}
else if ($operation == 'feedback_deal')
{
    // 处理反馈
    pdo_update('xuan_mixloan_smallloan_feedback', array('status' => 1), array("id" => $_GPC["id"]));
    message("提交成功", referer(), "success");
}
else if ($operation == 'feedback_delete')
{
    // 反馈删除
    pdo_delete('xuan_mixloan_smallloan_feedback', array("id" => $_GPC["id"]));
    message("提交成功", referer(), "success");
} else if ($operation == 'advs_list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    $sql = 'select id,name,createtime from ' . tablename('xuan_mixloan_smallloan_advs') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_smallloan_advs') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'advs_delete') {
    pdo_delete('xuan_mixloan_smallloan_advs', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('smallloan', array('op' => 'advs_list')), "sccuess");
} else if ($operation == 'advs_add') {
    //添加
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_smallloan_advs', $data);
        message("提交成功", $this->createWebUrl('smallloan', array('op' => 'advs_list')), "sccuess");
    }
} else if ($operation == 'advs_update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_smallloan_advs"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_smallloan_advs', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('smallloan', array('op' => 'advs_list')), "sccuess");
    }
}
include $this->template('smallloan');
