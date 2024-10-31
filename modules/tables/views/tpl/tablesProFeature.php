<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pubydoc-pro-desc">
	<?php
		/* translators: %s url */ 
		echo sprintf(esc_html__('Please be advised that this feature available only in PRO version. You can %s today and have all PRO features of plugin!', 'publish-your-table'), '<a href="' . esc_url($this->pro_url) . '" class="button button-mini" target="_blank">' . esc_url__('Get Pro', 'publish-your-table') . '</a>');
	?>
</div>
