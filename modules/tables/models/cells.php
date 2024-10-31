<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class CellsModelPyt extends ModelPyt {
	private $maxUpdate = 400;
	protected $propStyles = array('pytWrap', 'pytValign');

	public function __construct() {
		$this->_setTbl('cells');
		$lists = array(
			'mode' => array(
				0 => __('Raw', 'publish-your-table'),
				1 => __('Calculated not formated', 'publish-your-table'),
				2 => __('Calculated formated', 'publish-your-table'),
				3 => __('Properties', 'publish-your-table'),
				4 => __('Styles', 'publish-your-table'),
				5 => __('Attributes', 'publish-your-table')
			),
			'visibility' => array(
				0 => __('Visibile', 'publish-your-table'),
				1 => __('Invisibile', 'publish-your-table'),
				2 => __('Hidden', 'publish-your-table'),
			),
		);
		$this->setFieldLists($lists);
	}

	public function getDefaultColModel() {
		return array(
			'title' => __('New column', 'publish-your-table'),
			'width' => 120,
			'resizable' => 1,
			'dataType' => 'html',
			'dataIndx' => '',
			'nameIndx' => 0,
			'searchable' => 1,
			'sortable' => 1,
			'prop' => array('pyt' => array('pytType' => array('type' => 'text', 'format' => '')))
		);
	}

	public function initTable( $tableId, $cols, $exist = false ) {
		if (empty($tableId)) {
			FramePyt::_()->pushError(esc_html__('Id can\'t be empty', 'publish-your-table'));
			return false;
		}
		$table = $this->getTable();
		$table->init(array('id' => $tableId, 'cols' => $cols, 'new' => !$exist));
		
		if ($exist && !$table->existTable) {
			FramePyt::_()->pushError(esc_html__('Table not exist in Database', 'publish-your-table'));
			return false;
		}

		return $table;
	}
	public function getColPrefix() {
		return $this->getTable()->colPrefix;
	}

	public function createColModel($cols, $data = array()) {
		$prefix = $this->getColPrefix();
		$default = $this->getDefaultColModel();
		$columns = array();
		for ($c = 1; $c <= $cols; $c++) {
			$name = $prefix . $c;
			$column = $default;
			$column['dataIndx'] = $name;
			$column['nameIndx'] = $c;
			$title = UtilsPyt::getArrayValue($data, 'header' . $c, __('Column', 'publish-your-table') . ' ' . $c);
			$column['prop']['pyt']['title'] = $title;
			$column['title'] = $title;
			$columns[$name] = $column;
		}
		return $columns;
	}

	public function createTable($tableId, $cols, $rows, $params = array()) {
		$table = $this->initTable($tableId, $cols);
		$table->createTable();
		$prefix = $this->getColPrefix();
		$data = array();
		for ($r = 1; $r <= $rows; $r++) {
			$row = array('mode' => 1, 'sorter' => $r);
			for ($c = 1; $c <= $cols; $c++) {
				$row[$prefix . $c] = UtilsPyt::getArrayValue($params, 'data-' . $c . '-' . $r);
			}
			$data[] = $row;
		}
		if (!empty($data)) {
			$table->bulkInsert($data);
		}
	}

	public function getCellsData( $tableId, $params ) {
		$table = $this->initTable($tableId, 0, true);
		if (!$table) {
			return false;
		}
		$curPage = UtilsPyt::getArrayValue($params, 'curPage', 0);
		$perPage = UtilsPyt::getArrayValue($params, 'perPage', 0);
		$sort = UtilsPyt::getArrayValue($params, 'sort', array());
		$orderBy = 'sorter';
		if (!empty($sort)) {
			$order = '';
			foreach ($sort as $s) {
				$order .= $s['dataIndx'] . ($s['dir'] == 'down' ? ' DESC' : ' ASC') . ',';
			}
			if (!empty($order)) {
				DbPyt::query('SET @counter = 0');
				$query = 'UPDATE ' . $table->getTable() . ' SET ssp_sorter = @counter := @counter + 1
					WHERE mode=1
					ORDER BY ' . substr($order, 0, -1);
				DbPyt::query($query);
			}
			$orderBy = 'ssp_sorter';
		}
		
		$total = $this->setWhere(array('mode' => 1))->getCount();
		$this->setWhere(array('mode' => 1))->setOrderBy($orderBy);

		if (!empty($perPage)) {
			$curPage = empty($curPage) ? 1 : $curPage;
			$perPage = empty($perPage) ? 100 : $perPage;

			$skip = ($perPage * ($curPage - 1));
			if ($skip >= $total) {
				$curPage = ceil($total / $perPage);
				$skip = ($perPage * ($curPage - 1));
			}

			$this->setLimit($skip . ',' . $perPage);
		}

		$rows = $this->getFromTbl();
		$prefix = $this->getColPrefix();
		$data = array();
		$fonts = array();

		$excludeFonts = $this->getModule()->getExcludeFonts();
		$showFonts = is_array($excludeFonts);
		foreach ($rows as $row) {
			$cells = array();
			foreach ($row as $name => $value) {
				if (strpos($name, $prefix) === 0) {
					$cells[$name] = $value;
				}
			}
			$props = $row['props'];
			
			if (!empty($props)) {
				if ($showFonts) {
					preg_match_all('/"font-family":"(.*)"/U', $props, $out, PREG_SET_ORDER);
				}

				$props = UtilsPyt::jsonDecode($props);
				if (is_array($props)) {
					if (!empty($props['pq_fn'])) {
						foreach ($props['pq_fn'] as $c => $formula) {
							if (!empty($formula['fn'])) {
								$props['pq_fn'][$c]['fn'] = html_entity_decode($formula['fn']);
							}
							if (!empty($formula['fnOrig'])) {
								$props['pq_fn'][$c]['fnOrig'] = html_entity_decode($formula['fnOrig']);
							}
						}
					}
					$cells = array_merge($cells, $props);
				}
				if ($showFonts) {
					foreach ($out as $ff) {
						$f = $ff[1];
						if (!empty($f) && !in_array($f, $fonts) && !in_array($f, $excludeFonts)) {
							$fonts[] = $f;
						}
					}
				}
			}
			$cells['id'] = $row['id'];
			$data[] = $cells;
			$first = false;
		}
		return array(
			'totalRecords' => $total,
			'curPage' => $curPage,
			'data' => $data,
			'fonts' => $fonts
		);
	}
	
	public function controlColModel ($colModel) {
		$prefix = $this->getColPrefix();
		$newColModel = array();

		foreach ($colModel as $column) {
			$dataIndx = UtilsPyt::getArrayValue($column, 'dataIndx');
			if ($dataIndx == 'id' || $dataIndx == 'cb') {
				continue;
			}
			if (empty($dataIndx) || isset($newColModel[$dataIndx]) || strpos($dataIndx, $prefix) !== 0) {
				FramePyt::_()->pushError(esc_html__('Empty or duplicate dataIndx', 'publish-your-table') . ': '. esc_html($dataIndx));
				continue;
			}
			$nameIndx = (int) str_replace($prefix, '', $dataIndx);
			if (empty($nameIndx)) {
				FramePyt::_()->pushError(esc_html__('Empty or not integer nameIndx', 'publish-your-table') . ': '. esc_html($nameIndx));
				continue;
			}
			$prop = UtilsPyt::getArrayValue($column, 'prop', array(), 2);
			foreach($prop as $name => $value) {
				if ($value === '') {
					unset($prop[$name]);
				}
			}
			$column['prop'] = $prop;
			$style = UtilsPyt::getArrayValue($column, 'style', array(), 2);
			foreach($style as $name => $value) {
				if (empty($value) || $value == 'inherit' || $value == '#') {
					unset($style[$name]);
				}
			}
			$column['style'] = $style;
			$style = UtilsPyt::getArrayValue($column, 'styleHead', array(), 2);
			foreach($style as $name => $value) {
				if (empty($value) || $value == 'inherit' || $value == '#') {
					unset($style[$name]);
				}
			}
			$column['styleHead'] = $style;

			$column['dataIndx'] = $dataIndx;
			$column['nameIndx'] = $nameIndx;
			$newColModel[$dataIndx] = $column;
			$rawDefault[$dataIndx] = '';
		}
		return $newColModel;
	}

	public function clearCellsData( $tableId ) {
		$table = $this->initTable($tableId, 0, true);
		if ($table) {
			$table->delete();
		}
		return true;
	}
	public function cloneCellsTable( $tableId, $newId ) {
		$table = $this->initTable($tableId, 0, true);
		if ($table) {
			$tableName = $table->getTable();
			if (!DbPyt::query('CREATE TABLE @__cells' . $newId . ' LIKE ' . $tableName)) {
				FramePyt::_()->pushError(DbPyt::getError());
				return false;
			}
			if (!DbPyt::query('INSERT INTO @__cells' . $newId . ' SELECT * FROM ' . $tableName)) {
				FramePyt::_()->pushError(DbPyt::getError());
				return false;
			}

			return true;
		}
		FramePyt::_()->pushError(esc_html__('Not found table for clone', 'publish-your-table'));
		return false;
	}

	public function saveCellsData( $request ) {
		$partSave = UtilsPyt::getArrayValue($request, 'partSave', false);
		$tableId = UtilsPyt::getArrayValue($request, 'tableId', 0, 1);
		$tableModel = FramePyt::_()->getModule('tables')->getModel('tables');
		$remove = UtilsPyt::getArrayValue($request, 'remove', false);
		
		$table = $this->initTable($tableId, 0, true);
		if (!$table) {
			return false;
		}
		$updateSettings = array();
		
		$maxUpdate = $this->maxUpdate;
		$colModel = UtilsPyt::getArrayValue($request, 'colModel');
		if (!empty($colModel) && !is_array($colModel)) {
			$colModel = UtilsPyt::jsonDecode(stripslashes($colModel));
		}
		if (empty($colModel)) {
			if ($partSave) {
				$settings = FramePyt::_()->getModule('tables')->getModel('tables')->getTableData($tableId);
				if (!$settings) {
					return false;
				}
				$newColModel = UtilsPyt::getArrayValue($settings, 'columns', array(), 2);
			}
			if (empty($newColModel)) {
				FramePyt::_()->pushError(esc_html__('Parametr colModel not fould', 'publish-your-table'));
				return false;
			}
		} else {
			$newColModel = $this->controlColModel($colModel);
			$table->alterTable($tableId, array_keys($newColModel));
			$updateSettings['columns'] = $newColModel;
		}

		$prefix = $this->getColPrefix();

		$rawDefault = array();
		$rawDefault['merge'] = '';
		foreach ($newColModel as $col => $column) {
			$rawDefault[$col] = '';
		}

		if ($remove) {
			$table->delete();
		};

		$list = UtilsPyt::getArrayValue($request, 'list');
		if (!empty($list) && !is_array($list)) {
			$list = UtilsPyt::jsonDecode(stripslashes($list));
		}

		if (!is_array($list)) {
			$list = array();
		}
		
		$offset = UtilsPyt::getArrayValue($list, 'offset', 0, 1);

		$orderList = UtilsPyt::getArrayValue($list, 'orderList', array(), 2);
		$propList = UtilsPyt::getArrayValue($list, 'propList', array(), 2);
		$formatedCells = UtilsPyt::getArrayValue($list, 'formated', array(), 2);
		$cleanFormats = UtilsPyt::getArrayValue($list, 'cleanFormats', false, 1) == 1;

		if ($cleanFormats) {
			$this->delete(array('additionalCondition' => 'mode=2'));
		}

		// delete
		$deleteList = UtilsPyt::getArrayValue($list, 'deleteList', array(), 2);
		$delete = '';
		$k = 0;
		$i = 0;
		$cnt = count($deleteList);
		foreach ($deleteList as $row) {
			$k++;
			$i++;
			$id = is_numeric($row) ? $row : UtilsPyt::getArrayValue($row, 'id', 0, 1);
			if ($id) {
				$delete .= $id . ',';
			}
			if ($i >= $cnt || $k >=	$maxUpdate) {
				if (!empty($delete)) {
					$delete = substr($delete, 0, -1);
					$this->delete(array('additionalCondition' => 'id IN (' . $delete . ')'));
					$this->delete(array('additionalCondition' => 'mode!=1 AND sorter IN (' . $delete . ')'));
				}
				$delete = '';
				$k = 0;
			}
		}
		
		// insert
		$addList = UtilsPyt::getArrayValue($list, 'addList', array(), 2);
		$formated = array();
		$maxSorter = 0;
		$maxId = 0;
		$insert = 0;
		$prevO = 0;
		$k = 0;
		$i = 0;
		$cnt = count($addList);

		$newIds = array();
		foreach ($addList as $row) {
			$i++;
			$id = UtilsPyt::getArrayValue($row, 'id');
			if ($id) {
				$k++;
				$sorter = $offset + 1;
				$o = array_search($id, $orderList);
				if ($o) {
					$sorter += $o;
					$orderList[$o] = false;
				}

				$propId = 'id_' . $id;
				$props = '';
				if (isset($propList[$propId])) {
					$props = UtilsPyt::jsonEncode($propList[$propId], true);
					unset($propList[$propId]);
				}
				$data = array('mode' => 1, 'sorter' => $sorter, 'props' => $props);
				foreach ($row as $col => $value) {
					if (isset($newColModel[$col])) {
						$data[$col] = esc_sql($value);
					}
				}
				if (count($data) > 0) {
					$newId = $this->insert($data);
					$insert++;
					if (!$newId) {
						return false;
					}
					$newIds[$id] = $newId;
					$id = $newId;
				}
				if ($sorter > $maxSorter) {
					$maxSorter = $sorter;
					$maxId = $id;
				}

				$form = UtilsPyt::getArrayValue($row, 'pyt_formated', array(), 2);
				$data['mode'] = 2;
				$data['sorter'] = $id;
				$data['props'] = '';
				foreach ($form as $col => $value) {
					if (isset($data[$col])) {
						$data[$col] = esc_sql($value);
					}
				}
				$formated[] = $data;
				unset($formatedCells[$propId]);
			}
			if ($i >= $cnt || $k >=	$maxUpdate) {
				if (!empty($formated)) {
					$table->bulkInsert($formated);
				}
				$formated = array();
				$k = 0;
			}
		}

		if (!empty($insert)) {
			$table->bulkUpdate('sorter=sorter+' . $insert, 'mode=1 AND sorter>=' . $maxSorter . ' AND id!=' . $maxId);
		}

		// update
		$delete = '';
		$updateList = UtilsPyt::getArrayValue($list, 'updateList', array(), 2);

		$formated = array();
		$delete = '';
		$k = 0;
		$i = 0;
		$cnt = count($updateList);
		foreach ($updateList as $row) {
			$i++;
			$id = UtilsPyt::getArrayValue($row, 'id', 0, 1);
			if ($id) {
				$k++;
				$data = $rawDefault;
				foreach ($row as $col => $value) {
					if (isset($newColModel[$col])) {
						$data[$col] = esc_sql($value);
					}
				}
				$o = array_search($id, $orderList);
				if ($o) {
					$data['sorter'] = $offset + $o + 1;
					$orderList[$o] = false;
				}
				$propId = 'id_' . $id;
				if (isset($propList[$propId])) {
					$data['props'] = UtilsPyt::jsonEncode($propList[$propId], true);
					unset($propList[$propId]);
				}

				if (count($data) > 0) {
					if (!$this->update($data, array('id' => $id))) {
						return false;
					}
				}
				//formated
				$delete .= $id . ',';

				$form = UtilsPyt::getArrayValue($row, 'pyt_formated', array(), 2);
				$data['mode'] = 2;
				$data['sorter'] = $id;
				$data['props'] = '';
				foreach ($form as $col => $value) {
					if (isset($data[$col])) {
						$data[$col] = esc_sql($value);
					}
				}
				$formated[] = $data;
				unset($formatedCells[$propId]);
			}
			if ($i >= $cnt || $k >=	$maxUpdate) {
				if (!empty($formated)) {
					if (!$cleanFormats) {
						$this->delete(array('additionalCondition' => 'mode=2 AND sorter IN (' . substr($delete, 0, -1) . ')'));
					}
					$table->bulkInsert($formated);
				}
				$delete = '';
				$formated = array();
				$k = 0;
			}
		}
		
		// order
		foreach ($orderList as $i => $id) {
			if ($id) {
				if (!$this->update(array('sorter' => $i + $offset + 1, 'merge' => ''), array('id' => $id, 'mode' => 1))) {
					return false;
				} 
			}
		}

		// cell's properties
		foreach ($propList as $id => $data) {
			if (!$this->update(array('props' => UtilsPyt::jsonEncode($data, true)), array('id' => (int) substr($id, 3), 'mode' => 1))) {
				return false;
			}
		}
		DbPyt::query('UPDATE ' . $table->getTable() . ' SET visibility=(CASE WHEN props LIKE \'%invis%\' THEN 1 WHEN props LIKE \'%pytVisible":"hidden%\' THEN 2 ELSE 0 END)');

		// formated data
		$formated = array();
		$rawDefault['mode'] = 2;
		$k = 0;
		$i = 0;
		$cnt = count($formatedCells);
		$delete = '';
		foreach ($formatedCells as $id => $form) {
			$i++;
			$k++;
			$data = $rawDefault;
			foreach ($form as $col => $value) {
				if (isset($data[$col])) {
					$data[$col] = esc_sql($value);
				}
			}
			$intId = (int) substr($id, 3);
			if (!$cleanFormats) {
				$delete .= $intId . ',';
			}
			$data['sorter'] = $intId;
			$formated[] = $data;
			if ($i >= $cnt || $k >=	$maxUpdate) {
				if (!empty($delete)) {
					$this->delete(array('additionalCondition' => 'mode=2 AND sorter IN (' . substr($delete, 0, -1) . ')'));
				}
				$delete = '';

				if (!empty($formated)) {
					$table->bulkInsert($formated);
				}
				$formated = array();
				$k = 0;
			}
		}

		$tuning = false;
		if (isset($request['tuning'])) {
			$tuning = UtilsPyt::getArrayValue($request, 'tuning');
			if (!empty($tuning) && !is_array($tuning)) {
				$tuning = UtilsPyt::jsonDecode(stripslashes($tuning));
			}
			
			// merged cells
			$merge = UtilsPyt::getArrayValue($tuning, 'merge', array(), 2);
			if (!empty($merge)) {
				$mergeData = array();
				foreach ($merge as $data) {
					if (!empty($data['id'])) {
						$id = $data['id'];
						$mergeData[empty($newIds[$id]) ? $data['id'] : $newIds[$id]][$data['col']] = array('r' => $data['rc'], 'c' => $data['cc']);
					}
				}
				foreach ($mergeData as $id => $data) {
					$this->update(array('merge' => json_encode($data)), array('id' => (int) $id, 'mode' => 1));
				}
			}
			if (!$remove && !empty($settings)) {
				$tuningCur = UtilsPyt::getArrayValue($settings, 'tuning', array(), 2);
				if (!empty($tuning)) {
					foreach ($tuning as $key => $data) {
						if (isset($tuningCur[$key])) {
							$tuningCur[$key] = array_merge($tuningCur[$key], $data);
						} else {
							$tuningCur[$key] = $data;
						}
					}
				}
				$tuning = $tuningCur;
			}
			$updateSettings['tuning'] = $tuning;
		}
		if (isset($request['builder'])) {
			$updateSettings['builder'] = UtilsPyt::getArrayValue($request, 'builder', 0, 1);
		}

		if (count($updateSettings) > 0) {
			$tableModel->updateSettings($tableId, $updateSettings);
		}
		return true;
	}

	public function convertPropToStyle( $data, $el = '', $col = '' ) {
		if (empty($data)) {
			return array();
		}
		$styles = UtilsPyt::getArrayValue($data, $el . 'style', array(), 2);
		$props = empty($el) ? $data : UtilsPyt::getArrayValue($data, $el . 'prop', array(), 2);
		if (!empty($col)) {
			$styles = UtilsPyt::getArrayValue($styles, $col, array(), 2);
			$props = UtilsPyt::getArrayValue($props, $col, array(), 2);
		}
		if (!empty($props['pytWrap'])) {
			$styles['white-space'] = 'nowrap';
			$styles['overflow'] = $props['pytWrap'];
		}
		if (!empty($props['pytValign'])) {
			$styles['vertical-align'] = $props['pytValign'];
		}

		return $styles;
	}

	public function getColumnsParams( $columns, $select, $rawValues = false, $partCols = false ) {
		if (empty($partCols) || !is_array($partCols)) {
			$partCols = false;
		}
		$c = 0;
		$i = 0;
		$cols = array();
		$headClasses = array();
		$propStyles = $this->propStyles;
		foreach ($columns as $name => $col) {
			$i++;
			if ($partCols && !in_array($i, $partCols)) {
				continue;
			}
			$styles = $this->convertPropToStyle($col);
			$cls = 'pyt-head-' . $name;
			$attrs = array(
				'type' => '',
				'format' => '',
				'visible' => 1,
				'search' => UtilsPyt::getArrayValue($col, 'searchable', 0, 1) == 1 ? 1 : 0
				);
			foreach ($col as $key => $prop) {
				if (!empty($prop) && strpos($key, 'pyt') === 0 && !in_array($key, $propStyles)) {
					if (is_array($prop)) {
						foreach ($prop as $k => $p) {
							$attrs[$k] = $p;
						}
					} else {
						$attrs[strtolower(substr($key, 3))] = $prop;
					}
				}
			}
			$respons = UtilsPyt::getArrayValue($attrs, 'respons');
			if ('hidden' == $attrs['visible']) continue;
			$attrs['visible'] = ( 'invis' == $attrs['visible'] || 'hidden' == $respons ? 0 : 1 );

			$width = UtilsPyt::getArrayValue($col, 'width');
			$attrs['width'] = empty($width) || UtilsPyt::getArrayValue($attrs, 'flex', 0, 1) == 1 ? '' : ( strpos($width, '%') ? $width : $width . 'px' );
			
			$cols[$name] = array(
				'num' => $c++,
				'i' => $i,
				'title' => $col['title'],
				'sortable' => UtilsPyt::getArrayValue($col, 'sortable', 0, 1) == 1,
				'style' => $styles,
				'classes' => $cls . (empty($respons) ? '' : ' ' . $respons),
				'attrs' => $attrs
				);

			$styles = UtilsPyt::getArrayValue($col, 'styleHead', array(), 2);
			if (!empty($styles)) {
				$headClasses[$cls] = $styles;
			}
			$select .= ',cc.' . $name . ' as ' . $name . '_o,' . ( $rawValues ? 'cc.' . $name : 'IFNULL(fc.' . $name . ',cc.' . $name . ') as ' . $name );
		}
		return array($cols, $headClasses, $select);
	}

	public function getFrontRows($table, $params) {
		$tableId = $table['id'];
		$tableObj = $this->initTable($tableId, 0, true);
		if (!$tableObj) {
			return false;
		}
		$tableName = $tableObj->getTable();

		$length = UtilsPyt::getArrayValue($params, 'length', 0, 1);
		if ($length < 0) {
			$length = 0;
		}
		$start = UtilsPyt::getArrayValue($params, 'start', 0, 1);
		$isPreview = UtilsPyt::getArrayValue($params, 'isPreview', false);
		$rawValues = $isPreview || UtilsPyt::getArrayValue($params, 'rawValues', false);
		$withoutRaw = UtilsPyt::getArrayValue($params, 'withoutRaw', false);
		$withoutSC = UtilsPyt::getArrayValue($params, 'withoutSC', false);
		$withFn = UtilsPyt::getArrayValue($params, 'withFn', false);
		$adminExport = UtilsPyt::getArrayValue($params, 'adminExport', false);
		
		$builder = UtilsPyt::getArrayValue($params, 'builder', 0, 1);
		$isSSP = UtilsPyt::getArrayValue($params, 'isSSP', false);
		$isPage = UtilsPyt::getArrayValue($params, 'isPage', false);
		$isTablePart = UtilsPyt::getArrayValue($params, 'isTablePart', false) || UtilsPyt::getArrayValue($params, 'isSingleCell', false);
		$propStyles = $this->propStyles;

		$total = $this->setWhere(array('mode' => 1, 'additionalCondition' => 'visibility<=1'))->getCount();
		$filtered = $total;
		
		$select = 'SELECT cc.id,cc.sorter,cc.props,cc.visibility,cc.merge' . ( $rawValues ? '' : ',fc.id as fid' );
		
		$partCols = $isTablePart ? UtilsPyt::getArrayValue($params, 'partCols', array(), 2) : false;

		list($cols, $headClasses, $select) = $this->getColumnsParams($table['columns'], $select, $rawValues, $partCols);

		$from = ' FROM ' . $tableName . ' cc' .
			( $rawValues ? '' : ' LEFT JOIN ' . $tableName . ' fc ON (fc.mode=2 AND fc.sorter=cc.id)' ) .
			' WHERE cc.mode=1 AND cc.visibility<=1';
		
		$onlyIds = UtilsPyt::getArrayValue($params, 'onlyIds', false);
		if ($onlyIds) {
			$from .= ' AND cc.id IN(' . $onlyIds . ')';
			$order = ' ORDER BY FIELD(cc.id,' . $onlyIds . ')';
		} else {
			$order = ' ORDER BY cc.sorter';
		}

		if ($isTablePart) {
			$partRows = UtilsPyt::getArrayValue($params, 'partRows', array(), 2);
			if (!empty($partRows)) {
				$from .= ' AND cc.sorter IN(' . implode(',', $partRows) . ')';
			}
		}

		$query = $select . $from;

		$clName = 'pyt-style-';
		$options = UtilsPyt::getArrayValue($table, 'options', array());

		if ($isSSP) {
			if ($isPage) {
				if (!empty($params['footerIds'])) {
					$exclude = '';
					$footer = 0;
					foreach ($params['footerIds'] as $id) {
						if (is_numeric($id)) {
							$exclude .= '"' . $id . '",';
							$footer++;
						}
					}
					if (!empty($exclude)) {
						$query .= ' AND cc.id NOT IN (' . substr($exclude, 0, -1) . ')';
						$total -= $footer;
					}
				}
				$search = UtilsPyt::getArrayValue($params, 'search', array());
				$calcFiltered = false;
				if (!empty($search)) {
					$searchValue = UtilsPyt::getArrayValue($search, 'value');
					if (!empty($searchValue)) {
						$where = '';
						foreach ($table['columns'] as $col) {
							$where .= 'cc.' . $col['dataIndx'] . ' LIKE "%' . esc_sql($searchValue) . '%" OR ';
						}
						if (!empty($where)) {
							$from .= ' AND (' . substr($where, 0, -4) . ')';
							$calcFiltered = true;
							
						}
					}
				}
				$colSearch = UtilsPyt::getArrayValue($params, 'columns', array(), 2);
				if (!empty($colSearch)) {
					$where = '';
					foreach ($colSearch as $sData) {
						$search = UtilsPyt::getArrayValue($sData, 'search', array());
						if (!empty($search)) {
							$searchValue = UtilsPyt::getArrayValue($search, 'value');
							if (!empty($searchValue) && isset($sData['data']) && isset($cols[$sData['data']])) {
								$where .= ' AND cc.' . $sData['data'] . ' LIKE "%' . esc_sql($searchValue) . '%"';
							}
						}
					}
					if (!empty($where)) {
						$from .= $where;
						$calcFiltered = true;
					}
				}

				if ($calcFiltered) {
					$filtered = DbPyt::get('SELECT COUNT(*) AS total' . $from /*. ' AND cc.visibility=0'*/, 'one');
					$query = $select . $from;
				}

				$colNames = UtilsPyt::getArrayValue($params, 'colNames', array(), 2);
				$colOrder = UtilsPyt::getArrayValue($params, 'order', array(), 2);
				if (!empty($colOrder)) {
					$newOrder = '';
					foreach ($colOrder as $oData) {
						$colNum = UtilsPyt::getArrayValue($oData, 'column', -1, 1, false, true);
						if ($colNum >= 0 && isset($colNames[$colNum]) && isset($cols[$colNames[$colNum]])) {
							$newOrder .= 'cc.' . $colNames[$colNum] . (UtilsPyt::getArrayValue($oData, 'dir', 'asc') == 'desc' ? ' DESC' : '') . ',';
						}
					}
					if (!empty($newOrder)) {
						$order = ' ORDER BY ' . substr($newOrder, 0, -1);
					}
				}

				$query .= $order . ( empty($length) ? '' : ' LIMIT ' . $start . ',' . $length );	
			} else {
				$clName = 'pyt-hstyle-';
				$header = UtilsPyt::getArrayValue($options, 'header', false) == 1 ? UtilsPyt::getArrayValue($options, 'header_rows', 0) : 0;
				$footer = UtilsPyt::getArrayValue($options, 'footer', false) == 1 && UtilsPyt::getArrayValue($options, 'custom_footer', false) == 1 ? UtilsPyt::getArrayValue($options, 'footer_rows', 0) : 0;
				$union = '';
				if ($header) {
					$union = $query . $order . ' LIMIT 0,' . $header;	
				}
				if ($footer) {
					if ($header) {
						$union = '(' . $union . ') UNION ALL (' . $query . $order . ' DESC LIMIT 0,' . $footer . ')';
					} else {
						$union = $query . $order . ' LIMIT ' . ($total - $footer + 1) . ',' . $footer;
					}
				}
				$query = $union;
			}
		} else {
			$query .= $order;
		}
		
		$data = array();
		$r = 0;
		$attrs = array();
		$classes = array();

		if (!empty($query)) {
			$rows = DbPyt::get($query);
			$defData = $isPage && !$adminExport && UtilsPyt::getArrayValue($options, 'auto_index') == 'new' ? array('auto' => '') : array();

			foreach ($rows as $row) {
				$d = $defData;
				$c = array();
				$а = array();
				$t = array();
				
				$props = UtilsPyt::jsonDecode($row['props']);
				$merge = UtilsPyt::jsonDecode($row['merge']);
				$rowStyles = $this->convertPropToStyle($props, 'pq_row');

				if (UtilsPyt::getArrayValue($props, 'pq_htfix', 0, 1)) {
					$height = UtilsPyt::getArrayValue($props, 'pq_ht', 0, 1);
					if (!empty($height)) {
						$rowStyles['height'] = $height . 'px';
					}
				}				
				$invis = $row['visibility'];
				$formated = $rawValues || is_null($row['fid']) ? 0 : 1;

				$cellProps = UtilsPyt::getArrayValue($props, 'pq_cellprop', array(), 2);
				$cellFns = $withFn ? UtilsPyt::getArrayValue($props, 'pq_fn', array(), 2) : array();
				$cellAttrs = UtilsPyt::getArrayValue($props, 'pq_cellattr', array(), 2);

				foreach ($cols as $name => $colData) {
					if (empty($invis)) {
						$styles = array_merge($colData['style'], $rowStyles, $this->convertPropToStyle($props, 'pq_cell', $name));
						$class = '';
						if (!empty($styles)) {
							foreach ($classes as $cl => $clData) {
								if ($clData == $styles) {
									$class = $cl;
									break;
								}
							}
							if (empty($class)) {
								$r++;
								$class = $clName . $r;
								$classes[$class] = $styles;
							}
						}
						if (!empty($merge[$name])) {
							$mdata = $merge[$name];
							if ($mdata['r'] != 1) {
								$а[$name]['rowspan'] = $mdata['r'];
							}
							if ($mdata['c'] != 1) {
								$а[$name]['colspan'] = $mdata['c'];
							}
						}
						$cProp = UtilsPyt::getArrayValue($cellProps, $name, array(), 2);
						foreach ($cProp as $key => $prop) {
							if (!empty($prop) && strpos($key, 'pyt') === 0 && !in_array($key, $propStyles)) {
								if (is_array($prop)) {
									foreach ($prop as $k => $p) {
										$а[$name][$k] = $p;
									}
								} else {
									$а[$name][strtolower(substr($key, 3))] = $prop;
								}
							}
						}
						$aProp = UtilsPyt::getArrayValue($cellAttrs, $name, array(), 2);
						foreach ($aProp as $key => $prop) {
							$t[$name][$key] = $prop;
						}
					} else {
						$class = 'pyt-invisible';
					}
					if (!$withoutRaw) {
						$а[$name]['value'] = $row[$name . '_o'];
					}
					$cFn = UtilsPyt::getArrayValue($cellFns, $name, array(), 2);
					if (!empty($cFn['fn'])) {
						$а[$name]['fn'] = html_entity_decode($cFn['fn']);
					}
					$value = $row[$name];
					if (!$withoutSC && UtilsPyt::getArrayValue($а[$name], 'front') == 'shortcode') {
						$value = do_shortcode(strpos($value, '%3C') !== false ? urldecode($value) : $value);
					}
					$d[$name] = $value;
					if (!empty($class)) {
						$c[$name] = $class;
					}
				}
				$data[] = $d;

				$rowA = array('id' => $row['id'], 'i' => $row['sorter'], 'h' => $invis, 'f' => $formated);
				if (!empty($c)) {
					$rowA['c'] = $c;
				}
				if (!empty($а)) {
					$rowA['a'] = $а;
				}
				if (!empty($t)) {
					$rowA['t'] = $t;
				}
				$attrs[] = $rowA;
			}
		}
		return array(
			'total' => $total,
			'filtered' => ($filtered > $total ? $total : $filtered),
			'rows' => $data,
			'attrs' => $attrs,
			'cols' => $cols,
			'classes' => array_merge($headClasses, $classes),
		);
	}

	public function getRangeData( $settings, $range, $withTitles = true ) {
		$tableId = $settings['id'];
		$table = $this->initTable($tableId, 0, true);
		if (!$table) {
			return false;
		}
		$isOneCell = false;

		if (isset($range['from'])) {
			$range = array($range);
		}
		$ranges = isset($range['from']) ? array($range) : $range;
		$columns = array_keys($settings['columns']);

		//$skip = $fCol;
		$fields = array();
		$minRow = -1;
		$maxRow = -1;
		$colRows = array();
		$colTitles = array();

		foreach ($ranges as $range) {
			$from = UtilsPyt::getArrayValue($range, 'from', array());
			$to = UtilsPyt::getArrayValue($range, 'to', array());
			if (empty($to)) {
				$isOneCell = true;
				$to = $from;
			}
			$fCol = UtilsPyt::getArrayValue($from, 'c', 1, 1);
			$tCol = UtilsPyt::getArrayValue($to, 'c', 1, 1);
			$fRow = UtilsPyt::getArrayValue($from, 'r', 0, 1);
			$tRow = UtilsPyt::getArrayValue($to, 'r', 0, 1);
			$formated = UtilsPyt::getArrayValue($range, 'formated', false);

			for ($c = $fCol - 1; $c < $tCol; $c++) {
				if (!isset($columns[$c])) {
					break;
				}
				$colName = $columns[$c];
				if (!in_array($colName, $fields)) {
					$fields[] = $colName;
					if ($withTitles) {
						$colTitles[] = array('name' => $settings['columns'][$colName]['title']);
					}
				}

				$colRows[array_search($colName, $fields)] = array($fRow, $tRow);
			}
			if (!empty($minRow)) {
				if (empty($fRow)) {
					$minRow = 0;
					$maxRow = 0;
				} else {
					if ($minRow < 0 || $fRow < $minRow) {
						$minRow = $fRow;
					}
					if ($maxRow < 0 || $tRow > $maxRow) {
						$maxRow = $tRow;
					}
				}
			}
		}
		if (empty($fields)) {
			return array();
		}

		$query = 'SELECT ' . implode(',', $fields) . ' FROM ' . $table->getTable() .
			' WHERE mode=' . ($formated ? 2 : 1);
		if ($isOneCell) {
			$query .= ' AND sorter=' . $fRow;
		} else {

			$query .= ' ORDER BY sorter';
			if (!empty($minRow)) {
				$query .= ' LIMIT ' . ($minRow - 1) . ',' . ($maxRow - $minRow + 1);

			}
		}

		$rows = DbPyt::get($query, 'all', ARRAY_N);
		if (count($ranges) > 1) {
			$multy = false;
			if (empty($minRow)) {
				$cnt = count($rows);
				foreach ($colRows as $c => $col) {
					if (empty($col[0])) {
						$colRows[$c][1] = $cnt - 1;	
					} else {
						$multy = true;
						$colRows[$c][0]--;
						$colRows[$c][1]--;
					}
				}
			} else {
				foreach ($colRows as $c => $col) {
					if ($col[0] != $minRow || $col[1] != $maxRow) {
						$multy = true;
					}
					$cnt = $colRows[$c][1] - $colRows[$c][0];
					$colRows[$c][0] -= $minRow;
					$colRows[$c][1] = $colRows[$c][0] + $cnt;
				}
			}

			if ($multy) {
				foreach ($rows as $r => $cols) {
					foreach ($cols as $c => $value) {
						if (!empty($colRows[$c]) && ($colRows[$c][0] > $r || $colRows[$c][1] < $r)) {
							$rows[$r][$c] = '';
						}
					}
				}
			}
		}
		return $withTitles ? array('values' => $rows, 'titles' => $colTitles) : $rows;
	}
}
