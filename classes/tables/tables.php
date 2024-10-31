<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TableTablesPyt extends TablePyt {
	public function __construct() {
		$this->_table = '@__tables';
		$this->_id = 'id';
		$this->_alias = 'pyt_tables';
		$this->_addField('id', 'text', 'int')
			->_addField('title', 'text', 'varchar', '', esc_html__('Title', 'publish-your-table'), 128)
			->_addField('type', 'text', 'tinyint', 0, esc_html__('Source', 'publish-your-table'))
			->_addField('creation', 'text', 'tinyint', 0, esc_html__('Creation Mode', 'publish-your-table'))
			->_addField('builder', 'text', 'tinyint', 0, esc_html__('Builder', 'publish-your-table'))
			->_addField('source', 'text', 'mediumtext', '', esc_html__('Query', 'publish-your-table'))
			->_addField('options', 'text', 'mediumtext', '', esc_html__('Options', 'publish-your-table'))
			->_addField('columns', 'text', 'mediumtext', '', esc_html__('Columns settings', 'publish-your-table'))
			->_addField('tuning', 'text', 'mediumtext', '', esc_html__('Additional settings', 'publish-your-table'))
			->_addField('css', 'text', 'text', '', esc_html__('Custom CSS code', 'publish-your-table'))
			->_addField('add_css', 'text', 'text', '', esc_html__('Additional CSS code', 'publish-your-table'))
			->_addField('add_js', 'text', 'text', '', esc_html__('Additional JS code', 'publish-your-table'));
	}
}
