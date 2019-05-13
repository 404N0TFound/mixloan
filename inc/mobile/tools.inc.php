<?php  
defined('IN_IA') or exit('Access Denied');
header("Access-Control-Allow-Origin:*");
global $_GPC,$_W;
$config    = $this->module['config'];
$operation = $_GPC['op'] ? : 'default';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
if ($operation == 'default')
{
	$list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_tools') . '
							where uniacid=:uniacid
							order by sort desc', array(':uniacid' => $_W['uniacid']));
	foreach ($list as &$row) {
		$row['ext_info'] = json_decode($row['ext_info'], 1);
	}
	unset($row);
    include $this->template('tools/index');
}