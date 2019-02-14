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
    $total = pdo_fetchcolumn( 'select count(1) from ' . tablename('xuan_mixloan_payment') . " a left join ".tablename("xuan_mixloan_member")." b ON a.uid=b.id where a.uniacid={$_W['uniacid']} " . $wheres );
    $pager = pagination($total, $pindex, $psize);
} else if ($operation == 'apply_list') {
    //申请列表
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $wheres = '';
    if (!empty($_GPC['name'])) {
        $wheres.= " AND a.realname LIKE '%{$_GPC['name']}%'";
    }
    if (!empty($_GPC['inviter'])) {
        $wheres.= " AND a.inviter='{$_GPC['inviter']}'";
    }
    if (!empty($_GPC['degree'])) {
        $wheres.= " AND a.degree='{$_GPC['degree']}'";
    }
    if (!empty($_GPC['ip'])) {
        $wheres.= " AND a.ip='{$_GPC['ip']}'";
    }
    if ($_GPC['status'] != "") {
        $wheres.= " AND a.status='{$_GPC['status']}'";
    }
    if (!empty($_GPC['broswer_type'])) {
        $wheres.= " AND a.broswer_type='{$_GPC['broswer_type']}'";
    }
    if (!empty($_GPC['device_type'])) {
        $wheres.= " AND a.device_type='{$_GPC['device_type']}'";
    }
    if (!empty($_GPC['pro_name'])) {
        $pros = pdo_fetchall('select id from ' . tablename('xuan_mixloan_product') . "
             where name LIKE '%{$_GPC['pro_name']}%'");
        foreach ($pros as $value) {
            $relate_id[] = $value['id'];
        }
        if (!empty($relate_id)) {
            $relate_id = implode(',', $relate_id);
            $wheres .= " AND a.pid in ({$relate_id})";
        }
    }
    $is_fake = intval($_GPC['is_fake']);
    if ($is_fake) {
        if ($is_fake == -1) {
            $wheres .= " and a.is_fake=0";
        } else {
            $wheres .= " and a.is_fake=1";
        }
    }
    if (!empty($_GPC['time'])) {
        $starttime = $_GPC['time']['start'];
        $endtime = $_GPC['time']['end'];
        $start = strtotime($starttime);
        $end = strtotime($endtime);
        $wheres .= " and a.createtime>{$start} and a.createtime<={$end}";
    } else {
        $starttime = date('Y-m');
        $endtime = date('Y-m-d');
    }
    $sql = 'select a.* from ' . tablename('xuan_mixloan_product_apply') . " a
            where 1 {$wheres} ORDER BY a.id DESC";
    if ($_GPC['export'] != 1) {
        $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    }
    $list = pdo_fetchall($sql);
    foreach ($list as &$row) {
        if ($row['type'] == 2) {
            $row['realname'] = pdo_fetchcolumn('SELECT nickname FROM '.tablename('xuan_mixloan_member').'
                WHERE id=:id', array(':id'=>$row['uid']));
            $row['name'] = '邀请购买代理';
        } else if ($row['type'] == 3) {
            $row['name'] = '合伙人分佣';
        } else if ($row['type'] == 1) {
            $pro = pdo_fetch('SELECT name,count_time FROM '.tablename('xuan_mixloan_product').'
                WHERE id=:id', array(':id'=>$row['pid']));
            $row['name'] = $pro['name'];
            $row['count_time'] = $pro['count_time'];
        }
        $row['nickname'] = pdo_fetchcolumn('SELECT nickname FROM '.tablename('xuan_mixloan_member').'
                WHERE id=:id', array(':id'=>$row['uid']));
        $row['inviter'] = pdo_fetch("select id,avatar,nickname from ".tablename("xuan_mixloan_member")." where id = {$row['inviter']}");
        if ($row['device_type'] == 1){
            $row['identification'] = '安卓';
        } else if ($row['device_type'] == 2) {
            $row['identification'] = '苹果';
        } else if ($row['device_type'] == 3) {
            $row['identification'] = 'windows';
        } else {
            $row['identification'] = '未知';
        }
        if ($row['browser_type'] == 1) {
            $row['identification'] .= '|微信';
        } else if ($row['browser_type'] == 2) {
            $row['identification'] .= '|浏览器';
        } else {
            $row['identification'] .= '|未知';
        }
    }
    unset($row);
    if ($_GPC['export'] == 1) {
        foreach ($list as &$row) {
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
                array(
                    'title' => '申请ip',
                    'field' => 'ip',
                    'width' => 30
                ),
                array(
                    'title' => '申请标识',
                    'field' => 'identification',
                    'width' => 30
                ),
            )
        ));
        unset($row);
    }
    $total = pdo_fetchcolumn( 'select count(1) from ' . tablename('xuan_mixloan_product_apply') . " a
            where 1 {$wheres} ORDER BY a.id DESC");
    $pager = pagination($total, $pindex, $psize);
    // 通过率
    $apply_count = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_product_apply') . " a
                    where 1 and a.status>=1" . $wheres) ? : 0;
    $apply_rate = round($apply_count / $total * 100, 2);
    // 下款率
    $pass_count = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_product_apply') . " a
                    where 1 and a.status=2" . $wheres) ? : 0;
    $pass_rate = round($pass_count / $total * 100, 2);
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
        $row['left_bonus'] = round($row['left_bonus'], 2);
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
    if ($_GPC['post']) {
        $item = pdo_fetch('select bonus,uid from ' . tablename('xuan_mixloan_withdraw') . '
             where id=:id', array(':id' => $_GPC['id']));
        $insert = array();
        $insert['uid'] = $item['uid'];
        $insert['money'] = $item['bonus'];
        $insert['reason'] = $_GPC['reason'];
        $insert['createtime'] = time();
        $insert['is_read'] = 0;
        pdo_insert('xuan_mixloan_withdraw_delete', $insert);
        pdo_delete('xuan_mixloan_withdraw', array("id" => $_GPC["id"]));
        message("提交成功", $this->createWebUrl('agent', array('op' => 'withdraw_list')), "sccuess");
    }
} else if ($operation == 'apply_update') {
    //申请编辑
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_product_apply"). " where id={$id}");
    if ($item['pid']) {
        $info = pdo_fetch('select * from '.tablename("xuan_mixloan_product")." where id=:id", array(':id'=>$item['pid']));
        $info['ext_info'] = json_decode($info['ext_info'], true);
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
    } else {
        $info['name'] = '邀请购买代理奖励';
    }
    $inviter = pdo_fetch('select avatar,nickname from '.tablename("xuan_mixloan_member")." where id=:id",array(':id'=>$item['inviter']));
    $inviter['count'] = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$item['inviter']} AND status>1 AND pid={$item['pid']}") ? : 0;
    $inviter['sum'] = pdo_fetchcolumn("SELECT SUM(relate_money) FROM ".tablename("xuan_mixloan_product_apply")." WHERE inviter={$item['inviter']} AND status>1 AND pid={$item['pid']}") ? : 0;
    $apply = pdo_fetch('select avatar,nickname,phone,certno from '.tablename("xuan_mixloan_member")." where id=:id",array(':id'=>$item['uid']));
    if ($_GPC['post'] == 1) {
        $re_money = $_GPC['data']['re_bonus'];
        $count_money = $_GPC['data']['done_bonus'] + $_GPC['data']['extra_bonus'];
        $one_man = m('member')->getInviterInfo($item['inviter']);
        $inviter_two = m('member')->getInviter($one_man['phone'], $one_man['openid']);
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
        $account = WeAccount::create($_W['acid']);
        if ($_GPC['data']['status'] == 1 && $re_money>0) {
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
            if ($inviter_two) {
                //给合伙人增加佣金
                $partner = m('member')->checkPartner($inviter_two);
                if ($partner['code'] == 1) {
                    $insert = array(
                        'uniacid' => $_W['uniacid'],
                        'uid' => $item['inviter'],
                        'phone' => $one_man['phone'],
                        'pid' => $item['id'],
                        'inviter' => $inviter_two,
                        're_bonus'=>0,
                        'done_bonus'=>0,
                        'extra_bonus'=>$re_money*$config['partner_bonus']*0.01,
                        'status'=>2,
                        'createtime'=>time(),
                        'type'=>3
                    );
                    pdo_insert('xuan_mixloan_product_apply', $insert);
                }
            }
        }
        if ($_GPC['data']['status'] == 2 && $count_money>0) {
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
            if ($inviter_two) {
                //给合伙人增加佣金
                $partner = m('member')->checkPartner($inviter_two);
                if ($partner['code'] == 1) {
                    $insert = array(
                        'uniacid' => $_W['uniacid'],
                        'uid' => $item['inviter'],
                        'phone' => $one_man['phone'],
                        'pid' => $item['id'],
                        'inviter' => $inviter_two,
                        're_bonus'=>0,
                        'done_bonus'=>0,
                        'extra_bonus'=>$count_money*$config['partner_bonus']*0.01,
                        'status'=>2,
                        'createtime'=>time(),
                        'type'=>3
                    );
                    pdo_insert('xuan_mixloan_product_apply', $insert);
                }
            }
        }
        if ($ext_info && $item['degree'] == 1 && $item['pid'] > 0) {
            //自动给二级打款
            $second_item = pdo_fetch('select id,inviter from ' . tablename('xuan_mixloan_product_apply') . '
                        where pid=:pid and phone=:phone and degree=2', array(':pid' => $item['pid'], ':phone' => $item['phone']));
            if ($second_item) {
                if ($info['done_reward_type'] == 1) {
                    $done_bonus = $info['ext_info']['done_two_init_reward_money'];
                } else if ($info['done_reward_type'] == 2) {
                    $done_bonus = ($info['ext_info']['done_two_init_reward_per'] / $info['ext_info']['done_one_init_reward_per']) * $_GPC['data']['done_bonus'];
                    $done_bonus = round($done_bonus, 2);
                } else {
                    $done_bonus = 0;
                }
                if ($info['re_reward_type'] == 1) {
                    $re_bonus = $info['ext_info']['re_two_init_reward_money'];
                } else if ($info['re_reward_type'] == 2) {
                    $re_bonus = ($info['ext_info']['re_two_init_reward_per'] / $info['re_one_init_reward_per']) * $_GPC['data']['re_bonus'];
                    $re_bonus = round($re_bonus, 2);
                } else {
                    $re_bonus = 0;
                }
                $second_update['relate_money'] = $_GPC['data']['relate_money'];
                if ($done_bonus) {
                    $second_update['done_bonus'] = $done_bonus;
                }
                if ($re_bonus) {
                    $second_update['re_bonus'] = $re_bonus;
                }
                $second_update['status'] = $_GPC['data']['status'];
                pdo_update('xuan_mixloan_product_apply', $second_update, array('id'=>$second_item['id']));
                $two_man = m('member')->getInviterInfo($second_item['inviter']);
                $inviter_thr = m('member')->getInviter($two_man['phone'], $two_man['openid']);
                if ($inviter_thr) {
                    //合伙人分佣
                    $partner_bonus = 0;
                    if ($second_update['re_bonus']) {
                        $partner_bonus += $second_update['re_bonus']*0.01*$config['partner_bonus'];
                    }
                    if ($second_update['done_bonus']) {
                        $partner_bonus += $second_update['done_bonus']*0.01*$config['partner_bonus'];
                    }
                    if ($partner_bonus) {
                        $partner = m('member')->checkPartner($inviter_thr);
                        if ($partner['code'] == 1) {
                            $insert_i = array(
                                'uniacid' => $_W['uniacid'],
                                'uid' => $second_item['inviter'],
                                'phone' => $two_man['phone'],
                                'inviter' => $inviter_thr,
                                'extra_bonus'=>$partner_bonus,
                                'status'=>2,
                                'pid'=>$second_item['id'],
                                'createtime'=>time(),
                                'degree'=>1,
                                'type'=>3
                            );
                            pdo_insert('xuan_mixloan_product_apply', $insert_i);
                        }
                    }
                }
            }
        }
        pdo_update('xuan_mixloan_product_apply', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", $this->createWebUrl('agent', array('op' => 'apply_list')), "sccuess");
    }
} else if ($operation == 'withdraw_update') {
    //提现更改
    $id = intval($_GPC['id']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_withdraw"). " where id={$id}");
    $item['ext_info'] = json_decode($item['ext_info'], true);
    $member = pdo_fetch('select avatar,nickname from '.tablename("xuan_mixloan_member")." where id=:id",array(':id'=>$item['uid']));
    if ($id < 11225) {
        //id 42之后改为微信二维码收款
        $bank = pdo_fetch('select img_url from '.tablename("xuan_mixloan_withdraw_qrcode")." where id=:id",array(':id'=>$item['bank_id']));
    } else {
        $bank = pdo_fetch('select realname,phone,type from '.tablename("xuan_mixloan_creditCard")." where id=:id",array(':id'=>$item['bank_id']));
    }
    if ($_GPC['post'] == 1) {
        if ($bank['type'] == 2 && empty($item['ext_info']['payment_no']) && $_GPC['data']['status'] == 1) {
            //支付宝收款接口
            $redis = redis();
            $key = 'withdraw' . $id;
            if (!$redis->get($key))
            {
                $redis->set($key, 1);
                $payment_no = date('YmdHis');
                $result = m('alipay')->transfer($payment_no, $item['bonus'], $bank['phone'], $bank['realname']);
                if ($result['code'] == -1) {
                    $redis->set($key, 0);
                    message($result['msg'], '', 'error');
                } else {
                    $_GPC['data']['ext_info']['payment_no'] = $result['order_id'];
                }
            }
        }
        if ($_GPC['data']['ext_info']) $_GPC['data']['ext_info'] = json_encode($_GPC['data']['ext_info']);
        pdo_update('xuan_mixloan_withdraw', $_GPC['data'], array('id'=>$item['id']));
        message("提交成功", referer(), "sccuess");
    }
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
            $result = pdo_update('xuan_mixloan_product_apply', $update, array('id'=>$value[0]));
            if ($result) {
                $count_money = $update['re_bonus'] + $update['done_bonus'] + $update['extra_bonus'];
                $item = pdo_fetch('select * from ' .tablename('xuan_mixloan_product_apply'). '
                    where id=:id', array(':id'=>$value[0]));
                $info = pdo_fetch('select name,done_reward_type,re_reward_type,ext_info from ' .tablename("xuan_mixloan_product"). "
                    where id=:id", array(':id'=>$item['pid']));
                $info['ext_info'] = json_decode($info['ext_info'], 1);
                $inviter = m('member')->getInviterInfo($item['inviter']);
                if ($status == 1 && $update['re_bonus']>0) {
                    $ext_info = array('content' => "您好，您的团队邀请了{$item['realname']}成功注册了{$info['name']}，奖励您{$item['degree']}级推广佣金{$update['re_bonus']}元，继续推荐产品，即可获得更多佣金奖励", 'remark' => "点击查看详情", 'url' => $url);
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
                    $inviter_two = m('member')->getInviter($inviter['phone'], $inviter['openid']);
                    if ($inviter_two) {
                        //给合伙人增加佣金
                        $partner = m('member')->checkPartner($inviter_two);
                        if ($partner['code'] == 1) {
                            $insert = array(
                                'uniacid' => $_W['uniacid'],
                                'uid' => $item['inviter'],
                                'phone' => $inviter['phone'],
                                'pid' => $item['id'],
                                'inviter' => $inviter_two,
                                're_bonus'=>0,
                                'done_bonus'=>0,
                                'extra_bonus'=>$update['re_bonus']*$config['partner_bonus']*0.01,
                                'status'=>2,
                                'createtime'=>time(),
                                'type'=>3
                            );
                            pdo_insert('xuan_mixloan_product_apply', $insert);
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
                        'to_uid'=>$item['inviter'],
                        'ext_info'=>json_encode($ext_info),
                    );
                    pdo_insert('xuan_mixloan_msg', $insert);
                    $inviter_two = m('member')->getInviter($inviter['phone'], $inviter['openid']);
                    if ($inviter_two) {
                        //给合伙人增加佣金
                        $partner = m('member')->checkPartner($inviter_two);
                        if ($partner['code'] == 1) {
                            $insert = array(
                                'uniacid' => $_W['uniacid'],
                                'uid' => $item['inviter'],
                                'phone' => $inviter['phone'],
                                'pid' => $item['id'],
                                'inviter' => $inviter_two,
                                're_bonus'=>0,
                                'done_bonus'=>0,
                                'extra_bonus'=>$count_money*$config['partner_bonus']*0.01,
                                'status'=>2,
                                'createtime'=>time(),
                                'type'=>3
                            );
                            pdo_insert('xuan_mixloan_product_apply', $insert);
                        }
                    }
                }
                if ($status > 0 && $item['degree'] == 1 && $item['pid'] > 0) {
                    //自动给二级打款
                    // $second_item = pdo_fetch('select id,inviter from ' . tablename('xuan_mixloan_product_apply') . '
                    //             where pid=:pid and phone=:phone and degree=2', array(':pid' => $item['pid'], ':phone' => $item['phone']));
                    // if ($second_item) {
                    //     if ($info['done_reward_type'] == 1) {
                    //         $done_bonus = $info['ext_info']['done_two_init_reward_money'];
                    //     } else if ($info['done_reward_type'] == 2) {
                    //         $done_bonus = ($info['ext_info']['done_two_init_reward_per'] / $info['ext_info']['done_one_init_reward_per']) * $update['done_bonus'];
                    //         $done_bonus = round($done_bonus, 2);
                    //     } else {
                    //         $done_bonus = 0;
                    //     }
                    //     if ($info['re_reward_type'] == 1) {
                    //         $re_bonus = $info['ext_info']['re_two_init_reward_money'];
                    //     } else if ($info['re_reward_type'] == 2) {
                    //         $re_bonus = ($info['ext_info']['re_two_init_reward_per'] / $info['re_one_init_reward_per']) * $update['re_bonus'];
                    //         $re_bonus = round($re_bonus, 2);
                    //     } else {
                    //         $re_bonus = 0;
                    //     }
                    //     $second_update['relate_money'] = trim($value[7]);
                    //     if ($done_bonus) {
                    //         $second_update['done_bonus'] = $done_bonus;
                    //     }
                    //     if ($re_bonus) {
                    //         $second_update['re_bonus'] = $re_bonus;
                    //     }
                    //     $second_update['status'] = $status;
                    //     pdo_update('xuan_mixloan_product_apply', $second_update, array('id'=>$second_item['id']));
                    //     $two_man = m('member')->getInviterInfo($second_item['inviter']);
                    //     $inviter_thr = m('member')->getInviter($two_man['phone'], $two_man['openid']);
                    //     if ($inviter_thr) {
                    //         //合伙人分佣
                    //         $partner_bonus = 0;
                    //         if ($second_update['re_bonus']) {
                    //             $partner_bonus += $second_update['re_bonus']*0.01*$config['partner_bonus'];
                    //         }
                    //         if ($second_update['done_bonus']) {
                    //             $partner_bonus += $second_update['done_bonus']*0.01*$config['partner_bonus'];
                    //         }
                    //         if ($partner_bonus) {
                    //             $partner = m('member')->checkPartner($inviter_thr);
                    //             if ($partner['code'] == 1) {
                    //                 $insert_i = array(
                    //                     'uniacid' => $_W['uniacid'],
                    //                     'uid' => $second_item['inviter'],
                    //                     'phone' => $two_man['phone'],
                    //                     'inviter' => $inviter_thr,
                    //                     'extra_bonus'=>$partner_bonus,
                    //                     'status'=>2,
                    //                     'pid'=>$second_item['id'],
                    //                     'createtime'=>time(),
                    //                     'degree'=>1,
                    //                     'type'=>3
                    //                 );
                    //                 pdo_insert('xuan_mixloan_product_apply', $insert_i);
                    //             }
                    //         }
                    //     }
                    // }
                }
                $sccuess += 1;
            } else {
                $failed += 1;
            }
        }
        message("上传完毕，成功数{$sccuess}，失败数{$failed}", '', 'sccuess');
    }
} else if ($operation == 'withdraw_operation') {
    // 提现快捷操作
    $id = intval($_GPC['id']);
    $status = intval($_GPC['status']);
    $item = pdo_fetch('select * from '.tablename("xuan_mixloan_withdraw"). "
        where id={$id}");
    $bank = pdo_fetch('select realname,bankname,banknum,phone,type from '.tablename("xuan_mixloan_creditCard")."
        where id=:id",array(':id'=>$item['bank_id']));
    $data['status'] = $status;
    if ($bank['type'] == 1) {
        message('该申请不是支付宝提现', referer(), 'sccuess');
    }
    if ($status == 1) {
        //支付宝收款接口
        $redis = redis();
        $key = 'withdraw' . $id;
        if (!$redis->get($key))
        {
            $redis->set($key, 1);
            $payment_no = date('YmdHis');
            $result = m('alipay')->transfer($payment_no, $item['bonus'], $bank['phone'], $bank['realname']);
            if ($result['code'] == -1) {
                message($result['msg'], '', 'error');
            } else {
                $data['ext_info']['payment_no'] = $result['order_id'];
                $data['ext_info'] = json_encode($data['ext_info']);
            }
        }
    }
    pdo_update('xuan_mixloan_withdraw', $data, array('id' => $id));
    message('操作成功', referer(), 'sccuess');
} else if ($operation == 'new_import') {
    // 新导入功能
    if ($_GPC['post']) {
        $excel_file = $_FILES['excel_file'];
        if ($excel_file['file_size'] > 2097152) {
            message('不能上传超过2M的文件', '', 'error');
        }
        $values = m('excel')->import('excel_file');
        $failed = $success = 0;
        foreach ($values as $value)
        {
            $pro_name = trim($value['3']);
            if (!empty($pro_name))
            {
                $product[$pro_name] = $pro_name;
            }
        }
        $products = m('product')->getList([], ['name'=>array_values($product)]);
        foreach ($products as $value)
        {
            $pro_list[$value['name']] = $value;
        }
        foreach ($values as $value)
        {
            $realname = trim($value[1]);
            $phone    = trim($value[2]);
            $pro_name = trim($value[3]);
            if (empty($phone))
            {
                $failed += 1;
                continue;
            }
            if (empty($pro_name))
            {
                $failed += 1;
                continue;
            }
            $relate_key   = substr($phone, 0, 3) . substr($phone, -3);
            $relate_money = trim($value[4]);
            $relate_id    = $pro_list[$pro_name]['id'];
            $product      = $pro_list[$pro_name];
            //一级
            $item_one = pdo_fetch('select * from ' . tablename("xuan_mixloan_product_apply") . "
                where (relate_key={$relate_key} or realname='{$realname}') and pid={$relate_id} and degree=1");
            if (!empty($item_one))
            {
                $update = array();
                $update['status'] = 2;
                if ($product['re_reward_type'] == 1)
                {
                    $update['re_bonus'] = $product['ext_info']['re_one_init_reward_money'];
                }
                else if ($product['re_reward_type'] == 2)
                {
                    $update['re_bonus'] = $product['ext_info']['re_one_init_reward_per'] * $relate_money * 0.01;
                }
                if ($product['done_reward_type'] == 1)
                {
                    $update['done_bonus'] = $product['ext_info']['done_one_init_reward_money'];
                }
                else if ($product['re_reward_type'] == 2)
                {
                    $update['done_bonus'] = $product['ext_info']['done_one_init_reward_per'] * $relate_money * 0.01;
                }
                if (trim($value[5]) != '')
                {
                    $update['re_bonus'] = trim($value[5]);
                }
                if (trim($value[7]) != '')
                {
                    $update['done_bonus'] = trim($value[7]);
                }
                $update['relate_money'] = intval($relate_money);
                $result = pdo_update('xuan_mixloan_product_apply', $update, array('id'=>$item_one['id']));
                if ($result) {
                    $success += 1;
                } else {
                    $failed += 1;
                }
            }
            $item_two = pdo_fetch('select * from ' . tablename("xuan_mixloan_product_apply") . "
                where relate_key={$relate_key} and pid={$relate_id} and degree=2");
            if (!empty($item_two))
            {
                $update = array();
                $update['status'] = 2;
                if ($product['re_reward_type'] == 1)
                {
                    $update['re_bonus'] = $product['ext_info']['re_two_init_reward_money'];
                }
                else if ($product['re_reward_type'] == 2)
                {
                    $update['re_bonus'] = $product['ext_info']['re_two_init_reward_per'] * $relate_money * 0.01;
                }
                if ($product['done_reward_type'] == 1)
                {
                    $update['done_bonus'] = $product['ext_info']['done_two_init_reward_money'];
                }
                else if ($product['re_reward_type'] == 2)
                {
                    $update['done_bonus'] = $product['ext_info']['done_two_init_reward_per'] * $relate_money * 0.01;
                }
                if (trim($value[6]) != '')
                {
                    $update['re_bonus'] = trim($value[6]);
                }
                if (trim($value[8]) != '')
                {
                    $update['done_bonus'] = trim($value[8]);
                }
                $update['relate_money'] = intval($relate_money);
                $result = pdo_update('xuan_mixloan_product_apply', $update, array('id'=>$item_two['id']));
            }
        }
        message("上传完毕，成功数{$success}，失败数{$failed}", '', 'success');
    }
} else if ($operation == 'export_agent') {
    //导出手机号
    $list = pdo_fetchall('select a.uid,b.phone from ' . tablename('xuan_mixloan_payment') . ' a 
        left join ' . tablename('xuan_mixloan_member') . " b on a.uid=b.id
        where a.uniacid=:uniacid and b.status=1 and b.phone<>''
        group by a.uid", array(':uniacid' => $_W['uniacid']));
    m('excel')->export($list, array(
        "title" => "代理手机号",
        "columns" => array(
            array(
                'title' => 'id',
                'field' => 'uid',
                'width' => 10
            ),
            array(
                'title' => '手机',
                'field' => 'phone',
                'width' => 30
            ),
        )
    ));
} else if ($operation == 'check_all') {
    // 批量操作
    $values = rtrim($_GPC['values'], ',');
    $values = explode(',', $values);
    $type = trim($_GPC['type']);
    if ($type == 'payall') {
        foreach ($values as $id) {
            //支付宝收款接口
            $item = pdo_fetch('select * from '.tablename("xuan_mixloan_withdraw"). " where id={$id}");
            $item['ext_info'] = json_decode($item['ext_info'], true);
            $bank = pdo_fetch('select realname,phone,type from '.tablename("xuan_mixloan_creditCard")." where id=:id",array(':id'=>$item['bank_id']));
            $redis = redis();
            $key = 'withdraw' . $id;
            if (!$redis->get($key))
            {
                $redis->set($key, 1);
                $payment_no = date('YmdHis');
                $result = m('alipay')->transfer($payment_no, $item['bonus'], $bank['phone'], $bank['realname']);
                if ($result['code'] == -1) {
                    $redis->set($key, 0);
                    // message($result['msg'], '', 'error');
                } else {
                    $update = array();
                    $update['status'] = 1;
                    $item['ext_info']['payment_no'] = $result['order_id'];
                    $update['ext_info'] = json_encode($item['ext_info']);
                    pdo_update('xuan_product_withdraw', $update, array('id' => $id));
                }
            }
        }
    }
    show_json(1, [], '操作成功');
}
include $this->template('agent');
?>
