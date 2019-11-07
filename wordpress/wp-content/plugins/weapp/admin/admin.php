<?php
define('WEAPP_PLUGIN_ADMIN_URL', plugins_url('', __FILE__));

require WEAPP_PLUGIN_DIR . 'admin/includes/trait-weapp-page.php';
require WEAPP_PLUGIN_DIR . 'admin/includes/class-weapp-page.php';

include WEAPP_PLUGIN_DIR . 'admin/hooks/admin-menus.php';
include WEAPP_PLUGIN_DIR . 'admin/hooks/hooks.php';
