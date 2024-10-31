<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pyt-toolbar" id="pytBuilderToolbar">
	<ul>
		<li>
			<button class="inactive pubydoc-tooltip" data-method="undo" title="<?php esc_attr_e('Undo', 'publish-your-table'); ?>">
				<i class="fa fa-undo" aria-hidden="true"></i>
			</button>
		</li>
		<li>
			<button class="inactive pubydoc-tooltip" data-method="redo" title="<?php esc_attr_e('Redo', 'publish-your-table'); ?>">
				<i class="fa fa-repeat" aria-hidden="true"></i>
			</button>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-notselect='column' title="<?php esc_attr_e('Add row above', 'publish-your-table'); ?>" data-toolbar="#toolbar-rows">
				<i class="fa fa-fw fa-th-list"></i><span> Rows</span>
			</button>
			<div id="toolbar-rows" class="toolbar-content">
				<a href="#" data-method="add_row">
					<i class="fa fa-fw fa-plus"></i>
				</a>
				<a href="#" data-method="remove_row">
					<i class="fa fa-fw fa-trash-o"></i>
				</a>
			</div>
		</li>
		<li>
			<button class="pubydoc-tooltip" title="<?php esc_attr_e('Add column on the left', 'publish-your-table'); ?>" data-toolbar="#toolbar-cols">
				<i class="fa fa-fw fa-th-large"></i><span> Columns</span>
			</button>
			<div id="toolbar-cols" class="toolbar-content">
				<a href="#" data-method="add_column">
					<i class="fa fa-fw fa-plus"></i>
				</a>
				<a href="#" data-method="remove_col">
					<i class="fa fa-fw fa-trash-o"></i>
				</a>
			</div>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-method="column_width" title="<?php esc_attr_e('Resize Columns', 'publish-your-table'); ?>">
				<i class="fa fa-exchange" aria-hidden="true"></i>
			</button>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-method="bold" id="tbeBold" title="<?php esc_attr_e('Bold', 'publish-your-table'); ?>">
				<i class="fa fa-fw fa-bold"></i>
			</button>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-method="italic" id="tbeItalic" title="<?php esc_attr_e('Italic', 'publish-your-table'); ?>">
				<i class="fa fa-fw fa-italic"></i>
			</button>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-method="underline" id="tbeUnderline" title="<?php esc_attr_e('Underline', 'publish-your-table'); ?>">
				<i class="fa fa-fw fa-underline"></i>
			</button>
		</li>
		<li>
			<button class="pubydoc-tooltip text-color toolbar-color" id="tbeTextColor" title="<?php esc_attr_e('Text color', 'publish-your-table'); ?>">
				<i class="fa fa-fw fa-font"></i>
			</button>
		</li>
		<li>
			<button class="pubydoc-tooltip bg-color toolbar-color" id="tbeBgColor" title="<?php esc_attr_e('Background color', 'publish-your-table'); ?>">
				<i class="fa-fw background-fill-icon"></i>
			</button>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-toolbar="#toolbar-alignment" title="<?php esc_attr_e('Alignment', 'publish-your-table'); ?>">
				<i class="fa fa-fw fa-align-left"></i>
			</button>
			<div id="toolbar-alignment" class="toolbar-content">
				<a href="#" data-method="left">
					<i class="fa fa-fw fa-align-left"></i>
				</a><br/>
				<a href="#" data-method="center">
					<i class="fa fa-fw fa-align-center"></i>
				</a><br/>
				<a href="#" data-method="right">
					<i class="fa fa-fw fa-align-right"></i>
				</a>
			</div>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-toolbar="#toolbar-alignment-vertical" title="<?php esc_attr_e('Vertical alignment', 'publish-your-table'); ?>">
				<i class="fa fa-fw fa-align-left fa-rotate-90"></i>
			</button>
			<div id="toolbar-alignment-vertical" class="toolbar-content">
				<a href="#" data-method="top">
					<i class="fa fa-fw fa-align-left fa-rotate-90"></i>
				</a>
				<a href="#" data-method="middle">
					<i class="fa fa-fw fa-align-center fa-rotate-90"></i>
				</a>
				<a href="#" data-method="bottom">
					<i class="fa fa-fw fa-align-right fa-rotate-90"></i>
				</a>
			</div>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-toolbar="#toolbar-word-wrapping-options" id="toolbar-word-wrapping" title="<?php esc_attr_e('Word wrapping', 'publish-your-table'); ?>">
				<i class="toolbar-word-wrap"></i>
			</button>
			<div id="toolbar-word-wrapping-options" class="toolbar-content">
				<a href="#" data-method="word-wrap-default" data-tt-content="<?php esc_attr_e('Wrap text automatically.', 'publish-your-table'); ?>">
					<i class="toolbar-word-wrap"></i>
				</a><br/>
				<a href="#" data-method="word-wrap-visible" data-tt-content="<?php esc_attr_e('Content that does not fit in the cell will be overlapping the following cells.', 'publish-your-table'); ?>">
					<i class="toolbar-word-wrap word-wrap-visible"></i>
				</a><br/>
				<a href="#" data-method="word-wrap-hidden" data-tt-content="<?php esc_attr_e('Content that does not fit in the cell will be clipped.', 'publish-your-table'); ?>">
					<i class="toolbar-word-wrap word-wrap-hidden"></i>
				</a>
			</div>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-method="format" id="tbeFormats" title="<?php esc_attr_e('Formats for cells value. All formats convert cell values to appropriate format types.', 'publish-your-table'); ?>">
				<i class="fa fa-eur" aria-hidden="true"></i>
			</button>
		</li>
		<?php 
			if ($this->is_pro) { ?>
			<li>
				<span class="toolContainer">
				<?php
					HtmlPyt::selectFontList('', array(
						'attrs' => 'id="tbeFontFamily" class="pubydoc-tooltip tool" data-method="family" data-event="change" title="' . __('Font Family', 'publish-your-table') . '"'
						));
						?>
				</span>
			</li>
		<?php } else { ?>
			<li>
				<span class="toolContainer pubydoc-show-pro">
					<span class="pubydoc-tooltip" title="<?php esc_attr_e('Font family changing available only in PRO version.', 'publish-your-table'); ?>"><?php esc_html_e('Font Family', 'publish-your-table'); ?></span>
					</span>
			</li>
		<?php } ?>
		<li>
			<span class="toolContainer">
			<?php
				HtmlPyt::selectbox('', array(
					'options' => range(5, 100, 1),
					'key' => 'value',
					'add' => 'px',
					'default' => __('Default', 'publish-your-table'),
					'attrs' => 'id="tbeFontSize" class="pubydoc-tooltip tool" data-method="size" data-event="change" title="' . __('Font Size', 'publish-your-table') . '"'
				));
				?>
			</span>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-type="text textarea html" data-method="link" title="<?php esc_attr_e('Insert link', 'publish-your-table'); ?>">
				<i class="fa fa-fw fa-link"></i>
			</button>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-type="text textarea html" data-method="media" title="<?php esc_attr_e('Insert media file', 'publish-your-table'); ?>">
				<i class="fa fa-fw fa-picture-o"></i>
				<span><?php esc_html_e('Insert Media', 'publish-your-table'); ?></span>
			</button>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-method="comment" title="<?php esc_attr_e('Comment', 'publish-your-table'); ?>">
				<i class="fa fa-fw fa-comment"></i>
			</button>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-toolbar="#toolbar-merge" data-method="merge" title="<?php esc_attr_e('Merge/unmerge selected cells', 'publish-your-table'); ?>">
				<i class="fa fa-expand"></i>
				<span><?php esc_html_e('Merge cells', 'publish-your-table'); ?></span>
			</button>
		</li>
		<?php 
			if ($this->is_pro) {
				DispatcherPyt::doAction('tablesIncludeTpl', 'tablesEditBuilderToolbar');
			} else { 
			?>
			<li>
				<span class="toolContainer pubydoc-show-pro pubydoc-tooltip" title="<?php esc_attr_e('Add diagram', 'publish-your-table'); ?>">
					<i class="fa fa-fw fa-bar-chart"></i>
				</span>
			</li>
			<li>
				<span class="toolContainer pubydoc-show-pro pubydoc-tooltip" title="<?php esc_attr_e('Add conditional formatting to cells', 'publish-your-table'); ?>">
					<i class="fa fa-fw fa-balance-scale"></i>
				</span>
			</li>
			<li>
				<span class="toolContainer pubydoc-show-pro pubydoc-tooltip" title="<?php esc_attr_e('Add <b>editable field</b> for selected cells to edit cell value on frontend', 'publish-your-table'); ?>">
					<span class="fa-stack">
						<i class="fa fa-fw fa-pencil-square-o"></i>
					</span>
				</span>
			</li>
			<li>
				<span class="toolContainer pubydoc-show-pro pubydoc-tooltip" title="<?php esc_attr_e('Convert the cell content into icon with tooltip.', 'publish-your-table'); ?>">
					<span class="fa-stack">
						<i class="fa fa-fw fa-square-o fa-stack-2x"></i>
						<i class="fa fa-fw fa-info fa-stack-1x"></i>
					</span>
				</span>
			</li>
			<li>
				<span class="toolContainer pubydoc-show-pro pubydoc-tooltip" title="<?php esc_attr_e('Make the rows collapsible. First collapsible row become the &quot;main&quot; row with control button. Other collapsible rows will be hidden by default - user might show / hide them by pressing on control button of &quot;main&quot; row.', 'publish-your-table'); ?>">
					<span class="fa-stack">
						<i class="fa fa-fw fa-square-o fa-stack-2x"></i>
						<i class="fa fa-fw fa-compress fa-stack-1x"></i>
					</span>
				</span>
			</li>
		<?php } ?>
		<li>
			<button class="pubydoc-tooltip" data-notselect='column' data-toolbar="#toolbar-invisibleRows" title="<?php esc_attr_e("Hide or remove the selected rows on frontend.  <br><br><i class='fa fa-fw fa-low-vision'></i> - hide rows (can be useful for placing intermediate calculations), <br> <i class='fa fa-fw fa-ban'></i> - remove rows (can be useful for placing information for admin side only). ", 'publish-your-table'); ?>">
				<i class="fa fa-fw fa-eye-slash"></i><span> Rows</span>
			</button>
			<div id="toolbar-invisibleRows" class="toolbar-content">
				<a href="#" data-method="add_invisible_row" title="<?php esc_attr_e('Hide rows. Can be useful for placing intermediate calculations', 'publish-your-table'); ?>"><i class="fa fa-fw fa-low-vision"></i></a>
				<a href="#" data-method="add_hidden_row" title="<?php esc_attr_e('Remove rows. Can be useful for placing information for admin side only.', 'publish-your-table'); ?>"><i class="fa fa-fw fa-ban"></i></a>
				<a href="#" data-method="remove_invis_row"><i class="fa fa-fw fa-trash-o"></i></a>
			</div>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-toolbar="#toolbar-invisibleColumns" title="<?php esc_attr_e("Hide or remove the selected columns on frontend.  <br><br><i class='fa fa-fw fa-low-vision'></i> - hide columns (can be useful for placing intermediate calculations), <br> <i class='fa fa-fw fa-ban'></i> - remove columns (can be useful for placing information for admin side only). ", 'publish-your-table'); ?>">
				<i class="fa fa-fw fa-eye-slash"></i><span> Columns</span>
			</button>
			<div id="toolbar-invisibleColumns" class="toolbar-content">
				<a href="#" data-method="add_invisible_col" title="<?php esc_attr_e('Hide columns. Can be useful for placing intermediate calculations', 'publish-your-table'); ?>"><i class="fa fa-fw fa-low-vision"></i></a>
				<a href="#" data-method="add_hidden_col" title="<?php esc_attr_e('Remove columns. Can be useful for placing information for admin side only.', 'publish-your-table'); ?>"><i class="fa fa-fw fa-ban"></i></a>
				<a href="#" data-method="remove_invis_col"><i class="fa fa-fw fa-trash-o"></i></a>
			</div>
		</li>
		<li>
			<button class="pubydoc-tooltip" data-toolbar="#toolbar-shortcode" title="<?php esc_attr_e("Do shortcodes in the selected cells.", 'publish-your-table'); ?>">
				<span class="fa-stack">
					<i class="fa fa-fw fa-square-o fa-stack-2x"></i>
					<i class="fa fa-fw fa-magic fa-stack-1x"></i>
				</span>
			</button>
			<div id="toolbar-shortcode" class="toolbar-content">
				<a href="#" data-method="add_shortcode"><i class="fa fa-fw fa-plus"></i></a>
				<a href="#" data-method="remove_shortcode"><i class="fa fa-fw fa-trash-o"></i></a>
			</div>
		</li>
		<li>
			<button class="pubydoc-tooltip pyt-toolbar-switcher" id="tbeMultisort" data-method="multiple_sorting" title="<?php esc_attr_e('Enable Multiple Sorting', 'publish-your-table'); ?>">
				<i class="fa fa-sort-alpha-asc" aria-hidden="true"></i>
			</button>
		</li>
		<li>
			<button class="pubydoc-tooltip pyt-toolbar-switcher" id="tbeColumnLetter" data-method="column_letters" title="<?php esc_attr_e('Show column letters', 'publish-your-table'); ?>">
				<i class="fa fa-header" aria-hidden="true"></i>
			</button>
		</li>
		<li>
			<button class="pubydoc-tooltip pyt-toolbar-switcher" id="tbeShowFilter" data-method="show_filter" title="<?php esc_attr_e('Show filter row', 'publish-your-table'); ?>">
				<i class="fa fa-filter" aria-hidden="true"></i>
			</button>
		</li>
	</ul>
</div>
<div class="row align-items-center" id="pytBuilderFormula">
	<div class="col-auto formula-icon">
		<em class="function">f</em>(&times;)
	</div>
	<div class="col formula-icon">
		<input type="text" id="formula"/>
	</div>
	<div class="pubydoc-clear"></div>
</div>
