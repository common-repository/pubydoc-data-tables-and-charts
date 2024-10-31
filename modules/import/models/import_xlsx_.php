<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
FramePyt::_()->getModule('import')->getModel('import');
class Import_xlsx_ModelPyt extends ImportModelPyt {

	public function import( $fileName, $extension ) {
		$inputFileType = $this->convertExtension($extension);

		$savedPrecision = ini_get('precision');

		$result = array();
		$props = array();
		$allProps = array();
		$columnsWidth = array();
		$mergedCells = array();
		$conditions = array();
		$condParams = FramePyt::_()->getModule('tablespro')->getConditionsParamsExcel();
		$rules = array();
		$order = array();

		$needCached = (filesize($fileName) > 2000000);
		if ($needCached) {
			set_time_limit(1000);
			\PhpOffice\PhpSpreadsheet\Settings::setCacheStorageMethod(\PhpOffice\PhpSpreadsheet\CachedObjectStorageFactory::cache_to_sqlite3);
		}
		$startRow = 1;
		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
		
		$chunkSize = 500;
		$chunkFilter = new ChunkReadFilter();
		$totalRows = 0;
		$totalColumns = 0;
		$rowNumber = 0;
		$lastRow = 0;
		$lastColumn = 0;
		$sheetInfo = $reader->listWorksheetInfo($fileName);
		$sheets = array();
		$maxColumns = 0;
		foreach($sheetInfo as $i => $data) {
			$sheets[$data['worksheetName']] = $data['totalRows'];
			if ($maxColumns < $data['totalColumns']) {
				$maxColumns = $data['totalColumns'];
			}
		}

		$rawData = $this->settings['raw'];
		$visibleData = $this->settings['visibleData'];
		$saved = false;
		ini_set('precision', $savedPrecision);

		$this->settings['cols'] = $maxColumns;

		$cellModel = FramePyt::_()->getModule('tables')->getModel('cells');
		$prefix = $cellModel->getColPrefix();
		$newTable = empty($this->settings['tableId']);

		$newModel = $this->settings['remove'] || $newTable;
		$lastId = 0;
		$lastOrder = 0;
		$lastTableRow = 0;
		$lastRule = 0;
		$maxRowId = 0;
		if ($newModel) {
			$colModel = $cellModel->createColModel($maxColumns);
		} else {
			$tableId = $this->settings['tableId'];
			list($lastTableRow, $lastId, $lastOrder, $lastRule) = FramePyt::_()->getModule('tablespro')->getModel('cellspro')->getMaxRowsData($tableId);
			$maxRowId = $lastId;
		}
		$withHeader = $this->settings['remove'] && $this->settings['header'];

		$defaultRow = array();
		for ($i = 1; $i <= $maxColumns; $i++) {
			$defaultRow[$prefix . $i] = '';
		}
		$firstRow = true;
		$globalProps = array();

		do {
			if ($startRow > 1) {
				$list = array('addList' => $result, 'orderList' => $order);
				if (!empty($props)) {
					$list['propList'] = $props;
				}
				$forSave = array('list' => $list);
				if (!$saved && $newModel) {
					$forSave['colModel'] = $colModel;
				}

				$this->updateTableData($forSave, $this->settings['remove']);
				unset($result, $workbook, $sheet, $rows);
				gc_collect_cycles();
				$result = array();
				$order = array();
				$allProps = array_merge($allProps, $props);
				$props = array();
				$this->settings['remove'] = false;
				$saved = true;
			}
			$chunkFilter->setRows($startRow, $chunkSize);
			$reader->setReadFilter($chunkFilter);

			$reader->setReadDataOnly($rawData);
			if($visibleData){
				$reader->setReadDataOnly(false);
			}

			$workbook = $reader->load($fileName);
			$sheet = $workbook->getActiveSheet();

			$importImage = !empty($this->settings['uniq']) || $newTable;
			if ($importImage) {
				$imgCoordinates = array();
				foreach ($sheet->getDrawingCollection() as $drawing) {
					$string = $drawing->getCoordinates();
					$coordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($string);
					if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\Drawing) {
						$imgCoordinates[$string] = $this->media_sideload_image($drawing);
					}
				}
				if(sizeof($imgCoordinates) == 0) $importImage = false;
			}

			$highestRows = $sheets[$sheet->getTitle()];
			$stop = $startRow + $chunkSize - 1;

			$rows = $sheet->getRowIterator($startRow, $stop < $highestRows ? $stop : $highestRows);
			$mergedData = $sheet->getMergeCells();
			$columnNumber = 0;

			if (!empty($mergedData)) {	// find data for merge
				foreach($mergedData as $md) {
					$merge = array();
					$range = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::splitRange($md);
					$range = !empty($range[0]) ? $range[0] : $range;
					if (!empty($range[0]) && !empty($range[1])) {
						$startCoords = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($range[0]);	// start cell
						$endCoords = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($range[1]);	// end cell
						$startColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($startCoords[0]);
						$endColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($endCoords[0]);

						$merge['r1'] = ((int)$startCoords[1]) - ($withHeader ? 2 : 1) + $lastTableRow;
						$merge['c1'] = $startColIndex;
						$merge['rc'] = $endCoords[1] - $startCoords[1] + 1;
						$merge['cc'] = $endColIndex - $startColIndex + 1;
						$merge['id'] = $merge['r1'] + 1 - $lastTableRow + $lastId;
						$merge['col'] = $prefix . $startColIndex;
					}
					array_push($mergedCells, $merge);
				}
			}

			foreach ($rows as $row) {
				$rowId = ($rowNumber + 1) + $lastId;
				$cellsO = $defaultRow;
				$prop = array();

				$height = $sheet->getRowDimension($row->getRowIndex())->getRowHeight();
				if ($height !== -1) {
					$prop['pq_ht'] = round($height * 1.333333); // pt to px
					$prop['pq_htfix'] = 1;
				}

				$cells = $row->getCellIterator();
				$cells->setIterateOnlyExistingCells(false);

				$isCurrentRowVisibility = $sheet->getRowDimension($rowNumber + 1)->getVisible();
				if (!$isCurrentRowVisibility) {
					$prop['pq_rowprop']['pytVisible'] = 'invis';
				}

				$isHeader = $newModel && $firstRow && $withHeader;

				foreach ($cells as $cell) {
					$col = $prefix . ($columnNumber + 1);
					$columnNameChars = $cell->getColumn();
					$columnDimension = $sheet->getColumnDimension($columnNameChars);
					$width = (int) $columnDimension->getWidth();
					$width = $width !== -1 ? round($width * 5.5) : 0;
					if ($newModel) {
						if (!isset($colModel[$col])) {
							if ($columnNumber >= $maxColumns) {
								$newMax = $columnNumber + 1;
								for ($c = $maxColumns + 1; $c <= $newMax; $c++) {
									$name = $prefix . $c;
									$colModel[$name] = array_merge($cellModel->getDefaultColModel(), array('dataIndx' => $name, 'nameIndx' => $c));
									$colModel[$name]['prop']['pyt']['title'] = __('Column', 'publish-your-table') . ' ' . $c;
								}
								$maxColumns = $newMax;
							}
						}

						if (!empty($width)) {
							$colModel[$col]['width'] = strval(round($width));
						}
					}

					$data = $cell->getValue() === null ? '' : $cell->getValue();
					//get values as visible in excel. setReadDataOnly must be set "bool" false
					if ($visibleData || $isHeader) {
						$data = (string) $cell->getFormattedValue();
					}

					$calculatedValue = '';
					$cellStyle = $cell->getStyle();
					$cellFormat = $cellStyle->getNumberFormat()->getFormatCode();
					$formatType = '';
					$format = '';

					if ($importImage) {
						$cellStrCoordinate = $cell->getCoordinate();
						if (isset($imgCoordinates[$cellStrCoordinate])) {
							$data = $imgCoordinates[$cellStrCoordinate];
						}
					}
					if (!$visibleData && !$isHeader) {
						// Check format
						if (preg_match('/%/', $cellFormat)) {
							$formatType = 'percent';
						} else if (preg_match('/[\$€£₴₽]+/Uu', $cellFormat)) {
							$formatType = 'money';
						} else if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell) && is_numeric($cell->getValue())) {
							$formatType = 'date';
							$format = '';
							$dataObject = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cell->getValue());
							$data = date_format($dataObject, 'Y-m-d');
						} 
						if (!empty($formatType)) {
							$prop['pq_cellprop'][$col]['pytType'] = array('type' => $formatType, 'format' => $format);
						}
					}

					// Check formulas and images

					if ($cell->isFormula()) {
						$formula = $cell->getValue();
						if ($isHeader) {
							$data = preg_match('/!/', $formula) ? $cell->getOldCalculatedValue() : $cell->getCalculatedValue();
						} else {
							if (preg_match('/!/', $formula)) { // cell has link to another sheet
								$data = $cell->getOldCalculatedValue();
							} elseif (strpos($formula, '=') === 0) {
								$formula = substr($formula, 1);
								$prop['pq_fn'][$col] = array('fn' => $formula, 'fnOrig' => $formula);
								$data = $cell->getCalculatedValue();
							}

							if (strtoupper(substr($cell->getValue(), 0, 6)) == '=IMAGE') {
								if (preg_match('/.*?(http.*?)["\']{1}/', $cell->getValue(), $match)) {
									$imgSrc = $match[1];
									$width = '';
									$size = getimagesize($imgSrc);
									if ($size AND $size[0]) {
										$width = $size[0];
									}
									$data = "<img width='{$width}' src='{$imgSrc}'>";
								}
							}
						}
					} 

					// Check links
					if ($cell->hasHyperlink() && !$isHeader) {
						if(!$cell->isFormula()) {
							$url = $cell->getHyperlink()->getUrl();

							if (preg_match('/mailto:([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}).*"(.*)"/i', $data, $match)) {
								if (!empty($match[2])) {
									$data = $match[2];
								} else if (!empty($match[1])) {
									$data = $match[1];
								}
							} else if (preg_match_all('/[\'|"](.*)[\'|"]/iU', $data, $match)) {
								if (!empty($match[1]) && !empty($match[1][1])) {
									$data = $match[1][1];
								} else {
									$data = $url;
								}
							}

							$data = '<a href=\"' . $url . '\" target=\"_blank\">' . $data . '</a>';
						}
					}

					if ($newModel && $firstRow && !$columnDimension->getVisible()) {
						$colModel[$col]['prop']['pyt']['pytVisible'] = 'invis';
					}

					// Set correct empty value for cell - let it be here after check all variations to get cell data
					if ($data) {
						$lastColumn = max($lastColumn, $columnNumber);
						$lastRow = $totalRows;
					}

					// Cell formatting
					$cellAlignment = $cellStyle->getAlignment();
					$horizontalAlignment = $cellAlignment->getHorizontal();
					if ($horizontalAlignment !== 'general') {
						$prop['pq_cellstyle'][$col]['text-align'] = $horizontalAlignment;
					}

					$verticalAlignment = $cellAlignment->getVertical();
					$verticalAlignment = $verticalAlignment == 'center' ? 'middle' : $verticalAlignment;
					if ($verticalAlignment !== 'general') {
						$prop['pq_cellprop'][$col]['pytValign'] = $verticalAlignment;
					}

					$cellFill = $cellStyle->getFill();
					$cellFont = $cellStyle->getFont();
					$bgColor = $this->getColor($cellFill, 'background');
					$fontColor = $this->getColor($cellFont);
					
					if ($cellFill->getFillType() !== 'none') {
						$prop['pq_cellstyle'][$col]['background-color'] = '#' . strtolower($bgColor);
					}
					if ($fontColor != '000000') {
						$prop['pq_cellstyle'][$col]['color'] = '#' . strtolower($fontColor);
					}
					if ($cellFont->getSize()) {
						$prop['pq_cellstyle'][$col]['font-size'] = $cellFont->getSize() . 'px';
					}
					if ($cellFont->getName()) {
						$prop['pq_cellstyle'][$col]['font-family'] = $cellFont->getName();
					}
					if ($cellFont->getBold()) {
						$prop['pq_cellstyle'][$col]['font-weight'] = 'bold';
					}
					if ($cellFont->getItalic()) {
						$prop['pq_cellstyle'][$col]['font-style'] = 'italic';
					}
					if ($cellFont->getUnderline() != 'none') {
						$prop['pq_cellstyle'][$col]['text-decoration'] = 'underline';
					}

					// check dropdown list
					$source = array();
					$dataValidation = $cell->getDataValidation();
					if ($dataValidation->getShowDropDown() && $dataValidation->getType() == 'list') {

						$list = $dataValidation->getFormula1();
						$source = array();
						if (strpos($list, '$') === false) {
							if (count($source) <= 1) {
								foreach ($workbook->getNamedRanges() as $range) {
    								if ($range->getName() == $list) {
    									$list = $range->getValue();
    									break;
    								}
								}
							}
						}


						if (strpos($list, '$') === false) {
							$source = explode(',', str_replace('"', '', $list));
						} else {
							$listSheet = $sheet;
							$isSheet = strpos($list, '!');
							if ($isSheet) {
								$listCells = substr($list, $isSheet + 1);
								$sheetTitle = substr($list, 0, $isSheet);
								if ($sheet->getTitle() != $sheetTitle) {
									$listSheet = $workbook->getSheetByName($sheetTitle);
								}
							} else {
								$listCells = $list;
							}
							try {
		
								$sourceData = $listSheet->rangeToArray(str_replace('$', '', $listCells), '', false, false);
								foreach ($sourceData as $srcRow) {
									foreach ($srcRow as $srcCell) {
										if ($srcCell !== '') {
											array_push($source, str_replace('"', '', $srcCell));
										}
									}
								}
							} catch (\PhpOffice\PhpSpreadsheet\Exception $e)  {
								array_push($source, 'error');
							}
						}
						$prop['pq_cellprop'][$col]['pytType'] = array('type' => 'select', 'format' => implode('\n', $source));
					}
					if ($isHeader) {
						if (isset($colModel[$col])) {
							$colModel[$col]['prop']['pyt']['title'] = $data;
							if (isset($prop['pq_cellstyle'][$col])) {
								$colModel[$col]['prop']['pyt']['styleHead'] = $prop['pq_cellstyle'][$col];
							}
							if (isset($prop['pq_cellprop'][$col])) {
								foreach ($prop['pq_cellprop'][$col] as $key => $value) {
									$colModel[$col]['prop']['pyt'][$key] = $value;
								}
							}
						}
					} else {
						$cellsO[$col] = $data;
					}
					$columnNumber++;
				}
				if (!$isHeader) {
					$cellsO['id'] = $rowId;
					$order[$lastOrder] = $rowId;
					$lastOrder++;
					$result[$rowNumber] = $cellsO;
					if (!empty($prop)) {
						$props['id_' . $rowId] = $prop;
					}
					if ($newModel) {
						if ($firstRow) {
							if (isset($prop['pq_cellstyle'])) {
								$globalProps['pq_cellstyle'] = $prop['pq_cellstyle'];
							}
							if (isset($prop['pq_cellprop'])) {
								$globalProps['pq_cellprop'] = $prop['pq_cellprop'];
							}
						} else {
							foreach ($globalProps as $b => $block) {
								if (!isset($prop[$b])) {
									unset($globalProps[$b]);
								} else {
									foreach ($block as $col => $params) {
										if (!isset($prop[$b][$col])) {
											unset($globalProps[$b][$col]);
										} else {
											foreach ($params as $key => $value) {
												if (!isset($prop[$b][$col][$key]) || $prop[$b][$col][$key] != $value) {
													unset($globalProps[$b][$col][$key]);
												}
											}
										}
									}
								}
							}
						}
					}
					$rowNumber++;
					$totalRows++;
				}
				$totalColumns = max($totalColumns, $columnNumber);
				$columnNumber = 0;
				$firstRow = false;
			}

			// Check conditional formatting
			if (!empty($sheet->getConditionalStylesCollection())) {
				foreach ($sheet->getConditionalStylesCollection() as $range => $conditional) {
					foreach ($conditional as $p => $condition) {
						$condStyle = $condition->getStyle();
						$condFill = $condStyle->getFill();
						$condFont = $condStyle->getFont();
						$condConditions = array_map(
							function ($n) {
								preg_match('/^"(.*)"$/', $n, $matches);
								return !empty($matches) ? $matches[1] : $n;
							},
							$condition->getConditions()
						);
						array_filter($condConditions);
						$styles = array();
						$color = $this->getColor($condFill, 'condition_background');
						if (!empty($color)) {
							$styles['background-color'] = '#' . $color;
						}
						$color = $this->getColor($condFont);
						if (!empty($color)) {
							$styles['color'] = '#' . $color;
						}
						if ($condFont->getBold()) {
							$styles['font-weight'] = 'bold';
						}
						if ($condFont->getItalic()) {
							$styles['font-style'] = 'italic';
						}
						if ($condFont->getUnderline()) {
							$styles['text-decoration'] = 'underline';
						}
						if (empty($styles)) {
							continue;
						}
						$lastRule++;
						$ruleName = 'rule' . $lastRule;
						$text = $condition->getText();
						$value = $condConditions;

						$conditions[$ruleName] = array(
							'styles' => $styles,
							'type' => UtilsPyt::getArrayValue($condParams['types'], $condition->getConditionType(), 'cell'),
							'oper' => UtilsPyt::getArrayValue($condParams['opers'], $condition->getOperatorType(), 'equals'),
							'value' => empty($text) ? $value[0] : $text,
							'styles' => $styles
						);
						if (count($value) > 1) {
							$conditions[$ruleName]['value2'] = $value[1];
						}
						$rules[$ruleName] = $range;
					}
				}
			}

			$startRow += $chunkSize;
		} while ($startRow <= $highestRows);

		$reader = null;
		$chunkFilter = null;
		unset($reader, $chunkFilter);
		gc_collect_cycles();

		$lastRow++; // +1 to include the last row
		$lastColumn++; // +1 to include the last column
		$needSave = !$saved;

		$leerRows = $totalRows - $lastRow;
		if ($leerRows > 0) {
			$offset = count($result) - $leerRows;

			if ($offset > 0) {
				array_splice($result, $offset);
			} else {
				$result = array();
			}
		}
	
		if ($newModel) {
			$existsGlobalProps = false;
			array_splice($colModel, $lastColumn + 1);
			foreach ($globalProps as $b => $block) {
				$isStyle = ('pq_cellstyle' == $b);
				foreach ($block as $col => $params) {
					if (!isset($colModel[$col]) || empty($params)) {
						unset($globalProps[$col]);
						continue;
					} 
					$existsGlobalProps = true;
					foreach ($params as $key => $value) {
						if ($isStyle) {
							$colModel[$col]['prop']['pyt']['style'][$key] = $value; 
						} else {
							$colModel[$col]['prop']['pyt'][$key] = $value;
						}
					}
				}
				if (empty($globalProps[$b])) {
					unset($globalProps[$b]);
				}
			}
			if ($existsGlobalProps) {
				$props = array_merge($allProps, $props);
				foreach ($props as $id => $prop) {
					foreach ($globalProps as $b => $block) {
						if (isset($prop[$b])) {
							foreach ($prop[$b] as $col => $params) {
								foreach ($params as $key => $value) {
									if (isset($block[$col][$key])) {
										unset($props[$id][$b][$col][$key]);
									}
								}
								if (empty($props[$id][$b][$col])) {
									unset($props[$id][$b][$col]);
								}
							}
							if (empty($props[$id][$b])) {
								unset($props[$id][$b]);
							}
						}
					}
					if (empty($props[$id])) {
						unset($props[$id]);
					}
				}
			}
			$needSave = true;
		}
		$tuning = array();
		if (!empty($mergedCells)) {
			$tuning['merge'] = $mergedCells;
		}
		foreach ($rules as $rule => $ranges) {
			$ranges = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::splitRange($ranges);
			foreach ($ranges as $i => $range) {
				$sCoords = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($range[0]);
				$eCoords = isset($range[1]) ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($range[1]) : $sCoords;
				$sColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sCoords[0]);
				$eColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($eCoords[0]);
				$sRow = $sCoords[1];
				$eRow = $eCoords[1];
				
				if ($withHeader) {
					$sRow--;
					$eRow--;
				}
				if ($eRow > $lastRow) {
					$eRow = $lastRow;
				}
				if ($newModel && $sRow == 1 && $eRow >= $lastRow) {
					for ($c = $sColIndex; $c <= $eColIndex; $c++) {
						$col = $prefix . $c;
						if (isset($colModel[$col])) {
							$pytCond = UtilsPyt::getArrayValue($colModel[$col]['prop']['pyt'], 'pytConds');
							$colModel[$col]['prop']['pyt']['pytConds'] = empty($pytCond) ? $rule : $pytCond . ',' . $rule;
						}
					}
				} else {
					for ($r = $sRow; $r <= $eRow; $r++) {
						$id = 'id_' . ($r + $maxRowId);

						if (isset($props[$id])) {
							$prop = UtilsPyt::getArrayValue($props[$id], 'pq_cellprop', array(), 2); 
							
							for ($c = $sColIndex; $c <= $eColIndex; $c++) {
								$col = $prefix . $c;
								$pytCond = isset($prop[$col]) ? UtilsPyt::getArrayValue($prop[$col], 'pytConds') : '';
								$props[$id]['pq_cellprop'][$col]['pytConds'] = empty($pytCond) ? $rule : $pytCond . ',' . $rule;
							}
						}
					}
				}
			}
		}

		if ($needCached) {
			global $wpdb;
			$wpdb->db_connect();
		}
		if ($needSave || !empty($result)) {
			$list = array('addList' => $result, 'orderList' => $order);
			if (!empty($props)) {
				$list['propList'] = $props;
			}
			$forSave = array('list' => $list);
			if ($newModel) {
				foreach ($colModel as $col => $data) {
					if (!empty($data['prop']['pyt'])) {
						foreach ($data['prop']['pyt'] as $key => $value) {
							$colModel[$col][$key] = $value;
						}
					}
				}
				$forSave['colModel'] = $colModel;
			}
			if ($conditions) {
				$tuning['conditions'] = $conditions;
			}
			if ($tuning) {
				$forSave['tuning'] = $tuning;
			}
			$this->updateTableData($forSave, $this->settings['remove']);
		}
		return $this->settings['tableId'];

	}

	public function media_sideload_image( $drawing ) {
		$title = empty($this->settings['uniq']) ? '' : $this->settings['uniq'] . '-';
		$description = strtolower(substr(trim($drawing->getDescription()), 0, 20));
		if (empty($description)) {
			return '';
		}
		$description = preg_replace('/[^a-z0-9-]/', '-', $description);
		$description = preg_replace('/-+/', "-", $description);
		$title .= $description;
		if (!function_exists('post_exists')) {
			require_once(ABSPATH . 'wp-admin/includes/post.php');
		}
		$id = post_exists($title);

		if (!$id) {
			try {
				if (!function_exists('media_handle_sideload')) {
					require_once(ABSPATH . 'wp-admin/includes/media.php');
					require_once(ABSPATH . 'wp-admin/includes/file.php');
					require_once(ABSPATH . 'wp-admin/includes/image.php');
				}

				$imgFile = $drawing->getPath();
				$extension = $drawing->getExtension();
				$temp = sys_get_temp_dir().'/sss.'.$extension;
				$zipReader = fopen($drawing->getPath(), 'r');
				$imageContents = '';
				while (!feof($zipReader)) {
					$imageContents .= fread($zipReader, 1024);
				}
				fclose($zipReader);
				file_put_contents($temp, $imageContents);
				$id = media_handle_sideload(array('name' => $title . '.' . $extension, 'tmp_name' => $temp), 0);
				if(is_wp_error($id)) {
					@unlink($temp);
				}
			} catch (Exception $e) {
				$id = 0;
			}
		}
		if ($id) {
			$w = $drawing->getWidth();
			$h = $drawing->getHeight();

			$img = wp_get_attachment_image_src($id, 'full');
			return '<img src="' . (is_array($img) ? $img[0] : '') . '"' . (empty($w) ? '' : ' width="' . $w . '"') . (empty($h) ? '' : ' height="' . $h . '"') .
				' style="' . (empty($w) ? '' : 'width:' . $w . 'px;') . (empty($h) ? '' : 'height:' . $h . 'px') . '">';
		}

		return '';
	}

	protected function getColumnWidth( $cells )	{
		$width = array();
		foreach ($cells as $cell) {
			array_push($width, $cell['width']);
		}
		return $width;
	}

	protected function getColor( $colorObj, $type = '' ) {
		switch ($type) {
			case 'background':
				$colorData = $colorObj->getStartColor();
				break;
			case 'condition_background':
				$colorData = $colorObj->getEndColor();
				break;
			default:
				$colorData = $colorObj->getColor();
				break;
		}
		$color = $colorData->getRGB();
		$color = strlen($color) == 4 ? $colorData->getARGB() : $color;
		$color = strlen($color) == 8 ? substr($color, 2) : $color;

		return $color;
	}

	protected function getColumnIndex( $columnName ) {
		return array_search(strtoupper($columnName), range('A', 'Z'), false);
	}

	protected function getRowIndex( $rowName ) {
		return (int) $rowName - 1;
	}

	/**
	 * @param $settings
	 */
	public function setSettings( $settings ) {
		foreach ($settings as $key => $value) {
			$this->settings[$key] = $value;
		}
	}
}

class ChunkReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
	private $_startRow = 0;
	private $_endRow = 0;

	public function setRows( $startRow, $chunkSize ) {
		$this->_startRow    = $startRow;
		$this->_endRow      = $startRow + $chunkSize;
	}

	public function readCell( $column, $row, $worksheetName = '' ) {
		if ((1 == $row) || ($row >= $this->_startRow && $row < $this->_endRow)) {
			return true;
		}
		return false;
	}
}
