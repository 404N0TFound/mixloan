<?php  
session_start();
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='';
if($operation == 'getCode'){
	//发送验证码
	$time = time()-86400;
	$cache =  rand(111111,999999);
	$phone = trim($_GPC['phone']);
	if($_GPC['type']=='register'){
		$content = "尊敬的用户，您的本次注册验证码为：{$cache}";
	}
	if (isset($_COOKIE['cache_code'])) {
		show_json(-1, null, "您的手太快啦，请休息会再获取");
	}
	$res = setcookie('cache_code', md5($phone.$cache), time()+90);
	if (!$res) {
		show_json(-1, null, "存储出错，请联系技术人员");
	}
	if ($config['sms_type'] == 1) {
		$res = baoSendSMS($phone,$content,$config);
		if($res==0){
			show_json(0, null, "发送验证码成功");
		}else if($res==42){
			show_json(-1, null, "短信帐号过期");
		}else if($res==30){
			show_json(-1, null, "短信密码错误");
		}else if($res==41){
			show_json(-1, null, "短信余额不足");
		}else{
			show_json(-1, null, "未知错误，错误代码{$res}");
		}
	} else {
		$res = juheSend($phone, $cache, $config);
		if ($res['code'] == 1) {
			show_json(0, null, "发送验证码成功");
		} else {
			show_json(-1, null, $res['msg']);
		}
	}
}else if($operation == 'register'){
	//注册
	$sql = " SELECT count(*) FROM ".tablename("xuan_mixloan_member")." WHERE uniacid=:uniacid and phone=:phone";
	$res = pdo_fetchcolumn($sql,array(":uniacid"=>$_W["uniacid"],"phone"=>$_GPC["phone"]));
	if($res){
		die(json_encode(array("result"=>-1,"msg"=>"该手机号已被注册！")));
	}
	if(!empty($member['phone'])){
		die(json_encode(array("result"=>-1,"msg"=>"该微信用户已经绑定手机号，不能二次注册")));
	}
	if($_COOKIE['cache']!=md5($_GPC['cache'])){
		die(json_encode(array("result"=>-1,"msg"=>"验证码错误")));
	}
	$array = array(
			"uniacid"=>$_W["uniacid"],
			"phone"=>$_GPC["phone"],
			"password"=>md5($_GPC['password']),
			"inviter"=>$_GPC['inviter'],
			"createtime"=>time(),
			"status"=>0, 
			"nickname"=>"注册用户".substr($_GPC['phone'], 7),
	);
	pdo_insert("xuan_mixloan_member",$array);
	die(json_encode(array("result"=>1,"msg"=>"注册成功！")));
}else if ($operation == 'upload') {
	//上传图片
	$setting = $_W['setting']['upload'][$type];
	$result = array(
		'jsonrpc' => '2.0',
		'id' => 'id',
		'error' => array('code' => 1, 'message'=>''),
	);
	load()->func('file');
	if (empty($_FILES['file']['tmp_name'])) {
		$binaryfile = file_get_contents('php://input', 'r');
		if (!empty($binaryfile)) {
			mkdirs(ATTACHMENT_ROOT . '/temp');
			$tempfilename = random(5);
			$tempfile = ATTACHMENT_ROOT . '/temp/' . $tempfilename;
			if (file_put_contents($tempfile, $binaryfile)) {
				$imagesize = @getimagesize($tempfile);
				$imagesize = explode('/', $imagesize['mime']);
				$_FILES['file'] = array(
					'name' => $tempfilename . '.' . $imagesize[1],
					'tmp_name' => $tempfile,
					'error' => 0,
				);
			}
		}
	}
	if (!empty($_FILES['file']['name'])) {
		if ($_FILES['file']['error'] != 0) {
			$result['error']['message'] = '上传失败，请重试！';
			die(json_encode($result));
		}
		$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		$ext = strtolower($ext);

		$file = file_upload($_FILES['file']);
		if (is_error($file)) {
			$result['error']['message'] = $file['message'];
			die(json_encode($result));
		}

		$pathname = $file['path'];
		$fullname = ATTACHMENT_ROOT . '/' . $pathname;

		$thumb = empty($setting['thumb']) ? 0 : 1; 			$width = intval($setting['width']); 			if ($thumb == 1 && $width > 0 && (!isset($_GPC['thumb']) || (isset($_GPC['thumb']) && !empty($_GPC['thumb'])))) {
			$thumbnail = file_image_thumb($fullname, '', $width);
			@unlink($fullname);
			if (is_error($thumbnail)) {
				$result['message'] = $thumbnail['message'];
				die(json_encode($result));
			} else {
				$filename = pathinfo($thumbnail, PATHINFO_BASENAME);
				$pathname = $thumbnail;
				$fullname = ATTACHMENT_ROOT .'/'.$pathname;
			}
		}
		$info = array(
			'name' => $_FILES['file']['name'],
			'ext' => $ext,
			'filename' => $pathname,
			'attachment' => $pathname,
			'url' => tomedia($pathname),
			'is_image' => 1,
			'filesize' => filesize($fullname),
		);
		$size = getimagesize($fullname);
		$info['width'] = $size[0];
		$info['height'] = $size[1];
		
		setting_load('remote');
		if (!empty($_W['setting']['remote']['type'])) {
			$remotestatus = file_remote_upload($pathname);
			if (is_error($remotestatus)) {
				$result['message'] = '远程附件上传失败，请检查配置并重新上传';
				file_delete($pathname);
				die(json_encode($result));
			} else {
				file_delete($pathname);
				$info['url'] = tomedia($pathname);
			}
		}
		
		pdo_insert('core_attachment', array(
			'uniacid' => $uniacid,
			'uid' => $_W['uid'],
			'filename' => $_FILES['file']['name'],
			'attachment' => $pathname,
			'type' => $type == 'image' ? 1 : 2,
			'createtime' => TIMESTAMP,
		));
		die(json_encode($info));
	} else {
		$result['error']['message'] = '请选择要上传的图片！';
		die(json_encode($result));
	}
} else if ($operation == 'queue') {
	//队列消耗模板信息
	$notices = pdo_fetchall("SELECT * FROM ".tablename("xuan_mixloan_notice")." WHERE uniacid=:uniacid AND status=0 ORDER BY id ASC LIMIT 50", array(':uniacid'=>$_W['uniacid']));
	if (!empty($notices)) {
		$count = 0;
		$filed = array();
        $account = WeAccount::create($_W['acid']);
		foreach ($notices as $row) {
			$data = json_decode($row['data'], 1);
			$res = $account->sendTplNotice($row['openid'], $row['template_id'], $data, $row['url']);
			if (!is_array($res)) {
				$count += pdo_update('xuan_mixloan_notice', array('status'=>1), array('id'=>$row['id']));
			} else {
				$filed[$row['id']] = $res['message'];
				pdo_update('xuan_mixloan_notice', array('status'=>-1), array('id'=>$row['id']));
			}
		}
		echo json_encode(['success_count'=>$count, 'filed'=>$filed]);
	} else {
		echo json_encode(['msg'=>'the queue is empty']);
	}
} else if ($operation == 'checkMember') {
	//得到会员信息
	$openid = m('user')->getOpenid();
	show_json(1, m('member')->getMember($openid));
} else if ($operation == 'apply_bank') {
	//银行列表
	$ids = array();
	$banks = m('applyApi')->bankList();
	foreach ($banks as $row)
	{
		$record = pdo_fetchcolumn('select count(1) from ' .tablename('xuan_mixloan_bank'). '
			where uniacid=:uniacid and code=:code', array(':uniacid' => $_W['uniacid'], ':code' => $row['id']));
		if (empty($record))
		{
			$ext_info = json_encode(array('stationChannelId' => $row['stationChannelId'], 'logo' => $row['iconPath']));
			$insert = array('uniacid' => $_W['uniacid'], 'name' => $row['name'], 'code' => $row['id'], 'ext_info' => $ext_info, 'createtime' => time());
			pdo_insert('xuan_mixloan_bank', $insert);
			$bank_id = pdo_insertid();
			$bank_ids[$row['id']] = $bank_id;
			$stationChannelIds[$row['id']] = $row['stationChannelId'];
			$ids[] = $row['id'];
		}
	}
	if (!empty($ids))
	{
		$cards = m('applyApi')->bankCards($ids);
		foreach ($cards as $card)
		{
			$record = pdo_fetchcolumn('select count(1) from ' .tablename('xuan_mixloan_bank_card'). '
				where uniacid=:uniacid and code=:code', array(':uniacid' => $_W['uniacid'], ':code' => $card['stationBankCardChannelId']));
			if ($record)
			{
				continue;
			}
			$detail = m('applyApi')->bankCard($card['stationBankCardChannelId']);
			$cardName = str_replace("\t", "", $detail['bankCard']['name']);
			$tags = array();
			foreach ($detail["bankCardAttrList"] as $val)
			{
				if (count($tags) < 3) 
				{
					$tags[] = $val['description'];
				}
				else
				{
					break;
				}
			}
			$ext_info = json_encode( array('intro' => $detail['bankCard']['description'], 'pic' => $detail['bankCard']['imgUrlPath'], 'tag' => $tags, 'v_name' => $cardName) );
			$insert = array('name' => $cardName, "ext_info" => $ext_info, "sort" => $detail['bankCard']['sort'], 'createtime' => time(), 'uniacid' => $_W['uniacid'], 'bank_id' => $bank_ids[$detail['bankCard']['bankId']], 'code' => $stationChannelIds[$detail['bankCard']['bankId']]);
			pdo_insert('xuan_mixloan_bank_card', $insert);
		}
	}
} else if ($operation == 'apply_loan') {
	//贷款列表
	$loans = m('applyApi')->loanList();
	foreach ($loans as $loan) {
		$record = pdo_fetchcolumn('select count(1) from ' .tablename('xuan_mixloan_loan'). '
				where uniacid=:uniacid and code=:code', array(':uniacid' => $_W['uniacid'], ':code' => $card['stationBankCardChannelId']));
		if ($record)
		{
			continue;
		}
		$ext_info = json_encode(array('logo' => $loan['iconPath'], 'profitType' => $loan['profitType'], 'v_name' => $loan['description']));
		$insert = array('name' => $loan['name'], 'ext_info' => $ext_info, 'sort' => $loan['sort'], 'code' => $loan['id'], 'uniacid' => $_W['uniacid'], 'createtime' => time());
		pdo_insert('xuan_mixloan_loan', $insert);
	}
} else if ($operation == 'apply_return') {
	//接口返回
	$json = file_get_contents("php://input");
	$json = preg_replace('/\'/', '"', $json);
	$data = json_decode($json, true);
	if (!empty($data['result']['clientNo']) && $data['result']['callbackType'] == 'CALLBACK_SUCCESS')
	{
		$id = $data['result']['clientNo'];
		$item = m('product')->getApplyList(['id', 'phone', 'pid'], ['id' => $id])[$id];
		$product = m('product')->getList([], ['id' => $item['pid']])[$item['pid']];
		if ($item && $product)
		{
			$one_update = array('status' => 2);
			if ($product['ext_info']['done_one_init_reward_per'])
			{
				$one_update['done_bonus'] = $product['ext_info']['done_one_init_reward_per'] * $data['result']['amount'] * 0.01;
			}
			else if ($product['ext_info']['done_one_init_reward_money'])
			{
				$one_update['done_bonus'] = $product['ext_info']['done_one_init_reward_money'];
			}
			if ($product['ext_info']['re_one_init_reward_per']) 
			{
				$one_update['done_bonus'] = $product['ext_info']['re_one_init_reward_per'] * $data['result']['amount'] * 0.01;
			}
			else if ($product['ext_info']['re_one_init_reward_money'])
			{
				$one_update['re_bonus'] = $product['ext_info']['re_one_init_reward_money'];
			}
			pdo_update('xuan_mixloan_product_apply', $one_update, array('id' => $id));
			$two = pdo_fetch('select id from ' .tablename('xuan_mixloan_product_apply'). '
				where phone=:phone and uniacid=:uniacid and pid=:pid and degree=2', array(':phone' => $item['phone'], ':pid' => $item['pid'], ':uniacid' => $_W['uniacid']));
		}
		if ($two)
		{
			$two_update = array('status' => 2);
			if ($product['ext_info']['done_two_init_reward_per'])
			{
				$one_update['done_bonus'] = $product['ext_info']['done_one_init_reward_per'] * $data['result']['amount'] * 0.01;
			}
			else if ($product['ext_info']['done_two_init_reward_money'])
			{
				$two_update['done_bonus'] = $product['ext_info']['done_two_init_reward_money'];
			}
			if ($product['ext_info']['re_two_init_reward_per']) 
			{
				$one_update['done_bonus'] = $product['ext_info']['re_one_init_reward_per'] * $data['result']['amount'] * 0.01;
			}
			else if ($product['ext_info']['re_two_init_reward_money'])
			{
				$two_update['re_bonus'] = $product['ext_info']['re_two_init_reward_money'];
			}
			pdo_update('xuan_mixloan_product_apply', $two_update, array('id' => $two['id']));
		}
	}
}


?>