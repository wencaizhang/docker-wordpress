<?php
add_filter('wpjam_default_avatar_data', function($args, $user_id){
	if($args['found_avatar']){
		return $args;
	}

	$defaults	= wpjam_get_setting('wpjam-avatar', 'defaults');

	if($defaults){
		$i	= $user_id  % count($defaults);

		$priority	= wpjam_get_setting('wpjam-avatar', 'priority');

		if($priority == 'gravatar'){
			$args['default']		= wpjam_get_thumbnail($defaults[$i]);
		}else{
			$args['found_avatar']	= true;	
			$args['url']			= wpjam_get_thumbnail($defaults[$i], [$args['width'],$args['height']]);
		}
	}
	
	return $args;
}, 10, 3);