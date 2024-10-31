<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class ImportPyt extends ModulePyt {    
	public function init() {
		parent::init();
			
	}
	public function importTableData( $id, $files, $options ) {
		$creation = UtilsPyt::getArrayValue($options, 'creation', 0);
		$file = '';
		switch ($creation) {
			case 1:
				$file = 'excel_file';

				break;
			case 2:
				$file = 'csv_file';
				break;
			case 3:
				return $this->importGoogleSpreadsheet($id, $options);
				break;
			default:
				break;
		}
		if (!empty($file) && !empty($files[$file])) {		
			$uploatedFile = $files[$file];
			if (!$this->controlUploatedFile($uploatedFile)) {
				return false;
			}
			$id = $this->importFile($id, $uploatedFile, $options);
			if (!$id) {
				FramePyt::_()->pushError(esc_html__('Unexpected error.', 'publish-your-table'));
			}
		} else {
			FramePyt::_()->pushError(esc_html__('File not found.', 'publish-your-table'));
			$id = false;
		}
		
		return $id;
	}

	public function importFile($id, $file, $settings) {
		$this->includePHPSpreadsheet();

		$extension = pathinfo($file['name'], PATHINFO_EXTENSION);

		$settings['remove'] = UtilsPyt::getArrayValue($settings, 'append_data', 0) != 1;
		$settings['tableId'] = $id;
		if (!empty($id)) {
			$settings['uniq'] = 'pyt-import-' . $id;
		}

		switch (strtolower($extension)) {
			case 'csv':
				$importer = $this->getModel('import_csv_');
				$settings['delimeter'] = UtilsPyt::getArrayValue($settings, 'csv_delim', ',');
				$settings['header'] = UtilsPyt::getArrayValue($settings, 'csv_header', 0) == 1;
				break;
			case 'xls':
			case 'xlsx':
				$importer = $this->getModel('import_xlsx_');
				$settings['raw'] = UtilsPyt::getArrayValue($settings, 'excel_raw', 0) == 1;
				$settings['visibleData'] = UtilsPyt::getArrayValue($settings, 'excel_visible', 0) == 1;
				$settings['header'] = UtilsPyt::getArrayValue($settings, 'excel_header', 0) == 1;
				break;
			default:
				FramePyt::_()->pushError(esc_html__('Unsupported file type', 'publish-your-table') . ' ' . $extension);
				return false;
		}
		$importer->setSettings($settings);
		
		return $importer->import($file['tmp_name'], $extension);
	}

	public function importGoogleSpreadsheet($id, $source) {

		$settings = UtilsPyt::getArrayValue($source, 'source', array());
		if (empty($settings) && !empty($source)) {
			$settings = $source;
		}
		$googleUrl = UtilsPyt::getArrayValue($settings, 'google_url');
		preg_match('/([\w-]{25,}).+#gid=(\d+)/', $googleUrl, $matches);
		if (empty($matches)) {
			FramePyt::_()->pushError(esc_html__('Wrong spreadsheet id or url', 'publish-your-table'));
			return false;
		}
		$spreadsheetId = $matches[1];
		$sheetId = $matches[2];
		$url = "https://docs.google.com/spreadsheets/d/$spreadsheetId/export?format=xlsx&gid=$sheetId";
		$response = wp_remote_get($url);
		if (is_wp_error($response)) {
			FramePyt::_()->pushError($response->get_error_message());
			return false;
		}
		$contentType = wp_remote_retrieve_header($response, 'content-type');
		if (strpos($contentType, 'application') === false) {
			FramePyt::_()->pushError(esc_html__('Please, check the sharing settings of your spreadsheet. It must be accessed to edit for everyone who has link', 'publish-your-table'));
		}
		$file = wp_remote_retrieve_body($response);
		if (empty($file)) {
			FramePyt::_()->pushError(error_get_last());
			return false;
		}
		$this->includePHPSpreadsheet();
		try {
			$importer = $this->getModel('import_xlsx_');
			$temp = tempnam(sys_get_temp_dir(), 'spreadsheet');
			$handler = fopen($temp, 'w+');
			fwrite($handler, $file);
			fclose($handler);

			$header = UtilsPyt::getArrayValue($settings, 'google_header', 0) == 1;

			$importer->setSettings(array_merge($settings, array(
				'tableId' => $id,
				'remove' => UtilsPyt::getArrayValue($settings, 'append_data', 0) != 1,
				'raw' => UtilsPyt::getArrayValue($settings, 'google_raw', 0) == 1,
				'visibleData' => false,
				'header' => $header,
				'uniq' => 'pyt-google-' . $spreadsheetId . '-' . $sheetId,
				'source' => array('google_url' => $googleUrl, 'google_header' => $header)
			)));

			try {
				$tableId = $importer->import($temp, 'xlsx');
				unlink($temp);
				if (!empty($tableID) && UtilsPyt::getArrayValue($settings, 'type', 0) == 1) {
					FramePyt::_()->getModule('tables')->getModel()->updateSettings($tableId, array('source' => array('google_url' => $googleUrl, 'google_header' => $header)));
				}
				return $tableId;

			} catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
				FramePyt::_()->pushError(__('Import Error. Possible reason', 'publish-your-table') . ' - ' . $e->getMessage() . ' ' . __('Also, please, check the sharing settings of your spreadsheet: it must be accessed to edit for everyone who has link.', 'publish-your-table'));
			}
		} catch (InvalidArgumentException $e) {
			FramePyt::_()->pushError($e->getMessage());
		}
		return false;
	}

	public function controlUploatedFile( $file ) {
		if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
			switch ($file['error']) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					FramePyt::_()->pushError(esc_html__('The uploaded file exceeds the max size of uploaded files.', 'publish-your-table'));
					break;
				case UPLOAD_ERR_PARTIAL:
					FramePyt::_()->pushError(esc_html__('The uploaded file was only partially uploaded.', 'publish-your-table'));
					break;
				case UPLOAD_ERR_NO_FILE:
					FramePyt::_()->pushError(esc_html__('No file was uploaded.', 'publish-your-table'));
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					FramePyt::_()->pushError(esc_html__('Missing a temporary folder.', 'publish-your-table'));
					break;
				case UPLOAD_ERR_CANT_WRITE:
					FramePyt::_()->pushError(esc_html__('Failed to write file to disk.', 'publish-your-table'));
					break;
				default:
					FramePyt::_()->pushError(esc_html__('Unexpected error.', 'publish-your-table'));
			}
			return false;
		}
		return true;
	}

	public function migrateTableData( $files ) {
		$file = 'sql_file';
		if (!empty($files[$file])) {		
			$uploatedFile = $files[$file];
			if (!$this->controlUploatedFile($uploatedFile)) {
				return false;
			}
			$result = $this->getModel('import_sql_')->import($uploatedFile['tmp_name']);

			if (!$result) {
				FramePyt::_()->pushError(esc_html__('Unexpected error.', 'publish-your-table'));
				return false;
			}
		} else {
			FramePyt::_()->pushError(esc_html__('File not found.', 'publish-your-table'));
			return false;
		}
		
		return true;
	}

	public function includePHPSpreadsheet()
	{
		if (!class_exists('PHPSpreadsheet')) {
			require_once(PYT_LIB_DIR . 'PhpOffice/autoloader.php');
		}
	}
}
