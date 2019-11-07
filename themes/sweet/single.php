<?php
get_header();

$bg_img	= wpjam_theme_get_setting('bg_img');
$color	= get_random_color();

while( have_posts() ){ the_post(); ?>
<div class="blog-header">
	<div class="blog-header-overlay"></div>
	<div class="header-image-block" style="background-image: url('<?php $bg_img = $bg_img ?: wpjam_get_post_thumbnail_url(); echo wpjam_get_thumbnail($bg_img, [1800]); ?>');"></div>
</div>
<div class="blog-post-section section">
	<div class="blog-post-container container w-container">
		<div class="white-content-block">
			<div class="blog-post-image-block" style="<?php if(wpjam_has_post_thumbnail()){ echo "background-image: url('".wpjam_get_post_thumbnail_url($post, [1800,800])."');"; }elseif( $color ){ echo "background-image:".$color; } ?>">
				<?php the_category(' ');?>
				<div class="blog-post-header">
					<div class="blog-header-title-wrapper">
						<div class="blog-post-date"><?php the_time('Y-m-d') ?></div>
						<h2 class="blog-post-title"><?php the_title(); ?></h2>
					</div>
				</div>
			</div>
			<div class="align-left white-content-block-content-wrapper">
				<div class="rich-text-block w-richtext">
					<?php the_content();?>
				</div>
				<div class="blog-author-block w-clearfix">
					<div class="blog-author-image" style="background-image: url('<?php echo get_avatar_url( get_the_author_meta('ID'), ['size'=>200] ); ?>');"></div>
					<div class="blog-author-title name"><?php echo get_the_author(); ?></div>
					<div class="blog-author-title">
						<?php if(get_the_author_meta('description')){ echo the_author_meta( 'description' ); }else{ echo '我还没有学会写个人说明！'; }?>
					</div>
					<a href="<?php echo get_author_posts_url( get_the_author_meta('ID') ) ?>" style="font-size: 14px">查看“<?php echo get_the_author(); ?>”的所有文章 →</a>
				</div>
			</div>
		</div>
		<?php comments_template( '', true ); ?>
	</div>
</div>

<?php } ?>

<div class="light-tint section">
	<div class="container w-container">
		<div class="section-title-wrapper">
			<h2 class="section-title">相关推荐</h2>
		</div>
		<div class="blog-list-wrapper w-dyn-list">
			<div class="blog-posts-list w-clearfix w-dyn-items w-row">
				<?php $related_query	= wpjam_get_related_posts_query(3);

				if($related_query->have_posts()){ ?>

				<?php while( $related_query->have_posts() ) { $related_query->the_post(); ?>

				<div class="blog-post-item w-col w-col-4 w-dyn-item">
					<a class="blog-post-image-link-block small w-inline-block" title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>" style="background-image: url('<?php echo wpjam_get_post_thumbnail_url($post, [560, 360]);?>');">
						<div class="blog-author-wrapper small w-clearfix">
							<div class="blog-author-image-block small" style="background-image: url('<?php echo get_avatar_url( get_the_author_meta('ID') ); ?>');"></div>
							<div class="blog-author-name small"><?php echo get_the_author(); ?></div>
							<div class="blog-date small"><?php the_time('Y-m-d') ?></div>
						</div>
					</a>
					<a class="blog-title-link" title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</div>

				<?php } } else {?> 

				<p>暂无相关文章!</p>

				<?php } wp_reset_query(); ?>
				
			</div>
		</div>
	</div>
</div>
<?php get_footer();?>