<?php
include WEIXIN_ROBOT_PLUGIN_DIR.'admin/includes/class-weixin-api-access-token.php';

add_action('wpjam_weixin_extend_tabs', function($tabs){
	$extend_tabs = [
		'extends'	=> ['title'=>'扩展管理',		'function'=>'option',	'option_name'=>'weixin_'.weixin_get_appid().'_extends', 'page_title'=>'<h2>扩展管理</h2>'],
		// 'campaign'	=> ['title'=>'第三方授权',	'function'=>'option',	'option_name'=>'weixin_'.weixin_get_appid().'_campaigns', 'page_title'=>'<h2>第三方 OAuth 2.0 授权</h2>'],
		// 'token'		=> ['title'=>'接口权限',		'function'=>'list',		'list_table_name'=>'weixin_api_access_tokens'],
		'short_url'	=> ['title'=>'链接缩短',		'function'=>'weixin_short_url_page'],
		'ip_list'	=> ['title'=>'微信IP列表',	'function'=>'weixin_ip_list_page'],
		'clear'		=> ['title'=>'数据清理',		'function'=>'weixin_clear_page'],
	];

	if(weixin_get_type() < 4){
		// unset($extend_tabs['short_url']);
		unset($extend_tabs['campaign']);
	}

	return $extend_tabs;
});


add_filter('wpjam_weixin_api_access_tokens_list_table', function(){
	global $current_tab;

	return [
		'title'				=> '接口权限',
		'singular'			=> 'weixin-api-access-token',
		'plural'			=> 'weixin-api-access-tokens',
		'model'				=> 'WEIXIN_APIAccessToken',
		'ajax'				=> true
	];

});


function weixin_ip_list_page(){
	echo "<h2>微信服务器的IP列表</h2>";
	echo "<p>";
	$ip_list = weixin()->get_callback_ip();
	if(is_wp_error($ip_list)){
		echo $ip_list->get_error_message();
	}else{
		echo implode('<br />', $ip_list);
	}
	echo '</p>';
}

function weixin_short_url_page(){
	global $current_admin_url;

	echo '<h2>长链接转短链接</h2>';

	$form_fields = array(
		'long_url'		=> array('title'=>'', 'type'=>'textarea', 'style'=>'max-width:640px;', 'value'=>'', 'description'=>'请输入需要转换的长链接，支持http://、https://、weixin://wxpay 格式的url'),
	);

	$nonce_action	= 'long2short';

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		$data		= wpjam_get_form_post($form_fields, $nonce_action);
		$long_url	= $data['long_url'];
		
		$form_fields['long_url']['value'] = $long_url;

		$short_url	= weixin()->shorturl($long_url);
		if(is_wp_error($short_url)){
			wpjam_admin_add_error($short_url->get_error_message(),'error');
		}else{
			wpjam_admin_add_error('短链接为： '.$short_url);
		}
	}

	wpjam_form($form_fields, $current_admin_url, $nonce_action, '缩短'); 

}

function weixin_get_tables(){
	$weixin_tables = [
		'weixin_activation' => [
			'weixin_users'	=> '微信用户'
		]
	];

	if(weixin_get_type() == 4) {
		// $weixin_tables['weixin_activation']['weixin_subscribes']	= '微信用户订阅';
	}
	
	return apply_filters('weixin_tables',$weixin_tables);
}

function weixin_clear_page() {
	global $current_admin_url;
	$action	= isset($_GET['action'])?$_GET['action']:'';
	?>
	<h2>数据检测和清理</h2>
	<p>
		微信机器人 WordPress 插件高级版已经尽量做好了自动创建数据库和缓存的自动更新，但是还是会不可避免出现一些不可知的问题和异常。<br />
	</p>
	
	<p><a href="<?php echo $current_admin_url.'&action=table'; ?>" class="button button-primary">检查数据表</a></p>

	<?php if($action == 'table'){ ?><ol>
		<?php foreach (weixin_get_tables() as $function => $weixin_tables) {
			call_user_func($function);
			foreach ($weixin_tables as $weixin_table_name => $weixin_table_title) {
				echo '<li><strong>'.$weixin_table_title.'</strong>表已经创建</li>';	
			}	
		}
		?>
	</ol><?php } ?>


	<p><a href="<?php echo $current_admin_url.'&action=cache' ?>" class="button button-primary">删除缓存</a></p>

	<?php if($action == 'cache'){  ?><ol>
		<?php 

		$weixin_caches = array(
			'自定义回复'			=> 'weixin_custom_replies',
			'内置回复'			=> 'weixin_builtin_replies',
			'微信 Access Token'	=> 'weixin_access_token'
		);

		foreach ($weixin_caches as $name => $cache_key) {
			delete_transient($cache_key);
			echo '<li><strong>'.$name.'缓存</strong>已经清除</li>';
		}

		?>
	</ol><?php } ?>

	<p><a href="<?php echo $current_admin_url.'&action=clear_quota' ?>" class="button button-primary">API调用次数清零</a></p>

	<?php if($action == 'clear_quota'){ ?><p>
		<?php 
		$response = weixin()->clear_quota();

		if(is_wp_error($response)){
			echo $response->get_error_message();
		} else{
			echo 'API调用次数已经清零';
		}
		?>
	</p><?php } ?>

	<?php
}


add_filter('wpjam_settings', function($wpjam_settings){
	global $current_option;

	$appid	= weixin_get_appid();

	if($current_option == 'weixin_'.$appid.'_extends' || $current_option == 'weixin_extends'){
		$extends_fields		= [];
		$weixin_extend_dir	= WEIXIN_ROBOT_PLUGIN_DIR.'/extends';
		
		if($weixin_extends = weixin_get_extends()){	 // 已激活的优先
			foreach ($weixin_extends as $weixin_extend_file => $value) {
				if($value){
					if(is_file($weixin_extend_dir.'/'.$weixin_extend_file) && $data = get_plugin_data($weixin_extend_dir.'/'.$weixin_extend_file)){
						$extends_fields[$weixin_extend_file] = ['title'=>$data['Name'],	'type'=>'checkbox',	'description'=>$data['Description']];
					}
				}
			}
		}

		if ($weixin_extend_handle = opendir($weixin_extend_dir)) {   
			while (($weixin_extend_file = readdir($weixin_extend_handle)) !== false) {
				if ($weixin_extend_file!="." && $weixin_extend_file!=".." && is_file($weixin_extend_dir.'/'.$weixin_extend_file) && empty($weixin_extends[$weixin_extend_file])) {
					if(pathinfo($weixin_extend_file, PATHINFO_EXTENSION) == 'php'){
						if(($data = get_plugin_data($weixin_extend_dir.'/'.$weixin_extend_file)) && $data['Name']){
							$extends_fields[$weixin_extend_file] = ['title'=>$data['Name'],	'type'=>'checkbox',	'description'=>$data['Description']];
						}
					}
				}
			}   
			closedir($weixin_extend_handle);   
		}

		if(is_multisite() && !is_network_admin()){
			$sitewide_extends = get_site_option('weixin_extends');
			unset($sitewide_extends['plugin_page']);
			if($sitewide_extends){
				foreach ($sitewide_extends as $extend_file => $value) {
					if(!$value) continue;
					unset($extends_fields[$extend_file]);
				}
			}
		}

		$wpjam_settings[$current_option]	= array('fields'=>$extends_fields);
	}elseif($current_option == 'weixin_'.$appid.'_campaigns'){
		$wpjam_settings[$current_option]	= array(
			'option_type'	=> 'single',
			'title'			=> '', 
			'summary'		=> '为了服务号的 OAuth 2.0 的第三方认证接口不会被滥用，请输入第三方活动的域名',
			'fields'		=> array(
				'weixin_'.$appid.'_campaigns'	=> array('title'=> '', 'type'=> 'mu-text', 'item_type'=>'url')
			)
		);
	}
	
	return $wpjam_settings;
});
