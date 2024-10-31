<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wpdb;
if (!defined('WPLANG') || WPLANG == '') {
	define('PYT_WPLANG', 'en_GB');
} else {
	define('PYT_WPLANG', WPLANG);
}
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

define('PYT_PLUG_NAME', basename(dirname(__FILE__)));
define('PYT_DIR', WP_PLUGIN_DIR . DS . PYT_PLUG_NAME . DS);
define('PYT_CLASSES_DIR', PYT_DIR . 'classes' . DS);
define('PYT_TABLES_DIR', PYT_CLASSES_DIR . 'tables' . DS);
define('PYT_HELPERS_DIR', PYT_CLASSES_DIR . 'helpers' . DS);
define('PYT_LANG_DIR', PYT_DIR . 'languages' . DS);
define('PYT_ASSETS_DIR', PYT_DIR . 'common' . DS);
define('PYT_IMG_DIR', PYT_ASSETS_DIR . 'img' . DS);
define('PYT_JS_DIR', PYT_ASSETS_DIR . 'js' . DS);
define('PYT_LIB_DIR', PYT_ASSETS_DIR . 'lib' . DS);
define('PYT_MODULES_DIR', PYT_DIR . 'modules' . DS);
define('PYT_ADMIN_DIR', ABSPATH . 'wp-admin' . DS);

define('PYT_PLUGINS_URL', plugins_url());
define('PYT_SITE_URL', get_bloginfo('wpurl') . '/');
define('PYT_LIB_PATH', PYT_PLUGINS_URL . '/' . PYT_PLUG_NAME . '/common/lib/');
define('PYT_JS_PATH', PYT_PLUGINS_URL . '/' . PYT_PLUG_NAME . '/common/js/');
define('PYT_CSS_PATH', PYT_PLUGINS_URL . '/' . PYT_PLUG_NAME . '/common/css/');
define('PYT_IMG_PATH', PYT_PLUGINS_URL . '/' . PYT_PLUG_NAME . '/common/img/');
define('PYT_MODULES_PATH', PYT_PLUGINS_URL . '/' . PYT_PLUG_NAME . '/modules/');

define('PYT_URL', PYT_SITE_URL);

define('PYT_LOADER_IMG', PYT_IMG_PATH . 'loading.gif');
define('PYT_TIME_FORMAT', 'H:i:s');
define('PYT_DATE_DL', '/');
define('PYT_DATE_FORMAT', 'm/d/Y');
define('PYT_DATE_FORMAT_HIS', 'm/d/Y (' . PYT_TIME_FORMAT . ')');
define('PYT_DB_PREF', 'pyt_');
define('PYT_MAIN_FILE', 'publish-your-table.php');

define('PYT_DEFAULT', 'default');

define('PYT_VERSION', '2.0.7');

define('PYT_CLASS_PREFIX', 'pytc');
define('PYT_TEST_MODE', true);

define('PYT_ADMIN',	'admin');
define('PYT_LOGGED', 'logged');
define('PYT_GUEST',	'guest');

define('PYT_METHODS', 'methods');
define('PYT_USERLEVELS', 'userlevels');
/**
 * Framework instance code
 */
define('PYT_CODE', 'pyt');
/**
 * Plugin name
 */
define('PYT_WP_PLUGIN_NAME', 'PubyDoc - Data Tables and Charts');
/**
 * Custom defined for plugin
 */
define('PYT_SHORTCODE', 'pyt-table');
define('PYT_SHORTCODE_PART', PYT_SHORTCODE . '-part');
define('PYT_SHORTCODE_CELL', PYT_SHORTCODE . '-cell');
define('PYT_SHORTCODE_VALUE', PYT_SHORTCODE . '-value');
define('PYT_SHORTCODE_CHART', 'pyt-diagram');

