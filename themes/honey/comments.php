<?php

if (comments_open ()) { ?>
    <div class="entry-comments" id="comments"><?php
            if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME'])){
                die('you sb!');
            }
            if (post_password_required ()) { ?>
                <p class="nocomments"><?php esc_html_e('这篇文章是密码保护的，输入密码后查看评论。', 'xintheme'); ?></p></div><?php
                return;
            }
            
            if (have_comments ()) { ?>
                <div class="comment-title"><h4 class="np-title-line">
                    <?php printf(
                        _n(esc_html__('1 条评论', 'xintheme'),
                        '%1$s ' . esc_html__('条评论', 'xintheme'),
                        get_comments_number()),
                        number_format_i18n(get_comments_number())
                        ); ?>                        
                </h4></div>
                <div class="comment-list clearfix">
                    <?php wp_list_comments(array('style' => 'div', 'callback' => 'xintheme_comment')); ?>
                </div>
                <div class="navigation">
                    <div class="left"><?php previous_comments_link() ?></div>
                    <div class="right"><?php next_comments_link() ?></div>
                </div><?php
            }

						$fields[ 'logged_in_as' ] = '<p class="logged-in-as">' . sprintf( __( '当前登陆账号为： <a href="%1$s">%2$s</a>，<a href="%3$s" title="Log out of this account">退出登陆?</a>' ), admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( ) ) ) ) . '</p>';
            $fields[ 'cancel_reply_link' ] = esc_html__('取消回复', 'xintheme');
						$fields[ 'comment_notes_before' ]=$fields[ 'comment_notes_after' ] = '';
            $fields[ 'label_submit' ] = esc_html__('提交评论', 'xintheme');
            $fields[ 'comment_field' ] = 
                '<p class="comment-form-comment">'.
                    '<textarea name="comment" required="required" placeholder="'.esc_html__('输入评论内容...', 'xintheme').'" id="comment" class="required" rows="7" tabindex="4"></textarea>'.
                '</p>';
            $fields[ 'title_reply' ] = esc_html__('发表评论', 'xintheme');

            comment_form($fields);
            
        ?>
    </div><?php
}