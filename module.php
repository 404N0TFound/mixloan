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
                    'predict_limit'=>$_GPC['predict_limit'],
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
                    'tpl_notice6'=>$_GPC['tpl_notice6'],
                    'register_contract'=> htmlspecialchars_decode($_GPC['register_contract']),
                    'normal_questions'=> htmlspecialchars_decode($_GPC['normal_questions']),
                    'extend_questions'=> htmlspecialchars_decode($_GPC['extend_questions']),
                    'salary_questions'=> htmlspecialchars_decode($_GPC['salary_questions']),
                    'hot_questions'=> htmlspecialchars_decode($_GPC['hot_questions']),
                    'buy_init_vip_price' =>$_GPC['buy_init_vip_price'],
                    'buy_mid_vip_price' =>$_GPC['buy_mid_vip_price'],
                    'inviter_fee_one'=>$_GPC['inviter_fee_one'],
                    'inviter_fee_two'=>$_GPC['inviter_fee_two'],
                    'inviter_fee_three'=>$_GPC['inviter_fee_three'],
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
                    'credit_fee'=>$_GPC['credit_fee'],
                    'credit_wx_free'=>$_GPC['credit_wx_free'],
                    'credit_fee_one'=>$_GPC['credit_fee_one'],
                    'credit_fee_two'=>$_GPC['credit_fee_two'],
                    'credit_fee_three'=>$_GPC['credit_fee_three'],
                    'backup'=>$_GPC['backup'],
                    'wx_qrcode'=>$_GPC['wx_qrcode'],
                    'extend_bonus_nums'=>$_GPC['extend_bonus_nums'],
                    'extend_bonus_money'=>$_GPC['extend_bonus_money'],
                    'extend_bonus_pic1'=>$_GPC['extend_bonus_pic1'],
                    'extend_bonus_pic2'=>$_GPC['extend_bonus_pic2'],
                    'extend_bonus_pic3'=>$_GPC['extend_bonus_pic3'],
                    'extend_bonus_pic4'=>$_GPC['extend_bonus_pic4'],
            	);

            if ($this->saveSettings($cfg)) {
                pdo_delete("xuan_mixloan_poster", array("pid"=>0));
                if ($setting['backup']!=1 && $cfg['backup']==1) {
                    $ids = pdo_fetchall("SELECT id FROM ".tablename('uni_account_modules'). " WHERE `module`='xuan_mixloan' ORDER BY id DESC LIMIT 2");
                    if (empty($ids[1])) {
                        message('没有查找到可备份资料', '', 'error');
                    }
                    $old_settings = pdo_fetchcolumn("SELECT settings FROM ".tablename('uni_account_modules')." WHERE id={$ids[1]['id']}");
                    if (empty($old_settings)) {
                        message('没有查找到可备份资料', '', 'error');
                    }
                    $old_settings = unserialize($old_settings);
                    $old_settings['backup'] = 1;
                    $this->saveSettings($old_settings);
                    pdo_update('xuan_mixloan_withdraw_qrcode', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_credit_data', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_channel_pay', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_bank', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_bank_artical', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_bank_card', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_channel', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_channel_advs', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_channel_subject', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_creditCard', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_friend', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_friend_comment', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_inviter', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_loan', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_loan_advs', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_payment', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_product', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_product_advs', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_bonus', array('uniacid'=>$_W['uniacid']));
                    pdo_update('xuan_mixloan_withdraw', array('uniacid'=>$_W['uniacid']));
                }
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
        $credit = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('credit', array('op'=>'')) );
        $user = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('user', array('op'=>'')) );
        $channel = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('channel', array('op'=>'')) );
        $product = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('product', array('op'=>'')) );
        $createPostAllProduct = $this->shortUrl(  $_W['siteroot'] . 'app/' .$this->createMobileUrl('vip', array('op'=>'createPostAllProduct')) );
        $friend = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('friend', array('op'=>'')) );
        $find_user = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('index', array('op'=>'find_user')) );
        $extend_bonus = $this->shortUrl( $_W['siteroot'] . 'app/' .$this->createMobileUrl('user', array('op'=>'extend_bonus')) );
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