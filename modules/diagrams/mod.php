<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class DiagramsPyt extends ModulePyt {

	public $diagramPreviewPath = '/pyt-diagrams';
		
	public function init() {
		parent::init();
		add_shortcode(PYT_SHORTCODE_CHART, array($this, 'doShortcode'));
		DispatcherPyt::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		//DispatcherPyt::addFilter('getTabAllDiagrams', array($this, 'getTabAllDiagrams'), 10, 2);
		DispatcherPyt::addAction('diagramsIncludeTpl', array($this, 'includeTemplate'), 10, 2);
		DispatcherPyt::addAction('updateTableData', array($this, 'resetDiagramStatus'), 10, 2);
		DispatcherPyt::addFilter('addTableSettings', array($this, 'addTableSettings'), 10, 2);

	}
	public function addAdminTab( $tabs ) {
		$icon = FramePyt::_()->isPro() ? '' : ' pubydoc-show-pro';
		$tabs['diagrams'] = array(
			'label' => esc_html__('Diagrams', 'publish-your-table'), 'callback' => array($this, 'getTabAllDiagrams'), 'fa_icon' => 'pyt-diagram' . $icon, 'sort_order' => 21, 'add_bread' => $this->getCode(),
		);
		return $tabs;
	}
	public function getTabAllDiagrams() {
		return $this->getView()->getTabAllDiagrams();
	}
	public function loadAssets() {
		framePps::_()->addScript('frontend.login', $this->getModPath(). 'js/frontend.login.js', array('jquery'));
	}
	
	public function includeTemplate( $tpl, $params = array() ) {
		$this->getView()->includeTemplate($tpl, $params);
	}
	public function doShortcode( $params ) {
		return $this->getView()->renderShortcode($params);
	}
	public function genDiagramView( $id ) {
		return $id . '-' . mt_rand(1, 99999);
	}
	public function resetDiagramStatus( $tableId ) {
		return $this->getModel()->resetDiagramsData($tableId);
	}
	public function addTableSettings( $settings, $tableId ) {
		if (isset($settings['options']) 
			&& UtilsPyt::getArrayValue($settings['options'], 'paging') == '1'
			&& UtilsPyt::getArrayValue($settings['options'], 'ssp') == '1') {
				if (FramePyt::_()->getModule('tablespro')->getModel('cellspro')->existsTableDiagramShortcode($tableId)) {
					$settings['options']['withDiagrams'] = '1';
				}
		}
		return $settings;
	}
}
