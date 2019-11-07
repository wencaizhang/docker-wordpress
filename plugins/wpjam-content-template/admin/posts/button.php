<?php
add_action('media_buttons', function($editor_id){
	// echo '<button type="button" id="content_template_button" class="button" href="javascript:;" data-editor="'.esc_attr( $editor_id ).'" style="padding-left:5px;">
		
	// </button>';

	wpjam_ajax_button([
		'action'		=> 'insert_content_template',
		'page_title'	=> '插入模板',
		'data'			=> ['editor_id'=>$editor_id],
		'tb_width'		=> 500,
		'tb_height'		=> 200,
		'button_text'	=> '<span class="dashicons dashicons-edit" style="margin:0 2px; width:18px; height:18px; color:#82878c; vertical-align:text-bottom;"></span> 插入模板', 
		'class'			=>'button'
	]);
});


add_filter('wpjam_page_ajax_response', function($ajax_response, $plugin_page, $page_action){

	if($page_action == 'insert_content_template'){
		return 'wpjam_insert_content_template_ajax_response';
	}

	return $ajax_response;
}, 999, 3);


function wpjam_insert_content_template_ajax_response(){
	$action			= $_POST['page_action'];
	$action_type	= $_POST['page_action_type'];

	$form	= ''; 
	if(current_user_can('edit_posts')){
		ob_start();

		$data		= wp_parse_args($_POST['data']);
		$editor_id	= $data['editor_id'] ?? '';

		$fields	= [
			'template'		=> ['title'=>'',	'type'=>'fieldset',	'fields'=>[
				'template_id'	=> ['title'=>'选择模板',	'type'=>'text',	'data_type'=>'post_type',	'post_type'=>'template',	'class'=>'all-options'],
				'template_view'	=> ['title'=>' ',		'type'=>'view',	'value'=>'请点击选择或者输入关键字查询后选择...'],
			]],
		];

		wpjam_ajax_form([
			'fields'		=> $fields,
			'action'		=> 'wpjam_shop_help', 
			'form_id'		=> 'template_form',
			'data'			=> ['editor_id'=>$editor_id],
			'submit_text'	=> '插入'
		]);
		
		$form   = ob_get_clean();
	
		wpjam_send_json([
			'errcode'	=> 0,
			'data'		=> $form
		]);
	}
}

add_action('admin_head', function(){
	?>
	<script type="text/javascript">
	jQuery(function($){
		$('body').on('submit', "#template_form", function(e){
			e.preventDefault();	// 阻止事件默认行为。
			wp.media.editor.insert("\n"+'[template id="'+$('#template_id').val()+'"]'+"\n");
			tb_remove();
		});
	});
	</script>

	<?php
});