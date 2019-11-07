<?php
// 微信页面转发统计
add_action('wp_ajax_weixin_share', 'weixin_robot_share_ajax_action_callback');
add_action('wp_ajax_nopriv_weixin_share', 'weixin_robot_share_ajax_action_callback');
function weixin_robot_share_ajax_action_callback(){
	if(is_weixin()){
		check_ajax_referer( "weixin_nonce" );

		if(isset($_POST['sub_type'])){
			$data = weixin_robot_get_ajax_post_data();
			$data['type']		= 'Share';
			do_action('weixin_share', $data);
		}
	}
	exit;
}

function weixin_robot_get_ajax_post_data(){
	
	$data = array();
	$data['post_id']		= $_POST['post_id'];
	$data['sub_type']		= $_POST['sub_type'];
	$data['url']			= WEIXIN_User::remove_query_arg($_POST['link']);
	$data['refer']			= isset($_POST['refer'])?$_POST['refer']:'';
	$data['network_type']	= isset($_POST['network_type'])?$_POST['network_type']:'';
	$data['screen_width']	= isset($_POST['screen_width'])?$_POST['screen_width']:'';
	$data['screen_height']	= isset($_POST['screen_height'])?$_POST['screen_height']:'';
	$data['retina']			= isset($_POST['retina'])?$_POST['retina']:'';
	$data['ip']				= wpjam_get_ip();
	$data['ua']				= wpjam_get_ua();

	$openid	= WEIXIN_User::get_current_openid();

	if($data['refer'] && !is_wp_error($openid) && $data['refer']==$openid){
		$data['refer'] = '';	// 自己推荐自己就不要了。
	}

	return $data;
}


// 微信转发前端JS代码
add_action( 'wp_enqueue_scripts',  function () {
	wp_register_style('weui', '//res.wx.qq.com/open/libs/weui/0.4.3/weui.min.css');
	
	if(is_404() || !is_weixin())	return;

	$weixin_data	= weixin_robot_get_share_data();

	if(!is_wp_error($weixin_data)){
		wp_deregister_script('jquery');
		wp_register_script('jquery', '//res.wx.qq.com/open/libs/jquery/2.1.4/jquery.js', array(), '2.1.4' );	// 使用微信官方 jQuery 库
		
		wp_enqueue_script('jweixin', '//res.wx.qq.com/open/js/jweixin-1.0.0.js', array('jquery') );
		wp_enqueue_script('weixin', WEIXIN_ROBOT_PLUGIN_URL.'/template/static/weixin7.js', array('jweixin', 'jquery') );
		
		wpjam_localize_script('weixin', 'weixin_data', $weixin_data);
	}
});


function weixin_robot_get_share_data(){
	$weixin_js_api_ticket	= weixin()->get_js_api_ticket();

	if(is_wp_error($weixin_js_api_ticket)){
		return $weixin_js_api_ticket;
	}

	$js_api_ticket	= $weixin_js_api_ticket['ticket'];

	$url			= apply_filters('weixin_current_url', wpjam_get_current_page_url());
	$timestamp		= time();
	$nonce_str		= wp_generate_password(16, $special_chars = false);
	$signature		= sha1("jsapi_ticket=$js_api_ticket&noncestr=$nonce_str&timestamp=$timestamp&url=$url");
	
	$size		= array(120,120);
	if(is_singular()){
		$img	= wpjam_get_post_thumbnail_url('',$size);
		$title	= get_the_title();
		$desc	= get_post_excerpt();	
		$post_id= get_the_ID();	
	}else{
		$img 	= wpjam_get_default_thumbnail_url($size);
		if($title	= wp_title('',false)){
			$title	= wp_title('',false);
		}else{
			$title	= get_bloginfo('name');
		}
		$desc	= '';
		$post_id= 0;
	}

	// 转发 hook，用于插件修改
	$link	= apply_filters('weixin_share_url',	WEIXIN_User::get_current_page_url());
		
	$openid	= WEIXIN_User::get_current_openid();
	if(!is_wp_error($openid)){
		$link	= add_query_arg( array('weixin_refer' => $openid), $link );
	}

	$weixin_setting	= weixin_get_setting();

	return array(
		'appid' 			=> weixin_get_appid(),
		'debug' 			=> apply_filters('weixin_jssdk_debug',		false),
		'timestamp'			=> $timestamp,
		'nonce_str'			=> $nonce_str,
		'signature'			=> $signature,

		'img'				=> apply_filters('weixin_share_img',		$img),
		'link'				=> $link,
		'title'				=> apply_filters('weixin_share_title',		$title),
		'desc'				=> apply_filters('weixin_share_desc',		$desc),
		'post_id'			=> $post_id,

		'refresh_url'		=> apply_filters('weixin_refresh_url',		'', $link),
		'notify'			=> apply_filters('weixin_share_notify',		$weixin_setting['weixin_share_notify']??'')?1:0,
		'content_wrap'		=> apply_filters('weixin_content_wrap',		$weixin_setting['weixin_content_wrap']??''),
		'hide_option_menu'	=> apply_filters('weixin_hide_option_menu',	$weixin_setting['weixin_hide_option_menu']??'')?1:0,

		'ajax_url'			=> apply_filters('weixin_share_ajax_url',	admin_url('admin-ajax.php')),
		'nonce'				=> wp_create_nonce('weixin_nonce'),
		'jsApiList'			=> apply_filters('weixin_share_js_api_list', array(
			'checkJsApi', 
			'onMenuShareTimeline', 
			'onMenuShareAppMessage', 
			'onMenuShareQQ', 
			'onMenuShareWeibo', 
			'onMenuShareQZone',
			'getNetworkType',
			'previewImage',
			'hideOptionMenu',
			'showOptionMenu',
			'hideMenuItems',
			'showMenuItems',
			'hideAllNonBaseMenuItem',
			'showAllNonBaseMenuItem',
			'closeWindow',
		)),
	);
}



function weixin_robot_get_share_types(){
	return array(
		'ShareAppMessage'	=> '发送给朋友',
		'ShareTimeline'		=> '分享到朋友圈',
		'ShareQQ'			=> '分享到QQ',
		'ShareWeibo'		=> '分享到微博',
		'favorite'			=> '收藏',
		'connector'			=> '分享到第三方',
	);
}

function weixin_robot_get_source_types(){
	return array(
		'direct'		=> '直接访问',
		'timeline'		=> '来自朋友圈',
		'groupmessage'	=> '来自微信群',
		'singlemessage'	=> '来自好友分享'
	);
}

function weixin_robot_get_network_types(){
	return array(
		'network_type:wifi'	=> 'wifi网络',
		'network_type:edge'	=> '非wifi,包含3G/2G',
		'network_type:fail'	=> '网络断开连接',
		'network_type:wwan'	=> '2g或者3g'
	);
}
