<?php
// include(WPJAM_BASIC_PLUGIN_DIR.	'admin/includes/class-wpjam-cron.php');

// class WEIXIN_Masssend extends WPJAM_Cron{
// 	public static function list($limit, $offset){
// 		?>
// 		<link rel="stylesheet" type="text/css" href="<?php echo WEIXIN_ROBOT_PLUGIN_URL.'/template/static/news-items.css'?>">
// 		<?php
// 		$msgtype_options	= ['mpnews'=>'图文',	 'image'=>'图片', 'voice'=>'语音', 'text'=>'文本'];
// 		$weixin_user_tags	= weixin()->get_tags();

// 		$items	= [];
		
// 		foreach (_get_cron_array() as $timestamp => $wp_cron) {
// 			foreach ($wp_cron as $hook => $dings) {
// 				if($hook != 'weixin_send_future_mass_message') continue;

// 				foreach( $dings as $key=>$data ) {
// 					$msgtype	= $msgtype_options[$data['args'][1]];
// 					$tag		= ($data['args'][0] == 'all')?'所有用户':'标签：'.$weixin_user_tags[$data['args'][0]]['name'];
// 					$content	= $data['args'][2];

// 					if($data['args'][1] == 'mpnews'){
// 						$material	= weixin()->get_material($data['args'][2], 'news');
// 						if(is_wp_error($material)){
// 							$content = $material->get_error_code().' '.$material->get_error_message();
// 						}else{
// 							$content	= '';
// 							$i 			= 1;
// 							$count		= count($material);

// 							foreach ($material as $news_item) {

// 								$item_div_class	= ($i == 1)? 'big':'small'; 
// 								$item_a_class	= ($i == $count)?'noborder':''; 
// 								$item_excerpt	= ($count == 1)?'<p>'.$news_item['digest'].'</p>':'';

// 								$thumb	= weixin()->get_material($news_item['thumb_media_id'], 'thumb');
// 								$thumb	= is_wp_error($thumb)?'':$thumb;

// 								$content   .= '
// 								<a class="'.$item_a_class.'" target="_blank" href="'.$news_item['url'] .'">
// 								<div class="img_container '.$item_div_class.'" style="background-image:url('.$thumb.');">
// 									<h3>'.$news_item['title'].'</h3>
// 								</div>
// 								'.$item_excerpt.'
// 								</a>';
								
// 								$i++;
// 							}
// 							$content 	= '<div class="reply_item">'.$content.'</div>';
// 						}
// 					}

// 					$items[] = [
// 						'cron_id'		=> $timestamp.'--'.$hook.'--'.$key,
// 						'timestamp'		=> get_date_from_gmt( date('Y-m-d H:i:s', $timestamp) ),
// 						'tag'			=> $tag,
// 						'msgtype'		=> $msgtype,
// 						'content'		=> $content,
// 					];
// 				}
// 			}
// 		}

// 		$total = count($items);

// 		return compact('items', 'total');
// 	}
// }