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
    $sql = 'select id,name,createtime from ' . tablename('xuan_mixloan_poster_data') . " where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select COUNT(1) from ' . tablename('xuan_mixloan_poster_data') . " where uniacid={$_W['uniacid']} " . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_poster_data', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('poster', array('op' => '')), "sccuess");
} else if ($operation == 'add') {
    //添加
    if ($_GPC['post'] == 1) {
        $temp = htmlspecialchars_decode($_GPC['data']);
        $temp = json_decode($temp, 1);
        foreach ($temp as $v) {
            if ($v['type'] == 'nickname') {
                $v['color'] = $_GPC['color'];
            }
            $poster[$v['type']] = $v;
        }
        $insert['ext_info']['poster'] = $poster;
        $insert['ext_info']['back'] = $_GPC['bg'];
        $insert['name'] = $_GPC['name'];
        $insert['prefix_text'] = $_GPC['prefix_text'];
        $insert['uniacid'] = $_W['uniacid'];
        $insert['createtime'] = time();
        $insert['ext_info'] = json_encode($insert['ext_info']);
        pdo_insert('xuan_mixloan_poster_data', $insert);
        message("提交成功", $this->createWebUrl('poster', array('op' => '')), "sccuess");
    }
} else if ($operation == 'update') {
    //编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_poster_data"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    $item['bg'] = $item['ext_info']['back'];
    foreach ($item['ext_info']['poster'] as $value) {
        $data[] = $value;
    }
    if ($_GPC['post'] == 1) {
        $temp = htmlspecialchars_decode($_GPC['data']);
        $temp = json_decode($temp, 1);
        foreach ($temp as $v) {
            if ($v['type'] == 'nickname') {
                $v['color'] = $_GPC['color'];
            }
            $poster[$v['type']] = $v;
        }
        $update['ext_info']['poster'] = $poster;
        $update['ext_info']['back'] = $_GPC['bg'];
        $update['name'] = $_GPC['name'];
        $update['prefix_text'] = $_GPC['prefix_text'];
        $update['ext_info'] = json_encode($update['ext_info']);
        pdo_update('xuan_mixloan_poster_data', $update, array('id'=>$id));
        pdo_delete('xuan_mixloan_poster', array('pid'=>0));
        message("提交成功", $this->createWebUrl('poster', array('op' => '')), "sccuess");
    }
}
else if ($operation == 'clear_poster')
{
    // 清理海报缓存
    pdo_delete('xuan_mixloan_poster');
    pdo_delete('xuan_mixloan_shorturl');
    message("清除成功", referer(), "success");
} else if ($operation == 'delete_all') {
    // 批量操作
    $values = rtrim($_GPC['values'], ',');
    $values = explode(',', $values);
    foreach ($values as $id) {
        //支付宝收款接口
        pdo_delete('xuan_mixloan_poster_data', array('id' => $id));
    }
    show_json(1, [], '操作成功');
}

include $this->template('poster');
?>