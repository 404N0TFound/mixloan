<?php
/**
 * 模块定义
 *
 */
defined('IN_IA') or exit('Access Denied');

class Xuan_mixloanModule extends WeModule {

	public function settingsDisplay($setting) {
		global $_W, $_GPC;
        load()->func('tpl');
		if(checksubmit()) {
            $cfg = array(
                    'title'=>$_GPC['title'],
                    'wx_name'=>$_GPC['wx_name'],
                    'logo'=>$_GPC['logo'],
                    'vip_comm'=>$_GPC['vip_comm'],
                    'poster_avatar'=>$_GPC['poster_avatar'],
                    'poster_image'=>$_GPC['poster_image'],
                    'poster_color'=>$_GPC['poster_color'],
                    'invite_avatar'=>$_GPC['invite_avatar'],
                    'invite_image'=>$_GPC['invite_image'],
                    'invite_color'=>$_GPC['invite_color'],
                    'qqnum'=>$_GPC['qqnum'],
                    'share_title'=>$_GPC['share_title'],
                    'share_image'=>$_GPC['share_image'],
                    'share_desc'=>$_GPC['share_desc'],
                    'smsuser' => $_GPC['smsuser'],
                    'smspass' => $_GPC['smspass'],
                    'jdwx_open' => $_GPC['jdwx_open'],
                    'jdwx_key' => $_GPC['jdwx_key'],
                    'tpl_notice1'=>$_GPC['tpl_notice1'],
                    'tpl_notice2'=>$_GPC['tpl_notice2'],
                    'tpl_notice3'=>$_GPC['tpl_notice3'],
                    'tpl_notice4'=>$_GPC['tpl_notice4'],
                    'tpl_notice5'=>$_GPC['tpl_notice5'],
                    'register_contract'=> htmlspecialchars_decode($_GPC['register_contract']),
                    'buy_vip_price' =>$_GPC['buy_vip_price'],
                    'inviter_fee_one'=>$_GPC['inviter_fee_one'],
                    'inviter_fee_two'=>$_GPC['inviter_fee_two'],
                    'inviter_fee_thr'=>$_GPC['inviter_fee_thr'],
                    'vip_friend'=>$_GPC['vip_friend'],
                    'vip_channel'=>$_GPC['vip_channel'],
                    'buy_adv_pics'=>$_GPC['buy_adv_pics'],
                    'buy_intro_pic' => $_GPC['buy_intro_pic'],
                    'product_logo'=>$_GPC['product_logo'],
                    'service_pic' => $_GPC['service_pic'],
                    'tutorials_pic'=>$_GPC['tutorials_pic'],
                    'buy_content'=>htmlspecialchars_decode($_GPC['buy_content']),
                    'buy_question' =>htmlspecialchars_decode($_GPC['buy_question']),
                    'buy_contract'=>htmlspecialchars_decode($_GPC['buy_contract']),
                'wx_qrcode'=>$_GPC['wx_qrcode']
            	);

            if ($this->saveSettings($cfg)) {
                pdo_delete("xuan_mixloan_poster", array("pid"=>0));
                message('保存成功', 'refresh');
            }
		}
		$setting = $this->module['config'];
        
        $queue_url = $_W['siteroot'] . 'app/' .$this->createMobileUrl('ajax', array('op'=>'queue'));
        $vip_buy = $this->shortUrl($_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'buy')));
        $mix_tutorials = $this->shortUrl($_W['siteroot'] . 'app/' .$this->createMobileUrl('mix', array('op'=>'tutorials')));
        $mix_service = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('mix', array('op'=>'service')) );
        $loan = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'')) );
        $want_subscribe = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('bank', array('op'=>'want_subscribe')) );
        $extend_query = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('bank', array('op'=>'extend_query')) );
        $extend_tips = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('bank', array('op'=>'extend_tips')) );
        $user = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('user', array('op'=>'')) );
        $channel = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('channel', array('op'=>'')) );
        $product = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'')) );
        $createPostAllProduct = $this->shortUrl(  $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'createPostAllProduct')) );
        $friend = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('friend', array('op'=>'')) );
		include $this->template('setting');
	}
    public function shortUrl($target) {
        return $target;
        $target_url = urlencode($target);
        $short = pdo_fetch("SELECT short_url,createtime FROM ".tablename("xuan_mixloan_shorturl")." WHERE target_url=:target_url ORDER BY id DESC", array(':target_url'=>$target));
        if (!$short || $short['createtime'] < time()-86400) {
            $url = "http://goo.gd/action/json.php?source=1681459862&url_long={$target_url}";
            $json = file_get_contents($url);
            $arr = json_decode($json, true);
            if ($arr['urls'][0]['result'] == true) {
                pdo_insert('xuan_mixloan_shorturl', ['target_url'=>$target, 'short_url'=>$arr['urls'][0]['url_short'], 'createtime'=>time()]);
                return $arr['urls'][0]['url_short'];
            } else {
                return false;
            }
        } else {
            return $short['short_url'];
        }
    }

}