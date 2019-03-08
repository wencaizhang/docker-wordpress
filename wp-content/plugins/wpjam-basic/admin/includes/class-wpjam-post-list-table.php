<?php 
class WPJAM_Post_List_Table{
	private $_args;

	public function __construct($args = []){
		$args	= wp_parse_args($args, [
			'model'			=> '',
			'post_type'		=> '',
			'capability'	=> 'manage_options'	
		]);

		$post_type	= $args['post_type'];

		$model		= $args['model'];

		$actions	= $model ? $model::get_actions() : [];
		$actions	= apply_filters('wpjam_'.$post_type.'_posts_actions', $actions);

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

		add_filter('views_edit-'.$post_type,	[$this,'views'],1,2);
		add_action('pre_get_posts', 			[$this,'pre_get_posts']);
		add_action('restrict_manage_posts',		[$this,'restrict_manage_posts']);

		if(is_post_type_hierarchical($post_type)){
			add_filter('page_row_actions',		[$this,'post_row_actions'],1,2);
		}else{
			add_filter('post_row_actions',		[$this,'post_row_actions'],1,2);
		}
		
		add_filter('manage_'.$post_type.'_posts_columns',			[$this,'manage_posts_columns']);
		add_action('manage_'.$post_type.'_posts_custom_column',		[$this,'manage_posts_custom_column'], 10, 2);
		add_filter('manage_edit-'.$post_type.'_sortable_columns',	[$this,'manage_posts_sortable_columns']);
		

		if(wp_doing_ajax()){
			add_action('wp_ajax_post-list-table-action', [$this, 'ajax_response']);
		}else{
			add_action('admin_head', [$this,'admin_head']);

			global $current_screen;

			add_filter('bulk_actions-'.$current_screen->id, [$this,'bulk_actions']);
		}
	}

	public function views($views){
		$model	= $this->get_model();

		if($model && method_exists($model, 'views')){
			$views = $model::views($views);
		}

		return $views;
	}

	public function get_model(){
		return $this->_args['model'];
	}

	public function get_post_type(){
		return $this->_args['post_type'];
	}

	public function get_action($key){
		$actions	= $this->_args['actions'];
		return $actions[$key] ?? [];
	}

	public function get_action_capability($key){
		$action	= $this->get_action($key);
		if($action){
			return $action['capability']??$this->_args['capability'];
		}else{
			return $this->_args['capability'];
		}
	}

	public function get_row_action($action_key, $args=[]){
		extract(wp_parse_args($args, [
			'id'		=> 0,
			'data'		=> [],
			'class'		=> '',
			'style'		=> '',
			'title'		=> '',
			'tag'		=> 'a'
		]));

		if(empty($id)){
			return '';
		}

		$action	= $this->get_action($action_key);
		if(!$action) {
			return '';
		}

		$capability	= $this->get_action_capability($action_key);
		if(!current_user_can($capability)) {
			return '';
		}

		$title			= $title?:$action['title'];
		$page_title		= $action['page_title'] ?? $action['title'];

		$class			= $class ?: '';
		$class			= 'post-list-table-action '.$class;

		$data_attr		= '" data-action="'.$action_key.'"';
	
		$nonce			= $this->create_nonce($action_key, $id);
		$direct			= $action['direct'] ?? '';
		$confirm		= $action['confirm'] ?? '';

		$data_values	= compact('nonce', 'id', 'direct', 'confirm');

		foreach ($data_values as $data_key=>$value) {
			if($value){
				$data_attr	.= ' data-'.$data_key.'="'.$value.'"';
			}
		}

		if($tag == 'a'){
			return '<a href="javascript:;" title="'.$page_title.'" class="'.$class.'" '.$style.' '.$data_attr.'>'.$title.'</a>';
		}else{
			return '<'.$tag.' title="'.$page_title.'" class="'.$class.'" '.$style.' '.$data_attr.'>'.$title.'</'.$tag.'>';
		}
	}

	public function get_fields($action_key='', $id=0){
		$model		= $this->get_model();
		$post_type	= $this->get_post_type();

		$fields		= $model ? $model::get_fields($action_key, $id) : [];

		if($action_key == ''){
			$post_fields	= wpjam_get_post_fields($post_type) ?: [];
			
			if($post_fields){
				$post_fields	= array_filter($post_fields, function($field){ return !empty($field['show_admin_column']); });
				$fields			= array_merge($fields, $post_fields);
			}

			if($this->get_post_type() == 'page'){
				$fields['template']		= ['title'=>'模板',	'column_callback'=>'get_page_template_slug'];

			}else{
				$fields['thumbnail']	= ['title'=>'缩略图'];
				$fields['views']		= ['title'=>'浏览',	'sortable_column'=>'meta_value_num'];
			}
		}

		return apply_filters('wpjam_'.$post_type.'_posts_fields', $fields, $action_key, $id);
	}

	public function list_action($list_action='', $id=0, $data=null){
		$result	= null;
		$bulk	= false;

		if(is_array($id)){
			$ids			= $id;
			$bulk			= true;
			$bulk_action	= 'bulk_'.$list_action;
		}

		$model	= $this->get_model();

		if($model){
			if(is_null($data)){
				if($bulk){
					if(method_exists($model, $bulk_action)){
						$result	= $model::$bulk_action($ids);
					}else{
						if(method_exists($model, $list_action)){
							foreach($ids as $_id) {
								$result	= $model::$list_action($_id);
								if(is_wp_error($result)){
									return $result;
								}
							}
						}
					}
				}else{
					if(method_exists($model, $list_action)){
						$result	= $model::$list_action($id);
					}
				}	
			}else{
				if($bulk){
					if(method_exists($model,$bulk_action)){
						$result	= $model::$bulk_action($ids, $data);
					}else{
						if(method_exists($model, $list_action)){
							foreach($ids as $_id) {
								$result	= $model::$list_action($_id, $data);
								if(is_wp_error($result)){
									return $result;
								}
							}
						}
					}
				}else{
					if(method_exists($model, $list_action)){
						$result	= $model::$list_action($id, $data);
					}
				}
			}
		}

		$post_type	= $this->get_post_type();

		$result	= apply_filters('wpjam_'.$post_type.'_posts_list_action', $result, $list_action, $id, $data);

		if($result){
			return $result;
		}else{
			return new WP_Error('empty_list_action', '没有定义该操作');
		}
	}

	public function ajax_response(){
		$model	= $this->get_model();

		$list_action_type	= $_POST['list_action_type'];
		$list_action		= $_POST['list_action'];
		
		$capability = $this->get_action_capability($list_action);
		if(!current_user_can($capability)){
			wpjam_send_json([
				'errcode'	=>'no_authority', 
				'errmsg'	=>'无权限'
			]);
		}

		$nonce	= $_POST['_ajax_nonce'];
		$bulk	= $_POST['bulk']??false;
		$ids	= [];
		$id		= 0;

		if($bulk){
			$bulk_action	= 'bulk_'.$list_action;
			$ids			= $_POST['ids']? wp_parse_args($_POST['ids']) : [];

			if(!$this->verify_nonce($nonce, $bulk_action)){
				wpjam_send_json([
					'errcode'	=> 'invalid_nonce',
					'errmsg'	=> '非法操作'
				]);
			}
		}else{
			$id		= $_POST['id']??'';

			if(!$this->verify_nonce($nonce, $list_action, $id)){
				wpjam_send_json([
					'errcode'	=> 'invalid_nonce',
					'errmsg'	=> '非法操作'
				]);
			}
		}
		
		$actions	= $this->_args['actions'];

		$action		= $actions[$list_action]??[];
		if(!$action) {
			wpjam_send_json([
				'errcode'	=> 'invalid_action',
				'errmsg'	=> '非法操作'
			]);
		}

		if($list_action_type == 'submit'){
			$page_title	= $action['submit_text'] ?? $action['title'];
		}else{
			$page_title	= $action['page_title'] ?? $action['title'];
		}

		$response_type	= $action['response'] ?? $list_action;

		if($list_action_type == 'direct'){
			if($bulk){
				$result	= $this->list_action($list_action, $ids); 
			}else{
				$result	= $this->list_action($list_action, $id);
				if($list_action == 'duplicate'){
					$id = $result;
				}
			}	
		}else{
			$data	= isset($_POST['data'])?wp_parse_args($_POST['data']):[];

			ob_start();

			$submit_text	= $action['submit_text'] ?? $action['title'];

			if($bulk){
				$fields	= $this->get_fields($list_action, $ids);
				$nonce	= $this->create_nonce($bulk_action, $id);
			}else{
				$fields	= $this->get_fields($list_action, $id);
				$nonce	= $this->create_nonce($list_action, $id);
			}

			WPJAM_AJAX::form([
				'data_type'		=> $action['data_type'] ?? 'form',
				'fields'		=> $fields,
				'data'			=> $data,
				'bulk'			=> $bulk,
				'ids'			=> $ids,
				'id'			=> $id,
				'action'		=> $list_action,
				'submit_text'	=> $submit_text,
				'page_title'	=> $page_title,
				'nonce'			=> $nonce,
				'notice_class'	=> 'list-table-action-notice',
				'form_id'		=> 'post_list_table_action_form',
			]);
			
			$form	= ob_get_clean();

			if($list_action_type == 'form'){
				wpjam_send_json(['errcode'=>0,	'page_title'=>$page_title, 'form'=>$form, 'type'=>$response_type]);
			}

			if($bulk){
				$result	= $this->list_action($list_action, $ids, $data); 
			}else{
				$result	= $this->list_action($list_action, $id, $data);
			}	
		}

		if(is_wp_error($result)){
			wpjam_send_json($result);
		}

		if($response_type == 'append'){
			wpjam_send_json(['errcode'=>0, 'page_title'=>$page_title,'data'=>$result, 'type'=>$response_type]);
		}elseif($response_type == 'delete'){
			$data ='';
		}elseif($response_type == 'duplicate'){
			if(is_numeric($result)){
				$id = $result;

				ob_start();
				$wp_list_table = _get_list_table('WP_Posts_List_Table', ['screen' => $_POST['screen']]);
				$wp_list_table->single_row(get_post($id));
				$data	= ob_get_clean();
			}else{
				$data	= '';
			}	
		}else{
			if($bulk){
				$wp_list_table = _get_list_table('WP_Posts_List_Table', ['screen' => $_POST['screen']]);
				$data	= [];
				foreach ($ids as $id) {
					ob_start();
					$wp_list_table->single_row(get_post($id));
					$data[$id]	= ob_get_clean();
				}
			}else{
				ob_start();
				$wp_list_table = _get_list_table('WP_Posts_List_Table', ['screen' => $_POST['screen']]);
				$wp_list_table->single_row(get_post($id));
				$data	= ob_get_clean();
			}
		}

		$errmsg = $result['errmsg'] ?? '';

		if($list_action_type == 'submit'){
			wpjam_send_json(['errcode'=>0, 'errmsg'=>$errmsg, 'data'=>$data, 'type'=>$response_type, 'form'=>$form]);
		}else{
			wpjam_send_json(['errcode'=>0, 'errmsg'=>$errmsg, 'data'=>$data, 'type'=>$response_type]);
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

		if($model && method_exists($model, 'post_row_actions')){
			$row_actions	= $model::post_row_actions($row_actions, $post);
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

			if($taxonomy->name == 'category' || empty($taxonomy->hierarchical) || empty($taxonomy->query_var) || empty($taxonomy->filterable)){
				continue;
			}

			$selected	= !empty($_GET[$taxonomy->name]) ? sanitize_title($_GET[$taxonomy->name]) : '';

			$dropdown_options = array(
				'taxonomy'			=> $taxonomy->name,
				'show_option_all'	=> $taxonomy->labels->all_items,
				'hide_empty'		=> 1,
				'hierarchical'		=> 1,
				'show_count'		=> 0,
				'orderby'			=> 'name',
				'value_field'		=> 'slug',
				'name'				=> $taxonomy->name,
				'selected'			=> $selected
			);

			wp_dropdown_categories($dropdown_options);
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

	public function manage_posts_custom_column($column_name, $post_id){
		$columns	= $this->_args['columns'];
		
		if($columns && isset($columns[$column_name])){
			if($column_name == 'thumbnail'){
				echo wpjam_get_post_thumbnail($post_id, [50,50]);
			}elseif($column_name == 'views'){
				echo wpjam_get_post_views($post_id);
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

			if($orderby && isset($sortable_columns[$orderby])){
				$fields	= $this->get_fields();
				$field	= $fields[$orderby] ?? '';

				$wp_query->set('meta_key', $orderby);
				
				$orderby_type = ($field['sortable_column'] == 'meta_value_num')?'meta_value_num':'meta_value';
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

	public function create_nonce($key, $id=0){
		$nonce_action	= $this->get_nonce_action($key, $id);
		return wp_create_nonce($nonce_action);
	}

	public function verify_nonce($nonce, $key, $id=0){
		$nonce_action	= $this->get_nonce_action($key, $id);
		return wp_verify_nonce($nonce, $nonce_action);
	}

	public function get_nonce_action($key, $id=0){
		$nonce_action	= $key.'-post-list-action';

		return ($id)?$nonce_action.'-'.$id:$nonce_action;
	}
}