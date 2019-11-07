<?php
do_action('wpjam_users_list_page_file');

add_filter('user_row_actions',	function($actions, $user){
	$actions['user_id'] = 'ID: '.$user->ID;	
	return $actions;
}, 999, 2);

// 后台可以根据显示的名字来搜索用户 
add_filter('user_search_columns',function($search_columns){
	return ['ID', 'user_login', 'user_email', 'user_url', 'user_nicename', 'display_name'];
});

add_action('admin_head',function(){
	?>
	<style type="text/css">
		.fixed th.column-role{width: 84px;}
		.fixed th.column-registered{width: 140px;}
	</style>
	<?php
});

if(wpjam_basic_get_setting('order_by_registered')){
	//显示用户注册时间
	add_filter('manage_users_columns', function($columns){
		$columns['registered']	= '注册时间';	
		return $columns;
	});

	add_filter('manage_users_custom_column', function($value, $column, $user_id){
		if($column == 'registered'){
			$user = get_userdata($user_id);
			return get_date_from_gmt($user->user_registered);
		}else{
			return $value;
		}
	}, 11, 3);

	//设置注册时间为可排序列.
	add_filter( "manage_users_sortable_columns", function($sortable_columns){
		$sortable_columns['registered'] = 'registered';
		return $sortable_columns;
	});

	//默认按注册时间排序.
	add_action('pre_user_query', function($query){
		if(!isset($_REQUEST['orderby'])){
			if( empty($_REQUEST['order']) || !in_array($_REQUEST['order'], ['asc','desc']) ){
				$_REQUEST['order'] = 'desc';
			}
			$query->query_orderby = "ORDER BY user_registered ".$_REQUEST['order'];
		}
	});
}