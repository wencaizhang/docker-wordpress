<?php
global $wpdb;

$year	= '';
$action	= get_query_var('action');

$wpjam_sitemap	= '';

if(empty($action)){	
	$home_last_mod = str_replace(' ', 'T', get_lastpostmodified('GMT')).'+00:00';
	$wpjam_sitemap .= "\t<url>\n";
	$wpjam_sitemap .= "\t\t<loc>".home_url()."</loc>\n";
	$wpjam_sitemap .= "\t\t<lastmod>".$home_last_mod."</lastmod>\n";
	$wpjam_sitemap .= "\t\t<changefreq>daily</changefreq>\n";
	$wpjam_sitemap .= "\t\t<priority>1.0</priority>\n";
	$wpjam_sitemap .= "\t</url>\n";

	$taxonomies = [];
	foreach (get_taxonomies(['public' => true]) as $taxonomy => $value) {
		if($taxonomy != 'post_format'){
			$taxonomies[]	= $taxonomy;
		}
	}

	$terms	= get_terms(['taxonomy'=>$taxonomies]);

	foreach ($terms as $term) {
		$priority	= ($term->taxonomy == 'category')?0.6:0.4;
		$wpjam_sitemap .= "\t<url>\n";
		$wpjam_sitemap .= "\t\t<loc>".get_term_link($term)."</loc>\n";
		$wpjam_sitemap .= "\t\t<lastmod>".$home_last_mod."</lastmod>\n";
		$wpjam_sitemap .= "\t\t<changefreq>daily</changefreq>\n";
		$wpjam_sitemap .= "\t\t<priority>".$priority."</priority>\n";
		$wpjam_sitemap .= "\t</url>\n";
	}

}elseif(is_numeric($action)){
	$post_types = [];
	foreach (get_post_types(['public' => true]) as $post_type => $value) {
		if($post_type != 'page' && $post_type != 'attachment'){
			$post_types[] = $post_type;
		}
	}

	$sitemap_query	= new WP_Query([
		'posts_per_page'	=> 1000,
		'paged'				=> $action,
		'post_type'			=> $post_types,
	]);

	$sitemap_posts	= $sitemap_query->posts;

	if ($sitemap_posts) {
		foreach ($sitemap_posts as $sitemap_post) {
			$permalink = get_permalink($sitemap_post->ID); //$siteurl.$sitemap_post->post_name.'/';
			$last_mod = str_replace(' ', 'T', $sitemap_post->post_modified_gmt).'+00:00';
			$wpjam_sitemap .= "\t<url>\n";
			$wpjam_sitemap .= "\t\t<loc>".$permalink."</loc>\n";
			$wpjam_sitemap .= "\t\t<lastmod>".$last_mod."</lastmod>\n";
			$wpjam_sitemap .= "\t\t<changefreq>weekly</changefreq>\n";
			$wpjam_sitemap .= "\t\t<priority>0.8</priority>\n";
			$wpjam_sitemap .= "\t</url>\n";
		}
	}
}else{
	$wpjam_sitemap = apply_filters('wpjam_'.$action.'_sitemap', '');
}

if(!isset($_GET['debug'])){
	header ("Content-Type:text/xml"); 

	echo '<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="'.WPJAM_BASIC_PLUGIN_URL.'/static/sitemap.xsl'.'"?>
<!-- generated-on="'.date('d. F Y').'" -->
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n".$wpjam_sitemap."\n".'</urlset>'."\n";
}else{

	// $wpjam_sitemap_url = home_url('/sitemap.xml');
	
	// $pingurls = array();
	// $pingurls[] = array(
	// 	'service' => 'GOOGLE',
	// 	'url' => 'http://www.google.com/webmasters/sitemaps/ping?sitemap='.$wpjam_sitemap_url,
	// 	'snippet' => 'Your Sitemap has been successfully added to our list of Sitemaps to crawl.'
	// );
	// $pingurls[] = array(
	// 	'service' => 'ASK.COM',
	// 	'url' => 'http://submissions.ask.com/ping?sitemap='.$wpjam_sitemap_url,
	// 	'snippet' => 'Your Sitemap has been successfully received and added to our Sitemap queue.'
	// );
	// $pingurls[] = array(
	// 	'service' => 'Bing',
	// 	'url' => 'http://www.bing.com/webmaster/ping.aspx?siteMap='.$wpjam_sitemap_url,
	// 	'snippet' => 'Thanks for submitting your sitemap.'
	// );
	// $pingurls[] = array(
	// 	'service' => 'YAHOO',
	// 	'url' => 'http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap='.$wpjam_sitemap_url,
	// 	'snippet' => 'Update notification has successfully submitted.'
	// );

	// echo '<ul>';
	// foreach($pingurls as $engine){
	// 	$httpresult = (array)wp_remote_get($engine['url']);
	// 	if(strpos($httpresult['body'], $engine['snippet']) !== false){
	// 		echo '<li>'.sprintf(__('%s was pinged at: ', 'simplesitemap'), $engine['service']).'<a href="'.$engine['url'].'">'.$engine['url'].'</a></li>';
	// 	}else{
	// 		echo  '<li>'.'<span style="color:#cc0000">'.sprintf(__('Oops .. %s ping failed at: ', 'simplesitemap').'</span>', $engine['service']).'<a href="'.$engine['url'].'">'.$engine['url'].'</a></li>';			
	// 	}
	// }
	// echo '</ul>';

    global $wpdb;
    
	echo get_num_queries();echo ' queries in ';timer_stop(1);echo ' seconds.<br>';

	echo '按执行顺序：<br>';
	echo '<pre>';
	var_dump($wpdb->queries);
	echo '</pre>';

	echo '按耗时：<br>';
	echo '<pre>';
	$qs = array();
	foreach($wpdb->queries as $q){
	$qs[''.$q[1].''] = $q;
	}
	krsort($qs);
	print_r($qs);
	echo '</pre>';
}