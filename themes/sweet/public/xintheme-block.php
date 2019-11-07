<?php
//载入前端样式
add_action('wp_head', function () {
	wp_enqueue_style( 'xintheme_blocks_block_assets', get_template_directory_uri().'/static/dist/blocks.style.build.css', array(), '' );
});




add_action( 'enqueue_block_assets', function () {
	if ( ! is_admin() ) {
		wp_enqueue_script('xintheme-blocks-block-front-js',get_template_directory_uri() . '/static/dist/blocks.front.build.js', array(),false, true);
	}
} );

/**
 * Enqueue Gutenberg block assets for backend editor.
 */
add_action( 'enqueue_block_editor_assets', function () {
	wp_enqueue_style( 'xintheme-blocks-block-style-css', get_template_directory_uri().'/static/dist/blocks.style.build.css', array(), '' );

	wp_enqueue_style( 'xintheme-blocks-block-editor-css', get_template_directory_uri().'/static/dist/blocks.editor.build.css', array(), '' );

	wp_enqueue_script('ecko-blocks-block-js',get_template_directory_uri() . '/static/dist/blocks.build.js', array( 'wp-blocks', 'wp-i18n', 'wp-element' ),false, true);
	
	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( 'ecko-blocks-block-js', 'xintheme-block' );
	}
} );

/**
 * Enqueue Gutenberg block dependencies
 */
add_action( 'enqueue_block_assets', function () {
	wp_enqueue_script('prism-js',get_template_directory_uri() . '/static/dist/prismjs.min.js', array(),false, true);
} );

/**
 * Add custom Gutenberg block category
 *
 * @param Array  $categories Default array of categories.
 * @param String $post Current post type.
 * @return Array Modified categories array including added category
 */


add_filter( 'block_categories', function ( $categories, $post ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug' => 'xintheme-block',
				'title' => __( '主题自带区块（XINTHEME）', 'xintheme-block' ),
			),
		)
	);
}, 10, 2 );
