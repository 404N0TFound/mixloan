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
} else if ($operation == 'questions') {
	//问题中心
	include $this->template('mix/questions');
} else if ($operation == 'adv') {
	//广告
	$id = intval($_GPC['id']);
	$adv = m('advs')->getList(['id', 'ext_info'], ['id'=>$id])[$id];
	$insert = array(
		'uniacid'=>$_W['uniacid'],
		'uid'=>$member['id'],
		'createtime'=>time(),
		'adv_id'=>$id,
	);
	pdo_insert('xuan_mixloan_advs_click', $insert);
	header("location:{$adv['ext_info']['url']}");	
}