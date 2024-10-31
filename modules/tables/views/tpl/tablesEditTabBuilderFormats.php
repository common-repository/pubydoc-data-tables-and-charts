<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
						<div data-parent="pytDataType" data-parent-value="number">
							<div class="pyt-input-group">
								<label>
									<?php esc_html_e('Number format', 'publish-your-table'); ?>
									<i class="fa fa-question pubydoc-tooltip" title="<?php esc_attr_e('Set cells output format e.g. 1,000.00, 1.00, 1,000. Use format 1. for integer. Set no value to use the default format.', 'publish-your-table'); ?>"></i>
								</label>
								<?php 
									HtmlPyt::text('', array(
										'attrs' => 'data-prop="pytType.format"'
									));
									?>
							</div>
						</div>
						<div data-parent="pytDataType" data-parent-value="money">
							<div class="pyt-input-group">
								<label>
									<?php esc_html_e('Currency Format', 'publish-your-table'); ?>
									<i class="fa fa-question pubydoc-tooltip" title="<?php echo esc_attr(__('Set cells output format for currencies. For example:', 'publish-your-table') . '<br /> $ 1,000.000<br /> € 1.00<br />' . __('Set no value to use the default format.', 'publish-your-table')); ?>"></i>
								</label>
								<?php 
									HtmlPyt::text('', array(
										'attrs' => 'data-prop="pytType.format"'
									));
									?>
							</div>
						</div>
						<div data-parent="pytDataType" data-parent-value="percent">
							<div class="pyt-input-group">
								<label>
									<?php esc_html_e('Percent format', 'publish-your-table'); ?>
									<i class="fa fa-question pubydoc-tooltip" title="<?php echo esc_attr(__('Set cells output format for percent numbers. For example:', 'publish-your-table') . '<br /> 10.00%<br /> 10%<br />' . __('Set no value to use the default format.', 'publish-your-table')); ?>"></i>
								</label>
								<?php 
									HtmlPyt::text('', array(
										'attrs' => 'data-prop="pytType.format"'
									));
									?>
							</div>
						</div>
						<div data-parent="pytDataType" data-parent-value="convert">
							<div class="pyt-input-group">
								<label>
									<?php esc_html_e('Percent format', 'publish-your-table'); ?>
									<i class="fa fa-question pubydoc-tooltip" title="<?php echo esc_attr(__('Percent with Convert format sets percent format and convert cells value to percentage by division by 100. Set cells output format for percent numbers. For example:', 'publish-your-table') . '<br /> 10.00%<br /> 10%<br />' . __('Set no value to use the default format.', 'publish-your-table')); ?>"></i>
								</label>
								<?php 
									HtmlPyt::text('', array(
										'attrs' => 'data-prop="pytType.format"'
									));
									?>
							</div>
						</div>
						<div data-parent="pytDataType" data-parent-value="date">
							<div class="pyt-input-group">
								<label><?php esc_html_e('Date format', 'publish-your-table'); ?></label>
								<?php 
									HtmlPyt::selectbox('', array(
										'options' => array_merge(array('' => __('Select a Format', 'publish-your-table')), $this->date_types),
										'attrs' => 'data-prop="pytType.format"'
									));
									?>
							</div>
						</div>
						<?php 
						if ($this->is_pro) {
								DispatcherPyt::doAction('tablesIncludeTpl', 'tablesEditBuilderFormats');
							} else { 
							?>
							<div data-parent="pytDataType" data-parent-value="button select file">
								<?php include 'tablesProFeature.php';?>
							</div>
						<?php }	?>