<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class DatabasesModelPyt extends ModelPyt {

	private $dbLink;
	private $dbName;


	public function connectToDatabase( $options = array() )	{
		global $wpdb;
		$dbLink = $wpdb;
		$dbName = UtilsPyt::getArrayValue($options, 'db_name', DB_NAME);
		$module = $this->getModule();
		if ($dbName == $module->dbExternalValue) {
			$dbExternal = UtilsPyt::getArrayValue($options, 'db_name_e', DB_NAME);
			if(!empty($dbExternal) && $dbExternal != DB_NAME) {
                $host = UtilsPyt::getArrayValue($options, 'db_host_e', DB_HOST);
				$login = UtilsPyt::getArrayValue($options, 'db_login_e');
				$password = htmlspecialchars_decode(UtilsPyt::getArrayValue($options, 'db_password_e'), ENT_QUOTES);
				$dbLink = new wpdb($login, $password, $dbExternal, $host);
				if(!empty($dbLink->error)) {
					FramePyt::_()->pushError(esc_html__('Error establishing a database connection', 'publish-your-table'));
					return false;
				}
				$dbName = $dbExternal;
			}
		} elseif ($dbName != DB_NAME) {
			$dbLink = new wpdb(DB_USER, DB_PASSWORD, $dbName, DB_HOST);
			if (!empty($dbLink->error)) {
				FramePyt::_()->pushError(esc_html__('Error establishing a database connection', 'publish-your-table'));
				return false;
			}
		}
		$this->dbLink = $dbLink;
		$this->dbName = $dbName;

		return true;
	}

	public function getDBNames() {
		$names = array();
		$exclusions = array('information_schema', 'mysql', 'performance_schema');//, DB_NAME);
		$result = DbPyt::get('SHOW DATABASES', 'all', ARRAY_N);
		foreach ($result as $name) {
			if (!in_array($name[0], $exclusions)) {
				$names[] = $name[0];
			}
		}
		return $names;
	}
	
	public function getDBTables() {
		if (empty($this->dbLink)) {
			$this->connectToDatabase();
		}
		$tables = array();
		$result = $this->dbLink->get_results('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE="BASE TABLE" AND TABLE_SCHEMA="' . $this->dbName . '"', ARRAY_N);
		if ($this->dbLink->last_error) {
			FramePyt::_()->pushError($this->dbLink->last_error);
			return false;
		}

		foreach ($result as $i => $table) {
			$tables[] = $table[0];
		}
		return $tables;
	}

	public function getTableFields( $table ) {
		if (empty($this->dbLink)) {
			$this->connectToDatabase();
		}

		$result = $this->dbLink->get_results('SHOW COLUMNS FROM ' . $this->dbName . '.' . $table, ARRAY_N);
		if ($this->dbLink->last_error) {
            FramePyt::_()->pushError($this->dbLink->last_error);
			return false;
        }

		$fields = array();
		foreach ($result as $i => $field) {
			$fields[] = $field[0];
		}
		return $fields;
	}

	public function prepareSQL( $sql ) {
		$sql = htmlspecialchars_decode($sql, ENT_QUOTES);

		$sql = preg_replace('/{sql.*?=(.*?)}/' , "'\\1'", $sql);
		$sql = preg_replace('/{sql.*?}/' , "'0'", $sql);

		return $sql;
	}

	public function getSQLColumns( $source ) {
		$table = UtilsPyt::getArrayValue($source, 'tbl_name');
		if (empty($this->dbLink)) {
			if (!$this->connectToDatabase($source)) {
				return false;
			}
		}
		$cols = array();

		if ($table == $this->getModule()->dbSQLValue) {
			$sql = UtilsPyt::getArrayValue($source, 'sql');
			if (!empty($sql)) {
				$sql .= ' LIMIT 1';
				$result = $this->dbLink->get_results($this->prepareSQL($sql), ARRAY_A);

				if ($this->dbLink->last_error) {
					FramePyt::_()->pushError($this->dbLink->last_error);
					return false;
				}
								
				if (count($result) > 0) {
					$row = $result[0];
					foreach ($row as $field => $cell) {
						$cols[] = $field;
					}
				}
			}
		} else {
			$fields = UtilsPyt::getArrayValue($source, 'tbl_fields', array());
			$tableFields = $this->getTableFields($table);
			if (empty($fields)) {
				$cols = $tableFields;
			} else {
				foreach ($fields as $field) {
					if (in_array($field, $tableFields)) {
						$cols[] = $field;
					}
				}
			}
		}
		
		return $cols;
	}

	public function getCellsData( $tableId, $source, $params ) {
		$connected = $this->connectToDatabase($source);
		if (!$connected) {
			return false;
		}
		$table = UtilsPyt::getArrayValue($source, 'tbl_name');

		$curPage = UtilsPyt::getArrayValue($params, 'curPage', 0);
		$perPage = UtilsPyt::getArrayValue($params, 'perPage', 0);
		if (empty($perPage)) {
			$perPage = 100;
		}

		$prefix = FramePyt::_()->getModule('tables')->getModel('cells')->getColPrefix();
		$isSql = $table == $this->getModule()->dbSQLValue;
		$total = 0;
		$data = array();
		$sql = '';

		if ($isSql) {
			$sql = $this->prepareSQL(UtilsPyt::getArrayValue($source, 'sql'));
			if (!empty($sql)) {
				$total = $this->dbLink->get_var('SELECT COUNT(*) FROM (' . $sql . ') as s');
			}
		} else {
			$fields = UtilsPyt::getArrayValue($source, 'tbl_fields', array());
			if (empty($fields)) {
				$fields = $this->getSQLColumns($source);
			}
			if (!empty($fields)) {
				$total = $this->dbLink->get_var( 'SELECT COUNT(*) FROM ' . $table );
				$sql = 'SELECT ';
				foreach($fields as $i => $field) {
					$sql .= $field . ' as ' . $prefix . ($i + 1) . ',';
				}
				$sql .= '0 as id FROM ' . $table;
			}
		}
		
		if (!empty($sql)) {
			if (!empty($perPage)) {
				$curPage = empty($curPage) ? 1 : $curPage;
				$perPage = empty($perPage) ? 100 : $perPage;

				$skip = ($perPage * ($curPage - 1));
				if ($skip >= $total) {
					$curPage = ceil( $total / $perPage );
					$skip = ( $perPage * ( $curPage - 1 ) );
				}
				$sql .= ' LIMIT ' . $skip . ',' . $perPage;
			}

			$rows = $this->dbLink->get_results($sql, $isSql ? ARRAY_N : ARRAY_A);
			if ($this->dbLink->last_error) {
				FramePyt::_()->pushError($this->dbLink->last_error);
				return false;
			}

			if (count($rows) > 0) {
				if ($isSql) {
					foreach ($rows as $i => $row) {
						$cells = array();
						foreach ($row as $j => $value) {
							$cells[$prefix . ($j + 1)] = $value;
						}
						$data[] = $cells;
					}
				} else {
					$data = $rows;
				}
			}
		}
		return array(
			'totalRecords' => $total,
			'curPage' => $curPage,
			'data' => $data,
			'fonts' => array()
		);

	}

	public function getRangeData( $settings, $range, $withTitles = true ) {

		$tableId = $settings['id'];
		$source = $settings['source'];

		$connected = $this->connectToDatabase($source);
		if (!$connected) {
			return false;
		}
		$ranges = isset($range['from']) ? array($range) : $range;
		
		//$range = $ranges[0];

		$minRow = -1;
		$maxRow = -1;
		$colRows = array();
		$table = UtilsPyt::getArrayValue($source, 'tbl_name');
		$isSql = $table == $this->getModule()->dbSQLValue;

		$data = array();
		$sql = '';
		$colTitles = array();
		$fields = $this->getSQLColumns($source);
		
		if ($isSql) {
			$sql = $this->prepareSQL(UtilsPyt::getArrayValue($source, 'sql'));
		} else {
			/*$fields = UtilsPyt::getArrayValue($source, 'tbl_fields', array());
			if (empty($fields)) {
				$fields = $this->getSQLColumns($source);
			}*/
			if (!empty($fields)) {
				$sql = 'SELECT ';
				foreach($fields as $i => $field) {
					$sql .= $field . ',';
				}
				$sql = substr($sql, 0, -1) . ' FROM ' . $table;
			}
		}
		if (empty($sql)) {
			return false;
		}

		foreach ($ranges as $range) {
			$from = UtilsPyt::getArrayValue($range, 'from', array());
			$to = UtilsPyt::getArrayValue($range, 'to', array());
			if (empty($to)) {
				$to = $from;
			}
			$fCol = UtilsPyt::getArrayValue($from, 'c', 1, 1);
			$tCol = UtilsPyt::getArrayValue($to, 'c', 1, 1);
			$fRow = UtilsPyt::getArrayValue($from, 'r', 0, 1);
			$tRow = UtilsPyt::getArrayValue($to, 'r', 0, 1);

			for ($c = $fCol - 1; $c < $tCol; $c++) {
				if (!isset($fields[$c])) {
					continue;
				}
				if (!isset($colRows[$c])) {
					$colRows[$c] = array($fRow, $tRow);
					$colTitles[] = array('name' => $fields[$c]);
				}
			}
			if (!empty($minRow)) {
				if (empty($fRow)) {
					$minRow = 0;
					$maxRow = 0;
				} else {
					if ($minRow < 0 || $fRow < $minRow) {
						$minRow = $fRow;
					}
					if ($maxRow < 0 || $tRow > $maxRow) {
						$maxRow = $tRow;
					}
				}
			}
		}
		if (!empty($minRow)) {
			$sql .= ' LIMIT ' . ($minRow - 1) . ',' . ($maxRow - $minRow + 1);
		}

		$rows = $this->dbLink->get_results($sql, ARRAY_N);
		if ($this->dbLink->last_error) {
			FramePyt::_()->pushError($this->dbLink->last_error);
			return false;
		}

		$cnt = count($rows);
		if ($cnt > 0) {
			if (empty($minRow)) {
				$cnt = count($rows);
				foreach ($colRows as $c => $col) {
					if (empty($col[0])) {
						$colRows[$c][1] = $cnt - 1;	
					} else {
						$colRows[$c][0]--;
						$colRows[$c][1]--;
					}
				}
			} else {
				foreach ($colRows as $c => $col) {
					$cnt = $colRows[$c][1] - $colRows[$c][0];
					$colRows[$c][0] -= $minRow;
					$colRows[$c][1] = $colRows[$c][0] + $cnt;
				}
			}
			$result = array();
			foreach ($rows as $r => $cols) {
				$result[$r] = array();
				foreach ($cols as $c => $value) {
					if (!empty($colRows[$c])) {
						$result[$r][] = $colRows[$c][0] > $r || $colRows[$c][1] < $r ? '' : $value;
					}
				}
			}
			return $withTitles ? array('values' => $result, 'titles' => $colTitles) : $result;
		}
		return false;


		/*$from = UtilsPyt::getArrayValue($range, 'from', array());
		$to = UtilsPyt::getArrayValue($range, 'to', array());
		$fCol = UtilsPyt::getArrayValue($from, 'c', 1, 1);
		$tCol = UtilsPyt::getArrayValue($to, 'c', 1, 1);
		$fRow = UtilsPyt::getArrayValue($from, 'r', 0, 1);
		$tRow = UtilsPyt::getArrayValue($to, 'r', 0, 1);

		$table = UtilsPyt::getArrayValue($source, 'tbl_name');
		$isSql = $table == $this->getModule()->dbSQLValue;

		$data = array();
		$sql = '';

		if ($isSql) {
			$sql = $this->prepareSQL(UtilsPyt::getArrayValue($source, 'sql'));
		} else {
			$columns = UtilsPyt::getArrayValue($source, 'tbl_fields', array());
			if (empty($columns)) {
				$columns = $this->getSQLColumns($source);
			}
			$fields = array();
			if (!empty($columns)) {
				for ($c = $fCol - 1; $c < $tCol; $c++) {
					if (!isset($columns[$c])) {
						break;
					}
					$fields[] = $columns[$c];
				}
				if (empty($fields)) {
					return array();
				}
				$sql = 'SELECT ' . implode(',', $fields) . ' FROM ' . $table;
			}
		}

		if (!empty($fRow)) {
			$sql .= ' LIMIT ' . ($fRow - 1) . ',' . ($tRow - $fRow + 1);
		}

		$rows = $this->dbLink->get_results($sql, ARRAY_N);
		if ($this->dbLink->last_error) {
			FramePyt::_()->pushError($this->dbLink->last_error);
			return false;
		}
		if ($isSql) {
			$cnt = count($rows);
			if ($cnt > 0) {
				if ($fCol > 1 || count($rows[0]) > $tCol) {
					$result = array();
					$fCol--;
					for ($r = 0; $r < $cnt; $r++) {
						$result[$r] = array();
						for ($c = $fCol; $c < $tCol; $c++) {
							$result[$r][] = $rows[$r][$c];
						}
					}
					return $result;
				}
			}
		}
		return $rows;*/
	}

	public function getFrontRows($settings, $params, $cols, $headClasses) {
		//$source = UtilsPyt::getArrayValue(UtilsPyt::getArrayValue($settings, 'source', array()), 'source', array());
		$source = UtilsPyt::getArrayValue($settings, 'source', array());
		$connected = $this->connectToDatabase($source);
		if (!$connected) {
			return false;
		}

		$length = UtilsPyt::getArrayValue($params, 'length', 0, 1);
		if ($length < 0) {
			$length = 0;
		}
		$start = UtilsPyt::getArrayValue($params, 'start', 0, 1);
		$isPreview = UtilsPyt::getArrayValue($params, 'isPreview', false);
		
		$isSSP = UtilsPyt::getArrayValue($params, 'isSSP', false);
		$isPage = UtilsPyt::getArrayValue($params, 'isPage', false);

		$prefix = FramePyt::_()->getModule('tables')->getModel('cells')->getColPrefix();

		$table = UtilsPyt::getArrayValue($source, 'tbl_name');
		$isSql = $table == $this->getModule()->dbSQLValue;
		$total = 0;
		$data = array();
		$query = '';

		$options = UtilsPyt::getArrayValue($settings, 'options', array());
		$onlyIds = false;
		$uniq = false;
		$uniqFields = array();
		$uniqFirst = 0;
		$fields = array();
		
		if ($isSql) {
			$query = UtilsPyt::getArrayValue($source, 'sql');
			
			$scAttributes = UtilsPyt::getArrayValue($params, 'scAttributes', array(), 2);
			foreach ($scAttributes as $key => $value) {
				if (stripos($key, 'sql') === 0) {
					$query = preg_replace('/{' . $key . '.*?}/' , "'" . $value . "'", $query);
				}
			}

			$query = $this->prepareSQL($query);

			if (!empty($query)) {
				$total = $this->dbLink->get_var('SELECT COUNT(*) FROM (' . $query . ') as s');
			}
			if ($isSSP && $isPage) {
				$fields = $this->getSQLColumns($source);
				$select = 'SELECT * ';
				$from = ' FROM (' . $query . ') as cc WHERE 1=1';
				$query = $select . $from;
			}

		} else {
			$fields = UtilsPyt::getArrayValue($source, 'tbl_fields', array());
			if (empty($fields)) {
				$fields = $this->getSQLColumns($source);
			}
			if (!empty($fields)) {
				$uniq = UtilsPyt::getArrayValue($source, 'tbl_uniq', array());

				if (!empty($uniq) && is_array($uniq) && count($uniq) == 1) {
					$onlyIds = UtilsPyt::getArrayValue($params, 'onlyIds', false);
				}

				if (UtilsPyt::getArrayValue($options, 'efields_save') == 1) {
					$uniqFirst = count($fields);
					foreach ($uniq as $key => $field) {
						$u = array_search($field, $fields);
						if ($u === false) {
							$u = count($fields);
							$fields[] = $field;
						}
						$uniqFields[] = $u;
						
					}					
				}

				$total = $this->dbLink->get_var( 'SELECT COUNT(*) FROM ' . $table );
				$select = 'SELECT ' . implode(',', $fields);
				$from = ' FROM ' . $table . ' WHERE 1=1';
				$query = $select . $from;
			}
		}

		if (!empty($fields)) {
			$colFields = array();
			$i = 0;
			foreach ($cols as $key => $d) {
				if (!isset($fields[$i])) {
					break;
				}
				$colFields[$key] = $fields[$i];
				$i++;
			}
		}

		$filtered = $total;
		
		$clName = 'pyt-style-';
		$order = '';

		if ($isSSP) {
			if ($isPage) {
				if ($uniq && !empty($params['footerIds']) && is_array($params['footerIds'])) {
					$cntUniq = count($uniq);

					$exclude = '';
					$footer = 0;

					if ($cntUniq > 1) {
						foreach ($params['footerIds'] as $footerUniq) {
							if ($cntUniq == count($footerUniq)) {
								$exclude .= '(';
								foreach ($footerUniq as $n => $u) {
									$exclude .= $uniq[$n] . '="' . $u .'" AND ';
								}
								$exclude = substr($exclude, 0, -5) . ') OR ';
								$footer++;
							}
						}
						if (!empty($exclude)) {
							$exclude = ' NOT (' . substr($exclude, 0, -4) . ')';
						}
					} else {
						foreach ($params['footerIds'] as $footerUniq) {
							$exclude .= '"' . $footerUniq[0] . '",';
							$footer++;
						}
						if (!empty($exclude)) {
							$exclude = $uniq[0] . ' NOT IN (' . substr($exclude, 0, -1) . ')';
						}

					}
					if (!empty($exclude)) {
						$from .= ' AND ' . $exclude;
						$total -= $footer;
					}
				}
				if ($onlyIds) {
					$from .= ' AND ' . $uniq[0] . ' IN(' . $onlyIds . ')';
					$order = ' ORDER BY FIELD(' . $uniq[0] . ',' . $onlyIds . ')';
				}

				$search = UtilsPyt::getArrayValue($params, 'search', array());
				$calcFiltered = false;
				if (!empty($search)) {
					$searchValue = UtilsPyt::getArrayValue($search, 'value');
					if (!empty($searchValue)) {
						$where = '';
						foreach ($settings['columns'] as $col) {
							if (isset($colFields[$col['dataIndx']])) {
								$where .= $colFields[$col['dataIndx']] . ' LIKE "%' . esc_sql($searchValue) . '%" OR ';
							}
						}
						if (!empty($where)) {
							$from .= ' AND (' . substr($where, 0, -4) . ')';
							$calcFiltered = true;
							
						}
					}
				}
				$colSearch = UtilsPyt::getArrayValue($params, 'columns', array(), 2);
				if (!empty($colSearch)) {
					$where = '';
					foreach ($colSearch as $sData) {
						$search = UtilsPyt::getArrayValue($sData, 'search', array());
						if (!empty($search)) {
							$searchValue = UtilsPyt::getArrayValue($search, 'value');
							if (!empty($searchValue) && isset($sData['data']) && isset($colFields[$sData['data']])) {
								$where .= ' AND ' . $colFields[$sData['data']] . ' LIKE "%' . esc_sql($searchValue) . '%"';
							}
						}
					}
					if (!empty($where)) {
						$from .= $where;
						$calcFiltered = true;
					}
				}

				if ($calcFiltered) {
					$filtered = $this->dbLink->get_var('SELECT COUNT(*) AS total' . $from);
				}
				$query = $select . $from;

				$colNames = UtilsPyt::getArrayValue($params, 'colNames', array(), 2);
				$colOrder = UtilsPyt::getArrayValue($params, 'order', array(), 2);
				if (!empty($colOrder)) {
					$newOrder = '';

					foreach ($colOrder as $oData) {
						$colNum = UtilsPyt::getArrayValue($oData, 'column', -1, 1, false, true);
						if ($colNum >= 0 && isset($colNames[$colNum]) && isset($colFields[$colNames[$colNum]])) {
							$newOrder .= $colFields[$colNames[$colNum]] . (UtilsPyt::getArrayValue($oData, 'dir', 'asc') == 'desc' ? ' DESC' : '') . ',';
						}
					}
					if (!empty($newOrder)) {
						$order = ' ORDER BY ' . substr($newOrder, 0, -1);
					}
				}

				$query .= $order . ( empty($length) ? '' : ' LIMIT ' . $start . ',' . $length );	
			} else {
				$clName = 'pyt-hstyle-';
				$header = UtilsPyt::getArrayValue($options, 'header', false) == 1 ? UtilsPyt::getArrayValue($options, 'header_rows', 0) : 0;
				$footer = $uniq && UtilsPyt::getArrayValue($options, 'footer', false) == 1 && UtilsPyt::getArrayValue($options, 'custom_footer', false) == 1 ? UtilsPyt::getArrayValue($options, 'footer_rows', 0) : 0;
				$union = '';

				if ($header) {
					$union = $query . $order . ' LIMIT 0,' . $header;	
				}
				if ($footer) {
					if ($header) {
						$union = '(' . $union . ') UNION ALL (' . $query . $order . ' DESC LIMIT 0,' . $footer . ')';
					} else {
						$union = $query . $order . ' LIMIT ' . ($total - $footer) . ',' . $footer;
					}
					$total -= $footer;
				}
				$query = $union;
			}
		} else {
			$query .= ' LIMIT 100 ';
		}
		
		$data = array();
		$r = 0;
		$attrs = array();
		$classes = array();
		$withUniq = !empty($uniqFields);

		if (!empty($query)) {
			$rows = $this->dbLink->get_results($query, ARRAY_N);
			if ($this->dbLink->last_error) {
				FramePyt::_()->pushError($this->dbLink->last_error);
				return false;
			}

			$defData = $isPage && UtilsPyt::getArrayValue($options, 'auto_index') == 'new' ? array('auto' => '') : array();
			foreach ($rows as $row) {
				$d = $defData;
				$c = array();
				$а = array();
				foreach ($row as $i => $value) {
					if ($withUniq && $i >= $uniqFirst) {
						continue;
					}
					$name = $prefix . ($i + 1);
					$colData = isset($cols[$name]) ? $cols[$name] : array();
					$styles = UtilsPyt::getArrayValue($colData, 'style', array());
					$class = '';
					if (!empty($styles)) {
						foreach ($classes as $cl => $clData) {
							if ($clData == $styles) {
								$class = $cl;
								break;
							}
						}
						if (empty($class)) {
							$r++;
							$class = $clName . $r;
							$classes[$class] = $styles;
						}
					}
					$а[$name]['value'] = $value;
					$d[$name] = $value;
					if (!empty($class)) {
						$c[$name] = $class;
					}
				}
				$data[] = $d;

				if ($withUniq) {
					$id = array();
					foreach ($uniqFields as $i) {
						$id[] = $row[$i];
					}
				} else {
					$id = 0;
				}

				$rowA = array('id' => $isPage ? json_encode($id) : $id, 'i' => 0, 'h' => false, 'f' => 0);
				if (!empty($c)) {
					$rowA['c'] = $c;
				}
				if (!empty($а)) {
					$rowA['a'] = $а;
				}
				$attrs[] = $rowA;
			}
		}
		return array(
			'total' => $total,
			'filtered' => ($filtered > $total ? $total : $filtered),
			'rows' => $data,
			'attrs' => $attrs,
			'cols' => $cols,
			'classes' => array_merge($headClasses, $classes),
		);
	}

	public function saveCellsValues( $settings, $cells ) {
		$columns = UtilsPyt::getArrayValue($settings, 'columns', array(), 2);
		$source = UtilsPyt::getArrayValue($settings, 'source', array(), 2);
		$uniq = UtilsPyt::getArrayValue($source, 'tbl_uniq', array(), 2);
		if (empty($uniq)) {
			FramePyt::_()->pushError(esc_html__('Unable to save: no unique fields set.', 'publish-your-table'));
			return false;
		}
		$table = UtilsPyt::getArrayValue($source, 'tbl_name');
		if ($table == $this->getModule()->dbSQLValue) {
			FramePyt::_()->pushError(esc_html__('Unable to save: no unique fields set for query.', 'publish-your-table'));
			return false;
		}
		if (!$this->connectToDatabase($source)) {
			return false;
		}
		
		$update = 'UPDATE ' . $table . ' SET ';
		$uCount = count($uniq);

		foreach($cells as $cell) {
			$col = $cell['col'];
			$data = array();
			$where = array();
			$colData = UtilsPyt::getArrayValue($columns, $col, array(), 2);

			if (isset($colData['prop']['pyt']['dbField'])) {
				$data[$colData['prop']['pyt']['dbField']] = $cell['ov'];
			} 
			$id = UtilsPyt::jsonDecode($cell['row']);

			if (!empty($id) && is_array($id) && count($id) == $uCount) {
				foreach ($id as $i => $value) {
					$where[$uniq[$i]] = $value;
				}
			} 
			if (!empty($data) && !empty($where)) {
				$result = $this->dbLink->update($table, $data, $where);

				if (!$result && $this->dbLink->last_error) {
					FramePyt::_()->pushError($this->dbLink->last_error);
					return false;
				}
			}
		}
		return true;
	}
}
