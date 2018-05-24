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
    //会员列表
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    $cond = '';
    if (!empty($_GPC['name'])) {
        $wheres.= " AND b.nickname LIKE '%{$_GPC['name']}%'";
    }
    if (!empty($_GPC['time'])) {
        $starttime = $_GPC['time']['start'];
        $endtime = $_GPC['time']['end'];
        $start = strtotime($starttime);
        $end = strtotime($endtime);
        $wheres .= " and a.createtime>{$start} and a.createtime<={$end}";
        $cond .= " and createtime>{$start} and createtime<={$end}";
    } else {
        $starttime = "";
        $endtime = "";
    }
    $sql = 'select a.id,a.uid,b.nickname,b.avatar,a.createtime,a.fee,a.tid from ' . tablename('xuan_mixloan_payment') . " a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id where a.uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY a.id DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);

    $all_money = pdo_fetchcolumn('select sum(fee) from ' .tablename('xuan_mixloan_payment'). '
        where uniacid=:uniacid' . $cond, array(':uniacid'=>$_W['uniacid'])) ? : 0;

    $count_bonus = pdo_fetchcolumn('select sum(re_bonus+done_bonus+extra_bonus) from ' .tablename('xuan_mixloan_bonus'). '
        where uniacid=:uniacid and type=2' . $cond, array(':uniacid'=>$_W['uniacid'])) ? : 0;

    $count_pay = pdo_fetchcolumn('select count(*) from ' .tablename('xuan_mixloan_payment'). '
        where uniacid=:uniacid and fee<>0' . $cond, array(':uniacid'=>$_W['uniacid'])) ? : 0;

    $date = date('Y-m-d');
    $last_day_time = strtotime("{$date} -1 days");
    $today_time = strtotime("{$date}");

    $count_lastday_pay = pdo_fetchcolumn('select count(*) from ' .tablename('xuan_mixloan_payment'). "
        where uniacid=:uniacid and fee<>0 
        and createtime>{$last_day_time} and createtime<={$today_time}",array(':uniacid'=>$_W['uniacid'])) ? : 0;

    $count_today_pay = pdo_fetchcolumn('select count(*) from ' .tablename('xuan_mixloan_payment'). "
        where uniacid=:uniacid and fee<>0
        and createtime>{$today_time}", array(':uniacid'=>$_W['uniacid'])) ? : 0;

    $total = pdo_fetchcolumn( 'select count(1) from ' . tablename('xuan_mixloan_payment') . " a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id where a.uniacid={$_W['uniacid']} " . $wheres );
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'apply_list') {
    //申请列表
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = $join = '';
    if (!empty($_GPC['name'])) {
        $wheres.= " AND a.realname LIKE '%{$_GPC['name']}%'";
    }
    if (!empty($_GPC['phone'])) {
        $wheres.= " AND a.phone LIKE '%{$_GPC['phone']}%'";
    }
    if (!empty($_GPC['uid'])) {
        $wheres.= " AND a.inviter='{$_GPC['uid']}'";
    }
    if (!empty($_GPC['type'])) {
        $wheres.= " AND a.type='{$_GPC['type']}'";
    }
    if ($_GPC['type'] == 1 && !empty($_GPC['p_type'])) {
        $join .= " LEFT JOIN ".tablename("xuan_mixloan_product")." c ON a.relate_id=c.id";
        $wheres.= " AND c.type='{$_GPC['p_type']}'";
    }
    if ($_GPC['type'] == 1 && !empty($_GPC['relate_id'])) {
        $wheres.= " AND a.relate_id='{$_GPC['relate_id']}'";
    }
    if ($_GPC['type'] == 3 && !empty($_GPC['title'])) {
        $join .= " LEFT JOIN ".tablename("xuan_mixloan_channel")." c ON a.relate_id=c.id";
        $wheres.= " AND c.title LIKE '%{$_GPC['title']}%'";
    }
    $c_arr = m('bank')->getCard(['id', 'name']);
    $s_arr = m('loan')->getList(['id', 'name']);
    foreach ($c_arr as &$row) {
        $row['type'] = 1;
    }
    unset($row);
    foreach ($s_arr as &$row) {
        $row['type'] = 2;
    }
    unset($row);
    $c_json = $c_arr ? json_encode(array_values($c_arr)) : json_encode([]);
    $s_json = $s_arr ? json_encode(array_values($s_arr)) : json_encode([]);
    $sql = 'select a.*,b.avatar from ' . tablename('xuan_mixloan_bonus') . " a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id {$join} where a.uniacid={$_W['uniacid']} and a.status<>-2 " . $wheres . ' ORDER BY a.id DESC';
    if ($_GPC['export'] != 1) {
        $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    }
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        if ($row['type'] == 2) {
            $row['name'] = '邀请购买代理';
        } else if ($row['type'] == 3) {
            $row['name'] = '邀请购买文章';
        } else if ($row['type'] == 4) {
            $row['name'] = '邀请付费信用查询';
        } else {
            $row['name'] = pdo_fetchcolumn('SELECT name FROM '.tablename('xuan_mixloan_product').' WHERE id=:id', array(':id'=>$row['relate_id']));
        }
        $row['inviter'] = pdo_fetch("select id,avatar,nickname from ".tablename("xuan_mixloan_member")." where id = {$row['inviter']}");
    }
    unset($row);
    if ($_GPC['export'] == 1) {
        foreach ($list as &$row) {
            if ($row['status'] == -2){
                $row['status'] = '邀请用户已注册过，不产生佣金';
            } else if ($row['status'] == -1){
                $row['status'] = '注册失败';
            } else if ($row['status'] == 0){
                $row['status'] = '邀请中';
            } else if ($row['status'] == 1){
                $row['status'] = '已注册';
            } else if ($row['status'] == 1){
                $row['status'] = '已完成';
            }
            $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
            if ($row['inviter']) {
                $row['inviter_name'] = $row['inviter']['nickname'];
                $row['inviter_count'] = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename("xuan_mixloan_bonus")." WHERE inviter={$row['inviter']['id']} AND status>1 AND relate_id={$row['relate_id']}") ? : 0;
                $row['inviter_sum'] = pdo_fetchcolumn("SELECT SUM(relate_money) FROM ".tablename("xuan_mixloan_bonus")." WHERE inviter={$row['inviter']['id']} AND status>1 AND relate_id={$row['relate_id']}") ? : 0;
            } else {
                $row['inviter_name'] = '无';
                $row['inviter_count'] = 0;
                $row['inviter_sum'] = 0;
            }
            if ($row['count_time'] == 1) {
                $row['count_time'] = '日结';
            } else if ($row['count_time'] == 7) {
                $row['count_time'] = '周结';
            } else if ($row['count_time'] == 30) {
                $row['count_time'] = '月结';
            } else {
                $row['count_time'] = '现结';
            }
        }
        unset($row);
        m('excel')->export($list, array(
            "title" => "申请资料",
            "columns" => array(
                array(
                    'title' => 'id',
                    'field' => 'id',
                    'width' => 10
                ),
                array(
                    'title' => '邀请人',
                    'field' => 'inviter_name',
                    'width' => 20
                ),
                array(
                    'title' => '被邀请人',
                    'field' => 'realname',
                    'width' => 20
                ),
                array(
                    'title' => '关联产品',
                    'field' => 'name',
                    'width' => 20
                ),
                array(
                    'title' => '身份证',
                    'field' => 'certno',
                    'width' => 20
                ),
                array(
                    'title' => '手机号',
                    'field' => 'phone',
                    'width' => 20
                ),
                array(
                    'title' => '结算方式',
                    'field' => 'count_time',
                    'width' => 20
                ),
                array(
                    'title' => '下款金额',
                    'field' => 'relate_money',
                    'width' => 20
                ),
                array(
                    'title' => '注册奖励',
                    'field' => 're_bonus',
                    'width' => 20
                ),
                array(
                    'title' => '下款/卡奖励',
                    'field' => 'done_bonus',
                    'width' => 20
                ),
                array(
                    'title' => '额外奖励',
                    'field' => 'extra_bonus',
                    'width' => 20
                ),
                array(
                    'title' => '邀请时间',
                    'field' => 'createtime',
                    'width' => 20
                ),
                array(
                    'title' => '该产品已成功邀请总数',
                    'field' => 'inviter_count',
                    'width' => 30
                ),
                array(
                    'title' => '该产品已邀请下款总额',
                    'field' => 'inviter_sum',
                    'width' => 30
                ),
            )
        ));
        unset($row);
    }
    $total = pdo_fetchcolumn( 'select count(*) from ' . tablename('xuan_mixloan_bonus') . " a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id {$join} where a.uniacid={$_W['uniacid']} and a.status<>-2  " . $wheres );
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'withdraw_list') {
    //提现列表
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    if (isset($_GPC['status']) && $_GPC['status'] != "") {
        $wheres .= " and a.status={$_GPC['status']}";
    }
    $sql = 'select a.id,b.nickname,b.avatar,a.createtime,a.bonus,a.status,a.uid from ' . tablename('xuan_mixloan_withdraw') . " a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id where a.uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY a.id DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        $all = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_bonus")." WHERE uniacid={$_W['uniacid']} AND inviter={$row['uid']}");
        $time = $row['createtime'];
        $apply_money = pdo_fetchcolumn('SELECT SUM(bonus) FROM '.tablename('xuan_mixloan_withdraw').' where uid=:uid AND createtime<='.$time, array(':uid'=>$row['uid']));
        $row['left_bonus'] = $all - $apply_money;
    }
    unset($row);
    $date = date('Y-m-d');
    $last_day_time = strtotime("{$date} -1 days");
    $today_time = strtotime("{$date}");
    
    $withdraw_all = pdo_fetchcolumn('select sum(bonus) from ' .tablename('xuan_mixloan_withdraw'). '
        where uniacid=:uniacid', array(':uniacid'=>$_W['uniacid'])) ? : 0;
    
    $last_day_all = pdo_fetchcolumn('select sum(bonus) from ' .tablename('xuan_mixloan_withdraw'). "
        where uniacid=:uniacid and createtime>{$last_day_time} and createtime<={$today_time}", array(':uniacid'=>$_W['uniacid'])) ? : 0;
    
    $applying_all = pdo_fetchcolumn('select sum(bonus) from ' .tablename('xuan_mixloan_withdraw'). '
        where uniacid=:uniacid and status=0', array(':uniacid'=>$_W['uniacid'])) ? : 0;
    
    $all_bonus = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_bonus")."
        WHERE uniacid={$_W['uniacid']} and status>0") ? : 0;
    $withdraw_left = $all_bonus - $withdraw_all;

    $total = pdo_fetchcolumn( 'select count(1) from ' . tablename('xuan_mixloan_withdraw') . "
        a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id
        where a.uniacid={$_W['uniacid']} " . $wheres );
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    //删除会员
    pdo_delete('xuan_mixloan_payment', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('agent', array('op' => '')), "sccuess");
} else if ($operation == 'apply_delete') {
    //删除申请
    pdo_delete('xuan_mixloan_bonus', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('agent', array('op' => 'apply_list')), "sccuess");
} else if ($operation == 'withdraw_delete') {
    //删除提现
    pdo_delete('xuan_mixloan_withdraw', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('agent', array('op' => 'withdraw_list')), "sccuess");
} else if ($operation == 'apply_update') {
    //申请编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_bonus"). " where id={$id}");
    if ($item['type'] == 1) {
        $info = pdo_fetch('select * from '.tablename("xuan_mixloan_product")." where id=:id", array(':id'=>$item['relate_id']));
        $info['ext_info'] = json_decode($info['ext_info'], true);
    } else if ($item['type'] == 2) {
        $info['ext_info']['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
        $info['name'] = '邀请购买代理奖励';
    } else if ($item['type'] == 3) {
        $info = pdo_fetch('SELECT * FROM '.tablename('xuan_mixloan_channel').' WHERE id=:id', array(':id'=>$item['relate_id']));
        $info['ext_info'] = json_decode($info['ext_info'], 1);
        $info['ext_info']['logo'] = tomedia($info['ext_info']['pic']);
        $info['name'] = $info['title'];
    } else if ($item['type'] == 4) {
        $info['ext_info']['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
        $info['name'] = '邀请付费信用查询';
    }
    $inviter = pdo_fetch('select avatar,nickname from '.tablename("xuan_mixloan_member")." where id=:id",array(':id'=>$item['inviter']));
    $inviter['count'] = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename("xuan_mixloan_bonus")." WHERE inviter={$item['inviter']} AND status>1 AND relate_id={$item['relate_id']} AND type={$item['type']}") ? : 0;
    $inviter['sum'] = pdo_fetchcolumn("SELECT SUM(relate_money) FROM ".tablename("xuan_mixloan_bonus")." WHERE inviter={$item['inviter']} AND status>1 AND relate_id={$item['relate_id']} AND type={$item['type']}") ? : 0;
    $apply = pdo_fetch('select avatar,nickname,phone,certno from '.tablename("xuan_mixloan_member")." where id=:id",array(':id'=>$item['uid']));
    if ($_GPC['post'] == 1) {
        pdo_update('xuan_mixloan_bonus', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('agent', array('op' => 'apply_list')), "sccuess");
    }
} else if ($operation == 'withdraw_update') {
    //提现更改
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_withdraw"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    $member = pdo_fetch('select avatar,nickname,openid from '.tablename("xuan_mixloan_member")." where id=:id",array(':id'=>$item['uid']));
    if (true) {
        //id 42之后改为微信二维码收款
        $bank = pdo_fetch('select img_url from '.tablename("xuan_mixloan_withdraw_qrcode")." where id=:id",array(':id'=>$item['bank_id']));
    } else {
        $bank = pdo_fetch('select realname,bankname,banknum,phone from '.tablename("xuan_mixloan_creditCard")." where id=:id",array(':id'=>$item['bank_id']));
    }
    if ($_GPC['post'] == 1) {
        if ($_GPC['data']['status'] == 1) {
            $wx = WeAccount::create();
            $msg = array(
                'first' => array(
                    'value' => "您申请的提现金额已到帐。",
                    "color" => "#4a5077"
                ),
                'keyword1' => array(
                    'value' => date("Y-m-d H:i:s",time()),
                    "color" => "#4a5077"
                ),
                'keyword2' => array(
                    'value' => "微信转账",
                    "color" => "#4a5077"
                ),
                'keyword3' => array(
                    'value' => $item['bonus'],
                    "color" => "#4a5077"
                ),
                'keyword4' => array(
                    'value' => 0,
                    "color" => "#4a5077"
                ),
                'keyword5' => array(
                    'value' => $item['bonus'],
                    "color" => "#4a5077"
                ),
                'remark' => array(
                    'value' => "感谢你的使用。",
                    "color" => "#A4D3EE"
                ),
            );
            $templateId=$config['tpl_notice6'];
            $res = $wx->sendTplNotice($member['openid'],$templateId,$msg);
        }
        if ($_GPC['data']['ext_info']) $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_withdraw', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('agent', array('op' => 'withdraw_list')), "sccuess");
    }
}
include $this->template('agent');
?>
