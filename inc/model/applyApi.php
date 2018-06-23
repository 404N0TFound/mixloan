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
        $params['stationid'] = $this->station_id;
        $query_url = $url . '?' . $this->GetHttpQueryString($params);
        $json = file_get_contents($query_url);
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
     */
    public function bankCard($ids)
    {
        if (!is_array($ids) || empty($ids))
        {
            return false;
        }
        $url = $this->host . 'api/bank/card';
        $params['bankIds'] = json_encode($ids);
        var_dump($ids);
        $params['stationid'] = $this->station_id;
        $query_url = $url . '?' . $this->GetHttpQueryString($params);
        $json = file_get_contents($query_url);
        $result = json_decode($json, true);
        var_dump($query_url);die;
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
}
