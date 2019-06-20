<?php  
session_start();
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
if(isset($_SESSION['userid']))$member = m('member')->getMemberById();
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='';
if($operation == 'getCode'){
	//发送验证码
	$time = time()-86400;
	$cache = rand(111111,999999);
	$phone = trim($_GPC['phone']);
	$content = "尊敬的用户，您的本次验证码为：{$cache}";
	if (sha1(md5(strtolower($_GPC['img_cache']))) != $_COOKIE['authcode']) {
        show_json(-1, [], "图形验证码不正确");
    }
    if ($_GPC['activity'] == 1) {
        $verify = pdo_fetchcolumn("SELECT count(1) FROM ".tablename('xuan_mixloan_member').' WHERE phone=:phone and uniacid=:uniacid', array('phone'=>$phone, ':uniacid'=>$_W['uniacid']));
        if ($verify) {
            show_json(102);
        }
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
				$result['message'] = '元程附件上传失败，请检查配置并重新上传';
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
} else if ($operation == 'msg_queue') {
	//队列消耗模板信息
	$result = pdo_fetch("SELECT * FROM ".tablename("xuan_mixloan_msg_queue")."
		WHERE status=0 ORDER BY id ASC");
	if (!empty($result)) {
		$insert = array();
		$max_id = $result['relate_id'] + 10000;
        $members = pdo_fetchall('select id from ' .tablename('xuan_mixloan_member'). "
        	where uniacid=:uniacid and id>={$result['relate_id']} and id <{$max_id}", array(':uniacid' => $_W['uniacid']));
        if (!empty($members)) {
	        foreach ($members as $member) {
	            $ext_info = json_decode($result['ext_info'], 1);
	            $temp = array(
	                'is_read'=>0,
	                'uid'=>0,
	                'createtime'=>time(),
	                'uniacid'=>$_W['uniacid'],
	                'to_uid'=>$member['id'],
	                'ext_info'=>"'" . addslashes(json_encode($ext_info)) . "'",
	            );
	            $temp_string = '('. implode(',', array_values($temp)) . ')';
	            $insert[] = $temp_string;
	        }
	        if (!empty($insert)) {
	            $insert_string =  implode(',', $insert);
	            pdo_run("INSERT " .tablename("xuan_mixloan_msg"). " ( `is_read`, `uid`, `createtime`, `uniacid`, `to_uid`, `ext_info`) VALUES {$insert_string}");
	            $count = count($insert);
	        }
	        pdo_update('xuan_mixloan_msg_queue', array('relate_id'=>$max_id), array('id' => $result['id']));
        } else {
        	pdo_update('xuan_mixloan_msg_queue', array('status'=>1), array('id' => $result['id']));
        }
		echo 'success';
	}
} else if ($operation == 'apply_temp') {
    //常规脚本
    $ids = [];
    if ($_GPC['type'] == 'temp') {
        $list = pdo_fetchall('SELECT id,phone FROM '.tablename('xuan_mixloan_product_apply').' where type=6');
        foreach ($list as $row) {
        	$id = pdo_fetchcolumn('select id from ' . tablename('xuan_mixloan_member') . '
        		where phone=:phone', array(':phone' => $row['phone']));
        	$update = array('inviter' => $id, 'uid' => $id);
        	pdo_update('xuan_mixloan_product_apply', $update, array('id' => $row['id']));
        }
    } 
    if (!empty($ids)) {
        echo implode(',', $ids);
    } else {
        echo 'empty';
    }
} else if ($operation == 'upload_file') {
    $fileroot = $_GPC['fileroot'];
    $filename = time() . rand(1,99999) . '.png';
    load()->library('qiniu');
    $auth = new Qiniu\Auth($_W['setting']['remote']['qiniu']['accesskey'], $_W['setting']['remote']['qiniu']['secretkey']);
    $config = new Qiniu\Config();
    $uploadmgr = new Qiniu\Storage\UploadManager($config);
    $putpolicy = Qiniu\base64_urlSafeEncode(json_encode(array(
        'scope' => $_W['setting']['remote']['qiniu']['bucket'] . ':' . $filename,
    )));
    $uploadtoken = $auth->uploadToken($_W['setting']['remote']['qiniu']['bucket'], $filename, 3600, $putpolicy);
    list($ret, $err) = $uploadmgr->putFile($uploadtoken, $filename, $fileroot);
    echo $_W['setting']['remote']['qiniu']['url'] . '/' . $filename ;
} else if ($operation == 'apply_analysis') {
    // 分析
    $endtime = strtotime(date("Y-m-d"));
    $starttime = $endtime - 86400;
    $list = pdo_fetchall('select count(*) as count,inviter from ' . tablename('xuan_mixloan_product_apply') . "
                    where degree=1 and createtime>{$starttime} and createtime<{$endtime} and type=1
                    group by inviter");
    foreach ($list as $row) {
        $count = pdo_fetchcolumn('select count(*) from ' . tablename('xuan_mixloan_apply_time') . "
                        where inviter={$row['inviter']} and last_time>8
                        and createtime>{$starttime} and createtime<{$endtime}") ? : 0;
        $rate = ($count / $row['count']) * 100;
        $insert = array();
        $insert['inviter'] = $row['inviter'];
        $insert['createtime'] = time();
        $insert['rate'] = $rate;
        $insert['count'] = $row['count'];
        pdo_insert('xuan_mixloan_apply_analysis', $insert);
    }
} else if ($operation == 'apply_update') {
    // 更新
    $starttime = strtotime(date("Y-m-d"));
    $last_day = $starttime - 86400;
    $inviters = pdo_fetchall('select inviter from ' . tablename('xuan_mixloan_apply_analysis') . "
        where createtime>{$starttime} and rate<10 and count>4");
    foreach ($inviters as $row) {
        $update = array('is_fake' => 1);
        $list = pdo_fetchall('select pid,phone from ' . tablename('xuan_mixloan_product_apply') . "
            where degree=1 and inviter={$row['inviter']} and type=1 and createtime>{$last_day}");
        foreach ($list as $value) {
            $condition = array('pid' => $value['pid'], 'phone' => $value['phone'], 'type' => 1);
            pdo_update('xuan_mixloan_product_apply', $update, $condition);
        }
    }
}