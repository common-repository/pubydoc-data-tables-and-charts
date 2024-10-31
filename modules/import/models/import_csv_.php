<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
FramePyt::_()->getModule('import')->getModel('import');
class Import_csv_ModelPyt extends ImportModelPyt {
	public function import( $fileName, $extension = 'CSV' ) {
		$result = array();
		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
		$reader->setDelimiter($this->settings['delimeter']);
		$reader->setReadDataOnly(true);
		$excel = $reader->load($fileName);

		$sheet = $excel->getActiveSheet();
		$rows = $sheet->getRowIterator();

		$cellModel = FramePyt::_()->getModule('tables')->getModel('cells');
		$prefix = $cellModel->getColPrefix();

		$rowNumber = 0;
		$firstRow = true;
		$maxColumns = 0;
		$withHeader = $this->settings['remove'] && $this->settings['header'];
		$titles = array();
		$order = array();

		$newTable = empty($this->settings['tableId']);
		$newModel = $this->settings['remove'] || $newTable;
		$lastId = 0;
		$lastOrder = 0;
		if (!$newModel) {
			list($l, $lastId, $lastOrder) = FramePyt::_()->getModule('tablespro')->getModel('cellspro')->getMaxRowsData($this->settings['tableId'], false);
		}
		
		foreach ($rows as $row) {
			$cells = $row->getCellIterator();
			$cells->setIterateOnlyExistingCells(false);
			$columnNumber = 0;
			$isHeader = $withHeader && $firstRow;
			foreach ($cells as $cell) {
				$columnNumber++;
				$data = stripslashes($cell->getValue());
				$col = $prefix . $columnNumber;
				if ($isHeader) {
					$titles[$col] = $data;
				} else {
					$rowId = $cell->getRow() + $lastId;
					$cellsO[$col] = stripslashes($cell->getValue());
				}				
			}
			if (!$isHeader) {
				$cellsO['id'] = $rowId;
				$order[$lastOrder] = $rowId;
				$lastOrder++;

				$result[$rowNumber] = $cellsO;
				$rowNumber++;
			}
			if ($maxColumns < $columnNumber) {
				$maxColumns = $columnNumber;
			}
			$firstRow = false;
		}

		$this->settings['cols'] = $maxColumns;

		if ($newModel) {
			$colModel = $cellModel->createColModel($maxColumns);
			if ($withHeader) {
				foreach ($colModel as $col => $data) {
					if (isset($titles[$col])) {
						$colModel[$col]['title'] = $titles[$col];
						$colModel[$col]['prop']['pyt']['title'] = $titles[$col];
					}
				}
			}
		}

		$list = array('addList' => $result, 'orderList' => $order);

		$forSave = array('list' => $list);
		if ($newModel) {
			$forSave['colModel'] = $colModel;
		}
		$this->updateTableData($forSave, $this->settings['remove']);

		return $this->settings['tableId'];
	}
}
