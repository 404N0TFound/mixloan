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
    if (!empty($_GPC['name'])) {
        $wheres.= " AND b.nickname LIKE '%{$_GPC['name']}%'";
    }
    $sql = 'select a.id,a.uid,b.nickname,b.avatar,b.phone,a.createtime,a.fee,a.tid from ' . tablename('xuan_mixloan_payment') . " a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id where a.uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY a.id DESC';
    if ($_GPC['export'] != 1) {
        $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    }
    $list = pdo_fetchall($sql);
    if ($_GPC['export'] == 1) {
        foreach ($list as &$row) {
            $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
        }
        unset($row);
        m('excel')->export($list, array(
            "title" => "代理资料",
            "columns" => array(
                array(
                    'title' => '会员id',
                    'field' => 'uid',
                    'width' => 20
                ),
                array(
                    'title' => '会员昵称',
                    'field' => 'nickname',
                    'width' => 50
                ),
                array(
                    'title' => '订单号',
                    'field' => 'tid',
                    'width' => 50
                ),
                array(
                    'title' => '购买费用',
                    'field' => 'fee',
                    'width' => 20
                ),
                array(
                    'title' => '手机号',
                    'field' => 'phone',
                    'width' => 50
                ),
                array(
                    'title' => '购买时间',
                    'field' => 'createtime',
                    'width' => 50
                ),
            )
        ));
    }
    $total = pdo_fetchcolumn( 'select count(1) from ' . tablename('xuan_mixloan_payment') . " a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id where a.uniacid={$_W['uniacid']} " . $wheres );
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'apply_list') {
    //申请列表
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    if (!empty($_GPC['name'])) {
        $wheres.= " AND a.realname LIKE '%{$_GPC['realname']}%'";
    }
    if (!empty($_GPC['uid'])) {
        $wheres.= " AND a.inviter='{$_GPC['uid']}'";
    }
    if (!empty($_GPC['type'])) {
        $wheres.= " AND c.type='{$_GPC['type']}'";
    }
    if (!empty($_GPC['relate_id'])) {
        $wheres.= " AND c.relate_id='{$_GPC['relate_id']}'";
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
    $sql = 'select a.*,b.avatar,c.name,c.count_time from ' . tablename('xuan_mixloan_product_apply') . " a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id LEFT JOIN ".tablename("xuan_mixloan_product")." c ON a.pid=c.id where a.uniacid={$_W['uniacid']} and a.status<>-2 " . $wheres . ' ORDER BY a.id DESC';
    if ($_GPC['export'] != 1) {
        $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    }
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        if (!$row['pid']) {
            $row['realname'] = pdo_fetchcolumn('SELECT nickname FROM '.tablename('xuan_mixloan_member').' WHERE id=:id', array(':id'=>$row['uid']));
            $row['name'] = '邀请购买代理';
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
                $row['inviter_count'] = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$row['inviter']['id']} AND status>1 AND pid={$row['pid']}") ? : 0;
                $row['inviter_sum'] = pdo_fetchcolumn("SELECT SUM(relate_money) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$row['inviter']['id']} AND status>1 AND pid={$row['pid']}") ? : 0;
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
    $total = pdo_fetchcolumn( 'select count(*) from ' . tablename('xuan_mixloan_product_apply') . " a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id LEFT JOIN ".tablename("xuan_mixloan_product")." c ON a.pid=c.id where a.uniacid={$_W['uniacid']} and a.status<>-2  " . $wheres );
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
        $all = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE uniacid={$_W['uniacid']} AND inviter={$row['uid']}");
        $row['left_bonus'] = $all - m('member')->sumWithdraw($row['uid']);
    }
    unset($row);
    $total = pdo_fetchcolumn( 'select count(1) from ' . tablename('xuan_mixloan_withdraw') . " a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id where a.uniacid={$_W['uniacid']} " . $wheres );
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    pdo_delete('xuan_mixloan_payment', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('agent', array('op' => '')), "sccuess");
} else if ($operation == 'apply_delete') {
    pdo_delete('xuan_mixloan_product_apply', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('agent', array('op' => 'apply_list')), "sccuess");
} else if ($operation == 'withdraw_delete') {
    pdo_delete('xuan_mixloan_withdraw', array("id" => $_GPC["id"]));
    message("提交成功", $this->createWebUrl('agent', array('op' => 'withdraw_list')), "sccuess");
} else if ($operation == 'apply_update') {
    //申请编辑
    //申请编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_product_apply"). " where id={$id}");
    if ($item['pid']) {
        $info = pdo_fetch('select * from '.tablename("xuan_mixloan_product")." where id=:id", array(':id'=>$item['pid']));
        $agent = m('member')->checkAgent($item['inviter'], $config);
        $info['ext_info'] = json_decode($info['ext_info'], true);
        if ($agent['level'] == 1) {
            if ($item['degree'] == 1) {
                $info['done_reward_money'] = $info['ext_info']['done_one_init_reward_money'];
                $info['done_reward_per'] = $info['ext_info']['done_one_init_reward_per'];
                $info['re_reward_money'] = $info['ext_info']['re_one_init_reward_money'];
                $info['re_reward_per'] = $info['ext_info']['re_one_init_reward_per'];
            } else if ($item['degree'] == 2) {
                $info['done_reward_money'] = $info['ext_info']['done_two_init_reward_money'];
                $info['done_reward_per'] = $info['ext_info']['done_two_init_reward_per'];
                $info['re_reward_money'] = $info['ext_info']['re_two_init_reward_money'];
                $info['re_reward_per'] = $info['ext_info']['re_two_init_reward_per'];
            }
        } else if ($agent['level'] == 2) {
            if ($item['degree'] == 1) {
                $info['done_reward_money'] = $info['ext_info']['done_one_mid_reward_money'];
                $info['done_reward_per'] = $info['ext_info']['done_one_mid_reward_per'];
                $info['re_reward_money'] = $info['ext_info']['re_one_mid_reward_money'];
                $info['re_reward_per'] = $info['ext_info']['re_one_mid_reward_per'];
            } else if ($item['degree'] == 2) {
                $info['done_reward_money'] = $info['ext_info']['done_two_mid_reward_money'];
                $info['done_reward_per'] = $info['ext_info']['done_two_mid_reward_per'];
                $info['re_reward_money'] = $info['ext_info']['re_two_mid_reward_money'];
                $info['re_reward_per'] = $info['ext_info']['re_two_mid_reward_per'];
            }
        } else if ($agent['level'] == 3) {
            if ($item['degree'] == 1) {
                $info['done_reward_money'] = $info['ext_info']['done_one_height_reward_money'];
                $info['done_reward_per'] = $info['ext_info']['done_one_height_reward_per'];
                $info['re_reward_money'] = $info['ext_info']['re_one_height_reward_money'];
                $info['re_reward_per'] = $info['ext_info']['re_one_height_reward_per'];
            } else if ($item['degree'] == 2) {
                $info['done_reward_money'] = $info['ext_info']['done_two_height_reward_money'];
                $info['done_reward_per'] = $info['ext_info']['done_two_height_reward_per'];
                $info['re_reward_money'] = $info['ext_info']['re_two_height_reward_money'];
                $info['re_reward_per'] = $info['ext_info']['re_two_height_reward_per'];
            }
        }
    } else {
        $info['name'] = '邀请购买代理奖励';
    }
    $inviter = pdo_fetch('select avatar,nickname from '.tablename("xuan_mixloan_member")." where id=:id",array(':id'=>$item['inviter']));
    $inviter['count'] = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$item['inviter']} AND status>1 AND pid={$item['pid']}") ? : 0;
    $inviter['sum'] = pdo_fetchcolumn("SELECT SUM(relate_money) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$item['inviter']} AND status>1 AND pid={$item['pid']}") ? : 0;
    $apply = pdo_fetch('select avatar,nickname,phone,certno from '.tablename("xuan_mixloan_member")." where id=:id",array(':id'=>$item['uid']));
    if ($_GPC['post'] == 1) {
        pdo_update('xuan_mixloan_product_apply', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('agent', array('op' => 'apply_list')), "sccuess");
    }
} else if ($operation == 'withdraw_update') {
    //提现更改
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_withdraw"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    $member = pdo_fetch('select avatar,nickname from '.tablename("xuan_mixloan_member")." where id=:id",array(':id'=>$item['uid']));
    $bank = pdo_fetch('select realname,bankname,banknum,phone from '.tablename("xuan_mixloan_creditCard")." where id=:id",array(':id'=>$item['bank_id']));
    if ($_GPC['post'] == 1) {
        if ($_GPC['data']['status'] == 1 && empty($item['ext_info']['payment_no'])) {
            $pay = m('pay')->pay($bank['banknum'],
                $bank['realname'], $_GPC['data']['ext_info']['bank_code'],
                $item['bonus'], $_GPC['data']['ext_info']['reason'],
                $item['ext_info']['partner_trade_no']);
            if ($pay['code']>1) {
                message($pay['msg'], $this->createWebUrl('agent', array('op'=>'withdraw_update', 'id'=>$id)), "error");
            } else {
                $_GPC['data']['ext_info']['partner_trade_no'] = $pay['data']['partner_trade_no'];
                $_GPC['data']['ext_info']['payment_no'] = $pay['data']['payment_no'];
            }
        }
        if ($_GPC['data']['ext_info']) $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_withdraw', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('agent', array('op' => 'withdraw_list')), "sccuess");
    }
}
include $this->template('agent');
?>
