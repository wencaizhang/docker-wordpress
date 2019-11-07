<?php
class WEAPP_AdminTemplate{
	private static $templates;

	public static function get($id){
		if(empty(self::$templates)) {
			self::$templates = [];
		}

		if(isset(self::$templates[$id])) {
			return self::$templates[$id];
		}

		self::$templates[$id]  = weapp()->get_template_library($id);

		return self::$templates[$id];
	}

	public static function choose($id, $data){
		return weapp()->add_template($id,  array_column($data['keyword_list'], 'keyword_id'));
	}

	public static function delete($template_id){
		return weapp()->del_template($template_id);
	}

	// 后台 list table 显示
	public static function list($count, $offset){
		global $current_tab;

		if($current_tab == 'mine'){
			$items	= weapp()->list_templates();
			if(is_wp_error($items)){
				return $items;
			}

			$templates	= WEAPP_Template::generate($items);

			if(is_wp_error($templates)){
				return $templates;
			}

			$total	= count($items);
		}else{
			$result = weapp()->list_template_library($count, $offset);

			if(is_wp_error($result)){
				return $result;
			}

			$items	= $result['list'];
			$total	= $result['total_count'];
		}

		return compact('items', 'total');
	}

	public static function item_callback($item){
		global $current_tab;

		if($current_tab == 'mine'){
			$template_ids		= weapp_get_setting('templates') ?: [];
			$template_keys		= array_flip($template_ids);

			$item['key']		= $template_keys[$item['template_id']] ?? '';
			$item['content']	= wpautop($item['content']);
			$item['example']	= wpautop($item['example']);
		}

		return $item;
	}

	Public static function get_actions(){
		global $current_tab;

		if($current_tab == 'library'){
			return[
				'choose'=>['title'=>'选用']
			];
		}else{
			return [
				'delete'=>['title'=>'删除',	'direct'=>true,	'confirm'=>true,	'bulk'=>true,	'response'=>'list']
			];
		}		
	}

	Public static function get_fields($action_key='', $id=''){
		global $current_tab;

		if($current_tab == 'library'){
			return [
				'id'			=> ['title'=>'ID',			'type'=>'view',	'show_admin_column'=>true],
				'title'			=> ['title'=>'模板标题',		'type'=>'view',	'show_admin_column'=>true],
				'keyword_list'	=> ['title'=>'关键词库',		'type'=>'mu-fields',	'fields'=>[
					'keyword_id'	=> ['title'=>'',	'type'=>'hidden'],
					'name'			=> ['title'=>'关键词内容：',	'type'=>'view'],
					'example'		=> ['title'=>'对应的示例：',	'type'=>'view'],
				]],
			];
		}else{
			return [
				'title'			=> ['title'=>'模板标题',		'type'=>'text',	'show_admin_column'=>true],
				'content'		=> ['title'=>'模板内容',		'type'=>'text',	'show_admin_column'=>true],
				'example'		=> ['title'=>'模板内容示例',	'type'=>'text',	'show_admin_column'=>true],
				'template_id'	=> ['title'=>'模板ID',		'type'=>'text',	'show_admin_column'=>true],
				'key'			=> ['title'=>'Key',			'type'=>'text',	'show_admin_column'=>'only'],
			];
		}

	}
}