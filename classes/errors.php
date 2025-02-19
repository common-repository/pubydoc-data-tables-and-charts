<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class ErrorsPyt {
	const FATAL = 'fatal';
	const MOD_INSTALL = 'mod_install';
	private static $errors = array();
	private static $haveErrors = false;
	
	public static $current = array();
	public static $displayed = false;
	
	public static function push( $error, $type = 'common' ) {
		if (!isset(self::$errors[$type])) {
			self::$errors[$type] = array();
		}
		if (is_array($error)) {
			self::$errors[$type] = array_merge(self::$errors[$type], $error);
		} else {
			self::$errors[$type][] = $error;
		}
		self::$haveErrors = true;
		
		if ('session' == $type) {
			self::setSession(self::$errors[$type]);
		}
	}
	public static function setSession( $error ) {
		$sesErrors = self::getSession();
		if (empty($sesErrors)) {
			$sesErrors = array();
		}
		if (is_array($error)) {
			$sesErrors = array_merge($sesErrors, $error);
		} else {
			$sesErrors[] = $error;
		}
		ReqPyt::setVar('sesErrors', $sesErrors, 'session');
	}
	public static function init() {
		$pytErrors = ReqPyt::getVar('pytErrors');
		if (!empty($pytErrors)) {
			if (!is_array($pytErrors)) {
				$pytErrors = array( $pytErrors );
			}
			$pytErrors = array_map('htmlspecialchars', array_map('stripslashes', array_map('trim', $pytErrors)));
			if (!empty($pytErrors)) {
				self::$current = $pytErrors;
				if (is_admin()) {
					add_action('admin_notices', array('ErrorsPyt', 'showAdminErrors'));
				} else {
					add_filter('the_content', array('ErrorsPyt', 'appendErrorsContent'), 99999);
				}
			}
		}
	}
	public static function showAdminErrors() {
		if (self::$current) {
			foreach (self::$current as $error) {
				echo '<div class="error notice is-dismissible"><p><strong>' . esc_html($error) . '</strong></p></div>';
			}
		}
	}
	public static function appendErrorsContent( $content ) {
		if (!self::$displayed && !empty(self::$current)) {
			$content = '<div class="toeErrorMsg">' . implode('<br />', self::$current) . '</div>' . $content;
			self::$displayed = true;
		}
		return $content;
	}
	public static function getSession() {
		return ReqPyt::getVar('sesErrors', 'session');
	}
	public static function clearSession() {
		ReqPyt::clearVar('sesErrors', 'session');
	}
	public static function get( $type = '' ) {
		$res = array();
		if (!empty(self::$errors)) {
			if (empty($type)) {
				foreach (self::$errors as $e) {
					foreach ($e as $error) {
						$res[] = $error;
					}
				}
			} else {
				$res = self::$errors[$type];
			}
		}
		return $res;
	}
	public static function haveErrors( $type = '' ) {
		if (empty($type)) {
			return self::$haveErrors;
		} else {
			return isset(self::$errors[$type]);
		}
	}
}
