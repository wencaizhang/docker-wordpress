<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/6/12
 * Time: 17:48
 */

include_once __DIR__ . '/PayHelper.php';

class CloseOrderHelper extends PayHelper
{
    public function __construct(WXPayConfig $config)
    {
        parent::__construct($config);
        $this->url = "https://api.mch.weixin.qq.com/pay/closeorder";
    }

    protected function createXml()
    {
        if (null == $this->parameters["out_trade_no"]) {
            throw new PayException("缺少统一支付接口必填参数out_trade_no！");
        }

        return parent::createXml();
    }
}
