<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="block-tab" id="block-tab-builder">
	
	<div id="builderLoading">
		<i class="fa fa-fw fa-spin fa-circle-o-notch"></i>
		<?php esc_html_e('Loading your table, please wait...', 'publish-your-table'); ?>
	</div>
	<div class="row pyt-builder-content">
		<div class="col-12" id="pyt-builder">
		<?php
			switch ($this->type) {
				case '0':
					include 'tablesEditTabBuilderToolbar.php';
					break;
				case '1':
					if ($this->is_pro) {
						DispatcherPyt::doAction('tablesIncludeTpl', 'tablesEditGoogle', array('source' => $this->source));
					} else { 
						include 'tablesProFeature.php';
					}
					break;
				case '3':
					if ($this->is_pro) {
						DispatcherPyt::doAction('tablesIncludeTpl', 'tablesEditDatabase', array('source' => $this->source));
					} else { 
						include 'tablesProFeature.php';
					}
					break;
				default:
					break;
			} ?>

			<div id="pytBuilderTable"></div>

			<div class="pubydoc-hidden">
			<?php if ($this->full_builder) { ?>
				<div id="pytDialogColumnsWidth" title="<?php esc_attr_e('Resize Column', 'publish-your-table'); ?>" data-save="<?php esc_attr_e('Apply', 'publish-your-table'); ?>" data-cancel="<?php esc_attr_e('Cancel', 'publish-your-table'); ?>" data-clear="<?php esc_attr_e('Clear Fixed Width', 'publish-your-table'); ?>">
					<p><?php esc_attr_e('Set columns width in pixels or percents. Press "Clear Fixed Width" to clear fixed columns width for all table columns. It’s better to use values in px, if you want width to be exactly the same as you’ve established – because the percentages can be re-calculated by browser in a specific way.'); ?>
					<div class="dialog-form"></div>
					<div class="column-row-default column-row">
						<div class="column-title pubydoc-width80"><?php esc_html_e('Column', 'publish-your-table'); ?><span class="column-num"></span></div>
						<div class="column-width pubydoc-width100"><input type="number" class="pubydoc-width80" name="width" value="" min='0'></div>
						<div class="column-point"><input type="radio" value="px" checked>px <input type="radio" name="point" value="%">%</div>
					</div>
				</div>
				<div id="pytDialogInsertLink" title="<?php esc_attr_e('Insert Link', 'publish-your-table'); ?>">
					<div class="dialog-form">
						<div class="pyt-input-group">
							<label><?php esc_html_e('Url', 'publish-your-table'); ?></label>
							<input type="text" class="url">
						</div>
						<div class="pyt-input-group">
							<label><?php esc_html_e('Link Text', 'publish-your-table'); ?></label>
							<input type="text" class="link-text">
						</div>
						<div class="pyt-input-group">
							<label>
								<input type="checkbox" class="link-target">
								<span><?php esc_html_e('Open in new tab', 'publish-your-table'); ?></span>
							</label>
						</div>
					</div>
				</div>
				<div id="pytDialogComment" title="<?php esc_attr_e('Cell comment', 'publish-your-table'); ?>">
					<div class="dialog-form">
						<div class="pyt-input-group">
							<textarea class="cell-comment"></textarea>
						</div>
					</div>
				</div>
				<div id="pytDialogDataType" data-title-cell="<?php esc_attr_e('Cells type', 'publish-your-table'); ?>" data-title-column="<?php esc_attr_e('Column type', 'publish-your-table'); ?>">
					<div class="dialog-form">
						<div class="pyt-input-group">
							<label><?php esc_html_e('Select data type', 'publish-your-table'); ?></label>
							<?php 
								HtmlPyt::selectbox('', array(
									'options' => array_merge(array(
										//'multi' => array('label' => __('Multi', 'publish-your-table'), 'attrs' => 'data-for="row"'),
										'' => array('label' => __('Default - column type', 'publish-your-table'), 'attrs' => 'data-for="row"'),
										), $this->col_types),
									'attrs' => 'id="pytDataType"'
								));
								?>
							<div class="pyt-warning" data-warning="multi">
								<?php esc_html_e('The selected cells contain different data types. To edit and set all selected cells to the same type, select it in the list above.', 'publish-your-table'); ?>
							</div>
						</div>
						<?php include 'tablesEditTabBuilderFormats.php';?>
					</div>
				</div>
				<div id="pytDialogHtmlEditor" title="<?php esc_attr_e('Edit Data', 'publish-your-table'); ?>">
					<textarea id="pytFieldHtml" class="pyt-field-html"></textarea>
				</div>
				<div id="pytDialogRowEditor" title="<?php esc_attr_e('Edit Data', 'publish-your-table'); ?>">
					<div class="pyt-fields-wrap">
					</div>
				</div>
			<?php 
				} else {
					HtmlPyt::selectFontList('', array(
						'attrs' => 'id="tbeFontFamily" class="pubydoc-tooltip tool" data-method="family" data-event="change" title="' . __('Font Family', 'publish-your-table') . '"'
					));
					HtmlPyt::selectbox('', array(
						'options' => range(5, 100, 1),
						'key' => 'value',
						'add' => 'px',
						'default' => __('Default', 'publish-your-table'),
						'attrs' => 'id="tbeFontSize" class="pubydoc-tooltip tool" data-method="size" data-event="change" title="' . __('Font Size', 'publish-your-table') . '"'
					));
				} 
				?>
				<div id="pytDialogColSettings" title="<?php esc_attr_e('Column Settings', 'publish-your-table'); ?>">
					<div class="pubydoc-clear">
						<ul class="pubydoc-grbtn tbs-col-tabs">
						<?php foreach ($this->col_tabs as $key => $data) { ?>
							<li>
								<a href="#block-tab-<?php echo esc_attr($key); ?>" class="button button-small <?php echo isset($data['class']) ? esc_attr($data['class']) : ''; ?>">
									<?php echo esc_html($data['label']); ?>
								</a>
							</li>
						<?php } ?>
						</ul>
					</div>
					<div class="block-tab" id="block-tab-general">
						<div class="dialog-form">
							<div class="pyt-input-group">
								<label><?php esc_html_e('Column name', 'publish-your-table'); ?></label>
								<input type="text" data-prop="title">
							</div>
							<div class="pyt-prop-block pyt-input-group">
								<label><?php esc_html_e('Data type', 'publish-your-table'); ?></label>
								<?php 
									HtmlPyt::selectbox('', array(
										'options' => $this->col_types,
										'attrs' => 'data-id="pytDataType" data-prop="pytType.type"'
									));
									?>
							</div>
							<?php include 'tablesEditTabBuilderFormats.php';?>
							<div class="pyt-input-group">
								<label>
									<?php esc_html_e('Responsive breakdowns', 'publish-your-table'); ?>
									<i class="fa fa-question pubydoc-tooltip" title="<?php esc_attr_e('<b>Always show in all devices:</b> display column on every device.<br /><br /><b>Hidden On Desktop:</b> display columns on all devices except PC. On PC, the column will be hidden under the plus sign (+).<br /><br /><b>Initial Hidden Mobile:</b> display columns on all devices except mobile. On mobile, the column is hidden under the plus sign (+). If a visitor clicks the plus sign (+), their details will be shown in a hidden column.<br /><br /><b>Initial Hidden Mobile and Tab:</b> display the column on all devices except mobile and tabs. On mobile and tabs, the column will be hidden under the plus sign (+).<br /><br /><b>Initial Hidden Mobile, Tab and Regular Computers:</b> the column data will be hidden under the plus sign (+) on each device.<br /><br /><b>Totally hidden on all devices:</b> hide column on all devices.<br><br><b>Important! </b>Automatic column hiding mode must be enabled for using this feature.', 'publish-your-table'); ?>"></i>
								</label>
								<?php 
									HtmlPyt::selectbox('', array(
										'options' => $this->col_respons,
										'attrs' => 'data-prop="pytRespons"'
									));
									?>
							</div>
						</div>
					</div>
					<div class="block-tab" id="block-tab-advanced">
						<div class="dialog-form">
							<div class="pyt-input-group">
								<label><?php esc_html_e('Column extra classes', 'publish-your-table'); ?></label>
								<input type="text" data-prop="cls">
							</div>
							<div class="pyt-input-group">
								<label><?php esc_html_e('Column width', 'publish-your-table'); ?></label>
								<div class="column-width">
									<input type="number" class="pubydoc-width80" data-prop="width.width" value="" min='0'>
									<input type="radio" name="colWidthPoints" value="" data-prop="width.points" data-default="">px 
									<input type="radio" name="colWidthPoints" value="%" data-prop="width.points">%
								</div>
							</div>
							<div class="pyt-input-row">
								<div class="pyt-input-group">
									<label>
										<input type="checkbox" data-prop="searchable" value="1" data-notchecked="0">
										<span><?php esc_html_e('Filterable', 'publish-your-table'); ?></span>
									</label>
								</div>
								<div class="pyt-input-group">
									<label>
										<?php
											if ($this->is_pro) {
												echo '<input type="checkbox" data-prop="pytFront" value="editable" data-notchecked="">';
											} else {
												HtmlPyt::proOptionLink();
											}
										?>
										<span><?php esc_html_e('Editable', 'publish-your-table'); ?></span>
									</label>
								</div>
							</div>
							<div class="pyt-input-row">
								<div class="pyt-input-group">
									<label>
										<input type="checkbox" data-prop="sortable" value="1" data-notchecked="0">
										<span><?php esc_html_e('Sortable', 'publish-your-table'); ?></span>
									</label>
								</div>
								
							</div>
						</div>
					</div>
					<div class="block-tab" id="block-tab-header">
						<div class="dialog-form">
							<div class="pyt-input-group">
								<label><?php esc_html_e('Header extra classes', 'publish-your-table'); ?></label>
								<input type="text" data-prop="clsHead">
							</div>
							<div class="pyt-input-row">
								<div class="pyt-input-group">
									<label><?php esc_html_e('Alignment', 'publish-your-table'); ?></label>
									<?php 
										$aligns = array('' => __('Default', 'publish-your-table'), 'left' => __('Left', 'publish-your-table'), 'center' => __('Center', 'publish-your-table'), 'right' => __('Right', 'publish-your-table'));
										HtmlPyt::selectbox('', array(
											'options' => $aligns,
											'attrs' => 'data-prop="styleHead.text-align" class="pubydoc-width150"'
										));
										?>
								</div>
								<div class="pyt-input-group">
									<label><?php esc_html_e('Vertical alignment', 'publish-your-table'); ?></label>
									<?php 
										$valigns = array('' => __('Default', 'publish-your-table'), 'top' => __('Top', 'publish-your-table'), 'middle' => __('Middle', 'publish-your-table'), 'bottom' => __('Bottom', 'publish-your-table'));
										HtmlPyt::selectbox('', array(
											'options' => $valigns,
											'attrs' => 'data-prop="styleHead.vertical-align" class="pubydoc-width150"'
										));
										?>
								</div>
							</div>
							<div class="pyt-input-group">
								<label><?php esc_html_e('Colors', 'publish-your-table'); ?></label>
								<?php 
									HtmlPyt::colorPicker('', array(
										'label' => __('Background', 'publish-your-table'),
										'attrs' => 'data-prop="styleHead.background-color"'
									));
									HtmlPyt::colorPicker('', array(
										'label' => __('Text color', 'publish-your-table'),
										'attrs' => 'data-prop="styleHead.color"'
									));
								?>
							</div>
							<div class="pyt-input-row">
								<div class="pyt-input-group">
									<label><?php esc_html_e('Font family', 'publish-your-table'); ?></label>
									<?php 
										if ($this->is_pro) {
											HtmlPyt::selectbox('', array(
												'options' => array(),
												'attrs' => 'data-prop="styleHead.font-family" class="pubydoc-width150" id="cHeaderFontFamily"'
											));
										} else {
											HtmlPyt::proOptionLink();
										}
										?>
								</div>
								<div class="pyt-input-group">
									<label><?php esc_html_e('Font size', 'publish-your-table'); ?></label>
									<?php 
										HtmlPyt::selectbox('', array(
											'options' => array(),
											'attrs' => 'data-prop="styleHead.font-size" class="pubydoc-width150" id="cHeaderFontSize"'
										));
										?>
								</div>
							</div>
							<div class="pyt-input-row">
								<div class="pyt-input-group">
									<label>
										<input type="checkbox" data-prop="styleHead.font-weight" value="bold">
										<span><?php esc_html_e('Bold', 'publish-your-table'); ?></span>
										<input type="checkbox" data-prop="styleHead.font-style" value="italic">
										<span><?php esc_html_e('Italic', 'publish-your-table'); ?></span>
									</label>
								</div>
								<div class="pyt-input-group">
									<label>
										<input type="checkbox" data-prop="styleHead.text-decoration" value="underline">
										<span><?php esc_html_e('Underline', 'publish-your-table'); ?></span>
									</label>
								</div>
							</div>
						</div>
					</div>
					<div class="block-tab" id="block-tab-body">
						<div class="dialog-form">
							<div class="pyt-input-row">
								<div class="pyt-input-group">
									<label><?php esc_html_e('Alignment', 'publish-your-table'); ?></label>
									<?php 
										HtmlPyt::selectbox('', array(
											'options' => $aligns,
											'attrs' => 'data-prop="style.text-align" class="pubydoc-width150"'
										));
										?>
								</div>
								<div class="pyt-input-group">
									<label><?php esc_html_e('Vertical alignment', 'publish-your-table'); ?></label>
									<?php 
										HtmlPyt::selectbox('', array(
											'options' => $valigns,
											'attrs' => 'data-prop="pytValign" class="pubydoc-width150"'
										));
										?>
								</div>
							</div>
							<div class="pyt-input-group">
								<label><?php esc_html_e('Colors', 'publish-your-table'); ?></label>
								<?php 
									HtmlPyt::colorPicker('', array(
										'label' => __('Background', 'publish-your-table'),
										'attrs' => 'data-prop="style.background-color"'
									));
									HtmlPyt::colorPicker('', array(
										'label' => __('Text color', 'publish-your-table'),
										'attrs' => 'data-prop="style.color"'
									));
								?>
							</div>
							<div class="pyt-input-row">
								<div class="pyt-input-group">
									<label><?php esc_html_e('Font family', 'publish-your-table'); ?></label>
									<?php 
										if ($this->is_pro) {
											HtmlPyt::selectbox('', array(
												'options' => array(),
												'attrs' => 'data-prop="style.font-family" class="pubydoc-width150" id="cBodyFontFamily"'
											));
										} else {
											HtmlPyt::proOptionLink();
										}
										?>
								</div>
								<div class="pyt-input-group">
									<label><?php esc_html_e('Font size', 'publish-your-table'); ?></label>
									<?php 
										HtmlPyt::selectbox('', array(
											'options' => array(),
											'attrs' => 'data-prop="style.font-size" class="pubydoc-width150" id="cBodyFontSize"'
										));
										?>
								</div>
							</div>
							<div class="pyt-input-row">
								<div class="pyt-input-group">
									<label>
										<input type="checkbox" data-prop="style.font-weight" value="bold">
										<span><?php esc_html_e('Bold', 'publish-your-table'); ?></span>
										<input type="checkbox" data-prop="style.font-style" value="italic">
										<span><?php esc_html_e('Italic', 'publish-your-table'); ?></span>
									</label>
								</div>
								<div class="pyt-input-group">
									<label>
										<input type="checkbox" data-prop="style.text-decoration" value="underline">
										<span><?php esc_html_e('Underline', 'publish-your-table'); ?></span>
									</label>
								</div>
							</div>
						</div>
					</div>
					<div class="block-tab" id="block-tab-conditional">
					<?php 
						if ($this->is_pro) {
							DispatcherPyt::doAction('tablesIncludeTpl', 'tablesEditBuilderConditions');
						} else { 
							include 'tablesProFeature.php';
						}
						?>
					</div>
				</div>
				<?php /*DispatcherPyt::doAction('diagramsIncludeTpl', 'diagramsEditDiagram', array('tables' => array()));*/ ?>
			</div>
			<?php DispatcherPyt::doAction('tablesIncludeTpl', 'tablesEditBuilder', array('full_builder' => $this->full_builder, 'fromBuilder' => true)); ?>
		</div>
	</div>
	<?php 
		HtmlPyt::hidden('', array(
			'value' => json_encode($this->builder_settings),
			'attrs' => 'id="pytBuilderSettings"')
		);
		?>
</div>
