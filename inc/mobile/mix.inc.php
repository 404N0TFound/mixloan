<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='service';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
if($operation=='service'){
	//客服服务
	include $this->template('mix/service');
} else if ($operation == 'tutorials') {
	//新手指南
	include $this->template('mix/tutorials');
} else if ($operation == 'get_pos') {
	//免费获取pos机
	include $this->template('mix/get_pos');
} else if ($operation == 'apply_cache') {
    require_once('../addons/xuan_mixloan/inc/model/cache.php');
    $cache = new Xuan_mixloan_Cache();
    $cache_img = $cache->doimg();
    if (!$cache_img['result']) {
        show_json(-1,[],'生成验证码失败');
    }
    $code = $cache->getCode();
    setcookie('authcode', sha1(md5($code)), time()+300);
    show_json(1, ['img' => $cache_img['file']]);
} else if ($operation == 'credit') {
    // 数据查询
    if ($_GPC['post']) {
        $realname = trim($_GPC['realname']);
        $phone = trim($_GPC['phone']);
        $certno = trim($_GPC['idcard']);
        $email = trim($_GPC['email']);
        $record = pdo_fetchcolumn('select count(1) from ' . tablename('xuan_mixloan_td_credit') . '
            where phone=:phone or certno=:certno', array(':phone' => $phone, ':certno' => $certno));
        if (!$record) {
            $insert = array();
            $insert['uniacid'] = $_W['uniacid'];
            $insert['phone'] = $phone;
            $insert['realname'] = $realname;
            $insert['certno'] = $certno;
            $insert['email'] = $email;
            $insert['createtime'] = time();
            $insert['status'] = 0;
            $insert['uid'] = $member['id'];
            pdo_insert('xuan_mixloan_td_credit', $insert);
        }
        show_json(1, ['url' => $this->createMobileUrl('mix', array('op' => 'td_service'))], '跳转中');
    }
    include $this->template('mix/credit');
} else if ($operation == 'td_service') {
    //同盾客服服务
    include $this->template('mix/td_service');
}