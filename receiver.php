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
                    $my_id = pdo_fetchcolumn("SELECT id FROM ".tablename("xuan_mixloan_member")." WHERE openid=:openid",array(":openid"=>$from));
                    if ($my_id != $this->message['scene']) {
                        //锁粉
                        if ($qrcid) {
                            pdo_run("UPDATE ".tablename("qrcode_stat")." SET type=2 WHERE openid='{$from}' AND qrcid<>{$qrcid}");
                        } else {
                            $qrcid = $this->message['scene'];
                        }
                        $man_one = pdo_fetch("SELECT nickname,openid,phone FROM ".tablename("xuan_mixloan_member")." WHERE id=:id", array(':id'=>$qrcid));
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
                        $wx->sendTplNotice($man_one['openid'],$templateId,$msg);
                        //二级通知
                        if ($man_one['phone']) {
                            $inviter_two = pdo_fetchcolumn("SELECT uid FROM ".tablename("xuan_mixloan_inviter"). " WHERE phone=:phone", array(":phone"=>$man_one['phone']));
                            if (!$inviter_two && $man_one['openid']) {
                                $inviter_two = pdo_fetchcolumn("SELECT `qrcid` FROM ".tablename("qrcode_stat")." WHERE openid=:openid AND type=1 ORDER BY id DESC",array(":openid"=>$man_one['openid']));
                            }
                        }
                        if ($inviter_two) {
                            $man_two = pdo_fetch("SELECT nickname,openid,phone FROM ".tablename("xuan_mixloan_member")." WHERE id=:id", array(':id'=>$inviter_two));
                            $wx = WeAccount::create();
                            $msg = array(
                                'first' => array(
                                    'value' => "您好，您的好友已通过您的朋友{$man_one['nickname']}的推广二维码关注{$config['title']}",
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
                                    'value' => "好友尚未购买代理，莫着急！继续推荐代理，好友购买成功，即可获得{$config['inviter_fee_two']}元奖励",
                                    "color" => "#A4D3EE"
                                ),
                            );
                            $templateId=$config['tpl_notice4'];
                            $wx->sendTplNotice($man_two['openid'],$templateId,$msg);
                        }
                        //三级通知
                        if ($man_two['phone']) {
                            $inviter_thr = pdo_fetchcolumn("SELECT uid FROM ".tablename("xuan_mixloan_inviter"). " WHERE phone=:phone", array(":phone"=>$man_two['phone']));
                            if (!$inviter_thr && $man_two['openid']) {
                                $inviter_thr = pdo_fetchcolumn("SELECT `qrcid` FROM ".tablename("qrcode_stat")." WHERE openid=:openid AND type=1 ORDER BY id DESC",array(":openid"=>$man_two['openid']));
                            }
                        }
                        if ($inviter_thr) {
                            $man_thr = pdo_fetch("SELECT nickname,openid,phone FROM ".tablename("xuan_mixloan_member")." WHERE id=:id", array(':id'=>$inviter_thr));
                            $wx = WeAccount::create();
                            $msg = array(
                                'first' => array(
                                    'value' => "您好，您的好友已通过您的朋友{$man_two['nickname']}的朋友{$man_one['nickname']}的推广二维码关注{$config['title']}",
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
                                    'value' => "好友尚未购买代理，莫着急！继续推荐代理，好友购买成功，即可获得{$config['inviter_fee_thr']}元奖励",
                                    "color" => "#A4D3EE"
                                ),
                            );
                            $templateId=$config['tpl_notice4'];
                            $wx->sendTplNotice($man_thr['openid'],$templateId,$msg);
                        }
                    }
                }
            }
        }
    }
}