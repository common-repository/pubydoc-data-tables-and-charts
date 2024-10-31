<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="pubydoc-bar pubydoc-titlebar">
	<ul class="pubydoc-bar-controls">
		<li class="pubydoc-title-icon">
			<i class="fa fa-gear"></i>
		</li>
		<li class="pubydoc-title-text">
			Common Settings
		</li>
	</ul>
	<div class="pubydoc-clear"></div>
</section>
<section>
	<form id="pytSettingsForm" class="pytInputsWithDescrForm">
		<div class="pubydoc-item pubydoc-panel">
		<?php 
		foreach ($this->options as $optCatKey => $optCatData) { 
			$opts = UtilsPyt::getArrayValue($optCatData, 'opts', [], 2);
			$parents = UtilsPyt::getArrayValue($optCatData, 'parents', [], 2);
			foreach ($opts as $optKey => $opt) {
				$htmlType = isset($opt['html']) ? $opt['html'] : false;
				if (empty($htmlType) || isset($parents[$optKey])) {
					continue;
				}
				?>
				<div class="row row-options-block">
					<div class="<?php HtmlPyt::blockClasses('label'); ?>">
						<?php 
						echo esc_html($opt['label']); 
						if (!empty($opt['changed_on'])) { 
						?>
							<span class="options-description">
								<?php 
								if ($opt['value']) {
									/* translators: %s: label */
									echo esc_html(sprintf(__('Turned On %s', 'publish-your-table'), DatePyt::_($opt['changed_on'])));
								} else {
									/* translators: %s: label */
									echo esc_html(sprintf(__('Turned Off %s', 'publish-your-table'), DatePyt::_($opt['changed_on'])));
								}
								?>
							</span>
						<?php } ?>
					</div>
					<div class="<?php HtmlPyt::blockClasses('info'); ?>">
						<i class="fa fa-question pubydoc-tooltip" title="<?php echo esc_attr($opt['desc']); ?>"></i>
					</div>
					<div class="<?php HtmlPyt::blockClasses('values'); ?>">
						<div class="options-value">
						<?php 
						if (!empty($opt['pro_link'])) {
							HtmlPyt::proOptionLink($opt['pro_link']); 
						} else {
							$name = 'opt_values[' . $optKey . ']';
							HtmlPyt::$htmlType($name, $this->getHtmlOptions($optKey, $opt));
							if (in_array($optKey, $parents)) {
								$hiddenClass = $opt['value'] ? '' : 'pubydoc-hidden';
								foreach (array_keys($parents, $optKey) as $childKey) {
									if (isset($opts[$childKey])) {
									?>
									</div>
									<div class="options-value <?php echo esc_attr($hiddenClass); ?>" data-parent="<?php echo esc_attr($name); ?>">
										<div class="options-label">
									<?php
										$childOpt = $opts[$childKey];
										$htmlChildType = $childOpt['html'];
										echo esc_html($childOpt['label']);
									?>
										</div>
									<?php
										HtmlPyt::$htmlChildType('opt_values[' . $childKey . ']', $this->getHtmlOptions($childKey, $childOpt));
									}
								}
							}
						}
						?>
						</div>
					</div>
				</div>
			<?php } ?>
		<?php } ?>

		<div class="pubydoc-clear"></div>
		</div>
		<?php 
			HtmlPyt::hidden('mod', array('value' => 'options'));
			HtmlPyt::hidden('action', array('value' => 'saveGroup'));
		?>
	</form>
</section>
<section class="pubydoc-bar">
	<ul class="pubydoc-bar-controls">
		<li title="<?php echo esc_attr__('Save all options', 'publish-your-table'); ?>">
			<button class="button button-primary" id="pytSettingsSaveBtn" data-toolbar-button>
				<i class="fa fa-fw fa-save"></i>
				<?php esc_html_e('Save', 'publish-your-table'); ?>
			</button>
		</li>
	</ul>
	<div class="pubydoc-clear"></div>
</section>
