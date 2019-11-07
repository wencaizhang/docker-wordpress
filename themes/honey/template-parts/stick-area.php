<div class="feature-area">
	<div class="feature-posts">
		<?php
			$sticky = get_option('sticky_posts');  
			rsort( $sticky );  
			query_posts( array( 'post__in' => $sticky, 'ignore_sticky_posts'=>1, 'posts_per_page'=>3) );
			if (have_posts()) :  
			$i = 1;
			while (have_posts()) : the_post();
			$img = xintheme_image('full', true);
		?>
		<div class="post-item<?php if ( 1 === $i ) : ?> active<?php endif; ?>">
			<div class="feature-bg" style="background-image:url(<?php echo esc_url($img['url']);?>);"></div>
			<div class="post-content">
				<div class="container xintheme-middle">
					<div class="entry-content">
						<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="more-link xintheme-hover xintheme-meta">
							<a href="<?php the_permalink(); ?>">阅读更多</a>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php $i++; endwhile; endif; wp_reset_query();?>
	</div>
</div>