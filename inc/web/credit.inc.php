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
    if (!empty($_GPC['realname'])) {
        $wheres.= " AND realname LIKE '%{$_GPC['realname']}%'";
    }
    if (!empty($_GPC['phone'])) {
        $wheres.= " AND phone LIKE '%{$_GPC['phone']}%'";
    }
    $sql = 'select id,uid,realname,phone,createtime,status,pay_type from ' . tablename('xuan_mixloan_credit_data') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        $row['member'] = pdo_fetch("select nickname,avatar from ".tablename('xuan_mixloan_member').' WHERE id=:id', array(':id'=>$row['uid']));
    }
    unset($row);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_credit_data') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_credit_data_card', array('bank_id'=>$_GPC['id']));
    message("提交成功", $this->createWebUrl('bank', array('op' => '')), "sccuess");
} else if ($operation == 'update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_credit_data"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        foreach ($item['ext_info'] as $key => $value) {
            if (empty($_GPC['data']['ext_info'][$key])) {
                $_GPC['data']['ext_info'][$key] = $value;
            }
        }
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_credit_data', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('bank', array('op' => '')), "sccuess");
    }
}
include $this->template('credit');
?>