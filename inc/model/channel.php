<?php
defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_Channel
{
    public function getList($get=[], $conditon=[], $orderBy=FALSE, $limit=FALSE, $offset=0) {
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
                if ($k == 'title') {
                    $wheres .= " AND `{$k}` LIKE '%{$v}%'";
                }  else if ($k == 'subject_id' && is_array($v)) {
                    $IN = implode(',', $v);
                    $wheres .= " AND `{$k}` IN ({$IN})";
                }else {
                    $wheres .= " AND `{$k}` = '{$v}'";
                }
            }
        }
        if ($offset) {
            $wheres .= " AND id<{$offset}";
        }
        $sql = "SELECT {$fields} FROM ".tablename('xuan_mixloan_channel')." WHERE uniacid={$_W['uniacid']} {$wheres} ";
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
                if (in_array('createtime',$get)) $value['createtime'] = date('Y-m-d', $value['createtime']);
                if (in_array('createtime',$get)) $value['ext_info']['pic'] = tomedia($value['ext_info']['pic']);
                $ret[$value['id']] = $value;
            }
        }
        return $ret;
    }

    public function getSubjectList($get=[], $conditon=[], $orderBy=FALSE, $limit=FALSE, $offset=FALSE) {
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
                if ($k == 'name') {
                    $wheres .= " AND `{$k}` LIKE '%{$v}%'";
                } else {
                    $wheres .= " AND `{$k}` = '{$v}'";
                }
            }
        }
        $sql = "SELECT {$fields} FROM ".tablename('xuan_mixloan_channel_subject')." WHERE uniacid={$_W['uniacid']} {$wheres} ";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        } else {
            $sql .= " ORDER BY id DESC";
        }
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        if ($offset) {
            $sql .= " OFFSET {$offset}";
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

    public function getAdvs() {
        global $_W;
        $list = pdo_fetchall('SELECT * FROM '.tablename('xuan_mixloan_channel_advs').' WHERE uniacid=:uniacid ORDER BY id DESC', array(':uniacid'=>$_W['uniacid']));
        if ($list) {
            foreach ($list as &$row) {
                if (!empty($row['ext_info'])) $row['ext_info'] = json_decode($row['ext_info'], true);
            }
            unset($row);
        }
        return $list;
    }

    public function getCommendSubjects(){
        global $_W;
        $sql = "SELECT t1.id,t1.name 
            FROM ".tablename('xuan_mixloan_channel_subject')." AS t1 JOIN (SELECT ROUND(RAND() * (SELECT MAX(id) FROM ".tablename('xuan_mixloan_channel_subject').")) AS id) AS t2 
            WHERE t1.id >= t2.id AND t1.uniacid=:uniacid
            ORDER BY t1.id ASC LIMIT 10";
        $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
        return $list;
    }
}