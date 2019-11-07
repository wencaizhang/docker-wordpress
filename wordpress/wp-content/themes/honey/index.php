<?php
	get_header();
	if ( wpjam_theme_get_setting('banner_area') ) {
		get_template_part('template-parts/stick', 'area');
	}
?>
<div class="xintheme-container container">
	<?php get_template_part('template-parts/popular', 'area');?>
	<div class="row">
		<div class="content-area list-side col-md-12">
			<?php get_template_part("template-parts/content", "list");?>
		</div>
	</div>
</div>
<?php get_footer();?>