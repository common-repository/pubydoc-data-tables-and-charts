<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class ShortcodeViewPyt extends ViewPyt {
	//protected $isSingleCell = array();
	//protected $isTablePart = array();
	/**
	 * Data for loading tables' rows from history
	 */
    public $isFromHistory = array();
	public $historyData = array();
	/**
	 * Check for auto import data from Google Spreadsheet
	 */

	// table styles
	private $_tableCSS = [];
	private $_excludeFonts = false;
	public $showFonts = false;
	public $tableFonts = [];

	/**
	 * Contains the value of "search" shortcode param for applying it to table
	 */
	private $tableSearch = '';
	private $shortAttributes = array();
	
	public $isPreview = false;

	public function addTableCssArray( $selector, $style, $important = false ) {
		foreach ($style as $key => $value) {
			$this->addTableCss($selector, $key, $value, $important);
		}
	}
	public function addTableCss( $selector, $style, $value, $important = false ) {
		if ($style == 'font-family') {
			if ($this->showFonts) $this->addTableFont($value);
			$value = '"' . $value . '"';
		}
		$this->_tableCSS[$selector][$style] = $value . ($value != '' && $important ? '!important' : '');
	}
	public function addTableFont($font) {
		if (!in_array($font, $this->tableFonts) && !in_array($font, $this->_excludeFonts)) {
			$this->tableFonts[] = $font;
		}
	}
	public function resetTableCss() {
		$this->_tableCSS = [];
		$this->tableFonts = [];
		$this->_excludeFonts = $this->getModule()->getExcludeFonts();
		$this->showFonts = is_array($this->_excludeFonts);
	}
	
	public function renderTableStyleHtml() {
		$html = '';
		foreach ($this->_tableCSS as $selector => $rules) {
			$html .= $selector . '{';
			foreach ($rules as $name => $value) {
				$html .= $name . ':' . $value . ';';
			}
			$html .= '}';
		}
		return $html;
	}
	
	public function renderHtmlAssets( $table ) {
		$options = UtilsPyt::getArrayValue($table, 'options', array(), 2);

		$assets = AssetsPyt::_();
		$assets->loadCoreJs();
		$assets->loadFontAwesome();
		if (!UtilsPyt::getArrayValue($options, 'loader_disable', false)) {
			$assets->loadLoaders();
		}

		$dtPlugins = array('buttons', 'fixedColumns', 'print', 'html5');
		if (UtilsPyt::getArrayValue($options, 'responsive_mode', 0, 1) == 1) {
			$dtPlugins[] = 'responsive';
		}
		if (UtilsPyt::getArrayValue($options, 'fixed_columns', 0, 1) == 1) {
			$dtPlugins[] = 'fixedColumns';
		}
		$assets->loadDataTables($dtPlugins, false);
		
		$frame = FramePyt::_();
		$module = $this->getModule();
		$path = $module->getModPath() . 'assets/';
		$frame->addScript('pyt-pqformulas',  $path . 'lib/pqgrid/pqformulas.js');

		$frame->addStyle('pyt-tables', $path . 'css/front.tables.css');
		$frame->addScript('pyt-tables', $path . 'js/common.tables.js');
		$frame->addScript('pyt-numeral', $path . 'lib/numeral.min.js');

		DispatcherPyt::doAction('tablesFrontAddAssets', $table);
	}

	

	public function renderShortcode( $attributes ) {
		$id = UtilsPyt::getArrayValue($attributes, 'id', 0, 1);
		if (empty($id)) {
			return false;
		}

		$table = $this->getModule()->getTableObj($id);
		if (!$table) {
			return sprintf(__('The table with ID %d not exists.', 'publish-your-table'), $id);
		}

		$this->renderHtmlAssets($table);
		$attributes = DispatcherPyt::applyFilters('addShortcodeAttributes', $attributes);

		return $this->renderTableHtml($table, $attributes);
	}

	public function renderShortcodePart( $attributes ) {
		$id = UtilsPyt::getArrayValue($attributes, 'id', 0, 1);
		if (empty($id)) {
			return false;
		}
		$row = UtilsPyt::getArrayValue($attributes, 'row');
		$col = UtilsPyt::getArrayValue($attributes, 'col');

		if (empty($row) && empty($col)) {
			return __('There are not all shortcode attributes specified. Usage example', 'publish-your-table') . ':<br />' . sprintf('[%s id="{table id}" row="{row numbers splitted by comma}" col="{column numbers splitted by comma}"]', PYT_SHORTCODE_PART);
		}

		$table = $this->getModule()->getTableObj($id);
		if (!$table) {
			return sprintf(__('The table with ID %d not exists.', 'publish-your-table'), $id);
		}

		$this->renderHtmlAssets($table);

		$rowsResult = array();
		$rows = array_filter(
			explode(',', $row)
		);
		foreach ($rows as $row ){
			if (strpos($row, '-') !== false) {
				$minMaxVals = explode('-', $row);
				foreach (range($minMaxVals[0], $minMaxVals[1]) as $item) {
					$rowsResult[] = (int) $item;
				}
			} else {
				$rowsResult[] = (int) $row;
			}
		}

		$colsResult = array();
		$cols = array_filter(
			explode(',', $col)
		);
		foreach ($cols as $col ){
			if (strpos($col, '-') !== false) {
				$minMaxVals = explode('-', $col);
				foreach (range($minMaxVals[0], $minMaxVals[1]) as $item) {
					$colsResult[] = UtilsPyt::lettersToNumbers( (string) $item);
				}
			} else {
				$colsResult[] = UtilsPyt::lettersToNumbers( (string) $col);
			}
		}

		return $this->renderTableHtml($table, array('isTablePart' => true, 'partCols' => $colsResult, 'partRows' => $rowsResult));
	}
	public function renderShortcodeCell( $attributes ) {
		$id = UtilsPyt::getArrayValue($attributes, 'id', 0, 1);
		if (empty($id)) {
			return false;
		}

		$row = UtilsPyt::getArrayValue($attributes, 'row');
		$col = UtilsPyt::getArrayValue($attributes, 'col');
		if (empty($row) && empty($col)) {
			return __('There are not all shortcode attributes specified. Usage example', 'publish-your-table') . ':<br />' . sprintf('[%s id="{table id}" row="{row number}" col="{column number}"]', PYT_SHORTCODE_CELL);
		}

		$table = $this->getModule()->getTableObj($id);
		if (!$table) {
			return sprintf(__('The table with ID %d not exists.', 'publish-your-table'), $id);
		}
		$this->renderHtmlAssets($table);

		return $this->renderTableHtml($table, array('isSingleCell' => true, 'partCols' => array((int) UtilsPyt::lettersToNumbers($col)), 'partRows' => array((int) $row)));
	}
	public function renderShortcodeValue( $attributes ) {
		$id = UtilsPyt::getArrayValue($attributes, 'id', 0, 1);
		if (empty($id)) {
			return false;
		}

		$row = (int) UtilsPyt::getArrayValue($attributes, 'row');
		$col = UtilsPyt::getArrayValue($attributes, 'col');
		if (empty($row) && empty($col)) {
			return __('There are not all shortcode attributes specified. Usage example', 'publish-your-table') . ':<br />' . sprintf('[%s id="{table id}" row="{row number}" col="{column number}"]', PYT_SHORTCODE_VALUE);
		}
		$table = $this->getModule()->getTableObj($id);
		if (!$table) {
			return sprintf(__('The table with ID %d not exists.', 'publish-your-table'), $id);
		}
		$col = (int) UtilsPyt::lettersToNumbers($col);
		$rowsData = $this->getModel('cells')->getRangeData($table, array('from' => array('r' => $row, 'c' => $col), 'formated' => true), false);

		return is_array($rowsData) && !empty($rowsData) ? $rowsData[0][0] : __('No value', 'publish-your-table');
	}

	public function renderTableHtml( $table, $params = array() ) {
		if (!$table) {
			return false;
		}

		$id = UtilsPyt::getArrayValue($table, 'id', 0, 1);
		$viewId = UtilsPyt::getArrayValue($table, 'view_id');
		$isPreview = $viewId == 'preview';

		$options = UtilsPyt::getArrayValue($table, 'options', array(), 2);

		if (!$isPreview && UtilsPyt::getArrayValue($options, 'disallow_index', false) && $this->disallowIndexing()) {
            return;
        }

		$frame = FramePyt::_();
		$isPro = $frame->isPro();

		$cache = CachePyt::_();
		$cachePath = $cache->getDirectory('cache_tables') . DS . $id;

		//$dispatcher->apply('before_table_render', array($table));

		$tableType = UtilsPyt::getArrayValue($table, 'type', 0, 1);
		
		$this->resetTableCss(UtilsPyt::getArrayValue($table, 'add_css'));

		$isPage = UtilsPyt::getArrayValue($params, 'isPage', false);

		$params['isPreview'] = $isPreview;

		$isTablePart = UtilsPyt::getArrayValue($params, 'isTablePart', false);
		$isSingleCell = UtilsPyt::getArrayValue($params, 'isSingleCell', false);

		$params['isSSP'] = (!$isSingleCell && !$isTablePart
			&& UtilsPyt::getArrayValue($options, 'paging', false)
			&& UtilsPyt::getArrayValue($options, 'ssp', false)
		);

		$enableCache = (!$isPreview && !$isPage
			&& !UtilsPyt::getArrayValue($options, 'disable_cache', false)
			&& $tableType == 0
			&& !$isSingleCell
			&& !$isTablePart);


        if ($enableCache && file_exists($cachePath)) {
			// Connect scripts and styles depending on table settings and table's cells settings for table cache
			return file_get_contents($cachePath);
        }

		$params['withFn'] = true;
		if (empty($tableType)) {
			$rowsData = $this->getModel('cells')->getFrontRows($table, $params);
		} else {
			$rowsData = DispatcherPyt::applyFilters('getFrontRows', array(), $table, $params);
		}

		$this->addClassesStyles($viewId, $rowsData['classes']);
		unset($rowsData['classes']);

		$table['encoded_title'] = htmlspecialchars($table['title'], ENT_QUOTES);
		
		if ($this->showFonts) {
			if (UtilsPyt::getArrayValue($options, 'custom_css', false, 1)) {
				$custom = UtilsPyt::getArrayValue($options, 'styles', array(), 2);
				$headerFont = UtilsPyt::getArrayValue($custom, 'header_font_family');
				if (!empty($headerFont)) {
					$this->addTableFont($headerFont);
				}
				$bodyFont = UtilsPyt::getArrayValue($custom, 'cell_font_family');
				if (!empty($bodyFont)) {
					$this->addTableFont($bodyFont);
				}
			}
		}

		if ($isPage) {
			return array_merge($rowsData, array('css' => $this->renderTableStyleHtml(), 'fonts' => $this->tableFonts));
		}
		
		$this->assign('table', $table);
		$this->assign('data', $rowsData);
		$this->assign('params', $params);
		$this->assign('is_feed', is_feed());
		$this->assign('is_pro', $frame->isPro());

		$renderData = parent::getContent('tablesShortcode');

		$renderData = '<style id="pyt-table-' . $viewId . '-css" type="text/css" class="pyt-styles-front">' . $this->renderTableStyleHtml() . '</style>' . $renderData;

        if ($enableCache) {
        	file_put_contents($cachePath, $renderData);
        }
		return $renderData;
	}
	
	public function addClassesStyles($id, $classes) {
		$wrap = '#pyt-table-' . $id . '-wrapper .';

		foreach ($classes as $class => $styles) {
			$this->addTableCssArray($wrap . $class, $styles, false);
		}
	}

	public function addTableStyles( $table ) {
		$id =  $table['id'];
		$module = $this->getModule();

		if (in_array($id, $module->tablesStyles)) return '';

		$styles = '';
		if(!empty($table['css'])) {
			$styles = $table['css'];
			$styles = str_replace('#__pyt', '.pyt-table-' . $id . '-wrap', trim(preg_replace('/\/\*.*\*\//Us', '', $styles)));
		}

		if(!empty($styles)) {
			$this->assign('tableId', $id);
			$this->assign('styles', $styles);

			return parent::getContent('tablesShortcodeStyle');
		}
		$module->tablesStyles[] = $id;
		return '';
	}

	public function disallowIndexing() {
		$userAgent = UtilsPyt::getUserBrowserString();

        $pattern = '/(abachobot|acoon|aesop_com_spiderman|ah-ha.com crawler|appie|arachnoidea|architextspider|atomz|baidu|bing|bot|deepindex|esismartspider|ezresult|fast-webcrawler|feed|fido|fluffy the spider|gigabot|google|googlebot|gulliver|gulper|gulper|henrythemiragorobot|http|ia_archiver|jeevesteoma|kit-fireball|linkwalker|lnspiderguy|lycos_spider|mantraagent|mediapartners|msn|nationaldirectory-superspider|nazilla|openbot|openfind piranha,shark|robozilla|scooter|scrubby|search|slurp|sogou|sohu|soso|spider|tarantula|teoma_agent1|test|uk searcher spider|validator|w3c_validator|wdg_validator|webaltbot|webcrawler|websitepulse|wget|winona|yahoo|yodao|zyborg)/i';
        return (bool) preg_match($pattern, $userAgent);
    }
	
}
