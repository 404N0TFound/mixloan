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
	$advs = m('loan')->getAdvs();
	$list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_loan') . '
							where uniacid=:uniacid and status=1
							order by id desc
							limit 50', array(':uniacid' => $_W['uniacid']));
	foreach ($list as &$row) {
		$row['ext_info'] = json_decode($row['ext_info'], 1);
		$row['category_name'] = pdo_fetchcolumn('select name from ' . tablename('xuan_mixloan_loan_category') . '
										where id=:id ', array(':id' => $row['category']));
	}
	unset($row);
    include $this->template('smallloan/index');
}
else if ($operation == 'category')
{
	$type = intval($_GPC['type']) ? : 1;
	$list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_loan_category') . "
							where uniacid=:uniacid and type={$type}
							order by sort desc", array(':uniacid' => $_W['uniacid']));
	foreach ($list as &$row) {
		$row['ext_info'] = json_decode($row['ext_info'], 1);
	}
	unset($row);
    include $this->template('smallloan/category');
}
else if ($operation == 'list')
{
	$agent = m('member')->checkAgent($member['id']);
	if ($agent['code'] != 1) {
		message('您还不是代理', $this->createMobileUrl('vip', array('op' => 'buy')), 'error');
	}
	$type = intval($_GPC['type']) ? : 1;
	$category = pdo_fetchall('select * from ' . tablename('xuan_mixloan_loan_category') . "
							where uniacid=:uniacid
							order by sort desc", array(':uniacid' => $_W['uniacid']));
    include $this->template('smallloan/list');
}
else if ($operation == 'getLoan') 
{
	//获取贷款列表
	$conditon = [];
	if (isset($_GPC['order']) && !empty($_GPC['order'])) {
		$orderBy = $_GPC['order'];
	} else {
		$orderBy = FALSE;
	}
	if (isset($_GPC['word']) && !empty($_GPC['word'])) {
		$condition['like_name'] = $_GPC['word'];
	}
	if (isset($_GPC['begin']) && !empty($_GPC['begin'])) {
		$condition['begin'] = $_GPC['begin'];
	}
	if (isset($_GPC['end']) && !empty($_GPC['end'])) {
		$condition['end'] = $_GPC['end'];
	}
	if (isset($_GPC['least']) && !empty($_GPC['least'])) {
		$condition['least'] = $_GPC['least'];
	}
	if (isset($_GPC['high']) && !empty($_GPC['high'])) {
		$condition['high'] = $_GPC['high'];
	}
	if (isset($_GPC['category']) && !empty($_GPC['category'])) {
		$condition['category'] = $_GPC['category'];
	}
	if (isset($_GPC['type']) && !empty($_GPC['type'])) {
		if ($_GPC['type'] == 4) {
			$ids = pdo_fetchall('select id from ' . tablename('xuan_mixloan_loan_category') . '
						where type=3');
			foreach ($ids as $value) {
				$id_arr[] = $value['id'];
			}
			$condition['category'] = $id_arr;
		} else {
			$condition['type'] = $_GPC['type'];
		}
	}
	$list = m('loan')->getList([], $condition, $orderBy);
	if (empty($list)) {
		show_json(-1);
	} else {
		foreach ($list as &$row) {
			$row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
			$row['group_id'] = $row['category'];
			$row['group_name'] = pdo_fetchcolumn('select name from ' . tablename('xuan_mixloan_loan_category') . '
										where id=:id ', array(':id' => $row['category']));
			$row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
		}
		unset($row);
		show_json(1, array_values($list));
	}
}
else if ($operation == 'get_code')
{
    $url = trim($_GPC['url']);
    $code = m('poster')->createQRcode($url);
    show_json(1, ['img' => $code], '成功');
}