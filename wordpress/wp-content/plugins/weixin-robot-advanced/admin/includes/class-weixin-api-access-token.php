<?php
class WEIXIN_APIAccessToken extends WPJAM_Model {
	private static $handler;

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('weixin_'.weixin_get_appid().'_api_access_tokens', 'token');
		}
		return static::$handler;
	}

	public static function item_callback($item){
		$today	= date('Y-m-d', current_time('timestamp'));
		if($item['date']){
			if($item['date'] < $today ){
				$item['date'] = '<span style="color:red;">已经过期<br />'.$item['date'].'</span>';
			}else{
				$item['date'] = '<span style="color:green;">未过期<br />'.$item['date'].'</span>';
			}
		}else{
			$item['date'] = '<span style="color:green;">永久</span>';
		}

		return $item;
	}

	public static function get_fields($action_key='', $id=''){
		$fields	= [
			'token'		=> ['title'=>'Token',	'type'=>'text',		'show_admin_column'=>true],
			'date'		=> ['title'=>'过期时间',	'type'=>'date',		'show_admin_column'=>true,	'sortable_columns'=>'meta_value_num',	'description'=>'请根据第三方开发商的需求设置有效期，留空为永久。'],
			'remark'	=> ['title'=>'备注',		'type'=>'textarea',		'show_admin_column'=>true,	'description'=>'请输入该 Token 的用途或者其他备注！']
		];

		if($action_key == 'edit'){
			$fields['token']['type']	= 'view'; 
		}elseif($action_key == 'add'){
			$fields['token']['value']	= wp_generate_password(32, false, false);
		}

		return $fields;
	}
}
