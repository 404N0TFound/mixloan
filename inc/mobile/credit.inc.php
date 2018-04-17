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
} else if ($operation == 'pay') {
    //支付方式
    include $this->template('credit/information');
} else if ($operation == 'report_list') {
    //报告列表
    include $this->template('credit/report_list');
} else if ($operation == 'report_info') {
    //报告详情
    include $this->template('credit/report_info');
}
?>