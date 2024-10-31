<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class DiagramsViewPyt extends ViewPyt {
	private $_diagramCSS = [];

	public function includeTemplate( $tpl, $params ) {
		foreach ($params as $param => $data) {
			$this->assign($param, $data);
		}
		return parent::display($tpl);
	}
	public function getTabAllDiagrams() {

		$assets = AssetsPyt::_();
		$assets->loadCoreJs();
		$assets->loadDataTables(array('buttons', 'responsive'));
		$assets->loadColorPicker();
		$assets->loadAdminEndCss();
		$assets->loadSlimscroll();

		$frame = FramePyt::_();
		$tablesModule = $frame->getModule('tables');
		$tablesPath = $tablesModule->getModPath() . 'assets/';
		$frame->addStyle('pyt-tables', $tablesPath . 'css/admin.tables.css');

		$path = $this->getModule()->getModPath() . 'assets/';
		$frame->addScript('pyt-diagrams-plotly', $path . 'js/plotly.min.js');

		$frame->addScript('pyt-diagrams', $path . 'js/pyt-diagram.js');
		$frame->addStyle('pyt-diagrams', $path . 'css/admin.diagrams.css');
		$frame->addScript('pyt-diagrams-edit', $path . 'js/admin.diagrams.edit.js');
		$frame->addScript('pyt-diagrams-list', $path . 'js/admin.diagrams.list.js');

		$newUrl = $frame->getModule('adminmenu')->getTabUrl('diagrams-new');
		$diagramModel = $this->getModule()->getModel();

		$settings = array(
			'emptyTable' => esc_html__('You have no Diagrams for now.', 'publish-your-table') . ' <a href="' . $newUrl . '">' . esc_html__('Create', 'publish-your-table') . '</a> ' . esc_html__('your first Diagram', 'publish-your-table') . '!',
			'lengthMenu' => esc_html__('Show', 'publish-your-table'),
			'info' => esc_html__('Showing', 'publish-your-table'),
			'btn-delete' => esc_html__('Delete selected', 'publish-your-table'),
			'btn-export' => esc_html__('Export', 'publish-your-table'),
			'btn-import' => esc_html__('Import', 'publish-your-table'),
			'btn-add' => esc_html__('Add diagram', 'publish-your-table'),
			'remove-confirm' => esc_html__('Are you sure want to remove %s diagram(s)?', 'publish-your-table'),
		);
		
		$this->assign('settings', $settings);
		$this->assign('types', $diagramModel->getFieldLists('type'));
		$this->assign('tables', $tablesModule->getModel()->getTablesList(true));

		return parent::getContent('diagramsAllDiagrams');
	}

	public function renderDiagramsAssets() {
		$frame = FramePyt::_();
		$path = $this->getModule()->getModPath() . 'assets/';
		$frame->addScript('pyt-diagrams-plotly', $path . 'js/plotly.min.js');
		$frame->addScript('pyt-diagrams', $path . 'js/pyt-diagram.js');
	}

	public function renderShortcode( $attributes ) {
		$id = UtilsPyt::getArrayValue($attributes, 'id', 0, 1);
		if (empty($id)) {
			return false;
		}

		$diagram = $this->getModule()->getModel()->getDiagramData($id);
		if (!$diagram) {
			return sprintf(__('The diagram with ID %d not exists.', 'publish-your-table'), $id);
		}

		$assets = AssetsPyt::_();
		$assets->loadCoreJs();
		$this->renderDiagramsAssets();

		$options = UtilsPyt::getArrayValue($diagram, 'options', array(), 2);
		$needed = array();
		$params = array('switch_rows_cols', 'label_first_col', 'header_first_row', 'show_values', 'bar_orientation');
		foreach ($params as $key) {
			$needed[$key] = UtilsPyt::getArrayValue($options, $key);
		}

		$viewId = $this->getModule()->genDiagramView($id);
		$tableId = UtilsPyt::getArrayValue($diagram, 'table_id', 0, 1);
		$config = UtilsPyt::getArrayValue($diagram, 'config');
		$isDynamic = UtilsPyt::getArrayValue($attributes, 'dynamic', 0, 1);
		$needRefresh = 0;
		$rawData = array();

		if (UtilsPyt::getArrayValue($diagram, 'status', 0, 1) == 0 && UtilsPyt::getArrayValue($options, 'refresh_dynamically') == '1') {
			$rawData = FramePyt::_()->getModule('tablespro')->getRangeData($tableId, UtilsPyt::getArrayValue($config, 'range', array(), 2), false);
			if ($rawData && is_array($config['data'])) {
				$config['data'] = [];
				$needRefresh = 1;
			}
		}
		$loader = '';
		if (!is_feed() && UtilsPyt::getArrayValue($options, 'show_loader', false)) {
			$tableSettings = FramePyt::_()->getModule('tables')->getModel()->getTableData($tableId, 'options');
			if (!UtilsPyt::getArrayValue($tableSettings, 'loader_disable', false)) {
				$assets->loadLoaders();

				$iconName = UtilsPyt::getArrayValue($tableSettings, 'loader_name', 'spinner');
				$iconCount = UtilsPyt::getArrayValue($tableSettings, 'loader_count', 0, 1);
				$iconColor = UtilsPyt::getArrayValue($tableSettings, 'loader_color', '#000000') . '!important';

				if ($iconName == 'spinner') {
					$loader = '<div class="pyt-diagram-loader pubydoc-table-loader spinner" style="background-color:' . $iconColor . '"></div>';
				} else {
					$loader = '<div class="pyt-diagram-loader pubydoc-table-loader la-' . esc_attr($iconName) . ' la-2x" style="color:' . $iconColor . '">' . str_repeat('<div></div>', $iconCount) . '</div>';
				}
			}
		}		
		
		$this->assign('id', $id);
		$this->assign('view_id', $viewId);
		$this->assign('table_id', $tableId);
		$this->assign('type', UtilsPyt::getArrayValue($diagram, 'type', 0, 1));
		$this->assign('dynamic', $isDynamic);
		$this->assign('refresh', $needRefresh);
		$this->assign('highlighting', UtilsPyt::getArrayValue($options, 'highlighting_hover', 0, 1) == '1' ? 1 : 0);
		$this->assign('options', $needed);
		$this->assign('config', $config);
		$this->assign('raw', $rawData);
		$this->assign('loader', $loader);
				
		return parent::getContent('diagramShortcode');
	}
}
