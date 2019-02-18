<?php
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
session_start();
$config = $this->module['config'];
$operation = $_GPC['op'] ? : 'login';
$user_id = $_SESSION['user_id'];
if($operation=='login') {
    //登陆
    include $this->template('partner/login');
}  else if($operation=='find_pass') {
    //找回密码
    include $this->template('partner/find_pass');
} else if ($operation == 'login_submit') {
    //登陆提交
    if (!empty($user_id)) {
        header("location:{$this->createMobileUrl('partner', array('op' => 'default'))}");
    }
    $phone = trim($_GPC['phone']);
    $password = trim($_GPC['password']);
    if (empty($phone)) {
        message('手机号不能为空', '', 'error');
    }
    if (empty($password)) {
        message('密码不能为空', '', 'error');
    }
    $record = pdo_fetch('select id,pass,status from ' . tablename('xuan_mixloan_member') . '
	 	where phone=:phone and uniacid=:uniacid', array(':phone' => $phone, ':uniacid' => $_W['uniacid']));
    if (empty($record)) {
        message('用户不存在', '', 'error');
    }
    if ($record['pass'] != $password) {
        message('密码不正确', '', 'error');
    }
    if ($record['status'] == 0) {
        message('您已被冻结', '', 'error');
    }
    $_SESSION['user_id'] = $record['id'];
    header("location:{$this->createMobileUrl('partner', array('op' => 'default'))}");
} else if ($operation == 'find_pass_submit') {
    //找回密码提交
    $phone = trim($_GPC['phone']);
    $password = trim($_GPC['password']);
    $smscode = trim($_GPC['smscode']);
    if (empty($phone)) {
        message('手机号不能为空', '', 'error');
    }
    if (empty($password)) {
        message('密码不能为空', '', 'error');
    }
    if (empty($smscode)) {
        message('短信不能为空', '', 'error');
    }
    if (md5($smscode.$phone) != $_COOKIE['cache_code']) {
        message('验证码不符或验证码已失效', '', 'error');
    }
    $record = pdo_fetch('select id from ' . tablename('xuan_mixloan_member') . '
	 	where phone=:phone', array(':phone' => $phone));
    if (empty($record)) {
        message('用户不存在', '', 'error');
    }
    pdo_update('xuan_mixloan_member', array('pass' => $password), array('id' => $record['id']));
    message('找回密码成功', $this->createMobileUrl('partner', array('op' => 'login')), 'sccuess');
} else if ($operation == 'loginout') {
    // 退出
    unset($_SESSION['user_id']);
    header("location:{$this->createMobileUrl('partner', array('op' => 'login'))}");
} else if ($operation == 'default') {
    // 首页
    if (empty($user_id)) {
        message('用户不存在', '', 'error');
    }
    $member = pdo_fetch('select * from ' . tablename('xuan_mixloan_member') . '
		where id=:id', array(':id' => $user_id));
    $count_all = m('member')->sumBonus($user_id) ? : 0;
    $withdraw_all = pdo_fetchcolumn('select sum(bonus) from ' . tablename('xuan_mixloan_withdraw') . '
		where uid=:uid', array(':uid' => $user_id)) ? : 0;
    $can_withdraw = $count_all - $withdraw_all;
    $one_degree_apply = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_product_apply_b') . '
		where inviter=:inviter and degree=1 and type=1', array(':inviter' => $user_id)) ? : 0;
    $all_degree_apply = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_product_apply_b') . '
		where inviter=:inviter and type=1', array(':inviter' => $user_id)) ? : 0;
    include $this->template('partner/default');
} else if ($operation == 'apply_data') {
    // 首页
    $psize = 20;
    $pindex = max(1, intval($_GPC['page']));
    $where = '';
    $realname = trim($_GPC['realname']);
    $phone = trim($_GPC['phone']);
    $starttime = trim($_GPC['starttime']);
    $endtime = trim($_GPC['endtime']);
    $product = trim($_GPC['product']);
    $status = trim($_GPC['status']);
    if (empty($user_id)) {
        message('用户不存在', '', 'error');
    }
    $member = pdo_fetch('select * from ' . tablename('xuan_mixloan_member') . '
		where id=:id', array(':id' => $user_id));
    $cond = array(':inviter' => $member['id']);
    if ($realname) {
        $where .= " and realname like :realname";
        $cond[':realname'] = '%' . $realname . '%';
    }
    if ($status) {
        if ($status == 1) {
            $where .= " and status>=:status";
        } else {
            $where .= " and status=:status";
        }
        $cond[':status'] =  $status ;
    }
    if ($phone) {
        $where .= " and phone like :phone";
        $cond[':phone'] = '%' . $phone . '%';
    }
    if ($starttime) {
        $where .= " and createtime>:starttime";
        $cond[':starttime'] = strtotime($starttime);
    }
    if ($endtime) {
        $where .= " and createtime<:endtime";
        $cond[':endtime'] = strtotime($endtime);
    }
    if ($product) {
        $pids = array();
        $products = pdo_fetchall('select id from ' . tablename('xuan_mixloan_product') . "
			where name like :name ", array(':name' => '%' . $product . '%'));
        foreach ($products as $value) {
            $pids[] = $value['id'];
        }
        if ($pids) {
            $pid_string = implode(',', $pids);
            $where .= " and pid in ({$pid_string})";
        }
    }
    $sql = 'select * from ' . tablename('xuan_mixloan_product_apply_b') . ' where 
			inviter=:inviter and degree=1' . $where . ' order by id desc';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $cond);
    foreach ($list as &$row) {
        if ($row['type'] == 1) {
            $pro = m('product')->getList(['id', 'name', 'ext_info'], ['id' => $row['pid']])[$row['pid']];
            $row['product_name'] = $pro['name'];
            $row['product_logo'] = tomedia($pro['ext_info']['logo']);
        } else if ($row['type'] == 2) {
            $row['realname'] = pdo_fetchcolumn('select nickname from ' . tablename('xuan_mixloan_member') . '
                where id=:id', array(':id' => $row['uid']));
            $row['product_name'] = '邀请代理奖励';
            $row['product_logo'] = '../addons/xuan_mixloan/template/style/picture/5a4f1cb45746c.png';
        } else if ($row['type'] == 4) {
            $row['realname'] = pdo_fetchcolumn('select nickname from ' . tablename('xuan_mixloan_member') . '
                where id=:id', array(':id' => $row['uid']));
            $row['product_name'] = '合伙人分佣';
            $row['product_logo'] = '../addons/xuan_mixloan/template/style/images/partner.png';
        } 
        $row['bonus'] = $row['re_bonus'] + $row['done_bonus'] + $row['extra_bonus'];
        $row['phone'] = func_substr_replace($row['phone']);
    }
    unset($row);
    $total = pdo_fetchcolumn( 'select count(*) from ' . tablename('xuan_mixloan_product_apply_b') . ' where 
			inviter=:inviter ' . $where, $cond);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('partner/apply_data');
} else if ($operation == 'login_type') {
    // 登陆方式
    $login_url = $_W['siteroot'] . '/app/' . 
                $this->createMobileUrl('partner', array('op' => 'login'));
    include $this->template('partner/login_type');
} else if ($operation == 'login_user') {
    // 用户直接跳转
    $openid = m('user')->getOpenid();
    $member = m('member')->getMember($openid);
    if (empty($member['id'])) {
        message('用户不存在', '', 'error');
    }
    if ($member['status'] == 0) {
        message('您已被冻结', '', 'error');
    }
    $_SESSION['user_id'] = $member['id'];
    header("location:{$this->createMobileUrl('partner', array('op' => 'default'))}");
} else if ($operation == 'product') {
    // 首页
    $psize = 20;
    $pindex = max(1, intval($_GPC['page']));
    $where = '';
    $name = trim($_GPC['name']);
    $is_show = trim($_GPC['is_show']);
    $cond = array();
    $agent = m('member')->checkAgent($_SESSION['user_id']);
    if ($agent['code'] != 1) {
        message('您还不是代理', '', 'error');
    }
    if ($is_show != '') {
        $where .= " and is_show=:is_show";
        $cond[':is_show'] =  $is_show ;
    }
    if ($name) {
        $where .= " and name like :name";
        $cond[':name'] = '%' . $name . '%';
    }
    $sql = 'select * from ' . tablename('xuan_mixloan_product') . ' where 
            1 ' . $where . ' order by id desc';
    $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $cond);
    foreach ($list as &$row) {
        $row['ext_info'] = json_decode($row['ext_info'], 1);
        $row['logo'] = tomedia($row['ext_info']['logo']);
        if ($row['type'] == 2) {
            $loan = pdo_fetch('select ext_info from ' . tablename('xuan_mixloan_loan') . '
                        where id=:id', array(':id' => $row['relate_id']));
            $loan['ext_info'] = json_decode($loan['ext_info'], 1);
            $row['url'] = $loan['ext_info']['url'];
            $row['long_url'] = $_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'apply', 'id'=>$row['relate_id'], 'inviter'=>$_SESSION['user_id'], 'pid'=>$row['id'], 'rand' => 1));
            $row['short_url'] = shortUrl($row['long_url']);
        }
    }
    unset($row);
    $total = pdo_fetchcolumn( 'select count(*) from ' . tablename('xuan_mixloan_product') . ' where 
            1 ' . $where . ' order by id desc', $cond);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('partner/product');
}