<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TablesPyt extends ModulePyt {
	/**
	 * Variables for appending of table styles to site header
	 */
	private $_tablesInPosts = array();
	private $_tablesObj = array();
	public $tablesStyles = array();

	private $columnTypes = null;
	private $excludeFonts = null;
	private $standartFonts = null;

	public function init() {
		DispatcherPyt::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		DispatcherPyt::addFilter('jsInitVariables', array($this, 'addJsVariables'), 10, 2);
		DispatcherPyt::addAction('beforeTableRender', array($this, 'beforeTableRender'));

		add_shortcode(PYT_SHORTCODE, array($this, 'doShortcode'));
		add_shortcode(PYT_SHORTCODE_PART, array($this, 'doShortcodePart'));
		add_shortcode(PYT_SHORTCODE_CELL, array($this, 'doShortcodeCell'));
		add_shortcode(PYT_SHORTCODE_VALUE, array($this, 'doShortcodeValue'));

		add_action('template_redirect', array($this, 'getTablesInPosts'));
        add_action('wp_head', array($this, 'setTableStyles'));
        //add_action('widgets_init', array($this, 'registerWidget'));
		add_action('shutdown', array($this, 'onShutdown'));

        DispatcherPyt::addAction('afterTableLoaded', array($this, 'afterTableLoaded'));

		add_filter('jetpack_lazy_images_blacklisted_classes', array($this, 'excludeFromLazyLoad'), 999, 1);
		
	}

	public function beforeTableRender() {

	}
	public function afterTableLoaded() {
	}
	public function addJsVariables($jsData) {
		$types = $this->getColumnTypes();
		$jsData['dataFormats'] = array();
		foreach ($types as $key => $data) {
			if ($data['enabled']) {
				$jsData['dataFormats'][$key] = $data['format'];
			}
		}
		return $jsData;
	}
	public function getStandartFonts() {
		if (is_null($this->standartFonts)) {
			$this->standartFonts = DispatcherPyt::applyFilters('getFontsList', array(), 'standart');
		}
		return $this->standartFonts;
	}
	public function getExcludeFonts() {
		if (is_null($this->excludeFonts)) {
			if (FramePyt::_()->isPro()) {
				$this->excludeFonts = array_merge($this->getStandartFonts(), array('inherit', 'unset', 'initial', 'none'));
			} else {
				$this->excludeFonts = false;
			}
		}
		return $this->excludeFonts;
	}

	public function getTablesInPosts() {
		if (empty($this->_tablesInPosts)) {
			global $wp_query;

			$havePostsListing = $wp_query && is_object($wp_query) && isset($wp_query->posts) && is_array($wp_query->posts) && !empty($wp_query->posts);

			if ($havePostsListing) {
				$tableShortcode = PYT_SHORTCODE;

				foreach ($wp_query->posts as $post) {
					if (is_object($post) && isset($post->post_content)) {
						// Get all pyt table shortcodes
						if (preg_match_all('/\[\s*'. $tableShortcode .'.*\s+.*\]/iUs', $post->post_content, $matches)) {
							if (!empty($matches[0])) {
								foreach ($matches[0] as $data) {
									// Find all params in shortcodes we have got
									preg_match_all('/(?P<KEYS>\w+)\=[\"\']?(?P<VALUES>[^\"\']*)[\"\']?[\s+|\]]/iU', $data, $params);
									if (!is_array($params['KEYS'])) {
										$params['KEYS'] = array( $params['KEYS'] );
									}
									if (!is_array($params['VALUES'])) {
										$params['VALUES'] = array( $params['VALUES'] );
									}
									$table_params = array();
									foreach ($params['KEYS'] as $key => $val) {
										if ($val == 'id') {
											array_push($this->_tablesInPosts, $params['VALUES'][$key]);
										}
										$table_params[$val] = $params['VALUES'][$key];
									}
								}
							}
						}
					}
				}
			}
		}
		return $this->_tablesInPosts;
	}

	public function getTablesObj() {
		if (empty($this->_tablesObj)) {
			$model = $this->getModel();
			$tablesInPosts = $this->getTablesInPosts();

			foreach ($tablesInPosts as $tableId) {
				if (isset($this->_tablesObj[$tableId])) {
					if ($this->_tablesObj[$tableId] !== false) {
						continue;
					}
				} else {
					$table = $model->getTableData($tableId);
					if (empty($table)) {
						$this->_tablesObj[$tableId] = false;
						continue;
					} else {
						$this->_tablesObj[$tableId] = array('table' => $table, 'views' => array());
					}
				}
				$viewId = $this->genTableView($tableId);
				$this->_tablesObj[$tableId]['views'][$viewId] = false; // false - not displayed
			}
		}
		return $this->_tablesObj;
	}

	public function genTableView( $id ) {
		return $id . '-' . mt_rand(1, 99999);
	}

	public function getTableObj( $id ) {
		if (isset($this->_tablesObj[$id])) {
			if ($this->_tablesObj[$id] === false) {
				return false;
			}
			$table = $this->_tablesObj[$id]['table'];
			foreach ($this->_tablesObj[$id]['views'] as $viewId => $displayed) {
				if (!$displayed) {
					$table['view_id'] = $viewId;
					$this->_tablesObj[$id]['views'][$viewId] = true;
					return $table;
				}
			}
		} else {
			$table = $this->getModel()->getTableData($id);
			if (empty($tableObj)) {
				$this->_tablesObj[$id] = false;
				return false;
			} else {
				$this->_tablesObj[$id] = array('table' => $table, 'views' => array());
			}
		}
		$viewId = $this->genTableView($id);
		$this->_tablesObj[$id]['views'][$viewId] = true;
		$table['view_id'] = $viewId;
		return $table;
	}

	public function setTableStyles() {
		if (!empty($this->_tablesInPosts)) {
			$tablesOnPage = $this->getTablesObj();

			$view = $this->getView('shortcode');
			foreach ($tablesOnPage as $table) {
				print $view->addTableStyles($table['table']);
			}
		}
	}

	/**
	 * Call wp footer manualy on broken themes to ensure than scripts are loaded
	 */
	public function onShutdown() {
		/*$settings = get_option('supsystic_tbl_settings');
		if (empty($settings['disable_wp_footer_fix']) && !is_admin() && did_action('after_setup_theme') && did_action('get_footer') && !did_action('wp_footer')) {
			wp_footer();
		}*/
	}

	public function addAdminTab( $tabs ) {
		$icon = FramePyt::_()->isPro() ? '' : ' pubydoc-show-pro';
		$code = $this->getCode();
		$tabs[ $code . '-new' ] = array(
			'label' => esc_html__('Add Table', 'publish-your-table'), 'callback' => array($this, 'getTabNewTable'), 'fa_icon' => 'pyt-add', 'sort_order' => 10, 'add_bread' => $this->getCode(),
		);
		$tabs[ $code . '-edit' ] = array(
			'label' => esc_html__('Edit', 'publish-your-table'), 'callback' => array($this, 'getTabEditTable'), 'sort_order' => 20, 'child_of' => $this->getCode(), 'hidden' => 1, 'add_bread' => $this->getCode(),
		);
		$tabs[$code] = array(
			'label' => esc_html__('Tables', 'publish-your-table'), 'callback' => array($this, 'getTabAllTables'), 'fa_icon' => 'pyt-visibility', 'sort_order' => 20, //'is_main' => true,
		);
		/*$tabs[$code . '-diagrams'] = array(
			'label' => esc_html__('Diagrams', 'publish-your-table'), 'callback' => array($this, 'getTabAllDiagrams'), 'fa_icon' => 'pyt-diagram' . $icon, 'sort_order' => 21, 'add_bread' => $this->getCode(),
		);*/
		return $tabs;
	}

	public function getTabAllTables() {
		return $this->getView()->getTabAllTables();
	}
	/*public function getTabAllDiagrams() {
		return DispatcherPyt::applyFilters('getTabAllDiagrams', $this->getView()->getTabAllDiagrams());
		//return $this->getView()->getTabAllDiagrams();
	}*/
	public function getTabNewTable() {
		return $this->getView()->getTabNewTable();
	}
	public function getTabEditTable() {
		$id = ReqPyt::getVar('id', 'get');
		return $this->getView()->getTabEditTable($id);
	}

	public function getShortcodesList( $type ) {					
		$shortcodes = array(
			'shortcode' => array(
				'name' => PYT_SHORTCODE,
				'label' => __('Table Shortcode', 'publish-your-table'),
				'attrs' => '',
				'info' => __('lets display the table in the site content', 'publish-your-table')
			));
		if ($type != 3) {
			$shortcodes['part_shortcode'] = array(
				'name' => PYT_SHORTCODE_PART,
				'label' => __('Table Part Shortcode', 'publish-your-table'),
				'attrs' => 'row=1-3 col=A,B',
				'info' => __('lets display just a part of table in the site content. You can display several rows or cols, for example, ', 'publish-your-table') . "'row=1,2,3', " . __('or set diapazone', 'publish-your-table') . ": 'row=1-3' " . __('or', 'publish-your-table') . " 'row=1-3,6'"
			);
			$shortcodes['cell_shortcode'] = array(
				'name' => PYT_SHORTCODE_CELL,
				'label' => __('Cell Shortcode', 'publish-your-table'),
				'attrs' => 'row=1 col=A',
				'info' => __('lets display a table with single cell in the site content', 'publish-your-table')
			);
			$shortcodes['value_shortcode'] = array(
				'name' => PYT_SHORTCODE_VALUE,
				'label' => __('Value Shortcode', 'publish-your-table'),
				'attrs' => 'row=1 col=A',
				'info' => __('lets display a value of single table cell in the site content', 'publish-your-table')
			);
		}
		$shortcodes = DispatcherPyt::applyFilters('tableShortcodesList', $shortcodes, $type);
		$shortcodes['php_code'] = array(
			'name' => PYT_SHORTCODE,
			'label' => __('PHP code', 'publish-your-table'),
			'attrs' => '',
			'info' => __('lets display the table through themes/plugins files (for example in the site footer). You can use any shortcode in this way.', 'publish-your-table')
		);
		return $shortcodes;
	}

	public function getEditTableTabsList( $type, $current = '' ) {
		$proClass = ( FramePyt::_()->isPro() ? '' : ' pubydoc-show-pro' );
		switch ($type) {
			case 1:
				$tabs = array(
					'builder' => array(
						'icon' => 'fa-google',
						'class' => $proClass,
						'pro' => true,
						'label' => $this->getModel()->getFieldLists('type', 1),
					)
				);
				break;
			case 2:
				$tabs = array(
					'woocommerce' => array(
						'icon' => 'fa-woocommerce',
						'class' => $proClass,
						'label' => $this->getModel()->getFieldLists('type', 2)
					)
				);
				break;
			case 3:
				$tabs = array(
					'builder' => array(
						'icon' => 'fa-database',
						'class' => '',
						'label' => $this->getModel()->getFieldLists('type', 3)
					)
				);
				break;
						
			default:
				$tabs = array(
					'builder' => array(
						'icon' => 'fa-th',
						'class' => '',
						'label' => __('Builder', 'publish-your-table')
					)
				);
				break;
		}
		$tabs = array_merge($tabs, array(
			'options' => array(
				'icon' => 'fa-wrench',
				'class' => '',
				'label' => __('Options', 'publish-your-table'),
			),
			'css' => array(
				'icon' => 'fa-code',
				'class' => '',
				'label' => __('CSS', 'publish-your-table'),
			),
			'js' => array(
				'icon' => 'fa-code',
				'class' => '',
				'label' => __('JS', 'publish-your-table'),
			),
			/*'history' => array(
				'icon' => 'fa-history',
				'class' => $proClass,
				'label' => __('History', 'publish-your-table'),
			),*/
		));

		if (empty($current) || !isset($tabs[$current])) {
			reset($tabs);
			$current = key($tabs);
		}
		$tabs[$current]['class'] .= ' current';
		
		return DispatcherPyt::applyFilters('tableEditTabsList', $tabs);
	}

	public function getMenuTableTypeList() {
		$proClass = ( FramePyt::_()->isPro() ? '' : ' pubydoc-show-pro' );
		$tabs = array(
			'manual' => array(
				'icon' => 'fa-gear',
				'class' => 'current',
				'type' => 0,
				'creation' => 0,
				'label' => __('Manually', 'publish-your-table'),
			),
			'import' => array(
				'icon' => 'fa-arrow-up',
				'type' => 0,
				'creation' => 1,
				'class' => $proClass,
				'label' => __('Import table', 'publish-your-table'),
			),
			'google' => array(
				'icon' => 'fa-google-plus-square',
				'type' => 1,
				'creation' => 3,
				'class' => $proClass,
				'label' => __('GoogleSheet table', 'publish-your-table'),
			),
			/*'woo' => array(
				'icon' => 'fa-woocommerce',
				'type' => 2,
				'creation' => 0,
				'class' => $proClass,
				'label' => __('WooCommerce table', 'publish-your-table'),
			),*/
			'sql' => array(
				'icon' => 'fa-sql',
				'type' => 3,
				'creation' => 0,
				'class' => $proClass,
				'label' => __('Custom SQL Query', 'publish-your-table'),
			)
		);
				
		return DispatcherPyt::applyFilters('tableMenuTableTypeList', $tabs);
	}

	public function getTabsColumnSettings() {
		$proClass = ( FramePyt::_()->isPro() ? '' : ' pubydoc-show-pro' );
		$tabs = array(
			'general' => array(
				'class' => 'current',
				'label' => __('General', 'publish-your-table'),
			),
			'advanced' => array(
				'class' => '',
				'label' => __('Advanced', 'publish-your-table'),
			),
			'header' => array(
				'class' => '',
				'label' => __('Header', 'publish-your-table'),
			),
			'body' => array(
				'class' => '',
				'label' => __('Body', 'publish-your-table'),
			),
			'conditional' => array(
				'class' => $proClass,
				'label' => __('Conditional', 'publish-your-table'),
			),
		);
				
		return DispatcherPyt::applyFilters('tableTabsBSColumnSettings', $tabs);
	}
	public function getColumnTypes() {
		if (is_null($this->columnTypes)) {
			$isPro = FramePyt::_()->isPro();
			$types = array(
				'text' => array(
					'enabled' => true,
					'format' => '',
					'label' => __('Single Line Text Field', 'publish-your-table'),
				),
				'textarea' => array(
					'enabled' => true,
					'format' => '',
					'label' => __('Text area', 'publish-your-table'),
				),
				'html' => array(
					'enabled' => true,
					'format' => '',
					'label' => __('HTML Field', 'publish-your-table'),
				),
				'number' => array(
					'enabled' => true,
					'format' => '1,000.00',
					'label' => __('Numeric Value', 'publish-your-table'),
				),
				'money' => array(
					'enabled' => true,
					'format' => '$1,000.00',
					'label' => __('Currency', 'publish-your-table'),
				),
				'percent' => array(
					'enabled' => true,
					'format' => '1.00%',
					'label' => __('Percent', 'publish-your-table'),
				),
				'convert' => array(
					'enabled' => true,
					'format' => '1.00%',
					'label' => __('Percent with Convert', 'publish-your-table'),
				),
				'date' => array(
					'enabled' => true,
					'format' => 'dd.mm.yy',
					'label' => __('Date Field', 'publish-your-table'),
				),
				'button' => array(
					'enabled' => $isPro,
					'attrs' => 'data-for="col"', // for columns only
					'format' => '',
					'label' => __('Button/Link', 'publish-your-table')
				),
				'select' => array(
					'enabled' => $isPro,
					'format' => '',
					'label' => __('Select Field', 'publish-your-table')
				),
				'file' => array(
					'enabled' => $isPro,
					'format' => '',
					'label' => __('File upload', 'publish-your-table')
				),
			);
			$this->columnTypes = DispatcherPyt::applyFilters('tableColTypes', $types);
		}
				
		return $this->columnTypes;
	}

	public function getColumnResponsive() {
		$types = array(
			'' => __('Default', 'publish-your-table'),
			'all' => __('Always show in all devices', 'publish-your-table'),
			'not-desktop' => __('Hidden On Desktop', 'publish-your-table'),
			'not-mobile' => __('Initial Hidden Mobile', 'publish-your-table'),
			'desktop' => __('Initial Hidden Mobile and Tab', 'publish-your-table'),
			'none' => __('Initial Hidden Mobile, Tab and Regular Computers', 'publish-your-table'),
			'hidden' => __('Totally hidden on all devices', 'publish-your-table'),
		);
		return DispatcherPyt::applyFilters('tableColResponsive', $types);
	}
	public function getDateFormats() {
		$formats = array(
			'm/d/yy' => 'm/d/yy (Ex. 4/28/2018)',
			'm/d/y' => 'm/d/y (Ex. 4/28/18)',
			'mm/dd/yy' => 'mm/dd/yy (Ex. 04/28/2018)',
			'mm/dm/y' => 'mm/dm/y (Ex. 04/28/18)',
			'M/d/yy' => 'M/d/yy (Ex. Apr/28/2018)',
			'y/mm/dd' => 'y/mm/dd (Ex. 18/04/28)',
			'yy-mm-dd' => 'yy-mm-dd (Ex. 2018/04/28)',
			'dd-M-y' => 'dd-M-y (Ex. 28-Apr-18)',
			'dd/mm/yy' => 'dd/mm/yy (Ex. 28.04.2018)',
			'dd.mm.y' => 'dd.mm.y (Ex. 28.04.18)',
			'MM d, yy' => 'MM d, yy (Ex. April 28, 2018)'
		);
		return DispatcherPyt::applyFilters('tableDateFormats', $formats);
	}

	public function doShortcode( $params ) {
		return $this->getView('shortcode')->renderShortcode($params);
	}
	public function doShortcodePart( $params ) {
		return $this->getView('shortcode')->renderShortcodePart($params);
	}
	public function doShortcodeCell( $params ) {
		return $this->getView('shortcode')->renderShortcodeCell($params);
	}
	public function doShortcodeValue( $params ) {
		return $this->getView('shortcode')->renderShortcodeValue($params);
	}
	public function excludeFromLazyLoad($classes) {
		array_push($classes, 'pytSkipLazy');
		return $classes;
	}
	public function setIniLimits() {
		// Override local and wp limits
		if (strlen(ini_get('memory_limit')) < 4) {
			ini_set('memory_limit', '12000M');
		}
		if (strlen(ini_get('connect_timeout')) < 2) {
			ini_set('connect_timeout', 24000);
		}
		if (strlen(ini_get('max_execution_time')) < 2) {
			ini_set('max_execution_time', 24000);
		}
		if (strlen(ini_get('max_input_time')) < 2) {
			ini_set('max_input_time', 24000);
		}
	}
}
