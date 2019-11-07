<?php
add_filter('attachment_fields_to_edit', function ($form_fields, $post){
	global $pagenow;
	
	if($pagenow == 'post.php'){
		return $form_fields;
	}

	$t	= (array) get_taxonomy('collection');

	$checklist	= wp_terms_checklist($post->ID, [
		'taxonomy'		=>'collection', 
		'walker'		=> new WPJAM_Walker_Collection_Checklist,
		'checked_ontop'	=>false, 
		'echo'			=>false, 
		'popular_cats'	=>[]
	]);

	$checklist	= str_replace('tax_input[collection]', 'attachments['.$post->ID.'][collection_id]', $checklist);
	$checklist	= '<ul class="cat-checklist collection-checklist">'.$checklist.'</ul>'; 

	$t['checkbox']	= $checklist;
	$t['input']		= 'checkbox';

	$form_fields['collection'] = $t;

	return $form_fields;
},10,2);


add_filter('attachment_fields_to_save', function($post, $attachment_data){
	$collection_ids	= $attachment_data['collection_id'] ?? [];

	$result	= wp_set_post_terms($post['ID'], $collection_ids, 'collection');

	if(is_wp_error($result)){
		$post['errors']	= $result;
	}

	return $post;
}, 10, 2);

add_filter('media_view_settings', function($settings){
	$collection_settings	= [];
	
	if($collections	= wpjam_get_terms(['taxonomy'=>'collection', 'hide_empty'=>false],2)){
		foreach ($collections as $collection) {
			$collection_settings[]	= $collection;
			if(!empty($collection['children'])){
				foreach ($collection['children'] as $sub_collection) {
					$sub_collection['name']	= '&nbsp;&nbsp;&nbsp;'.$sub_collection['name'];
					$collection_settings[]	= $sub_collection;
				}
			}
		}
	}

	$settings['collections']	= $collection_settings;
	unset($settings['mimeTypes']);

	return $settings;
});

add_filter('ajax_query_attachments_args', function($query){
	$collection_id	= $_REQUEST['query']['collection_id'] ?? 0;
	if($collection_id){
		if($collection = get_term($collection_id, 'collection')){
			$query['taxonomy']	= 'collection';
			$query['term']		= $collection->slug;
		}
	}

	return $query;
});

add_action('admin_head', function(){
	?>
	<style type="text/css">
	.edit-attachment, 
	.media-types.media-types-required-info{display: none !important;} 
	.media-modal-content .media-frame select.attachment-filters {width: auto; max-width: 100%;}
	tr.compat-field-collection ul.collection-checklist{height: auto !important; font-size: 13px; padding: 8px !important;}
	tr.compat-field-collection ul.collection-checklist label{line-height: 1.5;}
	tr.compat-field-collection ul.collection-checklist input{margin:-4px 4px 0 0 !important;}
	tr.compat-field-collection ul.collection-checklist >li:after{content:""; display: block; clear: both; margin-bottom: 12px;}
	tr.compat-field-collection ul.collection-checklist ul.children{margin-left: 8px;}
	tr.compat-field-collection ul.collection-checklist ul.children li{float: left; margin: 8px 8px 0 0;}
	</style>
	<script type="text/javascript">
	var CollectionFilter = wp.media.view.AttachmentFilters.extend({
		id: 'media-attachment-collection-filters',

		createFilters: function() {
			var filters = {};
			_.each( wp.media.view.settings.collections || {}, function( value, index ) {
				filters[ index ] = {
					text: value.name,
					props: {
						collection_id: value.id,
					}
				};
			});
			filters.all = {
				text:  '所有分类',
				props: {
					collection_id: ''
				},
				priority: 10
			};
			this.filters = filters;
		}
	});

	var AttachmentsBrowser = wp.media.view.AttachmentsBrowser;
	wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
		createToolbar: function() {
			AttachmentsBrowser.prototype.createToolbar.call(this);

			if(this.controller.isModeActive('grid') || this.options.date){

				this.toolbar.set('CollectionFilterLabel', new wp.media.view.Label({
					value: '按分类筛选',
					attributes: {
						'for': 'media-attachment-collection-filters'
					},
					priority: -75
				}).render());

				this.toolbar.set('CollectionFilter', new CollectionFilter({
					controller: this.controller,
					model:	 this.collection.props,
					priority: -75
				}).render() );
			}
		}
	});
	</script>
	<?php
});



// add_filter('get_edit_post_link', function($link, $post_id){
// 	$post	= get_post($post_id);

// 	if($post->post_type == 'attachment'){
// 		return wp_get_attachment_url($post_id);
// 	}else{
// 		return $link;
// 	}
// }, 10, 2);

