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
	if($_GPC['type']=='register'){
		$content = "尊敬的用户，您的本次注册验证码为：{$cache}";
	}
	if (isset($_COOKIE['cache_code'])) {
		show_json(-1, null, "您的手太快啦，请休息会再获取");
	}
	$res = setcookie('cache_code', md5($cache), time()+90);
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
}else if ($operation == 'apply_temp') {
    //常规脚本
    $ids = [];
    if ($_GPC['type'] == 'product_apply') {
        $list = pdo_fetchall('SELECT id,uid,inviter FROM '.tablename('xuan_mixloan_product_apply').' WHERE uniacid=:uniacid', array(':uniacid'=>$_W['uniacid']));
        foreach ($list as $key => $value) {
            if ($value['uid'] == $value['inviter']) {
                $ids[] = $value['id'];
            }
        }
    } else if ($_GPC['type'] == 'qrcode') {
        $list = pdo_fetchall('SELECT a.id,a.qrcid,a.openid,b.id as uid FROM '.tablename('qrcode_stat').' a left join '.tablename('xuan_mixloan_member').' b ON a.openid=b.openid WHERE a.uniacid=:uniacid AND a.type=1 GROUP BY a.openid', array(':uniacid'=>$_W['uniacid']));
        foreach ($list as $key => $value) {
            if ($value['qrcid'] == $value['uid']) {
                if ($_GPC['update']) {
                    pdo_update('qrcode_stat', array('type'=>2), array('qrcid'=>$value['uid'], 'openid'=>$value['openid']));
                }
                $ids[] = $value['id'];
            }
        }
    } else if ($_GPC['type'] == 'inivter') {
        $list = pdo_fetchall('SELECT a.id,a.phone,a.uid,b.id as member_id FROM '.tablename('xuan_mixloan_inviter').' a left join '.tablename('xuan_mixloan_member').' b ON a.phone=b.phone WHERE a.uniacid=:uniacid', array(':uniacid'=>$_W['uniacid']));
        foreach ($list as $key => $value) {
            if ($value['uid'] == $value['member_id']) {
                if ($_GPC['update']) {
                    pdo_delete('xuan_mixloan_inviter', array('id'=>$value['id']));
                }
                $ids[] = $value['id'];
            }
        }
    } else if ($_GPC['type'] == 'temp') {
        $list = pdo_fetchall('SELECT * FROM '.tablename('xuan_mixloan_payment').' WHERE uniacid=:uniacid', array(':uniacid'=>$_W['uniacid']));
        foreach ($list as $row) {
            $all = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE uniacid={$_W['uniacid']} AND inviter={$row['uid']}");
            $row['left_bonus'] = $all - m('member')->sumWithdraw($row['uid']);
            if ($row['left_bonus']<0) {
                if ($_GPC['update']) {
                	$temp = pdo_fetch('SELECT id,extra_bonus FROM '.tablename('xuan_mixloan_product_apply')." WHERE inviter={$row['uid']} AND status>0 ORDER BY id ASC");
                	if (!empty($temp)) {
                		pdo_update('xuan_mixloan_product_apply', array('extra_bonus'=>$temp['extra_bonus']-$row['left_bonus']), array('id'=>$temp['id']));
                	} else {
	                	$insert = array(
	                		'uniacid'=>$_W['uniacid'],
	                		'uid'=>0,
	                		'pid'=>1,
	                		'phone'=>18270088787,
	                		'certno'=>362532199109141716,
	                		'realname'=>'赖敏',
	                		'inviter'=>$row['uid'],
	                		'extra_bonus'=>-$row['left_bonus'],
	                		'createtime'=>time(),
	                		'status'=>2,
	                		'degree'=>1
	                	);
	                	pdo_insert('xuan_mixloan_product_apply', $insert);
                	}
                }
                $ids[] = $row['uid'];
            }
        }
    }
    if (!empty($ids)) {
        echo implode(',', $ids);
    } else {
        echo 'empty';
    }
} else if ($operation == 'temp') {
	//临时脚本
	$list = pdo_fetchall('SELECT id,img_url FROM '.tablename('xuan_mixloan_withdraw_qrcode').' WHERE uniacid=:uniacid', array(':uniacid'=>$_W['uniacid']));
	foreach ($list as $row) {
		if (strstr($row['img_url'], 'wx.yonka.wang')) {
			$img_url = str_replace('wx.yonka.wang', 'tk.yonka.wang', $row['img_url']);
			// echo $img_url;
			pdo_update('xuan_mixloan_withdraw_qrcode', array('img_url'=>$img_url), array('id'=>$row['id']));
		}
	}
}


?>