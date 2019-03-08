<?php get_header(); the_post();?>
<div class="xintheme-container container">
	<div class="row">
<div class="content-area with-sidebar col-md-12">
	<div class="theiaStickySidebar">
		<article <?php post_class('single'); ?>>
			<?php 			
				echo '<h1 class="entry-title">'.get_the_title().'</h1>';
				
				echo '<div class="entry-date xintheme-meta">'.xintheme_cats().''.get_the_time(get_option('date_format')).'';
				echo '<span> · </span>'.wpjam_get_post_views(get_the_ID()).' 次浏览</div>';
			?>
            <div class="entry-content">
                <?php the_content(); ?>
                <?php //wp_link_pages(); ?>
                <div class="clearfix"></div>
            </div>
		<?php
			echo get_the_tag_list(('<div class="entry-tags xintheme-meta"><h5>'.esc_html__('标签', 'xintheme').':</h5>'), '', '</div>');
			$post_image = xintheme_image('full', true);
			echo '<div class="entry-share clearfix">';
				echo '<a class="qq-share" href="' . esc_url(get_permalink()) . '" title="分享到QQ" data-title="' . esc_attr(get_the_title()) . '" data-image="' . esc_attr($post_image['url']) . '" data-excerpt="'. get_the_excerpt() .'"><i class="iconfont icon-QQ"></i></a>';
                echo '<a class="weixin-share" href="' . esc_url(get_permalink()) . '" title="分享到微信" data-image="' . esc_attr($post_image['url']) . '"><i class="iconfont icon-weixin"></i></a>';
                echo '<a class="weibo-share" href="' . esc_url(get_permalink()) . '" title="分享到新浪微博" data-title="' . esc_attr(get_the_title()) . '" data-image="' . esc_attr($post_image['url']) . '" data-excerpt="'. get_the_excerpt() .'"><i class="iconfont icon-weibo"></i></a>';
            echo xintheme_comment_count();
            echo '</div>';
		?>
		</article>
        <?php 
            $prev = get_adjacent_post(false,'',true) ;
            $next = get_adjacent_post(false,'',false) ;
        ?>
        <div class="nextprev-postlink">
            <div class="row">
                <div class="col-md-6">
                <?php if ( isset($prev->ID) ):
                $pid = $prev->ID;
                $img = wp_get_attachment_image_src( get_post_thumbnail_id($pid), 'thumbnail' );
                if($img['0']){
                    $thumb = '<div class="post-thumb"><div style="background-image: url('.esc_url($img['0']).')"></div></div>';
                }else{
                    $pformat = get_post_format( $pid ) == "" ? "standard" : get_post_format( $pid );
                    $thumb = '<div class="post-thumb format-icon '.esc_attr($pformat).'"></div>';
                } ?>
                    <div class="prev-post-link">
                        <a href="<?php echo esc_url(get_permalink( $pid )); ?>" title="<?php echo get_the_title( $pid );?>"><?php echo ($thumb .'<h4>'.get_the_title( $pid ).'</h4><span class="xintheme-meta"><i class="iconfont icon-icon_shangyipian"></i>'.esc_html__('上一篇', 'xintheme').'</span>'); ?></a>
                    </div>
                <?php endif;
                if ( isset($next->ID) ):
                    $pid = $next->ID;
                    $img = wp_get_attachment_image_src( get_post_thumbnail_id($pid), 'thumbnail' );
                    if($img['0']){
                        $thumb = '<div class="post-thumb"><div style="background-image: url('.esc_url($img['0']).')"></div></div>';
                    }else{
                        $pformat = get_post_format( $pid ) == "" ? "standard" : get_post_format( $pid );
                        $thumb = '<div class="post-thumb format-icon '.esc_attr($pformat).'"></div>';
                    } ?>
                    </div>
                    <div class="col-md-6">
                        <div class="next-post-link">
                            <a href="<?php echo esc_url(get_permalink( $pid )); ?>"><?php echo ($thumb .'<h4>'.get_the_title( $pid ).'</h4><span class="xintheme-meta">'.esc_html__('下一篇', 'xintheme').'<i class="iconfont icon-icon_xiayipian"></i></span>'); ?></a>
                        </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
		<?php xintheme_author(); ?>
		<?php comments_template('', true); ?>
	</div>
</div>

	</div>
</div>
<?php get_footer();?>