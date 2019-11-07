<?php
add_action('admin_head',function(){
	global $taxonomy;

	$tax_obj	= get_taxonomy($taxonomy);
	$supports	= $tax_obj->supports ?? ['slug', 'description', 'parent'];
	$levels		= $taxonomy_obj->levels ?? 0;

	if($levels == 1){
		$supports	= array_diff($supports, ['parent']);
	}

	?>
	
	<style type="text/css">

	.form-field.term-parent-wrap p{display: none;}
	.form-field span.description{color:#666;}

	<?php foreach (['slug', 'description', 'parent'] as $key) { if(!in_array($key, $supports)){ ?>
	.form-field.term-<?php echo $key ?>-wrap{display: none;}
	<?php } } ?>

	</style>
	<?php
});

add_filter('taxonomy_parent_dropdown_args', function($args, $taxonomy, $action_type){
	$tax_obj	= get_taxonomy($taxonomy);
	$levels		= $tax_obj->levels ?? 0;

	if($levels > 1){
		$args['depth']	= $levels - 1;

		if($action_type == 'edit'){
			$term_id		= $args['exclude_tree'];
			$term_levels		= count(get_ancestors($term_id, $taxonomy, 'taxonomy'));
			$child_levels	= $term_levels;

			$children	= get_term_children($term_id, $taxonomy);
			if($children){
				$child_levels = 0;

				foreach($children as $child){
					$new_child_levels	= count(get_ancestors($child, $taxonomy, 'taxonomy'));
					if($child_levels	< $new_child_levels){
						$child_levels	= $new_child_levels;
					}
				}
			}

			$redueced	= $child_levels - $term_levels;

			if($redueced < $args['depth']){
				$args['depth']	-= $redueced;
			}else{
				$args['parent']	= -1;
			}
		}
	}

	return $args;
}, 10, 3);

add_filter('wpjam_term_options', function($term_options, $taxonomy){
	$term_thumbnail_type		= wpjam_cdn_get_setting('term_thumbnail_type') ?: '';
	$term_thumbnail_taxonomies	= wpjam_cdn_get_setting('term_thumbnail_taxonomies') ?: [];

	if($term_thumbnail_type && $term_thumbnail_taxonomies && in_array($taxonomy, $term_thumbnail_taxonomies)){
		$term_options['thumbnail'] = [
			'title'				=> '缩略图', 
			'taxonomies'		=> $term_thumbnail_taxonomies, 
			'show_admin_column'	=> true,	
			'column_callback'	=> function($term_id){
				return wpjam_get_term_thumbnail($term_id, [50,50]);
			}
		];

		if($term_thumbnail_type == 'img'){
			$width	= wpjam_cdn_get_setting('term_thumbnail_width') ?: 200;
			$height	= wpjam_cdn_get_setting('term_thumbnail_height') ?: 200;

			$term_options['thumbnail']['type']		= 'img';
			$term_options['thumbnail']['item_type']	= 'url';

			if($width || $height){
				$term_options['thumbnail']['size']			= $width.'x'.$height;
				$term_options['thumbnail']['description']	= '尺寸：'.$width.'x'.$height;
			}
		}else{
			$term_options['thumbnail']['type']	= 'image';
		}
	}

	return $term_options;
},99,2);

add_action($taxonomy.'_edit_form_fields', function($term, $taxonomy=''){
	$taxonomy_fields = wpjam_get_term_options($taxonomy);
	
	wpjam_fields($taxonomy_fields, array(
		'data_type'		=> 'term_meta',
		'id'			=> $term->term_id,
		'fields_type'	=> 'tr',
		'item_class'	=> 'form-field'
	));
}, 10, 2);


add_filter('term_updated_messages', function($messages){
	global $taxonomy;

	if($taxonomy == 'post_tag' || $taxonomy == 'category'){
		return $messages;
	}

	$labels		= get_taxonomy_labels(get_taxonomy($taxonomy));
	$label_name	= $labels->name;

	$messages[$taxonomy]	= array_map(function($message) use ($label_name){
		if($message == $label_name) return $message;

		return str_replace(
			['项目', 'Item'], 
			[$label_name, ucfirst($label_name)], 
			$message
		);
	}, $messages['_item']);

	return $messages;
});