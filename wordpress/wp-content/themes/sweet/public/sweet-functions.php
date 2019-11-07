<?php
if( wpjam_get_setting('wpjam_theme', 'foot_link') ) {
	add_filter('pre_option_link_manager_enabled', '__return_true');	/*激活友情链接后台*/
}

//禁止代码标点转换
remove_filter('the_content', 'wptexturize');

//载入JS\CSS
add_action('wp_enqueue_scripts', function () {
	if (!is_admin()) {
		
		wp_enqueue_style('style', get_stylesheet_directory_uri().'/static/css/style.css');

		wp_enqueue_script('sweet',	get_stylesheet_directory_uri() . '/static/js/sweet.js', ['jquery'], '', true);

		if (is_singular() && comments_open() && get_option('thread_comments')){
			wp_enqueue_script( 'comment-reply' );
		}

	}	
});

// //删除菜单多余css class
// function wpjam_css_attributes_filter($classes) {
// 	return is_array($classes) ? array_intersect($classes, array('current-menu-item','current-post-ancestor','current-menu-ancestor','current-menu-parent','menu-item-has-children','menu-item')) : '';
// }
// add_filter('nav_menu_css_class',	'wpjam_css_attributes_filter', 100, 1);
// add_filter('nav_menu_item_id',		'wpjam_css_attributes_filter', 100, 1);
// add_filter('page_css_class', 		'wpjam_css_attributes_filter', 100, 1);

//删除wordpress默认相册样式
add_filter( 'use_default_gallery_style', '__return_false' );

//默认文章缩略图
add_filter('wpjam_default_thumbnail_uri',function ($default_thumbnail){
	$default_thumbnails	= wpjam_get_setting('wpjam_theme', 'thumbnails');

	if($default_thumbnails){
		shuffle($default_thumbnails);
		return $default_thumbnails[0];
	}else{
		return $default_thumbnail;
	}
},99);

add_filter('default_option_wpjam-cdn', function($default){
	return [
		'term_thumbnail_type'		=> 'img',
		'term_thumbnail_taxonomies'	=> ['category']
	];
});


add_filter('option_wpjam-cdn', function($value){
	$value['term_thumbnail_type']		= 'img';
	$value['term_thumbnail_taxonomies']	= ['category'];
	$value['term_thumbnail_width']		= 0;
	$value['term_thumbnail_height']		= 0;

	return $value;
});

$wpjam_extends	= get_option('wpjam-extends');
if($wpjam_extends){
	$wpjam_extends_updated	= false;
	if(!empty($wpjam_extends['related-posts.php'])){
		unset($wpjam_extends['related-posts.php']);
		$wpjam_extends_updated	= true;
	}

	if(!empty($wpjam_extends['wpjam-postviews.php'])){
		unset($wpjam_extends['wpjam-postviews.php']);
		$wpjam_extends_updated	= true;
	}

	if(!empty($wpjam_extends['mobile-theme.php'])){
		unset($wpjam_extends['mobile-theme.php']);
		$wpjam_extends_updated	= true;
	}

	if($wpjam_extends_updated){
		update_option('wpjam-extends', $wpjam_extends);
	}
}

/* 评论作者链接新窗口打开 */
add_filter('get_comment_author_link', function () {
	$url	= get_comment_author_url();
	$author = get_comment_author();
	if ( empty( $url ) || 'http://' == $url ){
		return $author;
	}else{
		return "<a target='_blank' href='$url' rel='external nofollow' class='url'>$author</a>";
	}
});

//文章自动nofollow
add_filter( 'the_content', function ( $content ) {
	//fancybox3图片添加 data-fancybox
	global $post;
	$pattern = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|swf)('|\")(.*?)>(.*?)<\/a>/i";
	$replacement = '<a$1href=$2$3.$4$5 data-fancybox="images" $6>$7</a>';
	$content = preg_replace($pattern, $replacement, $content);
	$content = str_replace(']]>', ']]>', $content);
	return $content;
});

add_filter('the_excerpt',function($post_excerpt){
	global $post;
	if($post_abstract = get_post_meta($post->ID, 'post_abstract', true)){
		return mb_strimwidth($post_abstract, 0, 350, '...');
	}else{
		return get_post_excerpt($post, 350);
	}
});

//禁止FEED
if( wpjam_get_setting('wpjam_theme', 'xintheme_feed') ) {
	function wpjam_disable_feed() {
		wp_die(__('<h1>Feed已经关闭, 请访问网站<a href="'.get_bloginfo('url').'">首页</a>!</h1>'));
	}

	add_action('do_feed',		'wpjam_disable_feed', 1);
	add_action('do_feed_rdf',	'wpjam_disable_feed', 1);
	add_action('do_feed_rss',	'wpjam_disable_feed', 1);
	add_action('do_feed_rss2',	'wpjam_disable_feed', 1);
	add_action('do_feed_atom',	'wpjam_disable_feed', 1);
}

//使用v2ex镜像avatar头像
if( wpjam_get_setting('wpjam_theme', 'xintheme_v2ex') ) {
	add_filter('get_avatar_url', function ($avatar, $id_or_email, $size){
		return str_replace(['cn.gravatar.com/avatar', 'secure.gravatar.com/avatar', '0.gravatar.com/avatar', '1.gravatar.com/avatar', '2.gravatar.com/avatar'], 'cdn.v2ex.com/gravatar', $avatar);
	}, 10, 3 );
}

add_action('wp_head', function (){
	if($favicon = wpjam_theme_get_setting('favicon')){
		echo '<link href="'.$favicon.'" rel="shortcut icon" type="image/x-icon">';
	}
	if($apple_icon = wpjam_theme_get_setting('apple-icon')){
		echo '<link href="'.$apple_icon.'" rel="apple-touch-icon">';
	}

	if(is_singular()) { 
		global $post;
		wpjam_update_post_views($post->ID);
	}
}); 

add_action('pre_get_posts', function($wp_query) {
	if($wp_query->is_main_query()){
		if(is_home()){
			$wp_query->set('ignore_sticky_posts', true);
		}elseif(is_author()){
			$wp_query->set('posts_per_page', 10);
		}
	}
	
});

/* 搜索关键词为空 */
add_filter( 'request', function ( $query_variables ) {
	if (isset($_GET['s']) && !is_admin()) {
		if (empty($_GET['s']) || ctype_space($_GET['s'])) {
			wp_redirect( home_url() );
			exit;
		}
	}
	return $query_variables;
});

function wpjam_theme_get_bg_img($post=null, $width=1000){
	// $default_bg_img	= wpjam_theme_get_setting('bg_img');
	// $default_bg_img	= $default_bg_img ? wpjam_get_thumbnail($default_bg_img, [$width]) : '';

	if(is_singular('post') || $post){
		$bg_img	= wpjam_get_post_thumbnail_url(null, [$width]);
		// $bg_img ?: $default_bg_img;
	}else{
		$bg_img	= '';

		if(is_category()){
			$bg_img	= wpjam_get_term_thumbnail_url(null, [$width]);
		}

		// $bg_img	= $bg_img ?: $default_bg_img;

		if(empty($bg_img)){
			global $post;
			$bg_img	= wpjam_get_post_thumbnail_url($post, [$width]);
		}
	}
	
	return $bg_img;
}



/**
 * 获取随机颜色
 * @return mixed
 */
function get_random_color() {
	$colors  = array(
		'linear-gradient(90deg,#9c4dff 0,#42a7ff 100%)',
		'linear-gradient(to right,#4a00e0,#8e2de2)',
		'linear-gradient(90deg, #8466ff 0%, #8466ff 100%)'
	);

	shuffle($colors);
	return $colors[0];
}

add_filter('category_description', function ($description) {
	return trim(wp_strip_all_tags($description));
});