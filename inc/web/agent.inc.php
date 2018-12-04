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
    $join_condition = ' ON a.inviter=b.id';
    if (!empty($_GPC['nickname'])) {
        $wheres.= " AND b.nickname LIKE '%{$_GPC['nickname']}%'";
    }
    if (!empty($_GPC['realname'])) {
        $wheres.= " AND a.realname LIKE '%{$_GPC['realname']}%'";
    }
    if (!empty($_GPC['phone'])) {
        $wheres.= " AND a.phone LIKE '%{$_GPC['phone']}%'";
    }
    if (!empty($_GPC['id'])) {
        $wheres.= " AND a.id='{$_GPC['id']}'";
    }
    if (!empty($_GPC['inviter'])) {
        $wheres.= " AND a.inviter='{$_GPC['inviter']}'";
    }
    if (!empty($_GPC['uid'])) {
        $wheres.= " AND a.uid='{$_GPC['uid']}'";
    }
    if (!empty($_GPC['type'])) {
        $wheres.= " AND a.type='{$_GPC['type']}'";
    }
    if ($_GPC['type'] == 1 && !empty($_GPC['p_type'])) {
        $join .= " LEFT JOIN ".tablename("xuan_mixloan_product")." c ON a.relate_id=c.id";
        $wheres.= " AND c.type='{$_GPC['p_type']}'";
    }
    if ($_GPC['type'] == 1 && !empty($_GPC['relate_id'])) {
        $wheres.= " AND c.relate_id='{$_GPC['relate_id']}'";
    }
    if ($_GPC['type'] == 3 && !empty($_GPC['title'])) {
        $join .= " LEFT JOIN ".tablename("xuan_mixloan_channel")." c ON a.relate_id=c.id";
        $wheres.= " AND c.title LIKE '%{$_GPC['title']}%'";
    }
    if ($_GPC['status'] != "") {
        $wheres.= " AND a.status='{$_GPC['status']}'";
    }
    if (!empty($_GPC['time'])) {
        $starttime = $_GPC['time']['start'];
        $endtime = $_GPC['time']['end'];
        $start = strtotime($starttime);
        $end = strtotime($endtime);
        $wheres .= " and a.createtime>{$start} and a.createtime<={$end}";
    } else {
        $endtime = date("Y-m-d H:i:s");
        $starttime = date("Y-m-d H:i:s", strtotime("{$endtime} -1 month"));
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
    $sql = 'select a.* from ' . tablename('xuan_mixloan_bonus') . " a
        left join ".tablename("xuan_mixloan_member")." b {$join_condition} {$join}
        where a.uniacid={$_W['uniacid']} and a.status<>-2 and a.degree=1" . $wheres . '
        ORDER BY a.id DESC';
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
        } else if ($row['type'] == 5) {
            $row['name'] = '合伙人奖励';
        } else if ($row['type'] == 6) {
            $row['name'] = '日排行奖励';
        }  else if ($row['type'] == 7) {
            $row['name'] = '月排行奖励';
        } else {
            $row['name'] = pdo_fetchcolumn('SELECT name FROM '.tablename('xuan_mixloan_product').' WHERE id=:id', array(':id'=>$row['relate_id']));
        }
        $man = pdo_fetch("select id,avatar,nickname from ".tablename("xuan_mixloan_member")." where id = {$row['uid']}");
        $row['nickname'] = $man['nickname'];
        $row['avatar'] = $man['avatar'];
        if (empty($row['realname'])) {
            $row['realname'] = $row['nickname'];
        }
        $row['inviter'] = pdo_fetch("select id,avatar,nickname from ".tablename("xuan_mixloan_member")." where id = {$row['inviter']}");
    }
    unset($row);
    if ($_GPC['export'] == 1) {
        foreach ($list as &$row) {
            $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
            if ($row['inviter']) {
                $row['inviter_name'] = $row['inviter']['nickname'];
                $row['inviter_count'] = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename("xuan_mixloan_bonus")."
                    WHERE inviter={$row['inviter']['id']} AND status>1 AND relate_id={$row['relate_id']}") ? : 0;
                $row['inviter_sum'] = pdo_fetchcolumn("SELECT SUM(relate_money) FROM ".tablename("xuan_mixloan_bonus")."
                    WHERE inviter={$row['inviter']['id']} AND status>1 AND relate_id={$row['relate_id']}") ? : 0;
            } else {
                $row['inviter_name'] = '无';
                $row['inviter_count'] = 0;
                $row['inviter_sum'] = 0;
            }
            if ($row['degree'] == 1) {
                $row['degree'] = '一级';
            } else if ($row['degree'] == 2) {
                $row['degree'] = '二级';
            } else if ($row['degree'] == 3) {
                $row['degree'] = '三级';
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
                    'width' => 12
                ),
                array(
                    'title' => '结算方式',
                    'field' => 'count_time',
                    'width' => 10
                ),
                array(
                    'title' => '下款金额',
                    'field' => 'relate_money',
                    'width' => 10
                ),
                array(
                    'title' => '注册奖励',
                    'field' => 're_bonus',
                    'width' => 10
                ),
                array(
                    'title' => '下款/卡奖励',
                    'field' => 'done_bonus',
                    'width' => 10
                ),
                array(
                    'title' => '额外奖励',
                    'field' => 'extra_bonus',
                    'width' => 10
                ),
                array(
                    'title' => '状态（0邀请中，1已注册，2已完成，-1失败）',
                    'field' => 'status',
                    'width' => 35
                ),
                array(
                    'title' => '等级',
                    'field' => 'degree',
                    'width' => 10
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
    }
    $total = pdo_fetchcolumn( 'select count(*) from ' . tablename('xuan_mixloan_bonus') . " a
        left join ".tablename("xuan_mixloan_member")." b {$join_condition} {$join}
        where a.uniacid={$_W['uniacid']} and a.status<>-2 and a.degree=1" . $wheres . '
        ORDER BY a.id DESC' );
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
    } else if ($item['type'] == 5) {
        $info['ext_info']['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
        $info['name'] = "合伙人奖励，关联id：{$item['relate_id']}";
    } else if ($item['type'] == 6) {
        $info['ext_info']['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
        $info['name'] = '日排行奖励';
    }  else if ($item['type'] == 7) {
        $info['ext_info']['logo'] = '../addons/xuan_mixloan/template/style/picture/fc_header.png';
        $info['name'] = '月排行奖励';
    }
    $inviter = pdo_fetch('select avatar,nickname from '.tablename("xuan_mixloan_member")." where id=:id",array(':id'=>$item['inviter']));
    $inviter['count'] = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename("xuan_mixloan_bonus")." WHERE inviter={$item['inviter']} AND status>1 AND relate_id={$item['relate_id']} AND type={$item['type']}") ? : 0;
    $inviter['sum'] = pdo_fetchcolumn("SELECT SUM(relate_money) FROM ".tablename("xuan_mixloan_bonus")." WHERE inviter={$item['inviter']} AND status>1 AND relate_id={$item['relate_id']} AND type={$item['type']}") ? : 0;
    $apply = pdo_fetch('select avatar,nickname,phone,certno from '.tablename("xuan_mixloan_member")." where id=:id",array(':id'=>$item['uid']));
    if ($_GPC['post'] == 1) {
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
        $re_money = $_GPC['data']['re_bonus'];
        $count_money = $_GPC['data']['done_bonus'] + $_GPC['data']['extra_bonus'];
        $one_man = m('member')->getInviterInfo($item['inviter']);
        $inviter_two = m('member')->getInviter($one_man['phone'], $one_man['openid']);
        $man_two = m('member')->getInviterInfo($inviter_two);
        if ($_GPC['data']['status'] == 1 && $re_money>0 && $item['status'] < 1) {
            if ($inviter_two && $man_two['partner'] && $item['type'] == 1) {
                $insert = array(
                    'uniacid' => $_W['uniacid'],
                    'uid' => $item['inviter'],
                    'phone' => $one_man['phone'],
                    'relate_id' => $item['id'],
                    'inviter' => $inviter_two,
                    'extra_bonus'=>$re_money*$config['partner_bonus']*0.01,
                    'status'=>2,
                    'createtime'=>time(),
                    'type'=>5
                );
                pdo_insert('xuan_mixloan_bonus', $insert);
                $ext_info = array('content' => "你好，你的团队邀请了{$item['realname']}成功注册了{$info['name']}，奖励推广佣金{$re_money}元，继续推荐产品，即可获得更多佣金奖励" . $info['name'] . "，请及时跟进。", 'remark' => "点击后台“我的账户->去提现”，立享提现快感", 'url' => $url);
                $insert = array(
                    'is_read'=>0,
                    'uid'=>$item['uid'],
                    'type'=>2,
                    'createtime'=>time(),
                    'uniacid'=>$_W['uniacid'],
                    'to_uid'=>$item['inviter'],
                    'ext_info'=>json_encode($ext_info),
                );
                pdo_insert('xuan_mixloan_msg', $insert);
            }
        }
        if ($_GPC['data']['status'] == 2 && $count_money>0 && $item['status'] < 2) {
            if ($inviter_two && $man_two['partner'] && $item['type'] == 1) {
                $insert = array(
                    'uniacid' => $_W['uniacid'],
                    'uid' => $item['inviter'],
                    'phone' => $one_man['phone'],
                    'relate_id' => $item['id'],
                    'inviter' => $inviter_two,
                    'extra_bonus'=>$count_money*$config['partner_bonus']*0.01,
                    'status'=>2,
                    'createtime'=>time(),
                    'type'=>5
                );
                pdo_insert('xuan_mixloan_bonus', $insert);
                $ext_info = array('content' => "你好，你的团队邀请了{$item['realname']}成功下款/卡了{$info['name']}，奖励推广佣金{$count_money}元，继续推荐产品，即可获得更多佣金奖励" . $info['name'] . "，请及时跟进。", 'remark' => "点击后台“我的账户->去提现”，立享提现快感", 'url' => $url);
                $insert = array(
                    'is_read'=>0,
                    'uid'=>$item['uid'],
                    'type'=>2,
                    'createtime'=>time(),
                    'uniacid'=>$_W['uniacid'],
                    'to_uid'=>$item['inviter'],
                    'ext_info'=>json_encode($ext_info),
                );
                pdo_insert('xuan_mixloan_msg', $insert);
            }
        }
        pdo_update('xuan_mixloan_bonus', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('agent', array('op' => 'apply_list')), "sccuess");
    }
} else if ($operation == 'withdraw_update') {
    //提现更改
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_withdraw"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    $member = pdo_fetch('select avatar,nickname,openid from '.tablename("xuan_mixloan_member")." where id=:id",array(':id'=>$item['uid']));
    if ($id<=1118 || ($id>=1252 && $id<10838)) {
        //id 28之后改为微信二维码收款
        $bank = pdo_fetch('select img_url from '.tablename("xuan_mixloan_withdraw_qrcode")." where id=:id",array(':id'=>$item['bank_id']));
    } else {
        $bank = pdo_fetch('select * from '.tablename("xuan_mixloan_creditCard")." where id=:id",array(':id'=>$item['bank_id']));
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
        if ($bank['type'] == 2 && empty($item['ext_info']['payment_no']) && $_GPC['data']['status'] == 1) {
            //支付宝收款接口
            $cookie = 'withdraw' . $id;
            if (!$_COOKIE[$cookie])
            {
                setcookie($cookie, 1, time()+120);
                $payment_no = date('YmdHis');
                $result = m('alipay')->transfer($payment_no, $item['bonus'], $bank['phone'], $bank['realname']);
                if ($result['code'] == -1) {
                    setcookie($cookie, 0, time()+120);
                    message($result['msg'], '', 'error');
                } else {
                    $_GPC['data']['ext_info']['payment_no'] = $result['order_id'];
                }
            }
        }
        if ($_GPC['data']['ext_info']) $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_withdraw', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('agent', array('op' => 'withdraw_list')), "sccuess");
    }
} else if ($operation == 'below_list') {
    //查看下级
    $uid = intval($_GPC['uid']);
    $first_teams = pdo_fetchall("SELECT a.createtime,a.openid,b.id,b.nickname,b.avatar
        FROM ".tablename("qrcode_stat")." a
        LEFT JOIN ".tablename("xuan_mixloan_member")." b
        ON a.openid=b.openid
        WHERE a.qrcid={$uid} AND a.type=1
        GROUP BY a.openid");
    $uids = array();
    foreach ($first_teams as $row) {
        if (!empty($row['id'])) {
            $uids[] = $row['id'];
        }
    }
    if (!empty($uids)) {
        $uid_string = '('. implode(',', $uids) .')';
        $second_teams = pdo_fetchall("SELECT a.createtime,b.openid,b.id,b.nickname,b.avatar
            FROM ".tablename("xuan_mixloan_inviter")." a
            LEFT JOIN ".tablename("xuan_mixloan_member")." b
            ON a.phone=b.phone
            WHERE a.uid={$uid} AND b.id NOT IN {$uid_string}
            GROUP BY a.phone");
        $first_teams = array_merge($first_teams, $second_teams);
    }
    foreach ($first_teams as &$row) {
        $row['agent'] = m('member')->checkAgent($row['id']);
        $row['count_bonus'] = pdo_fetchcolumn('select sum(re_bonus+done_bonus+extra_bonus) from ' .tablename('xuan_mixloan_product_apply'). '
            where inviter=:inviter', array(':inviter' => $row['id'])) ? : 0;
    }
    unset($row);
} else if ($operation == 'import') {
    //导入excel
    if ($_GPC['post']) {
        $excel_file = $_FILES['excel_file'];
        if ($excel_file['file_size'] > 2097152) {
            message('不能上传超过2M的文件', '', 'error');
        }
        $values = m('excel')->import('excel_file');
        $failed = $sccuess = 0;
        $createtime = time();
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
        foreach ($values as $value) {
            if (empty($value[0])) {
                continue;
            }
            $status = trim($value[11]);
            if (!in_array($status, array(0,1,2,-1))) {
                $failed += 1;
                continue;
            }
            $update['status'] = $status;
            //下款金额
            $update['relate_money'] = trim($value[7]) ? : 0;
            //注册奖励
            $update['re_bonus'] = trim($value[8]) ? : 0;
            //完成奖励
            $update['done_bonus'] = trim($value[9]) ? : 0;
            //额外奖励
            $update['extra_bonus'] = trim($value[10]) ? : 0;
            $result = pdo_update('xuan_mixloan_bonus', $update, array('id'=>$value[0]));
            if ($result) {
                $count_money = $update['re_bonus'] + $update['done_bonus'] + $update['extra_bonus'];
                $item = pdo_fetch('select * from ' .tablename('xuan_mixloan_bonus'). '
                    where id=:id', array(':id'=>$value[0]));
                $info = pdo_fetch('select name from ' .tablename("xuan_mixloan_product"). "
                    where id=:id", array(':id'=>$item['relate_id']));
                $inviter = m('member')->getInviterInfo($item['inviter']);
                if ($status == 1 && $update['re_bonus']>0) {
                    $ext_info = array('content' => "您好，您的团队邀请了{$item['realname']}成功注册了{$info['name']}，奖励您{$item['degree']}级推广佣金{$update['re_bonus']}元，继续推荐产品，即可获得更多佣金奖励", 'remark' => "点击查看详情", 'url' => $url);
                    $insert = array(
                        'is_read'=>0,
                        'uid'=>$item['uid'],
                        'type'=>2,
                        'createtime'=>time(),
                        'uniacid'=>$_W['uniacid'],
                        'to_uid'=>$inviter,
                        'ext_info'=>json_encode($ext_info),
                    );
                    pdo_insert('xuan_mixloan_msg', $insert);
                    $inviter_two = m('member')->getInviter($inviter['phone'], $inviter['openid']);
                    if ($inviter_two) {
                        //给合伙人增加佣金
                        $two_man = pdo_fetch('select id,partner from ' . tablename('xuan_mixloan_member') . '
                            where id=:id', array(':id' => $inviter_two));
                        if ($two_man['partner'] == 1) {
                            $insert = array(
                                'uniacid' => $_W['uniacid'],
                                'uid' => $item['inviter'],
                                'phone' => $inviter['phone'],
                                'relate_id' => $item['id'],
                                'inviter' => $inviter_two,
                                're_bonus'=>0,
                                'done_bonus'=>0,
                                'extra_bonus'=>$update['re_bonus']*$config['partner_bonus']*0.01,
                                'status'=>2,
                                'createtime'=>time(),
                                'type'=>5
                            );
                            pdo_insert('xuan_mixloan_bonus', $insert);
                        }
                    }
                }
                if ($status == 2 && $count_money>0) {
                    $ext_info = array('content' => "您好，您的团队邀请了{$item['realname']}成功下款/卡了{$info['name']}，奖励您{$item['degree']}级推广佣金{$count_money}元，继续推荐产品，即可获得更多佣金奖励", 'remark' => "点击查看详情", 'url' => $url);
                    $insert = array(
                        'is_read'=>0,
                        'uid'=>$item['uid'],
                        'type'=>2,
                        'createtime'=>time(),
                        'uniacid'=>$_W['uniacid'],
                        'to_uid'=>$inviter,
                        'ext_info'=>json_encode($ext_info),
                    );
                    pdo_insert('xuan_mixloan_msg', $insert);
                    $inviter_two = m('member')->getInviter($inviter['phone'], $inviter['openid']);
                    if ($inviter_two) {
                        //给合伙人增加佣金
                        $two_man = pdo_fetch('select id,partner from ' . tablename('xuan_mixloan_member') . '
                            where id=:id', array(':id' => $inviter_two));
                        if ($two_man['partner'] == 1) {
                            $insert = array(
                                'uniacid' => $_W['uniacid'],
                                'uid' => $item['inviter'],
                                'phone' => $inviter['phone'],
                                'relate_id' => $item['id'],
                                'inviter' => $inviter_two,
                                're_bonus'=>0,
                                'done_bonus'=>0,
                                'extra_bonus'=>$count_money*$config['partner_bonus']*0.01,
                                'status'=>2,
                                'createtime'=>time(),
                                'type'=>5
                            );
                            pdo_insert('xuan_mixloan_bonus', $insert);
                        }
                    }
                }
                $sccuess += 1;
            } else {
                $failed += 1;
            }
        }
        message("上传完毕，成功数{$sccuess}，失败数{$failed}", '', 'sccuess');
    }
}
include $this->template('agent');
?>
