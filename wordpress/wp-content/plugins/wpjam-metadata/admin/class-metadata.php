<?php
class WPJAM_MetaData{
	public static function get_meta_type(){
		global $plugin_page;
		return str_replace(['wpjam-','-metas'], '', $plugin_page);
	}

	public static function get_mode(){
		$object_key	= self::get_object_key();
		$object_id	= wpjam_get_data_parameter($object_key);
		$meta_key 	= wpjam_get_data_parameter('meta_key');

		$mode		= wpjam_get_data_parameter('mode');

		if(!$mode){
			$mode	= ($object_id || $meta_key) ? 'list' : 'summary';
		}

		return $mode;
	}

	public static function get_meta_id_key(){
		$meta_type	= self::get_meta_type();
		return 'user' == $meta_type ? 'umeta_id' : 'meta_id';
	}

	public static function get_object_key(){
		$meta_type	= self::get_meta_type();
		return $meta_type.'_id';
	}

	public static function get_primary_key(){
		$mode	= self::get_mode();
		if($mode == 'summary'){
			return 'meta_key';
		}else{
			return self::get_meta_id_key();
		}
	}

	public static function get($id){
		$meta_type	= self::get_meta_type();
		$mode		= self::get_mode();
		if($mode == 'summary'){
			global $wpdb;
			$table	= _get_meta_table($meta_type);
			$count	= $wpdb->get_var($wpdb->prepare("SELECT count(*) as count FROM {$table} WHERE meta_key = %s", $id), ARRAY_A);
			return ['meta_key'=>$id, 'count'=>$count];
		}else{
			return (array)get_metadata_by_mid($meta_type, $id);
		}
	}

	public static function get_by_ids($ids){
		$values	= [];

		foreach($ids as $id){
			$values[$id]	= self::get($id);
		}

		return $values;
	}

	public static function insert($data){
		$meta_key	= trim($data['key']);
		$meta_value	= trim($data['value']);
		
		$meta_type	= self::get_meta_type();
		$object_key	= self::get_object_key();

		$object_id	= $data[$object_key];

		if(empty($meta_key)){
			return new WP_Error('empty_meta_key', 'meta_key 不能为空');
		}

		if(metadata_exists($meta_type, $object_id, $meta_key)){
			return new WP_Error('meta_key_exits', $object_key.' 为 '.$object_id.' 的 meta_key 已存在');
		}

		$result	= add_metadata($meta_type, $object_id, $meta_key, $meta_value);

		if(!$result){
			return new WP_Error('add_meta_key_failed', 'meta 添加失败');
		}

		return $meta_key;
	}

	public static function update($id, $data){
		$new_meta_value	= trim($data['meta_value']);

		$meta			= self::get($id);
		$meta_value		= $meta['meta_value']; 

		if($new_meta_value == $meta_value){
			return new WP_Error('meta_value_not_modified', 'meta_value 未修改');
		}

		$meta_type	= self::get_meta_type();
		return update_metadata_by_mid($meta_type, $id, $new_meta_value);;
	}

	public static function delete($id, $data=[]){
		$meta_type	= self::get_meta_type();
		$mode		= self::get_mode();

		if($mode == 'list'){
			return delete_metadata_by_mid($meta_type, $id);
		}else{
			$meta_key2	= $data['meta_key2'] ?? '';

			if($id == $meta_key2){
				return delete_metadata($meta_type, null, $id, '', true);	
			}else{
				return new WP_Error('invalid_meta_key','确认输入的 meta_key 不正确。');
			}
		}	
	}

	public static function replace($id, $data){
		$mode	= self::get_mode();
		if($mode == 'list'){
			$search		= $data['search'];
			$replace	= $data['replace'];

			if(empty($search)){
				return new WP_Error('empty_search', '搜索值不能为空');
			}

			if($search == $replace){
				return new WP_Error('same_search_replace', '搜索值和替换值相同');
			}

			$meta			= self::get($id);
			$meta_value		= $meta['meta_value']; 
			$new_meta_value	= str_replace_deep($search, $replace, $meta_value);

			if($meta_value != $new_meta_value){
				$meta_type	= self::get_meta_type();
				return update_metadata_by_mid($meta_type, $id, $new_meta_value);
			}

			return true;
		}
	}

	public static function rename($meta_key, $data){
		$new_meta_key	= trim($data['new_meta_key']);

		if(empty($new_meta_key)){
			return new WP_Error('empty_new_meta_key', '新的 meta_key 不能为空');
		}

		if($new_meta_key == $meta_key){
			return new WP_Error('meta_key_not_modified', 'meta_key 未修改');
		}

		global $wpdb;

		$meta_type	= self::get_meta_type();
		$table		= _get_meta_table($meta_type);

		if($wpdb->query($wpdb->prepare("SELECT * FROM {$table} where meta_key = %s", $new_meta_key))){
			return new WP_Error('meta_key_exits', '新的 meta_key 已存在');
		}

		$items	= $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} where meta_key = %s", $meta_key), ARRAY_A);

		if(empty($items)){
			return new WP_Error('empty_meta_key', 'meta_key 已重命名');
		}

		$id_key	= self::get_meta_id_key();

		foreach($items as $item){
			$meta_id	= $item[$id_key];
			$meta_value	= $item['meta_value'];

			update_metadata_by_mid($meta_type, $meta_id, $meta_value, $new_meta_key);
		}

		return true;
	}

	public static function views(){
		$current	= self::get_mode();
		$views		= []; 

		foreach (['summary'=>['dashicon'=>'excerpt-view','title'=>'汇总'], 'list'=>['dashicon'=>'list-view','title'=>'列表']] as $key => $mode) {
			$class			= $key == $current ? 'current' : '';
			$views[$key]	= wpjam_get_list_table_filter_link(['mode'=>$key], '<span class="dashicons dashicons-'.$mode['dashicon'].'"></span> '.$mode['title'].'模式', $class);
		}

		return $views;
	}

	public static function query_items($limit, $offset){
		global $wpdb;
		
		$mode		= self::get_mode();
		$meta_type	= self::get_meta_type();
		$table		= _get_meta_table($meta_type);
		$search		= $_REQUEST['s'] ?? '';

		if($mode == 'summary'){
			if($search){
				$items	= $wpdb->get_results($wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS meta_key, count(*) as count FROM {$table} WHERE meta_key like %s GROUP BY meta_key ORDER BY count DESC, meta_key ASC LIMIT $offset, $limit", '%'.$search.'%'), ARRAY_A);
			}else{
				$items	= $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS meta_key, count(*) as count FROM {$table} WHERE meta_key NOT LIKE '%_capabilities' GROUP BY meta_key ORDER BY count DESC, meta_key ASC LIMIT $offset, $limit", ARRAY_A);	
			}
		}else{
			$id_key		= self::get_meta_id_key();
			$object_key	= self::get_object_key();

			if($search){
				$items	= $wpdb->get_results($wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM {$table} where meta_value like %s OR meta_key like %s OR {$object_key} like %s ORDER BY {$id_key} DESC LIMIT $offset, $limit", '%'.$search.'%', '%'.$search.'%', '%'.$search.'%'), ARRAY_A);
			}else{
				if($object_id = wpjam_get_data_parameter($object_key)){
					$items	= $wpdb->get_results($wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM {$table} where {$object_key} = %d ORDER BY {$id_key} DESC LIMIT $offset, $limit", $object_id), ARRAY_A);
				}elseif($meta_key = wpjam_get_data_parameter('meta_key')){
					$items	= $wpdb->get_results($wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM {$table} where meta_key = %s ORDER BY {$id_key} DESC LIMIT $offset, $limit", $meta_key), ARRAY_A);
				}else{
					$items	= $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS * FROM {$table} ORDER BY {$id_key} DESC LIMIT $offset, $limit", ARRAY_A);
				}
			}

			$object_ids	= wp_list_pluck($items, $object_key);
			
			if($meta_type == 'post'){
				_prime_post_caches($object_ids, false, false);
			}elseif($meta_type == 'term'){
				_prime_term_caches($object_ids, false);
			}elseif($meta_type == 'user'){
				cache_users($object_ids);
			}elseif($meta_type == 'comment'){
				_prime_comment_caches($object_ids);
			}		
		}

		$total	= $wpdb->get_var("SELECT FOUND_ROWS()");

		return compact('items', 'total');
	}

	public static function get_object_name($item){
		$meta_type	= self::get_meta_type();
		$object_id	= $item[$meta_type.'_id'] ?? 0;

		if(!$object_id){
			return '';
		}

		if($meta_type == 'post'){
			return get_the_title($object_id) ?: '不存在';
		}elseif($meta_type == 'term'){
			$term	= get_term($object_id);

			return ($term && !is_wp_error($term)) ? $term->name : '不存在';
		}elseif($meta_type == 'user'){
			$user	= get_userdata($object_id);

			return ($user && !is_wp_error($user)) ? $user->display_name : '不存在';
		}elseif($meta_type == 'comment'){
			$comment	= get_comment($object_id);

			return ($comment && !is_wp_error($comment)) ? $comment->comment_content : '不存在';
		}

		return '';
	}

	public static function item_callback($item){
		$meta_type	= self::get_meta_type();
		$mode		= self::get_mode();

		if($mode == 'list'){
			$item[$meta_type]	= self::get_object_name($item);

			$object_key	= self::get_object_key();
			$object_id	= $item[$object_key] ?? 0;

			if(is_serialized($item['meta_value']) || !is_scalar($item['meta_value'])){
				if(!is_scalar($item['meta_value'])){
					$item['meta_value']	= 'SERIALIZED DATA';
				}elseif(is_serialized_string($item['meta_value'])){
					$item['meta_value']	= '<pre>'.maybe_unserialize( $item['meta_value'] ).'</pre>';
					unset($item['row_actions']['view']);
				}else{
					$item['meta_value']	= 'SERIALIZED DATA';
				}

				unset($item['row_actions']['edit']);
			}else{
				$item['meta_value']	= '<pre>'.esc_textarea($item['meta_value']).'</pre>';

				unset($item['row_actions']['view']);
				unset($item['row_actions']['replace']);
			}
		}else{
			// $item['count']	= wpjam_get_list_table_filter_link(['meta_key'=>$item['meta_key']], $item['count']);
		}
		return $item;
	}

	public static function get_actions(){
		$mode		= self::get_mode();

		if($mode == 'list'){
			return [
				'edit'			=> ['title'=>'编辑'],
				'view'			=> ['title'=>'查看',	'submit_text'=>''],			
				'replace'		=> ['title'=>'替换',	'bulk'=>true],		
				'delete'		=> ['title'=>'删除',	'page_title'=>'删除',	'direct'=>true,	'confirm'=>true,	'bulk'=>true]
			];
		}else{
			return [
				// 'add'			=> ['title'=>'新建'],
				// 'replace_all'	=> ['title'=>'全局替换',	'direct'=>false,	'overall'=>true],
				'view'			=> ['title'=>'查看',		'filter'=>['meta_key'],	'data'=>['mode'=>'list']],
				'rename'		=> ['title'=>'重命名',	'response'=>'list'],
				'delete'		=> ['title'=>'删除',		'page_title'=>'确认删除？',	'submit_text'=>'确认删除'],
			];
			
		}
	}

	public static function get_fields($action_key='', $id=0){
		$meta_type	= self::get_meta_type();
		$object_key	= self::get_object_key();

		if(self::get_mode() == 'list'){
			$id_key	= self::get_meta_id_key();

			$fields	= [
				$id_key			=> ['title'=>$id_key,		'type'=>'view',	'show_admin_column'=>true],
				$object_key		=> ['title'=>$object_key,	'type'=>'view',	'show_admin_column'=>true],
				$meta_type		=> ['title'=>$meta_type,	'type'=>'view',	'show_admin_column'=>'only'],
				'meta_key'		=> ['title'=>'meta_key',	'type'=>'view',	'show_admin_column'=>true],
				'meta_value'	=> ['title'=>'meta_value',	'type'=>'text',	'show_admin_column'=>true]
			];

			if($action_key == 'view'){
				$meta	= self::get($id);
				$fields['meta_value']['type']	= 'view';
				$fields['meta_value']['value']	= '<pre>'.var_export($meta['meta_value'], true).'</pre>';
			}elseif($action_key == 'replace' || $action_key == 'replace_all'){
				unset($fields['meta_value']);

				if(is_array($id)){
					unset($fields[$id_key]);
					unset($fields[$object_key]);
				}

				$fields['search']	= ['title'=>'搜索',	'type'=>'text'];
				$fields['replace']	= ['title'=>'替换',	'type'=>'text'];

				return $fields;
			}else{
				$meta	= self::get($id);
			}

			return $fields;	
		}else{
			if($action_key == 'delete'){
				return [
					'meta_key'	=> ['title'=>'',	'type'=>'view',	'value'=>'所有 meta_key 为「<strong>'.$id.'</strong>」的 '.$meta_type.'meta 都将会被删除。<br /><br />请在下面输入框再次输入 meta_key 确认删除：'],
					'meta_key2'	=> ['title'=>'',	'type'=>'text'],
				];
			}elseif($action_key == 'rename'){
				return [
					'meta_key'		=> ['title'=>'旧的meta_key',	'type'=>'view'],
					'new_meta_key'	=> ['title'=>'新的meta_key',	'type'=>'text'],
				];
			}elseif($action_key == 'add'){
				return [
					$object_key		=> ['title'=>$object_key,	'type'=>'number'],
					'key'			=> ['title'=>'meta_key',	'type'=>'text'],
					'value'			=> ['title'=>'meta_value',	'type'=>'text'],
				];
			}elseif($action_key == 'replace' || $action_key == 'replace_all'){
				$fields	= [
					'meta_key'	=> ['title'=>'meta_key',	'type'=>'view'],
					'search'	=> ['title'=>'搜索',			'type'=>'text'],
					'replace'	=> ['title'=>'替换',			'type'=>'text'],
				];

				if($action_key == 'replace_all'){
					unset($fields['meta_key']);
				}

				return $fields;
			}else{
				return [
					'meta_key'	=> ['title'=>'meta_key',	'type'=>'view',	'show_admin_column'=>'only'],
					'count'		=> ['title'=>'数量',			'type'=>'view',	'show_admin_column'=>'only']
				];
			}
		}	
	}

	public static function get_filterable_fields(){
		$object_key	= self::get_object_key();
		return ['meta_key',$object_key];
	}
}

function wpjam_metas_list_table(){
	global $wpdb;
	
	$mode 		= WPJAM_MetaData::get_mode();
	$meta_type 	= WPJAM_MetaData::get_meta_type();
	$title		= ucfirst($meta_type).' Meta';
	$object_key	= WPJAM_MetaData::get_object_key();
	$object_id	= wpjam_get_data_parameter($object_key);
	$meta_key 	= wpjam_get_data_parameter('meta_key');
	
	if($meta_key){
		$title		.= '：'.$meta_key;
	}elseif($object_id){
		$title		.= '：'.$object_id;
	}
			
	$table		= _get_meta_table($meta_type);
	$total		=  $wpdb->get_var("SELECT count(*) FROM {$table}");
	$summary	= '总数量：<strong>'.number_format_i18n($total).'</strong>';

	return [
		'title'			=> $title,
		'summary'		=> $summary,
		'plural'		=> 'metas',
		'singular' 		=> 'meta',
		'model'			=> 'WPJAM_MetaData',
		'query_data'	=> compact('mode'),
		'fixed'			=> false,
		'ajax'			=> true,
		'search'		=> true
	]; 
}


add_action('admin_head', function(){
	?>
	<style type="text/css">
	td.column-meta_id, td.column-umeta_id{width: 104px;}
	td.column-meta_key{min-width: 140px;}
	td pre {white-space: pre-wrap; word-wrap: break-word; margin: 0; word-break: break-all;}
	.subsubsub .dashicons{vertical-align: sub;margin-bottom: 10px;}
	</style>
	<?php
});