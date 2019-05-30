<?php
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config    = $this->module['config'];
$operation = $_GPC['op'] ? : 'default';
$openid    = m('user')->getOpenid();
$member    = m('member')->getMember($openid);
if ($operation == 'default')
{
    include $this->template('smallloan/index');
}
else if ($operation == 'feedback_list')
{
    include $this->template('smallloan/feedback_list');
}
else if ($operation == 'list')
{
    include $this->template('smallloan/list');
}
else if ($operation == 'index')
{
    // 首页
    $keyword = trim($_GPC['keyword']);
    $cond = array();
    if ($keyword)
    {
        $wheres .= " and name like :name";
        $cond[':name'] = "%{$keyword}%";
    }
    $list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_category') . "
					where type=4 {$wheres}
					order by sort desc", $cond);
    if (empty($list))
    {
        $cate_list = pdo_fetchall('select category_id from ' . tablename('xuan_mixloan_smallloan') . "
						where 1 {$wheres}", $cond);
        foreach ($cate_list as $value) {
            $cat_arr[] = $value['category_id'];
        }
        $cat_string = implode(',', $cat_arr);
        $list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_category') . "
						where 1 and id in ({$cat_string})
						order by sort desc");

    }
    if (!empty($list))
    {
        foreach ($list as &$row)
        {
            $row['ext_info'] = json_decode($row['ext_info'], 1);
            $row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
        }
        unset($row);
    }
    show_json(1, ['list' => $list], '获取成功');
}
else if($operation == 'get_list')
{
    // 列表
    $type    = intval($_GPC['type']);
    $keyword = trim($_GPC['keyword']);
    $cond    = array();
    $ret     = array();
    if ($type)
    {
        $category = pdo_fetch('select name from ' . tablename('xuan_mixloan_category') . "
						where id=:id",  array(':id' => $type));
        $wheres .= " and category_id=:category_id";
        $cond[':category_id'] = $type;
        $list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_smallloan') . "
						where 1 {$wheres}
						order by id desc", $cond);
        $ret['title'] = $category['name'];
    }
    if ($keyword)
    {
        $wheres .= " and name like :name";
        $cond[':name'] = "%{$keyword}%";
        $cate_list = pdo_fetchall('select id from ' . tablename('xuan_mixloan_category') . "
						where type=4 {$wheres}", $cond);
        foreach ($cate_list as $value) {
            $cat_arr[] = $value['id'];
        }
        if (!empty($cat_arr)) {
            $cat_string = implode(',', $cat_arr);
            $list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_smallloan') . "
							where 1 and category_id in ({$cat_string})
							order by id desc");
        } else {
            $list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_smallloan') . "
							where 1 {$wheres}
							order by id desc", $cond);
        }
        $ret['title'] = $keyword;
    }
    if (!empty($list))
    {
        foreach ($list as &$row)
        {
            $row['ext_info'] = json_decode($row['ext_info'], 1);
            $row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
        }
        unset($row);
    }
    $ret['list'] = $list;
    show_json(1, $ret, '获取成功');
}
else if ($operation == 'feedback_submit')
{
    // 反馈提交
    if (empty($member['id']))
    {
        show_json(-1, ['url' => $this->createMobileUrl('index', array('op' => 'login'))], '请先登陆');
    }
    if (empty($member['agent']))
    {
        show_json(-1, ['url' => $this->createMobileUrl('index', array('op' => 'login'))], '您还不是代理哦');
    }
    $new_smallloan = trim($_GPC['new_smallloan']);
    $old_smallloan = trim($_GPC['old_smallloan']);
    $old_category = trim($_GPC['old_category']);
    $insert = array();
    $insert['new_smallloan'] = $new_smallloan;
    $insert['old_smallloan'] = $old_smallloan;
    $insert['old_category'] = $old_category;
    $insert['uid'] = $member['id'];
    $insert['createtime'] = time();
    pdo_insert('xuan_mixloan_smallloan_feedback', $insert);
    show_json(1, ['url' => $this->createMobileUrl('smallloan', array('op' => 'default'))], '提交成功');
}
else if ($operation == 'get_advs')
{
    // 获取广告
    $advs = pdo_fetchall('select * from ' . tablename('xuan_mixloan_smallloan_advs') . "
					where uniacid={$_W['uniacid']}");
    foreach ($advs as &$row) {
        $row['ext_info'] = json_decode($row['ext_info'], 1);
        $row['image'] = tomedia($row['ext_info']['pic']);
        $row['title'] = $row['name'];
        $row['url']   = $row['ext_info']['url'];
    }
    unset($row);
    show_json(1, $advs, '获取成功');
}