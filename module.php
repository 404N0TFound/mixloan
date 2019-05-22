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
        $setting = $this->module['config'];
        if(checksubmit()) {
            $cfg = array(
                    'title'=>$_GPC['title'],
                    'wx_name'=>$_GPC['wx_name'],
                    'logo'=>$_GPC['logo'],
                    'qqnum'=>$_GPC['qqnum'],
                    'share_title'=>$_GPC['share_title'],
                    'share_image'=>$_GPC['share_image'],
                    'share_desc'=>$_GPC['share_desc'],
                    'wx_qrcode'=>$_GPC['wx_qrcode'],
                    'tpl_notice3'=>$_GPC['tpl_notice3'],
                    'tpl_notice6'=>$_GPC['tpl_notice6'],
                    'tpl_notice7'=>$_GPC['tpl_notice7'],
                    'smsuser'=>$_GPC['smsuser'],
                    'smspass'=>$_GPC['smspass'],
                    'register_back'=>$_GPC['register_back']
                );
            if ($this->saveSettings($cfg)) {
                message('保存成功', 'refresh');
            }
        }
        $loan = $_W['siteroot'] . 'app/' .$this->createMobileUrl('loan', array('op'=>'')) ;
        include $this->template('setting');
    }
}