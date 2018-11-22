<?php
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config    = $this->module['config'];
$operation = $_GPC['op'] ? : 'default';
$openid    = m('user')->getOpenid();
$member    = m('member')->getMember($openid);

if ($operation == 'category_list')
{
    //分类列表
    $sql = 'select * from ' . tablename('xuan_mixloan_mall_category') . "
        where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY sort DESC';
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        $row['ext_info'] = json_decode($row['ext_info'], 1);
        $row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
    }
    unset($row);
    show_json(1, array_values($list), '获取成功');
}
else if ($operation == 'adv_list')
{
    // 广告
    $sql = 'select * from ' . tablename('xuan_mixloan_mall_adv') . "
        where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY sort DESC';
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        $row['ext_info'] = json_decode($row['ext_info'], 1);
        $row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
    }
    unset($row);
    show_json(1, array_values($list), '获取成功');
}
else if ($operation == 'default')
{
    include $this->template('mall/index');
}
else if ($operation == 'order')
{
    $id = intval($_GPC['id']);
    include $this->template('mall/order');
}
else if ($operation == 'info')
{
    $id = intval($_GPC['id']);
    include $this->template('mall/info');
}
else if ($operation == 'person')
{
    include $this->template('mall/person');
}
else if ($operation == 'list')
{
    // 列表
    $wheres = '';
    $condition = array();
    $category_id = intval($_GPC['category_id']);
    if (!empty($category_id))
    {
        $wheres .= " AND category_id={$category_id}";
    }
    $sql = 'select * from ' . tablename('xuan_mixloan_mall') . "
        where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY sort DESC';
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        $row['ext_info'] = json_decode($row['ext_info'], 1);
        $row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
        if (!empty($row['ext_info']['pictures']))
        {
            foreach ($row['ext_info']['pictures'] as &$pic)
            {
                $pic = tomedia($pic);
            }
            unset($pic);
        }
    }
    unset($row);
    show_json(1, array_values($list), '获取成功');
}
else if ($operation == 'get_recommend')
{
    // 列表
    $wheres = '';
    $condition = array();
    $is_recommend = 1;
    $wheres .= " AND is_recommend={$is_recommend}";
    $sql = 'select * from ' . tablename('xuan_mixloan_mall') . "
        where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY sort DESC';
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        $row['ext_info'] = json_decode($row['ext_info'], 1);
        $row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
        if (!empty($row['ext_info']['pictures']))
        {
            foreach ($row['ext_info']['pictures'] as &$pic)
            {
                $pic = tomedia($pic);
            }
            unset($pic);
        }
    }
    unset($row);
    show_json(1, array_values($list), '获取成功');
}
else if ($operation == 'get_info')
{
    // 详情
    if (empty($member['id']))
    {
        show_json(-1, ['url' => $this->createMobileUrl('index', array('op'=>'login'))], '请先登陆');
    }
    $id = intval($_GPC['id']);
    if (empty($id))
    {
        show_json(-1, [], 'error');
    }
    $item = pdo_fetch('select * from ' . tablename('xuan_mixloan_mall') . "
        where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], 1);
    $item['nums'] = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_mall_order') . '
		where pid=:pid', array(':pid' => $id)) ? : 0;
    $item['category'] = pdo_fetchcolumn('select name from ' . tablename('xuan_mixloan_mall_category') . '
		where id=:id', array(':id' => $item['category_id'])) ? : 0;
    $item['stock'] = $item['ext_info']['stock'] - $item['nums'];
    $item['credits'] = $member['credits'];
    if (!empty($item['ext_info']['pictures']))
    {
        foreach ($item['ext_info']['pictures'] as &$pic)
        {
            $pic = tomedia($pic);
        }
        unset($pic);
    }
    show_json(1, $item, 'success');
}
else if ($operation == 'recommend_list')
{
    // 推荐列表
    $wheres = '';
    $condition = array();
    $sql = 'select * from ' . tablename('xuan_mixloan_mall') . "
        where uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY rand() DESC';
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        $row['ext_info'] = json_decode($row['ext_info'], 1);
        $row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
        if (!empty($row['ext_info']['pictures']))
        {
            foreach ($row['ext_info']['pictures'] as &$pic)
            {
                $pic = tomedia($pic);
            }
            unset($pic);
        }
    }
    unset($row);
    show_json(1, array_values($list), '获取成功');
}
else if ($operation == 'order_submit')
{
    // 提交
    $id       = intval($_GPC['id']);
    $phone    = trim($_GPC['phone']);
    $realname = trim($_GPC['realname']);
    $address  = trim($_GPC['address']);
    $account  = trim($_GPC['account']);
    if (empty($member['id']))
    {
        show_json(-1, ['url' => $this->createMobileUrl('index', array('op'=>'login'))], '请先登陆');
    }
    if (empty($id))
    {
        show_json(-1, [], '出错了');
    }
    if (empty($phone))
    {
        show_json(-1, [], '请填写手机');
    }
    if (empty($realname))
    {
        show_json(-1, [], '请填写姓名');
    }
    if (empty($address))
    {
        show_json(-1, [], '请填写地址');
    }
    $item = m('credit')->getList(['id', 'ext_info'], ['id' => $id])[$id];
    if ($member['credits'] < $item['ext_info']['credits'])
    {
        show_json(-1, [], '您的积分不足');
    }
    $left = $member['credits'] - $item['ext_info']['credits'];
    pdo_update('xuan_mixloan_member', array('credits'=>$left), array('id'=>$member['id']));
    $ext_info = array();
    $ext_info['realname'] = $realname;
    $ext_info['address']  = $address;
    $ext_info['account']  = $account;
    $ext_info['credits']  = $item['ext_info']['credits'];
    $insert = array();
    $insert['uniacid'] = $_W['uniacid'];
    $insert['pid'] = $id;
    $insert['phone'] = $phone;
    $insert['ext_info'] = json_encode($ext_info);
    $insert['status'] = 0;
    $insert['createtime'] = time();
    $insert['uid'] = $member['id'];
    pdo_insert('xuan_mixloan_credit_order', $insert);
    show_json(1, ['url'=>'../credit/index.html'], 'success');
}
else if ($operation == 'order_record')
{
    // 订单列表
    if (empty($member['id']))
    {
        show_json(-1, ['url' => $this->createMobileUrl('index', array('op'=>'login'))], '请先登陆');
    }
    $list = m('credit')->getOrderList([], ['uid' => $member['id']]);
    foreach ($list as &$row)
    {
        $item = m('credit')->getList(['id', 'title', 'ext_info'], ['id' => $row['pid']])[$row['pid']];
        $row['title'] = $item['title'];
        $row['logo']  = $item['ext_info']['logo'];
        $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
    }
    unset($row);
    show_json(1, array_values($list), 'success');
}