<?php  
defined('IN_IA') or exit('Access Denied');
global $_GPC,$_W;
$config = $this->module['config'];
(!empty($_GPC['op']))?$operation=$_GPC['op']:$operation='service';
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
if($operation=='service'){
	//客服服务
	include $this->template('mix/service');
} else if ($operation == 'tutorials') {
	//新手指南
	include $this->template('mix/tutorials');
} else if ($operation == 'area_customer') {
	//地区贷款客户
	include $this->template('mix/area_customer');
} else if ($operation == 'getService') {
	//根据地区id获取客服
	$areaId = intval($_GPC['areaId']);
	$keyword = trim($_GPC['keyword']);
	$con = array(':uniacid' => $_W['uniacid'], ':area_city' => $areaId);
	if (!empty($keyword)) {
		$where .= " and name like '%{$keyword}%'";
	}
	$list = pdo_fetchall('select * from ' . tablename('xuan_mixloan_service') . '
		where uniacid=:uniacid and area_city=:area_city' . $where, $con);
	if (!empty($list)) {
		foreach ($list as &$row) {
			$row['ext_info'] = json_decode($row['ext_info'], true);
			$row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
		} 
		unset($row);
		show_json(1, ['list' => $list]);
	} else if ($keyword) {
		show_json(-1, [], '没有找到相关数据');
	} else {
		show_json(-1, [], '正在对接中...');
	}
} else if ($operation == 'serviceDetail') {
	//客服详情
	$id = intval($_GPC['id']);
	$con = array(':uniacid' => $_W['uniacid'], ':id' => $id);
	$where = '';
	$item = pdo_fetch('select * from ' . tablename('xuan_mixloan_service') . '
		where uniacid=:uniacid and id=:id', $con);
	$item['ext_info'] = json_decode($item['ext_info'], true);
	if ($item['rate_type'] == 1) {
		$item['rate_type'] = '日';
	} else {
		$item['rate_type'] = '月';
	}
	$service = m('member')->checkService($member['id']);
	if ($service['code'] == 1) {
		$verify = 1;
	} else {
		$verify = 0;
	}
	include $this->template('mix/serviceDetail');
} else if ($operation == 'buyService') {
	//购买资格
	$fee = $config['buy_service_fee'];
	if ($member['id'] == 1) {
		$fee = '0.01';
	}
	if (!is_weixin()) {
		$notify_url = 'http://wx.wyhrkj.com/addons/xuan_mixloan/lib/wechat/payResult.php';
        $record = pdo_fetch('select * from ' .tablename('xuan_mixloan_paylog'). '
		    where type=3 and is_pay=0 and uid=:uid order by id desc', array(':uid'=>$member['id']));
        if (empty($record)) {
            $tid = "10003" . date('YmdHis', time());
            $trade_no = "ZML".date("YmdHis");
            $insert = array(
                'notify_id'=>$trade_no,
                'tid'=>$tid,
                'createtime'=>time(),
                'uid'=>$member['id'],
                'uniacid'=>$_W['uniacid'],
                'fee'=>$fee,
                'is_pay'=>0,
                'type'=>3,
            );
            pdo_insert('xuan_mixloan_paylog', $insert);
        } else {
            if ($record['createtime']+60 < time())
            {
                //超过1分钟重新发起订单
                $tid = "10003" . date('YmdHis', time());
                $trade_no = "ZML".date("YmdHis");
                $insert = array(
                    'notify_id'=>$trade_no,
                    'tid'=>$tid,
                    'createtime'=>time(),
                    'uid'=>$member['id'],
                    'uniacid'=>$_W['uniacid'],
                    'fee'=>$fee,
                    'is_pay'=>0,
                    'type'=>3,
                );
                pdo_insert('xuan_mixloan_paylog', $insert);
            }
            else
            {
                $trade_no = $record['notify_id'];
            }
        }
        $result = m('pay')->H5pay($trade_no, $fee, $notify_url);
        if ($result['code'] == 1) {
            $redirect_url = urlencode($_W['siteroot'] . 'app/' .
                $this->createMobileUrl('vip', array('op'=>'checkPay')));
            $url = "{$result['data']['url']}&redirect_url={$redirect_url}";
        } else {
            message('请稍后再试', $this->createMobileUrl('user'), 'error');
        }
        include $this->template('vip/openHref');
	} else {
		$tid = "10003" . date('YmdHis', time());
		$title = "购买{$config['title']}代理会员";
		$fee = $config['buy_service_fee'];
		$params = array(
		    'tid' => $tid, 
		    'ordersn' => $tid, 
		    'title' => $title, 
		    'fee' => $fee, 
		    'user' => $member['id'], 
		);
		//调用pay方法
		$this->pay($params);
	}
} else if ($operation == 'apply_cache') {
    require_once('../addons/xuan_mixloan/inc/model/cache.php');
    $cache = new Xuan_mixloan_Cache();
    $cache_img = $cache->doimg();
    if (!$cache_img['result']) {
        show_json(-1,[],'生成验证码失败');
    }
    $code = $cache->getCode();
    setcookie('authcode', sha1(md5($code)), time()+300);
    show_json(1, ['img' => $cache_img['file']]);
}