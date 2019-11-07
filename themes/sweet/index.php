<?php
get_header();

$index_type	= wpjam_theme_get_setting('index_type');
$index_type	= ($index_type == 'grid') ? 'grid' : 'list';

get_template_part('template-parts/index', $index_type);

get_footer();