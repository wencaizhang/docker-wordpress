<?php
/**
* Easy FancyBox options and their defaults array
*/
$efb_url = "https://premium.status301.net/downloads/easy-fancybox-pro/";

$efb_options = array (
	'Global' => array(
		'title' => __('Global settings','easy-fancybox'),
		'input' => 'deep',
		'hide' => true,
		'options' => array(
			'Enable' => array (
				'title' => __('Media','easy-fancybox'),
				'input' => 'multiple',
				'hide' => true,
				'options' => array(
					'p1' => array (
						'hide' => true,
						'description' => __('Enable FancyBox for','easy-fancybox') . '<br />'
					),
					'IMG' => array (
						'id' => 'fancybox_enableImg',
						'input' => 'checkbox',
						'hide' => true,
						'default' => ( function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( easyFancyBox::$plugin_basename ) ) ? '' : '1',
						'description' => '<strong>' . __('Images','easy-fancybox') . '</strong>'
					),
					'Inline' => array (
						'id' => 'fancybox_enableInline',
						'input' => 'checkbox',
						'hide' => true,
						'default' => '',
						'description' => '<strong>' . __('Inline content','easy-fancybox') . '</strong>'
					),
					'PDF' => array (
						'id' => 'fancybox_enablePDF',
						'input' => 'checkbox',
						'hide' => true,
						'default' => '',
						'description' => '<strong>' . __('PDF','easy-fancybox') . '</strong>'
					),
					'SWF' => array (
						'id' => 'fancybox_enableSWF',
						'input' => 'checkbox',
						'hide' => true,
						'default' => '',
						'description' => '<strong>' . __('SWF','easy-fancybox') . '</strong>'
					),
					'SVG' => array (
						'id' => 'fancybox_enableSVG',
						'input' => 'checkbox',
						'hide' => true,
						'default' => '',
						'description' => '<strong>' . __('SVG','easy-fancybox') . '</strong>'
					),
					'YouTube' => array (
						'id' => 'fancybox_enableYoutube',
						'input' => 'checkbox',
						'hide' => true,
						'default' => '',
						'description' => '<strong>' . __('YouTube','easy-fancybox') . '</strong>'
					),
					'Vimeo' => array (
						'id' => 'fancybox_enableVimeo',
						'input' => 'checkbox',
						'hide' => true,
						'default' => '',
						'description' => '<strong>' . __('Vimeo','easy-fancybox') . '</strong>'
					),
					'Dailymotion' => array (
						'id' => 'fancybox_enableDailymotion',
						'input' => 'checkbox',
						'hide' => true,
						'default' => '',
						'description' => '<strong>' . __('Dailymotion','easy-fancybox') . '</strong>'
					),
					'iFrame' => array (
						'id' => 'fancybox_enableiFrame',
						'input' => 'checkbox',
						'hide' => true,
						'default' => '',
						'description' => '<strong>' . __('iFrames','easy-fancybox') . '</strong>'
					)
				),
				'description' => '<a href="'.$efb_url.'"><strong><em>' . __('For advanced options and support, please get the Easy FancyBox - Pro extension.','easy-fancybox') . '</strong></a>'
			),
			'Overlay' => array (
				'title' => __('Overlay','easy-fancybox'),
				'input' => 'multiple',
				'hide' => true,
				'options' => array(
					'overlayShow' => array (
						'id' => 'fancybox_overlayShow',
						'input' => 'checkbox',
						'noquotes' => true,
						'default' => '1',
						'description' => __('Show the overlay around content opened in FancyBox.','easy-fancybox')
					),
					'hideOnOverlayClick' => array (
						'id' => 'fancybox_hideOnOverlayClick',
						'input' => 'checkbox',
						'noquotes' => true,
						'default' => '1',
						'description' => __('Close FancyBox when overlay is clicked.','easy-fancybox')
					),
					'overlayOpacity' => array (
						'id' => 'fancybox_overlayOpacity',
						'title' => __('Opacity','easy-fancybox'),
						'label_for' => 'fancybox_overlayOpacity',
						'input' => 'number',
						'step' => '0.1',
						'min' => '0',
						'max' => '1',
						'class' => 'small-text',
						'default' => '',
						'description' => __('Value between 0 and 1. ','easy-fancybox') . ' <em>' . __('Default:','easy-fancybox')  . ' 0.7</em><br />'
					),
					'overlayColor' => array (
						'id' => 'fancybox_overlayColor',
						'title' => __('Color','easy-fancybox'),
						'label_for' => 'fancybox_overlayColor',
						'input' => 'text',
						'sanitize_callback' => 'colorval',
						'class' => 'small-text',
						'default' => '',
						'description' => __('Enter an HTML color value.','easy-fancybox') . ' <em>' . __('Default:','easy-fancybox')  . ' #777</em><br />'
					),
					'overlaySpotlight' => array (
						'id' => 'fancybox_overlaySpotlight',
						'input' => 'checkbox',
						'hide' => true,
						'status' => get_option('fancybox_overlaySpotlight') ? '' : 'disabled',
						'default' => '',
						'description' => __('Spotlight effect','easy-fancybox') . ( get_option('fancybox_overlaySpotlight') ? '' : '. <em><a href="'.$efb_url.'">' . __('Make available &raquo;','easy-fancybox') ) . '</a></em>'
					)
				)
			),
			'Window' => array (
				'title' => __('Window','easy-fancybox'),
				'input' => 'multiple',
				'hide' => true,
				'options' => array(
					'p1' => array (
						'hide' => true,
						'description' => '<strong>' . __('Appearance','easy-fancybox') . '</strong><br />'
					),
					'showCloseButton' => array (
						'id' => 'fancybox_showCloseButton',
						'input' => 'checkbox',
						'noquotes' => true,
						'default' => '1',
						'description' => __('Show the (X) close button','easy-fancybox')
					),
					'backgroundColor' => array (
						'id' => 'fancybox_backgroundColor',
						'hide' => true,
						'title' => __('Background color','easy-fancybox'),
						'label_for' => 'fancybox_backgroundColor',
						'input' => 'text',
						'sanitize_callback' => 'colorval',
						'status' => 'disabled',
						'class' => 'small-text',
						'default' => '',
						'description' => ''
					),
					'textColor' => array (
						'id' => 'fancybox_textColor',
						'hide' => true,
						'title' => __('Text color','easy-fancybox'),
						'label_for' => 'fancybox_textColor',
						'input' => 'text',
						'sanitize_callback' => 'colorval',
						'status' => 'disabled',
						'class' => 'small-text',
						'default' => '',
						'description' => '<em><a href="'.$efb_url.'">' . __('Make available &raquo;','easy-fancybox') . '</a></em><br />'
					),
					'titleColor' => array (
						'id' => 'fancybox_titleColor',
						'hide' => true,
						'title' => __('Title color','easy-fancybox'),
						'label_for' => 'fancybox_titleColor',
						'input' => 'text',
						'sanitize_callback' => 'colorval',
						'class' => 'small-text',
						'default' => '',
						'description' => ''
					),
					'paddingColor' => array (
						'id' => 'fancybox_paddingColor',
						'hide' => true,
						'title' => __('Border color','easy-fancybox'),
						'label_for' => 'fancybox_paddingColor',
						'input' => 'text',
						'sanitize_callback' => 'colorval',
						'class' => 'small-text',
						'default' => '',
						'description' => '<em>' . __('Default:','easy-fancybox')  . ' #000 x #fff</em><br />' . __('Note:','easy-fancybox') . ' ' . __('Use RGBA notation for semi-transparent borders.','easy-fancybox') . ' <em>' . __('Example:','easy-fancybox') . ' rgba(10,10,30,0.7)</em><br />'
					),
					'borderRadius' => array (
						'id' => 'fancybox_borderRadius',
						'hide' => true,
						'title' => __('Border radius','easy-fancybox'),
						'label_for' => 'fancybox_borderRadius',
						'input' => 'number',
						'step' => '1',
						'min' => '0',
						'max' => '99',
						'sanitize_callback' => 'intval',
						'status' => 'disabled',
						'class' => 'small-text',
						'default' => '',
						'description' => '<em><a href="'.$efb_url.'">' . __('Make available &raquo;','easy-fancybox') . '</a></em><br />'
					),

					'p11' => array (
						'hide' => true,
						'description' => '<br /><strong>' . __('Dimensions','easy-fancybox') . '</strong><br />'
					),
					'width' => array (
						'id' => 'fancybox_width',
						'title' => translate('Width'),
						'label_for' => 'fancybox_width',
						'input' => 'text',
						'sanitize_callback' => 'intval',
						'class' => 'small-text',
						'default' => '',
						'description' => ' '
					),
					'height' => array (
						'id' => 'fancybox_height',
						'title' => translate('Height'),
						'label_for' => 'fancybox_height',
						'input' => 'text',
						'sanitize_callback' => 'intval',
						'class' => 'small-text',
						'default' => '',
						'description' => '<em>' . __('Default:','easy-fancybox')  . ' 560 x 340</em><br />' . __('If content size is not set or cannot be determined automatically, these default dimensions will be used.','easy-fancybox') . '<br />'
					),
					'padding' => array (
						'id' => 'fancybox_padding',
						'title' => translate('Border'),
						'label_for' => 'fancybox_padding',
						'input' => 'number',
						'step' => '1',
						'min' => '0',
						'max' => '100',
						'sanitize_callback' => 'intval',
						'class' => 'small-text',
						'default' => '',
						'description' => '<em>' . __('Default:','easy-fancybox')  . ' 10</em><br />'
					),
					'margin' => array (
						'id' => 'fancybox_margin',
						'title' => __('Margin','easy-fancybox'),
						'label_for' => 'fancybox_margin',
						'input' => 'number',
						'step' => '1',
						'min' => '20',
						'max' => '80',
						'sanitize_callback' => 'intval',
						'class' => 'small-text',
						'default' => '20',
						'description' => '<em>' . __('Default:','easy-fancybox')  . ' 40</em><br />'
					),

					'p2' => array (
						'hide' => true,
						'description' => '<br /><strong>' . __('Behavior','easy-fancybox') . '</strong><br />'
					),
					'centerOnScroll' => array (
						'id' => 'fancybox_centerOnScroll',
						'input' => 'checkbox',
						'noquotes' => true,
						'default' => '',
						'description' => __('Center while scrolling (always disabled on touch devices and when content, including the title, might be larger than the viewport)','easy-fancybox')
					),
					'enableEscapeButton' => array (
						'id' => 'fancybox_enableEscapeButton',
						'input' => 'checkbox',
						'noquotes' => true,
						'default' => '1',
						'description' => __('Esc key stroke closes FancyBox','easy-fancybox')
					),
					'autoScale' => array (
						'id' => 'fancybox_autoScale',
						'input' => 'checkbox',
						'noquotes' => true,
						'default' => '1',
						'description' => __('Scale large content down to fit in the browser viewport.','easy-fancybox')
					),
					'speedIn' => array (
						'id' => 'fancybox_speedIn',
						'title' => __('Opening speed','easy-fancybox'),
						'label_for' => 'fancybox_speedIn',
						'input' => 'number',
						'step' => '100',
						'min' => '0',
						'max' => '6000',
						'sanitize_callback' => 'intval',
						'class' => 'small-text',
						'default' => '',
					),
					'speedOut' => array (
						'id' => 'fancybox_speedOut',
						'title' => __('Closing speed','easy-fancybox'),
						'label_for' => 'fancybox_speedOut',
						'input' => 'number',
						'step' => '100',
						'min' => '0',
						'max' => '6000',
						'sanitize_callback' => 'intval',
						'class' => 'small-text',
						'default' => '',
						'description' => '<br />' . __('Duration in milliseconds. Higher is slower.','easy-fancybox') . ' <em>' . __('Default:','easy-fancybox')  . ' 300</em><br />'
					),
					'mouseWheel' => array (
						'id' => 'fancybox_mouseWheel',
						'hide' => true,
						'input' => 'checkbox',
						'default' => '1',
						'description' => __('Include the Mousewheel jQuery extension script to allow gallery browsing by mousewheel action.','easy-fancybox')
					)
				)
			),

			'Miscellaneous' => array (
				'title' => __('Miscellaneous','easy-fancybox'),
				'input' => 'multiple',
				'hide' => true,
				'options' => array(
					'p0' => array (
						'hide' => true,
						'description' => '<strong>' . __('Auto popup','easy-fancybox') . '</strong><br />'
					),
					'autoClick' => array (
						'id' => 'fancybox_autoClick',
						'title' => __('Open on page load','easy-fancybox'),
						'label_for' => 'fancybox_autoClick',
						'hide' => true,
						'input' => 'select',
						'options' => array(
							'' => translate('None'),
							'1' => __('Link with ID "fancybox-auto"','easy-fancybox'),
						),
						'default' => '1',
						'description' => '<em><a href="'.$efb_url.'">' . __('More options &raquo;','easy-fancybox') . '</a></em><br />'
					),
					'delayClick' => array (
						'id' => 'fancybox_delayClick',
						'title' => __('Delay in milliseconds','easy-fancybox'),
						'label_for' => 'fancybox_delayClick',
						'hide' => true,
						'input' => 'number',
						'step' => '100',
						'min' => '0',
						'max' => '',
						'sanitize_callback' => 'intval',
						'class' => 'small-text',
						'default' => '1000',
						'description' => ' <em>' . __('Default:','easy-fancybox')  . ' 1000</em><br />'
					),
					'jqCookie' => array (
						'id' => '',
						'title' => __('Hide popup after first visit?','easy-fancybox'),
						'hide' => true,
						'input' => 'select',
						'status' => 'disabled',
						'default' => '0',
						'sanitize_callback' => 'intval',
						'options' => array(
							'0' => translate('No'),
							'1' => __('1 Day','easy-fancybox'),
							'7' => __('1 Week','easy-fancybox'),
							'30' => __('1 Month','easy-fancybox'),
							'365' => __('1 Year','easy-fancybox')
						),
						'description' => ' <em><a href="'.$efb_url.'">' . __('Make available &raquo;','easy-fancybox') . '</a></em><br />'
					),
					'cookiePath' => array (
						'id' => '',
						'default' => '',
						'hide' => true
					),
					'p1' => array (
						'hide' => true,
						'description' => '<br /><strong>' . __('Browser & device compatibility','easy-fancybox') . '</strong><br />'
					),
					'minViewportWidth' => array (
						'id' => 'fancybox_minViewportWidth',
						'title' => __('Minimum browser/device viewport width','easy-fancybox'),
						'label_for' => 'fancybox_minViewportWidth',
						'input' => 'number',
						'step' => '1',
						'min' => '320',
						'max' => '900',
						'sanitize_callback' => 'intval',
						'class' => 'small-text',
						'default' => '',
						'description' => __('(leave empty to ignore)','easy-fancybox') . '<br/>'
					),
/*					'forceNewtab' => array (
						'id' => 'fancybox_forceNewtab',
						'input' => 'checkbox',
						'hide' => true,
						'default' => '1',
						'description' => __('Make media links open in a new tab when viewport falls below minimum width (above)','easy-fancybox')
					),*/
					'compatIE8' => array (
						'id' => 'fancybox_compatIE8',
						'input' => 'checkbox',
						'hide' => true,
						'default' => '',
						'description' => __('Include IE 8 compatibility style rules','easy-fancybox')
					),
					'p2' => array (
						'hide' => true,
						'description' => '<br /><strong>' . __('Theme & plugins compatibility','easy-fancybox') . '</strong><br />'
										. __('Try to deactivate all conflicting light box scripts in your theme or other plugins. If this is not possible, try a higher script priority number which means scripts are added later, wich may allow them to override conflicting scripts. A lower priority number, excluding WordPress standard jQuery, or even moving the plugin scripts to the header may work in cases where there are blocking errors occuring in other script.','easy-fancybox')
										. '<br /><br />'
					),
					'scriptPriority' => array (
						'id' => 'fancybox_scriptPriority',
						'title' => __('FancyBox script priority','easy-fancybox'),
						'label_for' => 'fancybox_scriptPriority',
						'hide' => true,
						'input' => 'number',
						'step' => '1',
						'min' => '-99',
						'max' => '999',
						'sanitize_callback' => 'intval',
						'class' => 'small-text',
						'default' => '10',
						'description' => __('Default priority is 10.','easy-fancybox') . ' ' . __('Higher is later.','easy-fancybox') . '<br/>'
					),
					'noFooter' => array (
						'id' => 'fancybox_noFooter',
						'input' => 'checkbox',
						'hide' => true,
						'default' => '',
						'description' => __('Move scripts from footer to theme head section (not recommended for site load times!)','easy-fancybox')
					),
					'nojQuery' => array (
						'id' => 'fancybox_nojQuery',
						'input' => 'checkbox',
						'hide' => true,
						'default' => '',
						'description' => __('Do not include standard WordPress jQuery library (do this only if you are sure jQuery is included from another source!)','easy-fancybox')
					),
					'pre45Compat' => array (
						'id' => 'fancybox_pre45Compat',
						'input' => 'checkbox',
						'hide' => true,
						'default' => function_exists( 'wp_add_inline_script' ) ? '' : '1',
						'description' => __('Do not use wp_add_inline_script/style functions (may solve issues with older script minification plugins)','easy-fancybox')
					),
					'p3' => array (
						'hide' => true,
						'description' => '<br /><strong>' . __('Advanced','easy-fancybox') . '</strong><br />'
					),
					'metaData' => array (
						'id' => 'fancybox_metaData',
						'hide' => true,
						'input' => 'checkbox',
						'status' => get_option('fancybox_metaData') ? '' : 'disabled',
						'default' =>  '',
						'description' => __('Include the Metadata jQuery extension script to allow passing custom parameters via link class.','easy-fancybox') . ( get_option('fancybox_metaData') ? '' : '. <em><a href="'.$efb_url.'">' . __('Make available &raquo;','easy-fancybox') ) . '</a></em>'
					),
					'vcMasonryCompat' => array (
						'id' => 'fancybox_vcMasonryCompat',
						'hide' => true,
						'input' => 'checkbox',
						'status' => 'disabled',
						'default' =>  '',
						'description' => __('WPBakery / Visual Composer - Masonry Grid Gallery compatibility.','easy-fancybox') . ' <em><a href="'.$efb_url.'">' . __('Make available &raquo;','easy-fancybox') . '</a></em>'
					)
				)
			)
		)
	),

	'IMG' => array(
		'title' => __('Images','easy-fancybox'),
		'input' => 'multiple',
		'options' => array(
			'intro' => array (
				'hide' => true,
				'description' => __('To make images open in an overlay, add their extension to the Autodetect field or use the class "fancybox" for its link. Clear field to switch off all autodetection.','easy-fancybox') . '<br />'
			),
			'tag' => array (
				'hide' => true,
				'default' => 'a.fancybox,area.fancybox,li.fancybox a'
			),
			'class' => array (
				'hide' => true,
				'default' => 'fancybox image'
			),
			'autoAttribute' => array (
				'id' => 'fancybox_autoAttribute',
				'title' => __('Autodetect','easy-fancybox'),
				'label_for' => 'fancybox_autoAttribute',
				'input' => 'text',
				'class' => 'regular-text',
				'hide' => true,
				'default' => '.jpg .jpeg .png .webp',
				'selector' => 'href*=',
				'description' => ' <em>' . __('Example:','easy-fancybox') . ' .jpg .jpeg .png .gif</em><br />'
			),
			'autoAttributeLimit' => array (
				'id' => 'fancybox_autoAttributeLimit',
				'title' => __('Apply to','easy-fancybox'),
				'label_for' => 'fancybox_autoAttributeLimit',
				'hide' => true,
				'input' => 'select',
				'options' => array(
					'' => __('All image links', 'easy-fancybox')
				),
				'default' => '',
				'description' => '<em><a href="'.$efb_url.'">' . __('More options &raquo;','easy-fancybox') . '</a></em><br />'
			),
			'type' => array (
				'id' => 'fancybox_classType',
				'title' => __('Force FancyBox to treat all media linked with class="fancybox" as images?','easy-fancybox'),
				'label_for' => 'fancybox_classType',
				'input' => 'select',
				'options' => array(
					'image' => translate('Yes'),
					'' => translate('No')
				),
				'default' => get_option('fancybox_enableInline') ? 'image' : '',
				'description' => '<br/>'
			),
			'p2' => array (
				'hide' => true,
				'description' => '<br /><strong>' . __('Behavior','easy-fancybox') . '</strong><br />'
			),
			'transitionIn' => array (
				'id' => 'fancybox_transitionIn',
				'title' => __('Transition In','easy-fancybox'),
				'label_for' => 'fancybox_transitionIn',
				'input' => 'select',
				'options' => array(
					'none' => translate('None'),
					'' => __('Fade','easy-fancybox'),
					'elastic' => __('Elastic','easy-fancybox'),
				),
				'default' => 'elastic',
				'description' => ' '
			),
			'easingIn' => array (
				'id' => 'fancybox_easingIn',
				'title' => __('Easing In','easy-fancybox'),
				'label_for' => 'fancybox_easingIn',
				'input' => 'select',
				'options' => array(
					'linear' => __('Linear','easy-fancybox'),
					'' => __('Swing','easy-fancybox'),
					'easeInBack' => __('easeInBack','easy-fancybox'),
					'easeOutBack' => __('easeOutBack','easy-fancybox')
				),
				'default' => 'easeOutBack',
				'description' => ' <em><a href="'.$efb_url.'">' . __('More options &raquo;','easy-fancybox') . '</a></em><br />'
			),
			'transitionOut' => array (
				'id' => 'fancybox_transitionOut',
				'title' => __('Transition Out','easy-fancybox'),
				'label_for' => 'fancybox_transitionOut',
				'input' => 'select',
				'options' => array(
					'none' => translate('None'),
					'' => __('Fade','easy-fancybox'),
					'elastic' => __('Elastic','easy-fancybox'),
				),
				'default' => 'elastic',
				'description' => ' '
			),
			'easingOut' => array (
				'id' => 'fancybox_easingOut',
				'title' => __('Easing Out','easy-fancybox'),
				'label_for' => 'fancybox_easingOut',
				'input' => 'select',
				'options' => array(
					'linear' => __('Linear','easy-fancybox'),
					'' => __('Swing','easy-fancybox'),
					'easeInBack' => __('easeInBack','easy-fancybox'),
					'easeOutBack' => __('easeOutBack','easy-fancybox')
				),
				'default' => 'easeInBack',
				'description' => ' <em><a href="'.$efb_url.'">' . __('More options &raquo;','easy-fancybox') . '</a></em><br />' . __('Note:','easy-fancybox') . ' ' . __('Easing effects only apply when Transition is set to Elastic. ','easy-fancybox')  . '<br /><br />'
			),
			'opacity' => array (
				'id' => 'fancybox_opacity',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '',
				'description' => __('Transparency fade during elastic transition. CAUTION: Use only when at least Transition In is set to Elastic!','easy-fancybox')
			),
			'hideOnContentClick' => array (
				'id' => 'fancybox_hideOnContentClick',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '',
				'description' => __('Close FancyBox when content is clicked','easy-fancybox')
			),
			'p1' => array (
				'hide' => true,
				'description' => '<br /><strong>' . __('Appearance','easy-fancybox') . '</strong><br />'
			),
			'titleShow' => array (
				'id' => 'fancybox_titleShow',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '1',
				'description' => __('Show title.','easy-fancybox') . ' ' . __('FancyBox will try to get a title from the link or thumbnail title attributes.','easy-fancybox')
			),
			'titlePosition' => array (
				'id' => 'fancybox_titlePosition',
				'title' => __('Title Position','easy-fancybox'),
				'label_for' => 'fancybox_titlePosition',
				'input' => 'select',
				'options' => array(
					'' => __('Float','easy-fancybox'),
					'outside' => __('Outside','easy-fancybox'),
					'inside' => __('Inside','easy-fancybox'),
					'over' => __('Overlay','easy-fancybox')
				),
				'default' => 'over',
				'description' => '<br />'
			),
			'titleFromAlt' => array (
				'id' => 'fancybox_titleFromAlt',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '1',
				'description' => __('Allow title from thumbnail alt attribute.','easy-fancybox')
			),
			'onStart' => array (
				'id' => '',
				'title' => __('Advanced','easy-fancybox'),
				'input' => 'select',
				'status' => 'disabled',
				'options' => array(
					'' => __('Hide/show title on mouse hover action','easy-fancybox')
				),
				'default' => '',
				'description' =>  '<em><a href="'.$efb_url.'">' . __('Make available &raquo;','easy-fancybox') . '</a></em><br />'
			),
			'p3' => array (
				'hide' => true,
				'description' => '<br /><strong>' . __('Gallery','easy-fancybox') . '</strong><br />'
			),
			'autoGallery' => array (
				'id' => 'fancybox_autoGallery',
				'title' => __('Autogallery','easy-fancybox'),
				'label_for' => 'fancybox_autoGallery',
				'hide' => true,
				'input' => 'select',
				'options' => array(
					'' => translate('Disabled'),
					'1' => __('WordPress galleries only','easy-fancybox'),
					'2' => __('All in one gallery','easy-fancybox')
				),
				'default' => '1',
				'description' => '<em><a href="'.$efb_url.'">' . __('More options &raquo;','easy-fancybox') . '</a></em><br />' . __('Note:','easy-fancybox') . ' ' . __('When disabled, you can use the rel attribute to manually group image links together.','easy-fancybox') . '<br /><br />'
			),
			'showNavArrows' => array (
				'id' => 'fancybox_showNavArrows',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '1',
				'description' => __('Show the gallery navigation arrows','easy-fancybox')
			),
			'enableKeyboardNav' => array (
				'id' => 'fancybox_enableKeyboardNav',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '1',
				'description' => __('Arrow key strokes browse the gallery','easy-fancybox')
			),
			'cyclic' => array (
				'id' => 'fancybox_cyclic',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '',
				'description' => __('Make galleries cyclic, allowing you to keep pressing next/back.','easy-fancybox')
			),
			'changeSpeed' => array (
				'id' => 'fancybox_changeSpeed',
				'title' => __('Change speed','easy-fancybox'),
				'label_for' => 'fancybox_changeSpeed',
				'input' => 'number',
				'step' => '1',
				'min' => '0',
				'max' => '6000',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '',
			),
			'changeFade' => array (
				'id' => 'fancybox_changeFade',
				'title' => __('Fade speed','easy-fancybox'),
				'label_for' => 'fancybox_changeFade',
				'input' => 'number',
				'step' => '1',
				'min' => '0',
				'max' => '6000',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '',
				'description' => '<br />' . __('Duration in milliseconds. Higher is slower.','easy-fancybox') . ' <em>' . __('Default:','easy-fancybox')  . ' 300</em><br /><br />'
			),
			'autoSelector' => array (
				'id' => 'fancybox_autoSelector',
				'hide' => true,
				'input' => 'hidden',
				'default' => '.gallery,.wp-block-gallery,.tiled-gallery' // add div.tiled-gallery for Tiled Galleries support
			),
			'onComplete' => array (
				'id' => '',
				'title' => __('Advanced','easy-fancybox'),
				'input' => 'select',
				'status' => 'disabled',
				'options' => array(
					'' => __('Slideshow','easy-fancybox')
				),
				'default' => '',
				'description' =>  '<em><a href="'.$efb_url.'">' . __('Make available &raquo;','easy-fancybox') . '</a></em>'
			),
/*					'titleFormat' => array (
				'id' => 'fancybox_titleFormat',
				'title' => __('Title format','easy-fancybox'),
				'label_for' => 'fancybox_titleFormat',
				'input' => 'select',
				'options' => array(
					'' => __('Default FancyBox style','easy-fancybox'),
					'function(title, currentArray, currentIndex, currentOpts) { return \'<div style="font-face:Arial,sans-serif;text-align:left"><span style="float:right;font-size:large"><a href="javascript:;" onclick="$.fancybox.close();">' . __('Close','easy-fancybox') . ' <img src="' . plugins_url(FANCYBOX_SUBDIR, __FILE__) . '/fancybox/fancy_close.png" /></a></span>\' + (title && title.length ? \'<b style="display:block;margin-right:80px">\' + title + \'</b>\' : \'\' ) + \'' . __('Image','easy-fancybox') . '\' + (currentIndex + 1) + \' ' . __('of','easy-fancybox') . ' \' + currentArray.length + \'</div>\';
}' => __('Mimic Lightbox2 style','easy-fancybox'),
					),
				'noquotes' => true,
				'default' => '',
				'description' =>  '<br />' . __('To improve Lightbox2 style disable Show close button and set titleposition to Inside or Outside','easy-fancybox') . '<br />'
				),*/
		)
	),

	'Inline' => array(
		'title' => __('Inline content','easy-fancybox'),
		'input' => 'multiple',
		'options' => array(
			'intro' => array (
				'hide' => true,
				'description' => __('To make inline content open in an overlay, wrap that content in a div with a unique ID, create a link with target "#uniqueID" and give it a class "fancybox-inline" attribute.','easy-fancybox') . '<br /><br />'
			),
			'tag' => array (
				'hide' => true,
				'default' => 'a.fancybox-inline,area.fancybox-inline,li.fancybox-inline a'
			),
			'class' => array (
				'hide' => true,
				'default' => 'fancybox-inline'
			),
			'type' => array (
				'default' => 'inline'
			),
			'autoDimensions' => array (
				'id' => 'fancybox_autoDimensions',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '1',
				'description' => __('Try to adjust size to inline/html content. If unchecked the default dimensions will be used.','easy-fancybox') . ''
			),
			'scrolling' => array (
				'id' => 'fancybox_InlineScrolling',
				'title' => __('Scrolling','easy-fancybox'),
				'label_for' => 'fancybox_InlineScrolling',
				'input' => 'select',
				'options' => array(
					'auto' => __('Auto','easy-fancybox'),
					'yes' => __('Always','easy-fancybox'),
					'no' => __('Never','easy-fancybox')
				),
				'default' => 'no',
				'description' => __('Define scrolling and scrollbar visibility.','easy-fancybox') . '<br /><br />'
			),
			'transitionIn' => array (
				'id' => 'fancybox_transitionInInline',
				'title' => __('Transition In','easy-fancybox'),
				'label_for' => 'fancybox_transitionInInline',
				'input' => 'select',
				'options' => array(
					'none' => translate('None'),
					'' => __('Fade','easy-fancybox'),
					'elastic' => __('Elastic','easy-fancybox'),
				),
				'default' => '',
				'description' => ' '
			),
			'easingIn' => array (
				'id' => 'fancybox_easingInInline',
				'title' => __('Easing In','easy-fancybox'),
				'label_for' => 'fancybox_easingInInline',
				'input' => 'select',
				'options' => array(
					'linear' => __('Linear','easy-fancybox'),
					'' => __('Swing','easy-fancybox'),
					'easeInBack' => __('easeInBack','easy-fancybox'),
					'easeOutBack' => __('easeOutBack','easy-fancybox')
				),
				'default' => 'easeOutBack',
				'description' => ' <em><a href="'.$efb_url.'">' . __('More options &raquo;','easy-fancybox') . '</a></em><br />'
			),
			'transitionOut' => array (
				'id' => 'fancybox_transitionOutInline',
				'title' => __('Transition Out','easy-fancybox'),
				'label_for' => 'fancybox_transitionOutInline',
				'input' => 'select',
				'options' => array(
					'none' => translate('None'),
					'' => __('Fade','easy-fancybox'),
					'elastic' => __('Elastic','easy-fancybox'),
				),
				'default' => '',
				'description' => ' '
			),
			'easingOut' => array (
				'id' => 'fancybox_easingOutInline',
				'title' => __('Easing Out','easy-fancybox'),
				'label_for' => 'fancybox_easingOutInline',
				'input' => 'select',
				'options' => array(
					'linear' => __('Linear','easy-fancybox'),
					'' => __('Swing','easy-fancybox'),
					'easeInBack' => __('easeInBack','easy-fancybox'),
					'easeOutBack' => __('easeOutBack','easy-fancybox')
				),
				'default' => 'easeInBack',
				'description' => ' <em><a href="'.$efb_url.'">' . __('More options &raquo;','easy-fancybox') . '</a></em><br />' . __('Note:','easy-fancybox') . ' ' . __('Easing effects only apply when Transition is set to Elastic. ','easy-fancybox')  . '<br /><br />'
			),
			'opacity' => array (
				'id' => 'fancybox_opacityInline',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '',
				'description' => __('Transparency fade during elastic transition. CAUTION: Use only when at least Transition In is set to Elastic!','easy-fancybox')
			),
			'hideOnContentClick' => array (
				'id' => 'fancybox_hideOnContentClickInline',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '',
				'description' => __('Close FancyBox when content is clicked','easy-fancybox')
			),
			'titleShow' => array (
				'noquotes' => true,
				'default' => 'false',
			)
		)
	),

	'PDF' => array(
		'title' => __('PDF','easy-fancybox'),
		'input' => 'multiple',
		'options' => array(
			'intro' => array (
				'hide' => true,
				'description' => __('To make any PDF document file open in an overlay, switch on Autodetect or use the class "fancybox-pdf" for its link.','easy-fancybox') . '<br />'
			),
			'autoAttribute' => array (
				'id' => 'fancybox_autoAttributePDF',
				'input' => 'checkbox',
				'hide' => true,
				'default' => '1',
				'selector' => '\'a[href*=".pdf"],area[href*=".pdf"],a[href*=".PDF"],area[href*=".PDF"]\'',
				'description' => __('Autodetect','easy-fancybox')
			),
			'tag' => array (
				'hide' => true,
				'default' => 'a.fancybox-pdf,area.fancybox-pdf,li.fancybox-pdf a'
			),
			'class' => array (
				'hide' => true,
				'default' => 'fancybox-pdf'
			),
			'type' => array (
				'default' => 'iframe'
			),
			'onStart' => array (
				'id' => 'fancybox_PDFonStart',
				'noquotes' => true,
				'title' => __('Embed with','easy-fancybox'),
				'label_for' => 'fancybox_PDFtitlePosition',
				'input' => 'select',
				'options' => array(
					'function(a,i,o){o.type=\'pdf\';}' => __('Object tag (plus fall-back link)','easy-fancybox'),
					'function(a,i,o){o.type=\'html\';o.content=\'<embed src="\'+a[i].href+\'" type="application/pdf" height="100%" width="100%" />\'}' => __('Embed tag','easy-fancybox'),
					'' => __('iFrame tag (let browser decide)','easy-fancybox'),
					'function(a,i,o){o.href=\'https://docs.google.com/viewer?embedded=true&url=\'+a[i].href;}' => __('Google Docs Viewer (external)','easy-fancybox')
				),
				'default' => '',
								// 'function(a,i,o){o.content=\'<object data="\'+a[i].href+\'" type="application/pdf" height="100%" width="100%"><a href="\'+a[i].href+\'" style="display:block;position:absolute;top:48%;width:100%;text-align:center">\'+jQuery(a[i]).html()+\'</a></object>\'}'
				// 'function(a, i, o) { o.content = \'<embed src="\' + a[i].href + \'#toolbar=1&navpanes=0&nameddest=self&page=1&view=FitH,0&zoom=80,0,0" type="application/pdf" height="100%" width="100%" />\' }'
				'description' => __('Note:','easy-fancybox') . ' ' . __('External viewers have bandwidth, usage rate and and file size limits.','easy-fancybox') . '<br /><br />' //' <em><a href="'.$efb_url.'">' . __('More options &raquo;','easy-fancybox') . '</a></em><br /><br />'
			),
			'width' => array (
				'id' => 'fancybox_PDFwidth',
				'title' => translate('Width'),
				'label_for' => 'fancybox_PDFwidth',
				'input' => 'text',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '90%',
				'description' => ' '
			),
			'height' => array (
				'id' => 'fancybox_PDFheight',
				'title' => translate('Height'),
				'label_for' => 'fancybox_PDFheight',
				'input' => 'text',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '90%'
			),
			'padding' => array (
				'id' => 'fancybox_PDFpadding',
				'title' => translate('Border'),
				'label_for' => 'fancybox_PDFpadding',
				'input' => 'number',
				'step' => '1',
				'min' => '0',
				'max' => '100',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '10',
				'description' => '<br /><br />'
			),
			'titleShow' => array (
				'id' => 'fancybox_PDFtitleShow',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '',
				'description' => __('Show title.','easy-fancybox') . ' ' . __('FancyBox will try to get a title from the link or thumbnail title attributes.','easy-fancybox')
			),
			'titlePosition' => array (
				'id' => 'fancybox_PDFtitlePosition',
				'title' => __('Title Position','easy-fancybox'),
				'label_for' => 'fancybox_PDFtitlePosition',
				'input' => 'select',
				'options' => array(
					'float' => __('Float','easy-fancybox'),
					'outside' => __('Outside','easy-fancybox'),
					'inside' => __('Inside','easy-fancybox')
				),
				'default' => 'float',
				'description' => '<br />'
			),
			'titleFromAlt' => array (
				'id' => 'fancybox_PDFtitleFromAlt',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '1',
				'description' => __('Allow title from thumbnail alt attribute.','easy-fancybox')
			),
			'autoDimensions' => array (
				'noquotes' => true,
				'default' => 'false'
			),
			'scrolling' => array (
				'default' => 'no',
			),
		)
	),

	'SWF' => array(
		'title' => __('SWF','easy-fancybox'),
		'input' => 'multiple',
		'options' => array(
			'intro' => array (
				'hide' => true,
				'description' => __('To make any Flash (.swf) file open in an overlay, switch on Autodetect or use the class "fancybox-swf" for its link.','easy-fancybox') . '<br />'
			),
			'autoAttribute' => array (
				'id' => 'fancybox_autoAttributeSWF',
				'input' => 'checkbox',
				'hide' => true,
				'default' => '1',
				'selector' => '\'a[href*=".swf"],area[href*=".swf"],a[href*=".SWF"],area[href*=".SWF"]\'',
				'description' => __('Autodetect','easy-fancybox') . '<br />'
			),
			'tag' => array (
				'hide' => true,
				'default' => 'a.fancybox-swf,area.fancybox-swf,li.fancybox-swf a'
			),
			'class' => array (
				'hide' => true,
				'default' => 'fancybox-swf'
			),
			'type' => array(
				'default' => 'swf'
			),
			'width' => array (
				'id' => 'fancybox_SWFWidth',
				'title' => translate('Width'),
				'label_for' => 'fancybox_SWFWidth',
				'input' => 'text',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'options' => array(),
				'default' => '680',
				'description' => ' '
			),
			'height' => array (
				'id' => 'fancybox_SWFHeight',
				'title' => translate('Height'),
				'label_for' => 'fancybox_SWFHeight',
				'input' => 'text',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'options' => array(),
				'default' => '495',
			),
			'padding' => array (
				'id' => 'fancybox_SWFpadding',
				'title' => translate('Border'),
				'label_for' => 'fancybox_SWFpadding',
				'input' => 'number',
				'step' => '1',
				'min' => '0',
				'max' => '100',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '0',
				'description' => '<br /><br />'
			),
			'titleShow' => array (
				'id' => 'fancybox_SWFtitleShow',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '',
				'description' => __('Show title.','easy-fancybox') . ' ' . __('FancyBox will try to get a title from the link or thumbnail title attributes.','easy-fancybox')
			),
			'titlePosition' => array (
				'id' => 'fancybox_SWFtitlePosition',
				'title' => __('Title Position','easy-fancybox'),
				'label_for' => 'fancybox_SWFtitlePosition',
				'input' => 'select',
				'options' => array(
					'float' => __('Float','easy-fancybox'),
					'outside' => __('Outside','easy-fancybox'),
					'inside' => __('Inside','easy-fancybox')
				),
				'default' => 'float',
				'description' => '<br />'
			),
			'titleFromAlt' => array (
				'id' => 'fancybox_SWFtitleFromAlt',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '1',
				'description' => __('Allow title from thumbnail alt attribute.','easy-fancybox')
			),
			'swf' => array (
				'noquotes' => true,
				'default' => '{\'wmode\':\'opaque\',\'allowfullscreen\':true}'
			)
		)
	),

	'SVG' => array(
		'title' => __('SVG','easy-fancybox'),
		'input' => 'multiple',
		'options' => array(
			'intro' => array (
				'hide' => true,
				'description' => __('To make any SVG (.svg) file open in an overlay, switch on Autodetect or use the class "fancybox-svg" for its link.','easy-fancybox') . '<br />'
			),
			'autoAttribute' => array (
				'id' => 'fancybox_autoAttributeSVG',
				'input' => 'checkbox',
				'hide' => true,
				'default' => '1',
				'selector' => '\'a[href*=".svg"],area[href*=".svg"],a[href*=".SVG"],area[href*=".SVG"]\'',
				'description' => __('Autodetect','easy-fancybox') . '<br />'
			),
			'tag' => array (
				'hide' => true,
				'default' => 'a.fancybox-svg,area.fancybox-svg,li.fancybox-svg a'
			),
			'class' => array (
				'hide' => true,
				'default' => 'fancybox-svg'
			),
			'type' => array(
				'default' => 'svg'
			),
			'width' => array (
				'id' => 'fancybox_SVGWidth',
				'title' => translate('Width'),
				'label_for' => 'fancybox_SVGWidth',
				'input' => 'text',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'options' => array(),
				'default' => '680',
				'description' => ' '
			),
			'height' => array (
				'id' => 'fancybox_SVGHeight',
				'title' => translate('Height'),
				'label_for' => 'fancybox_SVGHeight',
				'input' => 'text',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'options' => array(),
				'default' => '495',
			),
			'padding' => array (
				'id' => 'fancybox_SVGpadding',
				'title' => translate('Border'),
				'label_for' => 'fancybox_SVGpadding',
				'input' => 'number',
				'step' => '1',
				'min' => '0',
				'max' => '100',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '0',
				'description' => '<br /><br />'
			),
			'titleShow' => array (
				'id' => 'fancybox_SVGtitleShow',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '',
				'description' => __('Show title.','easy-fancybox') . ' ' . __('FancyBox will try to get a title from the link or thumbnail title attributes.','easy-fancybox')
			),
			'titlePosition' => array (
				'id' => 'fancybox_SVGtitlePosition',
				'title' => __('Title Position','easy-fancybox'),
				'label_for' => 'fancybox_SVGtitlePosition',
				'input' => 'select',
				'options' => array(
					'float' => __('Float','easy-fancybox'),
					'outside' => __('Outside','easy-fancybox'),
					'inside' => __('Inside','easy-fancybox')
					//,'over' => __('Overlay','easy-fancybox')
				),
				'default' => 'float',
				'description' => '<br />'
			),
			'titleFromAlt' => array (
				'id' => 'fancybox_SVGtitleFromAlt',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '1',
				'description' => __('Allow title from thumbnail alt attribute.','easy-fancybox')
			),
			'svg' => array (
				'noquotes' => true,
				'default' => '{\'wmode\':\'opaque\',\'allowfullscreen\':true}'
			)
		)
	),

	'YouTube' => array(
		'title' => __('YouTube','easy-fancybox'),
		'input' => 'multiple',
		'options' => array(
			'intro' => array (
				'hide' => true,
				'description' => __('To make any YouTube movie open in an overlay, switch on Autodetect or use the class "fancybox-youtube" for its link.','easy-fancybox') . '<br />'
			),
			'autoAttribute' => array (
				'id' => 'fancybox_autoAttributeYoutube',
				'input' => 'checkbox',
				'hide' => true,
				'default' => '1',
				'selector' => '\'a[href*="youtu.be/"],area[href*="youtu.be/"],a[href*="youtube.com/"],area[href*="youtube.com/"]\').filter(function(){return this.href.match(/\/(?:youtu\.be|watch\?|embed\/)/);}',
				'description' => __('Autodetect','easy-fancybox') . '<br />'
			),
			'tag' => array (
				'hide' => true,
				'default' => 'a.fancybox-youtube,area.fancybox-youtube,li.fancybox-youtube a'
			),
			'class' => array (
				'hide' => true,
				'default' => 'fancybox-youtube'
			),
			'type' => array(
				'default' => 'iframe'
			),
			'noCookie' => array (
				'id' => 'fancybox_YoutubenoCookie',
				'input' => 'checkbox',
				'hide' => true,
				'default' => '',
				'description' => __('Enable privacy-enhanced mode','easy-fancybox') . '<br />'
			),
			'width' => array (
				'id' => 'fancybox_YoutubeWidth',
				'title' => translate('Width'),
				'label_for' => 'fancybox_YoutubeWidth',
				'input' => 'number',
				'step' => '1',
				'min' => '420',
				'max' => '1500',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '640',
				'description' => ' '
			),
			'height' => array (
				'id' => 'fancybox_YoutubeHeight',
				'title' => translate('Height'),
				'label_for' => 'fancybox_YoutubeHeight',
				'input' => 'number',
				'step' => '1',
				'min' => '315',
				'max' => '900',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '360',
			),
			'padding' => array (
				'id' => 'fancybox_Youtubepadding',
				'title' => translate('Border'),
				'label_for' => 'fancybox_Youtubepadding',
				'input' => 'number',
				'step' => '1',
				'min' => '0',
				'max' => '100',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '0',
				'description' => '<br /><br />'
			),
			'keepRatio' => array(
				'noquotes' => true,
				'default' => '1'
			),
			'titleShow' => array (
				'id' => 'fancybox_YoutubetitleShow',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '',
				'description' => __('Show title.','easy-fancybox') . ' ' . __('FancyBox will try to get a title from the link or thumbnail title attributes.','easy-fancybox')
			),
			'titlePosition' => array (
				'id' => 'fancybox_YoutubetitlePosition',
				'title' => __('Title Position','easy-fancybox'),
				'label_for' => 'fancybox_YoutubetitlePosition',
				'input' => 'select',
				'options' => array(
					'float' => __('Float','easy-fancybox'),
					'outside' => __('Outside','easy-fancybox'),
					'inside' => __('Inside','easy-fancybox')
				),
				'default' => 'float',
				'description' => '<br />'
			),
			'titleFromAlt' => array (
				'id' => 'fancybox_YoutubetitleFromAlt',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '1',
				'description' => __('Allow title from thumbnail alt attribute.','easy-fancybox')
			),
			'onStart' => array (
				'noquotes' => true,
				'default' => get_option( 'fancybox_YoutubenoCookie' ) ?
					'function(a,i,o){var splitOn=a[i].href.indexOf("?");var urlParms=(splitOn>-1)?a[i].href.substring(splitOn):"";o.allowfullscreen=(urlParms.indexOf("fs=0")>-1)?false:true;o.href=a[i].href.replace(/https?:\/\/(?:www\.)?youtu(?:\.be\/([^\?]+)\??|be\.com\/watch\?(.*(?=v=))v=([^&]+))(.*)/gi,"https://www.youtube-nocookie.com/embed/$1$3?$2$4");}' :
					'function(a,i,o){var splitOn=a[i].href.indexOf("?");var urlParms=(splitOn>-1)?a[i].href.substring(splitOn):"";o.allowfullscreen=(urlParms.indexOf("fs=0")>-1)?false:true;o.href=a[i].href.replace(/https?:\/\/(?:www\.)?youtu(?:\.be\/([^\?]+)\??|be\.com\/watch\?(.*(?=v=))v=([^&]+))(.*)/gi,"https://www.youtube.com/embed/$1$3?$2$4&autoplay=1");}'
			)
		)
	),

	'Vimeo' => array(
		'title' => __('Vimeo','easy-fancybox'),
		'input' => 'multiple',
		'options' => array(
			'intro' => array (
				'hide' => true,
				'description' => __('To make any Vimeo movie open in an overlay, switch on Autodetect or use the class "fancybox-vimeo" for its link.','easy-fancybox') . '<br />'
			),
			'autoAttribute' => array (
				'id' => 'fancybox_autoAttributeVimeo',
				'input' => 'checkbox',
				'hide' => true,
				'default' => '1',
				'selector' => '\'a[href*="vimeo.com/"],area[href*="vimeo.com/"]\').filter(function(){return this.href.match(/\/(?:[0-9]+|video\/)/);}',
				'description' => __('Autodetect','easy-fancybox') . '<br />'
			),
			'tag' => array (
				'hide' => true,
				'default' => 'a.fancybox-vimeo,area.fancybox-vimeo,li.fancybox-vimeo a'
			),
			'class' => array (
				'hide' => true,
				'default' => 'fancybox-vimeo'
			),
			'type' => array(
				'default' => 'iframe'
			),
			'width' => array (
				'id' => 'fancybox_VimeoWidth',
				'title' => translate('Width'),
				'label_for' => 'fancybox_VimeoWidth',
				'input' => 'number',
				'step' => '1',
				'min' => '400',
				'max' => '1500',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '500',
				'description' => ' '
			),
			'height' => array (
				'id' => 'fancybox_VimeoHeight',
				'title' => translate('Height'),
				'label_for' => 'fancybox_VimeoHeight',
				'input' => 'number',
				'step' => '1',
				'min' => '225',
				'max' => '900',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '281'
			),
			'padding' => array (
				'id' => 'fancybox_Vimeopadding',
				'title' => translate('Border'),
				'label_for' => 'fancybox_Vimeopadding',
				'input' => 'number',
				'step' => '1',
				'min' => '0',
				'max' => '100',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '0',
				'description' => '<br /><br />'
			),
			'keepRatio' => array(
				'noquotes' => true,
				'default' => '1'
			),
			'titleShow' => array (
				'id' => 'fancybox_VimeotitleShow',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '',
				'description' => __('Show title.','easy-fancybox') . ' ' . __('FancyBox will try to get a title from the link or thumbnail title attributes.','easy-fancybox')
			),
			'titlePosition' => array (
				'id' => 'fancybox_VimeotitlePosition',
				'title' => __('Title Position','easy-fancybox'),
				'label_for' => 'fancybox_VimeotitlePosition',
				'input' => 'select',
				'options' => array(
					'float' => __('Float','easy-fancybox'),
					'outside' => __('Outside','easy-fancybox'),
					'inside' => __('Inside','easy-fancybox')
				),
				'default' => 'float',
				'description' => '<br />'
			),
			'titleFromAlt' => array (
				'id' => 'fancybox_VimeotitleFromAlt',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '1',
				'description' => __('Allow title from thumbnail alt attribute.','easy-fancybox')
			),
			'onStart' => array (
				'noquotes' => true,
				'default' => 'function(a,i,o){var splitOn=a[i].href.indexOf("?");var urlParms=(splitOn>-1)?a[i].href.substring(splitOn):"";o.allowfullscreen=(urlParms.indexOf("fullscreen=0")>-1)?false:true;o.href=a[i].href.replace(/https?:\/\/(?:www\.)?vimeo\.com\/([0-9]+)\??(.*)/gi,"https://player.vimeo.com/video/$1?$2&autoplay=1");}'
			)
		)
	),

	'Dailymotion' => array(
		'title' => __('Dailymotion','easy-fancybox'),
		'input' => 'multiple',
		'options' => array(
			'intro' => array (
				'hide' => true,
				'description' => __('To make any Dailymotion movie open in an overlay, switch on Autodetect or use the class "fancybox-dailymotion" for its link.','easy-fancybox') . '<br />'
			),
			'autoAttribute' => array (
				'id' => 'fancybox_autoAttributeDailymotion',
				'input' => 'checkbox',
				'hide' => true,
				'default' => '1',
				'selector' => '\'a[href*="dailymotion.com/"],area[href*="dailymotion.com/"]\').filter(function(){return this.href.match(/\/video\//);}',
				'description' => __('Autodetect','easy-fancybox') . '<br />'
			),
			'tag' => array (
				'hide' => true,
				'default' => 'a.fancybox-dailymotion,area.fancybox-dailymotion,li.fancybox-dailymotion a'
			),
			'class' => array (
				'hide' => true,
				'default' => 'fancybox-dailymotion'
			),
			'type' => array(
				'default' => 'iframe'
			),
			'width' => array (
				'id' => 'fancybox_DailymotionWidth',
				'title' => translate('Width'),
				'label_for' => 'fancybox_DailymotionWidth',
				'input' => 'number',
				'step' => '1',
				'min' => '320',
				'max' => '1500',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '560',
				'description' => ' '
			),
			'height' => array (
				'id' => 'fancybox_DailymotionHeight',
				'title' => translate('Height'),
				'label_for' => 'fancybox_DailymotionHeight',
				'input' => 'number',
				'step' => '1',
				'min' => '180',
				'max' => '900',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '315'
			),
			'padding' => array (
				'id' => 'fancybox_DailymotionPadding',
				'title' => translate('Border'),
				'label_for' => 'fancybox_DailymotionPadding',
				'input' => 'number',
				'step' => '1',
				'min' => '0',
				'max' => '100',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '0',
				'description' => '<br /><br />'
			),
			'keepRatio' => array(
				'noquotes' => true,
				'default' => '1'
			),
			'titleShow' => array (
				'id' => 'fancybox_DailymotiontitleShow',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '',
				'description' => __('Show title.','easy-fancybox') . ' ' . __('FancyBox will try to get a title from the link or thumbnail title attributes.','easy-fancybox')
			),
			'titlePosition' => array (
				'id' => 'fancybox_DailymotiontitlePosition',
				'title' => __('Title Position','easy-fancybox'),
				'label_for' => 'fancybox_DailymotiontitlePosition',
				'input' => 'select',
				'options' => array(
					'float' => __('Float','easy-fancybox'),
					'outside' => __('Outside','easy-fancybox'),
					'inside' => __('Inside','easy-fancybox')
				),
				'default' => 'float',
				'description' => '<br />'
			),
			'titleFromAlt' => array (
				'id' => 'fancybox_DailymotiontitleFromAlt',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '1',
				'description' => __('Allow title from thumbnail alt attribute.','easy-fancybox')
			),
			'onStart' => array (
				'noquotes' => true,
				'default' => 'function(a,i,o){var splitOn=a[i].href.indexOf("?");var urlParms=(splitOn>-1)?a[i].href.substring(splitOn):"";o.allowfullscreen=(urlParms.indexOf("fullscreen=0")>-1)?false:true;o.href=a[i].href.replace(/^https?:\/\/(?:www\.)?dailymotion.com\/video\/([^\?]+)(.*)/gi,"https://www.dailymotion.com/embed/video/$1?$2&autoPlay=1");}'
			)
		)
	),

/*		'Tudou' => array(
		'id' => 'fancybox_Tudou',
		'title' => __('Tudou','easy-fancybox'),
		'label_for' => '',
		'input' => 'multiple',
		'class' => '',			'description' =>  '',
		'options' => array(
			 'autoAttributeTudou' => array (
				'id' => 'fancybox_autoAttributeTudou',
				'label_for' => '',
				'input' => 'checkbox',
				'class' => '',
				'options' => array(),
				'hide' => true,
				'default' => '1',
				'description' => __('Tudou links','easy-fancybox')
				)
			)
		),*/

/*		'Animoto' => array(),

Example ANIMOTO page link http://animoto.com/play/Kf9POzQMSOGWyu41gtOtsw should become
http://static.animoto.com/swf/w.swf?w=swf/vp1&f=Kf9POzQMSOGWyu41gtOtsw&i=m

*/

	'iFrame' => array(
		'title' => __('iFrames','easy-fancybox'),
		'input' => 'multiple',
		'options' => array(
			'intro' => array (
				'hide' => true,
				'description' => __('To make a website or HTML document open in an overlay, use the class "fancybox-iframe" for its link.','easy-fancybox') . '<br /><br />'
			),
			'tag' => array (
				'hide' => true,
				'default' => 'a.fancybox-iframe,area.fancybox-iframe,li.fancybox-iframe a'
			),
			'class' => array (
				'hide' => true,
				'default' => 'fancybox-iframe'
			),
			'type' => array (
				'default' => 'iframe'
			),
/* 				other than overflow:auto not supported on many browsers
		'scrolling' => array (
				'id' => 'fancybox_iFrameScrolling',
				'title' => __('Scrolling','easy-fancybox'),
				'label_for' => 'fancybox_iFrameScrolling',
				'input' => 'select',
				'options' => array(
					'auto' => __('Auto','easy-fancybox'),
					'yes' => __('Always','easy-fancybox'),
					'no' => __('Never','easy-fancybox')
					),
				'default' => 'auto',
				'description' => __('Define scrolling and scrollbar visibility.','easy-fancybox') . '<br />'
				),*/
			'width' => array (
				'id' => 'fancybox_iFramewidth',
				'title' => translate('Width'),
				'label_for' => 'fancybox_iFramewidth',
				'input' => 'text',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '70%',
				'description' => ' '
			),
			'height' => array (
				'id' => 'fancybox_iFrameheight',
				'title' => translate('Height'),
				'label_for' => 'fancybox_iFrameheight',
				'input' => 'text',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '90%',
			),
			'padding' => array (
				'id' => 'fancybox_iFramepadding',
				'title' => translate('Border'),
				'label_for' => 'fancybox_iFramepadding',
				'input' => 'number',
				'step' => '1',
				'min' => '0',
				'max' => '100',
				'sanitize_callback' => 'intval',
				'class' => 'small-text',
				'default' => '0',
				'description' => '<br /><br />'
			),
			'titleShow' => array (
				'id' => 'fancybox_iFrametitleShow',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '',
				'description' => __('Show title.','easy-fancybox') . ' ' . __('FancyBox will try to get a title from the link or thumbnail title attributes.','easy-fancybox')
			),
			'titlePosition' => array (
				'id' => 'fancybox_iFrametitlePosition',
				'title' => __('Title Position','easy-fancybox'),
				'label_for' => 'fancybox_iFrametitlePosition',
				'input' => 'select',
				'options' => array(
					'float' => __('Float','easy-fancybox'),
					'outside' => __('Outside','easy-fancybox'),
					'inside' => __('Inside','easy-fancybox')
				),
				'default' => 'float',
				'description' => '<br />'
			),
			'titleFromAlt' => array (
				'id' => 'fancybox_iFrametitleFromAlt',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '1',
				'description' => __('Allow title from thumbnail alt attribute.','easy-fancybox') . '<br/>'
			),
			'allowfullscreen' => array (
				'id' => 'fancybox_allowFullScreen',
				'input' => 'checkbox',
				'noquotes' => true,
				'default' => '',
				'description' => __('Allow embedded content to jump to full screen mode','easy-fancybox')
			)
		)
	)
);
