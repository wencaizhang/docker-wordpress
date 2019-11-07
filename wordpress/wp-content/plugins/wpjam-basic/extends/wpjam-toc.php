<?php 
/*
Plugin Name: 文章目录
Plugin URI: http://blog.wpjam.com/project/wpjam-toc/
Description: 自动根据文章内容里的子标题提取出文章目录，并显示在内容前。
Version: 1.0
*/

//内容中自动加入文章目录
add_filter('the_content',function($content){
	$post_id	= get_the_ID();
	if(doing_filter('get_the_excerpt') || !is_singular() || $post_id != get_queried_object_id()){
		return $content;
	}

	if(get_post_meta($post_id,'toc_hidden',true)){
		return $content;
	}

	global $toc_count, $toc_items;

	$toc_items	= [];
	$toc_count	= 0;

	if(metadata_exists('post', $post_id, 'toc_depth')){
		$toc_depth = get_post_meta($post_id,'toc_depth',true);
	}else{
		$toc_depth = wpjam_basic_get_setting('toc_depth');
	}

	if($toc_depth == 1 ){
		$regex = '#<h1(.*?)>(.*?)</h1>#';
	}else{
		$regex = '#<h([1-'.$toc_depth.'])(.*?)>(.*?)</h\1>#';
	}

	$content = preg_replace_callback($regex, function($matches){
		global $toc_count, $toc_items;

		$toc_count ++;
		$toc_items[] = ['text'=>trim(strip_tags($matches[3])), 'depth'=>$matches[1], 'count'=>$toc_count];

		return "<h{$matches[1]} {$matches[2]}><a name=\"toc-{$toc_count}\"></a>{$matches[3]}</h{$matches[1]}>";
	}, $content);

	$toc_position = wpjam_basic_get_setting('toc_position') ?: 'content';

	if($toc_items && $toc_position == 'content' && !has_shortcode($content, 'toc')){
		$index		= '<div id="toc">'."\n".'<p><strong>文章目录</strong><span>[隐藏]</span></p>'."\n".wpjam_get_toc().'</div>'."\n";

		if(wpjam_basic_get_setting('toc_copyright')){
			$index	.= '<a href="http://blog.wpjam.com/project/wpjam-basic/"><small>WPJAM TOC</small></a>'."\n";
		}

		$content = $index.$content;
	}

	return $content;
});


// 根据 $TOC 数组输出文章目录 HTML 代码 
function wpjam_get_toc(){
	global $toc_items;

	if(empty($toc_items)){
		return '';
	}
	
	$index		= '<ul>'."\n";
	$prev_depth	= 0;
	$to_depth	= 0;

	foreach($toc_items as $toc_item){
		$toc_depth	= $toc_item['depth'];

		if($prev_depth){
			if($toc_depth == $prev_depth){
				$index .= '</li>'."\n";
			}elseif($toc_depth > $prev_depth){
				$to_depth++;
				$index .= '<ul>'."\n";
			}else{
				$to_depth2 = ($to_depth > ($prev_depth - $toc_depth))? ($prev_depth - $toc_depth) : $to_depth;

				if($to_depth2){
					for ($i=0; $i<$to_depth2; $i++){
						$index .= '</li>'."\n".'</ul>'."\n";
						$to_depth--;
					}
				} 
				
				$index .= '</li>';
			}
		}

		$prev_depth	= $toc_depth;

		$index .= '<li><a href="#toc-'.$toc_item['count'].'">'.$toc_item['text'].'</a>';
	}

	for($i=0; $i<=$to_depth; $i++){
		$index .= '</li>'."\n".'</ul>'."\n";
	}

	return $index;
}

// 使用 Shortcode 方式插入
add_shortcode('toc', function($atts, $content=''){
	if(get_the_ID() == get_queried_object_id()){
		return wpjam_get_toc();
	}else{
		return '';
	}
});

if(wpjam_basic_get_setting('toc_auto')){
	add_action('wp_head', function(){
		if(is_singular()){
			echo '<script type="text/javascript">'."\n".wpjam_basic_get_setting('toc_script')."\n".'</script>'."\n";
			echo '<style type="text/css">'."\n".wpjam_basic_get_setting('toc_css')."\n".'</style>'."\n";
		}
	});	
}


