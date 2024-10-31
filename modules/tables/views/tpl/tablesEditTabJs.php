<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="block-tab" id="block-tab-js">
	<div class="row pyt-js-content">
		<div class="col-12">
			<div>
				<label><?php esc_html_e('Here you can add custom JavaScript which will be executed when the table is initialized.', 'publish-your-table'); ?></label>
				<div class="cm-editor-container">
				<?php
					HtmlPyt::textarea('add_js', array(
						'value' => $this->add_js,
						'attrs' => 'id="pytJsEditor"'
						));
					?>
				</div>
			</div>
		</div>
	</div>
</div>
