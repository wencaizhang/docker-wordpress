<?php

// 根据参数获取当前群分享和朋友圈数据
function weapp_get_group_shares($args=[], $appid=''){
	$weapp	= weapp($appid);

	static $groups;

	if(isset($groups))	return $groups;

	$groups	= [];

	$iv = wpjam_get_parameter('iv', array('method' =>'POST'));
	if($iv){	// 用户信息
		$send	= 0;

		WEAPP_User::set_appid($appid);

		include WP_CONTENT_DIR.'/plugins/weapp/api/auth.signon.php';
		$groups['access_token']	= WEAPP_User::generate_access_token($user['openid']);
		$groups['expired_in']	= DAY_IN_SECONDS - 600;
		$groups['user']			= WEAPP_User::parse_for_json($user);
	}

	$share_type	= wpjam_get_parameter('share_type',	array('method' => 'POST'));
	if(empty($share_type))	return [];

	$code		= wpjam_get_parameter('code',	array('method'=>'POST'));
	$openid		= weapp_get_current_openid($code, $appid);
	if(is_wp_error($openid)) return $openid;

	$gid	= wpjam_get_parameter('gid',	array('method'=>'POST'));
	if(!$gid){
		$share_iv 	= wpjam_get_parameter('share_iv',array('method'=>'POST'));

		if($share_iv){
			$session_key	= weapp_get_session_key($code, $appid);
			if(is_wp_error($session_key)){
				return $session_key;
			}

			$share_encrypted_data	= wpjam_get_parameter('share_encrypted_data',	array('method'=>'POST', 'required'=>true));

			$gid	= $weapp->decrypt_gid($session_key, $share_iv, $share_encrypted_data);
			if(is_wp_error($gid)){
				return $gid;
			}
		}
	}

	$type = $share_type;
	if($type == 'timeline'){
		WEAPP_User::set_appid($appid);
		$groups['share_user'] = WEAPP_User::parse_for_json($gid);
	}elseif($type == 'group'){
		$groups['gid']	=  $gid;
	}

	$time	= time();

	if(!class_exists('WEAPP_GroupShare')){
		include WEAPP_PLUGIN_DIR.'includes/class-weapp-group-share.php';
	}

	WEAPP_GroupShare::set_appid($appid);

	$group_shares	= WEAPP_GroupShare::get_shares($gid);

	$data	= compact('openid','gid','type','time');
	if($args){
		$data	= $data+$args; 
	}

	if($group_shares){
		$group_openids	= array_column($group_shares, 'openid');
		$group_shares	= array_combine($group_openids, $group_shares);

		if(isset($group_shares[$openid])){
			$current_share	= $group_shares[$openid];

			// if($time - $current_share['time'] > DAY_IN_SECONDS){
				WEAPP_GroupShare::update($current_share['id'], $data);
			// }
		}else{
			WEAPP_GroupShare::insert($data);
		}
	}else{
		WEAPP_GroupShare::insert($data);
	}

	$groups['group_shares']	= WEAPP_GroupShare::get_shares($gid);

	return $groups;
}