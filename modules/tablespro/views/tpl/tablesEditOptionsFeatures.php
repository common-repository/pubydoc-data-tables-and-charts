<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$options = $this->options;
?>
<?php if ($this->type != 1) { ?>
	<div class="pyt-option-wrapper">
		<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Save table data entered through frontend fields. Refer to the <i class="fa fa-fw fa-pencil-square-o"></i> buttons on Extended builder toolbar.', 'publish-your-table');
			if ($this->type == 3) {
				echo '<br><br><b>';
				esc_attr_e('Important!', 'publish-your-table'); echo '</b> '; esc_attr_e('For tables built on the basis of a query from the database: the option will work only if Unique Fields are specified.', 'publish-your-table');
			}
			?>">
			<?php esc_html_e('Save frontend fields', 'publish-your-table'); ?>
		</div>
		<div class="pyt-option-value">
			<?php 
				HtmlPyt::checkbox('options[efields_save]', array(
					'checked' => UtilsPyt::getArrayValue($options, 'efields_save', 0, 1),
				));
			?>
		</div>
	</div>
<?php } ?>
<div class="pyt-option-wrapper">
	<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Adds a symbol ✓ to last edited cell.', 'publish-your-table'); ?>">
		<?php esc_html_e('Mark last edited cell', 'publish-your-table'); ?>
	</div>
	<div class="pyt-option-value">
		<?php 
			HtmlPyt::checkbox('options[efields_mark_last]', array(
				'checked' => UtilsPyt::getArrayValue($options, 'efields_mark_last', 0, 1),
			));
		?>
	</div>
</div>
<?php 
	$parent = UtilsPyt::getArrayValue($options, 'efields_logged', 0, 1);
	$classHidden = $parent ? '' : 'pytHidden';
?>
<div class="pyt-option-wrapper">
	<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Allows to use frontend fields only for logged in users', 'publish-your-table'); ?>">
		<?php esc_html_e('Use for logged in users only', 'publish-your-table'); ?>
	</div>
	<div class="pyt-option-value">
		<?php 
			HtmlPyt::checkbox('options[efields_logged]', array(
				'checked' => UtilsPyt::getArrayValue($options, 'efields_logged', 0, 1),
			));
		?>
	</div>
</div>
<div class="pyt-option-wrapper pyt-option-sub <?php echo esc_attr($classHidden); ?>" data-parent="options[efields_logged]">
	<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Allows to use frontend fields only for users with selected roles. If there are no chosen roles - all logged in users will have ability to use the frontend fields.', 'publish-your-table'); ?>">
		<?php esc_html_e('Use for current roles only', 'publish-your-table'); ?>
	</div>
	<div class="pyt-option-big">
		<?php 
			HtmlPyt::selectlist('options[efields_roles]', array(
				'options' => FramePyt::_()->getModule('options')->getAvailableUserRolesSelect(),
				'value' => UtilsPyt::getArrayValue($options, 'efields_roles'),
				'attrs' => 'data-placeholder="' . __('Select roles', 'publish-your-table') . '"'
			));
		?>
	</div>
</div>
<?php if (empty($this->type)) { ?>
	<div class="pyt-option-wrapper">
		<div class="pyt-option-label pubydoc-tooltip" title="<?php esc_attr_e('Select the file extension that will be available for upload.', 'publish-your-table'); ?>">
			<?php esc_html_e('Allow these file extensions', 'publish-your-table'); ?>
		</div>
		<div class="pyt-option-big">
			<?php 
				HtmlPyt::selectlist('options[efields_extensions]', array(
					'options' => FramePyt::_()->getModule('tablespro')->fileTypes,
					'value' => UtilsPyt::getArrayValue($options, 'efields_extensions'),
					'key' => 'value',
					'attrs' => 'data-placeholder="' . __('Select extensions', 'publish-your-table') . '"'
				));
			?>
		</div>
	</div>
<?php } ?>
