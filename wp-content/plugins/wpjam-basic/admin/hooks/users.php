<?php
function wpjam_user_row_actions($actions, $user){
	$capability	= (is_multisite())?'manage_site':'manage_options';
	if(current_user_can($capability) && !is_network_admin()){
		$actions['login_as']	= '<a title="以此身份登陆" href="'.wp_nonce_url("users.php?action=login_as&amp;users=$user->ID", 'bulk-users').'">以此身份登陆</a>';
	}

	$actions['user_id'] = 'ID: '.$user->ID;
	
	return $actions;
}
add_filter('user_row_actions',		'wpjam_user_row_actions', 999, 2);
add_filter('ms_user_row_actions',	'wpjam_user_row_actions',10,2);

add_filter('handle_bulk_actions-users', function($sendback, $action, $user_ids){
	if($action == 'login_as'){
		wp_set_auth_cookie($user_ids, true);
		wp_set_current_user($user_ids);
	}
	return admin_url();
},10,3);

//添加用户注册时间和其他字段
add_filter('manage_users_columns', function($columns){
	if(wpjam_basic_get_setting('simplify_user')){
		unset($columns['name']);  //隐藏姓名
		unset($columns['email']);  //隐藏姓名
		unset($columns['posts']);  //隐藏姓名
		$columns['nickname']	= '昵称';
	}

	$columns['registered']	= '注册时间';
	return $columns;
});


//显示用户注册时间和其他字段
add_filter('manage_users_custom_column', function($value, $column, $user_id){
	if($column == 'registered'){
		$user = get_userdata($user_id);
		return get_date_from_gmt($user->user_registered);
	}elseif($column == 'nickname'){
		$user = get_userdata($user_id);
		return $user->display_name;
	}else{
		return $value;
	}
},11,3);

if(wpjam_basic_get_setting('order_by_registered')){
	//设置注册时间为可排序列.
	add_filter( "manage_users_sortable_columns", function($sortable_columns){
		$sortable_columns['registered'] = 'registered';
		return $sortable_columns;
	});

	//按注册时间排序.
	add_action('pre_user_query', function($query){
		if(!isset($_REQUEST['orderby'])){
			if( empty($_REQUEST['order']) || !in_array($_REQUEST['order'], ['asc','desc']) ){
				$_REQUEST['order'] = 'desc';
			}
			$query->query_orderby = "ORDER BY user_registered ".$_REQUEST['order'];
		}
	});
	
}

// 后台可以根据显示的名字来搜索用户 
add_filter('user_search_columns',function($search_columns){
	return ['ID', 'user_login', 'user_email', 'user_url', 'user_nicename', 'display_name'];
});

// add_filter('get_available_languages', '__return_empty_array');

if(wpjam_basic_get_setting('simplify_user')){
	//移除不必要的用户联系信息
	// add_filter('user_contactmethods', function ( $contactmethods ) {
	// 	unset($contactmethods['aim']);
	// 	unset($contactmethods['yim']);
	// 	unset($contactmethods['jabber']);
		
	// 	//也可以自己增加
	// 	//$contactmethods['user_mobile'] = '手机号码';
	// 	//$contactmethods['user_contact'] = '收货联系人';
	// 	//$contactmethods['user_address'] = '收货地址';

	// 	return $contactmethods;
	// }); 

	add_action('show_user_profile','wpjam_edit_user_profile');
	add_action('edit_user_profile','wpjam_edit_user_profile');
	function wpjam_edit_user_profile($user){
		?>
		<script>
		jQuery(document).ready(function($) {
			$('#first_name').parent().parent().hide();
			$('#last_name').parent().parent().hide();
			$('#display_name').parent().parent().hide();
			$('.user-email-wrap').parent().parent().prev('h2').hide();
			$('.user-email-wrap').parent().parent().hide();
			$('.user-description-wrap').parent().parent().prev('h2').hide();
			$('.user-description-wrap').parent().parent().hide();
			$('.show-admin-bar').hide();
		});
		</script>
	<?php
	}

	add_action('personal_options_update','wpjam_edit_user_profile_update');
	add_action('edit_user_profile_update','wpjam_edit_user_profile_update');
	function wpjam_edit_user_profile_update($user_id){
		if (!current_user_can('edit_user', $user_id))
			return false;

		$user = get_userdata($user_id);

		$_POST['nickname']		= ($_POST['nickname'])?:$user->user_login;
		$_POST['display_name']	= $_POST['nickname'];

		$_POST['first_name']	= '';
		$_POST['last_name']		= '';
	}
}

// add_action('user_register', function($user_id){
// 	$user = get_userdata($user_id);

// 	wp_update_user(array(
// 		'ID'			=> $user_id,
// 		'display_name'	=> $user->user_login
// 	)) ;

// });


// add_action('show_user_profile','wpjam_edit_user_avatar_profile');
// add_action('edit_user_profile','wpjam_edit_user_avatar_profile');
// function wpjam_edit_user_avatar_profile($profileuser){

// 	echo '<h3>自定义头像</h3>';

// 	wpjam_form_fields(array(
// 		'avatar'	=> array('title'=>'头像', 'type'=>'img', 'value'=>get_user_meta($profileuser->ID, 'avatar', true)),
// 	)); 
// }

// add_action('personal_options_update','wpjam_edit_user_avatar_profile_update');
// add_action('edit_user_profile_update','wpjam_edit_user_avatar_profile_update');
// function wpjam_edit_user_avatar_profile_update($user_id){
// 	if(!empty($_POST['avatar'])){
// 		update_user_meta( $user_id, 'avatar', $_POST['avatar'] );
// 	}else{
// 		if(get_user_meta( $user_id, 'avatar', true )){
// 			delete_user_meta( $user_id, 'avatar' );
// 		}
// 	}
// }


/* 在后台修改用户昵称的时候检查是否重复 */
// add_action('user_profile_update_errors', function ($errors, $update, $user){
// 	$check = wpjam_check_nickname($user->nickname,$user->ID);
	
// 	if(is_wp_error($check)){
// 		$errors->add( 'nickname_'.$check->get_error_code, '<strong>错误</strong>：'.$check->get_error_message(), array( 'form-field' => 'nickname' ) );
// 	}
	
// },10,3 );


// 检测用户名是合法标准
function wpjam_check_nickname($nickname, $user_id=0 ){
	if(!$nickname)
		return new WP_Error('empty', $nickname.' 为空');

	if(mb_strwidth($nickname)>20)
		return new WP_Error('too_long', $nickname.' 超过20个字符。');

	if(wpjam_blacklist_check($nickname))
		return new WP_Error('illegal', $nickname. '含有非法字符。');
	

	if($nickname != wpjam_get_validated_nickname($nickname))
		return new WP_Error('invalid', $nickname.' 非法，只能含有中文汉字、英文字母、数字、下划线、中划线和点。');

	if(wpjam_is_duplicate_nickname($nickname,$user_id)){
		return new WP_Error('duplicate', $nickname.' 已被人使用！');
	}

	return true;
}

// 检测用户名是否重复
function wpjam_is_duplicate_nickname($nickname, $user_id=0){
	$users	= get_users(array('blog_id'=>0,'meta_key'=>'nickname', 'meta_value'=>$nickname));
	if(count($users) > 1){
		return true;
	}elseif($users && $user_id != $users[0]->ID){
		return true;
	}

	$users	= get_users(array('blog_id'=>0,'login'=>$nickname));
	if(count($users) > 1){
		return true;
	}elseif($users && $user_id != $users[0]->ID){
		return true;
	}


	return false;
}

// 只能含有中文汉字、英文字母、数字、下划线、中划线和点。
function wpjam_get_validated_nickname($nickname){

	// $nickname	= remove_accents( $nickname );
	// $nickname	= preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $nickname);	// Kill octets
	// $nickname	= preg_replace('/&.+?;/', '', $nickname); // Kill entities
	
	//限制不能使用特殊的中文
	$nickname	= preg_replace('/[^A-Za-z0-9_.\-\x{4e00}-\x{9fa5}]/u', '', $nickname);
	
	$nickname	= trim($nickname);
	// Consolidate contiguous whitespace
	$nickname	= preg_replace('|\s+|', ' ', $nickname);
	
	return $nickname;
}