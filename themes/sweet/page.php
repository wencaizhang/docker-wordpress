<?php
get_header();

$bg_img	= wpjam_theme_get_setting('bg_img');
$color	= get_random_color();

while( have_posts() ): the_post(); ?>
<div class="blog-header">
	<div class="blog-header-overlay"></div>
	<div class="header-image-block" style="background-image: url('<?php $bg_img = $bg_img ?: wpjam_get_post_thumbnail_url(); echo wpjam_get_thumbnail($bg_img, [1800]); ?>');">
	</div>
</div>
<div class="blog-post-section section">
	<div class="blog-post-container container w-container">
		<div class="white-content-block">
			<div class="blog-post-image-block" style="<?php if(wpjam_has_post_thumbnail()){ echo "background-image: url('".wpjam_get_post_thumbnail_url($post, [1800,800])."');"; }elseif( $color ){ echo "background-image:".$color; } ?>">
				<div class="blog-post-header">
					<div class="blog-header-title-wrapper">
						<h2 class="blog-post-title"><?php the_title(); ?></h2>
					</div>
				</div>
			</div>
			<div class="align-left white-content-block-content-wrapper">
				<div class="rich-text-block w-richtext">
					<?php the_content();?>
					<?php endwhile; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php get_footer();?>