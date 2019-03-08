<?php
// 获取设置
function wpjam_get_setting($option, $setting_name, $blog_id=0){
	if(is_string($option)) {
		$option = wpjam_get_option($option, $blog_id);
	}

	if($option && isset($option[$setting_name])){
		$value	= $option[$setting_name];
	}else{
		$value	= null;
	}

	if($value && is_string($value)){
		return  str_replace("\r\n", "\n", trim($value));
	}else{
		return $value;
	}
}

// 更新设置
function wpjam_update_setting($option_name, $setting_name, $setting_value, $blog_id=0){
	$option	= wpjam_get_option($option_name, $blog_id);
	$option[$setting_name]	= $setting_value;

	if($blog_id && is_multisite()){
		return update_blog_option($id, $option_name, $option);
	}else{
		return update_option($option_name, $option);
	}
}

// 获取选项
function wpjam_get_option($option_name, $blog_id=0){
	if(is_multisite()){
		if(is_network_admin()){
			return get_site_option($option_name);
		}else{
			if(wp_installing()){	// 安装的时候没有缓存，会有一大堆 SQL 请求
				static $options;
				$options	= $options ?? [];
				if(isset($options[$option_name])){
					return $options[$option_name];
				}
			}

			if(is_multisite() && $blog_id){
				$option	= get_blog_option($blog_id, $option_name) ?: [];
			}else{
				$option	= get_option($option_name) ?: [];	
			}

			if(is_multisite() && apply_filters('wpjam_option_use_site_default', false, $option_name)){
				$site_option	= get_site_option($option_name) ?: [];
				$option			= $option + $site_option;
			}

			if(wp_installing()){
				$options[$option_name]	= $option;
			}

			return $option;
		}
	}else{
		return get_option($option_name);
	}
}


function wpjam_parse_fields_setting($fields, $sub=0){
	return WPJAM_Field::parse_fields_setting($fields, $sub);
}

function wpjam_parse_field_value($value, $field){
	return WPJAM_Field::parse_field_value($value, $field);
}

function wpjam_get_field_post_ids($value, $field){
	return WPJAM_Field::get_field_post_ids($value, $field);
}

function wpjam_get_post_type_setting($post_type){
	$settings	= get_option('wpjam_post_types') ?: [];
	return $settings[$post_type] ?? [];
}







function wpjam_parse_shortcode_attr($str,  $tagnames=null){
	return 	WPJAM_API::parse_shortcode_attr($str,  $tagnames);
}

// 去掉非 utf8mb4 字符
function wpjam_strip_invalid_text($str){
	return WPJAM_API::strip_invalid_text($str);
}

// 去掉 4字节 字符
function wpjam_strip_4_byte_chars($chars){
	return WPJAM_API::strip_4_byte_chars($chars);
}

// 去掉控制字符
function wpjam_strip_control_characters($text){
	return WPJAM_API::strip_control_characters($text);
}

//获取纯文本
function wpjam_get_plain_text($text){
	return WPJAM_API::get_plain_text($text);
}

//获取第一段
function wpjam_get_first_p($text){
	return WPJAM_API::get_first_p($text);
}

//中文截取方式
function wpjam_mb_strimwidth($text, $start=0, $width=40){
	return WPJAM_API::mb_strimwidth($text, $start, $width);
}

// 检查非法字符
function wpjam_blacklist_check($str){
	return  WPJAM_API::blacklist_check($str);
}

// 获取当前页面 url
function wpjam_get_current_page_url(){
	return WPJAM_API::get_current_page_url();
}

// 获取参数，
function wpjam_get_parameter($parameter, $args=[]){
	return WPJAM_API::get_parameter($parameter, $args);
}

function wpjam_human_time_diff($from, $to=0) {
	return WPJAM_API::human_time_diff($from, $to);
}

function wpjam_get_video_mp4($id_or_url){
	return WPJAM_API::get_video_mp4($id_or_url);
}

function wpjam_get_qqv_mp4($vid){
	return WPJAM_API::get_qqv_mp4($vid);
}

function wpjam_send_json($response, $options = JSON_UNESCAPED_UNICODE, $depth = 512){
	WPJAM_API::send_json($response, $options, $depth);
}

function wpjam_json_encode( $data, $options = JSON_UNESCAPED_UNICODE, $depth = 512){
	return WPJAM_API::json_encode($data, $options, $depth);
}

function wpjam_json_decode($json){
	return WPJAM_API::json_decode($json);
}

function wpjam_remote_request($url, $args=[], $err_args=[]){
	return WPJAM_API::http_request($url, $args, $err_args);
}

function wpjam_get_ua(){
	return WPJAM_API::get_user_agent();
}

function wpjam_get_user_agent(){
	return WPJAM_API::get_user_agent();
}

function wpjam_get_ua_data($ua=''){
	return WPJAM_API::parse_user_agent($ua);
}

function wpjam_parse_user_agent($ua=''){
	return WPJAM_API::parse_user_agent($ua);
}

function wpjam_get_ipdata($ip=''){
	return WPJAM_API::parse_ip($ip);
}

function wpjam_parse_ip($ip=''){
	return WPJAM_API::parse_ip($ip);
}

function wpjam_get_ip(){
	return WPJAM_API::get_ip();
}	

function is_wpjam_json($json=''){
	global $wpjam_json;

	if(!empty($wpjam_json)){
		if($json){
			return ($wpjam_json == $json);
		}else{
			return $wpjam_json;
		}
	}else{
		return false;
	}
}

function wpjam_get_json(){
	global $wpjam_json;

	return $wpjam_json;
}

function is_ipad(){
	return WPJAM_API::is_ipad();
}

function is_iphone(){
	return WPJAM_API::is_iphone();
}

function is_ios(){
	return WPJAM_API::is_ios();
}

function is_mac(){
	return is_macintosh();
}

function is_macintosh(){
	return WPJAM_API::is_macintosh();
}

function is_android(){
	return WPJAM_API::is_android();
}

// 判断当前用户操作是否在微信内置浏览器中
function is_weixin(){ 
	return WPJAM_API::is_weixin();
}

// 判断当前用户操作是否在微信小程序中
function is_weapp(){ 
	return WPJAM_API::is_weapp();
}




function wpjam_get_data_parameter($key){
	$value		= '';
	if(isset($_GET[$key])){
		$value	= $_GET[$key];
	}elseif(isset($_REQUEST['data'])){
		$data	= wp_parse_args($_REQUEST['data']);
		$value	= $data[$key] ?? '';
	}
	
	return $value;
}

// 打印
function wpjam_print_r($value){
	$capability	= (is_multisite())?'manage_site':'manage_options';
	if(current_user_can($capability)){
		echo '<pre>';
		print_r($value);
		echo '</pre>';
	}
}

function wpjam_var_dump($value){
	$capability	= (is_multisite())?'manage_site':'manage_options';
	if(current_user_can($capability)){
		echo '<pre>';
		var_dump($value);
		echo '</pre>';
	}
}


//WP Pagenavi
function wpjam_pagenavi($total=0){
	if(!$total){
		global $wp_query;
		$total = $wp_query->max_num_pages;
	}

	$big = 999999999; // need an unlikely integer
	
	$pagination = array(
		'base'		=> str_replace( $big, '%#%', get_pagenum_link( $big ) ),
		'format'	=> '',
		'total'		=> $total,
		'current'	=> max( 1, get_query_var('paged') ),
		'prev_text'	=> __('&laquo;'),
		'next_text'	=> __('&raquo;'),
		'end_size'	=> 2,
		'mid_size'	=> 2
	);

	echo '<div class="pagenavi">'.paginate_links($pagination).'</div>'; 
}

// 判断一个数组是关联数组，还是顺序数组
function wpjam_is_assoc_array(array $arr){
	if ([] === $arr) return false;
	return array_keys($arr) !== range(0, count($arr) - 1);
}

// 向关联数组指定的 Key 之前插入数据
function wpjam_array_push(&$array, $data=null, $key=false){
	$data	= (array)$data;

	$offset	= ($key===false)?false:array_search($key, array_keys($array));
	$offset	= ($offset)?$offset:false;

	if($offset){
		$array = array_merge(
			array_slice($array, 0, $offset), 
			$data, 
			array_slice($array, $offset)
		);
	}else{	// 没指定 $key 或者找不到，就直接加到末尾
		$array = array_merge($array, $data);
	}
}

function wpjam_localize_script($handle, $object_name, $l10n ){
	wp_localize_script( $handle, $object_name, array('l10n_print_after' => $object_name.' = ' . wpjam_json_encode( $l10n )) );
}


function wpjam_is_mobile_number($number){
	return preg_match('/^0{0,1}(1[3,5,8][0-9]|14[5,7]|166|17[0,1,3,6,7,8]|19[8,9])[0-9]{8}$/', $number);
}


function wpjam_create_meta_table($meta_type, $table=''){
	if(empty($meta_type)){
		return;
	}

	global $wpdb;

	$table	= $table ?: $wpdb->prefix . $meta_type .'meta';
	$column	= sanitize_key($meta_type . '_id');

	// if($wpdb->get_var("show tables like '{$table}'") != $table) {
		$sql	= "CREATE TABLE {$table} (
			meta_id bigint(20) unsigned NOT NULL auto_increment,
			{$column} bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) default NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY {$column} ({$column}),
			KEY meta_key (meta_key(191))
		)";

		echo $sql;

		$wpdb->query($sql);
	// }
}

// function wpjam_is_400_number($number){
// 	return preg_match('/^400(\d{7})$/', $number);
// }

// function wpjam_is_800_number($number){
// 	return preg_match('/^800(\d{7})$/', $number);
// }

function wpjam_is_scheduled_event( $hook ) {	// 不用判断参数
	$crons = _get_cron_array();
	if (empty($crons)) return false;
	
	foreach ($crons as $timestamp => $cron) {
		if (isset($cron[$hook])) return true;
	}

	return false;
}

function wpjam_is_holiday($date=''){
	$date	= ($date)?$date:date('Y-m-d', current_time('timestamp'));
	$w		= date('w', strtotime($date));

	$is_holiday = ($w == 0 || $w == 6)?1:0;

	return apply_filters('wpjam_holiday', $is_holiday, $date);
}

function wpjam_set_cookie($key, $value, $expire){
	$expire	= ($expire < time())?$expire+time():$expire;

	$secure = ('https' === parse_url(get_option('home'), PHP_URL_SCHEME));

	setcookie($key, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure);

    if ( COOKIEPATH != SITECOOKIEPATH ){
        setcookie($key, $value, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure);
    }
    $_COOKIE[$key] = $value;
}


function wpjam_basic_get_default_settings(){
	return [
		'diable_revision'				=> 1,
		'disable_emoji'					=> 1,
		'disable_privacy'				=> 1,
		
		'remove_head_links'				=> 1,
		'remove_capital_P_dangit'		=> 1,

		'order_by_registered'			=> 1,
		'excerpt_optimization'			=> 1,
		'search_optimization'			=> 1,
		'404_optimization'				=> 1,
		'strict_user'					=> 1,
		'show_all_setting'				=> 1,

		'admin_footer_text'				=> '<span id="footer-thankyou">感谢使用<a href="https://cn.wordpress.org/" target="_blank">WordPress</a>进行创作。</span> | <a href="http://wpjam.com/" title="WordPress JAM" target="_blank">WordPress JAM</a>'
	];
}