<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pyt-export-tabs pubydoc-clear">
	<ul class="pubydoc-grbtn">
		<li>
			<a href="#block-tab-export-excel" class="button current" data-export="excel">
				<i class="fa fa-fw fa-file-excel-o"></i><?php esc_html_e('MS Excel', 'publish-your-table'); ?>
			</a>
		</li>
		<li>
			<a href="#block-tab-export-csv" class="button" data-export="csv">
				<i class="fa fa-fw fa-file-text-o"></i><?php esc_html_e('CSV', 'publish-your-table'); ?>
			</a>
		</li>
		<!--<li>
			<a href="#block-tab-export-pdf" class="button" data-export="pdf">
				<i class="fa fa-fw fa-file-pdf-o"></i><?php esc_html_e('PDF', 'publish-your-table'); ?>
			</a>
		</li>-->
	</ul>
</div>
<div class="block-tab" id="block-tab-export-excel">
	<div class="pubydoc-result-settings">
		<div class="settings-wrapper pubydoc-width-full">
			<div class="settings-label">
				<?php esc_html_e('Type', 'publish-your-table'); ?>
			</div>
			<div class="settings-option">
				<?php 
					HtmlPyt::selectbox('excel_type', array(
						'options' => array(
							'xlsx' => 'MS Excel 2007 (.xlsx)',
							'xls' => 'MS Excel 2003 (.xls)'),
						'attrs' => 'class="pubydoc-width300"'
						));
				?>
			</div>
		</div>
		<div class="settings-wrapper pubydoc-width-full">
			<div class="settings-option">
				<?php 
					HtmlPyt::checkbox('excel_valuesonly', array());
				?>
				<?php esc_html_e('Export only values (without styles)', 'publish-your-table'); ?>
			</div>
			<div class="settings-option">
				<?php 
					HtmlPyt::checkbox('excel_raw', array());
				?>
				<?php esc_html_e('Export raw values', 'publish-your-table'); ?>
			</div>
			<div class="settings-option">
				<?php 
					HtmlPyt::checkbox('excel_fn', array());
				?>
				<?php esc_html_e('Export formulas', 'publish-your-table'); ?>
			</div>
			<div class="settings-option">
				<?php 
					HtmlPyt::checkbox('excel_header', array('attrs' => 'class="pyt-import-header"'));
				?>
				<?php esc_html_e('Save header as first line', 'publish-your-table'); ?>
			</div>
		</div>
	</div>
</div>
<div class="block-tab" id="block-tab-export-csv">
	<div class="pubydoc-result-settings">	
		<div class="settings-wrapper pubydoc-width-full">
			<div class="settings-label">
				<?php esc_html_e('Delimiter', 'publish-your-table'); ?>
			</div>
			<div class="settings-option">
				<?php 
					HtmlPyt::selectbox('csv_delim', array(
						'options' => array(',' => ',', ';' => ';'), 
						'attrs' => 'class="pubydoc-width300"'));
				?>
			</div>
		</div>
		<div class="settings-wrapper pubydoc-width-full">
			<div class="settings-option">
				<?php 
					HtmlPyt::checkbox('csv_raw', array());
				?>
				<?php esc_html_e('Export raw values', 'publish-your-table'); ?>
			</div>
			<div class="settings-option">
				<?php 
					HtmlPyt::checkbox('csv_header', array('attrs' => 'class="pyt-import-header"'));
				?>
				<?php esc_html_e('Save header as first line', 'publish-your-table'); ?>
			</div>
		</div>
	</div>
</div>
<div class="block-tab" id="block-tab-export-pdf">
	<div class="pubydoc-result-settings">	
		
	</div>
</div>
