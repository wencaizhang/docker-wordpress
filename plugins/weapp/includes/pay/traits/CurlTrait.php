<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/3/5
 * Time: 15:35
 */


trait CurlTrait
{
    protected function httpGet($url, $params = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $url = $this->buildURL($url, $params);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    /**
     * 以post方式提交data到url
     *
     * @param $url
     * @param $data
     * @param int $timeout
     *
     * @return bool|mixed
     */
    protected function httpPost($url, $data, $timeout = 30)
    {
        $filedString = $data;
        if (is_array($data)) {
            $filedString = http_build_query($data);
        }


        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $filedString);
        //运行curl

        $errno = curl_errno($ch);
        if ($errno) {
            $errstr = curl_strerror($errno);
            trigger_error('CURL error: ' . $errno . '  ' . $errstr . "\r\n" . $url);
            $data = false;
        } else {
            $data = curl_exec($ch);
        }

        curl_close($ch);

        return $data;

        //返回结果
//        if ($data) {
//            curl_close($ch);
//
//            return $data;
//        } else {
//            $error = curl_errno($ch);
//            echo "curl出错，错误码:$error" . "<br>";
//            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
//            curl_close($ch);
//
//            return false;
//        }
    }

    protected function httpGetJson($url, $params = [])
    {
        $result = $this->httpGet($url, $params);
//        wpjam_print_r($result);
        if (!$result) {
            return false;
        }

        return json_decode($result, true);
    }

    protected function httpPostJson($url, $data, $timeout = 30)
    {
        $result = $this->httpPost($url, $data, $timeout);
        if ($result === false) {
            return false;
        }

        return json_decode($result, true);
    }


    /**
     * 使用证书，以post方式提交data到对应的接口url
     *
     * @param $url
     * @param $data
     * @param $sslCertPath
     * @param $sslKeyPath
     * @param int $timeout
     *
     * @return bool|mixed
     */
    protected function httpPostSSL($url, $data, $sslCertPath, $sslKeyPath, $timeout = 30)
    {
        $filedString = $data;
        if (is_array($data)) {
            $filedString = http_build_query($data);
        }

        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, $sslCertPath);
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, $sslKeyPath);
//        curl_setopt($ch, CURLOPT_CAINFO, $this->caFilePath);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $filedString);
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);

            return $data;
        } else {
            $error = curl_errno($ch);
            trigger_error("curl出错: `$url`，错误码: $error");
//             echo "curl出错，错误码:$error" . "<br>";
//             echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);

            return false;
        }
    }

    public function buildURL($url, $data = [])
    {
        if (empty($data)) {
            return $url;
        }

        $coms  = parse_url($url);
        $query = "";
        if (isset($coms["query"]) && strlen($coms["query"])) {
            $query .= $coms["query"] . "&" . http_build_query($data);
        } else {
            $query .= http_build_query($data);
        }

        $ret = $coms["scheme"] . "://" . $coms["host"];
        if (isset($coms["port"])) {
            $ret .= ":" . $coms["port"];
        }

        if (isset($coms["path"])) {
            $ret .= $coms["path"];
        }

        if ($query) {
            $ret .= "?" . $query;
        }

        if (isset($coms["fragment"])) {
            $ret .= "#" . $coms["fragment"];
        }

        return $ret;
    }

}