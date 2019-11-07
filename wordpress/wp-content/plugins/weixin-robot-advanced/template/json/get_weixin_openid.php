<?php
$openid	= WEIXIN_User::get_current_openid();
wpjam_send_json(compact('openid'));