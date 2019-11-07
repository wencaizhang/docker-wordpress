<?php
include(WEIXIN_ROBOT_PLUGIN_DIR.'includes/class-weixin-reply-setting.php');	// 微信自定义回复设置类
/**
 * 1.第三方回复加密消息给公众平台；
 * 2.第三方收到公众平台发送的消息，验证消息的安全性，并对消息进行解密。
 */
class WEIXIN_Reply {
	private $appid;
	private $token;
	private $crypt;

	private $timestamp;
	private $nonce;

	private $message;

	private $openid;
	private $response	= '';

	public function __construct($appid, $token, $encodingAESKey){
		$this->appid	= $appid;
		$this->token	= $token;

		$key	= base64_decode($encodingAESKey . "=");
		$iv		= substr($key, 0, 16);

		$this->crypt	= new WPJAM_OPENSSL_Crypt($key, ['method'=>'aes-256-cbc', 'iv'=>$iv, 'options'=>OPENSSL_ZERO_PADDING]);
	}

	public function verify_msg($timestamp, $nonce, $signature){
		$this->timestamp	= $timestamp;
		$this->nonce		= $nonce;
		
		$array	= [$this->token, $this->timestamp, $this->nonce];

		sort($array, SORT_STRING);
		
		if(sha1(implode($array)) == $signature){
			return true;
		}else{
			return false;
		}
	}

	public function response_msg($msg_input, $msg_signature, $openid){
		$keyword	= '';

		$this->openid	= $openid;

		if(isset($_GET['debug'])){
			$this->tpl	= '';
			$keyword 	= strtolower(trim($_GET['t']));
		}else{
			$msg_input	= $this->decrypt($msg_input, $msg_signature);
			if(is_wp_error($msg_input)){
				return $msg_input;
			}

			$msg_input	= wpjam_strip_control_characters($msg_input);	// 去掉控制字符

			libxml_disable_entity_loader(true);
			$message	= @simplexml_load_string($msg_input, 'SimpleXMLElement', LIBXML_NOCDATA);

			if(!$message){
				return new WP_Error('empty_weixin_message', '微信消息XML为空');
			}

			// $message	= (array)$message;
			// $message	= map_deep($message,'strval');

			$message	= json_decode(json_encode((array)$message), true);

			$this->message	= $message;

			$this->tpl		= "
			<ToUserName><![CDATA[".$message['FromUserName']."]]></ToUserName>
			<FromUserName><![CDATA[".$message['ToUserName']."]]></FromUserName>
			<CreateTime>".time()."</CreateTime>
			";

			$type = strtolower(trim($message['MsgType']));

			if($type == 'text'){ 				// 文本消息
				$keyword = strtolower(trim($message['Content']));
				if($keyword == '【收到不支持的消息类型，暂无法显示】'){
					$keyword	= '[emotion]';
				}
			}elseif($type == 'event'){			// 事件消息
				$event		= strtolower(trim($message['Event']));
				if($event == 'click'){			// 点击事件
					$keyword	= strtolower(trim($message['EventKey']));
				}elseif($event == 'subscribe' || $event == 'unsubscribe' || $event == 'scan') { 	// 订阅事件，取消订阅事件，已关注用户扫描带参数二维码
					$keyword	= $event;
				}elseif($event == 'location'){		// 高级接口，用户自动提交地理位置事件。
					$keyword	= 'event-location';
				}else{
					$keyword	= '['.$event.']';		// 其他消息，统一处理成关键字为 [$event] ，后面再做处理。
				}
			}elseif($type=='voice'){
				if(isset($message['Recognition']) && trim($message['Recognition'])){	// 如果支持语言识别，识别之后的文字作为关键字
					$keyword = strtolower(trim(str_replace(['！','？','。'], '', $message['Recognition'])));
				}else{
					$keyword = '[voice]';
				}
			}else{		// 其他消息，统一处理成关键字为 [$type] ，后面再做处理。
				$keyword = '['.$type.']';
			}
		}

		if($this->context_reply($keyword) || $this->custom_reply($keyword) || $this->builtin_reply($keyword) || $this->query_reply($keyword)){
			// 
		}

		$response	= $this->get_response();

		if($data = WEIXIN_Message::prepare($message, $response)){
			WEIXIN_Message::insert($data);
		}

		do_action('weixin_message', $message, $this->get_response());		// 数据统计
	}

	private function context_reply($keyword){
		$context_function	= $this->get_context_reply();

		if($context_function === false) return false;

		$type	= $this->message['MsgType'];

		if($type != 'text') {
			if($type == 'event'){
				$event	= strtolower($this->message['Event']);
				
				if($event == 'view' || $event == 'click'){
					$this->delete_context_reply();
				}
			}

			return false;
		}

		if(function_exists($context_function)){
			$this->set_context_reply($context_function);	// 每次使用，自动续命 60 秒
			call_user_func($context_function, $keyword);
			return true;
		}else{
			return false;
		}	
	}

	public function set_context_reply($function, $expire_in=600){
		return wp_cache_set($this->openid, $function, 'weixin_context_reply', $expire_in);
	}

	public function get_context_reply(){
		return wp_cache_get($this->openid, 'weixin_context_reply');
	}

	public function delete_context_reply(){
		return wp_cache_delete($this->openid, 'weixin_context_reply');
	}

	public function custom_reply($keyword){
		// 前缀匹配，只支持2个字
		$prefix_keyword = mb_substr($keyword, 0, 2);

		$custom_replies	= WEIXIN_ReplySetting::get_custom_replies('all');

		if(isset($custom_replies['full'][$keyword])){
			$custom_reply 	= $custom_replies['full'][$keyword];
		}elseif( isset($custom_replies['prefix'][$prefix_keyword])){
			$custom_reply	= $custom_replies['prefix'][$prefix_keyword];
		}elseif($custom_replies['fuzzy'] && preg_match('/'.implode('|', array_keys($custom_replies['fuzzy'])).'/', $keyword, $matches)){
			$fuzzy_keyword	= $matches[0];
			$custom_reply	= $custom_replies['fuzzy'][$fuzzy_keyword];
		}else{
			return false;
		}

		$rand_key = array_rand($custom_reply, 1);
		$custom_reply = $custom_reply[$rand_key];

		$reply	= str_replace("\r\n", "\n", maybe_unserialize($custom_reply['reply']));
		$type	= $custom_reply['type'];

		if($type == 'text'){		// 文本回复
			$this->set_response('custom-text');
			$this->text_reply($reply);
		}elseif($type == 'img'){	// 文章图文回复
			$post_ids	= explode(',', $reply);
			$this->wp_query_reply(array(
				'post__in'		=> $post_ids,
				'orderby'		=> 'post__in',
				'posts_per_page'=> count($post_ids), 
				'post_type'		=> 'any'
			));
			$this->set_response('custom-img');
		}elseif($type == 'img2'){	// 自定义图文回复
			$items	= '';

			if(is_array($reply)){
				$items .= $this->get_item($reply['title'], $reply['description'], $reply['pic_url'], $reply['url']);
			}else{
				$lines	= explode("\n", $reply);
				
				if( isset($lines[0]) && isset($lines[1]) && isset($lines[2]) && isset($lines[3])){
					$items .= $this->get_item($lines[0], $lines[1], $lines[2], $lines[3]);
				}else{
					trigger_error($keyword."\n".$reply."\n".'自定义图文不完整');
					return false;
				}
			}

			$this->news_reply($items);
			$this->set_response('custom-img2');
		}elseif($type == 'news'){	// 素材图文回复
			$material	= weixin()->get_material($reply, 'news');
			if(is_wp_error($material)){
				if($material->get_error_code() == '40007'){
					WEIXIN_ReplySetting::update($custom_reply['id'],['status'=>0]);
				}
				
				$this->text_reply('素材图文错误：'.$material->get_error_code().' '.$material->get_error_message());
			}else{
				$items	= '';
				foreach ($material as $news_item) {
					$items	.= $this->get_item($news_item['title'], $news_item['digest'], $news_item['thumb_url'], $news_item['url']);
					break;
				}
				$this->news_reply($items);
				$this->set_response('custom-news');
			}
		}elseif($type == '3rd'){	// 第三方回复
			$this->third_reply($reply);
		}elseif($type == 'dkf'){	// 多客服
			$this->transfer_customer_service_reply($reply);
			$this->set_response('dkf');
		}elseif($type == 'image'){	// 图片回复
			$this->image_reply($reply);
			$this->set_response('custom-image');
		}elseif($type == 'voice'){	// 语音回复
			$this->set_response('custom-voice');
			$this->voice_reply($reply);
		}elseif($type == 'music'){	// 音乐回复
		 	$this->set_response('custom-music');
			$raw_items 		= explode("\n", $reply);
			$title 			= isset($raw_items[0])?$raw_items[0]:'';
			$description	= isset($raw_items[1])?$raw_items[1]:'';
			$music_url		= isset($raw_items[2])?$raw_items[2]:'';
			$hq_music_url	= isset($raw_items[3])?$raw_items[3]:'';
			$thumb_media_id	= isset($raw_items[4])?$raw_items[4]:'';
			$this->music_reply($title, $description, $music_url, $hq_music_url, $thumb_media_id);
		}elseif($type == 'video'){	// 视频回复
			$this->set_response('custom-video');
			$raw_items 	= explode("\n", $reply);
			$MediaId	= $raw_items[0];
			$title 		= isset($raw_items[1])?$raw_items[1]:'';
			$description= isset($raw_items[2])?$raw_items[2]:'';
			$this->video_reply($MediaId, $title, $description);
		}elseif($type == 'function'){	// 函数回复
			call_user_func($reply, $keyword);
		}elseif($type == 'wxcard'){
			$this->set_response('wxcard');
			$raw_items 	= explode("\n", $reply);
			$card_id	= isset($raw_items[0])?$raw_items[0]:'';
			$outer_id	= isset($raw_items[1])?$raw_items[1]:'';
			$code		= isset($raw_items[2])?$raw_items[2]:'';
			$openid		= isset($raw_items[3])?$raw_items[3]:'';

			$card_ext	= weixin_robot_generate_card_ext(compact('card_id','outer_id','code','openid'));
			$wxcard		= compact('card_id','card_ext');

			$response 	= weixin()->send_custom_message([
				'touser'	=>$this->openid,
				'msgtype'	=>'wxcard',
				'wxcard'	=>compact('card_id','card_ext')
			]);

			echo ' ';
		}

		return true;
	}	

	public function builtin_reply($keyword){
		// 前缀匹配，只支持2个字
		$prefix_keyword = mb_substr($keyword, 0, 2);

		$builtin_replies 		= WEIXIN_ReplySetting::get_builtin_replies('full');
		$builtin_replies_prefix	= WEIXIN_ReplySetting::get_builtin_replies('prefix');

		if($builtin_replies && isset($builtin_replies[$keyword]) ){
			$builtin_reply =  $builtin_replies[$keyword];
		}elseif($builtin_replies_prefix && isset($builtin_replies_prefix[$prefix_keyword]) ){
			$builtin_reply =  $builtin_replies_prefix[$prefix_keyword];
		}else{
			return false;
		}

		if(isset($builtin_reply['method'])){
			call_user_func([$this, $builtin_reply['method']], $keyword);
		}elseif(isset($builtin_reply['function'])){
			call_user_func($builtin_reply['function'], $keyword);
		}else{
			echo ' ';
		}
		
		return true;
	}

	public function query_reply($keyword){
		if(apply_filters('weixin_custom_keyword', false, $keyword)){
			return true;
		}

		$weixin_seting		= weixin_get_setting();

		// 检测关键字是不是太长了
		$keyword_length = mb_strwidth(preg_replace('/[\x00-\x7F]/','',$keyword),'utf-8')+str_word_count($keyword)*2;

		$weixin_keyword_allow_length	= $weixin_seting['weixin_keyword_allow_length']??10;

		if($keyword_length > $weixin_keyword_allow_length){
			$this->too_long_reply();
			return true;
		}

		if(!empty($weixin_seting['weixin_3rd_search'])){ // 如果使用第三方搜索，跳转到第三方
			$this->third_reply();
			return true;
		}
		
		if(WEIXIN_SEARCH){	// 如果支持搜索日志
			// 搜索日志
			if($this->wp_query_reply(['s'=>$keyword])){
				return true;
			}else{
				$this->not_found_reply($keyword);
				return true;
			}
			
		}else{
			$this->not_found_reply($keyword);
			return true;
		}
	}

	public function default_reply($keyword){
		if($this->custom_reply($keyword)) {
			return true;
		}

		$defaut_replies	= WEIXIN_ReplySetting::get_default_replies(); 
		$reply		= isset($defaut_replies[$keyword])?$defaut_replies[$keyword]['value']:'';
		$this->text_reply($reply);
	}

	public function subscribe_reply($keyword){

		WEIXIN_User::subscribe($this->openid);
		
		$subscribe_custom_keyword = '[subscribe]';
		if(weixin_get_type() == 4 && !empty($this->message['EventKey'])){	// 如果是认证服务号，并且是带参数二维码
			$scene	= str_replace('qrscene_','',$this->message['EventKey']);
			$subscribe_custom_keyword = '[subscribe_'.$scene.']';

			WEIXIN_UserSubscribe::insert(array(
				'openid'=>$this->openid,
				'scene'	=>$scene,
				'type'	=>'subscribe',
				'time'	=>time()
			));
			// do_action('weixin_subscribe', $openid, $scene);
		}
		
		if(	$this->custom_reply($subscribe_custom_keyword) == false &&
			$this->builtin_reply($subscribe_custom_keyword)
		){
			$this->default_reply('[subscribe]');
			$this->set_response('subscribe');
		}
	}

	// 取消订阅回复
	public function unsubscribe_reply($keyword){
		WEIXIN_User::unsubscribe($this->openid);
		echo ' ';
	}

	// 带参数二维码扫描回复
	public function scan_reply($keyword){
		$scan_custom_keyword		= '[scan]';
		$subscribe_custom_keyword	= '[subscribe]';
		
		if(weixin_get_type() == 4 && !empty($this->message['EventKey'])){
			$scene	= $this->message['EventKey'];
			$scan_custom_keyword		= '[scan_'.$scene.']';
			$subscribe_custom_keyword	= '[subscribe_'.$scene.']';

			WEIXIN_UserSubscribe::insert(array(
				'openid'=>$this->openid,
				'scene'	=>$scene,
				'type'	=>'scan',
				'time'	=>time()
			));
		}

		if(	$this->custom_reply($scan_custom_keyword) == false && 
			$this->custom_reply($subscribe_custom_keyword) == false && 
			$this->builtin_reply($scan_custom_keyword) == false && 
			$this->builtin_reply($subscribe_custom_keyword) == false && 
			$this->custom_reply('[scan]') == false
		){
			$this->default_reply('[subscribe]');
		}
	}

	// 服务号高级接口用户自动上传地理位置时的回复
	private function location_event_reply($keyword){
		$openid		= $this->openid;
		$last_enter_reply	= wp_cache_get($openid,'weixin_enter_reply');
		$last_enter_reply	= ($last_enter_reply)?$last_enter_reply:0;

		if(time() - $last_enter_reply > apply_filters('weixin_enter_time',HOUR_IN_SECONDS*8))  {
			$this->default_reply('[event-location]');
			wp_cache_set($openid, time(), 'weixin_enter_reply', HOUR_IN_SECONDS);
		}
	}

	private function verify_reply($keyword){
		$message = $this->message;

		if($keyword == '[qualification_verify_success]' || $keyword == '[naming_verify_success]' || $keyword == '[annual_renew]' || $keyword == '[verify_expired]'){
			$time	= (string)$message['ExpiredTime'];
			$time	= get_date_from_gmt(date('Y-m-d H:i:s',$time));

			if($keyword == '[qualification_verify_success]'){
				$type 	= 'success';
				$notice	= '资质认证成功，你已经获取了接口权限，下次认证时间：'.$time.'！';	
			}elseif($keyword == '[naming_verify_success]'){
				$type 	= 'success';
				$notice	= '名称认证成功，下次认证时间：'.$time.'！';	
			}elseif($keyword == '[annual_renew]'){
				$type 	= 'warning';
				$notice	= '你的账号需要年审了，到期时间：'.$time.'！';	
			}elseif($keyword == '[verify_expired]'){
				$type 	= 'error';
				$notice	= '你的账号认证过期了，过期时间：'.$time.'！';
				$type 	= 'error';
			}
		}else{
			$time	= (string)$message['FailTime'];
			$time	= get_date_from_gmt(date('Y-m-d H:i:s',$time));
			$reason	= (string)$message['FailReason'];
			$type = 'error';


			if($keyword == '[qualification_verify_fail]'){
				$type 	= 'error';
				$notice	= '资质认证失败，时间：'.$time.'，原因：'.$reason.'！';	
			}elseif($keyword == '[naming_verify_fail]'){
				$type 	= 'error';
				$notice	= '名称认证失败，时间：'.$time.'，原因：'.$reason.'！';	
			}
		}

		WPJAM_Notice::add(['type'=>$type, 'notice'=>$notice]);

		echo ' ';
	}

	// 找不到内容时回复
	public function not_found_reply($keyword){
		$this->default_reply('[default]');

		if($this->get_response() != 'third' && $this->get_response() != 'function' ){
			$this->set_response('not-found');
		}
	}

	// 关键字太长回复
	public function too_long_reply(){
		$this->default_reply('[too-long]');
		
		if($this->get_response() != '3rd' && $this->get_response() != 'function' ){
			$this->set_response('too-long');
		}
	}

	// 日志搜索回复
	public function wp_query_reply($args=''){
		// 获取除 page 和 attachmet 之外的所有日志类型
		$post_types	= get_post_types(['exclude_from_search'=>false]);

		unset($post_types['page']);
		unset($post_types['attachment']);

		$weixin_seting	= weixin_get_setting();

		$args	= wp_parse_args($args, array(
			'ignore_sticky_posts'	=> true,
			'posts_per_page'		=> 2,
			'post_status'			=> 'publish',
			'post_type'				=> $post_types
		));

		if(!empty($args['s']) && !empty($weixin_seting['weixin_search_url'])){
			$search_term	= $args['s'];
			$weixin_url		= get_search_link($search_term);

			if($term_id = term_exists($search_term)){
				if($term = get_term($term_id)){
					unset($args['s']);

					if($term->taxonomy == 'category'){
						$args['category_name']	= $term->slug;
					}elseif($term->taxonomy == 'post_tag'){
						$args['tag']	= $term->slug;
					}else{
						$args[$term->taxonomy]	= $term->slug;
					}

					$weixin_url	= get_term_link($term);
				}
			}
		}

		global $wp_the_query;

		$args	= apply_filters('weixin_query', $args);

		$wp_the_query->query($args);

		$items	= '';
		
		if($wp_the_query->have_posts()){
			$found_posts	= $wp_the_query->found_posts;
			while ($wp_the_query->have_posts()) {
				$wp_the_query->the_post();

				$title	= apply_filters('weixin_title', get_the_title()); 
				$excerpt= apply_filters('weixin_description', get_post_excerpt( '', apply_filters( 'weixin_description_length', 150 ) ) );

				if($found_posts == 1 || empty($weixin_url)){
					$weixin_url	= get_permalink();

					if($custome_weixin_url = get_post_meta(get_the_ID(), 'weixin_url', true)){
						$weixin_url	= $custome_weixin_url;
					}
				}

				if(weixin_get_type() == 3){	// 认证订阅号才能加，普通订阅号会出问题，后面不能通过 JS SDK 去掉
					$weixin_url	= add_query_arg('weixin_access_token', WEIXIN_User::generate_access_token($this->get_openid()), $weixin_url);
				}else{
					if(strpos($weixin_url, '?') === false){
						$weixin_url	.= '?';
					}
				}

				$thumb	= wpjam_get_post_thumbnail_url('', [720,400]);
				$items	= $items.$this->get_item($title, $excerpt, $thumb, $weixin_url);
				
				break;
			}

			$this->set_response('query');

			$status = apply_filters('weixin_query_reply', false, [$title, $excerpt, $thumb, $weixin_url], $args);

			if(!$status){
				$this->news_reply($items);
			}

			return true;
		}

		return false;
	}

	private function third_reply($no=1){
		$weixin_seting	= weixin_get_setting();
		$third_cache	= $weixin_seting['weixin_3rd_cache_'.$no]??'';
		$third_url		= $weixin_seting['weixin_3rd_url_'.$no]??'';

		$type	= $this->message['MsgType'];
		$third_response = false;

		if($type == 'text'){
			$keyword	= $this->message['Content'];

			if($keyword && $third_cache){
				$third_response	= wp_cache_get($keyword, 'weixin_third_reply');
			}
		}

		if($third_response === false){
			$third_url	= add_query_arg($_GET,$third_url);
			$args		= [ 
				'headers' 	=> ['Content-Type'=>'text/xml', 'Accept-Encoding'=>''],
				'body'		=> file_get_contents('php://input'),
				'need_json_decode'	=> false
			];

			$third_response	= wpjam_remote_request($third_url, $args);

			if(is_wp_error($third_response)){
				$third_response = ' ';
			}else{
				if(($type == 'text') && $keyword && $third_cache){
					wp_cache_set($keyword, $third_response, 'weixin_third_reply', $third_cache);
				}
			}
		}

		echo $third_response;
		$this->set_response('3rd');
	}

	private function openid_replace($str){
		if($openid = $this->openid){
			return str_replace(["\r\n", '[openid]', '[weixin_access_token]'], ["\n", $openid, WEIXIN_User::generate_access_token($openid)], $str);
		}
		return $str;
	}

	public function get_weixin_openid(){ // 微信的 USER OpenID
		return $this->openid;
	}

	public function get_openid(){
		return $this->openid;
	}

	public function get_response(){
		return $this->response;
	}

	public function set_response($response){
		$this->response = $response;
	}

	public function get_message(){
		return $this->message;
	}

	public function textReply($text){
		$this->text_reply($text);
	}

	public function text_reply($text){
		if(is_array($text)){
			trigger_error(var_export($text, true));
		}
		if(trim($text)){
			echo $this->encrypt("
				<xml>".$this->tpl."
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[".$this->openid_replace($text)."]]></Content>
				</xml>
			");
		}else{
			echo ' ';	// 回复空字符串
		}
	}

	public function get_item($title, $description, $pic_url, $url){
		if(!$description) $description = $title;

		return
		'
		<item>
			<Title><![CDATA['.html_entity_decode($title, ENT_QUOTES, "utf-8" ).']]></Title>
			<Description><![CDATA['.html_entity_decode($description, ENT_QUOTES, "utf-8" ).']]></Description>
			<PicUrl><![CDATA['.$pic_url.']]></PicUrl>
			<Url><![CDATA['.$this->openid_replace($url).']]></Url>
		</item>
		';
	}

	public function news_reply($items, $count=1){
		echo $this->encrypt( "
			<xml>".$this->tpl."
				<MsgType><![CDATA[news]]></MsgType>
				<Content><![CDATA[]]></Content>
				<ArticleCount>".$count."</ArticleCount>
				<Articles>
				".$items."
				</Articles>
			</xml>
		");
	}

	public function image_reply($media_id){
		echo $this->encrypt("
			<xml>".$this->tpl."
				<MsgType><![CDATA[image]]></MsgType>
				<Image>
				<MediaId><![CDATA[".$media_id."]]></MediaId>
				</Image>
			</xml>
		");
	}

	public function voice_reply($media_id){
		echo $this->encrypt("
			<xml>".$this->tpl."
				<MsgType><![CDATA[voice]]></MsgType>
				<Voice>
				<MediaId><![CDATA[".$media_id."]]></MediaId>
				</Voice>
			</xml>
		");
	}

	public function video_reply($video, $title='', $description=''){
		echo $this->encrypt("
			<xml>".$this->tpl."
				<MsgType><![CDATA[video]]></MsgType>
				<Video>
				<MediaId><![CDATA[".$video."]]></MediaId>
				<Title><![CDATA[".$title."]]></Title>
				<Description><![CDATA[".$description."]]></Description>
				</Video>
			</xml>
		");
	}

	public function music_reply($title='', $description='', $music_url='', $hq_music_url='', $thumb_media_id=''){
		echo $this->encrypt("
			<xml>".$this->tpl."
				<MsgType><![CDATA[music]]></MsgType>
				<Music>
				<Title><![CDATA[".$title."]]></Title>
				<Description><![CDATA[".$description."]]></Description>
				<MusicUrl><![CDATA[".$music_url."]]></MusicUrl>
				<HQMusicUrl><![CDATA[".$hq_music_url."]]></HQMusicUrl>
				<ThumbMediaId><![CDATA[".$thumb_media_id."]]></ThumbMediaId>
				</Music>
			</xml>
		");
	}

	public function transfer_customer_service_reply($KfAccount=''){
		if($KfAccount){
			$msg = "
			<xml>".$this->tpl."
				<MsgType><![CDATA[transfer_customer_service]]></MsgType>
				<TransInfo>
			        <KfAccount>".$KfAccount."</KfAccount>
			    </TransInfo>
			</xml>
			";
		}else{
			$msg = "
			<xml>".$this->tpl."
				<MsgType><![CDATA[transfer_customer_service]]></MsgType>
			</xml>
			";
		}

		echo $this->encrypt($msg);
	}

	/**
	 * 将公众平台回复用户的消息加密打包.
	 * <ol>
	 *    <li>对要发送的消息进行AES-CBC加密</li>
	 *    <li>生成安全签名</li>
	 *    <li>将消息密文和安全签名打包成xml格式</li>
	 * </ol>
	 *
	 * @param $reply_msg string 公众平台待回复用户的消息，xml格式的字符串
	 * @param $timeStamp string 时间戳，可以自己生成，也可以用URL参数的timestamp
	 * @param $nonce string 随机串，可以自己生成，也可以用URL参数的nonce
	 * @param &$encrypt_msg string 加密后的可以直接回复用户的密文，包括msg_signature, timestamp, nonce, encrypt的xml格式的字符串,
	 *                      当return返回0时有效
	 *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function encrypt($reply_msg){

		$encrypted	= apply_filters('weixin_message_encrypted', true);

		if(!$encrypted){
			return $reply_msg;
		}

		try {
			//获得16位随机字符串，填充到明文之前
			$random = wpjam_generate_random_string(16);
			$text	= $random . pack("N", strlen($reply_msg)) . $reply_msg . $this->appid;
			
			//使用自定义的填充方式对明文进行补位填充
			$text = $this->encode($text);

			//加密
			$encrypt_msg = $this->crypt->encrypt($text);
		} catch (Exception $e) {
			//print $e;
			return new WP_Error('encrypt_aes_failed', 'aes 加密失败');
		}

		//生成安全签名
		$msg_signature = $this->generate_msg_signature($encrypt_msg);
		if(is_wp_error($msg_signature)){
			return $msg_signature;
		}

		//生成发送的xml
		return "
		<xml>
			<Encrypt><![CDATA[".$encrypt_msg."]]></Encrypt>
			<MsgSignature><![CDATA[".$msg_signature."]]></MsgSignature>
			<TimeStamp>".$this->timestamp."</TimeStamp>
			<Nonce><![CDATA[".$this->nonce."]]></Nonce>
		</xml>
		";
	}

	/**
	 * 检验消息的真实性，并且获取解密后的明文.
	 * <ol>
	 *    <li>利用收到的密文生成安全签名，进行签名验证</li>
	 *    <li>若验证通过，则提取xml中的加密消息</li>
	 *    <li>对消息进行解密</li>
	 * </ol>
	 *
	 * @param $msgSignature string 签名串，对应URL参数的msg_signature
	 * @param $timestamp string 时间戳 对应URL参数的timestamp
	 * @param $nonce string 随机串，对应URL参数的nonce
	 * @param $postData string 密文，对应POST请求的数据
	 * @param &$msg string 解密后的原文，当return返回0时有效
	 *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function decrypt($msg, $msg_signature=''){

		$encrypted	= apply_filters('weixin_message_encrypted', true);

		if(!$encrypted){
			return $msg;
		}

		if(strpos($msg, '<Encrypt>') === false){
			return new WP_Error('invaild_encrypt_xml', '非法加密 XML'); 
		}

		// 提取出xml数据包中的加密消息
		try {
			$xml = new DOMDocument();
			$xml->loadXML($msg);
			$encrypt_array	= $xml->getElementsByTagName('Encrypt');
			// $openid_array	= $xml->getElementsByTagName('ToUserName');
			$encrypt_msg	= $encrypt_array->item(0)->nodeValue;
		} catch (Exception $e) {
			return new WP_Error('parse_xml_failed', 'XML 解析失败');
		}

		//验证安全签名
		$signature = $this->generate_msg_signature($encrypt_msg);
		if(is_wp_error($signature)){
			return $signature;
		}

		if ($signature != $msg_signature) {
			return new WP_Error('validate_signature_error', '签名验证错误');
		}

		try {
			$decrypted = $this->crypt->decrypt($encrypt_msg);
		} catch (Exception $e) {
			return new WP_Error('decrypt_aes_failed', 'aes 解密失败');
		}

		try {
			//去除补位字符
			$result = $this->decode($decrypted);

			//去除16位随机字符串,网络字节序和AppId
			if (strlen($result) < 16)	return "";

			$content		= substr($result, 16, strlen($result));
			$len_list		= unpack("N", substr($content, 0, 4));
			$xml_len		= $len_list[1];
			$decrypt_msg	= substr($content, 4, $xml_len);
			$from_appid		= substr($content, $xml_len + 4);
		} catch (Exception $e) {
			//print $e;
			return new WP_Error('illegal_buffer', '解密后得到的buffer非法');
		}
		if ($from_appid != $this->appid){
			return new WP_Error('validate_appid_error', 'Appid 校验错误');
		}
		// return $xml_content;

		// if(is_wp_error($decrypted_msg)){
		// 	return $decrypted_msg;
		// }

		return $decrypt_msg;
	}

	/**
	 * 用SHA1算法生成安全签名
	 * @param string $token 票据
	 * @param string $timestamp 时间戳
	 * @param string $nonce 随机字符串
	 * @param string $encrypt 密文消息
	 */
	public function generate_msg_signature($encrypt_msg){
		try {
			$array = [$encrypt_msg, $this->token, $this->timestamp, $this->nonce];
			sort($array, SORT_STRING);
			return sha1(implode($array));
		} catch (Exception $e) {
			//print $e . "\n";
			return new WP_Error('compute_signature_failed', 'sha加密生成签名失败');
		}
	}

	/**
	 * 对需要加密的明文进行填充补位
	 * @param $text 需要进行填充补位操作的明文
	 * @return 补齐明文字符串
	 */
	private function encode($text){
		//计算需要填充的位数
		$amount_to_pad	= 32 - (strlen($text) % 32);
		$amount_to_pad	= ($amount_to_pad)?$amount_to_pad:32;
		
		//获得补位所用的字符
		$pad_chr = chr($amount_to_pad);
		
		return $text . str_repeat($pad_chr, $amount_to_pad);
	}

	/**
	 * 对解密后的明文进行补位删除
	 * @param decrypted 解密后的明文
	 * @return 删除填充补位后的明文
	 */
	private function decode($text){
		$pad	= ord(substr($text, -1));
		$pad	= ($pad >= 1 && $pad <= 32)?$pad:0;
		return substr($text, 0, (strlen($text) - $pad));
	}
}

