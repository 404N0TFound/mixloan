<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_ApplyApi
{
    private $host = "https://api.51ley.com/apis/";
    private $station_id = "974483935490736129";
    private $user_id = "1003477013291335680";

    /*
     * 获取银行列表
     */
    public function bankList()
    {
        $url = $this->host . 'api/bank';
        $header[] = "stationid:" . $this->station_id;
        $header[] = "content-type: application/json";
        $json = $this->tocurl($url, array(), $header);
        $result = json_decode($json, true);
        if ($result['status'] == '200')
        {
            return $result['result'];
        }
        else
        {
            return false;
        }
    }
    /*
     * 获取银行卡通道列表
     * @param $ids
     */
    public function bankCards($ids)
    {
        if (!is_array($ids) || empty($ids))
        {
            return false;
        }
        $url = $this->host . 'api/bank/card';
        $params['bankIds'] = $ids;
        $header[] = "stationid: " . $this->station_id;
        $header[] = "content-type: application/json";
        $json = $this->tocurl($url, $params, $header);
        $result = json_decode($json, true);
        if ($result['status'] == '200')
        {
            return $result['result'];
        }
        else
        {
            return false;
        }
    }
    /*
     * 获取银行卡详情
     * @param $id
     */
    public function bankCard($id)
    {
        if (empty($id))
        {
            return false;
        }
        $url = $this->host . 'api/bank/card/';
        $url .= $id;
        $header[] = "stationid: " . $this->station_id;
        $json = $this->tocurl($url, array(), $header);
        $result = json_decode($json, true);
        if ($result['status'] == '200')
        {
            return $result['result'];
        }
        else
        {
            return false;
        }
    }
    /**
     * 数组转get方式
     * @param $params
     * @return string
     */
    function GetHttpQueryString($params)
    {
        if (!empty($params) && !is_array($params)) {
            return "";
        }
        ksort($params);
        $str = "";
        foreach ($params as $k => $v)
        {
            $str .= $k ."=". $v ."&";
        }
        $str = rtrim($str, "&");
        return $str;
    }

   /** 
     * 发送数据 
     * @param String $url     请求的地址 
     * @param Array  $header  自定义的header数据 
     * @param Array  $content POST的数据 
     * @return String 
     */  
    public  function tocurl($url, $params, $header)
    {  
        $content = json_encode($params);
        $ch = curl_init();  
        if(substr($url,0,5)=='https')
        {  
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查  
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在  
        }  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
        if ($params) 
        {
            curl_setopt($ch, CURLOPT_POST, true);  
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);   
        }
        $response = curl_exec($ch);  
        if($error=curl_error($ch)){  
            die($error);  
        }  
        curl_close($ch);  
        return $response;  
    }
}
