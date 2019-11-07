<?php
// 创建 WEAPP 对象
function weapp($appid=''){
	$appid	= $appid ?: weapp_get_appid();

	if(empty($appid)) {
		$error	= new WP_Error('empty_appid', '如果测试环境，请在链接加上 ?appid=APPID');

		if(is_wpjam_json()){
			wpjam_send_json($error);
		}else{
			wp_die('小程序 appid 为空',	'empty_appid');
		}
	}

	$weapp_setting	= weapp_get_setting($appid);

	if(empty($weapp_setting)){
		$error	= new WP_Error('empty_weapp_setting', '请先在后台小程序设置中加入该小程序');

		if(is_wpjam_json()){
			wpjam_send_json($error);
		}else{
			wp_die('小程序设置信息为空',	'请先在后台小程序设置中加入该小程序');
		}
	}

	$secret				= $weapp_setting['secret'];
	$component_blog_id	= $weapp_setting['component_blog_id']??0;

	static $weapps;

	$weapps	= ($weapps)??[];

	if(isset($weapps[$appid])) {
		return $weapps[$appid];
	}

	$weapps[$appid] = new WEAPP($appid, $secret, $component_blog_id);

	return $weapps[$appid];
}

// 判断当前的 weapp 设置是否有效
function weapp_exists($appid, $secret){
	$weapp			= new WEAPP($appid, $secret);
	$access_token	= $weapp->get_access_token($force=true);
	return is_wp_error($access_token) ? false : true;
}

// 获取 appid
// 正式环境：		从 refer 获取
// 如果在后台：	从 $plugin_page 中通过正则获取
// 测试环境：		从 $_GET['appid'] 中获取
function weapp_get_appid(){
	if(!is_multisite()){
		return wpjam_get_setting('wpjam_weapp', 'appid');
	}

	global $_weapp_appid;

	if(isset($_weapp_appid)) {
		return $_weapp_appid;	
	}

	$appid	= '';

	$refer	= $_SERVER['HTTP_REFERER'] ??'';
	if($refer && preg_match('|https://servicewechat.com/(.*?)/.*?/|i', $refer, $matches)){
		if($matches[1] != 'touristappid'){
			$appid	= $matches[1];	
		}
	}elseif(is_admin()){
		$weapp_settings	= WEAPP_Setting::get_settings(get_current_blog_id());

		if(!$weapp_settings) {
			return '';
		}

		if(count($weapp_settings) == 1){
			$appid	= $weapp_settings[0]['appid'];
		}else{
			global $pagenow, $plugin_page, $current_list_table, $current_option;

			$appid_str = '';

			if(!empty($current_list_table)){
				$appid_str	= $current_list_table; 
			}elseif(!empty($current_option)){
				$appid_str	= $current_option; 
			}elseif(!empty($plugin_page)){
				$appid_str	= $plugin_page; 
			}elseif(!empty($_REQUEST['page'])){
				$appid_str	= $_REQUEST['page'];
			}elseif(!empty($_REQUEST['plugin_page'])){
				$appid_str	= $_REQUEST['plugin_page'];
			}
			
			if($appid_str && preg_match('/[-|_](wx[A-Za-z0-9]{16})/', $appid_str, $matches)){
				$appid	= ($matches[1]);
			}else{
				return '';
			}
		}
	}elseif(!empty($_GET['appid']) && strpos($_GET['appid'], 'wx') === 0){
		$appid	= $_GET['appid'];
	}else{
		global $wp;

		if(!empty($wp->query_vars['appid'])) {
			$appid = $wp->query_vars['appid'];
		}
	}

	$_weapp_appid = $appid;
	return $appid;
}

// 特殊接口处理
function weapp_set_appid($appid){
	global $_weapp_appid;

	if($appid){
		$_weapp_appid = $appid;
	}
}

// weapp_get_setting() 获取 weapp_get_appid() 的小程序设置
// weapp_get_setting($appid) 获取 $appid 的小程序设置
// weapp_get_setting($setting_name) 获取  weapp_get_appid() 的小程序 $setting_name 的设置
// weapp_get_setting($setting_name, $appid) 获取 $appid 的小程序 $setting_name 的设置
function weapp_get_setting(){
	$args_num	= func_num_args();
	$args		= func_get_args();

	$appid		= '';

	if($args_num == 0){
		$setting_name	= '';
	}elseif($args_num == 1){
		$setting_name	= $args[0];
		
		if($setting_name){
			if(preg_match('/(wx[A-Za-z0-9]{16})/', $setting_name)){
				$appid			= $setting_name;
				$setting_name	= '';
			}
		}
	}elseif($args_num == 2){
		$setting_name	= $args[0];
		$appid			= $args[1];
	}

	if(!is_multisite()){
		if($setting_name){
			return wpjam_get_setting('wpjam_weapp', $setting_name);
		}else{
			return wpjam_get_option('wpjam_weapp');
		}
	}

	$appid	= $appid ?: weapp_get_appid();

	if($appid){
		$option	= WEAPP_Setting::get_setting($appid);

		if($setting_name){
			return $option[$setting_name] ?? null;
		}else{
			return $option;
		}
	}else{
		if($setting_name){
			return null;
		}else{
			return [];
		}
	}
}

function weapp_update_setting($setting_name, $setting_value, $appid=''){
	if(is_multisite()){
		$appid	= $appid ?: weapp_get_appid();

		if(empty($appid)){
			return false;
		}

		$weapp_setting	= WEAPP_Setting::get($appid);

		if(empty($weapp_setting) || empty($weapp_setting['blog_id']) || isset($weapp_setting[$setting_name])){
			return false;
		}

		return wpjam_update_setting('weapp_'.$appid, $setting_name, $setting_value, $weapp_setting['blog_id']);
	}else{
		return wpjam_update_setting('wpjam_weapp', $setting_name, $setting_value);
	}
}

// 获取 $blog_id 下所有小程序设置
function weapp_get_settings($blog_id=''){
	if(is_multisite()){
		$blog_id = ($blog_id) ?: get_current_blog_id();
		return WEAPP_Setting::get_settings($blog_id);
	}else{
		return [wpjam_get_option('wpjam_weapp')];
	}
}

function weapp_get_version($appid=''){
	return weapp($appid)->get_version();
}

// 获取当前的 openid
// 如果 $code 不为空，从 jscode 接口中获取
// 否则从 $access_token 中获取
function weapp_get_current_openid($code='', $appid=''){
	if($code){
		return weapp($appid)->get_openid_by_jscode($code);
	}else{
		global $weapp_current_openid;

		if(isset($weapp_current_openid)){
			return $weapp_current_openid;
		}

		$access_token	= wpjam_get_parameter('access_token');

		if(empty($access_token)){
			if((isset($_GET['debug']) || isset($_GET['debug_openid'])) && isset($_GET['openid'])){
				return $_GET['openid'];	// 用于测试
			}else{
				// return new WP_Error('required_access_token', 'Access Token 为空！');
				return new WP_Error('illegal_access_token', 'Access Token 为空！');
			}
		}

		WEAPP_User::set_appid($appid);

		$openid = WEAPP_User::get_openid_by_access_token($access_token);

		if(!is_wp_error($openid)){
			$weapp_current_openid	= $openid;
		}

		return $openid;
	}
}

// 设置当前的 openid
function weapp_set_current_openid($openid){
	global $weapp_current_openid;
	$weapp_current_openid	= $openid;
}

// 获取 $session_key
// 如果 $code 不为空，从 jscode2session 接口中获取，并存到内存
// 否则获取当前 openid，并且内存中获取
function weapp_get_session_key($code='', $appid=''){
	$weapp	= weapp($appid);

	if($code){
		return $weapp->get_session_key($code);
	}else{
		$openid = weapp_get_current_openid('', $appid);

		if(is_wp_error($openid)){
			return $openid;
		}

		return $weapp->get_session_key_by_openid($openid);
	}
}

function weapp_msg_sec_check($content, $appid=''){
	return weapp($appid)->msg_sec_check($content);
}

function weapp_img_sec_check($media, $appid=''){
	return weapp($appid)->img_sec_check($media);
}

// 发送客服消息
function weapp_send_custom_message($data, $appid=''){
	return weapp($appid)->send_custom_message($data);
}

// 将WP的图片上传到小程序临时素材
function weapp_get_wp_img_media_id($img_id, $appid=''){
	$weapp	= weapp($appid);
	
	if(!get_post($img_id)){
		return new WP_Error('invalid_attachment_id', '非法附件ID');
	}

	$media	= get_post_meta($img_id, 'weapp_media', true);

	if(!$media  || ((time() - $media['created_at'] + 600) > DAY_IN_SECONDS*3)){

		$img_file	= get_attached_file($img_id);
		if(!$img_file){
			return new WP_Error('invalid_attachment_id', '非法附件ID');
		}

		$media	= $weapp->upload_media($img_file);

		if(is_wp_error($media)){
			return $media;
		}

		update_post_meta($img_id, 'weapp_media', $media);
	}

	return $media['media_id'];
}

function weapp_create_qrcode($data, $return='url', $appid=''){
	$weapp	= weapp($appid);

	$width	= $data['width'] ?? 430;
	$type	= $data['type'] ?? 'wxacode';
	$scene	= $data['scene'] ?? '';
	$path	= $data['path'] ?? '';
	$page	= $data['page'] ?? '';

	if($type == 'unlimit'){
		$media_id	= $weapp->create_qrcode(compact('page','width','scene'), $type);
	}else{
		$media_id	= $weapp->create_qrcode(compact('path','width'), $type);
	}

	if(is_wp_error($media_id)){
		return $media_id;
	}

	if($return == 'url'){
		return $weapp->get_media_url($media_id, $type);
	}else{
		return $media_id;
	}
}

// 获取一个当前可用的 form_id
function weapp_get_form_id($openid, $appid=''){
	WEAPP_UserFormId::set_appid($appid);
	return WEAPP_UserFormId::get_form_id($openid);
}

function weapp_get_form_ids($openids=null, $delete=true, $appid=''){
	WEAPP_UserFormId::set_appid($appid);
	return WEAPP_UserFormId::get_form_ids($openids, $delete);
}

// 新增一个 form_id
function weapp_add_form_id($openid, $form_id, $appid=''){
	if(weapp_get_version($appid)	== 'devtools'){
		return;
	}

	if(empty($form_id))	return;

	WEAPP_UserFormId::set_appid($appid);
	return WEAPP_UserFormId::add_form_id($openid, $form_id);
}

function weapp_add_prepay_id($openid, $prepay_id, $appid=''){
	WEAPP_UserFormId::set_appid($appid);
	return WEAPP_UserFormId::add_prepay_id($openid, $prepay_id);
}

// 发送模板消息
function weapp_send_template_message($data, $appid=''){
	return WEAPP_TemplateMessage::send($data, $appid);
}

function weapp_send_template_messages($datas=null){
	return WEAPP_TemplateMessage::send_template_messages($datas);
}

// 根据 key 获取 template_id
function weapp_get_template_id($key, $appid=''){
	
	$weapp_setting	= weapp_get_setting($appid);

	if($weapp_setting){

		WEAPP_Template::set_appid($appid);

		return WEAPP_Template::get_template_id($key);
	}

	return false;
}

function weapp_create_table($appid){
	WEAPP_User::set_appid($appid);
	WEAPP_User::create_table();
	do_action('weapp_create_table', $appid);
}

// 获取 $openid 的小程序用户详情
function weapp_get_user($openid, $parsed=true, $appid=''){
	_deprecated_function(__FUNCTION__, 'WEAPP 1.0', 'WEAPP_User::parse_for_json 或者 WEAPP_User::get');

	WEAPP_User::set_appid($appid);

	if($parsed){
		return WEAPP_User::parse_for_json($openid);
	}else{
		return WEAPP_User::get($openid);
	}
}

// 根据参数获取一批的小程序用户详情
// 参数可以 openids 或者 WPJAM_Query 查询参数
function weapp_get_users($args, &$next_cursor=0, $appid=''){
	WEAPP_User::set_appid($appid);

	if(wpjam_is_assoc_array($args)){
		$query			= WEAPP_User::Query($args);

		$users			= $query->datas;
		$found_rows		= $query->found_rows;
		$next_cursor	= $query->next_cursor;
	}else{
		_deprecated_function(__FUNCTION__, 'WEAPP 1.0', 'WEAPP_User::get_by_ids');

		$users			= WEAPP_User::get_ids($args);
	}

	return $users;
}

// 请使用 wpjam_get_thumbnail($img_url, $args) 代替
function weapp_get_thumbnail($img_url, $mode=1, $width=0, $height=0){
	return wpjam_get_thumbnail($img_url, compact('mode', 'width', 'height'));
}

// 请使用 wpjam_content_images($content, $width=750) 代替
function weapp_content_images($content, $width=750){
	return wpjam_content_images($content, $width);
}