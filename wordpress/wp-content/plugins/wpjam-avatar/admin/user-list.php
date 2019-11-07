<?php
add_filter('user_row_actions',	function ($actions, $user){
	$capability	= is_multisite() ? 'manage_site' : 'manage_options';
	if(current_user_can($capability)){
		$actions['login_as']	= '<a title="以此身份登陆" href="'.wp_nonce_url("users.php?action=login_as&amp;users=$user->ID", 'bulk-users').'">以此身份登陆</a>';
	}
	
	return $actions;
}, 10, 2);

add_filter('handle_bulk_actions-users', function($sendback, $action, $user_ids){
	if($action == 'login_as'){
		wp_set_auth_cookie($user_ids, true);
		wp_set_current_user($user_ids);
	}
	return admin_url();
},10,3);

add_filter('ms_user_row_actions',	function($actions, $user){
	$actions['user_id'] = 'ID: '.$user->ID;	
	return $actions;
}, 999, 2);

add_filter('manage_users_columns', function($columns){
	unset($columns['name']);  //隐藏姓名
	unset($columns['email']); 
	// unset($columns['posts']);

	wpjam_array_push($columns, ['detail'=>'用户信息'], 'role');	
		
	return $columns;
});

add_filter('wpmu_users_columns', function($columns){
	unset($columns['name']);  //隐藏姓名
	unset($columns['email']); 
	// unset($columns['posts']);

	wpjam_array_push($columns, ['detail'=>'用户信息'], 'registered');	
		
	return $columns;
});

add_filter('manage_users_custom_column', function ($value, $column, $user_id){
	if($column == 'detail'){
		$user 	= get_userdata($user_id);
		if(is_network_admin()){
			$avatar	= get_avatar($user_id, 32);	
		}else{
			$avatar	= '';
		}
		
		return $avatar.apply_filters('wpjam_user_detail_column', '昵称：'.$user->display_name, $user_id);
	}else{
		return $value;
	}
}, 11, 3);

add_action('admin_head',function(){
	if(is_network_admin()){ ?>
	<style type="text/css">
	.fixed th.column-blogs{width: 224px;}
	.column-username img{display: none;}
	.column-detail img{float: left; margin-right: 10px; margin-top: 1px; }
	</style>
	<?php }
});