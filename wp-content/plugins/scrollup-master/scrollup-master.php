<?php
/**
 * Plugin Name:       Scroll Back To Top Button
 * Plugin URI:        http://wordpress.org/plugins/scrollup-master/
 * Description:       A simple WordPress plugin to create scroll back to top button.
 * Version:           2.9.0
 * Author:            Sayful Islam
 * Author URI:        https://sayfulislam.com/
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! class_exists( 'Scrollup_Master' ) ) {

	class Scrollup_Master {

		/**
		 * @var object
		 */
		protected static $instance;

		/**
		 * @return Scrollup_Master
		 */
		public static function instance() {
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Scrollup_Master constructor.
		 */
		public function __construct() {
			add_action( 'wp_head', array( $this, 'inline_styles' ), 8 );
			add_action( 'wp_footer', array( $this, 'scrollup_icon' ), 1 );
			add_action( 'wp_footer', array( $this, 'inline_scripts' ), 60 );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'admin_init', array( $this, 'settings_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		/**
		 * Admin script
		 */
		public function admin_scripts() {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}

		/**
		 * Plugin inline style
		 */
		public function inline_styles() {
			$options        = self::get_options();
			$btn_bottom     = absint( $options['btn_bottom'] ) . 'px';
			$btn_right      = absint( $options['btn_right'] ) . 'px';
			$color          = esc_attr( $options['color'] );
			$bg_color       = esc_attr( $options['bg_color'] );
			$hover_color    = esc_attr( $options['hover_color'] );
			$bg_hover_color = esc_attr( $options['bg_hover_color'] );
			if ( esc_attr( $options['button_type'] ) == 'circle' ) {
				$border_radius = '32px';
			} else {
				$border_radius = '0';
			}
			?>
            <style type="text/css">
                .scrollup-button {
                    display: none;
                    position: fixed;
                    z-index: 1000;
                    padding: 8px;
                    cursor: pointer;
                    bottom: <?php echo $btn_bottom; ?>;
                    right: <?php echo $btn_right; ?>;
                    background-color: <?php echo $bg_color; ?>;
                    border-radius: <?php echo $border_radius; ?>;
                    -webkit-animation: display 0.5s;
                    animation: display 0.5s;
                }

                .scrollup-button .scrollup-svg-icon {
                    display: block;
                    overflow: hidden;
                    fill: <?php echo $color; ?>;
                }

                .scrollup-button:hover {
                    background-color: <?php echo $bg_hover_color; ?>;
                }

                .scrollup-button:hover .scrollup-svg-icon {
                    fill: <?php echo $hover_color; ?>;
                }
            </style>
			<?php
		}

		/**
		 * Scroll to top icon
		 */
		public function scrollup_icon() {
			$options   = self::get_options();
			$icon_type = esc_attr( $options['icon_type'] );
			$title     = esc_attr( $options['tooltiptitle'] );
			$_distance = absint( $options['scrolldistance'] );

			$all_themes = array( 'arrow-up', 'angle-up', 'angle-double-up', 'chevron-up', 'long-arrow-up' );
			$theme      = in_array( $icon_type, $all_themes ) ? $icon_type : 'arrow-up';

			?>
            <span id="scrollup-master" class="scrollup-button" title="<?php echo $title; ?>"
                  data-distance="<?php echo $_distance; ?>"
            >
			<?php if ( $theme == 'arrow-up' ): ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="scrollup-svg-icon" width="32" height="32"
                     viewBox="0 0 24 24"><path
                            d="M12 2q0.4 0 0.7 0.3l7 7q0.3 0.3 0.3 0.7 0 0.4-0.3 0.7t-0.7 0.3q-0.4 0-0.7-0.3l-5.3-5.3v15.6q0 0.4-0.3 0.7t-0.7 0.3-0.7-0.3-0.3-0.7v-15.6l-5.3 5.3q-0.3 0.3-0.7 0.3-0.4 0-0.7-0.3t-0.3-0.7q0-0.4 0.3-0.7l7-7q0.3-0.3 0.7-0.3z"></path></svg>
			<?php elseif ( $theme == 'angle-up' ): ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="scrollup-svg-icon" width="32" height="32"
                     viewBox="0 0 18 28"><path
                            d="M16.8 18.5c0 0.1-0.1 0.3-0.2 0.4l-0.8 0.8c-0.1 0.1-0.2 0.2-0.4 0.2-0.1 0-0.3-0.1-0.4-0.2l-6.1-6.1-6.1 6.1c-0.1 0.1-0.2 0.2-0.4 0.2s-0.3-0.1-0.4-0.2l-0.8-0.8c-0.1-0.1-0.2-0.2-0.2-0.4s0.1-0.3 0.2-0.4l7.3-7.3c0.1-0.1 0.2-0.2 0.4-0.2s0.3 0.1 0.4 0.2l7.3 7.3c0.1 0.1 0.2 0.2 0.2 0.4z"></path></svg>
			<?php elseif ( $theme == 'angle-double-up' ): ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="scrollup-svg-icon" width="32" height="32"
                     viewBox="0 0 18 28"><path
                            d="M16.8 20.5c0 0.1-0.1 0.3-0.2 0.4l-0.8 0.8c-0.1 0.1-0.2 0.2-0.4 0.2-0.1 0-0.3-0.1-0.4-0.2l-6.1-6.1-6.1 6.1c-0.1 0.1-0.2 0.2-0.4 0.2s-0.3-0.1-0.4-0.2l-0.8-0.8c-0.1-0.1-0.2-0.2-0.2-0.4s0.1-0.3 0.2-0.4l7.3-7.3c0.1-0.1 0.2-0.2 0.4-0.2s0.3 0.1 0.4 0.2l7.3 7.3c0.1 0.1 0.2 0.2 0.2 0.4zM16.8 14.5c0 0.1-0.1 0.3-0.2 0.4l-0.8 0.8c-0.1 0.1-0.2 0.2-0.4 0.2-0.1 0-0.3-0.1-0.4-0.2l-6.1-6.1-6.1 6.1c-0.1 0.1-0.2 0.2-0.4 0.2s-0.3-0.1-0.4-0.2l-0.8-0.8c-0.1-0.1-0.2-0.2-0.2-0.4s0.1-0.3 0.2-0.4l7.3-7.3c0.1-0.1 0.2-0.2 0.4-0.2s0.3 0.1 0.4 0.2l7.3 7.3c0.1 0.1 0.2 0.2 0.2 0.4z"></path></svg>
			<?php elseif ( $theme == 'chevron-up' ): ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="scrollup-svg-icon" width="32" height="32"
                     viewBox="0 0 28 28"><path
                            d="M26.3 20.8l-2.6 2.6c-0.4 0.4-1 0.4-1.4 0l-8.3-8.3-8.3 8.3c-0.4 0.4-1 0.4-1.4 0l-2.6-2.6c-0.4-0.4-0.4-1 0-1.4l11.6-11.6c0.4-0.4 1-0.4 1.4 0l11.6 11.6c0.4 0.4 0.4 1 0 1.4z"></path></svg>
			<?php elseif ( $theme == 'long-arrow-up' ): ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="scrollup-svg-icon" width="32" height="32"
                     viewBox="0 0 12 28"><path
                            d="M12 7.7c-0.1 0.2-0.2 0.3-0.5 0.3h-3.5v19.5c0 0.3-0.2 0.5-0.5 0.5h-3c-0.3 0-0.5-0.2-0.5-0.5v-19.5h-3.5c-0.2 0-0.4-0.1-0.5-0.3s0-0.4 0.1-0.5l5.5-6c0.1-0.1 0.2-0.2 0.4-0.2v0c0.1 0 0.3 0.1 0.4 0.2l5.5 6c0.1 0.2 0.2 0.4 0.1 0.5z"></path></svg>
			<?php endif; ?>
            </span>
			<?php
		}

		/**
		 * plugin script
		 */
		public function inline_scripts() {
			ob_start();
			include_once dirname( __FILE__ ) . '/assets/js/script.min.js';
			$script = ob_get_clean(); ?>
            <script type='text/javascript'>
				<?php echo wp_strip_all_tags( $script ) . PHP_EOL; ?>
            </script>
            <?php
		}

		/**
		 * Get plugin options
		 *
		 * @return array
		 */
		public static function get_options() {
			$options_array = array(
				'btn_bottom'     => '20',
				'btn_right'      => '20',
				'scrolldistance' => '300',
				'tooltiptitle'   => 'Scroll Back to Top',
				'icon_type'      => 'arrow-up',
				'button_type'    => 'square', // Circle
				'color'          => '#ffffff',
				'hover_color'    => '#ffffff',
				'bg_color'       => '#494949',
				'bg_hover_color' => '#494949'
			);
			$options       = wp_parse_args( get_option( 'sis_scrooltotop_settings' ), $options_array );

			return $options;
		}

		/**
		 * Register setting page
		 */
		public function settings_init() {
			register_setting(
				'sis_scrooltotop_settings',
				'sis_scrooltotop_settings',
				array( $this, 'sanitize' )
			);
		}

		/**
		 * Sanitize options
		 *
		 * @param $input
		 *
		 * @return mixed
		 */
		public function sanitize( $input ) {
			$input['btn_bottom']     = absint( $input['btn_bottom'] );
			$input['btn_right']      = absint( $input['btn_right'] );
			$input['scrolldistance'] = absint( $input['scrolldistance'] );
			$input['tooltiptitle']   = sanitize_text_field( $input['tooltiptitle'] );
			$input['icon_type']      = sanitize_text_field( $input['icon_type'] );
			$input['color']          = $this->sanitize_hex_color( $input['color'] );
			$input['hover_color']    = $this->sanitize_hex_color( $input['hover_color'] );
			$input['bg_color']       = $this->sanitize_hex_color( $input['bg_color'] );
			$input['bg_hover_color'] = $this->sanitize_hex_color( $input['bg_hover_color'] );

			return $input;
		}

		/**
		 * Sanitizes a hex color.
		 *
		 * @param $color
		 *
		 * @return string
		 */
		private function sanitize_hex_color( $color ) {
			if ( function_exists( 'sanitize_hex_color' ) ) {
				return sanitize_hex_color( $color );
			}

			if ( '' === $color ) {
				return '';
			}

			// 3 or 6 hex digits, or the empty string.
			if ( preg_match( '/#([a-f0-9]{3}){1,2}\b/i', $color ) ) {
				return $color;
			}

			return '';
		}

		/**
		 * Add options page
		 */
		public function admin_menu() {
			add_options_page(
				'Scroll to Top',
				'Scroll to Top',
				'manage_options',
				'scrollup-master',
				array( $this, 'options_page_callback' )
			);
		}

		/**
		 * Option page callback
		 */
		public function options_page_callback() {
			include_once dirname( __FILE__ ) . '/options.php';
		}
	}
}

add_action( 'plugins_loaded', array( 'Scrollup_Master', 'instance' ) );
