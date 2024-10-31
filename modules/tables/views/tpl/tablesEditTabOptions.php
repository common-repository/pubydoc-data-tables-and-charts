<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
	$options = $this->options;
	$styles = UtilsPyt::getArrayValue($options, 'styles', array(), 2);
	$searches = UtilsPyt::getArrayValue($options, 'searches', array(), 2);
	$sortings = UtilsPyt::getArrayValue($options, 'sortings', array(), 2);
	$formats = UtilsPyt::getArrayValue($options, 'formats', array(), 2);
	$pages = UtilsPyt::getArrayValue($options, 'pages', array(), 2);
	$langs = UtilsPyt::getArrayValue($options, 'langs', array(), 2);
?>
<div class="block-tab" id="block-tab-options">
	<div class="row pyt-options-content">
		<div class="col-12 mt-n2">
			<div class="pyt-options-section pt-2">
				<ul class="pyt-options-tabs">
					<li><a href="#pyt-tab-options-main" class="current"><?php esc_html_e('Main', 'publish-your-table'); ?></a></li>
					<li><a href="#pyt-tab-options-features"><?php esc_html_e('Features', 'publish-your-table'); ?></a></li>
					<li><a href="#pyt-tab-options-appearance"><?php esc_html_e('Appearance', 'publish-your-table'); ?></a></li>
					<li><a href="#pyt-tab-options-text"><?php esc_html_e('Text', 'publish-your-table'); ?></a></li>
				</ul>
				<div class=" pubydoc-clear"></div>
				<div class="pyt-options-wrap">
					<div class="pyt-options-block" id="pyt-tab-options-main">
						<div class="pyt-options-title">
							<?php esc_html_e('Table Elements', 'publish-your-table'); ?>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Check here if you want to show the name of the table above the table', 'publish-your-table'); ?>">
								<?php esc_html_e('Caption', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[caption]', array(
										'checked' => UtilsPyt::getArrayValue($options, 'caption', 0, 1),
									));
									?>
							</div>
						</div>
						<?php 
							$parent = UtilsPyt::getArrayValue($options, 'description', 0, 1);
							$classHidden = $parent ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('You can add short description to the table between title and table', 'publish-your-table'); ?>">
								<?php esc_html_e('Description', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[description]', array(
										'checked' => $parent,
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[description]">
							<div class="pyt-option-big">
								<?php 
									HtmlPyt::textarea('options[description_text]', array(
										'value' => UtilsPyt::getArrayValue($options, 'description_text'),
										'placeholder' => __('Enter description text', 'publish-your-table')
									));
									?>
							</div>
						</div>
						<?php 
							$parent = UtilsPyt::getArrayValue($options, 'signature', 0, 1);
							$classHidden = $parent ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('You can add signature under table footer', 'publish-your-table'); ?>">
								<?php esc_html_e('Signature', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[signature]', array(
										'checked' => $parent,
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[signature]">
							<div class="pyt-option-big">
								<?php 
									HtmlPyt::textarea('options[signature_text]', array(
										'value' => UtilsPyt::getArrayValue($options, 'signature_text'),
										'placeholder' => __('Enter signature text', 'publish-your-table')
									));
									?>
							</div>
						</div>
						<?php 
							$parent = UtilsPyt::getArrayValue($options, 'header', 0, 1);
							$classHidden = $parent ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label">
								<?php esc_html_e('Header', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[header]', array(
										'checked' => $parent,
									));
									?>
							</div>
						</div>
						<?php /*if ($this->type == 1) { ?>
							<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[header]">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Count of table rows, which will be added to header', 'publish-your-table'); ?>">
									<?php esc_html_e('Count additional rows', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-value">
									<?php 
										HtmlPyt::number('options[header_rows]', array(
											'value' => UtilsPyt::getArrayValue($options, 'header_rows', 1, 1),
											'attrs' => 'min="0"'
										));
										?>
								</div>
							</div>
						<?php }*/ ?>
						<?php 
							$parent = UtilsPyt::getArrayValue($options, 'footer', 0, 1);
							$classHidden = $parent ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label">
								<?php esc_html_e('Footer', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[footer]', array(
										'checked' => $parent,
									));
									?>
							</div>
						</div>
						<?php 
							$parent = UtilsPyt::getArrayValue($options, 'custom_footer', 0, 1);
							$subHidden = $parent ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[footer]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('If checked - footer will be created from the last table rows. Otherwise - footer will be created from header rows.', 'publish-your-table'); 
								if ($this->type == 3) {
									echo '<br><br><b>';
									esc_attr_e('Important!', 'publish-your-table'); echo '</b> '; esc_attr_e('For tables built on the basis of a query from the database: the option will work only if Unique Fields are specified.', 'publish-your-table');
								}
								?>">
								<?php esc_html_e('Custom footer', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[custom_footer]', array(
										'checked' => $parent,
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($subHidden); ?>" data-parent="options[custom_footer]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Count of table rows, which will be moved to footer.', 'publish-your-table'); ?>">
								<?php esc_html_e('Count of footer rows', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::number('options[footer_rows]', array(
										'value' => UtilsPyt::getArrayValue($options, 'footer_rows', 1, 1),
										'attrs' => 'min="1"'
									));
									?>
							</div>
						</div>
						<?php 
							$fHeader = UtilsPyt::getArrayValue($options, 'fixed_header', 0, 1);
							$classHidden = $fHeader ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Allows to fix the table\'s header during table scrolling. <br><br><b>Important!</b> Header option must be enabled for using this feature. Also you need to set Fixed Table Height to create a vertical scroll for your table. To see the work of this feature you should not use such Responsive Modes such as Standard and Automatic columns hiding.', 'publish-your-table'); ?>">
								<?php esc_html_e('Fixed header', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[fixed_header]', array(
										'checked' => $fHeader,
									));
									?>
							</div>
						</div>
						<?php 
							$fFooter = UtilsPyt::getArrayValue($options, 'fixed_footer', 0, 1);
							$classHidden = $fFooter ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Allows to fix the table\'s footer during table scrolling. <br><br><b>Important!</b> Footer option must be enabled for using this feature. Also you need to set Fixed Table Height to create a vertical scroll at the table. To see the work of this feature you should not use such Responsive Modes as Standard and Automatic columns hiding.', 'publish-your-table'); ?>">
								<?php esc_html_e('Fixed footer', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[fixed_footer]', array(
										'checked' => $fFooter,
									));
									?>
							</div>
						</div>
						<?php 
							$classHidden = $fHeader || $fFooter ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[fixed_header] options[fixed_footer]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Fixed table height in px. This value must be less than the original table height to create a vertical scroll, otherwise you will not see that the fixed header / footer exists.', 'publish-your-table'); ?>">
								<?php esc_html_e('Fixed table height', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::number('options[footer_height]', array(
										'value' => UtilsPyt::getArrayValue($options, 'footer_height', 400, 1),
										'attrs' => 'min="1"'
									));
									?>
							</div>
						</div>
						<?php 
							$parent = UtilsPyt::getArrayValue($options, 'fixed_columns', 0, 1);
							$classHidden = $parent ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Allows to fix columns during table scrolling. <br><br><b>Important!</b> The fixing of columns suggests that the table will have a horisontal scroll type of responsive mode, otherwise you will not see that the fixed columns exist. So this feature is a kind of responsive mode on its own and will not work with such Responsive Modes as Standard and Automatic columns hiding.', 'publish-your-table'); ?>">
								<?php esc_html_e('Fixed columns', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[fixed_columns]', array(
										'checked' => $parent,
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[fixed_columns]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Number of column to fix by left side of the table.', 'publish-your-table'); ?>">
								<?php esc_html_e('Left columns count', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::number('options[fixed_left]', array(
										'value' => UtilsPyt::getArrayValue($options, 'fixed_left', 1, 1),
										'attrs' => 'min="0"'
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[fixed_columns]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Number of column to fix by right side of the table.', 'publish-your-table'); ?>">
								<?php esc_html_e('Right columns count', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::number('options[fixed_right]', array(
										'value' => UtilsPyt::getArrayValue($options, 'fixed_right', 0, 1),
										'attrs' => 'min="0"'
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Add index (row number) to table.', 'publish-your-table'); ?>">
								<?php esc_html_e('Auto index', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::selectbox('options[auto_index]', array(
										'options' => array('' => __('No index', 'publish-your-table'), 'first' => __('Use first column', 'publish-your-table'), 'new' => __('Create new column', 'publish-your-table')),
										'value' => UtilsPyt::getArrayValue($options, 'auto_index'),
									));
									?>
							</div>
						</div>
					</div>
					
					<div class="pyt-options-block">
						<div class="pyt-options-title">
							<?php esc_html_e('Options', 'publish-your-table'); ?>
						</div>
						<?php if (empty($this->type)) { ?>
							<div class="pyt-option-wrapper" data-not-preview="1">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('This feature is necessary for those cases, when table contains the shortcodes. By checking the box, you can make sure that they will be rendered correctly and won\'t be influenced by cache.', 'publish-your-table'); ?>">
									<?php esc_html_e('Disable table cache', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-value">
									<?php 
										HtmlPyt::checkbox('options[disable_cache]', array(
											'checked' => UtilsPyt::getArrayValue($options, 'disable_cache', 0, 1),
										));
										?>
								</div>
							</div>
						<?php } ?>
						<div class="pyt-option-wrapper" data-not-preview="1">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Disable indexing table for search bots', 'publish-your-table'); ?>">
								<?php esc_html_e('Disallow indexing', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[disallow_index]', array(
										'value' => UtilsPyt::getArrayValue($options, 'disallow_index', 0, 1),
									));
									?>
							</div>
						</div>
					</div>
					<div class="pyt-options-block">
						<div class="pyt-options-title">
							<?php esc_html_e('Data formats', 'publish-your-table'); ?>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Set decimal delimiter for numbers', 'publish-your-table'); ?>">
								<?php esc_html_e('Decimal delimiter', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::selectbox('options[formats][dec]', array(
										'options' => array('.' => '.', ',' => ','),
										'value' => UtilsPyt::getArrayValue($formats, 'dec', '.'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Set thousands delimiter for numbers', 'publish-your-table'); ?>">
								<?php esc_html_e('Thousands delimiter', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::selectbox('options[formats][thn]', array(
										'options' => array(',' => ',', '.' => '.', ' ' => ' ' . __('Space', 'publish-your-table')),
										'value' => UtilsPyt::getArrayValue($formats, 'thn', ','),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Set default output format for numbers e.g. 1,000.00, 1.00, 1,000', 'publish-your-table'); ?>">
								<?php esc_html_e('Number', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::text('options[formats][number]', array(
										'value' => UtilsPyt::getArrayValue($formats, 'number', '1,000.00'),
										'attrs' => 'data-type="number"'
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php echo esc_attr(__('Set default output format for currencies. For example:', 'publish-your-table') . '<br /> $ 1,000.000<br /> € 1.00'); ?>">
								<?php esc_html_e('Currency', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::text('options[formats][money]', array(
										'value' => UtilsPyt::getArrayValue($formats, 'money', '$1,000.00'),
										'attrs' => 'data-type="money"'
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php echo esc_attr(__('Set default output format for percent numbers. For example:', 'publish-your-table') . '<br /> 10.00%<br /> 10%'); ?>">
								<?php esc_html_e('Percent', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::text('options[formats][percent]', array(
										'value' => UtilsPyt::getArrayValue($formats, 'percent', '1.00%'),
										'attrs' => 'data-type="percent"'
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php echo esc_attr(__('Set default output format for date. For example:', 'publish-your-table') . '<br /> yy-mm-dd - 1991-12-25<br /> dd.mm.y - 25.12.91'); ?>">
								<?php esc_html_e('Date', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::text('options[formats][date]', array(
										'value' => UtilsPyt::getArrayValue($formats, 'date', 'dd.mm.yy'),
										'attrs' => 'data-type="date"'
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php echo esc_attr(__('Set output format for time and duration. For example:', 'publish-your-table') . '<br />
											 1) ' . __('time', 'publish-your-table') . '<br />
											 HH:mm - 18:00<br />
											 hh:mm a - 9:00 pm<br /><br />
											 2) ' . __('duration', 'publish-your-table') . '<br />
											 hh:mm - 36:40<br />
											 hh:mm:ss - 36:40:12'); ?>">
								<?php esc_html_e('Time / Duration', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::text('options[formats][time]', array(
										'value' => UtilsPyt::getArrayValue($formats, 'time', 'HH:mm'),
										'attrs' => 'data-type="time"'
									));
									?>
							</div>
						</div>
					</div>
					<div class="pyt-options-block" id="pyt-tab-options-features">
						<div class="pyt-options-title">
							<?php esc_html_e('General features', 'publish-your-table'); ?>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php echo esc_attr(__('Standard Responsive mode - in this mode if table content doesn\'t fit all columns become under each other with one cell per row', 'publish-your-table') . '<br><br>' .
											__('Automatic column hiding - in this mode table columns will collapse from right to left if content does not fit to parent container width', 'publish-your-table') . '<br><br>' .
											__('Horizontal scroll - in this mode scroll bar will be added if table overflows parent container width', 'publish-your-table') . '<br><br>' .
											__('Disable Responsivity - default table fluid layout', 'publish-your-table') . '<br>'); ?>">
								<?php esc_html_e('Responsive mode', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::selectbox('options[responsive_mode]', array(
										'options' => array(
											0 => __('Standard responsive mode', 'publish-your-table'),
											1 => __('Automatic column hiding', 'publish-your-table'),
											2 => __('Horizontal scroll', 'publish-your-table'),
											3 => __('Disable responsivity', 'publish-your-table')),
										'value' => UtilsPyt::getArrayValue($options, 'responsive_mode', 0, 1),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Table information display field', 'publish-your-table'); ?>">
								<?php esc_html_e('Table information', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[info]', array(
										'checked' => UtilsPyt::getArrayValue($options, 'info', 0, 1),
									));
									?>
							</div>
						</div>
						<?php 
							$parent = UtilsPyt::getArrayValue($options, 'ordering', 0, 1);
							$classHidden = $parent ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('To allow dynamic sorting with arrows you must enable Header option. Note that if there are merged cells in the table sorting will be disabled.', 'publish-your-table'); ?>">
								<?php esc_html_e('Sorting', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[ordering]', array(
										'checked' => $parent,
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[ordering]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Number of column to apply sort order. Set no value to disable table sorting by default.', 'publish-your-table'); ?>">
								<?php esc_html_e('Sorting column', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::number('options[sortings][column]', array(
										'value' => UtilsPyt::getArrayValue($sortings, 'column', '', 1),
										'attrs' => 'min="1"'
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[ordering]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Set sort order by default', 'publish-your-table'); ?>">
								<?php esc_html_e('Sorting order', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::selectbox('options[sortings][order]', array(
										'options' => array('asc' => __('Ascending', 'publish-your-table'), 'desc' => __('Descending', 'publish-your-table')),
										'value' => UtilsPyt::getArrayValue($sortings, 'order', 'asc'),
									));
									?>
							</div>
						</div>
						<?php 
							$parent = UtilsPyt::getArrayValue($options, 'paging', 0, 1);
							$classHidden = $parent ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label">
								<?php esc_html_e('Pagination', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[paging]', array(
										'checked' => $parent,
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[paging]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Show drop down list to select the number of rows on the page.', 'publish-your-table'); ?>">
								<?php esc_html_e('Pagination menu', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[pages][dropdown]', array(
										'checked' => UtilsPyt::getArrayValue($pages, 'dropdown', 0, 1),
									));
									?>
							</div>
						</div>
						<?php 
							$parent = UtilsPyt::getArrayValue($pages, 'dropdown', 0, 1);
							$subHidden = $parent ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($subHidden); ?>" data-parent="options[pages][dropdown]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Here you can set the number of rows to display on one Pagination page. Establish several numbers separated by comma to let users choose it personally. First number will be displayed by default. Since that the number of Pagination Pages will be recounted also.', 'publish-your-table'); ?>">
								<?php esc_html_e('Pagination list content', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-big">
								<?php 
									HtmlPyt::text('options[pages][menu]', array(
										'value' => UtilsPyt::getArrayValue($pages, 'menu', '50,100,All'),
									));
									?>
							</div>
						</div>
						<?php 
							$subHidden = $parent ? 'pytHidden' : '';
						?>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($subHidden); ?>" data-parent-reverse="options[pages][dropdown]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Here you can set the number of rows to display on one Pagination page.', 'publish-your-table'); ?>">
								<?php esc_html_e('Rows per page', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::number('options[pages][rows]', array(
										'value' => UtilsPyt::getArrayValue($pages, 'rows', 5, 1),
										'attrs' => 'min="0"'
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[paging]">
							<div class="pyt-option-label">
								<?php esc_html_e('Pagination size', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::selectbox('options[pages][size]', array(
										'options' => array('large' => __('Large', 'publish-your-table'), 'medium' => __('Medium', 'publish-your-table'), 'small' => __('Small', 'publish-your-table')),
										'value' => UtilsPyt::getArrayValue($pages, 'size', 'large'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[paging]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Scroll to table top for pagination change', 'publish-your-table'); ?>">
								<?php esc_html_e('Scroll top by pagination', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[pages][scroll]', array(
										'checked' => UtilsPyt::getArrayValue($pages, 'scroll', 0, 1),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[paging]" data-need-save="1">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('This option is recommended for a large tables that cannot be processed in conventional way. The table will be sequentially loaded by ajax on a per page basis, all filtering, ordering and search clauses is server-side implemented too.', 'publish-your-table'); ?>">
								<?php esc_html_e('Server-side processing', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[ssp]', array(
										'checked' => UtilsPyt::getArrayValue($options, 'ssp', 0, 1),
									));
									?>
							</div>
						</div>
						<?php 
							$parent = UtilsPyt::getArrayValue($options, 'searching', 0, 1);
							$classHidden = $parent ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label">
								<?php esc_html_e('Searching', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[searching]', array(
										'checked' => $parent,
									));
									?>
							</div>
						</div>
						<?php 
							$sColumn = UtilsPyt::getArrayValue($searches, 'columns', 0, 1);
							$sClassHidden = $sColumn ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[searching]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Add search by table columns. Use a semicolon as separator for select any of the values.', 'publish-your-table'); ?>">
								<?php esc_html_e('Search by columns', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[searches][columns]', array(
										'checked' => $sColumn,
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($sClassHidden); ?>" data-parent="options[searches][columns]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Here you can choose where the column search fields will be: at the top or bottom of the table.', 'publish-your-table'); ?>">
								<?php esc_html_e('Location of search fields', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::selectbox('options[searches][fields_position]', array(
										'options' => array('bottom' => __('Bottom', 'publish-your-table'), 'top' => __('Top', 'publish-your-table')),
										'value' => UtilsPyt::getArrayValue($searches, 'fields_position', 'bottom'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[searching]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Lets make search by fields, marked as hidden (see appropriate button on Extended builder toolbar)', 'publish-your-table'); ?>">
								<?php esc_html_e('Search by hidden fields', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[searches][search_hidden]', array(
										'value' => UtilsPyt::getArrayValue($searches, 'search_hidden', 0, 1),
									));
									?>
							</div>
						</div>
						<?php 
							$sResult = UtilsPyt::getArrayValue($searches, 'result_only', 0, 1);
							$sClassHidden = $sColumn ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[searching]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Hide table by default and show only if search has a result', 'publish-your-table'); ?>">
								<?php esc_html_e('Show only search results', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[searches][result_only]', array(
										'value' => $sResult,
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($sClassHidden); ?>" data-parent="options[searches][result_only]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Table will not be hidden by default, but will be empty', 'publish-your-table'); ?>">
								<?php esc_html_e('Show empty table', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[searches][show_table]', array(
										'value' => UtilsPyt::getArrayValue($searches, 'show_table', 0, 1),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[searching]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Display only entries with matching characters in the beginning of words', 'publish-your-table'); ?>">
								<?php esc_html_e('Strict matching', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[searches][strict]', array(
										'value' => UtilsPyt::getArrayValue($searches, 'strict', 0, 1),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[searching]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Set minimum count of characters to start search in Search field. Set 0 to make search in any case.', 'publish-your-table'); ?>">
								<?php esc_html_e('Minimum characters', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::number('options[searches][min_chars]', array(
										'value' => UtilsPyt::getArrayValue($searches, 'min_chars', 0, 1),
										'attrs' => 'min="0"'
									));
									?>
							</div>
						</div>
					</div>
					<div class="pyt-options-block">
						<div class="pyt-options-title">
							<?php esc_html_e('Frontend fields', 'publish-your-table'); ?>
						</div>
						<?php
							if ($this->is_pro) {
								DispatcherPyt::doAction('tablesIncludeTpl', 'tablesEditOptionsFeatures', array('options' => $options, 'type' => $this->type));
							} else { 
						?>
							<div class="pyt-option-wrapper">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php echo esc_attr_e('Save table data entered through frontend fields. Refer to the <i class="fa fa-fw fa-pencil-square-o"></i> buttons on Extended builder toolbar.', 'publish-your-table'); ?>">
									<?php esc_html_e('Save frontend fields', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-value">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							</div>
							<div class="pyt-option-wrapper">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php echo esc_attr_e('Adds a symbol ✓ to last edited cell', 'publish-your-table'); ?>">
									<?php esc_html_e('Mark last edited cell', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-value">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							</div>
							<div class="pyt-option-wrapper">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php echo esc_attr_e('Allows to use frontend fields only for logged in users', 'publish-your-table'); ?>">
									<?php esc_html_e('Use for logged in users only', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-value">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							</div>
							<div class="pyt-option-wrapper">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php echo esc_attr_e('Allows to use frontend fields only for users with selected roles. If there are no chosen roles - all logged in users will have ability to use the frontend fields.', 'publish-your-table'); ?>">
									<?php esc_html_e('Use for current roles only', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-value">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							</div>
							<div class="pyt-option-wrapper">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Select the file extension that will be available for upload.', 'publish-your-table'); ?>">
									<?php esc_html_e('Allow these file extensions', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-value">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							</div>
						<?php } ?>
					</div>
					<div class="pyt-options-block">
						<div class="pyt-options-title">
							<?php esc_html_e('Export', 'publish-your-table'); ?>
						</div>
						<?php
							if ($this->is_pro) {
								DispatcherPyt::doAction('tablesIncludeTpl', 'tablesEditOptionsExport', array('options' => $options, 'type' => $this->type));
							} else { 
						?>
							<div class="pyt-option-wrapper">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Allows to export table in pdf, csv, xls formats from the front-end. Choose needed formats.', 'publish-your-table'); ?>">
									<?php esc_html_e('Frontend export', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-big">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							</div>
							<div class="pyt-option-wrapper">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Select position of export links around of table', 'publish-your-table'); ?>">
									<?php esc_html_e('Export links position', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-value">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							</div>
							<div class="pyt-option-wrapper">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Allows you to export only visible data: filtered rows with sorting', 'publish-your-table'); ?>">
									<?php esc_html_e('Export only visible', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-value">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							</div>
							<div class="pyt-option-wrapper">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Choose the paper size for PDF pages', 'publish-your-table'); ?>">
									<?php esc_html_e('PDF paper size', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-value">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							</div>
							<div class="pyt-option-wrapper">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Choose the orientation for PDF pages', 'publish-your-table'); ?>">
									<?php esc_html_e('PDF page orientation', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-value">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							</div>
							<div class="pyt-option-wrapper">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Allows export to PDF file the fonts, which were set for table content via editor toolbar. Important! Custom fonts might not contain some specific characters (greek, cyrillic etc.), so after importing of fonts your PDF file might lost part of content.', 'publish-your-table'); ?>">
									<?php esc_html_e('Export fonts to PDF', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-value">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							</div>
							<div class="pyt-option-wrapper">
								<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Automticaly appends selected logo for output pdf or printing.', 'publish-your-table'); ?>">
									<?php esc_html_e('Export Logo', 'publish-your-table'); ?>
								</div>
								<div class="pyt-option-value">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							</div>
						<?php } ?>
					</div>
					<div class="pyt-options-block" id="pyt-tab-options-appearance">
						<div class="pyt-options-title">
							<?php esc_html_e('Appearance', 'publish-your-table'); ?>
						</div>
						<?php 
							$parent = UtilsPyt::getArrayValue($options, 'auto_width', 0, 1);
							$classHidden = $parent ? 'pytHidden' : '';
						?>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('If checked - width of table columns will be calculated automatically for table width 100%.<br /><br />
								Otherwise - you can set table width manually: columns width will be get from "Fixed table width" option
								(toolbar on Extended builder tab) or calculated depending on the columns width in the table editor.<br /><br />
								If you do not want to apply columns width at all - you should uncheck "Auto table width" option, set "Fixed table width"
								option to "auto" and check "Compact table" option.', 'publish-your-table'); ?>">
								<?php esc_html_e('Auto table width', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[auto_width]', array(
										'checked' => $parent,
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent-reverse="options[auto_width]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Set fixed table width in px or %. Choose &quot;disable&quot; to make table width adjust by table content.', 'publish-your-table'); ?>">
								<?php esc_html_e('Fixed table width', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-inline">
								<?php 
									HtmlPyt::number('options[table_width]', array(
										'value' => UtilsPyt::getArrayValue($options, 'table_width', 100, 1),
										'attrs' => 'min="0"'
									));
									HtmlPyt::radiobuttons('options[table_width_type]', array(
										'options' => array('%' => '%', 'px' => 'px', 'auto' => __('disable', 'publish-your-table')),
										'value' => UtilsPyt::getArrayValue($options, 'table_width_type', '%'),
										'no_br' => true
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent-reverse="options[auto_width]">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Set fixed table width in px or %. Choose &quot;disable&quot; to make table width adjust by table content.', 'publish-your-table'); ?>">
								<?php esc_html_e('Fixed table width (mobile)', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-inline">
								<?php 
									HtmlPyt::number('options[table_width_mobile]', array(
										'value' => UtilsPyt::getArrayValue($options, 'table_width_mobile', 100, 1),
										'attrs' => 'min="0"'
									));
									HtmlPyt::radiobuttons('options[table_width_type_mobile]', array(
										'options' => array('%' => '%', 'px' => 'px', 'auto' => __('disable', 'publish-your-table')),
										'value' => UtilsPyt::getArrayValue($options, 'table_width_type_mobile', '%'),
										'no_br' => true
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Decrease the amount of whitespace in the table', 'publish-your-table'); ?>">
								<?php esc_html_e('Compact table', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[compact]', array(
										'checked' => UtilsPyt::getArrayValue($options, 'compact', 0, 1),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Disable wrapping of content in the table, so every word in the cells will be in one single line.', 'publish-your-table'); ?>">
								<?php esc_html_e('Disable wrapping', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[nowrap]', array(
										'checked' => UtilsPyt::getArrayValue($options, 'nowrap', 0, 1),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('This mode allows you to separate the content into paragraphs. To move to a new line in the cell - please press CTRL + Enter.', 'publish-your-table'); ?>">
								<?php esc_html_e('Paragraph mode', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[paragraph_mode]', array(
										'checked' => UtilsPyt::getArrayValue($options, 'paragraph_mode', 0, 1),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Add Lightbox for images', 'publish-your-table'); ?>">
								<?php esc_html_e('Lightbox', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									if ($this->is_pro) {
										HtmlPyt::checkbox('options[lightbox]', array(
											'checked' => UtilsPyt::getArrayValue($options, 'lightbox', 0, 1),
										));
									} else {
										HtmlPyt::proOptionLink();
									}
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Cell - adds border around all four sides of each cell, Row - adds border only over and under each row. (i.e. only for the rows).', 'publish-your-table'); ?>">
								<?php esc_html_e('Borders', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::selectbox('options[border]', array(
										'options' => array('cell' => __('Cell', 'publish-your-table'), 'row' => __('Row', 'publish-your-table'), 'no' => __('None', 'publish-your-table')),
										'value' => UtilsPyt::getArrayValue($options, 'border', 'cell'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Add automatic highlight for table odd rows', 'publish-your-table'); ?>">
								<?php esc_html_e('Row striping', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[stripe]', array(
										'checked' => UtilsPyt::getArrayValue($options, 'stripe', 0, 1),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Row highlighting by mouse hover', 'publish-your-table'); ?>">
								<?php esc_html_e('Highlighting by mousehover', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[hover]', array(
										'checked' => UtilsPyt::getArrayValue($options, 'hover', 0, 1),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('If checked - the current sorted column will be highlighted', 'publish-your-table'); ?>">
								<?php esc_html_e('Highlight the order column', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[order-column]', array(
										'checked' => UtilsPyt::getArrayValue($options, 'order-column', 0, 1),
									));
									?>
							</div>
						</div>
						<?php 
							$loader = UtilsPyt::getArrayValue($options, 'loader_disable', 0, 1);
							$iconName = UtilsPyt::getArrayValue($options, 'loader_name', 'spinner');
							$iconCount = UtilsPyt::getArrayValue($options, 'loader_count', 0, 1);

							$classHidden = $loader ? 'pytHidden' : '';
						?>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Enable / disable table loader icon before table will be completely loaded.', 'publish-your-table'); ?>">
								<?php esc_html_e('Hide table loader', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[loader_disable]', array(
										'checked' => $loader,
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent-reverse="options[loader_disable]">
							<div class="pyt-option-label">
								<button class="button button-small pyt-loader-select"><?php esc_html_e('Choose icon', 'publish-your-table'); ?></button>
							</div>
							<div class="pyt-option-value">
								<div class="pyt-loader-preview">
									<?php 
										if ('spinner' === $iconName) {
											echo '<div class="pubydoc-table-loader spinner"></div>';
										} else {
											echo '<div class="pubydoc-table-loader la-' . esc_attr($iconName) . ' la-2x">';
											for ($i = 1; $i <= $iconCount; $i++) {
												echo '<div></div>';
											}
											echo '</div>';
										}
									?>
								</div>
								<?php 
									HtmlPyt::hidden('options[loader_name]', array('value' => $iconName));
									HtmlPyt::hidden('options[loader_count]', array('value' => $iconCount));
								?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent-reverse="options[loader_disable]">
							<div class="pyt-option-inline">
								<?php 
									HtmlPyt::colorPicker('options[loader_color]', array(
										'value' => UtilsPyt::getArrayValue($options, 'loader_color', '#000000'),
									));
									?>
							</div>
						</div>
					</div>
					<div class="pyt-options-block">
						<div class="pyt-options-title">
							<?php esc_html_e('Table styling', 'publish-your-table'); ?>
						</div>
						<?php 
							$parent = UtilsPyt::getArrayValue($options, 'custom_css', 0, 1);
							$classHidden = $parent ? '' : 'pytHidden';
						?>
						<div class="pyt-option-wrapper" data-not-preview="1">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Choose your custom table styles below. Any settings you leave blank will default to your theme styles.', 'publish-your-table'); ?>">
								<?php esc_html_e('Use custom styles', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php 
									HtmlPyt::checkbox('options[custom_css]', array(
										'checked' => $parent,
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label">
								<?php esc_html_e('Borders external', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-inline">
								<?php 
									HtmlPyt::colorPicker('options[styles][external_border_color]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'external_border_color'),
									));
									?>
								<div class="pyt-option-label-inline">
									<?php esc_html_e('Width', 'publish-your-table'); ?>
								</div>
								<?php 
									HtmlPyt::number('options[styles][external_border_width]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'external_border_width'),
										'attrs' => 'min="0" class="input-small"'
									));
									?>
								<div class="pyt-option-label-right">
									<?php esc_html_e('px', 'publish-your-table'); ?>
								</div>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label">
								<?php esc_html_e('Borders header', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-inline">
								<?php 
									HtmlPyt::colorPicker('options[styles][header_border_color]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'header_border_color'),
									));
									?>
								<div class="pyt-option-label-inline">
									<?php esc_html_e('Width', 'publish-your-table'); ?>
								</div>
								<?php 
									HtmlPyt::number('options[styles][header_border_width]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'header_border_width'),
										'attrs' => 'min="0" class="input-small"'
									));
									?>
								<div class="pyt-option-label-right">
									<?php esc_html_e('px', 'publish-your-table'); ?>
								</div>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label">
								<?php esc_html_e('Borders rows', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-inline">
								<?php 
									HtmlPyt::colorPicker('options[styles][row_border_color]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'row_border_color'),
									));
									?>
								<div class="pyt-option-label-inline">
									<?php esc_html_e('Width', 'publish-your-table'); ?>
								</div>
								<?php 
									HtmlPyt::number('options[styles][row_border_width]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'row_border_width'),
										'attrs' => 'min="0" class="input-small"'
									));
									?>
								<div class="pyt-option-label-right">
									<?php esc_html_e('px', 'publish-your-table'); ?>
								</div>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label">
								<?php esc_html_e('Borders columns', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-inline">
								<?php 
									HtmlPyt::colorPicker('options[styles][col_border_color]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'col_border_color'),
									));
									?>
								<div class="pyt-option-label-inline">
									<?php esc_html_e('Width', 'publish-your-table'); ?>
								</div>
								<?php 
									HtmlPyt::number('options[styles][col_border_width]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'col_border_width'),
										'attrs' => 'min="0" class="input-small"'
									));
									?>
								<div class="pyt-option-label-right">
									<?php esc_html_e('px', 'publish-your-table'); ?>
								</div>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label">
								<?php esc_html_e('Header background', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-inline">
								<?php 
									HtmlPyt::colorPicker('options[styles][header_bg_color]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'header_bg_color'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label">
								<?php esc_html_e('Header font', 'publish-your-table'); ?>
							</div>
							<?php if ($this->is_pro) { ?>
								<div class="pyt-option-big">
								<?php
									HtmlPyt::selectFontList('options[styles][header_font_family]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'header_font_family'),
									));
									?>
								</div>
							<?php } else { ?>
								<div class="pyt-option-value">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							<?php }	?>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-inline">
								<?php 
									HtmlPyt::colorPicker('options[styles][header_font_color]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'header_font_color'),
									));
									?>
								<div class="pyt-option-label-inline">
									<?php esc_html_e('Size', 'publish-your-table'); ?>
								</div>
								<?php 
									HtmlPyt::number('options[styles][header_font_size]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'header_font_size'),
										'attrs' => 'min="0" class="input-small"'
									));
									?>
								<div class="pyt-option-label-right">
									<?php esc_html_e('px', 'publish-your-table'); ?>
								</div>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label">
								<?php esc_html_e('Cell background', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-inline">
								<?php 
									HtmlPyt::colorPicker('options[styles][cell_bg_color]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'cell_bg_color'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label">
								<?php esc_html_e('Cell font', 'publish-your-table'); ?>
							</div>
							<?php if ($this->is_pro) { ?>
								<div class="pyt-option-big">
								<?php
									HtmlPyt::selectFontList('options[styles][cell_font_family]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'cell_font_family'),
									));
									?>
								</div>
							<?php } else { ?>
								<div class="pyt-option-value">
									<?php HtmlPyt::proOptionLink(); ?>
								</div>
							<?php }	?>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-inline">
								<?php 
									HtmlPyt::colorPicker('options[styles][cell_font_color]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'cell_font_color'),
									));
									?>
								<div class="pyt-option-label-inline">
									<?php esc_html_e('Size', 'publish-your-table'); ?>
								</div>
								<?php 
									HtmlPyt::number('options[styles][cell_font_size]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'cell_font_size'),
										'attrs' => 'min="0" class="input-small"'
									));
									?>
								<div class="pyt-option-label-right">
									<?php esc_html_e('px', 'publish-your-table'); ?>
								</div>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label">
								<?php esc_html_e('Search bar colors', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-big">
								<?php
									HtmlPyt::colorPicker('options[styles][search_bg_color]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'search_bg_color'),
										'label' => __('background', 'publish-your-table')
									));
									HtmlPyt::colorPicker('options[styles][search_font_color]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'search_font_color'),
										'label' => __('font', 'publish-your-table')
									));
									HtmlPyt::colorPicker('options[styles][search_border_color]', array(
										'value' => UtilsPyt::getArrayValue($styles, 'search_border_color'),
										'label' => __('border', 'publish-your-table')
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Set vertical alignment of table cell contents.', 'publish-your-table'); ?>">
								<?php esc_html_e('Vertical alignment', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php
									HtmlPyt::selectbox('options[styles][vertical_alignment]', array(
										'options' => array('' => __('None', 'publish-your-table'), 'top' => __('Top', 'publish-your-table'), 'middle' => __('Middle', 'publish-your-table'), 'bottom' => __('Bottom', 'publish-your-table')),
										'value' => UtilsPyt::getArrayValue($styles, 'vertical_alignment'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Set horizontal alignment of table cell contents.', 'publish-your-table'); ?>">
								<?php esc_html_e('Horizontal alignment', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php
									HtmlPyt::selectbox('options[styles][horizontal_alignment]', array(
										'options' => array('' => __('None', 'publish-your-table'), 'left' => __('Left', 'publish-your-table'), 'center' => __('Center', 'publish-your-table'), 'right' => __('Right', 'publish-your-table')),
										'value' => UtilsPyt::getArrayValue($styles, 'horizontal_alignment'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Set horizontal pagination buttons position.', 'publish-your-table'); ?>">
								<?php esc_html_e('Pagination position', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php
									HtmlPyt::selectbox('options[styles][paging_position]', array(
										'options' => array('' => __('None', 'publish-your-table'), 'left' => __('Left', 'publish-your-table'), 'center' => __('Center', 'publish-your-table'), 'right' => __('Right', 'publish-your-table')),
										'value' => UtilsPyt::getArrayValue($styles, 'paging_position'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label">
								<?php esc_html_e('Show sorting icon on mouse over', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php
									HtmlPyt::checkbox('options[styles][sorting_hover]', array(
										'checked' => UtilsPyt::getArrayValue($styles, 'sorting_hover', 0, 1),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[custom_css]" data-not-preview="1">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Set all columns of the same width.', 'publish-your-table'); ?>">
								<?php esc_html_e('Fixed layout', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php
									HtmlPyt::checkbox('options[styles][fixed_layout]', array(
										'checked' => UtilsPyt::getArrayValue($styles, 'fixed_layout', 0, 1),
									));
									?>
							</div>
						</div>
					</div>
					<div class="pyt-options-block" id="pyt-tab-options-text">
						<div class="pyt-options-title">
							<?php esc_html_e('Overwrite table text', 'publish-your-table'); ?>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label">
								<?php esc_html_e('Empty table', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-big">
								<?php
									HtmlPyt::text('options[langs][sEmptyTable]', array(
										'placeholder' => __('No data available in table', 'publish-your-table'),
										'value' => UtilsPyt::getArrayValue($langs, 'sEmptyTable'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Variables: _START_, _END_, _TOTAL_<br />Example: Showing _START_ to _END_ of _TOTAL_ entries', 'publish-your-table'); ?>">
								<?php esc_html_e('Table info text', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-big">
								<?php
									HtmlPyt::text('options[langs][sInfo]', array(
										'placeholder' => __('Showing _START_ to _END_ of _TOTAL_ entries', 'publish-your-table'),
										'value' => UtilsPyt::getArrayValue($langs, 'sInfo'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label">
								<?php esc_html_e('Empty info text', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-big">
								<?php
									HtmlPyt::text('options[langs][sInfoEmpty]', array(
										'placeholder' => __('Showing 0 to 0 of 0 entries'),
										'value' => UtilsPyt::getArrayValue($langs, 'sInfoEmpty'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Variables: _MAX_<br />Example: (filtered from _MAX_ total entries)', 'publish-your-table'); ?>">
								<?php esc_html_e('Filtered info text', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-big">
								<?php
									HtmlPyt::text('options[langs][sInfoFiltered]', array(
										'placeholder' => __('(filtered from _MAX_ total entries)', 'publish-your-table'),
										'value' => UtilsPyt::getArrayValue($langs, 'sInfoFiltered'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Variables: _MENU_<br />Example: Show _MENU_ entries', 'publish-your-table'); ?>">
								<?php esc_html_e('Length text', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-big">
								<?php
									HtmlPyt::text('options[langs][sLengthMenu]', array(
										'placeholder' => __('Show _MENU_ entries', 'publish-your-table'),
										'value' => UtilsPyt::getArrayValue($langs, 'sLengthMenu'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label">
								<?php esc_html_e('Search label', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-big">
								<?php
									HtmlPyt::text('options[langs][sSearch]', array(
										'placeholder' => __('Search:'),
										'value' => UtilsPyt::getArrayValue($langs, 'sSearch'),
									));
									?>
							</div>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label">
								<?php esc_html_e('Zero records', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-big">
								<?php
									HtmlPyt::text('options[langs][sZeroRecords]', array(
										'placeholder' => __('No matching records are found'),
										'value' => UtilsPyt::getArrayValue($langs, 'sZeroRecords'),
									));
									?>
							</div>
						</div>
					</div>
					<div class="pyt-options-block">
						<div class="pyt-options-title">
							<?php esc_html_e('Language', 'publish-your-table'); ?>
						</div>
						<div class="pyt-option-wrapper">
							<div class="pyt-option-label">
								<?php esc_html_e('Table Language', 'publish-your-table'); ?>
							</div>
							<div class="pyt-option-value">
								<?php
									HtmlPyt::selectbox('options[langs][file]', array(
										'options' => $this->translations,
										'value' => UtilsPyt::getArrayValue($langs, 'file'),
									));
									?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="pyt-preview-section pt-2">
				<div class="pyt-preview-styling pubydoc-clear">
					<ul class="pubydoc-grbtn">
						<li>
							<a href="#preview-tab-desktop" class="button button-small current" data-preview="desktop">
								<?php esc_html_e('Desktop', 'publish-your-table'); ?>
							</a>
						</li>
						<li>
							<a href="#preview-tab-tablet" class="button button-small" data-preview="tablet">
								<?php esc_html_e('Tablet', 'publish-your-table'); ?>
							</a>
						</li>
						<li>
							<a href="#preview-tab-mobile" class="button button-small" data-preview="mobile">
								<?php esc_html_e('Mobile', 'publish-your-table'); ?>
							</a>
						</li>
					</ul>
				</div>
				<div id="pytPreviewContainer"></div>
				<div class="pyt-preview-notice" data-type="loading">
					<i class="fa fa-fw fa-spin fa-circle-o-notch"></i>
					<?php esc_html_e('Loading your table, please wait...', 'publish-your-table'); ?>
				</div>
				<div class="pytHidden pyt-preview-notice" data-type="empty">
					<i class="fa fa-fw fa-exclamation-circle"></i>
					<?php esc_html_e('Table is empty', 'publish-your-table'); ?>
				</div>
				<div class="pytHidden pyt-preview-notice" data-type="finished">
					<i class="fa fa-fw fa-exclamation-circle"></i>
					<?php esc_html_e('Note that the table may look a little different depending on your theme style.', 'publish-your-table'); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="pubydoc-hidden">
		<div id="pytLoaderIconDialog" title="<?php esc_attr_e('Choose icon', 'publish-your-table'); ?>" data-cancel="<?php esc_attr_e('Cancel', 'publish-your-table'); ?>">		
			<?php
				$loaderSkins = array(
					'timer' => 1, //number means count of div necessary to display loader
					'ball-beat'=> 3,
					'ball-circus'=> 5,
					'ball-atom'=> 4,
					'ball-spin-clockwise-fade-rotating'=> 8,
					'line-scale'=> 5,
					'ball-climbing-dot'=> 4,
					'square-jelly-box'=> 2,
					'ball-rotate'=> 1,
					'ball-clip-rotate-multiple'=> 2,
					'cube-transition'=> 2,
					'square-loader'=> 1,
					'ball-8bits'=> 16,
					'ball-newton-cradle'=> 4,
					'ball-pulse-rise'=> 5,
					'triangle-skew-spin'=> 1,
					'fire'=> 3,
					'ball-zig-zag-deflect'=> 2
				);
			?>
			<div class="items items-list">
				<div class="item">
					<div class="item-inner">
						<div class="item-loader-container">
							<div class="preicon_img" data-name="spinner" data-items="0">
								<div class="pubydoc-table-loader spinner"></div>
							</div>
						</div>
					</div>
					<div class="item-title">spinner</div>
				</div>
				<?php foreach ($loaderSkins as $name => $count) { ?>
					<div class="item">
						<div class="item-inner">
							<div class="item-loader-container">
								<div class="pubydoc-table-loader la-<?php echo esc_attr($name); ?> la-2x preicon_img" data-name="<?php echo esc_attr($name); ?>" data-items="<?php echo esc_attr($count); ?>">
									<?php
										for ($i=0; $i < $count; $i++) {
											echo '<div></div>';
										}
										?>
								</div>
							</div>
						</div>
						<div class="item-title"><?php echo esc_html($name); ?></div>
					</div>
				<?php }	?>
			</div>
		</div>
	</div>
</div>
