<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TableCellsPyt extends TablePyt {
	public $existTable = false;
	public $colPrefix = 'col';
	public function __construct() {
		$this->_id = 'id';
		$this->_alias = 'pyt_cells';
		$this->init();
	}

	public function init( $params = array() ) {
		$id = UtilsPyt::getArrayValue($params, 'id');
		$cols = UtilsPyt::getArrayValue($params, 'cols', 1);
		$new = UtilsPyt::getArrayValue($params, 'new', false);
		$name = '@__cells' . $id;

		$this->_table = $name;
		$this->_fields = array();
		$this->_addField('id', 'text', 'int')
			->_addField('mode', 'text', 'tinyint', 0, esc_html__('Type', 'publish-your-table'))
			->_addField('sorter', 'text', 'int', 0, esc_html__('Order/Parent', 'publish-your-table'))
			->_addField('ssp_sorter', 'text', 'int', 0, esc_html__('SSP order fror admin panel', 'publish-your-table'))
			->_addField('visibility', 'text', 'tinyint', 0, esc_html__('Visibility settings', 'publish-your-table'))
			->_addField('merge', 'text', 'text', 0, esc_html__('Merge settings', 'publish-your-table'))
			->_addField('props', 'text', 'mediumtext', 0, esc_html__('Row/cells settings', 'publish-your-table'));

		$this->existTable = $this->isRealTable();
		if (!$new && $this->existTable) {
			$columns = DbPyt::get('SHOW COLUMNS FROM `' . $this->_table . '`');
			foreach ($columns as $col) {
				$name = $col['Field'];
				if (strpos($name, $this->colPrefix) === 0) {
					$this->_addField($name, 'text', 'text', '');
				}
			}
		} else {
			for ($i = 1; $i <= $cols; $i++) {
				$this->_addField($this->colPrefix . $i, 'text', 'text', '');
			}
		}
	}

	public function dropTable( $id ) {
		DbPyt::query('DROP TABLE IF EXISTS `@__cells' . $id . '`');	
	}

	public function createTable() {
		$tableName = $this->_table;

		DbPyt::query('DROP TABLE IF EXISTS `' . $tableName . '`');
		$query = 'CREATE TABLE IF NOT EXISTS `' . $tableName . '` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`mode` tinyint(1) NOT NULL,
			`sorter` int(11) NOT NULL,
			`ssp_sorter` int(11) NOT NULL,
			`visibility` tinyint(1) NOT NULL,
			`merge` text NOT NULL,
			`props` mediumtext NOT NULL,';

		foreach ($this->_fields as $i => $field) {
			$name = $field->name;
			if (strpos($name, $this->colPrefix) === false) {
				continue;
			}
			$query .= '`' . $name . '` text NOT NULL,';
		}
		$query .= 'PRIMARY KEY (`id`), INDEX (`sorter`)) DEFAULT CHARSET=utf8;';
		DbPyt::query($query);		
	}

	public function alterTable( $id, $columns ) {
		$fields = $this->_fields;
		$prefix = $this->colPrefix;
		$query = '';
		// drop column
		foreach ($fields as $i => $field) {
			$name = $field->name;
			if (strpos($name, $prefix) === 0 && !in_array($name, $columns)) {
				$query .= 'DROP COLUMN `' . $name . '`,';
			}
		}
		// add column
		foreach ($columns as $i => $name) {
			if (!isset($fields[$name])) {
				$query .= 'ADD COLUMN `' . $name . '` text NOT NULL,';
			}
		}

		if (!empty($query)) {
			DbPyt::query('ALTER TABLE `' . $this->_table . '` ' . substr($query, 0, -1));
			$this->init(array('id' => $id, 'cols' => count($columns)));
		}
	}
}
