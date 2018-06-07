<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='index';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$agent = m('member')->checkAgent($member['id']);
if($operation=='index'){
	//首页
	$advs = m('channel')->getAdvs();
	$subjects = m('channel')->getSubjectList(['id', 'name', 'ext_info'], ['type'=>1]);
	$channel_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>1], 'sort DESC', 3);
	$channel_low_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>1], 'id DESC', 3);
	$credit_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>2], 'sort DESC', 3);
	$course_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>3], 'sort DESC', 3);
	$hot_list = m('channel')->getList(['id', 'title', 'apply_nums'], ['type'=>1, 'is_hot'=>1], 'sort DESC', 3);
	include $this->template('channel/index');
} elseif ($operation == 'credit_card') {
	//信用卡
	$advs = m('channel')->getAdvs();
	$subjects = m('channel')->getSubjectList(['id', 'name', 'ext_info'], ['type'=>2]);
	$channel_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>1], 'sort DESC', 3);
	$credit_low_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>2], 'id DESC', 3);
	$credit_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>2], 'sort DESC', 3);
	$course_list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['type'=>3], 'sort DESC', 3);
	$hot_list = m('channel')->getList(['id', 'title', 'apply_nums'], ['type'=>2, 'is_hot'=>1], 'sort DESC', 3);
	include $this->template('channel/credit_card');
} elseif ($operation == 'course') {
	//新手教程
	$course_list = m('channel')->getList(['id', 'title'], ['type'=>3], 'sort DESC');
	include $this->template('channel/course');
} else if ($operation == 'getNew') {
	//ajax获取新数据
	$type = intval($_GPC['type']);
	$offset = intval($_GPC['rollcount']);
	$subject = m('channel')->getSubjectList(['id', 'ext_info'], ['type'=>$type], 'id ASC', 1, $offset);
	if (empty($subject)) {
		show_json(-1);
	} else {
		$ids = array_keys($subject);
		$subjectRes = $subject[$ids[0]];
	}
	$list = m('channel')->getList(['id', 'title', 'subject_id', 'createtime', 'ext_info', 'apply_nums'], ['subject_id'=>$subjectRes['id']], 'sort DESC', 3);
	// $list = m('channel')->getList(['id', 'title', 'subject_id', 'createtime', 'ext_info', 'apply_nums'], ['type'=>$type], 'id DESC', 4, $offset);
	if (empty($list)) {
		show_json(-1);
	}
	$min_k = min(array_keys($list));
	$list[$min_k]['stress'] = 1;
	$list[$min_k]['ext_info']['pic'] = tomedia($subjectRes['ext_info']['pic']);
	show_json(1,array_values($list));
} else if ($operation == 'artical') {
	//详情
	if ($config['vip_channel']) {
		if ($agent['code']!=1) {
	        header("location:{$this->createMobileUrl('vip', array('op'=>'buy'))}");
		}
	}
    $banner = m('advs')->getList([],['type'=>1]);
	$id = intval($_GPC['id']);
	if (!$id) {
		message('id不能为空', '', 'error');
	}
	$res = m('channel')->getList([],['id'=>$id]);
	if (!$res) {
		message('抱歉，文章已不存在', '', 'error');
	}
	if ($_GPC['inviter'] && $_GPC['inviter'] != $member['id']) {
		m('member')->checkFirstInviter($openid, $_GPC['inviter']);
	}
	$item = $res[$id];
	pdo_update('xuan_mixloan_channel', array('apply_nums'=>$item['apply_nums']+1), array('id'=>$item['id']));
	// if (preg_match('/src=[\'\"]?([^\'\"]*)[\'\"]?/i', $item['ext_info']['content'], $result)) {
	// 	$share_image = $result[1];
	// } else {
	// 	$share_image = tomedia($config['share_image']);
	// }
	if (strip_tags($item['ext_info']['content'])) {
		$share_desc = strip_tags($item['ext_info']['content']);
	} else {
		$share_desc = $config['share_desc'];
	}
	if ($item['ext_info']['pic']) {
		$share_image = tomedia($item['ext_info']['pic']);
	} else {
		$share_image = tomedia($config['share_image']);
	}
	if ($agent['code'] == 1) {
		$share_link = $_W['siteroot'] . 'app/' .$this->createMobileUrl('channel', array('op'=>'artical', 'id'=>$id, 'inviter'=>$member['id']));
	} else {
		$share_link = $_W['siteroot'] . 'app/' .$this->createMobileUrl('channel', array('op'=>'artical', 'id'=>$id));
	}
	include $this->template('channel/artical');
} else if ($operation == 'search') {
	//搜索
	if ($_GPC['post'] == 1) {
		if ($_GPC['keyword']) {
			$keyword = trim($_GPC['keyword']);
		}
		$subjects = m('channel')->getSubjectList(['id'], ['name'=>$keyword]);
		if (!empty($subjects)) {
			$subjectIds = array_keys($subjects);
			$list =  m('channel')->getList(['id', 'title', 'apply_nums', 'createtime', 'ext_info'], ['subject_id'=>$subjectIds]);
		} else {
			$list = m('channel')->getList(['id', 'title', 'apply_nums', 'createtime', 'ext_info'], ['title'=>$keyword]);
		}
		if (!empty($list)) {
			show_json(1, array_values($list));
		}
		show_json(-1);
	}
	include $this->template('channel/search');
} else if ($operation == 'keyword') {
	//关键词联想
	if ($_GPC['keyword']) {
		$keyword = trim($_GPC['keyword']);
	}
	$list = m('channel')->getList(['id', 'title'], ['title'=>$keyword]);
	if (!empty($list)) {
		show_json(1, array_values($list));
	} else {
		show_json(-1);
	}
} else if ($operation == 'getCommendSubjects') {
	//随机出专题
	$subjects = m('channel')->getCommendSubjects();
	if (!empty($subjects)) {
		show_json(1, array_values($subjects));
	} else {
		show_json(-1);
	}
} else if ($operation == 'hot') {
	//热门文章
	$hot_list = m('channel')->getList([], ['is_hot'=>1]);
	include $this->template('channel/hot');
} else if ($operation == 'subject') {
	//专题
	$subject = m('channel')->getSubjectList(['id','ext_info'], ['id'=>$_GPC['id']]);
	if (empty($subject)) {
		message("专题已被删除啦");
	} else {
		$ids = array_keys($subject);
		$subjectRes = $subject[$ids[0]];
	}
	$list = m('channel')->getList(['id', 'title', 'createtime', 'ext_info', 'apply_nums'], ['subject_id'=>$subjectRes['id']]);
	include $this->template('channel/subject');
}
?>