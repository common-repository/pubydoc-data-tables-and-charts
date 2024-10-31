<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="pytDialogDiagramSettings" 
	title="<?php esc_attr_e('Diagram Settings', 'publish-your-table'); ?>" 
	data-create="<?php esc_attr_e('Create', 'publish-your-table'); ?>" 
	data-save="<i class='fa fa-floppy-o'></i><?php esc_attr_e('Save', 'publish-your-table'); ?>" 
	data-refresh="<?php esc_attr_e('Click button Refresh before saving ', 'publish-your-table'); ?>" 
	data-clone="<i class='fa fa-clone'></i><?php esc_attr_e('Clone', 'publish-your-table'); ?>" 
	data-clone-confirm="<?php esc_html_e('Are you sure you want to clone diagram?', 'publish-your-table'); ?>"
	data-cancel="<?php esc_attr_e('Cancel', 'publish-your-table'); ?>">
	<div class="pubydoc-clear">
		<ul class="pubydoc-grbtn tbs-col-tabs">
			<li>
				<a href="#block-tab-general" class="button button-small"><?php esc_html_e('General', 'publish-your-table'); ?></a>
			</li>
			<li>
				<a href="#block-tab-type" class="button button-small"><?php esc_html_e('Type', 'publish-your-table'); ?></a>
			</li>
			<li>
				<a href="#block-tab-layout" class="button button-small"><?php esc_html_e('Layout', 'publish-your-table'); ?></a>
			</li>
			<li>
				<a href="#block-tab-advanced" class="button button-small"><?php esc_html_e('Advanced', 'publish-your-table'); ?></a>
			</li>
		</ul>
		<button class="button button-dark" id="pytDiagramRefresh"><i class="fa fa-refresh" aria-hidden="true"></i><?php esc_html_e('Refresh', 'publish-your-table'); ?></button>
	</div>
	<div class="row">
		<div class="col-12">
		<div class="pyt-diagram-settings">
			<div class="block-tab pyt-options-wrap" id="block-tab-general">
				<div class="pyt-option-wrapper">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Select table for diagram', 'publish-your-table'); ?>">
						<?php esc_html_e('Table', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-big">
						<?php 
							HtmlPyt::selectbox('table_id', array('options' => $this->tables, 'default' => esc_attr__('Select table for diagram', 'publish-your-table'), 'attrs' => 'id="pytDiagramsTableId" data-not-preview="1"'));
						?>
					</div>
				</div>
				<div class="pyt-option-wrapper">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Specify a range for diagram. Set the range in the format A1:C10 as in Excel, where the letter is the column number in alphabetical order, the digit is the row number.<br><br>If you want to use all rows of a column, then write like this: A:A (for one column) or A:C (for several columns).<br><br>It is possible to specify several ranges, then use a semicolon for separation: A1:B10;D1:D10.', 'publish-your-table'); ?>">
						<?php esc_html_e('Range', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-big">
						<?php 
							HtmlPyt::input('table_range', array('attrs' => 'id="pytDiagramsTableRange" data-reset-legend="1" data-reset-xy="1" data-not-preview="1"'));
						?>
					</div>
				</div>
				<div class="pyt-option-wrapper">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('You can add title for your diagram', 'publish-your-table'); ?>">
						<?php esc_html_e('Title', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-big">
						<?php 
							HtmlPyt::input('title', array('attrs' => 'id="pytDiagramsTitle" data-not-preview="1"'));
						?>
					</div>
				</div>
				<div class="pyt-option-wrapper">
					<?php 
						HtmlPyt::checkbox('options[switch_rows_cols]', array('attrs' => 'id="pytDiagramsSwitch" data-reset-legend="1" data-reset-xy="1"'));
						?>
					<label for="pytDiagramsSwitch">
						<?php esc_html_e('Switch rows / columns', 'publish-your-table'); ?>
					</label>
				</div>
				<div class="pyt-option-wrapper">
					<?php 
						HtmlPyt::checkbox('options[label_first_col]', array('attrs' => 'id="pytDiagramsLabel" data-reset-legend="1" data-reset-xy="1"'));
						?>
					<label for="pytDiagramsLabel">
						<?php esc_html_e('Use first column as labels', 'publish-your-table'); ?>
					</label>
				</div>
				<div class="pyt-option-wrapper">
					<?php 
						HtmlPyt::checkbox('options[header_first_row]', array('attrs' => 'id="pytDiagramsHeader" data-reset-legend="1" data-reset-xy="1"'));
						?>
					<label for="pytDiagramsHeader">
						<?php esc_html_e('Use first row as headers', 'publish-your-table'); ?>
					</label>
				</div>
				<div class="pyt-option-wrapper">
					<?php 
						HtmlPyt::checkbox('options[show_values]', array('attrs' => 'id="pytDiagramsValues"'));
						?>
					<label for="pytDiagramsValues" class="pubydoc-tooltip" title="<?php esc_attr_e('In 2D working only with included headers', 'publish-your-table'); ?>">
						<?php esc_html_e('Show values into diagram', 'publish-your-table'); ?>
					</label>
				</div>
				<div class="pyt-option-wrapper">
					<?php 
						HtmlPyt::checkbox('options[refresh_dynamically]', array('attrs' => 'id="pytDiagramsRefresh" data-not-preview="1"'));
						?>
					<label for="pytDiagramsRefresh" class="pubydoc-tooltip" title="<?php esc_attr_e('When this option is enabled, the chart will be refreshed every time the table data changes. ', 'publish-your-table'); ?>">
						<?php esc_html_e('Refresh dynamically', 'publish-your-table'); ?>
					</label>
				</div>
				<div class="pyt-option-wrapper">
					<?php 
						HtmlPyt::checkbox('options[show_loader]', array('attrs' => 'id="pytDiagramsLoader" data-not-preview="1"'));
						?>
					<label for="pytDiagramsLoader" class="pubydoc-tooltip" title="<?php esc_attr_e('Enable / disable table loader icon before diagram will be completely loaded. ', 'publish-your-table'); ?>">
						<?php esc_html_e('Show loader', 'publish-your-table'); ?>
					</label>
				</div>
			</div>
			<?php $path = FramePyt::_()->getModule('diagrams')->getModPath() . '/assets/img/'; ?>
			<div class="block-tab pyt-options-wrap" id="block-tab-type">
				<div class="pyt-option-wrapper pyt-diagrams-types">
					<?php foreach ($this->types as $key => $value) { ?>
						<div class="pyt-option-value">
							<img src="<?php echo $path . 'chart' . $key; ?>.png" data-type="<?php echo esc_attr($key); ?>" class="pyt-diagrams-chart">
						</div>
					<?php } ?>
					<input type="hidden" name="type" id="pytDiagramsType" data-default="0" data-reset-xy="1" data-not-preview="1">
				</div>
			</div>
			<div class="block-tab pyt-options-wrap" id="block-tab-layout">
				<div class="pyt-option-wrapper">
					<?php 
						HtmlPyt::checkbox('options[auto_size]', array('attrs' => 'id="pytDiagramsAutoSize" data-default="1"'));
						?>
					<label for="pytDiagramsAutoSize">
						<?php esc_html_e('Auto size', 'publish-your-table'); ?>
					</label>
				</div>
				<div class="pyt-option-wrapper pyt-option-sub" data-parent-reverse="options[auto_size]">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Set the value in px or %, for example, 400 (equals to 400px) or 90%. Leave this field empty to use default width value.', 'publish-your-table'); ?>">
						<?php esc_html_e('Width', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-value">
						<?php 
							HtmlPyt::input('options[width]', array('attrs' => 'data-default="650" data-not-preview="1"'));
						?>
					</div>
				</div>
				<div class="pyt-option-wrapper pyt-option-sub" data-parent-reverse="options[auto_size]">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Set the value in px or %, for example, 200 (equals to 200px) or 80%. Leave this field empty to use default height value.', 'publish-your-table'); ?>">
						<?php esc_html_e('Height', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-value">
						<?php 
							HtmlPyt::input('options[height]', array('attrs' => 'data-default="350" data-reset-xy="1" data-not-preview="1"'));
						?>
					</div>
				</div>
				<div class="pyt-option-wrapper">
					<?php 
						HtmlPyt::checkbox('options[show_title]', array('attrs' => 'id="pytDiagramsShowTitle" data-default="1"'));
						?>
					<label for="pytDiagramsShowTitle">
						<?php esc_html_e('Show title', 'publish-your-table'); ?>
					</label>
				</div>
				<div class="pyt-option-wrapper pyt-option-sub" data-parent="options[show_title]">
					<div class="pyt-option-inline">
						<?php 
							HtmlPyt::colorPicker('options[title_color]', array('attrs' => 'data-not-preview="1"'));
						?>
						<div class="pyt-option-label-inline">
							<?php esc_html_e('Size', 'publish-your-table'); ?>
						</div>
						<?php 
							HtmlPyt::number('options[title_size]', array('attrs' => 'min="0" class="input-small" data-default="16" data-not-preview="1"'));
						?>
						<div class="pyt-option-label-right">
							<?php esc_html_e('px', 'publish-your-table'); ?>
						</div>
						
					</div>
				</div>
				<div class="pyt-option-wrapper">
					<?php 
						HtmlPyt::checkbox('options[show_legend]', array('attrs' => 'id="pytDiagramsShowLegend" data-default="1"'));
						?>
					<label for="pytDiagramsShowLegend">
						<?php esc_html_e('Show legend', 'publish-your-table'); ?>
					</label>
				</div>
				<div class="pyt-option-wrapper pyt-option-sub" data-parent="options[show_legend]">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Sets the orientation of the legend.', 'publish-your-table'); ?>">
						<?php esc_html_e('Orientation', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-value">
						<?php 
							HtmlPyt::selectbox('options[legend_orientation]', array(
								'options' => array('v' => esc_html__('vertical', 'publish-your-table'), 'h' => esc_html__('horizontal', 'publish-your-table')), 
								'attrs' => 'data-default="v" class="pubydoc-width200"'));
						?>
					</div>
				</div>
				<div class="pyt-option-wrapper">
						<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Sets the margins of the diagram (top / right / bottom /left).', 'publish-your-table'); ?>">
							<?php esc_html_e('Margins', 'publish-your-table'); ?>
						</div>
						<div class="pyt-option-inline pyt-option-sub">
						<?php 
							HtmlPyt::number('options[margin_t]', array('attrs' => 'min="0" class="input-small" data-default="100" data-not-preview="1"'));
							HtmlPyt::number('options[margin_r]', array('attrs' => 'min="0" class="input-small" data-default="80" data-not-preview="1"'));
							HtmlPyt::number('options[margin_b]', array('attrs' => 'min="0" class="input-small" data-default="80" data-not-preview="1"'));
							HtmlPyt::number('options[margin_l]', array('attrs' => 'min="0" class="input-small" data-default="80" data-not-preview="1"'));
						?>
						</div>

				</div>

				<div class="pyt-option-wrapper">
					<?php 
						HtmlPyt::checkbox('options[highlighting_hover]', array('attrs' => 'id="pytDiagramsHighlighting"'));
						?>
					<label for="pytDiagramsHighlighting">
						<?php esc_html_e('Highlighting by mousehover', 'publish-your-table'); ?>
					</label>
				</div>
				<div class="pyt-option-wrapper">
					<?php 
						HtmlPyt::checkbox('options[custom_colors]', array('attrs' => 'id="pytDiagramsCustomColors"'));
						?>
					<label for="pytDiagramsCustomColors">
						<?php esc_html_e('Use custom colors', 'publish-your-table'); ?>
					</label>
				</div>
				<div class="pyt-option-wrapper pyt-option-sub" id="pytColorWay" data-parent="options[custom_colors]">
					<div class="pyt-option-inline">
						<?php 
							HtmlPyt::colorPicker('options[trace_color1]', array('attrs' => 'data-not-preview="1" data-empty="#1f77b4"'));
							HtmlPyt::colorPicker('options[trace_color2]', array('attrs' => 'data-not-preview="1" data-empty="#ff7f0e"'));
						?>
					</div>
					<div class="pyt-option-inline">
						<?php 
							HtmlPyt::colorPicker('options[trace_color3]', array('attrs' => 'data-not-preview="1" data-empty="#2ca02c"'));
							HtmlPyt::colorPicker('options[trace_color4]', array('attrs' => 'data-not-preview="1" data-empty="#d62728"'));
						?>
					</div>
					<div class="pyt-option-inline">
						<?php 
							HtmlPyt::colorPicker('options[trace_color5]', array('attrs' => 'data-not-preview="1" data-empty="#9467bd"'));
							HtmlPyt::colorPicker('options[trace_color6]', array('attrs' => 'data-not-preview="1" data-empty="#8c564b"'));
						?>
					</div>
					<div class="pyt-option-inline">
						<?php 
							HtmlPyt::colorPicker('options[trace_color7]', array('attrs' => 'data-not-preview="1" data-empty="#e377c2"'));
							HtmlPyt::colorPicker('options[trace_color8]', array('attrs' => 'data-not-preview="1" data-empty="#7f7f7f"'));
						?>
					</div>
					<div class="pyt-option-inline">
						<?php 
							HtmlPyt::colorPicker('options[trace_color9]', array('attrs' => 'data-not-preview="1" data-empty="#bcbd22"'));
							HtmlPyt::colorPicker('options[trace_color10]', array('attrs' => 'data-not-preview="1" data-empty="#17becf"'));
						?>
					</div>
				</div>

			</div>
			<div class="block-tab pyt-options-wrap" id="block-tab-advanced">
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="5">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Determines types for charts. The same types will be set to the same settings.', 'publish-your-table'); ?>">
						<?php esc_html_e('Types for charts', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-inline mb-2">
						<?php 
							HtmlPyt::selectbox('options[multi_trace1]', array(
								'options' => array(
									'lines' => esc_html__('Lines', 'publish-your-table'),
									'markers' => esc_html__('Markers', 'publish-your-table'),
									'lines+markers' => esc_html__('Lines + Markers', 'publish-your-table'),
									'bar' => esc_html__('Bar', 'publish-your-table'),
									'bubble' => esc_html__('Bubble', 'publish-your-table')), 
								'attrs' => 'data-empty="lines" data-reset-xy="1" class="pubydoc-width200"'));
							HtmlPyt::selectbox('options[multi_trace2]', array(
								'options' => array(
									'lines' => esc_html__('Lines', 'publish-your-table'),
									'markers' => esc_html__('Markers', 'publish-your-table'),
									'lines+markers' => esc_html__('Lines + Markers', 'publish-your-table'),
									'bar' => esc_html__('Bar', 'publish-your-table'),
									'bubble' => esc_html__('Bubble', 'publish-your-table')), 
								'attrs' => 'data-empty="markers" data-reset-xy="1" class="pubydoc-width200"'));
						?>
					</div>
					<div class="pyt-option-inline">
						<?php 
							HtmlPyt::selectbox('options[multi_trace3]', array(
								'options' => array(
									'lines' => esc_html__('Lines', 'publish-your-table'),
									'markers' => esc_html__('Markers', 'publish-your-table'),
									'lines+markers' => esc_html__('Lines + Markers', 'publish-your-table'),
									'bar' => esc_html__('Bar', 'publish-your-table'),
									'bubble' => esc_html__('Bubble', 'publish-your-table')), 
								'attrs' => 'data-empty="bar" data-reset-xy="1" class="pubydoc-width200"'));
							HtmlPyt::selectbox('options[multi_trace4]', array(
								'options' => array(
									'lines' => esc_html__('Lines', 'publish-your-table'),
									'markers' => esc_html__('Markers', 'publish-your-table'),
									'lines+markers' => esc_html__('Lines + Markers', 'publish-your-table'),
									'bar' => esc_html__('Bar', 'publish-your-table'),
									'bubble' => esc_html__('Bubble', 'publish-your-table')), 
								'attrs' => 'data-empty="bubble" data-reset-xy="1" class="pubydoc-width200"'));
						?>
					</div>
				</div>

				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="0 1">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Determines the drawing mode for this scatter trace.', 'publish-your-table'); ?>">
						<?php esc_html_e('Drawing mode', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-value">
						<?php 
							HtmlPyt::selectbox('options[lines_mode]', array(
								'options' => array(
									'lines' => esc_html__('Lines', 'publish-your-table'),
									'markers' => esc_html__('Markers', 'publish-your-table'),
									'lines+markers' => esc_html__('Lines + Markers', 'publish-your-table'),
									'none' => esc_html__('None (for area cart)', 'publish-your-table')), 
								'attrs' => 'data-default="lines+markers" data-reset-xy="1" class="pubydoc-width200"'));
						?>
					</div>
				</div>
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="0 1 5">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Sets the line type and width.', 'publish-your-table'); ?>">
						<?php esc_html_e('Lines', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-inline">
						<?php 
							HtmlPyt::selectbox('options[lines_dash]', array(
								'options' => array(
									'solid' => esc_html__('solid', 'publish-your-table'),
									'dot' => esc_html__('dot', 'publish-your-table'),
									'dash' => esc_html__('dash', 'publish-your-table'),
									'longdash' => esc_html__('longdash', 'publish-your-table'),
									'dashdot' => esc_html__('dashdot', 'publish-your-table'),
									'longdashdot' => esc_html__('longdashdot', 'publish-your-table')),
								'attrs' => 'data-empty="solid"'));
							HtmlPyt::selectbox('options[lines_shape]', array(
								'options' => array(
									'linear' => esc_html__('linear', 'publish-your-table'),
									'spline' => esc_html__('spline', 'publish-your-table'),
									'hv' => esc_html__('hv', 'publish-your-table'),
									'vh' => esc_html__('vh', 'publish-your-table')),
								'attrs' => 'data-empty="linear"'));
						?>
						<div class="pyt-option-label-inline">
							<?php esc_html_e('Width', 'publish-your-table'); ?>
						</div>
						<?php
							HtmlPyt::number('options[lines_width]', array('attrs' => 'min="0" class="input-small" data-default="2" data-not-preview="1"'));
						?>
						<div class="pyt-option-label-right">
							<?php esc_html_e('px', 'publish-your-table'); ?>
						</div>
					</div>
				</div>
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="0 1 5">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Sets the marker symbols and size.', 'publish-your-table'); ?>">
						<?php esc_html_e('Markers', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-inline">
						<?php 
							HtmlPyt::selectbox('options[markers_symbol]', array(
								'options' => array(
									'circle' => esc_html__('circle', 'publish-your-table'),
									'circle-open' => esc_html__('circle open', 'publish-your-table'),
									'square' => esc_html__('square', 'publish-your-table'),
									'square-open' => esc_html__('square open', 'publish-your-table'),
									'diamond' => esc_html__('diamond', 'publish-your-table'),
									'diamond-open' => esc_html__('diamond open', 'publish-your-table'),
									'cross' => esc_html__('cross', 'publish-your-table'),
									'star' => esc_html__('star', 'publish-your-table'),
									'bowtie' => esc_html__('bowtie', 'publish-your-table'),
									'arrow-up' => esc_html__('arrow-up', 'publish-your-table'),
									'arrow-up-open' => esc_html__('arrow-up open', 'publish-your-table'),
									'arrow-down' => esc_html__('arrow-down', 'publish-your-table'),
									'arrow-down-open' => esc_html__('arrow-down open', 'publish-your-table')), 
								'attrs' => 'data-empty="circle" class="pubydoc-width200"'));
						?>
						<div class="pyt-option-label-inline">
							<?php esc_html_e('Size', 'publish-your-table'); ?>
						</div>
						<?php 
							HtmlPyt::number('options[markers_size]', array('attrs' => 'min="0" class="input-small" data-default="6" data-not-preview="1"'));
						?>
						<div class="pyt-option-label-right">
							<?php esc_html_e('px', 'publish-your-table'); ?>
						</div>
						
					</div>
				</div>
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="4 5">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Sets the bubble symbols and opacity (number between or equal to 0 and 1).', 'publish-your-table'); ?>">
						<?php esc_html_e('Bubble ', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-inline">
						<?php 
							HtmlPyt::selectbox('options[bubble_symbol]', array(
								'options' => array(
									'circle' => esc_html__('circle', 'publish-your-table'),
									'circle-open' => esc_html__('circle open', 'publish-your-table'),
									'square' => esc_html__('square', 'publish-your-table'),
									'square-open' => esc_html__('square open', 'publish-your-table'),
									'diamond' => esc_html__('diamond', 'publish-your-table'),
									'diamond-open' => esc_html__('diamond open', 'publish-your-table'),
									'cross' => esc_html__('cross', 'publish-your-table'),
									'star' => esc_html__('star', 'publish-your-table'),
									'bowtie' => esc_html__('bowtie', 'publish-your-table'),
									'arrow-up' => esc_html__('arrow-up', 'publish-your-table'),
									'arrow-up-open' => esc_html__('arrow-up open', 'publish-your-table'),
									'arrow-down' => esc_html__('arrow-down', 'publish-your-table'),
									'arrow-down-open' => esc_html__('arrow-down open', 'publish-your-table')), 
								'attrs' => 'data-empty="circle" class="pubydoc-width200"'));
						?>
						<div class="pyt-option-label-inline">
							<?php esc_html_e('Opacity', 'publish-your-table'); ?>
						</div>
						<?php 
							HtmlPyt::number('options[bubble_opacity]', array('attrs' => 'min="0" max="1" step="0.1" class="input-small" data-empty="1" data-not-preview="1"'));
						?>
				
					</div>
				</div>
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="0 1 4 5">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Sets the positions of the value text elements with respects to the (x,y) coordinates.', 'publish-your-table'); ?>">
						<?php esc_html_e('Text position', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-big">
						<?php 
							HtmlPyt::selectbox('options[textposition]', array(
								'options' => array(
									'top left' => esc_html__('top left', 'publish-your-table'),
									'top center' => esc_html__('top center', 'publish-your-table'),
									'top right' => esc_html__('top right', 'publish-your-table'),
									'middle left' => esc_html__('middle left', 'publish-your-table'),
									'middle center' => esc_html__('middle center', 'publish-your-table'),
									'middle right' => esc_html__('middle right', 'publish-your-table'),
									'bottom left' => esc_html__('bottom left', 'publish-your-table'),
									'bottom center' => esc_html__('bottom center', 'publish-your-table'),
									'bottom right' => esc_html__('bottom right', 'publish-your-table')), 
								'attrs' => 'data-empty="top center"'));
						?>
					</div>
				</div>

				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="2">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Determines how bars at the same location coordinate are displayed on the graph.<br><br>With "stack", the bars are stacked on top of one another.<br><br>With "relative", the bars are stacked on top of one another, with negative values below the axis, positive values above With "group", the bars are plotted next to one another centered around the shared location.', 'publish-your-table'); ?>">
						<?php esc_html_e('Drawing bar mode', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-value">
						<?php 
							HtmlPyt::selectbox('options[bar_stacked]', array(
								'options' => array(
									'group' => esc_html__('group', 'publish-your-table'),
									'stack' => esc_html__('stack', 'publish-your-table'),
									'relative' => esc_html__('relative', 'publish-your-table')), 
								'attrs' => 'data-default="group" data-reset-xy="1" class="pubydoc-width200"'));
						?>
					</div>
				</div>

				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="2">
					<div class="pyt-option-label">
						<?php esc_html_e('Bar orientation', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-value">
						<?php 
							HtmlPyt::selectbox('options[bar_orientation]', array(
								'options' => array('v' => esc_html__('vertical', 'publish-your-table'), 'h' => esc_html__('horizontal', 'publish-your-table')),
								'attrs' => 'data-default="v" data-reset-xy="1" class="pubydoc-width200"'));
						?>
					</div>
				</div>
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="2 5">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Specifies the location of the values text. "inside" positions `text` inside, next to the bar end (rotated and scaled if needed). "outside" positions `text` outside, next to the bar end (scaled if needed), unless there is another bar stacked on this one, then the text gets pushed inside. "auto" tries to position `text` inside the bar, but if the bar is too small and no bar is stacked on this one the text is moved outside.', 'publish-your-table'); ?>">
						<?php esc_html_e('Bar text position', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-value">
						<?php 
							HtmlPyt::selectbox('options[bar_textposition]', array(
								'options' => array(
									'auto' => esc_html__('auto', 'publish-your-table'),
									'inside' => esc_html__('inside', 'publish-your-table'),
									'outside' => esc_html__('outside', 'publish-your-table')),
								'attrs' => 'data-empty="auto" class="pubydoc-width200"'));
						?>
					</div>
				</div>

				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="0 1 2 4 5">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Determines which trace information appear on hover.', 'publish-your-table'); ?>">
						<?php esc_html_e('Hoverinfo', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-inline">
						<div>
							<?php 
								HtmlPyt::checkbox('options[hover_x]', array('attrs' => 'id="pytDiagramsHoverX" data-default="1"'));
							?>
							<label for="pytDiagramsHoverX">
								<?php esc_html_e('X value', 'publish-your-table'); ?>
							</label>
						</div>
						<div class="pyt-option-label-inline">
							<?php 
								HtmlPyt::checkbox('options[hover_y]', array('attrs' => 'id="pytDiagramsHoverY" data-default="1"'));
							?>
							<label for="pytDiagramsHoverY">
								<?php esc_html_e('Y value', 'publish-your-table'); ?>
							</label>
						</div>
						<div class="pyt-option-label-inline">
							<?php 
								HtmlPyt::checkbox('options[hover_name]', array('attrs' => 'id="pytDiagramsHoverName" data-default="1"'));
							?>
							<label for="pytDiagramsHoverName">
								<?php esc_html_e('Name', 'publish-your-table'); ?>
							</label>
						</div>
					</div>
				</div>
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="1">
					<?php 
						HtmlPyt::checkbox('options[area_stacked]', array('attrs' => 'id="pytDiagramsAreaStacked"'));
						?>
					<label for="pytDiagramsAreaStacked">
						<?php esc_html_e('Stacked area chart', 'publish-your-table'); ?>
					</label>
				</div>
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="3">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('To display multiple charts, specify the number of charts per line.', 'publish-your-table'); ?>">
						<?php esc_html_e('Number of charts per row', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-value">
						<?php 
							HtmlPyt::number('options[pie_columns]', array('attrs' => 'min="0" data-not-preview="1"'));
						?>
					</div>
				</div>
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="3">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Sets the fraction of the radius to cut out of the pie. Use this to make a donut chart. Number between or equal to 0 and 1', 'publish-your-table'); ?>">
						<?php esc_html_e('Donat hole', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-value">
						<?php 
							HtmlPyt::number('options[pie_hole]', array('attrs' => 'min="0" max="1" step="0.1" data-not-preview="1"'));
						?>
					</div>
				</div>
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="3">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Specifies the direction at which succeeding sectors follow one another.', 'publish-your-table'); ?>">
						<?php esc_html_e('Direction', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-value">
						<?php 
							HtmlPyt::selectbox('options[pie_direction]', array(
								'options' => array(
									'counterclockwise' => esc_html__('counterclockwise', 'publish-your-table'),
									'clockwise' => esc_html__('clockwise', 'publish-your-table')),
								'attrs' => 'data-empty="counterclockwise" class="pubydoc-width200"'));
						?>
					</div>
				</div>
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="3">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Specifies the location of the text info.', 'publish-your-table'); ?>">
						<?php esc_html_e('Text position', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-value">
						<?php 
							HtmlPyt::selectbox('options[pie_textposition]', array(
								'options' => array(
									'auto' => esc_html__('auto', 'publish-your-table'),
									'inside' => esc_html__('inside', 'publish-your-table'),
									'outside' => esc_html__('outside', 'publish-your-table')),
								'attrs' => 'data-empty="auto" class="pubydoc-width200"'));
						?>
					</div>
				</div>
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="3">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Controls the orientation of the text inside chart sectors. When set to "auto", text may be oriented in any direction in order to be as big as possible in the middle of a sector. The "horizontal" option orients text to be parallel with the bottom of the chart, and may make text smaller in order to achieve that goal. The "radial" option orients text along the radius of the sector. The "tangential" option orients text perpendicular to the radius of the sector.', 'publish-your-table'); ?>">
						<?php esc_html_e('Inside text orientation', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-value">
						<?php 
							HtmlPyt::selectbox('options[pie_textorientation]', array(
								'options' => array(
									'auto' => esc_html__('auto', 'publish-your-table'),
									'horizontal' => esc_html__('horizontal', 'publish-your-table'),
									'radial' => esc_html__('radial', 'publish-your-table'),
									'tangential' => esc_html__('tangential', 'publish-your-table')),
								'attrs' => 'data-empty="auto" class="pubydoc-width200"'));
						?>
					</div>
				</div>
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="3">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Determines which trace information appear on the graph.', 'publish-your-table'); ?>">
						<?php esc_html_e('Text info', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-inline">
						<div>
							<?php 
								HtmlPyt::checkbox('options[pie_text_label]', array('attrs' => 'id="pytDiagramsPieTextLabel"'));
							?>
							<label for="pytDiagramsPieTextLabel">
								<?php esc_html_e('label', 'publish-your-table'); ?>
							</label>
						</div>
						<div class="pyt-option-label-inline">
							<?php 
								HtmlPyt::checkbox('options[pie_text_text]', array('attrs' => 'id="pytDiagramsPieTextText" data-default="1"'));
							?>
							<label for="pytDiagramsPieTextText">
								<?php esc_html_e('value', 'publish-your-table'); ?>
							</label>
						</div>
						<div class="pyt-option-label-inline">
							<?php 
								HtmlPyt::checkbox('options[pie_text_percent]', array('attrs' => 'id="pytDiagramsPieTextPercent" data-default="1"'));
							?>
							<label for="pytDiagramsPieTextPercent">
								<?php esc_html_e('percent', 'publish-your-table'); ?>
							</label>
						</div>
					</div>
				</div>
				<div class="pyt-option-wrapper" data-parent="type" data-parent-value="3">
					<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Determines which trace information appear on hover.', 'publish-your-table'); ?>">
						<?php esc_html_e('Hover info', 'publish-your-table'); ?>
					</div>
					<div class="pyt-option-inline">
						<div>
							<?php 
								HtmlPyt::checkbox('options[pie_hover_label]', array('attrs' => 'id="pytDiagramsPieHoverLabel" data-default="1"'));
							?>
							<label for="pytDiagramsPieHoverLabel">
								<?php esc_html_e('label', 'publish-your-table'); ?>
							</label>
						</div>
						<div class="pyt-option-label-inline">
							<?php 
								HtmlPyt::checkbox('options[pie_hover_value]', array('attrs' => 'id="pytDiagramsPieHoverValue" data-default="1"'));
							?>
							<label for="pytDiagramsPieHoverValue">
								<?php esc_html_e('value', 'publish-your-table'); ?>
							</label>
						</div>
						<div class="pyt-option-label-inline">
							<?php 
								HtmlPyt::checkbox('options[pie_hover_percent]', array('attrs' => 'id="pytDiagramsPieHoverPercent" data-default="1"'));
							?>
							<label for="pytDiagramsPieHoverPercent">
								<?php esc_html_e('percent', 'publish-your-table'); ?>
							</label>
						</div>
						<div class="pyt-option-label-inline">
							<?php 
								HtmlPyt::checkbox('options[pie_hover_name]', array('attrs' => 'id="pytDiagramsPieHoverName" data-default="1"'));
							?>
							<label for="pytDiagramsPieHoverName">
								<?php esc_html_e('name', 'publish-your-table'); ?>
							</label>
						</div>
					</div>
				</div>
				
				
			</div>
		</div>
		<div class="pyt-diagram-preview" id="pytDiagramPreview" data-empty="<?php esc_html_e('Select table, range and chart type', 'publish-your-table'); ?>"></div>
		<input type="hidden" name="config" id="pytDiagramConfig">
	</div>
	</div>
</div>
