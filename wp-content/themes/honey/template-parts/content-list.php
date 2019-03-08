<?php
if ( is_home() ) {
	$args = array(
		'ignore_sticky_posts' => 1,
		'paged' => $paged,
		'category__not_in'=> wpjam_theme_get_setting('index_no_cat'),
	);
	query_posts($args);
}
if (have_posts()) {
    $atts['img_size'] = 'xintheme_list_thumb';

	if ( is_home() ) {
		echo '<h3><i class="iconfont icon-zuixin1"></i>最新文章</h3>';
	}else {
		if ( is_search() ) {
			echo '<div class="breadcrumbs">';
			echo '关键词：“'.$s.'” '. '，共搜到 ' . $wp_query->found_posts . ' 篇文章'.'';
			echo '</div>';
		}elseif( is_author() ){
			echo '<div class="breadcrumbs">';
			echo ''.xintheme_author().'';
			echo '</div>';
		}else {
			echo '<div class="breadcrumbs">';
			echo '<i class="iconfont icon-tripposition"></i><span>当前位置</span><i class="iconfont icon-enter"></i><a href="'.get_option('home').'">首页</a><i class="iconfont icon-enter"></i>'.xintheme_cats().'';
			echo '</div>';
		}
	}
	echo '<div class="list-blog">';
    while (have_posts()) { the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php
            $media = xintheme_standard_media($post, $atts);
            if($media){
                echo '<div class="entry-post">';
            } else {
                echo '<div class="entry-post no-media">';
            }                           

			ob_start();
			echo balanceTags($media);
			echo '<div class="entry-cats">'.xintheme_cats().'';
			echo '<div class="entry-date xintheme-meta">'.get_the_time(get_option('date_format')).'</div>';
			echo '</div>';
			echo '<h2 class="entry-title"><a href="'.esc_url(get_permalink()).'">'.get_the_title().'</a></h2>';                    

			echo '<div class="entry-content clearfix">';
			echo  the_excerpt();
			echo '</div>';

            echo '</div>';
            ?>
        </article><?php
    }
    echo '</div>';
    xintheme_pagination();
}   
