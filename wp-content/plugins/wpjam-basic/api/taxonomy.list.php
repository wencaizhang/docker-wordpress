<?php
$taxonomy	= $args['taxonomy']??'';
if(empty($taxonomy)){
	wpjam_send_json(array(
		'errcode'	=> 'empty_taxonomy',
		'errmsg'	=> '自定义分类未设置'
	));
}

$output		= $args['output']??$taxonomy.'s';

$number		= ($args['number'])??0;
if($number){
	unset($args['number']);
}

if(isset($args['mapping'])){
	$mapping	= wp_parse_args($args['mapping']);
	if($mapping && is_array($mapping)){
		foreach ($mapping as $key => $get) {
			if(isset($_GET[$get])){
				$args[$key]	= $_GET[$get];
			}
		}
	}

	unset($args['mapping']);
}

$max_depth	= $args['max_depth']??-1;
if($terms = wpjam_get_terms($args, $max_depth)){
	if($number){
		$paged	= $_GET['paged']??1;
		$offset	= $number * ($paged-1);

		$response['current_page']	= (int)$paged;
		$response['total_pages']	= ceil(count($terms)/$number);
		$terms = array_slice($terms, $offset, $number);
	}
	$response[$output]	= array_values($terms);
}else{
	$response[$output]	= array();
}

if($taxonomy == 'category'){
	$response['taxonomy_title']	= '分类';
}elseif($taxonomy == 'post_tag'){
	$response['taxonomy_title']	= '标签';
}else{
	$response['taxonomy_title']	= get_taxonomy($taxonomy)->label;
}