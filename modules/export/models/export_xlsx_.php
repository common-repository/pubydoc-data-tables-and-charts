<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
FramePyt::_()->getModule('export')->getModel('export');
class Export_xlsx_ModelPyt extends ExportModelPyt {

	public function export( $id ) {	
		$excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $excel->getActiveSheet();

		$valuesOnly = UtilsPyt::getArrayValue($this->settings, 'valuesOnly', false);
		$withFn = UtilsPyt::getArrayValue($this->settings, 'withFn', false);
		$data = $this->getDataForExport($id);
		
		$autoIndex = UtilsPyt::getArrayValue($this->settings, 'autoIndex', false);
		$columns = UtilsPyt::getArrayValue($data, 'cols', array(), 2);
		
		$i = 0;
		if (!$valuesOnly) {
			foreach ($columns as $key => $col) {
				$i++;
				if (!empty($col['attrs'])) {
					if (empty($col['attrs']['visible'])) {
						$sheet->getColumnDimensionByColumn($i)->setVisible(false);
					} else if (!empty($col['attrs']['width'])) {
						$w = $col['attrs']['width'];
						if (strpos($w, 'px')) {
							$colWidth = round(str_replace('px', '', $w) / 5.5);	// px to pt
							if ($colWidth > 0) {
								$sheet->getColumnDimensionByColumn($i)->setWidth($colWidth);
							}
						}
					}

				}
			}
		}

		$isXlsx = $this->settings['type'] != 'xls';
		$classes = $valuesOnly ? array() : UtilsPyt::getArrayValue($data, 'classes', false);

		$header = UtilsPyt::getArrayValue($this->settings, 'header', false);
		$rowsCount = $data['total'];

		$uploadDir = wp_upload_dir();
		$imgUrl = $uploadDir['baseurl'];
		$imgDir = $uploadDir['basedir'];
		$tempFiles = array();

		$countIndex = 0;
		$letterIndex = 0;
		$addRow = 1;
		if ($header) {

			foreach ($columns as $key => $col) {
				$cellIndex = $this->prefix[$countIndex] . $this->letters[$letterIndex] . $addRow;
				$sheet->setCellValue($cellIndex, $this->getCellValue($col['title']));
				
				$cellClass = UtilsPyt::getArrayValue($col, 'classes', false);
				if (!$valuesOnly && !empty($cellClass) && !empty($classes[$cellClass])) {
					$this->setStyles($sheet->getStyle($cellIndex), $classes[$cellClass]);
				}
				$letterIndex = $this->calcLetterIndex($letterIndex, $countIndex);
			}
			$countIndex = 0;
			$letterIndex = 0;
			$addRow++;
		}

		$attrs = UtilsPyt::getArrayValue($data, 'attrs', array(), 2);
		$conds = array();
		$borderStyle = array(
			'allBorders' => array(
				'borderStyle' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				'color' => array('rgb' => 'CCCCCC')
			)
		);

		foreach ($data['rows'] as $rowIndex => $cells) {
			$realCellsInRowCount = 0;
			$invisibleCellsInRowCount = 0;
			$colIndex = 0;
			$attrsRow = UtilsPyt::getArrayValue($attrs, $rowIndex, array(), 2);
			$attrsRowA = UtilsPyt::getArrayValue($attrsRow, 'a', array(), 2);
			$attrsRowC = UtilsPyt::getArrayValue($attrsRow, 'c', array(), 2);
			if (!empty($attrsRowC)) {
				$cl = current($attrsRowC);
				if (isset($classes[$cl]) && !empty($classes[$cl]['height'])) {
					$rowHeight = round(((int) str_replace('px', '', $classes[$cl]['height'])) / 1.333333);	// px to pt
					if($rowHeight > 0) {
						$sheet->getRowDimension($rowIndex + $addRow)->setRowHeight($rowHeight);
					}
				}
			}
			// set invisibility row
			if (1 == $attrsRow['h']) {
				$sheet->getRowDimension($rowIndex + $addRow)->setVisible(false);
			}
			foreach ($cells as $col => $value) {
				$colIndex++;
				$cellIndex = $this->prefix[$countIndex] . $this->letters[$letterIndex] . ($rowIndex + $addRow);
				$isLink = '';
				$isImg = false;
				$isRemoteImg = false;

				if ($autoIndex && $colIndex == 1) {
					$cellValue = $rowIndex + $addRow - 1;
				} else {
					$cellValue = $this->getCellValue($value, $isLink);
				}
				$attrsCellA = UtilsPyt::getArrayValue($attrsRowA, $col, array(), 2);
				$cellClass = UtilsPyt::getArrayValue($attrsRowC, $col);
				
				if (preg_match('/^<img.*src="([^"]*)".*>$/i', $cellValue, $matches)) {
					$imgPath = $matches[1];
					if (strpos($imgPath, $imgUrl) === 0) {
						$isImg = true;
						$imgPath = str_replace($imgUrl, $imgDir, $imgPath);
					} else {
						if (isset($tempFiles[$imgPath])) {
							$imgPath = $tempFiles[$imgPath];
							$isRemoteImg = true;
						} else {
							try {
								$response = wp_remote_get($imgPath);
								if (!is_wp_error($response) && strpos(wp_remote_retrieve_header($response, 'content-type'), 'image') !==  false) {
									$file = wp_remote_retrieve_body($response);
									if (!empty($file)) {
										$temp = tempnam(sys_get_temp_dir(), 'sstimage');
										$tempFiles[$imgPath] = $temp;
										$handler = fopen($temp, 'w+');
										fwrite($handler, $file);
										fclose($handler);
										$imgPath = $temp;
										$isRemoteImg = true;
									}
								}
							} catch (Exception $e) {}
						}
					}
					if ($isImg || $isRemoteImg) {
						if (preg_match('/^<img.*width="([^"]*)".*>$/i', $cellValue, $matches)) {
							$imgWidth = $matches[1];
						} else {
							$imgWidth = 0;
						}
						if (preg_match('/^<img.*height="([^"]*)".*>$/i', $cellValue, $matches)) {
							$imgHeight = $matches[1];
						} else {
							$imgHeight = 0;
						}
					}
				}

				if (!$valuesOnly) {
					$colspan = UtilsPyt::getArrayValue($attrsCellA, 'colspan', 1, 1) - 1;
					$rowspan = UtilsPyt::getArrayValue($attrsCellA, 'rowspan', 1, 1) - 1;

					if ($colspan > 0 || $rowspan > 0) {
						$mergeCountIndex = $countIndex;
						$mergeLetterIndex = $letterIndex + $colspan;
						if ($mergeLetterIndex >= count($this->letters)) {
							$mergeCountIndex++;
							$mergeLetterIndex = $mergeLetterIndex - count($this->letters);
						}
						$toCol = $this->prefix[$mergeCountIndex] . $this->letters[$mergeLetterIndex];
						$toRow = $rowIndex + $addRow + $rowspan;
						$sheet->mergeCells($cellIndex.':'.$toCol.$toRow);		// coordinates for merge
					}
				}
				if ($isLink) {
					$sheet->setHyperlink($cellIndex, new \PhpOffice\PhpSpreadsheet\Cell\Hyperlink($isLink, $cellValue));
				}
				if ($isImg || $isRemoteImg) {
					$objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
					$objDrawing->setPath($imgPath);
					$objDrawing->setResizeProportional(true);
					if ($imgWidth > 0) {
						$objDrawing->setWidth($imgWidth);
					}
					if ($imgHeight > 0) {
						$objDrawing->setHeight($imgHeight);
					}
					$objDrawing->setOffsetX(5);
					$objDrawing->setOffsetY(5);
					$objDrawing->setCoordinates($cellIndex);
					$h = $objDrawing->getHeight() - ($objDrawing->getHeight() * .25) + 10;
					$w = round($objDrawing->getWidth() / 5.5);
					if ($h > $sheet->getRowDimension($rowIndex + $addRow)->getRowHeight()) {
						$sheet->getRowDimension($rowIndex + $addRow)->setRowHeight($h);
					}
					if ($w > $sheet->getColumnDimensionByColumn($colIndex)->getWidth()) {
						$sheet->getColumnDimensionByColumn($colIndex)->setWidth($w);
					}					
					$objDrawing->setWorksheet($sheet);
				} else {
					if ($withFn) {
						$fn = UtilsPyt::getArrayValue($attrsCellA, 'fn');
						if (!empty($fn)) { 
							$cellValue = '=' . $fn;
						}
					}
					$sheet->setCellValue($cellIndex, $cellValue);
				}
				
				if (!$valuesOnly) {
					if (!empty($cellClass) && !empty($classes[$cellClass])) {
						$this->setStyles($sheet->getStyle($cellIndex), $classes[$cellClass]);
					}		
				
					// export seletable list
					$cellType = UtilsPyt::getArrayValue($attrsCellA, 'type');
					if (!empty($cellType)) {
						$cellFormat = UtilsPyt::getArrayValue($attrsCellA, 'format');
						if ('select' == $cellType && !empty($cellFormat)) {
							$dataValidation = $sheet->getCell($cellIndex)->getDataValidation();
							$dataValidation->setType( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST );
							$dataValidation->setErrorStyle( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION );
							$dataValidation->setAllowBlank(false);
							$dataValidation->setShowDropDown(true);
							$dataValidation->setFormula1('"' . str_replace("\n", ',', str_replace(array('"', ','), array('', ';'),  $cellFormat)) . '"');
						}
					}
					$cellConds = UtilsPyt::getArrayValue($attrsCellA, 'conds');

					if (!empty($cellConds)) {
						$rules = explode(',', $cellConds);
						foreach ($rules as $rule) {
							if (isset($conds[$rule])) {
								$conds[$rule][] = $cellIndex;
							} else {
								$conds[$rule] = array($cellIndex);
							}
						}
					}
				}

				$realCellsInRowCount++;
				$letterIndex = $this->calcLetterIndex($letterIndex, $countIndex);
			}

			$letterIndex = 0;
			$countIndex = 0;
		}
		// set invisibility column
		
		if (!$valuesOnly && $isXlsx) {
			$tuning = UtilsPyt::getArrayValue($this->table, 'tuning', array(), 2);
			$conditions = UtilsPyt::getArrayValue($tuning, 'conditions', array(), 2);
			$addCol = $autoIndex && UtilsPyt::getArrayValue($this->settings, 'autoIndexNew', false) ? 1 : 0;

			foreach ($columns as $key => $col) {
				if (!empty($col['attrs'])) {
					$colConds = UtilsPyt::getArrayValue($col['attrs'], 'conds');
					if (!empty($colConds)) {
						$rules = explode(',', $colConds);
						$letter = $this->prefix[$countIndex] . $this->letters[$col['num'] + $addCol];
						$range = $letter . $addRow . ':' . $letter . ($addRow + $rowsCount);
						foreach ($rules as $rule) {
							if (isset($conds[$rule])) {
								$conds[$rule][] = $range;
							} else {
								$conds[$rule] = array($range);
							}
						}
					}
				}
			}

			// set conditional formatting (work only for xlsx - need fix)
			if (!empty($conds) && !empty($conditions)) {
				$condParams = FramePyt::_()->getModule('tablespro')->getConditionsParamsExcel(false);

				foreach ($conds as $rule => $cells) {
					if (isset($conditions[$rule])) {

						$cond = $conditions[$rule];
						$objConditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
						$objConditional->setConditionType(UtilsPyt::getArrayValue($condParams['types'], $cond['type'], 'cellIs'));
						$objConditional->setOperatorType(UtilsPyt::getArrayValue($condParams['opers'], $cond['oper'], 'equal'));
						$v = $cond['value'];
						$values = array(is_numeric($v) ? $v : '"' . $v . '"');
						if (!empty($cond['value2'])) {
							$values[] = is_numeric($cond['value2']) ? $cond['value2'] : '"' . $cond['value2'] . '"';
						}
						$objConditional->setText((string)$v);
						$objConditional->setConditions($values);

						$condStyle = $objConditional->getStyle();
						$condFill = $condStyle->getFill();
						$condFont = $condStyle->getFont();

						foreach($cond['styles'] as $n => $v) {
							switch ($n) {
								case 'background-color':
									if (!empty($v)) {
										$condFill->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
										$condFill->getEndColor()->setRGB($this->getColor($v));
										$condStyle->getBorders()->applyFromArray($borderStyle);
									}
								break;
								case 'color':
									if (!empty($v)) {
										$condFont->getColor()->setRGB($this->getColor($v));
									}
									break;
								case 'font-weight':
									if ('bold' == $v) {
										$condFont->setBold(true);
									}
									break;
								case 'font-style':
									if ('italic' == $v) {
										$condFont->setItalic(true);
									}
									break;
								case 'text-decoration':
									if ('underline' == $v) {
										$condFont->setUnderline(true);
									}
									break;
								default: 
									break;
							}
						}
					}
					$objConditional->setStyle($condStyle);

					foreach ($cells as $range) {
						$conditionalStyles = $sheet->getStyle($range)->getConditionalStyles();
						array_push($conditionalStyles, $objConditional);
						$sheet->getStyle($range)->setConditionalStyles($conditionalStyles);						  
					}
				}
			}
		}
		$excel->getActiveSheet()->setSelectedCells('A1');

		$this->beginUpload();
		$writer = $this->getWriter($excel);
			
		if (!$uploadDir['error']) {
			$filename = trailingslashit($uploadDir['basedir']) . 'temp.pdf';
			$writer->save($filename);
			readfile($filename);
		}
		$this->removeTempFiles($tempFiles);
		die();
	}

	public function removeTempFiles( $tempFiles )	{
		foreach($tempFiles as $temp) {
			@unlink($temp);	
		}
	}

	public function getWriter( $spreadsheet )	{
		if ($this->settings['type'] == 'pdf') {
			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Tcpdf($spreadsheet);
			$writer->setPreCalculateFormulas(true);
			return $writer;
		}
		return $this->settings['type'] == 'xls' ? new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet) : new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

	}

	public function getColor( $color ) {
		return str_replace('#', '', $color);
	}

	public function setStyles ( $cellStyle, $styles ) {
		$cellAlignment = $cellStyle->getAlignment();
		$cellAlignment->setWrapText(true);
		$cellFill = $cellStyle->getFill();
		$cellFont = $cellStyle->getFont();
		$isAlign = false;
		foreach ($styles as $key => $value) {
			switch ($key) {
				case 'text-align':
					$isAlign = true;
					if ('center' == $value) {
						$cellAlignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
					} else if ('right' == $value) {
						$cellAlignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
					} else {
						$cellAlignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);	
					}
					break;
				case 'vertical-align':
					if ('top' == $value) {
						$cellAlignment->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
					} else if ('bottom' == $value) {
						$cellAlignment->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM);
					} else {
						$cellAlignment->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);	
					}
					break;
				case 'background-color':
					$cellFill->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
					$cellFill->getStartColor()->setRGB($this->getColor($value));
					break;
				case 'color':
					$cellFont->getColor()->setRGB($this->getColor($value));;
					break;
				case 'font-size':
					$cellFont->setSize((int)str_replace('px', '', $value));
					break;
				case 'font-family':
					$cellFont->setName($value);
					break;
				case 'font-weight':
					if ('bold' == $value) {
						$cellFont->setBold(true);
					}
					break;
				case 'font-style':
					if ('italic' == $value) {
						$cellFont->setItalic(true);
					}
					break;
				case 'text-decoration':
					if ('underline' == $value) {
						$cellFont->setUnderline(true);
					}
					break;				 		
				default:
					break;
			}
		} 
		if (!$isAlign) {
			$cellAlignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
		}
		return;
	}
}
