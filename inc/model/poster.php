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
        if (strstr(tomedia($config['poster_image']), 'jiemeiimages')) {
            $tomedia_img = trim(tomedia($config['poster_image']));
            $bgpng = imagecreatefromstring(file_get_contents($tomedia_img));
        } else {
            $bgpath = IA_ROOT . '/attachment/' . $config['poster_image'];
            $bgpng = imagecreatefrompng($bgpath);
        }
        $font = XUAN_MIXLOAN_PATH."data/fonts/msyh.ttf";
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
            //字体
            $poster_color = hex2rgb($config['poster_color']);
            $color = imagecolorallocatealpha($bgpng,$poster_color['r'],$poster_color['g'],$poster_color['b'],0);
            imagettftext($bgpng,imagesx($bgpng)*0.03,0,imagesx($bgpng)*0.4,imagesy($bgpng)*0.9,$color,$font,func_substr_replace($params['member']['nickname']));
        }
        $newl = imagecreatetruecolor(imagesx($bgpng)*0.35,imagesx($bgpng)*0.35);
        imagecopyresized($newl,$QR,0,0,0,0,imagesx($bgpng)*0.35,imagesx($bgpng)*0.35,imagesx($QR),imagesy($QR));
        if (!$config['poster_avatar']) {
            $height = 0.4;
        } else {
            $height = 0.4;
        }
        imagecopymerge($bgpng,$newl,imagesx($bgpng)*0.33,imagesy($bgpng)*$height,0,0,imagesx($newl),imagesy($newl),100);
        $res = imagepng($bgpng,$params['out']);
        imagedestroy($QR);
        imagedestroy($bgpng);
        if ($res) {
            if (strstr(tomedia($config['poster_image']), 'jiemeiimages')) { 
                $url = 'http://cheexuan.com/app/index.php?i=3&c=entry&op=upload_file&do=ajax&m=xuan_mixloan';
                $res = file_get_contents($url . '&fileroot=' . $params['out']);
                $poster = $res;
                unlink($params['out']);
            } else {
                $poster = $params['poster_path'];
            }
            $insert = array(
                'uniacid'=>$_W['uniacid'],
                'uid'=>$params['member']['id'],
                'pid'=>$params['pid'],
                'type'=>$params['type'],
                'poster'=>$poster,
                'createtime'=>time(),
            );
            pdo_insert('xuan_mixloan_poster',$insert);
            return $poster;
        } else {
            return false;
        }
    }
    /**
     *   新的生成海报方式
     **/
    public function createNewPoster($params) {
        global $_W;
        $poster_id = intval($params['poster_id']);
        if (empty($poster_id)) {
            return false;
        }
        $ext_info = pdo_fetchcolumn("SELECT ext_info FROM ".tablename('xuan_mixloan_poster_data').' WHERE id=:id', array(':id'=>$poster_id));
        $ext_info = json_decode($ext_info, 1);
        if (empty($ext_info) || empty($ext_info['back'])) {
            return false;
        }
        if (strstr(tomedia($ext_info['back']), 'jiemeiimages')) {
            $tomedia_img = trim(tomedia($ext_info['back']));
            $bgpng = imagecreatefromstring(file_get_contents($tomedia_img));
        } else {
            $bgpath = IA_ROOT . '/attachment/' . $ext_info['back'];
            $bgpng = imagecreatefrompng($bgpath);
        }
        $font = XUAN_MIXLOAN_PATH."data/fonts/msyh.ttf";
        $width_proportion = imagesx($bgpng) / 320;
        if (!empty($ext_info['poster']['qr'])) {
            //二维码
            $tmplogo = XUAN_MIXLOAN_PATH."data/poster/base.jpg";
            require_once(IA_ROOT.'/framework/library/qrcode/phpqrcode.php');
            QRcode::png($params['url'],$tmplogo,'L',15,2);
            $QR = imagecreatefromstring(file_get_contents($tmplogo));
            $width = bcmul(intval($ext_info['poster']['qr']['width']), $width_proportion);
            $height = intval($ext_info['poster']['qr']['height'])* $width_proportion;
            $left = intval($ext_info['poster']['qr']['left'])* $width_proportion;
            $top = intval($ext_info['poster']['qr']['top'])* $width_proportion;
            $newQRcode = imagecreatetruecolor($width, $height);
            imagecopyresized($newQRcode,$QR,0,0,0,0,$width, $height,imagesx($QR),imagesy($QR));
            imagecopymerge($bgpng,$newQRcode,$left,$top,0,0,imagesx($newQRcode),imagesy($newQRcode),100);
        }
        if (!empty($ext_info['poster']['head'])) {
            //头像
            if (strstr($params['member']['avatar'], 'mix_loan')) {
                $avatar = imagecreatefromstring(file_get_contents($params['member']['avatar']));
            } else {
                $avatar = imagecreatefromstring(curl_file_get_contents($params['member']['avatar']));
            }
            $width = bcmul(intval($ext_info['poster']['head']['width']), $width_proportion);
            $height = intval($ext_info['poster']['head']['height'])* $width_proportion;
            $left = intval($ext_info['poster']['head']['left'])* $width_proportion;
            $top = intval($ext_info['poster']['head']['top'])* $width_proportion;
            $newAvatar = imagecreatetruecolor($width, $height);
            imagecopyresized($newAvatar,$avatar,0,0,0,0,$width,$height,imagesx($avatar),imagesy($avatar));
            imagecopymerge($bgpng,$newAvatar,$left,$top,0,0,$width,$height,100);
        }
        if (!empty($ext_info['poster']['nickname'])) {
            //昵称
            $poster_color = hex2rgb($ext_info['poster']['nickname']['color']);
            $left = intval($ext_info['poster']['head']['left'])* $width_proportion;
            $top = intval($ext_info['poster']['head']['top'])* ($width_proportion+0.2);
            $color = imagecolorallocatealpha($bgpng,$poster_color['r'],$poster_color['g'],$poster_color['b'],0);
            imagettftext($bgpng,$ext_info['poster']['nickname']['size']*$width_proportion,0,$left,$top,$color,$font,$params['member']['nickname']);
        }
        $res = imagepng($bgpng,$params['out']);
        @imagedestroy($QR);
        imagedestroy($bgpng);
        if ($res) {
            if (strstr(tomedia($ext_info['back']), 'jiemeiimages')) { 
                $url = 'http://cheexuan.com/app/index.php?i=3&c=entry&op=upload_file&do=ajax&m=xuan_mixloan';
                $res = file_get_contents($url . '&fileroot=' . $params['out']);
                $poster = $res;
                unlink($params['out']);
            } else {
                $poster = $params['poster_path'];
            }
            $insert = array(
                'uniacid'=>$_W['uniacid'],
                'uid'=>$params['member']['id'],
                'pid'=>$params['pid'],
                'type'=>$params['type'],
                'poster'=>$poster,
                'createtime'=>time(),
            );
            pdo_insert('xuan_mixloan_poster',$insert);
            return $poster;
        } else {
            return false;
        }
    }
}