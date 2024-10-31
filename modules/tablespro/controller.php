<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TablesProControllerPyt extends ControllerPyt {
	
	public function getNoncedMethods() {
		return array('uploadFileData', 'saveEditableFields', 'getDatabaseColumns', 'saveSourceData');
	}

	public function uploadFileData() {
		$res = new ResponsePyt();
		$files = ReqPyt::get('files');

		if (!empty($files['cellFile'])) {
			$cellFile = $files['cellFile']; 
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once(ABSPATH . 'wp-admin/includes/file.php');
			}
			add_filter('upload_dir', array($this, 'changeUploadPath'));
			add_filter('upload_mimes', array($this, 'changeUploadMimes'));

			$upload = wp_handle_upload($cellFile, array('test_form' => false));

			remove_filter('upload_dir', array($this, 'changeUploadPath'));
			remove_filter('upload_mimes', array($this, 'changeUploadMimes'));

			if($upload && empty($upload['error'])) {
				$mime = strpos($upload['type'], '/') ? explode('/', $upload['type']) : array($upload['type']);
				switch($mime[0]){
					case 'image':
						$html = '<img src="'. $upload['url']. '">';
						break;
					default:
						$html = '<a href="'. $upload['url']. '" target="_blank">' . __('Download', 'publish-your-table'). '</a>';
						break;
				}
				$html .= '<i class="ui-icon ui-icon-closethick pyt-file-delete"></i>';

				$res->html = $html;
			} else {
				$res->pushError(__('Upload failed! Please try again.', 'publish-your-table'));
			}
		} else {
			$res->pushError(__('Upload failed!', 'publish-your-table'));
		}
		return $res->ajaxExec();
	}

	public function changeUploadPath( $param ){
		$mydir = '/pyt-upload-files';

		$param['path'] = $param['basedir'] . $mydir;
		$param['url'] = $param['baseurl'] . $mydir;

		return $param;
	}

	public function changeUploadMimes($mimes){
		$fileMimes = array(
			'svg' => 'image/svg+xml',
			'rar' => array(
				'application/x-rar-compressed',
				'application/x-rar'
			)
		);

		foreach ($fileMimes as $type => $mime){
			!is_array($mime) && $mime = array($mime);
			foreach ($mime as $single){
				$mimes[$type] = $single;
			}
		}

		return $mimes;
	}
	
	public function saveEditableFields() {
		$res = new ResponsePyt();

		$model = $this->getModel('cellspro');
		$result = $model->saveCellsValues(ReqPyt::get('post'));

		if ($result) {
			$res->ignoreShellData();
			$res->addMessage(esc_html__('Data saved successfully', 'publish-your-table'));
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}

	public function getDatabaseTables() {
		$res = new ResponsePyt();
		//FramePyt::_()->getModule('tables')->setIniLimits();
		$model = $this->getModel('databases');
		$ok = false;
		try {
			$source = UtilsPyt::jsonDecode(stripslashes(ReqPyt::getVar('source')));
			$source = UtilsPyt::getArrayValue($source, 'source', array(), 2);
			$connected = $model->connectToDatabase($source);
			if ($connected) {
				$dbTables = $model->getDBTables();
				if (is_array($dbTables)) {
					$res->ignoreShellData();
					$res->addData(array('tables' => $dbTables));
					$ok = true;
				}
			}
			if (!$ok) {
				$res->pushError(FramePyt::_()->getErrors());
			}

		} catch (Exception $e) {
			$res->pushError($e->getMessage());
		}
		return $res->ajaxExec();     
	}

	public function getTableFields() {
		$res = new ResponsePyt();
		//FramePyt::_()->getModule('tables')->setIniLimits();
		$model = $this->getModel('databases');
		$ok = false;
		try {
			$source = UtilsPyt::jsonDecode(stripslashes(ReqPyt::getVar('source')));
			$source = UtilsPyt::getArrayValue($source, 'source', array(), 2);
			$connected = $model->connectToDatabase($source);
			if ($connected) {
				$dbFields = $model->getTableFields($source['tbl_name']);
				if (is_array($dbFields)) {
					$res->ignoreShellData();
					$res->addData(array('fields' => $dbFields));
					$ok = true;
				}
			}
			if (!$ok) {
				$res->pushError(FramePyt::_()->getErrors());
			}
		} catch (Exception $e) {
			$res->pushError($e->getMessage());
		}
		return $res->ajaxExec();     
	}

	public function getSQLColumns() {
		$res = new ResponsePyt();
		//FramePyt::_()->getModule('tables')->setIniLimits();
		$model = $this->getModel('databases');
		$request = ReqPyt::get('post');

		$source = UtilsPyt::jsonDecode(stripslashes(UtilsPyt::getArrayValue($request, 'source')));
		$source = UtilsPyt::getArrayValue($source, 'source', array(), 2);

		$cols = $model->getSQLColumns($source);
		if (is_array($cols)) {
			$res->ignoreShellData();
			$res->addData(array('cols' => $cols));
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}

		return $res->ajaxExec();
	}

	public function saveSourceData() {
		$res = new ResponsePyt();

		$model = $this->getModel('cellspro');
		$result = $model->saveSourceData(ReqPyt::get('post'));
		if ($result) {
			$res->addMessage(esc_html__('Settings saved successfully', 'publish-your-table'));
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}

    public function getGoogleColumns() {
		$res = new ResponsePyt();
		FramePyt::_()->getModule('tables')->setIniLimits();
		$source = UtilsPyt::jsonDecode(stripslashes(ReqPyt::getVar('source')));
		$source = UtilsPyt::getArrayValue($source, 'source', array(), 2);
		$tableId = ReqPyt::getVar('tableId');

		$tableId = FramePyt::_()->getModule('import')->importGoogleSpreadsheet($tableId, $source);
		if ($tableId) {
			$res->ignoreShellData();
			$model = FramePyt::_()->getModule('tables')->getModel();
			$model->updateSettings($tableId, array('source' => $source));

			$res->addData(array('settings' => $model->getTableData($tableId)));
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}

		return $res->ajaxExec();
	}

	public function getRangeData() {
		$res = new ResponsePyt();
		$request = ReqPyt::get('post');
		$range = empty($request['range']) ? array() : $request['range'];

		$result = $this->getModule()->getRangeData(ReqPyt::getVar('tableId'), $range);

		if ($result) {
			$res->ignoreShellData();
			$res->data = $result;
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}

}
