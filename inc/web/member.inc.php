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
    $wheres = ' AND status!=-1';
    if (!empty($_GPC['id'])) {
        $wheres.= " AND id='{$_GPC['id']}'";
    }
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
            $row['type'] = m('member')->checkAgent($row['id'])['code'];
            $row['user_type'] = m('member')->checkAgent($row['id'])['name'];
        }
        unset($row);
    } else {
        $list = pdo_fetchall($sql);
        m('excel')->export($list, array("title" => "会员数据-" . date('Y-m-d-H-i', time()), "columns" => array(array('title' => '昵称', 'field' => 'nickname', 'width' => 12), array('title' => '姓名', 'field' => 'realname', 'width' => 12), array('title' => '昵称', 'field' => 'nickname', 'width' => 12),)));
    }
    $total = pdo_fetchcolumn( 'select count(1) from ' . tablename('xuan_mixloan_member') . "where uniacid={$_W['uniacid']} "  . $wheres . ' ORDER BY ID DESC' );
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    $member = m('member')->getMember($_GPC['id']);
    pdo_update('xuan_mixloan_member', array("status" => -1, 'openid'=>'', 'uid'=>0), array('id'=>$_GPC['id']));
    pdo_delete('xuan_mixloan_inviter', array("phone" => $member["phone"]));
    pdo_delete('xuan_mixloan_inviter', array("uid" => $_GPC["id"]));
    pdo_delete('xuan_mixloan_payment', array("uid" => $_GPC["id"]));
    message("删除成功", $this->createWebUrl('member'), 'success');
} else if ($operation == 'set_agent') {
    //设为代理
    $res = m('member')->checkAgent($_GPC['id']);
    if ($res['code'] == 1) {
        message("此会员已经是代理，取消代理可以去“代理会员”取消", "", "error");
    }
    $member = pdo_fetch('select * from '.tablename('xuan_mixloan_member').'
        where id=:id', array(':id'=>$_GPC['id']));
    $params['tid'] = "20001" . date('YmdHis', time());
    $insert = array(
            "uniacid"=>$_W["uniacid"],
            "uid"=>$_GPC['id'],
            "createtime"=>time(),
            "tid"=>$params['tid'],
            "fee"=>0,
    );
    pdo_insert("xuan_mixloan_payment",$insert);
    pdo_update("xuan_mixloan_member", array('level'=>1), array('id'=>$_GPC['id']));
    //模板消息提醒
    $account = WeAccount::create($_W['acid']);
    $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
    $datam = array(
        "first" => array(
            "value" => "您好，您已购买成功",
            "color" => "#173177"
        ) ,
        "name" => array(
            "value" => "{$config['title']}代理会员",
            "color" => "#173177"
        ) ,
        "remark" => array(
            "value" => '点击查看详情',
            "color" => "#4a5077"
        ) ,
    );
    $account->sendTplNotice($member['openid'], $config['tpl_notice2'], $datam, $url);

    message("设置成功", $this->createWebUrl('member'), "success");
} else if ($operation == 'agent') {
    //设为代理
    $res = m('member')->checkAgent($_GPC['id']);
    if ($res['code'] == 1) {
        message("此会员已经是代理，取消代理可以去“代理会员”取消", "", "error");
    }
    $member = pdo_fetch('select * from '.tablename('xuan_mixloan_member').'
        where id=:id', array(':id'=>$_GPC['id']));
    $params['tid'] = "20001" . date('YmdHis', time());
    $insert = array(
            "uniacid"=>$_W["uniacid"],
            "uid"=>$_GPC['id'],
            "createtime"=>time(),
            "tid"=>$params['tid'],
            "fee"=>0,
    );
    pdo_insert("xuan_mixloan_payment",$insert);
    pdo_update("xuan_mixloan_member", array('level'=>2), array('id'=>$_GPC['id']));
    $fee = $config['buy_mid_vip_price'];
    //模板消息提醒
    $account = WeAccount::create($_W['acid']);
    $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
    $datam = array(
        "first" => array(
            "value" => "您好，您已购买成功",
            "color" => "#173177"
        ) ,
        "name" => array(
            "value" => "{$config['title']}代理会员",
            "color" => "#173177"
        ) ,
        "remark" => array(
            "value" => '点击查看详情',
            "color" => "#4a5077"
        ) ,
    );
    $account->sendTplNotice($openid, $config['tpl_notice2'], $datam, $url);
    //$inviter = m('member')->getInviter($member['phone'], $openid);
    // $man = m('member')->getInviterInfo($inviter);
    // if ($inviter && $config['inviter_fee_one']) {
    //     $insert_i = array(
    //         'uniacid' => $_W['uniacid'],
    //         'uid' => $member['id'],
    //         'phone' => $member['phone'],
    //         'certno' => $member['certno'],
    //         'realname' => $member['realname'],
    //         'inviter' => $inviter,
    //         'extra_bonus'=>0,
    //         'done_bonus'=>0,
    //         're_bonus'=>$config['inviter_fee_one']*$fee*0.01,
    //         'status'=>2,
    //         'createtime'=>time(),
    //         'degree'=>1,
    //         'type'=>2
    //     );
    //     pdo_insert('xuan_mixloan_bonus', $insert_i);
    //     $datam = array(
    //         "first" => array(
    //             "value" => "您好，您的徒弟{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
    //             "color" => "#173177"
    //         ) ,
    //         "order" => array(
    //             "value" => $params['tid'],
    //             "color" => "#173177"
    //         ) ,
    //         "money" => array(
    //             "value" => $config['inviter_fee_one']*$fee*0.01,
    //             "color" => "#173177"
    //         ) ,
    //         "remark" => array(
    //             "value" => '点击查看详情',
    //             "color" => "#4a5077"
    //         ) ,
    //     );
    //     $account->sendTplNotice($man['openid'], $config['tpl_notice5'], $datam, $url);
    //     //二级
    //     $man = m('member')->getInviterInfo($inviter);
    //     $inviter = m('member')->getInviter($man['phone'], $man['openid']);
    //     if ($inviter && $config['inviter_fee_two']) {
    //         $insert_i = array(
    //             'uniacid' => $_W['uniacid'],
    //             'uid' => $member['id'],
    //             'phone' => $member['phone'],
    //             'certno' => $member['certno'],
    //             'realname' => $member['realname'],
    //             'inviter' => $inviter,
    //             'extra_bonus'=>0,
    //             'done_bonus'=>0,
    //             're_bonus'=>$config['inviter_fee_two']*$fee*0.01,
    //             'status'=>2,
    //             'createtime'=>time(),
    //             'degree'=>2,
    //             'type'=>2
    //         );
    //         pdo_insert('xuan_mixloan_bonus', $insert_i);
    //         $datam = array(
    //             "first" => array(
    //                 "value" => "您好，您的徒弟{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
    //                 "color" => "#173177"
    //             ) ,
    //             "order" => array(
    //                 "value" => $params['tid'],
    //                 "color" => "#173177"
    //             ) ,
    //             "money" => array(
    //                 "value" => $config['inviter_fee_two']*$fee*0.01,
    //                 "color" => "#173177"
    //             ) ,
    //             "remark" => array(
    //                 "value" => '点击查看详情',
    //                 "color" => "#4a5077"
    //             ) ,
    //         );
    //         $account->sendTplNotice($man['openid'], $config['tpl_notice5'], $datam, $url);
    //         //三级
    //         $man = m('member')->getInviterInfo($inviter);
    //         $inviter = m('member')->getInviter($man['phone'], $man['openid']);
    //         if ($inviter && $config['inviter_fee_three']) {
    //             $insert_i = array(
    //                 'uniacid' => $_W['uniacid'],
    //                 'uid' => $member['id'],
    //                 'phone' => $member['phone'],
    //                 'certno' => $member['certno'],
    //                 'realname' => $member['realname'],
    //                 'inviter' => $inviter,
    //                 'extra_bonus'=>0,
    //                 'done_bonus'=>0,
    //                 're_bonus'=>$config['inviter_fee_three']*$fee*0.01,
    //                 'status'=>2,
    //                 'createtime'=>time(),
    //                 'degree'=>3,
    //                 'type'=>2
    //             );
    //             pdo_insert('xuan_mixloan_bonus', $insert_i);
    //             $datam = array(
    //                 "first" => array(
    //                     "value" => "您好，您的徒弟{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
    //                     "color" => "#173177"
    //                 ) ,
    //                 "order" => array(
    //                     "value" => $params['tid'],
    //                     "color" => "#173177"
    //                 ) ,
    //                 "money" => array(
    //                     "value" => $config['inviter_fee_three']*$fee*0.01,
    //                     "color" => "#173177"
    //                 ) ,
    //                 "remark" => array(
    //                     "value" => '点击查看详情',
    //                     "color" => "#4a5077"
    //                 ) ,
    //             );
    //             $account->sendTplNotice($man['openid'], $config['tpl_notice5'], $datam, $url);
    //         }
    //     }
    // }
    message("设置成功", $this->createWebUrl('member'), "success");
}else if ($operation == 'send_msg') {
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
} else if ($operation == 'send_notice') {
    //发送模板消息，签档提醒
    if ($_GPC['post'] == 1) {
        $first = "尊敬的代理，您好！\n“最新口子”内容已经更新，请订阅查看！";
        $title = $_GPC['title'];
        $author = $_GPC['author'];
        $time = date("Y-m-d H-i");
        $createtime = time();
        $remark = "最新口子已经更新，您可以点击【详情】或打开【代理中心-最新口子】查看今日更多内容\n（如无需订阅，请在个人中心取消订阅）";
        $url = $_GPC['url'];
        $members = pdo_fetchall("SELECT openid FROM `ims_mc_mapping_fans` WHERE uniacid=:uniacid AND follow=1", [':uniacid'=>$_W['uniacid']]);
        foreach ($members as $member) {
            $openid = $member['openid'];
            $datam = array(
                "first" => array(
                    "value" => $first,
                    "color" => "#173177"
                ) ,
                "keyword1" => array(
                    "value" => $title,
                    "color" => "#FF0000"
                ) ,
                "keyword2" => array(
                    "value" => $author,
                    "color" => "#173177"
                ) ,
                "keyword3" => array(
                    "value" => $time,
                    "color" => "#173177"
                ) ,
                "remark" => array(
                    "value" => $remark,
                    "color" => "#A4D3EE"
                ) ,
            );
            $temp = array(
                'uniacid' => $_W['uniacid'],
                'openid' => "'{$openid}'",
                'template_id' => "'{$config['tpl_notice3']}'",
                'data' => "'" . addslashes(json_encode($datam)) . "'",
                'url' => "'{$url}'",
                'createtime'=>$createtime,
                'status'=>0
            );
            $temp_string = '('. implode(',', array_values($temp)) . ')';
            $insert[] = $temp_string;
        }
        if (!empty($insert)) {
            $insert_string =  implode(',', $insert);
            pdo_run("INSERT ".tablename("xuan_mixloan_notice"). " ( `uniacid`, `openid`, `template_id`, `data`, `url`, `createtime`, `status`) VALUES {$insert_string}");
        }
        
        $count = count($insert);
        message("发送成功，总计发送{$count}条，已转入消息发送队列", "", "success");
        
    }
}
include $this->template('member');
?>