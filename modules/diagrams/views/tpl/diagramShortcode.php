<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// render Loader
echo $this->loader;
?>
<div
	class="pyt-diagram-front"
    id="pyt-diagram-<?php echo esc_attr($this->view_id); ?>"
    data-id="<?php echo esc_attr($this->id); ?>"
    data-view-id="<?php echo esc_attr($this->view_id); ?>"
    data-dynamic="<?php echo esc_attr($this->dynamic); ?>"
    data-refresh="<?php echo esc_attr($this->refresh); ?>"
    data-highlighting="<?php echo esc_attr($this->highlighting); ?>"
    data-table-id="<?php echo esc_attr($this->table_id); ?>"
    data-type="<?php echo esc_attr($this->type); ?>"
    data-options="<?php echo htmlentities(UtilsPyt::jsonEncode($this->options)); ?>"
    data-config="<?php echo htmlentities(UtilsPyt::jsonEncode($this->config)); ?>"
    data-raw="<?php echo htmlentities(UtilsPyt::jsonEncode($this->raw)); ?>"
>
    <!-- Diagram goes here -->
</div>