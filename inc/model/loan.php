<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_Loan
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
                if ($k == 'type') {
                    $wheres .= " AND find_in_set('{$v}', `{$k}`)";
                } else if ($k == 'begin') {
                    $wheres .= " AND `money_high` >= {$v}";
                } else if ($k == 'end') {
                    $wheres .= " AND `money_high` <= {$v}";
                } else if ($k == 'least') {
                    $wheres .= " AND `time_blow` >= {$v}";
                } else if ($k == 'high') {
                    $wheres .= " AND `time_high` <= {$v}";
                } else {
                    $wheres .= " AND `{$k}` = '{$v}'";
                }
            }
        }
        $sql = "SELECT {$fields} FROM ".tablename('xuan_mixloan_loan')." WHERE uniacid={$_W['uniacid']} {$wheres} ";
        if ($orderBy) {
            if ($orderBy == 1) {
                $sql .= ' ORDER BY `money_high` DESC';
            } else if ($orderBy == 2) {
                $sql .= ' ORDER BY (`rate`/`rate_type`) ASC';
            } else if ($orderBy == 3) {
                $sql .= ' ORDER BY `apply_nums` DESC';
            } else {
                $sql .= " ORDER BY {$orderBy}";
            }
        } else {
            $sql .= " ORDER BY sort DESC";
        }
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        $list = pdo_fetchall($sql);
        if (!empty($list)) {
            foreach ($list as $key => $value) {
                if (!empty($value['ext_info'])) $value['ext_info'] = json_decode($value['ext_info'], true);
                if ($value['rate_type'] == 1) {
                    $value['rate_type'] = '日';
                } else if ($value['rate_type'] == 30) {
                    $value['rate_type'] = '月';
                }
                $ret[$value['id']] = $value;
            }
        }
        return $ret;
    }

    public function getAdvs() {
        global $_W;
        $list = pdo_fetchall('SELECT * FROM '.tablename('xuan_mixloan_loan_advs').' WHERE uniacid=:uniacid ORDER BY id DESC', array(':uniacid'=>$_W['uniacid']));
        if ($list) {
            foreach ($list as &$row) {
                if (!empty($row['ext_info'])) $row['ext_info'] = json_decode($row['ext_info'], true);
            }
            unset($row);
        }
        return $list;
    }

    public function getRecommends(){
        global $_W;
        $sql = "SELECT * FROM " .tablename('xuan_mixloan_loan'). "
            WHERE uniacid=:uniacid and status=1 ORDER BY RAND() LIMIT 3";
        $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
        if ($list) {
            foreach ($list as &$row) {
                if (!empty($row['ext_info'])) {
                    $row['ext_info'] = json_decode($row['ext_info'], true);
                    $row['ext_info']['logo'] = tomedia($row['ext_info']['logo']);
                }
                if ($row['rate_type'] == 1) {
                    $row['rate_type'] = '日';
                } else if ($row['rate_type'] == 30) {
                    $row['rate_type'] = '月';
                }
            }
            unset($row);
        }
        return $list;
    }

    public function getBarrage($list) {
        $ret = [];
        if ($list) {
            $name = ['赵', '王', '钱', '孙', '李', '周', '吴', '郑', '冯', '陈', '诸', '卫', '蒋', '沈', '韩', '杨', '刘', '许', '尤', '何', '吕', '施', '张'];
            $sex = ['先生','女士'];
            foreach ($list as $value) {
                $n_rand = rand(0,count($name)-1);
                $s_rand = rand(0,count($sex)-1);
                $res['name'] = $name[$n_rand].$sex[$s_rand];
                $res['loan'] = $value['name'];
                $res['money'] = rand(50,100)*100;
                $loops = 0;
                while($res['money'] > $value['money_high']) {
                    if($loops ++ > 5) {
                        $loops = 0;
                        break;//防止死循环
                    }
                    $res['money'] = rand(20,50)*100;
                }
                while($res['money'] > $value['money_high']) {
                    if($loops ++ > 5) {
                        $loops = 0;
                        break;//防止死循环
                    }
                    $res['money'] = rand(5,20)*100;
                }
                $ret[] = $res;
            }
        }
        return $ret;
    }

    public function getBarrageB() {
        //获得弹幕信息 0是虚假，1是真实
        $min = 1000;
        $max = 20000;
        $name = "赵 钱 孙 李 周 吴 郑 王 冯 陈 褚 卫 蒋 沈 韩 杨 朱 秦 尤 许 何 吕 施 张 孔 曹 严 华 金 魏 陶 姜 戚 谢 邹 喻 刘 黄";
        $name = explode(" ", $name);
        for ($i = 0; $i < 10; $i++) {
            $ar = rand(0, count($name) - 1);
            $res[$i]['realname'] = $name[$ar] . '**';
            $res[$i]['createtime'] = date('Y-m-d H:i:s', time() - rand(0, 9999));
            $res[$i]['money'] = substr(rand($min, $max) , 2) * 100;
        }
        return $res;
    }
    public function checkRecord($type, $relate_id, $phone)
    {
        global $_W;
        $array = array(
            'type'=>$type,
            ':uniacid'=>$_W['uniacid'],
            ':relate_id'=>$relate_id,
            ':phone'=>$phone,
        );
        $record = pdo_fetchcolumn('select count(1) from '.tablename('xuan_mixloan_apply').'
            where uniacid=:uniacid and relate_id=:relate_id and phone=:phone', $array);
        return $record;
    }
}