<?php
add_action('admin_notices', array('WPJAM_Notice', 'display'));

add_filter('removable_query_args', function($removable_query_args){
	return array_merge($removable_query_args, ['added', 'duplicated', 'unapproved',	'unpublished', 'published', 'geted', 'created', 'synced']);
});

add_action('admin_enqueue_scripts', function(){
	global $pagenow;

	if($pagenow == 'customize.php'){
		return;
	}

	// wp_enqueue_script('jquery-ui-button');
	add_thickbox();

	wp_enqueue_style('editor-buttons');

	wp_enqueue_script('raphael',	WPJAM_BASIC_PLUGIN_URL.'/static/raphael.min.js');
	wp_enqueue_script('morris',		WPJAM_BASIC_PLUGIN_URL.'/static/morris.min.js');

	wp_enqueue_style('morris',		WPJAM_BASIC_PLUGIN_URL.'/static/morris.css');
	wp_enqueue_style('wpjam-style',	WPJAM_BASIC_PLUGIN_URL.'/static/style.css');

	// wp_enqueue_style('morris', '//cdn.staticfile.org/morris.js/0.5.1/morris.css');
	// $scripts->add( 'mediaelement-core', "/wp-includes/js/mediaelement/mediaelement-and-player$suffix.js", array(), '4.2.6-78496d1', 1 );

	wp_deregister_script('mediaelement-core');
	wp_register_script('mediaelement-core', "/wp-includes/js/mediaelement/mediaelement-and-player.min.js", array(), '4.2.6-78496d1', 1 );

	wp_deregister_script('imgareaselect');
	wp_deregister_style('imgareaselect');

	wp_enqueue_media();
	
	wp_enqueue_style('wp-color-picker'); 
	wp_enqueue_script('wp-color-picker');

	if($pagenow != 'plugins.php'){
		wp_enqueue_script('wpjam-script',	WPJAM_BASIC_PLUGIN_URL.'/static/script.js', ['jquery','jquery-ui-core','thickbox']);
		wp_enqueue_script('wpjam-form',		WPJAM_BASIC_PLUGIN_URL.'/static/form.js',   ['jquery','jquery-ui-core','wp-backbone','media-views','wp-color-picker']);
	}

	global $plugin_page, $current_tab, $current_admin_url, $current_list_table;

	if($plugin_page){
		$params	= $_REQUEST;

		foreach (['page', 'tab', '_wp_http_referer', '_wpnonce'] as $query_key) {
			unset($params[$query_key]);
		}

		$params	= $params?:new stdClass();

		wp_localize_script('wpjam-script', 'wpjam_page_setting', [
			'plugin_page'		=> $plugin_page,
			'current_tab'		=> $current_tab,
			'current_admin_url'	=> $current_admin_url,
			'current_list_table'=> $current_list_table ?? null,
			'params'			=> $params
		]);
	}
});

//模板 JS
add_action('print_media_templates', function (){ ?>

	<div id="tb_modal" style="display:none; background: #f1f1f1;"></div>

	<script type="text/html" id="tmpl-wpjam-del-item">
	<a href="javascript:;" class="button del-item">删除</a> <span class="dashicons dashicons-menu"></span>
	</script>

	<script type="text/html" id="tmpl-wpjam-img">
	<img style="{{ data.img_style }}" src="{{ data.img_url }}{{ data.thumb_args }}" alt="" /><a href="javascript:;" data-bg_class="{{ data.bg_class }}"  class="del-img dashicons dashicons-no-alt"></a>
	</script>

	<script type="text/html" id="tmpl-wpjam-mu-img">
	<div class="mu-img mu-item"><img width="100" src="{{ data.img_url }}?imageView2/1/w/200/h/200/"><input type="hidden" name="{{ data.input_name }}" value="{{ data.img_value }}" /><a href="javascript:;" class="del-item dashicons dashicons-no-alt"></a></div>
	</script>

	<script type="text/html" id="tmpl-wpjam-mu-file">
	<div class="mu-item"><input type="url" name="{{ data.input_name }}" id="{{ data.input_id }}" class="regular-text" value="{{ data.img_url }}"  /> <a href="javascript:;" class="button del-item">删除</a>  <span class="dashicons dashicons-menu"></span></div>
	</script>

	<?php wpjam_form_field_tmpls();
});

