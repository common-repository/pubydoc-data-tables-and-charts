<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class ImportControllerPyt extends ControllerPyt {
	public function getNoncedMethods() {
		return array('importTableData');
	}

	public function importTableData() {
		$res = new ResponsePyt();
		if (ReqPyt::getVar('type') == 'sql') {
			$tableId = $this->getModule()->migrateTableData(ReqPyt::get('files'));
		} else {
			$tableId = $this->getModule()->importTableData(ReqPyt::getVar('tableId'), ReqPyt::get('files'), ReqPyt::get('post'));
		}

		if ($tableId) {
			$res->ignoreShellData();
			$res->addMessage(esc_html__('Data imported successfully', 'publish-your-table'));
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}
}
