<?php 
	if(is_home()&&!is_paged()){
	if( wpjam_theme_get_setting('popular_area') ){
?>
<div class="xintheme-popular-posts">
		<h3><i class="iconfont icon-remen"></i>热评文章</h3>
	<div class="row">
		<?php
			query_posts('posts_per_page=4&ignore_sticky_posts=1&orderby=comment_count');
			while (have_posts()) : the_post();
		?>
		<div class="col-md-3">
			<div class="popular-thumb">
				<a href="<?php the_permalink(); ?>">
					<?php echo xintheme_image('xintheme_grid_thumb');?>
				</a>
				<div class="popular-content xintheme-middle">
					<div class="entry-content">
						<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<span class="entry-date xintheme-meta"><?php the_time('Y-m-d') ?></span>
						<a href="<?php the_permalink(); ?>"><i class="iconfont icon-jia"></i></a>
					</div>
				</div>
			</div>
		</div>
		<?php endwhile; wp_reset_query();?>
	</div>
</div>
<?php } } ?>