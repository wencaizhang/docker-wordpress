<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/3/2
 * Time: 10:47
 */

include_once __DIR__ . '/PayHelper.php';

class OrderQueryHelper extends PayHelper
{

    public function __construct(WXPayConfig $config)
    {
        parent::__construct($config);
        $this->url = "https://api.mch.weixin.qq.com/pay/orderquery";
    }

    /**
     * @return string
     * @throws PayException
     */
    protected function createXml()
    {
        //检测必填参数
        if (!isset($this->parameters["out_trade_no"]) && !isset($this->parameters["transaction_id"])) {
            throw new PayException("订单查询接口中，out_trade_no、transaction_id至少填一个！" . "<br>");
        }

        return parent::createXml();
    }
}
