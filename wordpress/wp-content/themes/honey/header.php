<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
				<?php $favicon = wpjam_theme_get_setting('favicon') ?: get_template_directory_uri().'/assets/img/favicon.ico'; ?>
				<link rel="shortcut icon" href="<?php echo $favicon;?>"/>
        <?php wp_head(); ?>
    </head>
    <body <?php body_class(); ?>>
        <div class="xintheme-mobile-menu"><i class="ion-ios-close-empty"></i>
            <nav><?php xintheme_mobilemenu(); ?></nav>
        </div>
        <div class="theme-layout">
			<header class="header-area header-2">
				<div class="xintheme-menu-container">
					<div class="container">
						<?php xintheme_logo(); ?>
						<a href="#" class="mobile-menu-icon"><span></span></a>
						<nav class="xintheme-menu<?php if( wpjam_theme_get_setting('header_search') ){ ?> padding-right<?php } ?>">
						<?php
							xintheme_menu();
							echo xintheme_social_icons();
							echo xintheme_searchmenu();
						?>
						</nav>
					</div>
				</div>
				<div class="header-clone"></div>         
			</header>