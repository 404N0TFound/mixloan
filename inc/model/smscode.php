<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_Smscode
{
    private $host       = 'http://api.1cloudsp.com/api/v2/single_send';
    private $accesskey  = 'lhnaE3bMnoY4jlpn';
    private $secret     = '6YGyZSQVdvnMDeTtjdYAmm2pPZ1Xp2cq';
    private $templateId = '8296';
    private $sign       = '6452';

    public function sendSms($mobile, $code)
    {
        if (empty($mobile))
        {
            return ['code' => -1, 'msg' => '手机不能为空']; 
        }
        if (empty($code))
        {
            return ['code' => -1, 'msg' => '验证码不能为空'];         
        }
        $params['accesskey']  = $this->accesskey;
        $params['secret']     = $this->secret;
        $params['templateId'] = $this->templateId;
        $params['sign']       = $this->sign;
        $params['content']    = $code;
        $params['mobile']     = $mobile;
        $result = $this->curl($this->host, $params);
        var_dump($result);
    }
    /**
     * post请求
     * @param $url
     * @param string $param
     * @return array
     */
    function curl($url, $param=array())
    {
        $postUrl = $url;
        $ch = curl_init();                                     
        curl_setopt($ch, CURLOPT_URL,$postUrl);                 
        curl_setopt($ch, CURLOPT_HEADER, 0);                    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);           
        curl_setopt($ch, CURLOPT_POST, 1);                      
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);                                
        curl_close($ch);
        return $data;
    }
}
