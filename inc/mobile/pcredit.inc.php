<?php
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='index';
if($operation=='index'){
    //首页
    $list = array();
    $pro_list = pdo_fetchall('select id,name from ' . tablename('xuan_mixloan_pcredit'));
    foreach ($pro_list as $value) {
        $ret = array();
        $ret['label'] = $value['name'];
        $ret['value'] = $value['id'];
        $list[] = $ret;
    }
    $list = json_encode($list);
    include $this->template('pcredit/default');
} else if ($operation == 'result') {
    $id = intval($_GPC['id']);
    $name = trim($_GPC['name']);
    if (!empty($id)) {
        $wheres .= " and id = {$id}";
    }
    if (!empty($name)) {
        $wheres .= " and name like '%{$name}%'";
    }
    $sql = "select * from " .tablename('xuan_mixloan_pcredit') . '
        where 1' . $wheres;
    $item = pdo_fetch($sql);
    if ($item) {
        $item['ext_info'] = json_decode($item['ext_info'], 1);
    }
    include $this->template('pcredit/result');
}