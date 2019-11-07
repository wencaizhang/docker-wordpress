<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/3/2
 * Time: 17:40
 */

include_once __DIR__ . '/traits/CurlTrait.php';
include_once __DIR__ . '/traits/SignTrait.php';
include_once __DIR__ . '/traits/XmlTrait.php';
include_once __DIR__ . '/PayException.php';
include_once __DIR__ . '/WXPayConfig.php';

class BaseHelper
{
    use SignTrait;
    use XmlTrait;
    use CurlTrait;

    protected $apiClientCertPath;
    protected $apiClientKeyPath;

    protected $appId;
    protected $appSecret;
    protected $mchId;
    protected $paySecretKey;

    public function __construct(WXPayConfig $config = null)
    {
        if ($config) {
            $this->appId        = $config->appId();
            $this->appSecret    = $config->appSecret();
            $this->mchId        = $config->mchId();
            $this->paySecretKey = $config->paySecretKey();

            $this->apiClientKeyPath  = $config->apiClientKeyPath();
            $this->apiClientCertPath = $config->apiClientCertPath();
        }
    }

    protected function trimString($value)
    {
        $ret = null;
        if (null != $value) {
            $ret = $value;
            if (strlen($ret) == 0) {
                $ret = null;
            }
        }

        return $ret;
    }

    /**
     * 生成签名
     * @param $params
     * @return string
     */
    protected function getSign($params)
    {
        foreach ($params as $k => $v) {
            $parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        $signString = $this->formatBizQueryParaMap($parameters, false);

        //签名步骤二：在string后加入KEY
        $signString = $signString . "&key=" . $this->paySecretKey;

        //签名步骤三：MD5加密
        $signString = md5($signString);

        //签名步骤四：所有字符转为大写
        $result = strtoupper($signString);
        //echo "【result】 ".$result."</br>";

        return $result;
    }

    /**
     *    作用：设置标配的请求参数，生成签名，生成接口参数xml
     */
    protected function createXml()
    {
        $this->parameters["appid"]     = $this->appId; //公众账号ID
        $this->parameters["mch_id"]    = $this->mchId; //商户号
        $this->parameters["nonce_str"] = $this->createNonceStr(); //随机字符串
        $this->parameters["sign"]      = $this->getSign($this->parameters); //签名
        $xml                           = $this->arrayToXml($this->parameters);

        return $xml;
    }

    protected function postSSL($url, $data)
    {
        return $this->httpPostSSL($url, $data, $this->apiClientCertPath, $this->apiClientKeyPath);
    }

    /**
     * @param string $apiClientCertPath
     */
    public function setApiClientCertPath($apiClientCertPath)
    {
        $this->apiClientCertPath = $apiClientCertPath;
    }

    /**
     * @param string $apiClientKeyPath
     */
    public function setApiClientKeyPath($apiClientKeyPath)
    {
        $this->apiClientKeyPath = $apiClientKeyPath;
    }

    /**
     * @param mixed $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @param mixed $appSecret
     */
    public function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;
    }

    /**
     * @param mixed $mchId
     */
    public function setMchId($mchId)
    {
        $this->mchId = $mchId;
    }

    /**
     * @param mixed $paySecretKey
     */
    public function setPaySecretKey($paySecretKey)
    {
        $this->paySecretKey = $paySecretKey;
    }


}
