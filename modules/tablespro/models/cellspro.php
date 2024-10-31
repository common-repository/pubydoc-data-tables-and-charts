<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
FramePyt::_()->getModule('tables')->getModel('cells');
class CellsProModelPyt extends CellsModelPyt {

	public function getMaxRowsData( $tableId, $withRules = true ) {
		$maxNums = 0;
		if (!empty($tableId)) {
			$table = $this->initTable($tableId, 0);
			if ($table->existTable) {
				$maxNums = DbPyt::get('SELECT count(*), max(id), max(sorter) FROM ' . $table->getTable() . ' WHERE mode=1', 'row', ARRAY_N);
				if ($withRules && is_array($maxNums) && !empty($maxNums[0])) {
					$tuning = FramePyt::_()->getModule('tables')->getModel()->getTableData($tableId, 'tuning');
					$rules = UtilsPyt::getArrayValue($tuning, 'conditions', array(), 2);
					$rule = 0;
					foreach ($rules as $key => $data) {
						$n = (int) str_replace('rule', '', $key);
						if ($n > $rule) {
							$rule = $n;
						}
					}
					$maxNums[] = $rule; 
				}
			}
		}
		
		return is_array($maxNums) && !empty($maxNums[0]) ? $maxNums : array(0, 0, 0, 0);
	}

	public function checkUseEditableFields( $options ) {
		if (UtilsPyt::getArrayValue($options, 'efields_logged') == 1) {
			$allowed = false;
			$user = new UserPyt;
			if ($user->isLoggedIn()) {
				$rolesOnly = UtilsPyt::getArrayValue($options, 'efields_roles', array(), 2);
				if (empty($rolesOnly)) {
					$allowed = true;
				} else {
					$userInfo = $user->getCurrent();
					if (!empty($userInfo->roles)) {
						foreach ($userInfo->roles as $role) {
							if (in_array($role, $rolesOnly)) {
								$allowed = true;
								break;
							}
						}
					}
				}
			}
			if (!$allowed) {
				FramePyt::_()->pushError(esc_html__('There is no permissions to save data through editable fields.', 'publish-your-table'));
				return false;
			}
		}
		return true;
	}

	public function saveCellsValues( $request ) {
		$tableId = UtilsPyt::getArrayValue($request, 'tableId', 0, 1);

		$settings = FramePyt::_()->getModule('tables')->getModel('tables')->getTableData($tableId);
		$options = UtilsPyt::getArrayValue($settings, 'options', array(), 2);
		if (UtilsPyt::getArrayValue($options, 'efields_save') != 1) {
			FramePyt::_()->pushError(esc_html__('Saving frontend fields is not allowed', 'publish-your-table'));
			return false;
		}
		if (!$this->checkUseEditableFields($options)) {
			return false;
		}
		
		$cells = UtilsPyt::getArrayValue($request, 'cells', array(), 2);
		$tableType = $settings['type'];

		if ($tableType == 3) {
			$model = FramePyt::_()->getModule('tablespro')->getModel('databases');
			$result = $model->saveCellsValues($settings, $cells);
			if (!$result) {
				return false;
			}
		} else {

			$table = $this->initTable($tableId, 0, true);
			if (!$table) {
				return false;
			}
			
			$columns = UtilsPyt::getArrayValue($settings, 'columns', array(), 2);
			
			foreach($cells as $cell) {
				$col = $cell['col'];
				$id = (int)$cell['row'];
				if ( !empty($id) && isset($columns[$col]) ) {
					if (!$table->update(array($col => $cell['ov']), array('id' => $id))) {
						return false;
					}
					if (!$table->update(array($col => $cell['fv']), array('mode' => 2, 'sorter' => $id))) {
						return false;
					}
				}
			}
		}
		DispatcherPyt::doAction('updateTableData', $tableId);
		CachePyt::_()->cleanCache('cache_tables', $tableId);
		return true;
	}

	public function saveSourceData( $request ) {
		$tableId = UtilsPyt::getArrayValue($request, 'tableId', 0, 1);

		$colModel = UtilsPyt::getArrayValue($request, 'colModel');
		if (!empty($colModel)) {
			$colModel = UtilsPyt::jsonDecode(stripslashes($colModel));
		}
		$newColModel = FramePyt::_()->getModule('tables')->getModel('cells')->controlColModel($colModel);
		
		$tuning = UtilsPyt::getArrayValue($request, 'tuning');
		if (!empty($tuning)) {
			$tuning = UtilsPyt::jsonDecode(stripslashes($tuning));
		}

		$source = UtilsPyt::getArrayValue($request, 'source');
		if (!empty($source)) {
			$source = UtilsPyt::jsonDecode(stripslashes($source));
		}
		
		$settings = array(
			'columns' => $newColModel,
			'tuning' => $tuning,
			//'source' => $source,
			'source' => UtilsPyt::getArrayValue($source, 'source', array(), 2),
		);
		FramePyt::_()->getModule('tables')->getModel('tables')->updateSettings($tableId, $settings);
		return true;
	}

	public function getFrontRowsPro($table, $params) {
		$tableId = $table['id'];
		$tableType = $table['type'];

		$data = array();

		switch ($tableType) {
			case 1:
				if (!$params['isPreview'] && !UtilsPyt::getArrayValue($params, 'isPage', false)) {
					$tableId = FramePyt::_()->getModule('import')->importGoogleSpreadsheet($tableId, UtilsPyt::getArrayValue($table, 'source', array(), 2));
				}
				if ($tableId) {
					$settings = FramePyt::_()->getModule('tables')->getModel('tables')->getTableData($tableId);
					if ($settings) {
						$table['columns'] = $settings['columns'];
						$table['tuning'] = $settings['tuning'];
						$data = $this->getFrontRows($table, $params);
					}
				}
				break;
			case 3:
				list($cols, $headClasses, $select) = $this->getColumnsParams($table['columns'], '');
				$data = FramePyt::_()->getModule('tablespro')->getModel('databases')->getFrontRows($table, $params, $cols, $headClasses);
				break;
			
			default:
				break;
		}
		return $data ? $data : array();
	}
	
	public function existsTableDiagramShortcode( $tableId ) {
		$table = $this->initTable($tableId, 0, true);
		if (!$table) {
			return false;
		}
		$fields = $table->getFields();
		$prefix = $table->colPrefix;
		$sc = "'%[pyt-diagram%'";
		$where = '';

		foreach ($fields as $field => $d) {
			if (strpos($field, $prefix) === 0) {
				$where .= $field . ' LIKE ' . $sc . ' OR ';
			}
		}
		if (!empty($where)) {
			return DbPyt::get('SELECT 1 FROM ' . $table->getTable() . ' WHERE mode=1 AND (' . substr($where, 0, -3) . ') LIMIT 1', 'one') == 1;
		}

		return false;
	}
}
