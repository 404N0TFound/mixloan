<?php

// 根据密钥对数据签名生成sign
function sign($data, $mch_key) {
	$str = '';
	ksort($data, SORT_STRING);
	foreach($data as $k => $v) {
		if ( $v === '' || $v === null || $k === 'sign' ) {
			continue;
		}
		$str .= $k . '=' . $v . '&';
	}

	$str .= 'key=' . $mch_key;
	return md5($str);
}

// 提交post请求
function post_json($url, $post) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	$s = curl_exec($ch);
	curl_close($ch);
	return $s;
}


// 记录日志
function mylog($str) {
	if ( is_array($str) || is_object($str) ) {
		$str = var_export($str, true);
	}

	$str = date('Y-m-d H:i:s') . "\t" . $str . "\r\n";

	$r = @file_put_contents('log.txt', $str, FILE_APPEND);
	if ( !$r ) {
		@unlink('log.txt');
		@file_put_contents('log.txt', $str);
	}
}


// 私钥计算sign
function rsa_private_sign($originalData, $key) {
	$encryptData = null;
	$pi_key = openssl_pkey_get_private($key);
	openssl_sign($originalData, $encryptData, $pi_key, OPENSSL_ALGO_MD5);
	openssl_free_key($pi_key);
	$encryptData = base64_encode($encryptData);
	return $encryptData;
}

// 私钥加密
function rsa_public_encrypt($str, $key) {
	$pi_key = openssl_pkey_get_public($key);
	$lists = str_split($str, 117);
	foreach($lists as &$v) {
		openssl_public_encrypt($v, $v, $pi_key);
	}
	openssl_free_key($pi_key);
	$lists = implode('', $lists);
	$lists = base64_encode($lists);
	return $lists;
}

// 私钥解密
function rsa_private_decrypt($str, $key) {
	$str = base64_decode($str);
	$pi_key = openssl_pkey_get_private($key);
	$lists = str_split($str, 128);
	$results = [];
	foreach($lists as $v) {
		$r = null;
		openssl_private_decrypt($v, $r, $pi_key);
		$results[] = $r;
	}
	$results = implode('', $results);
	return $results;
}