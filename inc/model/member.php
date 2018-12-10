<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_Member
{
    public function getInfo($openid = '')
    {
        global $_W;
        $uid = intval($openid);
        if ($uid == 0) {
            $info = pdo_fetch('select * from ' . tablename('xuan_mixloan_member') . ' where openid=:openid and uniacid=:uniacid limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':openid' => $openid
            ));
        } else {
            $info = pdo_fetch('select * from ' . tablename('xuan_mixloan_member') . ' where id=:id  and uniacid=:uniacid limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $uid
            ));
        }
        if (!empty($info['uid'])) {
            load()->model('mc');
            $uid                = mc_openid2uid($info['openid']);
            $fans               = mc_fetch($uid, array(
                'credit1',
                'credit2',
                'birthyear',
                'birthmonth',
                'birthday',
                'gender',
                'avatar',
                'resideprovince',
                'residecity',
                'nickname'
            ));
            $info['credit1']    = $fans['credit1'];
            $info['credit2']    = $fans['credit2'];
            $info['birthyear']  = empty($info['birthyear']) ? $fans['birthyear'] : $info['birthyear'];
            $info['birthmonth'] = empty($info['birthmonth']) ? $fans['birthmonth'] : $info['birthmonth'];
            $info['birthday']   = empty($info['birthday']) ? $fans['birthday'] : $info['birthday'];
            $info['nickname']   = empty($info['nickname']) ? $fans['nickname'] : $info['nickname'];
            $info['gender']     = empty($info['gender']) ? $fans['gender'] : $info['gender'];
            $info['sex']        = $info['gender'];
            $info['avatar']     = empty($info['avatar']) ? $fans['avatar'] : $info['avatar'];
            $info['headimgurl'] = $info['avatar'];
            $info['province']   = empty($info['province']) ? $fans['resideprovince'] : $info['province'];
            $info['city']       = empty($info['city']) ? $fans['residecity'] : $info['city'];
        }
        if (!empty($info['birthyear']) && !empty($info['birthmonth']) && !empty($info['birthday'])) {
            $info['birthday'] = $info['birthyear'] . '-' . (strlen($info['birthmonth']) <= 1 ? '0' . $info['birthmonth'] : $info['birthmonth']) . '-' . (strlen($info['birthday']) <= 1 ? '0' . $info['birthday'] : $info['birthday']);
        }
        if (empty($info['birthday'])) {
            $info['birthday'] = '';
        }
        return $info;
    }
    public function getMember($openid = '')
    {
        global $_W;
        $uid = intval($openid);
        if (empty($uid) || strlen($openid) == 32) {
            $info = pdo_fetch('select * from ' . tablename('xuan_mixloan_member') . ' where  openid=:openid and uniacid=:uniacid limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':openid' => $openid
            ));
        } else {
            $info = pdo_fetch('select * from ' . tablename('xuan_mixloan_member') . ' where id=:id and uniacid=:uniacid limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $uid
            ));
        }
        return $info;
    }
    public function getMid()
    {
        global $_W;
        $openid = m('user')->getOpenid();
        $member = $this->getMember($openid);
        return $member['id'];
    }
    public function getMobile()
    {
        global $_W;
        $openid = m('user')->getOpenid();
        $member = $this->getMember($openid);
        return $member['mobile'];
    }
    public function setCredit($openid = '', $credittype = 'credit1', $credits = 0, $log = array())
    {
        global $_W;
        load()->model('mc');
        $uid = mc_openid2uid($openid);
        if (!empty($uid)) {
            $value     = pdo_fetchcolumn("SELECT {$credittype} FROM " . tablename('mc_members') . " WHERE `uid` = :uid", array(
                ':uid' => $uid
            ));
            $newcredit = $credits + $value;
            if ($newcredit <= 0) {
                $newcredit = 0;
            }
            pdo_update('mc_members', array(
                $credittype => $newcredit
            ), array(
                'uid' => $uid
            ));
            if (empty($log) || !is_array($log)) {
                $log = array(
                    $uid,
                    '未记录'
                );
            }
            $data = array(
                'uid' => $uid,
                'credittype' => $credittype,
                'uniacid' => $_W['uniacid'],
                'num' => $credits,
                'createtime' => TIMESTAMP,
                'operator' => intval($log[0]),
                'remark' => $log[1]
            );
            pdo_insert('mc_credits_record', $data);
        } else {
            $value     = pdo_fetchcolumn("SELECT {$credittype} FROM " . tablename('xuan_mixloan_member') . " WHERE  uniacid=:uniacid and openid=:openid limit 1", array(
                ':uniacid' => $_W['uniacid'],
                ':openid' => $openid
            ));
            $newcredit = $credits + $value;
            if ($newcredit <= 0) {
                $newcredit = 0;
            }
            pdo_update('xuan_mixloan_member', array(
                $credittype => $newcredit
            ), array(
                'uniacid' => $_W['uniacid'],
                'openid' => $openid
            ));
        }
    }
    public function getCredit($openid = '', $credittype = 'credit1')
    {
        global $_W;
        load()->model('mc');
        $uid = mc_openid2uid($openid);
        if (!empty($uid)) {
            return pdo_fetchcolumn("SELECT {$credittype} FROM " . tablename('mc_members') . " WHERE `uid` = :uid", array(
                ':uid' => $uid
            ));
        } else {
            return pdo_fetchcolumn("SELECT {$credittype} FROM " . tablename('xuan_mixloan_member') . " WHERE  openid=:openid and uniacid=:uniacid limit 1", array(
                ':uniacid' => $_W['uniacid'],
                ':openid' => $openid
            ));
        }
    }
    public function checkMember($openid = '')
    {
        global $_W, $_GPC;
        if (strexists($_SERVER['REQUEST_URI'], '/web/')) {
            return;
        }
        if (empty($openid)) {
            $openid = m('user')->getOpenid();
        }
        if (empty($openid)) {
            die("<!DOCTYPE html>
            <html>
                <head>
                    <meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'>
                    <title>抱歉，出错了</title><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'><link rel='stylesheet' type='text/css' href='https://res.wx.qq.com/connect/zh_CN/htmledition/style/wap_err1a9853.css'>
                </head>
                <body>
                <div class='page_msg'><div class='inner'><span class='msg_icon_wrp'><i class='icon80_smile'></i></span><div class='msg_content'><h4>请在微信客户端打开链接</h4></div></div></div>
                </body>
            </html>");
            return;
        }
        $member   = m('member')->getMember($openid);
        $userinfo = m('user')->getInfo();
        $followed = m('user')->followed($openid);
        $uid      = 0;
        $mc       = array();
        if (empty($member)) {
            load()->model('mc');
            if ($followed) {
                $uid = mc_openid2uid($openid);
                $mc  = mc_fetch($uid, array(
                    'realname',
                    'mobile',
                    'avatar',
                    'resideprovince',
                    'residecity',
                    'residedist'
                ));
            }
            $member = array(
                'uniacid' => $_W['uniacid'],
                'uid' => $uid,
                'openid' => $openid,
                'nickname' => !empty($mc['nickname']) ? $mc['nickname'] : $userinfo['nickname'],
                'avatar' => !empty($mc['avatar']) ? $mc['avatar'] : $userinfo['avatar'],
                'province' => !empty($mc['residecity']) ? $mc['resideprovince'] : $userinfo['province'],
                'city' => !empty($mc['residecity']) ? $mc['residecity'] : $userinfo['city'],
                'country' => !empty($mc['country']) ? $mc['country'] : $userinfo['country'],
                'sex'=> !empty($mc['gender']) ? $mc['gender'] : $userinfo['sex'],
                'createtime' => time(),
                'status' => -2
            );
            pdo_insert('xuan_mixloan_member', $member);
        } else {
            $upgrade = array();
            if ($followed) {
                $uid = mc_openid2uid($openid);
            }
            // if ($userinfo['nickname'] != $member['nickname']) {
            //     $upgrade['nickname'] = $userinfo['nickname'];
            // }
            // if ($userinfo['avatar'] != $member['avatar']) {
            //     $upgrade['avatar'] = $userinfo['avatar'];
            // }
            if (!empty($uid)) {
                if (empty($member['uid'])) {
                    $upgrade['uid'] = $uid;
                }
                if ($member['credit1'] > 0) {
                    mc_credit_update($uid, 'credit1', $member['credit1']);
                    $upgrade['credit1'] = 0;
                }
                if ($member['credit2'] > 0) {
                    mc_credit_update($uid, 'credit2', $member['credit2']);
                    $upgrade['credit2'] = 0;
                }
            }
            if (!empty($upgrade)) {
                pdo_update('xuan_mixloan_member', $upgrade, array(
                    'id' => $member['id']
                ));
            }
        }
    }
    /*
    *   查看是否加入过代理
    */
    function checkAgent($uid) {
        $check = pdo_fetch('SELECT id,msg FROM '.tablename("xuan_mixloan_payment")."
                    WHERE uid=:uid ORDER BY id DESC", array(':uid'=>$uid));
        if ($check) {
            if ($check['msg'] == 1) {
                $name = '城市代理';
            } else if ($check['msg'] == 2) {
                $name = '大区总代';
            }  else if ($check['msg'] == 3) {
                $name = '永久合伙人';
            } 
            return ['code'=>'1','name'=>$name, 'msg'=>$check['msg'], 'id'=>$check['id']];
        } else {
            return ['code'=>'0','name'=>'用户'];
        }
    }

    /*
    *   获取总提现的钱
    */
    public function sumWithdraw($uid){
        $bonus = pdo_fetchcolumn('SELECT SUM(bonus) FROM '.tablename('xuan_mixloan_withdraw').' where uid=:uid', array(':uid'=>$uid));
        return $bonus ? : 0;
    }

    /**
     *   获取邀请
     **/
    public function getInviter($phone, $openid="") {
        global $_W;
        $res = false;
        if (!$res && $openid) {
            $res = pdo_fetchcolumn("SELECT `qrcid` FROM ".tablename("qrcode_stat")." WHERE openid=:openid AND uniacid=:uniacid AND type=1 ORDER BY id ASC",array(":openid"=>$openid,":uniacid"=>$_W["uniacid"]));
        }
        if (!$res && $phone) {
            $res = pdo_fetchcolumn("SELECT uid FROM ".tablename("xuan_mixloan_inviter"). " WHERE phone=:phone", array(":phone"=>$phone));
        }
        return $res;
    }

    /**
    *   获取用户手机号
    **/
    public function getInviterPhone($uid) {
        if (!$uid) {
            return false;
        }
        $res = pdo_fetchcolumn("SELECT phone FROM ".tablename("xuan_mixloan_member"). " WHERE id={$uid}");
        return $res;
    }
    /**
     *   口子进来的锁定上级
     *
     */
    public function checkFirstInviter($openid, $inviter, $config) {
        global $_W;
        $openid = trim($openid);
        $inviter = intval($inviter);
        if (empty($openid) || empty($inviter)) {
            return false;
        }
        $id = pdo_fetchcolumn('select id from ' . tablename('xuan_mixloan_member') . '
            where openid=:openid', array(
            ':openid' => $openid
        ));
        if ($id == $inviter) {
            return false;
        }
        $res = pdo_fetchcolumn("SELECT count(*) FROM " . tablename("qrcode_stat") . "
            WHERE openid=:openid AND uniacid=:uniacid AND type=1", array(
            ":openid" => $openid,
            ":uniacid" => $_W["uniacid"]
        ));
        if (!$res) {
            $insert = array(
                'uniacid' => $_W['uniacid'],
                'acid' => 0,
                'qid' => 0,
                'openid' => $openid,
                'type' => 1,
                'qrcid' => $inviter,
                'scene_str' => $inviter,
                'createtime' => time() ,
            );
            pdo_insert('qrcode_stat', $insert);
            if (!empty($config['inviter_bonus_one'])) {
                $insert = array();
                $insert['uid'] = $id;
                $insert['inviter'] = $inviter;
                $insert['re_bonus'] = $config['inviter_bonus_one'];
                $insert['type']   = 7;
                $insert['status'] = 2;
                $insert['degree'] = 1;
                $insert['createtime'] = time();
                $insert['uniacid'] = $_W['uniacid'];
                pdo_insert('xuan_mixloan_product_apply', $insert);
            }
            $one_openid = pdo_fetchcolumn('select openid from ' . tablename('xuan_mixloan_member') . '
                        where id=:id', array(':id' => $inviter));
            $two_inviter = pdo_fetchcolumn('select qrcid from ' . tablename('qrcode_stat') . '
                        where openid=:openid and type=1', array(':openid' => $one_openid));
            if ($two_inviter && !empty($config['inviter_bonus_two'])) {
                $insert = array();
                $insert['uid'] = $id;
                $insert['inviter'] = $two_inviter;
                $insert['re_bonus'] = $config['inviter_bonus_two'];
                $insert['type']   = 7;
                $insert['status'] = 2;
                $insert['degree'] = 2;
                $insert['createtime'] = time();
                $insert['uniacid'] = $_W['uniacid'];
                pdo_insert('xuan_mixloan_product_apply', $insert);
            }
            $two_openid = pdo_fetchcolumn('select openid from ' . tablename('xuan_mixloan_member') . '
                        where id=:id', array(':id' => $two_inviter));
            $thr_inviter = pdo_fetchcolumn('select qrcid from ' . tablename('qrcode_stat') . '
                        where openid=:openid and type=1', array(':openid' => $two_openid));
            if ($thr_inviter && !empty($config['inviter_bonus_thr'])) {
                $insert = array();
                $insert['uid'] = $id;
                $insert['inviter'] = $thr_inviter;
                $insert['re_bonus'] = $config['inviter_bonus_thr'];
                $insert['type']   = 7;
                $insert['status'] = 2;
                $insert['degree'] = 3;
                $insert['createtime'] = time();
                $insert['uniacid'] = $_W['uniacid'];
                pdo_insert('xuan_mixloan_product_apply', $insert);
            }
            $thr_openid = pdo_fetchcolumn('select openid from ' . tablename('xuan_mixloan_member') . '
                        where id=:id', array(':id' => $thr_inviter));
            $four_inviter = pdo_fetchcolumn('select qrcid from ' . tablename('qrcode_stat') . '
                        where openid=:openid and type=1', array(':openid' => $thr_openid));
            if ($four_inviter && !empty($config['inviter_bonus_four'])) {
                $insert = array();
                $insert['uid'] = $id;
                $insert['inviter'] = $four_inviter;
                $insert['re_bonus'] = $config['inviter_bonus_four'];
                $insert['type']   = 7;
                $insert['status'] = 2;
                $insert['degree'] = 4;
                $insert['createtime'] = time();
                $insert['uniacid'] = $_W['uniacid'];
                pdo_insert('xuan_mixloan_product_apply', $insert);
            }
            $four_openid = pdo_fetchcolumn('select openid from ' . tablename('xuan_mixloan_member') . '
                        where id=:id', array(':id' => $four_inviter));
            $five_inviter = pdo_fetchcolumn('select qrcid from ' . tablename('qrcode_stat') . '
                        where openid=:openid and type=1', array(':openid' => $four_openid));
            if ($five_inviter && !empty($config['inviter_bonus_five'])) {
                $insert = array();
                $insert['uid'] = $id;
                $insert['inviter'] = $five_inviter;
                $insert['re_bonus'] = $config['inviter_bonus_five'];
                $insert['type']   = 7;
                $insert['status'] = 2;
                $insert['degree'] = 5;
                $insert['createtime'] = time();
                $insert['uniacid'] = $_W['uniacid'];
                pdo_insert('xuan_mixloan_product_apply', $insert);
            }
            $five_openid = pdo_fetchcolumn('select openid from ' . tablename('xuan_mixloan_member') . '
                        where id=:id', array(':id' => $five_inviter));
            $six_inviter = pdo_fetchcolumn('select qrcid from ' . tablename('qrcode_stat') . '
                        where openid=:openid and type=1', array(':openid' => $five_openid));
            if ($six_inviter && !empty($config['inviter_bonus_six'])) {
                $msg = pdo_fetchcolumn('select msg from ' . tablename('xuan_mixloan_payment') . '
                        where uid=:uid', array(':uid' => $six_inviter));
                if ($msg >= 2) {
                    $insert = array();
                    $insert['uid'] = $id;
                    $insert['inviter'] = $six_inviter;
                    $insert['re_bonus'] = $config['inviter_bonus_six'];
                    $insert['type']   = 7;
                    $insert['status'] = 2;
                    $insert['degree'] = 6;
                    $insert['createtime'] = time();
                    $insert['uniacid'] = $_W['uniacid'];
                    pdo_insert('xuan_mixloan_product_apply', $insert);
                }
            }
            $six_openid = pdo_fetchcolumn('select openid from ' . tablename('xuan_mixloan_member') . '
                        where id=:id', array(':id' => $six_inviter));
            $sev_inviter = pdo_fetchcolumn('select qrcid from ' . tablename('qrcode_stat') . '
                        where openid=:openid and type=1', array(':openid' => $six_openid));
            if ($sev_inviter && !empty($config['inviter_bonus_sev'])) {
                $msg = pdo_fetchcolumn('select msg from ' . tablename('xuan_mixloan_payment') . '
                        where uid=:uid', array(':uid' => $sev_inviter));
                if ($msg >= 2) {
                    $insert = array();
                    $insert['uid'] = $id;
                    $insert['inviter'] = $sev_inviter;
                    $insert['re_bonus'] = $config['inviter_bonus_sev'];
                    $insert['type']   = 7;
                    $insert['status'] = 2;
                    $insert['degree'] = 7;
                    $insert['createtime'] = time();
                    $insert['uniacid'] = $_W['uniacid'];
                    pdo_insert('xuan_mixloan_product_apply', $insert);
                }
            }
            return true;
        } else {
            return false;
        }
    }
     /**
    *   获取用户手机号和openid
    **/
    public function getInviterInfo($uid) {
        if (!$uid) {
            return false;
        }
        $res = pdo_fetch("SELECT phone,openid,nickname FROM ".tablename("xuan_mixloan_member"). " WHERE id={$uid}");
        return $res;
    }
    /**
     *   检查uid的上下三级是否和inviter存在关系
     **/
    public function checkIfRelation($inviter, $uid) {
        if (empty($inviter) || empty($uid)) {
            return flase;
        }
        $low_man = $this->getInviterInfo($uid);
        $inviter_man = $this->getInviterInfo($inviter);
        //检查inviter的上三级
        $temp_id = $this->getInviter($inviter_man['phone'], $inviter_man['openid']);
        if (!empty($temp_id)) {
            if ($temp_id == $uid) {
                //一级
                return 'below_one';
            }
            $temp_man = $this->getInviterInfo($temp_id);
            $temp_id = $this->getInviter($temp_man['phone'], $temp_man['openid']);
            if (!empty($temp_id)) {
                if ($temp_id == $uid) {
                    //二级
                    return 'below_two';
                }
                $temp_man = $this->getInviterInfo($temp_id);
                $temp_id = $this->getInviter($temp_man['phone'], $temp_man['openid']);
                if (!empty($temp_id)) {
                    if ($temp_id == $uid) {
                        //三级
                        return 'below_thr';
                    }
                }
            }
        }
        //检查uid的上三级
        $temp_id = $this->getInviter($low_man['phone'], $low_man['openid']);
        if (!empty($temp_id)) {
            if ($temp_id == $inviter) {
                //一级
                return 'up_one';
            }
            $temp_man = $this->getInviterInfo($temp_id);
            $temp_id = $this->getInviter($temp_man['phone'], $temp_man['openid']);
            if (!empty($temp_id)) {
                if ($temp_id == $inviter) {
                    //二级
                    return 'up_two';
                }
                $temp_man = $this->getInviterInfo($temp_id);
                $temp_id = $this->getInviter($temp_man['phone'], $temp_man['openid']);
                if (!empty($temp_id)) {
                    if ($temp_id == $inviter) {
                        //三级
                        return 'up_thr';
                    }
                }
            }
        }
        return false;
    }
    /*
     *   查看是否加入过合伙人
    */
    function checkPartner($uid) {
        if (empty($uid)) {
            return ['code' => '0', 'name' => '用户'];
        }
        $check = pdo_fetch('SELECT id FROM ' . tablename("xuan_mixloan_partner") . " WHERE uid=:uid ORDER BY id DESC", array(
            ':uid' => $uid
        ));
        if ($check) {
            return ['code' => '1', 'name' => '合伙人', 'id' => $check['id']];
        } else {
            return ['code' => '0', 'name' => '用户'];
        }
    }
}