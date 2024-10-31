<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class ExportControllerPyt extends ControllerPyt {
	public function getNoncedMethods() {
		return array('exportTableData', 'generateUrl');
	}

	public function generateUrl()
	{
		$res = new ResponsePyt();
		$res->ignoreShellData();

		$url = 'admin.php?page=pyt-tables&export-pyt-table=true&type=%s&id=%d';
		$values = array(ReqPyt::getVar('type'), ReqPyt::getVar('tableId'));
		$params = ReqPyt::getVar('params');
		if (!empty($params) && is_string($params)) {
			$params = UtilsPyt::jsonDecode(stripslashes($params));
			foreach ($params as $key => $value) {
				$url .= '&' . $key . '=%s'; 
				$values[] = $value;
			}
		}
		$rows = ReqPyt::getVar('rows');
		$add = '';
		if (!is_null($rows) || $rows != 'all') {
			$add = '&rows=' . (is_array($rows) ? implode(',', $rows) : '0');
		}
		$res->addData(array('url' => admin_url(vsprintf($url, $values) . $add)));
		return $res->ajaxExec();
	}

	public function exportTableData() {

		$res = new ResponsePyt();
		if (ReqPyt::getVar('type') == 'sql') {
			$result = $this->getModule()->migrateTableData(ReqPyt::getVar('ids'), ReqPyt::getVar('mode'));	
		} else {
			$result = $this->getModule()->exportTableData(ReqPyt::getVar('tableId'), ReqPyt::get('post'));
		}

		if ($result) {
			$res->ignoreShellData();
			$res->addMessage(esc_html__('Data exported successfully', 'publish-your-table'));

		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}
}
