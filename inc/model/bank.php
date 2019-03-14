<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_Bank
{
    public function getList() {
        global $_W;
        $banks = pdo_fetchall('SELECT * FROM '.tablename("xuan_mixloan_bank")." WHERE uniacid=:uniacid order by id desc limit 8", array(':uniacid'=>$_W['uniacid']));
        if (empty($banks)) {
            return [];
        }
        foreach ($banks as $key => $value) {
            $value['ext_info'] = json_decode($value['ext_info'], true);
            $ret[$value['id']] = $value;
        }
        return $ret;
    }

    public function getArtical($get=[], $conditon=[]) {
        global $_W;
        $wheres = $fields = "";
        if (!empty($get)) {
            $fields = implode(',', $get);
        } else {
            $fields = '*';
        }
        if (!empty($conditon)) {
            foreach ($conditon as $k => $v) {
                $wheres .= " AND `{$k}` = '{$v}'";
            }
        }
        $sql = "SELECT {$fields} FROM ".tablename('xuan_mixloan_bank_artical')." WHERE uniacid={$_W['uniacid']} {$wheres}";
        $list = pdo_fetchall($sql);
        foreach ($list as $key => $value) {
            if (!empty($value['ext_info'])) $value['ext_info'] = json_decode($value['ext_info'], true);
            $ret[$value['id']] = $value;
        }
        return $ret;
    }

    public function getCard($get=[], $conditon=[], $orderBy=FALSE, $limit=FALSE) {
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
                if ($k == 'card_type') {
                    $wheres .= " AND find_in_set('{$v}', `{$k}`)";
                } else if ($k == 'id' && is_array($v) && !empty($v)) {
                    $v_string = implode(',', $v);
                    $wheres .= " AND `{$k}` IN ({$v_string})";
                } else {
                    $wheres .= " AND `{$k}` = '{$v}'";
                }
            }
        }
        $sql = "SELECT {$fields} FROM ".tablename('xuan_mixloan_bank_card')." WHERE uniacid={$_W['uniacid']} {$wheres} ";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
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
                $ret[$value['id']] = $value;
            }
        }
        return $ret;
    }

    public function getRecommendCard($list){
        if (!$list) {
            return [];
        }
        $len = count($list);
        $keys = array_keys($list);
        shuffle($keys);
        if (isset($keys[0])) {
            $ret[] = $list[$keys[0]];
        }
        if (isset($keys[1])) {
            $ret[] = $list[$keys[1]];
        }
        if (isset($keys[2])) {
            $ret[] = $list[$keys[2]];
        }
        return $ret;
    }
}