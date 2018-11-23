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
    $item = pdo_fetch('select * from ' . tablename('xuan_mixloan_mall') . "
        where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], 1);
    $ext_info = array();
    $ext_info['realname'] = $realname;
    $ext_info['address']  = $address;
    $ext_info['account']  = $account;
    $insert = array();
    $insert['uniacid'] = $_W['uniacid'];
    $insert['pid'] = $id;
    $insert['phone'] = $phone;
    $insert['money'] = $item['ext_info']['money'];
    $insert['ext_info'] = json_encode($ext_info);
    $insert['status'] = 0;
    $insert['createtime'] = time();
    $insert['uid'] = $member['id'];
    pdo_insert('xuan_mixloan_mall_order', $insert);
    $insert_id = pdo_insertid();
    show_json(1, ['url'=>$this->createMobileUrl('mall', array('op'=>'pay', 'id'=>$insert_id))], '订单已提交，请支付');
}
else if ($operation == 'pay')
{
    // 支付
    $id = intval($_GPC['id']);
    $order = pdo_fetch('select money from ' . tablename('xuan_mixloan_mall_order') . '
        where id=:id', array(':id' => $id));
    $tid = "30001" . date('YmdHis', time());
    pdo_update('xuan_mixloan_mall_order', array('tid'=>$tid), array('id'=>$id));
    $title = "购买商品";
    $fee = $order['money'];
    $params = array(
        'tid' => $tid,
        'ordersn' => $tid,
        'title' => $title,
        'fee' => $fee,
        'user' => $member['id'],
        'module' => 'xuan_mixloan'
    );
    $insert = array(
        'openid' => $openid,
        'uniacid' => $_W['uniacid'],
        'acid' => $_W['uniacid'],
        'tid' => $tid,
        'fee' => $fee,
        'status' => 0,
        'module' => 'xuan_mixloan',
        'card_fee' => $fee,
    );
    pdo_insert('core_paylog', $insert);
    $url = url('mc/cash/alipay') . "&params=" . base64_encode(json_encode($params));
    include $this->template('vip/openHref');
}
else if ($operation == 'order_record')
{
    // 订单列表
    if (empty($member['id']))
    {
        show_json(-1, ['url' => $this->createMobileUrl('index', array('op'=>'login'))], '请先登陆');
    }
    $list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_mall_order') . '
        where uid=:uid', array(':uid' => $member['id']));
    foreach ($list as &$row)
    {
        $item = pdo_fetch('select * from ' . tablename('xuan_mixloan_mall') . '
                where id=:id', array(':id' => $row['pid']));
        $item['ext_info'] = json_decode($item['ext_info'], 1);
        $row['title'] = $item['title'];
        $row['logo']  = tomedia($item['ext_info']['logo']);
        $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
    }
    unset($row);
    show_json(1, array_values($list), 'success');
}