<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/3/2
 * Time: 11:18
 */

include_once __DIR__ . '/PayHelper.php';

class DownloadBillHelper extends PayHelper
{
    const BILL_TYPE_ALL     = "ALL";
    const BILL_TYPE_SUCCESS = "SUCCESS";
    const BILL_TYPE_REFUND  = "REFUND";
    const BILL_TYPE_REVOKED = "REVOKED";

    public $billHeader;
    public $bills;
    public $billAmount;
    private $billType;

    public function __construct(WXPayConfig $config)
    {
        parent::__construct($config);
        $this->url = "https://api.mch.weixin.qq.com/pay/downloadbill";
    }

    protected function createXml()
    {
        if (!isset($this->parameters["bill_date"])) {
            throw new PayException("对账单接口中，缺少必填参数bill_date！" . "<br>");
        }
        if (!isset($this->parameters["bill_type"])) {
            $this->setParameter("bill_type", Bill::BILL_TYPE_ALL);
        }
        $this->billType = $this->parameters["bill_type"];

        return parent::createXml();
    }

    public function getResult()
    {
        $result = parent::getResult();
        if (!$result) {
            $this->parseBills();
        }

        return $result;
    }

    private function parseBills()
    {

        $array = explode("\r\n", $this->response);
        if (!count($array)) {
            return;
        }

        $string           = $array[0];
        $header           = explode(",", $string);
        $this->billHeader = $header;

        $this->bills = [];
        $index       = 1;
        for (; $index < count($array) - 3; $index++) {
            $string        = $array[$index];
            $this->bills[] = explode(",", $string);
        }

        $this->billAmount = [];
        for (; $index < count($array) - 1; $index++) {
            $string             = $array[$index];
            $this->billAmount[] = explode(",", $string);
        }
    }
}
