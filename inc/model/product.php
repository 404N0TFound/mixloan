<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_Product
{
    public function getList($get=[], $conditon=[], $orderBy=FALSE, $limit=FALSE) {
        global $_W;
        $ret = [];
        $wheres = $fields = "";
        if (!empty($get)) {
            $fields = implode(',', $get);
        } else {
            $fields = '*';
        }
        if (!empty($conditon)) {
            foreach ($conditon as $k => $v) {
                if ($k == 'id' && is_array($v)) {
                    $v_string = implode(',', $v);
                    $wheres .= " AND `{$k}` IN ({$v_string})";
                } else if ($k == 'n_id') {
                    $wheres .= " AND `id` NOT IN ({$v})";
                } else {
                    $wheres .= " AND `{$k}` = '{$v}'";
                }
            }
        }
        $sql = "SELECT {$fields} FROM ".tablename('xuan_mixloan_product')." WHERE uniacid={$_W['uniacid']} {$wheres} ";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        } else {
            $sql .= " ORDER BY id DESC";
        }
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        $list = pdo_fetchall($sql);
        if (!empty($list)) {
            foreach ($list as $key => $value) {
                if (!empty($value['ext_info'])) {
                    $value['ext_info'] = json_decode($value['ext_info'], true);
                    $value['ext_info']['pic'] = tomedia($value['ext_info']['pic']);
                    $value['ext_info']['logo'] = tomedia($value['ext_info']['logo']);
                    $value['ext_info']['apply_pic'] = tomedia($value['ext_info']['apply_pic']);
                    if ($value['ext_info']['is_extra_reward']) {
                        if ($value['ext_info']['extra_reward_type'] == 1) {
                            $value['ext_info']['extra_reward_one'] = '邀请' . $value['ext_info']['extra_reward_one_con'] . '人';
                            $value['ext_info']['extra_reward_two'] = '邀请' . $value['ext_info']['extra_reward_two_con'] . '人';
                            $value['ext_info']['extra_reward_thr'] = '邀请' . $value['ext_info']['extra_reward_thr_con'] . '人';
                        } else {
                            $value['ext_info']['extra_reward_one'] = '放款' . $value['ext_info']['extra_reward_one_cond'] . '元';
                            $value['ext_info']['extra_reward_two'] = '放款' . $value['ext_info']['extra_reward_two_cond'] . '元';
                            $value['ext_info']['extra_reward_thr'] = '放款' . $value['ext_info']['extra_reward_thr_cond'] . '元';
                        }
                    }
                }
                switch ($value['count_time']) {
                    case '1':
                        $value['account_type'] = '日';
                        break;
                    case '7':
                        $value['account_type'] = '周';
                        break;
                    case '30':
                        $value['account_type'] = '月';
                        break;
                }
                $ret[$value['id']] = $value;
            }
        }
        return $ret;
    }

    public function getApplyList($get=[], $conditon=[], $orderBy=FALSE, $limit=FALSE) {
        global $_W;
        $ret = [];
        $wheres = $fields = "";
        if (!empty($get)) {
            $fields = implode(',', $get);
        } else {
            $fields = '*';
        }
        if (!empty($conditon)) {
            foreach ($conditon as $k => $v) {
                if (is_array($v)) {
                    $v_string = implode(',', $v);
                    $wheres .= " AND `{$k}` IN ({$v_string})";
                } else if ($k == 'la_status'){
                    $wheres .= " AND `status` > {$v}";
                } else {
                    $wheres .= " AND `{$k}` = '{$v}'";
                }
            }
        }
        $sql = "SELECT {$fields} FROM ".tablename('xuan_mixloan_bonus')." WHERE uniacid={$_W['uniacid']} {$wheres} ";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        } else {
            $sql .= " ORDER BY id DESC";
        }
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        $list = pdo_fetchall($sql);
        if (!empty($list)) {
            foreach ($list as $key => $value) {
                $ret[$value['id']] = $value;
            }
        }
        return $ret;
    }

    public function getAdvs() {
        global $_W;
        $list = pdo_fetchall('SELECT * FROM '.tablename('xuan_mixloan_product_advs').' WHERE uniacid=:uniacid ORDER BY id DESC', array(':uniacid'=>$_W['uniacid']));
        if ($list) {
            foreach ($list as &$row) {
                if (!empty($row['ext_info'])) $row['ext_info'] = json_decode($row['ext_info'], true);
                $row['image'] = tomedia($row['ext_info']['pic']);
                $row['url'] = $row['ext_info']['name'];
                $row['title'] = $row['name'];
            }
            unset($row);
        }
        return $list;
    }

    public function getRecommends(){
        global $_W;
        $sql = "SELECT * FROM ".tablename('xuan_mixloan_product')." where uniacid=:uniacid AND is_show=1 order by id desc limit 5";
        $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
        if ($list) {
            foreach ($list as &$row) {
                if (!empty($row['ext_info'])) {
                    $row['ext_info'] = json_decode($row['ext_info'], true);
                    $row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
                    $row['ext_info']['pic'] = tomedia($row['ext_info']['pic']);
                }
               
            }
            unset($row);
        }
        return $list;
    }
    public function packupItems($items) {
        if (!$items) {
            return [];
        }
        $return = [];
        foreach ($items as $item) {
            $res = [];
            $res['id'] = $item['id'];
            $res['title'] = $item['name'];
            switch ($item['count_time']) {
                case '1':
                    $res['day'] = 1;
                    break;
                case '30':
                    $res['day'] = 2;
                    break;
                case '7':
                    $res['day'] = 3;
                    break;
            }
            $res['imgs'] = $item['ext_info']['logo'];
            if ($item['done_reward_type'] == 1) {
                if ($item['ext_info']['done_one_init_reward_money'] == intval($item['ext_info']['done_one_init_reward_money'])) {
                    $done_money = intval($item['ext_info']['done_one_init_reward_money']);
                } else {
                    $done_money = $item['ext_info']['done_one_init_reward_money'];
                }
                $res['intro1'] = "{$done_money}";
            } else if ($item['done_reward_type'] == 2){
                if ($item['ext_info']['done_one_init_reward_per'] == intval($item['ext_info']['done_one_init_reward_per'])) {
                    $done_per = intval($item['ext_info']['done_one_init_reward_per']);
                } else {
                    $done_per = $item['ext_info']['done_one_init_reward_per'];
                }
                $res['intro1'] = "{$done_per}点";
            }
            if ($item['re_reward_type'] == 1) {
                if ($item['ext_info']['re_one_init_reward_money'] == intval($item['ext_info']['re_one_init_reward_money'])) {
                    $re_money = intval($item['ext_info']['re_one_init_reward_money']);
                } else {
                    $re_money = $item['ext_info']['re_one_init_reward_money'];
                }
                $res['intro1'] .= "+申请{$re_money}";
            } else if ($item['re_reward_type'] == 2){
                if ($item['ext_info']['re_one_init_reward_per'] == intval($item['ext_info']['re_one_init_reward_per'])) {
                    $re_per = intval($item['ext_info']['re_one_init_reward_per']);
                } else {
                    $re_per = $item['ext_info']['re_one_init_reward_per'];
                }
                $res['intro1'] .= "+申请{$re_per}点";
            }
            $res['hot'] = $item['is_hot'];
            $res['maintain'] = 1;
            $return[] = $res;
        }
        return $return;
    }

    /**
     *   获取特殊贷款
     **/
    public function getSpecialLoan($type, $where="") {
        global $_W;
        $sql = "SELECT a.id,a.relate_id,b.name,b.money_high,b.rate,b.rate_type,b.ext_info FROM ".tablename('xuan_mixloan_product')." a LEFT JOIN ".tablename("xuan_mixloan_loan")." b ON a.relate_id=b.id WHERE a.uniacid={$_W['uniacid']} AND find_in_set('{$type}',b.type) AND a.type=2 AND a.is_show=1 {$where} ORDER BY b.id";
        $list = pdo_fetchall($sql);
        $ret = [];
        if (!empty($list)) {
            foreach ($list as $value) {
                if ($value['ext_info']) {
                    $value['ext_info'] = json_decode($value['ext_info'], true);
                    $value['ext_info']['logo'] = tomedia($value['ext_info']['logo']);
                }
                $color = 'rgb('. rand(0,255) .','. rand(0,255) .','. rand(0,255) .')';
                $value['color'] = RGBToHex($color);
                $ret[$value['id']] = $value;
            }
        }
        return $ret;
    }

    /**
    *   获取虚假弹幕
    **/
    public function getBarrage($credit, $loan) {
        $name = $ret = [];
        if (!empty($credit)) {
            foreach ($credit as $value) {
                $name[] = $value['name'];
            }
        }
        if (!empty($loan)) {
            foreach ($loan as $value) {
                $name[] = $value['name'];
            }
        }
        $phone_f = ["130", "137", "138", "139", "150", "159", "170", "179", "180", "189"];
        if (!empty($name)) {
            shuffle($name);
            foreach ($name as $value) {
                $barrage = [];
                $rand = rand(0,9);
                $barrage['phone']   = $phone_f[$rand] . "****" . rand(1111,9999) ;
                $barrage['name']    = $value;
                $ret[] = $barrage;
            }
        }
        return $ret;
    }

    public function getIds($list) {
        $ids = [];
        if (!empty($list)) {
            foreach ($list as $key => $value) {
                $ids[] = $value['id'];
            }
        }
        return $ids;
    }

    /**
    *   1申请位数 
    *   2贷款放款成功&申请信用卡成功 
    *   3奖金
    **/
    public function getNums($product_ids=[], $params=[], $type) {
        global $_W;
        $wheres = "";
        if (!empty($product_ids) && !is_array($product_ids)) {
            $id_string = implode(',', $product_ids);
            $wheres .= "AND relate_id IN ({$id_string}) AND type=1";
        }
        $ret = [];
        $inviter = (int)$params['inviter'];
        if ($params['begin']) {
            $begin = strtotime($params['begin']);
            $end = strtotime($params['begin']." +1 month");
            $wheres .= " AND createtime>={$begin} AND createtime<={$end}";
        }
        if ($type == 1) {
            $fields = "relate_id, COUNT(*) AS count";
            $wheres .= " AND status<>-2 AND degree=1";
        } else if ($type == 2) {
            $fields = "relate_id, COUNT(*) AS count, SUM(relate_money) AS relate_money";
            $wheres .= " AND status>1";
        } else if ($type == 3) {
            $fields .= "relate_id, SUM(re_bonus+done_bonus+extra_bonus) AS bonus";
            $wheres .= " AND status>1";
        }
        $sql = "SELECT {$fields} FROM ".tablename("xuan_mixloan_bonus")." WHERE uniacid={$_W['uniacid']} AND inviter={$inviter} {$wheres} GROUP BY relate_id";
        $list = pdo_fetchall($sql);
        if ($list) {
            foreach ($list as $key => $value) {
                $ret[$value['relate_id']] = $value;
            }
        } 
        return $ret;
    }


    /**
     *   申请位数
     **/
    public function getApplys($params=[]) {
        global $_W;
        $inviter = (int)$params['inviter'];
        $begin = strtotime($params['begin']);
        $end = strtotime($params['begin']." +1 month");
        $fields = "COUNT(1) AS count,degree";
        $sql = "SELECT {$fields} FROM ".tablename("xuan_mixloan_bonus")." WHERE uniacid={$_W['uniacid']} AND createtime>={$begin} AND createtime<{$end} AND inviter={$inviter} AND type=1 GROUP BY degree";
        $res = pdo_fetchall($sql);
        if ($res) {
            foreach ($res as $row) {
                if ($row['degree'] == 1) {
                    $ret['one_degree'] = $row['count'];
                } else if ($row['degree'] == 2){
                    $ret['two_degree'] = $row['count'];
                }
            }
        }
        if (!$ret['one_degree']) {
            $ret['one_degree'] = 0;
        }
        if (!$ret['two_degree']) {
            $ret['two_degree'] = 0;
        }
        return $ret;
    }

    /**
    *   获取前10奖金
    **/
    public function getTopBonus($id){
        if (empty($id)) {
            return [];
        }
        $ret = [];
        $list = pdo_fetchall("SELECT inviter,SUM(relate_money) AS `money`,COUNT(1) AS count FROM ".tablename("xuan_mixloan_bonus")." WHERE relate_id={$id} AND type=1 GROUP BY inviter HAVING money<>0 AND inviter<>0 ORDER BY money,count DESC LIMIT 10");
        foreach ($list as $row) {
            $inviter_ids[] = $row['inviter'];
        }
        if ($inviter_ids) {
            $inviter_ids_string = implode(',', $inviter_ids);
            $members = pdo_fetchall("SELECT id,phone FROM ".tablename("xuan_mixloan_member")." where id IN ({$inviter_ids_string})");
            foreach ($members as $value) {
                $inviters[$value['id']] = $value['phone'];
            }
        }
        foreach ($list as $k => $v) {
            $v['phone'] = $inviters[$v['inviter']];
            $v['phone'] = substr($v['phone'], 0, 3) . '*****' . substr($v['phone'], -3);
            $ret[$k+1] = $v;
        }
        return $ret;
    }
}