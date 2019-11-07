<?php
add_filter('wpjam_hashtag_tabs', function(){
	return [
		'keywords'	=>['title'=>'内部链接',	'function'=>'list',		'list_table_name'=>'wpjam-hashtags'],
		'setting'	=>['title'=>'#设置',		'function'=>'option',	'option_name'=>'wpjam-hashtag'],
	];
});

add_filter('wpjam_hashtags_list_table', function(){
	return [
		'title'		=>'内部链接',
		'plural'	=>'hashtags',
		'singular'	=>'hashtag',
		'model'		=>'WPJAM_Hashtag',
		'fixed'		=>false,
		'ajax'		=>true
	];
});

class WPJAM_HashtagHandler extends WPJAM_Item{
	public function get_items(){
		$items 	= wpjam_get_setting('wpjam-hashtag', 'links') ?: [];
		return $this->parse_items($items);
	}

	public function update_items($items){
		return wpjam_update_setting('wpjam-hashtag', 'links', $items);
	}
}

class WPJAM_Hashtag extends WPJAM_Model {
	private static $handler;

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler	= new WPJAM_HashtagHandler([
				'unique_key'	=> 'keyword',
				'unique_title'	=> '关键字'
			]);
		}
		return static::$handler;
	}

	public static function get_fields($action_key='', $item_id=0){
		return [
			'keyword'	=> ['title'=>'关键字',	'type'=>'text',	'show_admin_column'=>true,	'class'=>'all-options'],
			'link'		=> ['title'=>'链接',		'type'=>'url',	'show_admin_column'=>true],
		];
	}
}

add_filter('wpjam_hashtag_setting', function(){
	return [
		'fields'	=> [
			'link_hashtag'		=> ['title'=>'内部链接前后显示',	'type'=>'text',	'class'=>'',	'value'=>''],
			'tag_hashtag'		=> ['title'=>'分类标签前后显示',	'type'=>'text',	'class'=>'',	'value'=>'#'],
			'search_hashtag'	=> ['title'=>'搜索结果前后显示',	'type'=>'text',	'class'=>'',	'value'=>'#'],
		],
	];
});