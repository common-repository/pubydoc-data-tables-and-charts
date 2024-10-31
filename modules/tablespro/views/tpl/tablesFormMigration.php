<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pubydoc-hidden">
	<div id="pytDialogMigration" title="<?php esc_attr_e('Import Tables in Database', 'publish-your-table'); ?>" 
		data-cancel="<?php esc_attr_e('Cancel', 'publish-your-table'); ?>"
		data-import="<?php esc_attr_e('Import', 'publish-your-table'); ?>">
		<div class="dialog-form">
			<div class="pubydoc-result-settings">	
				<div class="settings-wrapper pubydoc-width-full">
					<div class="settings-label">
						<?php esc_html_e('Create the data migration file with the "export" button on a source database then import all the saved tables to the current database.', 'publish-your-table'); ?>
					</div>
					<div class="settings-option">
						<?php HtmlPyt::file('sql_file', array()); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
