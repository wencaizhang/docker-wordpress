<?php
/*
Template Name: 主题使用文档
*/
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php the_title(); ?></title>
<link rel="shortcut icon" href="<?php echo get_option('home'); ?>/favicon.ico"/>
<?php echo plugins_document_css(); ?>
</head>
<body>
<a class="sr-only" href="#content">导航</a><header class="navbar navbar-inverse navbar-fixed-top bs-docs-nav" role="banner">
<div class="container">
	<div class="navbar-header">
		<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
		<span class="sr-only">展开菜单</span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		</button>
		<a target="_blank" href="<?php echo get_option('home'); ?>" rel="home" class="navbar-brand">
			<img src="<?php echo plugins_url('logo.png', __FILE__ ); ?>" alt="<?php bloginfo('name'); ?>">
		</a>
	</div>
	<nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
	<ul class="nav navbar-nav">
		<?php if(function_exists('wp_nav_menu')) wp_nav_menu(array('container' => false, 'items_wrap' => '%3$s', 'theme_location' => 'documentation-nav')); ?>
	</ul>
	</nav>
</div>
</header>
<div class="bs-header" id="content">
	<div class="container" style="font-size: 18px;">
		<h1><?php the_post(); the_title(); ?></h1>
		<?php if( has_excerpt() ){
			the_excerpt();
		}?>
	</div>
</div>
<div class="container bs-docs-container">
	<div class="row">
		<div class="col-md-3">
			<div id="category-ct" class="bs-sidebar hidden-print" role="complementary">
				<ul class="nav bs-sidenav" id="category">
				</ul>
			</div>
		</div>
		<div class="col-md-9" role="main">
			<div class="bs-docs-section">
				<?php the_content();?>
			</div>
		</div>
	</div>
</div>

<footer class="bs-footer" role="contentinfo">
<div class="container">
</div>
</footer>
<?php echo plugins_document_js(); ?>
<script>
    $(function(){
        var notdefault = 0;
        $('.bs-docs-section').find('h3,h4').each(function(index,item){
            notdefault = 1;
            $(this).attr('id','c'+index);
            var headerText=$(this).text();
            var tagName=$(this)[0].tagName.toLowerCase();
            var tagIndex=parseInt(tagName.charAt(1));
			$('#category').append($('<li class="article-ch'+tagIndex+'"><a href="#c'+index+'">'+headerText+'</a></li>'));
        });
	   //页面平滑滚动
		$('.bs-sidenav  a').click(function () {
			var offsetNum = $($.attr(this, 'href')).offset();//获取偏移量
				offsetTop = offsetNum.top - 70;//偏移量具体调整
				console.log(offsetNum);
				$('html, body').animate({scrollTop: offsetTop}, 1000);
			return false;
		});
    });
</script>
</body>
</html>