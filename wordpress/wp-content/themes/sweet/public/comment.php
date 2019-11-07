<?php
//评论列表
function wpjam_theme_list_comments($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment;
	global $commentcount,$wpdb, $post;
	if(!$commentcount) { 
		$comments = get_comments(['post_id'=>$post->ID]);
		$cnt = count($comments);
		$page = get_query_var('cpage');
		$cpp=get_option('comments_per_page');
		if (ceil($cnt / $cpp) == 1 || ($page > 1 && $page	== ceil($cnt / $cpp))) {
			$commentcount = $cnt + 1;
		} else {
			$commentcount = $cpp * $page + 1;
		}
	}
?>
<li id="comment-<?php comment_ID() ?>" <?php comment_class(); ?>>
	<div id="div-comment-<?php comment_ID() ?>" class="comment-wrapper u-clearfix">
		<div class="comment-author-avatar vcard">
			<?php echo get_avatar($comment,60); ?>
		</div>
		<div class="comment-content">
			<div class="comment-author-name vcard">
				<cite class="fn"><?php /*wpjam_comment_level($comment);*/ comment_author_link();?></cite>
			</div>
			<div class="comment-metadata">
				<time><?php comment_date() ?> <?php comment_time() ?></time>
				<span class="reply-link">
					<?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth'], 'reply_text' => "回复"))) ?>
				</span>
			</div>
			<div class="comment-body" itemprop="comment">
				<?php comment_text() ?>
				<?php if ( $comment->comment_approved == '0' ) : ?>
					<font style="color:#C00; font-style:inherit">您的评论正在等待审核中...</font>
				<?php endif; ?>
			</div>
		</div>
	</div>
</li>
<?php }

//评论等级
function wpjam_author_class($comment_author_email){
	global $wpdb; $author_count = count($wpdb->get_results( "SELECT comment_ID as author_count FROM $wpdb->comments WHERE comment_author_email = '$comment_author_email' ")); 
	$adminEmail = get_option('admin_email');if($comment_author_email ==$adminEmail) return;
	if($author_count>=1 && $author_count<10 && $comment_author_email!=$adminEmail)
		echo '<span class="level level-0">初来乍到</span>';
	else if($author_count>=10 && $author_count< 20)
		echo '<span class="level level-1">江湖少侠</span>';
	else if($author_count>=20 && $author_count< 40)
		echo '<span class="level level-2">崭露头角</span>';
	else if($author_count>=40 && $author_count< 60)
		echo '<span class="level level-3">自成一派</span>';
	else if($author_count>=60 && $author_count< 80)
		echo '<span class="level level-4">横扫千军</span>';
	else if($author_count>=80&& $author_count<100)
		echo '<span class="level level-5">登峰造极</span>';
	else if($author_count>=100&& $author_count< 120)
		echo '<span class="level level-6">一统江湖</span>';
}

function wpjam_comment_level($comment){
	$html = "";
	if(($vip = wpjam_author_class($comment->comment_author_email))){
		$html .= '' . $vip . '>';
		for($i = 0; $i < $vip; $i++){
			$html .= '';
		}
		$html .= '';
	};
	echo $html;
}

// 评论邮件
add_action('comment_post',function ($comment_id) {
	$comment = get_comment($comment_id);
	$parent_id = $comment->comment_parent ? $comment->comment_parent : '';
	$spam_confirmed = $comment->comment_approved;
	if (($parent_id != '') && ($spam_confirmed != 'spam')) {
		$wp_email = 'no-reply@' . preg_replace('#^www.#', '', strtolower($_SERVER['SERVER_NAME'])); //e-mail 发出点, no-reply 可改为可用的 e-mail.
		$to = trim(get_comment($parent_id)->comment_author_email);
		$subject = '您在 [' . get_option("blogname") . '] 的留言有了回复';
		$message = '
<table cellpadding="0" cellspacing="0" class="email-container" align="center" width="550" style="font-size: 15px; font-weight: normal; line-height: 22px; text-align: left; border: 1px solid rgb(177, 213, 245); width: 550px;">
<tbody><tr>
<td>
<table cellpadding="0" cellspacing="0" class="padding" width="100%" style="padding-left: 40px; padding-right: 40px; padding-top: 30px; padding-bottom: 35px;">
<tbody>
<tr class="logo">
<td align="center">
<table class="logo" style="margin-bottom: 10px;">
<tbody>
<tr>
<td>
<span style="font-size: 22px;padding: 10px 20px;margin-bottom: 5%;color: #65c5ff;border: 1px solid;box-shadow: 0 5px 20px -10px;border-radius: 2px;display: inline-block;">' . get_option("blogname") . '</span>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr class="content">
<td>
<hr style="height: 1px;border: 0;width: 100%;background: #eee;margin: 15px 0;display: inline-block;">
<p>Hi ' . trim(get_comment($parent_id)->comment_author) . '!<br>您评论在 "' . get_the_title($comment->comment_post_ID) . '":</p>
<p style="background: #eee;padding: 1em;text-indent: 2em;line-height: 30px;">' . trim(get_comment($parent_id)->comment_content) . '</p>
<p>'. $comment->comment_author .' 给您的答复:</p>
<p style="background: #eee;padding: 1em;text-indent: 2em;line-height: 30px;">' . trim($comment->comment_content) . '</p>
</td>
</tr>
<tr>
<td align="center">
<table cellpadding="12" border="0" style="font-family: Lato, \'Lucida Sans\', \'Lucida Grande\', SegoeUI, \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: bold; line-height: 25px; color: #444444; text-align: left;">
<tbody><tr>
<td style="text-align: center;">
<a target="_blank" style="color: #fff;background: #65c5ff;box-shadow: 0 5px 20px -10px #44b0f1;border: 1px solid #44b0f1;width: 200px;font-size: 14px;padding: 10px 0;border-radius: 2px;margin: 10% 0 5%;text-align:center;display: inline-block;text-decoration: none;" href="' . htmlspecialchars(get_comment_link($parent_id)) . '">查看详情</a>
</td>
</tr>
</tbody></table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" align="center" class="footer" style="max-width: 550px; font-family: Lato, \'Lucida Sans\', \'Lucida Grande\', SegoeUI, \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 15px; line-height: 22px; color: #444444; text-align: left; padding: 20px 0; font-weight: normal;">
<tbody><tr>
<td align="center" style="text-align: center; font-size: 12px; line-height: 18px; color: rgb(163, 163, 163); padding: 5px 0px;">
</td>
</tr>
<tr>
<td style="text-align: center; font-weight: normal; font-size: 12px; line-height: 18px; color: rgb(163, 163, 163); padding: 5px 0px;">
<p>Please do not reply to this message , because it is automatically sent.</p>
<p>© '.date("Y").' <a name="footer_copyright" href="' . home_url() . '" style="color: rgb(43, 136, 217); text-decoration: underline;" target="_blank">' . get_option("blogname") . '</a></p>
</td>
</tr>
</tbody>
</table>';
		$from = "From: \"" . get_option('blogname') . "\" <$wp_email>";
		$headers = "$from\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
		wp_mail( $to, $subject, $message, $headers );
	}
});
