<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_Poster
{
    public function getPoster($get=[], $conditon=[]) {
        global $_W;
        $wheres = $fields = "";
        if (!empty($get)) {
            $fields = implode(',', $get);
        } else {
            $fields = '*';
        }
        if (!empty($conditon)) {
            foreach ($conditon as $k => $v) {
                $wheres .= " AND `{$k}` = '{$v}'";
            }
        }
        $sql = "SELECT {$fields} FROM ".tablename('xuan_mixloan_poster')." WHERE uniacid={$_W['uniacid']} {$wheres} ";
        $item = pdo_fetch($sql);
        return $item;
    }
    /**
    *   生成海报
    **/
    public function createPoster($config, $params) {
        global $_W;
        $tmplogo = XUAN_MIXLOAN_PATH."data/poster/base.jpg";
        require_once(IA_ROOT.'/framework/library/qrcode/phpqrcode.php');
        QRcode::png($params['url'],$tmplogo,'L',15,2);
        $QR = imagecreatefromstring(file_get_contents($tmplogo));
        $bgpath = IA_ROOT . '/attachment/' . $config['poster_image'];
        $font = XUAN_MIXLOAN_PATH."data/fonts/msyh.ttf";
        $bgpng = imagecreatefrompng($bgpath);
        if ($config['poster_avatar']) {
            //头像
            if (strstr($params['member']['avatar'], 'mix_loan')) {
                $avatar = imagecreatefromstring(file_get_contents($params['member']['avatar']));
            } else {
                $avatar = imagecreatefromstring(curl_file_get_contents($params['member']['avatar']));
            }
            $newa = imagecreatetruecolor(imagesx($bgpng)*0.2,imagesx($bgpng)*0.2);
            imagecopyresized($newa,$avatar,0,0,0,0,imagesx($bgpng)*0.2,imagesx($bgpng)*0.2,imagesx($avatar),imagesy($avatar));
            imagecopymerge($bgpng,$newa,imagesx($bgpng)*0.4,imagesy($bgpng)*0.7,0,0,imagesx($bgpng)*0.2,imagesx($bgpng)*0.2,100);
        }
        $newl = imagecreatetruecolor(imagesx($bgpng)*0.35,imagesx($bgpng)*0.35);
        imagecopyresized($newl,$QR,0,0,0,0,imagesx($bgpng)*0.35,imagesx($bgpng)*0.35,imagesx($QR),imagesy($QR));
        if ($config['poster_avatar']) {
            //字体
            $poster_color = hex2rgb($config['poster_color']);
            $color = imagecolorallocatealpha($bgpng,$poster_color['r'],$poster_color['g'],$poster_color['b'],0);
            imagettftext($bgpng,imagesx($bgpng)*0.03,0,imagesx($bgpng)*0.4,imagesy($bgpng)*0.9,$color,$font,$params['member']['nickname']);
        }
        if (!$config['poster_avatar']) {
            $height = 0.55;
        } else {
            $height = 0.4;
        }
        imagecopymerge($bgpng,$newl,imagesx($bgpng)*0.33,imagesy($bgpng)*$height,0,0,imagesx($newl),imagesy($newl),100);
        $res = imagepng($bgpng,$params['out']);
        imagedestroy($QR);
        imagedestroy($bgpng);
        if ($res) {
            $insert = array(
                'uniacid'=>$_W['uniacid'],
                'uid'=>$params['member']['id'],
                'pid'=>$params['pid'],
                'type'=>$params['type'],
                'poster'=>$params['poster_path'],
                'createtime'=>time(),
            );
            pdo_insert('xuan_mixloan_poster',$insert);
            return true;
        } else {
            return false;
        }
    }
}