<?php
class WEIXIN_UserTag  {
	public static function get($id){
		$tags = self::get_tags();
		
		if(is_wp_error($tags)){
			return $tags;
		}

		return $tags[$id];
	}

	public static function get_tags(){
		return weixin()->get_tags();
	}

	public static function insert($data){
		$tag = weixin()->create_tag($data['name']);

		if(is_wp_error($tag)){
			return $tag;
		}

		return $tag['tag']['id'];
	}

	public static function update($id, $data){
		$tag	= self::get($id);

		if(is_wp_error($tag)){
			return $tag;
		}

		if(trim($data['name']) == trim($tag['name'])){
			return true;
		}

		return weixin()->update_tag($id, $data['name']);
	}

	public static function delete($id){
		return weixin()->delete_tag($id);
	}

	// 后台 list table 显示
	public static function list($limit, $offset){

		$items = self::get_tags();

		if(is_wp_error($items)){
			wpjam_admin_add_error($items->get_error_code().'：'. $items->get_error_message(),'error');
			return;
		}

		if(isset($_GET['orderby'])){
			$order = ($_GET['order'] == 'desc')?'DESC':'ASC';
			$items = wp_list_sort($items, $_GET['orderby'], $order);
		}

		$total = count($items);

		return compact('items', 'total');
	}

	public static function item_callback($item){
		if($item['id'] < 100){
			unset($item['row_actions']);
		}

		$item['count']	= '<a href="'.admin_url('admin.php?page=weixin-users&tab=list&tagid='.$item['id']).'">'.$item['count'].'</a>';
		return $item;
	}

	public static function get_fields($action_key='', $id=0){
		return array(
			'name'	=> array('title'=>'名称',	'type'=>'text',	'show_admin_column'=>true,	'description'=>'30个字符以内'),
			'id'	=> array('title'=>'ID',		'type'=>'text',	'show_admin_column'=>'only'),
			'count'	=> array('title'=>'数量',	'type'=>'text',	'show_admin_column'=>'only',	'sortable_column'=>true),
		);
	}
}