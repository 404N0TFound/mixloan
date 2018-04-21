<?php
defined('IN_IA') or exit('Access Denied');

class Xuan_mixloanModuleProcessor extends WeModuleProcessor {
    public function respond() {
        global $_W,$_GPC;
        $from = $this->message['from'];
        if($this->message['type'] == 'text') {
            $config = $this->module['config'];
            $content = $this->message['content'];
            if($content == "我的海报"){
                $member = pdo_fetch("SELECT id,nickname,openid,avatar FROM ".tablename('xuan_mixloan_member').' WHERE openid=:openid', array(':openid'=>$from));
                if (empty($member)) {
                	return false;
                }
                $check = pdo_fetchcolumn('SELECT COUNT(1) FROM '.tablename("xuan_mixloan_payment")." WHERE uid={$member['id']}");
                if ($check == false) {
                    $url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'buy'));
                	return $this->respText("您还不是代理哦，请先购买代理，成为代理后即可拥有专属海报\n<a href='{$url}'>点击购买</a>");
                }
                $poster = pdo_fetch('SELECT poster,media_id FROM '.tablename('xuan_mixloan_poster')." WHERE uid={$member['id']} AND type=3");
                if (empty($poster)){
                	require_once(IA_ROOT . '/addons/xuan_mixloan/inc/model/poster.php');
                	$wx = WeAccount::create();
                	$posterClass = new Xuan_mixloan_Poster();
				    $barcode = array(
				        'action_name'=>"QR_LIMIT_SCENE",
				        'action_info'=> array(
				            'scene' => array(
				                'scene_id'=>$member['id'],
				            )
				        )
				    );
				    $barCode = $wx->barCodeCreateDisposable($barcode);
					$cfg['poster_avatar'] = $config['invite_avatar'];
					$cfg['poster_image'] = $config['invite_image'];
					$cfg['poster_color'] = $config['invite_color'];
					$url = $barCode['url'];
					$out = IA_ROOT . "/addons/xuan_mixloan/data/poster/invite_{$member['id']}.png";
					$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    				$poster_path = $http_type .  $_SERVER['HTTP_HOST'];
					$poster_path .= "/addons/xuan_mixloan/data/poster/invite_{$member['id']}.png";
					$params = array(
						"url" => $url,
						"member" => $member,
						"type" => 3,
						"pid" => 0,
						"out" => $out,
						"poster_path" => $poster_path
					);
					$invite_res = $posterClass->createPoster($cfg, $params);
					if ($invite_res) {
                    	$result = $wx->uploadMedia($out);
            			pdo_update('xuan_mixloan_poster', array('media_id'=>$result['media_id']), array('type'=>3, 'uid'=>$member['id']));
                    	return $this->respImage($result['media_id']);
					} else {
						return false;
					}
                    // return $this->respText("小主稍等片刻，海报生成中，请20秒后重新获取");
                } else {
                    return $this->respImage($poster['media_id']);
                }
            }
        }
    }

}