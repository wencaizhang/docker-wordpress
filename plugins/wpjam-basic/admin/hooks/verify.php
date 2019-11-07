<?php
class WPJAM_Verify{
	public static function verify(){
		if(self::verify_domain()){
			return 'verified';
		}

		$weixin_user	= self::get_weixin_user();

		if($weixin_user && $weixin_user['subscribe']){
			if(time() - $weixin_user['last_update'] < DAY_IN_SECONDS) {
				return true;
			}else{
				$weixin_user	= self::update_weixin_user($weixin_user['openid']);
				if(!is_wp_error($weixin_user) && $weixin_user && $weixin_user['subscribe']){
					return true;
				}else{
					return false;
				}
			}
		}

		return false;
	}

	public static function verify_domain($id=0){
		return get_transient('wpjam_basic_verify');
	}

	public static function get_weixin_user(){
		return get_user_meta(get_current_user_id(), 'wpjam_weixin_user', true);
	}

	public static function get_openid(){
		$weixin_user	= self::get_weixin_user();

		return $weixin_user?$weixin_user['openid']:'';
	}

	public static function get_qrcode($key=''){
		$key	= $key?:md5(home_url().'_'.get_current_user_id());

		return wpjam_remote_request('http://jam.wpweixin.com/api/weixin/qrcode/create.json?key='.$key);
	}

	public static function bind_user($data){
		$response	= wpjam_remote_request('http://jam.wpweixin.com/api/weixin/qrcode/verify.json', [
			'method'	=>'POST',
			'body'		=> $data
		]);

		if(is_wp_error($response)){
			return $response;
		}

		$weixin_user =	$response['user']; 

		$weixin_user['last_update']	= time();

		update_user_meta(get_current_user_id(), 'wpjam_weixin_user', $weixin_user);

		return $weixin_user;
	}

	public static function update_weixin_user($openid){
		$response	= wpjam_remote_request('http://jam.wpweixin.com/api/topic/user/get.json?openid='.$openid);

		if(is_wp_error($response)){
			return $response;
		}

		$user	= $response['user'];

		$user['last_update']	= time();

		update_user_meta(get_current_user_id(), 'wpjam_weixin_user', $user);

		return $user;
	}
}