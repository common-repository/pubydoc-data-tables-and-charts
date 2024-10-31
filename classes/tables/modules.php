<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TableModulesPyt extends TablePyt {
	public function __construct() {
		$this->_table = '@__modules';
		$this->_id = 'id';     /*Let's associate it with posts*/
		$this->_alias = 'sup_m';
		$this->_addField('label', 'text', 'varchar', 0, esc_html__('Label', 'publish-your-table'), 128)
				->_addField('type_id', 'selectbox', 'smallint', 0, esc_html__('Type', 'publish-your-table'))
				->_addField('active', 'checkbox', 'tinyint', 0, esc_html__('Active', 'publish-your-table'))
				->_addField('params', 'textarea', 'text', 0, esc_html__('Params', 'publish-your-table'))
				->_addField('code', 'hidden', 'varchar', '', esc_html__('Code', 'publish-your-table'), 64)
				->_addField('ex_plug_dir', 'hidden', 'varchar', '', esc_html__('External plugin directory', 'publish-your-table'), 255);
	}
}
