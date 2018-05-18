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
    $sql = 'select a.id,a.uid,b.nickname,b.avatar,a.createtime,a.fee,a.tid from ' . tablename('xuan_mixloan_payment') . " a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id where a.uniacid={$_W['uniacid']} " . $wheres . ' ORDER BY a.id DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
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
        $join .= " LEFT JOIN ".tablename("xuan_mixloan_product")." c ON a.relate_id=c.id";
        $wheres.= " AND c.relate_id='{$_GPC['relate_id']}'";
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
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
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
        $row['inviter'] = pdo_fetch("select avatar,nickname from ".tablename("xuan_mixloan_member")." where id = {$row['inviter']}");
    }
    unset($row);
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
    $total = pdo_fetchcolumn( 'select count(1) from ' . tablename('xuan_mixloan_withdraw') . " a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id where a.uniacid={$_W['uniacid']} " . $wheres );
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
    $member = pdo_fetch('select avatar,nickname from '.tablename("xuan_mixloan_member")." where id=:id",array(':id'=>$item['uid']));
    $bank = pdo_fetch('select realname,bankname,banknum,phone from '.tablename("xuan_mixloan_creditCard")." where id=:id",array(':id'=>$item['bank_id']));
    if ($_GPC['post'] == 1) {
        if ($_GPC['data']['status'] == 1 && empty($item['ext_info']['partner_trade_no'])) {
            $pay = m('pay')->pay($bank['banknum'], $bank['realname'], $_GPC['data']['ext_info']['bank_code'], $item['bonus'], $_GPC['data']['ext_info']['reason']);
            if ($pay['code'] > 1) {
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
