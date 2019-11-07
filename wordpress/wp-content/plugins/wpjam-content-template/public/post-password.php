<?php
function wpjam_content_template_post_password_required($required, $post){
	if($required){
		return has_shortcode($post->post_content, 'password') ? false : $required;
	}else{
		return $required;
	}
}

add_filter('post_password_required', 'wpjam_content_template_post_password_required', 10, 2);

add_shortcode('password',  function($atts, $text=''){
	remove_filter('post_password_required', 'wpjam_content_template_post_password_required', 10, 2);

	if(post_password_required()){
		$text	= get_the_password_form();
	}else{
		global $post;
		$text	= $post->post_password ? wpautop($text) : '';
	}

	add_filter('post_password_required', 'wpjam_content_template_post_password_required', 10, 2);
	
	return $text;
});

add_filter('protected_title_format', function($protected_format, $post){
	return has_shortcode($post->post_content, 'password') ? '%s' : $protected_format;
}, 10, 2);



add_filter('the_password_form',	function($output){
	if(!defined('WEIXIN_ROBOT_PLUGIN_DIR')){
		return $output;
	}

	$weixin_qrcode = wpjam_get_setting('wpjam-content-template', 'weixin_qrcode');

	if(empty($weixin_qrcode)){
		return $output;
	}
	
	if(!preg_match('/<label for="pwbox-(.*?)">/i', $output, $match)){
		return $output;
	}

	$post_id	= $match[1];

	$qrcode	= wpjam_get_thumbnail($weixin_qrcode, '160x160');
	$qrcode	= '<img src="'.$weixin_qrcode.'" class="content-template-weixin-qrcode" width="80" height="80" />'; 

	$tip	= wpjam_get_setting('wpjam-content-template', 'weixin_tip');
	$tip	= $tip ?: '下面内容受密码保护，扫码关注公众号，回复「[keyword]」获取密码。';
	$tip	= str_replace('[keyword]', 'PP'.$post_id, $tip);

	$label	= 'pwbox-' . $post_id;

	$post_password_form = '<form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" class="post-password-form content-template-post-password-form" method="post">'.$qrcode.'<p>'.$tip.'</p>
	<label for="' . $label . '"><input name="post_password" id="' . $label . '" type="password" size="20" /></label>
	<input type="submit" name="Submit" value="' . esc_attr_x( 'Enter', 'post password form' ) . '" />
	</form>';

	if(get_post($post_id)->post_type == 'template'){
		return $post_password_form;
	}else{
		return '<div class="content-template post-password-content-template">'.$post_password_form.'</div>';
	}	
});

add_filter('weixin_builtin_reply', function ($weixin_builtin_replies){
	$weixin_builtin_replies['pp']	= ['type'=>'prefix', 'reply'=>'文章密码', 'function'=>'wpjam_post_password_reply'];
	return $weixin_builtin_replies;
});

function wpjam_post_password_reply($keyword){
	global $weixin_reply;

	$post_id	= str_replace('pp', '', $keyword);
	
	if(!$post_id){
		$weixin_reply->textReply('PP后面要跟上文章ID，比如：PP123。');
	}else{
		$post	= get_post($post_id);
		if($post){
			$reply	= wpjam_get_setting('wpjam-content-template', 'weixin_reply');
			$reply	= $reply ?: '密码是： [password]';
			$reply	= str_replace('[password]', $post->post_password, $reply);
			$weixin_reply->textReply($reply);
		}else{
			$weixin_reply->textReply('你查询的文章不存在，所以没密码。');	
		}
	}
	$weixin_reply->set_response('post_password');
}

add_action('wp_head', function(){
	if(defined('WEIXIN_ROBOT_PLUGIN_DIR')){
		if($weixin_style	= wpjam_get_setting('wpjam-content-template', 'weixin_style')){
			echo '<style type="text/css">'."\n".
			$weixin_style.	
			'</style>';
		}
	}
});