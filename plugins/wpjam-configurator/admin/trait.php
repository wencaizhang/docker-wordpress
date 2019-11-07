<?php
trait WPJAM_Configurator {
	public static function get_post_type_options(){
		$options	= wp_list_pluck(get_post_types(['show_ui' => true], 'objects'), 'label', 'name');
		
		unset($options['attachment']);
		unset($options['wp_block']);

		$post_types_settings	= get_option('wpjam_post_types');

		if(isset($post_types_settings['post']) && empty($post_types_settings['post']['active'])){
			unset($options['post']);
		}

		if(isset($post_types_settings['page']) && empty($post_types_settings['page']['active'])){
			unset($options['page']);
		}

		return $options;
	}

	public static function get_taxonomy_options(){
		$options	= wp_list_pluck(get_taxonomies(['show_ui'=>true],'objects'), 'label', 'name');

		unset($options['link_category']);
		unset($options['collection']);

		return $options;
	}

	public static function get_items_table($items, $id){
		if(empty($items)){
			return '';
		}

		$table	= '<table>';
		$table	.= '<tbody>';
		foreach ($items as $i=>$item) {

			if(!isset($item['detail'])){
				$item['key']	= $i;
				$row_actions	= false;
			}else{
				$row_actions	= true;
			}

			if(empty($item['key'])){
				continue;
			}

			$table	.= '<tr>';
			$table	.= '<td class="field-key">'.$item['key'].'</td>';

			if($row_actions){
				$table	.= '<td><div class="row-actions">';

				$table	.= wpjam_get_list_table_row_action('edit_field',[
					'id'	=> $id,
					'data'	=> ['i'=>$i],
					'title'	=>'修改',
				]);

				$table	.= ' | ';

				$table	.= '<span class="delete">'.wpjam_get_list_table_row_action('delete_field',[
					'id'	=> $id,
					'data'	=> ['i'=>$i],
					'title'	=>'删除',
				]).'</span>';

				$table	.= '</div></td>';
			}
				
			$table	.= '</tr>';
		}

		$table	.= '</tbody>';
		$table	.= '</table>';

		return $table;
	}

	public static function get_source_field(){
		return ['title'=>'来源',		'type'=>'view',		'show_admin_column'=>'only',	'options'=>['system'=>'系统自带', 'code'=>'代码生成',	'configurator'=>'配置器生成']];
	}

	public static function get_count_field(){
		return ['title'=>'数量',		'type'=>'view',		'show_admin_column'=>'only'];
	}

	public static function get_field_usage_description(){
		return '<a href="https://blog.wpjam.com/m/wpjam-configurator-fields/" style="text-decoration:none;" target="_blank">配置器字段使用说明</a>';
	}

	public static function get_api_module_usage_description(){
		return '<a href="https://blog.wpjam.com/m/wpjam-configurator-api-modules/" style="text-decoration:none;" target="_blank">配置器接口模块使用说明</a>';
	}
}