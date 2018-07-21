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
    if (!empty($_GPC['id'])) {
        $wheres.= " AND id='{$_GPC['id']}'";
    }
    if (!empty($_GPC['nickname'])) {
        $wheres.= " AND nickname LIKE '%{$_GPC['nickname']}%'";
    }
    if (!empty($_GPC['time'])) {
        $starttime = $_GPC['time']['start'];
        $endtime = $_GPC['time']['end'];
        $start = strtotime($starttime);
        $end = strtotime($endtime);
        $wheres .= " and createtime>{$start} and createtime<={$end}";
    } else {
        $starttime = date('Y-m');
        $endtime = date('Y-m-d H:i:s');
    }
    $sql = 'select * from ' . tablename('xuan_mixloan_member') . " where uniacid={$_W['uniacid']} "  . $wheres . ' ORDER BY ID DESC';
    if ($_GPC['export'] != 1) {
        $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql);
        foreach ($list as &$row) {
            $row['type'] = m('member')->checkAgent($row['id'])['code'];
        }
        unset($row);
    } else {
        $list = pdo_fetchall($sql);
        foreach ($list as &$row)
        {
            if(strpos($row['nickname'],'=') === 0){
                $row['nickname'] = "'" . $row['nickname'];
            }
            $row['agent_name'] = m('member')->checkAgent($row['id'])['name'];
            $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
            $row['id'] = "1000" . $row['id'];
        }
        unset($row);
        m('excel')->export($list, array(
            "title" => "会员资料",
            "columns" => array(
                array(
                    'title' => '会员id',
                    'field' => 'id',
                    'width' => 10
                ),
                array(
                    'title' => '昵称',
                    'field' => 'nickname',
                    'width' => 30
                ),
                array(
                    'title' => '真实姓名',
                    'field' => 'realname',
                    'width' => 20
                ),
                array(
                    'title' => '身份证',
                    'field' => 'certno',
                    'width' => 30
                ),
                array(
                    'title' => '手机号',
                    'field' => 'phone',
                    'width' => 20
                ),
                array(
                    'title' => '身份',
                    'field' => 'agent_name',
                    'width' => 12
                ),
                array(
                    'title' => '注册时间',
                    'field' => 'createtime',
                    'width' => 30
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
    message("删除成功");
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
    $inviter = m('member')->getInviter($member['phone']);
    if ($inviter && $config['inviter_fee_one']) {
        $insert_i = array(
            'uniacid' => $_W['uniacid'],
            'uid' => $member['id'],
            'phone' => $member['phone'],
            'certno' => $member['certno'],
            'realname' => $member['realname'],
            'inviter' => $inviter,
            'extra_bonus'=>0,
            'done_bonus'=>0,
            're_bonus'=>$config['inviter_fee_one'],
            'status'=>2,
            'createtime'=>time()
        );
        pdo_insert('xuan_mixloan_product_apply', $insert_i);
        //模板消息提醒
        $one_openid = m('user')->getOpenid($inviter);
        $datam = array(
            "first" => array(
                "value" => "您好，您的徒弟{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
                "color" => "#173177"
            ) ,
            "order" => array(
                "value" => $tid,
                "color" => "#173177"
            ) ,
            "money" => array(
                "value" => $config['inviter_fee_one'],
                "color" => "#173177"
            ) ,
            "remark" => array(
                "value" => '点击查看详情',
                "color" => "#4a5077"
            ) ,
        );
        $account = WeAccount::create($_W['acid']);
        $account->sendTplNotice($one_openid, $config['tpl_notice5'], $datam, $url);
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
}
include $this->template('member');
?>