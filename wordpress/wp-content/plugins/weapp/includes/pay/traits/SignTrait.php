<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/3/4
 * Time: 18:17
 */


trait SignTrait {

    /**
     * 使用sha1生成签名参数
     * @param $params
     * @return string
     */
    protected function sha1Signature($params) {
        $string = $this->formatBizQueryParaMap($params, false);
        $signString = sha1($string);
        return $signString;
    }

    /**
     * 使用md5生成签名参数
     * @param $params
     * @param bool $upcase
     * @return string
     */
    protected function md5Signature($params, $upcase = true){
        $string = $this->formatBizQueryParaMap($params, false);
        if ($upcase) {
            $signString = strtoupper(md5($string));
        }
        return $signString;
    }

    /**
     * 格式化参数，签名过程需要使用
     * 这里参数的顺序要按照 key 值 ASCII 码升序排序
     * @param $paraMap
     * @param $urlencode
     * @return string
     */
    protected function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $key => $value) {
            if ($urlencode) {
                $value = urlencode($value);
            }
            $buff .= $key . "=" . $value . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }


    /**
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return string
     */
    protected function createNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取当前页面url
     * @return string
     */
    public function getCurrentUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        return $url;
    }

}