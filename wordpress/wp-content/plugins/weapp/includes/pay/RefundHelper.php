<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/3/2
 * Time: 11:11
 */

include_once __DIR__ . '/PayHelper.php';

class RefundHelper extends PayHelper
{

    public function __construct(WXPayConfig $config)
    {
        parent::__construct($config);
        $this->url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
    }

    /**
     * @return string
     * @throws PayException
     */
    protected function createXml()
    {
        //检测必填参数
        if (!isset($this->parameters["out_trade_no"]) && !isset($this->parameters["transaction_id"])) {
            throw new PayException("退款申请接口中，out_trade_no、transaction_id至少填一个！" . "<br>");
        } elseif (!isset($this->parameters["out_refund_no"])) {
            throw new PayException("退款申请接口中，缺少必填参数out_refund_no！" . "<br>");
        } elseif (!isset($this->parameters["total_fee"])) {
            throw new PayException("退款申请接口中，缺少必填参数total_fee！" . "<br>");
        } elseif (!isset($this->parameters["refund_fee"])) {
            throw new PayException("退款申请接口中，缺少必填参数refund_fee！" . "<br>");
        } elseif (!isset($this->parameters["op_user_id"])) {
//            throw new PayException("退款申请接口中，缺少必填参数op_user_id！" . "<br>");
            $this->parameters['op_user_id'] = $this->mchId;
        }

        return parent::createXml();
    }

    public function getResult()
    {
        $this->postXmlSSL();
        $this->result = $this->xmlToArray($this->response);

        return $this->result;
    }

    public function getRefundId()
    {
        return isset($this->result['refund_id']) ? $this->result['refund_id'] : null;
    }

    public function getRefundChannel()
    {
        return isset($this->result['refund_channel']) ? $this->result['refund_channel'] : null;
    }
}
