<?php
defined('IN_IA') or exit('Access Denied');
function m($name = '')
{
    static $_modules = array();
    if (isset($_modules[$name])) {
        return $_modules[$name];
    }
    $model = XUAN_MIXLOAN_INC."model/" . strtolower($name) . '.php';
    if (!is_file($model)) {
        die(' Model ' . $name . ' Not Found!');
    }
    require_once($model);
    $class_name      = 'Xuan_mixloan_' . ucfirst($name);
    $_modules[$name] = new $class_name();
    return $_modules[$name];
}

function is_array2($array)
{
    if (is_array($array)) {
        foreach ($array as $k => $v) {
            return is_array($v);
        }
        return false;
    }
    return false;
}
function get_last_day($year, $month)
{
    return date('t', strtotime("{$year}-{$month} -1"));
}
function show_json($code = 0, $data = null, $msg = null, $jsonp = null)
{
    $ret = array(
        'code' => $code
    );
    if ($data) {
        $ret['data'] = $data;
    } else {
        $ret['data'] = [];
    }
    if ($msg) {
        $ret['msg'] = $msg;
    }
    if ($jsonp) {
        $ret['jsonp'] = $jsonp;
    }
    die(json_encode($ret));
}
function is_weixin()
{
    if (empty($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false) {
        return false;
    }
    return true;
}


function baoSendSMS($mobile,$content,$config) {
    load()->func('communication');
    $user = $config['smsuser'];
    $pass = md5($config['smspass']);
    $result = file_get_contents("http://api.smsbao.com/sms?u={$user}&p={$pass}&m=".$mobile."&c=".urlencode($content));
    return $content;
}

/**
 * 获取今天
 */
function getTime(){
    $cTime = date('Y-m-d', time());
    $arr = explode('-', $cTime);
    return $arr;
}



function getServerIp(){
    if(!empty($_SERVER["HTTP_CLIENT_IP"])){
      $cip = $_SERVER["HTTP_CLIENT_IP"];
    }
    elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
      $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    elseif(!empty($_SERVER["REMOTE_ADDR"])){
      $cip = $_SERVER["REMOTE_ADDR"];
    }
    else{
      $cip = 0;
    }
    return $cip;
}

/**
* 获取当前根域名
**/
function getNowHostUrl() {
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    return $http_type .  $_SERVER['HTTP_HOST'];
}

/**
* get
**/
function curl_file_get_contents($durl){
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $durl);
   curl_setopt($ch, CURLOPT_TIMEOUT, 2);
   curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);
   curl_setopt($ch, CURLOPT_REFERER,_REFERER_);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $r = curl_exec($ch);
   curl_close($ch);
   return $r;
 }

 /**
 * 十六进制 转 RGB
 */
function hex2rgb($hexColor) {
    $color = str_replace('#', '', $hexColor);
    if (strlen($color) > 3) {
        $rgb = array(
            'r' => hexdec(substr($color, 0, 2)),
            'g' => hexdec(substr($color, 2, 2)),
            'b' => hexdec(substr($color, 4, 2))
        );
    } else {
        $color = $hexColor;
        $r = substr($color, 0, 1) . substr($color, 0, 1);
        $g = substr($color, 1, 1) . substr($color, 1, 1);
        $b = substr($color, 2, 1) . substr($color, 2, 1);
        $rgb = array(
            'r' => hexdec($r),
            'g' => hexdec($g),
            'b' => hexdec($b)
        );
    }
    return $rgb;
}
/**
 * RGB转 十六进制
 * @param $rgb RGB颜色的字符串 如：rgb(255,255,255);
 * @return string 十六进制颜色值 如：#FFFFFF
 */
function RGBToHex($rgb){
    $regexp = "/^rgb\(([0-9]{0,3})\,\s*([0-9]{0,3})\,\s*([0-9]{0,3})\)/";
    $re = preg_match($regexp, $rgb, $match);
    $re = array_shift($match);
    $hexColor = "#";
    $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
    for ($i = 0; $i < 3; $i++) {
        $r = null;
        $c = $match[$i];
        $hexAr = array();
        while ($c > 16) {
            $r = $c % 16;
            $c = ($c / 16) >> 0;
            array_push($hexAr, $hex[$r]);
        }
        array_push($hexAr, $hex[$c]);
        $ret = array_reverse($hexAr);
        $item = implode('', $ret);
        $item = str_pad($item, 2, '0', STR_PAD_LEFT);
        $hexColor .= $item;
    }
    return $hexColor;
}

/**
 *   缩短地址
 **/
function shortUrl($target) {
    $target = str_replace('wx.twoplus.top', 'wx.hanjun.site', $target);
    $target_url = urlencode($target);
    $short = pdo_fetch("SELECT short_url,createtime FROM ".tablename("xuan_mixloan_shorturl")." WHERE target_url=:target_url ORDER BY id DESC", array(':target_url'=>$target));
    if (!$short || $short['createtime'] < time()-86400) {
        $long_url = urlencode($target);
        $url      = "http://suo.im/api.php?format=json&url=".$long_url;
        $json     = file_get_contents( $url );
        $arr      = json_decode($json, true);
        if ($arr['err'] == 0) {
            pdo_insert('xuan_mixloan_shorturl', ['target_url'=>$target, 'short_url'=>$arr['url'], 'createtime'=>time()]);
            return $arr['url'];
        } else {
            return false;
        }
    } else {
        return $short['short_url'];
    }
}
/**
*   获取格式化金额
**/
function formatMoney($number) {
    $integer = floor($number);
    $decimal = $number - $integer;
    if ($decimal != 0) {
        $decimal *= 100;
        $decimal = ceil($decimal);
    } else {
        $decimal = "00";
    }
    return ['int'=>$integer, 'dec'=>$decimal];
}
/*
*   发送信息
*/
function sendCustomNotice($openid, $msg, $url = '', $account = null)
{
    $content = "";
    if (is_array($msg)) {
        foreach ($msg as $key => $value) {
            if (!empty($value['title'])) {
                $content .= $value['title'] . ":" . $value['value'] . "\n";
            } else {
                $content .= $value['value'] . "\n";
                if ($key == 0) {
                    $content .= "\n";
                }
            }
        }
    } else {
        $content = $msg;
    }
    if (!empty($url)) {
        $content .= "\n".'<a href="'.$url.'">点击查看</a>';
    }
    m('wechat')->sendtxtmsg($openid,$content);
}

/**
 * [将Base64图片转换为本地图片并保存]
 * @param  [Base64] $base64_image_content [要保存的Base64]
 * @param  [目录] $path [要保存的路径]
 */
function base64_image_content($base64_image_content,$path){
    //匹配出图片的格式
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
        $type = $result[2];
        if ($type != 'jpeg' && $type != 'jeg' && $type != 'png') {
            return false;
        }
        $new_file = $path."/";
        $file_name = time() . ".{$type}";
        $new_file = $new_file . $file_name;
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
            return $file_name;
        }else{
            return false;
        }
    }else{
        return false;
    }
}
// 隐藏部分字符串
function func_substr_replace($str, $replacement = '*', $start = 4, $length = 3)
{
    $len = mb_strlen($str,'utf-8');
    if ($len > intval($start+$length)) {
        $str1 = mb_substr($str,0,$start,'utf-8');
        $str2 = mb_substr($str,intval($start+$length),NULL,'utf-8');
    } else {
        $str1 = mb_substr($str,0,1,'utf-8');
        $str2 = mb_substr($str,$len-1,1,'utf-8');
        $length = $len - 2;
    }
    $new_str = $str1;
    for ($i = 0; $i < $length; $i++) {
        $new_str .= $replacement;
    }
    $new_str .= $str2;
    return $new_str;
}
