<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php if ($this->full_builder) { ?>
<div class="pubydoc-hidden">
	<div id="pytDialogConditions" data-title-cell="<?php esc_attr_e('Cells conditional formatting', 'publish-your-table'); ?>" data-title-column="<?php esc_attr_e('Column conditional formatting', 'publish-your-table'); ?>">
		<div class="dialog-form">
			<?php include 'tablesEditBuilderConditions.php';?>
		</div>
	</div>
</div>
<div class="pubydoc-hidden">
	<div id="pytDialogImport" title="<?php esc_attr_e('Import data to the table', 'publish-your-table'); ?>">
		<div class="dialog-form">
			<?php include 'tablesFormImport.php';?>
		</div>
	</div>
</div>
<div class="pubydoc-hidden">
	<div id="pytDialogExport" title="<?php esc_attr_e('Export data to the file', 'publish-your-table'); ?>">
		<div class="dialog-form">
			<?php include 'tablesFormExport.php';?>
		</div>
	</div>
</div>
<?php } ?>
