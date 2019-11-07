<?php 
if(!class_exists('WPJAM_List_Table')){
	include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-list-table.php';
}

class WPJAM_Posts_List_Table extends WPJAM_List_Table{
	public function __construct($args = []){
		$args	= wp_parse_args($args, [
			'model'			=> '',
			'post_type'		=> '',
			'capability'	=> 'manage_options',	
		]);

		$post_type	= $args['post_type'];

		$pt_obj		= get_post_type_object($post_type);
		$args['title']	= $args['title'] ?? $pt_obj->label;


		$model		= $args['model'];

		$actions	= $model ? $model::get_actions() : [];
		$actions	= apply_filters('wpjam_'.$post_type.'_posts_actions', $actions, $post_type);

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

		add_filter('views_edit-'.$post_type,	[$this,'post_type_views'],1,2);
		add_action('pre_get_posts', 			[$this,'pre_get_posts']);
		add_action('restrict_manage_posts',		[$this,'restrict_manage_posts']);

		if(is_post_type_hierarchical($post_type)){
			add_filter('page_row_actions',		[$this,'post_row_actions'],1,2);
		}else{
			if($post_type == 'attachment'){
				add_filter('media_row_actions',	[$this,'post_row_actions'],1,2);
			}else{
				add_filter('post_row_actions',	[$this,'post_row_actions'],1,2);
			}
			
		}
		
		if($post_type == 'attachment'){
			add_filter('manage_media_columns',			[$this, 'manage_media_columns']);
			add_filter('manage_media_custom_column',	[$this, 'manage_media_custom_column', 10, 2]);
		}else{
			add_filter('manage_'.$post_type.'_posts_columns',			[$this, 'manage_posts_columns']);
			add_action('manage_'.$post_type.'_posts_custom_column',		[$this, 'manage_posts_custom_column'], 10, 2);
			add_filter('manage_edit-'.$post_type.'_sortable_columns',	[$this, 'manage_posts_sortable_columns']);
		}

		if(wp_doing_ajax()){
			add_action('wp_ajax_wpjam-list-table-action', [$this, 'ajax_response']);
		}else{
			add_action('admin_head', [$this,'admin_head']);

			global $current_screen;

			add_filter('bulk_actions-'.$current_screen->id, [$this,'bulk_actions']);
		}
	}

	public function post_type_views($views){
		$model	= $this->get_model();

		if($model && method_exists($model, 'views')){
			$views = $model::views($views);
		}

		return $views;
	}

	public function get_post_type(){
		return $this->_args['post_type'];
	}

	public function get_fields($action_key='', $id=0){
		$model		= $this->get_model();
		$post_type	= $this->get_post_type();

		$fields		= $model ? $model::get_fields($action_key, $id) : [];
		$fields		= $fields ?: [];

		if($action_key == ''){
			$post_fields	= wpjam_get_post_fields($post_type) ?: [];
			
			if($post_fields){
				$post_fields	= array_filter($post_fields, function($field){ return !empty($field['show_admin_column']); });
				$fields			= array_merge($fields, $post_fields);
			}

			if($this->get_post_type() == 'page'){
				$fields['template']		= ['title'=>'模板',	'column_callback'=>'get_page_template_slug'];
			}else{
				if(is_post_type_viewable($this->get_post_type())){
					$fields['thumbnail']	= ['title'=>'缩略图'];
					$fields['views']		= ['title'=>'浏览',	'sortable_column'=>'meta_value_num'];
				}elseif(post_type_supports($post_type, 'thumbnail')){
					$fields['thumbnail']	= ['title'=>'缩略图'];
				}
			}
		}

		return apply_filters('wpjam_'.$post_type.'_posts_fields', $fields, $action_key, $id);
	}

	public function ajax_form($list_action, $args=[]){
		
		$bulk	= $_POST['bulk'] ?? false;
		
		if($bulk){
			$ids	= $_POST['ids'] ? wp_parse_args($_POST['ids']) : [];
			$id		= 0;
			$fields	= $this->get_fields($list_action, $ids);
			$nonce	= $this->create_nonce('bulk_'.$list_action, $id);
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

	public function single_row($_post){
		global $post, $authordata;

		if(is_numeric($_post)){
			$post	= get_post($_post);
		}else{
			$post	= $_post;	
		}
		
		$post_type	= $post->post_type;

		$authordata = get_userdata( $post->post_author );

		if($post_type == 'attachment'){
			$wp_list_table = _get_list_table('WP_Media_List_Table', ['screen'=>$_POST['screen_id']]);

			$post_owner = ( get_current_user_id() == $post->post_author ) ? 'self' : 'other';
			?>
			<tr id="post-<?php echo $post->ID; ?>" class="<?php echo trim( ' author-' . $post_owner . ' status-' . $post->post_status ); ?>">
				<?php $wp_list_table->single_row_columns($post); ?>
			</tr>
			<?php
		}else{
			$wp_list_table = _get_list_table('WP_Posts_List_Table', ['screen'=>$_POST['screen_id']]);
			$wp_list_table->single_row($post);
		}
	}

	public function post_row_actions($row_actions, $post){
		$id			= $post->ID;
		$actions	= $this->_args['actions'];
		$model		= $this->get_model();

		if($actions){
			foreach ($actions as $action_key => $action){
				$row_actions[$action_key] = $this->get_row_action($action_key, compact('id'));
			}
		}

		if($model){
			$post_type	= $this->get_post_type();
			
			if($post_type == 'attachment'){
				if(method_exists($model, 'media_row_actions')){
					$row_actions	= $model::media_row_actions($row_actions, $post);
				}
			}else{
				if(method_exists($model, 'post_row_actions')){
					$row_actions	= $model::post_row_actions($row_actions, $post);
				}
			}
		}

		$row_actions['post_id'] = 'ID: '.$post->ID;

		return $row_actions;
	}

	public function restrict_manage_posts($post_type){
		$taxonomies	= get_object_taxonomies($post_type, 'objects');

		if(empty($taxonomies)){
			return;
		}

		foreach($taxonomies as $taxonomy) {

			if($taxonomy->name == 'category' || empty($taxonomy->hierarchical) || empty($taxonomy->filterable)){
				continue;
			}

			$taxonomy_key	= $taxonomy->name.'_id';

			$selected	= 0;
			if(!empty($_REQUEST[$taxonomy_key])){
				$selected	= $_REQUEST[$taxonomy_key];
			}elseif(!empty($_REQUEST['taxonomy']) && ($_REQUEST['taxonomy'] == $taxonomy->name) && !empty($_REQUEST['term'])){
				if($term		= get_term_by('slug', $_REQUEST['term'], $taxonomy->name)){
					$selected	= $term->term_id;
				}
			}elseif(!empty($taxonomy->query_var) && !empty($_REQUEST[$taxonomy->query_var])){
				if($term	= get_term_by('slug', $_REQUEST[$taxonomy->query_var], $taxonomy->name)){
					$selected	= $term->term_id;
				}
			}

			wp_dropdown_categories(array(
				'taxonomy'			=> $taxonomy->name,
				'show_option_all'	=> $taxonomy->labels->all_items,
				'hide_empty'		=> 0,
				'hierarchical'		=> 1,
				'show_count'		=> 0,
				'orderby'			=> 'name',
				'name'				=> $taxonomy_key,
				'selected'			=> $selected
			));
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

	public function manage_posts_columns($columns){
		if($this->_args['columns']){
			wpjam_array_push($columns, $this->_args['columns'], 'date'); 
		}

		$model	= $this->get_model();

		if($model && method_exists($model, 'manage_posts_columns')){
			$columns	= $model::manage_posts_columns($columns);
		}

		return $columns;	
	}

	public function manage_media_columns($columns){
		$model	= $this->get_model();

		if($model && method_exists($model, 'manage_media_columns')){
			$columns	= $model::manage_media_columns($columns);
		}

		return $columns;	
	}

	public function manage_posts_custom_column($column_name, $post_id){
		$columns	= $this->_args['columns'];
		
		if($columns && isset($columns[$column_name])){
			if($column_name == 'thumbnail'){
				echo wpjam_get_post_thumbnail($post_id, [50,50]);
			}elseif($column_name == 'views'){
				echo wpjam_get_post_views($post_id, false);
			}else{
				$fields	= $this->get_fields();
				$field	= $fields[$column_name] ?? '';

				$model	= $this->get_model();

				if($model && method_exists($model, 'column_callback') && empty($field['column_callback'])){
					echo $model::column_callback($post_id, $column_name);
				}else{
					echo wpjam_column_callback($column_name, array(
						'id'		=> $post_id,
						'field'		=> $field,
						'data_type'	=> 'post_meta'
					));
				}
			}
		}else{
			$model	= $this->get_model();

			if($model && method_exists($model, 'column_callback')){
				echo $model::column_callback($post_id, $column_name);
			}
		}
	}

	public function manage_media_custom_column($column_name, $post_id){
		$model	= $this->get_model();
		
		if($model && method_exists($model, 'column_callback')){
			echo $model::column_callback($post_id, $column_name);
		}
	}

	public function manage_posts_sortable_columns($columns){
		if($this->_args['sortable_columns']){
			return array_merge($columns, $this->_args['sortable_columns']);
		}else{
			return $columns;
		}
	}

	public function pre_get_posts($wp_query){
		if($sortable_columns	= $this->_args['sortable_columns']){
			$orderby	= $wp_query->get('orderby');

			if($orderby && is_string($orderby) && isset($sortable_columns[$orderby])){
				$fields	= $this->get_fields();
				$field	= $fields[$orderby] ?? '';

				$orderby_type = ($field['sortable_column'] == 'meta_value_num')?'meta_value_num':'meta_value';
				
				$wp_query->set('meta_key', $orderby);
				$wp_query->set('orderby', $orderby_type);
			}
		}

		$model	= $this->get_model();

		if($model && method_exists($model, 'pre_get_posts')){
			$model::pre_get_posts($wp_query);
		}
	}

	public function the_posts($posts){
		if($posts){
			$model	= $this->get_model();

			if($model && method_exists($model, 'the_posts')){
				$posts = $model::the_posts($posts);
			}
		}

		return $posts;
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
		$nonce_action	= $key.'-post-list-action';

		return ($id)?$nonce_action.'-'.$id:$nonce_action;
	}
}

class WPJAM_Post_List_Table extends WPJAM_Posts_List_Table{
	
}