<?php

/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/3/3
 * Time: 16:19
 */
class WXPayConfig
{
    private $appId;
    private $appSecret;
    private $mchId;
    private $paySecretKey;

    private $apiClientCertPath;
    private $apiClientKeyPath;

    /**
     * @param $appId
     * @param $secret
     * @param $mchId
     * @param $paySecretKey
     * @param $certPath
     * @param $keyPath
     */
    public function __construct($appId, $secret, $mchId, $paySecretKey, $certPath = null, $keyPath = null)
    {
        $this->appId             = $appId;
        $this->appSecret         = $secret;
        $this->mchId             = $mchId;
        $this->paySecretKey      = $paySecretKey;
        $this->apiClientCertPath = $certPath;
        $this->apiClientKeyPath  = $keyPath;

        if ( ! $this->apiClientCertPath) {
            $this->apiClientCertPath = WPJAM_PLUGIN_WEIXIN_PAY_CERT_DIR.DIRECTORY_SEPARATOR.$this->mchId.'/apiclient_cert.pem';;
        }
        if ( ! $this->apiClientKeyPath) {
            $this->apiClientKeyPath = WPJAM_PLUGIN_WEIXIN_PAY_CERT_DIR.DIRECTORY_SEPARATOR.$this->mchId.'/apiclient_key.pem';
        }
    }

    public function appId()
    {
        return $this->appId;
    }

    public function appSecret()
    {
        return $this->appSecret;
    }

    public function mchId()
    {
        return $this->mchId;
    }

    public function paySecretKey()
    {
        return $this->paySecretKey;
    }

    public function apiClientCertPath()
    {
        return $this->apiClientCertPath;
    }

    public function apiClientKeyPath()
    {
        return $this->apiClientKeyPath;
    }
}
