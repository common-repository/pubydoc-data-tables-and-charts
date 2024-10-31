<?php 
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	//$source = empty($this->source) ? array() : UtilsPyt::getArrayValue($this->source, 'source', array(), 2);
	$source = empty($this->source) ? array() : $this->source;
?>
<div id="pyt-google-settings">
	<div class="pubydoc-result-settings" id="pyt-google-params">
		<div class="settings-wrapper pubydoc-width-full">
			<div class="settings-label">
				<?php 
					esc_html_e('Link from Google Tables', 'publish-your-table');
					echo '<i class="fa fa-question pubydoc-tooltip" title="' . esc_attr__('Type Google Sheet url to import data from sheet to table. Later at any time it will be possible to change.', 'publish-your-table') . '"></i>';
				?>
			</div>
			<div class="settings-option">
				<?php 
					HtmlPyt::text('source[google_url]', array(
					'value' => UtilsPyt::getArrayValue($source, 'google_url', ''),
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
					HtmlPyt::checkbox('source[google_header]', array(
						'checked' => UtilsPyt::getArrayValue($source, 'google_header', 0, 1),
						'attrs' => 'id="pytGoogleHeader"'
					));
				?>
				<?php esc_html_e('Treat first line as a header', 'publish-your-table'); ?>
			</div>
		</div>
	</div>
	<div class="pubydoc-result-settings">
		<div class="settings-wrapper pubydoc-width-full">
			<div class="settings-option">
				<button class="button button-dark pubydoc-width-full" id="pytLoadInBuilder"><?php esc_attr_e('GO', 'publish-your-table'); ?></button>
			</div>
		</div>
	</div>
</div>
