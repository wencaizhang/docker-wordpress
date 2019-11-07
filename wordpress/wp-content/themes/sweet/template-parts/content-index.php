<div class="blog-post-item w-dyn-item">
	<?php  if(wpjam_has_post_thumbnail()){?>
	<a class="blog-post-image-link-block w-inline-block" title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>" style="background-image: url('<?php echo wpjam_get_post_thumbnail_url($post, [1480,620]);?>');">
		<div class="blog-posts first-blog-post-overlay">
			<div class="blog-author-wrapper w-clearfix">
				<?php  if( wpjam_theme_get_setting('show_post_author') ){?>
				<div class="blog-author-image-block" style="background-image: url('<?php echo get_avatar_url( get_the_author_meta('ID')); ?>');"></div>
				<div class="blog-author-name"><?php echo get_the_author(); ?></div>
				<?php }?>
				<div class="blog-date"><?php the_time('Y-m-d') ?></div>
			</div>
			<div class="first-blog-post-title"><?php if ( is_sticky() ){?>【置顶】<?php }?><?php the_title(); ?></div>
		</div>
	</a>
	<?php }else{ $color = get_random_color(); ?>
	<a class="blog-post-image-link-block w-inline-block" href="<?php the_permalink(); ?>" style="height: auto;">
		<div class="blog-posts first-blog-post-overlay" style="position: inherit;background-image:<?php echo $color; ?>">
			<div class="blog-author-wrapper w-clearfix">
				<?php  if( wpjam_theme_get_setting('show_post_author') ){?>
				<div class="blog-author-image-block" style="background-image: url('<?php echo get_avatar_url( get_the_author_meta('ID')); ?>');"></div>
				<div class="blog-author-name"><?php echo get_the_author(); ?></div>
				<?php }?>
				<div class="blog-date"><?php the_time('Y-m-d') ?></div>
			</div>
			<div class="first-blog-post-title"><?php if ( is_sticky() ){?>【置顶】<?php }?><?php the_title(); ?></div>
		</div>
	</a>
	<?php } ?>
	<div class="blog-summary-content-wrapper">
		<div class="summary-block">
			<div class="summary-gradient"></div>
			<p class="blog-summary-paragraph"><?php the_excerpt(); ?></p>
		</div>
		<a href="<?php the_permalink(); ?>">阅读更多 →</a>
	</div>
	<?php the_category(' ');?>
</div>