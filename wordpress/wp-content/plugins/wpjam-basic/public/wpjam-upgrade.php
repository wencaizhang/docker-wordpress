<?php
function wpjam_basic_upgrade(){
	$current	= 3.73;
	$version	= wpjam_basic_get_setting('version') ?: 0;

	if($version >= $current){
		return;
	}

	add_filter('default_option_wpjam-basic', 'wpjam_basic_get_default_settings');
	
	wpjam_basic_update_setting('version', $current);
	
	if($version < 3.72){
		
		WPJAM_Message::create_table();

		$theme_switched	= get_option('theme_switched', null);
		if(is_null($theme_switched)){
			update_option('theme_switched', '');
		}

		if(is_multisite()){
			$db_locale	= get_option('WPLANG', null);

			if(is_null($db_locale)){
				$db_locale	= get_site_option('WPLANG');
				if($db_locale !== false){
					update_option('WPLANG', $db_locale);
				}
			}
		}
	}
}

wpjam_basic_upgrade();