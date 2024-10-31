(function ($, app) {
"use strict";

// ================================================================ //
// ******************* BUILDER MODEL *************************** //	
	function BuilderModel() {
		this.inited = false;
		this.saveBeforePreview = true;
		this.$obj = this;
		return this.$obj;
	}

	BuilderModel.prototype.init = function () {
		var _this = this.$obj;
		_this.builderContent = $('#pyt-builder'),
		_this.builderLoader = $('#builderLoading')
		_this.showContentLoading();

		_this.builderToolbar = $('#pytBuilderToolbar');
		_this.builderFormula = $('#pytBuilderFormula');
		
		_this.fullBuilder = _this.builderToolbar.length == 1;
		_this.sourceSettings = false;

		_this.adminPage = app.pytAdminTablePage;
		_this.formatModel = app.pytFormats;
		_this.builderSettings = pytParseJSON($('#pytBuilderSettings').val());
		_this.builderType = _this.getSettingValue('builder', 0);
		if (!_this.inited) {
			_this.table = $('#pytBuilderTable');
			_this.formulaInput = $('#formula');
			_this.cellParams = ['pq_cellattr', 'pq_cellprop', 'pq_cellstyle', 'pq_rowprop', 'pq_rowstyle', 'pq_fn', 'pq_ht', 'pq_htfix'];
			_this.defColWidth = 120;
			_this.rowsPerPage = parseInt(_this.getSettingValue('rows_per_page', 0));
			_this.pagination = (_this.rowsPerPage > 0);
			_this.remote = _this.getSettingValue('ssp', false);
			_this.loadStep = _this.getSettingValue('load_table_step', 0);
			_this.setFormats();
		}
		
		_this.rowOrders = [];
		_this.maxColIndx = 0;
		_this.maxRowIndx = 0;
		_this.initColumnModel();
		_this.initSortModel();
		_this.initMergeCells();
		
		_this.edited = false;
		_this.saved = false;
		_this.changeFormat = false;
		_this.changeFormatRemote = false;
		_this.changeFormatType = '';
		_this.rowChanged = [];
		_this.lettersMode = false;
		pytApplyHookAction(_this, 'beforeInitGrid');

		_this.initGrid(false);
		if(!_this.inited) {
			if (_this.fullBuilder) {
				_this.toolbar = new app.pytBuilderToolbar('#pytBuilderToolbar', _this),
				_this.toolbar.init();
			}
			_this.initColSettingsForm();
			pytClassInitPro(_this);
		}
		_this.eventsBuilderModel();
		_this.inited = true;
		_this.hideContentLoading();
	}

	BuilderModel.prototype.getSettingValue = function (key, def) {
		if (this.builderSettings[key]) return this.builderSettings[key];
		return typeof(def) == 'undefined' ? false : def;
	}
	BuilderModel.prototype.save = function() {
		var _this = this.$obj;
		if(_this.adminPage.saving) {
			setTimeout(function() {	_this.save(); }, 2000);
			return;
		}
		if (_this.inited && _this.edited) {
			pytApplyHookAction(_this, 'saveChangesPro');
			if (_this.edited ) _this.saveChanges();
		}
	}
	BuilderModel.prototype.setFormats = function() {
		var _this = this.$obj,
			format = _this.formatModel,
			$optionsTab = $('.pyt-options-wrap');

		format.setFormatTable(false);
		format.setDelims($optionsTab.find('select[name="options[formats][dec]"]').val(), $optionsTab.find('select[name="options[formats][thn]"]').val());

		$optionsTab.find('input[name^="options[formats]"]').each(function() {
			format.setFormat($(this).attr('data-type'), $(this).val());
		});
	}

	BuilderModel.prototype.eventsBuilderModel = function () {
		var _this = this.$obj,
			$buildersContent = _this.builderContent,
			$builderSwitchers = $('.pyt-builder-switcher a');

		$builderSwitchers.on('click', function (e) {
			e.preventDefault();
			var $this = $(this);
			if ($this.hasClass('current')) return;
			var	builder = $this.attr('data-builder'),
				grid = _this.grid,
				colModel = _this.grid.colModel,
				toolbar = _this.toolbar;

			$builderSwitchers.removeClass('current');
			$this.addClass('current');
			if (builder == 1) {
				_this.builderToolbar.removeClass('pytHidden');
				_this.builderFormula.removeClass('pytHidden');
				if (grid) {

					grid.option('toolbar.cls', 'pytHidden');
					grid.option('filterModel.header', _this.showFilter);
					toolbar.refreshControlButtons();
					colModel[0].hidden = true;
				}
			} else {
				_this.builderToolbar.addClass('pytHidden');
				_this.builderFormula.addClass('pytHidden');
				if (grid) {
					grid.option('toolbar.cls', 'pq-toolbar-pyt');
					_this.showFilter = grid.option('filterModel.header');
					grid.option('filterModel.header', false);
					if (_this.lettersMode) {
						_this.lettersMode = false;
						_this.setColLetters(grid);
					}
					colModel[0].hidden = false;
				}
			}
			if (grid) {
				grid.refreshToolbar();
				grid.refresh();
			}
			_this.builderType = builder;
		});
		$builderSwitchers.filter('[data-builder="' + _this.builderType + '"]').trigger('click');
	}
	
	BuilderModel.prototype.refresh = function () {
		this.$obj.formatModel.setFormatTable(false);
		this.$obj.grid.refreshView();
	}

	//------------- Column Model-------------//
	BuilderModel.prototype.initColumnModel = function (columns) {
		var _this = this.$obj,
			columns = typeof columns == 'undefined' ? _this.getSettingValue('columns', []) : columns,
			colModel = [],
			maxNameIndx = 0,
			checkbox = {
				dataIndx: 'cb',
				maxWidth: 30,
				minWidth: 30,
				align: 'center',
				resizable: false,
				title: '',
				menuIcon: false,
				type: 'checkBoxSelection',
				clsHead: 'pyt-col-cb',
				sortable: false,
				editor: false,
				nodrag: true,
				nodrop: true,
				copy: false,
				dataType: 'bool',
				cb: {
					all: true, //checkbox selection in the header affect current page only.
					header: true //show checkbox in header.
				} 
			};
		if (_this.builderType == 1) checkbox.hidden = true;

		colModel.push(checkbox);
		_this.defaultColumn = {title: 'New', align: 'left', width: '120', resizable: true, dataType: 'html', prop: {pyt: {pytType: {type: 'text', format: ''}, sortable: 1, searchable: 1}}};

		$.each(columns, function(name) {
			var col = this;
			col.dataType = 'html';
			col.resizable = true;
			if (typeof(col.sortable) == 'undefined') col.sortable = 1;
			else col.sortable = parseInt(col.sortable);
			if (this.nameIndx > maxNameIndx) {
				maxNameIndx = this.nameIndx;
			}
			if (!col.hasOwnProperty('prop')) col.prop = {};
			if (!col.prop.hasOwnProperty('pyt')) col.prop.pyt = {};
			if (!col.prop.pyt.hasOwnProperty('pytType')) col.prop.pyt.pytType = {type: 'text', format: ''};

			_this.addColumnSettings(col);

			colModel.push(col);
		});
		colModel.push({dataType: 'string', dataIndx: 'id', hidden: true, menuInHide: true});

		_this.maxColIndx = maxNameIndx;
		_this.colModel = colModel;
		_this.colPrefix = _this.getSettingValue('col_prefix', 'col');
	}

	BuilderModel.prototype.addColumnSettings = function(col) {
		for (var k in col) {
			if (k.indexOf('pyt') === 0) col[k] = '';
		}
		var _this = this.$obj,
			pytProp = col.prop.pyt;
		delete pytProp.pyt;
		col.prop = {pyt: pytProp};

		for (var key in pytProp) {
			var value = pytProp[key],
				isObj = typeof(value) == 'object';
			if (key == 'width' && isObj) {
				if (value.width && value.width != '') {
					col.width = value.width + (value.points == '%' ? '%' : '');
					col.pytFlex = false;
					col.resizable = (value.points != '%');
				} else {
					if (col._width) col.width = col._width;
					col.resizable = true;
					col.pytFlex = true;
				}
			} else col[key] = isObj ? $.extend(true, {}, value) : value;
			col.prop[key] = pytProp[key];
		}
		col = pytApplyHookFilter(_this, col.pytType.type +'FormatSettings', col);
		pytApplyHookAction(_this, 'addColumnSettingsPro', col);
		return col;
	}

	BuilderModel.prototype.getNewColumnIndex = function () {
		var _this = this.$obj;
		_this.maxColIndx++;
		return _this.colPrefix + _this.maxColIndx;
	}

	BuilderModel.prototype.updateColModel = function () {
		var _this = this.$obj;
		_this.grid.colModel = _this.grid.colModel.map(function (col) {
			if (col.dataIndx.indexOf('col') == 0) _this.addColumnSettings(col);
			return col;
		});
	}
	BuilderModel.prototype.setColumnModel = function (model, titles) {
		var _this = this.$obj;
		_this.colModel = model.map(function (col) {
			var newCol = $.extend(true, {}, col);
			if (newCol.dataIndx in titles) newCol.title = titles[newCol.dataIndx];
			delete(newCol.filter);
			delete(newCol.filterUI);
			delete(newCol.format);
			delete(newCol.deFormat);
			delete(newCol.render);
			delete(newCol.align);
			delete(newCol.valign);
			delete(newCol.leftPos);

			return _this.convertSettings(newCol);
		});
		return _this.colModel;
	}
	BuilderModel.prototype.getModelColumn = function (key) {
		var _this = this.$obj,
			model = _this.grid.getColModel();
		for (var i = 0; i < model.length; i++) {
			if (model[i].dataIndx == key) {
				return model[i];
			}
		}
		return false;
	}
	BuilderModel.prototype.getColDataIndx = function(colIndx) {
		return this.$obj.grid.getColModel()[colIndx].dataIndx;
	}
	BuilderModel.prototype.updateColumnProps = function(cols, props) {
		if (!Array.isArray(cols)) return;
		var _this = this.$obj,
			grid = _this.grid,
			colModel = grid.getColModel(),
			updated = false;
		for (var c = 0; c < cols.length; c++) {
			var col = cols[c];
			if (colModel[col]) {
				var pyt = colModel[col].prop.pyt,
					coped = [];
				for (var p = 0; p < props.length; p++) {
					var ps = props[p][0].split('.'),
						prop = ps[0],
						sub = ps.length > 1 ? ps[1] : false,
						value = props[p][1];
					if (value == '') {
						if (sub) delete pyt[prop][sub];
						else delete pyt[prop];
					} else {
						if (sub) {
							pyt[prop][sub] = value;
							pyt[prop] = _this.convertPropsToObject(pyt[prop]);
						}
						else pyt[prop] = value;
					}
					pyt.edit = false;
				}
			}			
		}
	}

	//------------- end Column Model-------------//

	//--------------- Sorter Model---------------//
	BuilderModel.prototype.initSortModel = function () {
		var _this = this.$obj,
			sorter = _this.getSettingValue('sorter', []),
			sortModel = {
				cancel: true,
				ignoreCase: false,
				multiKey: 'shiftKey',
				number: true,
				on: true,
				single: pytCheckSettings(sorter, 'single') == '1',
				type: _this.remote ? 'remote' : 'local',
				sorter: pytCheckSettings(sorter, 'sorter', [])
			};
		_this.sortModel = sortModel;
	}
	BuilderModel.prototype.setSortModel = function (model) {
		var _this = this.$obj;

		if ('sorter' in model) {
			for (var i in model.sorter) {
				if (_this.getModelColumn(model.sorter[i].dataIndx) == false) {
					delete(model.sorter[i]);
				}
			};
		}
		model = _this.convertSettings(model);

		_this.sortModel = model;
		return _this.sortModel;
	}
	BuilderModel.prototype.saveRowOrder = function(grid, reset) {
		var _this = this.$obj,
			ids = (this.remote ? grid.pageData() : grid.getData()).map(function (rd) {
			return rd.id;
		});
		if (typeof reset != undefined && reset) _this.rowOrders = [];
		_this.rowOrders.push(ids);
	}
	//------------- end Sorter Model-------------//

	//--------------- Merge Model ---------------//
	BuilderModel.prototype.initMergeCells = function (mergeCells) {
		var _this = this.$obj,
			mergeCells = typeof mergeCells == 'undefined' ? _this.getSettingValue('merge', []) : mergeCells;

		mergeCells = mergeCells.map(function (merge) {
			return {
				r1: parseInt(merge.r1),
				c1: parseInt(merge.c1),
				cc: parseInt(merge.cc),
				rc: parseInt(merge.rc),
			};
		});
		_this.mergeCells = mergeCells;
		if (_this.grid) {
			_this.grid.option('mergeCells', mergeCells);
		}
	}
	BuilderModel.prototype.setMergeCells = function (data) {
		this.$obj.mergeCells = data;
	}
	BuilderModel.prototype.getMergedCells = function() {
		var _this = this.$obj,
			grid = _this.grid,
			colModel = grid.option('colModel'),
			mergeCells = grid.option('mergeCells').map(function (merge) {
				var newMerge = $.extend({}, merge);
				var row = grid.getRowData({rowIndx: newMerge.r1});
				if (row) {
					newMerge.id = row.id;
					newMerge.col = colModel[newMerge.c1].dataIndx;
				}
				return newMerge;
			});
		return mergeCells;
	}
	//------------- end Merge Model-------------//

	BuilderModel.prototype.reinitGrid = function () {
		var _this = this.$obj,
			grid = _this.grid;
		grid.option('colModel', _this.colModel);
		grid.refreshDataAndView();
	}

	BuilderModel.prototype.initGrid = function (reinit) {
		var _this = this.$obj;
		if (_this.inited) {
			if (reinit)	_this.reinitGrid();
			return;
		}
		var page = _this.adminPage,
			format = _this.formatModel,
			isFull = _this.fullBuilder;

		_this.showFilter = (isFull && _this.builderType == 1);		

		var settings = { 
			width: 'auto',
			minHeight: 700,
			create: function (evt, ui) {
				this.widget().pqTooltip({position: {my:'left', at: 'right top'}});
			},
			roundCorners: false,
			resizable: true,
			collapsible: { on: false, collapsed: false, toggle: false},
			stripeRows: false,            
			showTitle: false,
			autofill: true,
			filterModel: {
				on: true,
				header: _this.showFilter,
				type: 'local',
				menuIcon: true,
				mode: 'AND'
			},
			beforeFilter: function( event, ui ) {
				var ass=9;
			},
			numberCell:{resizable: true, minWidth: 40},
			editable: isFull,
			showBottom: false,
			selectionModel : {
				all: true,
			},
			trackModel: { on: true },
			showToolbar : true,
			historyModel: { on: isFull, allowInvalid: true, checkEditable: true, checkEditableAdd: true},
			mergeCells: [],
			animModel:  { on: true, duration: 300 },
			columnOrder: function( event, ui ) {
				_this.setColLetters(this);
			},
			dragColumns: { enabled: isFull },
			dragModel:{
				on: isFull,
				diHelper: ['id'],
				tmplDragN: '<span class="ui-icon ui-icon-grip-dotted-vertical pq-drag-handle">&nbsp;</span>',
				dragNodes: function(rd, evt){
					var selected = this.Selection().getSelection(),
						rows = [];
					$.each(selected, function() {
						if (this.colIndx == 0) rows.push(this.rowData);
					});
					return rows.length > 0 ? rows : [rd];
				},
				contentHelper: function(diHelper, dragNodes){
					return 'move ' + dragNodes.length + ' row(s)';
				},
			},
			dropModel:{
				on: isFull
			},
			moveNode: function (event, ui) {
				_this.saveRowOrder(this, false);
				_this.edited = true;
				this.refresh();
			},
			columnTemplate: {
				styleHead: {},
				prop: {},
				minWidth: 1,
				render: function(ui) {
					if (ui.dataIndx == 'cb') return;
					var data = ui.cellData;
					if (typeof(data) == 'undefined') data = '';

					var row = ui.rowData,
						typeFormat = _this.getCellTypeFormat(row, ui.column),
						formated = format.formatValue(data, typeFormat.type, typeFormat.format);
					if (!('pyt_formated' in row)) row.pyt_formated = {};
					row.pyt_formated[ui.column.dataIndx] = formated;

					var result = {attr: 'data-type="' + typeFormat.type + '"', text: formated};
					return pytApplyHookFilter(_this, 'renderCellPro', result, ui);
				},
				format: function(val) {
					return val;
				},
				deFormat: function(val) { //need for filtering
					return val;
				}
			},
			columnResize: function(evt, ui) {
				if (ui.oldWidth != ui.newWidth) {
					var col = this.option('colModel')[ui.colIndx];
					if (col) {
						var pytProps = $.extend(true, {}, _this.convertPropsToObject(col.prop.pyt));
						pytProps['width'] = {width: ui.newWidth, points: ''};
						pytProps.edit = true;
						this.Range({ c1: ui.colIndx }).prop('pyt', pytProps);
						_this.addColumnSettings(col);
						_this.edited = true;
					}
				}
			},
			complete: function() {
				_this.saveRowOrder(this, true);
				this.flex();
				$.each(this.option('colModel'), function(i) {
					if (!this.pytFlex && this.prop.width) {
						var w = parseInt(this.prop.width.width);
						if (this.prop.width.points && this.prop.width.points.length) w += this.prop.width.points;
						this.width = w;
					}
				});
				this.refreshView();
			},         
			autoRow: true,
			rowResize: true,
			contextMenu: {
				on: true,
				headItems: isFull ? _this.getHeadCMenu : _this.getHeadCMenuLight ,
				cellItems: isFull ? _this.getBodyCMenu : _this.getBodyCMenuLight,                
			},
   
			swipeModel: { on: false },
			history: function (evt, ui) {
				var typ = ui.type;
				if (typ == 'add') _this.setChangedRows();
				else if (typ == 'undo' || typ == 'redo') {
					var columns = this.colUndoRedo;
					if (columns && columns.length == 2) {
						var isStyle = columns[0] == 'style';
						for(var key in columns[1]) {
							var col = columns[1][key];
							if (isStyle) col.prop.pyt.style = $.extend(true, {}, col.style);
							else {
								if (!col.prop.pyt.edit) {
									var prop = $.extend(true, {}, col.prop);
									delete prop.pyt;
									col.prop.pyt = prop;
								}
								_this.addColumnSettings(col);
							}
						}
						this.refreshView();
					}
				} 
				if (_this.toolbar) {
					var toolbar = _this.toolbar.getContainer(),
						undo = toolbar.find('[data-method="undo"]'),
						redo = toolbar.find('[data-method="redo"]');
					if (ui.canUndo != null) {
						if (ui.canUndo) undo.removeClass('inactive');
						else undo.addClass('inactive');
						_this.edited = ui.canUndo;
					}
					if (ui.canRedo != null) {
						if (ui.canRedo) redo.removeClass('inactive');
						else redo.addClass('inactive');
					}
				}
			},
			selectEnd: function(e, ui) {
				_this.setFormulaInput(ui);
				_this.grid.option('toolbar').items[2].attr = '';
				_this.grid.refreshToolbar();
			},
			change: function( event, ui ) {
				_this.edited = true;
				_this.setFormulaInput(ui);
			},
			beforeValidate: function(evt, ui){
				if(ui.source == 'checkbox' ) {
					ui.track = false;
					ui.history = false;
				}
			},
			sort: function( event, ui ) {
				if (event.originalEvent && event.originalEvent.type == 'click') {
					_this.edited = true;
				}
			},
			editor: {
				type: function (ui) {
					var cellType = _this.getCellTypeFormat(ui.rowData, ui.column);
					if (cellType.type == 'textarea') return 'textarea';
					return 'textbox';
				},
				init: function (ui) {
					var cellType = _this.getCellTypeFormat(ui.rowData, ui.column),
						editFunc = cellType.type +'Editor';
					if(_this[editFunc]) return _this[editFunc](ui, cellType.format);
				}
			},
			mergeCells: _this.mergeCells,
			freezeCols: 1,
			toolbar: {
				cls: _this.builderType == 1 ? 'pytHidden' : 'pq-toolbar-pyt',
				items: [
					{
						type: function () {
							return '<select class="pyt-bulk-select">' + 
								'<option value="">' + page.getLangString('builder', 'bulk-label') + '</option>' +
								'<option value="delete">' + page.getLangString('builder', 'bulk-delete') + '</option></select>';
						}
					},
					{
						cls: 'button button-secondary button-small pyt-bulk-button',
						type: 'button', label: page.getLangString('builder', 'btn-apply'), 
						listener: function () {
							var action = _this.table.find('.pyt-bulk-select').val();
							if (action == '') return;
							var list = [],
								checked = this.Checkbox('cb').getCheckedNodes();
							if (action == 'delete') {
								if (checked && checked.length) {
									$.each(checked, function() {
										list.push({ rowIndx: this.pq_ri});
									});
									if (list.length) {
										_this.grid.deleteRow({rowList: list});
										_this.saveRowOrder(_this.grid, false);
									}
								}
							}
						},
					},
					{
						cls: 'button button-secondary button-small pubydoc-right',
						attr: 'disabled',
						type: 'button', label: page.getLangString('builder', 'row-edit'), 
						listener: function () {
							var row = _this.getSelectedRow();
							if (row !== false) {
								_this.rowEditor(row);
							}
						}
					},
					{
						cls: 'button button-secondary button-small pubydoc-right',
						type: 'button', label: page.getLangString('builder', 'row-add'), listener: function () {
							_this.rowEditor(-1);
						}
					},
					{ type: '<span class="pyt-separator"></span>' },
					{
						cls: 'button button-secondary button-small pubydoc-right',
						type: 'button', label: page.getLangString('builder', 'col-delete'),
						listener: function () {
							_this.deleteColumns();
						}
					},
					{
						cls: 'button button-secondary button-small pubydoc-right',
						type: 'button', label: page.getLangString('builder', 'col-add'),
						listener: function () {
							_this.selectedColumn = -1;
							_this.dialogColSettings.dialog('open');
						}
					},
					
				]
			}
		};

		var location = _this.remote ? 'remote' : 'local'; 
		if (_this.pagination) {
			var rPP = _this.rowsPerPage,
				minPP = 100, maxPP = 1000,
				options = [];
			if (rPP < minPP) options.push(rPP, minPP, maxPP / 2, maxPP);
			else {
				options.push(minPP);
				if (rPP > maxPP) options.push(maxPP / 2, maxPP, rPP);
				else options.push(rPP, maxPP);
			}
			settings.pageModel = { 
				type: location, 
				rPP: rPP, 
				strRpp: "{0}", 
				strDisplay: page.getLangString('builder', 'page-info'),
				strPage: page.getLangString('builder', 'page-str'),
				strRefresh: page.getLangString('builder', 'page-refresh'),
				strFirstPage: page.getLangString('builder', 'page-first'),
				strPrevPage: page.getLangString('builder', 'page-prev'),
				strNextPage: page.getLangString('builder', 'page-next'),
				strLastPage: page.getLangString('builder', 'page-last'),
				rPPOptions: options,
				beforeChange: function() {
					if (_this.edited && _this.remote) {
						return confirm(_this.adminPage.getLangString('builder', 'page-save', 'Save page?'));
					}
				 }
			}
		}

		settings.colModel = _this.colModel;
		settings.sortModel = _this.sortModel;

		var adminPage = _this.adminPage;
		
		settings.dataModel = {
			location: 'remote',
			dataType: 'JSON',
			method: 'POST',
			url: adminPage.ajaxurl,
			recIndx: 'id',
			postData: {
				tableId: adminPage.tableId,
				tableType: adminPage.tableType,
				source: function() {
					return pytApplyHookFilter(_this, 'getSourceSettings', '');
				},
				reqType: 'ajax',
				pl: 'pyt',
				mod: 'tables',
				action: 'getCellsData'
			},
			getData: function (dataJSON) {
				_this.initGridData(dataJSON);
				var data = dataJSON.data;

				if (_this.changeFormatRemote) _this.edited = true;//????????????????
				return { curPage: dataJSON.curPage, totalRecords: dataJSON.totalRecords, data: data };
			}
		};

		var $grid = _this.table.pqGrid(settings);
		_this.grid = $grid.pqGrid('instance');

		if (!_this.inited) _this.gridEvents();
	}
	BuilderModel.prototype.gridEvents = function () {
		var _this = this.$obj;
		_this.formulaInput.on('focus', function () {
			if(_this.grid.Selection().address().length == 0) {
				_this.grid.setSelection({rowIndx: 0,colIndx: 0});
			}
		});

		_this.formulaInput.on('keyup', function () {
			var cell = _this.getSelectedFirstCell();
			if (cell) {
				var row = {};
				row[_this.getColDataIndx(cell.c)] = $(this).val();
				_this.grid.updateRow({rowIndx: cell.r, newRow: row});
			}
		});
		_this.table.on('click', 'div.pq-grid-col.pq-grid-col-leaf', function(e) {
			var $elem = $(e.target);

			if ($elem.hasClass('pq-grid-col') && $elem.width() - e.offsetX < 16) {
				e.preventDefault();
				_this.selectedColumn = parseInt($elem.attr('pq-col-indx'));
				_this.dialogColSettings.dialog('open');
				return false;
			}
		});
	}
	BuilderModel.prototype.setFormulaInput = function (ui) {
		var _this = this.$obj;
		if (_this.formulaInput.is(':focus')) return;
		_this.formulaInput.val('');
		var	cell = _this.getSelectedFirstCell(true, ui.selection);
		if (cell && cell.row) {
			var row = cell.row,
				di = cell.dataIndx;
			_this.formulaInput.val(row.pq_fn && di in row.pq_fn ? '=' + row.pq_fn[di].fn : row[di]);
		} else _this.formulaInput.val('');
	}

	BuilderModel.prototype.convertSettings = function (obj) {
		for (var key in obj) {
			var typ = typeof(obj[key]);
			if (typ == 'boolean') obj[key] = (obj[key] ? 1 : 0);
			else if (typ == 'array' || typ == 'object') obj[key] = Object.assign({}, obj[key]);
		}
		return obj;
	}
	BuilderModel.prototype.getNewRowIndex = function () {
		var _this = this.$obj;
		_this.maxRowIndx++;
		return 'new' + _this.maxRowIndx;
	}

	BuilderModel.prototype.initGridData = function (data) {
		var _this = this.$obj;
		pytApplyHookAction(_this, 'initGridDataPro', data)
	}
	BuilderModel.prototype.setColLetters = function(grid) {
		if (this.$obj.lettersMode) {
			$.each(grid.colModel, function() { 
				this.title = pq.toLetter(this.leftPos);
			});
			grid.refreshHeader();
		}
	}
	BuilderModel.prototype.getCellTypeFormat = function(row, col) {
		var cellType = {};
		if (row.pq_cellprop && row.pq_cellprop[col.dataIndx]) {
			cellType = row.pq_cellprop[col.dataIndx]['pytType'];
			if (cellType && cellType.type && cellType.type != '') return cellType;
		}
		return col.pytType ? col.pytType : {};
	}
	BuilderModel.prototype.updateCell = function(row, col, value) {
		var data = {};
		data[col] = value;
		this.$obj.grid.updateRow({rowIndx: row, newRow: data});
	}
	BuilderModel.prototype.htmlEditor = function(ui) {
		var _this = this.$obj,
			page = _this.adminPage;
		_this.htmlField = {
			value: typeof(ui.cellData) == 'undefined' ? '' : ui.cellData, 
			row: ui.rowIndx, 
			col: ui.dataIndx
		};

		if (!_this.dialogHtmlEditor) {
			var dHtmlEditor = $('#pytDialogHtmlEditor'),
				$field = dHtmlEditor.find('#pytFieldHtml'),
				fieldId = $field.attr('id'),
				page = _this.adminPage,
				editorSettings = {
					selector: '#' + fieldId,
					mediaButtons: true,
					quicktags: true,
					tinymce: {
						wpautop: true,
						toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,forecolor,link,undo,redo,wp_help',
						height: 200
					}
				};
			_this.dialogHtmlEditor = dHtmlEditor.dialog({
				position: {my: 'center', at: 'center', of: '.pubydoc-main'},
				resizable: true,
				height: 'auto',
				width: '600px',
				modal: true,
				autoOpen: false,
				dialogClass: 'pubydoc-plugin',
				classes: {
					'ui-dialog': 'pubydoc-plugin'
				},
				buttons: [
					{
						text: page.getLangString('builder', 'btn-save'),
						class: 'button button-secondary',
						click: function() {
							var row = {};
							row[_this.htmlField.col] = wp.editor.getContent($field.attr('id'));
							_this.grid.updateRow({rowIndx: _this.htmlField.row, newRow: row});

							$(this).dialog('close');
							_this.grid.focus({rowIndxPage: ui.rowIndxPage, colIndx: ui.colIndx});
						}
					},
					{
						text: page.getLangString('builder', 'btn-cancel'),
						class: 'button button-secondary',
						click: function() {
							$(this).dialog('close');
							_this.grid.focus({rowIndxPage: ui.rowIndxPage, colIndx: ui.colIndx});
						}
					}
				],
				open: function(event, ui) {
					$field.val(_this.htmlField.value);
					wp.editor.remove(fieldId);
					wp.editor.initialize(fieldId, editorSettings);

					tinyMCE.get(fieldId).setContent(_this.htmlField.value);

					_this.mediaMCE = true;
				},
				create: function( event, ui ) {
					var parent = $(this).parent();
					parent.css('maxWidth', $(window).width()+'px');
					parent.css('minWidth', '300px');
					parent.css('maxHeight', 500);
				}
			});
		}
		_this.dialogHtmlEditor.dialog('open');
	}

	BuilderModel.prototype.dateEditor = function(ui) {
		var _this = this.$obj,
			$input = ui.$cell.find('input'),
			grid = this,
			format = 'yy-mm-dd',
			val = $input.val();

		$input
		.val(val)
		.datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: format,
			showAnim: '',
			onSelect: function () {
				this.firstOpen = true;
			},
			beforeShow: function (input, inst) {
				setTimeout(function () {
					//to fix the issue of datepicker z-index when grid is in maximized state.
					$('.ui-datepicker').css('z-index', 999999999999);
				});
				return !this.firstOpen;
			},
			onClose: function () {
				this.focus();
			}
		});
	}

	BuilderModel.prototype.rowEditor = function(row) {
		var _this = this.$obj,
			page = _this.adminPage
			_this.selectedRow = row;

		if (!_this.dialogRowEditor) {
			var dRowEditor = $('#pytDialogRowEditor');

			_this.dialogRowEditor = dRowEditor.dialog({
				position: {my: 'top', at: 'top', of: '.pubydoc-main'},
				resizable: true,
				height: 'auto',
				width: '600px',
				modal: true,
				autoOpen: false,
				dialogClass: 'pubydoc-plugin',
				classes: {
					'ui-dialog': 'pubydoc-plugin'
				},
				buttons: [
					{
						text: page.getLangString('builder', 'btn-save'),
						class: 'button button-secondary',
						click: function() {
							var $this = $(this),
								$container = dRowEditor.find('.pyt-fields-wrap'),
								row = _this.selectedRow,
								isNew = row < 0,
								data = {};
							$container.find('[name]').each(function(){
								data[this.name] = this.value;
							});

							$container.find('.pyt-file-wrap').each(function(){
								var $this = $(this);
								data[$this.attr('data-col')] = $this.html();
							});

							$container.find('.pyt-field-html').each(function(){
								var $this = $(this);
								data[$this.attr('data-col')] = wp.editor.getContent($this.attr('id'));
							});

							if (isNew) {
								_this.addRows(false, data);
							} else {
								_this.grid.updateRow({rowIndx: row, newRow: data});
								var r = _this.grid.getRowData({rowIndx: row});
								_this.addChangedRows(r.id);
							}
							_this.adminPage.saving = false;
							_this.edited = true;

							$(this).dialog('close');

						}
					},
					{
						text: page.getLangString('builder', 'btn-cancel'),
						class: 'button button-secondary',
						click: function() {
							$(this).dialog('close');
						}
					}
				],
				open: function(event, ui) {
					var $this = $(this),
						row = _this.selectedRow,
						isNew = row < 0,
						data = isNew ? [] : _this.grid.getRowData({rowIndx: row}),
						columns = _this.grid.colModel, 
						$container = dRowEditor.find('.pyt-fields-wrap').empty(),
						col, blockField, pytType, typ, format, value;

					$this.closest('.pubydoc-plugin').find('.ui-dialog-title').html(page.getLangString('builder', isNew ? 'row-add' : 'row-edit'));
					$this.css({"maxWidth": "600px", "minWidth": "300px", "maxHeight":"500px"});

					$.each(columns, function() {
						col = this.dataIndx;
						if (col != 'cb' && col != 'id') {
							value = col in data ? data[col] : '';
							pytType = _this.getCellTypeFormat(data, this);
							typ = pytType.type;
							format = pytType.format;
							blockField = '<div class="pyt-input-group" data-type="' + typ + '"><label>' + this.leftPos + '. ' + (this.title.length ? this.title : '') + '</label>';

							switch(typ) {
								case 'textarea': 
									blockField += '<textarea name="' + col + '">' + value + '</textarea>';
									break;
								case 'number':
								case 'money':
								case 'percent':
								case 'convert':
									blockField += '<input type="number" name="' + col + '" value="' + value + '"></div>';
									break;
								case 'select':
									blockField += '<select name="' + col + '">';
									var options = (format ? format : '').split('\n');
									for(var i = 0; i < options.length; i++) {
										blockField += '<option' + (options[i] == value ? ' selected="selected"' : '') + ' value="' + options[i] + '">' + options[i] + '</option>';
									}
									blockField += '</select>';
									break;
								case 'html':
									blockField += '<textarea id="pytFieldHtml-' + col +'" class="pyt-field-html" data-col="' + col + '">' + value + '</textarea>';
									break;
								case 'file':
									blockField += '<div class="pyt-file-wrap" data-col="' + col + '">' + value + '</div><input type="file" class="pyt-field-file">';
									break;
								default:
									blockField += '<input type="text" name="' + col + '" value="' + value + '"></div>';
							}
							$container.append(blockField);
						}
					});
					$container.find('.pyt-input-group[data-type="date"]').on('click', function() {
						_this.dateEditor({'$cell' : $(this)});
					});
					$container.find('.pyt-input-group .pyt-field-html').each(function() {
						var $field = $(this),
							fieldId = $field.attr('id'),
							editorSettings = {
								selector: '#' + fieldId,
								mediaButtons: true,
								quicktags: true,
								tinymce: {
									wpautop: true,
									toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,forecolor,link,undo,redo,wp_help',
									height: 200
								}
							};

						wp.editor.remove(fieldId);
						wp.editor.initialize(fieldId, editorSettings);

						tinyMCE.get(fieldId).setContent($field.val());

						_this.mediaMCE = true;
					});

					pytApplyHookAction(_this, 'addEditDataEvents', $container);

				},
				create: function( event, ui ) {
					var parent = $(this).parent();
					parent.css('maxWidth', $(window).width()+'px');
					parent.css('minWidth', '300px');
					parent.css('maxHeight', '500px');
				}
			});
		}
		_this.dialogRowEditor.dialog('open');
	}

	BuilderModel.prototype.getSelected = function() {
		var selection = this.$obj.grid.Selection();
		return selection.address().length ? selection : false;
	}
	BuilderModel.prototype.getSelectedFirstCell = function(withData, selection, type) {
		var _this = this.$obj,
			grid = _this.grid,
			range = selection ? selection.address() : grid.Selection().address();
		if (range.length) {
			range = range[0];
			if (type && type != range.type) return false;
			var r = range.r1,
				c = range.c1,
				t = range.type;
			if (range._type == 'column') t = 'column';
			if (t == 'column' && _this.remote) {
				r = grid.riOffset;
			} else if (t == 'row' || range._type == 'row') {
				c = 1;
			}
			if (withData) {
				return {c: c, r: r, row: _this.grid.getRowData({rowIndx: r}), dataIndx: _this.getColDataIndx(c), type: t};
			} else {
				return {c: c, r: r};
			}
		}
		return false;
	}
	BuilderModel.prototype.getSelectedRow = function() {
		var _this = this.$obj,
			grid = _this.grid,
			range = grid.Selection().address();

		return range.length ? range[0].r1 : false;
	}
	BuilderModel.prototype.getRangeRowCols = function(row) {
		var grid = this.$obj.grid,
			range = grid.Selection().address(),
			blocks = [];
		for (var i = 0; i < range.length; i++) {
			if (row) blocks.push({r1: range[i].r1, r2: range[i].r2});
			else blocks.push({c1: range[i].c1, c2: range[i].c2});
		}
		return blocks.length ? grid.Range(blocks) : false;
	}
	BuilderModel.prototype.getColsFromSelection = function(selection) {
		var grid = this.$obj.grid,
			ranges = selection ? selection.address() : grid.Selection().address(),
			cols = [];
		for (var i = 0; i < ranges.length; i++) {
			var range = ranges[i];
			if (range.type == 'column' || range._type == 'column') {
				var c1 = Math.min(range.c1, range.c2),
					c2 = Math.max(range.c1, range.c2);
				if (isNaN(c1) || isNaN(c1)) continue;
				for (var c = c1; c <= c2; c++) cols.push(c);
			}
		}
		return cols.length ? cols : false;
	}
	BuilderModel.prototype.withBlockSelection = function(selection) {
		var grid = this.$obj.grid,
			ranges = selection ? selection.address() : grid.Selection().address();
		for (var i = 0; i < ranges.length; i++) {
			if (ranges[i].type != 'column' && ranges[i]._type != 'column') return true;
		}
		return false;
	}
	BuilderModel.prototype.convertSelectedToBlock = function(selection) {
		var ranges = selection.address();
		for (var i = 0; i < ranges.length; i++) {
			if (ranges[i].type == 'row') ranges[i].type = 'block';
			if (ranges[i]._type == 'row') ranges[i]._type = 'block';
		}
		return selection;
	}

	BuilderModel.prototype.addChangedRows = function(id) {
		var _this = this.$obj;
		if (_this.rowChanged == 'all') return;
		if (id == 'all') _this.rowChanged = 'all';
		else if (_this.rowChanged.indexOf(id) == -1) _this.rowChanged.push(id);
	}
	BuilderModel.prototype.setChangedRows = function(selection) {
		var _this = this.$obj;
		if (_this.rowChanged == 'all') return;

		var grid = _this.grid,
			range = selection ? selection.address() : grid.Selection().address();
		if (range.length) {
			range = range[0];
			if (range.type == 'column' || range._type == 'column') {
				_this.addChangedRows('all');
				return false;
			}
			for (var r = range.r1; r <= range.r2; r++) {
				var row = grid.getRowData({rowIndx: r});
				_this.addChangedRows(row.id);
			}
		}
	}
	BuilderModel.prototype.getSelectedRowCols = function(row) {
		var selected = this.$obj.grid.Selection().getSelection(),
			list = [];
		$.each(selected, function() {
			var indx = row ? this.rowIndx : this.colIndx;
			if (!toeInArrayPyt(indx, list)) {
				list.push(indx);
			}
		});
		return list;
	}
	
	BuilderModel.prototype.addRows = function(selected, data) {
		var _this = this.$obj,
			isEmpty = typeof(data) == 'undefined',
			list = [];

		if (!selected) selected = _this.getSelectedRowCols(true);
		$.each(selected, function() {
			var newId = _this.getNewRowIndex(),
				newRow = isEmpty ? {} : $.extend(true, {}, data);
			newRow['id'] = newId;

			list.push({ rowIndx: this, newRow: newRow});
			_this.addChangedRows(newId);
		});
		if (list.length == 0) {
			var newId = _this.getNewRowIndex(),
				newRow = isEmpty ? {} : $.extend(true, {}, data);
			newRow['id'] = newId;
			list.push({ newRow: newRow});
			_this.addChangedRows(newId);
		}
		if (list.length) {
			_this.grid.addRow({rowList: list});
			_this.saveRowOrder(_this.grid, false);
		}
	}
	BuilderModel.prototype.deleteRows = function(selected) {
		var _this = this.$obj,
			list = [];

		if (!selected) selected = _this.getSelectedRowCols(true);
		$.each(selected, function() {
			list.push({ rowIndx: this});
		});
		if (list.length) {
			_this.grid.deleteRow({rowList: list});
			_this.saveRowOrder(_this.grid, false);
		}
	}
	BuilderModel.prototype.addColumns = function(selected, colInsx, pytProps, colParams) {
		var _this = this.$obj,
			grid = _this.grid,
			colModel = grid.option('colModel'),
			copyProps = typeof(pytProps) == 'undefined',
			list = [];

		if (!selected) selected = _this.getSelectedRowCols(false);
		$.each(selected, function() {
			var ci = this,
				col = ci in colModel ? colModel[ci] : false;
			if (copyProps) {
				var newProp = $.extend(true, {}, col.prop.pyt);
				delete(newProp.title);
			}
			if (col) {
				list.push(colParams ? colParams : {
					title: '',
					dataIndx: _this.getNewColumnIndex(),
					width: col.width,
					align: col.align,
					resizable: true,
					dataType: 'html',
					prop: {pyt: copyProps ? newProp : pytProps},
				});
				if (colInsx < 0) colInsx = ci;
			}
		});
		if (list.length) {
			grid.Columns().add(list, colInsx, colModel);
			_this.updateColModel();
			grid.refreshView();
			_this.addChangedRows('all');
		}
		_this.setColLetters(grid);
	}
	BuilderModel.prototype.deleteColumns = function(selected) {
		var _this = this.$obj,
			minIndex = -1;

		if (!selected) selected = _this.getSelectedRowCols(false);
		$.each(selected, function() {
			if (minIndex < 0 || minIndex > this) minIndex = this;
		});
		if (minIndex >= 0) {
			var colModel = _this.grid.option('colModel');
			_this.grid.Columns().remove(selected.length, minIndex, colModel);
		}
		_this.setColLetters(_this.grid);
	}

	BuilderModel.prototype.getHeadCMenu = function(evt, ui) {
		if (ui.colIndx == 0) return;
		var builder = app.pytTabModels.builder,
			page = builder.adminPage,
			toolbar = builder.toolbar;
		return [
				{
					name: page.getLangString('builder', 'col-settings'),
					icon: 'ui-icon ui-icon-gear',
					action: function (evt, ui) {
						builder.selectedColumn = ui.colIndx;
						builder.dialogColSettings.dialog('open');
					}
				},
				{
					name: page.getLangString('builder', 'tbe-col-sortable', 'Sortable'),
					icon: 'ui-icon ui-icon-triangle-2-n-s',
					shortcut: (ui.column.sortable ? 'ON' : 'OFF'),
					tooltip: 'PRO option',
					action: function(evt, ui) {
						ui.column.sortable = ui.column.sortable ? 0 : 1;
						builder.edited = true;
						var colModel = builder.grid.getColModel(),
							col = colModel[ui.colIndx];
						if (col) {
							col.prop.pyt['sortable'] = ui.column.sortable ? 1 : 0;
							builder.addColumnSettings(col);
						}

						this.refresh();
					}
				},
			];
	}

	BuilderModel.prototype.getHeadCMenuLight = function(evt, ui) {
		return [
				
			];
	}

	BuilderModel.prototype.getBodyCMenu = function(evt, ui) {
		if (ui.colIndx == 0) return;
		var builder = app.pytTabModels.builder,
			page = builder.adminPage;
		return [
				{
					name: page.getLangString('builder', 'tbe-row-insert', 'Insert row'),
					icon: 'ui-icon ui-icon-plus',
					subItems: [
						{
							name: page.getLangString('builder', 'btn-above', 'above'),
							action: function(evt, ui) { builder.addRows([ui.rowIndx]); }
						},
						{
							name: page.getLangString('builder', 'btn-below', 'below'),
							action: function(evt, ui) { builder.addRows([ui.rowIndx + 1]); }
						}
					]
				},
				{
					name: page.getLangString('builder', 'tbe-row-delete', 'Delete row'),
					icon: 'ui-icon ui-icon-trash',
					action: function (evt, ui) { builder.deleteRows([ui.rowIndx]); }
				},
				'separator',
				{
					name: page.getLangString('builder', 'col-insert', 'Add column'),
					icon: 'ui-icon ui-icon-plus',
					subItems: [
						{
							name: page.getLangString('builder', 'btn-left', 'left'),
							action: function(evt, ui) { builder.addColumns([ui.colIndx], ui.colIndx); }
						},
						{
							name: page.getLangString('builder', 'btn-right', 'right'),
							action: function(evt, ui) { builder.addColumns([ui.colIndx], ui.colIndx + 1); }
						}
					]
				},
				{
					name: page.getLangString('builder', 'tbe-col-delete', 'Delete column'),
					icon: 'ui-icon ui-icon-trash',
					action: function (evt, ui) { builder.deleteColumns([ui.colIndx]); }
				},
				'separator',
				{
					name: page.getLangString('builder', 'btn-undo', 'Undo'),
					icon: 'ui-icon ui-icon-arrowrefresh-1-n',
					disabled: !this.History().canUndo(), 
					action: function(evt, ui){
						this.History().undo();
					}
				},
				{
					name: page.getLangString('builder', 'btn-redo', 'Redo'),
					icon: 'ui-icon ui-icon-arrowrefresh-1-s',
					disabled: !this.History().canRedo(), 
					action: function(evt, ui){
						this.History().redo();
					}
				},
				'separator',
				{
					name: page.getLangString('builder', 'btn-copy', 'Copy'),
					icon: 'ui-icon ui-icon-copy',
					shortcut: 'Ctrl - C',
					tooltip: 'Works only for copy / paste within the same grid',
					action: function(){
						this.copy();
					}
				},
				{
					name: page.getLangString('builder', 'btn-paste', 'Paste'),
					icon: 'ui-icon ui-icon-clipboard',
					shortcut: 'Ctrl - V',
					disabled: !this.canPaste(),
					action: function(){                        
						this.paste();
					}
				},
				'separator',
				{
					name: page.getLangString('builder', 'btn-copyrange', 'Copy range'),
					icon: 'ui-icon ui-icon-extlink',
					action: function(){
						var range = builder.getSelectedRange();
						if (range != false) builder.copyToClipboard(range);
					}
				},
			];
	}
	BuilderModel.prototype.getBodyCMenuLight = function(evt, ui) {
		if (ui.colIndx == 0) return;
		var builder = app.pytTabModels.builder,
			page = builder.adminPage;
		return [
				{
					name: page.getLangString('builder', 'btn-copyrange', 'Copy range'),
					icon: 'ui-icon ui-icon-extlink',
					action: function(){
						var range = builder.getSelectedRange();
						if (range != false) builder.copyToClipboard(range);
					}
				},
				{
					name: page.getLangString('builder', 'btn-diagram', 'Copy range'),
					icon: 'ui-icon ui-icon-image',
					shortcut: page.isPro ? '' : '<div class="pubydoc-prolink">PRO</div>',
					action: function(){
						pytApplyHookAction(builder, 'addDiagramm');
					}
				},
			];
	}
	BuilderModel.prototype.copyToClipboard = function(str) {
		var $temp = $('<input>');
		$('body').append($temp);
		$temp.val(str).select();
		document.execCommand('copy');
		$temp.remove();
	}

	BuilderModel.prototype.getSelectedRange = function () {
		var grid = this.$obj.grid,
			range = grid.Selection().address();
		if (range.length) {
			range = range[0];
			var c1 = pq.toLetter(range.c1),
				c2 = pq.toLetter(range.c2);

			if (range.type == 'column' || range._type == 'column') {
				return c1 + ':' + c2;
			}
			return c1 + (range.r1 + 1) + ':' + c2 + (range.r2 + 1);
		}
		return false;
	}

	BuilderModel.prototype.initColSettingsForm = function () {
		var _this = this.$obj,
			grid = _this.grid,
			page = _this.adminPage,
			dColSettings = $('#pytDialogColSettings');

		_this.dialogColSettings = dColSettings.dialog({
				resizable: false,
				position: {my: 'center', at: 'center', of: '.pubydoc-main'},
				maxHeight: 700,
				height: 'auto',
				width: '400px',
				modal: true,
				autoOpen: false,
				dialogClass: 'pubydoc-plugin',
				classes: {
					'ui-dialog': 'pubydoc-plugin'
				},
				buttons: [
					{
						text: page.getLangString('builder', 'btn-save'),
						class: 'button button-secondary pyt-save',
						click: function() {
							var $this = $(this),
								colInx = _this.selectedColumn,
								isNew = colInx < 0,
								colModel = grid.colModel,
								column = isNew ? null : colModel[colInx],
								pytProps = isNew ? {} : $.extend(true, {}, _this.convertPropsToObject(column.prop.pyt));
							$this.find('[data-prop]').each(function() {
								var $elem = $(this);
								if ($elem.closest('.pytHidden').length == 0) {
									var props = $elem.attr('data-prop').split('.'),
										prop = props[0],
										sub = props.length > 1 ? props[1] : '',
										value = '';
									if ($elem.is('input[type="radio"]')) {
										if ($elem.is(':checked')) value = $elem.val() ;
									}
									else {
										value = $elem.is('input[type="checkbox"]:not(:checked)') ? ($elem.is('[data-notchecked]') ? $elem.data('notchecked') : 0) : $elem.val();
									}

									if (value == '' || value == null) {
										if ($elem.is('[data-default]')) value = $elem.attr('data-default');
										else value = '';
									}

									if (sub.length) {
										var subsub = props.length > 2 ? props[2] : '';
										if (!(prop in pytProps) || typeof(pytProps[prop]) != 'object') pytProps[prop] = {};
										if (subsub.length) {
											if (!(sub in pytProps[prop]) || typeof(pytProps[prop][sub]) != 'object') pytProps[prop][sub] = {};
											if (value == '' && pytProps[prop][sub]) delete pytProps[prop][sub][subsub];
											else pytProps[prop][sub][subsub] = value;
										} else {
											if (value == '') delete pytProps[prop][sub];
											else pytProps[prop][sub] = value;
										}
									} else {
										pytProps[prop] = value;
									}
								}
							});
							if (isNew) {
								var lastCol = colModel.length - 2;
								_this.addColumns([lastCol], lastCol + 1, pytProps);
							} else {
								pytProps.edit = true;
								grid.Range({ c1: colInx }).prop('pyt', pytProps);
								_this.addColumnSettings(column);
								grid.refreshView();	
							}
							if (!_this.isEqualProps(_this.oldPytProps, pytProps, ['pytType', 'pytFormat'])) {
								_this.changeFormat = true;
								_this.changeFormatType = 'column';
							}
							_this.edited = true;
							$(this).dialog('close');
						}
					},
					{
						text: page.getLangString('builder', 'btn-cancel'),
						class: 'button button-secondary',
						click: function() {
							$(this).dialog('close');
						}
					}
				],
				open: function(event, ui) {
					var $this = $(this),
						colInx = _this.selectedColumn,
						isNew = colInx < 0,
						column = isNew ? _this.defaultColumn : _this.grid.colModel[colInx],
						pytProps = column.prop.pyt;

					if ($this.find('#cHeaderFontSize option').length == 0) {
						var $hFontSize = $this.find('#cHeaderFontSize'),
							$bFontSize = $this.find('#cBodyFontSize');
						$('#tbeFontSize option').each(function() {
							$hFontSize.append($(this).clone());
							$bFontSize.append($(this).clone());
						});
						if ($('#tbeFontFamily').length) {
							var $cFontFamily = $this.find('#cHeaderFontFamily'),
								$bFontFamily = $this.find('#cBodyFontFamily');
							$('#tbeFontFamily option').each(function() {
								$cFontFamily.append($(this).clone());
								$bFontFamily.append($(this).clone());
							});
						}
					}


					$this.find('.pyt-save').html(page.getLangString('builder', isNew ? 'col-add' : 'btn-save'));
					_this.oldPytProps = $.extend(true, {}, _this.convertPropsToObject(pytProps));
					$tabs.eq(0).trigger('click');

					$this.find('[data-prop]').each(function() {
						var $elem = $(this),
							value = '';
						if ($elem.is('[data-default]'))	value = $elem.attr('data-default');
						var props = $elem.attr('data-prop').split('.'),
							prop = props[0],
							sub = props.length > 1 ? props[1] : '',
							curValue = prop in pytProps ? pytProps[prop] : (prop in column ? column[prop] : null);
						if (curValue != null) {
							var isObj = typeof(curValue) == 'object',
								subsub = sub.length && props.length > 2 ? props[2] : '';
							if (subsub.length) {
								value = isObj && curValue[sub] && curValue[sub][subsub] ? curValue[sub][subsub] : '';
							} else {
								value = sub.length ? (isObj && sub in curValue ? curValue[sub] : '') : (isObj ? '' : curValue);
							}
						}

						if($elem.is('input[type="text"]') || $elem.is('input[type="number"]')) $elem.val(value).trigger('change');
						else if($elem.is('select')) $elem.val(value).trigger('pytChange');
						else if($elem.is('input[type="radio"]') || $elem.is('input[type="checkbox"]')) $elem.prop('checked', $elem.is('[value="' + (value === true ? 1 : value) + '"]'));
						else $elem.val(value);
					});
					pytApplyHookAction(_this, 'addColSettingsForm', dColSettings, pytProps);
				},
				create: function( event, ui ) {
					$(this).parent().css('maxWidth', $(window).width()+'px');
				}
			});

		var $tabsContent = dColSettings.find('.block-tab'),
			$tabs = dColSettings.find('.tbs-col-tabs .button'),
			$currentTab = $tabs.filter('.current').attr('href');
			$tabsContent.filter($currentTab).addClass('active');

		$tabs.off('click').on('click', function (e) {
			e.preventDefault();
			var $this = $(this),
				$curTab = $this.attr('href');

			$tabsContent.removeClass('active');
			$tabs.filter('.current').removeClass('current');
			$this.addClass('current');

			$tabsContent.filter($curTab).addClass('active');
		});
		dColSettings.find('select').on('change pytChange', function (e) {
			var $this = $(this),
				childs = dColSettings.find('div[data-parent="' + $this.attr('data-id') + '"]');
			if (childs.length) {
				childs.addClass('pytHidden');
				childs.filter('[data-parent-value~="' + $this.val() + '"]').removeClass('pytHidden');
			}
		});
	}
	BuilderModel.prototype.isEqualProps = function(oObj, nObj, props) {
		if (oObj && typeof(oObj) == 'object' && nObj && typeof(nObj) == 'object') {
			var props = typeof(props) == 'undefined' ? Object.getOwnPropertyNames(oObj).concat(Object.getOwnPropertyNames(nObj)) : props;
			for (var p = 0; p < props.length; p++) {
				var prop = props[p],
					oldVal = pytCheckSettings(oObj, prop),
					newVal = pytCheckSettings(nObj, prop),
					newType = typeof(newVal);
				if (typeof(oldVal) == newType) {
					if (newType == 'object') {
						for (var s in newVal) {
							if (!(s in oldVal) || oldVal[s] != newVal[s]) return false;
						}
					} else return oldVal == newVal;
				} else return false;
			}
		} else return false;
		return true;
	}
	BuilderModel.prototype.convertPropsToObject = function(obj) {
		if (Array.isArray(obj)) obj = $.extend(true, {}, obj);
		for(var p in obj) {
			if (Array.isArray(obj[p])) obj[p] = $.extend({}, obj[p]);
		}
		return obj;
	}

	BuilderModel.prototype.saveChanges = function() {
		var _this = this.$obj,
			grid = _this.grid,
			formatModel = _this.formatModel,
			colModel = grid.option('colModel'),
			gridChanges = grid.getChanges({ format: 'byVal', all: true }),
			rowChanged = _this.rowChanged,
			isAll = rowChanged == 'all',
			format = _this.changeFormat,
			fullFormat = format && _this.changeFormatType == 'column',
			stepChunk = _this.loadStep,
			byPart = false;

		if (fullFormat && _this.remote) {
			_this.changeFormatRemote = true;
		}
		gridChanges['cleanFormats'] = fullFormat ? 1 : 0;

		if (_this.changeFormatRemote) {
			format = true;
			fullFormat = true; 
		}

		if (rowChanged.length || format) {
			var data = grid.getData(),
				pChunk = 0,
				fChunk = 0,
				pCnt = 0,
				fCnt = 0,
				props = [{}],
				formated = [{}],
				params = _this.cellParams;
			$.each(data, function() {
				var id = this.id,
					changed = isAll || (rowChanged.indexOf(id) != -1);
				if (changed || fullFormat) {
					var row = this,
						strId = 'id_' + id;
					if (changed) {
						var fields = {};
						$.each(params, function() {
							if (row.hasOwnProperty(this) && !pytIsEmptyValue(row[this])) fields[this] = _this.convertPropsToObject(row[this]);
						});
						if (Object.keys(fields).length) {
							props[pChunk][strId] = fields;
							pCnt++;
							if (stepChunk > 0 && pCnt >= stepChunk) {
								pChunk++;
								props[pChunk] = {};
								pCnt = 0;
							}
						}
					}
					if (format) {
						var fields = {};
						for (var i in colModel) {
							var col = colModel[i].dataIndx;
							if (col != 'id') {
								var typFormat = _this.getCellTypeFormat(row, colModel[i]);
								fields[col] = formatModel.formatValue(row[col], typFormat.type, typFormat.format);
							}
						}
						if (Object.keys(fields).length) {
							formated[fChunk][strId] = fields;
							fCnt++;
							if (stepChunk > 0 && fCnt >= stepChunk) {
								fChunk++;
								formated[fChunk] = {};
								fCnt = 0;
							}
						}
					}
				}
			});
			if (Object.keys(props).length) {
				if (pChunk > 0) byPart = true;
				else gridChanges['propList'] = props[0];
			}
			if (!byPart && Object.keys(formated).length) {
				if (fChunk > 0) byPart = true;
				else gridChanges['formated'] = formated[0];
			}
		}

		var sortModel = grid.option('sortModel'),
			mergeCells = _this.getMergedCells(grid),
			cntOrders = _this.rowOrders.length;


		_this.setColumnModel(colModel.slice(), _this.lettersMode ? _this.realColNames : []);
		_this.setSortModel($.extend({}, sortModel));
		_this.setMergeCells(mergeCells.slice());
		gridChanges['offset'] = _this.remote ? grid.riOffset : 0;

		if (cntOrders > 0) {
			gridChanges['orderList'] = _this.rowOrders[cntOrders - 1];
		}

		_this.adminPage.saving = true;
		_this.showLoading();
		_this.adminPage.setNeedPreview();

		var tuning = pytApplyHookFilter(_this, 'addTuningParams', {sorter: _this.sortModel, merge: _this.mergeCells});

		$.sendFormPyt({
			btn: $('#pytBtnSave'),
			data: {
				mod: 'tables',
				action: 'saveTableData',
				tableId: _this.adminPage.tableId,
				builder: _this.builderType,
				colModel:  JSON.stringify(_this.colModel),
				tuning: JSON.stringify(_this.convertPropsToObject(tuning)),
				list: JSON.stringify(gridChanges),
			},
			onSuccess: function(res) {
				if (!res.error) {

					if (byPart) {
						var done = true,
							ajaxPromise = new $.Deferred().resolve();

						if (props) {
							$.each(props, function (index, data) {
								if (Object.keys(data).length) {
									ajaxPromise = ajaxPromise.then(function() {
										return $.sendFormPyt({data: {mod: 'tables',	action: 'saveTableData', tableId: _this.adminPage.tableId, partSave: 1,	list: JSON.stringify({propList: data})}});
									},function() {
										if (done) {
											done = false;
											alert('Failed to save table data: There are errors during the request');
										}
									});
								}
							});
						}
						if (formated) {
							$.each(formated, function (index, data) {
								if (Object.keys(data).length) {
									ajaxPromise = ajaxPromise.then(function() {
										return $.sendFormPyt({data: {mod: 'tables',	action: 'saveTableData', tableId: _this.adminPage.tableId, partSave: 1,	list: JSON.stringify({formated: data})}});
									},function() {
										if (done) {
											done = false;
											alert('Failed to save table data: There are errors during the request');
										}
									});
								}
							});
						}
						ajaxPromise = ajaxPromise.then(function() {
							_this.afterSaveChanges(gridChanges);
						});
					} else _this.afterSaveChanges(gridChanges);
					
				}
			}
		});
		
	}

	BuilderModel.prototype.aiaxSaveChanged = function (gridChanges, tuning) {
		var _this = this.$obj,
			saveStep = _this.loadStep;

			if(byPart) {
				var self = this,
					step = ((typeof app.Models.Tables.step != 'undefined') && parseInt(app.Models.Tables.step)) ? parseInt(app.Models.Tables.step) : 400,
					done = true,
					ajaxPromise = new $.Deferred().resolve(),
					rowsChunks = app._getChunksArray(rows, step),
					rowsData = [];

				for(var i = 0; i < rowsChunks.length; i++) {
					rowsData.push({
						id: id,
						step: step,
						last: i == (rowsChunks.length - 1) ? 1 : 0,
						rows: this._prepareData(rowsChunks[i]) })
				}

				$.each(rowsData, function (index, data) {
					ajaxPromise = ajaxPromise.then(function() {
						data._maxIter = 3;
						return self.request('updateRows', data);
					},function() {
						if(done) {
							done = false;
							alert('Failed to save table data: There are errors during the request');
						}
					});
				});
				ajaxPromise = ajaxPromise.then(function() {
					self.endSave();
				});
			} else {
				return this.request('updateRows', { id: id, rows: this._prepareData(rows) });
			}
		};

	BuilderModel.prototype.afterSaveChanges = function(changes) {
		var _this = this.$obj,
			grid = _this.grid;

		if (changes.addList && changes.addList.length) {
			grid.refreshDataAndView();
		} else {
			grid.commit({ type: 'add', rows: changes.addList });
			grid.commit({ type: 'update', rows: changes.updateList });
			grid.commit({ type: 'delete', rows: changes.deleteList });
		}
		_this.rowChanged = [];
		_this.changeFormat = false;
		_this.changeFormatType = '';
		grid.history({ method: 'reset' });

		_this.edited = false;
		_this.saved = true;
		_this.adminPage.saving = false;
		_this.hideLoading();
	}
	BuilderModel.prototype.showLoading = function() {
		this.$obj.grid.showLoading();
	}
	BuilderModel.prototype.hideLoading = function() {
		this.$obj.grid.hideLoading();
	}
	BuilderModel.prototype.showContentLoading = function () {
		var _this = this.$obj;
		_this.builderContent.addClass('pytHidden');
		_this.builderLoader.removeClass('pytHidden');
	}
	BuilderModel.prototype.hideContentLoading = function () {
		var _this = this.$obj;
		_this.builderContent.removeClass('pytHidden');
		_this.builderLoader.addClass('pytHidden');
	}

// ********************* BUILDER MODEL **************************** //	
// ================================================================ //
	

	if (typeof app.pytTabModels == 'undefined') app.pytTabModels = {};
	app.pytTabModels.builder = new BuilderModel();
	
}(window.jQuery, window));
