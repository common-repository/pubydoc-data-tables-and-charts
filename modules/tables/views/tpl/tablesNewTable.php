<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="pubydoc-bar pubydoc-titlebar">
	<ul class="pubydoc-bar-controls">
		<li class="pubydoc-title-icon">
			<i class="fa fa-television"></i>
		</li>
		<li class="pubydoc-title-text">
			<?php esc_html_e('How would you like to create your table?', 'publish-your-table'); ?>
		</li>
	</ul>
	<div class="pubydoc-clear"></div>
</section>
<section>
	<form id="pytNewTableForm" class="" enctype="multipart/form-data" method="post">
		<div class="pubydoc-item pubydoc-panel">
			<div class="row pubydoc-main-container">
				<div class="col-md-12">
					<div class="pyt-menu-section">
						<ul class="pubydoc-grbtn">
							<?php foreach ($this->menu_tabs as $key => $data) { ?>
								<li>
									<a href="#block-tab-<?php echo esc_attr($key); ?>" class="button button-dark <?php echo isset($data['class']) ? esc_attr($data['class']) : ''; ?>" data-type="<?php echo esc_attr($data['type']); ?>" data-creation="<?php echo esc_attr($data['creation']); ?>">
										<i class="fa fa-fw <?php echo esc_attr($data['icon']); ?>"></i><?php echo esc_html($data['label']); ?>
									</a>
								</li>
							<?php } ?>
						</ul>
					</div>
					<div class="pubydoc-result-section">
						<div class="pubydoc-result-settings">	
							<div class="settings-wrapper pubydoc-width-full options-for-save">
								<div class="settings-label">
									<?php 
										esc_html_e('Table title', 'publish-your-table');
										echo '<i class="fa fa-question pubydoc-tooltip" title="' . esc_attr__('The name of the table. Later at any time it will be possible to change.', 'publish-your-table') . '"></i>';
									?>
								</div>
								<div class="settings-option">
									<?php 
										HtmlPyt::text('title', array(
										'placeholder' => __('Enter a title to identify your table', 'publish-your-table'), 
										)); 
									?>
								</div>
							</div>
						</div>
						<div class="block-tab" id="block-tab-manual">
							<div class="pyt-source-header">
								<?php esc_html_e('Manually Create a Table', 'publish-your-table'); ?>
							</div>
							<div class="pyt-source-desc">
								<?php esc_html_e('Manually Create your table columns and rows to get complete control over your data with tons customizations', 'publish-your-table'); ?>
							</div>
							<div class="pubydoc-result-settings">	
								<div class="settings-wrapper">
									<div class="settings-label">
										<?php 
											esc_html_e('Number of columns', 'publish-your-table');
											echo '<i class="fa fa-question pubydoc-tooltip" title="' . esc_attr__('Number of columns. Later at any time it will be possible to change.', 'publish-your-table') . '"></i>';
										?>
									</div>
									<div class="settings-option">
										<?php 
											HtmlPyt::number('cols', array(
											'value' => 3,
											'attrs' => 'min="1" max="20" class="pubydoc-flat-input" id="pytTableCols"'
										));
										?>
									</div>
								</div>
								<div class="settings-wrapper">
									<div class="settings-label">
										<?php 
											esc_html_e('Number of rows', 'publish-your-table');
											echo '<i class="fa fa-question pubydoc-tooltip" title="' . esc_attr__('Number of rows. Later at any time it will be possible to change.', 'publish-your-table') . '"></i>';
										?>
									</div>
									<div class="settings-option">
										<?php 
											HtmlPyt::number('rows', array(
											'value' => 3,
											'attrs' => 'min="1" max="200" class="pubydoc-flat-input" id="pytTableRows"'
										));
										?>
									</div>
								</div>
							</div>
							<div class="pubydoc-result-settings pubydoc-clear" id="pytColumnsBlock">
							<?php
								for($c = 0; $c <= 3; $c++) {
							?>
								<div class="pyt-column-block <?php echo empty($c) ? ' pubydoc-template' : ''; ?>">
									<div class="pyt-column-header">
										<div class="input-edit-wrapper">
										<?php 
											HtmlPyt::text('', array(
											'placeholder' => __('New column', 'publish-your-table'),
											'attrs' => 'class="pubydoc-flat-input"'
										));
										?>
										</div>
									</div>
									<i class="fa fa-times pubydoc-action-icon pyt-column-delete"></i>
									<?php
										for($r = 1; $r <= 3; $r++) {
									?>
									<div class="pyt-column-row">
										<?php 
											HtmlPyt::text('', array(
											'attrs' => 'class="pubydoc-flat-input"'
										));
										?>
									</div>
									<?php } ?>
								</div>
							<?php } ?>
							</div>
						</div>
						<div class="block-tab" id="block-tab-import">
							<div class="pyt-source-header">
								<?php esc_html_e('Import Table', 'publish-your-table'); ?>
							</div>
							<?php 
								if ($this->is_pro) {
									DispatcherPyt::doAction('tablesIncludeTpl', 'tablesFormImport');
								} else { 
									include 'tablesProFeature.php';
								}
							?>
						</div>
						<div class="block-tab" id="block-tab-google">
							<div class="pyt-source-header">
								<?php esc_html_e('GoogleSheet Table', 'publish-your-table'); ?>
							</div>
							<div class="pyt-source-desc">
								<?php esc_html_e('Table data on frontend will be automatic synchronize with selected Google Sheet. If Google Sheets contains any images the unique description for each is required to not download the redundant copies in Media Library.', 'publish-your-table'); ?>
							</div>
							<?php 
								if ($this->is_pro) {
									DispatcherPyt::doAction('tablesIncludeTpl', 'tablesNewGoogle');
								} else { 
									include 'tablesProFeature.php';
								}
							?>
						</div>
						<div class="block-tab" id="block-tab-woo">
							<div class="pyt-source-header">
								<?php esc_html_e('WooCommerce Table', 'publish-your-table'); ?>
							</div>
							<div class="pyt-source-desc">
								<?php esc_html_e('WooCommerce Product Table allows you to create flexible, responsive tables and gives you full control over the columns properties. List your products in any format: price list, order forms, product catalogues & more.', 'publish-your-table'); ?>
							</div>
							<?php 
								if ($this->is_pro) {
									DispatcherPyt::doAction('tablesIncludeTpl', 'tablesNewWoo');
								} else { 
									include 'tablesProFeature.php';
								}
							?>
						</div>
						<div class="block-tab" id="block-tab-sql">
							<div class="pyt-source-header">
								<?php esc_html_e('Custom SQL Query', 'publish-your-table'); ?>
							</div>
							<div class="pyt-source-desc">
								<?php esc_html_e('Retrieve data for table from the database. This feature allows you display table and its fields data from any table of WP database or External databases on the front-end.', 'publish-your-table'); ?>
							</div>
							<?php 
								if ($this->is_pro) {
									DispatcherPyt::doAction('tablesIncludeTpl', 'tablesNewSql');
								} else { 
									include 'tablesProFeature.php';
								}
							?>
						</div>
					</div>
				</div>
			</div>
			<div class="row row-options-block options-for-save">
				<?php 
				HtmlPyt::hidden('type', array('value' => '0', 'attrs' => 'id="pytTableType"'));
				HtmlPyt::hidden('creation', array('value' => '0', 'attrs' => 'id="pytTableCreation"'));
				HtmlPyt::hidden('builder', array('value' => '0'));
				HtmlPyt::hidden('mod', array('value' => 'tables'));
				HtmlPyt::hidden('action', array('value' => 'saveNewTable')); 
				?>
			</div>
			<div class="pubydoc-clear"></div>
		</div>
	</form>
</section>
<section class="pubydoc-bar">
	<ul class="pubydoc-bar-controls">
		<li class="pubydoc-right">
			<button class="button button-primary" id="pytNewTableFormSave">
				<i class="fa fa-fw fa-save"></i>
				<?php esc_html_e('Create the table', 'publish-your-table'); ?>
			</button>
		</li>
	</ul>
	<div class="pubydoc-clear"></div>
</section>
