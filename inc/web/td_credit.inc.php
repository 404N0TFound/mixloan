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
    $wheres = ' AND status<>-1';
    if (!empty($_GPC['id'])) {
        $wheres.= " AND id='{$_GPC['id']}'";
    }
    if (!empty($_GPC['realname'])) {
        $wheres.= " AND realname LIKE '%{$_GPC['realname']}%'";
    }
    if (!empty($_GPC['phone'])) {
        $wheres.= " AND phone LIKE '%{$_GPC['phone']}%'";
    }
    if (!empty($_GPC['certno'])) {
        $wheres.= " AND certno '%{$_GPC['certno']}%'";
    }
    $sql = 'select * from ' . tablename('xuan_mixloan_td_credit') . "where uniacid={$_W['uniacid']} "  . $wheres . ' ORDER BY ID DESC';
    if ($_GPC['export'] != 1) {
        $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql);
        foreach ($list as &$row) {
            $man = pdo_fetch('select nickname,avatar from ' . tablename('xuan_mixloan_member') . '
                where id=:id', array(':id' => $row['uid']));
            $row['avatar'] = $man['avatar'];
            $row['nickname'] = $man['nickname'];
        }
        unset($row);
    } else {
        $list = pdo_fetchall($sql);
        m('excel')->export($list, array(
            "title" => "查询资料",
            "columns" => array(
                array(
                    'title' => 'id',
                    'field' => 'id',
                    'width' => 10
                ),
                array(
                    'title' => '真实姓名',
                    'field' => 'realname',
                    'width' => 20
                ),
                array(
                    'title' => '身份证',
                    'field' => 'certno',
                    'width' => 30
                ),
                array(
                    'title' => '手机号',
                    'field' => 'phone',
                    'width' => 20
                ),
                array(
                    'title' => '邮箱',
                    'field' => 'email',
                    'width' => 12
                ),
            )
        ));
    }
    $total = pdo_fetchcolumn( 'select count(1) from ' . tablename('xuan_mixloan_td_credit') . "where uniacid={$_W['uniacid']} "  . $wheres . ' ORDER BY ID DESC' );
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_td_credit', array('id'=>$_GPC['id']));
    message("删除成功");
} else if ($operation == 'update') {
    $id = $_GPC['id'];
    $item = pdo_fetch("select * from ".tablename("xuan_mixloan_td_credit")." where id={$id}");
    if ($_GPC['post'] == 1) {
        pdo_update("xuan_mixloan_td_credit", $_GPC['data'], array("id"=>$id));
        message('更新成功', $this->createWebUrl('td_credit'), 'success');
    }
}
include $this->template('td_credit');
?>