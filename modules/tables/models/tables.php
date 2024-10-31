<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TablesModelPyt extends ModelPyt {
	private $encodedFields = array('css', 'add_css', 'add_js');
	private $jsonedFields = array('options', 'source', 'columns', 'tuning');

	public function __construct() {
		$this->_setTbl('tables');
		$lists = array(
			'type' => array(
				0 => __('Manual', 'publish-your-table'),
				1 => __('Google Sheet', 'publish-your-table'),
				2 => __('WooCommerce', 'publish-your-table'),
				3 => __('DataBase', 'publish-your-table'),
			),
			'creation' => array(
				0 => __('None', 'publish-your-table'),
				1 => __('MS Excel', 'publish-your-table'),
				2 => __('CSV', 'publish-your-table'),
				3 => __('Google Sheet', 'publish-your-table'),
				4 => __('SQL', 'publish-your-table'),
			),
			'builder' => array(
				0 => __('Builder Standart', 'publish-your-table'),
				1 => __('Builder Extended', 'publish-your-table'),
			),
		);
		$this->setFieldLists($lists);
	}

	public function prepareTablesList( $tables ) {
		$rows = array();
		$editUrl = FramePyt::_()->getModule('adminmenu')->getTabUrl('tables-edit');
		$btnDelete = __('Are you sure to delete this?', 'publish-your-table') . '<div class="buttons"><button>' . __('Cancel', 'publish-your-table') . '</button><button class="pyt-delete">' . __('Confirm', 'publish-your-table') . '</button></div>';
		foreach ($tables as $table) {
			$id = $table['id'];
			$rows[] = array(
				'<input type="checkbox" class="pytCheckOne" data-id="' . $id . '">', 
				$id, 
				'<a href="' . esc_url($editUrl . '&id=' . $id) .'" class="pyt-edit-link">' . esc_html($table['title']) . '</a>',
				$this->getFieldLists('type', $table['type']),
				'<input type="text" class="pubydoc-shortcode pubydoc-flat-input" readonly value="[pyt-table id=' . $id . ']">',
				'<div class="pubydoc-list-actions" data-id="' . $id . '"><i class="fa fa-fw fa-sign-in pyt-edit pubydoc-tooltip" title="' . esc_attr__('Edit', 'publish-your-table') .
					'"></i><i class="fa fa-fw fa-gear pyt-options pubydoc-tooltip" title="' . esc_attr__('Settings', 'publish-your-table') .
					'"></i><i class="fa fa-fw fa-copy pyt-clone pubydoc-tooltip" title="' . esc_attr__('Clone', 'publish-your-table') .
					'"></i><i class="fa fa-fw fa-trash-o pubydoc-tooltip pyt-delete" title="' . esc_attr($btnDelete . '<div class="pytHidden">' . $id . '</div>') .
					'"></i></div>'
			);
		}
		return $rows;
	}

	public function getTablesList( $withType = false) {
		$tables = $this->setSelectFields('id, title, type')->setOrderBy('id')->setSortOrder('DESC')->getFromTbl();
		$list = array();
		foreach ($tables as $table) {
			$list[$table['id']] = $withType ? array('label' => $table['title'], 'attrs' => 'data-type="' . $table['type'] .'"') : $table['title'];
		}
		return $list;
	}
	public function getTableData( $id, $field = false, $raw = false ) {
		$table = $this->getById($id);
		if (!$table) {
			FramePyt::_()->pushError(esc_html__('Table not found.', 'publish-your-table'));
			return false;
		}
		if ($field) {
			if (!$raw ) {
				if (in_array($field, $this->encodedFields)) {
					return stripslashes(base64_decode($table[$field]));
				}
				if (in_array($field, $this->jsonedFields)) {
					return UtilsPyt::jsonDecode($table[$field]);
				}
			}
			return $table[$field];
		}
		if (!$raw ) {
			foreach($this->encodedFields as $key) {
				$table[$key] = stripslashes(base64_decode($table[$key]));
			}
			foreach($this->jsonedFields as $key) {
				$table[$key] = UtilsPyt::jsonDecode($table[$key]);
			}
		}

		return $table;
	}

	public function saveNew( $data = array() ) {
		$table = $data;
		if (empty($data['title'])) {
			$table['title'] = gmdate('Y-m-d-h-i-s');
		}
		$type = UtilsPyt::getArrayValue($data, 'type', 0, 1, $this->getFieldKeys('type'));
		$table['creation'] = UtilsPyt::getArrayValue($data, 'creation', 0, 1, $this->getFieldKeys('creation'));
		$table['builder'] = UtilsPyt::getArrayValue($data, 'builder', 0, 1, $this->getFieldKeys('builder'));
		$cols = UtilsPyt::getArrayValue($data, 'cols', 3, 1);
		$rows = UtilsPyt::getArrayValue($data, 'rows', 0, 1);
		$cellModel = FramePyt::_()->getModule('tables')->getModel('cells');

		$table['columns'] = UtilsPyt::jsonEncode($cellModel->createColModel($cols, $data), true);

		if (!empty($data['source'])) {
			$table['source'] = UtilsPyt::jsonEncode($data['source'], true);
		}
		$options = array('header' => 1);
		$table['options'] = UtilsPyt::jsonEncode($options, true);
		$table['type'] = $type;
		
		$tableId = $this->insert( $table );
		if ($tableId) {
			if (empty($data['title'])) {
				$this->updateById(array('title' => 'Table ' . $tableId), $tableId);
			}
			if ($type <= 1) {
				$cellModel->createTable($tableId, $cols, $rows, $data);
			}
		}
		return $tableId;
	}
	public function saveTitle( $id, $title ) {
		if (empty($id)) {
			FramePyt::_()->pushError(esc_html__('Id can\'t be empty', 'publish-your-table'));
			return false;
		}
		if (empty($title)) {
			FramePyt::_()->pushError(esc_html__('Title can\'t be empty', 'publish-your-table'));
			return false;
		}
		CachePyt::_()->cleanCache('cache_tables', $id);
		return $this->updateById(array('title' => $title), $id);
	}

	public function updateSettings( $tableId, $data ) {
		$data = DispatcherPyt::applyFilters('addTableSettings', $data, $tableId);
		$columns = array();
		foreach ($data as $col => $value) {
			if (in_array($col, $this->encodedFields)) {
				$columns[$col] = empty($value) ? '' : base64_encode(stripslashes($value));
			} else if (in_array($col, $this->jsonedFields) && is_array($value)) {
				$columns[$col] = UtilsPyt::jsonEncode($value, true);
			} else {
				$columns[$col] = $value;
			}
		}

		if (count($columns) > 0) {
			$this->updateById($columns, $tableId);
		}

		DispatcherPyt::doAction('updateTableData', $tableId);
		CachePyt::_()->cleanCache('cache_tables', $tableId);
		return true;
	}

	protected function _dataRemove( $ids ) {
		$cellTable = FramePyt::_()->getTable('cells');
		if (count($ids) > 0) {
			foreach ($ids as $id) {
				$cellTable->dropTable($id);
				DispatcherPyt::doAction('updateTableData', $id);
				CachePyt::_()->cleanCache('cache_tables', $id);
			}
		}
		return true;
	}
	public function cloneTable( $id ) {
		$table = $this->getTableData($id, false, true);
		if (!$table) {
			return false;
		}
		unset($table['id']);
		$table['title'] .= '-clone';
		$newId = $this->insert( $table );

		if ($newId) {
			$data = array();
			//you simply cannot insert json-fields - you must convert it; otherwise, line breaks are inserted 
			foreach($this->jsonedFields as $key) {
				$data[$key] = UtilsPyt::jsonDecode($table[$key]);
			}
			$this->updateSettings($newId, $data);
			FramePyt::_()->getModule('tables')->getModel('cells')->cloneCellsTable($id, $newId);
		}
		return $newId;
	}
}
