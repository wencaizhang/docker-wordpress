<?php
include WPJAM_CONFIGURATOR_PLUGIN_DIR.'admin/trait.php';

add_filter('wpjam_apis_list_table', function(){
	return [
		'title'			=> '接口',
		'singular' 		=> 'api',
		'plural' 		=> 'apis',
		'primary_column'=> 'title',
		'primary_key'	=> 'json',
		'model'			=> 'WPJAM_APISetting',
		'ajax'			=> true,
		'sortable'		=> true
	];
});

add_action('admin_head', function(){
	?>
	<style type="text/css">
	td.column-modules table td{
		padding:0 10px 4px 0;
	}
	</style>

	<script type="text/javascript">
	jQuery(function($){
		$('body').on('list_table_action_success', function(response){
			$('.mu-fields').sortable({
				handle: '.dashicons-menu'
			});
		});

		$('.wp-list-table tbody').sortable({items: '> tr.source-configurator'});
	});
	</script>
	<?php
});

Class WPJAM_APISetting extends WPJAM_Model{
	use WPJAM_Configurator;

	public static function add_module($json, $item){
		$api	= self::get($json);
		if(empty($api)){
			return new WP_Error('invalid_json', '非法接口');
		}

		$item_handler	= new WPJAM_APIField($json);

		return $item_handler->insert($item);
	}

	public static function edit_module($json, $item){
		$api	= self::get($json);
		if(empty($api)){
			return new WP_Error('invalid_json', '非法接口');
		}

		$item_handler	= new WPJAM_APIField($json);

		return $item_handler->update($item['i'], $item);
	}

	public static function delete_module($json){
		$api	= self::get($json);
		if(empty($api)){
			return new WP_Error('invalid_json', '非法接口');
		}

		$i		= wpjam_get_data_parameter('i') ?: 0;

		$item_handler	= new WPJAM_APIField($json);

		return $item_handler->delete($i);
	}

	public static function insert($data){
		$data['json']	= str_replace('mag.', '', $data['json']);
		return parent::insert($data);
	}

	public static function get($json){
		if($api	= parent::get($json)){
			$api['source']	= 'configurator';
			$item['class']	= 'source-configurator';
		}
		
		return $api;
	}

	public static function query_items($limit, $offset){
		$items	= [];

		$apis	= wpjam_get_option('wpjam_apis');

		foreach ($apis as $json => $api) {
			$items[$json]	= $api;
			$items[$json]['source']	= 'configurator';
		}

		global $wpjam_apis;

		if($wpjam_apis){

			foreach ($wpjam_apis as $json => $api) {
				$items[$json]	= [
					'source'	=> 'code',
					'json'		=> $json,
					'title'		=> $api['title'] ?? '',
					'modules'	=> $api['modules'] ?? '',
				];
			}
		}

		$total	= count($items);

		return compact('items', 'total');
	}

	public static function item_callback($item){
		$json	= $item['json'];
		$item['api']	= '<a href="'.home_url('/api/mag.'.$json.'.json').'" target="_blank">'.'mag.'.$json.'</a>';
		$item['class']	= 'source-'.$item['source'];

		if($item['source'] == 'configurator'){
			unset($item['row_actions']['edit_module']);
			unset($item['row_actions']['delete_module']);
		}else{
			$item['api']	= wpautop($item['api']);
			unset($item['row_actions']);
		}

		$modules	= '';

		if($item['modules']){
			$module_type_options	= self::get_module_type_options();

			$modules	= '<table>';
			$modules	.= '<tbody>';
			foreach ($item['modules'] as $i=>$module) {

				if(empty($module['type'])){
					continue;
				}

				$modules	.= '<tr>';

				$modules	.= '<td class="module-type">'.$module_type_options[$module['type']].'</td>';

				if(isset($item['row_actions'])){
					$modules	.= '<td><div class="row-actions">';

					$modules	.= wpjam_get_list_table_row_action('edit_module',[
						'id'	=> $json,
						'data'	=> ['i'=>$i],
						'title'	=>'修改',
					]);

					$modules	.= ' | ';

					$modules	.= '<span class="delete">'.wpjam_get_list_table_row_action('delete_module',[
						'id'	=> $json,
						'data'	=> ['i'=>$i],
						'title'	=>'删除',
					]).'</span>';

					$modules	.= '</div></td>';
				}

				$modules	.= '</tr>';
			}

			$modules	.= '</tbody>';
			$modules	.= '</table>';
		}

		$item['modules']	= $modules;

		return $item;
	}

	public static function user_can($item){
		return in_array($item['source'], ['configurator', '配置器生成']);
	}

	public static function get_actions(){
		return [
			'add'			=> ['title'=>'新建'],
			'edit'			=> ['title'=>'编辑'],
			'duplicate'		=> ['title'=>'复制'],
			'add_module'	=> ['title'=>'添加模块',	'page_title'=>'添加模块'],
			'edit_module'	=> ['title'=>'编辑模块',	'page_title'=>'编辑模块'],
			'delete_module'	=> ['title'=>'删除模块',	'page_title'=>'编辑模块',		'direct'=>true, 'confirm'=>true],
			'delete'		=> ['title'=>'删除',		'direct'=>true, 'confirm'=>true,	'bulk'=>true]
		];
	}

	public static function get_module_type_options(){
		return ['post_type'=>'文章','taxonomy'=>'分类','setting'=>'设置','other'=>'其他'];
	}

	public static function get_fields($action_key='', $json=''){
		if($action_key == 'add_module'){
			return [
				'type'	=> ['title'=>'类型',	'type'=>'radio',	'options'=>self::get_module_type_options()],
				'args'	=> ['title'=>'参数',	'type'=>'textarea',	'description'=>self::get_api_module_usage_description()],
			];
		}elseif($action_key == 'edit_module'){
			$i		= wpjam_get_data_parameter('i') ?: 0;
			$api	= self::get($json);

			if(empty($api)){
				return new WP_Error('invalid_json', '非法 Meta Box');
			}

			if(isset($api['modules'])){
				$module	= $api['modules'][$i] ?? [];
			}else{
				$module	= [];
			}

			if(empty($module)){
				return new WP_Error('invalid_module_index', '非法字段');
			}

			$type	= $module['key'] ?? '';
			$args	= $module['args'] ?? '';

			return [
				'i'			=> ['title'=>'名称',	'type'=>'hidden',	'value'=>$i],
				'type'		=> ['title'=>'类型',	'type'=>'radio',	'value'=>$type,	'options'=>self::get_module_type_options()],
				'args'		=> ['title'=>'参数',	'type'=>'textarea',	'value'=>$args,	'description'=>self::get_api_module_usage_description()],
			];
		}else{
			$json = $json ? 'mag.'.$json : '';
			
			return [
				'title'		=> ['title'=>'名称',	'type'=>'text',		'show_admin_column'=>true,	'required'],
				'json'		=> ['title'=>'接口',	'type'=>'text', 	'show_admin_column'=>false,	'required',	'value'=>$json],
				'modules'	=> ['title'=>'模块',	'type'=>'view', 	'show_admin_column'=>'only'],
				'api'		=> ['title'=>'接口',	'type'=>'text', 	'show_admin_column'=>'only',	'required'],
				'auth'		=> ['title'=>'权限',	'type'=>'radio', 	'show_admin_column'=>false,	'options'=>[0=>'无需登录',1=>'需要登录']],
				'source'	=> self::get_source_field(),
			];
		}
	}

	private static $handler;

	public static function get_handler(){
		global $wpdb;
		if(is_null(static::$handler)){
			static::$handler = new WPJAM_Option('wpjam_apis', 'json');
		}
		return static::$handler;
	}
}

class WPJAM_APIField extends WPJAM_Item{
	private $json;
	public function __construct($json){
		$this->json	= $json;
		parent::__construct([
			'primary_key'	=> 'id',
			'primary_title'	=> 'ID'
		]);
	}

	public function get_items(){
		$api	= wpjam_get_setting('wpjam_apis', $this->json);

		if(isset($api)){
			$items	= $api['modules'] ?? [];

			return $this->parse_items($items);
		}
	}

	public function update_items($items){
		$api	= wpjam_get_setting('wpjam_apis', $this->json);

		if(isset($api)){
			$api['modules']	= $items;
			return wpjam_update_setting('wpjam_apis', $this->json, $api);
		}
	}
}