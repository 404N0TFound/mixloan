<?php  
defined('IN_IA') or exit('Access Denied');
session_start();
global $_GPC,$_W;
$openid = $_GPC['userId'];
$member = m('member')->getMember($openid);
$config = $this->module['config'];
$user_id = $_SESSION['user_id'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='index';
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
        exit();
    }
    $phone = trim($_GPC['phone']);
    $password = trim($_GPC['password']);
    if (empty($phone)) {
        message('手机号不能为空', '', 'error');
    }
    if (empty($password)) {
        message('密码不能为空', '', 'error');
    }
    $record = pdo_fetch('select id,password,status from ' . tablename('xuan_mixloan_partner') . '
	 	where phone=:phone and uniacid=:uniacid', array(':phone' => $phone, ':uniacid' => $_W['uniacid']));
    if (empty($record)) {
        message('用户不存在', $this->createMobileUrl('partner', array('op' => 'default')), 'error');
    }
    if ($record['password'] != $password) {
        message('密码不正确', '', 'error');
    }
    if ($record['status'] == -1) {
        message('您已被冻结', '', 'error');
    }
    $_SESSION['user_id'] = $record['id'];
    header("location:{$this->createMobileUrl('partner', array('op' => 'default'))}");
} else if ($operation == 'find_pass_submit') {
    //找回密码提交
    $phone    = trim($_GPC['phone']);
    $password = trim($_GPC['password']);
    $smscode  = trim($_GPC['smscode']);
    $token    = md5(sha1($phone));
    if (empty($phone)) {
        message('手机号不能为空', '', 'error');
    }
    if (empty($password)) {
        message('密码不能为空', '', 'error');
    }
    if (empty($smscode)) {
        message('短信不能为空', '', 'error');
    }
    $record = pdo_fetch('select cache,updatetime from ' . tablename('xuan_mixloan_cache') . '
        where token=:token', array(':token' => $token));
    if ($record['updatetime'] < time() - 90)
    {
        message('验证码已失效', '', 'error');
    }
    if ($smscode != $record['cache'])
    {
        message('验证码不符', '', 'error');
    }
    $record = pdo_fetch('select id from ' . tablename('xuan_mixloan_partner') . '
	 	where phone=:phone and uniacid=:uniacid', array(':phone' => $phone, ':uniacid' => $_W['uniacid']));
    if (empty($record)) {
        message('用户不存在', '', 'error');
    }
    pdo_update('xuan_mixloan_partner', array('password' => $password), array('id' => $record['id']));
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
    $member = pdo_fetch('select * from ' . tablename('xuan_mixloan_partner') . '
		where id=:id', array(':id' => $user_id));
    $starttime = strtotime(date('Y-m-d'));
    $endtime = $starttime - 86400;
    $register_nums = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_member') . '
                                where inviter=:inviter', array(':inviter' => $user_id)) ? : 0;
    $register_yesterday_nums = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_member') . "
                                where inviter=:inviter
                                and createtime>{$starttime} and createtime<{$endtime}", array(':inviter' => $user_id))  ? : 0;
    $long_url = $_W['siteroot'] . '/app/' . $this->createMobileUrl('partner', array('op' => 'invite', 'utook' => $user_id));
    $share_url = shortUrl($long_url);
    include $this->template('partner/default');
} else if ($operation == 'register_list') {
    // 邀请注册
    $psize = 20;
    $pindex = max(1, intval($_GPC['page']));
    $where = '';
    $phone = trim($_GPC['phone']);
    $starttime = trim($_GPC['starttime']);
    $endtime = trim($_GPC['endtime']);
    if (empty($user_id)) {
        message('用户不存在', '', 'error');
    }
    $member = pdo_fetch('select * from ' . tablename('xuan_mixloan_partner') . '
		where id=:id', array(':id' => $user_id));
    $cond = array(':inviter' => $member['id']);
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
    $sql = 'select * from ' . tablename('xuan_mixloan_member') . ' a 
            where inviter=:inviter ' . $where . '
            order by id desc';
    if (!empty($_GPC['export'])) {
        $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    }
    $list = pdo_fetchall($sql, $cond);
    foreach ($list as &$row) {
        $row['phone'] = func_substr_replace($row['phone']);
        $row['realname'] = func_substr_replace($row['realname'], '*', 1, 1);
        $row['info'] = pdo_fetch('select base,idcard,phone,face,contact from ' . tablename('xuan_mixloan_info') . '
                                    where uid=:uid', array(':uid' => $row['id']));
    }
    unset($row);
    if ($_GPC['export'] == 1)
    {
        foreach ($list as &$row) {
            $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
        }
        unset($row);
        m('excel')->export($list, array(
            "title" => "注册资料",
            "columns" => array(
                array(
                    'title' => '姓名',
                    'field' => 'realname',
                    'width' => 20
                ),
                array(
                    'title' => '手机号',
                    'field' => 'phone',
                    'width' => 15
                ),
                array(
                    'title' => '注册时间',
                    'field' => 'createtime',
                    'width' => 20
                ),
            )
        ));
    }
    $total = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_member') . ' a 
                                where inviter=:inviter ' . $where . '
                                order by id desc', $cond);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('partner/register_list');
} else if ($operation == 'order_list') {
    // 邀请下款
    $psize = 20;
    $pindex = max(1, intval($_GPC['page']));
    $where = ' and a.status>0';
    $phone = trim($_GPC['phone']);
    $starttime = trim($_GPC['starttime']);
    $endtime = trim($_GPC['endtime']);
    if (empty($user_id)) {
        message('用户不存在', '', 'error');
    }
    $member = pdo_fetch('select * from ' . tablename('xuan_mixloan_partner') . '
        where id=:id', array(':id' => $user_id));
    $cond = array(':inviter' => $member['id']);
    if ($phone) {
        $where .= " and b.phone like :phone";
        $cond[':phone'] = '%' . $phone . '%';
    }
    if ($starttime) {
        $where .= " and a.gettime>:starttime";
        $cond[':starttime'] = strtotime($starttime);
    }
    if ($endtime) {
        $where .= " and a.gettime<:endtime";
        $cond[':endtime'] = strtotime($endtime);
    }
    $sql = 'select a.*,b.phone,b.realname from ' . tablename('xuan_mixloan_order') . ' a 
            left join ' . tablename('xuan_mixloan_member') . ' b on a.uid=b.id 
            where b.inviter=:inviter ' . $where . '
            order by a.id desc';
    if (!empty($_GPC['export'])) {
        $sql.= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    }
    $list = pdo_fetchall($sql, $cond);
    foreach ($list as &$row) {
        $row['phone'] = func_substr_replace($row['phone']);
        $row['realname'] = func_substr_replace($row['realname'], '*', 1, 1);
    }
    unset($row);
    if ($_GPC['export'] == 1)
    {
        foreach ($list as &$row) {
            $row['get_time'] = date('Y-m-d H:i:s', $row['gettime']);
        }
        unset($row);
        m('excel')->export($list, array(
            "title" => "下款资料",
            "columns" => array(
                array(
                    'title' => '姓名',
                    'field' => 'realname',
                    'width' => 20
                ),
                array(
                    'title' => '手机号',
                    'field' => 'phone',
                    'width' => 15
                ),
                array(
                    'title' => '下款时间',
                    'field' => 'get_time',
                    'width' => 20
                ),
                array(
                    'title' => '下款金额',
                    'field' => 'money',
                    'width' => 20
                ),
            )
        ));
    }
    $total = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_order') . ' a 
                            left join ' . tablename('xuan_mixloan_member') . ' b on a.uid=b.id 
                            where b.inviter=:inviter ' . $where, $cond);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('partner/order_list');
} else if ($operation == 'invite') {
	// 邀请页面
	include $this->template('partner/invite');
} else if ($operation == 'contract') {
	// 协议
	include $this->template('partner/contract');
}