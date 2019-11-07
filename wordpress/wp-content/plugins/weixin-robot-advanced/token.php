<?php
//提交认证的时候如果无法通过，可以使用该文件来验证通过。
$use_plugin = 1;

if($use_plugin){
  include('../../../wp-load.php');

  //file_put_contents(WP_CONTENT_DIR.'/uploads/weixin.log',var_export($_SERVER,true));
  include WEIXIN_ROBOT_PLUGIN_DIR.'template/reply.php';
  exit;
}else{
  define ( "TOKEN", "weixin" );

  class wechatCallbackapiTest{
    public function valid()
    {
        $echoStr = $_GET["echostr"];        //随机字符串
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    private function checkSignature() {
        $signature = $_GET ["signature"];
        $timestamp = $_GET ["timestamp"];
        $nonce = $_GET ["nonce"];
        $token = TOKEN;
        $tmpArr = array (
              $token,
              $timestamp,
              $nonce
        );
        sort ( $tmpArr );
        $tmpStr = implode ( $tmpArr );
        $tmpStr = sha1 ( $tmpStr );
        
        if ($tmpStr == $signature) {
          return true;
        } else {
          return false;
        }
    }   
  }

  $wechatObj = new wechatCallbackapiTest();
  $wechatObj->valid();
}


