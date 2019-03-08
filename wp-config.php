<?php
/**
 * WordPress基础配置文件。
 *
 * 这个文件被安装程序用于自动生成wp-config.php配置文件，
 * 您可以不使用网站，您需要手动复制这个文件，
 * 并重命名为“wp-config.php”，然后填入相关信息。
 *
 * 本文件包含以下配置选项：
 *
 * * MySQL设置
 * * 密钥
 * * 数据库表名前缀
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/zh-cn:%E7%BC%96%E8%BE%91_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL 设置 - 具体信息来自您正在使用的主机 ** //
/** WordPress数据库的名称 */
define( 'WPCACHEHOME', '/var/www/html/wp-content/plugins/wp-super-cache/' );
define('WP_CACHE', true);
define( 'DB_NAME', 'wordpress');

/** MySQL数据库用户名 */
define( 'DB_USER', 'wordpress');

/** MySQL数据库密码 */
define( 'DB_PASSWORD', 'wordpress');

/** MySQL主机 */
define( 'DB_HOST', 'db:3306');

/** 创建数据表时默认的文字编码 */
define( 'DB_CHARSET', 'utf8');

/** 数据库整理类型。如不确定请勿更改 */
define('DB_COLLATE', '');

/**#@+
 * 身份认证密钥与盐。
 *
 * 修改为任意独一无二的字串！
 * 或者直接访问{@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org密钥生成服务}
 * 任何修改都会导致所有cookies失效，所有用户将必须重新登录。
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'XEFaG}uOrzU>{Pp{mf5EX`f38(W-?0h2a7@nwY>4Hdc:`D9tD70TI7:?aAW@~-s-');
define('SECURE_AUTH_KEY',  'UH}^a[n%A)r2Avn%XhY>G.Xl@H<P*/kcZ/)#9e<cjJe49z;5`d,6M=Rf*U:mTo#/');
define('LOGGED_IN_KEY',    'nLdBkU3j#%H3glr`#*7}L:48u +*;fuQ_J#!@ }Y,L6,5jnlr)wY@H*R>RKxC>Bh');
define('NONCE_KEY',        'DE0j]EOhnQDksRqfZ)B U5DJ{eRrSK=R6[yQ8p!fP;8qXXLeI!j%/*si|gcfa3Bt');
define('AUTH_SALT',        'zT(4[MC(k:j}g:,E<}~#2$7&Xy64:HQYZiwaA`xBaQen.x,h_qU&292=6BA[&=S&');
define('SECURE_AUTH_SALT', '4M,s -t8D!-c.2nBRQEXDH!DE]6C$Sgvt*nZQZl>wo0GN%[so&+(-4r8]$BT%OSU');
define('LOGGED_IN_SALT',   'WTj/arHLu[@{U;y>uDb;VX;9%zo(M`L2T+arnlzMuE01Y@P3y>CY {~$r{vNP8Pv');
define('NONCE_SALT',       'iVHoI$QeMmIp{; HYFW91-!7BYEiSA4xTjmY43&pqH7d7W?Y!tE,rYkN+`[O;NU@');

/**#@-*/

/**
 * WordPress数据表前缀。
 *
 * 如果您有在同一数据库内安装多个WordPress的需求，请为每个WordPress设置
 * 不同的数据表前缀。前缀名只能为数字、字母加下划线。
 */
$table_prefix = 'wp_';

/**
 * 开发者专用：WordPress调试模式。
 *
 * 将这个值改为true，WordPress将显示所有用于开发的提示。
 * 强烈建议插件开发者在开发环境中启用WP_DEBUG。
 *
 * 要获取其他能用于调试的信息，请访问Codex。
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);


// If we're behind a proxy server and using HTTPS, we need to alert Wordpress of that fact
// see also http://codex.wordpress.org/Administration_Over_SSL#Using_a_Reverse_Proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
	$_SERVER['HTTPS'] = 'on';
}

/* 好了！请不要再继续编辑。请保存本文件。使用愉快！ */

/** WordPress目录的绝对路径。 */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** 设置WordPress变量和包含文件。 */
require_once(ABSPATH . 'wp-settings.php');

/** 避免使用 FTP 安装或更新插件 */
define("FS_METHOD","direct");
define("FS_CHMOD_DIR", 0777);
define("FS_CHMOD_FILE", 0777);
