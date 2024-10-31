<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class AdminmenuControllerPyt extends ControllerPyt {
	public function addNoticeAction() {
		$res = new ResponseWtbp();
		$code = ReqWtbp::getVar('code', 'post');
		$choice = ReqWtbp::getVar('choice', 'post');
		if (!empty($code) && !empty($choice)) {
			$optModel = FrameWtbp::_()->getModule('options')->getModel();
			switch ($choice) {
				case 'hide':
					$optModel->save('hide_' . $code, 1);
					break;
				case 'later':
					$optModel->save('later_' . $code, time());
					break;
				case 'done':
					$optModel->save('done_' . $code, 1);
					break;
			}
			$this->getModel()->checkAndSend( true );
		}
		$res->ajaxExec();
	}
}