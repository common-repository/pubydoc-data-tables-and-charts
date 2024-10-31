<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Export_sql_ModelPyt extends ModelPyt {
	private $delim = '----------------------------------';
	
	public function export( $ids, $mode ) {
		// Begin export
		if (ob_get_contents()) {
			ob_end_clean();
		}
		$isDiagram = ('diagrams' == $mode);
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . ( $isDiagram ? 'Chart' : 'DataTable' ) . 'Migration.sql"');
		if (ob_get_contents()) {
			ob_end_clean();
		}

		if ($isDiagram) {
			$sql = $this->exportDiagrams($ids);
		} else {
			$sql = $this->exportTables($ids);
		}

		die();
    }

    public function exportTables( $ids ) {
		$tables = array('diagrams');
		$delimEOL = PHP_EOL . $this->delim . PHP_EOL;

		foreach ($ids as $i => $id) {
			$table = FramePyt::_()->getTable('tables');

			$tableName = $table->getTable();
			$fields = $this->selectFields($tableName);

			print 'INSERT INTO `' . $tableName . '` ('. $fields .')' . PHP_EOL .
				'VALUES' . $this->selectValues($tableName, $fields, 'id', $id) . ';' . $delimEOL .
				'SET @table_id = (SELECT last_insert_id());' . $delimEOL;

			$fieldWhere = 'table_id';
			$limit = 400;
			foreach ($tables as $t => $table) {
				$tableName = '@__' . $table;
				if (DbPyt::exist($tableName)) {
				
					$countRows = $this->getCountRows($tableName, $fieldWhere, $id);
					if ($countRows > 0) {
						$fields = $this->selectFields($tableName);
						$offset = 0;
						do {
							$values = $this->selectValues($tableName, $fields, $fieldWhere, $id, '@table_id,', $limit, $offset);
							if (!empty($values)) {
								print 'INSERT INTO `' . $tableName . '` (`table_id`,' . $fields . ')' . PHP_EOL . 'VALUES' . $values . ';' . $delimEOL;
							}
							$offset += $limit;
						} while ($offset < $countRows);
					}
				}
			}
			print PHP_EOL;

			$tableName = '@__cells' . $id;

			if (DbPyt::exist($tableName)) {
				$result = DbPyt::get('SHOW CREATE TABLE ' . $tableName, 'row');
				if ($result && !empty($result['Create Table'])) {
					print 'DROP TABLE IF EXISTS `@__cells_temp`;' . $delimEOL;
					$create = $result['Create Table'];
					$pos = strpos($create, '(');
					print 'CREATE TABLE `@__cells_temp` ' . substr($result['Create Table'], $pos) . $delimEOL;

					$countRows = $this->getCountRows($tableName);
					if ($countRows > 0) {
						$fields = $this->selectFields($tableName, '');
						$offset = 0;
						do {
							$values = $this->selectValues($tableName, $fields, '', 0, '', $limit, $offset);
							if (!empty($values)) {
								print 'INSERT INTO `@__cells_temp` (' . $fields . ')' . PHP_EOL . 'VALUES' . $values . ';' . $delimEOL;
							}
							$offset += $limit;
						} while ($offset < $countRows);
					}

					print 'SET @sql_text = concat("RENAME TABLE `@__cells_temp` TO `@__cells",@table_id,"`");' . $delimEOL;
					print 'PREPARE stmt FROM @sql_text;' . $delimEOL;
					print 'EXECUTE stmt;' . $delimEOL;
					print 'DEALLOCATE PREPARE stmt;' . $delimEOL;
				}
			}
			print PHP_EOL;
		}
	}

	public function exportDiagrams( $ids ) {
		$delimEOL = PHP_EOL . $this->delim . PHP_EOL;

		$tableName = '@__diagrams';

		if (DbPyt::exist($tableName)) {

			foreach ($ids as $i => $id) {
				$fields = $this->selectFields($tableName, array('id', 'table_id'));

				print 'INSERT INTO `' . $tableName . '` ('. $fields .')' . PHP_EOL .
					'VALUES' . $this->selectValues($tableName, $fields, 'id', $id) . ';' . $delimEOL;
			}

			print PHP_EOL;
		}
	}

	private function selectValues( $table, $fields, $where, $id, $pre = '', $limit = 0, $offset = 0 ) {
		$query = 'SELECT ' . $fields . ' FROM `' . $table . '`';
		if (!empty($where)) {
			$query .= ' WHERE ' . $where . '=' . $id;
		}
		if ($limit > 0) {
			$query .= ' ORDER BY id LIMIT ' . $offset . ', ' . $limit;
		}
		$results = DbPyt::get($query, 'all', ARRAY_N);

		if (count($results) == 0) {
			return '';
		}

		$values = '';
		foreach ($results as $i => $row) {
			$value = $pre;
			foreach($row as $f => $val) {
				$value .= "'" . esc_sql($val) . "',";
			}
			$values .= '(' . substr($value, 0, -1) . '),';
		}
		return substr($values, 0, -1);
	}

	private function getCountRows( $table, $where = '', $id = 0 ) {
		$query = 'SELECT count(*) FROM `' . $table . '`';
		if (!empty($where)) {
			$query .= ' WHERE ' . $where . '=' . $id;
		}
		return DbPyt::get($query, 'one');
	}

	private function selectFields( $table, $exclude = array('id', 'table_id') ) {
		$columns = DbPyt::get('SHOW COLUMNS FROM `' . $table . '`');
		$fields = '';
		$full = empty($exclude);
		foreach ($columns as $col) {
			$name = $col['Field'];
			if ($full || !in_array($name, $exclude)) {
				$fields .= '`' . $name . '`,';
			}
		}
		return substr($fields, 0, -1);
	}
}
