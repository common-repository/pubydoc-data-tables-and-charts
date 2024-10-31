<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$options = UtilsPyt::getArrayValue($this->table, 'options', array(), 2);
	$tuning = UtilsPyt::getArrayValue($this->table, 'tuning', array(), 2);
	$conds = UtilsPyt::getArrayValue($tuning, 'conditions', array());

	$isSingleCell = UtilsPyt::getArrayValue($this->params, 'isSingleCell', false);
	if (!$isSingleCell) {
	
		$exportTypes = UtilsPyt::getArrayValue($options, 'export', array(), 2);
		foreach ($conds as $rule => $data) {
			unset($conds[$rule]['styles']);
		}
		$logo = '';
		if (UtilsPyt::getArrayValue($options, 'export_logo')) {
			$logo = array(
				'position' => UtilsPyt::getArrayValue($options, 'logo_position'),
				'alignment' => UtilsPyt::getArrayValue($options, 'logo_alignment')
				);
		}
	}
?>
data-lightbox="<?php echo UtilsPyt::getArrayValue($options, 'lightbox') ? 1 : 0; ?>"
data-conditions="<?php echo esc_attr(json_encode($conds)); ?>"
data-editable="<?php echo $this->getModel('cellspro')->checkUseEditableFields($options) ? 1 : 0; ?>"
data-save-efields="<?php echo UtilsPyt::getArrayValue($options, 'efields_save') ? 1 : 0; ?>"
data-mark-efields="<?php echo UtilsPyt::getArrayValue($options, 'efields_mark_last') ? 1 : 0; ?>"
data-allowed-files="<?php echo esc_attr(json_encode(UtilsPyt::getArrayValue($options, 'efields_extensions', array('png','jpeg','jpg','pdf'), 2))); ?>"

<?php if (!$isSingleCell) { ?>
	data-export="<?php echo esc_attr(json_encode($exportTypes)); ?>"
	data-export-position="<?php echo esc_attr(UtilsPyt::getArrayValue($options, 'export_position')); ?>"
	data-export-visible="<?php echo UtilsPyt::getArrayValue($options, 'export_visible') ? 1 : 0; ?>"
	data-pdf-size="<?php echo esc_attr(UtilsPyt::getArrayValue($options, 'pdf_size')); ?>"
	data-pdf-orientation="<?php echo esc_attr(UtilsPyt::getArrayValue($options, 'pdf_orientation')); ?>"
	data-export-logo="<?php echo esc_attr(json_encode($logo)); ?>"
	data-collapsible-rows="<?php echo esc_attr(json_encode(UtilsPyt::getArrayValue($tuning, 'collapsibleRows', array(), 2))); ?>"
<?php } ?>
