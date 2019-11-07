<?php
add_filter('wpjam_groups_list_table', function(){
	return [
		'title'			=>'讨论组',
		'plural'		=>'wpjam-groups',
		'singular'		=>'wpjam-group',
		'model'			=>'WPJAM_Group',
		'fixed'			=>false,
		'capability'	=>is_multisite() ? 'manage_sites' : 'manage_options',
		'ajax'			=>true,
	];
});

class WPJAM_Group extends WPJAM_Taxonomy{
	public static function list($limit, $offset){

		$switched	= wpjam_topic_switch_to_blog();

		$items		= wpjam_get_terms([
			'taxonomy'		=> 'group', 
			'hide_empty'	=> false, 
			'meta_key'		=> 'order', 
			'orderby'		=> 'meta_value_num',
			'order'			=> 'DESC'
		]);

		$total		= count($items);

		if($switched){
			restore_current_blog();
		}

		return compact('items', 'total');
	}

	public static function item_callback($item){

		$switched	= wpjam_topic_switch_to_blog();

		$item['order']	= get_term_meta($item['id'], 'order', true);

		if($switched){
			restore_current_blog();
		}

		return $item;
	}

	public static function get($id){
		$switched	= wpjam_topic_switch_to_blog();

		$term	= parent::get($id);

		if($term){
			$term['order']	= get_term_meta($id, 'order', true);
		}

		if($switched){
			restore_current_blog();
		}

		return $term;
	}

	public static function insert($data){
		$switched	= wpjam_topic_switch_to_blog();

		$data['taxonomy']	= 'group';
		$data['meta_input']	= ['order'=>$data['order']];

		$result		= parent::insert($data);

		if($switched){
			restore_current_blog();
		}

		return $result;
	}

	public static function update($id, $data){
		$switched	= wpjam_topic_switch_to_blog();

		$data['taxonomy']	= 'group';
		$data['meta_input']	= ['order'=>$data['order']];

		$result		= parent::update($id, $data);

		if($switched){
			restore_current_blog();
		}

		return $result;
	}

	public static function delete($id){
		$switched	= wpjam_topic_switch_to_blog();

		$result		= parent::delete($id);

		if($switched){
			restore_current_blog();
		}

		return $result;
	}

	public static function get_fields($action_key='', $id=0){
		return [
			// 'group'		=> ['title'=>'分组',	'type'=>'select',	'options'=>$groups],
			'name'	=> ['title'=>'名称',		'type'=>'text',		'show_admin_column'=>true],
			'slug'	=> ['title'=>'别名',		'type'=>'text',		'show_admin_column'=>true],
			'order'	=> ['title'=>'排序',		'type'=>'number',	'show_admin_column'=>true],
		];
	}
}