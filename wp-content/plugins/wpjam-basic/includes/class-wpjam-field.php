<?php
class WPJAM_Field{
	// 解析 shortcode 模式的 fields
	public static function parse_fields_setting($fields, $is_sub=0){
		if(empty($fields)) return array();

		$parsed_fields  = array();

		if(is_array($fields)){
			$fields	= ($is_sub)?$fields:wp_list_pluck($fields, 'detail', 'key');

			foreach ($fields as $field_key => $field_detail) {
				if(!$field_key || !$field_detail) continue;

				$field_detail	= wpjam_parse_shortcode_attr(stripslashes_deep($field_detail), 'field');

				if(empty($field_detail['type'])) continue;
				
				if((!empty($field_detail['sub-field']) || !empty($field_detail['sub_field'])) && $is_sub == 0){
					continue;
				}else{
					if($field_detail['type'] == 'mu-fields' || $field_detail['type'] == 'fieldset'){
						if(!empty($field_detail['fields'])){
							$field_detail['fields']	= array_flip(explode(',', $field_detail['fields']));

							foreach ($field_detail['fields'] as $sub_field_key=>$dummy) {
								if(isset($fields[$sub_field_key])){
									$field_detail['fields'][$sub_field_key]	= $fields[$sub_field_key];
								}else{
									unset($field_detail['fields'][$sub_field_key]);
								}
							}
							$field_detail['fields']	= self::parse_fields_setting($field_detail['fields'], 1);
						}

						// 设置 mu-fields 的初始值
						if(!empty($field_detail['value']) && $field_detail['type'] == 'mu-fields'){
							$field_detail['value'] = array_map(function($sub_value){ return wp_parse_args($sub_value); }, explode(',', $field_detail['value'])); 
						}
					}

					$parsed_fields[$field_key]	= $field_detail;
				}
			}

			return $parsed_fields;
		}else{
			// term options 情况
			$field	= array();
			foreach (explode("\n", $fields) as $i => $a_field) {
				if(trim($a_field)){
					$a_field	= wpjam_parse_shortcode_attr(stripslashes_deep($a_field), 'field');

					if($i==0){
						$field	= $a_field;
					}elseif ($a_field) {
						$field['fields'][$a_field['key']]	= $a_field;
					}
				}
			}

			return $field;
		}
	}

	public static function parse_field_value($value, $field){
		$value	= $value?:($field['default']??'');
		if(empty($value))	return $value;

		if($field['type'] == 'img'){
			$thumbnail_size	= $field['size']??'200x200';
			$item_type		= $field['item_type']??'';
			if($item_type == 'url'){
				$value	= wpjam_get_thumbnail($value, $thumbnail_size);
			}else{
				// wpjam_print_R($value);
				$value	= wpjam_get_thumbnail(wp_get_attachment_url($value), $thumbnail_size);
				// wpjam_print_R($value);
			}
		}elseif($field['type'] == 'image'){
			$thumbnail_size	= $field['size']??'200x200';
			$value	= wpjam_get_thumbnail($value, $thumbnail_size);
		}elseif($field['type'] == 'file'){
			$value	= wpjam_get_thumbnail($value);
		}elseif($field['type'] == 'mu-img') {
			if(is_array($value)){
				$thumbnail_size	= $field['size']??'200x200';
				$item_type		= $field['item_type']??'';
				if($item_type == 'url'){
					$value	= array_map(function($v) use ($thumbnail_size){ return  wpjam_get_thumbnail($v, $thumbnail_size); }, $value);
				}else{
					$value	= array_map(function($v) use ($thumbnail_size){ return  wpjam_get_thumbnail(wp_get_attachment_url($v), $thumbnail_size); }, $value);
				}
				$value	= array_values($value);
			}
		}elseif($field['type'] == 'mu-image' || $field['type'] == 'mu-file') {
			if(is_array($value)){
				$thumbnail_size	= $field['size']??'200x200';
				$value	= array_map(function($v) use ($thumbnail_size){ return  wpjam_get_thumbnail($v, $thumbnail_size); }, $value);
			}
		}elseif($field['type'] == 'mu-fields'){
			if($value && is_array($value)){

				foreach ($field['fields'] as $sub_key => $sub_field) {
					if($sub_field['type']	== 'img'){
						$thumbnail_size	= $sub_field['size']??'200x200';
						$item_type		= $sub_field['item_type']??'';
						array_walk($value, function(&$v) use ($sub_key, $thumbnail_size, $item_type){ 
							if($item_type == 'url'){
								$v[$sub_key] = wpjam_get_thumbnail($v[$sub_key], $thumbnail_size);
							}else{
								$v[$sub_key] = wpjam_get_thumbnail(wp_get_attachment_url($v[$sub_key]), $thumbnail_size);
							} 
						});
					}elseif($sub_field['type']	== 'mu-img'){
						$thumbnail_size	= $sub_field['size']??'200x200';
						$item_type		= $sub_field['item_type']??'';

						array_walk($value, function(&$v) use ($sub_key, $thumbnail_size, $item_type){ 
							if($item_type == 'url'){
								$v[$sub_key] = array_map(function($a_v) use ($thumbnail_size){ return  wpjam_get_thumbnail($a_v, $thumbnail_size); }, $v[$sub_key]);
							}else{
								$v[$sub_key] = array_map(function($a_v) use ($thumbnail_size){ return  wpjam_get_thumbnail(wp_get_attachment_url($a_v), $thumbnail_size); }, $v[$sub_key]);
							} 
						});

					}elseif($sub_field['type']	== 'file'){
						array_walk($value, function(&$v) use ($sub_key){ $v[$sub_key] = wpjam_get_thumbnail($v[$sub_key]); });
					}else{
						if(!empty($sub_field['data-type'])){
							$sub_field['data_type']	= $sub_field['data-type'];
						}

						if(!empty($sub_field['data_type'])){
							if($sub_field['data_type'] == 'post_type'){

								$thumbnail_size	= $sub_field['size']??'200x200';

								array_walk($value, function(&$v) use ($sub_key, $thumbnail_size){
									$basic			= true;
									$v[$sub_key]	= wpjam_get_post($v[$sub_key], compact('thumbnail_size', 'basic'));
								});
							}
						}
					}		
				}

				$value	= array_values($value);	// 去掉 0 1 2 key
			}else{
				$value	= array();
			}
		}elseif($field['type'] == 'mu-text') {
			$value	= array_filter($value);

			if(!empty($field['data-type'])){
				$field['data_type']	= $field['data-type'];
			}

			if(!empty($field['data_type'])){
				if($field['data_type'] == 'post_type'){
					$thumbnail_size	= $field['size']??'200x200';
					
					array_walk($value, function(&$v) use ($thumbnail_size){
						$basic	= true;
						$v		= wpjam_get_post($v, compact('thumbnail_size','basic'));
					});
				}
			}
		}else{
			if(!empty($field['data-type'])){
				$field['data_type']	= $field['data-type'];
			}

			if(!empty($field['data_type'])){
				if($field['data_type'] == 'video' || $field['data_type'] == 'qq-video' || $field['data_type'] == 'qq_video'){
					$value	= wpjam_get_video_mp4($value);
				}elseif($field['data_type'] == 'post_type'){
					$thumbnail_size	= $field['size']??'200x200';
					$value	= wpjam_get_post($value, compact($thumbnail_size));
				}
			}
		}

		return $value;
	}

	public static function get_field_post_ids($value, $field){
		if(empty($value)){
			return array();
		}

		if($field['type'] == 'img'){
			$item_type		= $field['item_type']??'';
			if($item_type != 'url'){
				return array('attachment'=>array($value));
			}
		}elseif($field['type'] == 'mu-img') {
			$item_type		= $field['item_type']??'';
			if(is_array($value) && $item_type != 'url'){
				return array('attachment'=>$value);
			}
		}elseif($field['type'] == 'mu-fields'){
			if($value && is_array($value)){
				foreach ($field['fields'] as $sub_key => $sub_field) {
					if(!empty($sub_field['data-type'])){
						$sub_field['data_type']	= $sub_field['data-type'];
					}

					if($sub_field['type']	== 'img'){
						return array('attachment'=>array_column($value, $sub_key));
					}elseif(!empty($sub_field['data_type'])){
						if($sub_field['data_type'] == 'post_type'){
							return array($sub_field['post_type']=>array_column($value, $sub_key));
						}
					}		
				}
			}
		}elseif($field['type'] == 'mu-text') {
			$value	= array_filter($value);

			if(!empty($field['data-type'])){
				$field['data_type']	= $field['data-type'];
			}

			if(!empty($field['data_type'])){
				if($field['data_type'] == 'post_type'){
					return array($field['post_type']=>$value);
				}
			}
		}else{
			if(!empty($field['data-type'])){
				$field['data_type']	= $field['data-type'];
			}

			if(!empty($field['data_type'])){
				if($field['data_type'] == 'post_type'){
					return array($field['post_type']=>array($value));
				}
			}
		}

		return array();
	}
}