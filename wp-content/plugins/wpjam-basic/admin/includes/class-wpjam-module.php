<?php
class WPJAM_ModuleSlider extends WPJAM_Model {
	private static $handler;

	private static $option_name	= 'module_sliders';
	private static $size		= [1200,640];

	public static function init($option_name, $size=[]){
		static::$option_name	= $option_name;
		static::$size 			= $size ?: [1200,640];
	}

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler	= new WPJAM_Option(static::$option_name);
		}
		return static::$handler;
	}

	public static function get($id){
		$data	= parent::get($id);

		$data['weapp_page']	= $data['weapp_page']??'';

		if(!empty($data['post_id'])){	// 兼容代码
			$data['product_id']	= $data['article_id']	= $data['post_id'];
		}

		$data['active']	=  $data['active'] ?? 1;

		return $data; 
	}

	public static function insert($data){
		$data	= self::validate($data);

		if(is_wp_error($data)){
			return $data;
		}

		if(count(self::get_all()) >= 10){
			return new WP_Error('too_much', '最多允许添加10个幻灯片');
		}

		return parent::insert($data);
	}

	public static function update($id, $data){
		$data	= self::validate($data);

		if(is_wp_error($data)){
			return $data;
		}

		return parent::update($id, $data);
	}

	public static function validate($data){
		if(empty($data['image'])){
			return new WP_Error('empty_image', '图片不能为空');
		}

		if(class_exists('WPJAM_WeappPage')){
			$data	= WPJAM_WeappPage::validate_data($data);	
		}

		return $data;
	}

	public static function up($key){
		$i	= str_replace('option_key_', '', $key); 

		if($i == 1){
			return new WP_Error('unable_up', '第一个不能向上移动');
		}
		
		$swap_key	= 'option_key_'.($i-1);

		return self::swap($key, $swap_key);
	}

	public static function down($key){
		$i	= str_replace('option_key_', '', $key); 

		if($i == count(self::get_all())){
			return new WP_Error('unable_down', '最后一个不能向下移动');
		}

		$swap_key	= 'option_key_'.($i+1);

		return self::swap($key, $swap_key);
	}

	public static function item_callback($item){
		if($item['option_key'] == 'option_key_1'){
			unset($item['row_actions']['up']);
		}elseif($item['option_key'] == 'option_key_'.count(self::get_all())){
			unset($item['row_actions']['down']);
		}

		if(class_exists('WPJAM_WeappPage')){
			$item	= WPJAM_WeappPage::parse_item($item);	
		}

		$image_width	= static::$size[0];
		$image_height	= static::$size[1];

		$style_height	= intval($image_height / $image_width * 300);

		$item['image']	= $item['image']?'<img src="'.wpjam_get_thumbnail($item['image'], $image_width.'x'.$image_height).'" style="width:300px; height:'.$style_height.'px" />':'';

		$item['active']	= $item['active']??1;
		$item['active']	= $item['active']?'显示':'不显示';
		
		return $item;
	}

	public static function get_actions(){
		return  [
			'add'	=> ['title'=>'新增',	'response'=>'list'],
			'edit'	=> ['title'=>'编辑'],
			// 'duplicate'	=> ['title'=>'复制',	'response'=>'list'],
			'up'	=> ['title'=>'<span class="dashicons dashicons-arrow-up-alt"></span>',	'page_title'=>'向上移动',	'direct'=>true,	'response'=>'list'],
			'down'	=> ['title'=>'<span class="dashicons dashicons-arrow-down-alt"></span>','page_title'=>'向下移动',	'direct'=>true,	'response'=>'list'],
			'delete'=> ['title'=>'删除',	'response'=>'list',	'direct'=>true, 'confirm'=>true],
		];
	}

	public static function get_fields($action_key='', $id=0){
		$fields	= apply_filters('wpjam_modules_fields', []);

		$image_width	= static::$size[0];
		$image_height	= static::$size[1];

		return array_merge(
			[
				'image'		=> ['title'=>'图片',	'type'=>'img',	'item_type'=>'url',	'show_admin_column'=>true,	'size'=>$image_width.'x'.$image_height, 'description'=>'规格：'.$image_width.'x'.$image_height],
			],
			$fields,
			[
				'path'		=> ['title'=>'路径',	'type'=>'view',		'show_admin_column'=>'only'],
				'title'		=> ['title'=>'标题',	'type'=>'text',		'show_admin_column'=>true],
				'active'	=> ['title'=>'显示',	'type'=>'checkbox',	'show_admin_column'=>true,	'value'=>1,	'description'=>'在小程序首页显示']
			]
		); 
	}
}

class WPJAM_ModuleLink extends WPJAM_Model {
	private static $handler;
	private static $option_name		= 'module_links';
	private static $icon_size		= [160,160];

	public static function init($option_name,  $icon_size=[]){
		static::$option_name	= $option_name;
		static::$icon_size		= $icon_size ?: [160,160];
	}

	public static function get_handler(){
		if(is_null(static::$handler)){

			static::$handler	= new WPJAM_Option(static::$option_name);
		}
		return static::$handler;
	}

	public static function insert($data){
		$data	= self::validate($data);

		if(is_wp_error($data)){
			return $data;
		}

		if(count(self::get_all()) >= 12){
			return new WP_Error('too_much', '最多允许添加12个快捷导航');
		}

		return parent::insert($data);
	}

	public static function update($id, $data){
		$data	= self::validate($data);

		if(is_wp_error($data)){
			return $data;
		}

		return parent::update($id, $data);
	}

	public static function validate($data){
		if(class_exists('WPJAM_WeappPage')){
			$data	= WPJAM_WeappPage::validate_data($data);	
		}

		return $data;
	}

	public static function up($key){
		$i	= str_replace('option_key_', '', $key); 

		if($i == 1){
			return new WP_Error('unable_up', '第一个不能向上移动');
		}
		
		$swap_key	= 'option_key_'.($i-1);

		return self::swap($key, $swap_key);
	}

	public static function down($key){
		$i	= str_replace('option_key_', '', $key); 

		if($i == count(self::get_all())){
			return new WP_Error('unable_down', '最后一个不能向下移动');
		}

		$swap_key	= 'option_key_'.($i+1);

		return self::swap($key, $swap_key);
	}

	public static function item_callback($item){
		if($item['option_key'] == 'option_key_1'){
			unset($item['row_actions']['up']);
		}elseif($item['option_key'] == 'option_key_'.count(self::get_all())){
			unset($item['row_actions']['down']);
		}

		if(empty($item['icon'])){
			$item['icon']	= WPJAM_Module::get_default_link_icon($item);
		}

		if($item['icon']){
			$item['icon']	= '<img src="'.wpjam_get_thumbnail($item['icon'], '100x100').'" style="width:50px;" />';
		}

		if(class_exists('WPJAM_WeappPage')){
			$item	= WPJAM_WeappPage::parse_item($item);	
		}
		
		return $item;
	}

	public static function get_actions(){
		return  [
			'add'	=> ['title'=>'新增',	'response'=>'list'],
			'edit'	=> ['title'=>'编辑'],
			'up'	=> ['title'=>'<span class="dashicons dashicons-arrow-up-alt"></span>',	'page_title'=>'向上移动',	'direct'=>true,	'response'=>'list'],
			'down'	=> ['title'=>'<span class="dashicons dashicons-arrow-down-alt"></span>','page_title'=>'向下移动',	'direct'=>true,	'response'=>'list'],
			'delete'=> ['title'=>'删除',	'response'=>'list',	'direct'=>true, 'confirm'=>true],
		];
	}

	public static function get_fields($action_key='', $id=0){
		$fields	= apply_filters('wpjam_modules_fields', []);

		$width	= static::$icon_size[0];
		$height	= static::$icon_size[1];

		return array_merge(
			$fields,
			[
				'icon'	=> ['title'=>'图标',	'type'=>'img',	'show_admin_column'=>true,	'item_type'=>'url',	'size'=>$width.'x'.$height, 'description'=>'规格：'.$width.'x'.$height],
				'title'	=> ['title'=>'名称',	'type'=>'text',	'show_admin_column'=>true],
				'path'	=> ['title'=>'路径',	'type'=>'text',	'show_admin_column'=>'only'],
			]
		);
	}
}

class WPJAM_ModuleCube extends WPJAM_Model {
	private static $handler;
	private static $option_name = 'module_cubes';
	protected static $limit = 10;

	public static function init($option_name, $limit){
		static::$option_name	= $option_name;
		static::$limit			= $limit ?: 10;
	}

	public static function get_handler(){
		if(is_null(static::$handler)){
			static::$handler	= new WPJAM_Option(static::$option_name);
		}
		return static::$handler;
	}

	public static function insert($data){
		$data	= static::validate($data);

		if(is_wp_error($data)){
			return $data;
		}

		$cube_limit		= static::$limit;

		if(count(self::get_all()) >= $cube_limit){
			return new WP_Error('too_much', '最多允许添加'.$cube_limit.'个图片魔方');
		}

		$data['key']	= md5(maybe_serialize($data).time());

		return parent::insert($data);
	}

	public static function update($id, $data){
		$data	= self::validate($data);

		if(is_wp_error($data)){
			return $data;
		}

		$current	= self::get($id);

		if(empty($current['key'])){
			$data['key']	= md5(maybe_serialize($data).time());
		}

		return parent::update($id, $data);
	}

	public static function validate($data){
		if($data['mode'] == 1){
			$data['layout'] = '1-1';
		}elseif(empty($data['layout'])){
			return new WP_Error('empty_layout', '布局不能为空。');
		}else{
			if(empty($data['height'])){
				return new WP_Error('empty_height', '高度不能为空。');
			}elseif($data['height']>=1200){
				return new WP_Error('too_big_height', '高度不能超过1280。');
			}
		}

		return $data;
	}

	public static function set_1($id, $data){
		return self::set($id, 1, $data);
	}

	public static function set_2($id, $data){
		return self::set($id, 2, $data);
	}

	public static function set_3($id, $data){
		return self::set($id, 3, $data);
	}

	public static function set_4($id, $data){
		return self::set($id, 4, $data);
	}

	public static function set($id, $no, $data){
		if(empty($data['image'])){
			return new WP_Error('empty_image', '图片不能为空。');
		}

		if(class_exists('WPJAM_WeappPage')){
			$data	= WPJAM_WeappPage::validate_data($data);	
		}

		if(is_wp_error($data)){
			return $data;
		}

		$cube	= self::get($id);
		$cube['images'][$no]	= $data; 

		return parent::update($id, $cube);
	}

	public static function item_callback($item){
		global $wpjam_list_table;

		unset($item['row_actions']['set_1']);
		unset($item['row_actions']['set_2']);
		unset($item['row_actions']['set_3']);
		unset($item['row_actions']['set_4']);

		$mode		= $item['mode'];
		$layout		= $item['layout'];
		$id			= $item['option_key'];
		$images		= $item['images']??[];

		
		$cube_options	= WPJAM_Module::get_cube_options();
		$cube_option	= $cube_options[$layout];

		$height		= $item['height']?:$cube_option['height'];
		$lines		= $cube_option['lines'];
		$widths		= $cube_option['widths'];

		$ratio		= $cube_options['1-1']['widths'][0] / 750;
		
		$height		= intval($height / $ratio);

		$height		= intval($height / 2.5);

		if($layout == '3-2' || $layout == '3-3'){
			$total_width	= 750/2.5 + 4*($mode-1);
			$total_height	= $height + 4;
		}elseif($layout == '4-2'){
			$total_width	= 750/2.5 + 4*($mode-2);
			$total_height	= $height + 4*2;
		}else{
			$total_width	= 750/2.5 + 4*($mode);
			$total_height	= $height + 4;
		}

		$images_html	= '';

		$layout_html	= '<div style="width:'.$total_width.'px; height:'.$total_height.'px; padding:2px; background-size:100% 100%; background-repeat: no-repeat; background-image: url('.WPJAM_BASIC_PLUGIN_URL.'/static/cube/cube-plus-'.$layout.'.png'.');">';

		$no = 0;
		foreach ($widths as $width) {
			$no ++;
			
			$width	= intval($width / $ratio);

			$width	= $width / 2.5;
			$image	= ($images && !empty($images[$no]))?$images[$no]:'';

			$images_html	.= '<p><strong>图'.$no.'：</strong>';

			if(($layout == '3-2' && $no == 2) || ($layout == '3-3' && $no == 1)){
				$layout_html	.= '<span style="width:'.($width+4).'px; height:'.($height+4).'px; display:block; float:left;">';
				$height			= $height/2 - 2;
			}elseif($layout == '4-2' && ($no == 1 || $no == 3)){
				$layout_html	.= '<span style="width:'.($width+4).'px; height:'.($height+8).'px; display:block; float:left;">';
				$height			= $height/2;
			}

			if($image){
				$img			= '<img src="'.wpjam_get_thumbnail($image['image'], [$width*2, $height*2]).'" width="'.$width.'" height="'.$height.'" />';
				$background		= 'background:#fff;';

				if(class_exists('WPJAM_WeappPage')){
					$image	= WPJAM_WeappPage::parse_item($image);	
				}

				$images_html	.= $image['path'];
			}else{
				$img			= ' ';
				$background		= ''; 
				$images_html	.= '未设置';
			}

			$images_html	.= '</p>';

			$layout_html	.= $wpjam_list_table->get_row_action('set_'.$no, [
				'id'		=> $id,
				'tag'		=> 'span',
				'style'		=> 'cursor: pointer; width:'.$width.'px; height:'.$height.'px; padding:2px; display:block; float:left; '.$background,
				'title'		=> $img
			]);

			if(($layout == '3-2' && $no == 3) || ($layout == '3-3' && $no == 2)){
				$layout_html	.= '</span>';
				$height	= ($height+2)*2;
			}elseif($layout == '4-2' && ($no == 2 || $no == 4)){
				$layout_html	.= '</span>';
				$height	= $height*2;
			}
		}

		$layout_html	.= '</div>'; 

		$item['layout']	= $layout_html;
		$item['images']	= $images_html;
		
		return $item;
	}

	public static function get_actions(){
		return  [
			'add'		=> ['title'=>'新增',	'response'=>'list'],
			'edit'		=> ['title'=>'编辑'],
			'set_1'		=> ['title'=>'设置图1','page_title'=>'设置图1'],
			'set_2'		=> ['title'=>'设置图2','page_title'=>'设置图2'],
			'set_3'		=> ['title'=>'设置图3','page_title'=>'设置图3'],
			'set_4'		=> ['title'=>'设置图4','page_title'=>'设置图4'],
			'delete'	=> ['title'=>'删除',	'response'=>'list',	'direct'=>true, 'confirm'=>true],
		];
	}

	public static function get_fields($action_key='', $id=0){
		$cube_options	= WPJAM_Module::get_cube_options();
		
		if(in_array($action_key,['set_1','set_2','set_3','set_4'])) {
			$no				= str_replace('set_', '', $action_key);

			$data 			= self::get($id);

			$layout			= $data['layout'];	
			$image_height	= $data['height'];

			$cube_option	= $cube_options[$layout];

			if($layout == '3-2'){
				if($no == '2' || $no == '3'){
					$image_height	= $image_height / 2;
				}
			}elseif($layout == '3-3'){
				if($no == '1' || $no == '2'){
					$image_height	= $image_height / 2;
				}
			}elseif($layout == '4-2'){
				$image_height	= $image_height / 2;
			}

			$image_width	= $cube_option['widths'][$no-1];

			$images			= $data['images']??[];
			$value			= $images[$no]??[];

			$fields			= apply_filters('wpjam_modules_fields', []);;

			foreach ($fields as $key => &$field) {
				if($field['type'] == 'fieldset'){
					foreach ($field['fields'] as $sub_key => &$sub_field) {
						$sub_field['value']	= $value[$sub_key]??'';
					}
				}else{
					$field['value']	= $value[$key]??'';
				}
			}

			return	array_merge(
				[	
					'image'	=> ['title'=>'图片',	'value'=>$value['image']??'',	'type'=>'img',	'item_type'=>'url',	'size'=>$image_width.'x'.$image_height, 'description'=>'规格：'.$image_width.'x'.$image_height]
				],
				$fields
			);
		}else{
			$mode_options	= [
				1	=>'一图模式',
				2	=>'二图模式',
				3	=>'三图模式',
				4	=>'四图模式'
			];

			$cube_options	= array_map(function($option){ $option['title']	= ''; return $option; }, $cube_options);

			return [
				'title'		=> ['title'=>'名称',		'type'=>'text',		'show_admin_column'=>true],
				'mode'		=> ['title'=>'模式',		'type'=>'select',	'show_admin_column'=>true,	'options'=>$mode_options],
				'layout'	=> ['title'=>'布局',		'type'=>'radio',	'show_admin_column'=>true,	'options'=>$cube_options ],
				'images'	=> ['title'=>'详情',		'type'=>'view',		'show_admin_column'=>'only'],
				'width'		=> ['title'=>'总宽度',	'type'=>'view',		'value'=>$cube_options['1-1']['widths'][0]],

				'height'	=> ['title'=>'高度',		'type'=>'number',	'show_admin_column'=>true,	'class'=>'small-text',	'description'=>'如果魔方为两行时，则为总高度。'],
				'spacing'	=> ['title'=>'间隔线',	'type'=>'checkbox',	'description'=>'图与图之间含有间隔线。'],
			];
		}	
	}

	public static function get_mode_options(){
		return [
			1	=>'一图模式',
			2	=>'二图模式',
			3	=>'三图模式',
			4	=>'四图模式'
		];
	}

	public static function list_page(){
		$cube_options	= WPJAM_Module::get_cube_options();
		$mode_1_height	= $cube_options['1-1']['height'];
		?>
		<script type="text/javascript">
		jQuery(function($){
			var cube_init	= true;
			var layouts;

			$('body').on('change', 'select#mode',function(){
				$("tr#tr_layout").hide();

				var mode	= $(this).val();
				var layout	= $('input[name="layout"]:checked').val();
				
				if(mode == '1'){
					$("tr#tr_layout").hide();
					if(!cube_init || !$("input#height").val()){
						$("input#height").val('<?php echo $mode_1_height; ?>');
					}
				}else{
					$("tr#tr_layout").show();
					
					$('#layout_options').empty().append(layouts.filter(function(){
						return ($(this).data('mode') == mode);
					}));
				}
			});

			$('body').on('change', '#layout_options input', function () {
				if ($(this).is(':checked')) {
					$("input#height").val($(this).parent().data('height'));
					cube_init = false;
				}
			});

			$('body').on('list_table_action_success', function(response){
				$("tr#tr_layout").hide();
				layouts	= $('#layout_options span').clone();
				cube_init	= true;
				$('body select#mode').change();
			});
		});
		</script>

		<style type="text/css">
		#layout_options label{
			display:inline-block;
			width:150px;
			height:75px;
			background-repeat:no-repeat;
			background-size: contain;
			margin-right:10px; 
		}
		#layout_options input{
			display: none;
		}
		
		<?php foreach ($cube_options as $key => $cube) { if($key == '1-1'){ continue; }?>
		#label_layout_<?php echo $key;?>{
			background-image: url(<?php echo WPJAM_BASIC_PLUGIN_URL.'/static/cube/cube-radio-'.$key.'.png';?>);
		}
		input#layout_<?php echo $key;?>:checked + #label_layout_<?php echo $key;?> {
			background-image: url(<?php echo WPJAM_BASIC_PLUGIN_URL.'/static/cube/cube-radio-selected-'.$key.'.png';?>);
		}
		<?php } ?>

		</style>
		<?php
	}
}