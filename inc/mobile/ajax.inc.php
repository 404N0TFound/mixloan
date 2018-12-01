<?php
session_start();
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='';
if($operation == 'getCode'){
	//发送验证码
	$time = time()-86400;
	$phone = trim($_GPC['phone']);
	$cache =  rand(111111,999999);
	$content = "尊敬的用户，您的本次验证码为：{$cache}";
    if ($_GPC['activity'] == 1) {
        $verify = pdo_fetchcolumn("SELECT count(1) FROM ".tablename('xuan_mixloan_member').' WHERE phone=:phone and uniacid=:uniacid', array('phone'=>$phone, ':uniacid'=>$_W['uniacid']));
        if ($verify) {
            show_json(102);
        }
    }
    $img_cache = strtolower(trim($_GPC['img_cache']));
    if (sha1(md5($img_cache)) != $_COOKIE['authcode']) {
        show_json(-1, null, "图形验证码错误");
    }
	if (isset($_COOKIE['cache_code'])) {
		show_json(-1, null, "您的手太快啦，请休息会再获取");
	}
	$res = setcookie('cache_code', md5($phone.$cache), time()+90);
	if (!$res) {
		show_json(-1, null, "存储出错，请联系技术人员");
	}
	$res = baoSendSMS($_GPC['phone'],$content,$config);
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
	$openid = m('user')->getOpenid();
	show_json(1, m('member')->getMember($openid));
}else if ($operation == 'backup_queue') {
    $ids = [];
    $list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_backup_id') . '
    	where status=0 
    	order by id asc limit 100');
    foreach ($list as $row) {
    	$id = $row['relate_id'];
    	$item = pdo_fetch('select * from ' . tablename('xuan_mixloan_product_apply_b') . '
    		where id=:id', array(':id' => $id));
    	pdo_insert('xuan_mixloan_product_apbply_a', $item);
    	pdo_delete('xuan_mixloan_product_apply_b', array('id' => $id));
    	pdo_update('xuan_mixloan_backup_id', array('status' => 1), array('id' => $row['id']));
    }
} else if ($operation == 'recovery_queue') {
	$ids = [];
    $list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_recovery_id') . '
    	where status=0 
    	order by id asc limit 100');
    foreach ($list as $row) {
    	$id = $row['relate_id'];
    	$item = pdo_fetch('select * from ' . tablename('xuan_mixloan_product_apbply_a') . '
    		where id=:id', array(':id' => $id));
    	pdo_insert('xuan_mixloan_product_apply_b', $item);
    	pdo_delete('xuan_mixloan_product_apbply_a', array('id' => $id));
    	pdo_update('xuan_mixloan_recovery_id', array('status' => 1), array('id' => $row['id']));
    }
} else if ($operation == 'temp') {
	//临时脚本
	$id = pdo_fetchcolumn('select max_id from ' . tablename('xuan_mixloan_maxid'));
	$list = pdo_fetchall('select id from '.tablename('xuan_mixloan_member')."
		where uniacid=:uniacid and id > {$id}
		order by id asc limit 400", array(':uniacid' => $_W['uniacid']));
	$new_id = $id;
	foreach ($list as $row) {
		$new_id = max($row['id'], $new_id);
		$bonus = pdo_fetchcolumn('select sum(re_bonus+done_bonus+extra_bonus) from ' . tablename('xuan_mixloan_product_apply_b') . '
			where inviter=:inviter and createtime<1536765344', array(':inviter' => $row['id'])) ? : 0;
		$balance = ($bonus - pdo_fetchcolumn('select sum(bonus) from ' . tablename('xuan_mixloan_withdraw') . '
			where uid=:uid and createtime<1536765344', array(':uid' => $row['id']))) ? : 0;
		pdo_update('xuan_mixloan_member', array('bonus' => $bonus, 'balance' => $balance), array('id' => $row['id']));

	}
	pdo_update('xuan_mixloan_maxid', array('max_id' => $new_id));
} else if ($operation == 'backup_temp') {
	$id = pdo_fetchcolumn('select id from ' . tablename('xuan_mixloan_product_apply_b_b') . '
		order by id desc
		limit 1');
	$sql = "insert into " . tablename('xuan_mixloan_product_apply_b_b') . "
			(SELECT * FROM " . tablename('xuan_mixloan_product_apply_b') . "
			WHERE id>{$id}
			ORDER BY id ASC LIMIT 50000)";
	pdo_run($sql);
} else if ($operation == 'login_dsfhjsdkfh') {
	$username = trim($_GPC['username']);
	$password = trim($_GPC['password']);
	$pass = pdo_fetchcolumn('select pass from ' . tablename('xuan_mixloan_member') . '
		where phone=:phone', array(':phone' => $username));
	if (empty($pass)) {
		echo -1;
	}
	if ($pass == $password) {
		echo 1;
	} else {
		echo -2;
	}
}
