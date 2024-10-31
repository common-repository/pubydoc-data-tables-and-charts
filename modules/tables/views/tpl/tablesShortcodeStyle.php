<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style type="text/css" id="pyt-table-<?php echo esc_attr($this->tableId); ?>-css">
	<?php HtmlPyt::echoEscapedHtml($this->styles); ?>
</style>
