<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class AdminmenuPyt extends ModulePyt {
	private $_tabs = array();
	protected $_mainSlug = 'pyt-tables';
	private $_mainCap = 'manage_options';

	public function init() {
		parent::init();
		add_action('admin_menu', array($this, 'initMenu'), 9);
		$plugName = plugin_basename(PYT_DIR . PYT_MAIN_FILE);
		add_filter('plugin_action_links_' . $plugName, array($this, 'addSettingsLinkForPlug') );
		//add_action('admin_footer', array($this, 'displayAdminFooter'), 9);
		add_action('admin_notices', array($this, 'checkAdminPromoNotices'));
	}
	public function addSettingsLinkForPlug( $links ) {
		$mainLink = 'https://pubydoc.com/';
		/* translators: %s: plugin name */
		$twitterStatus = sprintf(esc_html__('Cool WordPress plugins from pubydoc.com developers. I tried %s - and this was what I need! #pubydoc.com', 'publish-your-table'), PYT_WP_PLUGIN_NAME);
		array_unshift($links, '<a href="' . esc_url($this->getMainLink()) . '">' . esc_html__('Settings', 'publish-your-table') . '</a>');
		array_push($links, '<a title="' . esc_attr__('More plugins for your WordPress site here!', 'publish-your-table') . '" href="' . esc_url($mainLink) . '" target="_blank">pubydoc.com</a>');
		return $links;
	}
	public function initMenu() {
		$mainCap = $this->getMainCap();
		$mainSlug = DispatcherPyt::applyFilters('adminMenuMainSlug', $this->_mainSlug);
		$mainMenuPageOptions = array(
			'page_title' => PYT_WP_PLUGIN_NAME, 
			'menu_title' => 'PubyDoc', 
			'capability' => $mainCap,
			'menu_slug' => $mainSlug,
			'function' => array($this, 'getAdminPage'));
		$mainMenuPageOptions = DispatcherPyt::applyFilters('adminMenuMainOption', $mainMenuPageOptions);

		add_menu_page($mainMenuPageOptions['page_title'], $mainMenuPageOptions['menu_title'], $mainMenuPageOptions['capability'], $mainMenuPageOptions['menu_slug'], $mainMenuPageOptions['function'], 'dashicons-list-view');
		$tabs = $this->getTabs();
		$subMenus = array();
		foreach ($tabs as $tKey => $tab) {
			if ('main_page' == $tKey) {
				continue;	// Top level menu item - is main page, avoid place it 2 times
			}
			if ( ( isset($tab['hidden']) && $tab['hidden'] )
				|| ( isset($tab['hidden_for_main']) && $tab['hidden_for_main'] )	// Hidden for WP main
				|| ( isset($tab['is_main']) && $tab['is_main'] ) ) {
				continue;
			}
			$slug = 'admin.php?page=' . $mainSlug . '&tab=' . $tKey;
			if ('tables' == $tKey) {
				array_unshift($subMenus, array('title' => esc_html__('All Tables', 'publish-your-table'), 'capability' => $mainCap, 'menu_slug' => $slug, 'function' => ''));
			} else {
				$subMenus[] = array('title' => $tab['label'], 'capability' => $mainCap, 'menu_slug' => $slug, 'function' => '');
			}
		}
		$subMenus = DispatcherPyt::applyFilters('adminMenuOptions', $subMenus);
		foreach ($subMenus as $opt) {
			add_submenu_page($mainSlug, $opt['title'], $opt['title'], $opt['capability'], $opt['menu_slug'], $opt['function']);
		}
		//remove duplicated WP menu item
		remove_submenu_page($mainSlug, $mainSlug);
	}
	public function getMainLink() {
		return UriPyt::_(array('baseUrl' => admin_url('admin.php'), 'page' => $this->getMainSlug()));
	}
	public function getMainSlug() {
		return $this->_mainSlug;
	}
	public function getMainCap() {
		return DispatcherPyt::applyFilters('adminMenuAccessCap', $this->_mainCap);
	}
	public function getPluginLinkPro() {
		return 'https://pubydoc.com/plugins/pyt/' ;
	}
	public function generateMainLink( $params = '' ) {
		$mainLink = $this->getMainLink();
		if (!empty($params)) {
			return $mainLink . ( strpos($mainLink , '?') ? '&' : '?' ) . $params;
		}
		return $mainLink;
	}
	public function getAdminPage() {
		if (!InstallerPyt::isUsed()) {
			InstallerPyt::setUsed();
		}
		return $this->getView()->getAdminPage();
	}
	public function checkAdminPromoNotices() {
		return;
		if (!FramePyt::_()->isAdminPlugOptsPage()) {
			return;
		}
		$notices = array();
		$moduleOptions = FramePyt::_()->getModule('options');
		// Start usage
		$startUsage = (int) $moduleOptions->get('start_usage');
		$currTime = time();
		$day = 24 * 3600;
		if ($startUsage) {	// Already saved
			/* translators: %s: label */
			$rateMsg = '<h3>' . esc_html(sprintf(__('Hey, I noticed you just use %s over a week – that’s awesome!', 'publish-your-table'), PYT_WP_PLUGIN_NAME)) . '</h3><p>' .
				esc_html__('Could you please do me a BIG favor and give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation.', 'publish-your-table') . '</p>';
			$rateMsg .= '<p><a href="https://wordpress.org/support/plugin/publish-your-table/reviews/?rate=5#new-post" target="_blank" class="button button-primary" data-statistic-code="done">' .
				esc_html__('Ok, you deserve it', 'publish-your-table') . '</a>
				<a href="#" class="button" data-statistic-code="later">' . esc_html__('Nope, maybe later', 'publish-your-table') . '</a>
				<a href="#" class="button" data-statistic-code="hide">' . esc_html__('I already did', 'publish-your-table') . '</a></p>';
			$notices = array(
				'rate_msg' => array('html' => $rateMsg, 'show_after' => 7 * $day),
			);
			foreach ($notices as $nKey => $n) {
				if ($currTime - $startUsage <= $n['show_after']) {
					unset($notices[ $nKey ]);
					continue;
				}
				$done = (int) $moduleOptions->get('done_' . $nKey);
				if ($done) {
					unset($notices[ $nKey ]);
					continue;
				}
				$hide = (int) $moduleOptions->get('hide_' . $nKey);
				if ($hide) {
					unset($notices[ $nKey ]);
					continue;
				}
				$later = (int) $moduleOptions->get('later_' . $nKey);
				if ( $later && ( $currTime - $later ) <= 2 * $day ) {	// remember each 2 days
					unset($notices[ $nKey ]);
					continue;
				}
			}
		} else {
			$moduleOptions->getModel()->save('start_usage', $currTime);
		}
		if (!empty($notices)) {
			$html = '';
			foreach ($notices as $nKey => $n) {
				$html .= '<div class="updated notice is-dismissible pubydoc-admin-notice" data-code="' . $nKey . '">' . $n['html'] . '</div>';
			}
			HtmlPyt::echoEscapedHtml($html);
		}
	}
	public function displayAdminFooter() {
		if (FramePyt::_()->isAdminPlugPage()) {
			$this->getView()->displayAdminFooter();
		}
	}
	
	public function getTabs() {
		if (empty($this->_tabs)) {
			$this->_tabs = DispatcherPyt::applyFilters('mainAdminTabs', array(
				// example: 'main_page' => array('label' => esc_html__('Main Page', 'publish-your-table'), 'callback' => array($this, 'getTabContent'), 'wp_icon' => 'dashicons-admin-home', 'sort_order' => 0),
			));
			foreach ($this->_tabs as $tabKey => $tab) {
				if (!isset($this->_tabs[ $tabKey ]['url'])) {
					$this->_tabs[ $tabKey ]['url'] = $this->getTabUrl( $tabKey );
				}
			}
			uasort($this->_tabs, array($this, 'sortTabsClb'));
		}
		return $this->_tabs;
	}
	public function sortTabsClb( $a, $b ) {
		if (isset($a['sort_order']) && isset($b['sort_order'])) {
			if ($a['sort_order'] > $b['sort_order']) {
				return 1;
			}
			if ($a['sort_order'] < $b['sort_order']) {
				return -1;
			}
		}
		return 0;
	}
	public function getTab( $tabKey ) {
		$this->getTabs();
		return isset($this->_tabs[ $tabKey ]) ? $this->_tabs[ $tabKey ] : false;
	}
	public function getTabContent() {
		return $this->getView()->getTabContent();
	}
	public function getActiveTab() {
		$reqTab = sanitize_text_field(ReqPyt::getVar('tab'));
		return empty($reqTab) ? 'tables' : $reqTab;
	}
	public function getTabUrl( $tab = '' ) {
		static $mainUrl;
		if (empty($mainUrl)) {
			$mainUrl = FramePyt::_()->getModule('adminmenu')->getMainLink();
		}
		return empty($tab) ? $mainUrl : $mainUrl . '&tab=' . $tab;
	}

	public function getEditLink( $id, $code ) {
		$link = $this->getTabUrl( $code . '-edit' ) . '&id=' . $id;
		return $link;
	}

}
