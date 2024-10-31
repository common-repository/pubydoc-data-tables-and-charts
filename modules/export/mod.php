<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class ExportPyt extends ModulePyt {    
	public function init() {
		parent::init();
		add_action('init', array($this, 'handleExportRequest'));
	}
	public function handleExportRequest()
	{
		if (!ReqPyt::getVar('export-pyt-table')) {
			return;
		}
		if (ReqPyt::getVar('type') == 'sql') {
			$this->migrateTableData(ReqPyt::getVar('ids'), ReqPyt::getVar('mode'));	
		} else {
			$this->exportTableData(ReqPyt::getVar('id'), ReqPyt::get('get'));
		}
	}

	public function exportTableData( $id, $options ) {
		$type = UtilsPyt::getArrayValue($options, 'type');

		$onlyIds = false;
		$rows = UtilsPyt::getArrayValue($options, 'rows', false);
		if ($rows !== false) {
			$onlyIds = implode(',', UtilsPyt::controlNumericValues(explode(',', $rows)));
		}
		$settings = array('type' => $type, 'front' => UtilsPyt::getArrayValue($options, 'front', false));
		switch ($type) {
			case 'excel':
				$exporter = $this->getModel('export_xlsx_');
				$settings['rawValues'] = UtilsPyt::getArrayValue($options, 'excel_raw', 0) == 1;
				$settings['valuesOnly'] = UtilsPyt::getArrayValue($options, 'excel_valuesonly', 0) == 1;
				$settings['withFn'] = UtilsPyt::getArrayValue($options, 'excel_fn', 0) == 1;
				$settings['header'] = UtilsPyt::getArrayValue($options, 'excel_header', 0) == 1;
				$settings['type'] = UtilsPyt::getArrayValue($options, 'excel_type', 'xlsx');
				$settings['onlyIds'] = $onlyIds;

				// check for ability do export to xlsx excel format
				if('xlsx' == $settings['type'] && !class_exists('ZipArchive')) {
					FramePyt::_()->pushError(esc_html__('You need to enable ZipArchive extension in PHP config file on your server. Please, contact to your server administrator.', 'publish-your-table'));
					return false;
				}
				break;
			case 'csv':
				$exporter = $this->getModel('export_csv_');
				$settings['delimeter'] = UtilsPyt::getArrayValue($options, 'csv_delim', ',');
				$settings['rawValues'] = UtilsPyt::getArrayValue($options, 'csv_raw', 0) == 1;
				$settings['header'] = UtilsPyt::getArrayValue($options, 'csv_header', 0) == 1;
				$settings['onlyIds'] = $onlyIds;
				break;
			case 'pdf':
				$exporter = $this->getModel('export_xlsx_');
				$settings['rawValues'] = false;
				$settings['valuesOnly'] = false;
				$settings['withFn'] = true;
				$settings['header'] = true;
				
				break;			
			default:
				FramePyt::_()->pushError(esc_html(__('Unsupported export type', 'publish-your-table') . ' ' . $type));
				return false;
				break;
		}
		$this->includePHPSpreadsheet();

		$exporter->setSettings($settings);
		$exporter->export($id);
		die();
	}

	public function migrateTableData( $ids, $mode ) {
		$ids = UtilsPyt::controlNumericValues(explode(',', $ids));
		if (!empty($ids)) {
			$exporter = $this->getModel('export_sql_')->export($ids, $mode);
		}
		die();
	}

	public function includePHPSpreadsheet()
	{
		if (!class_exists('PHPSpreadsheet')) {
			require_once(PYT_LIB_DIR . 'PhpOffice/autoloader.php');
		}
		if (!class_exists('ZipStream')) {
			require_once(PYT_LIB_DIR . 'ZipStream/autoloader.php');
		}
		if (!class_exists('Enum')) {
			require_once(PYT_LIB_DIR . 'ZipStream/Enum.php');
		}
		if (!class_exists('Enum')) {
			require_once(PYT_LIB_DIR . 'TCPDF/tcpdf.php');
		}
	}
}
