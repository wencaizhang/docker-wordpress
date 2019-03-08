<?php
// wp_cache_add_global_groups(array('weixin'));
class WEIXIN{
	private $appid;
	private $secret;

	public function __construct($appid, $secret){
		$this->appid	= $appid;
		$this->secret	= trim($secret);
	}

	// 用户接口
	public function get_user_info($openid){
		return $this->http_request('https://api.weixin.qq.com/cgi-bin/user/info?openid='.urlencode($openid));
	}

	public function batch_get_user_info($openids, $lang='zh_CN'){
		$user_list	= array_map(function($openid) use($lang){
			return array('openid' => $openid, 'lang' => $lang);
		}, $openids);

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/user/info/batchget', array(
			'method'	=> 'POST',
			'body'		=> compact('user_list')

		));
	}

	public function get_user_list($next_openid){
		return $this->http_request('https://api.weixin.qq.com/cgi-bin/user/get?next_openid='.$next_openid);
	}

	public function update_user_remark($openid, $remark){
		return $this->http_request('https://api.weixin.qq.com/cgi-bin/user/info/updateremark', array(
			'method'=> 'POST',
			'body'	=> compact('openid', 'remark')
		));
	}

	// 获取微信 Oauth Access Token
	public function get_oauth_access_token($code){
		$user_oauth	= wp_cache_get($code, 'weixin_code_'.$this->appid);

		if($user_oauth === false) {
			$user_oauth	= $this->http_request('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appid.'&secret='.$this->secret.'&code='.$code.'&grant_type=authorization_code', array('need_access_token'=>false));

			if(is_wp_error($user_oauth)){
				return $user_oauth;
			}

			wp_cache_set($code, $user_oauth, 'weixin_code_'.$this->appid, MINUTE_IN_SECONDS*5);	// 防止同个 code 多次请求
		}

		return $user_oauth;
	}

	public function refresh_oauth_access_token($openid, $refresh_token){
		return $this->http_request('https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.$this->appid.'&grant_type=refresh_token&refresh_token='.$refresh_token, array('need_access_token'=>false));
	}

	// 获取微信 OAuth 用户详细信息
	public function get_oauth_userifo($openid, $access_token){
		return  $this->http_request('https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN', array('need_access_token'=>false));
	}

	public function get_oauth_redirect_url($scope='snsapi_base', $redirect_uri=''){
		return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appid.'&redirect_uri='.urlencode($redirect_uri).'&response_type=code&scope='.$scope.'&state='.wp_create_nonce($scope).'#wechat_redirect';
	}

	

	// 1. 最多100个标签
	// 2. 用户最多打10个标签
	// 3. 3个系统默认保留的标签不能修改
	// 4. 粉丝数超过10w的标签不能删除
	// 5. 批量为用户打标签每次最多50个用户，取消打标签也是

	// 因为获取用户详细资料的接口已有标签信息，所以获取用户标签接口无意义

	// 微信 batchget 用户资料里面的 tagid 列表是错的，==> 微信已经修正成对的，

	public function get_tags($force=false){

		$user_tags	= wp_cache_get('weixin_user_tags', $this->appid);

		if($user_tags === false || $force){
			$response	= $this->http_request('https://api.weixin.qq.com/cgi-bin/tags/get');

			if(is_wp_error($response)){
				return $response;
			}

			$user_tags = $response['tags'];

			if($user_tags){
				$user_tag_ids	= array_column($user_tags, 'id');
				$user_tags		= array_combine($user_tag_ids, $user_tags);
			}

			wp_cache_set('weixin_user_tags', $user_tags, $this->appid, DAY_IN_SECONDS);
		}

		return $user_tags;
	}

	public function create_tag($name){
		wp_cache_delete('weixin_user_tags', $this->appid);

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/tags/create', array(
			'method'	=> 'POST',
			'body'		=> array('tag'=>compact('name')),
		));
	}

	public function update_tag($id, $name){
		wp_cache_delete('weixin_user_tags', $this->appid);

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/tags/update', array(
			'method'	=> 'POST',
			'body'		=> array('tag'=>compact('id','name')),
		));
	}

	public function delete_tag($id){
		wp_cache_delete('weixin_user_tags', $this->appid);

		$id	= (int)$id;

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/tags/delete', array(
			'method'	=> 'POST',
			'body'		=> array('tag'=>compact('id')),
		));
	}

	public function batch_tagging($openid_list, $tagid){
		wp_cache_delete('weixin_user_tags', $this->appid);

		if(is_string($openid_list)){	// 单个 openid 情况也支持，我牛逼吧
			$openid_list = [$openid_list];
		}

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging', array(
			'method'	=> 'POST',
			'body'		=> compact('openid_list','tagid'),
		));
	}

	public function batch_untagging($openid_list, $tagid){
		wp_cache_delete('weixin_user_tags', $this->appid);

		if(is_string($openid_list)){
			$openid_list = [$openid_list];
		}

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/tags/members/batchuntagging', array(
			'method'	=> 'POST',
			'body'		=> compact('openid_list','tagid'),
		));
	}

	public function get_tag_users($tagid, $next_openid=''){
		if(empty($next_openid)){
			$tag_users	= wp_cache_get($tagid, 'weixin_tag_users_'.$this->appid.'');
		}

		if($next_openid || $tag_users === false){
			$tag_users	= $this->http_request('https://api.weixin.qq.com/cgi-bin/user/tag/get', array(
				'method'	=> 'POST',
				'body'		=> compact('tagid', 'next_openid'),
			));

			if(empty($next_openid)){
				wp_cache_set($tagid, $tag_users, 'weixin_tag_users_'.$this->appid, MINUTE_IN_SECONDS);
			}
		}

		return $tag_users;
	}

	public function get_blacklist($next_openid=''){
		if(empty($next_openid)){
			$blacklist	= wp_cache_get('weixin_blacklist', $this->appid);
		}

		if($next_openid || $blacklist === false){
			$response	= $this->http_request('https://api.weixin.qq.com/cgi-bin/tags/members/getblacklist', array(
				'method'	=> 'POST',
				'body'		=> compact('next_openid'),
			));

			if(is_wp_error($response)){
				return $response;
			}

			$blacklist	= $response['data']['openid'];
			$total		= $response['total'];
			$count		= $response['count'];

			if($total > $count){
				$next_openid	= $response['next_openid'];
				// 继续获取，以后再写，谁TM有一万个黑名单用户的时候，我一定帮他写。
			}

			if($next_openid == ''){
				wp_cache_set('weixin_blacklist', $blacklist, $this->appid, HOUR_IN_SECONDS);
			}
		}

		return $blacklist;
	}

	public function batch_blacklist($openid_list){
		wp_cache_delete('weixin_blacklist', $this->appid);

		if(is_string($openid_list)){
			$openid_list = [$openid_list];
		}

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/tags/members/batchblacklist', array(
			'method'	=> 'POST',
			'body'		=> compact('openid_list'),
		));
	}

	public function batch_unblacklist($openid_list){
		wp_cache_delete('weixin_blacklist', $this->appid);

		if(is_string($openid_list)){
			$openid_list = [$openid_list];
		}

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/tags/members/batchunblacklist', array(
			'method'	=> 'POST',
			'body'		=> compact('openid_list'),
		));
	}

	// 获取主菜单
	public function get_menu(){
		return $this->http_request('https://api.weixin.qq.com/cgi-bin/menu/get');
	}

	// 删除菜单以及所有个性化菜单，尽量不要使用
	public function weixin_robot_delete_menu(){
		return $this->http_request('https://api.weixin.qq.com/cgi-bin/menu/delete');
	}

	// 创建菜单
	public function create_menu($button){
		return $this->http_request('https://api.weixin.qq.com/cgi-bin/menu/create', array(
			'method'	=> 'POST',
			'body'		=> compact('button'),
		));
	}

	// 创建个性化菜单
	public function add_conditional_menu($button, $matchrule){
		return $this->http_request('https://api.weixin.qq.com/cgi-bin/menu/addconditional', array(
			'method'	=> 'POST',
			'body'		=> compact('button','matchrule'),
		));
	}

	// 删除个性化菜单
	public function del_conditional_menu($menuid){
		return $this->http_request('https://api.weixin.qq.com/cgi-bin/menu/delconditional', array(
			'method'	=> 'POST',
			'body'		=> compact('menuid'),
		));
	}

	// 测试个性化菜单匹配结果
	public function try_match_menu($user_id){
		return $this->http_request('https://api.weixin.qq.com/cgi-bin/menu/trymatch', array(
			'method'	=> 'POST',
			'body'		=> compact('user_id'),
		));
	}


	// 新增临时素材
	public function upload_media($media, $type='image'){
		$response	= $this->http_request('https://api.weixin.qq.com/cgi-bin/media/upload?type='.$type, array(
			'method'	=> 'file',
			'body'		=> array('media'=> new CURLFile($media,'', basename($media))),
		));

		if(is_wp_error($response)){
			return $response;
		}

		if($response['type'] == 'thumb'){
			return $response['thumb_media_id'];
		}else{
			return $response['media_id'];
		}
	}

	public function upload_img_media($media){	//上传图片获取微信图片链接
		$url	= 'https://api.weixin.qq.com/cgi-bin/media/uploadimg';
		$post	= array('media'=> new CURLFile($media,'',basename($media)));

		$response	= $this->http_request($url, array(
			'method'	=> 'file',
			'body'		=> array('media'=> new CURLFile($media,'',basename($media))),
		));

		if(is_wp_error($response)){
			return $response;
		}

		return $response['url'];
	}

	// 获取临时素材
	public function get_media($media_id, $type='image'){
		if($type=='image'){
			$media_dir	= substr($media_id, 0, 1).'/'.substr($media_id, 1, 1);
			if(!is_dir(WEIXIN_ROBOT_PLUGIN_TEMP_DIR.$this->appid.'/'.'media/'.$media_dir)){
				mkdir(WEIXIN_ROBOT_PLUGIN_TEMP_DIR.$this->appid.'/'.'media/'.$media_dir, 0777, true);
			}

			$media_file	= WEIXIN_ROBOT_PLUGIN_TEMP_DIR.$this->appid.'/'.'media/'.$media_dir.'/'.$media_id.'.jpg';
			$media_url	= WEIXIN_ROBOT_PLUGIN_TEMP_URL.$this->appid.'/'.'media/'.$media_dir.'/'.$media_id.'.jpg';

			if(!file_exists($media_file)){
				$response	= $this->http_request('https://api.weixin.qq.com/cgi-bin/media/get?media_id='.$media_id, array(
					'stream'			=>true, 
					'filename'			=>$media_file,
					'need_json_decode'	=>false
				));

				if(is_wp_error($response)){
					return $response;
				}
			}

			return $media_url;
		}
	}

	// 生成下载临时素材的链接
	public function get_media_download_url($media_id){
		$response = $this->get_access_token();

		if(is_wp_error($response)){
			return $response;
		}

		return 'https://api.weixin.qq.com/cgi-bin/media/get?media_id='.$media_id.'&access_token='.$response['access_token'];
	}

	// 获取永久素材
	public function get_material($media_id, $type='image', $force=false){
		$url	= 'https://api.weixin.qq.com/cgi-bin/material/get_material';
		$body	= compact('media_id');

		if($type=='image' || $type=='thumb'){

			$media_dir	= substr($media_id, 0, 1).'/'.substr($media_id, 1, 1);
			if(!is_dir(WEIXIN_ROBOT_PLUGIN_TEMP_DIR.$this->appid.'/'.'material/'.$media_dir)){
				mkdir(WEIXIN_ROBOT_PLUGIN_TEMP_DIR.$this->appid.'/'.'material/'.$media_dir, 0777, true);
			}

			$media_file	= WEIXIN_ROBOT_PLUGIN_TEMP_DIR.$this->appid.'/'.'material/'.$media_dir.'/'.$media_id.'.jpg';
			$media_url	= WEIXIN_ROBOT_PLUGIN_TEMP_URL.$this->appid.'/'.'material/'.$media_dir.'/'.$media_id.'.jpg';

			if(!file_exists($media_file) || $force){
				$response	= $this->http_request($url, array(
					'method'			=> 'POST',
					'body'				=> $body,
					'stream'			=> true, 
					'filename'			=> $media_file,
					'need_json_decode'	=> false
					));

				if(is_wp_error($response)){
					if($response->get_error_code() == '40007'){	//  invalid media_id
						$im = imagecreatetruecolor(120, 20);
						$text_color = imagecolorallocate($im, 233, 14, 91);
						imagestring($im, 1, 5, 5,  'invalid media_id', $text_color);

						imagejpeg($im, $media_file, 100 ); // 存空图片，防止重复请求
					}
					return $response;
				}	
			}

			return $media_url;
		}elseif($type == 'news'){
			$material = wp_cache_get($media_id, 'weixin_material_'.$this->appid);
			if($material === false || $force){
				$response	= $this->http_request($url, array(
					'method'	=> 'POST',
					'body'		=> $body
					));

				if(is_wp_error($response)){
					return $response;
				}

				$material	= $response['news_item'];
				wp_cache_set($media_id, $material, 'weixin_material_'.$this->appid, DAY_IN_SECONDS);
			}
			return $material;
		}elseif($type == 'video'){
			$response	= $this->http_request($url, array(
				'method'	=> 'POST',
				'body'		=> $body
				));

			return $response;
		}
	}

	//删除永久素材
	public function del_material($media_id){
		wp_cache_delete($media_id, 'weixin_material_'.$this->appid);

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/material/del_material', array(
			'method'	=> 'POST',
			'body'		=> compact('media_id')
		));
	}

	// 新增图文素材
	public function add_news_material($articles){
		return $this->http_request('https://api.weixin.qq.com/cgi-bin/material/add_news', array(
			'method'	=> 'POST',
			'body'		=> compact('articles')
		));
	}

	// 修改图文素材
	public function update_news_material($media_id, $index, $articles){
		return $this->http_request('https://api.weixin.qq.com/cgi-bin/material/update_news', array(
			'method'	=> 'POST',
			'body'		=> compact('media_id', 'index', 'articles')
		));
	}

	//新增其他类型永久素材
	public function add_material($media, $type='image', $args=array()){
		extract(wp_parse_args( $args, array(
			'description'	=> '',
			'filename'		=> '',
			'filetype'		=> '',
		)));

		$body	= array();
		$body['type']	= $type;

		$filename 		= ($filename)?$filename:basename($media);
		$body['media']	= new CURLFile($media, $filetype, $filename);

		if($description){
			$body['description']= wpjam_json_encode($description);
		}

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/material/add_material', array(
			'method'	=> 'file',
			'body'		=> $body
		));
	}

	// 获取素材列表
	public function batch_get_material($type = 'news', $offset = 0, $count = 20 ){
		return $this->http_request('https://api.weixin.qq.com/cgi-bin/material/batchget_material', array(
			'method'	=> 'post',
			'body'		=> compact("type", "offset", "count")
		));
	}

	// 获取素材总数
	public function get_material_count(){
		$material_count  = wp_cache_get('weixin_material_count', $this->appid);

		if($material_count === false){

			$material_count = $this->http_request('https://api.weixin.qq.com/cgi-bin/material/get_materialcount');

			if(is_wp_error($material_count)){
				return $material_count;
			}

			wp_cache_set('weixin_material_count', $material_count, $this->appid, 60);
		}

		return $material_count;
	}

	public function sendall_mass_message($tag_id, $msgtype='text', $content='', $send_ignore_reprint=1){
		$data	= $this->get_message_send_data($msgtype, $content);

		if($tag_id == 'all'){
			$data['filter']	= array('is_to_all'=>true);
		}else{
			$data['filter']	= array('tag_id'=>$tag_id, 'is_to_all'=>false);
		}

		$data['send_ignore_reprint']	= (int)$send_ignore_reprint;

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/message/mass/sendall', array(
			'method'	=> 'post',
			'body'		=> $data
		));
	}

	public function send_mass_message($touser, $msgtype='text', $content=''){
		$data	= $this->get_message_send_data($msgtype, $content);

		$data['touser']	= $touser;

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/message/mass/send', array(
			'method'	=> 'post',
			'body'		=> $data
		));
	}

	public function preview_mass_message($towxname, $msgtype='text', $content=''){
		$data	= $this->get_message_send_data($msgtype, $content);
		$data['towxname']	= $towxname;

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/message/mass/preview', array(
			'method'	=> 'post',
			'body'		=> $data
			));
	}

	public function send_custom_message($data=[]){

		$data	= wp_parse_args($data, array(
			'touser'	=> '',
			'msgtype'	=> 'text',
		));
		
		// if($kf_account){
		// 	$data['customservice']	= array('kf_account' => $kf_account);
		// }

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/message/custom/send', array(
			'method'	=> 'post',
			'body'		=> $data
		));
	}

	public function send_template_message($data=[]){

		$data	= wp_parse_args($data, array(
			'touser'			=> '',
			'template_id'		=> '',
			'url'				=> '',
			'miniprogram'		=> [],
			'data'				=> [],
		));
		
		// if($url){
		// 	$data['url']	= $url;
		// }

		// if($miniprogram){
		// 	$data['miniprogram']	= $miniprogram;
		// }

		// if($topcolor){
		// 	$data['topcolor']	= $topcolor;
		// }

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/message/template/send', array(
			'method'	=> 'post',
			'body'		=> $data
		));
	}

	public function upload_news_media($articles){
		return $this->http_request($url, array(
			'method'	=> 'post',
			'body'		=> 'https://api.weixin.qq.com/cgi-bin/media/uploadnews'
		));
	}

	public function get_message_send_data($msgtype='text', $content='', $title='', $description=''){
		$data 				= [];
		$data['msgtype']	= $msgtype;
		
		switch ($msgtype) {
			case 'text':
				$data['text']	= ['content'	=> $content];
				break;

			case 'voice':
				$data['voice']	= ['media_id'	=> $content];
				break;

			case 'image':
				$data['image']	= ['media_id'	=> $content];
				break;

			// case 'video':
			// 	$data['video']	= array('media_id'	=> $content, 'title'=> $title, 'description'=>$description);
			// 	break;

			case 'news':
				$data['news']	= ['articles'	=> $content];
				break;

			case 'mpnews':
				$data['mpnews']	= ['media_id'	=> $content];
				break;

			case 'mpvideo':
				$data['mpvideo']= ['media_id'	=> $content];
				break;

			case 'miniprogrampage':
				$data['miniprogrampage']	=  $content;
				break;

			case 'wxcard':
				$data['wxcard']	= $content;
				break;
			
			default:
				break;
		}

		return $data;
	}

	public function get_js_api_ticket(){
		$response = wp_cache_get('weixin_js_api_ticket', $this->appid);
		if($response == false){

			$response = $this->http_request('https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi');

			if(is_wp_error($response)){
				return $response;
			}

			$response['expires_in']	= time()+$response['expires_in']-600;

			wp_cache_set('weixin_js_api_ticket', $response,	$this->appid, $response['expires_in']);
		}

		return $response;
	}

	public function get_wx_card_ticket(){
		$response = wp_cache_get('weixin_wx_card_ticket', $this->appid);
		if($response == false){

			$response = $this->http_request('https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=wx_card');

			if(is_wp_error($response)){
				return false;
			}

			$response['expires_in']	= time()+$response['expires_in']-600;

			wp_cache_set('weixin_wx_card_ticket', $response, $this->appid, $response['expires_in']);
		}

		return $response;
	}

	// 创建带参数的二维码
	public function create_qrcode($action_name='QR_LIMIT_SCENE',$scene='',$expire_seconds=2592000){
		$data = compact('action_name');

		if($action_name == 'QR_LIMIT_SCENE'){
			$data['action_info']	= array( 'scene' => array( 'scene_id' => (int)$scene ) );
		}elseif($action_name == 'QR_LIMIT_STR_SCENE'){
			$data['action_info']	= array( 'scene' => array( 'scene_str' => $scene ) );
		}elseif($action_name == 'QR_SCENE'){
			$data['action_info']	= array( 'scene' => array( 'scene_id' => (int)$scene ) );
			$data['expire_seconds']	= intval($expire_seconds);
		}elseif($action_name == 'QR_STR_SCENE'){
			$data['action_info']	= array( 'scene' => array( 'scene_str' => $scene ) );
			$data['expire_seconds']	= intval($expire_seconds);
		}

		return $this->http_request('https://api.weixin.qq.com/cgi-bin/qrcode/create', array(
			'method'	=> 'POST',
			'body'		=> $data
		));
	}




	public function add_customservice_kf_account($data){
		wp_cache_delete('weixin_kf_list', $this->appid);

		return $this->http_request('https://api.weixin.qq.com/customservice/kfaccount/add', array(
			'method'	=> 'POST',
			'body'		=> $data
		));
	}

	public function update_customservice_kf_account($data){
		wp_cache_delete('weixin_kf_list', $this->appid);

		return $this->http_request('https://api.weixin.qq.com/customservice/kfaccount/update', array(
			'method'	=> 'POST',
			'body'		=> $data
		));
	}

	public function delete_customservice_kf_account($kf_account){
		wp_cache_delete('weixin_kf_list', $this->appid);

		return $this->http_request('https://api.weixin.qq.com/customservice/kfaccount/del?kf_account='.urldecode($kf_account));
	}

	public function invite_customservice_kf_account_worker($kf_account, $invite_wx){
		wp_cache_delete('weixin_kf_list', $this->appid);

		return $this->http_request('https://api.weixin.qq.com/customservice/kfaccount/inviteworker', array(
			'method'	=> 'POST',
			'body'		=> compact('kf_account','invite_wx')
		));
	}

	public function upload_customservice_kf_account_headimg($kf_account, $media){
		wp_cache_delete('weixin_kf_list', $this->appid);

		return	$this->http_request('https://api.weixin.qq.com/customservice/kfaccount/uploadheadimg?kf_account='.urldecode($kf_account), array(
			'method'	=> 'file',
			'body'		=> array('media'=> curl_file_create($media)),
		));
	}

	public function get_customservice_kf_list(){
		$kf_list	= wp_cache_get('weixin_kf_list', $this->appid);
		if($kf_list === false){
			$response	= $this->http_request('https://api.weixin.qq.com/cgi-bin/customservice/getkflist');
			if(is_wp_error($response)){
				wp_cache_set('weixin_kf_list', array(), $this->appid, 60);
				return $response;
			}else{
				$kf_list = $response['kf_list'];
				wp_cache_set('weixin_kf_list', $kf_list, $this->appid, 3600);
			}
		}

		return $kf_list;
	}

	public function get_customservice_online_kf_list(){
		$online_kf_list = wp_cache_get('weixin_online_kf_list', $this->appid);
		if($online_kf_list === false){
			$response	= $this->http_request('https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist');
			if(is_wp_error($response)){
				wp_cache_set('weixin_online_kf_list', array(), $this->appid, 30);
				return $response;
			}else{
				$online_kf_list = $response['kf_online_list'];
				wp_cache_set('weixin_online_kf_list', $online_kf_list, $this->appid, 30);
			}
		}

		return $online_kf_list;
	}

	public function create_customservice_kf_session($kf_account, $openid, $text=''){
		return $this->http_request('https://api.weixin.qq.com/customservice/kfsession/create', array(
			'method'	=> 'POST',
			'body'		=> compact('kf_account', 'openid', 'text')
		));
	}

	public function close_customservice_kf_session($kf_account, $openid, $text=''){
		return $this->http_request('https://api.weixin.qq.com/customservice/kfsession/close', array(
			'method'	=> 'POST',
			'body'		=> compact('kf_account', 'openid', 'text')
		));
		
	}

	public function get_customservice_kf_session($openid){
		return $this->http_request('https://api.weixin.qq.com/customservice/kfsession/getsession?openid='.$openid);
	}

	public function get_customservice_kf_session_list($kf_account){
		$response	= $this->http_request('https://api.weixin.qq.com/customservice/kfsession/getsessionlist?kf_account='.$kf_account);

		if(is_wp_error($response)){
			return $response;
		}

		return $response['sessionlist'];
	}

	public function get_customservice_kf_wait_case_session_list($kf_account){
		return $this->http_request('https://api.weixin.qq.com/customservice/kfsession/getwaitcase');
	}

	public function get_customservice_msg_record($starttime, $endtime, $pageindex=1, $pagesize=50){
		return $this->http_request('https://api.weixin.qq.com/customservice/msgrecord/getrecord', array(
			'method'	=> 'POST',
			'body'		=> compact('starttime','endtime','pagesize','pageindex')
		));
	}



	public function get_article_total($begin_date, $end_date){	
		return $this->http_request('https://api.weixin.qq.com/datacube/getarticletotal', array(
			'method'	=> 'POST',
			'body'		=> compact('begin_date','end_date')
		));
	}

	public function get_article_summary($begin_date, $end_date){
		return $this->http_request('https://api.weixin.qq.com/datacube/getarticlesummary', array(
			'method'	=> 'POST',
			'body'		=> compact('begin_date','end_date')
		));
	}


	public function get_interface_summary($begin_date, $end_date, $type='day'){
		if($type == 'day'){
			$url		= 'https://api.weixin.qq.com/datacube/getinterfacesummary';		
		}else{
			$url		= 'https://api.weixin.qq.com/datacube/getinterfacesummaryhour';
			$end_date	= $begin_date;
		}

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('begin_date','end_date')
		));
	}

	public function get_up_stream_msg($begin_date, $end_date, $type='day'){
		$urls	= array(
			'day'	=> 'https://api.weixin.qq.com/datacube/getupstreammsg',
			'hour'	=> 'https://api.weixin.qq.com/datacube/getupstreammsghour',
			'week'	=> 'https://api.weixin.qq.com/datacube/getupstreammsgweek',
			'month'	=> 'https://api.weixin.qq.com/datacube/getupstreammsgmonth'
		);

		return $this->http_request($urls[$type], array(
			'method'	=> 'POST',
			'body'		=> compact('begin_date','end_date')
		));
	}


	public function get_up_stream_msg_dist($begin_date, $end_date, $type='day'){
		$urls	= array(
			'day'	=> 'https://api.weixin.qq.com/datacube/getupstreammsgdist',
			'hour'	=> 'https://api.weixin.qq.com/datacube/getupstreammsgdisthour',
			'week'	=> 'https://api.weixin.qq.com/datacube/getupstreammsgdistweek',
			'month'	=> 'https://api.weixin.qq.com/datacube/getupstreammsgdistmonth'
			);

		return $this->http_request($urls[$type], array(
			'method'	=> 'POST',
			'body'		=> compact('begin_date','end_date')
		));
	}

	public function get_user_read( $begin_date, $end_date='', $type = 'day'){
		if($type == 'day'){
			$url 	= 'https://api.weixin.qq.com/datacube/getuserread';
		}else{
			$url 	= 'https://api.weixin.qq.com/datacube/getuserreadhour';
			$end_date	= $begin_date;
		}

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('begin_date','end_date')
		));
	}

	public function get_user_share( $begin_date, $end_date='', $type = 'day'){
		if($type == 'day'){
			$url 	= 'https://api.weixin.qq.com/datacube/getusershare';
		}else{
			$url 	= 'https://api.weixin.qq.com/datacube/getusersharehour';
			$end_date	= $begin_date;
		}

		return $this->http_request($url, array(
			'method'	=> 'POST',
			'body'		=> compact('begin_date','end_date')
		));
	}

	public function get_user_summary($begin_date, $end_date){
		return $this->http_request('https://api.weixin.qq.com/datacube/getusersummary', array(
			'method'	=> 'POST',
			'body'		=> compact('begin_date','end_date')
		));
	}

	public function get_user_cumulate($begin_date, $end_date){
		return $this->http_request('https://api.weixin.qq.com/datacube/getusercumulate', array(
			'method'	=> 'POST',
			'body'		=> compact('begin_date','end_date')
		));
	}



	
	private function http_request($url, $args=array()){
		$args = wp_parse_args( $args, array(
			'need_access_token'	=> true,
			'need_json_encode'	=> true,
			'timeout'			=> 5,
		) );

		if($args['need_access_token']){
			$response = $this->get_access_token();
			if(is_wp_error($response)){
				return $response;
			}else{
				$url = add_query_arg(array('access_token'=>$response['access_token']), $url);
				$url = str_replace('%40', '@', $url);  
			}
		}

		unset($args['need_access_token']);

		$response =  WPJAM_API::http_request($url, $args);

		if(is_wp_error($response)){

			$errcode	= $response->get_error_code();

			if($errcode == '40001' || $errcode == '40014' || $errcode == '42001'){
				// 40001 获取access_token时AppSecret错误，或者access_token无效。请开发者认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口
				// 40014 不合法的access_token，请开发者认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口
				// 42001 access_token超时，请检查access_token的有效期，请参考基础支持-获取access_token中，对access_token的详细机制说明
				wp_cache_delete('weixin_access_token', $this->appid);
			}elseif($errcode == '50002'){	
				// 50002 用户受限，可能是违规后接口被封禁
				// $robot_option = wpjam_get_option('weixin-robot');
				// $robot_option['weixin_type'] = -1;
				// update_option('weixin-robot', $robot_option);
			}
		}

		return $response;
	}

	public function get_access_token($force=false){
		$response = wp_cache_get('weixin_access_token', $this->appid);

		if ($response === false || $force) {
			$response = $this->http_request("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->secret, array('need_access_token'=>false));

			if(is_wp_error($response)){
				return $response;
			}

			$response['expires_in']	= time()+$response['expires_in']-600;

			wp_cache_set('weixin_access_token', $response, $this->appid, $response['expires_in']);
		}

		return $response;
	}

	public function get_current_autoreply_info(){
		return  $this->http_request('https://api.weixin.qq.com/cgi-bin/get_current_autoreply_info');
	}

	public function get_current_selfmenu_info(){
		return  $this->http_request('https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info');
	}

	public function clear_quota(){
		$last_clear_quota = wp_cache_get('weixin_clear_quota', $this->appid);
		if($last_clear_quota === false){
			wp_cache_set( 'weixin_clear_quota', 1, $this->appid, HOUR_IN_SECONDS);
			
			$response	= $this->http_request('https://api.weixin.qq.com/cgi-bin/clear_quota', array(
				'method'	=> 'POST',
				'body'		=> array('appid'=>$this->appid)
			));

			return $response;
		}else{
			return new WP_Error('-1', '一小时内你刚刚清理过');
		}
	}

	// 获取微信短连接
	public function shorturl($long_url){
		$response	= $this->http_request('https://api.weixin.qq.com/cgi-bin/shorturl', array(
			'method'	=> 'POST',
			'body'		=> array('action'=>'long2short', 'long_url'=>$long_url)
		));

		if(is_wp_error($response)){
			return $response;
		}

		return $response['short_url'];
	}

	// 获取获取微信服务器IP地址
	public function get_callback_ip(){
		$ip_list	= wp_cache_get('ip_list', $this->appid);

		if($ip_list === false){
			$response	= $this->http_request('https://api.weixin.qq.com/cgi-bin/getcallbackip');

			if(is_wp_error($response)){
				return $response;
			}

			$ip_list = $response['ip_list'];
			wp_cache_set('ip_list', $ip_list, $this->appid, DAY_IN_SECONDS);
		}
		return $ip_list;
	}

	// 语义查询
	public function semantic_search($query, $category, $uid='', $location=array()){
		$appid 	= $this->appid;

		extract(wp_parse_args( $location, array(
			'latitude'	=> '',
			'longitude'	=> '',
			'city'		=> '',
			'region'	=> ''
		)));

		$response	= $this->http_request('https://api.weixin.qq.com/semantic/semproxy/search', array(
			'method'	=> 'POST',
			'body'		=> compact('query', 'category', 'appid', 'uid', 'latitude', 'longitude', 'city', 'region')
		));

		if(is_wp_error($response)){
			return $response;
		}

		return $response['semantic'];
	}
}
