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
	$cache =  rand(111111,999999);
	if ($_GPC['activity'] == 1) {
		$verify = pdo_fetchcolumn("SELECT count(1) FROM ".tablename('xuan_mixloan_member').' WHERE phone=:phone', array('phone'=>$_GPC['phone']));
		if ($verify) {
			show_json(102);
		}
	}
	$content = "尊敬的用户，您的本次注册验证码为：{$cache}";
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
} else if ($operation == 'pay_result') {
    //易联支付结果通知
    $openid = m('user')->getOpenid();
    $member = m('member')->getInfo($openid);
    $result = array();
	require_once(IA_ROOT . '/addons/xuan_mixloan/lib/yilian/Notify.php');
	if ($result['RetCode'] != "0000") {
		message($result['RetMsg'], $this->createMobileUrl('vip', ['op'=>'buy']), 'error');
	}
    if (empty($member['id'])) {
        header("location:{$this->createMobileUrl('user')}");
    }
    $agent = m('member')->checkAgent($member['id']);
    if ($agent['code'] == 1) {
        message("您已经是会员，请不要重复提交", $this->createMobileUrl('user'), "error");
    }
    $insert = array(
        "uniacid"=>$_W["uniacid"],
        "uid"=>$member['id'],
        "createtime"=>time(),
        "tid"=>$orderId,
        "fee"=>$fee,
    );
    pdo_insert("xuan_mixloan_payment", $insert);
    //模板消息提醒
    $datam = array(
        "first" => array(
            "value" => "您好，您已购买成功",
            "color" => "#173177"
        ) ,
        "name" => array(
            "value" => "{$config['title']}代理会员",
            "color" => "#173177"
        ) ,
        "remark" => array(
            "value" => '点击查看详情',
            "color" => "#4a5077"
        ) ,
    );
    $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'salary'));
    $account = WeAccount::create($_W['acid']);
    $account->sendTplNotice($openid, $config['tpl_notice2'], $datam, $url);
    $inviter = m('member')->getInviter($member['phone'], $member['openid']);
    if ($inviter && $config['inviter_fee_one']) {
        $insert_i = array(
            'uniacid' => $_W['uniacid'],
            'uid' => $member['id'],
            'phone' => $member['phone'],
            'certno' => $member['certno'],
            'realname' => $member['realname'],
            'inviter' => $inviter,
            'extra_bonus'=>0,
            'done_bonus'=>0,
            're_bonus'=>$config['inviter_fee_one'],
            'status'=>2,
            'createtime'=>time(),
            'degree'=>1
        );
        pdo_insert('xuan_mixloan_product_apply', $insert_i);
        //模板消息提醒
        $one_openid = m('user')->getOpenid($inviter);
        $datam = array(
            "first" => array(
                "value" => "您好，您的徒弟{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
                "color" => "#173177"
            ) ,
            "order" => array(
                "value" => $orderId,
                "color" => "#173177"
            ) ,
            "money" => array(
                "value" => $config['inviter_fee_one'],
                "color" => "#173177"
            ) ,
            "remark" => array(
                "value" => '点击查看详情',
                "color" => "#4a5077"
            ) ,
        );
        $account = WeAccount::create($_W['acid']);
        $account->sendTplNotice($one_openid, $config['tpl_notice5'], $datam, $url);
        //二级
        $man = m('member')->getInviterInfo($inviter);
        $inviter = m('member')->getInviter($man['phone'], $man['openid']);
        if ($inviter && $config['inviter_fee_two']) {
            $insert_i = array(
                'uniacid' => $_W['uniacid'],
                'uid' => $member['id'],
                'phone' => $member['phone'],
                'certno' => $member['certno'],
                'realname' => $member['realname'],
                'inviter' => $inviter,
                'extra_bonus'=>0,
                'done_bonus'=>0,
                're_bonus'=>$config['inviter_fee_two'],
                'status'=>2,
                'createtime'=>time(),
                'degree'=>2
            );
            pdo_insert('xuan_mixloan_product_apply', $insert_i);
            //模板消息提醒
            $two_openid = m('user')->getOpenid($inviter);
            $datam = array(
                "first" => array(
                    "value" => "您好，您的徒弟{$man['nickname']}邀请了{$member['nickname']}成功购买了代理会员，奖励您推广佣金，继续推荐代理，即可获得更多佣金奖励",
                    "color" => "#173177"
                ) ,
                "order" => array(
                    "value" => $orderId,
                    "color" => "#173177"
                ) ,
                "money" => array(
                    "value" => $config['inviter_fee_two'],
                    "color" => "#173177"
                ) ,
                "remark" => array(
                    "value" => '点击查看详情',
                    "color" => "#4a5077"
                ) ,
            );
            $account = WeAccount::create($_W['acid']);
            $account->sendTplNotice($two_openid, $config['tpl_notice5'], $datam, $url);
        }
    }
    message("支付成功", $this->createMobileUrl('user'), "success");
     
} else if ($operation == 'pay_query') {
	//代付查询
	$list = pdo_fetchall('SELECT id,ext_info FROM '.tablename('xuan_mixloan_withdraw').' WHERE uniacid=:uniacid AND status=1', array(':uniacid'=>$_W['uniacid']));
	if (empty($list)) {
		echo 'empty';
	}
	$ids = [];
	foreach ($list as $row) {
		$ext_info = json_decode($row['ext_info'], true);
		if (empty($ext_info)) {
			continue;
		}
		$SN = $ext_info['SN'];
		$MER_ORDER_NO = $ext_info['MER_ORDER_NO'];
		$batchNo = $ext_info['batchNo'];
		if (empty($SN) || empty($MER_ORDER_NO) || empty($batchNo)) {
			continue;
		}
		require_once('../addons/xuan_mixloan/lib/yilian_pay/pay_query.php');
		if ($res['TRANS_STATE'] == '0000') {
			if ($res['TRANS_DETAILS'][0]['PAY_STATE'] == "0000") {
				pdo_update('xuan_mixloan_withdraw', array('status'=>2), array('id'=>$row['id']));
				$ids[] = $row['id'];
			}
		}
	}
	echo json_encode($ids);
} else if ($operation == 'apply_temp') {
    //常规脚本
    $ids = [];
    if ($_GPC['type'] == 'temp') {
        $list = pdo_fetchall('SELECT * FROM '.tablename('xuan_mixloan_payment').' WHERE uniacid=:uniacid', array(':uniacid'=>$_W['uniacid']));
        foreach ($list as $row) {
            $all = pdo_fetchcolumn("SELECT SUM(re_bonus+done_bonus+extra_bonus) FROM ".tablename("xuan_mixloan_product_apply")." WHERE uniacid={$_W['uniacid']} AND inviter={$row['uid']}");
            $row['left_bonus'] = $all - m('member')->sumWithdraw($row['uid']);
            if ($row['left_bonus']<0) {
                $bonus = pdo_fetch('select id,extra_bonus from '.tablename('xuan_mixloan_product_apply').' where inviter=:inviter and status>1', array(':inviter'=>$row['uid']));
                if ($bonus) {
                    pdo_update('xuan_mixloan_product_apply', array('extra_bonus'=>$bonus['extra_bonus']-$row['left_bonus']), array('id'=>$bonus['id']));
                } else {
                	$insert = array(
                		'uniacid'=>$_W['uniacid'],
                		'uid'=>0,
                		'pid'=>29,
                		'phone'=>18678350582,
                		'certno'=>'371402198803251212',
                		'realname'=>'李龙',
                		'inviter'=>$row['uid'],
                		'extra_bonus'=>-$row['left_bonus'],
                		'createtime'=>time(),
                		'status'=>2,
                		'degree'=>1
                	);
                	pdo_insert('xuan_mixloan_product_apply', $insert);
                }
                $ids[] = $row['uid'];
            }
        }
    } else if ($_GPC['type'] == 'query') {
    	$item = pdo_fetch('SELECT id,ext_info FROM '.tablename('xuan_mixloan_withdraw').' WHERE id=:id', array(':id'=>$_GPC['id']));
		if (empty($item)) {
			echo 'empty';
		}
		$ext_info = json_decode($item['ext_info'], true);
		if (empty($ext_info)) {
			continue;
		}
		$SN = $ext_info['SN'];
		$MER_ORDER_NO = $ext_info['MER_ORDER_NO'];
		$batchNo = $ext_info['batchNo'];
		if (empty($SN) || empty($MER_ORDER_NO) || empty($batchNo)) {
			continue;
		}
		require_once('../addons/xuan_mixloan/lib/yilian_pay/pay_query.php');
		var_dump($res);die;
    }
    if (!empty($ids)) {
        echo implode(',', $ids);
    } else {
        echo 'empty';
    }
}