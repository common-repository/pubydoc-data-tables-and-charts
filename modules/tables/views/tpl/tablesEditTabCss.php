<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="block-tab" id="block-tab-css">
	<div class="row pyt-css-content">
		<div class="col-12">
			<div>
				<label><?php esc_html_e('Here you can add custom CSS for the current Table.', 'publish-your-table'); ?></label>
				<div class="cm-editor-container">
				<?php
					HtmlPyt::textarea('add_css', array(
						'value' => $this->add_css,
						'attrs' => 'id="pytCssEditor"'
						));
					?>
				</div>
			</div>
		</div>
	</div>
</div>