<?php
// Dashboard Widget
add_action('wp_dashboard_setup',  function(){
	remove_meta_box('dashboard_primary', get_current_screen(), 'side');

	add_action('pre_get_comments', function($query){
		$query->query_vars['type']	= 'comment';
	});
		
	if(is_multisite()){
		$user_id = get_current_user_id();
		
		if(!in_array( get_current_blog_id(), array_keys(get_blogs_of_user($user_id)))){
			remove_meta_box('dashboard_quick_press', get_current_screen(), 'side');
		}
	}

	
	$dashboard_widgets	= [];

	$dashboard_widgets['wpjam_update']	= [
		'title'		=> 'WordPress资讯及技巧',
		'context'	=> 'side',	// 位置，normal 左侧, side 右侧
	];

	if($dashboard_widgets	= apply_filters('wpjam_dashboard_widgets', $dashboard_widgets)){
		wpjam_admin_add_dashboard_widgets($dashboard_widgets);
	}
}, 1);


// add_action('activity_box_end', function(){
// 	echo '<span class="dashicons dashicons-megaphone"></span> Sweet主题升级到 1.5。';
// });

// add_filter('dashboard_glance_items', function($elements){
// 	$elements[]	= '<a><span class="dashicons dashicons-megaphone"></span> Sweet主题升级到 1.5。</a>';
// 	return $elements;
// });

add_filter('dashboard_recent_posts_query_args', function($query_args){
	$query_args['post_type']	= 'any';
	// $query_args['posts_per_page']	= 10;

	return $query_args;
});

add_filter('dashboard_recent_drafts_query_args', function($query_args){
	$query_args['post_type']	= 'any';

	return $query_args;
});

function wpjam_admin_add_dashboard_widgets($dashboard_widgets){
	
	foreach ($dashboard_widgets as $widget_id => $meta_box) {
		$meta_box = wp_parse_args($meta_box, array(
			'title'		=> '',
			'callback'	=> wpjam_get_filter_name($widget_id,'dashboard_widget_callback'),
			'control'	=> null,
			'args'		=> [],
			'context'	=> 'normal',	// 位置，normal 左侧, side 右侧
			'priority'	=> 'core'
		));
		
		add_meta_box($widget_id, $meta_box['title'], $meta_box['callback'], get_current_screen(), $meta_box['context'], $meta_box['priority'], $meta_box['args']);
	}
}

function wpjam_admin_dashboard_page($page_setting=[]){
	global $plugin_page, $current_tab;

	require_once(ABSPATH . 'wp-admin/includes/dashboard.php');
	
	// wp_dashboard_setup();

	$dashboard_widgets	= $page_setting['widgets'] ?? [];
	$dashboard_widgets	= apply_filters(wpjam_get_filter_name($plugin_page,'dashboard_widgets'), $dashboard_widgets);

	if($dashboard_widgets){
		wpjam_admin_add_dashboard_widgets($dashboard_widgets);
	}

	wp_enqueue_script('dashboard');
	
	if(wp_is_mobile()) {
		wp_enqueue_script('jquery-touch-punch');
	}

	$filter_name	= wpjam_get_filter_name($plugin_page, 'welcome_panel');
	
	if(has_action($filter_name)){

		echo '<div id="welcome-panel" class="welcome-panel">';
		do_action($filter_name);
		echo '</div>';
		
	}else{

		$page_title	= $page_setting['page_title'] ?? ($page_setting['title'] ?? '');
		$summary	= $page_setting['summary']??'';

		if($page_title){
			if(!empty($current_tab)){
				echo '<h2>'.$page_title.'</h2>';	
			}else{
				echo '<h1>'.$page_title.'</h1>';
			}
		}

		if($summary){
			echo wpautop($summary);
		}

	} 

	echo '<div id="dashboard-widgets-wrap">';
	wp_dashboard();
	echo '</div>';
}

function wpjam_update_dashboard_widget_callback(){
	?>
	<style type="text/css">
		#dashboard_wpjam .inside{margin:0; padding:0;}
		a.jam-post {border-bottom:1px solid #eee; margin: 0 !important; padding:6px 0; display: block; }
		a.jam-post:last-child{border-bottom: 0;}
		a.jam-post p{display: table-row; }
		a.jam-post img{display: table-cell; width:40px; height: 40px; margin:4px 12px; }
		a.jam-post span{display: table-cell; height: 40px; vertical-align: middle;}
	</style>
	<div class="rss-widget">
	<?php 

	$api = 'http://jam.wpweixin.com/api/post/list.json';

	$jam_posts = get_transient('dashboard_jam_posts');

	if($jam_posts === false){
		$response	= wpjam_remote_request($api);

		if(!is_wp_error($response)){
			$jam_posts	= $response['posts'];
			set_transient('dashboard_jam_posts', $jam_posts, 12 * HOUR_IN_SECONDS );
		}

	}
	if($jam_posts){
		// wpjam_print_r($jam_posts);

		$i = 0;
		foreach ($jam_posts as $jam_post){
			if($i == 5) break;
			echo '<a class="jam-post" target="_blank" href="http://blog.wpjam.com'.$jam_post['post_url'].'"><p>'.'<img src="'.str_replace('imageView2/1/w/200/h/200/', 'imageView2/1/w/100/h/100/', $jam_post['thumbnail']).'" /><span>'.$jam_post['title'].'</span></p></a>';
			$i++;
		}
	}	
	?>
	</div>

	<p class="community-events-footer">
		<a href="https://blog.wpjam.com/" target="_blank">WordPress果酱 <span aria-hidden="true" class="dashicons dashicons-external"></span></a> |
		<a href="http://www.xintheme.com/" target="_blank">xintheme <span aria-hidden="true" class="dashicons dashicons-external"></span></a>
	</p>

	<?php
}