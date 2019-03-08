<?php
do_action('wpjam_term_page_file', $taxonomy);

add_action($taxonomy.'_edit_form_fields', function($term, $taxonomy=''){
	$taxonomy_fields = wpjam_get_term_options($taxonomy);
	
	wpjam_fields($taxonomy_fields, array(
		'data_type'		=> 'term_meta',
		'id'			=> $term->term_id,
		'fields_type'	=> 'tr',
		'item_class'	=> 'form-field'
	));
}, 10, 2);


