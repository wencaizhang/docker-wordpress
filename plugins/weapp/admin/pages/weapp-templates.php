<?php
include(WEAPP_PLUGIN_DIR.'admin/includes/class-weapp-template.php');

add_filter('wpjam_weapp_templates_list_table', function(){
	global $current_tab;

	$args = [
		'title'				=> '模板列表',
		'singular'			=> 'weapp-template',
		'plural'			=> 'weapp-templates',
		'primary_column'	=> 'title',
		'primary_key'		=> 'template_id',
		'model'				=> 'WEAPP_AdminTemplate',
		'per_page'			=> 20,
		'ajax'				=> true,
		'capability'		=> 'manage_weapp_'.weapp_get_appid()
	];

	if($current_tab == 'library'){
		$args['title']			= '模板库';
		$args['primary_key']	= 'id';
	}

	return $args;				
});

add_action('admin_head', function(){
	?>
	<script type="text/javascript">
	jQuery(function($){
		$('body').on('list_table_action_success', function(event, response){
			// console.log(response);

			if(response.list_action == 'choose'){
				$("body #tr_keyword_list .mu-item").last().remove();
			}
		});
		
	});
	</script>
	<?php
});