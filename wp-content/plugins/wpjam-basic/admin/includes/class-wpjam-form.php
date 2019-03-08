<?php
class WPJAM_Form{
	public static $field_tmpls = [];
	
	public static function get_field_value($field, $args=[]){
		extract(wp_parse_args($args, array(
			'data_type'		=> 'form',
			'field_name'	=> '',		// 合并存储
			'data'			=> [],
			'id'			=> 0
		)));

		$field_type	= $field['type'];

		$key	= $field['key'];
		$value	= $field['value'] ?? '';

		if($field_type == 'view' && isset($field['value'])){
			return $value;
		}

		if($data_type == 'form'){
			return $data[$key]??$value;
		}elseif($data_type == 'post_meta'){
			if($id && metadata_exists('post', $id, $key)){

				return get_post_meta($id, $key, true);
			}else{
				return $value;
			}
		}elseif($data_type == 'term_meta'){
			if($id && metadata_exists('term', $id, $key)){
				return get_term_meta($id, $key, true);
			}else{
				return $value;
			}
		}elseif($data_type == 'option'){
			if($field_name){	// 如果合并存储
				$option	= wpjam_get_option($field_name);
				return $option[$key]??$value;
			}else{
				return (get_option($key) !== false)?get_option($key):$value;
			}
		}

		return $value;
	}

	// 显示字段
	public static function fields_callback($fields, $args=[]){
		extract(wp_parse_args($args, array(
			'fields_type'	=> 'table',
			'data_type'		=> 'form',
			'field_name'	=> '',		// 合并存储
			'field_callback'=> '',
			'data'			=> [],
			'id'			=> 0,
			'is_add'		=> false,
			'item_class'	=> '',
		)));

		$item_class	= ($item_class)?' class="'.$item_class.'"':''; 

		if($fields_type == 'list'){
			echo '<ul>';
		}elseif($fields_type == 'table'){
			echo '<table class="form-table" cellspacing="0">';
			echo '<tbody>';
		}

		foreach($fields as $key => $field){ 

			if(isset($field['show_admin_column']) && ($field['show_admin_column'] === 'only')){
				continue;
			}

			$field['key']	= $key;

			if($field_name){
				$field['name']	= $field_name.'['.$key.']';
			}else{
				$field_callback	= '';	// 不是合并存储，不能有 field_callback
			}

			if(!$is_add){
				if($field['type'] == 'fieldset'){
					$fieldset_type	= $field['fieldset_type']??'single';
					if($fieldset_type == 'single'){
						if($field['fields']){
							foreach ($field['fields'] as $sub_key => $sub_field) {
								$sub_field['key']	= $sub_key;
								$field['fields'][$sub_key]['value']	= self::get_field_value($sub_field, $args);

								if($field_name){
									$field['fields'][$sub_key]['name']	= $field_name.'['.$sub_key.']';
								}
							}
						}
					}else{
						$field['value']	= self::get_field_value($field, $args);
					}
				}else{
					$field['value']	= self::get_field_value($field, $args);
				}

				if(isset($field['options_key'])){
					$options_key	= $field['options_key'];
					if(isset($fields[$options_key])){
						$options_field			= $fields[$options_key];
						$options_field['key']	= $options_key;
						
						$options 	= array_values(self::get_field_value($options_field, $args));

						if($options_field['type'] == 'mu-fields'){
							$options	= array_combine(array_column($options,'key'), array_column($options,'item'));
						}

						$field['options']	= $options;
					}else{
						continue;
					}
				}
			}

			$field['html']	= self::field_callback($field, $field_callback);
			$field['title']	= $field['title']??'';

			if($field['title'] && $field['type']!='fieldset'){
				$field['title']	= '<label for="'.$key.'">'.$field['title'].'</label>';
			}

			if($field['type'] == 'hidden'){
				echo $field['html'];
			}else{
				if($fields_type == 'list'){
					echo '<li'.$item_class.' id="li_'.$key.'">'.$field['title'].$field['html'].'</li>';	
				}elseif($fields_type == 'tr' || $fields_type == 'table'){
					echo '<tr'.$item_class.' valign="top" id="tr_'.$key.'">';
					if($field['title']) {
						echo '<th scope="row">'.$field['title'].'</th>';
						echo '<td>'.$field['html'].'</td>';
					} else {
						echo '<td colspan="2" style="padding:20px 10px 20px 0;">'.$field['html'].'</td>';
					}
					echo '</tr>';
				}elseif($fields_type == 'div'){
					echo '<div'.$item_class.'id="div_'.$key.'">';
					echo $field['title'];
					echo $field['html'];
					echo '</div>';
				}else{
					echo $field['title'].$field['html'];
				}
			}
		}

		if($fields_type == 'list'){
			echo '</ul>';
		}elseif($fields_type == 'table'){
			echo '</tbody>';
			echo '</table>';
		}
	}

	public static function field_callback($field, $field_callback=''){
		$field_callback	= $field_callback ?: ($field['field_callback'] ?? '');

		if($field_callback){
			$field	= self::parse_field($field);
			return call_user_func($field_callback, $field);
		}else{
			return self::get_field_html($field);
		}
	}

	// 后端字段解析
	public static function parse_field($field){
		$field['key']	= $field['key']??'';
		$field['name']	= $field['name']??$field['key'];
		$field['value']	= isset($field['value'])?stripslashes_deep($field['value']):'';

		if(empty($field['type'])){
			$field['type']	= 'text';
		}elseif($field['type'] == 'br'){
			$field['type']	= 'view';
		}else{
			$field['type']	= str_replace(['mulit','multi','_'], ['mu','mu','-'], $field['type']);	// 各种 multi 写错，全部转换成 mu-
		}

		if(!empty($field['data-type'])){
			$field['data_type']	= $field['data-type'];	// data-type 转换成 data-type，所有自定义属性，都要写成 下划线
			unset($field['data-type']);
		}

		// if(!empty($field['data_type'])){
		// 	if($field['data_type'] == 'post_type' && !empty($field['post_type'])){
		// 		$wpjam_query	= new wp_query(array(
		// 			'post_type'			=> $field['post_type'],
		// 			'posts_per_page'	=> -1
		// 		));

		// 		$field['options']	= $field['options']??array(''=>' ');
		// 		if ( $wpjam_query->have_posts()) {
		// 			foreach ($wpjam_query->posts as $wpjam_post) {
		// 				$field['options'][$wpjam_post->ID]	= $wpjam_post->post_title;
		// 			}
		// 		}
		// 	}
		// }

		if(isset($field['options']) && !is_array($field['options'])){
			if(strpos($field['options'], '&')){			// url query string 模式
				$field['options']	= wp_parse_args($field['options']);
			}elseif(strpos($field['options'], '=>')){	// 自创的，不够标准，不推荐使用
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
			'color'		=> ''
		);
		$field['class']	= $field['class']??($default_classes[$field['type']]??'regular-text');

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

		if(!empty($field['post_type'])){
			$field['data-post_type']	= $field['post_type'];
			$field['list']				= $field['key'].'_list';
			$field['class']				.= ' post_id';
			if($field['value'] && empty($field['description']) && get_post($field['value'])){
				$field['description'] = get_post($field['value'])->post_title;
			}
		}
		
		$datalist = '';
		if(isset($field['list'])){
			$datalist	.= '<datalist id="'.$field['list'].'">';

			if(!empty($field['options'])){
				foreach ($field['options'] as $option_key => $option) {
					$datalist	.= '<option label="'.esc_attr($option).'" value="'.esc_attr($option_key).'" />';
				}
			}
			
			$datalist	.= '</datalist>';
		}
		
		$field['datalist'] = $datalist;
		
		$extra	= '';
		foreach ($field as $attr_key => $attr_value) {

			if(is_numeric($attr_key)){
				$attr_key	= $attr_value = strtolower(trim($attr_value));
				$field[$attr_key]	= $attr_value;
			}else{
				$attr_key	= strtolower(trim($attr_key));
			}
			
			if(!in_array($attr_key, ['type','name','title','key','description','class','value','default','options','fields','size','show_admin_column','sortable_column','taxonomies','taxonomy','datalist','settings','data_type','item_type','total','field_callback','field_validate','column_callback'])){

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

	// 获取表单 HTML
	public static function get_field_html($field){
		$field	= self::parse_field($field);
		extract($field);

		$del_item_button	= ' <a href="javascript:;" class="button del-item">删除</a> ';
		$sortable_dashicons	= ' <span class="dashicons dashicons-menu"></span>';

		switch ($type) {
			case 'color':
				$field_html	= self::get_input_field_html('text', $name, $key, $class.' color', $value, '', $description);
				break;

			case 'range':
				$extra		.=	' onchange="jQuery(\'#'.$key.'_span\').html(jQuery(\'#'.$key.'\').val());"';
				$field_html	= self::get_input_field_html($type, $name, $key, $class, $value, $extra).' <span id="'.$key.'_span">'.$value.'</span>';
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

			case 'checkbox':
				if(!empty($field['options'])){
					$field_html	= '';
					$i = 0;
					foreach ($field['options'] as $option_value => $option_title){ 
						$checked	= '';
						if($value && is_array($value) && in_array($option_value, $value)){
							$checked	= " checked='checked'";
						}

						$field_html .= '<span style="margin-right:8px;">'.self::get_input_field_html($type, $name.'['.$i.']', $key.'_'.$option_value, $class, $option_value, $extra.$checked, $option_title).'</span>';
						$i++;
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
					$sep	= $sep??' ';
					$value	= $value?:current(array_keys($field['options']));

					foreach ($field['options'] as $option_value => $option_title) {
						if(is_array($option_title)){
							$data_attr	= '';
							foreach ($option_title as $k => $v) {
								if($k != 'title' && !is_array($v)){
									$data_attr .= ' data-'.$k.'='.$v;
								}
							}

							$field_html	.= '<span '.$data_attr.'><input type="radio" name="'.$name.'" id="'.$key.'_'.$option_value.'" class="'.$class.'" value="'.$option_value.'" '.$extra.checked($option_value, $value, false).' /><label id="label_'.$key.'_'.$option_value.'" for="'.$key.'_'.$option_value.'">'.$option_title['title'].$sep.'</label></span>';
						}else{
							$field_html	.= '<span><input type="radio" name="'.$name.'" id="'.$key.'_'.$option_value.'" class="'.$class.'" value="'.$option_value.'" '.$extra.checked($option_value, $value, false).' /><label id="label_'.$key.'_'.$option_value.'" for="'.$key.'_'.$option_value.'">'.$option_title.$sep.'</label></span>';
						}
					}

					$field_html = '<div id="'.$key.'_options">'.$field_html.'</div>';

					if($description){
						$field_html .= '<br />'.$description;
					}
				}
				break;

			case 'file':
				$field_html	= self::get_input_field_html('url', $name, $key, $class, $value, $extra, $description).'<input type="button" item_type="" class="wpjam-file button" value="选择文件">';
				break;

			case 'image':
				$field_html	= self::get_input_field_html('url', $name, $key, $class, $value, $extra, '').'<input type="button" item_type="image" class="wpjam-file button" value="选择图片">'.$description;
				break;

			case 'img':
				$field_html = '';
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
					}
				}else{
					$div_class	.= ' default';
				}

				$field_html = '<div style="display: inline-block;">'.self::get_input_field_html('hidden', $name, $key, $class, $value).'<div data-item_type="'.$item_type.'" data-img_style="'.$img_style.'" data-thumb_args="'.$thumb_args.'"  class="'.$div_class.'">'.$field_html.'</div></div>'.$description;

				break;

			case 'textarea':
				$rows = $field['rows'] ?? 6;
				$field_html = '<textarea name="'.$name.'" id="'.$key.'" class="'.$class.' code" rows="'.$rows.'" cols="50" '.$extra.' >'.esc_textarea($value).'</textarea>'.$description;
				break;

			case 'editor':
				wp_enqueue_editor();
				
				$field_html = '';
				ob_start();
				$settings = $field['settings'] ?? [];
				wp_editor($value, $key, $settings);
				$field_style	= isset($field['style'])?' style="'.$field['style'].'"':'';
				$field_html 	= '<div'.$field_style.'>'.ob_get_contents().'</div>';
				ob_end_clean();

				$field_html		.= $description;
				break;

			case 'mu-file':
				$field_html  = '';
				if(is_array($value)){
					foreach($value as $file){
						if(!empty($file)){
							$field_html .= '<div class="mu-item">'.self::get_input_field_html('url', $name.'[]', $key, $class, esc_attr($file)).$del_item_button.$sortable_dashicons.'</div>';
						}
					}
				}

				$field_html  .= '<div class="mu-item">'.self::get_input_field_html('url', $name.'[]', $key, $class).'<input type="button" item_type="" class="wpjam-mu-file button" value="选择文件[多选]" title="按住Ctrl点击鼠标左键可以选择多个文件"></div>';

				$field_html = '<div class="mu-files sortable">'.$field_html.'</div>'.$description;

				break;

			case 'mu-image':
				$field_html  = '';
				if(is_array($value)){
					foreach($value as $image){
						if(!empty($image)){
							$field_html .= '<div class="mu-item">'.self::get_input_field_html('url', $name.'[]', $key, $class, esc_attr($image)).$del_item_button.$sortable_dashicons.'</div>';
						}
					}
				}

				$field_html  .= '<div class="mu-item">'.self::get_input_field_html('url', $name.'[]', $key, $class).' <input type="button" item_type="image" class="wpjam-mu-file button" value="选择图片[多选]" title="按住Ctrl点击鼠标左键可以选择多张图片"></div>';

				$field_html = '<div class="mu-images sortable" style="display:inline-grid;">'.$field_html.'</div>'.$description;
				break;

			case 'mu-img':
				$field_html = '';
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
				

				$field_html  .= '<div title="按住Ctrl点击鼠标左键可以选择多张图片" class="wpjam-mu-img" data-item_type="'.$item_type.'" data-input_name="'.$name.'[]"></div>';

				$field_html = '<div class="mu-imgs sortable">'.$field_html.'</div>'.$description;
				break;

			case 'mu-text':
				$field_html	= '';
				$item_type	= $field['item_type'] ?? 'text';
				$item_field	= $field;
				unset($item_field['description']);
				$item_field['type']	= $item_type;
				$item_field['name']	= $name.'[]';

				if(!empty($total)){
					for($i=0; $i<$total; $i++) { 
						$item	= $value[$i] ?? '';
						
						$item_field['value']	= $item;

						$field_html .= '<div class="mu-item">'.self::get_field_html($item_field).'</div>';
					}
				}else{
					if(is_array($value)){
						foreach($value as $item){
							if(!empty($item)){
								$item_field['value']	= $item;

								$field_html .= '<div class="mu-item">'.self::get_field_html($item_field).$del_item_button.$sortable_dashicons.'</div>';
							}
						}
					}

					$item_field['value']	= '';
					$field_html .= '<div class="mu-item">'.self::get_field_html($item_field).' <a class="wpjam-mu-text button">添加选项</a></div>';

					$field_html = '<div class="mu-texts sortable">'.$field_html.'</div>';
				}

				$field_html .= $description;

				break;

			case 'mu-fields':
				$field_html  = '';

				if(!empty($fields)){
					if(!empty($field['data-type'])){
						$field['data_type']	= $field['data-type'];
					}

					if(isset($field['data_type']) && $field['data_type'] == 'vote'){
						$fields['key']	= array('title'=>'', 'type'=>'hidden');
					}

					if(!empty($total)){
						for($i=0; $i<$total; $i++) { 
							$item	= $value[$i] ?? [];
							if($the_field_html = self::get_mu_fields_html($name, $fields, $i, $item)){
								$field_html .= '<div class="mu-item">'.$the_field_html.$sortable_dashicons.'</div>'; 
							}
						}

						$field_html	= '<div class="mu-fields sortable">'.$field_html.'</div>';
					}else{
						$i = 0;
						if(is_array($value)){
							foreach($value as $item){
								if(!empty($item)){
									if($the_field_html = self::get_mu_fields_html($name, $fields, $i, $item)){
										$field_html .= '<div class="mu-item">'.$the_field_html.$del_item_button.$sortable_dashicons.'</div>'; 
										$i++;
									}
								}
							}
						}

						$tmpl_id	= md5($name);

						$field_html	.= '<div class="mu-item">';
						$field_html	.= self::get_mu_fields_html($name, $fields, $i);
						$field_html	.= ' <a class="wpjam-mu-fields button" data-i="'.$i.'" data-tmpl-id="wpjam-'.$tmpl_id.'">添加选项</a>'; 
						$field_html	.= '</div>'; 

						$field_html	= '<div class="mu-fields sortable" id="mu_fields_'.$name.'">'.$field_html.'</div>';

						self::$field_tmpls[$tmpl_id]	= '<div class="mu-item">'.self::get_mu_fields_html($name,  $fields, '{{ data.i }}').' <a class="wpjam-mu-fields button" data-i="{{ data.i }}" data-tmpl-id="wpjam-'.$tmpl_id.'">添加选项</a>'.'</div>';
					}	
				}

				break;

			case 'fieldset':
				$field_html  = '<legend class="screen-reader-text"><span>'.$title.'</span></legend>';

				if(!empty($fields)){
					$fieldset_type		= $field['fieldset_type'] ?? 'single';

					foreach ($fields as $sub_key=>$sub_field) {

						if($fieldset_type == 'array'){
							$sub_field['name']	= $sub_field['name']??$name.'['.$sub_key.']';
							$sub_field['value']	= $value[$sub_key]??($sub_field['value']??'');
						}else{
							$sub_field['name']	= $sub_field['name']??$sub_key;
							$sub_field['value']	= $sub_field['value']??'';
						}

						if($sub_field['type'] == 'hidden'){
							$field_html			.= self::field_callback($sub_field);
						}else{
							$sub_field['key']	= $sub_field['key'] ?? $sub_key;

							if(!empty($sub_field['title'])){
								$field_title 		= '<label class="sub-field-label" for="'.$sub_key.'">'.$sub_field['title'].'</label>';
								$field_html			.= '<div class="sub-field" id="div_'.$sub_key.'">'.$field_title.'<div class="sub-field-detail">'.self::field_callback($sub_field).'</div>'.'</div>';
							}else{
								$field_title 		= '';
								$field_html			.= '<div class="sub-field" id="div_'.$sub_key.'">'.$field_title.self::field_callback($sub_field).'</div>';
							}	
						}
					}
				}
				break;

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
			
			default:
				$field_html = self::get_input_field_html($type, $name, $key, $class, $value, $extra, $description);
				break;
		}

		return apply_filters('wpjam_field_html', $field_html.$datalist, $field);
	}

	// 获取 input 表单 HTML
	public static function get_input_field_html($type, $name, $key, $class, $value='', $extra='',$description=''){
		$value	= ' value="'.esc_attr($value).'"';
		$class	= ($class)?' class="'.esc_attr($class).'"':'';
		$id		= ($key)?' id="'.esc_attr($key).'"':'';
		if($description && $type != 'hidden'){
			return '<label for="'.esc_attr($key).'">'.'<input type="'.esc_attr($type).'" name="'.esc_attr($name).'"'.$id.$class.$value.$extra.' /> '.$description.'</lable>';
		}else{
			return '<input type="'.esc_attr($type).'" name="'.esc_attr($name).'"'.$id.$class.$value.$extra.' /> ';
		}
	}

	public static function get_mu_fields_html($name, $fields, $i, $value=[]){
		$field_html		= '';
		$field_count	= count($fields);
		$count			= 0;

		$return = false;
		foreach ($fields as $sub_key=>$sub_field) {
			$count ++;
			$sub_field['name']	= $name.'['.$i.']'.'['.$sub_key.']';

			if($value){
				if(!empty($value[$sub_key])){
					$sub_field['value']	= $value[$sub_key];
				}
			}

			$class				= 'sub-field sub-field_'.$sub_key;		
			$sub_key			.=  '_'.$i; 
			$sub_field['key']	= $sub_key;
			$sub_field['data-i']= $i;

			if($sub_field['type'] == 'hidden'){
				$field_html		.= self::field_callback($sub_field);
			}else{
                // $field_style = $sub_field['style'] ?? '';
				$field_title 	= (!empty($sub_field['title']))?'<label class="sub-field-label" for="'.$sub_key.'">'.$sub_field['title'].'</label>':'';	
				$field_html		.= '<div class="'.$class.'" id="sub_field_'.$sub_key.'">'.$field_title.'<div class="sub-field-detail">'.self::field_callback($sub_field).'</div>'.'</div>';
			}
		}

		return $field_html;
	}

	public static function validate_fields_value($fields, $value=[]){
		$data = [];

		foreach ($fields as $key => $field) {
			if($field['type'] == 'fieldset'){
				if($field['fields']){
					$fieldset_type	= $field['fieldset_type'] ?? 'single';

					if($fieldset_type == 'array'){
						$field_value	= ($value)?($value[$key]??''):($_POST[$key]??'');
						$field_value	= self::validate_field_value($field, $field_value);
						if($field_value !== false){
							$data[$key] = $field_value;
						}
					}else{
						foreach ($field['fields'] as $sub_key => $sub_field) {
							$field_value	= ($value)?($value[$sub_key]??''):($_POST[$sub_key]??'');
							$field_value	= self::validate_field_value($sub_field, $field_value);
							if($field_value !== false){
								$data[$sub_key] = $field_value;
							}
						}
					}
				}
			}else{
				$field_value	= ($value)?($value[$key]??''):($_POST[$key]??'');
				$field_value	= self::validate_field_value($field, $field_value);
				if($field_value !== false){
					$data[$key] = $field_value;
				}
			}
		}

		return $data;
	}

	// 验证字段值
	public static function validate_field_value($field, $value=''){
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

		if(in_array($type, array('mu-image','mu-file','mu-text','mu-img'))){
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

				if(!empty($field['data-type'])){
					$field['data_type']	= $field['data-type'];
				}

				if(isset($field['data_type']) && $field['data_type'] == 'vote'){
					$value	= array_map(function($v){ $v['key']	= $v['key']?:md5(serialize($v)); return $v; }, $value);
				}
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

		if($field_validate	= ($field['field_validate'] ?? '')){
			$value	= call_user_func($field_validate, $value);
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
			if(!empty($field['data-type'])){
				$field['data_type']	= $field['data-type'];
			}

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