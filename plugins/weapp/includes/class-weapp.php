<?php
// wp_cache_add_global_groups(array('weapp'));

class WEAPP{
	private $appid;
	private $secret;
	private $component_blog_id;

	public function __construct($appid, $secret='', $component_blog_id=0){
		$this->appid		= $appid;
		$this->secret		= $secret;
		$this->component_blog_id	= $component_blog_id;
	}

	public function get_component_blog_id(){
		return $this->component_blog_id;
	}

	//获取物流公司信息
	public function mall_get_logistics()
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/express/business/delivery/getall';
		
		return $this->http_request($url, array('method'=>'GET'));
	}

	//生成运单
	public function mall_add_order($args)
	{
		$url    = 'https://api.weixin.qq.com/cgi-bin/express/business/order/add';

		return $this->http_request($url, [
			'method'	=> 'POST',
			'body'		=> $args
		]);
	}

	//取消运单
	public function mall_cancel_order($args)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/express/business/order/cancel';

		return $this->http_request($url, [
			'method'    => 'POST',
			'body'      => $args,
		]);
	}

	//获取运单数据
	public function mall_get_order($args)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/express/business/order/get';

		return $this->http_request($url, [
			'method'    => 'POST',
			'body'      => $args,
		]);
	}

	//获取运单轨迹
	public function mall_get_path($args)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/express/business/path/get';

		return $this->http_request($url, [
			'method'    => 'POST',
			'body'      => $args,
		]);
	}

	public function jscode2session($code){
		$session_data	= wp_cache_get($this->appid.$code, 'weapp_code');

		if($session_data === false){

			if($this->component_blog_id){
				$session_data	= apply_filters('weapp_session_data', null, $code, $this->appid, $this->component_blog_id);
			}else{
				$session_data	= $this->http_request('https://api.weixin.qq.com/sns/jscode2session?appid='.$this->appid.'&secret='.$this->secret.'&js_code='.$code.'&grant_type=authorization_code', array('need_access_token'=>false));
			}
		}

		if(is_wp_error($session_data)){
			return $session_data;
		}

		wp_cache_set($this->appid.$code, $session_data, 'weapp_code', MINUTE_IN_SECONDS);
		wp_cache_set($this->appid.$session_data['openid'], $session_data['session_key'], 'weapp_session_key', DAY_IN_SECONDS);

		return $session_data;
	}

	public function get_openid_by_jscode($code){
		$session_data 	= $this->jscode2session($code);
		if(is_wp_error($session_data)){
			wpjam_send_json($session_data);
		}

		return $session_data['openid'];
	}

	public function get_session_key($code){
		$session_data	= $this->jscode2session($code);
		
		if(is_wp_error($session_data)){
			return $session_data;
		}

		return $session_data['session_key'];
	}

	public function get_session_key_by_openid($openid){

		$session_key	= wp_cache_get($this->appid.$openid, 'weapp_session_key');

		if($session_key === false){
			return new WP_Error('empty_session_key', '服务器缓存的 Session Key 失效！');
		}

		return $session_key;
	}

	public function get_version(){
		static $version;

		if(isset($version)) return $version;
		
		$refer		= ($_SERVER['HTTP_REFERER'])??'';
		$version	= 0;
		if($refer && preg_match('|https://servicewechat.com/.*?/(.*?)/|i', $refer, $matches)){
			$version	= $matches[1];
		}

		return $version;
	}

	public function decrypt_user($session_data, $iv, $encrypted_data){
		$session_key	= is_array($session_data)?$session_data['session_key']:$session_data;
		$user_info		= $this->decrypt($session_key, $iv, $encrypted_data);

		if(is_wp_error($user_info)){
			return $user_info;
		}

		if(is_array($session_data)){
			if($session_data['openid'] != $user_info['openId']){
				return new WP_Error('illegal_openid', '两次获取的 openid 不一致！');
			}
		}

		return $user_info;
	}

	public function decrypt_gid($session_key, $iv, $encrypted_data){
		$group_info	= $this->decrypt($session_key, $iv, $encrypted_data);

		if(is_wp_error($group_info)){
			return $group_info;
		}

		return $group_info['openGId'];
	}

	public function decrypt_run($session_key, $iv, $encrypted_data){
		$step_info	= $this->decrypt($session_key, $iv, $encrypted_data);

		if(is_wp_error($step_info)){
			return $step_info;
		}

		return $step_info['stepInfoList'];
	}

	public function decrypt_phone($session_key, $iv, $encrypted_data){
		$phone_info	= $this->decrypt($session_key, $iv, $encrypted_data);

		if(is_wp_error($phone_info)){
			return $phone_info;
		}

		return $phone_info;
	}

	public function decrypt($aeskey, $iv, $encrypted_data){
		

		if (strlen($aeskey) != 24) {
			return new WP_Error('illegal_aes_key', '非法 AES Key');
		}

		if (strlen($iv) != 24) {
			return new WP_Error('illegal_iv', '非法 iv');
		}

		$aeskey			= base64_decode($aeskey);
		$iv				= base64_decode($iv);
		$encrypted_data	= base64_decode($encrypted_data);

		try {
			$decrypted_data	= openssl_decrypt($encrypted_data, "AES-128-CBC", $aeskey, 1, $iv);

			// $decrypted_data	= $wpjam_openssl_crypt->decrypt(base64_decode($encrypted_data));
		}catch(Exception $e) {
			return new WP_Error('aes_decrypt_failed', 'AES 解密失败');
		}

		if(!$decrypted_data){
			return new WP_Error('aes_decrypt_failed', 'AES 解密失败');
		}

		// if(get_current_blog_id() == 360){
		// 	wpjam_send_json(compact('decrypted_data'));
		// }

		//去除补位字符
		$pad	= ord(substr($decrypted_data, -1));
		$pad	= ($pad >= 1 && $pad <= 32)?$pad:0;
		$decrypted_data	= substr($decrypted_data, 0, (strlen($decrypted_data) - $pad));

		if (strlen($decrypted_data) < 16)	{
			return new WP_Error('illegal_decrypted_length', '非法解密数据长度');
		}

		$decrypted_data	= wpjam_json_decode($decrypted_data);

		if(is_wp_error($decrypted_data)){
			return $decrypted_data;
		}

		if ($decrypted_data['watermark']['appid'] != $this->appid) {
			return new WP_Error('illegal_watermark_appid', '和数据水印中的 appid 不匹配！');
		}

		return $decrypted_data;
	}

	public function msg_sec_check($content){
		return $this->http_request('https://api.weixin.qq.com/wxa/msg_sec_check',[
			'method'	=> 'POST',
			'body'		=> compact('content')
		]);
	}

	public function img_sec_check($media){
		return $this->http_request('https://api.weixin.qq.com/wxa/img_sec_check',[
			'method'	=>'file',
			'body'		=> array('media'=>new CURLFile($media,'',basename($media)))
		]);
	}

	public function send_custom_message($data){
		$data	= wp_parse_args($data, array(
			'touser'		=> '',
			'msgtype'		=> 'text'
		));

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/message/custom/send', array(
			'method'	=> 'POST',
			'body'		=> $data,
		));
	}

	public function send_template_message($data){
		$data	= wp_parse_args($data, array(
			'touser'			=> '',
			'template_id'		=> '',
			'page'				=> '',
			'form_id'			=> '',
			'data'				=> array(),
			'emphasis_keyword'	=> '',
		));

		$url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send';

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> $data,
		));
	}

	public function list_template_library($count=20, $offset=0){
		$url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/library/list';

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('offset', 'count'),
		));
	}

	public function get_template_library($id){
		$url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/library/get';

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('id'),
		));
	}

	public function add_template($id, $keyword_id_list){
		$url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/add';

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('id', 'keyword_id_list'),
		));
	}

	public function del_template($template_id){
		$url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/del';

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('template_id'),
		));
	}

	public function list_templates(){
		$templates	= [];

		$count		= 20;
		$offset		= 0;

		do{
			$result = $this->list_template($count, $offset);
			if(is_wp_error($result)) {
				return $result;
			}
			
			$templates 	= array_merge($templates, $result['list']);

			$offset	+= $count;
		} while(count($result['list']) == $count);
		
		return $templates;
	}

	public function list_template($count=20, $offset=0){
		$url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/list';

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('offset', 'count'),
		));
	}

	public function mall_add_shoping_list($user_open_id, $sku_product_list){
		$url = 'https://api.weixin.qq.com/mall/addshoppinglist';
		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('user_open_id', 'sku_product_list')
		));
	}

	public function mall_delete_shoping_list($user_open_id, $sku_product_list){
		$url = 'https://api.weixin.qq.com/mall/deleteshoppinglist';

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('user_open_id', 'sku_product_list')
		));
	}

	public function mall_delete_all_shoping_list($user_open_id){
		$url = 'https://api.weixin.qq.com/mall/deletebizallshoppinglist';

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('user_open_id')
		));
	}

	public function mall_add_orders($order_list){
		$url	= 'https://api.weixin.qq.com/mall/importorder?action=add-order&is_history=0';
		
		$args	= [
			'method'	=> 'POST',
			'body'		=> compact('order_list')
		];

		$err_args	= [
			'errcode'	=>'errcode',
			'errmsg'	=>'errmsg',
			'detail'	=>'fail_order_list'
		];

		return $this->http_request($url, $args, $err_args);
	}

	public function mall_update_orders($order_list){
		$url	= 'https://api.weixin.qq.com/mall/importorder?action=update-order&is_history=0';

		$args	= [
			'method'	=> 'POST',
			'body'		=> compact('order_list')
		];

		$err_args	= [
			'errcode'	=>'errcode',
			'errmsg'	=>'errmsg',
			'detail'	=>'fail_order_list'
		];
		
		return $this->http_request($url, $args, $err_args);
	}

	public function mall_delete_order($user_open_id, $order_id){
		$url = 'https://api.weixin.qq.com/mall/deleteorder';

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('user_open_id', 'order_id')
		));
	}

	public function mall_import_product($product_list){
		$url = 'https://api.weixin.qq.com/mall/importproduct';

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('product_list')
		));
	}

	public function mall_query_product($key_list){
		$url = 'https://api.weixin.qq.com/mall/queryproduct';

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('key_list')
		));
	}

	public function upload_media($media, $type='image'){
		$url	= 'https://api.weixin.qq.com/cgi-bin/media/upload?type='.$type;
		return $this->http_request($url, array(
			'method'	=>'file',
			'body'		=> array('media'=>new CURLFile($media,'',basename($media)))
		));
	}

	public function get_access_token($force=false){
		if($this->component_blog_id){
			$access_token	= apply_filters('weapp_access_token', null, $this->appid, $this->component_blog_id);
		}else{
			$access_token = wp_cache_get($this->appid, 'weapp_access_token');

			if ($access_token === false || $force) {
				
				$url		= "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->secret}";
				$response	= $this->http_request($url, array('need_access_token'=>false));

				if(is_wp_error($response)){
					return $response;
				}else{
					$access_token	= $response['access_token'];
					$expires_in		= $response['expires_in'];
					wp_cache_set($this->appid, $access_token, 'weapp_access_token', $expires_in-600);
				}
			}
		}

		return $access_token;
	}

	public function create_qrcode($data, $type='wxacode'){
		if($type == 'wxacode'){
			return $this->get_wxacode($data);
		}elseif($type == 'unlimit'){
			return  $this->get_unlimit_wxacode($data);
		}elseif($type == 'qrcode'){
			return  $this->create_wxa_qrcode($data);
		}
	}

	public function get_wxacode($data){
		$data		= wp_parse_args($data, array(
			'path'			=> '',
			'width'			=> 430,
			'auto_color'	=> true
		));

		$media_id	= md5(maybe_serialize($data));
		$media_file	= $this->get_media_file($media_id, 'wxacode');

		if(isset($data['time'])){
			unset($data['time']);
		}

		if(!file_exists($media_file)){
			$url	= "https://api.weixin.qq.com/wxa/getwxacode";
			$result	= $this->stream_file($url, $data, $media_file);

			if(is_wp_error($result)){
				if(file_exists($media_file)){
					unlink($media_file);	
				}
				
				return $result;
			}
		}

		return $media_id;
	}

	public function get_unlimit_wxacode($data){
		$data		= wp_parse_args($data, array(
			'scene'			=> '',
			'width'			=> 430,
			'auto_color'	=> true,
		));

		$media_id	= md5(maybe_serialize($data));
		$media_file	= $this->get_media_file($media_id, 'unlimit');

		// if(!file_exists($media_file)){
			$url	= "https://api.weixin.qq.com/wxa/getwxacodeunlimit";
			$result	= $this->stream_file($url, $data, $media_file);

			if(is_wp_error($result)){
				unlink($media_file);
				return $result;
			}
		// }

		return $media_id;
	}

	public function create_wxa_qrcode($data){
		$data		= wp_parse_args($data, array(
			'path'			=> '',
			'width'			=> 430
		));

		$media_id	= md5(maybe_serialize($data));
		$media_file	= $this->get_media_file($media_id, 'qrcode');

		if(!file_exists($media_file)){
			$url	= "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode";
			$result	= $this->stream_file($url, $data, $media_file);

			if(is_wp_error($result)){
				unlink($media_file);
				return $result;
			}
		}

		return $media_id;
	}

	Public function stream_file($url, $data, $file){
		return $this->http_request($url, array(
			'method'			=> 'POST',
			'body'				=> $data,
			'stream'			=> true,
			'filename'			=> $file,
			'need_json_decode'	=> false
		));
	}

	private function http_request($url, $args=[], $err_args=[]){
		$args = wp_parse_args( $args, array(
			'need_access_token'	=> true,
			'need_json_encode'	=> true,
			'timeout'			=> 5,
		) );
		if($args['need_access_token']){
			$access_token = $this->get_access_token();
			if(is_wp_error($access_token)){
				return $access_token;
			}elseif(empty($access_token)){
				return;
			}else{
				$url = add_query_arg(array('access_token'=>$access_token), $url);
				$url = str_replace('%40', '@', $url);  
			}
		}
		unset($args['need_access_token']);

		$response = WPJAM_API::http_request($url, $args, $err_args);
		if(is_wp_error($response)){
			$errcode	= $response->get_error_code();

			if($errcode == '40001' || $errcode == '40014' || $errcode == '42001'){
				// 40001 获取access_token时AppSecret错误，或者access_token无效。请开发者认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口
				// 40014 不合法的access_token，请开发者认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口
				// 42001 access_token超时，请检查access_token的有效期，请参考基础支持-获取access_token中，对access_token的详细机制说明
				wp_cache_delete($this->appid, 'weapp_access_token');
			}elseif($errcode == '50002' || $errcode == '9009205' || $errcode = '9009301'){
				// 50002 用户受限，可能是违规后接口被封禁
				// $robot_option = wpjam_get_option('weixin-robot');
				// $robot_option['weixin_type'] = -1;
				// update_option('weixin-robot', $robot_option);
			}

//			trigger_error(var_export($response, true));
		}

		return $response;
	}

	public function get_media_url($media_id, $type=''){
		$media_dir	= substr($media_id, 0, 1).'/'.substr($media_id, 1, 1);

		if($type){
			return WEAPP_MEDIA_URL.'/'.$this->appid.'/'.$type.'/'.$media_dir.'/'.$media_id.'.jpg';
		}else{
			return WEAPP_MEDIA_URL.'/'.$this->appid.'/'.$media_dir.'/'.$media_id.'.jpg';
		}
	}

	public function get_media_file($media_id, $type=''){
		if(!is_dir(WEAPP_MEDIA_DIR.'/'.$this->appid)){
			mkdir(WEAPP_MEDIA_DIR.'/'.$this->appid, 0777, true);
		}

		$media_dir	= substr($media_id, 0, 1).'/'.substr($media_id, 1, 1);

		if($type){
			if(!is_dir(WEAPP_MEDIA_DIR.'/'.$this->appid.'/'.$type.'/'.$media_dir)){
				mkdir(WEAPP_MEDIA_DIR.'/'.$this->appid.'/'.$type.'/'.$media_dir, 0777, true);
			}
			return WEAPP_MEDIA_DIR.'/'.$this->appid.'/'.$type.'/'.$media_dir.'/'.$media_id.'.jpg';
		}else{
			return WEAPP_MEDIA_DIR.'/'.$this->appid.'/'.$media_dir.'/'.$media_id.'.jpg';
		}
	}
}
