<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<li>
	<span class="toolContainer">
	<?php
		HtmlPyt::selectFontList('', array(
			'attrs' => 'id="tbeFontFamily" class="pubydoc-tooltip tool" data-method="family" data-event="change" title="' . __('Font Family', 'publish-your-table') . '"'
		));
		?>
	</span>
</li>
