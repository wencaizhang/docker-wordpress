<?php
/*
Template Name: 首页-2，演示文件
*/
get_header();?>
<style>
.page .container {position: relative;padding-right: 100px;padding-left: 100px}
@media (max-width:767px){
  .page .container {position: relative;padding-right: 0;padding-left: 0}
}
</style>
<?php
$logo = wpjam_theme_get_setting('logo');?>
<div class="hero-section">
	<div class="hero-recent-post-list-wrapper w-dyn-list">
		<div class="hero-recent-post-list w-dyn-items">
			<div class="hero-recent-post-item w-dyn-item" style="background-image: url('http://sweet-xintheme-cn.oss-cn-beijing.aliyuncs.com/wp-content/uploads/2019/03/wKgEaVyRwZGATsLhAAmcvWDghBE69.jpeg');">
			</div>
		</div>
	</div>
	<div class="hero-overlay-section" <?php if( !$logo ){?>style="padding-top: 150px"<?php }?>>
		<div class="container w-container">
			<div class="white-content-block">
				<div class="blog-list-wrapper w-dyn-list">
					<div class="blog-archive-list fullwidth w-clearfix w-dyn-items">
						<?php
						$args = array(
						'ignore_sticky_posts' => 1,
						'showposts' => 3,
						'paged' => $paged
						);
						query_posts($args);
						if ( have_posts() ) {
							while(have_posts()) {
							the_post();
							get_template_part('template-parts/content','index');
							}
						}?>
					</div>
				</div>
				<div class="tint white-content-block-content-wrapper">
					没有更多文章了...
				</div>
			</div>
		</div>
	</div>
</div>
<div class="section">
	<div class="container w-container">
		<?php
		$tj_post_title = wpjam_theme_get_setting('tj_post_title');
		$tj_post_id	= wpjam_theme_get_setting('tj_post_id');?>
		<?php if( $tj_post_title ){?>
		<div class="section-title-wrapper">
			<h2 class="section-title"><?php echo $tj_post_title;?></h2>
		</div>
		<?php }?>
		<div class="blog-list-wrapper w-dyn-list">
			<div class="blog-posts-list w-clearfix w-dyn-items w-row">
				<?php
				foreach ($tj_post_id as $post_id ){
				$posts = get_posts('numberposts=3&post_type=any&include='.$post_id.'');
				if($posts) : foreach( $posts as $post ) : setup_postdata( $post ); ?>
				<div class="blog-post-item w-col w-col-4 w-dyn-item">
					<a class="blog-post-image-link-block small w-inline-block" href="<?php the_permalink(); ?>" style="background-image: url('<?php echo wpjam_get_post_thumbnail_url($post,array(), $crop=1);?>');">
					<div class="blog-author-wrapper small w-clearfix">
						<div class="blog-author-image-block small" style="background-image: url('<?php echo get_avatar_url( get_the_author_meta('email'), array("size"=>250) ); ?>');">
						</div>
						<div class="blog-author-name small">
							<?php echo get_the_author() ?>
						</div>
						<div class="blog-date small">
							<?php the_time('Y-m-d') ?>
						</div>
					</div>
					</a><a class="blog-title-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</div>
				<?php endforeach; endif; }?>
			</div>
		</div>
	</div>
</div>
<?php get_footer();?>