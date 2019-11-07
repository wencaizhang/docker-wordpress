<?php
trait WEIXIN_Trait{
	public static function get_appid($appid=''){
		if(!empty(static::$appid)){
			$appid	=  static::$appid;
		}else{
			$appid	= $appid ?: weixin_get_appid();
		}

		$weixin	= weixin($appid);
		if(is_wp_error($weixin)){
			if(is_wpjam_json()){
				wpjam_send_json($weixin);
			}else{
				wp_die($weixin->get_error_message(), $weixin->get_error_code());
			}
		}else{
			return $appid;
		}
	}

	public static function set_appid($appid=''){
		static::$appid	= $appid ?: weixin_get_appid();
	}
}