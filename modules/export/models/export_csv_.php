<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
FramePyt::_()->getModule('export')->getModel('export');
class Export_csv_ModelPyt extends ExportModelPyt {

	public function export( $id ) {
        $excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $excel->getActiveSheet();

		$valuesOnly = UtilsPyt::getArrayValue($this->settings, 'valuesOnly', false);
		$header = UtilsPyt::getArrayValue($this->settings, 'header', false);
		$data = $this->getDataForExport($id);
		
		$autoIndex = UtilsPyt::getArrayValue($this->settings, 'autoIndex', false);

		$countIndex = 0;
		$letterIndex = 0;
		$addRow = 1;
		if ($header) {
			$columns = UtilsPyt::getArrayValue($data, 'cols', array(), 2);
			foreach ($columns as $key => $col) {
				$cellIndex = $this->prefix[$countIndex] . $this->letters[$letterIndex] . $addRow;
				$sheet->setCellValue($cellIndex, addslashes($this->getCellValue($col['title'])));

				$letterIndex = $this->calcLetterIndex($letterIndex, $countIndex);
			}
			$countIndex = 0;
			$letterIndex = 0;
			$addRow++;
		}
		foreach ($data['rows'] as $rowIndex => $cells) {
			foreach ($cells as $col => $value) {
				$cellIndex = $this->prefix[$countIndex] . $this->letters[$letterIndex] . ($rowIndex + $addRow);
				$isLink = '';
				$cellValue = $autoIndex && $letterIndex == 0 ? $rowIndex + 1 : $this->getCellValue($value, $isLink);
                $sheet->setCellValue($cellIndex, addslashes($cellValue));

                $letterIndex = $this->calcLetterIndex($letterIndex, $countIndex);
            }
			$letterIndex = 0;
			$countIndex = 0;
		}
		$this->beginUpload();

		$writer = $this->getWriter($excel);
		try {
			$writer->save('php://output');
		} catch (Exception $e) {
			$uploadDir = wp_upload_dir();
			if (!$uploadDir['error']) {
				$filename = trailingslashit($uploadDir['basedir']) . 'temp.xls';
				$writer->save($filename);
				readfile($filename);
				unlink($filename);
			}
		}
		die();
    }

	public function getWriter( $spreadsheet )	{
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
		return $writer->setDelimiter($this->settings['delimeter'])->setEnclosure('')->setLineEnding("\r\n");
	}
}
