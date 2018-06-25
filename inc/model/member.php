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
    public function getMember($openid = '', $unionid = '')
    {
        global $_W;
        $uid = intval($openid);
        if (empty($uid)) {
            $info = pdo_fetch('select * from ' . tablename('xuan_mixloan_member') . ' where openid=:openid and uniacid=:uniacid limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':openid' => $openid,
            )); 
            if (empty($info) && $unionid) {
                $info = pdo_fetch('select * from ' . tablename('xuan_mixloan_member') . ' where unionid=:unionid and uniacid=:uniacid limit 1', array(
                    ':uniacid' => $_W['uniacid'],
                    ':unionid' => $unionid,
                )); 
            }
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
    public function checkMember($openid = '') {
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
                 <div class='page_msg'><div class='inner'><span class='msg_icon_wrp'><i class='icon80_smile'></i></span><div class='msg_content'><h4>请在APP客户端打开</h4></div></div></div>
                 </body>
             </html>");
             return;
        }
        $wx = WeAccount::create();
        $member = m('member')->getMember($openid);
        $userinfo = m('user')->getInfo();
        $followed = m('user')->followed($openid);
        $uid = 0;
        $mc = array();
        if (empty($member)) {
            if (is_weixin()) {
                $tempinfo = m('user')->oauth_info();
            }
            load()->model('mc');
            if ($followed) {
                $uid = mc_openid2uid($openid);
                $mc = mc_fetch($uid, array(
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
                'sex' => !empty($mc['gender']) ? $mc['gender'] : $userinfo['sex'],
                'createtime' => time() ,
                'status' => - 2,
                'unionid' => $tempinfo['unionid']
            );
            pdo_insert('xuan_mixloan_member', $member);
        } else {
            $upgrade = array();
            if ($followed) {
                $uid = mc_openid2uid($openid);
            }
            if (empty($member['unionid'])) {
                if (is_weixin()) {
                    $tempinfo = m('user')->oauth_info();
                    $upgrade['unionid'] = $tempinfo['unionid'];
                }
            }
            if (!empty($uid)) {
                if (empty($member['uid'])) {
                    $upgrade['uid'] = $uid;
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
        $check = pdo_fetch('SELECT id,msg FROM '.tablename("xuan_mixloan_payment")." WHERE uid=:uid ORDER BY id DESC", array(':uid'=>$uid));
        if ($check) {
            return ['code'=>'1','name'=>'代理', 'msg'=>$check['msg'], 'id'=>$check['id']];
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
        if (!$phone) {
            return false;
        }
        $res = pdo_fetchcolumn("SELECT uid FROM ".tablename("xuan_mixloan_inviter"). " WHERE phone=:phone", array(":phone"=>$phone));
        if (!$res && $openid) {
            $res = pdo_fetchcolumn("SELECT `qrcid` FROM ".tablename("qrcode_stat")." WHERE openid=:openid AND uniacid=:uniacid AND type=1 ORDER BY id DESC",array(":openid"=>$openid,":uniacid"=>$_W["uniacid"]));
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
}