<?php
abstract class WPJAM_API{
	public static function method_allow($method, $send=true){
		if ($_SERVER['REQUEST_METHOD'] != $method) {
			$wp_error = new WP_Error('method_not_allow', '接口不支持 '.$_SERVER['REQUEST_METHOD'].' 方法，请使用 '.$method.' 方法！');
			if($send){
				self::send_json($wp_error);
			}else{
				return $wp_error;
			}
		}else{
			return true;		
		}
	}

	public static function get_post_input(){
		static $post_input;
		if(!isset($post_input)) {
			$post_input	= file_get_contents('php://input');
			// trigger_error(var_export($post_input,true));
			if(is_string($post_input)){
				$post_input	= @self::json_decode($post_input);
			}
		}

		return $post_input;
	}

	public static function get_parameter($parameter, $args=array()){
		extract(wp_parse_args($args, array(
			'method'	=> 'GET', 		
			'required'	=> false,
			'length'	=> false,
			'name'		=> '',
			'default'	=> NULL,
			'type'		=> NULL,
			'send'		=> true
		)));

		$value = NULL;

		if ($method == 'POST') {
			if(empty($_POST)){	// 支持 json 实体 POST
				$post_input	= self::get_post_input();

				if(is_array($post_input)){
					$value = $post_input[$parameter] ?? $default;
				}
			}else{
				$value = $_POST[$parameter]??$default;
			}
		} elseif ($method == 'GET') {
			$value = $_GET[$parameter] ?? $default;
		} else {
			$value = $_REQUEST[$parameter] ?? '';
			if(empty($value) && empty($_POST)){	// 支持 json 实体 POST

				$post_input	= self::get_post_input();
				
				if(is_array($post_input)){
					$value = $post_input[$parameter] ?? $default;
				}
			}

			$value	= $value ?: $default;
		}

		if($required && is_null($value)) {
			$wp_error = new WP_Error('missing_parameter', '缺少 '.$method.' 参数：'.$parameter);
			if($send){
				self::send_json($wp_error);
			}else{
				return $wp_error;
			}
		}

		if($type == 'int' && $value && !is_numeric($value)) {
			return intval($value);
			// $wp_error = new WP_Error('illegal_type', $parameter.' 参数类型错误！');
			// if($send){
			// 	self::send_json($wp_error);
			// }else{
			// 	return $wp_error;
			// }
		}

		if($length && is_int($length) && (mb_strlen($value) < $length)){
			$wp_error = new WP_Error('short_parameter', $name.'长度不能少于'.$length.'！');
			if($send){
				self::send_json($wp_error);
			}else{
				return $wp_error;
			}
		}
		

		return $value;
	}

	public static function get_parameters($parameters, $args=array()){
		if(!is_array($parameters)){
			$parameters = wp_parse_slug_list($parameters);
		}

		$result = array();
		foreach ($parameters as $parameter) {
			$value = self::get_parameter($parameter, $args);
			if(is_wp_error($value)){
				return $value;
			}
			$result[$parameter] = $value;
		}

		return $result;
	}

	public static function get_qq_vid($id_or_url){
		if(filter_var($id_or_url, FILTER_VALIDATE_URL)){ 
			if(preg_match('#https://v.qq.com/x/page/(.*?).html#i',$id_or_url, $matches)){
				return $matches[1];
			}elseif(preg_match('#https://v.qq.com/x/cover/.*/(.*?).html#i',$id_or_url, $matches)){
				return $matches[1];
			}else{
				return '';
			}
		}else{
			return $id_or_url;
		}
	}

	public static function get_video_mp4($id_or_url){
		if(filter_var($id_or_url, FILTER_VALIDATE_URL)){ 
			if(preg_match('#http://www.miaopai.com/show/(.*?).htm#i',$id_or_url, $matches)){
				return 'http://gslb.miaopai.com/stream/'.esc_attr($matches[1]).'.mp4';
			}elseif(preg_match('#https://v.qq.com/x/page/(.*?).html#i',$id_or_url, $matches)){
				return self::get_qqv_mp4($matches[1]);
			}elseif(preg_match('#https://v.qq.com/x/cover/.*/(.*?).html#i',$id_or_url, $matches)){
				return self::get_qqv_mp4($matches[1]);
			}else{
				return str_replace(['%3A','%2F'], [':','/'], urlencode($id_or_url));
			}
		}else{
			return self::get_qqv_mp4($id_or_url);
		}
	}

	public static function get_qqv_mp4($vid){
		if(strlen($vid) > 20){
			return new WP_Error('invalid_qqv_vid', '非法的腾讯视频 ID');
		}

		$mp4 = wp_cache_get($vid, 'qqv_mp4');
		if($mp4 === false){
			$response	= wpjam_remote_request('http://vv.video.qq.com/getinfo?otype=json&platform=11001&vid='.$vid, array(
				'timeout'			=>4,
				'need_json_decode'	=>false
			));

			if(is_wp_error($response)){
				return $response;
			}

			$response	= trim(substr($response, strpos($response, '{')),';');
			$response	= wpjam_json_decode($response);

			if(is_wp_error($response)){
				return $response;
			}

			if(empty($response['vl'])){
				return new WP_Error('illegal_qqv', '该腾讯视频不存在或者为收费视频！');
			}

			$u		= $response['vl']['vi'][0];
			$p0		= $u['ul']['ui'][0]['url'];
			$p1		= $u['fn'];
			$p2		= $u['fvkey'];

			$mp4	= $p0.$p1.'?vkey='.$p2;

			wp_cache_set($vid, $mp4, 'qqv_mp4', HOUR_IN_SECONDS*6);
		}

		return $mp4;
	}

	public static function parse_shortcode_attr($str,  $tagnames=null){
		$pattern = get_shortcode_regex(array($tagnames));

		if(preg_match("/$pattern/", $str, $m)){
			return shortcode_parse_atts( $m[3] );
		}else{
			return array();
		}		
	}

	public static function human_time_diff($from,  $to=0) {
		$to		= ($to)?:time();
		$day	= date('Y-m-d',$from);
		$today	= date('Y-m-d');
		
		$secs	= $to - $from;	//距离的秒数
		$days	= $secs / DAY_IN_SECONDS;

		$from += get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ;

		if($secs > 0){
			if((date('Y')-date('Y',$from))>0 && $days>3){//跨年且超过3天
				return date('Y-m-d',$from);
			}else{

				if($days<1){//今天
					if($secs<60){
						return $secs.'秒前';
					}elseif($secs<3600){
						return floor($secs/60)."分钟前";
					}else {
						return floor($secs/3600)."小时前";
					}
				}else if($days<2){	//昨天
					$hour=date('g',$from);
					return "昨天".$hour.'点';
				}elseif($days<3){	//前天
					$hour=date('g',$from);
					return "前天".$hour.'点';
				}else{	//三天前
					return date('n月j号',$from);
				}
			}
		}else{
			if((date('Y')-date('Y',$from))<0 && $days<-3){//跨年且超过3天
				return date('Y-m-d',$from);
			}else{

				if($days>-1){//今天
					if($secs>-60){
						return absint($secs).'秒后';
					}elseif($secs>-3600){
						return floor(absint($secs)/60)."分钟前";
					}else {
						return floor(absint($secs)/3600)."小时前";
					}
				}else if($days>-2){	//昨天
					$hour=date('g',$from);
					return "明天".$hour.'点';
				}elseif($days>-3){	//前天
					$hour=date('g',$from);
					return "后天".$hour.'点';
				}else{	//三天前
					return date('n月j号',$from);
				}
			}
		}
	}
	
	public static function get_current_page_url(){
		$ssl		= (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true:false;
		$sp			= strtolower($_SERVER['SERVER_PROTOCOL']);
		$protocol	= substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
		$port		= $_SERVER['SERVER_PORT'];
		$port		= ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
		$host		= isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		return $protocol . '://' . $host . $port . $_SERVER['REQUEST_URI'];
	}

	public static function json_encode( $data, $options = JSON_UNESCAPED_UNICODE, $depth = 512){
		return wp_json_encode( $data, $options, $depth );
	}

	public static function send_json($response=[], $status_code=null){
		if(is_wp_error($response)){
			$response = array('errcode'=>$response->get_error_code(), 'errmsg'=>$response->get_error_message());
		}else{
			$response['errcode']	= $response['errcode'] ?? 0;
		}

		$result	= self::json_encode($response);

		if(isset($_REQUEST['callback']) || isset($_GET['_jsonp'])){
			if(!headers_sent() && !isset($_GET['debug'])){
				@header( 'Content-Type: application/javascript; charset=' . get_option( 'blog_charset' ) );	

				if ( null !== $status_code ) {
					status_header( $status_code );
				}
			}

			if(isset($_GET['_jsonp'])){
				$jsonp_callback	= $_GET['_jsonp'];
			}else{
				$jsonp_callback	= $_REQUEST['callback'];
			}
			
			echo '/**/' . $jsonp_callback . '(' . $result . ')';

		}else{
			
			if(!headers_sent() && !isset($_GET['debug'])){
				
				@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

				if ( null !== $status_code ) {
					status_header( $status_code );
				}
			}

			echo $result;
		}

		exit;
	}

	public static function json_decode($json, $assoc=true, $depth=512, $options=0){
		$json	= self::strip_control_characters($json);

		if(empty($json)){
			return new WP_Error('empty_json', 'JSON 内容不能为空！');
		}

		$result	= json_decode($json, $assoc, $depth, $options);

		if(is_null($result)){
			require_once( ABSPATH . WPINC . '/class-json.php' );

			$wp_json	= new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
			$result		= $wp_json->decode($json); 

			if(is_null($result)){
				if(isset($_GET['debug'])){
					print_r(json_last_error());
					print_r(json_last_error_msg());
				}
				
				trigger_error('json_decode_error '. json_last_error_msg()."\n".var_export($json,true));
				return new WP_Error('json_decode_error', json_last_error_msg());
			}else{
				if($assoc){
					return (array)$result;
				}else{
					return (object)$result;
				}
			}
		}else{
			return $result;
		}
	}

	// 移除除了 line feeds 和 carriage returns 所有控制字符
	public static function strip_control_characters($text){
		return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F]/u', '', $text);	
		// return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $str);
	}

	// 去掉非 utf8mb4 字符
	public static function strip_invalid_text($str){
		$regex = '/
		(
			(?: [\x00-\x7F]                  # single-byte sequences   0xxxxxxx
			|   [\xC2-\xDF][\x80-\xBF]       # double-byte sequences   110xxxxx 10xxxxxx
			|   \xE0[\xA0-\xBF][\x80-\xBF]   # triple-byte sequences   1110xxxx 10xxxxxx * 2
			|   [\xE1-\xEC][\x80-\xBF]{2}
			|   \xED[\x80-\x9F][\x80-\xBF]
			|   [\xEE-\xEF][\x80-\xBF]{2}
			|    \xF0[\x90-\xBF][\x80-\xBF]{2} # four-byte sequences   11110xxx 10xxxxxx * 3
			|    [\xF1-\xF3][\x80-\xBF]{3}
			|    \xF4[\x80-\x8F][\x80-\xBF]{2}
			){1,50}                          # ...one or more times
		)
		| .                                  # anything else
		/x';

		return preg_replace($regex, '$1', $str);
	}

	public static function strip_4_byte_chars($chars){
		return preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $chars);
		// return preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $chars);
	}

	//获取纯文本
	public static function get_plain_text($text){

		$text = wp_strip_all_tags($text);
		
		$text = str_replace('"', '', $text); 
		$text = str_replace('\'', '', $text);	
		// replace newlines on mac / windows?
		$text = str_replace("\r\n", ' ', $text);
		// maybe linux uses this alone
		$text = str_replace("\n", ' ', $text);
		$text = str_replace("  ", ' ', $text);

		return trim($text);
	}

	// 获取第一段
	public static function get_first_p($text){
		if($text){
			$text = explode("\n", trim(strip_tags($text))); 
			$text = trim($text['0']); 
		}
		return $text;
	}

	public static function mb_strimwidth($text, $start=0, $length=40){
		return mb_strimwidth(wp_strip_all_tags($text), $start, $length, '...','utf-8');
	}

	public static function blacklist_check($str){
		$moderation_keys	= trim(get_option('moderation_keys'));
		$blacklist_keys		= trim(get_option('blacklist_keys'));

		$words = explode("\n", $moderation_keys ."\n".$blacklist_keys);

		foreach ((array)$words as $word){
			$word = trim($word);

			// Skip empty lines
			if ( empty($word) ) continue;

			// Do some escaping magic so that '#' chars in the
			// spam words don't break things:
			$word	= preg_quote($word, '#');
			if ( preg_match("#$word#i", $str) ) return true;
		}

		return false;
	}

	public static function http_request($url, $args=array(), $err_args=array()){
		$args = wp_parse_args( $args, array(
			'timeout'			=> 5,
			'method'			=> '',
			'body'				=> array(),
			'sslverify'			=> false,
			'blocking'			=> true,	// 如果不需要立刻知道结果，可以设置为 false
			'stream'			=> false,	// 如果是保存远程的文件，这里需要设置为 true
			'filename'			=> null,	// 设置保存下来文件的路径和名字
			'need_json_decode'	=> true,
			'need_json_encode'	=> false,
			// 'headers'		=> array('Accept-Encoding'=>'gzip;'),	//使用压缩传输数据
			// 'headers'		=> array('Accept-Encoding'=>''),
			// 'compress'		=> false,
			'decompress'		=> true,
		));

		if(isset($_GET['debug'])){
			print_r($args);	
		}

		$need_json_decode	= $args['need_json_decode'];
		$need_json_encode	= $args['need_json_encode'];

		$method				= ($args['method'])?strtoupper($args['method']):($args['body']?'POST':'GET');

		unset($args['need_json_decode']);
		unset($args['need_json_encode']);
		unset($args['method']);

		if($method == 'GET'){
			$response = wp_remote_get($url, $args);
		}elseif($method == 'POST'){
			if($need_json_encode && is_array($args['body'])){
				$args['body']	= self::json_encode($args['body']);
			}
			$response = wp_remote_post($url, $args);
		}elseif($method == 'FILE'){	// 上传文件
			$args['method'] = ($args['body'])?'POST':'GET';
			$args['sslcertificates']	= isset($args['sslcertificates'])?$args['sslcertificates']: ABSPATH.WPINC.'/certificates/ca-bundle.crt';
			$args['user-agent']			= isset($args['user-agent'])?$args['user-agent']:'WordPress';
			$wp_http_curl	= new WP_Http_Curl();
			$response		= $wp_http_curl->request($url, $args);
		}elseif($method == 'HEAD'){
			if($need_json_encode && is_array($args['body'])){
				$args['body']	= self::json_encode($args['body']);
			}

			$response = wp_remote_head($url, $args);
		}else{
			if($need_json_encode && is_array($args['body'])){
				$args['body']	= self::json_encode($args['body']);
			}

			$response = wp_remote_request($url, $args);
		}

		if(is_wp_error($response)){
			trigger_error($url."\n".$response->get_error_code().' : '.$response->get_error_message()."\n".var_export($args['body'],true));
			return $response;
		}

		$headers	= $response['headers'];
		$response	= $response['body'];

		if($need_json_decode || isset($headers['content-type']) && strpos($headers['content-type'], '/json')){
			if($args['stream']){
				$response	= file_get_contents($args['filename']);
			}

			$response	= self::json_decode($response);

			if(get_current_blog_id() == 339){
				// print_r($response);
			}

			if(is_wp_error($response)){
				return $response;
			}
		}
		
		extract(wp_parse_args($err_args,  array(
			'errcode'	=>'errcode',
			'errmsg'	=>'errmsg',
			'detail'	=>'detail'
		)));

		if(isset($response[$errcode]) && $response[$errcode]){
			$errcode	= $response[$errcode];
			$errmsg		= isset($response[$errmsg])?$response[$errmsg]:'';

			if(isset($response[$detail])){
				$detail	= $response[$detail];

				trigger_error($url."\n".$errcode.' : '.$errmsg."\n".var_export($detail,true)."\n".var_export($args['body'],true));
				return new WP_Error($errcode, $errmsg, $detail);
			}else{

				trigger_error($url."\n".$errcode.' : '.$errmsg."\n".var_export($args['body'],true));
				return new WP_Error($errcode, $errmsg);
			}	
		}

		if(isset($_GET['debug'])){
			echo $url;
			print_r($response);
		}

		return $response;
	}

	static $user_agent;
	static $referer;
	static $is_macintosh;
	static $is_iphone;
	static $is_ipod;
	static $is_ipad;
	static $is_android;
	static $is_weapp;
	static $is_weixin;

	public static function get_ip(){
		// if (!empty($_SERVER['HTTP_CLIENT_IP'])) { //check ip from share internet
		// 	return $_SERVER['HTTP_CLIENT_IP'];
		// } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //to check ip is pass from proxy
		// 	return $_SERVER['HTTP_X_FORWARDED_FOR'];
		// } else {
			return $_SERVER['REMOTE_ADDR'] ??'';
		// }
		// return '';
	}

	public static function parse_ip($ip=''){
		$ip	= ($ip)?:self::get_ip();

		if($ip == 'unknown'){
			return false;
		}

		$ipdata		= IP::find($ip);

		return array(
			'ip'		=> $ip,
			'country'	=> isset($ipdata['0'])?$ipdata['0']:'',
			'region'	=> isset($ipdata['1'])?$ipdata['1']:'',
			'city'		=> isset($ipdata['2'])?$ipdata['2']:'',
			'isp'		=> '',
		);
	}

	public static function get_user_agent(){
		if (!isset(self::$user_agent)){
			self::$user_agent = ($_SERVER['HTTP_USER_AGENT'])??'';
		}

		return self::$user_agent;
	}

	public static function parse_user_agent($user_agent=''){
		$user_agent	= ($user_agent)?:self::get_user_agent();
		$user_agent	= $user_agent.' ';	// 为了特殊情况好匹配

		$os	= $os_ver = $device	= $build = $weixin_ver = $net_type = '';

		if(self::is_weixin() || self::is_weapp()){
			if(preg_match('/MicroMessenger\/(.*?)\s/', $user_agent, $matches)){
				$weixin_ver = $matches[1];
			}

			if(preg_match('/NetType\/(.*?)\s/', $user_agent, $matches)){
				$net_type = $matches[1];
			}
		}

		if(self::is_ios()){
			$os 	= 'iOS';
			$os_ver	= self::get_ios_version($user_agent);
			if(self::is_ipod()){
				$device	= 'iPod';
			}elseif(self::is_iphone()){
				$device	= 'iPhone';
			}elseif(self::is_ipad()){
				$device	= 'iPad';
			}
		}elseif(self::is_android()){
			$os		= 'Android';

			if(preg_match('/Android ([0-9\.]{1,}?); (.*?) Build\/(.*?)[\)\s;]{1}/i', $user_agent, $matches)){
				if(!empty($matches[1]) && !empty($matches[2])){
					$os_ver	= trim($matches[1]);
					$device	= $matches[2];
					if(strpos($device,';')!==false){
						$device	= substr($device, strpos($device,';')+1, strlen($device)-strpos($device,';'));
					}
					$device	= trim($device);
					$build	= trim($matches[3]);
				}
			}
			
		}elseif(stripos($user_agent, 'Windows NT')){
			$os		= 'Windows';
		}elseif(stripos($user_agent, 'Macintosh')){
			$os		= 'Macintosh';
		}elseif(stripos($user_agent, 'Windows Phone')){
			$os		= 'Windows Phone';
		}elseif(stripos($user_agent, 'BlackBerry') || stripos($user_agent, 'BB10')){
			$os		= 'BlackBerry';
		}elseif(stripos($user_agent, 'Symbian')){
			$os		= 'Symbian';
		}else{
			$os		= 'unknown';
		}

		return compact("os", "os_ver", "device", "build", "weixin_ver", "net_type");
	}

	public static function get_ios_version($user_agent){
		if(preg_match('/OS (.*?) like Mac OS X[\)]{1}/i', $user_agent, $matches)){
			return trim($matches[1]);
		}else{
			return '';
		}
	}

	public static function get_ios_build($user_agent){
		if(preg_match('/Mobile\/(.*?)\s/i', $user_agent, $matches)){
			return trim($matches[1]);
		}else{
			return '';
		}
	}

	public static function get_referer(){
		if (!isset(self::$referer)){
			self::$referer = ($_SERVER['HTTP_REFERER'])??'';
		}

		return self::$referer;
	}

	public static function is_iphone(){
		if (!isset(self::$is_iphone)){
			if(strpos(self::get_user_agent(), 'iPhone') !== false){
				self::$is_iphone = true;
			}else{
				self::$is_iphone = false;
			}
		}

		return self::$is_iphone;
	}

	public static function is_mac(){
		return self::is_macintosh();
	}

	public static function is_macintosh(){
		if (!isset(self::$is_macintosh)){
			if(strpos(self::get_user_agent(), 'Macintosh') !== false){
				self::$is_macintosh = true;
			}else{
				self::$is_macintosh = false;
			}
		}

		return self::$is_macintosh;
	}

	public static function is_ipod(){
		if (!isset(self::$is_ipod)){
			if(strpos(self::get_user_agent(), 'iPod') !== false){
				self::$is_ipod = true;
			}else{
				self::$is_ipod = false;
			}
		}

		return self::$is_ipod;
	}

	public static function is_ipad(){	
		if (!isset(self::$is_ipad)){
			if(strpos(self::get_user_agent(), 'iPad') !== false){
				self::$is_ipad = true;
			}else{
				self::$is_ipad = false;
			}
		}

		return self::$is_ipad;
	}

	public static function is_ios(){
		return self::is_iphone() || self::is_ipod() || self::is_ipad();
	}

	public static function is_android(){
		if (!isset(self::$is_android)){
			if(strpos(self::get_user_agent(), 'Android') !== false){
				self::$is_android = true;
			}else{
				self::$is_android = false;
			}			
		}

		return self::$is_android;
	}

	public static function is_weixin(){ 
		if (!isset(self::$is_weixin)){
			if(strpos(self::get_user_agent(), 'MicroMessenger') !== false){
				if(strpos(self::get_referer(), 'https://servicewechat.com') !== false){
					self::$is_weixin	= false;
					self::$is_weapp		= true;
				}else{
					self::$is_weixin	= true;
					self::$is_weapp		= false;
				}
			}else{
				self::$is_weixin	= false;
			}			
		}

		return self::$is_weixin;
	}

	public static function is_weapp(){
		if (!isset(self::$is_weapp)){
			if(strpos(self::get_user_agent(), 'MicroMessenger') !== false){
				if(strpos(self::get_referer(), 'https://servicewechat.com') !== false){
					self::$is_weapp		= true;
					self::$is_weixin	= false;
				}else{
					self::$is_weapp		= false;
					self::$is_weixin	= true;
				}
			}else{
				self::$is_weapp = false;
			}			
		}

		return self::$is_weapp;
	}
}

class WPJAM_OPENSSL_Crypt{
	private $key;
	private $method = 'aes-128-cbc';
	private $iv = '';
	private $options = OPENSSL_RAW_DATA;

	public function __construct($key, $args=[])
	{
		$this->key		= $key;
		$this->method	= $args['method'] ?? $this->method;
		$this->options	= $args['options'] ?? $this->options;
		$this->iv		= $args['iv'] ?? '';
	}

	public function encrypt($text)
	{
		$encrypted_text = openssl_encrypt($text, $this->method, $this->key, $this->options, $this->iv);

		return trim($encrypted_text);
	}

	public function decrypt($encrypted_text)
	{
		$decrypted_text = openssl_decrypt($encrypted_text, $this->method, $this->key, $this->options, $this->iv);

		return trim($decrypted_text);
	}

	public static function generate_random_string($length)
	{
		
		$alphabet	= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		$max		= strlen($alphabet);

		$token		= '';
		for ($i = 0; $i < $length; $i++) {
			$token .= $alphabet[self::crypto_rand_secure(0, $max - 1)];
		}

		return $token;
	}

	private static function crypto_rand_secure($min, $max)
	{
		$range	= $max - $min;
		if($range < 1){
			return $min;
		} // not so random...

		$log	= ceil(log($range, 2));
		$bytes	= (int)($log / 8) + 1;		// length in bytes
		$bits	= (int)$log + 1;			// length in bits
		$filter	= (int)(1 << $bits) - 1;	// set all lower bits to 1
		
		do {
			$rnd	= hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd	= $rnd & $filter;	// discard irrelevant bits
		}while($rnd > $range);

		return $min + $rnd;
	}
}

wp_cache_add_global_groups(['wpjam_list_cache']);
class WPJAM_ListCache{
	private $key;

	public function __construct($key){
		$this->key	= $key;
	}

	private function get_items(&$cas_token){
		$items	= wp_cache_get_with_cas($this->key, 'wpjam_list_cache', $cas_token);

		if($items === false){
			$items	= [];
			wp_cache_add($this->key, [], 'wpjam_list_cache', DAY_IN_SECONDS);
			$items	= wp_cache_get_with_cas($this->key, 'wpjam_list_cache', $cas_token);
		}

		return $items;
	}

	private function set_items($cas_token, $items){
		return wp_cache_cas($cas_token, $this->key, $items, 'wpjam_list_cache', DAY_IN_SECONDS);
	}

	public function get_all(){
		return wp_cache_get($this->key, 'wpjam_list_cache');
		return $items?:[];
	}

	public function get($k){
		$items = $this->get_all();
		return $items[$k]??false;  
	}

	public function add($item, $k=null){
		$cas_token	= '';
		$retry		= 10;

		do{
			$items	= $this->get_items($cas_token);

			if($k!==null){
				if(isset($items[$k])){
					return false;
				}

				$items[$k]	= $item;
			}else{
				$items[]	= $item;
			}
			
			$result	= $this->set_items($cas_token, $items);

			$retry	 -= 1;
		}while (!$result && $retry > 0);

		return $result;
	}

	public function increment($k, $offset=1){
		$cas_token	= '';
		$retry		= 10;

		do{
			$items		= $this->get_items($cas_token);
			$items[$k]	= $items[$k]??0; 
			$items[$k]	= $items[$k]+$offset;
			
			$result	= $this->set_items($cas_token, $items);

			$retry	 -= 1;
		}while (!$result && $retry > 0);

		return $result;
	}

	public function decrement($k, $offset=1){
		return $this->increment($k, 0-$offset);
	}

	public function set($item, $k){
		$cas_token	= '';
		$retry		= 10;

		do{
			$items		= $this->get_items($cas_token);
			$items[$k]	= $item;
			$result		= $this->set_items($cas_token, $items);
			$retry 		-= 1;
		}while(!$result && $retry > 0);

		return $result;
	}

	public function remove($k){
		$cas_token	= '';
		$retry		= 10;

		do{
			$items	= $this->get_items($cas_token);
			if(!isset($items[$k])){
				return false;
			}
			unset($items[$k]);
			$result	= $this->set_items($cas_token, $items);
			$retry 	-= 1;
		}while(!$result && $retry > 0);

		return $result;
	}

	public function empty(){
		$cas_token		= '';
		$retry	= 10;

		do{
			$items	= $this->get_items($cas_token);
			if($items == []){
				return [];
			}
			$result	= $this->set_items($cas_token, []);
			$retry 	-= 1;
		}while(!$result && $retry > 0);

		if($result){
			return $items;
		}

		return $result;
	}
}

class WPJAM_Cache{
	/* HTML 片段缓存
	Usage:

	if (!WPJAM_Cache::output('unique-key')) {
		functions_that_do_stuff_live();
		these_should_echo();
		WPJAM_Cache::store(3600);
	}
	*/
	public static function output($key) {
		$output	= get_transient($key);
		if(!empty($output)) {
			echo $output;
			return true;
		} else {
			ob_start();
			return false;
		}
	}

	public static function store($key, $cache_time='600') {
		$output = ob_get_flush();
		set_transient($key, $output, $cache_time);
		echo $output;
	}
}

class IP{
	private static $ip = null;
	private static $fp = null;
	private static $offset = null;
	private static $index = null;
	private static $cached = array();

	public static function find($ip){
		if (empty( $ip ) === true) {
			return 'N/A';
		}

		$nip	= gethostbyname($ip);
		$ipdot	= explode('.', $nip);

		if ($ipdot[0] < 0 || $ipdot[0] > 255 || count($ipdot) !== 4) {
			return 'N/A';
		}

		if (isset( self::$cached[$nip] ) === true) {
			return self::$cached[$nip];
		}

		if (self::$fp === null) {
			self::init();
		}

		$nip2 = pack('N', ip2long($nip));

		$tmp_offset	= (int) $ipdot[0] * 4;
		$start		= unpack('Vlen',
			self::$index[$tmp_offset].self::$index[$tmp_offset + 1].self::$index[$tmp_offset + 2].self::$index[$tmp_offset + 3]);

		$index_offset = $index_length = null;
		$max_comp_len = self::$offset['len'] - 1024 - 4;
		for ($start = $start['len'] * 8 + 1024; $start < $max_comp_len; $start += 8) {
			if (self::$index{$start}.self::$index{$start + 1}.self::$index{$start + 2}.self::$index{$start + 3} >= $nip2) {
				$index_offset = unpack('Vlen',
					self::$index{$start + 4}.self::$index{$start + 5}.self::$index{$start + 6}."\x0");
				$index_length = unpack('Clen', self::$index{$start + 7});

				break;
			}
		}

		if ($index_offset === null) {
			return 'N/A';
		}

		fseek(self::$fp, self::$offset['len'] + $index_offset['len'] - 1024);

		self::$cached[$nip] = explode("\t", fread(self::$fp, $index_length['len']));

		return self::$cached[$nip];
	}

	private static function init(){
		if (self::$fp === null) {
			self::$ip = new self();

			self::$fp = fopen(WP_CONTENT_DIR.'/uploads/17monipdb.dat', 'rb');
			if (self::$fp === false) {
				throw new Exception('Invalid 17monipdb.dat file!');
			}

			self::$offset = unpack('Nlen', fread(self::$fp, 4));
			if (self::$offset['len'] < 4) {
				throw new Exception('Invalid 17monipdb.dat file!');
			}

			self::$index = fread(self::$fp, self::$offset['len'] - 4);
		}
	}

	public function __destruct(){
		if (self::$fp !== null) {
			fclose(self::$fp);
		}
	}
}