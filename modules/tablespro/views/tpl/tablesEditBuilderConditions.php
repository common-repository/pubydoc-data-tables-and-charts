	<?php 
		if ( ! defined( 'ABSPATH' ) ) {
			exit;
		}
		$condParams = FramePyt::_()->getModule('tablespro')->getConditionsParams();
	?>
		<div class="pyt-cond-form">	
			<div class="pyt-input-group">
				<label><?php esc_html_e('Rules', 'publish-your-table'); ?></label>
				<div class="pyt-warning pytHidden" data-warning="multi">
					<?php esc_html_e('The selected cells contain different conditional formatting. To edit and set the same conditional formatting for all selected cells, adjust the rules below and save. Note that this will remove all current conditional formatting of the selected cells.', 'publish-your-table'); ?>
				</div>
				<input type="hidden" data-id="pytDataConds" data-prop="pytConds">
				<ul class="pyt-rules-wrapper">
					<li class="pyt-new-rule"><div><?php esc_html_e('New conditional rule', 'publish-your-table'); ?></div></li>
					<li class="pyt-tpl-rule"><div class="pyt-rule-preview">Text</div><div class="pyt-rule-cond"></div><div class="fa fa-trash pyt-rule-delete"></div></li>
				</ul>
			</div>
			<div class="pyt-input-group pyt-relative">
				<label>
					<?php esc_html_e('Condition type', 'publish-your-table'); ?>
					<button class="button button-small pyt-apply-cond"><?php esc_html_e('Apply', 'publish-your-table'); ?></button>
				</label>
				<?php 
					HtmlPyt::selectbox('', array(
						'options' => $condParams['types'],
						'attrs' => 'data-default="cell" data-required="1" class="pubydoc-width150" data-cond="type"'
					));
				?>
			</div>
			<div class="pyt-input-group">
				<label>
					<?php esc_html_e('Operator', 'publish-your-table'); ?>
				</label>
				<select data-default="equals" data-required="1" class="pubydoc-width150" data-cond="oper">
				<?php foreach ($condParams['opers'] as $oper => $data) { ?>
					<option data-type="<?php echo esc_attr($data['type']); ?>" value="<?php echo esc_attr($oper); ?>"><?php echo esc_attr($data['label']); ?></option>
				<?php } ?>
				</select>
			</div>
			<div class="pyt-input-group">
				<label>
					<?php esc_html_e('Value', 'publish-your-table'); ?>
				</label>
				<div class="pyt-inline-group">
				<?php 
					HtmlPyt::text('', array(
						'attrs' => 'data-default="" data-required="1" class="pubydoc-width150" data-cond="value"'
						));
					HtmlPyt::text('', array(
						'attrs' => 'data-default="" data-required="1" class="pubydoc-width150" data-cond="value2"'
						));
					?>
				</div>
			</div>
			<div class="pyt-input-group">
				<label><?php esc_html_e('Styles', 'publish-your-table'); ?></label>
				<?php 
					HtmlPyt::colorPicker('', array(
						'label' => __('Background', 'publish-your-table'),
						'attrs' => 'data-cond="styles.background-color" data-default="" data-required="1"'
					));
					HtmlPyt::colorPicker('', array(
						'label' => __('Text color', 'publish-your-table'),
						'attrs' => 'data-cond="styles.color" data-default="" data-required="1"'
				));
				?>
			</div>
			<div class="pyt-input-group">
				<label>
					<input type="checkbox" data-cond="styles.font-weight" value="bold" data-required="1">
					<span><?php esc_html_e('Bold', 'publish-your-table'); ?></span>
					<input type="checkbox" data-cond="styles.font-style" value="italic" data-required="1">
					<span><?php esc_html_e('Italic', 'publish-your-table'); ?></span>
					<input type="checkbox" data-cond="styles.text-decoration" value="underline" data-required="1">
					<span><?php esc_html_e('Underline', 'publish-your-table'); ?></span>
				</label>
			</div>
		</div>
