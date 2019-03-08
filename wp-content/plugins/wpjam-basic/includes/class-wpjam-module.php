<?php
class WPJAM_Module{
	private $option_name = 'wpjam_modules';
	private $args;

	public function __construct($option_name='wpjam_modules', $args = []){
		$this->option_name	= $option_name;
		$this->args			= wp_parse_args($args, [
			'ratio'		=> 1.6,
			'width'		=> 1200,
			'modules'	=> [],
			'settings'	=> [],
			'fields'	=> [],
		]);
	}

	public function get_modules(){
		$settings	= $this->get_settings();
		unset($settings['cubes']);

		return get_option($this->option_name) ?: $settings;
	}

	public function update_modules($modules){
		update_option($this->option_name, $modules);
	}

	public function get_settings(){
		$defaults = [
			'search_box'=>[
				'module_title'	=>'搜索框',
				'enable'		=>false,
				'fields'		=>['enable','type'],
				'data_type'		=>'search_box',
			],

			'sliders'	=>[
				'module_title'	=>'幻灯片',
				'enable'		=>true,
				'fields'		=>['enable','type'],
				'data_type'		=>'sliders',
				'option_name'	=>'module_sliders',
				'size'			=>[1200, 640]
			],
			
			'links'		=>[
				'module_title'	=>'快捷导航',
				'enable'		=>true,	
				'title'			=>'快捷导航',
				'fields'		=>['enable','type'],
				'data_type'		=>'links',
				'option_name'	=>'module_links',
				'size'			=>[160,160]
			],

			'cubes'		=> [
				'enable'		=>false,
				'module_title'	=>'图片魔方',	
				'fields'		=>['enable','type','title'],	
				'option_name'	=>'module_cubes',
				'data_type'		=>'cube',
				'limit'			=>10
			]
		];

		foreach ($this->args['modules'] as $key => &$setting) {
			if(isset($defaults[$key])){
				$setting	= wp_parse_args($setting, $defaults[$key]);
			}
		}

		return wp_parse_args($this->args['modules'], $defaults);
	}

	public function get_fields(){
		return wp_parse_args($this->args['fields'], [
			'enable'	=> ['title'=>'', 	'type'=>'hidden'],
			'type'		=> ['title'=>'',	'type'=>'hidden'],
			'title'		=> ['title'=>'标题',	'type'=>'text',		'class'=>'all-options'],
			'number'	=> ['title'=>'数量',	'type'=>'number',	'class'=>'small-text',	'min'=>1,	'max'=>10],
		]); 
	}

	public function get_cubes(){
		$_cubes	= get_option($this->get_settings()['cubes']['option_name']) ?: [];

		if(empty($_cubes)){
			return [];
		}

		$cubes	= [];
		foreach ($_cubes as $key => $cube) {
			$cubes[$cube['key']]	= $cube;
		}

		return $cubes;
	}

	public static function get_cube_options(){
		$cube_options	= [
			'1-1'	=>['mode'=>'1',	'lines'=>1,	'widths'=>[1200],			'height'=>400],
			'2-1'	=>['mode'=>'2',	'lines'=>1,	'widths'=>[600,600],		'height'=>600],
			'2-2'	=>['mode'=>'2',	'lines'=>1,	'widths'=>[800,400],		'height'=>400],
			'2-3'	=>['mode'=>'2',	'lines'=>1,	'widths'=>[400,800],		'height'=>400],
			'3-1'	=>['mode'=>'3',	'lines'=>1,	'widths'=>[400,400,400],	'height'=>400],
			'3-2'	=>['mode'=>'3',	'lines'=>2,	'widths'=>[600,600,600],	'height'=>800],
			'3-3'	=>['mode'=>'3',	'lines'=>2,	'widths'=>[600,600,600],	'height'=>800],
			'4-1'	=>['mode'=>'4',	'lines'=>1,	'widths'=>[300,300,300,300],'height'=>300],
			'4-2'	=>['mode'=>'4',	'lines'=>2,	'widths'=>[600,600,600,600],'height'=>800],
		];

		return apply_filters('wpjam_module_cube_options', $cube_options);
	}
	public static function parse_item($item){
		$parsed_item = [];

		$item['weapp_page']	= $item['weapp_page']??''; 

		if($item['weapp_page'] == 'mini_program'){
			$parsed_item['type']	= 'mini_program';
			$parsed_item['appid']	= $item['appid'];
			$parsed_item['path']	= $item['path'];
			if($parsed_item['path']){
				$parsed_item['path']	= '/'.ltrim($item['path'], '/');
			}
		}elseif($item['weapp_page'] == 'web_view'){
			$parsed_item['type']	= 'web_view';
			$parsed_item['src']		= $item['src'];
		}elseif($item['weapp_page'] == 'contact'){
			$parsed_item['type']	= 'contact';
			$parsed_item['tips']	= $item['tips'];
		}elseif($item['weapp_page'] == ''){
			$parsed_item['type']	= 'none';
		}else{
			$parsed_item['type']	= '';
			$parsed_item['path']	= $item['path'];
		}

		return $parsed_item;
	}

	public static function parse_sliders($sliders, $size){
		if(empty($sliders)){
			return [];
		}
				
		$parsed_sliders	= [];
		foreach ($sliders as $slider) {
			$active	= $slider['active'] ?? 1;

			if($active){
				$parsed_slider			= self::parse_item($slider);
				$parsed_slider['image']	= wpjam_get_thumbnail($slider['image'], $size);
				$parsed_sliders[]		= $parsed_slider;
			}
		}

		return $parsed_sliders;	
	}

	public static function parse_links($links, $size){
		if(empty($links)){
			return [];
		}

		$parsed_links	= [];
		foreach ($links as $link){
			$parsed_link			= self::parse_item($link);
			$parsed_link['title']	= $link['title'];

			if($link['icon']){
				$parsed_link['icon']	= wpjam_get_thumbnail($link['icon'], $size);
			}else{
				$icon	= self::get_default_link_icon($link);
				$parsed_link['icon']	= $icon ? wpjam_get_thumbnail($icon, $size) : '';
			}
			
			$parsed_links[] = $parsed_link;
		}

		return $parsed_links;
	}

	public static function get_default_link_icon($link){
		return apply_filters('wpjam_module_default_link_icon', '', $link);
	}

	public static function parse_cube($cube, $ratio=1.6){
		if(empty($cube)){
			return [];
		}

		$images	= $cube['images']??[];

		if(empty($images)){
			return [];
		}

		$height		= intval($cube['height']);
		$mode		= intval($cube['mode']);
		$layout		= $cube['layout'];
		$spacing	= isset($cube['spacing'])?boolval($cube['spacing']):0;

		$parsed_cube	= [
			'mode'		=>$mode,
			'layout'	=>$layout,
			'height'	=>intval($height/$ratio),
			'spacing'	=>$spacing,
			'images'	=>[],
		];

		$cube_options	= self::get_cube_options();

		$cube_option	= $cube_options[$layout];
		$widths			= $cube_option['widths'];

		$no = 0;
		foreach ($widths as $image_width) {
			$no++;
			if(!empty($images[$no])){
				$image_height = $height;

				if(($layout == '3-2' && ($no == 2 || $no == 3)) || ($layout == '3-3' && ($no == 1 || $no == 2)) || $layout == '4-2') {
					$image_height = $height / 2; 
				}

				$cube_image				= self::parse_item($images[$no]);
				$cube_image['image']	= wpjam_get_thumbnail($images[$no]['image'], [$image_width, $image_height]);
				$cube_image['width']	= intval($image_width/$ratio);
				$cube_image['height']	= intval($image_height/$ratio);

				$parsed_cube['images'][]	= $cube_image;
			}else{
				$parsed_cube['images'][]	= [];
			}
		}

		return $parsed_cube;		
	}

	public function output(){
		$modules			= [];
		$cubes				= [];
		$modules_settings	= [];

		$raw_modules	= $this->get_modules();
		$settings		= $this->get_settings();
		$ratio			= $this->args['ratio'];

		if(isset($raw_modules['search_box'])){
			$raw_modules	= array_merge(['search_box'=>$raw_modules['search_box']], $raw_modules);
		}

		foreach ($raw_modules as $module_key=>$raw_module) {
			if(!$raw_module['enable']){
				continue;
			}

			$module_exits	= true;
			$module_type	= $raw_module['type']??$module_key;

			if($module_type == 'cube'){
				$raw_module	= wp_parse_args($raw_module, $settings['cubes']);
			}else{
				if(isset($settings[$module_type])){
					$raw_module	= wp_parse_args($raw_module, $settings[$module_type]);
				}else{
					continue;
				}
			}

			$height			= $raw_module['height']??0;
			$height			= intval($height/$ratio);

			$module_setting	= [
				'key'	=>$module_key,
				'type'	=>$module_type,
				'height'=>$height,
				'title'	=>$raw_module['title']??''
			];

			$data_type	= $raw_module['data_type'] ?? '';

			if($data_type == 'sliders'){
				$sliders	= get_option($raw_module['option_name']);
				$size		= $raw_module['size'];

				$sliders	= self::parse_sliders($sliders, $size);

				if($sliders){
					$modules[$module_key]	= ['type'=>'other',	'args'=>compact('sliders')];
				}else{
					$module_exits	= false;
				}
			}elseif($data_type == 'links'){
				$links	= get_option($raw_module['option_name']);
				$size	= $raw_module['size'];
				$links	= self::parse_links($links, $size);

				if($links){
					$modules[$module_key]	= ['type'=>'other',	'args'=>compact('links')];
				}else{
					$module_exits	= false;
				}
			}elseif($data_type == 'cube'){
				$module_exits	= false;

				$raw_cubes		= $raw_cubes ?? $this->get_cubes();

				if($raw_cubes){
					$raw_cube	= $raw_cubes[$module_key]??[];
					$cube		= self::parse_cube($raw_cube, $ratio);

					if($cube){
						$module_exits	= true;
						$cube['title']	= $raw_module['title'];
						$cubes[$module_key]	= $cube;

						$module_setting['spacing']	= $cube['spacing'];
						$module_setting['layout']	= $cube['layout'];
						$module_setting['mode']		= $cube['mode'];
						$module_setting['height']	= intval($cube['height']);
					}	
				}
			}elseif($data_type == 'search_box'){
				$post_type		= $raw_module['post_type'] ?? '';

				$module_exits	= false;

				if($post_type){
					$module_exits	= true;

					$_query	= wpjam_query(['post_type'=>$post_type, 'post_status'=>'publish', 'no_found_rows'=>false]);

					$modules[$module_type]	= [
						'type'	=>'other',
						'args'	=>['total'=>(int)$_query->found_posts]
					];
				}
			}elseif($data_type == 'post_type'){
				$post_type		= $raw_module['post_type'] ?? '';
				$module_exits	= false;

				if($post_type){
					$module_exits	= true;

					$args	= [
						'post_type'	=> $post_type,
						'sub'		=> 1,
						'action'	=> 'list',
						'output'	=> $module_key
					];

					if($raw_module){

						foreach($raw_module as $setting_key=>$setting_value) {
							if(in_array($setting_key, ['module_title', 'enable', 'type', 'title', 'fields', 'data_type', 'post_type', 'height'])){
								continue;
							}

							if($setting_key == 'number'){
								$args['posts_per_page']	= intval($setting_value);
							}else{
								$args[$setting_key]		= $setting_value;
							}	
						}
					}

					$modules[$module_key]	= [
						'type'	=>'post_type',
						'args'	=>$args
					];
				} 
			}

			$modules	= apply_filters('wpjam_modules', $modules, $module_key, $raw_module);
			
			if($module_exits){
				$module_setting = apply_filters('wpjam_module_setting', $module_setting, $module_key, $raw_module);
				$modules_settings[$module_key]	= $module_setting;
			}
		}

		if($cubes){
			$modules['cubes']	= ['type'=>'other',	'args'=>compact('cubes')];
		}

		$modules['settings']	= [
			'type'	=>'other',
			'args'	=>['modules'=>array_values($modules_settings)]
		];

		return array_values($modules);
	}
}