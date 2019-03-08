<?php
add_action('admin_init', function () {
	remove_submenu_page('themes.php', 'theme-editor.php');
}, 999);

add_filter('wpjam_pages', function ($wpjam_pages){
	if(PHP_VERSION >= 7.1){
		$wpjam_pages['themes']['subs']['wpjam-theme-setting']	= [
			'menu_title'	=> '主题设置',	
			'function'		=> 'option',
			'option_name'	=> 'wpjam_theme',
			'page_file'		=> TEMPLATEPATH .'/admin/theme-setting.php',		
		];
	}

	global $submenu;

	unset($submenu['themes.php'][6]);

	return $wpjam_pages;

});

if(PHP_VERSION < 7.1){
	function wpjam_theme_support_page(){
		echo "<h1>主题支持</h1>";
		echo '<p><strong>Honey 需要PHP 7.1 版本才能运行，请升级PHP到7.1或以上。</strong></p>';
	}
}else{
	wpjam_register_theme_upgrader('http://www.xintheme.com/api?id=18357');
}

add_filter('admin_footer_text', function  () {
	echo 'Powered by <a href="http://www.xintheme.com" target="_blank">新主题 XinTheme</a> + <a href="https://blog.wpjam.com/" target="_blank">WordPress 果酱</a>';
});

//编辑器增强
add_filter('mce_buttons_3', function ($buttons) {
	$buttons[] = 'hr';
	$buttons[] = 'del';
	$buttons[] = 'sub';
	$buttons[] = 'sup'; 
	$buttons[] = 'fontselect';
	$buttons[] = 'fontsizeselect';
	$buttons[] = 'cleanup';   
	$buttons[] = 'styleselect';
	$buttons[] = 'wp_page';
	$buttons[] = 'anchor';
	$buttons[] = 'backcolor';
	return $buttons;
});

/*编辑器添加分页按钮*/
add_filter('mce_buttons',function ($mce_buttons) {
	$pos = array_search('wp_more',$mce_buttons,true);
	if ($pos !== false) {
		$tmp_buttons	= array_slice($mce_buttons, 0, $pos+1);
		$tmp_buttons[]	= 'wp_page';
		$mce_buttons	= array_merge($tmp_buttons, array_slice($mce_buttons, $pos+1));
	}
	return $mce_buttons;
});


//字体增加  
add_filter('tiny_mce_before_init', function ($initArray){  
   $initArray['font_formats'] = "微软雅黑='微软雅黑';宋体='宋体';黑体='黑体';仿宋='仿宋';楷体='楷体';隶书='隶书';幼圆='幼圆';";  
   return $initArray;  
});

add_filter('contextual_help', function ($old_help, $screen_id, $screen){
	$screen->remove_help_tabs();
	return $old_help;
}, 10, 3 );

//去除后台标题中的“—— WordPress”
add_filter('admin_title', function ($admin_title, $title){
	return $title.' &lsaquo; '.get_bloginfo('name');
}, 10, 2);
