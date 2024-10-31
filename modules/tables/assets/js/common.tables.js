(function ($, app) {
"use strict";
// ================================================================ //
// ********************* FORMAT MODEL ***************************** //
	function FormatModel() {
		this.$obj = this;
		return this.$obj;
	}
	FormatModel.prototype.init = function() {
		var _this = this.$obj;
		_this.languageData = $.extend(true, {}, numeral.languageData());
		_this.formats = {};
		_this.formats['def'] = PYT_DATA.dataFormats;
		_this.formats['def']['lang'] = $.extend(true, {}, _this.languageData);
		_this.tableViewId = false;
	}
	FormatModel.prototype.setFormatTable = function(tbl) {
		this.$obj.tableViewId = tbl;
	}
	FormatModel.prototype.getFormatData = function(tbl) {
		var _this = this.$obj;
		if (!tbl) return _this.formats['def'];
		if (!_this.formats[tbl]) {
			_this.formats[tbl] = {};
			_this.formats[tbl]['lang'] = $.extend(true, {}, _this.languageData);
		}
		return _this.formats[tbl];
	}
	FormatModel.prototype.setDelims = function(dec, thn, tbl) {
		var _this = this.$obj,
			formats = _this.getFormatData(tbl);
		formats['lang'].delimiters = {decimal: dec, thousands: thn};
	}
	FormatModel.prototype.setFormat = function(typ, format, tbl) {
		var _this = this.$obj;
		if (!_this.formats['def'].hasOwnProperty(typ)) return;
		var formats = _this.getFormatData(tbl);
		formats[typ] = format;
		if (typ == 'percent') formats['convert'] = format;
	}
	FormatModel.prototype.getFormat = function(typ) {
		var _this = this.$obj,
			tbl = _this.tableViewId,
			format = tbl && _this.formats[tbl] ? _this.formats[tbl][typ] : false;
		return format && format.length ? format : (typ in _this.formats['def'] ? _this.formats['def'][typ] : '');
	}
	FormatModel.prototype.getLangData = function(tbl) {
		return tbl && this.$obj.formats[tbl] ? this.$obj.formats[tbl]['lang'] : this.$obj.formats['def']['lang'];
	}
	FormatModel.prototype.formatValue = function (value, typ, format) {
		if (typ == 'select') typ = '';
		if (typeof(value) == 'undefined' || value == null) value = '';
		if (value !== '') {
			var _this = this.$obj,
				func = typ +'FormatValue';
			if (_this[func]) value = _this[func](value, !format || format == '' ? _this.getFormat(typ) : format);
		}
		return value;
	}
	FormatModel.prototype.dateFormatValue = function (value, format) {
		if (value == '0') value = '';
		var d = new Date(parseFloat(value) == value ? Date.UTC(0,0,value-1) : value);
		if (d instanceof Date && !isNaN(d))	value = $.datepicker.formatDate(format, d);
		else value = '';
		return value;
	}
	FormatModel.prototype.timeFormatValue = function (value, format) {
		if (value == '0') value = '';
		if (value.length) {
			var d = new Date(0);
			try {
				d.setSeconds(value);
				value = d.toISOString().substr(11, 8);
			} catch(e) {
				/*d.setSeconds(0);
				value = d.toISOString().substr(11, 8);*/
				value = '--';
			}
		}
		return value;
	}
	FormatModel.prototype.numberFormatValue = function (value, format) {
		return this.isNumber(value) ? this.formatNumbers(value, format) : 0;
	}
	FormatModel.prototype.moneyFormatValue = function (value, format) {
		format += "";
		var _this = this.$obj,
			withoutSymbol = format.match(/\d.?\d*.?\d*/)[0],
			symbol = format.replace(withoutSymbol, '') || '$',	// We need to set currency symbol in any case for normal work of numeraljs
			lang = _this.getLangData(_this.tableViewId);
		lang.currency.symbol = symbol;
		numeral.language('en', lang);
		return numeral(value).format(symbol == '$' ? format : format.replace(symbol, '$'));
	}
	FormatModel.prototype.percentFormatValue = function (value, format) {
		return this.isNumber(value) ? this.formatNumbers(value, format) : '0%';
	}
	FormatModel.prototype.convertFormatValue = function (value, format) {
		return this.isNumber(value) ? this.formatNumbers(value / 100, format) : '0%';
	}
	FormatModel.prototype.isNumber = function (value) {
		if (value && !isNaN(value)) {
			if (value.toString().match(/^-{0,1}\d+\.{0,1}\d*$/)) return true;
		}
		return false;
	}
	FormatModel.prototype.formatNumbers = function (value, format) {
		format += "";
		numeral.language('en', this.$obj.getLangData(this.$obj.tableViewId));
		return numeral(value).format(format);
	}

// ********************* FORMAT MODEL ***************************** //
// ================================================================ //

// ================================================================ //
// *********************** FRONT PAGE ***************************** //


	function TablesFront() {
		this.dtInstances = {};
		this.dtSettings = {};
		this.extraConfig = [];
		this.$obj = this;
		this.initFormulas();
		return this.$obj;
	}

	TablesFront.prototype.setTableInstance = (function(instance) {
		this.$obj.dtInstances[instance.viewId] = instance;
	});
	TablesFront.prototype.getTableInstance = (function(id) {
		return id in this.$obj.dtInstances ? this.$obj.dtInstances[id] : false;
	});
	TablesFront.prototype.setTableSettings = (function(id, settings) {
		this.$obj.dtSettings[id] = settings;
	});
	TablesFront.prototype.getTableSettings = (function(id) {
		return id in this.$obj.dtSettings ? this.$obj.dtSettings[id] : false;
	});
	TablesFront.prototype.destroyTable = (function(id) {
		var _this = this.$obj,
			iTable = _this.getTableInstance(id);
		if (iTable) {
			var iSettings = _this.getTableSettings(id);
			iSettings.wrapper.addClass('pyt-hidden');
			iTable.destroy();
			iTable = false;
			iSettings = false;
		}
	});
	TablesFront.prototype.refreshTable = (function(id) {
		var _this = this.$obj,
			iTable = _this.getTableInstance(id);
		if (iTable) {
			pytApplyHookAction(_this, 'resetProFeatures', id);
			iTable.columns.adjust().draw(false);
		}
	});
	TablesFront.prototype.moveTableCss = (function() {
		$('style.pyt-styles-front').appendTo('head');
	});
	TablesFront.prototype.addTableCss = (function(viewId, css) {
		var $elem = $('#pyt-table-' + viewId + '-css');
		if ($elem.length && css.length) {
			$elem.html($elem.html() + css);
		}
	});
	TablesFront.prototype.runCustomJs = (function () {
		var _this = this.$obj;
		$ ('.pyt-table-wrap').each(function () {
			var wrapper = $(this),
				jsCodeStr = wrapper.data('custom-js');
			if (jsCodeStr.length > 0){
				try {
					eval(jsCodeStr);
				} catch(e) {
					console.log(e);
				}

			}
		});
	});

	TablesFront.prototype.init = (function(id) {
		var _this = this.$obj;
		_this.formatModel = app.pytFormats;
		_this.ajaxurl = typeof(ajaxurl) == 'undefined' || typeof(ajaxurl) !== 'string' ? PYT_DATA.ajaxurl : ajaxurl;
		_this.moveTableCss();
		_this.mobileMode = $(window).width() <= 991;
		_this.runCustomJs();

		// events for custom javascript hook, example: document.addEventListener('pytCustomBeforeInit', function(event) {console.log('Custom js');});
		_this.customBeforeInitEvent = document.createEvent('Event');
		_this.customBeforeInitEvent.initEvent('pytCustomBeforeInit', false, true); 
		_this.customAfterInitEvent = document.createEvent('Event');
		_this.customAfterInitEvent.initEvent('pytCustomAfterInit', false, true); 
		//document.dispatchEvent(customEvent);
		
		pytApplyHookAction(_this, 'beforeInitTables');
		

		_this.initTablesOnPage(id);
	});

	TablesFront.prototype.setTableWidthMode = (function(id, isMobile) {
		var _this = this.$obj;
		$(typeof(id) == 'undefined' ? '.pyt-table-wrap' : '#pyt-table-' + id + '-wrapper').each(function () {
			isMobile = (typeof(isMobile) == 'undefined' ? _this.mobileMode : isMobile);
			var ssDiv = $(this),
				widthAttr = ssDiv.data('table-width-' + (isMobile ? 'mobile' : 'fixed'));
			if(typeof(widthAttr) != 'undefined') {
				ssDiv.css('display', (widthAttr == 'auto' ? 'inline-block' : '')).css('width', widthAttr);
			}
		});
	});

	TablesFront.prototype.initTablesOnPage = (function(id) {
		var _this = this.$obj,
			tables = $(typeof id != 'undefined' ? '#pyt-table-' + id + ':not(.dataTable)' : '.pyt-table');
		if (tables.length == 0) return;
		_this.setTableWidthMode(id);

		document.dispatchEvent(_this.customBeforeInitEvent);

		tables.each(function () {
			_this.initializeTable(this, _this.showTable, function($table) {
				// This is used when table is hidden in tabs and can't calculate itself width to adjust on small screens
				if ($table.is(':visible')) {
					// Fix bug in FF and IE which not supporting max-width 100% for images in td
					_this.calculateImages($table);
				} else {
					$table.data('isVisible', setInterval(function(){
						if ($table.is(':visible')) {
							clearInterval($table.data('isVisible'));
							_this.calculateImages($table);
						}
					}, 250));
				}
				setTimeout(function(){
					$table.trigger('responsive-resize.dt');
					_this.getTableInstance($table.data('view-id')).columns.adjust();
				}, 350);
			});
		});
		document.dispatchEvent(_this.customAfterInitEvent);
	});

	TablesFront.prototype.initializeTable = (function(table, callback, finalCallback, reinit) {
		reinit = typeof reinit != 'undefined' ? reinit : {};

		var _this = this.$obj,
			format = _this.formatModel,
			$table = (table instanceof $ ? table : $(table)),
			features = $table.data('features'),
			viewId = $table.data('view-id'),
			config = {columns: [], dom: 'lfrtip', aoColumnDefs: []},
			tSettings = {
				selector: '#' + $table.attr('id'),
				tableId: $table.data('id'),
				viewId: viewId,
				isPreview: viewId == 'preview',
				tableType: $table.data('type'),
				ssp: $table.data('server-side-processing') == '1',
				sspProsessing: false,
				responsiveMode: $table.data('responsive-mode'),
				wrapper: $table.closest('.pyt-table-wrap'),
				head: $table.data('head') == '1',
				foot: $table.data('foot') == '1',
				fixedHead: $table.data('fixed-head'),
				fixedFoot: $table.data('fixed-foot'),
				fixedLeft: parseInt($table.data('fixed-left')),
				fixedRight: parseInt($table.data('fixed-right')),
				autoIndex: $table.data('auto-index'),
			},
			formats = $table.data('formats'),
			tableInstance = {},
			defaultFeatures = {
				autoWidth:  false,
				info:       false,
				ordering:   false,
				paging:     false,
				responsive: false,
				searching:  false,
				stateSave:  false,
				api: 		true,
				retrieve:   true,
				processing: true,
				initComplete: callback,
				/*headerCallback: function( thead, data, start, end, display ) {
					$(thead).closest('thead').find('th').each(function() {
						self.setStylesToCell(this);
					});
				},
				footerCallback: function( tfoot, data, start, end, display ) {
					$(tfoot).closest('tfoot').find('th').each(function() {
						self.setStylesToCell(this);
					});
				},*/
				// order param disable the default table sorting.
				// it should be here because of Woocommerce addon:
				// it has no hidden header for tables without header
				// and in triggers an error during initializing.
				// order param should be disabled later during sorting activation
				order: [],
			};
		if (formats && typeof(formats) == 'object') {
			if (formats.dec && formats.thn) format.setDelims(formats.dec, formats.thn, viewId);
			for (var typ in formats) {
				if (typ != 'dec' && typ != 'thn') format.setFormat(typ, formats[typ], viewId);
			}
		}
		
		tSettings.fixedCols = tSettings.fixedLeft || tSettings.fixedRight;
		var i = 0,
			fixedCols = true,
			wrapId = '#' + tSettings.wrapper.attr('id'),
			sortTypes = {'num-fmt': [], 'date': []};

		tSettings.colProps = [];
		tSettings.colNames = [];

		$table.find('thead th[data-col]').each(function() {
			var $this = $(this),
				widthStyles = '',
				colName = $this.attr('data-col')
			config.columns.push({
				data: colName,
				searchable: true,
				visible: $this.attr('data-visible') == '1',
			});
			tSettings.colNames.push(colName);

			var colData = $this.data(),
				props = {
					name: colName,
					typ: $this.attr('data-type') || '',
					format: $this.data('format') || ''
				};
			if (colData) {
				for (var key in colData) props[key] = colData[key];
			}
			var t = props.typ;
			if (t == 'date') sortTypes['date'].push(i);
			else if (t == 'number' || t == 'money' || t == 'percent' || t == 'convert') sortTypes['num-fmt'].push(i);

			tSettings.colProps.push(props);
			$this.attr('data-col-id', i);
			i++;
			if ($this.attr('data-width')) {
				var width = $this.attr('data-width');
				widthStyles = wrapId + ' table:not(.oneColumn):not(.childTable)>tbody>tr:not(.child)>td:nth-child(' + i + ') {max-width:' + width + ' !important;width:' + width + ' !important;min-width:' + width + ' !important;}'+ wrapId + ' table:not(.oneColumn):not(.childTable) tr:not(.child) th:nth-child(' + i + ') {max-width:' + width + ' !important;}';
				_this.addTableCss(viewId, widthStyles);
			} else fixedCols = false;

			
		});

		if (fixedCols) {
			_this.addTableCss(viewId, wrapId + ' table {table-layout:fixed !important;}');
		}
		
		// Set features
		$.each(features, function () {
			config[this] = true;
		});
		tSettings.searchSettings = toeInArrayPyt('searching', features) ? $table.data('searching-settings') : false;
		tSettings.sortSettings = toeInArrayPyt('ordering', features) ? $table.data('sorting-settings') : false,
		tSettings.pagingSettings = toeInArrayPyt('paging', features) ? $table.data('paging-settings') : false;

		if (tSettings.sortSettings) {
			for(var typ in sortTypes) {
				if (sortTypes[typ].length > 0) {
					config.aoColumnDefs.push({ type: typ, targets: sortTypes[typ] });
				}
			}
			config.aoColumnDefs.push({ type: 'natural-nohtml-ci', targets: '_all' });
		}

		tSettings.searchСolumns = false;
		if (tSettings.searchSettings) {
			var sMinChars = pytCheckSettings(tSettings.searchSettings, 'min_chars', 0),
				sResultOnly = pytCheckSettings(tSettings.searchSettings, 'result_only') == '1',
				sStrict = pytCheckSettings(tSettings.searchSettings, 'strict') == '1',
				sShowTable = pytCheckSettings(tSettings.searchSettings, 'show_table') == '1',
				sСolumns = pytCheckSettings(tSettings.searchSettings, 'columns') == '1';
			tSettings.searchСolumns = sСolumns;
			if (sMinChars > 0 || sResultOnly || sStrict) {
				var addSearchFunction = function(settings, data) {
					var $searchInput = $(settings.nTableWrapper).find('.dataTables_filter input'),
						searchValue = $searchInput.val(),
						viewId = $(settings.nTable).data('view-id'),
						tSettings = _this.getTableSettings(viewId);
					if (tSettings.searchSettings) {
						var sResultOnly = pytCheckSettings(tSettings.searchSettings, 'result_only') == '1',
							sStrict = pytCheckSettings(tSettings.searchSettings, 'strict') == '1';

						if (sResultOnly && searchValue.length === 0) {
							return false;
						}
						if (sStrict) {
							searchValue = $.fn.dataTable.util.escapeRegex(searchValue);
							var regExp = new RegExp('^' + searchValue, 'i');

							for (var i = 0; i < data.length; i++) {
								var words = data[i].replace(/\s\s+/g, ' ').split(' ');

								for (var j = 0; j < words.length; j++) {
									if (words[j].match(regExp)) return true;
								}
							}
							return false;
						} else {
							return data.join(' ').toLowerCase().indexOf(searchValue.toLowerCase()) !== -1
						}
					}
				};
				var functionIndex = $.fn.dataTable.ext.search.indexOf(addSearchFunction);
				if (functionIndex == -1) $.fn.dataTable.ext.search.push(addSearchFunction);

				$table.on('init.dt', function (event, settings)  {
					if (!settings) return;

					var $tableWrapper = $(settings.nTableWrapper),
						$tableSearchInput = $tableWrapper.find('.dataTables_filter input'),
						$customInput = $tableSearchInput.clone(),
						$tableScroll = $table.closest('.dataTables_scroll'),
						$tableForHide = $tableScroll.length ? $tableScroll : $table;

					$tableSearchInput.replaceWith($customInput);

					$customInput.on('input change', function() {
						if (!sShowTable) {
							if (sResultOnly && sMinChars && (this.value.length < sMinChars || !this.value.length)) {
								$tableForHide.hide();
								$tableForHide.parent().find('.dataTables_paginate').hide();
							} else {
								$tableForHide.show();
								$tableForHide.parent().find('.dataTables_paginate').show();
							}
						}
						if (sMinChars && (this.value.length < sMinChars && this.value.length !== 0)) {
							event.preventDefault();
							return false;
						}
						_this.getTableInstance($table.data('view-id')).columns.adjust().draw(false);
					});
					if (sResultOnly && !sShowTable) {
						$tableForHide.hide();
						$tableForHide.parent().find('.dataTables_paginate').hide();
					}
				});
			}
			if (sСolumns) {
				var inputTop = pytCheckSettings(tSettings.searchSettings, 'fields_position') == 'top',
					tPosition = inputTop ? 'thead' : 'tfoot';
				if (!$table.find('.pyt-col-search-wrap').length) {
					var headerRow = $table.find('thead tr:first').find('th');
					if (headerRow.length) {
						var searchRow = '<tr class="pyt-col-search-wrap">',
							func = inputTop ? 'prepend' : 'append';
						for (var i = 0; i < headerRow.length; i++) {

							var cellItem = $(headerRow[i]),
								cellClass = '',
								cellStyle = '',
								cellAttr = '';
							if (cellItem.hasClass('invisibleCell')){
								cellClass = ' class="invisibleCell"';
							}
							if (cellItem.attr('data-width')){
								cellStyle = ' style="width:'+cellItem.attr('data-width')+'"';
							}
							searchRow += '<th ' + cellClass + cellStyle + cellAttr + '>';
							if (tSettings.colProps[i]['search'] == 1) searchRow += '<input class="search-column" type="text" data-column-num="'+i+'"/>';
							searchRow += '</th>';
						}
						searchRow += '</tr>';
						if ($table.find(tPosition).length == 0) {
							$table.append($('<' + tPosition + '>'));
						}
						$table.find(tPosition)[func](searchRow);
					}
				}
				if (tSettings.autoIndex.length > 0){
					$('.pyt-col-search-wrap th:first-child input').css({'visibility':'hidden'});
				}
			}
			var searchDisable = [];
			for (var i = 0; i < tSettings.colProps.length; i++) {
				if (tSettings.colProps[i]['search'] != 1) searchDisable.push(i);
			}
			if (searchDisable.length) config.aoColumnDefs.push({ "searchable": false, "targets": searchDisable });

		}
		if (tSettings.sortSettings) {
			var sortingEnable = ['_all'],
				sortingDisable = 'nosort',
				aaSorting = [],
				multipleSorting = pytCheckSettings(tSettings.sortSettings, 'multi');

			if(!$table.data('head')) {
				sortingDisable = ['_all'];
			}

			if(multipleSorting && multipleSorting.length) {
				aaSorting = multipleSorting;
			} else {
				var columnsCount = $table.find('tr:first th').length,
					sortColumn = pytCheckSettings(tSettings.sortSettings, 'column', 0),
					sortOrder = pytCheckSettings(tSettings.sortSettings, 'order', 'asc'),
					columnNumber = sortColumn - 1;

				if(columnNumber >= 0 && columnNumber < columnsCount) {
					aaSorting.push([columnNumber, sortOrder]);
				}
			}
			config.aoColumnDefs.push({ "sortable": false, "targets": sortingDisable });
			config.aoColumnDefs.push({ "sortable": true, "targets": sortingEnable });
			config.aaSorting = aaSorting;
			delete defaultFeatures.order;
		}

		if (tSettings.pagingSettings) {
			if (pytCheckSettings(tSettings.pagingSettings, 'dropdown') == '1') {
				var pageMenu = pytCheckSettings(tSettings.pagingSettings, 'menu');
				if (pageMenu.length) {
					config.aLengthMenu = [];
					config.aLengthMenu.push(pageMenu.replace('All', -1).split(',').map(Number));
					config.aLengthMenu.push(pageMenu.split(','));
				}
				config.bLengthChange = true;
			} else {
				config.bLengthChange = false;
				config.pageLength = pytCheckSettings(tSettings.pagingSettings, 'rows', 10);
			}
		}
		
		// Set responsive mode
		if (tSettings.responsiveMode == 0) {
			// Responsive Mode: Standart Responsive Mode
			var labelStyles = '',
				id = '#' + $table.attr('id');

			// Add header data to each response row
			$table.find('thead th').each(function(index, el) {
				labelStyles += id + '.oneColumnWithLabels td:nth-of-type(' + (index + 1) + '):before { content: "' + $(this).text() + '"; }';
			});
			_this.addTableCss(viewId, labelStyles);

			$(window).on('load resize orientationchange', $table, function(event) {
				event.preventDefault();
				clearTimeout($table.data('resizeTimer'));

				$table.data('resizeTimer', setTimeout(function() {
					$table.removeClass('oneColumn oneColumnWithLabels');
					$table.css('width', '100%');
					var tableWidth = $table.width(),
						wrapperWidth = $table.closest('.pyt-table-wrap').width();
					if (tableWidth > wrapperWidth || $(window).width() <= 475) {
						$table.addClass('oneColumn');
						if ($table.data('head') == '1') {
							$table.addClass('oneColumnWithLabels');
						}
					}
				}, 150));
				if(tSettings.ssp) {
					$table.find('td').each(function() {
						$(this).css({'width': '','min-width': ''});
					});
				}
			});
		} else if (tSettings.responsiveMode === 1) {
			// Responsive Mode: Automatic Column Hiding
			config.responsive = {
				breakpoints: [
					{ name: 'desktop', width: Infinity },
					{ name: 'tablet',  width: 768 },
					{ name: 'phone',   width: 480 },
					{ name: 'mobile',   width: 480 }
				],
				details: {
					renderer: function (api, rowIdx, columns) {
						var $table = $(api.table().node()),
							$subTable = $('<table/>').addClass('childTable');
						$.each(columns, function (i, col) {
							if (col.hidden) {
								var $cell = $(api.cell(col.rowIndex, col.columnIndex).node()).clone(),
									markup = '<tr data-dt-row="'+col.rowIndex+'" data-dt-column="'+col.columnIndex+'" data-id="' + $(api.row(col.rowIndex).node()).attr('data-id') + '">';
								if ($table.data('head') == '1') {
									var tableHeadTr = $(api.table().header()).find('tr:not(.pyt-col-search-wrap)').eq(0),
										$headerContent = tableHeadTr.find('th').eq(col.columnIndex).html();
									markup += '<td>';
									if ($headerContent) {
										markup += $headerContent;
									}
									markup += '</td>';
								}
								markup += '</tr>';
								$cell.after(
									$('<td>')
										.addClass('collapsed-cell-holder')
										.attr('data-cell-row', col.rowIndex)
										.attr('data-cell-column', col.columnIndex)
										.hide()
								);
								$subTable.append($(markup).append($cell.addClass('collapsed').show()));
							}
						});
						return $subTable.is(':empty') ? false : $subTable;
					}
				}
			};
			$table.on('responsive-resize.dt', function(event, api, columns) {
				if(typeof api == 'undefined' || typeof columns == 'undefined') {
					api = _this.getTableInstance($(this).data('view-id'));
					columns = api.columns;
				}
				var autoHiding = [],
					searchColumn = $table.find('.pyt-col-search-wrap input.search-column');
				for (var i = 0, len = columns.length; i < len; i++) {
					autoHiding[i] = columns[i] ? 1 : 0;
				}
				$table.find('th input.search-column').each(function() {
					var th = $(this).parents('th:first'),
						i = th.index();
					if(columns.length > i) {
						th.css('display', columns[i] ? '' : 'none');
					}
				});
				$table.attr('data-auto-hiding', autoHiding.join());
				if ($table.width() > $table.parent().width()) {
					$table.css('width', '100%');
					$table.css('max-width', '100%');
					api.responsive.recalc();
					return;
				}
				for (var i = 0, len = columns.length; i < len; i++) {
					if (columns[i]) {
						$table.find('tr > td.collapsed-cell-holder[data-cell-column="' + i + '"]').each(function(index, el) {
							var $this = $(this),
								$cell = $(api.cell(
								$this.data('cell-row'),
								$this.data('cell-column')
							).node());
							if ($cell.hasClass('collapsed')) {
								$cell.removeClass('collapsed');
								$this.replaceWith($cell);
							}
						});
					}
				}
				if ($table.data('merged')) {
					// if has merged cells remove them, with autohidding they not working
					$table.find('td[data-hide]').show();
					$table.find('td[data-rowspan]').attr({'data-rowspan':1,rowspan:1,'data-colspan':1,colspan:1});
				}
			});
		} else if (tSettings.responsiveMode === 2) {
			// Responsive Mode: Horizontal Scroll
			config.scrollX = true;
			config.bAutoWidth = false;
			var firstRow = $table.find('tbody tr:first-child td');
			if(firstRow.length) {
				var cntCols = firstRow.length;
				$table.find('thead tr:first-child th').each(function(i, th){
					if(cntCols > i && $(th).css('width')) {
						firstRow.eq(i).css('width', $(th).css('width'));
					}
				});
			}
		}
		if(tSettings.responsiveMode === 2 || tSettings.responsiveMode === 3) {
			// Responsive Mode: 2 - Horizontal Scroll, 3 - Disable Responsivity
			if(tSettings.fixedHead || tSettings.fixedFoot) {
				config.scrollY = $table.data('fixed-height');
				config.scrollCollapse = true;
			}
			if (tSettings.fixedCols) {
				config.fixedColumns = {
					leftColumns: tSettings.fixedLeft,
					rightColumns: tSettings.fixedRight
				};
				config.scrollX = true;
			}
		}

		$table.find('.pyt-invisible').closest('tr').addClass('pyt-invisible');

		// Add translation
		config.language = $table.data('translations') || {};

		var ajaxSource = {};

		if (tSettings.ssp) {
			tSettings.styleCellsId = 'pyt-table-' + viewId + '-cells';
			pytCreateStyleElem(tSettings.styleCellsId);
			var footerIds = [];
			$table.find('tfoot tr[data-id]').each(function(){
				footerIds.push($(this).data('id'));
			});
			var rowAttrs = [];
			ajaxSource = {
				serverSide: true,
				ajax: {
					url: _this.ajaxurl,
					type: 'POST',
					data: {
						pl: 'pyt',
						action: 'getFrontPage',
						mod: 'tables',
						reqType: 'ajax',
						tableId: $table.data('id'),
						viewId: $table.data('view-id'),
						footerIds: footerIds,
						colNames: tSettings.colNames,
						scAttributes: $table.data('sc-attributes'),
					},
					beforeSend: function() {
						_this.getTableSettings(viewId).sspProsessing = true;
					},
					dataSrc: function (json) {
						rowAttrs = json.attrs;
						_this.getTableSettings(viewId).sspProsessing = false;
						$('#' + tSettings.styleCellsId).html(json.css);
						_this.applyTableEventClb(_this.afterPageLoad, 0, tSettings, json.fonts);
						return json.data;
					}
				},
				createdRow: function (row, data, dataIndex) {
					var a = rowAttrs[dataIndex];
					if (a) {
						$(row).attr('data-id', a.id).attr('data-row-index', a.i);
						if (a.f != 1) $(row).attr('data-not-format', '1');
					}
				}
			};
			if(typeof(config.aoColumnDefs) == 'undefined') {
				config.aoColumnDefs = [];
			}
			config.aoColumnDefs.push({
				targets: '_all',
				cellType: 'td',
				createdCell: function (td, cellData, rowData, row, col) {
					var key = config.columns[col].data,
						$td = $(td);
					if (rowAttrs[row]['c'] && rowAttrs[row]['c'][key]) {
						$td.attr('class', rowAttrs[row]['c'][key]);
					}
					if (rowAttrs[row]['a'] && rowAttrs[row]['a'][key]) {
						var attrs = rowAttrs[row]['a'][key];
						for(var k in attrs) $td.attr('data-' + k, attrs[k]);
					}
					if (rowAttrs[row]['t'] && rowAttrs[row]['t'][key]) {
						var attrs = rowAttrs[row]['t'][key];
						for(var k in attrs) $td.attr(k, attrs[k]);
					}
				}
			});
		}
		
		_this.setTableSettings(viewId, tSettings);
		pytApplyHookAction(_this, 'beforeInitTable', $table);

		config = pytApplyHookFilter(_this, 'addedConfigFeatures', config, $table);
					
		tableInstance = $table.DataTable($.extend({}, defaultFeatures, config, _this.extraConfig, ajaxSource, reinit));
		tableInstance.id = $table.data('id');
		tableInstance.viewId = viewId;

		_this.setTableInstance(tableInstance);

		_this.setColumnSearch(tSettings);

		if (!tSettings.isPreview && !tSettings.ssp) {
			_this.formatCells(tSettings);
			_this.updateAutoIndex(tSettings);
			tSettings.mergedCells = _this.setMergedCells(tSettings);
		}

		$table.on('draw.dt', function() {
			if (tSettings.ssp) {
				$table.find('.pyt-invisible').closest('tr').addClass('pyt-invisible');
			}
			_this.updateAutoIndex(tSettings);
			_this.formatCells(tSettings);
			pytApplyHookAction(_this, 'afterRedrawTable', $table);

			if (tSettings.mergedCells) {
				_this.resetMergedCells(tSettings);
			}
			if(tSettings.ssp || tSettings.mergedCells) {
				_this.setMergedCells(tSettings);
			}

			setTimeout(function() {tableInstance.columns.adjust();}, 350);
		});
		
		if (tSettings.responsiveMode == 1) {
			$table.on('responsive-resize.dt', function(event, api, columns) {
				if(!tSettings.ssp && $table.data('merged')) {
					tableInstance.fnResetFakeRowspan();
				}
			});
		}
		if (tSettings.fixedColumns) {
			tableInstance.fixedColumns().update();
		}
		
		pytApplyHookAction(_this, 'afterInitTable', $table);
		return typeof finalCallback  == 'function' ? finalCallback($table) : tableInstance;
	});

	/** Callback for displaying table after initializing
	 * @param {object} settings - DataTables settings object
	 * @param {object} json - JSON data retrieved from the server if the ajax option was set. Otherwise undefined.
	 */
	TablesFront.prototype.showTable = (function(settings, json) {
		var _this = app.pytTables,	// it is callback so "this" does not equal vendor[appName] object
			$table = this,
			viewId = $table.data('view-id'),
			$tableWrap = $table.closest('.pyt-table-wrap'),
			tSettings = _this.getTableSettings(viewId);

		// Page change callback
		$table.on('page.dt', function() {
			if(tSettings.ssp) {
				tSettings.sspProsessing = true;
			}
			var table = $(this);
			_this.applyTableEventClb(_this.pageEvent, 50, tSettings);
			if(pytCheckSettings(tSettings.pagingSettings, 'scroll', 0) == 1) {
				$('html, body').animate({
					scrollTop: table.closest('.dataTables_wrapper').offset().top
				}, 100);
			}
		});

		// Show table
		$tableWrap.prev('.pubydoc-table-loader').hide();
		$tableWrap.css('visibility', 'visible');

		if(tSettings.responsiveMode === 2 || tSettings.fixedHeader || tSettings.fixedFooter || tSettings.fixedCols) {
			// Responsive Mode: Horizontal Scroll
			$(window).on('load resize orientationchange', $table, function(event) {
				var tBody = $tableWrap.find('.dataTables_scrollBody'),
					tBodyTable = tBody.find('.pyt-table');

				if(tBody.width() > tBodyTable.width() || $tableWrap.width() > tBodyTable.width()) {
					tBody.width(tBodyTable.width());
					$tableWrap.find('.dataTables_scrollHead, .dataTables_scrollFoot, .dataTables_scrollBody').width(tBodyTable.width() + 1);
				}
				if(tBody.isHorizontallyScrollable()){
					tBody.css({'border-bottom' : 'none'});
				}else{
					tBody.removeStyle('border-bottom');
				}
				var table = _this.getTableInstance($table.data('view-id'));
				setTimeout(function(){
					table.columns.adjust();
				}, 350);
			});

			// need resize twice to get better frontend view
			var tBody = $tableWrap.find('.dataTables_scrollBody'),
				tBodyTable = tBody.find('.pyt-table');

			if(tBodyTable.is(":visible")){
				setTimeout(function() {
					$(window).trigger('load');
				}, 200);
			}
			var $tHeadTable = $tableWrap.find('.dataTables_scrollHead .pyt-table');
		}

		// Correct width of fixed columns
		if(tSettings.fixedColumns) {
			$table.api().fixedColumns().relayout();
		}
	});

	TablesFront.prototype.getCellColumn = (function($td) {
		var col = $td.closest('tr').attr('data-dt-column');
		return $td.closest('table').find('thead tr:not(.pyt-col-search-wrap):first th').eq(col ? col : $td.index()).attr('data-col-id');
	});

	TablesFront.prototype.formatCells = (function(tSettings) {
		var _this = this.$obj,
			$table = $(tSettings.selector),
			formatModel = _this.formatModel,
			columns = tSettings.colProps,
			tableInstance = _this.getTableInstance(tSettings.viewId),
			first = true,
			editedCells = _this.Formulas.computeAll(tableInstance, tSettings);

		formatModel.setFormatTable(tSettings.viewId);
		$table.find('tbody tr[data-not-format=1] td, tbody td[data-not-format=1]').each(function() {
			var $td = $(this),
				value = $td.html(),
				typ = $td.attr('data-type'),
				format = $td.attr('data-format');
			$td.attr('data-value', value);
			if (!typ) {
				var col = _this.getCellColumn($td);
				if (col) {
					typ = columns[col].typ;
					format = columns[col].format;
				}
			}
			if (typ) {
				var formated = formatModel.formatValue(value, typ, format);
				$td.html(formated);
			}
			pytApplyHookFilter(_this, 'formatCellPro', $td, tSettings, first);
			first = false;
			if ($td.hasClass('collapsed')) {
				$(tableInstance.cell($td).node()).attr('data-value', value).html(formated).attr('class', $td.attr('class').replace('collapsed',''));
			}
		});
		$table.find('tbody [data-not-format=1]').removeAttr('data-not-format');
		return editedCells;
	});

	TablesFront.prototype.updateAutoIndex = (function(tSettings) {
		if (tSettings.autoIndex.length > 0) {
			var dt = this.$obj.getTableInstance(tSettings.viewId),
				index = tSettings.ssp ? dt.page.info().start + 1 : 1;
			dt.column(0, {order: 'current', page: 'current', search: 'applied'}).nodes().each(function (cell, i) {
				dt.cell(cell).data(index);
				index++;
			});
		}
	});

	TablesFront.prototype.redrawMergedCells = (function(tSettings) {
		if (tSettings.mergedCells) {
			var _this = this.$obj;
			_this.resetMergedCells(tSettings);
			_this.setMergedCells(tSettings);
		}
	});

	TablesFront.prototype.resetMergedCells = (function(tSettings) {
		var $table = $(tSettings.selector);
		$table.find('.pyt-merged').each(function() {
			var cell = $(this);
			cell.html(cell.attr('data-ovalue')).removeClass('pyt-merged');
		});
		$table.find('td[data-merged="1"]').attr({'rowspan': 1, 'colspan': 1});
	});

	TablesFront.prototype.setMergedCells = (function(tSettings) {
		var mergedCells = false,
			$table = $(tSettings.selector);

		$table.find('tbody td[data-colspan], tbody td[data-rowspan]').each(function() {
			// Fix for searching by merged cells
			var cell = $(this),
				cellData = cell.html();
			mergedCells = true;

			// prevent of copy cell data if it contains tags with id attribute - it must be unique on page
			if (!cellData.toString().match(/<.*?id=['|"].*?['|"].*?>/g)) {
				var colIndex = cell.index(),
					rowIndex = cell.closest('tr').index(),
					colspan = parseInt(cell.attr('data-colspan')) || 1,
					rowspan = parseInt(cell.attr('data-rowspan')) || 1,
					setRowspan = 0,
					setColspan = 0;

				for (var i = rowIndex + 1; i <= rowIndex + rowspan; i++) {
					var hiddenRow = $table.find('tbody tr:nth-child(' + i + ')');
					if (hiddenRow.hasClass('pyt-invisible') || hiddenRow.hasClass('pyt-collapsed')) continue;

					setRowspan++;
					for (var j = colIndex + 1; j <= colIndex + colspan; j++) {
						if (setRowspan == 1) {
							setColspan++;
							if (setColspan == 1) continue;
						}
						var hiddenCell = hiddenRow.find('td:nth-child(' + j + ')');

						if (hiddenCell) {
							if (!hiddenCell.hasClass('pyt-merged')) {
								hiddenCell.attr('data-ovalue', hiddenCell.html());
								hiddenCell.addClass('pyt-merged').html(cellData);
							}
						}

					}
				}
				if (setRowspan > 1)	cell.attr('rowspan', setRowspan);
				if (setColspan > 1)	cell.attr('colspan', setColspan);
				cell.attr('data-merged', 1);
			}
		});
		return mergedCells;
	});

	TablesFront.prototype.applyTableEventClb = (function(clb, timeout, tSettings, params) {
		// Callback for applying events' actions and other functions to tables with server side processing (SSP)
		timeout = timeout ? timeout : 0;
		var _this = this.$obj;

		if (tSettings.sspProsessing) {
			setTimeout(function() {
				_this.applyTableEventClb(clb, timeout, tSettings);
			}, 50);
		} else {
			if (typeof clb == 'function') {
				var arg = Array.from(arguments).slice(2);
				setTimeout(function() {
					clb.apply(_this, arg);
				}, timeout);
			}
		}
	});

	TablesFront.prototype.pageEvent = (function(tSettings) {
		var _this = this.$obj,
			table = $(tSettings.selector),
			tableWrapper = table.parents('.supsystic-tables-wrap:first');
		//_this.setImgLightbox(tSettings);
		/*setTimeout(function() {
			table.api().columns.adjust().draw(false);
		}, 250);*/

		//this.getRuleJSInstance(table).init();
		//this.formatDataAtTable(table, true);
		//this.fixHeaderOfHiddenColumns(table);
		/*if ('ontouchstart' in window || navigator.msMaxTouchPoints) {
			tableWrapper.find('td, th').on('click', this.applyMobileTableComments);
		}
		this.initShortcodesInTable(table);*/
	});

	TablesFront.prototype.setColumnSearch = (function(tSettings) {
		if (!tSettings.searchСolumns) return;
		var _this = this.$obj,
			$table = $(tSettings.selector),
			inputs = $table.parents('.dataTables_wrapper:first').find('.pyt-col-search-wrap .search-column');
		if (inputs.length == 0) return;
		inputs.off('keyup.dtg change.dtg').on('keyup.dtg change.dtg',function () {
			var input = $(this),
				position = input.parents('th:first').index(),
				value = this.value,
				column = _this.getTableInstance(tSettings.viewId).column(position + ':visIdx');
			if (column.search() !== value) {
				column.search(value.replace(/;/g, "|"), true, false).draw();
				setTimeout(function() {
					column.draw();
				}, 50);
			}
		});
	});

	TablesFront.prototype.getOriginalImageSizes = (function(img) {
		var tempImage = new Image(),
			width,
			height;
		if ('naturalWidth' in tempImage && 'naturalHeight' in tempImage) {
			width = img.naturalWidth;
			height = img.naturalHeight;
		} else {
			tempImage.src= img.src;
			width = tempImage.width;
			height = tempImage.height;
		}
		return {
			width: width,
			height: height
		};
	});


	TablesFront.prototype.calculateImages = (function($table) {
		var _this = this.$obj,
			$images = $table.find('img');
		if ($images.length > 0 && /firefox|trident|msie/i.test(navigator.userAgent)) {
			$images.hide();
			$.each($images, function(index, el) {
				var $img = $(this),
					originalSizes = _this.getOriginalImageSizes(this);
				if ($img.closest('td, th').width() < originalSizes.width) {
					$img.css('width', '100%');
				}
			});
			$images.show();
		}
	});

	TablesFront.prototype.initFormulas = (function() {
		var _this = this.$obj,
			letterNum = {},
		formulas = {
			fn: [],
			cntRows: 0,
			cntCols: 0,
			deString: function(t, e, n) {
				var r = [];
				return t = t.replace(/(?:^|[^"]+)"(([^"]|"{2})+)"(?=([^"]+|$))/g, function(t, e) {
					var n = t.indexOf(e);
					return r.push(e),
					t.slice(0, n - 1) + "#" + (r.length - 1) + "#"
				}),
				t = e(t),
				r.forEach(function(e, r) {
					n && (e = e.replace(/""/g, '\\"')),
					t = t.replace("#" + r + "#", '"' + e + '"')
				}),
				t
			},
			cell: function(t) {
				var e = this.toCell(t)
				  , n = e.r
				  , r = e.c;
				return this.valueArr(n, r)[0]
			},
			computeAll: function(dt, settings) {
				var e = this, n = dt, edited = [];
				e.dt = dt;
				//e.startRow = settings.ssp ? e.dt.page.info().start : 0;
				e.startRow = 0;
				e.startCol = settings.autoIndex == 'new' ? 1 : 0;
				e.setData(true);
				e.searchReplace = new RegExp(/"/, 'g');

				for (var i = e.fn.length; i--; ) {
					var fn = e.fn[i],
						//node = e.getNode(fn.ri+1, fn.ci),
						node = $(e.dt.cell(fn.ri, fn.ci-1).node()),
						oldValue = node.attr('data-value'),
						value = e.execIfDirty(fn);
					e.fn[i].val = value;
					if (value != oldValue) {
						edited.push(node);
						node.attr('data-value', value).attr('data-not-format', 1).html(value);
					}
				}
				
				return edited;
			},
			setData: function(reset) {
				var e = this,
					cells = e.dt.cells('[data-fn]')[0];
				if (reset) {
					e.fn = [];
					e.data = [];
					e.colModel = [];
					e.obj = Object.assign({}, pq.formulas);
				}
				if (e.fn.length == 0) {
					for(var i = 0; i < cells.length; i++) {
						var r = cells[i].row,
							c = cells[i].column,
							node = $(e.dt.cell(r, c).node());
						e.fn.push({
							ci: c + 1,
							ciO: c + 1,
							clean: 0,
							fn: node.attr('data-fn'),
							fnOrig: node.attr('data-fn'),
							ri: r,
							riO: r,
							val: node.attr('data-value')
						});
					}
					if (e.fn.length) {
						e.cntRows = e.dt.rows()[0].length;
						e.cntCols = e.dt.columns()[0].length;
					}

				}
				return;
			},
			execIfDirty: function(t) {
				if (t.clean) {
					if (0.5 == t.clean)
						return
				} else
					t.clean = 0.5,
					t.val = this.exec(t.fn, t.ri, t.ci),
					t.clean = 1;
				return t.val
			},
			exec: function(t, e, r) {
				var i = this
				  , o = i.obj
				  , a = i.deString(t, function(t) {
					return t = t.replace(/(\$?([A-Z]+)?\$?([0-9]+)?\:\$?([A-Z]+)?\$?([0-9]+)?)/g, function(t, e) {
						return o[e] = o[e] || i.range(e),
						"this['" + e + "']"
					}),
					t = t.replace(/(?:[^:A-Z]|^)(\$?[A-Z]+\$?[0-9]+)(?!:)/g, function(t, e) {
						o[e] = o[e] || i.cell(e);
						var n = t.charAt(0);
						return (t === e ? "" : "$" == n ? "" : n) + e
					}),
					t = t.replace(/{/g, "[").replace(/}/g, "]").replace(/(?:[^><])(=+)/g, function(t, e) {
						return t + (1 === e.length ? "==" : "")
					}).replace(/<>/g, "!==").replace(/&/g, "+")
				}, !0);
				o.getRange = function() {
					return {
						r1: e,
						c1: r
					}
				}
				;
				try {
					var l = new Function("with(this){return " + a + "}").call(o);
					"function" == typeof l ? l = "#NAME?" : "string" == typeof l && this.deString(l, function(t) {
						t.indexOf("function") >= 0 && (l = "#NAME?")
					}),
					l !== l && (l = null)
				} catch (s) {
					l = "string" == typeof s ? s : s.message
				}
				return l
			},
			getNode: function(t, e) {
				//return $(this.dt.cell(t, e - 1).node());
				return $(this.dt.cell('[data-row-index="'+t+'"]', '[data-col-index="'+e+'"]').node());
			},
			range: function(t) {
				var e = t.split(":")
				  , n = this.that
				  , r = this.toCell(e[0])
				  , i = r.r
				  , o = r.c
				  , a = this.toCell(e[1])
				  , l = a.r
				  , s = a.c;
				return this.valueArr(null == i ? 0 : i, null == o ? 0 : o, null == l ? this.cntRows - 1 : l, null == s ? this.cntCols.length - 1 : s)
			},
			toCell: function(t) {
				var e = t.match(/\$?([A-Z]+)?\$?(\d+)?/);
				return {
					c: e[1] ? this.toNumber(e[1]) + this.startCol : null,
					//r: e[2] ? e[2] - 1 - this.startRow : null
					r: e[2] ? parseInt(e[2]) : null
				}
			},
			toNumber: function(t) {
				var n, r, i, l, s, d = letterNum[t];
				if (null == d) {
					for (n = t.length, d = -1, r = 0; n > r; r++) {
						i = t[r];
						l = i.charCodeAt(0) - 64;
						s = n - r - 1;
						d += l * Math.pow(26, s);
					}
					d++;
					letterNum[t] = d
				}
				return d
			},
			getFn: function(s) {
				for (var i = this.fn.length; i--; ) {
					if (this.fn[i].fn == s) {
						return this.fn[i];
					}
				}
				return {};
			},
			valueArr: function(t, e, n, r) {
				var i, o, a, l, s, d, c, node, u = this.that, f = this.cntCols, p = [], g = [], v = [];//, w = this.cntRows;
				n = null == n ? t : n;
				r = null == r ? e : r;
				t = 0 > t ? 0 : t;
				e = 0 > e ? 0 : e;
				//n = n > w ? w : n;
				r = r > f ? f : r;
				for (i = t; n >= i; i++) {
					for (o = e; r >= o; o++) {
						node = this.getNode(i, o);
						if (node.length) {
							s = node.attr('data-fn');
							if (s) {
								d = this.execIfDirty(this.getFn(s)); 
							}
							else {
								d = node.attr('data-value').replace(this.searchReplace, '&quot;');
								if (d) {
									c = 1 * d;
									d = d == c ? c : d; 
								}
								else d = '';
							}
							p.push(d),
							v.push(d);
						}
					}
					g.push(v),
					v = []
				}
				return p.get2Arr = function() {
					return g
				}
				,
				p.getRange = function() {
					return {
						r1: t,
						c1: e,
						r2: n,
						c2: r
					}
				}
				,
				p
			}
		};
		_this.Formulas = formulas;
		_this.Formulas.obj = Object.assign({}, pq.formulas);
	});

	app.pytFormats = new FormatModel();
	app.pytTables = new TablesFront();

	$(document).ready(function () {
		app.pytFormats.init();
		app.pytTables.init();
	});
}(window.jQuery, window));

(function ($) {
	$.fn.isHorizontallyScrollable = function() {
		if (this.scrollLeft()) {
			// Element is already scrolled, so it is scrollable
			return true;
		} else {
			// Test by actually scrolling
			this.scrollLeft(1);

			if (this.scrollLeft()) {
				// Scroll back
				this.scrollLeft(0);
				return true;
			}
		}
		return false;
	};
	$.extend($.expr.pseudos || $.expr[ ":" ], {
		"vertically-scrollable": function(a, i, m) {
			return $(a).isVerticallyScrollable();
		},
		"horizontally-scrollable": function(a, i, m) {
			return $(a).isHorizontallyScrollable();
		}
	});

	$.fn.removeStyle = function(style)
	{
		var search = new RegExp(style + '[^;]+;?', 'g');

		return this.each(function()
		{
			$(this).attr('style', function(i, style)
			{
				return style && style.replace(search, '');
			});
		});
	};
}(window.jQuery));