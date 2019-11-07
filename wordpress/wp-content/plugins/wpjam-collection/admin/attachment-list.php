<?php
class WPJAM_Attachment{
	public static function get_actions(){
		return [
			'collection'	=> ['title'=>'设置分类',	'capability'=>'edit_posts',	'bulk'=>true],
		];
	}

	public static function get_fields($action_key='', $post_id=0){
		if($action_key == 'collection'){
			// $collections	= get_terms(['taxonomy'=>'collection', 'hide_empty'=>false]);

			$checklist_post_id	= is_array($post_id) ? null : $post_id;

			$checklist	= wp_terms_checklist($checklist_post_id, [
				'taxonomy'		=>'collection', 
				'walker'		=> new WPJAM_Walker_Collection_Checklist,
				'checked_ontop'	=>false, 
				'echo'			=>false, 
				'popular_cats'	=>[]
			]);

			$checklist	= '<ul class="collection-checklist">'.$checklist.'</ul>'; 

			return [
				'collection_hidden'	=> ['title'=>'',	'type'=>'mu-text',	'name'=>'tax_input[collection]'],
				'collection'		=> ['title'=>'',	'type'=>'view',		'value'=>$checklist],
			];
		}

		return [];
	}

	public static function collection($post_id, $data){

		$collection_ids	= $data['tax_input']['collection'] ?? [];

		$result	= wp_set_post_terms($post_id, $collection_ids, 'collection');

		if(is_wp_error($result)){
			return $result;
		}else{
			return true;
		}
	}

	public static function media_row_actions($actions, $post){
		unset($actions['edit']);

		if(strpos($post->post_mime_type, 'image/') === 0){
			$actions['view']	= str_replace('rel="bookmark"', 'class="thickbox" rel="bookmark"', $actions['view']);
		}else{
			$actions['view']	= str_replace('rel="bookmark"', '', $actions['view']);
		}

		return $actions;
	}

	public static function manage_media_columns($columns){
		unset($columns['parent']);
		unset($columns['comments']);
		$columns['author']	= '上传者';

		return $columns;
	}
}

add_filter('wp_get_attachment_image_src', function($image, $attachment_id, $size){
	if(CDN_NAME == ''){
		return $image;	
	}

	if(wp_attachment_is_image($attachment_id)){
		$img_url	= wp_get_attachment_url($attachment_id);
		$size		= array_map(function($s){ return $s*2; }, wpjam_parse_size($size));
		$image[0]	= wpjam_get_thumbnail($img_url, $size);
	}

	return $image;
}, 10, 3);

add_action('admin_head', function(){
	?>
	<style type="text/css">
	th.column-taxonomy-collection{width: 210px;}
	#tr_collection_hidden{display: none;}
	#tr_collection ul.collection-checklist{margin: 0;}
	#tr_collection ul.collection-checklist label{line-height: 1.5;}
	#tr_collection ul.collection-checklist >li:after{content:""; display: block; clear: both; margin-bottom: 18px;}
	#tr_collection ul.collection-checklist ul.children{margin-left: 18px;}
	#tr_collection ul.collection-checklist ul.children li{float: left; margin: 8px 18px 0 0;}
	</style>
	<?php
});

global $wpjam_list_table;

$wpjam_list_table	= new WPJAM_Post_List_Table([
	'model'		=> 'WPJAM_Attachment',
	'post_type'	=> 'attachment'
]);