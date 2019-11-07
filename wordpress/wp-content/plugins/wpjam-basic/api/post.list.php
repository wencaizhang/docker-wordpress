<?php
/* 规则：
** 1. 分成主的查询和子查询（$post_list_args['sub']=1）
** 2. 主查询支持 $_GET 参数 和 $_GET 参数 mapping
** 3. 子查询（sub）只支持 $post_list_args 参数
** 4. 主查询返回 next_cursor 和 total_pages，current_page，子查询（sub）没有
** 5. $_GET 参数只适用于 post.list 
** 6. term.list 只能用 $_GET 参数 mapping 来传递参数
*/

global $wp, $wp_query, $post_list_args, $is_main_query;

$post_list_args	= $args;

$output			= $post_list_args['output'] ?? '';
$is_sub			= $post_list_args['sub'] ?? false;
$is_main_query	= !$is_sub;

if($is_main_query){
	// 参数 mapping
	if(!empty($post_list_args['mapping'])){
		$mapping	= wp_parse_args($post_list_args['mapping']);
		if($mapping && is_array($mapping)){
			foreach ($mapping as $key => $mapping_key) {
				if($value = wpjam_get_parameter($mapping_key)){
					$post_list_args[$key]	= $value;
				}
			}
		}

		unset($post_list_args['mapping']);

		trigger_error('using_mapping');
	}
}

// Post type 处理
$post_type	= wpjam_post_list_get_parameter('post_type');

if($post_type && $post_type != 'any' && !is_array($post_type)){
	$post_type_object	= get_post_type_object($post_type);
	if(!$post_type_object){
		wpjam_send_json([
			'errcode'	=> 'post_type_not_defined',
			'errmsg'	=> 'post_type 未定义'
		]);
	}

	if($is_main_query){
		$response['current_post_type']	= [
			'post_type'	=> $post_type,
			'label'		=> $post_type_object->label,
		];
	}
}

$wp->set_query_var('post_type', $post_type);

// 状态处理
// post_status 支持的 $post_list_args 参数为 post_status ，get 参数为 status
$post_status	= 'publish';

if(!empty($post_list_args['post_status'])){
	$post_status	= $post_list_args['post_status'];
}

if($is_main_query){
	if($status = wpjam_get_parameter('status')){
		$post_status = $status;
	}
}

$wp->set_query_var('post_status', $post_status);

// 缓存处理
$wp->set_query_var('cache_results', true);
// $wp->set_query_var('update_post_meta_cache', true);
// $wp->set_query_var('update_post_term_cache', true);
// $wp->set_query_var('lazy_load_term_meta', false);	// 在 the_posts filter 的时候，已经处理了

// 搜索处理
if($is_main_query){
	$is_search	= $_GET['s'] ?? false;
	$use_cursor	= $is_search ? false : true;

	// 搜索 post_meta 的 filter
	// if($is_search && !empty($post_list_args['search_metas'])){
	// 	$wp->set_query_var('search_metas', $post_list_args['search_metas']);
	// }
}

// 作者处理
foreach (['author', 'author_name'] as $key) {
	$value	= wpjam_post_list_get_parameter($key);
	if($value){
		$wp->set_query_var($key, $value);
	}
}

foreach (['author__in', 'author__not_in'] as $key) {
	$value	= wpjam_post_list_get_parameter($key, false);
	if($value){
		$wp->set_query_var($key, $value);
	}
}

// 页面处理
foreach (['post_parent', 'post_parent__in', 'post_parent__not_in', 'post__in', 'post__not_in'] as $key) {
	$value	= wpjam_post_list_get_parameter($key, false);
	if($value){
		$wp->set_query_var($key, $value);
	}
}

// 排序处理
$orderby	= wpjam_post_list_get_parameter('orderby');
$order		= wpjam_post_list_get_parameter('order');

if($orderby){
	if($is_main_query){
		$use_cursor	= false;
	}

	if(is_array($orderby)){
		$wp->set_query_var('orderby', $orderby);
	}else{
		$wp->set_query_var('orderby', $orderby);

		if($is_main_query && !$is_search && $orderby == 'date'){
			$use_cursor	= true;
		}
	}
}

if($order){
	$wp->set_query_var('order', $order);
}

// 分页处理, 置顶处理
foreach (['nopaging', 'posts_per_page', 'ignore_sticky_posts'] as $key) {
	$value	= wpjam_post_list_get_parameter($key, false);
	if($value){
		$wp->set_query_var($key, $value);
	}else{
		if($key == 'ignore_sticky_posts' && $is_main_query && is_null($value)){
			$wp->set_query_var($key, true);
		}
	}
}

$date_query	= wpjam_post_list_get_parameter('date_query', false);
if($date_query){
	$wp->set_query_var('date_query', $date_query);
}

if($is_main_query){
	if($use_cursor){
		if(empty($date_query)){
			$date_query = [];

			if($cursor	= wpjam_get_parameter('cursor',	['default'=>0,	'type'=>'int'])){
				$date_query[]	= ['before' => get_date_from_gmt(date('Y-m-d H:i:s', $cursor))];
			}

			if($since	= wpjam_get_parameter('since',	['default'=>0,	'type'=>'int'])){
				$date_query[]	= ['after' => get_date_from_gmt(date('Y-m-d H:i:s', $since))];
			}

			if($date_query){
				$wp->set_query_var('date_query', $date_query);
			}
		}
	}else{
		if($paged	= wpjam_get_parameter('paged',	['type'=>'int'])){
			$wp->set_query_var('paged', $paged);
		}
	}	
}

// 同时支持 $_GET 参数 和 $post_list_args 参数

// meta 处理

$meta_query	= wpjam_post_list_get_parameter('meta_query', false);
if($meta_query){
	$wp->set_query_var('meta_query', $meta_query);
}

foreach (['meta_key','meta_value','meta_value_num', 'meta_compare'] as $key) {
	$value	= wpjam_post_list_get_parameter($key);
	if($value){
		$wp->set_query_var($key, $value);
	}
}

// taxonomy 参数处理，同时支持 $_GET 和 $post_list_args 参数
$tax_query	= wpjam_post_list_get_parameter('tax_query', false);

if(empty($tax_query)){
	if($post_type){
		$taxonomies = get_object_taxonomies($post_type);
	}else{
		$taxonomies = get_taxonomies(['public' => true]);
	}

	if($taxonomies){
		$taxonomy_key_list	= [
			'category'	=> ['cat', 'category_id', 'cat_id'],
			'post_tag'	=> ['tag_id']
		];

		foreach ($taxonomies as $taxonomy) {
			if($is_main_query){
				if($taxonomy == 'category'){
					$slug = wpjam_get_parameter('category_name');
				}elseif($taxonomy == 'post_tag'){
					$slug = wpjam_get_parameter('tag');
				}else{
					$slug = wpjam_get_parameter($taxonomy);
				}

				if($slug){
					$term = get_term_by('slug', $slug, $taxonomy);

					$current_taxonomy	= wpjam_get_term($term, $taxonomy);
					if(is_wp_error($current_taxonomy)){
						wpjam_send_json($current_taxonomy);
					}

					if(empty($response['current_taxonomy'])){
						$response['current_taxonomy']	= $taxonomy;
					}

					$response['current_'.$taxonomy]	= $current_taxonomy;

					if(empty($response['page_title'])){
						$response['page_title']		= $current_taxonomy['page_title'];
					}

					if(empty($response['share_title'])){
						$response['share_title']	= $current_taxonomy['share_title'];
					}
				}
			}

			$taxonomy_keys	= $taxonomy_key_list[$taxonomy] ?? [$taxonomy.'_id'];

			foreach ($taxonomy_keys as $key) {

				$value	= wpjam_post_list_get_parameter($key);

				if($value){
					$tax_query[$taxonomy]	= ['taxonomy'=>$taxonomy, 'terms'=>[$value], 'field'=>'id'];
					$current_taxonomy		= wpjam_get_term($value, $taxonomy);
					if(is_wp_error($current_taxonomy)){
						wpjam_send_json($current_taxonomy);
					}

					if($is_main_query){
						if(empty($response['current_taxonomy'])){
							$response['current_taxonomy']	= $taxonomy;
						}

						$response['current_'.$taxonomy]	= $current_taxonomy;

						if(empty($response['page_title'])){
							$response['page_title']		= $current_taxonomy['page_title'];
						}

						if(empty($response['share_title'])){
							$response['share_title']	= $current_taxonomy['share_title'];
						}
					}
				}
			}
		}
	}
}

$output	= $output ?: ($post_type ? $post_type.'s' : 'posts');
if($tax_query){
	if(empty($tax_query['relation'])){
		$tax_query	= array_values($tax_query);
		$tax_query['relation']	= 'AND';
	}
	
	$wp->set_query_var('tax_query', $tax_query);
}

// wpjam_print_r($wp);

$wp->query_posts();

// wpjam_print_r($wp_query);

$posts_json = [];

if($wp_query->have_posts()){
	$posts_json	= apply_filters('wpjam_posts_json', $wp_query->posts, $post_list_args);
	$posts_json	= array_map(function($post_json) use ($post_list_args){ return wpjam_get_post($post_json->ID, $post_list_args); }, $posts_json);
}

if($is_main_query){
	$response['total']			= (int)$wp_query->found_posts;
	$response['total_pages']	= (int)$wp_query->max_num_pages;
	$response['current_page']	= (int)wpjam_get_parameter('paged',	['default'=>1,	'type'=>'int']);
	
	if($use_cursor){
		$response['next_cursor']	= ($posts_json && $wp_query->max_num_pages>1) ? end($posts_json)['timestamp'] : 0;
	}
}

$response[$output]	= $posts_json;

if($post_type && $post_type != 'any' && !is_array($post_type)){
	if(empty($response['page_title'])){
		$response['page_title']	= $post_type_object->label;
	}

	if(empty($response['share_title'])){
		$response['share_title']	= $post_type_object->label;
	}
}