<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class FramePyt extends BaseObjectPyt {
	private $_modules = array();
	private $_tables = array();
	private $_allModules = array();
	/**
	 * Uses to know if we are on one of the plugin pages
	 */
	private $_inPlugin = false;
	/**
	 * Array to hold all scripts and add them in one time in addScripts method
	 */
	private $_scripts = array();
	private $_scriptsInitialized = false;
	private $_styles = array();
	private $_stylesInitialized = false;
	private $_useFootAssets = false;

	private $_scriptsVars = array();
	private $_mod = '';
	private $_action = '';
	/**
	 * Object with result of executing non-ajax module request
	 */
	private $_res = null;

	public function __construct() {
		$this->_res = toeCreateObjPyt('response', array());

	}
	public static function getInstance() {
		static $instance;
		if (!$instance) {
			$instance = new FramePyt();
		}
		return $instance;
	}
	public static function _() {
		return self::getInstance();
	}
	public function parseRoute() {
		// Check plugin
		$pl = ReqPyt::getVar('pl');
		if (PYT_CODE == $pl) {
			$mod = ReqPyt::getMode();
			if ($mod) {
				$this->_mod = $mod;
			}
			$action = ReqPyt::getVar('action');
			if ($action) {
				$this->_action = $action;
			}
		}
	}
	public function setMod( $mod ) {
		$this->_mod = $mod;
	}
	public function getMod() {
		return $this->_mod;
	}
	public function setAction( $action ) {
		$this->_action = $action;
	}
	public function getAction() {
		return $this->_action;
	}
	protected function _extractModules() {
		$activeModules = $this->getTable('modules')->get($this->getTable('modules')->alias() . '.*');
		if ($activeModules) {
			foreach ($activeModules as $m) {
				$code = $m['code'];
				$moduleLocationDir = PYT_MODULES_DIR;
				if (!empty($m['ex_plug_dir'])) {
					$moduleLocationDir = UtilsPyt::getExtModDir( $m['ex_plug_dir'] );
				}
				if (is_dir($moduleLocationDir . $code)) {
					$this->_allModules[$m['code']] = 1;
					if ((bool) $m['active']) {
						importClassPyt($code . strFirstUpPyt(PYT_CODE), $moduleLocationDir . $code . DS . 'mod.php');
						$moduleClass = toeGetClassNamePyt($code);
						if (class_exists($moduleClass)) {
							$this->_modules[$code] = new $moduleClass($m);
							if (is_dir($moduleLocationDir . $code . DS . 'tables')) {
								$this->_extractTables($moduleLocationDir . $code . DS . 'tables' . DS);
							}
						}
					}
				}
			}
		}
	}
	protected function _initModules() {
		if (!empty($this->_modules)) {
			foreach ($this->_modules as $mod) {
				 $mod->init();
			}
		}
	}
	public function init() {
		ReqPyt::init();
		CachePyt::_()->init();
		
		$this->_extractTables();

		$this->_extractModules();

		$this->_initModules();

		DispatcherPyt::doAction('afterModulesInit');

		ModInstallerPyt::checkActivationMessages();

		$this->_execModules();
		if ($this->isSuccessInit()) {
			AssetsPyt::_()->init();
		}

		$addAssetsAction = $this->usePackAssets() && !is_admin() ? 'wp_footer' : 'init';

		add_action($addAssetsAction, array($this, 'addScripts'));
		add_action($addAssetsAction, array($this, 'addStyles'));
		//add_action('wp_enqueue_scripts', array($this, 'addStyles'));
		global $langOK;
		register_activation_hook(PYT_DIR . DS . PYT_MAIN_FILE, array('UtilsPyt', 'activatePlugin')); //See classes/install.php file
		register_uninstall_hook(PYT_DIR . DS . PYT_MAIN_FILE, array('UtilsPyt', 'deletePlugin'));
		register_deactivation_hook(PYT_DIR . DS . PYT_MAIN_FILE, array( 'UtilsPyt', 'deactivatePlugin' ) );

		add_action('init', array($this, 'connectLang'));
	}
	public function isSuccessInit() {
		return !empty($this->_modules) && $this->getModule('options') && $this->getModule('adminmenu');
	}
	public function connectLang() {
		global $langOK;
		$langOK = load_plugin_textdomain('publish-your-table', false, PYT_PLUG_NAME . '/languages/');
	}
	/**
	 * Check permissions for action in controller by $code and made corresponding action
	 *
	 * @param string $code Code of controller that need to be checked
	 * @param string $action Action that need to be checked
	 * @return bool true if ok, else - should exit from application
	 */
	public function checkPermissions( $code, $action ) {
		//return true;
		if ($this->havePermissions($code, $action)) {
			return true;
		} else {
			exit(esc_html_e('You have no permissions to view this page', 'publish-your-table'));
		}
	}
	/**
	 * Check permissions for action in controller by $code
	 *
	 * @param string $code Code of controller that need to be checked
	 * @param string $action Action that need to be checked
	 * @return bool true if ok, else - false
	 */
	public function havePermissions( $code, $action ) {
		$res = true;
		$mod = $this->getModule($code);
		$action = strtolower($action);
		if ($mod) {
			$permissions = $mod->getController()->getPermissions();
			if (!empty($permissions)) {  // Special permissions
				$user = new UserPyt;
				if (isset($permissions[PYT_METHODS]) && !empty($permissions[PYT_METHODS])) {
					foreach ($permissions[PYT_METHODS] as $method => $permissions) {   // Make case-insensitive
						$permissions[PYT_METHODS][strtolower($method)] = $permissions;
					}
					if (array_key_exists($action, $permissions[PYT_METHODS])) {        // Permission for this method exists
						$currentUserPosition = $user->getCurrentUserPosition();
						if ( ( is_array($permissions[ PYT_METHODS ][ $action ] ) && !in_array($currentUserPosition, $permissions[ PYT_METHODS ][ $action ]) )
							|| ( !is_array($permissions[ PYT_METHODS ][ $action ]) && $permissions[PYT_METHODS][$action] != $currentUserPosition )
						) {
							$res = false;
						}
					}
				}
				if (isset($permissions[PYT_USERLEVELS])	&& !empty($permissions[PYT_USERLEVELS])) {
					$currentUserPosition = $user->getCurrentUserPosition();
					// For multi-sites network admin role is undefined, let's do this here
					if (is_multisite() && is_admin() && is_super_admin()) {
						$currentUserPosition = PYT_ADMIN;
					}
					foreach ($permissions[PYT_USERLEVELS] as $userlevel => $methods) {
						if (is_array($methods)) {
							$lowerMethods = array_map('strtolower', $methods);          // Make case-insensitive
							if (in_array($action, $lowerMethods)) {                      // Permission for this method exists
								if ($currentUserPosition != $userlevel) {
									$res = false;
								}
								break;
							}
						} else {
							$lowerMethod = strtolower($methods);            // Make case-insensitive
							if ($lowerMethod == $action) {                   // Permission for this method exists
								if ($currentUserPosition != $userlevel) {
									$res = false;
								}
								break;
							}
						}
					}
				}
			}
			if ($res) {	// Additional check for nonces
				$noncedMethods = $mod->getController()->getNoncedMethods();
				if (!empty($noncedMethods)) {
					$noncedMethods = array_map('strtolower', $noncedMethods);
					if (in_array($action, $noncedMethods)) {
						check_ajax_referer('pyt-nonce', 'pytNonce');
					}
				}
			}
		}
		return $res;
	}
	public function getRes() {
		return $this->_res;
	}
	public function execAfterWpInit() {
		$this->_doExec();
	}
	/**
	 * Check if method for module require some special permission. We can detect users permissions only after wp init action was done.
	 */
	protected function _execOnlyAfterWpInit() {
		$res = false;
		$mod = $this->getModule( $this->_mod );
		$action = strtolower( $this->_action );
		if ($mod) {
			$permissions = $mod->getController()->getPermissions();
			if (!empty($permissions)) {  // Special permissions
				if (isset($permissions[PYT_METHODS]) && !empty($permissions[PYT_METHODS])) {
					foreach ($permissions[PYT_METHODS] as $method => $permissions) {   // Make case-insensitive
						$permissions[PYT_METHODS][strtolower($method)] = $permissions;
					}
					if (array_key_exists($action, $permissions[PYT_METHODS])) {        // Permission for this method exists
						$res = true;
					}
				}
				if (isset($permissions[PYT_USERLEVELS])	&& !empty($permissions[PYT_USERLEVELS])) {
					$res = true;
				}
			}
			if (!$res) {
				$noncedMethods = $mod->getController()->getNoncedMethods();
				if (!empty($noncedMethods)) {
					$noncedMethods = array_map('strtolower', $noncedMethods);
					if (in_array($action, $noncedMethods)) {
						$res = true;
					}
				}
			}
		}
		return $res;
	}
	protected function _execModules() {
		if ($this->_mod) {
			// If module exist and is active
			$mod = $this->getModule($this->_mod);
			if ($mod && !empty($this->_action)) {
				//add_action('init', array($this, 'execAfterWpInit'));
				if ($this->_execOnlyAfterWpInit()) {
					add_action('init', array($this, 'execAfterWpInit'));
				} else {
					$this->_doExec();
				}
			}
		}
	}
	protected function _doExec() {
		$mod = $this->getModule($this->_mod);
		if ($mod && $this->checkPermissions($this->_mod, $this->_action)) {
			switch (ReqPyt::getVar('reqType')) {
				case 'ajax':
					add_action('wp_ajax_' . $this->_action, array($mod->getController(), $this->_action));
					add_action('wp_ajax_nopriv_' . $this->_action, array($mod->getController(), $this->_action));
					break;
				default:
					$this->_res = $mod->exec($this->_action);
					break;
			}
		}
	}
	protected function _extractTables( $tablesDir = PYT_TABLES_DIR ) {
		$mDirHandle = opendir($tablesDir);
		while ( ( $file = readdir($mDirHandle) ) !== false ) {
			if ( is_file($tablesDir . $file) && ( '.' != $file ) && ( '..' != $file ) && strpos($file, '.php') ) {
				$this->_extractTable( str_replace('.php', '', $file), $tablesDir );
			}
		}
	}
	protected function _extractTable( $tableName, $tablesDir = PYT_TABLES_DIR ) {
		importClassPyt('noClassNameHere', $tablesDir . $tableName . '.php');
		$this->_tables[$tableName] = TablePyt::_($tableName);
	}
	/**
	 * Public alias for _extractTables method
	 *
	 * @see _extractTables
	 */
	public function extractTables( $tablesDir ) {
		if (!empty($tablesDir)) {
			$this->_extractTables($tablesDir);
		}
	}
	public function exec() {
		//deprecated
	}
	public function getTables () {
		return $this->_tables;
	}
	/**
	 * Return table by name
	 *
	 * @param string $tableName table name in database
	 * @return object table
	 * @example FramePyt::_()->getTable('products')->getAll()
	 */
	public function getTable( $tableName ) {
		if (empty($this->_tables[$tableName])) {
			$this->_extractTable($tableName);
		}
		return $this->_tables[$tableName];
	}
	public function getModules( $filter = array() ) {
		$res = array();
		if (empty($filter)) {
			$res = $this->_modules;
		} else {
			foreach ($this->_modules as $code => $mod) {
				if (isset($filter['type'])) {
					if (is_numeric($filter['type']) && $filter['type'] == $mod->getTypeID()) {
						$res[$code] = $mod;
					} elseif ($filter['type'] == $mod->getType()) {
						$res[$code] = $mod;
					}
				}
			}
		}
		return $res;
	}

	public function getModule( $code ) {
		return ( isset($this->_modules[$code]) ? $this->_modules[$code] : null );
	}
	public function inPlugin() {
		return $this->_inPlugin;
	}
	public function usePackAssets() {
		if (!$this->_useFootAssets && $this->getModule('options') && $this->getModule('options')->get('foot_assets')) {
			$this->_useFootAssets = true;
		}
		return $this->_useFootAssets;
	}
	/**
	 * Push data to script array to use it all in addScripts method
	 *
	 * @see wp_enqueue_script definition
	 */
	public function addScript( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false, $vars = array() ) {
		$src = empty($src) ? $src : UriPyt::_($src);
		if (!$ver) {
			$ver = PYT_VERSION;
		}
		if ($this->_scriptsInitialized) {
			wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
		} else {
			$this->_scripts[] = array(
				'handle' => $handle,
				'src' => $src,
				'deps' => $deps,
				'ver' => $ver,
				'in_footer' => $in_footer,
				'vars' => $vars
			);
		}
	}
	/**
	 * Add all scripts from _scripts array to worwpfess
	 */
	public function addScripts() {
		if (!empty($this->_scripts)) {
			foreach ($this->_scripts as $s) {
				wp_enqueue_script($s['handle'], $s['src'], $s['deps'], $s['ver'], $s['in_footer']);

				if ($s['vars'] || isset($this->_scriptsVars[$s['handle']])) {
					$vars = array();
					if ($s['vars']) {
						$vars = $s['vars'];
					}
					if ($this->_scriptsVars[$s['handle']]) {
						$vars = array_merge($vars, $this->_scriptsVars[$s['handle']]);
					}
					if ($vars) {
						foreach ($vars as $k => $v) {
							wp_localize_script($s['handle'], $k, is_array($v) ? $v : array($v));
						}
					}
				}
			}
		}
		$this->_scriptsInitialized = true;
	}
	public function addJSVar( $script, $name, $val ) {
		if ($this->_scriptsInitialized) {
			wp_localize_script($script, $name, is_array($val) ? $val : array($val));
		} else {
			$this->_scriptsVars[$script][$name] = $val;
		}
	}

	public function addStyle( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
		$src = empty($src) ? $src : UriPyt::_($src);
		if (!$ver) {
			$ver = PYT_VERSION;
		}
		if ($this->_stylesInitialized) {
			wp_enqueue_style($handle, $src, $deps, $ver, $media);
		} else {
			$this->_styles[] = array(
				'handle' => $handle,
				'src' => $src,
				'deps' => $deps,
				'ver' => $ver,
				'media' => $media
			);
		}
	}
	public function addStyles() {
		if (!empty($this->_styles)) {
			foreach ($this->_styles as $s) {
				wp_enqueue_style($s['handle'], $s['src'], $s['deps'], $s['ver'], $s['media']);
			}
		}
		$this->_stylesInitialized = true;
	}
	//Very interesting thing going here.............
	public function loadPlugins() {
		require_once(ABSPATH . 'wp-includes/pluggable.php');
	}
	public function loadWPSettings() {
		require_once(ABSPATH . 'wp-settings.php');
	}
	public function loadLocale() {
		require_once(ABSPATH . 'wp-includes/locale.php');
	}
	public function moduleActive( $code ) {
		return isset($this->_modules[$code]);
	}
	public function moduleExists( $code ) {
		if ($this->moduleActive($code)) {
			return true;
		}
		return isset($this->_allModules[$code]);
	}
	public function isTplEditor() {
		$tplEditor = ReqPyt::getVar('tplEditor');
		return (bool) $tplEditor;
	}
	/**
	 * This is custom method for each plugin and should be modified if you create copy from this instance.
	 */
	public function isAdminPlugOptsPage() {
		$page = ReqPyt::getVar('page');
		if (is_admin() && !empty($page) && strpos($page, self::_()->getModule('adminmenu')->getMainSlug()) !== false) {
			return true;
		}
		return false;
	}
	public function isAdminPlugPage() {
		if ($this->isAdminPlugOptsPage()) {
			return true;
		}
		return false;
	}
	public function licenseDeactivated() {
		return ( !$this->getModule('license') && $this->moduleExists('license') );
	}
	public function savePluginActivationErrors() {
		update_option(PYT_CODE . '_plugin_activation_errors', ob_get_contents());
	}
	public function getActivationErrors() {
		return get_option(PYT_CODE . '_plugin_activation_errors');
	}
	public function isPro() {
		return true;
		return $this->moduleExists('license') && $this->getModule('license') && $this->moduleExists('tablespro') && $this->getModule('tablespro');
	}
	public function getProUrl() {
		return 'https://pubydoc.com';
	}
}
