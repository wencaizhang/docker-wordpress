<?php
if(!function_exists('user_can_for_blog')){
	function user_can_for_blog($user, $blog_id, $capability){
		$switched = is_multisite() ? switch_to_blog( $blog_id ) : false;

		if ( ! is_object( $user ) ) {
			$user = get_userdata( $user );
		}

		if ( ! $user || ! $user->exists() ) {
			return false;
		}

		if ( empty( $user ) ) {
			if ( $switched ) {
				restore_current_blog();
			}
			return false;
		}

		$args = array_slice( func_get_args(), 2 );
		$args = array_merge( array( $capability ), $args );

		$can = call_user_func_array( array( $user, 'has_cap' ), $args );

		if ( $switched ) {
			restore_current_blog();
		}

		return $can;
	}
}

if(!function_exists('get_metadata_by_value')){
	function get_metadata_by_value($meta_type, $meta_value, $meta_key=''){
		global $wpdb;

		if(!$meta_type || empty($meta_value)){
			return false;
		}

		$meta_value	= maybe_serialize($meta_value);

		$table = _get_meta_table( $meta_type );
		if(!$table){
			return false;
		}

		if($meta_key){
			$meta	= $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE meta_value = %s AND meta_key = %s", $meta_value, $meta_key));
		}else{
			$meta	= $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE meta_value = %s", $meta_value));
		}

		if(empty($meta)){
			return false;
		}

		if(isset($meta->meta_value)){
			$meta->meta_value = maybe_unserialize( $meta->meta_value );
		}

		return $meta;
	}
}


// 兼容代码
if(!function_exists('wp_cache_delete_multi')){
	function wp_cache_delete_multi( $keys, $group = '' ) {
		foreach ($keys as $key) {
			wp_cache_delete($key, $group);
		}

		return true;
	}
}
	
if(!function_exists('wp_cache_get_multi')){	
	function wp_cache_get_multi( $keys, $group = '' ) {

		$datas = [];

		foreach ($keys as $key) {
			$datas[$key] = wp_cache_get($key, $group);
		}

		return $datas;
	}
}

if(!function_exists('wp_cache_get_with_cas')){
	function wp_cache_get_with_cas( $key, $group = '', &$cas_token = null ) {
		return wp_cache_get($key, $group);
	}
}

if(!function_exists('wp_cache_cas')){
	function wp_cache_cas( $cas_token, $key, $data, $group = '', $expire = 0  ) {
		return wp_cache_set($key, $data, $group, $expire);
	}
}

function wpjam_get_taxonomy_levels($taxonomy){
	$taxonomy_obj	= get_taxonomy($taxonomy);
	return $taxonomy_obj->levels ?? 0;
}

function wpjam_api_set_response(&$response){
	global $wp_query;

	if($wp_query->have_posts()){

		if(isset($_GET['s'])){
			$response['total_pages']	= (int)$wp_query->max_num_pages;
			$response['current_page']	= (int)(isset($_GET['paged'])?$_GET['paged']:1);
		}else{
			$response['has_more']	= ($wp_query->max_num_pages>1)?1:0;

			$first_post_time = (int)strtotime(get_gmt_from_date($wp_query->posts[0]->post_date)); 
			$post = end($wp_query->posts);
			$last_post_time = (int)strtotime(get_gmt_from_date($post->post_date));

			$first_time	= isset($_GET['first_time'])?(int)$_GET['first_time']:'';
			$last_time	= isset($_GET['last_time'])?(int)$_GET['last_time']:'';

			if(!$first_time && !$last_time){								//第一次加载，需要返回first_time和最后last_time
				$response['first_time']	= $first_post_time;
				$response['last_time'] 	= $last_post_time;
			}elseif($first_time && $wp_query->max_num_pages > 1){			//下拉刷新，数据超过一页：需要返回fist_time和last_time，客户端需要把所有数据清理
				$response['first_time']	= $first_post_time;
				$response['last_time'] 	= $last_post_time;
			}elseif($first_time && $wp_query->max_num_pages < 2){			//下拉刷新，数据不超过一页：需要返回first_time，不需要last_time
				$response['first_time']	= $first_post_time;
			}elseif($last_time){											//加载更多：不需要first_time，需要返回last_time
				$response['last_time']	= $last_post_time;
			}

			$response['total_pages']	= (int)$wp_query->max_num_pages;
			$response['current_page']	= (int)(isset($_GET['paged'])?$_GET['paged']:1);
		}
	}
}

function wpjam_api_signon(){
	$user = $_SERVER['PHP_AUTH_USER'] ?? '';
	$pass = $_SERVER['PHP_AUTH_PW'] ?? '';

	if(empty($user) || empty($pass))	return false;

	$wp_user = wp_signon(array(
		'user_login'	=> $user,
		'user_password'	=> $pass,
	));

	if(is_wp_error($wp_user))	return false;

	if(current_user_can('mamage_options'))	return true;
	
	return false;
}

function wpjam_get_post_type_setting($post_type){
	$settings	= get_option('wpjam_post_types') ?: [];
	return $settings[$post_type] ?? [];
}

if(!function_exists('get_post_excerpt')){   
	//获取日志摘要
	function get_post_excerpt($post=null, $excerpt_length=240){
		return wpjam_get_post_excerpt($post, $excerpt_length);
	}
}

/* tag thumbnail */
function wpjam_has_tag_thumbnail(){
	return wpjam_has_term_thumbnail();
}

function wpjam_get_tag_thumbnail_url($term=null, $size='full', $crop=1, $retina=1){
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $retina);
}

function wpjam_get_tag_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-tag-image", $retina=2){
	return wpjam_get_term_thumbnail($term, $size, $crop, $class, $retina);
}

function wpjam_tag_thumbnail($size='thumbnail', $crop=1, $class="wp-tag-image", $retina=2){
	wpjam_term_thumbnail($size, $crop, $class, $retina);
}

/* category thumbnail */
function wpjam_has_category_thumbnail(){
	return wpjam_has_term_thumbnail();
}

function wpjam_get_category_thumbnail_url($term=null, $size='full', $crop=1, $retina=1){
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $retina);
}

function wpjam_get_category_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-category-image", $retina=2){
	return wpjam_get_term_thumbnail($term, $size, $crop, $class, $retina);
}

function wpjam_category_thumbnail($size='thumbnail', $crop=1, $class="wp-category-image", $retina=2){
	wpjam_term_thumbnail($size, $crop, $class, $retina);
}


function wpjam_cdn_content($content){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_content_images');
	return wpjam_content_images($content);
}

function wpjam_get_content_remote_img_url($img_url, $post_id=0){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_content_remote_image_url');
	return wpjam_get_content_remote_image_url($img_url, $post_id);	
}

function wpjam_get_post_first_image($post=null, $size='full'){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_post_first_image_url');
	return wpjam_get_post_first_image_url($post=null, $size='full');
}


function wpjam_is_mobile() {
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wp_is_mobile');
	return wp_is_mobile();
}

function get_post_first_image($post_content=''){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_post_first_image');
	return wpjam_get_post_first_image($post_content);
}

function wpjam_get_post_image_url($image_id, $size='full'){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wp_get_attachment_image_url');
	
	if($thumb = wp_get_attachment_image_src($image_id, $size)){
		return $thumb[0];
	}
	return false;	
}

function wpjam_get_post_thumbnail_src($post=null, $size='thumbnail', $crop=1, $retina=1){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_post_thumbnail_url');
	return wpjam_get_post_thumbnail_url($post, $size, $crop, $retina);
}

function wpjam_get_post_thumbnail_uri($post=null, $size='full'){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_post_thumbnail_url');
	return wpjam_get_post_thumbnail_url($post, $size);
}

function wpjam_get_default_thumbnail_src($size){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_default_thumbnail_url');
	return wpjam_get_default_thumbnail_url($size);
}

function wpjam_get_default_thumbnail_uri(){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_default_thumbnail_url');
	return wpjam_get_default_thumbnail_url('full');
}

function wpjam_get_category_thumbnail_src($term=null, $size='thumbnail', $crop=1, $retina=1){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $retina);	
}

function wpjam_get_category_thumbnail_uri($term=null){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, 'full');
}

function wpjam_get_tag_thumbnail_src($term=null, $size='thumbnail', $crop=1, $retina=1){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $retina);	
}

function wpjam_get_tag_thumbnail_uri($term=null){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, 'full');
}

function wpjam_get_term_thumbnail_src($term=null, $size='thumbnail', $crop=1, $retina=1){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $retina);	
}

function wpjam_get_term_thumbnail_uri($term=null){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, 'full');
}