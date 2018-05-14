<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}
class Xuan_Mixloan_Jdwx
{  
	public function jd_credit_three($key,$realname,$phone,$certno){
		//手机号三要素
		$url = "https://way.jd.com/yingyan/telvertify?id={$certno}&name={$realname}&telnumber={$phone}&appkey={$key}";
		$resJson =file_get_contents($url);
		$res = json_decode($resJson,1);
		if ($res['code'] == "10000") {
			if ($res['result']['resp']['code'] == "0") {
				return ['code'=>1, 'data'=>$res['result']['data']];
			} else {
				return ['code'=>-1, 'msg'=>$res['result']['resp']['desc']];
			}
		} else {
			return ['code'=>-1, 'msg'=>$res['msg']];
		}
	}

	public function bankcardinfo($key, $bankno){
		//银行卡信息
		$url = "https://way.jd.com/yingyan/bankcardinfo?bankno={$bankno}&appkey={$key}";
		$resJson =file_get_contents($url);
		$res = json_decode($resJson,1);
		if ($res['code'] == "10000") {
			if ($res['result']['resp']['code'] == "0") {
				return ['code'=>1, 'data'=>$res['result']['data']];
			} else {
				return ['code'=>-1, 'msg'=>$res['result']['resp']['desc']];
			}
		} else {
			return ['code'=>-1, 'msg'=>$res['msg']];
		}
	}

	public function QryBankCardBy4Element($key, $bankno, $name, $idcard, $phone){
		//银行卡四元素
		$url = "https://way.jd.com/youhuoBeijing/QryBankCardBy4Element?accountNo={$bankno}&name={$name}&idCardCode={$idcard}&bankPreMobile={$phone}&appkey={$key}";
		$resJson =file_get_contents($url);
		$res = json_decode($resJson,1);
		if ($res['code'] == "10000") {
			if ($res['result']['result']['result'] != "F") {
				return ['code'=>1, 'data'=>$res['result']['data']];
			} else {
				return ['code'=>-1, 'msg'=>$res['result']['result']['message']];
			}
		} else {
			return ['code'=>-1, 'msg'=>$res['msg']];
		}
	}

    public function henypot4JD($key, $name, $idcard, $phone){
        //蜜罐数据
        $url = "https://way.jd.com/juxinli/henypot4JD?name={$name}&idCard={$idcard}&phone={$phone}&appkey={$key}";
        $resJson = file_get_contents($url);
        $res = json_decode($resJson,1);
        if ($res['code'] == "10000") {
            if ($res['result']['success'] == true) {
                return ['code'=>1, 'data'=>$res['result']['data']];
            } else {
                return ['code'=>-1, 'msg'=>$res['result']['message']];
            }
        } else {
            return ['code'=>-1, 'msg'=>$res['msg']];
        }
    }
}