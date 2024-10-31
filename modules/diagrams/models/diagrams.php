<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class DiagramsModelPyt extends ModelPyt {
	//status - 1-static, 0-need-redraw (if table changed or for external table)
	public function __construct() {
		$this->_setTbl('diagrams');
		$lists = array(
			'type' => array(
				0 => __('Line', 'publish-your-table'),
				1 => __('Area', 'publish-your-table'),
				2 => __('Bar', 'publish-your-table'),
				3 => __('Pie', 'publish-your-table'),
				4 => __('Bubble', 'publish-your-table'),
				5 => __('Column', 'publish-your-table'),
			),
		);
		$this->setFieldLists($lists);
	}

	public function saveDiagram( $id, $status, $data = array(), $config = array() ) {
		$isNew = empty($id) || !is_integer($id);

		$diagram = array(
			'title' => UtilsPyt::getArrayValue($data, 'title', 'diagram ' . gmdate('Y-m-d-h-i-s')),
			'type' => UtilsPyt::getArrayValue($data, 'type', 0, 1, $this->getFieldKeys('type')),
			'table_id' => UtilsPyt::getArrayValue($data, 'table_id', 0, 1),
			'table_range' => UtilsPyt::getArrayValue($data, 'table_range'),
			'status' => $status == 1 ? 1 : 0,
			'options' => UtilsPyt::jsonEncode(UtilsPyt::getArrayValue($data, 'options', array(), 2)),
			'config' => is_array($config) ? UtilsPyt::jsonEncode($config) : null
		);

		if ($isNew) {
			$id = $this->insert( $diagram );
		} else {
			$this->updateById($diagram, $id);
		}
		return $id;
	}

	public function prepareTablesList( $diagrams ) {
		$rows = array();
		$editUrl = FramePyt::_()->getModule('adminmenu')->getTabUrl('tables-edit');
		$tablesModel = FramePyt::_()->getModule('tables')->getModel();
		$tables = array();

		$btnDelete = __('Are you sure to delete this?', 'publish-your-table') . '<div class="buttons"><button>' . __('Cancel', 'publish-your-table') . '</button><button class="pyt-delete">' . __('Confirm', 'publish-your-table') . '</button></div>';
		$uploadPath = wp_upload_dir();
		$previewUrl = $uploadPath['baseurl'] . FramePyt::_()->getModule('diagrams')->diagramPreviewPath . '/';
		$previewDir = $uploadPath['basedir'] . FramePyt::_()->getModule('diagrams')->diagramPreviewPath . '/';

		foreach ($diagrams as $diagram) {

			$id = $diagram['id'];
			$tableId = $diagram['table_id'];
			if (!empty($diagram['options'])) {
				$diagram['options'] = UtilsPyt::jsonDecode($diagram['options']);
			}
			if (!empty($diagram['config'])) {
				$diagram['config'] = UtilsPyt::jsonDecode($diagram['config']);
			}
			if (!isset($tables[$tableId])) {
				$table = $tablesModel->getById($tableId);
				$tables[$tableId] = $table ? $table['title'] : '???';
			}
			
			$rows[] = array(
				'<input type="checkbox" class="pytCheckOne" data-id="' . $id . '" data-settings="' . htmlentities(UtilsPyt::jsonEncode($diagram)) . '">', 
				$id, 
				'<a href="#" class="pyt-edit-link">' . $diagram['title'] . '</a>',
				$this->getFieldLists('type', $diagram['type']),
				'<a target="_blank" href="' . esc_url($editUrl . '&id=' . $tableId) .'">' . $tables[$tableId] . '</a>',
				$diagram['table_range'],
				'<input type="text" class="pubydoc-shortcode pubydoc-flat-input" readonly value="[pyt-diagram id=' . $id . ']">',
				( file_exists($previewDir . $id . '.png') ? '<img src="' . $previewUrl . $id . '.png" class="pyt-diagrams-preview">' : '' )
			);
		}
		return $rows;
	}
	public function getDiagramData( $id ) {
		$diagram = $this->getById($id);
		if (!$diagram) {
			FramePyt::_()->pushError(esc_html__('Diagram not found.', 'publish-your-table'));
			return false;
		}
		if (!empty($diagram['options'])) {
			$diagram['options'] = UtilsPyt::jsonDecode($diagram['options']);
		}
		if (!empty($diagram['config'])) {
			$diagram['config'] = UtilsPyt::jsonDecode($diagram['config']);
		}
        return $diagram;
	}

	public function resetDiagramsData( $tableId ) {
		$this->update(array('status' => 0), array('table_id' => $tableId, 'status' => 1));
		return true;
	}

	public function cloneDiagram( $id ) {
		$diagram = $this->getById($id);
		if (!$diagram) {
			FramePyt::_()->pushError(esc_html__('Diagram not found.', 'publish-your-table'));
			return false;
		}
		unset($diagram['id']);
		$diagram['title'] .= '-clone';
		return $this->insert( $diagram );
	}
}
