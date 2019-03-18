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
    include $this->template('smallloan/index');
}
else if ($operation == 'category')
{
	$agent = m('member')->checkAgent($member['id']);
	if ($agent['code'] != 1) {
		message('您还不是代理哦', $this->createMobileUrl('vip', array('op' => 'buy')), 'error');
	}
    include $this->template('smallloan/category');
}
else if ($operation == 'add_loan')
{
	$agent = m('member')->checkAgent($member['id']);
	if ($agent['code'] != 1) {
		message('您还不是代理哦', $this->createMobileUrl('vip', array('op' => 'buy')), 'error');
	}
    include $this->template('smallloan/add_loan');
}
else if ($operation == 'my_loan')
{
    include $this->template('smallloan/my_loan');
}
else if ($operation == 'index') 
{
	// 首页
	$list = m('category')->getList(['id', 'name', 'ext_info'], ['type' => 3, 'parent_id' => 0], ' sort desc');
	foreach ($list as &$row) 
	{
		$row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
	}
	unset($row);
	show_json(1, ['list' => array_values($list)], '成功');
} 
else if ($operation == 'index_list') 
{
	// 首页列表
	$bind_id = intval($_GPC['bind_id']);
	$list = m('category')->getList(['id', 'name', 'ext_info'], ['type' => 3, 'parent_id' => $bind_id], ' sort desc');
	foreach ($list as &$row) 
	{
		$row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
		$row['nums'] = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_smallloan') . '
						where two_category=:two_category and status=1', array(':two_category' => $row['id'])) ? : 0;
	}
	unset($row);
	show_json(1, ['list' => array_values($list)], '成功');
}
else if ($operation == 'add_smallloan_submit') 
{
	// 添加小贷
	if (empty($member['id']))
	{
		show_json(-1, ['url' => $this->createMobileUrl('index', array('op' => 'login'))], '用户未登录');
	}
	$name = trim($_GPC['name']);
	$one_category = intval($_GPC['one_category']);
	$two_category = intval($_GPC['two_category']);
	$logo = trim($_GPC['logo_pic']);
	$qrcode = trim($_GPC['qrcode_pic']);
	$url = trim($_GPC['url']);
	if (empty($name))
	{
		show_json(-1, [], '小贷名字不能为空');
	}
	if (empty($one_category))
	{
		show_json(-1, [], '一级分类不能为空');
	}
	if (empty($two_category))
	{
		show_json(-1, [], '二级分类不能为空');
	}
	if (empty($logo))
	{
		show_json(-1, [], 'logo不能为空');
	}
	$record = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_smallloan') . '
					where name=:name', array(':name' => $name));
	if (!empty($record))
	{
		show_json(-1, [], '该产品已经有人上传过啦');
	}
	$insert = array();
	$ext_info = array();
	$ext_info['logo'] = $logo;
	$ext_info['qrcode'] = $qrcode;
	$ext_info['url'] = $url;
	$insert['uid'] = $member['id'];
	$insert['name'] = $name;
	$insert['one_category'] = $one_category; 
	$insert['two_category'] = $two_category; 
	$insert['createtime'] = time(); 
	$insert['ext_info'] = json_encode($ext_info);
	pdo_insert('xuan_mixloan_smallloan', $insert);
	show_json(1, ['url' => $this->createMobileUrl('smallloan', array('op' => 'my_loan'))], '上传成功，请等待审核');
}
else if ($operation == 'get_my_loan')
{
	// 得到贷款
	if (empty($member['id']))
	{
		show_json(-1, ['url' => $this->createMobileUrl('index', array('op' => 'login'))], '用户未登录');
	}
	$list = pdo_fetchall('select id,name,status from ' . tablename('xuan_mixloan_smallloan') . '
		where uid=:uid
		order by id desc', array(':uid' => $member['id']));
	foreach ($list as &$row) 
	{
		if ($row['status'] == 1) 
		{
			$row['status'] = '已通过';
		}
		else if ($row['status'] == -1) 
		{
			$row['status'] = '已驳回';
		}
		else if ($row['status'] == -2) 
		{
			$row['status'] = '已下架';
		}
		else if ($row['status'] == 0) 
		{
			$row['status'] = '审核中';
		}
	}
	unset($row);
	show_json(1, ['list' => $list], '成功');
}
else if ($operation == 'del_my_loan')
{
	$id = intval($_GPC['id']);
	$item = pdo_fetch('select uid,status from ' . tablename('xuan_mixloan_smallloan') . ' 
						where id=:id', array(':id' => $id));
	if ($item['uid'] != $member['id'])
	{
		show_json(-1, [], '您没有权限删除');
	}
	if ($item['status'] == 1 || $item['status'] == -2) 
	{
		show_json(-1, [], '审核通过，已无法删除');
	}
	pdo_delete('xuan_mixloan_smallloan', array('id' => $id));
	show_json(1, [], '删除成功');
}
else if ($operation == 'get_category')
{
	$id = intval($_GPC['id']);
	$keyword = trim($_GPC['keyword']);
	$wheres = ' and status=1';
	$cond = array();
	if (!empty($id))
	{
		$wheres .= " and two_category={$id}";
	}
	if (!empty($keyword))
	{
		$cates = pdo_fetchall('select id from ' . tablename('xuan_mixloan_category') . '
			where name like :name and parent_id<>0 and type=3', array(':name' => "%{$keyword}%"));
		if ($cates) {
			$ids = array();
			foreach ($cates as $cate) {
				$ids[] = $cate['id'];
			}
			$id_string = implode(',', $ids);
			$wheres .= " and two_category in {$id_string}";
		} else {
			$wheres .= " and name like :name";
			$cond[":name"] = "%{$keyword}%";
		}
	}
	$list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_smallloan') . "
				where 1 {$wheres}", $cond);
	foreach ($list as &$row) 
	{
		$row['ext_info'] = json_decode($row['ext_info'], 1);
		$row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);		
	}
	unset($row);
	show_json(1, ['list' => $list], '成功');
}
else if ($operation == 'get_code')
{
	$id = intval($_GPC['id']);
	$agent = m('member')->checkAgent($member['id']);
	if ($agent['code'] != 1) {
		show_json(-1, ['url' => $this->createMobileUrl('vip', array('op' => 'buy'))], '您还不是代理哦');
	}
	$item = pdo_fetch('select id,ext_info from ' . tablename('xuan_mixloan_smallloan') . ' 
						where id=:id', array(':id' => $id));
	$item['ext_info'] = json_decode($item['ext_info'], 1);
    $url = shortUrl($item['ext_info']['url'], $config);
	$intro = $item['ext_info']['intro'];
    $code = m('poster')->createQRcode($url);
	show_json(1, ['url' => $url, 'code' => $code, 'intro' => $intro], '成功');
}
else if ($operation == 'get_advs')
{
	// 获取广告
	$advs = pdo_fetchall('select * from ' . tablename('xuan_mixloan_smallloan_advs') . "
					where uniacid={$_W['uniacid']}");
	foreach ($advs as &$row) {
		$row['ext_info'] = json_decode($row['ext_info'], 1);
		$row['image'] = tomedia($row['ext_info']['pic']);
		$row['title'] = $row['name'];
		$row['url']   = $row['ext_info']['url'];
	}
	unset($row);
	show_json(1, $advs, '获取成功');
}