<?php
// 直接新增
add_action('save_post', function($post_id, $post, $update){
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
		return;

	if(!current_user_can('edit_post', $post_id)) 
		return;

	if(!$update && $post->post_status == 'publish'){

		// $baidu_zz	= $_POST['baidu_zz'] ?? false;

		// if($baidu_zz){
			$post_link	= apply_filters('baiduz_zz_post_link', get_permalink($post_id), $post_id);
			$update		= false;

			wpjam_notify_baidu_zz($post_link, $update);
			wpjam_notify_xzh($post_link, compact('update'));
		// }
	}
}, 10, 3);

// 修改文章
add_action('post_updated', function($post_id, $post_after, $post_before){
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
		return;

	if(!current_user_can('edit_post', $post_id)) 
		return;

	if($post_after->post_status == 'publish'){
		// $baidu_zz	= $_POST['baidu_zz'] ?? false;

		// if($baidu_zz){
			$post_link	= apply_filters('baiduz_zz_post_link', get_permalink($post_id), $post_id);
			$update		= false;

			if(wp_cache_get($post_id, 'wpjam_baidu_zz_notified') === false){
				wp_cache_set($post_id, true, 'wpjam_baidu_zz_notified', HOUR_IN_SECONDS);
				
				wpjam_notify_baidu_zz($post_link, $update);
				wpjam_notify_xzh($post_link, compact('update'));
			}
		// }
	}
}, 10, 3);

/*
add_action('post_submitbox_misc_actions', function (){ ?>
	<div class="misc-pub-section baidu-zz">

		<input type="checkbox" name="baidu_zz" id="baidu_zz" value="1">
		<label for="baidu_zz">提交给百度站长</label>

		

	</div>
<?php });
*/



add_action('wpjam_'.$post_type.'_posts_actions', function($actions){
	$actions['baidu-zz']	= ['title'=>'提交到百度站长', 'bulk'=>true,	'direct'=>true];
	return $actions;
});


add_filter('wpjam_'.$post_type.'_posts_list_action', function($result, $list_action, $post_id, $data){
	if($list_action == 'baidu-zz'){

		$urls	= '';

		if(is_array($post_id)){		
			$post_ids	= $post_id;
			// $posts		= wpjam_posts($post_ids);
			
			foreach ($post_ids as $post_id) {
				if(get_post($post_id)->post_status == 'publish'){
					if(wp_cache_get($post_id, 'wpjam_baidu_zz_notified') === false){
						wp_cache_set($post_id, true, 'wpjam_baidu_zz_notified', HOUR_IN_SECONDS);
						$urls	.= apply_filters('baiduz_zz_post_link', get_permalink($post_id))."\n";	
					}
				}
			}
		}else{
			if(get_post($post_id)->post_status == 'publish'){
				if(wp_cache_get($post_id, 'wpjam_baidu_zz_notified') === false){
					wp_cache_set($post_id, true, 'wpjam_baidu_zz_notified', HOUR_IN_SECONDS);
					$urls	.= apply_filters('baiduz_zz_post_link', get_permalink($post_id))."\n";	
				}else{
					return new WP_Error('has_submited', '一小时内已经提交过了');
				}
			}else{
				return new WP_Error('invalid_post_status', '未发布的文章不能同步到百度站长');
			}
		}

		if($urls){
			wpjam_notify_xzh($urls, true);
			wpjam_notify_baidu_zz($urls, true);
		}else{
			return new WP_Error('empty_urls', '没有需要提交的链接');
		}

		return true;
	}

	return $result;
}, 10, 4);
