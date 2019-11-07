<?php
class WEAPP_AdminSetting extends WEAPP_Setting {
	public static function list($limit, $offset){
		if(!is_super_admin()){
			self::get_handler()->where('blog_id', get_current_blog_id());
		}

		if(empty($_GET['orderby'])){
			self::get_handler()->order_by('time');
		}

		return parent::list($limit, $offset);
	}

	public static function item_callback($item){
		$item['time']	= get_date_from_gmt(date('Y-m-d H:i:s',$item['time']));

		if(is_multisite()){
			$item['blog_id']	= '<a href="'.get_admin_url($item['blog_id'],'admin.php?page=weapp-settings').'">'.get_blog_option($item['blog_id'], 'blogname').'</a>';
		}

		if($item['component_blog_id']){
			unset($item['row_actions']['edit']);
			unset($item['row_actions']['delete']);
		}

		return $item;
	}

	public static function get_actions(){
		return [
			'add'		=> ['title'=>'新增'],
			'edit'		=> ['title'=>'编辑'],
			'delete'	=> ['title'=>'删除',	'direct'=>true,	'confirm'=>true],
		];
	}

	public static function get_fields($action_key='', $id=''){
		$fields = [
			'name'				=> ['title'=>'小程序名',		'type'=>'text',	'show_admin_column'=>true, 	'required'],
			'appid'				=> ['title'=>'小程序ID',		'type'=>'text',	'show_admin_column'=>true,	'required'],
			'secret'			=> ['title'=>'小程序密钥',	'type'=>'text',	'required'],
			'blog_id'			=> ['title'=>'所属站点',		'type'=>'text',	'show_admin_column'=>true,	'value'=>get_current_blog_id()],
			'component_blog_id'	=> ['title'=>'第三方平台',	'type'=>'text',	'show_admin_column'=>'only'],
			'time'				=> ['title'=>'添加时间',		'type'=>'view',	'show_admin_column'=>'only'],
		];

		if(!is_multisite()){
			unset($fields['component_blog_id']);
		}

		return $fields;
	}
}