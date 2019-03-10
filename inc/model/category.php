<?php

defined('IN_IA') or exit('Access Denied');
class Xuan_mixloan_Category {
    /**
     * @param array $get
     * @param array $conditon
     * @param bool $orderBy
     * @param bool $limit
     * @param bool $pindex
     * @return array
     */
    public function getList($get=[], $conditon=[], $orderBy=FALSE, $pindex=FALSE, $psize=FALSE)
    {
        global $_W;
        $ret = [];
        $wheres = $fields = "";
        if (!empty($get))
        {
            $fields = implode(',', $get);
        }
        else
        {
            $fields = '*';
        }
        if (!empty($conditon))
        {
            foreach ($conditon as $k => $v)
            {
                if (is_array($v))
                {
                    $v_string = implode(',', $v);
                    $wheres .= " AND `{$k}` IN ({$v_string})";
                }
                else
                {
                    $wheres .= " AND `{$k}` = '{$v}'";
                }
            }
        }
        $sql = "SELECT {$fields} FROM " . tablename('xuan_mixloan_category') . "
            WHERE uniacid={$_W['uniacid']} {$wheres} ";
        if ($orderBy)
        {
            $sql .= " ORDER BY {$orderBy}";
        }
        else
        {
            $sql .= " ORDER BY id DESC";
        }
        if ($psize && !$pindex)
        {
            $sql .= " LIMIT {$psize}";
        }
        if ($psize && $pindex)
        {
            $sql .= " LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        }
        $list = pdo_fetchall($sql);
        if (!empty($list))
        {
            foreach ($list as $key => $value)
            {
                if (!empty($value['ext_info']))
                {
                    $value['ext_info'] = json_decode($value['ext_info'], true);
                }
                $ret[$value['id']] = $value;
            }
        }
        return $ret;
    }    
    /**
     * @param array $fields
     * @param array $conditon
     * @return string
     */
    public function getValue($fields='COUNT(*)', $conditon=[])
    {
        global $_W;
        $wheres = "";
        if (!empty($conditon))
        {
            foreach ($conditon as $k => $v)
            {
                if (is_array($v))
                {
                    $v_string = implode(',', $v);
                    $wheres .= " AND `{$k}` IN ({$v_string})";
                }
                else if (strstr($k, 'like'))
                {
                    $k = explode('_', $k)[1];
                    $wheres .= " AND `{$k}` LIKE '%{$v}%'";
                }
                else
                {
                    $wheres .= " AND `{$k}` = '{$v}'";
                }
            }
        }
        $sql = "SELECT {$fields} FROM " . tablename('xuan_mixloan_category') . "
            WHERE uniacid={$_W['uniacid']} {$wheres} ";
        $result = pdo_fetchcolumn($sql);
        return $result;
    }

}

