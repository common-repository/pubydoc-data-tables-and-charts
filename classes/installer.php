<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class InstallerPyt {
	public static $update_to_version_method = '';
	private static $_firstTimeActivated = false;
	public static function init( $isUpdate = false ) {
		global $wpdb;
		$wpPrefix = $wpdb->prefix; /* add to 0.0.3 Versiom */
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$current_version = get_option($wpPrefix . PYT_DB_PREF . 'db_version', 0);
		if (!$current_version) {
			self::$_firstTimeActivated = true;
		}
		/**
		 * Table modules 
		 */
		if (!DbPyt::exist('@__modules')) {
			dbDelta(DbPyt::prepareQuery("CREATE TABLE IF NOT EXISTS `@__modules` (
			  `id` smallint(3) NOT NULL AUTO_INCREMENT,
			  `code` varchar(32) NOT NULL,
			  `active` tinyint(1) NOT NULL DEFAULT '0',
			  `type_id` tinyint(1) NOT NULL DEFAULT '0',
			  `label` varchar(64) DEFAULT NULL,
			  `ex_plug_dir` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE INDEX `code` (`code`)
			) DEFAULT CHARSET=utf8;"));
			DbPyt::query("INSERT INTO `@__modules` (id, code, active, type_id, label) VALUES
				(NULL, 'adminmenu',1,1,'Admin Menu'),
				(NULL, 'options',1,1,'Options'),
				(NULL, 'tables',1,1,'Tables'),
				(NULL, 'optionspro',1,1,'Options Pro'),
				(NULL, 'tablespro',1,1,'Tables Pro'),
				(NULL, 'import',1,1,'Import'),
				(NULL, 'export',1,1,'Export'),
				(NULL, 'diagrams',1,1,'Diagrams');");
		} else {
			DbPyt::query( "DELETE FROM `@__modules` WHERE code='license'" );
			DbPyt::query( "UPDATE `@__modules` SET active=1, type_id=1, ex_plug_dir=null" );
		}
		
		/**
		 * Table tables
		 */
		if (!DbPyt::exist('@__tables')) {
			dbDelta(DbPyt::prepareQuery("CREATE TABLE IF NOT EXISTS `@__tables` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`title` varchar(128) NULL DEFAULT NULL,
				`type` tinyint(1) NOT NULL DEFAULT '0',
				`creation` tinyint(1) NOT NULL DEFAULT '0',
				`builder` tinyint(1) NOT NULL DEFAULT '0',
				`source` mediumtext NOT NULL,
				`options` mediumtext NOT NULL,
				`columns` mediumtext NOT NULL,
				`tuning` mediumtext NOT NULL,
				`css` text,
				`add_css` text,
				`add_js` text,
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;"));
		}
		
		/**
		 * Table diagrams
		 */
		if (!DbPyt::exist("@__diagrams")) {
			//require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta(DbPyt::prepareQuery("CREATE TABLE IF NOT EXISTS `@__diagrams` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`title` varchar(128) NULL DEFAULT NULL,
				`type` tinyint(1) NOT NULL DEFAULT '0',
				`table_id` int(11) NOT NULL,
				`table_range` varchar(15) NULL,
				`status` tinyint(1) NOT NULL DEFAULT '0',
				`options` mediumtext NULL DEFAULT NULL,
				`config` mediumtext NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			 ) DEFAULT CHARSET=utf8;"));
		}
		InstallerDbUpdaterPyt::runUpdate();
		if ($current_version && !self::$_firstTimeActivated) {
			self::setUsed();
		}
		update_option($wpPrefix . PYT_DB_PREF . 'db_version', PYT_VERSION);
		add_option($wpPrefix . PYT_DB_PREF . 'db_installed', 1);
	}
	public static function setUsed() {
		update_option(PYT_DB_PREF . 'plug_was_used', 1);
	}
	public static function isUsed() {
		return (int) get_option(PYT_DB_PREF . 'plug_was_used');
	}
	public static function delete() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix;
		$wpdb->query('DROP TABLE IF EXISTS `' . $wpdb->prefix . esc_sql(PYT_DB_PREF) . 'modules`');
		delete_option($wpPrefix . PYT_DB_PREF . 'db_version');
		delete_option($wpPrefix . PYT_DB_PREF . 'db_installed');
	}
	public static function deactivate() {
		
	}
	public static function update() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix; /* add to 0.0.3 Versiom */
		$currentVersion = get_option($wpPrefix . PYT_DB_PREF . 'db_version', 0);
		if (!$currentVersion || version_compare(PYT_VERSION, $currentVersion, '>')) {
			self::init( true );
			update_option($wpPrefix . PYT_DB_PREF . 'db_version', PYT_VERSION);
		}
	}
}
