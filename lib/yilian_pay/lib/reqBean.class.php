<?php
/**
 * Created by PhpStorm.
 * User: Durban
 * Date: 2015/1/5
 * Time: 20:41
 */
//请求类实体
class reqBean
{

    public $VERSION = ""; //版本号	
    public $MSG_TYPE = ""; //消息类型
    public $BATCH_NO = ""; //批次号
    public $USER_NAME = ""; //员工名
    public $TRANS_STATE = ""; //请求状态	ANS		0000表示请求成功
    public $MSG_SIGN = ""; //签名信息	ANS		对整个报文签名
    public $TRANS_DETAILS = array();
    public $DETATL_FIELDS = array("SN", "BANK_CODE", "ACC_NO", "ACC_NAME", "ACC_PROVINCE", "ACC_CITY", "AMOUNT", "MOBILE_NO", "PAY_STATE", "BANK_NO", "BANK_NAME", "ACC_TYPE", "ACC_PROP", "ID_TYPE", "ID_NO", "CNY", "EXCHANGE_RATE", "SETT_AMOUNT", "USER_LEVEL", "SETT_DATE", "REMARK", "RESERVE", "RETURN_URL", "MER_ORDER_NO", "MER_SEQ_NO", "QUERY_NO_FLAG", "TRANS_DESC","SMS_CODE");

    //添加数组
    public function addDetail($detail)
    {
        $this->TRANS_DETAILS[] = $detail;
    }

    //清空数据
    public function clearFields()
    {
        $this->VERSION = "";
        $this->MSG_TYPE = "";
        $this->BATCH_NO = "";
        $this->USER_NAME = "";
        $this->TRANS_STATE = "";
        $this->MSG_SIGN = "";
        $this->TRANS_DETAILS = array();
        $this->DETATL_FIELDS = array("SN", "BANK_CODE", "ACC_NO", "ACC_NAME", "ACC_PROVINCE", "ACC_CITY", "AMOUNT", "MOBILE_NO", "PAY_STATE", "BANK_NO", "BANK_NAME", "ACC_TYPE", "ACC_PROP", "ID_TYPE", "ID_NO", "CNY", "EXCHANGE_RATE", "SETT_AMOUNT", "USER_LEVEL", "SETT_DATE", "REMARK", "RESERVE", "RETURN_URL", "MER_ORDER_NO", "MER_SEQ_NO", "QUERY_NO_FLAG", "TRANS_DESC","SMS_CODE");
    }

    //类转换成xml
    public function classToXml()
    {
        $xml = "<MSGBEAN>";
        $xml .= "<VERSION>" . $this->VERSION . "</VERSION>";
        $xml .= "<MSG_TYPE>" . $this->MSG_TYPE . "</MSG_TYPE>";
        $xml .= "<BATCH_NO>" . $this->BATCH_NO . "</BATCH_NO>";
        $xml .= "<USER_NAME>" . $this->USER_NAME . "</USER_NAME>";
        $xml .= "<TRANS_STATE>" . $this->TRANS_STATE . "</TRANS_STATE>";
        $xml .= "<MSG_SIGN>" . $this->MSG_SIGN . "</MSG_SIGN>";
        $xml .= "<TRANS_DETAILS>";
        foreach ($this->TRANS_DETAILS as $item)
        {
            $xml .= "<TRANS_DETAIL>";
            foreach ($this->DETATL_FIELDS as $val)
            {
                $xml .= "<" . $val . ">" . (isset($item[$val]) ? $item[$val] : "") . "</" . $val . ">";
            }
            $xml .= "</TRANS_DETAIL>";
        }
        $xml .= "</TRANS_DETAILS>";
        $xml .= "</MSGBEAN>";
        return $xml;
    }

    public function xmlToClass($xml)
    {
        $this->clearFields();
        $data = simplexml_load_string($xml);
        $this->VERSION = isset($data->VERSION) ? (string) $data->VERSION : "";
        $this->MSG_TYPE = isset($data->MSG_TYPE) ? (string) $data->MSG_TYPE : "";
        $this->BATCH_NO = isset($data->BATCH_NO) ? (string) $data->BATCH_NO : "";
        $this->USER_NAME = isset($data->USER_NAME) ? (string) $data->USER_NAME : "";
        $this->TRANS_STATE = isset($data->TRANS_STATE) ? (string) $data->TRANS_STATE : "";
        $this->MSG_SIGN = isset($data->MSG_SIGN) ? (string) $data->MSG_SIGN : "";
        $this->TRANS_DETAILS = array();
        $list = isset($data->TRANS_DETAILS->TRANS_DETAIL) ? $data->TRANS_DETAILS->TRANS_DETAIL : array();
        if ($list)
        {
            foreach ($list as $item)
            {
                $row = array();
                foreach ($this->DETATL_FIELDS as $val)
                {
                    $row[$val] = isset($item->$val) ? (string) $item->$val : "";
                }
                $this->TRANS_DETAILS[] = $row;
            }
        }
    }

    //返回类签名信息
    public function toSign()
    {
        $sign = "";
        $sign .= $this->BATCH_NO;
//        if ($this->VERSION)
//            $sign .= ($this->VERSION ? " " . $this->VERSION : "");
        $sign .= ($this->USER_NAME ? " " . $this->USER_NAME : "");
        $sign .= ($this->MSG_TYPE ? " " . $this->MSG_TYPE : "");
        $sign .= ($this->TRANS_STATE ? " " . $this->TRANS_STATE : "");
        if ($this->TRANS_DETAILS)
        {
            foreach ($this->TRANS_DETAILS as $item)
            {
                $sign .= ($item['SN'] ? " " . $item['SN'] : "");
                $sign .= ($item['PAY_STATE'] ? " " . $item['PAY_STATE'] : "");
                $sign .= ($item['ACC_NO'] ? " " . $item['ACC_NO'] : "");
                $sign .= ($item['ACC_NAME'] ? " " . $item['ACC_NAME'] : "");
                $sign .= ($item['AMOUNT'] ? " " . $item['AMOUNT'] : "");
                $sign .= ($item['CNY'] ? " " . $item['CNY'] : "");
            }
        }
        return $sign;
    }

}