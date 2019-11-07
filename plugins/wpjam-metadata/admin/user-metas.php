<?php
include WPJAM_METADATA_PLUGIN_DIR.'admin/class-metadata.php';

add_filter('wpjam_user_metas_list_table', 'wpjam_metas_list_table');