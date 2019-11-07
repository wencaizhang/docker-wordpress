<?php
add_filter('wpjam_options_list_table', function(){
	return [
		'title'		=> '站点选项',
		'plural'	=> 'wpjam_options',
		'singular' 	=> 'wpjam_option',
		'fixed'		=> false,
		'ajax'		=> true,
		'per_page'	=> 500,
		'model'		=> 'WPJAM_AdminOption',
		'summary'	=> '下面这些选项不是 WordPress 系统自动生成的，可能是你的主题或者其他插件生成，删除前请确保不再使用。'
	]; 
});

add_action('admin_head', function(){
	?>
	<style type="text/css">
	td pre { white-space: pre-wrap; word-wrap: break-word; margin: 0; }
	th.column-option_nam{width: 180px;}
	th.column-source{width: 120px;}
	</style>
	<?php
});

class WPJAM_AdminOption{
	public static function get_primary_key(){
		return 'option_name';
	}

	public static function delete($option_name, $data=[]){
		$option_name2	= $data['option_name2'] ?? '';

		if($option_name == $option_name2){
			return delete_option($option_name);	
		}else{
			return new WP_Error('invalid_option_name','确认输入的 option_name 不正确。');
		}
	}

	public static function insert($data){
		$option_name	= trim($data['option_name']);
		$option_value	= $data['option_value'];

		if(empty($option_name)){
			return new WP_Error('empty_option_name', 'option_name 不能为空');
		}

		if(get_option($option_name)){
			return new WP_Error('option_name_exits', 'option_name 已存在');
		}

		$result	= add_option($option_name, $option_value);

		if(!$result){
			return new WP_Error('add_option_failed', 'option 添加失败');
		}

		return $option_name;
	}

	public static function update($option_name, $data){
		$new_option_value	= trim($data['option_value']);

		if($new_option_value == get_option($option_name)){
			return new WP_Error('option_value_not_modified', 'option_value 未修改');
		}

		return update_option($option_name, $new_option_value);
	}

	public static function replace($option_name, $data){
		$search		= $data['search'];
		$replace	= $data['replace'];

		if(empty($search)){
			return new WP_Error('empty_search', '搜索值不能为空');
		}

		if($search == $replace){
			return new WP_Error('same_search_replace', '搜索值和替换值相同');
		}

		$option_value		= get_option($option_name);
		$new_option_value	= str_replace_deep($search, $replace, $option_value);

		if($option_value != $new_option_value){
			return update_option($option_name, $new_option_value);
		}

		return true;
	}

	public static function replace_all($data){
		$search		= $data['search'];
		$replace	= $data['replace'];

		if(empty($search)){
			return new WP_Error('empty_search', '搜索值不能为空');
		}

		if($search == $replace){
			return new WP_Error('same_search_replace', '搜索值和替换值相同');
		}

		global $wpdb;

		$option_names	= $wpdb->get_col($wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_value LIKE %s", '%'.$wpdb->esc_like($search).'%'));

		if(empty($option_names)){
			return new WP_Error('empty_search_results', '搜索不到含有“'.$search.'”的站点选项');
		}

		foreach ($option_names as $option_name) {
			$option_value		= get_option($option_name);
			$new_option_value	= str_replace_deep($search, $replace, $option_value);

			if($option_value != $new_option_value){
				return update_option($option_name, $new_option_value);
			}
		}
		
		return true;
	}

	public static function rename($option_name, $data){
		$new_option_name	= trim($data['new_option_name']);

		if(empty($new_option_name)){
			return new WP_Error('empty_new_option_name', '新的 option_name 不能为空');
		}

		if($new_option_name == $option_name){
			return new WP_Error('option_name_not_modified', 'option_name 未修改');
		}

		if(get_option($new_option_name)){
			return new WP_Error('option_name_exits', '新的 option_name 已存在');
		}

		$option_value	= get_option($option_name, $default=null);

		if(is_null($option_value)){
			return new WP_Error('empty_option_name', 'option_name 已重命名');
		}

		$result	= add_option($new_option_name, $option_value);

		if(!$result){
			return new WP_Error('add_option_failed', 'option 添加失败');
		}

		return delete_option($option_name);
	}

	public static function get($option_name){
		$option_value	= get_option($option_name);

		if($option_value === false){
			return [];
		}else{
			return compact('option_name', 'option_value');
		}
	}

	public static function get_by_ids($option_names){
		$options	= [];

		foreach($option_names as $option_name){
			$option_value	= get_option($option_name);
			if($option_value === false){
				$options[$option_name]	= [];
			}else{
				$options[$option_name]	= compact('option_name', 'option_value');
			}
		}

		return $options;
	}

	// 后台 list table 显示
	public static function query_items($limit, $offset){
		global $wpdb;
		$items	= $wpdb->get_results( "SELECT * FROM $wpdb->options WHERE option_name != '' ORDER BY option_name", ARRAY_A);

		$items	= array_filter($items, function($item){
			$option_name	= $item['option_name'];

			// 使用内存缓存，则 options 中的瞬时缓存无效。
			if(strpos($option_name, '_site_transient_') === 0 || strpos($option_name, '_transient_') === 0){
				if(wp_using_ext_object_cache()){
					delete_option($option_name);
				}
				
				return false;
			}else{
				return true;
			}
		});

		$total	= count($items);

		return compact('items', 'total');
	}

	public static function get_option_source($option_name){
		$sources = [
			'siteurl'                         => 'system',
			'home'                            => 'system',
			'blogname'                        => 'system',
			/* translators: site tagline */
			'blogdescription'                 => 'system',
			'users_can_register'              => 'system',
			'admin_email'                     => 'system',
			/* translators: default start of the week. 0 = Sunday, 1 = Monday */
			'start_of_week'                   => 'system',
			'use_balanceTags'                 => 'system',
			'use_smilies'                     => 'system',
			'require_name_email'              => 'system',
			'comments_notify'                 => 'system',
			'posts_per_rss'                   => 'system',
			'rss_use_excerpt'                 => 'system',
			'mailserver_url'                  => 'system',
			'mailserver_login'                => 'system',
			'mailserver_pass'                 => 'system',
			'mailserver_port'                 => 'system',
			'default_category'                => 'system',
			'default_comment_status'          => 'system',
			'default_ping_status'             => 'system',
			'default_pingback_flag'           => 'system',
			'posts_per_page'                  => 'system',
			/* translators: default date format, see https://secure.php.net/date */
			'date_format'                     => 'system',
			/* translators: default time format, see https://secure.php.net/date */
			'time_format'                     => 'system',
			/* translators: links last updated date format, see https://secure.php.net/date */
			'links_updated_date_format'       => 'system',
			'comment_moderation'              => 'system',
			'moderation_notify'               => 'system',
			'permalink_structure'             => 'system',
			'rewrite_rules'                   => 'system',
			'hack_file'                       => 'system',
			'blog_charset'                    => 'system',
			'moderation_keys'                 => 'system',
			'active_plugins'                  => 'system',
			'category_base'                   => 'system',
			'ping_sites'                      => 'system',
			'comment_max_links'               => 'system',
			'gmt_offset'                      => 'system',

			// 1.5
			'default_email_category'          => 'system',
			'recently_edited'                 => 'system',
			'template'                        => 'system',
			'stylesheet'                      => 'system',
			'comment_whitelist'               => 'system',
			'blacklist_keys'                  => 'system',
			'comment_registration'            => 'system',
			'html_type'                       => 'system',

			// 1.5.1
			'use_trackback'                   => 'system',

			// 2.0
			'default_role'                    => 'system',
			'db_version'                      => 'system',

			// 2.0.1
			'uploads_use_yearmonth_folders'   => 'system',
			'upload_path'                     => 'system',

			// 2.1
			'blog_public'                     => 'system',
			'default_link_category'           => 'system',
			'show_on_front'                   => 'system',

			// 2.2
			'tag_base'                        => 'system',

			// 2.5
			'show_avatars'                    => 'system',
			'avatar_rating'                   => 'system',
			'upload_url_path'                 => 'system',
			'thumbnail_size_w'                => 'system',
			'thumbnail_size_h'                => 'system',
			'thumbnail_crop'                  => 'system',
			'medium_size_w'                   => 'system',
			'medium_size_h'                   => 'system',

			// 2.6
			'avatar_default'                  => 'system',

			// 2.7
			'large_size_w'                    => 'system',
			'large_size_h'                    => 'system',
			'image_default_link_type'         => 'system',
			'image_default_size'              => 'system',
			'image_default_align'             => 'system',
			'close_comments_for_old_posts'    => 'system',
			'close_comments_days_old'         => 'system',
			'thread_comments'                 => 'system',
			'thread_comments_depth'           => 'system',
			'page_comments'                   => 'system',
			'comments_per_page'               => 'system',
			'default_comments_page'           => 'newest',
			'comment_order'                   => 'system',
			'sticky_posts'                    => 'system',
			'widget_categories'               => 'system',
			'widget_text'                     => 'system',
			'widget_rss'                      => 'system',
			'uninstall_plugins'               => 'system',

			// 2.8
			'timezone_string'                 => 'system',

			// 3.0
			'page_for_posts'                  => 'system',
			'page_on_front'                   => 'system',

			// 3.1
			'default_post_format'             => 'system',

			// 3.5
			'link_manager_enabled'            => 'system',

			// 4.3.0
			'finished_splitting_shared_terms' => 'system',
			'site_icon'                       => 'system',

			// 4.4.0
			'medium_large_size_w'             => 'system',
			'medium_large_size_h'             => 'system',

			// 4.9.6
			'wp_page_for_privacy_policy'      => 'system',

			// 4.9.8
			'show_comments_cookies_opt_in'    => 'system',
		];

		global $wpdb;
		
		$blog_id	= get_current_blog_id();

		$sources['dashboard_widget_options']	= 'system';
		$sources['post_count']				= 'system';
		$sources['WPLANG']					= 'system';
		$sources['theme_switched']			= 'system';
		$sources['_split_terms']			= 'system';
		$sources['allowedthemes']			= 'system';
		$sources['blog_upload_space']		= 'system';
		$sources['current_theme']			= 'system';
		$sources['db_upgraded']				= 'system';
		$sources['cron']					= 'system';
		$sources['recently_activated']		= 'system';
		$sources['sidebars_widgets']		= 'system';
		$sources['new_admin_email']			= 'system';
		$sources['nav_menu_options']		= 'system';
		$sources['initial_db_version']		= 'system';
		$sources['fresh_site']				= 'system';
		$sources['can_compress_scripts']	= 'system';
		
		
		$sources['recovery_keys']			= 'system';
		$sources['recovery_mode_email_last_sent']	= 'system';
		$sources['auto_core_update_notified']		= 'system';

		foreach (get_taxonomies() as $taxonomy) {
			$sources[$taxonomy.'_children']	= 'system';
		}

		$user_roles	= $wpdb->get_blog_prefix().'user_roles';
		$sources[$user_roles]				= 'system';

		$sources['wpjam-basic']				= 'WPJAM Basic 插件';
		$sources['wpjam-cdn']				= 'WPJAM Basic 插件';
		$sources['wpjam-extends']			= 'WPJAM Basic 插件';
		$sources['301-redirects']			= 'WPJAM Basic 扩展';

		$sources['baidu-ydzq']				= 'WPJAM 其他插件';
		$sources['baidu-ydzq-menu']			= 'WPJAM Basic 扩展';
		$sources['baidu-zz']				= 'WPJAM Basic 扩展';
		$sources['xzh']						= 'WPJAM Basic 扩展';
		$sources['baijiahao']				= 'WPJAM Basic 插件';
		$sources['weixin-robot']			= '微信机器人插件';
		$sources['weixin-robot-advanced']	= '微信机器人插件';
		$sources['wpjam_weapp']				= '微信小程序插件';
		$sources['mirror_cache']			= 'WPJAM 其他插件';

		if(isset($sources[$option_name])){
			return $sources[$option_name];
		}elseif(strpos($option_name, 'theme_mods_') === 0){
			return 'system';
		}elseif(strpos($option_name, 'widget_') === 0){
			return 'system';
		}elseif(strpos($option_name, '_site_transient_') === 0){	// 使用内存缓存，则 options 中的瞬时缓存无效。
			if(!wp_using_ext_object_cache()){
				return 'system';
			}
		}elseif(strpos($option_name, '_transient_') === 0){	// 使用内存缓存，则 options 中的瞬时缓存无效。
			if(!wp_using_ext_object_cache()){
				return 'system';
			}
		}elseif(strpos($option_name, 'wpjam') === 0){
			return 'WPJAM 其他插件';
		}elseif(function_exists('weixin_get_appid') && strpos($option_name, 'weixin_'.weixin_get_appid()) === 0){
			return '微信机器人插件';
		}elseif(function_exists('weapp_get_appid')){
			$appid	= weapp_get_appid();

			if(strpos($option_name, 'weapp_'.$appid) === 0 || strpos($option_name, 'topic_'.$appid) === 0){
				return '微信小程序插件';
			}
		}
		
		return '其他插件或主题';
	}

	public static function item_callback($item){
		if(is_serialized($item['option_value']) || !is_scalar($item['option_value'])){
			if(!is_scalar($item['option_value'])){
				$item['option_value']	= 'SERIALIZED DATA';
			}elseif(is_serialized_string($item['option_value'])){
				$item['option_value']	= '<pre>'.maybe_unserialize( $item['option_value'] ).'</pre>';
				unset($item['row_actions']['view']);
			}else{
				$item['option_value']	= 'SERIALIZED DATA';
			}

			unset($item['row_actions']['edit']);
		}else{
			$item['option_value']	= '<pre>'.esc_textarea($item['option_value']).'</pre>';

			unset($item['row_actions']['view']);
			unset($item['row_actions']['replace']);
		}

		$item['source']	= self::get_option_source($item['option_name']);
		return $item;
	}

	public static function get_actions(){
		return [
			'add'			=> ['title'=>'新建'],
			'edit'			=> ['title'=>'编辑'],
			'view'			=> ['title'=>'查看',		'submit_text'=>''],
			'replace'		=> ['title'=>'替换',		'bulk'=>true],
			'replace_all'	=> ['title'=>'全局替换',	'direct'=>false,	'overall'=>true],
			'rename'		=> ['title'=>'重命名',	'response'=>'list'],
			'delete'		=> ['title'=>'删除',		'page_title'=>'确认删除？',	'submit_text'=>'确认删除']
		];
	}

	public static function get_fields($action_key='', $option_name=''){
		$fields	= [
			'option_name'	=> ['title'=>'option_name',		'type'=>'text',		'show_admin_column'=>true],
			'source'		=> ['title'=>'来源',				'type'=>'view',		'show_admin_column'=>'only',	'options'=>['system'=>'系统自带','code'=>'其他插件']],
			'option_value'	=> ['title'=>'option_value',	'type'=>'textarea',	'show_admin_column'=>true]
		];

		if($action_key == 'view'){	
			$option_value	= get_option($option_name);

			$fields['option_value']['type']		= 'view';
			$fields['option_value']['value']	= '<pre>'.var_export($option_value, true).'</pre>';
		}elseif($action_key == 'edit'){
			$option_value	= get_option($option_name);

			if(!is_scalar($option_value)){
				wpjam_send_json(['errcode'=>'serialized_option', 'errmsg'=>'非标量选项值不能直接修改']);
			}
		}elseif($action_key == 'rename'){
			$fields['option_name']['title']	= '旧的option_name';
			$fields['new_option_name']		= ['title'=>'新的option_name',	'type'=>'text'];
			unset($fields['option_value']);
		}elseif($action_key == 'delete'){
			$fields['delete_fieldset']	= ['title'=>'确认删除？',	'type'=>'fieldset',	'fields'=>[
				'delete_view'	=> ['title'=>'',	'type'=>'view',	'value'=>'确认删除，请再次输入option_name'],
				'option_name2'	=> ['title'=>'',	'type'=>'text']
			]];
			unset($fields['option_value']);
		}elseif($action_key == 'replace' || $action_key == 'replace_all'){
			$fields	= $fields + [
				'search'		=> ['title'=>'搜索',			'type'=>'text'],
				'replace'		=> ['title'=>'替换',			'type'=>'text'],
			];

			if($action_key == 'replace_all' || is_array($option_name)){
				unset($fields['option_name']);
			}

			unset($fields['option_value']);
		}

		return $fields;
	}
}


	// copy from wp-admin/includes/schema.php
	

