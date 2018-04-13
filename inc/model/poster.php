<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_Poster
{
    public function getPosterData($get=[], $conditon=[]) {
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
        $list = pdo_fetchall($sql);
        $ret = [];
        foreach ($list as $value) {
            $ret[$value['id']] = $value;
        }
        return $ret;
    }
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
    public function createPoster($params) {
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
        $bgpath = IA_ROOT . '/attachment/' . $ext_info['back'];
        $bgpng = imagecreatefrompng($bgpath);
        $font = XUAN_MIXLOAN_PATH."data/fonts/msyh.ttf";
        if (!empty($ext_info['poster']['qr'])) {
            //二维码
            $tmplogo = XUAN_MIXLOAN_PATH."data/poster/base.jpg";
            require_once(IA_ROOT.'/framework/library/qrcode/phpqrcode.php');
            QRcode::png($params['url'],$tmplogo,'L',15,2);
            $QR = imagecreatefromstring(file_get_contents($tmplogo));
            $newQRcode = imagecreatetruecolor($ext_info['poster']['qr']['width'],$ext_info['poster']['qr']['height']);
            imagecopyresized($newQRcode,$QR,0,0,0,0,$ext_info['poster']['qr']['width'],$ext_info['poster']['qr']['height'],imagesx($QR),imagesy($QR));
            imagecopymerge($bgpng,$newQRcode,$ext_info['poster']['qr']['left'],$ext_info['poster']['qr']['top'],0,0,imagesx($newQRcode),imagesy($newQRcode),100);
        }
        if (!empty($ext_info['poster']['head'])) {
            //头像
            if (strstr($params['member']['avatar'], 'mix_loan')) {
                $avatar = imagecreatefromstring(file_get_contents($params['member']['avatar']));
            } else {
                $avatar = imagecreatefromstring(curl_file_get_contents($params['member']['avatar']));
            }
            $newAvatar = imagecreatetruecolor($ext_info['poster']['head']['width'],$ext_info['poster']['head']['height']);
            imagecopyresized($newAvatar,$avatar,0,0,0,0,$ext_info['poster']['head']['width'],$ext_info['poster']['head']['height'],imagesx($avatar),imagesy($avatar));
            imagecopymerge($bgpng,$newAvatar,$ext_info['poster']['head']['left'],$ext_info['poster']['head']['top'],0,0,$ext_info['poster']['head']['width'],$ext_info['poster']['head']['height'],100);
        }
        if (!empty($ext_info['poster']['nickname'])) {
            //昵称
            $poster_color = hex2rgb($ext_info['poster']['nickname']['color']);
            $color = imagecolorallocatealpha($bgpng,$poster_color['r'],$poster_color['g'],$poster_color['b'],0);
            imagettftext($bgpng,$ext_info['poster']['nickname']['size'],0,$ext_info['poster']['nickname']['left'],$ext_info['poster']['nickname']['top'],$color,$font,$params['member']['nickname']);
        }
        $res = imagepng($bgpng,$params['out']);
        @imagedestroy($QR);
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