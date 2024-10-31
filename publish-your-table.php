<?php
/**
 * Plugin Name: PubyDoc - Data Tables and Charts
 * Plugin URI: https://pubydoc.com/
 * Description: Create and manage beautiful data tables and charts with custom design.
 * Version: 2.0.7
 * Author: PubyDoc
 * License: GPL v2 or later
 * Text Domain: publish-your-table
 * Domain Path: /languages
 **/
/**
 * Base config constants and functions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.php');
/**
 * Connect all required core classes
 */
importClassPyt('DbPyt');
importClassPyt('InstallerPyt');
importClassPyt('BaseObjectPyt');
importClassPyt('ModulePyt');
importClassPyt('ModelPyt');
importClassPyt('ViewPyt');
importClassPyt('ControllerPyt');
importClassPyt('HelperPyt');
importClassPyt('DispatcherPyt');
importClassPyt('FieldPyt');
importClassPyt('TablePyt');
importClassPyt('FramePyt');

//importClassPyt('LangPyt');
importClassPyt('ReqPyt');
importClassPyt('UriPyt');
importClassPyt('HtmlPyt');
importClassPyt('ResponsePyt');
importClassPyt('FieldAdapterPyt');
importClassPyt('ValidatorPyt');
importClassPyt('ErrorsPyt');
importClassPyt('UtilsPyt');
importClassPyt('ModInstallerPyt');
importClassPyt('InstallerDbUpdaterPyt');
importClassPyt('DatePyt');
importClassPyt('AssetsPyt');
importClassPyt('CachePyt');
importClassPyt('UserPyt');
/**
 * Check plugin version - maybe we need to update database, and check global errors in request
 */
InstallerPyt::update();
ErrorsPyt::init();
/**
 * Start application
 */
FramePyt::_()->parseRoute();
FramePyt::_()->init();
FramePyt::_()->exec();
