<?php
wp_cache_add_global_groups(array('weapp_settings'));
class WEIXIN_Setting extends WPJAM_Model {
	public static function insert($data){
		$appid	= $data['appid'];

		if(empty($data['component_blog_id'])){
			
			$secret	= $data['secret'];

			if(!weixin_exists($appid, $secret)){
				return new WP_Error('weixin_not_exists', '输入的 appid 和 secret 有误，请仔细核对！');
			}
		}
		
		$data['type']	= 'weixin';
		$data['time']	= time();
		$data['blog_id']= $data['blog_id']?:get_current_blog_id();

		$blog_id = $data['blog_id'];

		wp_cache_delete($blog_id, 'weapp_settings');
		
		$result	= parent::insert($data);

		if(is_wp_error($result)){
			return $result;
		}

		self::create_table($appid);

		return $result;
	}

	public static function update($appid, $data){
		if($weixin_setting = self::get($appid)){
			if(empty($weixin_setting['component_blog_id']) && empty($data['component_blog_id']) && isset($data['secret'])){
				
				$secret	= $data['secret'];
				
				if(!weixin_exists($appid, $secret)){
					return new WP_Error('weixin_not_exists', '输入的 appid 和 secret 有误，请仔细核对！');
				}
			}

			$old_blog_id	= $weixin_setting['blog_id'];
			$new_blog_id	= $data['blog_id']??0;

			wp_cache_delete($old_blog_id, 'weapp_settings');

			if($new_blog_id){
				if($old_blog_id != $data['blog_id']){	// 迁移设置
					wp_cache_delete($new_blog_id, 'weapp_settings');

					if($weixin_setting = get_blog_option($old_blog_id, 'weixin_'.$appid)){
						update_blog_option($new_blog_id, 'weixin_'.$appid, $weixin_setting);
					}
				}
			}
		}else{
			return new WP_Error('weixin_setting_not_exists', '系统中没有你更新的小程序，可能已经被删除了。');
		}

		$result = parent::update($appid, $data);

		if(is_wp_error($result)){
			return $result;
		}

		self::create_table($appid);

		return $result;
	}

	public static function delete($appid){
		if($weixin_setting = self::get($appid)){
			wp_cache_delete($weixin_setting['blog_id'], 'weapp_settings');
		}else{
			return new WP_Error('weixin_setting_not_exists', '系统中没有你更新的小程序，可能已经被删除了。');
		}

		return parent::delete($appid);
	}

	public static function get_settings($blog_id){
		$weixin_settings	= wp_cache_get($blog_id, 'weapp_settings');
		
		if($weixin_settings === false){
			$weixin_settings	= self::Query()->where('blog_id', $blog_id)->where('type', 'weixin')->get_results();

			$weixin_settings	= array_map(function($weixin_setting) use($blog_id){
				$weixin_setting_ex	= get_blog_option($blog_id, 'weixin_'.$weixin_setting['appid']);
				$weixin_setting_ex	= $weixin_setting_ex?:array();
				return array_merge($weixin_setting, $weixin_setting_ex);
			}, $weixin_settings);

			wp_cache_set($blog_id, $weixin_settings, 'weapp_settings', DAY_IN_SECONDS);
		}

		return $weixin_settings;
	}

	public static function list($limit, $offset){
		if(!is_super_admin()){
			self::get_handler()->where('blog_id', get_current_blog_id());
		}

		self::get_handler()->where('type', 'weixin');

		if(empty($_GET['orderby'])){
			self::get_handler()->order_by('time');
		}

		return parent::list($limit, $offset);
	}

	public static function item_callback($item){
		if($item['blog_id'] == get_current_blog_id()){
			$item['row_actions']['setting']	= '<a href="'.admin_url('admin.php?page=weixin-'.$item['appid'].'-setting').'">设置</a>';
		}

		$item['time']		= get_date_from_gmt(date('Y-m-d H:i:s',$item['time']));
		$item['blog_id']	= '<a href="'.get_admin_url($item['blog_id'],'admin.php?page=weixin-settings').'">'.get_blog_option($item['blog_id'], 'blogname').'</a>';

		return $item;
	}

	private static 	$handler;

	public static function get_handler(){
		global $wpdb;
		if(is_null(self::$handler)){
			self::$handler = new WPJAM_DB($wpdb->base_prefix . 'weapps', array(
				'primary_key'		=> 'appid',
				'cache_group'		=> 'weapp_settings',
				'field_types'		=> array('blog_id'=>'%d','time'=>'%d'),
				'searchable_fields'	=> ['appid','name'],
				'filterable_fields'	=> [],
			));
		}
		return self::$handler;
	}

	public static function create_table($appid=''){

		if($appid){

			WEIXIN_Message::set_appid($appid);
			WEIXIN_Message::create_table();

			WEIXIN_User::set_appid($appid);;
			WEIXIN_User::create_table();
		}

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;

		$table	= $wpdb->base_prefix . 'weixins';

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