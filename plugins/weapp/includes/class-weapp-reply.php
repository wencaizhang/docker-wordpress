<?php
wp_cache_add_global_groups(['weapp_replies']);

function weapp_reply_user($touser, $data)
{
    return WEAPP_Reply::reply($touser, $data);
}

class WEAPP_Reply extends WPJAM_Model
{
    use WEAPP_Trait;

    public static function get($id)
    {
        $reply = parent::get($id);
        if (!$reply) {
            return $reply;
        }

        if (is_admin()) {
            if ($reply['msg_type'] == 'event') {
                $reply['sessionfrom'] = $reply['keyword'];
            }
        }

        $reply_type = ($reply['reply_type']) ?? 'text';

        $reply[$reply_type] = maybe_unserialize($reply['reply']);

        return $reply;
    }

    public static function insert($data)
    {
        $msg_type = $data['msg_type'] ?? 'text';

        if ($msg_type == 'text') {
            $cache_key = 'weapp_' . $msg_type . '_full_replies';
            self::cache_delete($cache_key);
            $cache_key = 'weapp_' . $msg_type . '_prefix_replies';
            self::cache_delete($cache_key);
            $cache_key = 'weapp_' . $msg_type . '_fuzzy_replies';
            self::cache_delete($cache_key);
        } else {
            $cache_key = 'weapp_' . $msg_type . '_replies';
            self::cache_delete($cache_key);
        }

        $data = self::prepare($data);

        return parent::insert($data);
    }

    public static function update($id, $data)
    {
        $reply = self::get($id);
        if (!$reply) {
            return new WP_Error('reply_no_exists', '该自定义回复不存在');
        }

        $msg_type = $reply['msg_type'] ?? 'text';

        if (is_admin()) {
            $data['msg_type'] = $msg_type;
        }

        $data = self::prepare($data);

        $result = parent::update($id, $data);

        if ($msg_type == 'text') {

            $cache_key = 'weapp_' . $msg_type . '_full_replies';
            self::cache_delete($cache_key);
            $cache_key = 'weapp_' . $msg_type . '_prefix_replies';
            self::cache_delete($cache_key);
            $cache_key = 'weapp_' . $msg_type . '_fuzzy_replies';
            self::cache_delete($cache_key);
        } else {
            $cache_key = 'weapp_' . $msg_type . '_replies';
            self::cache_delete($cache_key);
        }

        return $result;
    }

    public static function prepare($data)
    {
        if (is_admin()) {

            $data['time'] = $data['time'] ?? time();

            $msg_type = $data['msg_type'];

            if ($msg_type == 'event') {
                $data['keyword'] = $data['sessionfrom'];
            }

            unset($data['sessionfrom']);

            $reply_type = $data['reply_type'] ?? 'text';

            $data['reply'] = ($reply_type == 'transfer_customer_service') ? '' : maybe_serialize($data[$reply_type]);
            $data['appid'] = static::get_appid();

            unset($data['text']);
            unset($data['image']);
            unset($data['link']);
            unset($data['miniprogrampage']);
        }

        return $data;
    }

    public static function delete($id)
    {
        $reply = self::get($id);
        if (!$reply) {
            return new WP_Error('reply_no_exists', '该自定义回复不存在');
        }

        $msg_type  = $reply['msg_type'];
        $cache_key = ($msg_type == 'text') ? 'weapp_' . $msg_type . '_' . $reply['match'] . '_replies' : 'weapp_' . $msg_type . '_replies';

        self::cache_delete($cache_key);

        return parent::delete($id);
    }

    public static function cache_get($cache_key){
       return wp_cache_get($cache_key, static::get_appid());
    }

    public static function cache_set($cache_key, $data){
        wp_cache_set($cache_key, $data, static::get_appid(), DAY_IN_SECONDS);
    }

    public static function cache_delete($cache_key){
        wp_cache_delete($cache_key, static::get_appid());
    }

    public static function get_replies($msg_type, $keyword = '')
    {
        $replies = [];
        if ($msg_type == 'image' || $msg_type == 'miniprogrampage' || $msg_type == 'default') {
            $replies = static::get_keywords($msg_type);
            if (!$replies) {
                return false;
            }
        } elseif ($msg_type == 'event') {
            $replies = static::get_keywords($msg_type);

            if ($replies && isset($replies[$keyword])) {
                $replies = $replies[$keyword];
            } else {
                return false;
            }
        } elseif ($msg_type == 'text') {
            $prefix_keyword = mb_substr($keyword, 0, 2);    // 前缀匹配，只支持2个字

            $replies        = static::get_keywords($msg_type, 'full');        // 完全匹配
            $replies_prefix = static::get_keywords($msg_type, 'prefix');    // 前缀匹配
            $replies_fuzzy  = static::get_keywords($msg_type, 'fuzzy');        // 模糊匹配

            $keyword = strtolower(trim($keyword));
            if ($replies && isset($replies[$keyword])) {
                $replies = $replies[$keyword];
            } elseif ($replies_prefix && isset($replies_prefix[$prefix_keyword])) {
                $replies = $replies_prefix[$prefix_keyword];
            } elseif ($replies_fuzzy && preg_match(
                    '/' . implode('|', array_keys($replies_fuzzy)) . '/',
                    $keyword,
                    $matches
                )) {
                $fuzzy_keyword = $matches[0];
                $replies       = $replies_fuzzy[$fuzzy_keyword];
            } else {
                return false;
            }
        }

        if (isset($_GET['debug'])) {
            print_r($replies);
        }

        // $rand_key = array_rand($replies, 1);
        // $replies = $replies[$rand_key];

        return $replies;
    }

    public static function get_keywords($msg_type = 'text', $match = null)
    {

        $cache_key = ($msg_type == 'text') ? 'weapp_' . $msg_type . '_' . $match . '_replies' : 'weapp_' . $msg_type . '_replies';
        $replies   = self::cache_get($cache_key);

        if ($replies === false) {
            $results = static::Query()->where('appid', static::get_appid())->where('msg_type', $msg_type)->where('status', 1)->where('match', $match)->get_results();

            if ($msg_type == 'text' || $msg_type == 'event') {
                $replies = [];

                if ($results) {
                    foreach ($results as $reply) {
                        $key = strtolower(trim($reply['keyword']));
                        if (strpos($key, ',')) {
                            foreach (explode(',', $key) as $new_key) {
                                $new_key = strtolower(trim($new_key));
                                if ($new_key !== '') {
                                    $replies[$new_key][] = $reply;
                                }
                            }
                        } else {
                            $replies[$key][] = $reply;
                        }
                    }
                }
            } else {
                $replies = ($results) ?: [];
            }

            self::cache_set($cache_key, $replies);
        }

        return $replies;
    }

    public static function reply($touser, $data)
    {
        $reply_type = $data['reply_type'] ?? 'text';

        if ($reply_type == 'text') {
            $content = $data['reply'];
            if (!$content) {
                return false;
            }

            $reply_content = compact('content');
        } elseif ($reply_type == 'image') {
            if (empty($data['reply'])) {
                return new WP_Error('image__missing', '图片回复的图片字段为空');
            }

            $media_id = weapp_get_wp_img_media_id($data['reply']);
            if (is_wp_error($media_id)) {
                return $media_id;
            }

            $reply_content = compact('media_id');

        } elseif ($reply_type == 'link') {
            $link = $data['reply'];

            foreach (['title', 'description', 'url', 'thumb_url'] as $key) {
                if (empty($link[$key])) {
                    return new WP_Error('link_' . $key . '_missing', '图文链接回复的' . $key . '字段为空');
                }
            }

            $reply_content = $link;
        } elseif ($reply_type == 'miniprogrampage') {
            $miniprogrampage = $data['reply'];

            foreach (['title', 'pagepath', 'image'] as $key) {
                if (empty($miniprogrampage[$key])) {
                    return new WP_Error('miniprogrampage_' . $key . '_missing', '小程序卡片回复的' . $key . '字段为空');
                }
            }

            $media_id = weapp_get_wp_img_media_id($miniprogrampage['image']);
            if (is_wp_error($media_id)) {
                return $media_id;
            }

            $miniprogrampage['thumb_media_id'] = $media_id;
            unset($miniprogrampage['image']);

            $reply_content = $miniprogrampage;
        } elseif ($reply_type == 'transfer_customer_service') {
            return 'transfer_customer_service';
        }

        return weapp_send_custom_message(
            [
                'touser'    => $touser,
                'msgtype'   => $reply_type,
                $reply_type => $reply_content,
            ]
        );
    }

    public static function list($limit, $offset)
    {
        ?>
        <style type="text/css">
            div.reply_item {
                padding: 12px;
                width: 320px;
                border: 1px solid #CCC;
                float: left;
                box-shadow: 1px 1px 3px #CCC;
                border-radius: 4px;
            }

            div.reply_item a {
                float: left;
                width: 320px;
            }

            div.reply_item div.small {
                width: 40px;
                height: 40px;
                float: right;
                background-size: cover;
            }

            div.reply_item div.big {
                width: 320px;
                height: 160px;
            }

            div.reply_item h3 {
                font-size: 14px;
                margin: 0 0 10px 0;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
            }

            div.reply_item p {
                margin: 0;
                float: left;
                width: 270px;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 3;
            }
        </style>
        <?php
        self::Query()->where('appid', static::get_appid());

        return parent::list($limit, $offset);
    }

    public static function item_callback($item)
    {
        if ($item['reply_type'] == 'image') {
            $item['reply'] = '<img src="' . wpjam_get_thumbnail(
                    wp_get_attachment_url($item['reply']),
                    400
                ) . '" width="200"/>';
        } elseif ($item['reply_type'] == 'link') {
            $reply = maybe_unserialize($item['reply']);

            $item['reply'] = '<div class="reply_item"><a target="_blank" href="' . $reply['url'] . '">';
            $item['reply'] .= '<h3>' . $reply['title'] . '</h3>';
            $item['reply'] .= '<div class="img_container small" style="background-image:url(' . wpjam_get_thumbnail(
                    $reply['thumb_url'],
                    ['width' => 80, 'height' => 80, 'mode' => 1]
                ) . ');"></div>';
            $item['reply'] .= '<p>' . $reply['description'] . '</p>';
            $item['reply'] .= '</a></div>';

        } elseif ($item['reply_type'] == 'miniprogrampage') {
            $reply = maybe_unserialize($item['reply']);

            $item['reply'] = '<div class="reply_item">
				<h3>' . $reply['title'] . '</h3>
				<div class="img_container big" style="background-image:url(' . wpjam_get_thumbnail(
                    wp_get_attachment_url($reply['image']),
                    ['width' => 640, 'height' => 320, 'mode' => 1]
                ) . '); background-size:320px 160px;">
				</div>
				<p>路径：' . $reply['pagepath'] . '</p>
			</div>';
        }

        if ($item['msg_type'] == 'text') {

        } elseif ($item['msg_type'] == 'event') {
            $item['match'] = '';
        } else {
            $item['sessionfrom'] = '';
            $item['keyword']     = '';
            $item['match']       = '';
        }

        return $item;
    }

    public static function views()
    {
        global $wpdb, $current_admin_url, $plugin_page;

        $appid = static::get_appid();

        $msg_types = WEAPP_Message::$types + ['default' => '默认'];

        $msg_type = ($_GET['msg_type']) ?? '';
        $status   = ($_GET['status']) ?? 1;

        $total      = static::Query()->where('appid', $appid)->where('status', 1)->get_var('count(*)');
        $status_0   = static::Query()->where('appid', $appid)->where('status', 0)->get_var('count(*)');
        $msg_counts = static::Query()->where('appid', $appid)->where('status', 1)->group_by('msg_type')->order_by(
            'count'
        )->get_results('COUNT( * ) AS count, `msg_type`');


        $views = [];

        $class        = (empty($msg_type) && $status) ? 'class="current"' : '';
        $views['all'] = '<a href="' . $current_admin_url . '" ' . $class . '>全部<span class="count">（' . $total . '）</span></a>';

        foreach ($msg_counts as $count) {
            $class    = ($msg_type == $count['msg_type']) ? 'class="current"' : '';
            $msg_type = ($msg_types[$count['msg_type']]) ?? $count['msg_type'];

            $views['msg-' . $count['msg_type']] = '<a href="' . $current_admin_url . '&msg_type=' . $count['msg_type'] . '" ' . $class . '>' . $msg_type . '<span class="count">（' . $count['count'] . '）</span></a>';
        }

        $class             = empty($status) ? 'class="current"' : '';
        $views['status-0'] = '<a href="' . $current_admin_url . '&status=0" ' . $class . '>未激活<span class="count">（' . $status_0 . '）</span></a>';

        return $views;
    }

    public static $types = [
        'text'                      => '文本',
        'image'                     => '图片',
        'link'                      => '图文链接',
        'miniprogrampage'           => '小程序卡片',
        'transfer_customer_service' => '转发到客服工具',
    ];
    protected static $handler;
    protected static $appid;

    public static function get_handler()
    {
        if (is_null(static::$handler)) {
            global $wpdb;
            static::$handler = new WPJAM_DB(
                $wpdb->base_prefix . 'weapp_replies', [
                    'primary_key'       => 'id',
                    'field_types'       => ['id' => '%d', 'status' => '%d'],
                    'searchable_fields' => ['keyword'],
                    'filterable_fields' => ['msg_type', 'reply_type', 'status'],
                ]
            );
        }

        return static::$handler;
    }

    public static function create_table()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        global $wpdb;

        $wpdb->weapp_replies = $wpdb->base_prefix . 'weapp_replies';

        if ($wpdb->get_var("show tables like '{$wpdb->weapp_replies}'") != $wpdb->weapp_replies) {
            $sql = "
			CREATE TABLE IF NOT EXISTS `{$wpdb->weapp_replies}` (
				`id` bigint(20) NOT NULL auto_increment,
				`appid` varchar(32) NOT NULL,
				`keyword` varchar(255)	NOT NULL,
				`msg_type` varchar(7)	NOT NULL,
				`match` varchar(7) NOT NULL,
				`reply` text NOT NULL,
				`reply_type` varchar(31) NOT NULL,
				`status` int(1) NOT NULL,
				`time` int(10) NOT NULL,
				PRIMARY KEY	(`id`)
			) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			";

            dbDelta($sql);
        }
    }
}
