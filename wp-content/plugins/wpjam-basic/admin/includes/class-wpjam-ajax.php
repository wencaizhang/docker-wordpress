<?php
class WPJAM_AJAX{
	public static function ajax_response(){
		global $plugin_page;

		$action	= $_POST['page_action'];
		$nonce	= $_POST['_ajax_nonce'];

		if(!wp_verify_nonce($nonce, $plugin_page.'-'.$action)){
			wpjam_send_json([
				'errcode'	=> 'invalid_nonce',
				'errmsg'	=> '非法操作'
			]);
		}

		$ajax_response	= wpjam_get_filter_name($plugin_page, 'ajax_response');
		$ajax_response	= apply_filters('wpjam_page_ajax_response', $ajax_response, $plugin_page);

		if(function_exists($ajax_response)){
			call_user_func($ajax_response);
		}else{
			wpjam_send_json([
				'errcode'	=> 'invalid_ajax_response',
				'errmsg'	=> '非法回调函数'
			]);
		}
	}

	public static function get_button($args){
		global $plugin_page;

		extract(wp_parse_args($args, [
			'fields'		=> [],
			'data'			=> [],
			'action'		=> '',
			'direct'		=> '',
			'confirm'		=> '',
			'button_text'	=> '保存',
			'page_title'	=> '',
			'class'			=> 'button-primary large'
		]));

		$data		= $data?http_build_query($data):'';
		$page_title	= $page_title?:$button_text;

		return '<a href="javascript:;" style="text-decoration:none;" id="wpjam_button_'.$action.'" class="'.$class.' wpjam-button" data-action="'.$action.'" data-data="'.$data.'" data-nonce="'.wp_create_nonce($plugin_page.'-'.$action).'" data-direct="'.$direct.'" data-confirm="'.$confirm.'"  data-title="'.$page_title.'">'.$button_text.'</a>';
	}

	public static function form($args){
		global $plugin_page;

		extract(wp_parse_args($args, [
			'data_type'		=> 'form',
			'fields_type'	=> 'table',
			'fields'		=> [],
			'data'			=> [],
			'bulk'			=> false,
			'ids'			=> [],
			'id'			=> '',
			'action'		=> '',
			'submit_text'	=> '',
			'nonce'			=> '',
			'form_id'		=> 'wpjam_form',
			'notice_class'	=> '',
			'submit_class'	=> 'button-primary large'
		]));

		if(empty($action)){
			return;
		}

		$nonce	= $nonce ?: wp_create_nonce($plugin_page.'-'.$action);

		if($fields){
			echo '<div class="'.$notice_class.' notice inline is-dismissible" style="display:none; margin:5px 0px 2px;"></div>';

			if($bulk){
				$ids	= $ids ? http_build_query($ids) : '';
				echo '<form method="post" action="#" id="'.$form_id.'" data-bulk="'.$bulk.'" data-ids="'.$ids.'" data-action="'.$action.'" data-nonce="'.$nonce.'" data-title="'.$submit_text.'">';
			}else{
				echo '<form method="post" action="#" id="'.$form_id.'" data-id="'.$id.'" data-action="'.$action.'" data-nonce="'.$nonce.'" data-title="'.$submit_text.'">';
			}
			
			WPJAM_Form::fields_callback($fields, compact('data','data_type','id'));
		}

		if($submit_text){
			echo '<p class="submit"><input type="submit" class="'.$submit_class.'" value="'.$submit_text.'"> <span class="spinner"  style="float: none; height: 28px;"></span></p>';
		}

		echo '<div class="card response" style="display:none;"></div>';

		if($fields){
			echo '</form>';
		}
	}
}


