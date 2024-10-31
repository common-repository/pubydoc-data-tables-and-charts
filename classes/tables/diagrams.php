<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TableDiagramsPyt extends TablePyt {
	public function __construct() {
		$this->_table = '@__diagrams';
		$this->_id = 'id';
		$this->_alias = 'pyt_diagrams';
		$this->_addField('id', 'text', 'int')
			->_addField('title', 'text', 'varchar', '', esc_html__('Title', 'publish-your-table'), 128)
			->_addField('type', 'text', 'tinyint', 0, esc_html__('Type', 'publish-your-table'))
			->_addField('table_id', 'text', 'int', 0, esc_html__('Table Id', 'publish-your-table'))
			->_addField('table_range', 'text', 'varchar', 0, esc_html__('Range', 'publish-your-table'))
			->_addField('status', 'text', 'tinyint', 0, esc_html__('Status', 'publish-your-table'))
			->_addField('options', 'text', 'mediumtext', '', esc_html__('Options', 'publish-your-table'))
			->_addField('config', 'text', 'mediumtext', '', esc_html__('Data config', 'publish-your-table'));
	}
}
