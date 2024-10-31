<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class ImportModelPyt extends ModelPyt {
	protected $settings = array();

	public function setSettings( $settings ) {
		foreach($settings as $key => $value) {
			$this->settings[$key] = $value;
		}
	}

	function convertExtension( $str ) {
		return strtoupper(substr($str, 0, 1)) . strtolower(substr($str, 1, strlen($str)));
	}

	public function updateTableData($data, $remove = false) {
		if (empty($this->settings['tableId'])) {
			$this->settings['rows'] = 0;
			$tableModel = FramePyt::_()->getModule('tables')->getModel('tables');
			$id = $tableModel->saveNew($this->settings);
			if (!$id) {
				return false;
			}
			$this->settings['tableId'] = $id;
			$remove = false;
		}
		$data['partSave'] = 1;
		$data['tableId'] = $this->settings['tableId'];

		$cellsModel = FramePyt::_()->getModule('tables')->getModel('cells');
        $data['remove'] = $remove;
        
		return $cellsModel->saveCellsData($data);
    }
}
