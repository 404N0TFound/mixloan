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
        $wheres.= " AND a.title LIKE '%{$_GPC['name']}%'";
    }
    if (!empty($_GPC['nickname'])) {
        $wheres.= " AND b.nickname LIKE '%{$_GPC['nickname']}%'";
    }
    if ($_GPC['top'] != "") {
        $wheres.= " AND a.top = {$_GPC['top']}";
    }
    $sql = 'select a.id,a.title,a.ctime,a.top,b.avatar,b.nickname from ' . tablename('xuan_mixloan_friend') . " a left join ".tablename("xuan_mixloan_member")." b on a.openid=b.openid where a.uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select count(1) from ' . tablename('xuan_mixloan_friend') . " a left join ".tablename("xuan_mixloan_member")." b on a.openid=b.openid where a.uniacid={$_W['uniacid']} " . $wheres );
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_friend', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('friend', array('op' => '')), "sccuess");
}else if ($operation == 'update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_friend"). " where id={$id}");
    if ($_GPC['post'] == 1) {
        pdo_update('xuan_mixloan_friend', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('friend', array('op' => '')), "sccuess");
    }
}
include $this->template('friend');
?>