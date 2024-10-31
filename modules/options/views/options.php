<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class OptionsViewPyt extends ViewPyt {
	private $_news = array();
		
	public function sortOptsSet( $a, $b ) {
		if ($a['weight'] > $b['weight']) {
			return -1;
		}
		if ($a['weight'] < $b['weight']) {
			return 1;
		}
		return 0;
	}

	public function getSettingsTabContent() {
		$frame = FramePyt::_();
		$frame->addScript('pyt-admin-settings', $this->getModule()->getModPath() . 'js/admin.settings.js');
		$frame->addStyle('pyt-admin-settings', $this->getModule()->getModPath() . 'css/admin.settings.css');

		$assets = AssetsPyt::_();
		$assets->loadCoreJs();
		$assets->loadJqueryUi();
		$assets->loadAdminEndCss();
		$assets->loadChosenSelects();
		DispatcherPyt::doAction('addAssetsContent');
	
		$this->assign('options', $frame->getModule('options')->getAll());
		return parent::getContent('optionsSettingsTabContent');
	}

	public function getHtmlOptions( $optKey, $opt ) {
		$htmlOpts = array('attrs' => 'data-optkey="' . $optKey . '"');
		$htmlType = $opt['html'];
		if (in_array($htmlType, array('selectbox', 'selectlist')) && isset($opt['options'])) {
			if (is_callable($opt['options'])) {
				$htmlOpts['options'] = call_user_func( $opt['options'] );
			} elseif (is_array($opt['options'])) {
				$htmlOpts['options'] = $opt['options'];
			}
		}
		if (in_array($htmlType, array('checkbox', 'checkboxToggle'))) {
			$htmlOpts['value'] = 1;
			$htmlOpts['checked'] = $opt['value'];
		} else {
			$htmlOpts['value'] = $opt['value'];
		}
		if (!empty($opt['classes'])) {
			$htmlOpts['attrs'] .= ' class="' . $opt['classes'] . '"';
		}
		return $htmlOpts;
	}
}
