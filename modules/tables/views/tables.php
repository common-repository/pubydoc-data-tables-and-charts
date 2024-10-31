<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TablesViewPyt extends ViewPyt {

	public function getTabAllTables() {
		$assets = AssetsPyt::_();
		$assets->loadCoreJs();
		$assets->loadDataTables(array('buttons', 'responsive'));
		$assets->loadAdminEndCss();

		$frame = FramePyt::_();
		$path = $this->getModule()->getModPath() . 'assets/';
		$frame->addScript('pyt-tables-list', $path . 'js/admin.tables.list.js');
		$frame->addStyle('pyt-tables', $path . 'css/admin.tables.css');
		$newUrl = $frame->getModule('adminmenu')->getTabUrl('tables-new');

		$settings = array(
			'emptyTable' => esc_html__('You have no Tables for now.', 'publish-your-table') . ' <a href="' . $newUrl . '">' . esc_html__('Create', 'publish-your-table') . '</a> ' . esc_html__('your first Table', 'publish-your-table') . '!',
			'lengthMenu' => esc_html__('Show', 'publish-your-table'),
			'info' => esc_html__('Showing', 'publish-your-table'),
			'btn-delete' => esc_html__('Delete selected', 'publish-your-table'),
			'btn-export' => esc_html__('Export', 'publish-your-table'),
			'btn-import' => esc_html__('Import', 'publish-your-table'),
			'btn-add' => esc_html__('Add table', 'publish-your-table'),
			'add-url' => $newUrl,
			'remove-confirm' => esc_html__('Are you sure want to remove %s table(s)?', 'publish-your-table'),
		);
		$this->assign('settings', $settings);
		$this->assign('is_pro', $frame->isPro());

		return parent::getContent('tablesAllTables');
	}
	public function getTabAllDiagrams() {
		$assets = AssetsPyt::_();
		$assets->loadCoreJs();
		$assets->loadAdminEndCss();
		$this->assign('pro_url', FramePyt::_()->getProUrl());
		return parent::getContent('tablesAllDiagrams');
	}

	public function getTabNewTable() {
		$assets = AssetsPyt::_();
		$assets->loadCoreJs();
		$assets->loadAdminEndCss();

		$frame = FramePyt::_();
		$path = $this->getModule()->getModPath() . 'assets/';
		$frame->addScript('pyt-tables-new', $path . 'js/admin.tables.new.js');
		$frame->addStyle('pyt-tables', $path . 'css/admin.tables.css');

		$this->assign('menu_tabs', $this->getModule()->getMenuTableTypeList());
		$this->assign('is_pro', $frame->isPro());
		$this->assign('pro_url', $frame->getProUrl());

		return parent::getContent('tablesNewTable');
	}
	public function getTabEditTable( $id ) {
		$assets = AssetsPyt::_();
		$assets->loadCoreJs();
		$assets->loadCodemirror();

		if (empty($id)) {
			return parent::getContent('tablesEditTableNotFound');
		}

		$table = $this->getModel('tables')->getTableData($id);
		if (!$table) {
			return parent::getContent('tablesEditTableNotFound');
		}
		$type = $table['type'];
		
		$dtPlugins = array('buttons', 'fixedColumns', 'responsive');
		if (true) {
			$dtPlugins[] = 'print';
			$dtPlugins[] = 'html5';
		}
		if (empty($type)) {
			wp_enqueue_editor();
			wp_enqueue_script('media-upload');
		}
		$module = $this->getModule();
		$path = $module->getModPath() . 'assets/';
		$frame = FramePyt::_();
		$lib = $path . 'lib/';
		$pqgrid = $lib . 'pqgrid/';
		

		$assets->loadDataTables($dtPlugins, false);
		$assets->loadSlimscroll();
		$assets->loadLoaders();
		
		$assets->loadColorPicker();
		$assets->loadAdminEndCss();
		$frame->addStyle('pyt-tables', $path . 'css/admin.tables.css');

		$fullBuilder = empty($type);

		$frame->addScript('pyt-pqgrid', $pqgrid . 'pqgrid.min.js');
		$frame->addStyle('pyt-pqgrid', $pqgrid . 'pqgrid.min.css');
		$frame->addScript('pyt-pqformulas', $pqgrid . 'pqformulas.js');

		$frame->addStyle('pyt-pqgrid-ui', $pqgrid . 'pqgrid.ui.min.css');
		$frame->addStyle('pyt-pqgrid-theme', $pqgrid . 'pqgrid_theme.css');

		$toolbar = $lib . 'toolbar/';
		$frame->addScript('jq-toolbar', $toolbar . 'jquery.toolbar.js');
		$frame->addStyle('jq-toolbar', $toolbar . 'jquery.toolbar.css');

		$frame->addScript('pyt-builder', $path . 'js/admin.builder.js');
		$frame->addStyle('pyt-builder', $path . 'css/admin.builder.css');
		$frame->addScript('pyt-numeral', $lib . 'numeral.min.js');

		if ($fullBuilder) {
			$frame->addScript('pyt-builder-toolbar', $path . 'js/admin.builder.toolbar.js');
		}
		$frame->addScript('pyt-tables-common', $path . 'js/common.tables.js');
		$frame->addScript('pyt-tables', $path . 'js/admin.tables.edit.js');

		$frame->addStyle('pyt-tables-front', $path . 'css/front.tables.css');
		
		$options = UtilsPyt::getArrayValue($table, 'options');

		DispatcherPyt::doAction('tablesEditAddAssets', $type);

		$this->assign('table_id', $id);
		$this->assign('table_title', $table['title']);
		$this->assign('type', $type);
		$this->assign('full_builder', $fullBuilder);
		$this->assign('main_tabs', $module->getEditTableTabsList($type, ReqPyt::getVar('block')));
		$this->assign('source', UtilsPyt::getArrayValue($table, 'source'));
		$this->assign('options', $options);
		$this->assign('add_css', UtilsPyt::getArrayValue($table, 'add_css'));
		$this->assign('add_js', UtilsPyt::getArrayValue($table, 'add_js'));
		$this->assign('translations', $this->getModel('language')->getLanguages());
		$this->assign('is_pro', $frame->isPro());
		$this->assign('pro_url', $frame->getProUrl());
		
		$this->assign('shortcodes', $module->getShortcodesList($type));
		$this->assign('builder_settings', $this->getBuilderSettings($table));
		$this->assign('col_tabs', $module->getTabsColumnSettings());
		$this->assign('col_types', $module->getColumnTypes());
		$this->assign('col_respons', $module->getColumnResponsive());
		$this->assign('date_types', $module->getDateFormats());
				
		return parent::getContent('tablesEditTable');
	}

	public function getBuilderSettings( $table ) {
		$options = FramePyt::_()->getModule('options')->getModel('options');
		$pagination = $options->get('builder_pagination');
		
		$settings = array(
			'builder' => empty($table['type']) ? $table['builder'] : 1,
			'col_prefix' => $this->getModel('cells')->getColPrefix(),
			'columns' => UtilsPyt::getArrayValue($table, 'columns', array(), 2),
			'pagination' => $pagination,
			'rows_per_page' => $pagination ? (int) $options->get('builder_rows') : 0,
			'ssp' => $pagination ? (int) $options->get('builder_ssp') : 0,
			'load_table_step' => (int) $options->get('load_table_step'),

		);
		$settings = array_merge($settings, UtilsPyt::getArrayValue($table, 'tuning', array(), 2));

		return $settings;
	}

	public function getTabsJSLangs( $tabs ) {

		$langs = array();
		foreach ($tabs as $tab => $v) {
			$lang = array();
			switch ($tab) {
				case 'builder':
					$lang = array(
						'page-refresh' => esc_html__('Refresh', 'publish-your-table'),
						'page-first' => esc_html__('First page', 'publish-your-table'),
						'page-prev' => esc_html__('Previous page', 'publish-your-table'),
						'page-next' => esc_html__('Next page', 'publish-your-table'),
						'page-last' => esc_html__('Last page', 'publish-your-table'),
						'page-info' => esc_html__('{0} to {1} of {2}', 'publish-your-table'),
						'page-str' => esc_html__('Page {0} / {1}', 'publish-your-table'),

						'btn-cancel' => esc_html__('Cancel', 'publish-your-table'),
						'btn-confirm' => esc_html__('Confirm', 'publish-your-table'),
						'btn-update' => esc_html__('Update', 'publish-your-table'),
						'btn-insert' => esc_html__('Insert', 'publish-your-table'),
						'btn-delete' => esc_html__('Delete', 'publish-your-table'),
						'btn-save' => esc_html__('Save', 'publish-your-table'),
						'btn-apply' => esc_html__('Apply', 'publish-your-table'),
						'btn-copy' => esc_html__('Copy', 'publish-your-table'),
						'btn-paste' => esc_html__('Paste', 'publish-your-table'),
						'btn-undo' => esc_html__('Undo', 'publish-your-table'),
						'btn-redo' => esc_html__('Redo', 'publish-your-table'),
						'btn-left' => esc_html__('left', 'publish-your-table'),
						'btn-right' => esc_html__('right', 'publish-your-table'),
						'btn-above' => esc_html__('above', 'publish-your-table'),
						'btn-below' => esc_html__('below', 'publish-your-table'),
						'btn-copyrange' => esc_html__('Copy range', 'publish-your-table'),
						'btn-diagram' => esc_html__('Create diagram', 'publish-your-table'),

						'col-add' => esc_html__('Add Column', 'publish-your-table'),
						'col-delete' => esc_html__('Delete Column(s)', 'publish-your-table'),
						'row-add' => esc_html__('Add Data', 'publish-your-table'),
						'row-edit' => esc_html__('Edit Data', 'publish-your-table'),
						'bulk-label' => esc_html__('Bulk Actions',  'publish-your-table'),
						'bulk-edit' => esc_html__('Edit', 'publish-your-table'),
						'bulk-delete' => esc_html__('Delete', 'publish-your-table'),

						'col-insert' => esc_html__('Add column', 'publish-your-table'),
						'col-settings' => esc_html__('Column settings', 'publish-your-table'),

						'page-save' => esc_html__('The data on the current page has been changed. Are you sure you do not want to save them first?', 'publish-your-table'),
						'select-cells' => esc_html__('You must select at least one cell in the table.', 'publish-your-table'),
						
						'tbe-col-sortable' => esc_html__('Sortable', 'publish-your-table'),
						'tbe-header-style' => esc_html__('Header styles', 'publish-your-table'),
						'tbe-col-merge' => esc_html__('Merge', 'publish-your-table'),
						'tbe-col-next' => esc_html__('with next column', 'publish-your-table'),
						'tbe-col-prev' => esc_html__('with prev column', 'publish-your-table'),
						'tbe-col-letters' => esc_html__('Toggle column letters', 'publish-your-table'),
						'tbe-col-filter' => esc_html__('Toggle filter row', 'publish-your-table'),
						'tbe-col-multi' => esc_html__('Multiple sorting', 'publish-your-table'),
						'tbe-row-insert' => esc_html__('Insert row', 'publish-your-table'),
						'tbe-row-delete' => esc_html__('Delete row', 'publish-your-table'),
						
						'tbe-col-delete' => esc_html__('Delete column', 'publish-your-table'),
					);
					break;
				default:
			}
			$langs[$tab] = DispatcherPyt::applyFilters('tableTabsJSLangs', $lang, $tab);
		}
		return $langs;
	}
}
