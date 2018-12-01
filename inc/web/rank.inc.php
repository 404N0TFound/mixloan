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
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $wheres = '';
        $cond = '';
        $type = $_GPC['type'] ? : 1;
        if (!empty($_GPC['time'])) {
            $starttime = $_GPC['time']['start'];
            $endtime = $_GPC['time']['end'];
            $start = strtotime($starttime);
            $end = strtotime($endtime);
        } else {
            $starttime = date('Y-m');
            $endtime =  date('Y-m-d H:i:s');
            $start = strtotime($starttime . ' -2 months');
            $end = strtotime($endtime . ' -2 months');
            $starttime = date('Y-m-d H:i:s', $start);
            $endtime =  date('Y-m-d H:i:s', $end);
        }
        $cond = '';
        $wheres .= " and b.createtime > {$start} and b.createtime<= {$end}";
        $cond .= " and createtime > {$start} and createtime<= {$end}";
        if (!empty($_GPC['id'])) {
            $wheres .= " AND b.inviter = {$_GPC['id']}";
        }
        if ($type == 1) {
            $sql = "select sum(b.re_bonus+b.done_bonus+b.extra_bonus) as bonus,inviter from " . tablename('xuan_mixloan_product_apply_b') . " b 
                where b.uniacid=:uniacid  " . $wheres . '
                group by inviter
                order by bonus desc';
            $total_sql = "select  distinct(inviter) from " . tablename('xuan_mixloan_product_apply_b') . " b 
                where b.uniacid=:uniacid  " . $wheres . '
                ';
        } else {
            $sql = "select sum(b.re_bonus+b.done_bonus+b.extra_bonus) as bonus,inviter from " . tablename('xuan_mixloan_product_apply_a') . " b 
                where b.uniacid=:uniacid  " . $wheres . '
                group by inviter
                order by bonus desc';
            $total_sql = "select  distinct(inviter) from " . tablename('xuan_mixloan_product_apply_a') . " b 
                where b.uniacid=:uniacid  " . $wheres . '
                ';

        }
        $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
        foreach ($list as &$row) {
            $row['member'] = pdo_fetch('select * from ' . tablename('xuan_mixloan_member') . '
                where id=:id', array(':id' => $row['inviter']));
            $all =  m('member')->sumBonus($member['id']);
            $used = m('member')->sumWithdraw($member['id']);
            $row['balance'] = $all - $used;
            if ($type == 1) {
                $row['count'] = pdo_fetchcolumn("select count(*) from " . tablename('xuan_mixloan_product_apply_b') . "
                    where inviter={$row['inviter']} " . $cond) ? : 0;
            } else {
                $row['count'] = pdo_fetchcolumn("select count(*) from " . tablename('xuan_mixloan_product_apply_a') . "
                    where inviter={$row['inviter']} " . $cond) ? : 0;
            }
            $row['black'] = pdo_fetchcolumn('select count(1) from ' . tablename('xuan_mixloan_blacklist') . '
                where uid=:uid', array(':uid' => $row['inviter']));
            $row['white'] = pdo_fetchcolumn('select count(1) from ' . tablename('xuan_mixloan_whitelist') . '
                where uid=:uid', array(':uid' => $row['inviter']));
        }
        unset($row);
        $total = pdo_fetchcolumn($total_sql, array(':uniacid' => $_W['uniacid']));
        $pager = pagination($total, $pindex, $psize);
    }
} else if ($operation == 'verify') {
    $password = trim($_GPC['password']);
    if ($password == 'Laowu.0577') {
        setcookie('verify', 1,time()+86400);
        header("location:{$this->createWebUrl('rank')}");
    } else {
        message('密码不正确');
    }
} else if ($operation == 'delete') {
    //删除
    $start = $_GPC['start'];
    $end = $_GPC['end'];
    $cond .= " and createtime > {$start} and createtime<= {$end}";
    $list = pdo_fetchall("select id from " . tablename('xuan_mixloan_product_apply_b') . "
        where inviter={$_GPC['id']} " . $cond);
    foreach ($list as $row) {
        $temp = array(
            'relate_id' => $row['id'],
            'status'=>0
        );
        $temp_string = '('. implode(',', array_values($temp)) . ')';
        $insert[] = $temp_string;
    }
    if (!empty($insert)) {
        $insert_string =  implode(',', $insert);
        $sql = "INSERT ".tablename("xuan_mixloan_backup_id"). " (`relate_id`, `status`) VALUES {$insert_string}";
        pdo_run($sql);
    }
    message('已添加到队列中，稍后自动执行删除', referer(), 'success');
} else if ($operation == 'recovery') {
    //恢复
    $start = $_GPC['start'];
    $end = $_GPC['end'];
    $cond .= " and createtime > {$start} and createtime<= {$end}";
    $list = pdo_fetchall("select id from " . tablename('xuan_mixloan_product_apply_a') . "
        where inviter={$_GPC['id']} " . $cond);
    foreach ($list as $row) {
        $temp = array(
            'relate_id' => $row['id'],
            'status'=>0
        );
        $temp_string = '('. implode(',', array_values($temp)) . ')';
        $insert[] = $temp_string;
    }
    if (!empty($insert)) {
        $insert_string =  implode(',', $insert);
        $sql = "INSERT ".tablename("xuan_mixloan_recovery_id"). " (`relate_id`, `status`) VALUES {$insert_string}";
        pdo_run($sql);
    }
    message('已添加到队列中，稍后自动执行恢复', referer(), 'success');
}
include $this->template('rank');
