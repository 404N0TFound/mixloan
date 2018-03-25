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
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = ' AND status<>-1';
    if (!empty($_GPC['openid'])) {
        $wheres.= " AND openid='{$openid}'";
    }
    if (!empty($_GPC['nickname'])) {
        $wheres.= " AND nickname LIKE '%{$_GPC['nickname']}%'";
    }
    $sql = 'select * from ' . tablename('xuan_mixloan_member') . "where uniacid={$_W['uniacid']} "  . $wheres . ' ORDER BY ID DESC';
    if ($_GPC['export'] != 1) {
        $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql);
        foreach ($list as &$row) {
            $row['type'] = m('member')->checkAgent($row['id'], $config)['code'];
        }
        unset($row);
    } else {
        $list = pdo_fetchall($sql);
        foreach ($list as &$row) {
            $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
        }
        unset($row);
        m('excel')->export($list, array(
            "title" => "会员资料",
            "columns" => array(
                array(
                    'title' => '昵称',
                    'field' => 'nickname',
                    'width' => 50
                ),
                array(
                    'title' => '手机号',
                    'field' => 'phone',
                    'width' => 50
                ),
                array(
                    'title' => '时间',
                    'field' => 'createtime',
                    'width' => 50
                ),
            )
        ));
    }
    $total = pdo_fetchcolumn( 'select count(1) from ' . tablename('xuan_mixloan_member') . "where uniacid={$_W['uniacid']} "  . $wheres . ' ORDER BY ID DESC' );
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    $member = m('member')->getMember($_GPC['id']);
    pdo_update('xuan_mixloan_member', array("status" => -1, 'openid'=>'', 'uid'=>0, 'phone'=>'', 'certno'=>''), array('id'=>$_GPC['id']));
    pdo_delete('xuan_mixloan_inviter', array("phone" => $member["phone"]));
    pdo_delete('xuan_mixloan_inviter', array("uid" => $_GPC["id"]));
    pdo_delete('xuan_mixloan_payment', array("uid" => $_GPC["id"]));
    message("删除成功", $this->createWebUrl('member'), 'success');
} else if ($operation == 'agent') {
    //设为代理
    $res = m('member')->checkAgent($_GPC['id'], $config);
    if ($res['code'] == 1) {
        message("此会员已经是代理，取消代理可以去“代理会员”取消", "", "error");
    }
    $insert = array(
            "uniacid"=>$_W["uniacid"],
            "uid"=>$_GPC['id'],
            "createtime"=>time(),
            "tid"=>"20001" . date('YmdHis', time()),
            "fee"=>0,
    );
    pdo_insert("xuan_mixloan_payment",$insert);
    message("设置成功", $this->createWebUrl('member'), "success");
} else if ($operation == 'send_msg') {
    //发送信息
    if ($_GPC['post'] == 1) {
        $msg = $_GPC['msg'];
        $url = $_GPC['url'];
        $members = pdo_fetchall("select b.openid from ".tablename('xuan_mixloan_payment').' a left join '. tablename('xuan_mixloan_member').' b on a.uid=b.id where a.msg=1 and a.uniacid=:uniacid group by a.uid', [':uniacid'=>$_W['uniacid']]);
        foreach ($members as $member) {
            sendCustomNotice($member['openid'], $msg, $url, $account);
        }
        message('发送成功', '', 'success');
    }
} else if ($operation == 'update') {
    $id = $_GPC['id'];
    $member = pdo_fetch("select * from ".tablename("xuan_mixloan_member")." where id={$id}");
    if ($_GPC['post'] == 1) {
        pdo_update("xuan_mixloan_member", $_GPC['data'], array("id"=>$id));
        message('更新成功', $this->createWebUrl('member'), 'success');
    }
}
include $this->template('member');
?>