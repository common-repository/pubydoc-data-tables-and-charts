<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<li>
	<button class="pubydoc-tooltip" data-method="add_diagram" title="<?php esc_attr_e('Create diagram', 'publish-your-table'); ?>">
		<i class="fa fa-fw fa-bar-chart"></i>
	</button>
</li>
<li>
	<button class="pubydoc-tooltip" data-method="сonditions" title="<?php esc_attr_e('Add conditional formatting to cells', 'publish-your-table'); ?>">
		<i class="fa fa-fw fa-balance-scale"></i>
	</button>
</li>
<li>
	<button class="pubydoc-tooltip" data-toolbar="#toolbar-editableField" title="<?php esc_attr_e("Add <b>editable field</b> for selected cells to edit cell value on frontend", 'publish-your-table'); ?>">
		<span class="fa-stack">
			<i class="fa fa-fw fa-square-o fa-stack-2x"></i>
			<i class="fa fa-fw fa-pencil fa-stack-1x"></i>
		</span>
	</button>
	<div id="toolbar-editableField" class="toolbar-content">
		<a href="#" data-method="add_editable"><i class="fa fa-fw fa-pencil-square-o"></i></a>
		<a href="#" data-method="remove_editable"><i class="fa fa-fw fa-trash-o"></i></a>
	</div>
</li>
<li>
	<button class="pubydoc-tooltip" data-toolbar="#toolbar-tooltipCell" title="<?php esc_attr_e('Convert the cell content into icon with tooltip.', 'publish-your-table'); ?>">
		<span class="fa-stack">
			<i class="fa fa-fw fa-square-o fa-stack-2x"></i>
			<i class="fa fa-fw fa-info fa-stack-1x"></i>
		</span>
	</button>
	<div id="toolbar-tooltipCell" class="toolbar-content">
		<a href="#" data-method="add_tooltip"><i class="fa fa-fw fa-plus"></i></a>
		<a href="#" data-method="remove_tooltip"><i class="fa fa-fw fa-trash-o"></i></a>
	</div>
</li>
<li>
	<button class="pubydoc-tooltip" data-toolbar="#toolbar-collapsible" title="<?php esc_attr_e('Make the rows collapsible. First collapsible row become the &quot;main&quot; row with control button. Other collapsible rows will be hidden by default - user might show / hide them by pressing on control button of &quot;main&quot; row.<br><br><b>Important!</b> This option makes sense only if table is not on responsive mode (or responsive mode is disabled) and not in Automatic column hiding mode.', 'publish-your-table'); ?>">
		<span class="fa-stack">
			<i class="fa fa-fw fa-square-o fa-stack-2x"></i>
			<i class="fa fa-fw fa-compress fa-stack-1x"></i>
		</span>
	</button>
	<div id="toolbar-collapsible" class="toolbar-content">
		<a href="#" data-method="add_collapsible"><i class="fa fa-fw fa-plus"></i></a>
		<a href="#" data-method="remove_collapsible"><i class="fa fa-fw fa-trash-o"></i></a>
	</div>
</li>
