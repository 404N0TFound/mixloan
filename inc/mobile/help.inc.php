<?php
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='index';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$agent = m('member')->checkAgent($member['id']);
if($operation=='index'){
    //帮助中心
    include $this->template('help/index');
} else if ($operation == 'category') {
    // 分类
    $list = pdo_fetchall('select id,name,ext_info from ' . tablename('xuan_mixloan_help_category') . '
		where uniacid=:uniacid order by sort desc', array(':uniacid' => $_W['uniacid']));
    if (!empty($list))
    {
        foreach ($list as &$row)
        {
            $row['ext_info'] = json_decode($row['ext_info'], 1);
            $row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
        }
        unset($row);
        show_json(1, array_values($list), '获取成功');
    }
    else
    {
        show_json(-1, [], '没有更多啦');
    }
} else if ($operation == 'questions') {
    $keyword     = trim($_GPC['keyword']);
    $category_id = intval($_GPC['cateId']);
    $condition   = array();
    $wheres = '';
    if (!empty($keyword))
    {
        $wheres .= " and title like '%{$keyword}%'";
    }
    if (!empty($category_id))
    {
        $wheres .= " and category_id = '{$category_id}'";
    }
    $list = pdo_fetchall('select id,title,ext_info from ' . tablename('xuan_mixloan_help') . "
		where uniacid=:uniacid {$wheres} order by id desc", array(':uniacid' => $_W['uniacid']));
    if (!empty($list))
    {
        foreach ($list as &$row)
        {
            $row['ext_info'] = json_decode($row['ext_info'], 1);
        }
        unset($row);
        show_json(1, array_values($list), '获取成功');
    }
    else
    {
        show_json(-1, [], '没有更多啦');
    }
}