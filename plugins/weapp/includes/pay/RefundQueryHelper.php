<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/3/2
 * Time: 11:15
 */

include_once __DIR__ . '/PayHelper.php';

class RefundQueryHelper extends PayHelper
{

    public function __construct(WXPayConfig $config)
    {
        parent::__construct($config);
        $this->url = "https://api.mch.weixin.qq.com/pay/refundquery";
    }

    protected function createXml()
    {
        if (!isset($this->parameters["out_refund_no"]) &&
            !isset($this->parameters["out_trade_no"]) &&
            !isset($this->parameters["transaction_id"]) &&
            !isset($this->parameters["refund_id "])
        ) {
            throw new PayException("退款查询接口中，out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个！" . "<br>");
        }

        return parent::createXml();
    }
}
