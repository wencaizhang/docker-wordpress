<?php
require_once( ABSPATH . 'wp-admin/includes/class-walker-category-checklist.php' );
class WPJAM_Walker_Collection_Checklist extends Walker_Category_Checklist {
	public function start_el( &$output, $collection, $depth = 0, $args = array(), $id = 0 ) {
		if(doing_filter('attachment_fields_to_edit')){
			$name	= 'tax_input[collection]['.$collection->term_id.']';
		}else{
			$name	= 'tax_input[collection][]';
		}

		$class 	= $args['has_children'] ? ' class="has-children"' : '';

		$output	.= "\n<li id='collection-{$collection->term_id}'$class>";

		if($args['has_children']){
			$output	.= esc_html($collection->name);
		}else{
			$checked	= checked(in_array($collection->term_id, $args['selected_cats']), true, false );
			$output		.= '<label class="selectit">';
			$output		.= '<input type="checkbox" name="'.$name.'" id="in-collection-'.$collection->term_id.'"'.$checked.' value="'.$collection->term_id.'" />';
			$output		.= esc_html($collection->name);
			$output		.= '</label>';
		}
	}
}