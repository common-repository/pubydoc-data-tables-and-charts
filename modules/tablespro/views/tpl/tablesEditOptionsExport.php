<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$options = $this->options;
?>
<div class="pyt-option-wrapper">
	<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Allows to export table in pdf, csv, xls formats from the front-end. Choose needed formats.', 'publish-your-table'); ?>">
		<?php esc_html_e('Frontend export', 'publish-your-table'); ?>
	</div>
	<div class="pyt-option-big">
		<?php 
			HtmlPyt::selectlist('options[export]', array(
				'options' => FramePyt::_()->getModule('export')->getModel()->exportFormats,
				'value' => UtilsPyt::getArrayValue($options, 'export'),
				'attrs' => 'data-placeholder="' . __('Select formats', 'publish-your-table') . '"'
			));
		?>
	</div>
</div>
<div class="pyt-option-wrapper">
	<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Select position of export links around of table.', 'publish-your-table'); ?>">
		<?php esc_html_e('Export links position', 'publish-your-table'); ?>
	</div>
	<div class="pyt-option-value">
		<?php 
			HtmlPyt::selectbox('options[export_position]', array(
				'options' => array('before' => __('Before', 'publish-your-table'), 'after' => __('After', 'publish-your-table')),
				'value' => UtilsPyt::getArrayValue($options, 'export_position', 'before'),
			));
		?>
	</div>
</div>
<div class="pyt-option-wrapper">
	<div class="pyt-option-label pubydoc-tooltip" title="<?php 
		esc_attr_e('Allows you to export only visible data: filtered rows with sorting.', 'publish-your-table'); 
		if ($this->type == 3) {
			echo '<br><br><b>';
			esc_attr_e('Important!', 'publish-your-table'); echo '</b> '; esc_attr_e('For tables built on the basis of a query from the database: the option will work only if an integer identifier is specified as a unique field.', 'publish-your-table');
		}
		?>">
		<?php esc_html_e('Export only visible', 'publish-your-table'); ?>
	</div>
	<div class="pyt-option-value">
		<?php 
			HtmlPyt::checkbox('options[export_visible]', array(
				'value' => UtilsPyt::getArrayValue($options, 'export_visible', 0, 1),
			));
		?>
	</div>
</div>
<div class="pyt-option-wrapper">
	<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Choose the paper size for PDF pages.', 'publish-your-table'); ?>">
		<?php esc_html_e('PDF paper size', 'publish-your-table'); ?>
	</div>
	<div class="pyt-option-value">
		<?php 
			HtmlPyt::selectbox('options[pdf_size]', array(
				'options' => array(
					'A4' => 'A4',
					'A3' => 'A3',
					'A5' => 'A5',
					'LETTER' => __('Letter', 'publish-your-table'),
					'LEGAL' => __('Legal', 'publish-your-table'),
					'TABLOID' => __('Tabloid', 'publish-your-table')),
				'value' => UtilsPyt::getArrayValue($options, 'pdf_size', 'auto', 'publish-your-table'),
			));
		?>
	</div>
</div>
<div class="pyt-option-wrapper">
	<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Choose the orientation for PDF pages.', 'publish-your-table'); ?>">
		<?php esc_html_e('PDF page orientation', 'publish-your-table'); ?>
	</div>
	<div class="pyt-option-value">
		<?php 
			HtmlPyt::selectbox('options[pdf_orientation]', array(
				'options' => array('portrait' => __('Portrait', 'publish-your-table'), 'landscape' => __('Landscape', 'publish-your-table')),
				'value' => UtilsPyt::getArrayValue($options, 'pdf_orientation', 'portrait'),
			));
		?>
	</div>
</div>
<?php 
	$parent = UtilsPyt::getArrayValue($options, 'export_logo', 0, 1);
	$classHidden = $parent ? '' : 'pytHidden';
?>
<div class="pyt-option-wrapper">
	<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Automticaly appends selected logo for output pdf or printing.', 'publish-your-table'); ?>">
		<?php esc_html_e('Export Logo', 'publish-your-table'); ?>
	</div>
	<div class="pyt-option-value">
		<?php 
			HtmlPyt::checkbox('options[export_logo]', array(
				'value' => $parent
			));
		?>
	</div>
</div>
<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[export_logo]">
	<div class="pyt-option-label">
		<button class="button button-small pyt-icon-select"><?php esc_html_e('Select Logo', 'publish-your-table'); ?></button>
	</div>
	<div class="pyt-option-value">
		<div class="pyt-icon-preview" style="<?php echo UtilsPyt::getArrayValue($options, 'logo_icon'); ?>">
			<?php 
				HtmlPyt::hidden('options[logo_icon]', array(
					'value' => UtilsPyt::getArrayValue($options, 'logo_icon'),
					'attrs' => 'class="pyt-icon-input"'
				));
			?>
		</div>
	</div>
</div>
<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[export_logo]">
	<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Select position of table logotype', 'publish-your-table'); ?>">
		<?php esc_html_e('Logo position', 'publish-your-table'); ?>
	</div>
	<div class="pyt-option-value">
		<?php 
			HtmlPyt::selectbox('options[logo_position]', array(
				'options' => array('top' => __('Above table', 'publish-your-table'), 'bottom' => __('Below table', 'publish-your-table')),
				'value' => UtilsPyt::getArrayValue($options, 'logo_position', 'top'),
			));
		?>
	</div>
</div>
<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[export_logo]">
	<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Select alignment of table logotype', 'publish-your-table'); ?>">
		<?php esc_html_e('Logo alignment', 'publish-your-table'); ?>
	</div>
	<div class="pyt-option-value">
		<?php 
			HtmlPyt::selectbox('options[logo_alignment]', array(
				'options' => array('left' => __('Left', 'publish-your-table'), 'center' => __('Center', 'publish-your-table'), 'right' => __('Right', 'publish-your-table')),
				'value' => UtilsPyt::getArrayValue($options, 'logo_alignment', 'left'),
			));
		?>
	</div>
</div>
