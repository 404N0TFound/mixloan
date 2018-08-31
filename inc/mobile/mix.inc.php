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
} else if ($operation == 'announce') {
	//公告
	$cid = trim($_GPC['cid']);
	$announce = pdo_fetch('select id,ext_info from ' . tablename('xuan_mixloan_announce') . '
		where uniacid=:uniacid order by id desc', array(':uniacid' => $_W['uniacid']));
	if ($announce) {
		$announce['ext_info'] = json_decode($announce['ext_info'], 1);
		$record = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_announce_record') . '
			where relate_id=:relate_id and cid=:cid', array(':relate_id'=>$announce['id'], ':cid'=>$cid));
		if (!$record) {
			$insert = array();
			$insert['cid'] = $cid;
			$insert['relate_id'] = $announce['id'];
			pdo_insert('xuan_mixloan_announce_record', $insert);
			show_json(1, [], $announce['ext_info']['content']);
		}
	} else {
		show_json(-1);
	}
}