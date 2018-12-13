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
    $status = $_GPC['status'] != '' ? $_GPC['status'] : 1;
    $wheres = ' AND status=' . $status;
    if (!empty($_GPC['id'])) {
        $wheres.= " AND id='{$_GPC['id']}'";
    }
    if (!empty($_GPC['phone'])) {
        $wheres.= " AND phone='{$_GPC['phone']}'";
    }
    if (!empty($_GPC['nickname'])) {
        $wheres.= " AND nickname LIKE '%{$_GPC['nickname']}%'";
    }
    $sql = 'select * from ' . tablename('xuan_mixloan_member') . "where uniacid={$_W['uniacid']} "  . $wheres . ' ORDER BY ID DESC';
    if ($_GPC['export'] != 1) {
        $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql);
        foreach ($list as &$row) {
            $agent = m('member')->checkAgent($row['id']);
            if ($agent['code'] == 1) {
                $partner = m('member')->checkPartner($row['id']);
                if ($partner['code'] == 1) {
                    $row['type'] = 3;
                } else {
                    $row['type'] = 2;
                }
            } else {
                $row['type'] = 1;
            }
        }
        unset($row);
    } else {
        $list = pdo_fetchall($sql);
        m('excel')->export($list, array("title" => "会员数据-" . date('Y-m-d-H-i', time()), "columns" => array(array('title' => '昵称', 'field' => 'nickname', 'width' => 12), array('title' => '姓名', 'field' => 'realname', 'width' => 12), array('title' => '昵称', 'field' => 'nickname', 'width' => 12),)));
    }
    $total = pdo_fetchcolumn( 'select count(1) from ' . tablename('xuan_mixloan_member') . "where uniacid={$_W['uniacid']} "  . $wheres . ' ORDER BY ID DESC' );
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'delete') {
    // $member = m('member')->getMember($_GPC['id']);
    // pdo_update('xuan_mixloan_member', array("status" => -1, 'openid'=>'', 'uid'=>0, 'phone'=>'', 'certno'=>''), array('id'=>$_GPC['id']));
    // pdo_delete('xuan_mixloan_inviter', array("phone" => $member["phone"]));
    // pdo_delete('xuan_mixloan_inviter', array("uid" => $_GPC["id"]));
    // pdo_delete('qrcode_stat', array("qrcid" => $_GPC["id"]));
    // pdo_delete('qrcode_stat', array("openid" => $member['openid']));
    // pdo_delete('xuan_mixloan_payment', array("uid" => $_GPC["id"]));
    pdo_update('xuan_mixloan_member', array('status' => 0), array('id' => $_GPC['id']));
    message("冻结成功", referer(), 'sccuess');
} else if ($operation == 'recovery') {
    // 解冻
    pdo_update('xuan_mixloan_member', array('status' => 1), array('id' => $_GPC['id']));
    message("解冻成功", referer(), 'sccuess');
} else if ($operation == 'true_delete') {
    // 永久删除
    $member = m('member')->getMember($_GPC['id']);
    pdo_update('xuan_mixloan_member', array("status" => -1, 'openid'=>'', 'uid'=>0, 'phone'=>'', 'certno'=>''), array('id'=>$_GPC['id']));
    pdo_delete('xuan_mixloan_inviter', array("phone" => $member["phone"]));
    pdo_delete('xuan_mixloan_inviter', array("uid" => $_GPC["id"]));
    pdo_delete('qrcode_stat', array("qrcid" => $_GPC["id"]));
    pdo_delete('qrcode_stat', array("openid" => $member['openid']));
    pdo_delete('xuan_mixloan_payment', array("uid" => $_GPC["id"]));
} else if ($operation == 'agent') {
    //设为代理
    $res = m('member')->checkAgent($_GPC['id']);
    if ($res['code'] == 1) {
        message("此会员已经是代理，取消代理可以去“代理会员”取消", "", "error");
    }
    $tid = "20001" . date('YmdHis', time());
    $member = m('member')->getMember($_GPC['id']);
    $insert = array(
            "uniacid"=>$_W["uniacid"],
            "uid"=>$_GPC['id'],
            "createtime"=>time(),
            "tid"=>$tid,
            "fee"=>0,
    );
    pdo_insert("xuan_mixloan_payment",$insert);
    //模板消息提醒
    $datam = array(
        "name" => array(
            "value" => "{$config['title']}代理会员",
            "color" => "#173177"
        ) ,
        "remark" => array(
            "value" => '点击查看详情',
            "color" => "#4a5077"
        ) ,
    );
    $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
    $account = WeAccount::create($_W['acid']);
    $account->sendTplNotice($member['openid'], $config['tpl_notice2'], $datam, $url);
    //一级
    $inviter = m('member')->getInviter($member['phone'], $member['openid']);
    if ($inviter) {
        $re_bonus = $config['inviter_fee_one'];
        if ($re_bonus) {
            $insert_i = array(
                'uniacid' => $_W['uniacid'],
                'uid' => $member['id'],
                'phone' => $member['phone'],
                'certno' => $member['certno'],
                'realname' => $member['realname'],
                'inviter' => $inviter,
                'extra_bonus'=>0,
                'done_bonus'=>0,
                're_bonus'=>$re_bonus,
                'status'=>2,
                'createtime'=>time(),
                'degree'=>1,
            );
            pdo_insert('xuan_mixloan_product_apply', $insert_i);
            $one_insert_id = pdo_insertid();
        }
        //消息提醒
        $ext_info = array('content' => "您好，您的徒弟{$member['nickname']}成功购买了代理会员，奖励您推广佣金" . $re_bonus . "元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", "url" => $salary_url);
        $insert = array(
            'is_read'=>0,
            'uid'=>$member['id'],
            'type'=>2,
            'createtime'=>time(),
            'uniacid'=>$_W['uniacid'],
            'to_uid'=>$inviter,
            'ext_info'=>json_encode($ext_info),
        );
        pdo_insert('xuan_mixloan_msg', $insert);
        //二级
        $man_one = m('member')->getInviterInfo($inviter);
        $inviter_two = m('member')->getInviter($man_one['phone'], $man_one['openid']);
        if ($inviter_two) {
            $re_bonus = $config['inviter_fee_two'];
            if ($re_bonus) {
                $insert_i = array(
                    'uniacid' => $_W['uniacid'],
                    'uid' => $member['id'],
                    'phone' => $member['phone'],
                    'certno' => $member['certno'],
                    'realname' => $member['realname'],
                    'inviter' => $inviter_two,
                    'extra_bonus'=>0,
                    'done_bonus'=>0,
                    're_bonus'=>$re_bonus,
                    'status'=>2,
                    'createtime'=>time(),
                    'degree'=>2
                );
                pdo_insert('xuan_mixloan_product_apply', $insert_i);
            }
            //消息提醒
            $ext_info = array('content' => "您好，您的徒弟{$man_one['nickname']}邀请了{$member['nickname']}成功购买了代理会员，奖励您推广佣金" . $re_bonus . "元，继续推荐代理，即可获得更多佣金奖励", 'remark' => "点击查看详情", "url" => $salary_url);
            $insert = array(
                'is_read'=>0,
                'uid'=>$member['id'],
                'type'=>2,
                'createtime'=>time(),
                'uniacid'=>$_W['uniacid'],
                'to_uid'=>$inviter_two,
                'ext_info'=>json_encode($ext_info),
            );
            pdo_insert('xuan_mixloan_msg', $insert);
        }
    }
    message("设置成功", $this->createWebUrl('member'), "success");
} else if ($operation == 'update') {
    $id = $_GPC['id'];
    $member = pdo_fetch("select * from ".tablename("xuan_mixloan_member")." where id={$id}");
    if ($_GPC['post'] == 1) {
        pdo_update("xuan_mixloan_member", $_GPC['data'], array("id"=>$id));
        message('更新成功', $this->createWebUrl('member'), 'success');
    }
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
} else if ($operation == 'msg') {
    //群发消息
    if ($_GPC['post']) {
        $insert = array();
        $members = pdo_fetchall('select id from ' .tablename('xuan_mixloan_member'). ' where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
        foreach ($members as $member) {
            $ext_info = array('content' => trim($_GPC['content']), 'remark' => trim($_GPC['remark']), 'url' => trim($_GPC['url']));
            $temp = array(
                'is_read'=>0,
                'uid'=>0,
                'createtime'=>time(),
                'uniacid'=>$_W['uniacid'],
                'to_uid'=>$member['id'],
                'ext_info'=>"'" . addslashes(json_encode($ext_info)) . "'",
            );
            $temp_string = '('. implode(',', array_values($temp)) . ')';
            $insert[] = $temp_string;
        }
        if (!empty($insert)) {
            $insert_string =  implode(',', $insert);
            pdo_run("INSERT " .tablename("xuan_mixloan_msg"). " ( `is_read`, `uid`, `createtime`, `uniacid`, `to_uid`, `ext_info`) VALUES {$insert_string}");
            $count = count($insert);
            message("发送成功，总计发送{$count}条", "", "success");
        }
    }
} else if ($operation == 'partner_list') {
    //合伙人
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    if (!empty($_GPC['uid'])) {
        $wheres.= " AND a.uid='{$_GPC['uid']}'";
    }
    if (!empty($_GPC['nickname'])) {
        $wheres.= " AND b.nickname LIKE '%{$_GPC['nickname']}%'";
    }
    $sql = 'select a.*,b.avatar,b.nickname from ' . tablename('xuan_mixloan_partner') . " a 
        left join " . tablename('xuan_mixloan_member') . " b on a.uid=b.id
        where a.uniacid={$_W['uniacid']} "  . $wheres . ' ORDER BY ID DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn( 'select count(*) from ' . tablename('xuan_mixloan_partner') . " a 
        left join " . tablename('xuan_mixloan_member') . " b on a.uid=b.id
        where a.uniacid={$_W['uniacid']} "  . $wheres);
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'partner_delete') {
    //取消合伙人
    pdo_delete('xuan_mixloan_partner', array("id" => $_GPC['id']));
    message("删除成功", referer());
} else if ($operation == 'set_partner') {
    //设置合伙人资格
    $id = intval($_GPC['id']);
    if (empty($id)) {
        message('id为空', '', 'error');
    }
    $partner = m('member')->checkPartner($id);
    if ($partner['code'] != 1) {
        $insert = array(
            'uniacid' => $_W['uniacid'],
            'uid' => $id,
            'createtime' => time(),
            'tid' => "20002" . date('YmdHis', time()),
            'fee' => 0,
        );
        pdo_insert('xuan_mixloan_partner', $insert);
    }
    message("操作成功", $this->createWebUrl('member',array('op'=>'partner_list')), "success");
} else if ($operation == 'below_list') {
    //下级列表
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    if (!empty($_GPC['inviter'])) {
        $wheres.= " AND a.qrcid='{$_GPC['inviter']}'";
    }
    if (!empty($_GPC['phone'])) {
        $wheres.= " AND b.phone LIKE '%{$_GPC['phone']}%'";
    }
    if (!empty($_GPC['nickname'])) {
        $wheres.= " AND b.nickname LIKE '%{$_GPC['nickname']}%'";
    }
    $sql = 'select a.openid,a.createtime,b.id,b.avatar,b.nickname from ' . tablename('qrcode_stat') . " a 
        left join " . tablename('xuan_mixloan_member') . " b on a.openid=b.openid
        where a.type=1 "  . $wheres . ' GROUP BY a.openid ORDER BY a.id DESC';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        $row['agent'] = m('member')->checkAgent($row['id']);
    }
    unset($row);
    $total = pdo_fetchcolumn( 'select count(DISTINCT a.openid) from ' . tablename('qrcode_stat') . " a 
        left join " . tablename('xuan_mixloan_member') . " b on a.openid=b.openid
        where a.type=1 "  . $wheres);
    $pager = pagination($total, $pindex, $psize);
}
include $this->template('member');
?>