<?php
trait WEAPP_Trait{
	public static function get_appid($appid=''){
		if(!empty(static::$appid)){
			$appid	=  static::$appid;
		}else{
			$appid	= ($appid)?:weapp_get_appid();
		}

		if(is_admin()){
			if(empty($appid)){
				wp_die('小程序 appid 为空',	'empty_appid');
			}else{
				return $appid;
			}
		}else{
			$weapp	= weapp($appid);
			if(is_wp_error($weapp)){
				if(is_wpjam_json()){
					trigger_error($weapp->get_error_code().'：'.$weapp->get_error_message());
					wpjam_send_json($weapp);
				}else{
					wp_die($weapp->get_error_message(), $weapp->get_error_code());
				}
			}else{
				return $appid;
			}
		}	
	}

	public static function set_appid($appid=''){
		static::$appid	= $appid ?: weapp_get_appid();
	}
}