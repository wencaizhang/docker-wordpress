<?php
/**
 * Easy FancyBox Class
 */

class easyFancyBox {

	private static $plugin_url;

	public static $plugin_basename;

	public static $plugin_dir;

	private static $inline_script;

	private static $inline_style;

	private static $inline_style_ie;

	public static $priority = 10;

	public static $onready_auto = false;

	public static $add_scripts = false;

	public static $options = array();

	public static $events = array( 'post-load' );

	/**********************
	   MAIN INLINE SCRIPT
	 **********************/

	private static function main() {

		// check for any enabled sections
		foreach ( self::$options['Global']['options']['Enable']['options'] as $value ) {
			if ( isset($value['id']) && '1' == get_option($value['id'],$value['default']) ) {
				self::$add_scripts = true;
				break;
			}
		}

		// and abort when none are active
		if ( !self::$add_scripts )
			return false;

		// begin building output FancyBox settings
		$script = 'var fb_timeout, fb_opts={';

		/*
		 * Global settings routine
		 */
		$more = 0;
		foreach (self::$options['Global']['options'] as $globals) {
			foreach ($globals['options'] as $_key => $_value) {
				if ( isset($_value['id']) )
					if ( isset($_value['default']) )
						$parm = get_option($_value['id'], $_value['default']);
					else
						$parm = get_option($_value['id']);
				elseif ( isset($_value['default']) )
					$parm = $_value['default'];
				else
					$parm = '';

				if ( isset($_value['input']) && 'checkbox'==$_value['input'] )
					$parm = ( '1' == $parm ) ? 'true' : 'false';

				if( !isset($_value['hide']) && $parm!='' ) {
					$quote = (is_numeric($parm) || (isset($_value['noquotes']) && $_value['noquotes'] == true) ) ? '' : '\'';
					if ($more>0)
						$script .= ',';
					$script .= '\''.$_key.'\':';
					$script .= $quote.$parm.$quote;
					$more++;
				} else {
					${$_key} = $parm;
				}
			}
		}

		$script .= ' };
if(typeof easy_fancybox_handler===\'undefined\'){
var easy_fancybox_handler=function(){
jQuery(\'.nofancybox,a.wp-block-file__button,a.pin-it-button,a[href*="pinterest.com/pin/create"],a[href*="facebook.com/share"],a[href*="twitter.com/share"]\').addClass(\'nolightbox\');';

		foreach (self::$options as $key => $value) {
			// check if not enabled or hide=true then skip
			if ( isset($value['hide']) || !get_option(self::$options['Global']['options']['Enable']['options'][$key]['id'], self::$options['Global']['options']['Enable']['options'][$key]['default']) )
				continue;

			$script .= '
/* ' . $key . ' */';

			/*
			 * Auto-detection routines (2x)
			 */
			$autoAttribute = isset($value['options']['autoAttribute']) ? get_option( $value['options']['autoAttribute']['id'], $value['options']['autoAttribute']['default'] ) : '';

			if ( !empty($autoAttribute) ) {
				if ( is_numeric($autoAttribute) ) {
					$script .= '
jQuery('.$value['options']['autoAttribute']['selector'].').not(\'.nolightbox,li.nolightbox>a\').addClass(\''.$value['options']['class']['default'].'\');';
				} else {
					// set selectors
					$file_types = array_filter( explode( ' ', str_replace( ',', ' ', $autoAttribute ) ) );
					$more = 0;
					$script .= '
var fb_'.$key.'_select=\'';
					foreach ( $file_types as $type ) {
						if ($type == "jpg" || $type == "jpeg" || $type == "png" || $type == "gif")
							$type = '.'.$type;
						if ($more>0)
							$script .= ',';
						$script .= 'a['.$value['options']['autoAttribute']['selector'].'"'.$type.'"]:not(.nolightbox,li.nolightbox>a),area['.$value['options']['autoAttribute']['selector'].'"'.$type.'"]:not(.nolightbox)';
						$more++;
					}
					$script .= '\';';

					$autoselector = class_exists('easyFancyBox_Advanced') ? get_option($value['options']['autoSelector']['id'],$value['options']['autoSelector']['default']) : $value['options']['autoSelector']['default'];

					// class and rel depending on settings
					if( '1' == get_option($value['options']['autoAttributeLimit']['id'],$value['options']['autoAttributeLimit']['default']) ) {
						// add class
						$script .= '
var fb_'.$key.'_sections=jQuery(\''.$autoselector.'\');
fb_'.$key.'_sections.each(function(){jQuery(this).find(fb_'.$key.'_select).addClass(\''.$value['options']['class']['default'].'\')';
						// and set rel
						switch( get_option($value['options']['autoGallery']['id'],$value['options']['autoGallery']['default']) ) {
							case '':
							default :
								$script .= ';});';
								break;

							case '1':
								$script .= '.attr(\'rel\',\'gallery-\'+fb_'.$key.'_sections.index(this));});';
								break;

							case '2':
								$script .= '.attr(\'rel\',\'gallery\');});';
								break;
						}
					} else {
						// add class
						$script .= '
jQuery(fb_'.$key.'_select).addClass(\''.$value['options']['class']['default'].'\')';
						// set rel
						switch( get_option($value['options']['autoGallery']['id'],$value['options']['autoGallery']['default']) ) {
							case '':
							default :
								$script .= ';';
								break;

							case '1':
								$script .= ';
var fb_'.$key.'_sections=jQuery(\''.$autoselector.'\');
fb_'.$key.'_sections.each(function(){jQuery(this).find(fb_'.$key.'_select).attr(\'rel\',\'gallery-\'+fb_'.$key.'_sections.index(this));});';
								break;

							case '2':
								$script .= '.attr(\'rel\',\'gallery\');';
								break;
						}
					}
				}
			}

			/*
			 * Generate .fancybox() bind
			 */

			// prepare auto popup
			if ( $key == $autoClick )
				$trigger = $value['options']['class']['default'];

			$script .= '
jQuery(\'' . $value['options']['tag']['default'] . '\')';

			// use each() to allow different metadata values per instance; fix by Elron. Thanks!
			$script .= '.each(function(){';

			// filter here
			$bind = 'jQuery(this).fancybox(jQuery.extend({},fb_opts,{';
			$more = 0;
			foreach ( $value['options'] as $_key => $_value ) {
				if ( isset($_value['id']) || isset($_value['default']) )
					$parm = isset($_value['id']) ? get_option($_value['id'], $_value['default']) : $_value['default'];
				else
					$parm = '';

				if ( isset($_value['input']) && 'checkbox'==$_value['input'] )
					$parm = ( '1' == $parm ) ? 'true' : 'false';

				if ( !isset($_value['hide']) && $parm!='' ) {
					$quote = ( is_numeric($parm) || ( isset($_value['noquotes']) && $_value['noquotes'] == true ) ) ? '' : '\'';
					if ( $more > 0 )
						$bind .= ',';
					$bind .= '\''.$_key.'\':';
					$bind .= $quote.$parm.$quote;
					$more++;
				}
			}
			$bind .= '}))';

			$script .= apply_filters( 'easy_fancybox_bind', $bind );

			$script .= '});';
		}

		$script .= '};
jQuery(\'a.fancybox-close\').on(\'click\',function(e){e.preventDefault();jQuery.fancybox.close()});
};';

		if ( empty($delayClick) ) $delayClick = '0';

		switch ( $autoClick ) {
			case '':
				break;

			case '1':
				$script .= '
var easy_fancybox_auto=function(){setTimeout(function(){jQuery(\'#fancybox-auto\').trigger(\'click\')},'.$delayClick.');};';
				self::$onready_auto = true;
				break;

			case '2':
				$script .= '
var easy_fancybox_auto=function(){setTimeout(function(){if(location.hash){jQuery(location.hash).trigger(\'click\');}},'.$delayClick.');};';
				self::$onready_auto = true;
				break;

			case '99':
				$script .= '
var easy_fancybox_auto=function(){setTimeout(function(){jQuery(\'a[class|="fancybox"]\').filter(\':first\').trigger(\'click\')},'.$delayClick.');};';
				self::$onready_auto = true;
				break;

			default :
				if ( !empty($trigger) ) {
					$script .= '
var easy_fancybox_auto=function(){setTimeout(function(){jQuery(\'a[class*="'.$trigger.'"]\').filter(\':first\').trigger(\'click\')},'.$delayClick.');};';
					self::$onready_auto = true;
				}
		}

		$script .= PHP_EOL;

		self::$inline_script = apply_filters( 'easy_fancybox_inline_script', $script );

		// HEADER STYLES //

		// customized styles
		$styles = '';
		if ( isset($overlaySpotlight) && 'true' == $overlaySpotlight )
			$styles .= '#fancybox-overlay{background-attachment:fixed;background-image:url("' . self::$plugin_url . 'images/light-mask.png");background-position:center;background-repeat:no-repeat;background-size:100% 100%}';

		if ( !empty($borderRadius) )
			$styles .= '#fancybox-outer,#fancybox-content{border-radius:'.$borderRadius.'px}.fancybox-title-inside{padding-top:'.$borderRadius.'px;margin-top:-'.$borderRadius.'px !important;border-radius: 0 0 '.$borderRadius.'px '.$borderRadius.'px}';

		$content_style = '';
		if ( !empty($backgroundColor) )
			$content_style .= 'background:'.$backgroundColor.';';

		if ( !empty($paddingColor) )
			$content_style .= 'border-color:'.$paddingColor.';';

		if ( !empty($textColor) ) {
			$content_style .= 'color:'.$textColor.';';
			$styles .= '#fancybox-outer{background:'.$paddingColor.'}'; //.fancybox-title-inside{background-color:'.$paddingColor.';margin-left:0 !important;margin-right:0 !important;width:100% !important;}
		}
		if ( !empty($content_style) )
			$styles .= '#fancybox-content{'.$content_style.'}';

		if ( !empty($titleColor) )
			$styles .= '#fancybox-title,#fancybox-title-float-main{color:'.$titleColor.'}';

		if ( !empty($styles) )
			self::$inline_style = $styles;

		// running our IE alphaimageloader relative path styles here
		if ( isset($compatIE8) && 'true' == $compatIE8 ) {
			self::$inline_style_ie = '/* IE6 */
.fancybox-ie6 #fancybox-close{background:transparent;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_close.png",sizingMethod="scale")}
.fancybox-ie6 #fancybox-left-ico{background:transparent;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_nav_left.png",sizingMethod="scale")}
.fancybox-ie6 #fancybox-right-ico{background:transparent;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_nav_right.png",sizingMethod="scale")}
.fancybox-ie6 #fancybox-title-over{background:transparent;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_title_over.png",sizingMethod="scale");zoom:1}
.fancybox-ie6 #fancybox-title-float-left{background:transparent;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_title_left.png",sizingMethod="scale")}
.fancybox-ie6 #fancybox-title-float-main{background:transparent;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_title_main.png",sizingMethod="scale")}
.fancybox-ie6 #fancybox-title-float-right{background:transparent;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_title_right.png",sizingMethod="scale")}
#fancybox-loading.fancybox-ie6 div{background:transparent;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_loading.png",sizingMethod="scale")}
/* IE6, IE7, IE8 */
.fancybox-ie #fancybox-title-over{background-image:url('.self::$plugin_url.'images/fancy_title_over.png)}
.fancybox-ie #fancybox-bg-n{filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_shadow_n.png",sizingMethod="scale")}
.fancybox-ie #fancybox-bg-ne{filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_shadow_ne.png",sizingMethod="scale")}
.fancybox-ie #fancybox-bg-e{filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_shadow_e.png",sizingMethod="scale")}
.fancybox-ie #fancybox-bg-se{filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_shadow_se.png",sizingMethod="scale")}
.fancybox-ie #fancybox-bg-s{filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_shadow_s.png",sizingMethod="scale")}
.fancybox-ie #fancybox-bg-sw{filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_shadow_sw.png",sizingMethod="scale")}
.fancybox-ie #fancybox-bg-w{filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_shadow_w.png",sizingMethod="scale")}
.fancybox-ie #fancybox-bg-nw{filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/fancy_shadow_nw.png",sizingMethod="scale")}';

			if ( isset($overlaySpotlight) && 'true' == $overlaySpotlight )
				self::$inline_style_ie .= '
#fancybox-overlay{filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'.self::$plugin_url.'images/light-mask.png",sizingMethod="scale")}';
		}

		return true;
	}

	/***********************
	    ACTIONS & FILTERS
	 ***********************/

	public static function enqueue_scripts() {

		// make sure whe actually need to do anything
		if ( !self::$add_scripts )
			return;

		global $wp_styles;
		$min = ( defined('WP_DEBUG') && WP_DEBUG ) ? '' : '.min';

		// ENQUEUE STYLE
		wp_enqueue_style( 'fancybox', self::$plugin_url.'css/jquery.fancybox.'.FANCYBOX_VERSION.$min.'.css', false, null, 'screen' );
		if ( !empty(self::$inline_style_ie) ) {
			wp_enqueue_style( 'fancybox-ie', self::$plugin_url.'css/jquery.fancybox-ie.'.FANCYBOX_VERSION.$min.'.css', false, null, 'screen' );
			$wp_styles->add_data( 'fancybox-ie', 'conditional', 'lt IE 9' );
		}

		// ENQUEUE SCRIPTS
		$dep = get_option( 'fancybox_nojQuery', false ) ? array() : array('jquery');
		$footer = get_option( 'fancybox_noFooter', false ) ? false : true;

		// register main fancybox script
		wp_enqueue_script( 'jquery-fancybox', self::$plugin_url.'js/jquery.fancybox.'.FANCYBOX_VERSION.$min.'.js', $dep, null, $footer );

		// jQuery Easing, which is ot needed if jQueryUI Core Effects are loaded
		if ( !wp_script_is( 'jquery-effects-core', 'enqueued' ) ) {
			$add_easing = false;
			// test for easing in IMG settings
			if ( get_option( 'fancybox_enableImg', self::$options['Global']['options']['Enable']['options']['IMG']['default'] )
				&& ( 'elastic' === get_option( 'fancybox_transitionIn', 'elastic' )
				|| 'elastic' === get_option( 'fancybox_transitionOut', 'elastic' ) ) )
				$add_easing = true;
			// test for easing in Inline settings
			if ( get_option( 'fancybox_enableInline', false )
				&& ( 'elastic' === get_option( 'fancybox_transitionInInline' )
				|| 'elastic' === get_option( 'fancybox_transitionOutInline' ) ) )
				$add_easing = true;
			// enqueue easing?
			if ( $add_easing ) {
				wp_enqueue_script( 'jquery-easing', self::$plugin_url.'js/jquery.easing.'.EASING_VERSION.$min.'.js', $dep, null, $footer );
			}
		}

		// jQuery Mousewheel, which is not needed if jQueryUI Mouse is loaded
		if ( get_option( 'fancybox_mouseWheel', true ) && !wp_script_is( 'jquery-ui-mouse', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery-mousewheel', self::$plugin_url.'js/jquery.mousewheel.'.MOUSEWHEEL_VERSION.$min.'.js', $dep, null, $footer );
		}

		// metadata in Miscellaneous settings?
		if ( get_option( 'fancybox_metaData' ) ) {
			wp_enqueue_script( 'jquery-metadata',self::$plugin_url.'js/jquery.metadata.'.METADATA_VERSION.$min.'.js', $dep, null, $footer );
		}

		if ( get_option( 'fancybox_pre45Compat', false ) || !function_exists( 'wp_add_inline_script' ) ) {
			// do it the old way
			if ( !empty(self::$inline_style) )
				add_action( 'wp_head', array(__CLASS__, 'print_inline_style'), 11 );
			if ( !empty(self::$inline_style_ie) )
				add_action( 'wp_head', array(__CLASS__, 'print_inline_style_ie'), 12 );
			if ( !empty(self::$inline_script) )
				add_action( $footer ? 'wp_footer' : 'wp_head', array(__CLASS__, 'print_inline_script'), self::$priority + 1 );
		} else {
			if ( !empty(self::$inline_style) )
				wp_add_inline_style( 'fancybox', self::$inline_style );
			if ( !empty(self::$inline_style_ie) )
				wp_add_inline_style( 'fancybox-ie', self::$inline_style_ie );
			if ( !empty(self::$inline_script) )
				wp_add_inline_script( 'jquery-fancybox', self::$inline_script );
		}

		do_action( 'easy_fancybox_enqueue_scripts', array($min,$dep,$footer) );
	}

	// fallback methods for WordPress pre-4.5
	public static function print_inline_script() {
		print( '<script type="text/javascript">' . self::$inline_script . '</script>' );
	}

	public static function print_inline_style() {
		print( '<style id="fancybox-inline-css" type="text/css">' . self::$inline_style . '</style>' );
	}

	public static function print_inline_style_ie() {
		print( '<!--[if lt IE 9]><style id="fancybox-inline-css-ie" type="text/css">' . self::$inline_style_ie . '</style><![endif]-->' );
	}

	// Hack to fix missing wmode in Youtube oEmbed code based on David C's code in the comments on
	// http://www.mehigh.biz/wordpress/adding-wmode-transparent-to-wordpress-3-media-embeds.html
	// without the wmode, videos will float over the light box no matter what z-index is set.
	public static function add_video_wmode_opaque($html) {
		if ( strpos($html, "<embed src=" ) !== false ) {
			$html = str_replace('</param><embed', '</param><param name="wmode" value="opaque"></param><embed wmode="opaque"', $html);
		} elseif ( strpos($html, 'youtube' ) !== false && strpos($html, 'wmode' ) == false ) {
			$html = str_replace('feature=oembed', 'feature=oembed&amp;wmode=opaque', $html);
		} elseif ( strpos($html, "vimeo" ) !== false  && strpos($html, 'wmode' ) == false ) {
			$html = str_replace('" width', '?theme=none&amp;wmode=opaque" width', $html);
		} elseif ( strpos($html, "dailymotion" ) !== false  && strpos($html, 'wmode' ) == false ) {
			$html = str_replace('" width', '?wmode=opaque" width', $html);
		}
		return $html;
	}

	public static function onready_callback( $content ) {

		$content .= 'jQuery(easy_fancybox_handler);jQuery(document).on(\'' . implode(" ", self::$events) . '\',easy_fancybox_handler);' . PHP_EOL;

		if ( self::$onready_auto )
			$content .=	apply_filters( 'easy_fancybox_onready_auto', 'jQuery(easy_fancybox_auto);' );

		return $content;
	}

	public static function upgrade( $old_version ) {
		if ( !$old_version ) { // upgrade from 1.7 or older
			if ( 'html' === get_option('fancybox_PDFclassType') ) {
				update_option('fancybox_PDFonStart', 'function(selectedArray,selectedIndex,selectedOpts){selectedOpts.type=\'pdf\';}');
				delete_option('fancybox_PDFclassType');
			}
		}
		// mark upgrade done
		update_option('easy_fancybox_version', EASY_FANCYBOX_VERSION);
	}

	public static function load_defaults() {
		if ( empty(self::$options) ) {
			include self::$plugin_dir . '/inc/easyfancybox-options.php';
			self::$options = $efb_options;
		}
	}

	public static function maybe_upgrade() {
		$version = get_option('easy_fancybox_version', 0);

		if ( version_compare( EASY_FANCYBOX_VERSION, $version, '>' ) )
			self::upgrade($version);
	}

	public static function load_main() {
		// Treat settings and prepare inline scripts and styles, or log debug message
		if ( self::main() ) {
			$priority = get_option( 'fancybox_scriptPriority' );
			if ( is_numeric($priority) ) self::$priority = $priority;

			add_action( 'wp_enqueue_scripts', array(__CLASS__,'enqueue_scripts'), self::$priority );
			add_filter( 'embed_oembed_html', array(__CLASS__,'add_video_wmode_opaque'), 10 );
		} elseif ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log('No active Easy FancyBox media types set.');
		}
	}

	/**********************
	         RUN
	 **********************/

	public function __construct( $file ) {
		// VARS
		self::$plugin_url = plugins_url( '/', $file );
		self::$plugin_basename = plugin_basename( $file );
		self::$plugin_dir = dirname( $file );

		add_action( 'init', array(__CLASS__, 'maybe_upgrade') );
		add_action( 'init', array(__CLASS__, 'load_defaults') );
		add_action( 'init', array(__CLASS__, 'load_main'), 12 );

		add_filter( 'easy_fancybox_inline_script', array(__CLASS__,'onready_callback') );
	}
}
