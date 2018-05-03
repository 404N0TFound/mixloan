<?php
class demo{
    $pub_key = ""; //��Կ �Է�
    private function signANDencrypt($req_bean) {
		Import("libs.XmlTool", ADDON_PATH);
		
		$this->pub_key = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCqWSfUW3fSyoOYzOG8joy3xldpBanLVg8gEDcvm9KxVjqvA/qJI7y0Rmkc1I7l9vAfWtNzphMC+wlulpaAsa/4PbfVj+WhoNQyhG+m4sP27BA8xuevNT9/W7/2ZVk4324NSowwWkaqo1yuZe1wQMcVhROz2h+g7j/uZD0fiCokWwIDAQAB';
		
		
		$xmlStr = XmlTool::createXml("MSGBEAN", $req_bean, true);
		
		
		//�̻�ǩ��
		$certs = array();
		$priKey = file_get_contents(ADDON_PATH.'/libs/pfx/'.$this->config['mer_pfx_key']);
		openssl_pkcs12_read($priKey, $certs, $this->config['mer_pfx_pass']);
		$dna_pri_key = $certs['pkey'];
		
		Import("libs.Crypt.Md5RSA", ADDON_PATH);
		$md5rsa = new Md5RSA();	
		if($req_bean['VERSION']) 
			$sign = $md5rsa->sign($this->getSignStr($req_bean), $dna_pri_key);
		else 
			$sign = $md5rsa->sign($xmlStr, $dna_pri_key);
		$the_sign = base64_encode($sign);
// 		echo $xmlStr."<hr>";
// 		echo $dna_pri_key."<hr>";
// 		echo $this->getSignStr($req_bean)."<hr>";
// 		echo $the_sign."<hr>";
// 		exit;
		$req_bean['MSG_SIGN'] = $the_sign;
	
		//���ܱ���
		$xmlStr = XmlTool::createXml("MSGBEAN", $req_bean, true);
		Import("libs.Crypt.TripleDES", ADDON_PATH);
		$key = $this->generateKey(9999,24);
		$des = new TripleDES($key);
		$req_body_enc = $des->encrypt($xmlStr);
		
		//������Կ
		$encrypted = '';
		 //��װ��Կ��
        $pem = chunk_split($this->pub_key, 64, "\n"); //ת��Ϊpem��ʽ�Ĺ�Կ
        $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
        $pub_key_pem = openssl_get_publickey($pem); //��ȡ��Կ����
		$req_key_enc = $this->rsa->encrypt($this->des->key, $this->pub_key_pem); //��Կ rsa  ����
		
		return $req_body_enc ."|". $req_key_enc;
	

	}
	
	public function generateKey($round, $length) {
		$key = "";
		for($i = 0;$i < $length;$i++) {
			$random = rand(0,$round) % 16;
			switch($random) {
				case  0: $key .= "0";break;
				case  1: $key .= "1";break;
				case  2: $key .= "2";break;
				case  3: $key .= "3";break;
				case  4: $key .= "4";break;
				case  5: $key .= "5";break;
				case  6: $key .= "6";break;
				case  7: $key .= "7";break;
				case  8: $key .= "8";break;
				case  9: $key .= "9";break;
				case  10: $key .= "A";break;
				case  11: $key .= "B";break;
				case  12: $key .= "C";break;
				case  13: $key .= "D";break;
				case  14: $key .= "E";break;
				case  15: $key .= "F";break;
				default: $i--;
			}	
		}
	
		return base64_encode($key);
	}
	
	private function sendAndRead($req) {
		Import("libs.Http", ADDON_PATH);
		$resXml = Http::postXmlUrl($this->config['getway'], $req, true, '');
		return $resXml;
	}
	
	private function decryptANDverify($res) {
		Import("libs.XmlTool", ADDON_PATH);
		$params = explode("|", $res);
		$msg_sign_enc = $params[0];
		$key_3des_enc = $params[1];
	
		//������Կ
		$priKey = file_get_contents(ADDON_PATH.'/libs/pfx/'.$this->config['mer_pfx_key']);
		$certs = array();
		openssl_pkcs12_read($priKey, $certs, $this->config['mer_pfx_pass']);
		$dna_pri_key = $certs['pkey'];
		
		$encrypted = '';
		$priKey = openssl_get_privatekey($dna_pri_key);//��ȡ��Կ����
		openssl_private_decrypt(base64_decode($key_3des_enc), $decrypted, $priKey);
		$key_3des = $decrypted;
	
		//���ܱ���
		Import("libs.Crypt.TripleDES", ADDON_PATH);
		$des = new TripleDES($key_3des);
		$msg_xml = $des->decrypt($msg_sign_enc);
		Import("libs.XmlTool", ADDON_PATH);
		$msg_sign = XmlTool::xml2array($msg_xml);
		//��ǩ
		$dna_sign_msg = $msg_sign['MSG_SIGN'];
		$msg_sign['MSG_SIGN'] = '';
		Import("libs.Crypt.Md5RSA", ADDON_PATH);
		$md5rsa = new Md5RSA();
		
		if($msg_sign['VERSION']) 
			$xmlSignStr = $this->getSignStr($msg_sign);
		else 
			$xmlSignStr = XmlTool::createXml("MSGBEAN", $msg_sign, true);
		
		$c = $md5rsa->isValid($xmlSignStr, base64_decode($dna_sign_msg), $this->config['dna_pub_key']);
		if(!$c) {
// 			echo $xmlSignStr."<hr>";
// 			echo $dna_sign_msg."<hr>";
			$msg_sign["TRANS_STATE"] = "00A0";
		}
		return $msg_sign;
	}
}