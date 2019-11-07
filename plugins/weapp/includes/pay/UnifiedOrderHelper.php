<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/2/28
 * Time: 17:48
 */

include_once __DIR__ . '/PayHelper.php';

class UnifiedOrderHelper extends PayHelper
{
    /**
     * 预支付交易会话标识
     * @var string
     */
    private $prepayId;

    /**
     * 支付链接
     * trade_type为NATIVE是有返回，可将该参数值生成二维码展示出来进行扫码支付
     * @var string
     */
    private $payCodeUrl;

    public function __construct(WXPayConfig $config)
    {
        parent::__construct($config);
        $this->url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    }

    protected function createXml()
    {
        if (null == $this->parameters["out_trade_no"]) {
            throw new PayException("缺少统一支付接口必填参数out_trade_no！" . "<br>");
        } elseif (null == $this->parameters["body"]) {
            throw new PayException("缺少统一支付接口必填参数body！" . "<br>");
        } elseif (null == $this->parameters["total_fee"]) {
            throw new PayException("缺少统一支付接口必填参数total_fee！" . "<br>");
        } elseif (null == $this->parameters["notify_url"]) {
            throw new PayException("缺少统一支付接口必填参数notify_url！" . "<br>");
        } elseif (null == $this->parameters["trade_type"]) {
            throw new PayException("缺少统一支付接口必填参数trade_type！" . "<br>");
        } elseif ("JSAPI" == $this->parameters["trade_type"] && null == $this->parameters["openid"]) {
            throw new PayException("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！" . "<br>");
        }

        if (!isset($this->parameters["spbill_create_ip"])) {
            $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR']; //终端ip
        }

        return parent::createXml();
    }

    public function getResult()
    {
        parent::getResult();
        if (isset($this->result['prepay_id'])) {
            $this->prepayId = $this->result['prepay_id'];
        }
        if (isset($this->result['code_url'])) {
            $this->payCodeUrl = $this->result['code_url'];
        }

        return $this->result;
    }

    public function getPrepayId()
    {
        return $this->prepayId;
    }

    public function getPayCodeUrl()
    {
        return $this->payCodeUrl;
    }
}
