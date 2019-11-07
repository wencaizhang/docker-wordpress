<?php
global $taxonomy_levels, $term_parent, $is_order_taxonomy, $term_orderby;

$taxonomy_obj		= get_taxonomy($taxonomy);
$is_order_taxonomy	= $taxonomy_obj->order ?? false;
$taxonomy_levels	= $taxonomy_obj->levels ?? 0;
$term_parent		= wpjam_get_data_parameter('parent');

// $term_orderby		= $_REQUEST['orderby'] ?? '';
// unset($_REQUEST['orderby']);

if(is_null($term_parent) && $taxonomy_levels == 1){
	$term_parent	= 0;
}

if($is_order_taxonomy){
	$transient_key	= 'wpjam_'.$taxonomy.'_taxonomy_order_checked';

	if(false === get_transient($transient_key)){
		$terms	= get_terms(['taxonomy'=>$taxonomy, 'orderby'=>'name', 'hide_empty'=>false]);
				
		foreach ($terms as $term) {
			if(!metadata_exists('term', $term->term_id, 'order')){
				update_term_meta($term->term_id, 'order', 1);
			}
		}

		// wpjam_print_r($terms);
		// _pad_term_counts($terms, $taxonomy);
		// wpjam_print_r($terms);
		
		set_transient($transient_key, true, DAY_IN_SECONDS);
	}	
}

add_filter($taxonomy.'_row_actions', function($actions, $term){
	global $post_type, $term_parent, $is_order_taxonomy;

	if((empty($term_parent) || $term_parent != $term->term_id) && get_term_children($term->term_id, $term->taxonomy)){
		$actions['children']	= '<a href="'.admin_url('edit-tags.php?taxonomy='.$term->taxonomy.'&post_type='.$post_type.'&parent='.$term->term_id).'">下一级</a>';
	}else{
		unset($actions['children']);
	}

	if(empty($_GET['orderby']) && $is_order_taxonomy){
		if($term->parent != $term_parent){
			unset($actions['move']);
			unset($actions['up']);
			unset($actions['down']);
		}
	}

	return $actions;
},10,2);

add_action('wpjam_'.$taxonomy.'_terms_actions', function($actions, $taxonomy){
	global $term_parent, $is_order_taxonomy;

	if(empty($term_parent)){
		$actions['children']	= ['direct'=>true,	'title'=>'下一级'];
	}

	if(empty($_GET['orderby']) && isset($term_parent)){
		if($is_order_taxonomy){
			$data	= ['parent'=>$term_parent];

			$actions['move']	= ['direct'=>true, 'data'=>$data,	'title'=>'<span class="dashicons dashicons-move"></span>',			'page_title'=>'拖动'];
			$actions['up']		= ['direct'=>true, 'data'=>$data,	'title'=>'<span class="dashicons dashicons-arrow-up-alt"></span>',	'page_title'=>'向上移动'];
			$actions['down']	= ['direct'=>true, 'data'=>$data,	'title'=>'<span class="dashicons dashicons-arrow-down-alt"></span>','page_title'=>'向下移动'];
		}
	}

	return $actions;
}, 10, 2);

add_filter('wpjam_'.$taxonomy.'_terms_list_action', function($result, $list_action, $term_id, $data){
	if($list_action != 'move'){
		return $result;
	}

	$term_ids	= get_terms([
		'parent'	=> $data['parent'],
		'orderby'	=> 'name',
		'taxonomy'	=> get_term($term_id)->taxonomy,
		'hide_empty'=> false,
		'fields'	=> 'ids'
	]);

	if(empty($term_ids) || !in_array($term_id, $term_ids)){
		return new WP_Error('key_not_exists', $term_id.'的值不存在');
	}

	$terms	= array_map(function($term_id){
		return ['id'=>$term_id, 'order'=>get_term_meta($term_id, 'order', true) ?: 0];
	}, $term_ids);

	$terms	= wp_list_sort($terms, 'order', 'DESC');
	$terms	= wp_list_pluck($terms, 'order', 'id');

	$next	= $data['next'] ?? false;
	$prev	= $data['prev'] ?? false;

	if(!$next && !$prev){
		return new WP_Error('invalid_move', '无效移动位置');
	}

	unset($terms[$term_id]);

	if($next){
		if(!isset($terms[$next])){
			return new WP_Error('key_not_exists', $next.'的值不存在');
		}

		$offset	= array_search($next, array_keys($terms));

		if($offset){
			$terms	= array_slice($terms, 0, $offset, true) +  [$term_id => 0] + array_slice($terms, $offset, null, true);	
		}else{
			$terms	= [$term_id => 0] + $terms;	
		}
	}else{
		if(!isset($terms[$prev])){
			return new WP_Error('key_not_exists', $prev.'的值不存在');
		}

		$offset	= array_search($prev, array_keys($terms));
		$offset ++;

		if($offset){
			$terms	= array_slice($terms, 0, $offset, true) +  [$term_id => 0] + array_slice($terms, $offset, null, true);	
		}else{
			$terms	= [$term_id => 0] + $terms;	
		}
	}

	$count	= count($terms);
	foreach ($terms as $term_id => $order) {
		if($order != $count){
			update_term_meta( $term_id, 'order', $count);
		}

		$count-- ;
	}
	
	return true;
}, 10, 4);

add_action('created_term', function($term_id, $tt_id, $taxonomy){
	global $is_order_taxonomy;

	if(!$is_order_taxonomy){
		return;
	}

	if(metadata_exists('term', $term_id, 'order')){
		return;
	}

	$term_ids	= get_terms([
		'parent'	=> get_term($term_id)->parent,
		'orderby'	=> 'name',
		'taxonomy'	=> $taxonomy,
		'hide_empty'=> false,
		'fields'	=> 'ids'
	]);

	update_term_meta($term_id, 'order', count($term_ids));
}, 10, 3);

add_action('parse_term_query', function($query){
	global $term_parent, $term_orderby;

	if(isset($term_parent)){
		if($term_parent){
			$taxonomy	= current($query->query_vars['taxonomy']);
			$hierarchy	= _get_term_hierarchy($taxonomy);
			$term_ids	= $hierarchy[$term_parent] ?? [];
			$term_ids[]	= $term_parent;
			if($ancestors = get_ancestors($term_parent, $taxonomy)){
				$term_ids	= array_merge($term_ids, $ancestors);
			}
			$query->query_vars['include']	= $term_ids;
			// $query->query_vars['pad_counts']	= true;
		}else{
			$query->query_vars['parent']	= $term_parent;
		}
	}

	// if($term_orderby){
	// 	$query->query_vars['orderby']	= $term_orderby;
	// }
});

add_filter('edit_'.$taxonomy.'_per_page', function($per_page){
	$parent	= wpjam_get_data_parameter('parent');
	return $parent ? 9999 : $per_page;
});

add_action('admin_head',function(){
	global $taxonomy, $post_type, $taxonomy_levels, $term_parent, $is_order_taxonomy;

	$term_link	= admin_url('edit-tags.php?taxonomy='.$taxonomy.'&post_type='.$post_type);

	if(!isset($term_parent)){
		$term_nav	= '<a href="'.$term_link.'&parent=0'.'" class="button button-primary">只显示第一级</a>';
	}elseif($term_parent > 0){
		$term_nav	= '<a href="'.$term_link.'&parent=0'.'" class="button button-primary">返回第一级</a>';
	}else{
		if($taxonomy_levels == 1){
			$term_nav	= '';
		}else{
			$term_nav	= '<a href="'.$term_link.'" class="button button-primary">显示所有</a>';
		}
	}

	$orderby	= $_GET['orderby'] ?? '';
	?>
	
	<script type="text/javascript">
	jQuery(function($){
		$('#doaction').after('<?php echo $term_nav; ?>');

		<?php if($is_order_taxonomy && isset($term_parent) && empty($orderby)){ ?>

		var parent	= <?php echo $term_parent; ?>;

		if(parent == 0){
			var level	= '0';
		}else{
			var level	= parseInt($('#tag-'+parent).attr('class').match(/\d+/)) + 1;
		}

		$.wpjam_list_table_sortable(' > tr.level-'+level);

		<?php } ?>
	});
	</script>
	<?php
});
