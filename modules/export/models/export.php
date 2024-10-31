<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class ExportModelPyt extends ModelPyt {
	public $exportFormats = array(
		'csv' => 'CSV',
		'xls' => 'MS Excel 2003 (.xls)',
		'xlsx' => 'MS Excel 2007 (.xlsx)',
		'pdf' => 'PDF',
		'print' => 'Print'
	);

	protected $settings = array();
	protected $length = 100;
	protected $start = 0;
	protected $table = null;
	protected $tableType = null;
	protected $cellModel = null;
	protected $letters = array();
	protected $prefix = array();

	public function setSettings( $settings ) {
		foreach($settings as $key => $value) {
			$this->settings[$key] = $value;
		}
		if (empty($this->letters)) {
			$this->letters = range('A', 'Z');
			$this->prefix = array_merge(array(''), $this->letters);
		}
	}

	public function beginUpload() {
		if (ob_get_contents()) {
			ob_end_clean();
		}
		$fileName = empty($this->settings['title']) ? 'GeneratedFile' : mb_ereg_replace("([\.]{2,})", '', mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $this->settings['title']));
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $fileName . '.' . $this->settings['type'] . '"');
		header('Cache-Control: max-age=0');
		if (ob_get_contents()) {
			ob_end_clean();
		}
	}

	public function getDataForExport( $tableId ) {
		$first = false;
		if (is_null($this->table)) {
			$this->table = FramePyt::_()->getModule('tables')->getModel()->getTableData($tableId);
			if (!$this->table) {
				return false;
			}
			$this->setSettings(array('title' => UtilsPyt::getArrayValue($this->table, 'title')));
			$first = true;
			if (UtilsPyt::getArrayValue($this->settings, 'front', false)) {
				$options = UtilsPyt::getArrayValue($this->table, 'options', array(), 2);
				$autoIndex = UtilsPyt::getArrayValue($options, 'auto_index');
				if (!empty($autoIndex)) {
					$this->setSettings(array('autoIndex' => true, 'autoIndexNew' => ($autoIndex == 'new')));
				}
			}
		}
		if (is_null($this->cellModel)) {
			$this->tableType = $this->table['type'];
			switch ($this->tableType) {
				case 0:
					$this->cellModel = FramePyt::_()->getModule('tables')->getModel('cells');
					break;
				case 3:
					$this->cellModel = FramePyt::_()->getModule('tablespro')->getModel('cellspro');
					break;
				default:
					break;
			}
		}
		$params = array(
			'isSSP' => true,
			'isPage' => true,
			'withoutRaw' => true,
			'withoutSC' => true,
			'withFn' => UtilsPyt::getArrayValue($this->settings, 'withFn', false),
			'length' => $this->length,
			'start' => $this->start,
			'rawValues' => UtilsPyt::getArrayValue($this->settings, 'rawValues', false),
			'footerIds' => array(),
			'onlyIds' => UtilsPyt::getArrayValue($this->settings, 'onlyIds', false),
			'adminExport' => !UtilsPyt::getArrayValue($this->settings, 'front', false),
		);

		if (empty($this->tableType)) {
			$data = $this->cellModel->getFrontRows($this->table, $params);
		} else {
			$data = $this->cellModel->getFrontRowsPro($this->table, $params);
		}
		if ($first && UtilsPyt::getArrayValue($this->settings, 'autoIndexNew', false) && !empty($data['cols'])) {
			array_unshift($data['cols'], array('title' => ''));
		}
		return $data;
	}

	public function getCellValue( $value, &$isLink = '' ) {
		$isLink = '';
		if (strpos($value, '%3C') !== false) {
			$value = urldecode($value);
		}
		if (strpos($value, '&#') !== false) {
			$value = html_entity_decode($value);
		}
		$value = preg_replace('/<a href="#" class="delete-upload-file" title="(.*?)">(.*?)<\/a>/', '', $value);
		if (preg_match('/^<a.*href="([^"]*)".*>(.*)<\/a>$/i', $value, $matches)) {
			$isLink = $matches[1];
			$value = $matches[2];
		}
		return $value;
	}
	public function calcLetterIndex( $letterIndex, &$countIndex ) {
		if ($this->letters[$letterIndex] == 'Z') {
			$letterIndex = 0;
			$countIndex++;
		} else {
			$letterIndex++;
		}
		return $letterIndex;
	}
}
