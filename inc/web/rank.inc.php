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
        $cond .= " and createtime > {$start} and createtime<= {$end}";
        if (!empty($_GPC['nickname'])) {
            $wheres .= " AND nickname LIKE '%{$_GPC['nickname']}%'";
        }
        if (!empty($_GPC['id'])) {
            $wheres .= " AND id = {$_GPC['id']}";
        }
        $sql = "select * from " . tablename('xuan_mixloan_member') . '
            where uniacid=:uniacid ' . $wheres . '
            order by id desc';
        $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
        foreach ($list as &$row) {
            if ($type == 1) {
                $row['bonus'] = pdo_fetchcolumn("select sum(re_bonus+done_bonus+extra_bonus) from " . tablename('xuan_mixloan_product_apply') . "
                    where inviter={$row['id']} " . $cond) ? : 0;
                $row['count'] = pdo_fetchcolumn("select count(*) from " . tablename('xuan_mixloan_product_apply') . "
                    where inviter={$row['id']} " . $cond) ? : 0;
            } else {
                $row['bonus'] = pdo_fetchcolumn("select sum(re_bonus+done_bonus+extra_bonus) from " . tablename('xuan_mixloan_product_apply_backup') . "
                    where inviter={$row['id']} " . $cond) ? : 0;
                $row['count'] = pdo_fetchcolumn("select count(*) from " . tablename('xuan_mixloan_product_apply_backup') . "
                    where inviter={$row['id']} " . $cond) ? : 0;
            }
        }
        unset($row);
        $total = pdo_fetchcolumn( "select count(*) from " . tablename('xuan_mixloan_member') . '
            where uniacid=:uniacid' . $wheres , array(':uniacid' => $_W['uniacid']));
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
    $list = pdo_fetchall("select id from " . tablename('xuan_mixloan_product_apply') . "
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
    $list = pdo_fetchall("select id from " . tablename('xuan_mixloan_product_apply_backup') . "
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
