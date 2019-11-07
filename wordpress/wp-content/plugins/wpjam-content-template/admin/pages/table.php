<?php
include WPJAM_CONTENT_TEMPLATE_PLUGIN_DIR .'admin/posts/template-type.php';

add_filter('wpjam_table_tabs', function(){
	$post_id	= wpjam_get_data_parameter('post_id');
	$tabs		= [
		'table'		=> ['title'=>'表格设置',	'function'=>'wpjam_table_setting_page'],
		'content'	=> ['title'=>'表格内容',	'function'=>'wpjam_table_no_fields_page'],
		'bulk'		=> ['title'=>'批量编辑',	'function'=>'wpjam_table_bulk_edit_page']
	];

	if($post_id && get_post_meta($post_id, '_table_fields', true)){
		$tabs['content']	= ['title'=>'表格内容',	'function'=>'list',	'list_table_name'=>'table_content'];
	}

	return $tabs;
});

function wpjam_table_setting_page(){
	$post_id		= wpjam_get_data_parameter('post_id') ?: 0;

	if($post_id){
		$post			= get_post($post_id);
		$post_title		= $post->post_title;
		$post_excerpt	= $post->post_excerpt;
		$post_password	= $post->post_password;
		$table_fields	= get_post_meta($post_id, '_table_fields', true);
	}else{
		$post_title		= $post_excerpt = $post_password = '';
		$table_fields	= [];
	}

	$type_options	= [
		'text'		=> '输入框',
		'textarea'	=> '文本框',
		'number'	=> '数字输入框',
		'url'		=> '链接输入框',
		'email'		=> '邮件输入框',
		'date'		=> '日期选择框',
		'time'		=> '时间选择框',
		'select'	=> '下拉选择框',
		// 'id'		=> '中国大陆身份证号',
		// 'tel'	=> '中国大陆手机号码',
		'img'		=> '上传图片',
	];

	$fields = [
		'post_title'	=> ['title'=>'标题',		'type'=>'text'],
		'shortcode'		=> ['title'=>'短代码',	'type'=>'view',		'value'=>'[template id="'.$post_id.'"]'],
		'post_excerpt'	=> ['title'=>'简介',		'type'=>'textarea',	'value'=>$post_excerpt,	'class'=>''],
		'post_password'	=> ['title'=>'密码',		'type'=>'text',		'value'=>$post_password,'class'=>'',	'description'=>'设置了密码保护，则前端必须输入密码才可查看'],
		'table_fields'	=> ['title'=>'字段',		'type'=>'mu-fields',	'value'=>$table_fields,	'fields'=>[
			'title'		=> ['title'=>'',	'type'=>'text',		'class'=>'',	'placeholder'=>'请输入字段名称'],
			'type'		=> ['title'=>'',	'type'=>'select',	'options'=>$type_options],
			'options'	=> ['title'=>'',	'type'=>'mu-text',	'class'=>'',	'placeholder'=>'请输入选项...'],
			'url_for'	=> ['title'=>'',	'type'=>'text',		'class'=>'',	'placeholder'=>'链接字段应用于...'],
			// 'required'	=> ['title'=>'',	'type'=>'checkbox',	'description'=>'必填'],
			'index'		=> ['title'=>'',	'type'=>'hidden'],
		]],
		'post_id'		=> ['title'=>'',		'type'=>'hidden',	'value'=>$post_id],
	];

	if(empty($post_id)){
		unset($fields['shortcode']);
	}

	$data			= compact('post_title', 'post_excerpt', 'post_password', 'table_fields',	'post_id');
	$submit_text	= $post_id ? '编辑' : '新建';

	echo $post_id ? '<h2>表格设置</h2>' : '<h1 class="wp-heading-inline">新建表格</h1>';

	wpjam_ajax_form([
		'fields'		=> $fields, 
		'data'			=> $data, 
		'submit_text'	=> $submit_text,
		'action'		=> 'save',
	]);	
}

function wpjam_table_ajax_response(){
	global $plugin_page; 

	$action	= $_POST['page_action'];

	check_ajax_referer($plugin_page.'-'.$action);

	$post_id	= wpjam_get_data_parameter('post_id');
	$data		= wp_parse_args($_POST['data']);

	if($action == 'save'){
		$post_title		= $data['post_title'] ?? '';
		$post_excerpt	= $data['post_excerpt'] ?? '';
		$post_password	= $data['post_password'] ?? '';

		$table_fields	= $data['table_fields'] ?? [];

		$post_status	= 'publish';

		if($table_fields){
			$indexs	= wp_list_pluck($table_fields, 'index');
			$index	= max($indexs);

			foreach ($table_fields as $key=> &$table_field) {
				if(empty($table_field['title'])){
					unset($table_fields[$key]);
					continue;
				}

				if($table_field['type'] == 'select'){
					$table_field['options']	= array_filter($table_field['options']);
				}else{
					unset($table_field['options']);

					if($table_field['type'] != 'url'){
						unset($table_field['url_for']);
					}
				}

				if(empty($table_field['index'])){
					$index++;
					$table_field['index']	= $index;
				}
			}

			if($table_fields){
				$table_fields	= array_values($table_fields);
			}
		}

		$table_fields	= $table_fields ?: [];

		$meta_input		= [
			'_template_type'	=>'table',
			'_table_fields'		=>$table_fields
		];

		if($post_id){
			$post_id		= WPJAM_Post::update($post_id, compact('post_title', 'post_excerpt', 'post_status', 'post_password', 'meta_input'));
			$is_add			= false;
		}else{
			$post_type		= 'template';
			$post_id		= WPJAM_Post::insert(compact('post_type','post_title', 'post_excerpt', 'post_status', 'post_password', 'meta_input'));
			$is_add			= true;
		}

		if(is_wp_error($post_id)){
			wpjam_send_json($post_id);
		}else{
			wpjam_send_json(compact('post_id', 'is_add'));
		}
	}elseif($action == 'bulk_edit'){

		$table_fields	= get_post_meta($post_id, '_table_fields', true);
		foreach ($table_fields as $table_field) {
			$field_indexs[]	= 'i'.$table_field['index'];		
		}

		$table_content	= trim($data['table_content']);

		if($table_content){
			$table_content	= str_replace("\r\n", "\n", $table_content);
			$table_content	= str_replace("\r\n", "\n", $table_content);

			$items	= [];
			$trs	= explode("\n\n", $table_content);

			$index	= 0; 
			foreach($trs as $tr){
				$index++;
				$tds	= explode("\n", $tr);
				$item	= [];
				foreach ($field_indexs as $i => $field_index) {
					$td	= $tds[$i] ?? '';
					$item[$field_index]	= trim($td);
				}

				$items[$index]	= $item;
			}

			$post_content	= maybe_serialize($items);
			$result			= WPJAM_Post::update($post_id, compact('post_content'));

			if(is_wp_error($result)){
				wpjam_send_json($result);
			}else{
				wpjam_send_json();
			}	
		}
	}
}

function wpjam_table_bulk_edit_page(){
	echo '<h2>批量编辑</h2>';

	$post_id	= wpjam_get_data_parameter('post_id');

	if($post_id){
		$table_fields	= get_post_meta($post_id, '_table_fields', true);
	}else{
		$table_fields	= [];
	}

	if(empty($table_fields)){
		echo '<p>请先在「表格设置」中添加字段。</p>';
		return;
	}

	foreach ($table_fields as $table_field) {
		if($table_field['type'] == 'textarea'){
			echo '<p>含有「富文本」类型的字段，不能批量编辑。</p>';
			return;
		}elseif($table_field['type'] == 'img'){
			echo '<p>含有「图片」类型的字段，不能批量编辑。</p>';
			return;
		}
	}

	echo '
	<p>批量编辑极其容易造成数据丢失和紊乱，批量编辑前请先做好备份。批量编辑规则：</p>
	<p>* 连续两个回车当做：<strong>一行</strong>。
	<br />* 单独一个回车当做：<strong>单元格</strong>。</p>
	';

	$post_content	= get_post($post_id)->post_content;
	$table_content	= $post_content ? maybe_unserialize($post_content) : [];

	$value	= '';

	if($table_content){
		$field_indexs	= [];

		foreach ($table_fields as $table_field) {
			$field_indexs[]	= 'i'.$table_field['index'];		
		}

		foreach ($table_content as $table_row) {
			foreach ($field_indexs as $field_index) {
				$v	= $table_row[$field_index] ?: ' ';
				$value	.= $v."\n";
			}
			$value	.="\n";
		}
	}

	$fields	= [
		'table_content'	=> ['title'=>'',	'type'=>'textarea',	'value'=>$value,	'rows'=>20,	'class'=>'large-text'],
		'post_id'		=> ['title'=>'',	'type'=>'hidden',	'value'=>$post_id],
	];

	wpjam_ajax_form([
		'fields'		=> $fields, 
		'action'		=> 'bulk_edit',
		'submit_text'	=> '批量编辑'
	]);
}

function wpjam_table_no_fields_page(){
	echo '<h2>表格内容</h2>';

	echo '<p>请先在「表格设置」中添加字段。</p>';
}

add_filter('wpjam_table_content_list_table', function(){
	return [
		'title'			=>'表格内容',
		'plural'		=>'table-contents',
		'singular'		=>'table-content',
		'model'			=>'WPJAM_TableContent',
		'capability'	=>'edit_others_posts',
		'fixed'			=>false,
		'ajax'			=>true,
		'sortable'		=>true
	];
});

class WPJAM_TableContent extends WPJAM_Model{
	private static $handler;
	protected static $limit = 8;

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_PostContent();
		}
		return static::$handler;
	}

	public static function item_callback($item){
		$items	= self::get_all();

		$ids	= array_keys($items);
		$max	= count($ids);
		$i		= array_search($item['id'], $ids);

		$post_id		= wpjam_get_data_parameter('post_id');
		$table_fields	= get_post_meta($post_id, '_table_fields', true);

		$table_fields	= wpjam_parse_content_template_table_fields($table_fields);
		
		foreach($table_fields as $table_field){
			$field_type		= $table_field['type'];
			$field_index	= 'i'.$table_field['index'];

			if($field_type == 'img'){
				if(!empty($item[$field_index])){
					$item[$field_index]	= wpjam_get_thumbnail($item[$field_index], '200x200');
					$item[$field_index]	= '<img src="'.$item[$field_index].'" width="100" height="100" />';
				}
			}elseif($field_type == 'textarea'){
				$item[$field_index]	= wpautop(do_shortcode($item[$field_index]));
			}

			if(isset($table_field['url'])){
				$url_index	= 'i'.$table_field['url']['index'];
				$url		= $item[$url_index] ?? '';

				$item[$field_index]	= '<a href="'.$url.'" target="_blank">'.$item[$field_index].'</a>';
			}
		}
		
		return $item;
	}

	public static function get_actions(){
		$post_id	= wpjam_get_data_parameter('post_id');
		if(get_post_meta($post_id, '_table_fields', true)){
			return  [
				'add'	=> ['title'=>'新建',	'last'=>true],
				'edit'	=> ['title'=>'编辑'],
				// 'duplicate'	=> ['title'=>'复制',	'response'=>'list'],
				'delete'=> ['title'=>'删除',	'direct'=>true, 'confirm'=>true,	'bulk'=>true],
			];
		}else{
			return [];
		}
	}

	public static function get_fields($action_key='', $id=0){
		$post_id		= wpjam_get_data_parameter('post_id');
		$table_fields	= get_post_meta($post_id, '_table_fields', true);

		$fields	= [];

		if($table_fields){
			foreach($table_fields as $table_field){
				$field_type		= $table_field['type'];
				$field_index	= 'i'.$table_field['index'];
				$field			= ['title'=>$table_field['title'],	'type'=>$table_field['type'],	'show_admin_column'=>true];

				if($table_field['type'] == 'select'){
					$field_options		= array_merge([''], $table_field['options']);
					$field['options']	= array_combine($field_options, $field_options);
				}elseif($table_field['type'] == 'img'){
					$field['item_type']	= 'url'; 
					$field['size']		= '200x200'; 
				}elseif($table_field['type'] == 'url'){
					if(!empty($table_field['url_for'])){
						$field['show_admin_column']	= false;
					}
				}

				$fields[$field_index]	= $field;
			}
		}

		return $fields;
	}
}

add_action('admin_head', function(){
	$post_id	= wpjam_get_data_parameter('post_id');
	?>
	<style type="text/css">

	table.form-table th{width: 120px;}

	div.mu-fields div.mu-item div.sub-field{
		vertical-align: top;
	}
	.sub-field_title, 
	.sub-field_type, 
	.sub-field_required,
	.sub-field_url_for,
	.sub-field_options{
		display: inline-block;
		margin-right: 10px !important;
	}

	.sub-field_options{display: none;}

	div.mu-fields > div.mu-item > a{
		margin: 6px 0 6px 20px;
	}

	div.mu-item span.dashicons{
		margin: 6px 0;
	}
	</style>
	<script type="text/javascript">
	jQuery(function($){
		$('body').on('change', '.sub-field_type select', function(){
			var i = $(this).data('i');
			if($(this).val() == 'select'){
				$('#sub_field_options_'+i).fadeIn().css('display', 'inline-block');
			}else{
				$('#sub_field_options_'+i).hide();
			}

			if($(this).val() == 'url'){
				$('#sub_field_url_for_'+i).fadeIn().css('display', 'inline-block');
			}else{
				$('#sub_field_url_for_'+i).hide();
			}
		});

		$('body').on('mu_fields_added', '#mu_fields_table_fields', function(event, i){
			$('#sub_field_url_for_'+i).hide();
		});

		$('.sub-field_type select').change();
	});
	</script>
	<?php
});