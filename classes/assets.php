<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class AssetsPyt {
	protected $_styles = array();
	private $_cdnUrl = '';

	public function init() {
		$this->getCdnUrl();
		if (is_admin()) {
			$isAdminPlugOptsPage = FramePyt::_()->isAdminPlugOptsPage();
			if ($isAdminPlugOptsPage) {
				$this->loadAdminCoreJs();
				$this->loadCoreCss();
				$this->loadBootstrap();
				$this->loadFontAwesome();
				$this->loadJqueryUi();
				FramePyt::_()->addScript('pyt-admin-options', PYT_JS_PATH . 'admin.options.js', array(), false, true);
				add_action('admin_enqueue_scripts', array($this, 'loadMediaScripts'));
				add_action('init', array($this, 'connectAdditionalAdminAssets'));
				// Some common styles - that need to be on all admin pages - be careful with them
				FramePyt::_()->addStyle('pubydoc-for-all-admin-' . PYT_CODE, PYT_CSS_PATH . 'pubydoc-for-all-admin.css');
			}
		}
	}
	public static function getInstance() {
		static $instance;
		if (!$instance) {
			$instance = new AssetsPyt();
		}
		return $instance;
	}
	public static function _() {
		return self::getInstance();
	}
	public function getCdnUrl() {
		if (empty($this->_cdnUrl)) {
			if ((int) FramePyt::_()->getModule('options')->get('use_local_cdn')) {
				$uploadsDir = wp_upload_dir( null, false );
				$this->_cdnUrl = $uploadsDir['baseurl'] . '/' . PYT_CODE . '/';
				if (UriPyt::isHttps()) {
					$this->_cdnUrl = str_replace('http://', 'https://', $this->_cdnUrl);
				}
			} else {
				$this->_cdnUrl = ( UriPyt::isHttps() ? 'https' : 'http' ) . '://pubydoc-14700.kxcdn.com/';
			}
		}
		return $this->_cdnUrl;
	}

	public function connectAdditionalAdminAssets() {
		if (is_rtl()) {
			FramePyt::_()->addStyle('pyt-style-rtl', PYT_CSS_PATH . 'style-rtl.css');
		}
	}
	public function loadMediaScripts() {
		if (function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		}
	}
	public function loadAdminCoreJs() {
		FramePyt::_()->addScript('jquery-ui-dialog');
		FramePyt::_()->addScript('jquery-ui-slider');
	}
	public function loadCoreJs() {
		static $loaded = false;
		if (!$loaded) {
			FramePyt::_()->addScript('jquery');
			FramePyt::_()->addScript('pyt-core', PYT_JS_PATH . 'core.js');
			FramePyt::_()->addScript('pyt-notify-js', PYT_JS_PATH . 'notify.js', array(), false, true);

			$ajaxurl = admin_url('admin-ajax.php');
			$jsData = array(
				'siteUrl' => PYT_SITE_URL,
				'imgPath' => PYT_IMG_PATH,
				'cssPath' => PYT_CSS_PATH,
				'loader' => PYT_LOADER_IMG,
				'close'	=> PYT_IMG_PATH . 'cross.gif',
				'ajaxurl' => $ajaxurl,
				'PYT_CODE' => PYT_CODE,
				'jsPath' => PYT_JS_PATH,
				'libPath' => PYT_LIB_PATH,
				'pytNonce' => wp_create_nonce('pyt-nonce')
			);
			if (is_admin()) {
				$jsData['isPro'] = FramePyt::_()->isPro();
			}
			$jsData = DispatcherPyt::applyFilters('jsInitVariables', $jsData);
			FramePyt::_()->addJSVar('pyt-core', 'PYT_DATA', $jsData);
			$this->loadTooltipster();
			$loaded = true;
		}
	}
	public function loadTooltipster() {
		$path = PYT_LIB_PATH . 'tooltipster/';
		FramePyt::_()->addScript('tooltipster', $path . 'jquery.tooltipster.min.js');
		FramePyt::_()->addStyle('tooltipster', $path . 'tooltipster.css');
	}
	public function loadSlimscroll() {
		FramePyt::_()->addScript('jquery.slimscroll', PYT_JS_PATH . 'slimscroll.min.js');
	}
	public function loadLoaders() {
		FramePyt::_()->addStyle('pyt-loaders', PYT_CSS_PATH . 'loaders.css');
	}
	public function loadCodemirror() {
		$path = PYT_LIB_PATH . 'codemirror/';
		FramePyt::_()->addStyle('pyt-codemirror', $path . 'codemirror.css');
		FramePyt::_()->addStyle('pyt-codemirror-addon-hint', $path . 'addon/hint/show-hint.css');
		FramePyt::_()->addScript('pyt-codemirror', $path . 'codemirror.js');
		FramePyt::_()->addScript('pyt-codemirror-addon-show-hint', $path . 'addon/hint/show-hint.js');
		FramePyt::_()->addScript('pyt-codemirror-mode-javascript', $path . 'mode/javascript/javascript.js');
		FramePyt::_()->addScript('pyt-codemirror-mode-css', $path . 'mode/css/css.js');
	}
	public function loadCoreCss() {
		$this->_styles = array(
			'pyt-style'			=> array('path' => PYT_CSS_PATH . 'style.css', 'for' => 'admin'),
			'pyt-pubydoc-ui'	=> array('path' => PYT_CSS_PATH . 'pubydoc-ui.css', 'for' => 'admin'),
			'dashicons'			=> array('for' => 'admin'),
			'bootstrap-alerts'	=> array('path' => PYT_CSS_PATH . 'bootstrap-alerts.css', 'for' => 'admin'),
		);
		foreach ($this->_styles as $s => $sInfo) {
			if (!empty($sInfo['path'])) {
				FramePyt::_()->addStyle($s, $sInfo['path']);
			} else {
				FramePyt::_()->addStyle($s);
			}
		}
		$this->loadFontAwesome();
	}
	public function loadAdminEndCss() {
		FramePyt::_()->addStyle('pyt-admin-options', PYT_CSS_PATH . 'admin.options.css');
	}
	public function loadColorPicker() {
		$path = PYT_LIB_PATH . 'colorpicker/';
		FramePyt::_()->addScript('pyt-colorpicker', $path . 'colorpicker.js');
		FramePyt::_()->addStyle('pyt-colorpicker', $path . 'colorpicker.css');
	}
	public function loadJqueryUi() {
		static $loaded = false;
		if (!$loaded) {
			//Includes: widget.js, position.js, data.js, disable-selection.js, effect.js, effects/effect-blind.js, effects/effect-bounce.js, effects/effect-clip.js, effects/effect-drop.js, effects/effect-explode.js, effects/effect-fade.js, effects/effect-fold.js, effects/effect-highlight.js, effects/effect-puff.js, effects/effect-pulsate.js, effects/effect-scale.js, effects/effect-shake.js, effects/effect-size.js, effects/effect-slide.js, effects/effect-transfer.js, focusable.js, form-reset-mixin.js, jquery-1-7.js, keycode.js, labels.js, scroll-parent.js, tabbable.js, unique-id.js, widgets/accordion.js, widgets/autocomplete.js, widgets/button.js, widgets/checkboxradio.js, widgets/controlgroup.js, widgets/datepicker.js, widgets/dialog.js, widgets/draggable.js, widgets/droppable.js, widgets/menu.js, widgets/mouse.js, widgets/progressbar.js, widgets/resizable.js, widgets/selectable.js, widgets/selectmenu.js, widgets/slider.js, widgets/sortable.js, widgets/spinner.js, widgets/tabs.js, widgets/tooltip.js
			FramePyt::_()->addScript('jquery-ui', PYT_JS_PATH . 'jquery-ui.min.js');
			FramePyt::_()->addStyle('jquery-ui', PYT_CSS_PATH . 'jquery-ui.min.css');
			$loaded = true;
		}
	}
	public function loadDataTables( $extensions = array(), $jqueryui = false ) {
		$frame = FramePyt::_();
		$path = PYT_LIB_PATH . 'datatables/';
		$frame->addScript('pyt-dt-js', $path . 'js/jquery.dataTables.min.js');
		if ($jqueryui) {
			$frame->addScript('pyt-dt-jq-js', $path . 'js/dataTables.jqueryui.min.js');
			$frame->addStyle('pyt-dt-css', $path . 'css/dataTables.jqueryui.min.css');
		} else {
			$frame->addStyle('pyt-dt-css', $path . 'css/jquery.dataTables.min.css');
		}
		foreach ($extensions as $ext) {
			switch ($ext) {
				case 'print':
					$frame->addScript('pyt-dt-print', $path . 'js/buttons.print.min.js');
					break;
				case 'html5':
					$frame->addScript('pyt-dt-jszip', PYT_LIB_PATH . 'jszip/jszip.min.js');
					$frame->addScript('pyt-dt-pdfmake', PYT_LIB_PATH . 'pdfmake/pdfmake.min.js');
					$frame->addScript('pyt-dt-vfs_fonts', PYT_LIB_PATH . 'pdfmake/vfs_fonts.js');
					$frame->addScript('pyt-dt-html', $path . 'js/buttons.html5.min.js');
					break;
				
				default:
					$frame->addScript('pyt-dt-' . $ext, $path . 'js/dataTables.' . $ext . '.min.js');
					if ($jqueryui) {
						$frame->addScript('pyt-dt-jq-' . $ext, $path . 'js/' . $ext . '.jqueryui.min.js');
					}				
					if ($jqueryui) {
						$frame->addStyle('pyt-dt-' . $ext, $path . 'css/' . $ext . '.jqueryui.min.css');
					} else {
						$frame->addStyle('pyt-dt-' . $ext, $path . 'css/' . $ext . '.dataTables.min.css');
					}
					break;
			}
		}
	}
	public function loadFontAwesome() {
		FramePyt::_()->addStyle('pyt-font-awesome', PYT_CSS_PATH . 'font-awesome.min.css');
	}
	public function loadChosenSelects() {
		$path = PYT_LIB_PATH . 'chosen/';
		FramePyt::_()->addStyle('pyt-jquery-chosen', $path . 'chosen.min.css');
		FramePyt::_()->addScript('pyt-jquery-chosen', $path . 'chosen.jquery.min.js');
	}
	public function loadDatePicker() {
		FramePyt::_()->addScript('jquery-ui-datepicker');
	}
	public function loadSortable() {
		static $loaded = false;
		if (!$loaded) {
			FramePyt::_()->addScript('jquery-ui-core');
			FramePyt::_()->addScript('jquery-ui-widget');
			FramePyt::_()->addScript('jquery-ui-mouse');

			FramePyt::_()->addScript('jquery-ui-draggable');
			FramePyt::_()->addScript('jquery-ui-sortable');
			$loaded = true;
		}
	}
	public function loadBootstrap() {
		static $loaded = false;
		if (!$loaded) {
			FramePyt::_()->addStyle('bootstrap.min', PYT_CSS_PATH . 'bootstrap.min.css');
			$loaded = true;
		}
	}
}
