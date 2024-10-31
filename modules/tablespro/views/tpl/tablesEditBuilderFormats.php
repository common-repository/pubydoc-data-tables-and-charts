<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div data-parent="pytDataType" data-parent-value="button">
	<div class="pyt-input-group">
		<label><?php esc_html_e('Button text', 'publish-your-table'); ?></label>
		<?php 
			$text = __('Buy', 'publish-your-table');
			HtmlPyt::text('', array(
				'attrs' => 'data-prop="pytType.format.text" data-default="' . $text . '" placeholder="' . $text . '"'
			));
			?>
	</div>
	<div class="pyt-input-group">
		<label>
			<input type="checkbox" data-prop="pytType.format.blank" value="1">
			<span><?php esc_html_e('Open link in new tab', 'publish-your-table'); ?></span>
		</label>
	</div>
	<div class="pyt-input-group">
		<label>
			<input type="checkbox" data-prop="pytType.format.rounded" value="1">
			<span><?php esc_html_e('Make button as rounded corner', 'publish-your-table'); ?></span>
		</label>
	</div>
	<div class="pyt-input-group">
		<label><?php esc_html_e('Button style', 'publish-your-table'); ?></label>
		<?php 
			HtmlPyt::colorPicker('', array(
				'label' => __('Background', 'publish-your-table'),
				'attrs' => 'data-prop="pytType.format.colorBg"'
			));
			HtmlPyt::colorPicker('', array(
				'label' => __('Text color', 'publish-your-table'),
				'attrs' => 'data-prop="pytType.format.colorText"'
			));
			HtmlPyt::colorPicker('', array(
				'label' => __('Border color', 'publish-your-table'),
				'attrs' => 'data-prop="pytType.format.colorBorder"'
			));
			?>
	</div>
</div>
<div data-parent="pytDataType" data-parent-value="select">
	<div class="pyt-input-group">
		<label>
			<?php esc_html_e('Choices', 'publish-your-table'); ?>
			<i class="fa fa-question pubydoc-tooltip" title="<?php echo esc_attr_e('Enter each choice on a new line', 'publish-your-table'); ?>"></i>
		</label>
		<?php 
			HtmlPyt::textarea('', array(
				'attrs' => 'data-prop="pytType.format" data-default=""'
			));
			?>
	</div>
</div>
