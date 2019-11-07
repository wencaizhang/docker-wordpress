<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 15/3/5
 * Time: 15:29
 */

trait XmlTrait
{

    /**
     * 将xml转为array
     * @param $xml
     * @return bool|mixed
     */
    protected function xmlToArray($xml)
    {
	    libxml_disable_entity_loader(true);
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($doc === false) {
            // $errors = libxml_get_errors();
            // trigger_error(var_export($errors, true));
            return false;
        }

        $doc = json_decode(json_encode($doc), true);
        if (!$doc) {
            return false;
        }
        //将XML转为array
        $array_data = json_decode(json_encode($doc), true);

        return $array_data;
    }

    /**
     * array转xml
     * @param $arr
     * @return string
     */
    protected function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";

        return $xml;
    }
}
