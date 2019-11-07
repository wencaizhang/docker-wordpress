<?php

# 常量定义
# =========================================================================
define('THEME_DIR',		get_template_directory_uri());

# 主题功能支持
# =========================================================================
add_action('after_setup_theme', 'xintheme_setup');
if (!function_exists('xintheme_setup')) {

    function xintheme_setup() {
        add_editor_style();
				add_theme_support('title-tag');
        register_nav_menus(array('main' => esc_html__('主菜单', 'xintheme')));

        add_theme_support('post-thumbnails');
				add_image_size('xintheme_grid_thumb', 300, 200, true);
        add_image_size('xintheme_list_thumb', 266, 169, true);
    }

}
if( wpjam_get_setting('wpjam_theme', 'foot_link') ) {
	add_filter('pre_option_link_manager_enabled', '__return_true');	/*激活后台链接*/
}
# 载入JS和css
# =========================================================================
add_action('wp_enqueue_scripts', 'xintheme_scripts');
function xintheme_scripts() {
	wp_enqueue_style('xintheme-bootstrap',	THEME_DIR . '/assets/css/bootstrap.min.css');
	wp_enqueue_style('xintheme-style',		THEME_DIR . '/style.css');
	wp_enqueue_style('xintheme-iconfont',	THEME_DIR . '/assets/fonts/iconfont.css');
	wp_enqueue_style('xintheme-responsive',	THEME_DIR . '/assets/css/responsive.css');

  wp_enqueue_script('xintheme-scripts', THEME_DIR . '/assets/js/scripts.js', array('jquery'), false, true);
  if (is_single() && comments_open()) {
    wp_enqueue_script('comment-reply');
  }
  wp_enqueue_script('xintheme-script', THEME_DIR . '/assets/js/waves-script.js');
}

# LOGO
# =======================================================
function xintheme_logo() {
    $logo = wpjam_theme_get_setting('logo');
    echo '<div class="xintheme-logo">';
        if ( !empty($logo) ) {
            echo '<a class="logo" href="' . esc_url(home_url('/')) . '">';
                echo '<img class="logo-img" src="' . esc_url($logo) . '" alt="' . esc_attr(get_bloginfo('name')) . '"/>';
            echo '</a>';
        } else {
            echo '<h1 class="site-name"><a class="logo" href="' . esc_url(home_url('/')) . '">';
                    bloginfo('name');
            echo '</a></h1>';
        }
    echo '</div>';
}

# 菜单
# =======================================================
function xintheme_menu() {
    wp_nav_menu(array(
        'container' => false,
        'menu_id' => '',
        'menu_class' => 'sf-menu',
        'fallback_cb' => 'xintheme_nomenu',
        'theme_location' => 'main'
    ));
}
function xintheme_nomenu() {
    echo "<ul class='sf-menu'>";
        $howmany = 5;
        $pages=wp_list_pages(array('title_li'=>'','echo'=>0));
        preg_match_all('/(<li.*?>)(.*?)<\/li>/i', $pages, $matches);
        if(!empty($matches[0])){echo implode("\n", array_slice($matches[0],0,5));}
    echo "</ul>";
}

function xintheme_mobilemenu($loc = 'main') {
    wp_nav_menu(array(
        'container' => false,
        'menu_id' => '',
        'menu_class' => 'sf-mobile-menu clearfix',
        'fallback_cb' => 'xintheme_nomobile',
        'theme_location' => $loc)
    );
}
function xintheme_nomobile() {
    echo "<ul class='clearfix'>";
    wp_list_pages(array('title_li' => ''));
    echo "</ul>";
}

# 导航栏社交按钮
# =======================================================
function xintheme_social_icons(){
    $socials 						= wpjam_theme_get_setting('social');
		$header_qq_url 			= wpjam_theme_get_setting('header_qq_url');
		$header_weibo_url 	= wpjam_theme_get_setting('header_weibo_url');
		$header_weixin_img	= wpjam_theme_get_setting('header_weixin_img');
		$header_email_url 	= wpjam_theme_get_setting('header_email_url');
    if(!empty($socials)){
      $output = '<div class="social-icons">';
			if( $header_qq_url ){
				$output .='<a title="QQ" href="http://wpa.qq.com/msgrd?v=3&uin='.$header_qq_url.'&site=qq&menu=yes" rel="nofollow" target="_blank"><i class="iconfont icon-QQ"></i></a>';
			}
			if( $header_weibo_url ){
				$output .='<a title="QQ" href="'.$header_weibo_url.'" rel="nofollow" target="_blank"><i class="iconfont icon-weibo"></i></a>';
			}
			if( $header_weixin_img ){
				$output .='<a title="QQ" href="http://wpa.qq.com/msgrd?v=3&uin='.$header_weixin_img.'&site=qq&menu=yes" rel="nofollow" target="_blank"><i class="iconfont icon-weixin"></i></a>';
			}
			if( $header_email_url ){
				$output .='<a title="QQ" href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email='.$header_email_url.'" rel="nofollow" target="_blank"><i class="iconfont icon-youxiang"></i></a>';
			}
      $output .= '</div>';
      return $output;
    }
}

# 导航栏搜索按钮
# =======================================================
function xintheme_searchmenu() {
	if( wpjam_theme_get_setting('header_search') ){
    $form = '<form method="get" class="searchform on-menu" action="' . esc_url(home_url('/')) . '" >';
    $form .= '<div class="input"><input type="text" value="' . get_search_query() . '" name="s" placeholder="" /><i class="iconfont icon-sousuo"></i></div>';
    $form .= '</form>';
    return $form;
	}
}
# 缩略图
# =======================================================
if (!function_exists('xintheme_image')) {
    function xintheme_image($size = 'full', $returnURL = false) {
        global $post;
        $attachment = get_post(get_post_thumbnail_id($post->ID));
        if(!empty($attachment)){
            if ($returnURL) {
                $lrg_img = wp_get_attachment_image_src($attachment->ID, $size);
                $url = $lrg_img[0];
                $alt0 = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
                $alt = empty($alt0)?$attachment->post_title:$alt0;
                $img['url'] = $url;
                $img['alt'] = $alt;
                return $img;
            } else {
                return get_the_post_thumbnail($post->ID,$size);
            }
        }
    }
}

# 博客列表
# =======================================================
function xintheme_standard_media($post, $atts) {
    if (has_post_thumbnail($post->ID)) {
        $output = '<div class="entry-media">';
        $output .= '<div class="xintheme-thumbnail">';
        $output .= xintheme_image($atts['img_size']);
        if (is_single($post)) {
            $img = xintheme_image('full', true);
            $output .= '<div class="image-overlay xintheme-middle"><div class="image-overlay-inner">';
            $output .= '<a href="' . esc_url($img['url']) . '" rel="prettyPhoto[' . esc_attr($post->ID) . ']" title="' . esc_attr(get_the_title()) . '" class="overlay-icon">';
            $output .= '</a></div></div>';
        } else {
            $output .= '<div class="image-overlay xintheme-middle"><div class="image-overlay-inner">';
            $output .= '<a href="' . esc_url(get_permalink()) . '" title="' . esc_attr(get_the_title()) . '" class="overlay-icon">';
            $output .= '</a></div></div>';
        }
        $output .= '</div>';
        $output .= '</div>';
        return $output;
    }
}
# 文章类别
# =======================================================
function xintheme_cats($sep = ' · '){
    $cats = '';
    foreach((get_the_category()) as $category) {
        $options = get_option("taxonomy_".$category->cat_ID);
        if (!isset($options['featured']) || !$options['featured']) {
            $cats .= '<a href="' . get_category_link( $category->term_id ) . '" title="' . sprintf( __( '查看《%s》下的所有文章', 'xintheme' ), $category->name ) . '" ' . '>'  . $category->name.'</a><span>'.$sep.'</span>';
        }
    }
    return $cats;
}

# 文章摘要
# =======================================================
add_filter('the_excerpt',function($post_excerpt){
	global $post;
	$meta_data = get_post_meta(get_the_ID(), 'extend_info', true); 	
	$post_abstract = isset($meta_data['post_abstract']) ?$meta_data['post_abstract'] : '';

    if(has_post_thumbnail($post->ID)){
		if(!empty($post_abstract)){
			return mb_strimwidth($post_abstract, 0, 180, '...');
		}else{
			return mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 180,"...");
		}
    } else {
		if(!empty($post_abstract)){
			return mb_strimwidth($post_abstract, 0, 300, '...');
		}else{
			return mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 300,"...");
		}
    } 
});

# 分页
# =======================================================
function xintheme_pagination() { ?>
    <div class="xintheme-pagination xintheme-hover xintheme-meta clearfix">
        <div class="older"><?php next_posts_link('<span>'.esc_html__( '下一页', 'xintheme').'</span><i class="iconfont icon-enter"></i>' ); ?></div>
        <div class="newer"><?php previous_posts_link('<i class="iconfont icon-return"></i><span>'.esc_html__( '上一页', 'xintheme').'</span>'); ?></div>
    </div>
<?php }

# 禁止全英文评论
# =======================================================
function xintheme_prohibit_comment_post( $incoming_comment ) {
    $pattern = '/[一-龥]/u';
    if(!preg_match($pattern, $incoming_comment['comment_content'])) {
    wp_die( "抱歉，本站禁止全英文评论，请输入一些汉字，谢谢！" );
    }
    return( $incoming_comment );
}
add_filter('preprocess_comment', 'xintheme_prohibit_comment_post');

# 评论框
# =======================================================
if (!function_exists('xintheme_comment_form')) {
    function xintheme_comment_form($fields) {
        global $id, $post_id;
        if (null === $post_id)
            $post_id = $id;
        else
            $id = $post_id;

        $commenter = wp_get_current_commenter();
        $req = get_option('require_name_email');
        $aria_req = ( $req ? " aria-required='true'" : '' );

        $fields = array(
            'author' => '<p class="comment-form-author">' .
            '<input id="author" name="author" required="required" placeholder="' . esc_html__('昵称 *', 'xintheme') . '" type="text" value="' . esc_attr($commenter['comment_author']) . '" size="30"' . $aria_req . ' />' . '</p>',
            'email' => '<p class="comment-form-email">' .
            '<input id="email" name="email" required="required" placeholder="' . esc_html__('邮箱 *', 'xintheme') . '" type="text" value="' . esc_attr($commenter['comment_author_email']) . '" size="30"' . $aria_req . ' />' . '</p>',
            'url' => '<p class="comment-form-url">' .
            '<input id="url" name="url" placeholder="' . esc_html__('网址', 'xintheme') . '" type="text" value="' . esc_attr($commenter['comment_author_url']) . '" size="30" />' . '</p><div class="clearfix"></div>',
        );
        return $fields;
    }
    add_filter('comment_form_default_fields', 'xintheme_comment_form');
}

# 评论框  用户信息栏显示在上方
# =======================================================
function xintheme_recover_comment_fields($comment_fields){
    $comment = array_shift($comment_fields);
    $comment_fields =  array_merge($comment_fields ,array('comment' => $comment));
    return $comment_fields;
}
add_filter('comment_form_fields','xintheme_recover_comment_fields');

# 添加@评论
# =======================================================
add_filter('comment_text', 'xintheme_comment_add_at_parent');
function xintheme_comment_add_at_parent($comment_text) {
    $comment_ID = get_comment_ID();
    $comment = get_comment($comment_ID);
    if ($comment->comment_parent) {
        $parent_comment = get_comment($comment->comment_parent);
        $comment_text = '<a href="#comment-' . $comment->comment_parent . '"><span class="parent-icon">@' . $parent_comment->comment_author . '</a></span> ' . $comment_text;
    }
    return $comment_text;
}

# 评论列表
# =======================================================
if (!function_exists('xintheme_comment')) {
    function xintheme_comment($comment, $args, $depth){
        $GLOBALS['comment'] = $comment;?>
        <div <?php comment_class();?> id="comment-<?php comment_ID(); ?>">
            <div class="comment-author">
                <?php echo get_avatar($comment, $size = '50'); ?>
            </div>
            <div class="comment-text">
                <h3 class="author"><?php echo get_comment_author_link(); ?></h3>
                <span class="entry-date xintheme-meta"><?php echo get_comment_date('Y-m-d'); ?></span>
                <?php comment_text() ?>
                <p class="reply xintheme-meta"><?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth'], 'reply_text' => " 回复"))) ?></p>
            </div><?php
    }
}

# 评论数
# =======================================================
if (!function_exists('xintheme_comment_count')) {
    function xintheme_comment_count() {
        if (comments_open()) {
            $comment_count = get_comments_number('0', '1', '%');
            if ($comment_count == 0) {
                $comment_trans = esc_html__('暂无评论', 'xintheme');
            } elseif ($comment_count == 1) {
                $comment_trans = esc_html__('1 条评论', 'xintheme');
            } else {
                $comment_trans = $comment_count . ' ' . esc_html__('条评论', 'xintheme');
            }
            return "<a href='" . esc_url(get_comments_link()) . "' title='" . esc_attr($comment_trans) . "' class='comment-count'><i class='iconfont icon-interactive'></i><span>" . esc_html($comment_trans) . "</span></a>";
        }
    }
}

# 作者信息
# =======================================================
function xintheme_author(){ ?>
    <div class="xintheme-author">
        <div class="author-box">
            <?php
            $tw_author_email = get_the_author_meta('email');
            echo get_avatar($tw_author_email, $size = '80');
            ?>
            <h3><?php
                if (is_author()){
                    the_author();
                }else{
                    the_author_posts_link();
                } ?>
            </h3>
            <?php
            echo '<p>';
                $description = get_the_author_meta('description');
                if ($description != '')
                    echo esc_html($description);
                else
                    esc_html_e('请到【后台 - 用户 - 我的个人资料】中填写个人说明。', 'xintheme');
            echo '</p>';
			?>
        </div>
    </div><?php
}

# 去除加载的css和js后面的版本号
# =======================================================
function _remove_script_version( $src ){
    $parts = explode( '?', $src );
    return $parts[0];
}
add_filter( 'script_loader_src', '_remove_script_version', 15, 1 );
add_filter( 'style_loader_src', '_remove_script_version', 15, 1 );
add_filter( 'pre_option_link_manager_enabled', '__return_true' );

# 文章浏览量
# =======================================================
function wpjam_theme_post_views($before = '(点击 ', $after = ' 次)', $echo = 1){
	global $post;
	$post_ID	= $post->ID;
	$views		= (int)get_post_meta($post_ID, 'views', true);
	if ($echo) {
		echo $before, number_format($views), $after;
	}else{
		return $views;
	}
};

add_action('wp_head', function (){
	if (is_singular()) { 
		global $post;
		wpjam_update_post_views($post->ID);
	}
});