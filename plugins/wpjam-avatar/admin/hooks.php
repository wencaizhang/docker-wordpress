<?php
add_action('admin_init', function(){
	if(current_user_can('publish_posts')){
		return; 
	}

	if(current_user_can('publish_posts')){
		return; 
	}
	
	if(!current_user_can('upload_files')){
		$current_user	= wp_get_current_user();

		if(isset($current_user->roles)){
			foreach ($current_user->roles as $role_name) {
				$role	= get_role($role_name);
				if(!$role->has_cap('upload_files')){
					$role->add_cap('upload_files');
				}
			}
		}
	}

	add_filter('upload_mimes', function($mimes){
		foreach ($mimes as $ext => $mime) {
			$mime = explode('/', $mime);
			if($mime[0] != 'image'){
				unset($mimes[$ext]);
			}
		}

		return $mimes;
	});

	add_action('pre_get_posts', function($wp_query){
		if(isset($wp_query->query['post_type']) && $wp_query->query['post_type'] === 'attachment'){
			$wp_query->set('author', get_current_user_id());
		}
	});
});

add_action('admin_menu', function(){
	if(current_user_can('publish_posts')){
		return; 
	}
	
	remove_menu_page('upload.php');
});
	

add_action( 'admin_print_footer_scripts', function(){
	remove_action('admin_print_footer_scripts', 'options_discussion_add_js');
	global $pagenow;

	if($pagenow == 'options-discussion.php'){
	?>
	<script type="text/javascript">
	(function($){
		$('.avatar-settings').remove();
	})(jQuery);

	</script>
	<?php }
},1);