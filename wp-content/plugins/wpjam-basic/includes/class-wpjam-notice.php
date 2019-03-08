<?php
class WPJAM_Notice extends WPJAM_Model {
	private static $handler;
	public static $errors = [];

	public static function get_handler(){
		global $wpdb;
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('wpjam_notices', 'key');
		}
		return static::$handler;
	}

	public static function insert($notice){
		$notice['time']	= ($notice['time'])??time();
		$notice['key']	= md5(maybe_serialize($notice));
		return parent::insert($notice);
	}

	public static function add($notice){
		return self::insert($notice);
	}

	public static function parse_notice($notice){
		return wp_parse_args( $notice, array(
			'type'		=> 'info',
			'class'		=> 'is-dismissible',
			'page'		=> '',
			'tab'		=> '',
			'notice'	=> '',
			'message'	=> '',
		));
	}

	public static function display(){

		// if(!current_user_can('manage_options')){
		//  	return;
		// }

		self::display_errors();

		if(!empty($_GET['notice_key'])){
			self::delete($_GET['notice_key']);
		}

		if($notices	= self::get_all()){

			uasort($notices, function($n, $m){ return $m['time'] <=> $n['time']; });

			foreach ($notices as $key => $notice){
				extract(self::parse_notice($notice));

				$link 	= '';
				if($page){
					$link 	= add_query_arg(array('notice_key'=>$key, 'page'=>$page, 'tab'=>$tab), admin_url('admin.php'));
					$link	= ' <a href="'.$link.'">查看详情</a>';
				}

				echo '<div data-key="'.$key.'" class="wpjam-notice notice notice-'.$type.' '.$class.'"><p>'.$notice.$link.'</p></div>';
			}
		}
	}

	public static function display_errors(){
		global $plugin_page;

		if(!empty($plugin_page)){

			$did_auto_error	= false;

			if(empty($did_auto_error)){
				$did_auto_error	= true;

				$removable_query_args	= wp_removable_query_args();

				if($removable_query_args = array_intersect($removable_query_args, array_keys($_GET))){
					foreach ($removable_query_args as $key) {
						if($key != 'message' && $key != 'settings-updated'){
							if($_GET[$key] === 'true' || $_GET[$key] === '1'){
								WPJAM_Notice::$errors[]	= array('message'=>'操作成功','type'=>'success');
							}else{
								WPJAM_Notice::$errors[]	= array('message'=>$_GET[$key],'type'=>'error');
							}
						}
					}
				}
			}
		}

		if(self::$errors){
			foreach (self::$errors as $error){
				$error	= self::parse_notice($error);
				if($error['message']){
					echo '<div class="notice notice-'.$error['type'].' '.$error['class'].'"><p>'.$error['message'].'</p></div>';
				}
			}
		}

		WPJAM_Notice::$errors	= array();
	}
}








