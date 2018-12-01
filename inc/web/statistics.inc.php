<?php
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$config = $this->module['config'];
if (empty($_GPC['op'])) {
    $operation = 'list';
} else {
    $operation = $_GPC['op'];
}
if ($operation == 'list')
{
    if ($_COOKIE['verify']) {
        if (!empty($_GPC['time'])) {
            $starttime = $_GPC['time']['start'];
            $endtime = $_GPC['time']['end'];
        } else {
            $starttime = date('Y-m-d');
            $endtime =  date('Y-m-d H:i:s');
        }
        $start = strtotime($starttime);
        $end = strtotime($endtime);
        $wheres = '';
        $wheres .= " and createtime > {$start} and createtime<= {$end}";
        // 购买代理数
        $sql = "select count(*) from " . tablename('xuan_mixloan_payment') .  "
            where uniacid={$_W['uniacid']}";
        $vip_buy_nums['filter'] = pdo_fetchcolumn($sql . $wheres) ? : 0;
        $vip_buy_nums['all']    = pdo_fetchcolumn($sql) ? : 0;
        // 购买代理金额
        $sql = "select sum(fee) from " . tablename('xuan_mixloan_payment') .  "
            where uniacid={$_W['uniacid']}1";
        $vip_buy_fee['filter'] = pdo_fetchcolumn($sql . $wheres) ? : 0;
        $vip_buy_fee['all']    = pdo_fetchcolumn($sql) ? : 0;
        // 购买代理返佣
        $sql = "select sum(re_bonus) from " . tablename('xuan_mixloan_product_apply_b') .  "
            where uniacid={$_W['uniacid']} and pid=0 and re_bonus>0";
        $vip_buy_reward['filter'] = pdo_fetchcolumn($sql . $wheres) ? : 0;
        $vip_buy_reward['all']    = pdo_fetchcolumn($sql) ? : 0;
        $sql = "select sum(re_bonus) from " . tablename('xuan_mixloan_product_apply_a') .  "
            where uniacid={$_W['uniacid']} and pid=0 and re_bonus>0";
        $vip_buy_reward['filter'] += pdo_fetchcolumn($sql . $wheres) ? : 0;
        $vip_buy_reward['all']    += pdo_fetchcolumn($sql) ? : 0;
        // 提现佣金
        $sql = "select sum(bonus) from " . tablename('xuan_mixloan_withdraw') .  "
            where uniacid={$_W['uniacid']} and status<>-1";
        $withdraw_money['filter'] = pdo_fetchcolumn($sql . $wheres) ? : 0;
        $withdraw_money['all']    = pdo_fetchcolumn($sql) ? : 0;
        // 申请中提现
        $sql = "select sum(bonus) from " . tablename('xuan_mixloan_withdraw') .  "
            where uniacid={$_W['uniacid']} and status=0";
        $withdraw_apply['all']    = pdo_fetchcolumn($sql) ? : 0;
        // 佣金总额
        $sql = "select sum(re_bonus+done_bonus+extra_bonus) from " . tablename('xuan_mixloan_product_apply_b') .  "
            where uniacid={$_W['uniacid']} and re_bonus>0";
        $reward['filter'] = pdo_fetchcolumn($sql . $wheres) ? : 0;
        $reward['all']    = pdo_fetchcolumn($sql) ? : 0;
        $sql = "select sum(re_bonus+done_bonus+extra_bonus) from " . tablename('xuan_mixloan_product_apply_a') .  "
            where uniacid={$_W['uniacid']} and re_bonus>0";
        $reward['filter'] += pdo_fetchcolumn($sql . $wheres) ? : 0;
        $reward['all']    += pdo_fetchcolumn($sql) ? : 0;
        // 剩余未体现
        $balance['all']           = $reward['all'] - $withdraw_money['all'] ? : 0;
        // 产品佣金
        $sql = "select sum(re_bonus+done_bonus+extra_bonus) from " . tablename('xuan_mixloan_product_apply_b') .  "
            where uniacid={$_W['uniacid']} and pid<>0 and (re_bonus>0 or done_bonus>0)";
        $reward_product['filter'] = pdo_fetchcolumn($sql . $wheres) ? : 0;
        $reward_product['all']    = pdo_fetchcolumn($sql) ? : 0;
        $sql = "select sum(re_bonus+done_bonus+extra_bonus) from " . tablename('xuan_mixloan_product_apply_a') .  "
            where uniacid={$_W['uniacid']} and pid<>0 and (re_bonus>0 or done_bonus>0)";
        $reward_product['filter'] += pdo_fetchcolumn($sql . $wheres) ? : 0;
        $reward_product['all']    += pdo_fetchcolumn($sql) ? : 0;
        // 产品申请
        $sql = "select count(*) from " . tablename('xuan_mixloan_product_apply_b') .  "
            where uniacid={$_W['uniacid']}";
        $product_apply_b['filter'] = pdo_fetchcolumn($sql . $wheres) ? : 0;
        $product_apply_b['all']    = pdo_fetchcolumn($sql) ? : 0;
        $sql = "select count(*) from " . tablename('xuan_mixloan_product_apply_a') .  "
            where uniacid={$_W['uniacid']}";
        $product_apply_b['filter'] += pdo_fetchcolumn($sql . $wheres) ? : 0;
        $product_apply_b['all']    += pdo_fetchcolumn($sql) ? : 0;
        // 代理数
        $sql = "select count(*) from " . tablename('xuan_mixloan_payment') .  "
            where uniacid={$_W['uniacid']}";
        $agent['filter'] = pdo_fetchcolumn($sql . $wheres) ? : 0;
        $agent['all']    = pdo_fetchcolumn($sql) ? : 0;
        // 会员数
        $sql = "select count(*) from " . tablename('xuan_mixloan_member') .  "
            where uniacid={$_W['uniacid']}";
        $user['filter'] = pdo_fetchcolumn($sql . $wheres) ? : 0;
        $user['all']    = pdo_fetchcolumn($sql) ? : 0;
    }
} else if ($operation == 'verify') {
    $password = trim($_GPC['password']);
    if ($password == 'Laowu.0577') {
        setcookie('verify', 1,time()+86400);
        header("location:{$this->createWebUrl('statistics')}");
    } else {
        message('密码不正确');
    }
}
include $this->template('statistics');
