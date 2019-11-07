<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php $navbar_type = wpjam_theme_get_setting('navbar_type');?>

<div class="navbar w-nav" <?php if( $navbar_type == 'left' ) {?>style="text-align: left"<?php }elseif( $navbar_type == 'right' ){?>style="text-align: right"<?php }?>>
	<div class="container w-container">
		<?php if( $logo = wpjam_theme_get_setting('logo') ){ ?>
		<a class="logo-block w-inline-block" href="<?php echo home_url(); ?>">
			<img class="logo" src="<?php echo $logo;?>">
		</a>
		<?php } ?>
		<nav class="nav-menu w-nav-menu" <?php if( !$logo ){?>style="margin-top: 0"<?php }?>>
			<?php if( function_exists('wp_nav_menu') && has_nav_menu('main') ){
				wp_nav_menu(['container'=>false, 'items_wrap'=>'%3$s', 'theme_location'=>'main']); ?>
			<?php } else { ?>
				<li class="menu-item"><a href="/wp-admin/nav-menus.php">请到【后台 - 外观 - 菜单】中设置菜单</a></li>
			<?php } ?>
		</nav>
		<div class="menu-button w-nav-button">
			<div class="w-icon-nav-menu right">
			</div>
		</div>
		<div class="rightNav">
			<?php wp_nav_menu(['container'=>false, 'items_wrap'=>'%3$s', 'theme_location'=>'main']); ?>
		</div>
	</div>
</div>
<div class="bgDiv"></div>