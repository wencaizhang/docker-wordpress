<?php
add_action('admin_init', function () {
	remove_submenu_page('themes.php', 'theme-editor.php');
}, 999);

if(PHP_VERSION >= 7.2){
	wpjam_add_menu_page('wpjam-sweet', array(
		'menu_title'	=> 'SWEET',
		'icon'			=> 'dashicons-buddicons-replies',
		'capability'	=> 'manage_options',
		'position'		=> '59',
		'function'		=> 'option',
		'option_name'	=> 'wpjam_theme',
		'page_file'		=> TEMPLATEPATH .'/admin/theme-setting.php',	
	));


	add_filter('wpjam_cdn_setting', function($setting){
		unset($setting['fields']['term_thumbnail_set']);
		unset($setting['fields']['default']);

		return $setting;
	}, 11);
}

wpjam_register_theme_upgrader('http://www.xintheme.com/api?id=31944');

add_filter('admin_footer_text', function  () {
	echo 'Powered by <a href="http://www.xintheme.com" target="_blank">新主题 XinTheme</a> + <a href="https://blog.wpjam.com/" target="_blank">WordPress 果酱</a>';
});

add_filter('contextual_help', function ($old_help, $screen_id, $screen){
	$screen->remove_help_tabs();
	return $old_help;
}, 10, 3 );

//去除后台标题中的“—— WordPress”
add_filter('admin_title', function ($admin_title, $title){
	return $title.' &lsaquo; '.get_bloginfo('name');
}, 10, 2);

 
add_action( 'admin_head', function () {  
    $custom_menu_css = '<style type="text/css">  
		.wp-first-item.wp-not-current-submenu.wp-menu-separator,.hide-if-no-customize{display: none;}
    </style>';  
    echo $custom_menu_css;  
} );


add_filter('wpjam_extends_setting', function($wpjam_setting){

	unset($wpjam_setting['fields']['related-posts.php']);
	unset($wpjam_setting['fields']['wpjam-postviews.php']);
	unset($wpjam_setting['fields']['mobile-theme.php']);

	return $wpjam_setting;
}, 99);