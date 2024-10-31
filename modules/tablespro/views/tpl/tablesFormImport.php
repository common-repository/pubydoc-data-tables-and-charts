<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$isNew = empty($this->fromBuilder);
if ($isNew) { ?>
	<div class="pyt-source-desc">
		<?php esc_html_e('Select the import type and set the required parameters. The table will be created based on the loaded data. Later at any time it will be possible to change the table and/or its contents by the builders.', 'publish-your-table'); ?>
	</div>
<?php } ?>
<div class="pyt-import-tabs pubydoc-clear">
	<ul class="pubydoc-grbtn">
		<li>
			<a href="#block-tab-import-excel" class="button current" data-creation="1">
				<i class="fa fa-fw fa-file-excel-o"></i><?php esc_html_e('MS Excel', 'publish-your-table'); ?>
			</a>
		</li>
		<li>
			<a href="#block-tab-import-csv" class="button" data-creation="2">
				<i class="fa fa-fw fa-file-text-o"></i><?php esc_html_e('CSV', 'publish-your-table'); ?>
			</a>
		</li>
		<li>
			<a href="#block-tab-import-google" class="button" data-creation="3">
				<i class="fa fa-fw fa-google-plus-square"></i><?php esc_html_e('Google Sheet', 'publish-your-table'); ?>
			</a>
		</li>
		<li>
			<a href="#block-tab-import-sql" class="button" data-type="1" data-creation="4">
				<i class="fa fa-fw fa-sql"></i><?php esc_html_e('SQL', 'publish-your-table'); ?>
			</a>
		</li>
	</ul>
</div>
<div class="block-tab" id="block-tab-import-excel">
	<div class="pubydoc-result-settings">	
		<div class="settings-wrapper pubydoc-width-full">
			<div class="settings-label">
				<?php esc_html_e('Excel File', 'publish-your-table'); ?>
			</div>
			<div class="settings-option">
				<?php HtmlPyt::file('excel_file', array()); ?>
			</div>
		</div>
		<div class="settings-wrapper pubydoc-width-full">
			<?php if (!$isNew) { ?>
				<div class="settings-option">
					<?php 
						HtmlPyt::checkbox('append_data', array('attrs' => 'class="pyt-import-append"'));
					?>
					<?php esc_html_e('Append to existing data table', 'publish-your-table'); ?>
				</div>
			<?php } ?>
			<div class="settings-option">
				<?php 
					HtmlPyt::checkbox('excel_raw', array());
				?>
				<?php esc_html_e('Import only raw data without formatting', 'publish-your-table'); ?>
			</div>
			<div class="settings-option">
				<?php 
					HtmlPyt::checkbox('excel_visible', array());
				?>
				<?php esc_html_e('Import string data as you can see it on Excel', 'publish-your-table'); ?>
			</div>
			<div class="settings-option">
				<?php 
					HtmlPyt::checkbox('excel_header', array('attrs' => 'class="pyt-import-header"'));
				?>
				<?php esc_html_e('Treat first line as a header', 'publish-your-table'); ?>
			</div>
		</div>
	</div>
</div>
<div class="block-tab" id="block-tab-import-csv">
	<div class="pubydoc-result-settings">	
		<div class="settings-wrapper pubydoc-width-full">
			<div class="settings-label">
				<?php esc_html_e('CSV File', 'publish-your-table'); ?>
			</div>
			<div class="settings-option">
				<?php HtmlPyt::file('csv_file', array()); ?>
			</div>
		</div>
		<div class="settings-wrapper pubydoc-width-full">
			<div class="settings-label">
				<?php esc_html_e('Delimiter', 'publish-your-table'); ?>
			</div>
			<div class="settings-option">
				<?php 
					HtmlPyt::selectbox('csv_delim', array('options' => array(',' => ',', ';' => ';'), 'attrs' => 'class="pubydoc-width300"'));
				?>
			</div>
		</div>
		<div class="settings-wrapper pubydoc-width-full">
			<?php if (!$isNew) { ?>
				<div class="settings-option">
					<?php 
						HtmlPyt::checkbox('append_data', array('attrs' => 'class="pyt-import-append"'));
					?>
					<?php esc_html_e('Append to existing data table', 'publish-your-table'); ?>
				</div>
			<?php } ?>
			<div class="settings-option">
				<?php 
					HtmlPyt::checkbox('csv_header', array('attrs' => 'class="pyt-import-header"'));
				?>
				<?php esc_html_e('Treat first line as a header', 'publish-your-table'); ?>
			</div>
		</div>
	</div>
</div>
<div class="block-tab" id="block-tab-import-google">
	<div class="pubydoc-result-settings">	
		<div class="settings-wrapper pubydoc-width-full">
			<div class="settings-label">
				<?php esc_html_e('Google Spreadsheet Url', 'publish-your-table'); ?>
			</div>
			<div class="settings-option">
				<?php 
					HtmlPyt::text('google_url', array(
					'placeholder' => 'https://docs.google.com/spreadsheets/d/xxxxxxxxxxxxxxxxxxxxxxxxxx/edit#gid=0'
					));
					?>
			</div>
			<div class="settings-note">
				<?php esc_html_e('Important! Please, check the sharing settings of your spreadsheet: it must be accessed to edit for everyone who has link. In other case the data will not import to table.', 'publish-your-table'); ?>
			</div>
		</div>
		<div class="settings-wrapper pubydoc-width-full">
			<?php if (!$isNew) { ?>
				<div class="settings-option">
					<?php 
						HtmlPyt::checkbox('append_data', array('attrs' => 'class="pyt-import-append"'));
					?>
					<?php esc_html_e('Append to existing data table', 'publish-your-table'); ?>
				</div>
			<?php } ?>
			<div class="settings-option">
				<?php 
					HtmlPyt::checkbox('google_raw', array());
				?>
				<?php esc_html_e('Import only raw data without formatting', 'publish-your-table'); ?>
			</div>
			<div class="settings-option">
				<?php 
					HtmlPyt::checkbox('google_header', array('attrs' => 'class="pyt-import-header"'));
				?>
				<?php esc_html_e('Treat first line as a header', 'publish-your-table'); ?>
			</div>
		</div>
	</div>
</div>
<div class="block-tab" id="block-tab-import-sql">
	not READY
</div>
