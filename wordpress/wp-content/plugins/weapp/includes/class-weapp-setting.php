<?php
class WEAPP_Setting extends WPJAM_Model {

    const SOURCE_DEFAULT = 0;
    const SOURCE_AUTHORIZE = 1;

	public static function insert($data){
		$appid	= $data['appid'];

		if(empty($data['component_blog_id'])){
			
			$secret	= $data['secret'];

			if(!weapp_exists($appid, $secret)){
				return new WP_Error('weapp_not_exists', '输入的 appid 和 secret 有误，请仔细核对！');
			}
		}

		$data['time']	= time();
		$data['blog_id']= $data['blog_id'] ?: get_current_blog_id();
		
		$result	= parent::insert($data);

		if(is_wp_error($result)){
			return $result;
		}

		return $result;
	}

	public static function update($appid, $data){
		$weapp_setting = self::get($appid);

		if($weapp_setting){
			if(empty($weapp_setting['component_blog_id']) && empty($data['component_blog_id']) && isset($data['secret'])){
				
				$secret	= $data['secret'];
				
				if($secret != $weapp_setting['secret'] && !weapp_exists($appid, $secret)){
					return new WP_Error('weapp_not_exists', '输入的 appid 和 secret 有误，请仔细核对！');
				}
			}
		}else{
			return new WP_Error('weapp_setting_not_exists', '系统中没有你更新的小程序，可能已经被删除了。');
		}

		$result = parent::update($appid, $data);

		if(is_wp_error($result)){
			return $result;
		}

		if(is_multisite()){
			$old_blog_id	= $weapp_setting['blog_id'];
			$new_blog_id	= $data['blog_id'] ?? 0;

			if($new_blog_id && $old_blog_id != $new_blog_id){	// 迁移设置
				if($weapp_setting = get_blog_option($old_blog_id, 'weapp_'.$appid)){
					update_blog_option($new_blog_id, 'weapp_'.$appid, $weapp_setting);
				}
			}
		}

		return $result;
	}

	public static function delete($appid){
		$weapp_setting = self::get($appid);

		if(!$weapp_setting){
			return new WP_Error('weapp_setting_not_exists', '系统中没有你更新的小程序，可能已经被删除了。');
		}

		return parent::delete($appid);
	}

	public static function get_setting($appid){
		
		$weapp_setting	= self::get($appid);
		if($weapp_setting){
			$setting_ex		= get_blog_option($weapp_setting['blog_id'], 'weapp_'.$appid) ?: [];
			$weapp_setting	= array_merge($weapp_setting, $setting_ex);
		}

		return $weapp_setting;
	}

	public static function get_settings($blog_id){
		
		$weapp_settings	= self::get_by('blog_id', $blog_id);

		$weapp_settings	= array_map(function($weapp_setting) use($blog_id){

			$weapp_setting_ex	= get_blog_option($blog_id, 'weapp_'.$weapp_setting['appid']);
			$weapp_setting_ex	= $weapp_setting_ex ?: [];

			return array_merge($weapp_setting, $weapp_setting_ex);
		}, $weapp_settings);

		return $weapp_settings;
	}

	private static 	$handler;

	public static function get_handler(){
		global $wpdb;
		if(is_null(self::$handler)){
			self::$handler = new WPJAM_DB(self::get_table(), array(
				'primary_key'		=> 'appid',
				'cache_key'			=> 'blog_id',
				'cache_group'		=> 'weapp_settings',
				'field_types'		=> ['blog_id'=>'%d','time'=>'%d'],
				'searchable_fields'	=> ['appid','name'],
				'filterable_fields'	=> ['component_blog_id'],
			));
		}
		return self::$handler;
	}

	public static function get_table(){
		global $wpdb;
		return $wpdb->base_prefix . 'weapps';
	}

	public static function create_table($appid=''){

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;

		$table	= self::get_table();

		if($wpdb->get_var("show tables like '{$table}'") != $table) {
			$sql = "
			CREATE TABLE IF NOT EXISTS `{$table}` (
				`blog_id` bigint(20) NOT NULL,
				`name` varchar(255) NOT NULL,
				`appid` varchar(32) NOT NULL,
				`secret` varchar(40) NOT NULL,
				`type` varchar(7) NOT NULL,
				`component_blog_id` bigint(20) NOT NULL DEFAULT 0,
				`time` int(10) NOT NULL,

				PRIMARY KEY	(`appid`),
				KEY `type` (`type`),
				KEY `blog_id` (`blog_id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";
	 
			dbDelta($sql);
		}
	}
}