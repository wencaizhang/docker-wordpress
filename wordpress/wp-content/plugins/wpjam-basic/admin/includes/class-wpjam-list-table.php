<?php
if(!class_exists('WP_List_Table')){
	include ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WPJAM_List_Table extends WP_List_Table {
	public function __construct($args = []){
		$args	= wp_parse_args($args, [
			'screen'			=> '',
			'title'				=> '',
			'plural'			=> '',
			'singular'			=> '',
			'primary_key'		=> '',
			'primary_column'	=> '',
			'fields'			=> [],
			'flat_fields'		=> [],
			'columns'			=> [],
			'sortable_columns'	=> [],
			'options_columns'	=> [],
			'bulk_actions'		=> [],
			'query_data'		=> [], // 额外参数
			'capability'		=> 'manage_options',
			'per_page'			=> 50,
			'model'				=> '',
			'ajax'				=> true,
			'sortable'			=> false,
			// 'modes'			=> '',
			'actions'			=> [
				'add'		=> ['title'=>'新建'],
				'edit'		=> ['title'=>'编辑'],
				'duplicate'	=> ['title'=>'复制'],
				'delete'	=> ['title'=>'删除',	'direct'=>true,	'bulk'=>true, 'confirm'=>true],
			]
		]);

		$args['screen']	= $args['screen'] ?: ($args['name'] ?? '');

		$model	= $args['model'];

		if(!$model || !class_exists($model)){
			$model	= $args['model'] = '';
		}

		if($model && method_exists($model,'get_primary_key')){
			$args['primary_key']	= $args['model']::get_primary_key();	
		}

		if($model && method_exists($model, 'get_actions')){
			$args['actions']	= $model::get_actions();
		}

		$args['actions']	= apply_filters(wpjam_get_filter_name($args['singular'], 'actions'), $args['actions']);

		$args['actions']	= $args['actions'] ?: [];

		if($args['sortable']){
			$args['actions']	= array_merge($args['actions'],[
				'move'	=> ['direct'=>true, 'title'=>'<span class="dashicons dashicons-move"></span>',			'page_title'=>'拖动'],
				'up'	=> ['direct'=>true, 'title'=>'<span class="dashicons dashicons-arrow-up-alt"></span>',	'page_title'=>'向上移动'],
				'down'	=> ['direct'=>true, 'title'=>'<span class="dashicons dashicons-arrow-down-alt"></span>',	'page_title'=>'向下移动'],
			]);
		}

		if($args['actions']){
			$bulk_actions	= [];

			if($model){
				foreach ($args['actions'] as $action_key => $action) {
					if(empty($action['bulk'])) {
						continue;
					}

					$capability	= $action['capability'] ?? $args['capability'];

					if(current_user_can($capability)){
						$bulk_actions[$action_key]	= $action['title'];
					}
				}

				if($bulk_actions){
					$args['bulk_actions']	= array_merge($args['bulk_actions'], $bulk_actions);
				}
			}else{
				$args['bulk_actions']	= [];
			}	
		}

		if($model && method_exists($model, 'get_fields')){
			$args['fields']	= $model::get_fields();
		}

		if($fields = $args['fields']){
			if(!empty($args['bulk_actions'])){
				$args['columns']['cb'] = 'checkbox';
				unset($fields['cb']);
			}
			
			foreach($fields as $key => $field){
				if($field['type'] == 'fieldset'){
					foreach ($field['fields'] as $sub_key => $sub_field){
						$args['flat_fields'][$sub_key]	= $sub_field;

						if(empty($sub_field['show_admin_column'])) {
							continue;
						}

						$args['columns'][$sub_key] = $sub_field['column_title']??$sub_field['title'];

						if(!empty($sub_field['options'])){
							$args['options_columns'][$sub_key] = $sub_field['options'];
						}

						if(!empty($sub_field['sortable_column'])){
							$args['sortable_columns'][$sub_key] = [$sub_key, true];
						}	
					}
				}else{
					$args['flat_fields'][$key]	= $field;

					if(empty($field['show_admin_column'])) {
						continue;
					}

					$args['columns'][$key] = $field['column_title'] ?? $field['title'];

					if(!empty($field['options'])){
						$args['options_columns'][$key] = $field['options'];
					}

					if(!empty($field['sortable_column'])){
						$args['sortable_columns'][$key] = [$key, true];
					}	
				}
			}
		}

		global $current_query_data;
		if(!empty($current_query_data)){
			$args['query_data']	= array_merge($current_query_data, $args['query_data']);
		}

		if(is_array($args['per_page'])){
			add_screen_option('per_page', $args['per_page']);	// 选项
		}

		if(!empty($args['style'])){
			add_action('admin_enqueue_scripts', function(){
				wp_add_inline_style('list-tables', $this->_args['style']);
			});
		}

		parent::__construct($args);
	}

	public function get_model(){
		return $this->_args['model'];
	}

	public function get_action($key){
		$actions	= $this->_args['actions'];

		if(isset($actions[$key])){
			$action	= $actions[$key];

			$action['key']	= $key;

			if(!empty($action['overall'])){
				$action['response']	= 'list';
			}

			return $action;
		}else{
			return [];
		}
	}

	protected function create_nonce($key, $id=''){
		return wp_create_nonce($this->get_nonce_action($key, $id));
	}

	protected function verify_nonce($nonce, $key, $id=''){
		return wp_verify_nonce($nonce, $this->get_nonce_action($key, $id));
	}

	protected function get_nonce_action($key, $id=0){
		$nonce_action	= $key.'-'.$this->_args['singular'];
		return $id ? $nonce_action.'-'.$id : $nonce_action;
	}

	

	public function get_row_action($action, $args=[]){
		if(is_string($action)){
			$action	= $this->get_action($action);
			if(!$action){
				return '';
			}
		}

		if(!$this->get_model() && !isset($this->_args['post_type']) && !isset($this->_args['taxonomy'])){
			return $this->get_row_action_compat($action, $args);
		}

		$capability	= $action['capability'] ?? $this->_args['capability'];

		if($capability != 'read' && !current_user_can($capability)){
			return '';
		}

		$args		= wp_parse_args($args, ['id'=>0, 'data'=>[], 'class'=>'', 'style'=>'', 'title'=>'', 'tag'=>'a']);
		$style		= $args['style'] ? ' style="'.$args['style'].'"' : '';
		$title		= $args['title'] ?: $action['title'];
		$page_title	= $action['page_title'] ?? ($action['title'].$this->_args['title']);
		$page_title	= esc_attr(wp_strip_all_tags($page_title));

		if(!empty($action['filter'])){
			$class		= 'list-table-filter '.$args['class'];

			$defaults	= $action['data'] ?? [];
			$data		= wp_parse_args($args['data'], $defaults);

			$data_attr	= $data ? 'data-filter=\''.$this->parse_data_filter($data).'\'' : '';
		}else{
			if($action['key'] == 'move'){
				$class	= 'list-table-move-action ';
			}else{
				$class	= 'list-table-action ';	
			}
			
			$class	.= $args['class'];

			$data_attr	= $this->get_action_data_attr($action, $args);
		}

		if($args['tag'] == 'a'){
			return '<a href="javascript:;" title="'.esc_attr($page_title).'" class="'.$class.'" '.$style.' '.$data_attr.'>'.$title.'</a>';
		}else{
			return '<'.$args['tag'].' title="'.esc_attr($page_title).'" class="'.$class.'"'.$style.' '.$data_attr.'>'.$title.'</'.$args['tag'].'>';
		}
	}

	private function get_action_data_attr($action, $args=[]){
		$args	= wp_parse_args($args, ['type'=>'button', 'id'=>0, 'data'=>[], 'bulk'=>false, 'ids'=>[]]);
		$key	= $action['key'];
		
		$datas	= [];
		$attr	= 'data-action="'.$key.'"';

		if($args['type'] == 'button'){

			$defaults	= $action['data'] ?? [];
		
			if(isset($this->_args['query_data'])){
				$defaults	= array_merge($defaults, $this->_args['query_data']);	
			}

			$data		= wp_parse_args($args['data'], $defaults);

			$datas['data']		= $data ? http_build_query($data) : '';
			$datas['direct']	= $action['direct'] ?? '';
			$datas['confirm']	= $action['confirm'] ?? '';
			$datas['tb_width']	= $action['tb_width'] ?? '';
			$datas['tb_height']	= $action['tb_height'] ?? '';
		}

		$datas['bulk']		= $args['bulk'];

		if($args['bulk']){
			$datas['nonce']	= $this->create_nonce('bulk_'.$key);
			$datas['ids']	= $args['ids'] ? http_build_query($args['ids']) : '';
		}else{
			$datas['nonce']	= $this->create_nonce($key, $args['id']);
			$datas['id']	= $args['id'];
		}

		foreach ($datas as $data_key=>$data_value) {
			if($data_value || $data_value === 0){
				$attr	.= ' data-'.$data_key.'="'.$data_value.'"';
			}
		}

		return $attr;
	}

	private function parse_data_filter($filters){
		$data_filters	= [];

		foreach ($filters as $name => $value) {
			$data_filters[]	= ['name'=>$name, 'value'=>$value];
		}

		return wpjam_json_encode($data_filters);
	}

	public function get_filter_link($filters, $title, $class=''){
		$title_attr	= esc_attr(wp_strip_all_tags($title));

		return '<a href="javascript:;" title="'.$title_attr.'" class="list-table-filter '.$class.'" data-filter=\''.$this->parse_data_filter($filters).'\'>'.$title.'</a>';
	}

	public function get_fields($key='', $id=0){
		$action	= $this->get_action($key);

		if(!empty($action['direct'])){
			return[];
		}

		$model = $this->get_model();

		if($model && method_exists($model, 'get_fields')){
			$fields = $model::get_fields($key, $id);

			if($query_data = $this->_args['query_data']){
				foreach($query_data as $data_key => $data_value){
					$fields[$data_key]	= ['title'=>'', 'type'=>'hidden', 'value'=>wpjam_get_data_parameter($data_value)];	
				}
			}

			$primary_key	= $this->_args['primary_key'];

			if(!in_array($key, ['add', 'duplicate']) && $primary_key && isset($fields[$primary_key])){
				$fields[$primary_key]['type']	= 'view';
			}

			return apply_filters(wpjam_get_filter_name($this->_args['singular'], 'fields'), $fields, $key, $id);
		}else{
			return $this->get_fields_compat($key, $id);	
		}
	}

	public function single_row($raw_item) {
		$model	= $this->get_model();

		if($model && (!is_array($raw_item) || is_object($raw_item))){
			$raw_item	= $model::get($raw_item);
		}

		if(empty($raw_item)){
			echo '';
			return ;
		}

		$raw_item	= (array)$raw_item;

		if($model && method_exists($model, 'before_single_row')){
			$model::before_single_row($raw_item);
		}

		$primary_key	= $this->_args['primary_key'];
		$id				= $raw_item[$primary_key];

		if($primary_key){
			$id		= str_replace('.', '-', $id);
		}

		if($primary_key && $this->_args['sortable']){
			$data_attr	= 'data-id="'.$id.'"';
		}else{
			$data_attr	= '';
		}

		$item	= $this->parse_item($raw_item);
		$style	= isset($item['style'])?' style="'.$item['style'].'"':'';

		if($primary_key){
			$class	= isset($item['class'])?' class="'.$item['class'].' tr-'.$id.'"':' class=" tr-'.$id.'"';

			echo '<tr id="'.$this->_args['singular'].'-'.$id.'" '.$data_attr.' ' . $style . $class . '>';
		}else{
			$class	= isset($item['class'])?' class="'.$item['class'].'"':'';

			echo '<tr' . $style . $class . '>';
		}
		
		$this->single_row_columns($item);
		echo '</tr>';

		if($model && method_exists($model, 'after_single_row')){
			$model::after_single_row($item, $raw_item);
		}
	}

	protected function parse_item($raw_item){
		$item	= (array)$raw_item;
		$model	= $this->get_model();

		$actions			= $this->_args['actions'];
		$primary_key		= $this->_args['primary_key'];
		$options_columns	= $this->_args['options_columns'];

		if($model && method_exists($model, 'row_actions')){
			$actions = $model::row_actions($actions, $item);
		}
		
		if($primary_key && $actions){
			$item_id		= $item[$primary_key];
			$row_actions	= [];

			foreach ($actions as $action_key => $action) {
				if($action_key == 'add' || !empty($action['overall'])){
					continue;
				}

				$action['key']	= $action_key;

				if(!empty($action['filter'])){
					$data			= $action['data'] ?? [];
					if(is_array($action['filter'])){
						$filter_keys	= $action['filter'];
					}else{
						$filter_keys	= explode(',', $filter_keys);
					}

					foreach($filter_keys as $filter_key){
						if(isset($item[$filter_key])){
							$data[$filter_key]	= $item[$filter_key];	
						}
					}

					$action['data']	= $data;
				}

				if($row_action = $this->get_row_action($action, ['id'=>$item_id])){
					$row_actions[$action_key] = $row_action;
				}
			}

			if($primary_key == 'id'){
				$row_actions[$primary_key]	= 'ID：'.$item_id;	// 显示 id
			}

			$item['row_actions']	= apply_filters(wpjam_get_filter_name($this->_args['singular'], 'row_actions'), $row_actions, $raw_item);
		}

		if(!$model){
			return $this->parse_item_compact($item);
		}

		if(method_exists($model, 'item_callback')){
			$item = $model::item_callback($item);	
		}

		if(method_exists($model, 'get_filterable_fields') && ($filterable_fields = $model::get_filterable_fields())) {
			foreach ($filterable_fields as $field_key) {
				if(isset($item[$field_key])){
					if($options_columns && isset($options_columns[$field_key])){
						$item_value		= $item[$field_key];
						$options		= $options_columns[$field_key];

						$option_value	= $options[$item_value]??'';
						$option_value	= is_array($option_value)?$option_value['title']:$option_value;

						$item[$field_key]	= $option_value? $this->get_filter_link([$field_key=>$item_value], $option_value):$item_value;

						unset($options_columns[$field_key]);
					}else{
						if($item[$field_key] && isset($raw_item[$field_key])){
							$item[$field_key] = $this->get_filter_link([$field_key=>$raw_item[$field_key]], $item[$field_key]);
						}
					}
				}
			}
		}

		if(!empty($options_columns)){
			foreach ($options_columns as $field_key => $options) {
				if(isset($item[$field_key])){
					if($this->_args['fields'] && $this->_args['flat_fields'][$field_key]['type'] == 'checkbox' && $item[$field_key]){
						$item[$field_key]	= wp_array_slice_assoc($options, $item[$field_key]);
						$item[$field_key]	= implode(',', $item[$field_key]);
					}else{
						$item[$field_key]	= $options[$item[$field_key]]??$item[$field_key];
					}
				}
			}
		}

		return $item;
	}

	public function display(){
		$model = $this->get_model();

		if($model){
			parent::display();
		}else{
			$this->display_compat();
		}
	}

	public function list_page(){
		$model 		= $this->get_model();	
		$actions	= $this->_args['actions'];

		global $current_tab;

		$page_title	= '';
		if(isset($actions['add'])){
			$page_title	= ' '.$this->get_row_action('add', ['class'=>'page-title-action']);
		}

		$subtitle	= '';
		if(method_exists($model, 'subtitle')){
			$subtitle	= $model::subtitle();
		}
		
		$subtitle 	.= (!empty($_REQUEST['s']))?' “'.$_REQUEST['s'].'”的搜索结果':'';
		$subtitle	= '<span class="subtitle">'.$subtitle.'</span>';

		$use_h1		= true;

		if($current_tab){
			global $plugin_page_setting;

			if(count($plugin_page_setting['tabs']) > 1){
				$use_h1	= false;
			}
		}
		
		if($use_h1){
			echo '<h1 class="wp-heading-inline">'.$this->_args['title'].'</h1>';
			echo $page_title;
			echo $subtitle;
		}else{
			echo '<h2>'.$this->_args['title'].$page_title.$subtitle.'</h2>';
		}

		echo '<hr class="wp-header-end">';
		echo '<div class="list-table-notice notice inline is-dismissible hidden"></div>';

		if(isset($this->_args['summary'])){
			echo wpautop($this->_args['summary']);
		}

		if(method_exists($model, 'before_list_page')){
			$model::before_list_page();
		}

		$this->views();
		
		echo '<form action="#" id="list_table_form" method="POST">';

		$this->search_box();
		$this->query_data_input();
		$this->display(); 

		echo '</form>';

		if(method_exists($model, 'list_page')){
			$model::list_page();
		}

		return true;
	}

	public function ajax_response(){
		$model			= $this->get_model();
		$action_type	= $_POST['list_action_type'];
		$nonce			= $_POST['_ajax_nonce'] ?? '';

		if($action_type == 'list'){
			if(!$this->verify_nonce($nonce, 'list')){
				wpjam_send_json(['errcode'=>'invalid_nonce', 'errmsg'=>'非法操作']);
			}

			if($_POST['data']){
				foreach (wp_parse_args($_POST['data']) as $key => $value) {
					$_REQUEST[$key]	= $value;
				}
			}

			$result	= $this->prepare_items();

			if(is_wp_error($result)){
				wpjam_send_json($result);
			}else{
				ob_start();
			
				$this->list_page();
				$data	= ob_get_clean();
				wpjam_send_json(['errcode'=>0, 'errmsg'=>'', 'data'=>$data, 'type'=>'list']);
			}
		}

		$list_action	= $_POST['list_action'];

		if(!$list_action) {
			wpjam_send_json(['errcode'=>'invalid_action', 'errmsg'=>'非法操作']);
		}

		$action	= $this->get_action($list_action);

		if(!$action) {
			wpjam_send_json(['errcode'=>'invalid_action', 'errmsg'=>'非法操作']);
		}

		$bulk	= $_POST['bulk'] ?? false;

		if($bulk){
			$bulk_action	= 'bulk_'.$list_action;

			if($action_type != 'form'){
				if(!$this->verify_nonce($nonce, $bulk_action)){
					wpjam_send_json(['errcode'=>'invalid_nonce', 'errmsg'=>'非法操作']);
				}
			}

			$ids	= $_POST['ids']? wp_parse_args($_POST['ids']) : [];
		}else{
			$id		= $_POST['id']??'';

			if($action_type != 'form'){
				if(!$this->verify_nonce($nonce, $list_action, $id)){
					wpjam_send_json(['errcode'=>'invalid_nonce',	'errmsg'=>'非法操作']);
				}
			}
		}

		$capability	= $action['capability'] ?? $this->_args['capability'];

		if(!current_user_can($capability)){
			wpjam_send_json(['errcode'=>'no_authority', 'errmsg'=>'无权限']);
		}
		
		$response_type	= $action['response'] ?? $list_action;
		$submit_text	= $action['submit_text']??$action['title'];

		if($action_type == 'submit'){
			$page_title	= $submit_text;
		}else{
			$page_title	= $action['page_title'] ?? $action['title'].$this->_args['title'];
		}

		if($action_type == 'form'){
			$form	= $this->ajax_form($list_action, compact('submit_text', 'response_type', 'page_title'));
			wpjam_send_json(['page_title'=>$page_title, 'form'=>$form, 'type'=>$response_type]);
		}elseif($action_type == 'direct'){
			if($bulk){
				$result	= $this->list_action($list_action, $ids); 
			}else{
				if(in_array($list_action, ['move', 'up', 'down'])){
					$data	= $_POST['data']? wp_parse_args($_POST['data']) : [];
					$result	= $this->list_action('move', $id, $data);
				}else{
					$result	= $this->list_action($list_action, $id);
					
					if($list_action == 'duplicate'){
						$id = $result;
					}
				}
			}
		}elseif($action_type == 'submit'){
			$data	= $_POST['data']? wp_parse_args($_POST['data']) : [];

			if($bulk){
				$fields	= $this->get_fields($list_action, $ids);
				$data	= wpjam_validate_fields_value($fields, $data);

				$result	= $this->list_action($list_action, $ids, $data); 
			}else{
				$fields	= $this->get_fields($list_action, $id);
				$data	= wpjam_validate_fields_value($fields, $data);
				
				if($list_action == 'add' || $list_action == 'duplicate'){
					$result	= $this->list_action('insert', 0, $data);
				}elseif($list_action == 'edit'){
					$result	= $this->list_action('update', $id, $data);
				}else{
					$result	= $this->list_action($list_action, $id, $data);
				}
			}
		}

		if(is_wp_error($result)){
			wpjam_send_json($result);
		}

		if($response_type == 'append'){
			wpjam_send_json(['page_title'=>$page_title, 'data'=>$result,	'type'=>$response_type]);
		}elseif($response_type == 'list'){
			$result	= $this->prepare_items();

			if(is_wp_error($result)){
				wpjam_send_json($result);
			}else{
				ob_start();
				$this->list_page();
				$data	= ob_get_clean();
			}
		}elseif(in_array($response_type, ['delete', 'move', 'up', 'down'])){
			$data ='';
		}elseif(in_array($response_type, ['add', 'duplicate'])){
			$id = $result;

			ob_start();
			$this->single_row($id);
			$data	= ob_get_clean();

			$result	= true;
		}else{
			$update	= $action['update'] ?? true;

			if($bulk){
				if(isset($this->_args['post_type'])){
					$items	= WPJAM_Post::get_by_ids($ids);
				}elseif(isset($this->_args['taxonomy'])){
					$items	= WPJAM_Term::get_by_ids($ids);
				}else{
					$items	= $model::get_by_ids($ids);	
				}
				
				$data	= [];
				if($update){
					foreach ($items as $id => $item) {
						ob_start();
						$this->single_row($item);
						$data[$id]	= ob_get_clean();
					}
				}
			}else{
				$data	= '';
				if($update){
					ob_start();
					$this->single_row($id);
					$data	= ob_get_clean();
				}
			}
		}

		if($result && is_array($result) && !empty($result['errmsg']) && $result['errmsg'] != 'ok'){ // 有些第三方接口返回 errmsg ： ok
			$errmsg = $result['errmsg'];
		}else{
			$errmsg = $page_title.'成功';
		}

		$response	= ['errcode'=>0, 'errmsg'=>$errmsg, 'data'=>$data, 'type'=>$response_type];
		
		if($action_type == 'submit'){
			if(!in_array($response_type, ['delete','list'])){
				$response['form']	= $this->ajax_form($list_action, compact('submit_text', 'response_type', 'page_title'));
			}

			if(in_array($response_type, ['add', 'duplicate'])){
				if(isset($action['last'])){
					$response['last']	= true;	
				}
			}
		}
		
		wpjam_send_json($response);
	}

	public function list_action($list_action='', $id=0, $data=null){
		$bulk	= false;

		if(is_array($id)){
			$ids			= $id;
			$bulk			= true;
			$bulk_action	= 'bulk_'.$list_action;
		}

		$model	= $this->get_model();
		$result	= null;

		if($model){
			if($bulk){
				if(method_exists($model, $bulk_action)){
					if(is_null($data)){
						$result	= $model::$bulk_action($ids);
					}else{
						$result	= $model::$bulk_action($ids, $data);
					}

					$result	= is_null($result) ? true : $result;
				}else{
					if(method_exists($model, $list_action)){
						foreach($ids as $_id) {
							if(is_null($data)){
								$result	= $model::$list_action($_id);
							}else{
								$result	= $model::$list_action($_id, $data);
							}
							
							if(is_wp_error($result)){
								return $result;
							}
						}

						$result	= is_null($result) ? true : $result;
					}
				}
			}else{
				if(method_exists($model, $list_action)){
					if($list_action == 'insert'){
						$action_key	= 'add';
					}elseif($list_action == 'update'){
						$action_key	= 'edit';
					}else{
						$action_key	= $list_action;
					}

					$action	= $this->get_action($action_key);

					if(empty($action['overall']) && $list_action != 'insert'){
						if(is_null($data)){
							$result	= $model::$list_action($id);
						}else{
							$result	= $model::$list_action($id, $data);
						}
					}else{
						if(is_null($data)){
							$result	= $model::$list_action();
						}else{
							$result	= $model::$list_action($data);
						}
					}

					$result	= is_null($result) ? true : $result;
				}
			}
		}

		if(isset($this->_args['post_type'])){
			$post_type	= $this->get_post_type();
			$result	= apply_filters('wpjam_'.$post_type.'_posts_list_action', $result, $list_action, $id, $data);
		}elseif(isset($this->_args['taxonomy'])){
			$taxonomy	= $this->get_taxonomy();
			$result	= apply_filters('wpjam_'.$taxonomy.'_terms_list_action', $result, $list_action, $id, $data);
		}else{
			$result	= apply_filters(wpjam_get_filter_name($this->_args['singular'], 'list_action'), $result, $list_action, $id, $data);
		}

		if(is_null($result)){
			return new WP_Error('empty_list_action', '没有定义该操作');
		}else{
			return $result;
		}
	}

	public function ajax_form($list_action, $args=[]){
		$model	= $this->get_model();

		$action = $this->get_action($list_action);

		$bulk	= $_POST['bulk'] ?? false;
		if($bulk){
			$ids	= $_POST['ids']? wp_parse_args($_POST['ids']) : [];

			$fields		= $this->get_fields($list_action, $ids);
			$data		= isset($_POST['data'])? wp_parse_args($_POST['data']) : [];
			$data_attr	= $this->get_action_data_attr($action, ['type'=>'form', 'bulk'=>true, 'ids'=>$ids]);
		}else{
			$id		= $_POST['id']??'';

			$fields		= $this->get_fields($list_action, $id);
			
			if($id){
				$data	= $model::get($id);
				if(empty($data) || is_wp_error($data)){
					wpjam_send_json(['errcode'=>'invalid_id', 'errmsg'=>'非法ID']);
				}
			}else{
				$data	= [];
			}

			$defaults	= isset($_POST['data']) ? wp_parse_args($_POST['data']) : [];

			if($defaults){
				$data	= wp_parse_args($data, $defaults);
			}

			$data_attr	= $this->get_action_data_attr($action, ['type'=>'form', 'id'=>$id]);
		}	

		$output	= '';
		$output	.= '<div class="list-table-action-notice notice inline is-dismissible hidden"></div>';
		$output	.= '<form method="post" id="list_table_action_form" action="#" '.$data_attr.'>';
		$output	.= wpjam_fields($fields, ['data'=>$data, 'echo'=>false]);

		if($args['submit_text']){
			$output .= '<p class="submit"><input type="submit" name="list-table-submit" id="list-table-submit" class="button-primary large"  value="'.$args['submit_text'].'"> <span class="spinner"></span></p>';
		}

		$output	.= "</form>";

		if($args['response_type'] == 'append'){ 
			$output	.= '<div class="card response" style="display:none;"></div>'; 
		}

		return $output;
	}

	protected function bulk_actions( $which = '' ) {
		if ( is_null( $this->_actions ) ) {
			$this->_actions = $this->_args['bulk_actions'];
			$two	= '';
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) )
			return;

		echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . __( 'Select bulk action' ) . '</label>';
		echo '<select name="action' . $two . '" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n";
		echo '<option value="-1">' . __( 'Bulk Actions' ) . "</option>\n";

		foreach ( $this->_actions as $key => $title) {
			
			if($action	= $this->get_action($key)){
				$class		= 'edit' === $key ? ' class="hide-if-no-js"' : '';
				$data_attr	= $this->get_action_data_attr($action, ['bulk'=>true]);

				echo "\t" . '<option value="' . $key . '"' . $class . $data_attr .'">' . $title . "</option>\n";
			}	
		}

		echo "</select>\n";

		submit_button( __( 'Apply' ), 'action list-table-bulk-action', '', false, array( 'id' => "doaction$two" ) );
		echo "\n";
	}

	protected function get_table_classes() {
		$classes = parent::get_table_classes();

		if(empty($this->_args['fixed'])){
			$classes	= array_diff($classes, ['fixed']);
		}

		return $classes;
	}

	public function get_plural(){
		return $this->_args['plural'];
	}

	public function get_singular(){
		return $this->_args['singular'];
	}

	public function column_default($item, $column_name){
		return $item[$column_name]??'';
	}

	public function column_cb($item){
		$primary_key	= $this->_args['primary_key'];
		if($primary_key){
			if($this->user_can($item)){
				$name	= isset($item['name']) ? strip_tags($item['name']) : $item[$primary_key];
				$value	= $item[$primary_key];

				return '<label class="screen-reader-text" for="cb-select-'.esc_attr($value).'">选择'.$name.'</label>'.'<input class="list-table-cb" type="checkbox" name="ids[]" value="'.esc_attr($value).'" id="cb-select-'.esc_attr($value). '" />';
			}else{
				return '<span class="dashicons dashicons-minus"></span>';
			}
		}else{
			return '';	
		}
	}

	public function user_can($item){
		if($model = $this->get_model()){
			if(method_exists($model, 'user_can')){
				return $model::user_can($item);
			}
		}

		return true;
	}

	protected function get_default_primary_column_name(){
		if(!empty($this->_args['primary_column'])){
			return $this->_args['primary_column'];
		}

		return parent::get_default_primary_column_name();
	}

	protected function handle_row_actions($item, $column_name, $primary){
		if ( $primary !== $column_name ) {
			return '';
		}

		if(!empty($item['row_actions'])){
			return $this->row_actions($item['row_actions'], false);
		}
	}

	public function row_actions($actions, $always_visible = true){
		return parent::row_actions($actions, $always_visible);
	}

	public function get_per_page(){
		if($this->_args['per_page'] && is_numeric($this->_args['per_page'])){
			return $this->_args['per_page'];
		}

		$option	= $this->screen->get_option('per_page', 'option');
		if($option){
			$defualt	= $this->screen->get_option('per_page', 'default')?:50;
			$per_page	= $this->get_items_per_page($option, $default);

			return $per_page;
		}

		return 50;
	}

	public function get_offset(){
		return ($this->get_pagenum()-1) * $this->get_per_page();
	}

	public function get_limit(){
		return $this->get_offset().','.$this->get_per_page();
	}

	public function prepare_items(){
		$model	= $this->get_model();

		if($model){
			$model_reflection	= new ReflectionClass($model);
			$model_methods		= $model_reflection->getMethods();
			$model_methods		= wp_list_pluck($model_methods, 'class', 'name');

			// wpjam_print_R($model_methods);

			if(isset($model_methods['query_items']) && $model_methods['query_items'] == $model){
				$method	= 'query_items';
			}elseif(isset($model_methods['list']) && $model_methods['list'] == $model){
				$method	= 'list';
			}elseif(isset($model_methods['query_items']) && $model_methods['query_items'] != 'WPJAM_Model'){
				$method	= 'query_items';
			}elseif(isset($model_methods['list']) && $model_methods['list'] != 'WPJAM_Model'){
				$method	= 'list';
			}else{
				$method	= 'query_items';
			}

			// wpjam_print_R($method);

			$result = $model::$method($this->get_per_page(), $this->get_offset());

			if(is_wp_error($result)){
				return $result;
			}

			$this->items	= $result['items'] ?? [];
			$total_items	= $result['total'] ?? 0;
			if($total_items){
				$this->set_pagination_args( array(
					'total_items'	=> $total_items,
					'per_page'		=> $this->get_per_page()
				));
			}
		}else{
			$args = func_get_args();

			$this->items	= $args[0];
			$this->set_pagination_args( array(
				'total_items'	=> $args[1],
				'per_page'		=> $this->get_per_page()
			));
		}

		return true;
	}

	public function get_columns(){
		return $this->_args['columns'];
	}

	public function get_sortable_columns(){
		return $this->_args['sortable_columns']??[];
	}

	public function get_views(){
		if($model = $this->get_model()){
			if(method_exists($model, 'views')){
				return $model::views();
			}
		}else{
			if(!empty($this->_args['views'])){
				return call_user_func($this->_args['views'],[]);
			}
		}

		return [];
	}

	public function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field($this->get_nonce_action('list'));
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which );?>">
			<?php if (!empty($this->_args['bulk_actions']) && $this->has_items() ){ ?>
			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
			<?php } ?>

			<?php $this->extra_tablenav( $which ); ?>

			<?php $this->pagination( $which ); ?>

			<br class="clear" />
		</div>
	<?php
	}

	public function extra_tablenav($which='top') {
		$model 		= $this->get_model();

		if($model && method_exists($model, 'extra_tablenav')){
			$model::extra_tablenav($which);
		}

		if($which == 'top'){
			$actions	= $this->_args['actions'];

			if($actions){
				$overall_actions = '';

				foreach ($actions as $action_key => $action) {
					if(!empty($action['overall'])){
						$action['key']		= $action_key;
						$overall_actions	.= $this->get_row_action($action, ['class'=>'button-primary button']);
					}
				}

				if($overall_actions){
					echo '<div class="alignleft actions overallactions">'.$overall_actions.'</div>';
				}
			}
		}

		do_action(wpjam_get_filter_name($this->_args['plural'], 'extra_tablenav'), $which);	
	}

	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_REQUEST['orderby'] ) ) {
			$current_orderby = $_REQUEST['orderby'];
		} else {
			$current_orderby = '';
		}

		if ( isset( $_REQUEST['order'] ) && 'desc' === $_REQUEST['order'] ) {
			$current_order = 'desc';
		} else {
			$current_order = 'asc';
		}

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			if ( in_array( $column_key, $hidden ) ) {
				$class[] = 'hidden';
			}

			if ( 'cb' === $column_key )
				$class[] = 'check-column';

			if ( $column_key === $primary ) {
				$class[] = 'column-primary';
			}

			$data_attr	= '';

			if ( isset( $sortable[$column_key] ) ) {
				list( $orderby, $desc_first ) = $sortable[$column_key];

				if ( $current_orderby === $orderby ) {
					$order = 'asc' === $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$class[] = 'list-table-sort';

				if($this->get_model()){
					$column_display_name = '<a href="javascript:;"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
				}else{
					$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
				}

				$data_attr	= 'data-orderby="'.$orderby.'" data-order="'.$order.'"'; 
			}

			$tag = ( 'cb' === $column_key ) ? 'td' : 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id = $with_id ? "id='$column_key'" : '';

			if ( !empty( $class ) )
				$class = "class='" . join( ' ', $class ) . "'";

			echo "<$tag $scope $id $class $data_attr>$column_display_name</$tag>";
		}
	}

	public function query_data_input(){
		if($query_data = $this->_args['query_data']){ $data	= wpjam_json_encode($query_data); ?>
			<input type="hidden" id="wpjam_query_data" name="wpjam_query_data" value='<?php echo $data; ?>' />
		<?php }
	}

	public function search_box($text='搜索', $input_id='wpjam') {
		if($this->is_searchable()){

			$input_id = $input_id . '-search-input';
			?>
			<p class="search-box">
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
				<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
				<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
			</p>
			<?php
		}
	}

	public function is_searchable(){
		if(empty($_REQUEST['s']) && (!$this->has_items() || $this->_pagination_args['total_pages'] <= 1)){
			return false;
		}

		if(isset($this->_args['search'])){
			return $this->_args['search'];
		}elseif($model = $this->get_model()){
			return method_exists($model, 'get_searchable_fields') && $model::get_searchable_fields();
		}else{
			return false;
		}
	}

	public function get_current_action_js_args(){
		$current_action	= $this->current_action();

		if(empty($current_action)){
			return false;
		}
		
		$action	= $this->get_action($current_action);

		if(empty($action) || !empty($action['direct'])){
			return false;
		}

		if($query_data = $this->_args['query_data']){
			$data	= http_build_query($query_data);
		}else{
			$data	= '';
		}
		
		if($current_action =='add'){
			return	['list_action_type'=>'form', 'list_action'=>$current_action, 'data'=>$data ?: null];
		}else{
			if(empty($_GET['id'])){
				return	['list_action_type'=>'form', 'list_action'=>$current_action, 'data'=>$data ?: null];
			}else{
				return	['list_action_type'=>'form', 'list_action'=>$current_action, 'id'=>$_GET['id'], 'data'=>$data ?: null];
			}
		}
	}

	public function _js_vars() {
		if($this->_args['sortable'] === true){
			$sortable_items	= ' >tr';
		}elseif($this->_args['sortable']){
			$sortable_items	= $this->_args['sortable']['items'];
		}

		$args	= $this->get_current_action_js_args();

		?>

		<script type="text/javascript">
		jQuery(function($){
			<?php if($this->_args['sortable']){ echo "$.wpjam_list_table_sortable('".$sortable_items."');"; } ?>

			<?php if($args){ echo "$.wpjam_list_table_action(".wpjam_json_encode($args).");"; } ?>

		});
		</script>

		<?php
	}

	public function get_row_action_compat($action, $args=[]){
		extract(wp_parse_args($args, [
			'id'		=> 0,
			'data'		=> [],
			'class'		=> '',
			'style'		=> '',
			'title'		=> '',
			'tag'		=> 'a'
		]));

		$capability	= $action['capability'] ?? $this->_args['capability'];

		if(!current_user_can($capability)) {
			return '';
		}

		$key		= $action['key'];
		$title		= $title?:$action['title'];
		$class		= $class?' '.$class:'';
		$page_title	= $action['page_title'] ?? ($action['title'].$this->_args['title']);

		
		global $current_admin_url;

		$action_url		= $current_admin_url.'&action='.$key;

		if($id){
			$primary_key	= $this->_args['primary_key'];
			$action_url		.= '&'.$primary_key.'='.$id;
		}

		$onclick	= '';

		if(!empty($action['direct']) || !empty($action['overall'])){
			$action_url = esc_url(wp_nonce_url($action_url, $this->get_nonce_action($key, $id)));
			if($key == 'delete'){
				$onclick = ' onclick="return confirm(\'你确定要删除？\');"';
			}
		}else{
			$action_url	.= '&TB_iframe=true&width=780&height=320';
			$class		= 'thickbox'.$class;
		}

		return '<a href="'.$action_url.'" title="'.$page_title.'" class="'.$class.'" '.$onclick.'>'.$title.'</a>';
		
	}

	public function get_fields_compat($key='', $id=0){
		$fields	= $this->_args['fields'];

		$primary_key	= $this->_args['primary_key'];
		if($key != 'add' && isset($fields[$primary_key])){
			$fields[$primary_key]['type']	= 'view';
		}
	
		return $fields;
	}

	public function parse_item_compact($item){
		if(!empty($this->_args['item_callback'])){
			$item = call_user_func($this->_args['item_callback'], $item);
		}

		$options_columns	= $this->_args['options_columns'];

		if(!empty($options_columns)){
			foreach ($options_columns as $field_key => $options) {
				if(isset($item[$field_key])){
					if($this->_args['fields'] && $this->_args['flat_fields'][$field_key]['type'] == 'checkbox' && $item[$field_key]){
						$item[$field_key]	= wp_array_slice_assoc($options, $item[$field_key]);
						$item[$field_key]	= implode(',', $item[$field_key]);
					}else{
						$item[$field_key]	= $options[$item[$field_key]]??$item[$field_key];
					}
				}
			}
		}

		return $item;
	}

	public function display_compat(){
		global $current_admin_url;

		echo '<div class="list-table">';
		echo '<form action="'. admin_url('admin.php').'" method="get">';

		$_SERVER['REQUEST_URI']	= remove_query_arg(['_wp_http_referer'], $_SERVER['REQUEST_URI']);

		foreach(wp_parse_args(parse_url($current_admin_url, PHP_URL_QUERY)) as $hidden_field => $hidden_value){
			echo '<input type="hidden" name="'.$hidden_field.'" value="' . $hidden_value .'">';
		}
		
		// $this->search_box('搜索', $this->_args['singular']);
		$this->search_box();
		$this->views();
		parent::display();

		echo '</form>';
		echo '</div>';
	}

	public function get_postfix($list=false){
		if($list){
			return str_replace('-', '_', $this->_args['plural']);
		}else{
			return str_replace('-', '_', $this->_args['singular']);
		}
	}
}