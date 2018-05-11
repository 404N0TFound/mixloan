<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='index';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$member['user_type'] = m('member')->checkAgent($member['id']);
if($operation=='index'){
	//信用查询中心首页
	include $this->template('credit/index');
} else if ($operation == 'information') {
    //基本信息
    include $this->template('credit/information');
} else if ($operation == 'insertInformation') {
    //基本信息提交
    $idcard = trim($_GPC['idcard']);
    $name = trim($_GPC['name']);
    $mobile = trim($_GPC['mobile']);
    $res = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('xuan_mixloan_credit_data').' WHERE uid=:uid AND phone=:phone', array(':phone'=>$mobile, ':uid'=>$member['id']));
    if ($res) {
        show_json(-1, null, "你已经查询过了，请去历史报告中查看记录");
    }
    if (empty($idcard)) {
        show_json(-1, null, "请输入身份证");
    }
    if (empty($name)) {
        show_json(-1, null, "请输入真实姓名");
    }
    if (empty($mobile)) {
        show_json(-1, null, "请输入手机号");
    }
    $insert = array(
        'uid'=>$member['id'],
        'certno'=>$idcard,
        'realname'=>$name,
        'phone'=>$mobile,
        'status'=>0,
        'createtime'=>time(),
        'uniacid'=>$_W['uniacid']
    );
    pdo_insert('xuan_mixloan_credit_data', $insert);
    $id = pdo_insertid();
    show_json(1, array('id'=>$id));
} else if ($operation == 'pay') {
    //支付方式
    include $this->template('credit/pay');
} else if ($operation == 'share_submit') {
    //分享提交
    $id = intval($_GPC['id']);
    if ($config['credit_wx_free'] && $id) {
        pdo_update('xuan_mixloan_credit_data', array('status'=>1, 'pay_type'=>2), array('id'=>$id));
        show_json(1);
    } else {
        show_json(-1, null, "分享失败");
    }
} else if ($operation == 'pay_submit') {
    //支付提交
    $id = intval($_GPC['id']);
    if (empty($id)) {
        message('id不存在', '', 'error');
    }
    $tid = "10003" . date('YmdHis', time());
    $_SESSION['credit_id'] = $id;
    $params = array(
        'tid' => $tid, 
        'ordersn' => $tid, 
        'title' => "信用查询付费", 
        'fee' => $config['credit_fee'], 
        'user' => $member['id'], 
    );
    //调用pay方法
    $this->pay($params);
    exit;
} else if ($operation == 'report_list') {
    //报告列表
    $list = pdo_fetchall("SELECT id,realname,createtime FROM ".tablename('xuan_mixloan_credit_data')." WHERE uid={$member['id']} ORDER BY id DESC");
    foreach ($list as &$row) {
        $row['tradeno'] = 'NY'.date('YmdHis', $row['createtime']);
    }
    unset($row);
    include $this->template('credit/report_list');
} else if ($operation == 'report_info') {
    //报告详情
    $id = intval($_GPC['id']);
    $report = pdo_fetch("SELECT * FROM ".tablename('xuan_mixloan_credit_data')." WHERE id={$id}");
    if ($report['status'] == 0) {
        $location = $this->createMobileUrl('credit', array('op'=>'pay', 'id'=>$id));
        header("location:{$location}");
        exit();
    }
    if (empty($report['ext_info']) && $report['status'] == 1) {
        $result = m('jdwx')->henypot4JD($config['jdwx_key'], $report['realname'], $report['certno'], $report['phone']);
        if ($result['code'] == 1) {
            // $ext_info['user_basic'] = $result['data']['user_basic'];
            // $ext_info['black_info_nums'] = count($result['data']['user_blacklist']['blacklist_category']);
            // $ext_info['bad_info_nums'] = count($result['data']['user_idcard_suspicion']['idcard_applied_in_orgs']);
            // $ext_info['search_info_nums'] = count($result['data']['user_searched_history_by_orgs']);
            // $ext_info['certno_suspect'] = $result['data']['user_idcard_suspicion']['idcard_applied_in_orgs'] ? true : false;
            // $ext_info['phone_suspect'] = $result['data']['user_phone_suspicion']['phone_applied_in_orgs'] ? true : false;
            // $ext_info['score'] = $result['data']['user_gray']['phone_gray_score'] ? : 99;
            // $ext_info['user_suspect'] = $ext_info['certno_suspect'] || $ext_info['phone_suspect'];
            // $ext_info['user_black'] = $ext_info['black_info_nums'] ? true : false;
            // $orgs = $result['data']['user_searched_history_by_orgs'];
            // $temp = [];
            // if (!empty($orgs)) {
            //     foreach ($orgs as $key => $value) {
            //         if (empty($temp[$value['searched_org']])) {
            //             $temp[$value['searched_org']] = 0;
            //         }
            //         $temp[$value['searched_org']] += 1;
            //     }  
            // }
            // $ext_info['orgs'] = $temp;
            // if ($ext_info['score'] > 50 && $ext_info['score'] < 80) {
            //     $ext_info['evaluate'] = '良';
            //     $ext_info['refuse_precent'] = '较低';
            // } else if ($ext_info['score'] >= 80) {
            //     $ext_info['evaluate'] = '优';
            //     $ext_info['refuse_precent'] = '低';
            // } else {
            //     $ext_info['evaluate'] = '低';
            //     $ext_info['refuse_precent'] = '较高';
            // }
            $list = $result;
            pdo_update('xuan_mixloan_credit_data', array('ext_info'=>json_encode($list)), array('id'=>$id));
        } else {
            message($result['msg'], '', 'error');
        }
    } else {
        $list = json_decode($report['ext_info'], 1);
    }
    include $this->template('credit/report_info');
} else if ($operation == 'example') {
    //样例
    include $this->template('credit/example');
}
?>