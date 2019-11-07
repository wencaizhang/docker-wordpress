<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/2/28
 * Time: 12:17
 */

include_once __DIR__ . '/BaseHelper.php';

class PayHelper extends BaseHelper
{
    protected $parameters; //请求参数，类型为关联数组

    protected $url; //接口url
    public $response; //微信返回的响应
    public $result; //返回参数，类型为关联数组, 由response获得

    /**
     * 设置请求参数
     * @param $parameter
     * @param $parameterValue
     */
    public function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * post请求xml
     * @return bool|mixed
     */
    public function postXml()
    {
        $xml            = $this->createXml();
        $this->response = $this->httpPost($this->url, $xml);

        // trigger_error(var_export($this->response, true));
        return $this->response;
    }

    /**
     * 使用证书post请求xml
     * @return bool|mixed
     */
    public function postXmlSSL()
    {
        $xml            = $this->createXml();
        $this->response = $this->postSSL($this->url, $xml);

        return $this->response;
    }

    /**
     * 获取结果，默认不使用证书
     * @return mixed
     */
    public function getResult()
    {
        $this->postXml();
        $this->result = $this->xmlToArray($this->response);

        return $this->result;
    }

    const RETURN_CODE_SUCCESS = 'SUCCESS';
    const RETURN_CODE_FAIL    = 'FAIL';
    public function getReturnCode()
    {
        return isset($this->result['return_code']) ? $this->result['return_code'] : null;
    }

    public function getReturnMsg()
    {
        return isset($this->result['return_msg']) ? $this->result['return_msg'] : null;
    }

    const RESULT_CODE_SUCCESS = 'SUCCESS';
    const RESULT_CODE_FAIL    = 'FAIL';
    public function getResultCode()
    {
        return isset($this->result['result_code']) ? $this->result['result_code'] : null;
    }

    public function getErrCode()
    {
        return isset($this->result['err_code']) ? $this->result['err_code'] : null;
    }

    public function getErrCodeDes()
    {
        return isset($this->result['err_code_des']) ? $this->result['err_code_des'] : null;
    }

    public function getJsPayParameters($prepayId)
    {
        $jsApiObj["appId"]     = $this->appId;
        $timeStamp             = time();
        $jsApiObj["timeStamp"] = "$timeStamp";
        $jsApiObj["nonceStr"]  = $this->createNoncestr();
        $jsApiObj["package"]   = "prepay_id=$prepayId";
        $jsApiObj["signType"]  = "MD5";
        $jsApiObj["paySign"]   = $this->getSign($jsApiObj);

        return $jsApiObj;
    }

}
