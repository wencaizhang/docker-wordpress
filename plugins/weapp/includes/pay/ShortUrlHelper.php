<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/3/2
 * Time: 11:20
 */

include_once __DIR__ . '/PayHelper.php';

/**
 * 该接口主要用于扫码原生支付模式一中的二维码链接转成短链接(weixin://wxpay/s/XXXXXX)，减小二维码数据量，提升扫描速度和精确度。Ω
 * Class ShortUrlHelper
 * @package Weixin\Pay
 */
class ShortUrlHelper extends PayHelper
{
    public function __construct(WXPayConfig $config)
    {
        parent::__construct($config);
        $this->url = "https://api.mch.weixin.qq.com/tools/shorturl";
    }

    protected function createXml()
    {
        if (null == $this->parameters["long_url"]) {
            throw new PayException("短链接转换接口中，缺少必填参数long_url！" . "<br>");
        }

        return parent::createXml(); // TODO: Change the autogenerated stub
    }
}
