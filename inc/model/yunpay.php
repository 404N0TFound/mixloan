<?php
require_once dirname(dirname(dirname(__FILE__))).'/lib/shanpayfunction.php';
if (!defined('IN_IA')) {
    exit('Access Denied');
}
class Xuan_Mixloan_Yunpay
{  
	//合作身份者PID，签约账号，由16位纯数字组成的字符串
	private $partner;
	//商户号（8位数字）
	private $user_seller;
	// MD5密钥，安全检验码，由数字和字母组成的32位字符串
	private $md5key;
	//服务器异步通知页面路径
	private $notify_url;
	//页面跳转同步通知页面路径
	private $return_url;

	/**
	*	初始化
	**/
	function __construct($params)
	{
		$this->partner = $params['partner'];
		$this->user_seller = $params['user_seller'];
		$this->md5key = $params['md5key'];
		$this->notify_url = $params['notify_url'];
		$this->return_url = $params['return_url'];
	}

	/**
	*	支付
	**/
	function pay($params)
	{
		if (empty($this->partner)) {
			return ['code'=>-1, 'msg'=>'缺少签约账号'];
		}
		if (empty($this->user_seller)) {
			return ['code'=>-1, 'msg'=>'缺少商户号'];
		}
		if (empty($this->md5key)) {
			return ['code'=>-1, 'msg'=>'缺少MD5密钥'];
		}
		if (empty($this->notify_url)) {
			return ['code'=>-1, 'msg'=>'缺少异步通知页面'];
		}
		if (empty($this->return_url)) {
			return ['code'=>-1, 'msg'=>'缺少同步通知页面'];
		}
		if (empty($params['subject'])) {
			return ['code'=>-1, 'msg'=>'缺少订单名称'];
		}
		if (empty($params['total_fee'])) {
			return ['code'=>-1, 'msg'=>'缺少付款金额'];
		}
		if (empty($params['body'])) {
			return ['code'=>-1, 'msg'=>'缺少订单描述'];
		}
		$parameter = array(
			"partner" => $this->partner,
		    "user_seller" => $this->user_seller,
			"out_order_no" => $params['out_order_no'],
			"subject" => $params['subject'],
			"total_fee"	=> $params['total_fee'],
			"body" => $params['body'],
			"notify_url" => $this->notify_url,
			"return_url" => $this->return_url
		);
		//建立请求
		$html_text = buildRequestFormShan($parameter, $this->md5key);
		var_dump($html_text);die;
	}
}