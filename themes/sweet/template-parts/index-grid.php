<?php $bg_img	= wpjam_theme_get_setting('bg_img'); ?>
<?php if ( is_author() ) { ?>
	<div class="author dynamic-intro-section">
		<div class="hero-overlay-section">
			<div class="container w-container">
				<div class="header-author-block">
					<div class="header-author-image" style="background-image: url('<?php echo get_avatar_url( get_the_author_meta('email'), array("size"=>250) ); ?>');">
					</div>
					<div class="author-social-wrapper">
						<a class="social-button w-inline-block" target="_blank" rel="nofollow" href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=<?php echo get_the_author_meta( 'email' );?>">
							<img class="social-icon" src="<?php bloginfo('template_directory'); ?>/static/images/mail.svg">
						</a>
					</div>
					<div class="header-author-name">
						<?php echo get_the_author(); ?>
					</div>
					<div class="rich-text-block w-richtext">
						<p>
							<?php if(get_the_author_meta('description')){ echo the_author_meta( 'description' );}else{echo'我还没有学会写个人说明！'; }?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<div class="hero-recent-post-list-wrapper w-dyn-list">
			<div class="hero-recent-post-list w-dyn-items">
				<div class="hero-recent-post-item w-dyn-item" style="background-image: url('<?php $bg_img = $bg_img ?: wpjam_get_post_thumbnail_url(); echo wpjam_get_thumbnail($bg_img, [1800]); ?>');">
				</div>
			</div>
		</div>
	</div>
<?php } else{ $sticky_posts	= get_option('sticky_posts') ?>
<div class="<?php echo ($sticky_posts && is_home()) ? 'dynamic-intro-section' : 'dynamic-intro-section without-content'; ?>">
<?php if ( is_home() && $sticky_posts) {  $slide_query	= wpjam_query(['p' => $sticky_posts[0]]);  ?>

	<?php if($slide_query->have_posts()){ ?>

	<?php while($slide_query->have_posts()){ $slide_query->the_post(); ?>

	<div class="hero-overlay-section mobile">
		<div class="container w-container">
			<div class="white-content-block">
				<div class="blog-list-wrapper w-dyn-list">
					<div class="blog-archive-list fullwidth w-clearfix w-dyn-items">
						<div class="blog-post-item w-dyn-item">
							<a class="blog-post-image-link-block w-inline-block" title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>" style="background-image: url('<?php echo wpjam_get_post_thumbnail_url(null, [1480,620]);?>');">
							<div class="blog-posts first-blog-post-overlay">
								<div class="blog-author-wrapper w-clearfix">
									<div class="blog-author-image-block" style="background-image: url('<?php echo get_avatar_url( get_the_author_meta('ID') ); ?>');"></div>
									<div class="blog-author-name"><?php echo get_the_author(); ?></div>
									<div class="blog-date"><?php the_time('Y-m-d'); ?></div>
								</div>
								<div class="first-blog-post-title">【置顶】<?php the_title(); ?></div>
							</div>
							</a>
							<div class="blog-summary-content-wrapper">
								<div class="summary-block">
									<div class="summary-gradient"></div>
									<p class="blog-summary-paragraph"><?php the_excerpt(); ?></p>
								</div>
								<a href="<?php the_permalink(); ?>">阅读更多 →</a>
							</div>
							<?php the_category(' ');?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } wp_reset_postdata(); } ?>
<?php } ?>

	<div class="hero-recent-post-list-wrapper w-dyn-list">
		<div class="hero-recent-post-list w-dyn-items">
			<div class="hero-recent-post-item w-dyn-item" style="background-image: url('<?php $bg_img = $bg_img ?: wpjam_get_post_thumbnail_url(); echo wpjam_get_thumbnail($bg_img, [1800]); ?>');"></div>
		</div>
	</div>
</div>
<?php } ?>
<div class="section">
	<div class="container w-container">
		<?php if( is_category() ){ ?>

		<?php

		$navbar_type	= wpjam_theme_get_setting('navbar_type');
		$style			= in_array($navbar_type, ['left', 'right']) ? 'style="text-align: '.$navbar_type.'"' : '';
		;
		?>

		<div class="section-title-wrapper" <?php echo $style; ?>>
			<h3 class="color dynamic-subtitle section-title subtitle"><?php single_cat_title(); ?></h3>
			<?php if($description	= category_description()) { ?><h3 class="dynamic-subtitle section-title subtitle">：<?php echo $description; ?></h3><?php }?>
		</div>

		<?php }?>

		<?php if ( have_posts() ) { ?>
		
		<?php while ( have_posts() ) { the_post();  ?>

		<?php if ($wp_query->current_post == 0) { ?>

		<div class="blog-list-wrapper w-dyn-list">
			<div class="w-dyn-items">
				<div class="first-blog-post-item w-dyn-item">
					<a class="big-archive blog-post-image-link-block w-inline-block" title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>" style="background-image: url('<?php echo wpjam_get_post_thumbnail_url($post, [1400,600]);?>');">
						<div class="first-blog-post-overlay">
							<div class="blog-author-wrapper w-clearfix">
								<div class="blog-author-image-block" style="background-image: url('<?php echo get_avatar_url( get_the_author_meta('ID') ); ?>');"></div>
								<div class="blog-author-name"><?php echo get_the_author(); ?></div>
								<div class="blog-date"><?php the_time('Y-m-d') ?></div>
							</div>
							<div class="first-blog-post-title"><?php the_title(); ?></div>
						</div>
					</a>
					<?php the_category(' ');?>
				</div>
			</div>
		</div>

		<div class="blog-list-wrapper w-dyn-list">
			<div class="blog-archive-list w-clearfix w-dyn-items w-row">
				
				<?php } else { ?>
				
				<div class="blog-post-item w-col w-col-4 w-dyn-item">
					<a class="blog-post-image-link-block medium w-inline-block" title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>" style="background-image: url('<?php echo wpjam_get_post_thumbnail_url($post, [440,440]);?>');">
					<div class="first-blog-post-overlay medium">
						<div class="first-blog-post-title medium"><?php the_title(); ?></div>
						<div class="blog-author-wrapper medium w-clearfix">
							<div class="blog-author-image-block small" style="background-image: url('<?php echo get_avatar_url( get_the_author_meta('ID') ); ?>');"></div>
							<div class="blog-author-name small"><?php echo get_the_author(); ?></div>
							<div class="blog-date small"><?php the_time('Y-m-d') ?></div>
						</div>
					</div>
					</a>
				</div>
				
				<?php } ?>

				<?php } } ?>
			</div>
		</div>
		
		<?php if(is_author()){ ?>

		<div class="tint white-content-block-content-wrapper" style="padding-top: 10px;padding-bottom: 10px;margin-top: 30px;">仅显示最新10篇文章</div>

		<?php }else{ ?>

		<?php if($wp_query->max_num_pages > 1 ){ ?>

		<div class="tint white-content-block-content-wrapper" style="background-color: #fff;"><?php wpjam_pagenavi();?></div>

		<?php }else{?>

		<div class="tint white-content-block-content-wrapper" style="padding-top: 10px;padding-bottom: 10px;margin-top: 30px;">没有更多文章了...</div>

		<?php } }?>
		
	</div>
</div>