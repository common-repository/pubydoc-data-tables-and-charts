<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TablesProViewPyt extends ViewPyt {
	public function includeTemplate( $tpl, $params ) {
		foreach ($params as $param => $data) {
			$this->assign($param, $data);
		}
		return parent::display($tpl);
	}
}
