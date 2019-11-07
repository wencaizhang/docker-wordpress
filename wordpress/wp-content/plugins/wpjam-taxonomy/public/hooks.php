<?php
add_filter('get_terms_defaults', function($defaults, $taxonomies){
	if(wpjam_is_order_taxonomy_query($taxonomies)){
		$defaults['orderby']	= 'order';
	}

	return $defaults;
}, 10, 2);


add_action('parse_term_query', function($query){
	if(wpjam_is_order_taxonomy_query($query->query_vars['taxonomy'])){
		$orderby	= $query->query_vars['orderby'];

		if(in_array($orderby, ['id', 'name'])){
			if(array_intersect(wp_list_pluck(debug_backtrace(), 'function'), ['wp_dropdown_categories', 'wp_list_categories'])){
				$orderby	= 'order';
			}
		}

		if($orderby == 'order'){
			$query->query_vars['orderby']	= 'meta_value_num';
			$query->query_vars['order']		= 'DESC';
			$query->query_vars['meta_key']	= 'order';
		}
	}

	// wpjam_print_r(wp_list_pluck(debug_backtrace(), 'function'));
		
}, 99);

function wpjam_is_order_taxonomy_query($taxonomies){
	if(empty($taxonomies) || (is_array($taxonomies) && count($taxonomies) > 1)){
		return false;
	}

	if(is_string($taxonomies)){
		$taxonomy	= $taxonomies;
	}else{
		$taxonomy	= current($taxonomies);
	}

	if($taxonomy){
		$tax_obj	= get_taxonomy($taxonomy);
		return $tax_obj->order ?? false;
	}else{
		return false;
	}
}

add_filter('register_taxonomy_args', function($args, $taxonomy){
	if(isset($args['levels'])){
		$args['levels_source']	= 'code';
	}else{
		$levels	= wpjam_get_setting('wpjam_taxonomy_levels', $taxonomy);

		if(!is_null($levels)){
			$args['levels']			= intval($levels);
			$args['levels_source']	= 'plugin';
		}
	}

	if(isset($args['order'])){
		$args['order_source']	= 'code';
	}else{
		$order	= wpjam_get_setting('wpjam_taxonomy_order', $taxonomy);

		if($order){
			$args['order']			= boolval($order);
			$args['order_source']	= 'plugin';
		}
	}

	return $args;
}, 10 ,2);