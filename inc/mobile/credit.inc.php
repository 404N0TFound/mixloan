<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='index';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$member['user_type'] = m('member')->checkAgent($member['id']);
if($operation=='index'){
	//信用查询中心首页
	include $this->template('credit/index');
} else if ($operation == 'information') {
    //基本信息
    include $this->template('credit/information');
} else if ($operation == 'insertInformation') {
    //基本信息提交
    $res = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xuan_mixloan_credit_data').' WHERE uid=:uid AND phone=:phone', array(':phone'=>$_GPC['mobile'], ':uid'=>$member['id']));
    if ($res) {
        show_json(-1, null, "你已经查询过了，请去历史报告中查看记录");
    }
    $insert = array(
        'uid'=>$member['id'],
        'certno'=>$_GPC['idcard'],
        'realname'=>$_GPC['name'],
        'phone'=>$_GPC['mobile'],
        'status'=>0,
        'createtime'=>time(),
        'uniacid'=>$_W['uniacid']
    );
    pdo_insert('xuan_mixloan_credit_data', $insert);
    $id = pdo_insertid();
    show_json(1, array('id'=>$id));
} else if ($operation == 'pay') {
    //支付方式
    include $this->template('credit/pay');
} else if ($operation == 'pay_submit') {
    //支付提交
    $id = intval($_GPC['id']);
    if (empty($id)) {
        message('id不存在', '', 'error');
    }
    $tid = "10003" . date('YmdHis', time());
    $_SESSION['credit_id'] = $id;
    $params = array(
        'tid' => $tid, 
        'ordersn' => $tid, 
        'title' => "信用查询付费", 
        'fee' => $config['credit_fee'], 
        'user' => $member['id'], 
    );
    //调用pay方法
    $this->pay($params);
    exit;
} else if ($operation == 'report_list') {
    //报告列表
    $list = pdo_fetchall("SELECT id,realname,createtime FROM ".tablename('xuan_mixloan_credit_data')." WHERE uid={$member['id']} ORDER BY id DESC");
    foreach ($list as &$row) {
        $row['tradeno'] = 'NY'.date('YmdHis', $row['createtime']);
    }
    unset($row);
    include $this->template('credit/report_list');
} else if ($operation == 'report_info') {
    //报告详情
    $id = intval($_GPC['id']);
    $report = pdo_fetch("SELECT * FROM ".tablename('xuan_mixloan_credit_data')." WHERE id={$id}");
    if ($report['status'] == 0) {
        $location = $this->createMobileUrl('credit', array('op'=>'pay', 'id'=>$id));
        header("location:{$location}");
    }
    include $this->template('credit/report_info');
}
?>