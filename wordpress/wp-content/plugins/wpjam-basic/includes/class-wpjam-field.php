<?php
class WPJAM_Field{
	public static $field_tmpls = [];

	public static function get_field_value($field, $args=[]){
		$args	= wp_parse_args($args, [
			'data_type'		=> 'form',
			'option_name'	=> '',
			'data'			=> [],
			'id'			=> 0
		]);

		$type	= $field['type'];

		if(is_admin()){
			$default	= $field['value'] ?? null;
		}else{
			$default	= $field['default'] ?? null;
		}

		if($type == 'view' && !is_null($default)){
			return $default;
		}

		$name	= $field['name'] ?? $field['key'];

		if(preg_match('/\[([^\]]*)\]/', $name)){
			$name_arr	= wp_parse_args($name);
			$name		= current(array_keys($name_arr));
		}else{
			$name_arr	= [];
		}

		$data_type	= $args['data_type'];
		$id			= $args['id'];
		$value		= null;

		if($data_type == 'form'){
			$data	= $args['data'];

			if($data && isset($data[$name])){
				$value	= $data[$name];
			}
		}elseif($data_type == 'option'){
			$option_name	= $args['option_name'];

			if($option_name){
				$value	= wpjam_get_setting($option_name, $name);
			}else{
				$value	= get_option($name, null);
			}
		}elseif($data_type == 'post_meta'){
			if($id && metadata_exists('post', $id, $name)){
				$value	= get_post_meta($id, $name, true);
			}
		}elseif($data_type == 'term_meta'){
			if($id && metadata_exists('term', $id, $name)){
				$value	= get_term_meta($id, $name, true);
			}
		}

		if(is_null($value)){
			return $default;
		}

		if($name_arr){
			$name_arr	= current(array_values($name_arr));
			
			do{
				$sub_name	= current(array_keys($name_arr));
				$name_arr	= current(array_values($name_arr));
				$value		= $value[$sub_name] ?? '';
			}while ($name_arr && $value);
		}

		return $value;
	}

	public static function fields_callback($fields, $args=[]){
		extract(wp_parse_args($args, array(
			'fields_type'	=> 'table',
			'data_type'		=> 'form',
			'option_name'	=> '',
			'data'			=> [],
			'id'			=> 0,
			'is_add'		=> false,
			'item_class'	=> '',
			'echo'			=> true,
		)));

		$item_class	= $item_class ? ' class="'.$item_class.'"' : ''; 

		$output	= '';

		if($fields_type == 'list'){
			$output	.= '<ul>';
		}elseif($fields_type == 'table'){
			$output	.= '<table class="form-table" cellspacing="0">';
			$output	.= '<tbody>';
		}
		
		foreach($fields as $key => $field){ 

			if(isset($field['show_admin_column']) && ($field['show_admin_column'] === 'only')){
				continue;
			}

			$field['key']	= $key;
			$field['name']	= $field['name'] ?? $key;

			if($field['type'] == 'fieldset'){
				$fieldset_type	= $field['fieldset_type'] ?? 'single';

				if(!empty($field['fields'])){
					foreach ($field['fields'] as $sub_key => &$sub_field){
						if($sub_field['type'] == 'fieldset'){
							wp_die('fieldset 不允许内嵌 fieldset');
						}

						$sub_field['key']	= $sub_key;
						$sub_field['name']	= $sub_field['name'] ?? $sub_key;

						if($fieldset_type == 'array'){
							$sub_field['name']	= $field['name'].self::generate_sub_field_name($sub_field['name']);	
						}

						if(!$is_add){
							$sub_field['value']	= self::get_field_value($sub_field, $args);
						}
						
						if($data_type == 'option' && $option_name){
							$sub_field['name']	= $option_name.self::generate_sub_field_name($sub_field['name']);;
						}	
					}
				}
			}else{
				if(!$is_add){
					$field['value']	= self::get_field_value($field, $args);
				}

				if($data_type == 'option' && $option_name){
					$field['name']	= $option_name.self::generate_sub_field_name($field['name']);
				}
			}

			$field['html']	= self::get_field_html($field);
			$field['title']	= $field['title']??'';

			if($field['title'] && $field['type']!='fieldset'){
				$field['title']	= '<label for="'.$key.'">'.$field['title'].'</label>';
			}

			if($field['type'] == 'hidden'){
				$output	.= $field['html'];
			}else{
				if($fields_type == 'list'){
					$output	.= '<li'.$item_class.' id="li_'.$key.'">'.$field['title'].$field['html'].'</li>';	
				}elseif($fields_type == 'tr' || $fields_type == 'table'){
					$output	.= '<tr'.$item_class.' valign="top" id="tr_'.$key.'">';
					if($field['title']) {
						$output	.= '<th scope="row">'.$field['title'].'</th>';
						$output	.= '<td>'.$field['html'].'</td>';
					} else {
						$output	.= '<td colspan="2" style="padding:12px 10px 12px 0;">'.$field['html'].'</td>';
					}
					$output	.= '</tr>';
				}elseif($fields_type == 'div'){
					$output	.= '<div'.$item_class.' id="div_'.$key.'">';
					$output	.= $field['title'];
					$output	.= $field['html'];
					$output	.= '</div>';
				}else{
					$output	.= $field['title'].$field['html'];
				}
			}
		}

		if($fields_type == 'list'){
			$output	.= '</ul>';
		}elseif($fields_type == 'table'){
			$output	.= '</tbody>';
			$output	.= '</table>';
		}

		if(wp_doing_ajax()){ 
			$output	.= self::get_field_tmpls();
		}

		if($echo){
			echo $output;
		}else{
			return $output;
		}
	}

	public static function get_field_tmpls(){
		$output = '';
		if(self::$field_tmpls){ 
			foreach (self::$field_tmpls as $tmpl_id => $field_tmpl) {
				$output .= "\n".'<script type="text/html" id="tmpl-wpjam-'.$tmpl_id.'">'."\n";
				$output .=  $field_tmpl."\n";
				$output .=  '</script>'."\n";
			}

			self::$field_tmpls	= [];
		}

		return $output;
	}

	public static function parse_field($field){
		$field['key']	= $field['key'] ?? '';
		$field['name']	= $field['name'] ?? $field['key'];
		$field['value']	= isset($field['value']) ?stripslashes_deep($field['value']) : '';

		if(empty($field['type'])){
			$field['type']	= 'text';
		}elseif($field['type'] == 'br'){
			$field['type']	= 'view';
		}else{
			$field['type']	= str_replace(['mulit','multi','_'], ['mu','mu','-'], $field['type']);	// 各种 multi 写错，全部转换成 mu-
		}

		if(!empty($field['data-type'])){
			// $field['data_type']	= $field['data-type'];	// data-type 转换成 data-type，所有自定义属性，都要写成 下划线
			unset($field['data-type']);
			trigger_error('data-type '.var_export($field, true));
		}

		if(isset($field['options']) && !is_array($field['options'])){
			if(strpos($field['options'], '&')){			// url query string 模式
				$field['options']	= wp_parse_args($field['options']);
			}elseif(strpos($field['options'], '=>')){	// 自创的，不够标准，不推荐使用
				trigger_error('using => in options');
				$options	= explode(",", $field['options']);
				$options	= array_map(function($option){
					$option	= explode("=>", $option);
					return array('k'=>trim($option[0]), 'v'=>trim($option[1]));
				}, $options);

				$field['options']	= wp_list_pluck($options, 'v', 'k');
			}
		}

		$default_classes = array(
			'textarea'	=> 'large-text',
			'checkbox'	=> '',
			'radio'		=> '',
			'select'	=> '',
			'color'		=> '',
			'date'		=> ''
		);

		$field['class']	= $field['class'] ?? ($default_classes[$field['type']] ?? 'regular-text');

		if($field['description'] = $field['description']??''){
			if($field['type'] == 'view' || $field['type'] == 'hr'){
				$field['description']	= '';
			}elseif($field['type'] == 'checkbox' || $field['type']=='mu-text'){
				$field['description']	= ' <span class="description">'.$field['description'].'</span>';
			}elseif($field['class'] != 'large-text' && $field['class'] != 'regular-text'){
				$field['description']	= ' <span class="description">'.$field['description'].'</span>';
			}else{
				$field['description']	= '<br /><span class="description">'.$field['description'].'</span>';
			}
		}
		
		$extra	= $field['extra'] ?? '';
		foreach ($field as $attr_key => $attr_value) {
			if(is_numeric($attr_key)){
				$attr_key	= $attr_value = strtolower(trim($attr_value));
				$field[$attr_key]	= $attr_value;
			}else{
				$attr_key	= strtolower(trim($attr_key));
			}
			
			if(!in_array($attr_key, ['type','name','title','key','description','class','value','default','options','fields','size','show_admin_column','sortable_column','taxonomies','taxonomy','settings','data_type','post_type','item_type','total','field_callback','field_validate','sanitize','validate','column_callback','sep','extra'])){

				if(is_object($attr_value) || is_array($attr_value)){
					trigger_error($attr_key.' '.var_export($attr_value, true));
				}else{
					$extra .= ' '.$attr_key.'="'.esc_attr($attr_value).'"';
				}
			}
		}

		$field['extra'] = $extra;

		return $field;
	}

	public static function get_field_html($field){
		$field	= self::parse_field($field);
		extract($field);

		$del_item_button	= ' <a href="javascript:;" class="button del-item">删除</a> ';
		$sortable_dashicons	= ' <span class="dashicons dashicons-menu"></span>';

		switch ($type) {
			case 'view':
				if(!empty($field['options'])){
					$value		= $value ?: 0;
					$field_html	= $field['options'][$value] ?? '';
				}else{
					$field_html	= $value;
				}
				
				break;

			case 'hr':
				$field_html	= '<hr />';
				break;

			case 'color':
				$field_html	= self::get_input_field_html('text', $name, $key, $class.' color', $value, '', $description);
				break;

			case 'range':
				$extra		.=	' onchange="jQuery(\'#'.$key.'_span\').html(jQuery(\'#'.$key.'\').val());"';
				$field_html	= self::get_input_field_html($type, $name, $key, $class, $value, $extra).' <span id="'.$key.'_span">'.$value.'</span>';
				break;

			case 'checkbox':
				if(!empty($field['options'])){
					$sep		= $field['sep'] ?? '&emsp;';
					$field_html	= '';
					foreach ($field['options'] as $option_value => $option_title){ 
						$checked	= '';
						if($value && is_array($value) && in_array($option_value, $value)){
							$checked	= " checked='checked'";
						}

						$field_html .= self::get_input_field_html($type, $name.'[]', $key.'_'.$option_value, $class, $option_value, $extra.$checked, $option_title).$sep;
					}

					$field_html = '<div id="'.$key.'_options">'.$field_html.'</div>'.$description;
				}else{
					$extra		.= checked('1', $value, false);
					$field_html	= self::get_input_field_html($type, $name, $key, $class, '1', $extra, $description);
				}
				break;

			case 'radio':
				$field_html	= '';
				if(!empty($field['options'])){
					$sep	= $field['sep'] ?? '&emsp;';
					$value	= $value?:current(array_keys($field['options']));

					foreach ($field['options'] as $option_value => $option_title) {
						$data_attr	= '';

						if(is_array($option_title)){
							foreach ($option_title as $k => $v) {
								if($k != 'title' && !is_array($v)){
									$data_attr .= ' data-'.$k.'='.$v;
								}
							}

							$option_title	= $option_title['title'];
						}

						$field_html	.= '<label '.$data_attr.' id="label_'.$key.'_'.$option_value.'" for="'.$key.'_'.$option_value.'"><input type="radio" name="'.$name.'" id="'.$key.'_'.$option_value.'" class="'.$class.'" value="'.$option_value.'" '.$extra.checked($option_value, $value, false).' />'.$option_title.'</label>'.$sep;
					}

					$field_html = '<div id="'.$key.'_options">'.$field_html.'</div>';

					if($description){
						$field_html .= '<br />'.$description;
					}
				}
				break;

			case 'select':
				$field_html	= '<select name="'.esc_attr($name).'" id="'. esc_attr($key).'" class="'.esc_attr($class).'" '.$extra.' >';
				if(!empty($field['options'])){
					foreach ($field['options'] as $option_value => $option_title){ 
						if(is_array($option_title)){
							$data_attr	= '';
							foreach ($option_title as $k => $v) {
								if($k != 'title' && !is_array($v)){
									$data_attr .= ' data-'.$k.'='.$v;
								}
							}
							$field_html .= '<option value="'.esc_attr($option_value).'" '.selected($option_value, $value, false).$data_attr.'>'.$option_title['title'].'</option>';
						}else{
							$field_html .= '<option value="'.esc_attr($option_value).'" '.selected($option_value, $value, false).'>'.$option_title.'</option>';
						}
					}
				}
				$field_html .= '</select>' .$description;

				break;

			case 'file':
				$field_html	= '';

				if(current_user_can('upload_files')){
					$field_html	= self::get_input_field_html('url', $name, $key, $class, $value, $extra, $description).'<input type="button" item_type="" class="wpjam-file button" value="选择文件">';
				}

				break;

			case 'image':
				$field_html	= '';
				
				if(current_user_can('upload_files')){
					$field_html	= self::get_input_field_html('url', $name, $key, $class, $value, $extra, '').'<input type="button" item_type="image" class="wpjam-file button" value="选择图片">'.$description;
				}

				break;

			case 'img':
				$field_html = '';

				if(current_user_can('upload_files')){
					$item_type	= $field['item_type']??'';
					$size		= $field['size']??'400x0';

					$img_style	= '';
					$thumb_args	= '';
					if(isset($field['size'])){
						$size	= wpjam_parse_size($field['size']);

						if($size['width'] > 600 || $size['height'] > 600){
							if($size['width'] > $size['height']){
								$size['height']	= intval(($size['height'] / $size['width']) * 600);
								$size['width']	= 600;
							}else{
								$size['width']	= intval(($size['width'] / $size['height']) * 600);
								$size['height']	= 600;
							}
						}

						if($size['width']){
							$img_style	.= ' width:'.intval($size['width']/2).'px;';
						}

						if($size['height']){
							$img_style	.= ' height:'.intval($size['height']/2).'px;';
						}

						$thumb_args	= wpjam_get_thumbnail('',$size);
					}else{
						$thumb_args	= wpjam_get_thumbnail('',400);
					}

					$img_style	= $img_style ?: 'max-width:200px;';
					
					$div_class	= 'wpjam-img';

					if(!empty($value)){
						$img_url	= ($item_type == 'url')?$value:wp_get_attachment_url($value);

						if($img_url){
							$img_url	= wpjam_get_thumbnail($img_url, $size);

							$field_html	= '<img style="'.$img_style.'" src="'.$img_url.'" alt="" /><a href="javascript:;" class="del-img dashicons dashicons-no-alt"></a>';
						}else{
							$field_html	= '<span class="wp-media-buttons-icon"></span> 添加图片</button>';
							$div_class	.= ' button add_media';
						}
					}else{
						$field_html	= '<span class="wp-media-buttons-icon"></span> 添加图片</button>';
						$div_class	.= ' button add_media';
					}

					$field_html = '<div class="wp-media-buttons" style="display: inline-block; float:none;">'.self::get_input_field_html('hidden', $name, $key, $class, $value).'<div data-item_type="'.$item_type.'" data-img_style="'.$img_style.'" data-thumb_args="'.$thumb_args.'"  class="'.$div_class.'">'.$field_html.'</div></div>'.$description;
				}

				break;

			case 'textarea':
				$rows = $field['rows'] ?? 6;
				$field_html = '<textarea name="'.$name.'" id="'.$key.'" class="'.$class.' code" rows="'.$rows.'" cols="50" '.$extra.' >'.esc_textarea($value).'</textarea>'.$description;
				break;

			case 'editor':
				wp_enqueue_editor();
				
				$field_html = '';
				ob_start();
				$settings		= $field['settings'] ?? [];
				wp_editor($value, $key, $settings);
				$field_style	= isset($field['style'])?' style="'.$field['style'].'"':'';
				$field_html 	= '<div'.$field_style.'>'.ob_get_contents().'</div>';
				ob_end_clean();

				$field_html		.= $description;
				break;

			case 'mu-file':
				$field_html	= '';
				
				if(current_user_can('upload_files')){

					if(is_array($value)){
						foreach($value as $file){
							if(!empty($file)){
								$field_html .= '<div class="mu-item">'.self::get_input_field_html('url', $name.'[]', $key, $class, esc_attr($file)).$del_item_button.$sortable_dashicons.'</div>';
							}
						}
					}

					$field_html  .= '<div class="mu-item">'.self::get_input_field_html('url', $name.'[]', $key, $class).'<input type="button" item_type="" class="wpjam-mu-file button" value="选择文件[多选]" title="按住Ctrl点击鼠标左键可以选择多个文件"></div>';

					$field_html = '<div class="mu-files sortable">'.$field_html.'</div>'.$description;
				}

				break;

			case 'mu-image':
				$field_html	= '';
				
				if(current_user_can('upload_files')){

					if(is_array($value)){
						foreach($value as $image){
							if(!empty($image)){
								$field_html .= '<div class="mu-item">'.self::get_input_field_html('url', $name.'[]', $key, $class, esc_attr($image)).$del_item_button.$sortable_dashicons.'</div>';
							}
						}
					}

					$field_html  .= '<div class="mu-item">'.self::get_input_field_html('url', $name.'[]', $key, $class).' <input type="button" item_type="image" class="wpjam-mu-file button" value="选择图片[多选]" title="按住Ctrl点击鼠标左键可以选择多张图片"></div>';

					$field_html = '<div class="mu-images sortable" style="display:inline-grid;">'.$field_html.'</div>'.$description;
				}

				break;

			case 'mu-img':
				$field_html = '';

				$field_html	= '';
				
				if(current_user_can('upload_files')){

					$item_type	= $field['item_type']??'';

					if(is_array($value)){
						foreach($value as $img){
							if(!empty($img)){

								$img_url	= ($item_type == 'url')?$img:wp_get_attachment_url($img);

								if(function_exists('wpjam_get_thumbnail')){
									$img_url = wpjam_get_thumbnail($img_url, 200, 200);
								}

								$field_html .= '<div class="mu-img mu-item"><img width="100" src="'.$img_url.'" alt="">'.self::get_input_field_html('hidden', $name.'[]', $key, $class, $img).'<a href="javascript:;" class="del-item dashicons dashicons-no-alt"></a></div>';
							}
						}
					}

					$field_html  .= '<div title="按住Ctrl点击鼠标左键可以选择多张图片" class="wpjam-mu-img dashicons dashicons-plus-alt2" data-item_type="'.$item_type.'" data-input_name="'.$name.'[]"></div>';

					$field_html = '<div class="mu-imgs sortable">'.$field_html.'</div>'.$description;
				}
					
				break;

			case 'mu-text':
				$field_html	= '';

				if(!empty($field['data_type'])){
					$field['list']	= $key.'_list';
					$field['extra']	= ' list="'.esc_attr($field['list']).'"';
				}

				$item_type	= $field['item_type'] ?? 'text';
				$item_field	= $field;
				unset($item_field['description']);
				$item_field['type']	= $item_type;
				$item_field['name']	= $name.'[]';

				if(!empty($total)){
					for($i=0; $i<$total; $i++) { 
						$item	= $value[$i] ?? '';
						
						$item_field['value']	= $item;
						$item_field['key']		= $key.'_'.$i;

						$field_html .= '<div class="mu-item">'.self::get_field_html($item_field).'</div>';
					}
				}else{
					$i = 0;
					if(is_array($value)){
						foreach($value as $item){
							if(!empty($item)){
								$item_field['value']	= $item;
								$item_field['key']		= $key.'_'.$i;

								$field_html .= '<div class="mu-item">'.self::get_field_html($item_field).$del_item_button.$sortable_dashicons.'</div>';

								$i++;
							}
						}
					}

					$item_field['value']	= '';
					$item_field['key']		= $key.'_'.$i;

					$field_html .= '<div class="mu-item">'.self::get_field_html($item_field).' <a class="wpjam-mu-text button" data-i="'.$i.'" data-key="'.$key.'">添加选项</a></div>';

					$field_html = '<div class="mu-texts sortable">'.$field_html.'</div>';
				}

				$field_html .= $description;

				break;

			case 'mu-fields':
				$field_html  = '';

				if(!empty($field['fields'])){
					if(!empty($field['data-type'])){
						// $field['data_type']	= $field['data-type'];
						trigger_error('data-type '.var_export($field, true));
					}

					if(!empty($total)){
						for($i=0; $i<$total; $i++) { 
							$item	= $value[$i] ?? [];
							if($the_field_html = self::get_mu_fields_html($name, $field['fields'], $i, $item)){
								$field_html .= '<div class="mu-item">'.$the_field_html.$sortable_dashicons.'</div>'; 
							}
						}

						$field_html	= '<div class="mu-fields sortable">'.$field_html.'</div>';
					}else{
						$i = 0;
						if(is_array($value)){
							foreach($value as $item){
								if(!empty($item)){
									if($the_field_html = self::get_mu_fields_html($name, $field['fields'], $i, $item)){
										$field_html .= '<div class="mu-item">'.$the_field_html.$del_item_button.$sortable_dashicons.'</div>'; 
										$i++;
									}
								}
							}
						}

						$tmpl_id	= md5($name);

						$field_html	.= '<div class="mu-item">';
						$field_html	.= self::get_mu_fields_html($name, $field['fields'], $i);
						$field_html	.= ' <a class="wpjam-mu-fields button" data-i="'.$i.'" data-tmpl-id="wpjam-'.$tmpl_id.'">添加选项</a>'; 
						$field_html	.= '</div>'; 

						$field_html	= '<div class="mu-fields sortable" id="mu_fields_'.$name.'">'.$field_html.'</div>';

						self::$field_tmpls[$tmpl_id]	= '<div class="mu-item">'.self::get_mu_fields_html($name,  $field['fields'], '{{ data.i }}').' <a class="wpjam-mu-fields button" data-i="{{ data.i }}" data-tmpl-id="wpjam-'.$tmpl_id.'">添加选项</a>'.'</div>';
					}	
				}

				break;

			case 'fieldset':
				if(!empty($field['fields'])){
					$field_html  = '<legend class="screen-reader-text"><span>'.$title.'</span></legend>';

					$fieldset_type	= $field['fieldset_type'] ?? 'single';

					foreach ($field['fields'] as $sub_key=>$sub_field) {
						$sub_field['name']	= $sub_field['name'] ?? $sub_key;
						
						if($sub_field['type'] == 'hidden'){
							$field_html			.= self::get_field_html($sub_field);
						}else{
							$sub_field['key']	= $sub_field['key'] ?? $sub_key;

							if(!empty($sub_field['title'])){
								$field_title	= '<label class="sub-field-label" for="'.$sub_key.'">'.$sub_field['title'].'</label>';
								$field_html		.= '<div class="sub-field" id="div_'.$sub_key.'">'.$field_title.'<div class="sub-field-detail">'.self::get_field_html($sub_field).'</div>'.'</div>';
							}else{
								$field_html		.= '<div class="sub-field" id="div_'.$sub_key.'">'.self::get_field_html($sub_field).'</div>';
							}	
						}
					}
				}
				break;
			
			default:
				$query_title	= '';
				if(!empty($field['data_type'])){
					$extra		.= ' data-data_type="'.esc_attr($field['data_type']).'"';

					if(!isset($field['list'])){
						$field['list']	= $key.'_list';
						$extra			.= ' list="'.esc_attr($field['list']).'"';
					}
					
					$class		= 'wpjam-query-id '.$class;
					$span_class	= 'wpjam-query-title';

					if($field['data_type'] == 'post_type'){
						$extra .= ' data-post_type="'.esc_attr($field['post_type']).'"';

						if($value && ($field_post = get_post($value))){
							$class		.= ' hidden';
							$post_title	= $field_post->post_title ?: $field_post->ID;
						}else{
							$span_class	.= ' hidden';
							$post_title	= '';
						}

						$query_title	= '<span class="'.$span_class.'"><span class="dashicons dashicons-dismiss"></span>'.$post_title.'</span>';
					}elseif($field['data_type'] == 'taxonomy'){
						$extra .= ' data-taxonomy="'.esc_attr($field['taxonomy']).'"';

						if($value && ($field_term = get_term($value))){
							$class		.= ' hidden';
							$term_name	= $field_term->name ?: $field_term->term_id;
						}else{
							$span_class	.= ' hidden';
							$term_name	= '';
						}

						$query_title	= '<span class="'.$span_class.'"><span class="dashicons dashicons-dismiss"></span>'.$term_name.'</span>';
					}

					$description	= '';
				}

				$field_html = self::get_input_field_html($type, $name, $key, $class, $value, $extra, $description).$query_title;
				
				break;
		}

		$datalist = '';
		if(!empty($field['list'])){
			static $datalist_ids;
			$datalist_ids	= $datalist_ids ?? [];

			if(!in_array($field['list'], $datalist_ids)){
				$datalist_ids[]	= $field['list'];

				$datalist	.= '<datalist id="'.$field['list'].'">';

				if(!empty($field['options'])){
					foreach ($field['options'] as $option_key => $option) {
						$datalist	.= '<option label="'.esc_attr($option).'" value="'.esc_attr($option_key).'" />';
					}
				}
				
				$datalist	.= '</datalist>';
			}
		}

		return apply_filters('wpjam_field_html', $field_html.$datalist, $field);
	}

	private static function get_input_field_html($type, $name, $key, $class, $value='', $extra='', $description=''){
		$class	= $class ? ' class="'.esc_attr($class).'"' : '';
		$html	= '<input type="'.esc_attr($type).'" name="'.esc_attr($name).'" id="'.esc_attr($key).'" value="'.esc_attr($value).'"'.$class.$extra.' />';

		if($description && $type != 'hidden'){
			$html	= '<label for="'.esc_attr($key).'">'.$html.$description.'</label>';
		}

		return $html;
	}

	private static function get_mu_fields_html($name, $fields, $i, $value=[]){
		$field_html		= '';
		$field_count	= count($fields);
		$count			= 0;

		$return = false;
		foreach ($fields as $sub_key=>$sub_field) {
			$count ++;

			$sub_name	= $sub_field['name'] ?? $sub_key;

			if(preg_match('/\[([^\]]*)\]/', $sub_name)){
				wp_die('mu-fields 类型里面子字段不允许[]模式');
			}
			
			$sub_field['name']	= $name.'['.$i.']'.'['.$sub_name.']';

			if($value){
				if(!empty($value[$sub_name])){
					$sub_field['value']	= $value[$sub_name];
				}
			}

			$class				= 'sub-field sub-field_'.$sub_key;		
			$sub_key			.= '_'.$i; 
			$sub_field['key']	= $sub_key;
			$sub_field['data-i']= $i;

			if($sub_field['type'] == 'hidden'){
				$field_html		.= self::get_field_html($sub_field);
			}else{
                // $field_style = $sub_field['style'] ?? '';
				$field_title 	= (!empty($sub_field['title']))?'<label class="sub-field-label" for="'.$sub_key.'">'.$sub_field['title'].'</label>':'';	
				$field_html		.= '<div class="'.$class.'" id="sub_field_'.$sub_key.'">'.$field_title.'<div class="sub-field-detail">'.self::get_field_html($sub_field).'</div>'.'</div>';
			}
		}

		return $field_html;
	}

	private static function generate_sub_field_name($name){
		if(preg_match('/\[([^\]]*)\]/', $name)){
			$name_arr	= wp_parse_args($name);
			$name		= '';

			do{
				$name		.='['.current(array_keys($name_arr)).']';
				$name_arr	= current(array_values($name_arr));
			}while ($name_arr);

			return $name;
		}else{
			return '['.$name.']';
		}
	}

	public static function validate_fields_value($fields, $values=[]){
		$data = [];

		foreach ($fields as $key => $field) {
			if($field['type'] == 'fieldset'){
				if(empty($field['fields'])){
					continue;
				}
				
				$fieldset_type	= $field['fieldset_type'] ?? 'single';

				if($fieldset_type == 'array'){
					
					$name	= $field['name'] ?? $key;

					array_walk($field['fields'], function(&$sub_field, $sub_key) use($name){
						$sub_field['name']	= $sub_field['name'] ?? $sub_key;
						$sub_field['name']	= $name.self::generate_sub_field_name($sub_field['name']);
					});
				}

				$data	= self::merge($data, self::validate_fields_value($field['fields'], $values));
			}else{
				$name	= $field['name'] ?? $key;
				$values	= $values ?: $_POST;

				if(preg_match('/\[([^\]]*)\]/', $name)){
					$name_arr	= wp_parse_args($name);
					$name		= current(array_keys($name_arr));
					$value		= $values[$name] ?? '';

					$name_arr		= current(array_values($name_arr));
					$sub_name_arr	= [];

					do{
						$sub_name	= current(array_keys($name_arr));
						$name_arr	= current(array_values($name_arr));
						$value		= $value[$sub_name] ?? '';

						array_unshift($sub_name_arr, $sub_name);
					}while($name_arr && $value);

					$value	= self::sanitize_by_field($value, $field);

					if($value !== false){	
						foreach($sub_name_arr as $sub_name) {
							$value	= [$sub_name => $value];
						}

						$data	= self::merge($data, [$name=>$value]);
					}
					
				}else{
					$value	= $values[$name] ?? '';
					$value	= self::sanitize_by_field($value, $field);

					if($value !== false){
						$data[$name]	= $value;
					}
				}
			}
		}

		return $data;
	}

	public static function merge($data, $arr){
		foreach($arr as $key => $value) {
			if(!isset($data[$key])){
				$data[$key]	= $arr[$key];
			}else{
				if(is_array($value) && is_array($data[$key])) {
					$data[$key]	= self::merge($data[$key], $value);
				}else{
					$data[$key]	= $value;
				}
			}
		}

		return $data;
	}
	
	public static function sanitize_by_field($value, $field){
		$field	= self::parse_field($field);
		$type	= $field['type'];

		if($type == 'view' || $type == 'hr'){
			return false;
		}

		if(!empty($field['readonly']) || !empty($field['disabled'])){
			return false;
		}

		if(isset($field['show_admin_column']) && ($field['show_admin_column'] === 'only')){
			return false;
		}

		if(in_array($type, ['mu-image','mu-file','mu-text','mu-img'])){
			if(!is_array($value)){
				$value	= '';
			}else{
				$value	= array_filter($value);
			}
		}elseif($type == 'mu-fields'){
			if(!is_array($value)){
				$value	= '';
			}else{
				$value	= array_filter($value, function($v){
					foreach($v as $sub_key => $sub_value) {
						if(is_array($sub_value)){
							$v[$sub_key]	= array_filter($sub_value);
						}
					}
					return !empty(array_filter($v));
				});
			}
		}

		if(!is_array($value)){
			$value	= stripslashes(trim($value));	
		}

		if($type == 'textarea'){
			$value	= str_replace("\r\n", "\n",$value);
		}elseif($type == 'number'){
			$value	= intval($value);
		}

		if(!empty($field['sanitize'])){
			$value	= call_user_func($field['sanitize'], $value);
		}

		return $value;
	}

	public static function column_callback($column_name, $args=[]){
		extract(wp_parse_args($args, array(
			'id'			=> 0,
			'field'			=> '',
			'data_type'		=> 'form',
			'item_value'	=> ''
		)));

		if($data_type == 'form'){
			$column_value = $item_value[$column_name]??'';
		}elseif($data_type == 'post_meta'){
			$column_value = get_post_meta($id, $column_name, true);
		}elseif($data_type == 'term_meta'){
			$column_value = get_term_meta($id, $column_name, true);
		}

		$column_value	= $column_value?:($field['default']??'');

		if(!empty($field['column_callback'])){
			$column_value = call_user_func($field['column_callback'], $id, $column_value);
		}else{
			$field	= self::parse_field($field); 
			if($column_value){
				if($field['type'] == 'img'){
					$item_type	= $field['item_type']??'';

					$attachment_url	= ($item_type == 'url')?$column_value:wp_get_attachment_url($column_value);
					if($attachment_url){
						return '<img src="'.wpjam_get_thumbnail($attachment_url, '80x80').'" width="40" />';
					}
				}elseif(!empty($field['data_type'])){
					if($field['data_type'] == 'post_type'){
						return get_post($column_value)->post_title;
					}
				}
			}

			if(!empty($field['options'])){
				$column_options	= $field['options'];
				$column_value	= $column_options[$column_value]??$column_value;
			}
		}

		return $column_value;
	}
}

class WPJAM_Form extends WPJAM_Field{
}