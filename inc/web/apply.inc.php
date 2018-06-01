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
    if (!empty($_GPC['id'])) {
        $wheres.= " AND id='{$_GPC['id']}'";
    }
    if (!empty($_GPC['phone'])) {
        $wheres.= " AND phone LIKE '%{$_GPC['phone']}%'";
    }
    $sql = 'select * from ' . tablename('xuan_mixloan_apply') . "
        where uniacid={$_W['uniacid']} "  . $wheres . ' ORDER BY ID DESC';
    if ($_GPC['export'] != 1) {
        $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql);
        foreach ($list as &$row) {
            if ($row['type'] == 1) {
                $row['relate_name'] = pdo_fetchcolumn('select name from ' .tablename('xuan_mixloan_loan'). '
                    where id=:id', array(':id'=>$row['relate_id']));
            }
        }
        unset($row);
    } else {
        $list = pdo_fetchall($sql);
        m('excel')->export($list, array("title" => "会员数据-" . date('Y-m-d-H-i', time()), "columns" => array(array('title' => '昵称', 'field' => 'nickname', 'width' => 12), array('title' => '姓名', 'field' => 'realname', 'width' => 12), array('title' => '昵称', 'field' => 'nickname', 'width' => 12),)));
    }
    $total = pdo_fetchcolumn( 'select count(1) from ' . tablename('xuan_mixloan_apply') . "where uniacid={$_W['uniacid']} "  . $wheres . ' ORDER BY ID DESC' );
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_apply', array('id'=>$_GPC['id']));
    message("删除成功", $this->createWebUrl('apply'), 'success');
} 
include $this->template('apply');
?>