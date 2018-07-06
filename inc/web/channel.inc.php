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
    if (!empty($_GPC['title'])) {
        $wheres.= " AND title LIKE '%{$_GPC['title']}%'";
    }
    if (!empty($_GPC['type'])) {
        $wheres.= " AND type={$_GPC['type']}";
    }
    $sql = 'select id,title,type,createtime from ' . tablename('xuan_mixloan_channel') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_channel') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_channel', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('channel', array('op' => '')), "sccuess");
} else if ($operation == 'add') {
    //添加
    $subjects_c = m('channel')->getSubjectList(['id', 'name', 'type'], ['type'=>1]);//口子
    $c_json = json_encode(array_values($subjects_c));
    $subjects_s = m('channel')->getSubjectList(['id', 'name', 'type'], ['type'=>2]);//信用卡
    $s_json = json_encode(array_values($subjects_s));
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        if ($data['type'] == 1 || $data['type'] == 2) {
            if (!$data['subject_id']) {
                message('请添加专题', '', 'error');
            }
        }
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['ext_info']['content'] = htmlspecialchars_decode($data['ext_info']['content']);
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_channel', $data);
        message("提交成功", $this->createWebUrl('channel', array('op' => '')), "sccuess");
    }
} else if ($operation == 'update') {
    //编辑
    $id = intval($_GPC['id']);
    $subjects_c = m('channel')->getSubjectList(['id', 'name', 'type'], ['type'=>1]);//口子
    $c_json = json_encode(array_values($subjects_c));
    $subjects_s = m('channel')->getSubjectList(['id', 'name', 'type'], ['type'=>2]);//信用卡
    $s_json = json_encode(array_values($subjects_s));
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_channel"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        if ($_GPC['data']['type'] == 1 || $_GPC['data']['type'] == 2) {
            if (!$_GPC['data']['subject_id']) {
                message('请添加专题', '', 'error');
            }
        }
        $_GPC['data']['ext_info']['content'] = htmlspecialchars_decode($_GPC['data']['ext_info']['content']);
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_channel', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('channel', array('op' => '')), "sccuess");
    }
} else if ($operation == 'advs_list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    $sql = 'select id,name,createtime from ' . tablename('xuan_mixloan_channel_advs') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_channel_advs') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'advs_delete') {
    pdo_delete('xuan_mixloan_channel_advs', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('channel', array('op' => 'advs_list')), "sccuess");
} else if ($operation == 'advs_add') {
    //添加
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_channel_advs', $data);
        message("提交成功", $this->createWebUrl('channel', array('op' => 'advs_list')), "sccuess");
    }
} else if ($operation == 'advs_update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_channel_advs"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_channel_advs', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('channel', array('op' => 'advs_list')), "sccuess");
    }
} else if ($operation == 'subject_list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    $sql = 'select id,name,type,createtime from ' . tablename('xuan_mixloan_channel_subject') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_channel_subject') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'subject_delete') {
    pdo_delete('xuan_mixloan_channel_subject', array("id" => $_GPC["id"]));
    pdo_delete('xuan_mixloan_channel', array("subject_id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('channel', array('op' => 'subject_list')), "sccuess");
} else if ($operation == 'subject_add') {
    //添加
    if ($_GPC['post'] == 1) {
        $data = $_GPC['data'];
        $data['uniacid'] = $_W['uniacid'];
        $data['createtime'] = time();
        $data['ext_info'] = json_encode($data['ext_info']);
        pdo_insert('xuan_mixloan_channel_subject', $data);
        message("提交成功", $this->createWebUrl('channel', array('op' => 'subject_list')), "sccuess");
    }
} else if ($operation == 'subject_update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_channel_subject"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    if ($_GPC['post'] == 1) {
        $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_channel_subject', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('channel', array('op' => 'subject_list')), "sccuess");
    }
}
include $this->template('channel');
?>