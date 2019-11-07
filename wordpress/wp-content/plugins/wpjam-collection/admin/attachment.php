<?php
add_action('admin_head', function(){
	?>
	<script type="text/javascript">
	jQuery(function($){
		$("ul.categorychecklist>li>label input").each(function(){
			if ($(this).parent().next('ul').hasClass('children')) {
				$(this).remove();
			}
		});
	});
	</script>
	<?php
});

add_filter('wp_terms_checklist_args',function($args){
	$args['checked_ontop']	= false;
	return $args;
});

// 去掉图片编辑功能
add_filter('wp_image_editors', '__return_empty_array');