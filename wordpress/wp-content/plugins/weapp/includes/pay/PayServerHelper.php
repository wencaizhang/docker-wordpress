<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/3/2
 * Time: 17:40
 */

include_once __DIR__ . '/BaseHelper.php';

class PayServerHelper extends BaseHelper
{

    protected $data; //接收到的数据，类型为关联数组
    protected $returnParameters; //返回参数，类型为关联数组

    /**
     * 将微信的请求xml转换成关联数组，以方便数据处理
     * @param $xml
     * @return mixed
     */
    public function parseXML($xml)
    {
        $this->data = $this->xmlToArray($xml);

        return $this->data;
    }

    /**
     * 检查签名
     * @return bool
     */
    public function checkSign()
    {
        $tmpData = $this->data;
        unset($tmpData['sign']);
        $sign = $this->getSign($tmpData); //本地签名
        if ($this->data['sign'] == $sign) {
            return true;
        }

        return false;
    }

    /**
     * 获取微信的请求数据
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 设置返回微信的xml数据
     */
    public function setReturnParameter($parameter, $parameterValue)
    {
        $this->returnParameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    /**
     * 生成接口参数xml
     * @return string
     */
    protected function createXml()
    {
        return $this->arrayToXml($this->returnParameters);
    }

    /**
     * 将xml数据返回微信
     */
    public function returnXml()
    {
        $returnXml = $this->createXml();

        return $returnXml;
    }
}
