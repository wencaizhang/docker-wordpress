<?php
class WEAPP_Template{
	use WEAPP_Trait;

	protected static $appid;

	public static function get_template_id($key){
		$appid	= self::get_appid();

		if(empty($appid)){
			return [];
		}

		$configs	= self::get_configs();

		if(empty($configs) || empty($configs[$key])){
			return new WP_Error('invild_template_key', '非法消息模板设置');
		}

		$template_ids	= weapp_get_setting('templates', $appid) ?: [];
		
		if($template_ids && isset($template_ids[$key])){
			return $template_ids[$key];
		}
		
		$template_ids	= self::generate();
		if(is_wp_error($template_ids)){
			return $template_ids;
		}

		return $template_ids[$key];
	}

	public static function generate($has_templates=[]){

		$appid	= self::get_appid();

		if(empty($appid)){
			return [];
		}

		$has_templates	= $has_templates ?: weapp()->list_templates();

		if(is_wp_error($has_templates)){
			return $has_templates;
		}

		$has_templates	= wp_list_pluck($has_templates, 'template_id', 'title');
		$template_ids	= [];

		foreach (self::get_configs() as $key => $config) {
			$title	= $config['title'];

			if($has_templates && isset($has_templates[$title])){
				$template_id	= $has_templates[$title];
			}else{
				$template_library  = weapp($appid)->get_template_library($config['id']);

				if(is_wp_error($template_library)){
					continue;
				}

				if($template_library['title'] == $config['title']){
					$response	= weapp($appid)->add_template($config['id'], $config['keyword_id_list']);
					if(is_wp_error($response)){
						continue;
					}
					
					$template_id	= $response['template_id'];
				}
			}

			$template_ids[$key]	= $template_id;
		}

		weapp_update_setting('templates', $template_ids, $appid);

		return $template_ids;
	}

	public static function get_configs(){
		// $templates	= [
		// 	'comment_reply'	=> ['id'=>'AT0782',	'title'=>'留言通知',		'keyword_id_list'=>[9, 11, 22, 25]],
		// 	'approved'		=> ['id'=>'AT0168',	'title'=>'审核通过提醒',	'keyword_id_list'=>[9, 10, 13]],
		// 	'reply_approved'=> ['id'=>'AT0426',	'title'=>'留言入选通知',	'keyword_id_list'=>[1, 2, 3]],
		// ];

		$configs	= [
			// 'comment_replied'	=> ['id'=>'AT0782',	'title'=>'留言通知',		'keyword_id_list'=>[9, 11, 22, 25]],
			'comment_approved'	=> ['id'=>'AT0426',	'title'=>'留言入选通知',	'keyword_id_list'=>[1, 2, 3]],
			'approved'			=> ['id'=>'AT0168',	'title'=>'审核通过提醒',	'keyword_id_list'=>[9, 10, 13]],
		];

		return apply_filters('weapp_template_configs', $configs, self::get_appid());
	}
}