<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_Advs
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
                if (is_array($v)) {
                    $wheres .= " AND `{$k}` IN (" . implode(',', $v) . ')';
                } else {
                    $wheres .= " AND `{$k}` = '{$v}'";
                }
            }
        }
        $sql = "SELECT {$fields} FROM ".tablename('xuan_mixloan_advs')." WHERE uniacid={$_W['uniacid']} {$wheres} ";
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
                if (!empty($value['ext_info'])) {
                    $value['ext_info'] = json_decode($value['ext_info'], true);
                }
                $ret[$value['id']] = $value;
            }
        }
        return $ret;
    }
}