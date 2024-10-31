<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class OptionsControllerPyt extends ControllerPyt {
	public function saveGroup() {
		$res = new ResponsePyt();
		if ($this->getModel()->saveGroup(ReqPyt::get('post'))) {
			$res->addMessage(esc_html__('Done', 'publish-your-table'));
		} else {
			$res->pushError ($this->getModel('options')->getErrors());
		}
		return $res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			PYT_USERLEVELS => array(
				PYT_ADMIN => array('saveGroup')
			),
		);
	}
}
