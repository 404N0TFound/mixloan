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
                    //进行粉丝增加通知
                    $qrcid = pdo_fetchcolumn("SELECT qrcid FROM ".tablename("qrcode_stat")." WHERE openid=:openid AND type=1 ORDER BY id ASC",array(":openid"=>$from));
                    //锁粉
                    if ($qrcid) {
                        pdo_run("UPDATE ".tablename("qrcode_stat")." SET type=2 WHERE openid='{$from}' AND qrcid<>{$qrcid}");
                    } else {
                        $qrcid = $this->message['scene'];
                    }
                    $openid = pdo_fetchcolumn("SELECT openid FROM ".tablename("xuan_mixloan_member")." WHERE id=:id", array(':id'=>$qrcid));
                    $wx = WeAccount::create();
                    $msg = array(
                        'first' => array(
                            'value' => "您好，您的好友已通过您的推广二维码关注{$config['title']}",
                            "color" => "#4a5077"
                        ),
                        'keyword1' => array(
                            'value' => $fans['nickname'],
                            "color" => "#4a5077"
                        ),
                        'keyword2' => array(
                            'value' => date("Y-m-d H:i:s",time()),
                            "color" => "#4a5077"
                        ),
                        'remark' => array(
                            'value' => "好友尚未购买代理，莫着急！继续推荐代理，好友购买成功，即可获得{$config['inviter_fee_one']}元奖励",
                            "color" => "#A4D3EE"
                        ),
                    );
                    $templateId=$config['tpl_notice4'];
                    $wx->sendTplNotice($openid,$templateId,$msg);
                    
                }
            }
        }
    }
}