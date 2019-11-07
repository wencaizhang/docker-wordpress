<?php
function weapp_setting() {
	
	$sections	= [];

	$weapp_appid	= weapp_get_appid();

	if(is_multisite()){
		$capability		= 'manage_weapp_' . $weapp_appid;
		$basic_fields	= [
			'appid'		=> ['title'=>'小程序ID',		'type'=>'view', 'value'=>$weapp_appid],
			'secret'	=> ['title'=>'小程序密钥',	'type'=>'view',	'value'=>weapp_get_setting('secret')],
		];
	}else{
		$capability		= 'manage_weapp' ;

		$basic_fields	= [
			'appid'		=> ['title'=>'小程序ID',		'type'=>'text',	'required'=>'required',	'class'=>'all-options'],
			'secret'	=> ['title'=>'小程序密钥',	'type'=>'text',	'required'=>'required'],
		];
	}

	$sections['basic'] = [
		'title' =>'基本设置',
		'fields'=>$basic_fields,
	];

	$feature_fields	= [
		'appid'			=> ['title'=>'小程序ID',			'type'=>'view', 	'value'=>$weapp_appid],
		// 'authorization'	=> ['title'=>'用户授权登录',		'type'=>'checkbox',	'description'=>'用户必须要授权登录，即用户信息过期返回错误。'],
		'template'		=> ['title'=>'模板消息配置',		'type'=>'checkbox',	'description'=>'在后台即可配置小程序模板消息。'],
		'qrcode'		=> ['title'=>'二维码管理',		'type'=>'checkbox',	'description'=>'在后台管理小程序二维码。'],
		'message'		=> ['title'=>'客服消息设置',		'type'=>'checkbox',	'description'=>'启用客服消息功能，实现小程序自定义回复。'],
		'custom_fields'	=> ['title'=>'用户自定义字段',	'type'=>'checkbox',	'description'=>'用户可以自定微信用户表字段，适应更多业务需求。'],
	];

	if(!is_multisite()){
		unset($feature_fields['appid']);
	}

	$sections['features'] = [
		'title' =>'功能设置',
		'fields'=>$feature_fields,
	];
	
	$sections['message'] = [
		'title'  =>'客服消息',
		'summary'=>'开启消息和事件接收之后，收可以使用客服消息接口进行异步回复！',
		'fields' =>[
			'token'				=> ['title'=>'消息令牌',		'type'=>'text', 'class'=>'all-options'],
			'encodingaeskey'	=> ['title'=>'消息加密密钥',	'type'=>'text'],
			'url'				=> [
				'title'=>'服务器地址',
				'type' =>'view',
				'value'=>home_url() . '/api/mag.message.reply.json?appid=' . $weapp_appid,
			],
		],
	];
	
	$sections['user_fields'] = [
		'title'  =>'用户自定义字段',
		'summary'=>'通过自定义字段来扩展小程序用户表！',
		'fields' =>[
			'user_fields'	=> ['title'=>'自定义字段', 'type'=>'mu-fields', 'fields'=>[
				'field'		=> ['title'=>'字段',	'type'=>'text',		'class'=>'all-options'],
				'type'		=> ['title'=>'类型',	'type'=>'select',	'options'=>[''=>'','int'=>'整形','varchar'=>'字符','text'=>'文本']],
				'length'	=> ['title'=>'长度',	'type'=>'number',	'class'=>'small-text'],
			]]
		],
	];

	if(is_multisite() && !current_user_can('manage_sites')){
		unset($sections['user_fields']);
	}

	$sections	= apply_filters('weapp_setting_sections', $sections);

	$field_validate = function ($value){
		if(!is_multisite()){
			if(empty($value['appid'])){
				wpjam_send_json([
					'errcode'	=> 'empty_appid',
					'errmsg'	=> '小程序ID不能为空'
				]);
			}

			if(empty($value['secret'])){
				wpjam_send_json([
					'errcode'	=> 'empty_secret',
					'errmsg'	=> '小程序密钥不能为空'
				]);
			}

			if($value['appid']){
				if(!weapp_exists($value['appid'], $value['secret'])){
					wpjam_send_json(new WP_Error('invalid_appid', '小程序ID或者小程序密钥错误，请仔细检查后重新输入。'));
				}
			}

			WEAPP_User::set_appid($value['appid']);
			WEAPP_User::create_table();
		}else{
			wp_cache_delete(get_current_blog_id(), 'weapp_settings');	// 清理缓存
			WEAPP_User::create_table();
		}

		$table	= WEAPP_User::get_table();

		if(!empty($value['custom_fields'])){ 
			$user_fields	= $value['user_fields'] ?? [];

			if($user_fields){
				$weapp_appid	= weapp_get_appid();
				
				if($old_user_fileds = weapp_get_setting('user_fields')){
					$old_user_fileds	= array_filter($old_user_fileds, function($old_user_filed){
						return $old_user_filed['field'] && $old_user_filed['type'];
					});
				}

				if($old_user_fileds){
					$old_user_fileds	= array_combine(array_column($old_user_fileds, 'field'), $old_user_fileds);
				}

				global $wpdb;
				foreach ($user_fields as $user_field) {
					$user_field_key		= $user_field['field'];
					$user_field_type	= $user_field['type'] ?? '';
					$user_field_length	= $user_field['length'] ?? 0;

					if(empty($user_field_key) || empty($user_field_type) ){
						continue;
					}

					$old_user_filed	= $old_user_fileds[$user_field_key] ?? [];

					if($old_user_filed){
						if($user_field_type == 'text'){
							if($old_user_filed['type'] == $user_field_type){
								continue;
							}
						}else{
							if($old_user_filed['type'] == $user_field_type && $old_user_filed['length'] == $user_field_length){
								continue;
							}
						}
					}

					if($user_field_type != 'text'){
						if(empty($user_field_length)){
							continue;
						}

						if($user_field_type == 'int'){
							if($user_field_length > 10){
								$user_field_type	= 'bigint';
							}
						}

						$user_field_type	.= '('.$user_field_length.')';
					}

					if ($wpdb->get_var("SHOW COLUMNS FROM `{$table}` LIKE '{$user_field_key}'") != $user_field_key) {	
						$sql = "ALTER TABLE $table ADD COLUMN `{$user_field_key}` {$user_field_type} NOT NULL";
					}else{
						$sql = "ALTER TABLE $table CHANGE COLUMN `{$user_field_key}` `{$user_field_key}` {$user_field_type} NOT NULL";
					}

					$wpdb->query($sql);
				}
			}
		}

		return $value;
	};

	$ajax	= false;

	return compact('sections', 'capability', 'ajax', 'field_validate');
}
add_filter('wpjam_weapp_' . weapp_get_appid() . '_setting', 'weapp_setting');
add_filter('wpjam_weapp_setting', 'weapp_setting');


add_action('admin_head',function(){
	?>
	<script type="text/javascript">
	jQuery(function($){
		<?php if(is_multisite()) { ?>
			var appid = <?php echo "'".weapp_get_appid()."'";?>;
		<?php }else{ ?>
			var appid = $('input#appid').val();
		<?php } ?>

		function weapp_changed(){
			if(appid){
				$('#tab_title_features').show();
			}else{
				$('#tab_title_features').hide();
			}

			$('input#message').change();
			$('input#custom_fields').change();
		}

		$('input#message').change(function(){
			if(appid && $('input#message:checked').val()){
				$('#tab_title_message').show();
			}else{
				$('#tab_title_message').hide();
			}
		});

		$('input#custom_fields').change(function(){
			if(appid && $('input#custom_fields:checked').val()){
				$('#tab_title_user_fields').show();
			}else{
				$('#tab_title_user_fields').hide();
			}
		});

		weapp_changed();
		
		$('body').on('option_action_success', function(e, response){
			<?php if(!is_multisite()) { ?> appid = response.data.appid; <?php } ?>
			weapp_changed();
		});
	});
	</script>
	<?php
});