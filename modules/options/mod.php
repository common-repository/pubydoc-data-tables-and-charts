<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class OptionsPyt extends ModulePyt {
	private $_options = array();
	private $_optionsToCategoires = array();	// For faster search

	public function init() {
		add_action('init', array($this, 'initAllOptValues'), 99);	// It should be init after all languages was inited (frame::connectLang)
		DispatcherPyt::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
	}
	public function initAllOptValues() {
		// Just to make sure - that we loaded all default options values
		$this->getAll();
	}
	/**
	 * This method provides fast access to options model method get
	 *
	 * @see optionsModel::get($d)
	 */
	public function get( $code ) {
		return $this->getModel()->get($code);
	}
	/**
	 * This method provides fast access to options model method get
	 *
	 * @see optionsModel::get($d)
	 */
	public function isEmpty( $code ) {
		return $this->getModel()->isEmpty($code);
	}

	public function addAdminTab( $tabs ) {
		$tabs['settings'] = array(
			'label' => esc_html__('Settings', 'publish-your-table'), 'callback' => array($this, 'getSettingsTabContent'), 'fa_icon' => 'pyt-settings', 'sort_order' => 30,
		);
		return $tabs;
	}
	public function getSettingsTabContent() {
		return $this->getView()->getSettingsTabContent();
	}
	
	public function getRolesList() {
		if (!function_exists('get_editable_roles')) {
			require_once( ABSPATH . '/wp-admin/includes/user.php' );
		}
		return get_editable_roles();
	}
	public function getAvailableUserRolesSelect() {
		$rolesList = $this->getRolesList();
		$rolesListForSelect = array();
		foreach ($rolesList as $rKey => $rData) {
			$rolesListForSelect[ $rKey ] = $rData['name'];
		}
		return $rolesListForSelect;
	}
	public function getAll() {
		if (empty($this->_options)) {
			$defSendmailPath = @ini_get('sendmail_path');
			if ( empty($defSendmailPath) && !stristr($defSendmailPath, 'sendmail') ) {
				$defSendmailPath = '/usr/sbin/sendmail';
			}
			$this->_options = DispatcherPyt::applyFilters('optionsDefine', array(
				'general' => array(
					'label' => esc_html__('General', 'publish-your-table'),
					'opts' => array(
						//'table_search' => array('label' => esc_html__('Include to Global Search', 'publish-your-table'), 'desc' => esc_html__('Enable this option if you want to include the tables data to global site search.', 'publish-your-table'), 'def' => '0', 'html' => 'checkboxToggle'),
						//'disable_wp_footer_fix' => array('label' => esc_html__('Disable WP Footer Fix', 'publish-your-table'), 'desc' => esc_html__('Standard WP theme must call the wp_footer() function in site footer (see Theme Development). But sometimes it is not happen in custom themes. In this case our plugin will call its function forced, because it needs it for correct work as the many other plugins. But if you have the problems in your site because of this feature - just enable this option to disable force call of wp_footer() function by our plugin.', 'publish-your-table'), 'def' => '0', 'html' => 'checkboxToggle'),
						'builder_pagination' => array('label' => esc_html__('Pagination in Builder', 'publish-your-table'), 'desc' => esc_html__('Pagination in Builder.', 'publish-your-table'), 'def' => '0', 'html' => 'checkboxToggle'),
						'builder_rows' => array('label' => esc_html__('rows per page', 'publish-your-table'), 'desc' => '', 'def' => '500', 'html' => 'input', 'classes' => 'pubydoc-flat-input pubydoc-number pubydoc-width60'),
						'builder_ssp' => array('label' => esc_html__('Server-side Processing', 'publish-your-table'), 'desc' => esc_html__('This option is recommended for a large tables that cannot be processed in conventional way. The table will be sequentially loaded by ajax on a per page basis.', 'publish-your-table'), 'def' => '0', 'html' => 'checkboxToggle'),
						'load_table_step' => array('label' => esc_html__('Rows Count per Request', 'publish-your-table'), 'desc' => esc_html__('Set the count of table rows, which will be put into the one saving request. If your table has more rows - as many requests will be sent as need to completely save all table data. It can be useful if you have a large table and can not improuve your server settings to save the table per single request. If you do not have problems with saving of tables it is better to left the default value - 400.', 'publish-your-table'), 'def' => '400', 'html' => 'input', 'classes' => 'pubydoc-flat-input pubydoc-number pubydoc-width60'),
						//'tables_page_search_enabled' =>  array('label' => esc_html__('Global Page Search Form', 'publish-your-table'), 'desc' => esc_html__('Use this form to make global search by all tables on page.', 'publish-your-table'), 'def' => '0', 'html' => 'checkboxToggle', 'pro' => true),
						//'tables_page_search_shortcode' => array('label' => '', 'desc' => '', 'def' => '[pyt-tables-page-search]', 'html' => 'inputShortcode'),
						//'load_by_ajax_enabled' => array('label' => esc_html__('Load by AJAX enable', 'publish-your-table'), 'desc' => esc_html__('Allow loading posts/pages with tables through AJAX. Important: the assets-shortcode must be specified in the parent page.', 'publish-your-table'), 'def' => '0', 'html' => 'checkboxToggle', 'pro' => true),
						//'load_by_ajax_shortcode' => array('label' => '', 'desc' => '', 'def' => '[pyt-tables-assets]', 'html' => 'inputShortcode'),
						//'access_roles' => array('label' => esc_html__('Roles', 'publish-your-table'), 'desc' => esc_html__('Set the users roles, to add permission to use the plugin. The Administrator role has set by default.', 'publish-your-table'), 'def' => 'administrator', 'html' => 'selectlist', 'options' => array($this, 'getAvailableUserRolesSelect'), 'classes' => 'pubydoc-width300', 'pro' => true),
					),
					'parents' => array(
						'builder_rows' => 'builder_pagination',
						'builder_ssp' => 'builder_pagination',
						//'load_by_ajax_shortcode' => 'load_by_ajax_enabled',
						//'tables_page_search_shortcode' => 'tables_page_search_enabled',
					),
				),
			));
			if (!FramePyt::_()->isPro()) {
				foreach ($this->_options as $catKey => $cData) {
					foreach ($cData['opts'] as $optKey => $opt) {
						$this->_optionsToCategoires[ $optKey ] = $catKey;
						if (isset($opt['pro'])) {
							$this->_options[ $catKey ]['opts'][ $optKey ]['pro_link'] = UriPyt::generatePluginLink('utm_source=plugin&utm_medium=' . $optKey);
						}
					}
				}
			}
			$this->getModel()->fillInValues( $this->_options );
		}
		return $this->_options;
	}
	public function getFullCat( $cat ) {
		$this->getAll();
		return isset($this->_options[ $cat ]) ? $this->_options[ $cat ] : false;
	}
	public function getCatOpts( $cat ) {
		$opts = $this->getFullCat($cat);
		return $opts ? $opts['opts'] : false;
	}
}
