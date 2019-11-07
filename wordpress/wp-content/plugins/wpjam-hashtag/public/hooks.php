<?php
add_filter('the_content', function($content){
	$post_id	= get_the_ID();

	if(doing_filter('get_the_excerpt') || !is_singular() || $post_id != get_queried_object_id()){ 
		return $content;
	}

	$hashtags	= wpjam_get_hashtag_replace($content);
	
	if($hashtags['search']){
		$content	= str_replace($hashtags['search'], $hashtags['replace'], $content);
	}

	return $content;
}, 1);

function wpjam_get_hashtag_replace($content){
	$search	= $replace = [];

	if(preg_match_all('/#([\w\s\-\_\.]+?)#/u', $content, $matches)){
		$keywords	= array_values(array_unique($matches[1]));
		$search		= array_values(array_unique($matches[0]));

		$hashtag	= wpjam_get_setting('wpjam-hashtag', 'search_hashtag') ?? '#';
		$replace	= array_map(function($keyword) use($hashtag){
			return '<a href="'.get_search_link($keyword).'" class="hashtag search-hashtag">'.$hashtag.$keyword.$hashtag.'</a>';
		}, $keywords);

		$hashtag_links	= wpjam_get_setting('wpjam-hashtag', 'links');

		if($hashtag_links){
			$hashtag	= wpjam_get_setting('wpjam-hashtag', 'link_hashtag') ?? '';

			foreach ($keywords as $i => $keyword) {
				foreach ($hashtag_links as $hashtag_link) {
					if(strnatcasecmp($hashtag_link['keyword'], $keyword) === 0){
						$replace[$i]	= '<a href="'.$hashtag_link['link'].'" class="hashtag innerlink-hashtag">'.$hashtag.$keyword.$hashtag.'</a>';
						unset($keywords[$i]);
						break;
					}
				}
			}
		}

		if($keywords){
			$terms		= get_terms(['name'=>$keywords]);
			if($terms){
				$hashtag	= wpjam_get_setting('wpjam-hashtag', 'tag_hashtag') ?? '#';

				foreach ($keywords as $i => $keyword) {
					foreach ($terms as $term) {
						if(strnatcasecmp($term->name, $keyword) === 0){
							$replace[$i]	= '<a href="'.get_term_link($term).'" class="hashtag tag-hashtag">'.$hashtag.$keyword.$hashtag.'</a>';	
							break;
						}
					}
				}
			}
		}
	}

	return compact('search', 'replace');
}