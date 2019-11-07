<?php
add_filter('wpjam_taxonomy_args_setting', function(){
	$fields	= [];

	$taxonomies = get_taxonomies(['hierarchical'=>true,'show_ui'=>true],'objects');

	foreach ($taxonomies as $taxonomy=>$taxonomy_obj) {
		$sub	= [];
		
		if(isset($taxonomy_obj->levels) && $taxonomy_obj->levels_source == 'code'){
			$sub[$taxonomy.'_levels']	= ['title'=>'层级',	'type'=>'view',		'value'=>'代码设置为：'.$taxonomy_obj->levels.'层'];
		}else{
			$sub[$taxonomy.'_levels']	= ['title'=>'层级',	'type'=>'number',	'name'=>'levels['.$taxonomy.']',	'value'=>0,	'class'=>'small-text',	'description'=>'层'];
		}

		if(isset($taxonomy_obj->order) && $taxonomy_obj->order_source == 'code'){
			$sub[$taxonomy.'_order']	= ['title'=>'排序',	'type'=>'view',	'value'=>'代码设置为：支持'];
		}else{
			$sub[$taxonomy.'_order']	= ['title'=>'排序',	'type'=>'checkbox',	'name'=>'order['.$taxonomy.']',	'value'=>0,	'description'=>'支持拖动排序'];
		}

		$fields[$taxonomy.'_set']	= ['title'=>$taxonomy_obj->label,	'type'=>'fieldset',	'fields'=>$sub];
	}

	$summary	= '请设置下面分类的层级和排序，层级为0则不限制层级。';

	return compact('fields', 'summary');	
});

add_filter('pre_option_wpjam_taxonomy_args', function($value){
	return [
		'levels'	=> get_option('wpjam_taxonomy_levels') ?: [],
		'order'		=> get_option('wpjam_taxonomy_order') ?: []
	]; 
});

add_filter('pre_update_option_wpjam_taxonomy_args', function($value, $old_value, $option){
	$levels	= $value['levels'] ?? [];
	$order	= $value['order'] ?? [];
	update_option('wpjam_taxonomy_levels', $levels);
	update_option('wpjam_taxonomy_order', $order);
	return $old_value;
}, 10, 3);