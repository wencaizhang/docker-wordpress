<?php 
if(!class_exists('WPJAM_List_Table')){
	include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-list-table.php';
}

class WPJAM_Terms_List_Table extends WPJAM_List_Table{
	public function __construct($args = []){
		$args	= wp_parse_args($args, [
			'model'			=> '',
			'taxonomy'		=> '',
			'capability'	=> 'manage_options',	
		]);

		$taxonomy	= $args['taxonomy'];
		$tax_obj	= get_taxonomy($taxonomy);

		$args['title']	= $args['title'] ?? $tax_obj->label;

		$model		= $args['model'];

		$actions	= $model ? $model::get_actions() : [];
		$actions	= apply_filters('wpjam_'.$taxonomy.'_terms_actions', $actions, $taxonomy);

		$bulk_actions	= [];
		if($actions){
			foreach ($actions as $action_key => $action) {
				if(empty($action['bulk'])) continue;

				$capability	= $action['capability']??$args['capability'];

				if(current_user_can($capability)){
					$bulk_actions[$action_key]	= $action['title'];
				}
			}
		}

		$args['actions']		= $actions;
		$args['bulk_actions']	= $bulk_actions;
		
		$this->_args	= $args;

		$this->_args['columns']				= [];
		$this->_args['sortable_columns']	= [];

		$fields	= $this->get_fields();

		if($fields){

			foreach ($fields as $key => $field) {
				$this->_args['columns'][$key] = $field['column_title']??$field['title'];
				
				if(!empty($field['sortable_column'])){
					$this->_args['sortable_columns'][$key] = [$key, true];
				}
			}
		}

		add_filter($taxonomy.'_row_actions',			[$this,	'term_row_actions'],1,2);
		add_action($taxonomy.'_add_form_fields',		[$this,	'term_add_form_fields']);
		add_filter('manage_edit-'.$taxonomy.'_columns',	[$this,	'manage_terms_columns']);
		add_filter('manage_'.$taxonomy.'_custom_column',[$this,	'manage_terms_custom_column'],10,3);

		add_action('created_term',	[$this,	'save_term_fields'],10,3);
		add_action('edited_term',	[$this,	'save_term_fields'],10,3);

		if(wp_doing_ajax()){
			add_action('wp_ajax_wpjam-list-table-action', [$this, 'ajax_response']);
		}else{
			add_action('admin_head', [$this,'admin_head']);

			global $current_screen;

			add_filter('manage_edit-'.$taxonomy.'_sortable_columns',	[$this,	'manage_terms_sortable_columns']);
			add_action('parse_term_query',	[$this,	'parse_term_query']);

			add_filter('bulk_actions-'.$current_screen->id, [$this,'bulk_actions']);
		}
	}

	public function get_taxonomy(){
		return $this->_args['taxonomy'];
	}

	public function get_fields($action_key='', $id=0){
		$model		= $this->get_model();
		$taxonomy	= $this->get_taxonomy();

		$fields		= $model ? $model::get_fields($action_key, $id) : [];
		$fields		= $fields ?: [];

		if($action_key == '' || $action_key == 'add'){
			$term_fields	= wpjam_get_term_options($taxonomy) ?: [];
			
			if($term_fields){
				if($action_key == ''){
					$term_fields	= array_filter($term_fields, function($field){ return !empty($field['show_admin_column']); });	
				}

				$fields	= array_merge($fields, $term_fields);
			}
		}

		return apply_filters('wpjam_'.$taxonomy.'_terms_fields', $fields, $action_key, $id);
	}

	public function ajax_form($list_action, $args=[]){
		
		$bulk	= $_POST['bulk'] ?? false;
		
		if($bulk){
			$ids	= $_POST['ids'] ? wp_parse_args($_POST['ids']) : [];
			$id		= 0;
			$fields	= $this->get_fields($list_action, $ids);
			$nonce	= $this->create_nonce($bulk_action, $id);
		}else{
			$ids	= [];
			$id		= $_POST['id'] ?? '';
			$fields	= $this->get_fields($list_action, $id);
			$nonce	= $this->create_nonce($list_action, $id);
		}

		$data	= isset($_POST['data'])? wp_parse_args($_POST['data']) : [];

		$form	= wpjam_get_ajax_form([
			// 'data_type'		=> $action['data_type'] ?? 'form',
			'data_type'		=> 'form',
			'fields'		=> $fields,
			'data'			=> $data,
			'bulk'			=> $bulk,
			'ids'			=> $ids,
			'id'			=> $id,
			'action'		=> $list_action,
			'submit_text'	=> $args['submit_text'],
			'page_title'	=> $args['page_title'],
			'nonce'			=> $nonce,
			'notice_class'	=> 'list-table-action-notice',
			'form_id'		=> 'list_table_action_form',
		]);

		return $form;
	}

	public function single_row($term){	
		if(is_numeric($term)){
			$term	= get_term($term);
		}

		$wp_list_table = _get_list_table('WP_Terms_List_Table', ['screen'=>$_POST['screen_id']]);
		$wp_list_table->single_row($term);
	}

	public function term_row_actions($row_actions, $term){
		$id			= $term->term_id;
		$actions	= $this->_args['actions'];

		if($actions){
			foreach ($actions as $action_key => $action){
				$row_actions[$action_key] = $this->get_row_action($action_key, compact('id'));
			}
		}

		$model		= $this->get_model();

		if($model){
			if(method_exists($model, 'term_row_actions')){
				$row_actions	= $model::term_row_actions($row_actions, $term);
			}
		}

		$tax_obj	= get_taxonomy($term->taxonomy);
		$supports	= $tax_obj->supports ?? ['slug', 'description', 'parent'];

		if(!in_array('slug', $supports)){
			unset($row_actions['inline hide-if-no-js']);
		}

		$row_actions['term_id'] = 'ID：'.$term->term_id;
		

		return $row_actions;
	}

	public function term_add_form_fields($taxonomy){
		$fields	= $this->get_fields('add');

		wpjam_fields($fields, [
			'data_type'		=> 'term_meta',
			'fields_type'	=> 'div',
			'item_class'	=> 'form-field',
			'is_add'		=> true
		]);
	}

	public function save_term_fields($term_id, $tt_id, $taxonomy){
		if(wp_doing_ajax()){
			if($_POST['action'] == 'inline-save-tax'){
				return;
			}
		}

		$fields	= $this->get_fields('add');

		if($value = wpjam_validate_fields_value($fields)){
			foreach ($value as $key => $field_value) {
				if($field_value === ''){
					if(metadata_exists('term', $term_id, $key)){
						delete_term_meta($term_id, $key);	
					}
				}else{
					update_term_meta($term_id, $key, $field_value);
				}
				// if($field_value){
				//		update_term_meta($term_id, $key, $field_value);
				// }else{
				// 	if(isset($fields[$key]['value'])){	// 如果设置了默认值，也是会存储的
				// 		$field_value	= ($fields[$key]['type'] == 'number')?0:'';
				// 		update_term_meta($term_id, $key, $field_value);
				// 	}elseif(get_term_meta($term_id, $key, true)) {
				// 		delete_term_meta($term_id, $key);
				// 	}
				// }
			}
		}
	}

	public function manage_terms_columns($columns){
		$taxonomy	= $this->get_taxonomy();
		$tax_obj	= get_taxonomy($taxonomy);
		$supports	= $tax_obj->supports ?? ['slug', 'description', 'parent'];

		if(!in_array('slug', $supports)){
			unset($columns['slug']);
		}

		if(!in_array('description', $supports)){
			unset($columns['description']);
		}

		if($this->_args['columns']){
			wpjam_array_push($columns, $this->_args['columns'], 'posts'); 
		}

		$model	= $this->get_model();

		if($model && method_exists($model, 'manage_terms_columns')){
			$columns	= $model::manage_terms_columns($columns);
		}

		return $columns;
	}

	public function manage_terms_custom_column($value, $column_name, $term_id){
		$columns	= $this->_args['columns'];
		if($columns && isset($columns[$column_name])){
			$fields	= $this->get_fields();

			$field	= $fields[$column_name] ?? '';

			$model	= $this->get_model();

			if($model && method_exists($model, 'column_callback') && empty($field['column_callback'])){
				echo $model::column_callback($term_id, $column_name);
			}else{
				echo wpjam_column_callback($column_name, array(
					'id'		=> $term_id,
					'field'		=> $field,
					'data_type'	=> 'term_meta'
				));
			}
		}else{
			$model	= $this->get_model();

			if($model && method_exists($model, 'column_callback')){
				echo $model::column_callback($term_id, $column_name);
			}
		}

		return $value;
	}

	public function manage_terms_sortable_columns($columns){
		if($this->_args['sortable_columns']){
			return array_merge($columns, $this->_args['sortable_columns']);
		}else{
			return $columns;
		}
	}

	public function parse_term_query($term_query){
		if($sortable_columns	= $this->_args['sortable_columns']){
			$orderby	= $term_query->query_vars['orderby'];

			if($orderby && isset($sortable_columns[$orderby])){

				$fields	= $this->get_fields();
				$field	= $fields[$orderby] ?? '';

				$orderby_type = ($field['sortable_column'] == 'meta_value_num')?'meta_value_num':'meta_value';

				$term_query->query_vars['meta_key']	= $orderby;
				$term_query->query_vars['orderby']	= $orderby_type;
			}
		}

		$model	= $this->get_model();

		if($model && method_exists($model, 'parse_term_query')){
			$model::parse_term_query($term_query);
		}
	}

	public function bulk_actions($bulk_actions=[]){
		if($this->_args['bulk_actions']){
			$bulk_actions = array_merge($bulk_actions, $this->_args['bulk_actions']);
		}

		$model	= $this->get_model();

		if($model && method_exists($model, 'bulk_actions')){
			$bulk_actions	= $model::bulk_actions($bulk_actions);
		}
		 
		return $bulk_actions;
	}

	public function admin_head(){
		if($bulk_actions = $this->_args['bulk_actions']){	$actions = $this->_args['actions'];
		?>

		<script type="text/javascript">
		jQuery(function($){
			<?php foreach($bulk_actions as $action_key => $bulk_action) { 
				$bulk_action = $actions[$action_key];
				$page_title	= $bulk_action['page_title']??$bulk_action['title']; 
				$nonce		= $this->create_nonce('bulk_'.$action_key); 
				$direct		= $bulk_action['direct']??''; 
				$confirm	= $bulk_action['confirm']??''; 
			?>
				
			$('.bulkactions option[value=<?php echo $action_key;?>]').data('action', '<?php echo $action_key?>').data('title', '<?php echo $page_title; ?>').data('bulk',1).data('nonce','<?php echo $nonce; ?>').data('direct','<?php echo $direct; ?>').data('confirm','<?php echo $confirm; ?>');
			
			<?php } ?>
		});
		</script>

		<?php } 

		$model	= $this->get_model();

		if($model && method_exists($model, 'admin_head')){
			$model::admin_head();
		}
	}

	protected function get_nonce_action($key, $id=0){
		$nonce_action	= $key.'-term-list-action';

		return ($id)?$nonce_action.'-'.$id:$nonce_action;
	}
}

class WPJAM_Term_List_Table extends WPJAM_Terms_List_Table{

}