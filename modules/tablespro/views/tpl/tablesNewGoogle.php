<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pubydoc-result-settings">
	<div class="settings-wrapper pubydoc-width-full">
		<div class="settings-label">
			<?php 
				esc_html_e('Link from Google Tables', 'publish-your-table');
				echo '<i class="fa fa-question pubydoc-tooltip" title="' . esc_attr__('Type Google Sheet url to import data from sheet to table. Later at any time it will be possible to change.', 'publish-your-table') . '"></i>';
			?>
		</div>
		<div class="settings-option">
			<?php 
				HtmlPyt::text('google_url', array(
				'placeholder' => 'https://docs.google.com/spreadsheets/d/xxxxxxxxxxxxxxxxxxxxxxxxxx/edit#gid=0'
				));
				?>
		</div>
		<div class="settings-note">
			<?php esc_html_e('Note! At Google Sheet Share Settings you need to activate "Everyone who has link can edit document" option.', 'publish-your-table'); ?>
		</div>
	</div>
	<div class="settings-wrapper pubydoc-width-full">
		<div class="settings-option">
			<?php 
				HtmlPyt::checkbox('google_header', array());
			?>
			<?php esc_html_e('Treat first line as a header', 'publish-your-table'); ?>
		</div>
	</div>
</div>
