<?php
$logo = wpjam_theme_get_setting('logo');
$bg_img = wpjam_theme_get_setting('bg_img');?>
<div class="hero-section">
	<div class="hero-recent-post-list-wrapper w-dyn-list">
		<div class="hero-recent-post-list w-dyn-items">
			<div class="hero-recent-post-item w-dyn-item" style="background-image: url('<?php $bg_img = $bg_img ?: wpjam_get_post_thumbnail_url(); echo wpjam_get_thumbnail($bg_img, [1800]); ?>');">
			</div>
		</div>
	</div>
	<div class="hero-overlay-section" <?php if( !$logo ){?>style="padding-top: 150px"<?php }?>>
		<div class="container w-container">
			<div class="white-content-block">
				<div class="blog-list-wrapper w-dyn-list">
					<div class="blog-archive-list fullwidth w-clearfix w-dyn-items">
					<?php if ( have_posts() ) { while( have_posts() ) { the_post();
						get_template_part('template-parts/content','index');
					} }?>
					</div>
				</div>
				
				<?php if( $wp_query->max_num_pages > 1 ){ ?>
				
				<div class="tint white-content-block-content-wrapper"><?php wpjam_pagenavi();?></div>
				
				<?php }else{?>

				<div class="tint white-content-block-content-wrapper">没有更多文章了...</div>
				
				<?php }?>

			</div>
		</div>
	</div>
</div>
<div class="section">
	<div class="container w-container">
		<?php if( $featured_title = wpjam_theme_get_setting('featured_title') ){ ?>
		<div class="section-title-wrapper">
			<h2 class="section-title"><?php echo $featured_title;?></h2>
		</div>
		<?php }?>

		<?php if( $featured_post_ids = wpjam_theme_get_setting('featured_post_ids') ) { ?>

		<div class="blog-list-wrapper w-dyn-list">
			<div class="blog-posts-list w-clearfix w-dyn-items w-row">

			<?php $featured_query = wpjam_query(['ignore_sticky_posts'=>true, 'post__in'=>$featured_post_ids]);

			if($featured_query->have_posts()){ ?>

			<?php while($featured_query->have_posts()){ $featured_query->the_post(); ?>

				<div class="blog-post-item w-col w-col-4 w-dyn-item">
					<a class="blog-post-image-link-block small w-inline-block" title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>" style="background-image: url('<?php echo wpjam_get_post_thumbnail_url($post, [440,300]);?>');">
						<div class="blog-author-wrapper small w-clearfix">
							<div class="blog-author-image-block small" style="background-image: url('<?php echo get_avatar_url( get_the_author_meta('ID') ); ?>');"></div>
							<div class="blog-author-name small"><?php echo get_the_author() ?></div>
							<div class="blog-date small"><?php the_time('Y-m-d') ?></div>
						</div>
					</a>
					<a class="blog-title-link" title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</div>

			<?php } } wp_reset_query(); ?>
				
			</div>
		</div>

		<?php } ?>
	</div>
</div>