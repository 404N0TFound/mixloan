<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloanModuleReceiver extends WeModuleReceiver {
    public function receive(){
        global $_W;
        $from = $this->message['from'];
        if($this->message['msgtype'] == 'event') {
            if ($this->message['event'] == 'subscribe') {
                load()->model('mc');
                $uid = mc_openid2uid($from);
                $fans = mc_fetch($uid,array('nickname'));
                $config = $this->module['config'];
                if($this->message['scene'] && !empty($fans)){
                    $scene = pdo_fetchcolumn("SELECT qrcid FROM ".tablename("qrcode_stat")." WHERE openid=:openid AND type=1 ORDER BY id ASC",array(":openid"=>$from));
                    if ($scene) {
                        pdo_run("UPDATE ".tablename("qrcode_stat")." SET type=2 WHERE openid='{$from}' AND qrcid<>{$scene}");
                    } else {
                        $scene = $this->message['scene'];
                    }
                }
            }
        }
    }
}