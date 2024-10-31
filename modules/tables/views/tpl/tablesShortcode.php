<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$table = $this->table;
	$data = $this->data;
	$tableId = $table['id'];
	$viewId = $table['view_id'];

	$options = UtilsPyt::getArrayValue($table, 'options', array(), 2);
	$tuning = UtilsPyt::getArrayValue($table, 'tuning', array(), 2);
	
	$isSSP = UtilsPyt::getArrayValue($this->params, 'isSSP', false);
	$isDB = UtilsPyt::getArrayValue($table, 'isDB', false);

	$isSingleCell = UtilsPyt::getArrayValue($this->params, 'isSingleCell', false);

	$tableWidth = false;
	$tableClass = 'pyt-table-' . $tableId;
	$wrapClass = $tableClass . '-wrap';

	$view = $this;
	$tableSelector = '.' . $tableClass;
	$wrapSelector = '.' . $wrapClass;
	$autoIndexNew = UtilsPyt::getArrayValue($options, 'auto_index') == 'new';
	$autoWidth = UtilsPyt::getArrayValue($options, 'auto_width', false);
	$compact = UtilsPyt::getArrayValue($options, 'compact', false);

	if (!$autoWidth) {
		$tableWidthType = UtilsPyt::getArrayValue($options, 'table_width_type', '%');
		$tableWidth = $tableWidthType == 'auto' ? 'auto' : UtilsPyt::getArrayValue($options, 'table_width', '100', 1) . $tableWidthType;
	}

	if($isSingleCell) {
		// Unset unneeded elements and features
		unset($options['header'], $options['footer'], $options['caption'], $options['description'], $options['signature'], $options['ordering'], $options['paging'], $options['searching']);
		$this->addTableCss($tableSelector, 'margin-left', 0);
		$this->addTableCssArray($tableSelector . ',' . $tableSelector . ' th,' . $tableSelector . ' td', array('width' => 'auto', 'min-width' => '100px'), true);
	}	

	// render Loader
	if (!$this->is_feed && !UtilsPyt::getArrayValue($options, 'loader_disable', false)) {
		$iconName = UtilsPyt::getArrayValue($options, 'loader_name', 'spinner');
		$iconCount = UtilsPyt::getArrayValue($options, 'loader_count', 0, 1);
		$iconColor = UtilsPyt::getArrayValue($options, 'loader_color', '#000000') . '!important';
		$loaderClass = 'pyt-loader-' . $tableId;
		$selector = '.' . $loaderClass;
		if ($iconName == 'spinner') {
			$this->addTableCss($selector, 'background-color', $iconColor);
			echo '<div class="' . esc_attr($loaderClass) . ' pubydoc-table-loader spinner"></div>';
		} else {
			$this->addTableCss($selector, 'color', $iconColor);
			echo '<div class="' . esc_attr($loaderClass) . ' pubydoc-table-loader la-' . esc_attr($iconName) . ' la-2x">' . str_repeat('<div></div>', $iconCount) . '</div>';
		}
	}

	// caption
	$description = UtilsPyt::getArrayValue($options, 'description', false) ? UtilsPyt::getArrayValue($options, 'description_text') : '';
	$caption = UtilsPyt::getArrayValue($options, 'caption', false) ? $table['title'] : '';
	$isCaption = !empty($caption) || !empty($description);
	$header = UtilsPyt::getArrayValue($options, 'header', false, 1);
	if (!$header) {
		$tableClass .= ' no-header';
	}
	$footer = UtilsPyt::getArrayValue($options, 'footer', false, 1);
	$customFooterRows = $footer && UtilsPyt::getArrayValue($options, 'custom_footer', false) ? UtilsPyt::getArrayValue($options, 'footer_rows', 1, 1) : 0;
	$fixedHeader = $header && UtilsPyt::getArrayValue($options, 'fixed_header', false);
	$fixedFooter = $footer && UtilsPyt::getArrayValue($options, 'fixed_footer', false);
	$fixedColumns = UtilsPyt::getArrayValue($options, 'fixed_columns', false);
	$fixedRowsCols = $fixedHeader || $fixedFooter || $fixedColumns;

	$signature = UtilsPyt::getArrayValue($options, 'signature', false) ? UtilsPyt::getArrayValue($options, 'signature_text') : '';

	$paging = UtilsPyt::getArrayValue($options, 'paging', false);

	// add wrapper classes
	if ($paging) {
		$pageSettings = UtilsPyt::getArrayValue($options, 'pages', array());
		$wrapClass .= ' pagination-' . UtilsPyt::getArrayValue($pageSettings, 'size', 'large');
	}
	if (UtilsPyt::getArrayValue($options, 'responsive_mode', 0, 1) == 3) {
		$wrapClass .= ' disable-responsive';
	}

	// add wrapper styles
	if ($tableWidth !== false) {
		if ($tableWidth == 'auto') {
			$this->addTableCss($wrapSelector, 'display', 'inline-block');
			$this->addTableCss($tableSelector, 'width', 'auto');
		}
		$this->addTableCss($wrapSelector, 'width', $tableWidth);
		$tableWidthTypeMobile = UtilsPyt::getArrayValue($options, 'table_width_type_mobile', '%');
		$tableWidthMobile = $tableWidthTypeMobile == 'auto' ? 'auto' : UtilsPyt::getArrayValue($options, 'table_width_mobile', '100', 1) . $tableWidthTypeMobile; 
	}
	if (!$this->is_feed) {
		$this->addTableCss($wrapSelector, 'visibility', 'hidden');
	}

	// table classes
	$dtClasses = array('compact', 'nowrap', 'stripe', 'hover', 'order-column'); // DataTable classes
	foreach ($dtClasses as $cl) {
		if (UtilsPyt::getArrayValue($options, $cl, false)) {
			$tableClass .= ' ' . $cl;
		}
	}
	$tableClass .= ' ' . UtilsPyt::getArrayValue($options, 'border', 'cell') . '-border';

	// table features
	$features = array();
	$dtFeatures = array('info', 'ordering', 'paging', 'searching');
	foreach ($dtFeatures as $f) {
		if (UtilsPyt::getArrayValue($options, $f, false)) {
			$features[] = $f;
		}
	}
	if ($autoWidth) {
		$features[] = 'autoWidth';
	}

	$columns = UtilsPyt::getArrayValue($data, 'cols', array(), 2);

	$sorting = UtilsPyt::getArrayValue($options, 'ordering', false);
	if ($sorting) {
		
		$sortSettings = UtilsPyt::getArrayValue($options, 'sortings', array());
		$sortModel = UtilsPyt::getArrayValue($tuning, 'sorter', array(), 2);
		$sorter = UtilsPyt::getArrayValue($sortModel, 'sorter', array(), 2);
		$multi = array();
		$addNum = $autoIndexNew ? 1 : 0;
		foreach ($sorter as $col) {
			$key = UtilsPyt::getArrayValue($col, 'dataIndx');
			if (isset($columns[$key])) {
				$multi[] = array($columns[$key]['num'] + $addNum, UtilsPyt::getArrayValue($col, 'dir') == 'down' ? 'desc' : 'asc');
			}
		}
		$sortSettings['multi'] = $multi;
	}
	$langs = UtilsPyt::getArrayValue($options, 'langs', array());
	$langFile = UtilsPyt::getArrayValue($langs, 'file', 'default');
	$translations = array();
	if ($langFile != 'default') {
		$langModel = $this->getModel('language');
		$translations = $langModel->getLanguagesData($langModel->getLanguages($langFile));
	}

	foreach ($langs as $key => $value) {
		if (!empty($value)) {
			$translations[$key] = $value;
		}
	}
			
	?>
	<div 
		id="pyt-table-<?php echo esc_attr($viewId); ?>-wrapper"
		class="pyt-table-wrap <?php echo esc_attr($wrapClass); ?>"
		data-id="<?php echo esc_attr($tableId); ?>"
		data-view-id="<?php echo esc_attr($viewId); ?>"
		data-fonts="<?php echo $this->showFonts ? esc_attr(json_encode($this->tableFonts)) : ''; ?>"
		data-custom-js="<?php echo esc_attr(UtilsPyt::getArrayValue($table, 'add_js')); ?>"
	<?php if ($tableWidth !== false) { ?>
		data-table-width-fixed="<?php echo esc_attr($tableWidth); ?>"
		data-table-width-mobile="<?php echo esc_attr($tableWidthMobile); ?>"
	<?php } ?>

	>
		
	<?php if ($fixedRowsCols && $isCaption) { ?>
		<div class="pyt-table-caption">
			<?php if (!empty($caption)) { ?>
				<div class="pyt-table-title"><?php echo esc_html($caption); ?></div>
			<?php 
			}
			if (!empty($description)) {
			?>
				<div class="pyt-table-desc"><?php echo esc_html($description); ?></div>
			<?php } ?>
		</div>
	<?php } ?>
		
	<table
		id="pyt-table-<?php echo esc_attr($viewId); ?>"
		class="pyt-table <?php echo esc_attr($tableClass); ?>"
		data-id="<?php echo esc_attr($tableId); ?>"
		data-view-id="<?php echo esc_attr($viewId); ?>"
		data-title="<?php echo esc_attr($table['title']); ?>"
		data-type="<?php echo esc_attr($table['type']); ?>"
		data-formats="<?php echo esc_attr(json_encode(UtilsPyt::getArrayValue($options, 'formats', array()))); ?>"
		data-features="<?php echo esc_attr(json_encode($features)); ?>"
		data-responsive-mode="<?php echo esc_attr(UtilsPyt::getArrayValue($options, 'responsive_mode', 0, 1)); ?>"
		data-server-side-processing="<?php echo $isSSP ? 1 : 0; ?>"
		data-searching-settings="<?php echo UtilsPyt::getArrayValue($options, 'searching', false) ? esc_attr(json_encode(UtilsPyt::getArrayValue($options, 'searches', array()))) : ''; ?>"
		data-paging-settings="<?php echo $paging ? esc_attr(json_encode($pageSettings)) : ''; ?>"
		data-sorting-settings="<?php echo $sorting ? esc_attr(json_encode($sortSettings)) : ''; ?>"
		data-head="<?php echo $header ? 1 : 0; ?>"
		data-foot="<?php echo $footer ? 1 : 0; ?>"
		data-foot-custom-rows="<?php echo esc_attr($customFooterRows); ?>"
		data-fixed-head="<?php echo $fixedHeader ? 1 : 0; ?>"
		data-fixed-foot="<?php echo $fixedFooter ? 1 : 0; ?>"
		data-fixed-height="<?php echo $fixedHeader || $fixedFooter ? esc_attr(UtilsPyt::getArrayValue($options, 'footer_height', 0, 1)) : 0; ?>"
		data-fixed-left="<?php echo $fixedColumns ? esc_attr(UtilsPyt::getArrayValue($options, 'fixed_left', 0, 1)) : 0; ?>"
		data-fixed-right="<?php echo $fixedColumns ? esc_attr(UtilsPyt::getArrayValue($options, 'fixed_right', 0, 1)) : 0; ?>"
		data-auto-index="<?php echo esc_attr(UtilsPyt::getArrayValue($options, 'auto_index')); ?>"
		data-sc-attributes="<?php echo esc_attr(json_encode(UtilsPyt::getArrayValue($this->params, 'scAttributes', array(), 2))); ?>"
		data-translations="<?php echo esc_attr(json_encode($translations)); ?>"
	<?php DispatcherPyt::doAction('tablesIncludeTpl', 'tablesShortcodeTableAttr', array('table' => $table, 'params' => $this->params));	?>
	>
	<?php if ($isCaption) { ?>
		<caption <?php echo $fixedRowsCols ? 'class="pyt-hidden"' : ''; ?>>
			<?php if (!empty($caption)) { ?>
				<div class="pyt-table-title"><?php echo esc_html($caption); ?></div>
			<?php 
			}
			if (!empty($description)) {
			?>
				<div class="pyt-table-desc"><?php echo esc_html($description); ?></div>
			<?php } ?>
		</caption>
	<?php }	?>
		<thead>
			<?php 
				$defClass = $header ? '' : 'pyt-invisible '; 
			?>
			<tr>
				<?php if ($autoIndexNew) { ?>
					<th class="<?php echo esc_attr($defClass); ?> nosort" data-col="auto" data-visible="1" data-width="1%"></th>
				<?php } ?>
				<?php 
				foreach($columns as $name => $column) { 
					$colClass = $defClass . $column['classes'];
					if (!$column['sortable']) {
						$colClass .= ' nosort';
					}
					$width = $column['sortable'];
					?>
					<th 
						class="<?php echo esc_attr($colClass); ?>"
						data-col="<?php echo esc_attr($name); ?>"
						data-col-index="<?php echo esc_attr($column['i']); ?>"
						<?php foreach($column['attrs'] as $k => $v) { ?>
							data-<?php echo esc_attr($k); ?>="<?php echo is_bool($v) ? (int) $v : esc_attr(is_array($v) ? json_encode($v) : $v); ?>"		
						<?php }	?>
						
					><?php echo esc_html($column['title']); ?></th>
				<?php } ?>
			</tr>
		</thead>
<?php
	$rowsBody = $data['rows'];
	$rowAttrs = $data['attrs'];

	if (!empty($customFooterRows)) {
		$rowsFoot = array_splice($rowsBody, 0 - $customFooterRows);
	}
	if (!$isSSP) {
?>
		<tbody>
		<?php 
			foreach ($rowsBody as $r => $row) {
				$attrs = $rowAttrs[$r];
				$attrsC = UtilsPyt::getArrayValue($attrs, 'c', array(), 2);
				$attrsD = UtilsPyt::getArrayValue($attrs, 'a', array(), 2);
				$attrsT = UtilsPyt::getArrayValue($attrs, 't', array(), 2);
			?>
			<tr data-id="<?php echo esc_attr(is_array($attrs['id']) ? json_encode($attrs['id']) : $attrs['id']); ?>" 
				data-row-index="<?php echo esc_attr($attrs['i']); ?>" <?php echo $attrs['f'] ? '' : ' data-not-format="1"'; ?>>
				<?php if ($autoIndexNew) { ?>
					<td<?php echo empty($attrs['h']) ? '' : ' class="pyt-invisible"'; ?>></td>
				<?php } ?>
			<?php foreach ($row as $col => $value) { ?>
				<td<?php 
					if (isset($attrsC[$col])) {
						echo ' class="' . esc_attr($attrsC[$col]) . '"';
					}
					if (isset($attrsD[$col])) {
						foreach ($attrsD[$col] as $key => $v) {
							echo ' data-' . esc_attr($key) . '="' . esc_attr($v) . '"';
						}
					}
					if (isset($attrsT[$col])) {
						foreach ($attrsT[$col] as $key => $v) {
							echo ' ' . esc_attr($key) . '="' . esc_attr($v) . '"';
						}
					}
				?>><?php HtmlPyt::echoEscapedHtml($value); ?></td>
			<?php } ?>
			</tr>
		<?php }	?>
		</tbody>
<?php }
	if ($footer) {
?>
		<tfoot>
			<?php if (empty($customFooterRows)) { ?>
				<tr>
					<?php if ($autoIndexNew) { ?>
						<th data-col="auto"></th>
					<?php } ?>
				<?php foreach($columns as $name => $column) { ?>
					<th	class="<?php echo esc_attr($column['classes']); ?>"><?php echo esc_html($column['title']); ?></th>
					<?php } ?>
				</tr>
			<?php 
			} elseif (!empty($rowsFoot)) { 
				$cntBody = count($rowsBody);
				foreach ($rowsFoot as $r => $row) {
					$attrs = $rowAttrs[$cntBody + $r];
					$classes = UtilsPyt::getArrayValue($attrs, 'c', array(), 2);
				?>
					<tr data-id="<?php echo esc_attr(is_array($attrs['id']) ? json_encode($attrs['id']) : $attrs['id']); ?>">
						<?php if ($autoIndexNew) { ?>
							<th data-col="auto"></th>
						<?php } ?>
					<?php foreach ($row as $col => $value) { ?>
						<th<?php echo isset($classes[$col]) ? ' class="' . esc_attr($classes[$col]) . '"' : ''; ?>><?php echo esc_html($value); ?></th>
					<?php } ?>
					</tr>
				<?php 
				}
			}
			?>
		</tfoot>
	<?php } ?>
	</table>
<?php if (!empty($signature)) { ?>
	<div class="pyt-table-signature"><?php echo esc_html($signature); ?></div>
<?php } ?>
</div>
