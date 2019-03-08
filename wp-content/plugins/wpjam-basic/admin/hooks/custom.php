<?php
if(wpjam_basic_get_setting('disable_auto_update')){  
	remove_action('admin_init', '_maybe_update_core');
	remove_action('admin_init', '_maybe_update_plugins');
	remove_action('admin_init', '_maybe_update_themes');
}

add_action('admin_head', function(){
	remove_action('admin_bar_menu', 'wp_admin_bar_wp_menu', 10);
	
	add_action('admin_bar_menu', function ($wp_admin_bar){
		if(wpjam_basic_get_setting('admin_logo')){
			$title 	= '<img src="'.wpjam_get_thumbnail(wpjam_basic_get_setting('admin_logo'),40,40).'" style="height:20px; padding:6px 0">';
		}else{
			$title	= '<span class="ab-icon"></span>';
		}
		$wp_admin_bar->add_menu( array(
			'id'    => 'wp-logo',
			'title' => $title,
			'href'  => self_admin_url(),
			'meta'  => array(
				'title' => __('About'),
			),
		) );
	});

	echo wpjam_basic_get_setting('admin_head');

	if(wpjam_basic_get_setting('favicon')){ 
		echo '<link rel="shortcut icon" href="'.wpjam_basic_get_setting('favicon').'">';
	}
});

// 修改 WordPress Admin text
add_filter('admin_footer_text', function($text){
	if(wpjam_basic_get_setting('admin_footer')){
		return wpjam_basic_get_setting('admin_footer');
	}
	return $text;
});

//给页面添加摘要
// add_action('add_meta_boxes', function($post_type, $post) {
// 	if($post_type == 'page'){
// 		add_meta_box( 'postexcerpt', __('Excerpt'), 'post_excerpt_meta_box', 'page', 'normal', 'core' );
// 	}
// }, 10, 2);


if(wpjam_basic_get_setting('diable_revision')){
	add_action('wp_print_scripts',function() {
		wp_deregister_script('autosave');
	});
}

if(wpjam_basic_get_setting('diable_block_editor')){
	add_filter('use_block_editor_for_post_type', '__return_false');
}

if(wpjam_basic_get_setting('disable_privacy')){
	add_action('admin_menu', function (){

		global $menu, $submenu;

		unset($submenu['options-general.php'][45]);

		// Bookmark hooks.
		remove_action( 'admin_page_access_denied', 'wp_link_manager_disabled_message' );

		// Privacy tools
		remove_action( 'admin_menu', '_wp_privacy_hook_requests_page' );
		// Privacy hooks
		remove_filter( 'wp_privacy_personal_data_erasure_page', 'wp_privacy_process_personal_data_erasure_page', 10, 5 );
		remove_filter( 'wp_privacy_personal_data_export_page', 'wp_privacy_process_personal_data_export_page', 10, 7 );
		remove_filter( 'wp_privacy_personal_data_export_file', 'wp_privacy_generate_personal_data_export_file', 10 );
		remove_filter( 'wp_privacy_personal_data_erased', '_wp_privacy_send_erasure_fulfillment_notification', 10 );

		// Privacy policy text changes check.
		remove_action( 'admin_init', array( 'WP_Privacy_Policy_Content', 'text_change_check' ), 100 );

		// Show a "postbox" with the text suggestions for a privacy policy.
		remove_action( 'edit_form_after_title', array( 'WP_Privacy_Policy_Content', 'notice' ) );

		// Add the suggested policy text from WordPress.
		remove_action( 'admin_init', array( 'WP_Privacy_Policy_Content', 'add_suggested_content' ), 1 );

		// Update the cached policy info when the policy page is updated.
		remove_action( 'post_updated', array( 'WP_Privacy_Policy_Content', '_policy_page_updated' ) );
	},9);
}


// 屏蔽后台功能提示
// if(wpjam_basic_get_setting('disable_update')){
// 	add_filter ('pre_site_transient_update_core', '__return_null');

// 	remove_action ('load-update-core.php', 'wp_update_plugins');
// 	add_filter ('pre_site_transient_update_plugins', '__return_null');

// 	remove_action ('load-update-core.php', 'wp_update_themes');
// 	add_filter ('pre_site_transient_update_themes', '__return_null');
// }

// 移除 Google Fonts
// if(wpjam_basic_get_setting('disable_google_fonts')){
// 	//add_filter( 'gettext_with_context', 'wpjam_disable_google_fonts', 888, 4);
// 	function wpjam_disable_google_fonts($translations, $text, $context, $domain ) {
// 		$google_fonts_contexts = array('Open Sans font: on or off','Lato font: on or off','Source Sans Pro font: on or off','Bitter font: on or off');
// 		if( $text == 'on' && in_array($context, $google_fonts_contexts ) ){
// 			$translations = 'off';
// 		}

// 		return $translations;
// 	}
// }

add_action('admin_init', function(){
	// 显示留言 ID
	add_filter('comment_row_actions',function ($actions, $comment){
		$actions['comment_id'] = 'ID：'.$comment->comment_ID;
		return $actions;
	},10,2);

	// remove_action( 'admin_notices', 'maintenance_nag' );
	// remove_action( 'network_admin_notices', 'maintenance_nag' );
}, 99);


add_filter('wpjam_post_options', function ($wpjam_options){
	if(wpjam_basic_get_setting('custom_footer')){
		$wpjam_options['wpjam_custom_footer_box'] = [
			'title'		=> '文章底部代码',	
			'fields'	=> [
				'custom_footer'	=>['title'=>'',	'type'=>'textarea', 'description'=>'自定义文章 Footer 代码可以让你在当前文章插入独有的 JS，CSS，iFrame 等类型的代码，让你可以对具体一篇文章设置不同样式和功能，展示不同的内容。']
			]
		];
	}

	return $wpjam_options;
});

add_filter('wpjam_term_options', function($term_options){
	$term_thumbnail_type		= wpjam_cdn_get_setting('term_thumbnail_type') ?: '';
	$term_thumbnail_taxonomies	= wpjam_cdn_get_setting('term_thumbnail_taxonomies') ?: [];

	if($term_thumbnail_type && $term_thumbnail_taxonomies){
		$term_options['thumbnail'] = [
			'title'				=> '缩略图', 
			'taxonomies'		=> $term_thumbnail_taxonomies, 
			'show_admin_column'	=> true,	
			'column_callback'	=> function($term_id){
				return wpjam_get_term_thumbnail($term_id, [50,50]);
			}
		];

		if($term_thumbnail_type == 'img'){
			$width	= wpjam_cdn_get_setting('term_thumbnail_width') ?: 200;
			$height	= wpjam_cdn_get_setting('term_thumbnail_height') ?: 200;

			$term_options['thumbnail']['type']			= 'img';
			$term_options['thumbnail']['item_type']		= 'url';
			$term_options['thumbnail']['size']			= $width.'x'.$height;
			$term_options['thumbnail']['description']	= '尺寸：'.$width.'x'.$height;

		}else{
			$term_options['thumbnail']['type']	= 'image';
		}
	}

	return $term_options;
});

if(wpjam_basic_get_setting('timestamp_file_name')){
	add_filter('wp_handle_upload_prefilter', function($file){	// 防止重名造成大量的 SQL 请求
		if(strlen($file['name'])<=15){
			$file['name']	= time().'-'.$file['name'];
		}
		return $file;
	});
}

add_action('wp_loaded', function (){
	if(CDN_NAME == '')
		return;

	add_filter('pre_option_thumbnail_size_w',	'__return_zero');
	add_filter('pre_option_thumbnail_size_h',	'__return_zero');
	add_filter('pre_option_medium_size_w',		'__return_zero');
	add_filter('pre_option_medium_size_h',		'__return_zero');
	add_filter('pre_option_large_size_w',		'__return_zero');
	add_filter('pre_option_large_size_h',		'__return_zero');

	add_filter('intermediate_image_sizes_advanced', function($sizes){
		if(isset($sizes['full'])){
			return ['full'=>$sizes['full']];
		}else{
			return [];
		}
	});

	add_filter('image_size_names_choose', function($sizes){
		if(isset($sizes['full'])){
			return ['full'=>$sizes['full']];
		}else{
			return [];
		}
	});

	add_filter('upload_dir', function($uploads){
		$uploads['url']		= wpjam_cdn_replace_local_hosts($uploads['url']);
		$uploads['baseurl']	= wpjam_cdn_replace_local_hosts($uploads['baseurl']);
		return $uploads;
	});

	add_filter('wp_calculate_image_srcset_meta', '__return_empty_array');
	// add_filter('image_downsize', '__return_true');

	add_filter('wp_prepare_attachment_for_js', function($response, $attachment, $meta){
		$meta = wp_get_attachment_metadata( $attachment->ID );
		if ( false !== strpos( $attachment->post_mime_type, '/' ) )
			list( $type, $subtype ) = explode( '/', $attachment->post_mime_type );
		else
			list( $type, $subtype ) = array( $attachment->post_mime_type, '' );

		if ( $meta && ( 'image' === $type || ! empty( $meta['sizes'] ) ) ) {
			if(isset($response['sizes'])){
				$url			= $response['sizes']['full']['url'];	
				$width			= $response['sizes']['full']['width'];
				$height			= $response['sizes']['full']['height'];
				$orientation	= $response['sizes']['full']['orientation'];

				foreach (['thumbnail', 'medium', 'medium_large', 'large'] as $s) {
					$size	= wpjam_parse_size($s);

					if($size['width'] < $width || $size['height'] < $height){
						$thumbnail_url	= wpjam_get_thumbnail($url, $s);
					}else{
						$thumbnail_url	= $url;
					}

					$thumbnail_width		= 0;
					$thumbnail_height		= 0;
					$thumbnail_orientation	= '';

					if($size['width']){
						if($size['width'] < $width){
							$thumbnail_width	= $size['width']; 
						}else{
							$thumbnail_width	= $width;
						}
					}else{
						$thumbnail_orientation	= $orientation;
					}

					if($size['height']){
						if($size['height'] < $height){
							$thumbnail_height	= $size['height']; 
						}else{
							$thumbnail_height	= $height;
						}
					}else{
						$thumbnail_orientation	= $orientation;
					}

					$thumbnail_orientation = $thumbnail_orientation ?: ($thumbnail_height > $thumbnail_width ? 'portrait' : 'landscape');

					$response['sizes'][$s]	= array(
						'url'			=> $thumbnail_url,
						'width'			=> $thumbnail_width,
						'height'		=> $thumbnail_height,
						'orientation'	=> $thumbnail_orientation
					);
				}
			}
		}

		return $response;
	}, 10, 3);

	// wp_get_attachment_image_src(271);
}, 11);
