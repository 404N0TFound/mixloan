<?php
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='index';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
if($operation=='index'){
    //首页
    $cates = pdo_fetchall('select id,name,ext_info from ' . tablename('xuan_mixloan_product_category') . "
        where uniacid={$_W['uniacid']} ORDER BY sort DESC");
    foreach ($cates as &$cate) {
        $list = m('product')->getList([], ['category'=>$cate['id'], 'is_show'=>1], ' sort desc');
        $cate['list'] = m('product')->packupItems($list);
        $cate['ext_info'] = json_decode($cate['ext_info'], true);
    }
    unset($cate);
    include $this->template('product/index');
}  else if ($operation == 'getProduct') {
    //得到产品
    $banner = m('product')->getAdvs();
    $new = m('product')->getRecommends();
    $new = m('product')->packupItems($new);
    // $card = m('product')->getList([], ['type'=>1, 'is_show'=>1], FALSE);
    // $loan = m('product')->getList([], ['type'=>2, 'is_show'=>1], FALSE);
    // $card = m('product')->packupItems($card);
    // $loan = m('product')->packupItems($loan);
    $card = $loan = array();
    $arr = array(
        'banner'=>$banner,
        'new'=>$new,
        'card'=>$card,
        'loan'=>$loan
    );
    show_json(1, $arr);
} else if ($operation == 'info') {
    //产品详情
    $agent = m('member')->checkAgent($member['id']);
    if ($agent['code']==1) {
        $verify = 1;
    } else {
        $verify = 0;
    }
    $id = intval($_GPC['id']);
    $info = m('product')->getList([],['id'=>$id])[$id];
    if ( empty($info['is_show']) ) {
        message('该代理产品已被下架', '', 'info');
    }
    if ($info['type'] == 1) {
        $poster_short_url = shortUrl($_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'apply', 'id'=>$id, 'inviter'=>$member['id'], 'rand' => 1)));
        $poster_long_url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'apply', 'id'=>$id, 'inviter'=>$member['id'], 'rand' => 1));
    } else {
        $poster_short_url = shortUrl($_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$info['relate_id'], 'inviter'=>$member['id'], 'pid'=>$info['id'], 'rand' => 1)));
        $poster_long_url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$info['relate_id'], 'inviter'=>$member['id'], 'pid'=>$info['id'], 'rand' => 1));
    }
    if ($info['ext_info']['poster_text']) {
        $poster_short_url = $info['ext_info']['poster_text'] . ':' . $poster_short_url;
    }
    $my_bonus = m('member')->sumBonus($member['id']) ? : 0;
    if ($my_bonus < floatval($info['ext_info']['bonus_condition'])) {
        $white = pdo_fetchcolumn('select count(1) from ' . tablename('xuan_mixloan_whitelist') . '
                where uid=:uid', array(':uid' => $member['id']));
        if (!$white) {
            $condition = false;
        } else {
            $condition = true;
        }
    } else {
        $condition = true;
    }
    $poster_path = getNowHostUrl()."/addons/xuan_mixloan/data/poster/{$id}_{$member['id']}.png";
    $top_list = m('product')->getTopBonus($id);
    include $this->template('product/info');
} else if ($operation == 'allProduct') {
    //全部产品
    $inviter = intval($_GPC['inviter']);
    $inviter_info = m('member')->getInviterInfo($inviter);
    $shop = pdo_fetch('select * from ' . tablename('xuan_mixloan_shop') . ' 
        where uid=:uid', array(':uid' => $inviter));
    $remove = pdo_fetch('select id,remove_ids from ' . tablename('xuan_mixloan_product_remove') . '
        where uniacid=:uniacid and uid=:uid', array(':uniacid' => $_W['uniacid'], ':uid' => $inviter));
    $cates = pdo_fetchall('select id,name,ext_info from ' . tablename('xuan_mixloan_product_category') . "
        where uniacid={$_W['uniacid']} ORDER BY sort DESC");
    foreach ($cates as &$cate) {
        if ($remove['remove_ids']) {
            $list = m('product')->getList([], ['category'=>$cate['id'], 'is_show'=>1, 'n_id'=>$remove['remove_ids']], FALSE);
        } else {
            $list = m('product')->getList([], ['category'=>$cate['id'], 'is_show'=>1], FALSE);
        }
        $cate['list'] = m('product')->packupItems($list);
        foreach ($cate['list'] as &$row) {
            if ($row['type'] == 1) {
                $row['url'] = $this->createMobileUrl('product', array('op' => 'apply', 'id' => $row['id'], 'inviter'=>$inviter, 'rand'=>1));
                $info = m('bank')->getCard(['id', 'ext_info'], ['id' => $row['relate_id']])[$row['relate_id']];
                $row['tag'] = $info['ext_info']['v_name'];
            } else {
                $row['url'] = $_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$row['relate_id'], 'inviter'=>$inviter, 'pid'=>$row['id'], 'rand'=>1));
                $info = m('loan')->getList(['id', 'money_high'], ['id' => $row['relate_id']])[$row['relate_id']];
                $row['tag'] = '最高额度' . $info['money_high'];
            }
        }
        unset($row);
        $cate['ext_info'] = json_decode($cate['ext_info'], true);
    }
    unset($cate);
    $hot_list = m('product')->getList([], ['is_show'=>1, 'is_hot'=>1, 'n_id'=>$remove['remove_ids']], FALSE);
    $hot_list = m('product')->packupItems($hot_list);
    foreach ($hot_list as &$row) {
        if ($row['type'] == 1) {
            $row['url'] = $this->createMobileUrl('product', array('op' => 'apply', 'id' => $row['id'], 'inviter'=>$inviter));
            $info = m('bank')->getCard(['id', 'ext_info'], ['id' => $row['relate_id']])[$row['relate_id']];
            $row['tag'] = $info['ext_info']['v_name'];
        } else {
            $row['url'] = $_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$row['relate_id'], 'inviter'=>$inviter, 'pid'=>$row['id']));
            $info = m('loan')->getList(['id', 'money_high'], ['id' => $row['relate_id']])[$row['relate_id']];
            $row['tag'] = '最高额度' . $info['money_high'];
        }
    }
    unset($row);
    include $this->template('product/allProduct');
} else if ($operation == 'apply') {
    //申请产品
    $id = intval($_GPC['id']);
    $inviter = intval($_GPC['inviter']);
    $info = m('product')->getList(['id', 'ext_info', 'is_show'],['id'=>$id])[$id];
    if ( empty($info['is_show']) ) {
        header("location:{$this->createMobileUrl('product', array('op' => 'allProduct', 'inviter' => $inviter))}");
        exit();
    }
    $my_bonus = pdo_fetchcolumn('select sum(re_bonus+done_bonus+extra_bonus) from ' . tablename('xuan_mixloan_product_apply_b') . '
        where inviter=:inviter', array(':inviter' => $inviter)) ? : 0;
    if ($info['ext_info']['bonus_condition'] && $my_bonus < floatval($info['ext_info']['bonus_condition'])) {
        $white = pdo_fetchcolumn('select count(1) from ' . tablename('xuan_mixloan_whitelist') . '
                where uid=:uid', array(':uid' => $inviter));
        if (!$white) {
            message('该代理没有达到推广该产品条件', '', 'error');
        }
    }
    $is_close = pdo_fetchcolumn('select is_close from ' . tablename('xuan_mixloan_agent_close') . '
        where uid=:uid', array(':uid' => $inviter));
    include $this->template('product/apply');
} else if ($operation == 'apply_submit') {
    //申请产品
    $id = intval($_GPC['id']);
    $inviter = intval($_GPC['inviter']);
    if (sha1(md5(strtolower($_GPC['cache']))) != $_COOKIE['authcode']) {
        show_json(-1, [], "图形验证码不正确");
    }
    if ($inviter == $member['id']) {
        show_json(-1, [], "您不能自己邀请自己");
    }
    if ($id <= 0) {
        show_json(-1, [], "id为空");
    }
    if(!trim($_GPC['name']) || !trim($_GPC['phone'])) {
        show_json(-1, [], '资料不能为空');
    }
    $record = m('product')->getApplyList(['id'], ['pid'=>$id, 'phone'=>$_GPC['phone']]);
    if ($record) {
        show_json(-1, [], "您已经申请过啦");
    }
    if ($config['jdwx_open'] == 1) {
        // $res = m('jdwx')->jd_credit_three($config['jdwx_key'], trim($_GPC['name']), trim($_GPC['phone']), trim($_GPC['idcard']));
        // if ($res['code'] == -1) {
        // 	show_json($res['code'], [], $res['msg']);
        // }
    }
    $info = m('product')->getList(['id', 'name', 'type', 'relate_id', 'is_show'],['id'=>$id])[$id];
    if ( empty($info['is_show']) ) {
        show_json(-1, [], '该代理产品已被下架');
    }
    if ($info['type'] == 1) {
        $pro = m('bank')->getCard(['id', 'ext_info'], ['id'=>$info['relate_id']])[$info['relate_id']];
    } else {
        $pro = m('loan')->getList(['id', 'ext_info'], ['id'=>$info['relate_id']])[$info['relate_id']];
    }
    if ($inviter) {
        $inviter_one = pdo_fetch("SELECT openid,nickname FROM ".tablename("xuan_mixloan_member") . " WHERE id=:id", array(':id'=>$inviter));
        $datam = array(
            "first" => array(
                "value" => "尊敬的用户您好，有一个用户通过您的邀请申请了{$info['name']}，请及时跟进。",
                "color" => "#173177"
            ) ,
            "keyword1" => array(
                'value' => trim($_GPC['name']),
                "color" => "#4a5077"
            ) ,
            "keyword2" => array(
                'value' => date('Y-m-d H:i:s', time()),
                "color" => "#4a5077"
            ) ,
            "remark" => array(
                "value" => '点击查看详情',
                "color" => "#4a5077"
            ) ,
        );
        $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
        $account = WeAccount::create($_W['acid']);
        $account->sendTplNotice($inviter_one['openid'], $config['tpl_notice1'], $datam, $url);
        if ($openid) {
            // pdo_update('xuan_mixloan_member', array('phone'=>trim($_GPC['phone']), 'certno'=>trim($_GPC['idcard'])), array('id'=>$member['id']));
        }
        $status = 0;
    } else {
        $status = -2;
    }
    $insert = array(
        'uniacid' => $_W['uniacid'],
        'uid' => $member['id'],
        'phone' => trim($_GPC['phone']),
        'certno' => trim($_GPC['idcard']),
        'realname' => trim($_GPC['name']),
        'pid' => $id,
        'inviter' => $inviter,
        're_bonus'=>0,
        'done_bonus'=>0,
        'extra_bonus'=>0,
        'status'=>$status,
        'createtime'=>time(),
        'ip'=>getServerIp(),
        'device_type'=>getDeviceType(),
    );
    $insert['browser_type'] = is_weixin() ? 1 : 2;
    pdo_insert('xuan_mixloan_product_apply_b', $insert);
    //二级
    $inviter_info = m('member')->getInviterInfo($inviter);
    $second_inviter = m('member')->getInviter($inviter_info['phone'], $inviter_info['openid']);
    if ($second_inviter) {
        $insert['inviter'] = $second_inviter;
        $insert['degree'] = 2;
        pdo_insert('xuan_mixloan_product_apply_b', $insert);
        $inviter_two = pdo_fetch("SELECT openid,nickname FROM ".tablename("xuan_mixloan_member") . " WHERE id=:id", array(':id'=>$second_inviter));
        $datam = array(
            "first" => array(
                "value" => "尊敬的用户您好，有一个用户通过您下级{$inviter_one['nickname']}的邀请申请了{$info['name']}，请及时跟进。",
                "color" => "#173177"
            ) ,
            "keyword1" => array(
                'value' => trim($_GPC['name']),
                "color" => "#4a5077"
            ) ,
            "keyword2" => array(
                'value' => date('Y-m-d H:i:s', time()),
                "color" => "#4a5077"
            ) ,
            "remark" => array(
                "value" => '点击查看详情',
                "color" => "#4a5077"
            ) ,
        );
        $account->sendTplNotice($inviter_two['openid'], $config['tpl_notice1'], $datam, $url);
    }
    show_json(1, $pro['ext_info']['url']);
} else if ($operation == 'customer') {
    //客户列表
    include $this->template('product/customer');
} else if ($operation == 'customer_list') {
    //客户列表接口
    $month = (int)$_GPC['month'];
    $year = (int)$_GPC['year'];
    $params['begin'] = "{$year}-{$month}-01";
    $params['inviter'] = $member['id'];
    if ($config['customer_hide_product']) {
        $remove = pdo_fetch('select id,remove_ids from ' . tablename('xuan_mixloan_product_remove') . '
            where uniacid=:uniacid and uid=:uid', array(':uniacid' => $_W['uniacid'], ':uid' => $member['id']));
        $condition_days = ['count_time'=>1, 'is_show'=>1, 'n_id' => $remove['remove_ids']];
        $condition_weeks = ['count_time'=>7, 'is_show'=>1, 'n_id' => $remove['remove_ids']];
        $condition_months = ['count_time'=>30, 'is_show'=>1, 'n_id' => $remove['remove_ids']];
        $params['remove_ids'] = $remove['remove_ids'];
    } else {
        $condition_days = ['count_time'=>1];
        $condition_weeks = ['count_time'=>7];
        $condition_months = ['count_time'=>30];
    }
    $days_list = m('product')->getList(['id', 'name', 'ext_info'], $condition_days);
    $weeks_list = m('product')->getList(['id', 'name', 'type'], $condition_weeks);
    $months_list = m('product')->getList(['id', 'name', 'type'], $condition_months);
    // $invite_list = m('product')->getList(['id', 'name', 'type'], ['is_show' => 0]);
    $days_ids = m('product')->getIds($days_list);
    $weeks_ids = m('product')->getIds($weeks_list);
    $months_ids = m('product')->getIds($months_list);
    // $invite_ids = m('product')->getIds($invite_list);
    $applys = m('product')->getApplys($params);
    $days_count_list = m('product')->getNums($days_ids, $params, 1);
    $weeks_count_list = m('product')->getNums($weeks_ids, $params, 1);
    $months_count_list = m('product')->getNums($months_ids, $params, 1);
    // $invite_count_list = m('product')->getNums($invite_ids, $params, 1);
    $days_succ_list = m('product')->getNums($days_ids, $params, 2);
    $weeks_succ_list = m('product')->getNums($weeks_ids, $params, 2);
    $months_succ_list = m('product')->getNums($months_ids, $params, 2);
    // $invite_succ_list = m('product')->getNums($invite_ids, $params, 2);
    $days_bonus_list = m('product')->getNums($days_ids, $params, 3);
    $weeks_bonus_list = m('product')->getNums($weeks_ids, $params, 3);
    $months_bonus_list = m('product')->getNums($months_ids, $params, 3);
    // $invite_bonus_list = m('product')->getNums($invite_ids, $params, 3);
    foreach ($days_list as &$row) {
        $row['count_num'] = $days_count_list[$row['id']]['count'] ? : 0;
        if ($row['type'] == 1) {
            $row['succ'] = $days_succ_list[$row['id']]['count'] ? $days_succ_list[$row['id']]['count'].'位' : '0'.'位';
        } else {
            $row['succ'] = $days_succ_list[$row['id']]['relate_money'] ? $days_succ_list[$row['id']]['relate_money'].'元' : '0'.'元';
        }
        $row['count_bonus'] = $days_bonus_list[$row['id']]['bonus'] ? : 0;
    }
    unset($row);
    foreach ($weeks_list as &$row) {
        $row['count_num'] = $weeks_count_list[$row['id']]['count'] ? : 0;
        if ($row['type'] == 1) {
            $row['succ'] = $weeks_succ_list[$row['id']]['count'] ? $weeks_succ_list[$row['id']]['count'].'位' : '0'.'位';
        } else {
            $row['succ'] = $weeks_succ_list[$row['id']]['relate_money'] ? $weeks_succ_list[$row['id']]['relate_money'].'元' : '0'.'元';
        }
        $row['count_bonus'] = $weeks_bonus_list[$row['id']]['bonus'] ? : 0;
    }
    unset($row);
    foreach ($months_list as &$row) {
        $row['count_num'] = $months_count_list[$row['id']]['count'] ? : 0;
        if ($row['type'] == 1) {
            $row['succ'] = $months_succ_list[$row['id']]['count'] ? $months_succ_list[$row['id']]['count'].'位' : '0'.'位';
        } else {
            $row['succ'] = $months_succ_list[$row['id']]['relate_money'] ? $months_succ_list[$row['id']]['relate_money'].'元' : '0'.'元';
        }
        $row['count_bonus'] = $months_bonus_list[$row['id']]['bonus'] ? : 0;
    }
    unset($row);
    // foreach ($invite_list as &$row) {
    //     $row['count_num'] = $invite_count_list[$row['id']]['count'] ? : 0;
    //     if ($row['type'] == 1) {
    //         $row['succ'] = $invite_succ_list[$row['id']]['count'] ? $invite_succ_list[$row['id']]['count'].'位' : '0'.'位';
    //     } else {
    //         $row['succ'] = $invite_succ_list[$row['id']]['relate_money'] ? $invite_succ_list[$row['id']]['relate_money'].'元' : '0'.'元';
    //     }
    //     $row['count_bonus'] = $invite_bonus_list[$row['id']]['bonus'] ? : 0;
    // }
    // unset($row);
    $arr = ['days_list'=>array_values($days_list), 'months_list'=>array_values($months_list), 'weeks_list'=>array_values($weeks_list), 'applys'=>$applys];
    show_json(1, $arr);
}else if ($operation == 'customer_detail') {
    //详情
    $pid = intval($_GPC['pid']);
    $inviter = intval($_GPC['inviter']);
    $degree = intval($_GPC['degree']) ? : 1;
    $type = $_GPC['type'] ? : 1;
    if (empty($pid) || empty($inviter)) {
        message('查询出错', '', 'error');
    }
    $arr = array(':pid'=>$pid, ':inviter'=>$inviter);
    if ($type == 1) {
        $condition = ' WHERE inviter=:inviter AND pid=:pid';
    } else if ($type == 2) {
        $condition = ' WHERE inviter=:inviter AND pid=:pid AND status>0';
    } else if ($type == 3) {
        $condition = ' WHERE inviter=:inviter AND pid=:pid AND status=-1';
    }
    $condition .= " and degree={$degree}";
    $count_num = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('xuan_mixloan_product_apply_b') . "
        WHERE inviter=:inviter AND pid=:pid and degree={$degree}", $arr) ? : 0;
    $count_succ_num = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('xuan_mixloan_product_apply_b') . "
        WHERE inviter=:inviter AND pid=:pid AND status>0 and degree={$degree}", $arr) ? : 0;
    $count_succ_bonus = pdo_fetchcolumn('SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ' . tablename('xuan_mixloan_product_apply_b') . "
        WHERE inviter=:inviter AND pid=:pid and degree={$degree}", $arr) ? : 0;
    $count_ing_num = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('xuan_mixloan_product_apply_b') . "
        WHERE inviter=:inviter AND pid=:pid AND status=0 and degree={$degree}", $arr) ? : 0;
    $sql = 'SELECT id,re_bonus,done_bonus,extra_bonus,pid,status,phone,createtime,degree FROM ' . tablename('xuan_mixloan_product_apply_b'). $condition;
    $list = pdo_fetchall($sql, $arr);
    if (!empty($list)) {
        foreach ($list as &$row) {
            $row['product'] = m('product')->getList(['id','ext_info','name'],['id'=>$row['pid']])[$row['pid']];
            $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
            $row['bonus'] = $row['re_bonus'] + $row['done_bonus'] + $row['extra_bonus'];
            if ($row['status'] == 1) {
                $row['state'] = '已注册';
            } else if ($row['status'] == -1) {
                $row['state'] = '已失效';
            } else if ($row['status'] == 0) {
                $row['state'] = '申请中';
            } else if ($row['status'] == 2) {
                $row['state'] = '已成功';
            }
            if ($row['degree'] == 1) {
                $row['degree'] = '直推';
            } else if ($row['degree'] == 2) {
                $row['degree'] = '二级';
            }
            $row['phone'] = substr($row['phone'], 0, 4) . '****' . substr($row['phone'], -3);
        }
        unset($row);
    }
    include $this->template('product/customer_detail');
} else if ($operation == 'choose_show_up') {
    //代理产品上下架分类
    $shop = pdo_fetch('select * from ' . tablename('xuan_mixloan_shop') . ' 
        where uid=:uid', array(':uid' => $member['id']));
    include $this->template('product/choose_show_up');
} else if ($operation == 'show_up') {
    //代理产品上下架
    $type = intval($_GPC['type']) ? : 1;
    $list = m('product')->getList(['id', 'name', 'ext_info'],['is_show' => 1, 'type' => $type]);
    $remove = pdo_fetch('select id,remove_ids from ' . tablename('xuan_mixloan_product_remove') . '
        where uniacid=:uniacid and uid=:uid', array(':uniacid' => $_W['uniacid'], ':uid' => $member['id']));
    if ($remove)
    {
        $remove_ids = explode(',', $remove['remove_ids']);
    }
    else
    {
        $remove_ids = array();
    }
    include $this->template('product/show_up');
} else if ($operation == 'set_show_up') {
    //设置代理产品上下架
    $id = intval($_GPC['id']);
    $remove = pdo_fetch('select id,remove_ids from ' . tablename('xuan_mixloan_product_remove') . '
        where uniacid=:uniacid and uid=:uid', array(':uniacid' => $_W['uniacid'], ':uid' => $member['id']));
    if ($remove)
    {
        $remove_ids = explode(',', $remove['remove_ids']);
        if (in_array($id, $remove_ids))
        {
            //上架
            foreach ($remove_ids as $val)
            {
                if ($val != $id)
                {
                    $new_ids[] = $val;
                }
            }
            if (empty($new_ids))
            {
                pdo_delete('xuan_mixloan_product_remove', array('id' => $remove['id']));
            }
            else
            {
                $new_ids = implode(',', $new_ids);
                pdo_update('xuan_mixloan_product_remove', array('remove_ids' => $new_ids), array('id' => $remove['id']));
            }
            show_json(1);
        }
        else
        {
            //下架
            $remove_ids[] = $id;
            $new_ids = implode(',', $remove_ids);
            pdo_update('xuan_mixloan_product_remove', array('remove_ids' => $new_ids), array('id' => $remove['id']));
            show_json(-1);
        }
    }
    else
    {
        //下架
        $remove_ids[] = $id;
        $new_ids = implode(',', $remove_ids);
        $insert = array('uniacid' => $_W['uniacid'], 'uid' => $member['id'], 'remove_ids' => $new_ids);
        pdo_insert('xuan_mixloan_product_remove', $insert);
        show_json(-1);
    }
} else if ($operation == 'shop_background') {
    // 店铺背景
    $pic_url = trim($_GPC['pic_url']);
    if (empty($pic_url)) {
        show_json(-1, [], '背景为空');
    }
    $shop_id = pdo_fetchcolumn('select id from ' . tablename('xuan_mixloan_shop') . ' 
        where uid=:uid', array(':uid' => $member['id']));
    if ($shop_id) {
        $update = array('background' => $pic_url);
        pdo_update('xuan_mixloan_shop', $update, array('id' => $shop_id));
    } else {
        $insert = array();
        $insert['background'] = $pic_url;
        $insert['uid']        = $member['id'];
        pdo_insert('xuan_mixloan_shop', $insert);
    }
    show_json(1, [], 'OK');
} else if ($operation == 'customer_old') {
    include $this->template('product/customer_old');
} else if ($operation == 'customer_old_list') {
    $month = (int)$_GPC['month'];
    $year = (int)$_GPC['year'];
    $params['begin'] = "{$year}-{$month}-01";
    $params['inviter'] = $member['id'];
    if ($config['customer_hide_product']) {
        $remove = pdo_fetch('select id,remove_ids from ' . tablename('xuan_mixloan_product_remove') . '
            where uniacid=:uniacid and uid=:uid', array(':uniacid' => $_W['uniacid'], ':uid' => $member['id']));
        $params['remove_ids'] = $remove['remove_ids'];
    }
    $applys = m('product')->getApplys($params);
    $invite_list = m('product')->getList(['id', 'name', 'type'], ['is_show' => 0]);
    $invite_ids = m('product')->getIds($invite_list);
    $invite_bonus_list = m('product')->getNums($invite_ids, $params, 3);
    $invite_succ_list = m('product')->getNums($invite_ids, $params, 2);
    $invite_count_list = m('product')->getNums($invite_ids, $params, 1);
    foreach ($invite_list as &$row) {
        $row['count_num'] = $invite_count_list[$row['id']]['count'] ? : 0;
        if ($row['type'] == 1) {
            $row['succ'] = $invite_succ_list[$row['id']]['count'] ? $invite_succ_list[$row['id']]['count'].'位' : '0'.'位';
        } else {
            $row['succ'] = $invite_succ_list[$row['id']]['relate_money'] ? $invite_succ_list[$row['id']]['relate_money'].'元' : '0'.'元';
        }
        $row['count_bonus'] = $invite_bonus_list[$row['id']]['bonus'] ? : 0;
    }
    unset($row);
    $arr = ['invite_list'=>array_values($invite_list), 'applys'=>$applys];
    show_json(1, $arr);
}