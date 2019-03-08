<?php
/**
 * Easy FancyBox Admin Class
 */
class easyFancyBox_Admin extends easyFancyBox {

	public static $pagehook;

	public static $compat_pro_min = '1.5.3';

	public static $do_compat_warning = false;

	/***********************
	     ADMIN FUNCTIONS
	 ***********************/

	public static function add_settings_section() {
 		add_settings_section('fancybox_section', __('FancyBox','easy-fancybox'), array(__CLASS__, 'settings_section'), 'media');
 	}

	public static function register_settings( $args = array() ) {
		if ( empty( $args ) ) $args = parent::$options;
		foreach ($args as $key => $value) {
			// check to see if the section is enabled, else skip to next
			if ( !isset($value['input'])
				|| array_key_exists($key, parent::$options['Global']['options']['Enable']['options'])
				&& !get_option( parent::$options['Global']['options']['Enable']['options'][$key]['id'], parent::$options['Global']['options']['Enable']['options'][$key]['default'])
				) continue;

			switch($value['input']) {
				case 'deep':
				// go deeper by looping back on itself
				self::register_settings($value['options']);
				break;

				case 'multiple':
				add_settings_field( 'fancybox_'.$key, '<a name="'.$value['title'].'"></a>'.$value['title'], array(__CLASS__, 'settings_fields'), 'media', 'fancybox_section', $value);
				foreach ( $value['options'] as $_value ) {
					if ( !isset($_value['sanitize_callback']) )
						$sanitize_callback = '';
					else
						$sanitize_callback = array(__CLASS__, $_value['sanitize_callback']);
					if ( isset($_value['id']) )
						register_setting( 'media', $_value['id'], $sanitize_callback );
				}
				break;

				default:
				if ( !isset($value['sanitize_callback']) )
					$sanitize_callback = '';
				else
					$sanitize_callback = array(__CLASS__, $value['sanitize_callback']);
				if ( isset($value['id']) )
					register_setting( 'media', 'fancybox_'.$key, $sanitize_callback );
			}
		}
	}

	// add our FancyBox Media Settings Section on Settings > Media admin page
	public static function settings_section() {
		echo '<style type="text/css">.options-media-php br { display: initial; }</style><!-- undo WP style rule introduced in 4.9 on settings-media -->
		<p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=Easy%20FancyBox&item_number='.EASY_FANCYBOX_VERSION.'&no_shipping=0&tax=0&charset=UTF%2d8&currency_code=EUR" title="'.__('Donate to keep the Easy FancyBox plugin development going!','easy-fancybox').'">
		<img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" style="border:none;float:right;margin:5px 0 0 10px" alt="'.__('Donate to keep the Easy FancyBox plugin development going!','easy-fancybox').'" width="92" height="26" /></a>';
		echo sprintf(__('The options in this section are provided by the plugin %s and determine the <strong>Media Lightbox</strong> overlay appearance and behavior controlled by %s.','easy-fancybox'),'<strong><a href="http://status301.net/wordpress-plugins/easy-fancybox/">'.__('Easy FancyBox','easy-fancybox').'</a></strong>','<strong><a href="http://fancybox.net/">'.__('FancyBox','easy-fancybox').'</a></strong>');
		echo '</p><p>'.__('First enable each sub-section that you need. Then save and come back to adjust its specific settings.','easy-fancybox').' '.__('Note: Each additional sub-section and features like <em>Auto-detection</em>, <em>Elastic transitions</em> and all <em>Easing effects</em> (except Swing) will have some extra impact on client-side page speed. Enable only those sub-sections and options that you actually need on your site.','easy-fancybox').' '.__('Some setting like Transition options are unavailable for SWF video, PDF and iFrame content to ensure browser compatibility and readability.','easy-fancybox').'</p>';

		/* Black Friday offer */
		if ( !class_exists('easyFancyBox_Advanced')
			&& current_user_can( 'install_plugins' )
			&& strtotime('now') > strtotime('23-11-2018')
			&& strtotime('now') < strtotime('27-11-2018') ) {
			echo '<p style="background-color:#F9D400;padding:5px 10px;border-radius:15px;box-shadow:#333 0 1px 1px;color:#000;">
				<strong>Easy FancyBox advanced options at 30% OFF!</strong>
				Black Friday to Cyber Monday: THE BIG 30 SALE at Status301.
				<em>A whopping 30% discount but only for the first 30 customers.
				After that, there will still be a discount of 15% for everybody until tuesday 0:00 GMT so
				<strong><a href="https://premium.status301.net/black-friday-til-cyber-monday-big-30-sale/?discount=BFCM30" target="_blank">to take advantage of this opportunity</a></strong>
				before it\'s too late</em>...</p>';
		}

		// Pro extension version compatibility message
		if ( self::$do_compat_warning ) {
			echo '<p class="update-nag">';
			_e('Notice: The current Easy FancyBox plugin version is not fully compatible with your version of the Pro extension. Some advanced options may not be functional.','easy-fancybox');
			echo ' ';
			if ( current_user_can( 'install_plugins' ) )
				printf(__('Please <a href="%1$s" target="_blank">download and install the latest Pro version</a>.','easy-fancybox'), 'https://premium.status301.net/account/');
			else
				_e('Please contact your web site administrator.','easy-fancybox');
			echo '</p>';
		}
	}

	// add our FancyBox Media Settings Fields
	public static function settings_fields($args){
		$disabled = (isset($args['status']) && 'disabled' == $args['status']) ? ' disabled="disabled"' : '';
		if (isset($args['input']))
			switch($args['input']) {
				case 'multiple':
				case 'deep':
					foreach ($args['options'] as $options)
						self::settings_fields($options);
					if (isset($args['description'])) echo $args['description'];
					break;
				case 'select':
					if( !empty($args['label_for']) )
						echo '<label for="'.$args['label_for'].'">'.$args['title'].'</label> ';
					else
						echo $args['title'];
					echo '
					<select name="'.$args['id'].'" id="'.$args['id'].'">';
					foreach ($args['options'] as $optionkey => $optionvalue) {
						$selected = (get_option($args['id'], $args['default']) == $optionkey) ? ' selected="selected"' : '';
						echo '
						<option value="'.esc_attr($optionkey).'"'.$selected.' '.$disabled.' >'.$optionvalue.'</option>';
					}
					echo '
					</select> ';
					if( empty($args['label_for']) )
						echo '<label for="'.$args['id'].'">'.$args['description'].'</label> ';
					else
						if (isset($args['description'])) echo $args['description'];
					break;
				case 'checkbox':
					if( !empty($args['label_for']) )
						echo '<label for="'.$args['label_for'].'">'.$args['title'].'</label> ';
					else
						if (isset($args['title'])) echo $args['title'];
					$value = esc_attr( get_option($args['id'], $args['default']) );
					if ($value == "1")
						$checked = ' checked="checked"';
					else
						$checked = '';
					if ($args['default'] == "1")
						$default = __('Checked','easy-fancybox');
					else
						$default = __('Unchecked','easy-fancybox');
					if( empty($args['label_for']) )
						echo '
					<label><input type="checkbox" name="'.$args['id'].'" id="'.$args['id'].'" value="1" '.$checked.' '.$disabled.' /> '.$args['description'].'</label><br />';
					else
						echo '
					<input type="checkbox" name="'.$args['id'].'" id="'.$args['id'].'" value="1" '.$checked.' '.$disabled.' /> '.$args['description'].'<br />';
					break;
				case 'text':
				case 'color': // TODO make color picker available for color values but do NOT use type="color" because that does not allow empty fields!
					if( !empty($args['label_for']) )
						echo '<label for="'.$args['label_for'].'">'.$args['title'].'</label> ';
					else
						echo $args['title'];
					echo '
					<input type="text" name="'.$args['id'].'" id="'.$args['id'].'" value="'.esc_attr( get_option($args['id'], $args['default']) ).'" class="'.$args['class'].'"'.$disabled.' /> ';
					if( empty($args['label_for']) )
						echo '<label for="'.$args['id'].'">'.$args['description'].'</label> ';
					else
						if (isset($args['description'])) echo $args['description'];
					break;
				case 'number':
					if( !empty($args['label_for']) )
						echo '<label for="'.$args['label_for'].'">'.$args['title'].'</label> ';
					else
						echo $args['title'];
					echo '
					<input type="number" step="'.$args['step'].'" min="'.$args['min'].'" max="'.$args['max'].'" name="'.$args['id'].'" id="'.$args['id'].'" value="'.esc_attr( get_option($args['id'], $args['default']) ).'" class="'.$args['class'].'"'.$disabled.' /> ';
					if( empty($args['label_for']) )
						echo '<label for="'.$args['id'].'">'.$args['description'].'</label> ';
					else
						if (isset($args['description'])) echo $args['description'];
					break;
				case 'hidden':
					echo '
					<input type="hidden" name="'.$args['id'].'" id="'.$args['id'].'" value="'.esc_attr( get_option($args['id'], $args['default']) ).'" /> ';
					break;
				default:
					if (isset($args['description'])) echo $args['description'];
			}
		else
			if (isset($args['description'])) echo $args['description'];
	}

	/**
	 * Adds an action link to the Plugins page
	 */
	public static function add_action_link( $links ) {
		$settings_link = '<a href="' . admin_url('options-media.php') . '">' . translate('Settings') . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/***
	 * Santize Callbacks
	 */

	public static function intval($setting = '') {
		if ($setting == '')
			return '';

		if (substr($setting, -1) == '%') {
			$val = intval(substr($setting, 0, -1));
			$prc = '%';
		} else {
			$val = intval($setting);
			$prc = '';
		}

		return ( $val != 0 ) ? $val.$prc : 0;
	}

	public static function colorval($setting = '') {
		if ($setting == '')
			return '';

		if (substr($setting, 0, 1) == '#')
			if ( ctype_xdigit(substr($setting, 1)) )
				return $setting;

		if (ctype_xdigit($setting))
				return '#'.$setting;

		return $setting;
	}

	/***********************
	    ACTIONS & FILTERS
	 ***********************/

	public static function admin_notice() {
		/* Version Nag */
		if ( self::$do_compat_warning && current_user_can( 'install_plugins' ) && !get_user_meta($current_user->ID, 'easy_fancybox_ignore_notice') ) {
			echo '<div class="update-nag"><p>';
			//echo '<a href="?easy_fancybox_ignore_notice=1" title="' . __('Hide message','easy-fancybox') . '" style="display:block;float:right">X</a>';
			_e('Notice: The current Easy FancyBox plugin version is not fully compatible with your version of the Pro extension. Some advanced options may not be functional.','easy-fancybox');
			echo '<br />';
			printf(__('Please <a href="%1$s" target="_blank">download and install the latest Pro version</a>.','easy-fancybox'), 'https://premium.status301.net/account/');
			echo ' ';
			printf(__('Or you can ignore and <a href="%1$s">hide this message</a>.','easy-fancybox'), '?easy_fancybox_ignore_notice=1');
			echo '</p></div>';
		}
	}

	public static function load_textdomain() {
		load_plugin_textdomain('easy-fancybox', false, dirname( parent::$plugin_basename ) . '/languages' );
	}

	public static function admin_init() {
		/* Dismissable notice */
		/* If user clicks to ignore the notice, add that to their user meta */
		global $current_user;

		if ( isset($_GET['easy_fancybox_ignore_notice']) && '1' == $_GET['easy_fancybox_ignore_notice'] )
			add_user_meta($current_user->ID, 'easy_fancybox_ignore_notice', 'true', true);

		if ( class_exists('easyFancyBox_Advanced')
				&& ( !defined('easyFancyBox_Advanced::VERSION')
				|| version_compare(easyFancyBox_Advanced::VERSION, self::$compat_pro_min, '<') ) )
			self::$do_compat_warning = true;
	}

	/**********************
	         RUN
	 **********************/

	public function __construct() {
		add_action('plugins_loaded', array(__CLASS__, 'load_textdomain'));
		add_action('admin_notices', array(__CLASS__, 'admin_notice'));
		add_filter('plugin_action_links_'.parent::$plugin_basename, array(__CLASS__, 'add_action_link') );

		// in preparation of dedicated admin page move:
		//add_action('admin_menu', array(__CLASS__, 'add_menu'));

		add_action('admin_init', array(__CLASS__, 'add_settings_section'));
		add_action('admin_init', array(__CLASS__, 'register_settings'));
		add_action('admin_init', array(__CLASS__, 'admin_init'));
	}
}
