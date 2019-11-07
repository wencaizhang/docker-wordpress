<?php if(post_password_required()) return;?>
<div id="comments" class="comments-area">
	<?php comments_number('', '<h3 class="comments-title">1 条评论</h3>', '<h3 class="comments-title">% 条评论</h3>' );?>
	
	<?php if(have_comments()){ ?>

		<ol class="comment-list">
			<?php wp_list_comments('type=comment&callback=wpjam_theme_list_comments'); ?>
		</ol>

		<?php the_comments_pagination(['prev_text'=>'上一页', 'next_text'=>'下一页']); ?>

	<?php } ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) { ?>
		<p class="no-comments"><?php _e( 'Comments are closed.' ); ?></p>
	<?php } ?>

	<?php comment_form(); ?>
</div>
<style>
<?php if( wpjam_theme_get_setting('comment-form-url') ) : ?>
.comment-form-url{display: none;}
<?php endif; ?>
</style>