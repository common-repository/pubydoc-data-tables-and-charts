(function ($, app) {
"use strict";
	var TablesFront = app.pytTables,
		FormatModel = app.pytFormats;
	app.pytImportedFonts = [],
	app.pytStyleFontsId = 'pyt-table-fonts';
	pytCreateStyleElem(pytStyleFontsId);

	FormatModel.constructor.prototype.buttonFormatValue = function (data, format) {
		return data.length ? '<a href="' + data + '" class="button pyt-field-button ' + format.pytClass + '" target="' + format.target + '">' + format.text + '</a>' : '';
	}

	TablesFront.constructor.prototype.beforeInitTables = function ($table) {
		var _this = this.$obj;
		_this.condModel = app.pytConditions;
		_this.condModel.init();
	}

	TablesFront.constructor.prototype.beforeInitTable = function ($table) {
		var _this = this.$obj,
			viewId = $table.data('view-id'),
			tSettings = _this.getTableSettings(viewId);
		tSettings.lightbox = $table.attr('data-lightbox') == '1';
		tSettings.editable = $table.attr('data-editable') == '1';
		tSettings.saveEditableFields = !tSettings.isPreview && $table.attr('data-save-efields') == '1';
		tSettings.markEditableFields = tSettings.editable && $table.attr('data-mark-efields') == '1';
		tSettings.collapsibleRows = $table.data('collapsible-rows');
		tSettings.allowedFileTypes = $table.data('allowed-files');
		tSettings.exportTypes = $table.data('export');
		tSettings.exportPosition = $table.attr('data-export-position');
		tSettings.exportVisible = $table.attr('data-export-visible') == '1';
		tSettings.pdfSize = $table.attr('data-pdf-size');
		tSettings.pdfOrientation = $table.attr('data-pdf-orientation');
		tSettings.exportLogo = $table.data('export-logo');

		tSettings.saveFieldsTimeout = false;
		tSettings.editedCells = [];


		$('#pyt-table-' + viewId + '-wrapper').each(function() {
			var $this = $(this),
				fonts = $this.data('fonts');
			if (fonts.length) _this.importFonts(fonts);	
		});

		var conditions = $table.data('conditions');
		if (conditions)	{
			_this.condModel.setCondsData(conditions, viewId);
		}
		_this.initCollapsibleRows($table, tSettings);
		if (!tSettings.ssp) _this.initTablePageData($table, tSettings);
	}

	TablesFront.constructor.prototype.initTablePageData = function ($table, tSettings) {

		var _this = this.$obj,
			columns = tSettings.colProps;

		_this.setImgLightbox(tSettings);
		
		for (var c = 0; c < columns.length; c++) {
			if (columns[c].front && columns[c].front != '') $table.find('tr td:nth-child(' + (c + 1) + '):not([data-front])').attr('data-front', columns[c].front);
		}
		if (tSettings.editable) _this.createEditableFields($table, tSettings);
		_this.setCollapsibleRows($table, tSettings);
		_this.calcConditions($table, tSettings);
		/*if (tSettings.ssp) {
			_this.formatCells(tSettings);
		}*/
		//_this.setTooltipCells($table);
		_this.drawTableDiagrams($table);
	}

	//only by SSP
	TablesFront.constructor.prototype.afterPageLoad = function (tSettings, fonts) {
		this.$obj.importFonts(fonts);
	}
	TablesFront.constructor.prototype.afterRedrawTable = function ($table) {
		var _this = this.$obj,
			viewId = $table.data('view-id'),
			tSettings = _this.getTableSettings(viewId);
		_this.setTooltipCells($table);
		_this.initTablePageData($table, tSettings);	
	}

	TablesFront.constructor.prototype.afterInitTable = function ($table) {
		var _this = this.$obj,
			viewId = $table.data('view-id'),
			tSettings = _this.getTableSettings(viewId);

		if (!tSettings.ssp) {
			_this.setImgLightbox(tSettings);
			//_this.setTooltipCells($table);
		}
		_this.setTooltipCells($table);
		_this.drawTableDiagrams($table);
	}

	TablesFront.constructor.prototype.initCollapsibleRows = function ($table, tSettings) {
		if (tSettings.responsiveMode == 1 || !tSettings.collapsibleRows || tSettings.collapsibleRows.length == 0) return;
		var _this = this.$obj;
		$table.closest('.pyt-table-wrap').on('click', 'tr.pyt-collapsible-main>td:first-child', function() {
			_this.toggleCollapsibleRows($table, tSettings, $(this).closest('tr'), false);
		});
	}
	TablesFront.constructor.prototype.toggleCollapsibleRows = function ($table, tSettings, mainRow, close) {
		if (!mainRow || mainRow.length == 0) return;

		var _this = this.$obj,
			mainId = mainRow.attr('data-id'),
			openClass = 'pyt-collapsible-open';

		if (mainRow.hasClass(openClass) || close) {
			mainRow.removeClass(openClass);
			var	children = tSettings.collapsibleRows['id_' + mainId] || [];
			$.each(children, function(i, id) {
				$table.find('tr[data-id="'+id+'"').addClass('pyt-collapsed').addClass('pyt-collaps-'+mainId);
			});

		} else {
			mainRow.addClass(openClass);
			$table.find('tr.pyt-collaps-'+mainId).each(function() {
				var row = $(this).removeClass('pyt-collaps-'+mainId);
				if (!row.is('[class*="pyt-collaps-"]')) row.removeClass('pyt-collapsed');
			});
		}
		if (!close) {
			_this.redrawMergedCells(tSettings);
			var table = _this.getTableInstance($table.data('view-id'));
			table.columns.adjust();
		}
	}

	TablesFront.constructor.prototype.setCollapsibleRows = function ($table, tSettings) {
		if (tSettings.responsiveMode == 1 || !tSettings.collapsibleRows || tSettings.collapsibleRows.length == 0) return;
		var _this = this.$obj,
			collapsible = tSettings.collapsibleRows,
			id;

		for (var mainId in collapsible) {
			id = mainId.replace('id_', '');
			_this.toggleCollapsibleRows($table, tSettings, $table.find('tr[data-id="' + id + '"]').addClass('pyt-collapsible-main'), true);
		}
	}

	TablesFront.constructor.prototype.drawTableDiagrams = function ($table) {
		if (app.pytDiagram) app.pytDiagram.refreshTableDiagrams($table);
		else {
			setTimeout(function() {
				if (app.pytDiagram) app.pytDiagram.refreshTableDiagrams($table);
			}, 100);
		}
	}

	TablesFront.constructor.prototype.addedConfigFeatures = function (config, $table) {
		var _this = this.$obj,
			viewId = $table.data('view-id'),
			tSettings = _this.getTableSettings(viewId);
		if (tSettings.exportTypes && tSettings.exportTypes.length) {

			var dom = config['dom'] || 'lfrtip',
				buttons = [];
			if (tSettings.exportPosition == 'before') config['dom'] = 'B' + dom;
			else config['dom'] = dom + 'B';

			for (var i = 0; i < tSettings.exportTypes.length; i++) {
				var typ = tSettings.exportTypes[i],
					head = tSettings.head ? 1 : 0,
					exportParams = {excel: {excel_header: head}, csv: {csv_delim: ';', csv_header: head}};
				switch (typ) {
					case 'xls':
					case 'xlsx':
					case 'csv':
						buttons.push({
							text: typ,
							export_type: typ,
							export_visible: tSettings.exportVisible,

							action  : function(e, dt, button, config) {
								var exportType = config.export_type,
									typ = exportType == 'csv' ? exportType : 'excel',
									params = exportParams[typ] ? exportParams[typ]: {},
									rows = 'all';
								params[typ + '_type'] = exportType;
								params['front'] = true;
								if (config.export_visible) {
									rows = [];
									var id;
									this.rows({order: 'current', page: 'current', search: 'applied'}).nodes().to$().each(function() {
										id = $(this).data('id');
										rows.push(typeof(id) == 'object' && id[0] ? id[0] : id);
									});
								}

								$.sendFormPyt({
									btn: $(button),
									data: {
										mod: 'export',
										action: 'generateUrl',
										tableId: tSettings.tableId,
										rows: rows,
										type: typ,
										params: JSON.stringify(params),
									},
									onSuccess: function(res) {
										if (!res.error && res.url) {
											window.location.href = res.url;
										}
									}
								});           				
							},
						});

						break;
					case 'pdf':
						var modifier = {};
						if (tSettings.exportVisible) {
							modifier = { 
								page: 'current',
								search: 'applied',
								order: 'applied'
							}
						}

						buttons.push({
							extend: 'pdf',
							exportOptions: {
								modifier: modifier
							},
							orientation: tSettings.pdfOrientation,
							pageSize: tSettings.pdfSize,
						});
						break;
					default:
						buttons.push({
							extend: typ
						});
						break;
				}
			}
			config['buttons'] = buttons;
		}
		
		return config;
	}
	TablesFront.constructor.prototype.importFonts = function (fonts) {
		var _this = this.$obj;
		fonts = typeof(fonts) == 'string' ? [fonts] : fonts;
		for (var i = 0; i < fonts.length; i++) {
			var font = fonts[i];
			if (font.length == 0 || font == 'inherit') return;
			if (pytImportedFonts.indexOf(font) != -1) return;
			pytStyleSheetImportFF(pytStyleFontsId, font);
			pytImportedFonts.push(font);
		}
	}
	

	TablesFront.constructor.prototype.setImgLightbox = function(tSettings) {
		if(tSettings.lightbox) {
			setTimeout(function(){
				$('#pyt-table-' + tSettings.viewId + '-wrapper').find('th img, td img').each(function() {
					var $this = $(this);
					if (!$this.parent().is('a')) {
						var url = $this.data('full') || $this.attr('src');
						$this.attr('href', url).attr('data-featherlight', 'image');
					}
				});
			}, 250);
		}
	}

	TablesFront.constructor.prototype.formatCellPro = function($td, tSettings, first) {
		var _this = this.$obj;
		if (!_this.condModel.conditions[_this.formatModel.tableViewId]) return;
		if (first) _this.condModel.setCondTable(_this.formatModel.tableViewId);

		var col = _this.getCellColumn($td),
			conds = tSettings.colProps[col].conds || '';
		if ($td.attr('data-conds')) conds += (conds.length ? ',' : '') + $td.attr('data-conds');
		if (conds.length) {
			_this.condModel.setConditionsToCell($td, conds);
		}
		return;
	}
	TablesFront.constructor.prototype.calcConditions = function($table, tSettings) {
		var _this = this.$obj,
			model = _this.condModel;
		if (!model.conditions[tSettings.viewId]) return;
		model.setCondTable(tSettings.viewId);

		var columns = tSettings.colProps,
			selector = 'tbody td[data-conds]';
		for (var c = 0; c < columns.length; c++) {
			if (columns[c].conds) selector += ', tbody td:nth-child(' + (c + 1) + ')';
		}
		$table.find(selector).each(function() {
			var $td = $(this),
				col = $td.index(),
				conds = columns[col].conds || '';
			if ($td.attr('data-conds')) conds += (conds.length ? ',' : '') + $td.attr('data-conds');
			if (conds.length) {
				model.setConditionsToCell($td, conds);
			}
		});
		return;
	}

	TablesFront.constructor.prototype.createEditableFields = function($table, tSettings) {
		var _this = this.$obj,
			viewId = $table.data('view-id'),
			$tableWrapper = $('#pyt-table-' + viewId + '-wrapper'),
			$fields = $table.find('td[data-front="editable"]');

		if($fields.length) {
			var $elArea = $tableWrapper.find('.editable-input'),
				$elFile = $tableWrapper.find('.editable-file'),
				$elSelect = $tableWrapper.find('.editable-select'),
				$elDate = $tableWrapper.find('.editable-date');
			$fields.find('.pyt-file-delete').css('display', 'inline-block');

			if(!$elArea.length) {
				$elArea = $('<textarea class="pyt-editable-field editable-input" />');
				$elArea.css({textIndent: '0', padding: '5px', overflowY: 'auto'});
				$elArea.appendTo($tableWrapper);
			}
			if(!$elFile.length) {
				$elFile = $('<input type="file" class="pyt-editable-field editable-file" />');
				$elFile.css({textIndent: '0', padding: '5px', overflowY: 'auto'});
				$elFile.appendTo($tableWrapper);
				$(tSettings.selector).off('click').on('click vclick tap', '.pyt-file-delete', function(){
					event.preventDefault();
					var	$td = $(this).closest('td');
					$td.html('');
					_this.updateTableCell('file', $tableWrapper.find('.editable-file'), $td, tSettings);
					return false;
				});
			}
			if(!$elSelect.length) {
				$elSelect = $('<select class="pyt-editable-field editable-select" />');
				$elSelect.appendTo($tableWrapper);
			}
			if(!$elDate.length && typeof $.datepicker == 'object') {
				$elDate = $('<input type="text" class="pyt-editable-field editable-date" />');
				$elDate.appendTo($tableWrapper);
				$elDate = $tableWrapper.find('.editable-date');
			}
			$(tSettings.selector).off('click.sup').on('click.sup', 'td[data-front="editable"]', function(event) {
				event.preventDefault();
				_this.showEditableCell($(this), tSettings);
			});
		}
	}

	TablesFront.constructor.prototype.setElemPosition = function($elem, $td) {
		var params = [
			{ selector: '.dataTables_scrollHead', scroll: true },
			{ selector: '.dataTables_scrollBody', scroll: true },
			{ selector: '.dataTables_scrollFoot', scroll: true },
			{ selector: '.DTFC_LeftHeadWrapper' },
			{ selector: '.DTFC_LeftBodyWrapper' },
			{ selector: '.DTFC_LeftFootWrapper' },
			{ selector: '.DTFC_RightHeadWrapper' },
			{ selector: '.DTFC_RightBodyWrapper' },
			{ selector: '.DTFC_RightFootWrapper' },
			{ selector: 'td.child' },
			{ selector: '.dataTables_wrapper' }
		], 
			isAutoHideCell = $td.is('.dtr-control'),
			isOneColumn = $td.closest('table').is('.oneColumn'),
			scrollTop, scrollLeft, position;

		for(var i = 0; i < params.length; i++) {
			var parent = $td.parents(params[i].selector + ':first');

			if(parent.length) {
				parent.append($elem);
				scrollTop = params[i].scroll ? parent.scrollTop() : 0;
				scrollLeft = 0;
				position = $td.position();
				var $pre = parent.find('.dt-buttons, .dataTables_filter'),
					pTop = 0;
				if (!isAutoHideCell && !isOneColumn && $pre.length) {
					$pre.each(function() {
						var $this = $(this),
							pPosition = $this.position();
						if (pPosition.top < position.top && pTop < $this.height()) pTop = $this.height();
					});
				}
				
				$elem.css({
					top: position.top + scrollTop + pTop,
					left: position.left + scrollLeft
				});
				break;
			}
		}
	}


	TablesFront.constructor.prototype.showEditableCell = function($td, tSettings) {
		var _this = this,
			formatModel = _this.formatModel,
			value = $td.attr('data-value'),
			typ = $td.attr('data-type'),
			format = $td.data('format'),
			formula = $td.data('formula'),
			cellType = false,
			isNumber = false,
			triggerEvent = ((typeof($.browser) != 'undefined' && $.browser.msie) || !!navigator.userAgent.match(/Trident\/7\./)) ? 'focusout' : 'blur';
		if (!typ) {
			var columns = tSettings.colProps,
				col = _this.getCellColumn($td);
			typ = columns[col].typ;
			format = columns[col].format;
		}		
		switch(typ) {
			case 'text':
			case 'textarea':
			case 'html':
				cellType = 'input';
				break;
			case 'number':
			case 'money':
			case 'percert':
			case 'convert':
				cellType = 'input';
				isNumber = true;
				break;
			case 'select':
				cellType = 'select';
				break;
			case 'file':
				cellType = 'file';
				break;
			case 'date':
				cellType = 'date';
				break;
		}
		if (!cellType) return;
		var $tableWrapper = $('#pyt-table-' + tSettings.viewId + '-wrapper'),
			$elem = $tableWrapper.find('.pyt-editable-field.editable-' + cellType),
			keyEvent = true;
		if ($elem.length == 0) return;

		switch(cellType) {
			case 'input':
				$elem.off(triggerEvent).on(triggerEvent, function() {
					if (isNumber) $elem.val($elem.val().replace(',', '.'));
					_this.updateTableCell(cellType, $elem, $td, tSettings);
				});
				break;
			case 'select':
				$elem.html('');
				var source = (format ? format : '').split('\n');
				for(var i = 0; i < source.length; i++) {
					var selected = source[i] == value ? 'selected="selected"' : '';
					$elem.append('<option ' + selected + '>' + source[i] + '</option>');
				}
				$elem.off(triggerEvent).on(triggerEvent, function() {
					_this.updateTableCell(cellType, $elem, $td, tSettings);
				});
				keyEvent = false;
				break;
			case 'date': 
				format = 'yy-mm-dd';
				$elem
					.val(value)
					.datepicker({
					changeMonth: true,
					changeYear: true,
					dateFormat: format,
					showAnim: '',
					onSelect: function () {
						this.firstOpen = true;
					},
					onClose: function () {
						_this.updateTableCell(cellType, $elem, $td, tSettings);
					}
				});
				break;
			case 'file':
				$elem.off('change click').on('change click', function(e) {
					if (e.originalEvent != null) {
						var file = typeof e.target.files[0] !== 'undefined' ? e.target.files[0] : {type:'pyt', name:''},
							message = '',
							file_ext = file.name.slice((Math.max(0, file.name.lastIndexOf(".")) || Infinity) + 1).toLowerCase();

						if (file.type === 'pyt') {
							$elem.hide();
						} else if (toeInArrayPyt(file_ext, tSettings.allowedFileTypes)) {
							var fileData = new FormData();
							fileData.append('cellFile', file);
							fileData.append('mod', 'tablespro');
							fileData.append('action', 'uploadFileData');
							$('*').css({cursor:'wait'});
							$.sendFormPyt({
								form: fileData,
								ajax: {
									cache: false,
									contentType: false,
									processData: false
								},
								onComplete: function (res) {
									$('*').css({cursor:''});
								},
								onSuccess: function(res) {
									if (!res.error && res.html) {
										$td.html(res.html);
										setTimeout(function(){
											_this.updateTableCell(cellType, $elem, $td, tSettings);
											$td.find('.pyt-file-delete').css('display', 'inline-block');
										}, 500);
									}
								}
							});
						} else {
							$elem.hide();
							$.sNotify({
								'error': true,
								'icon': 'fa fa-exclamation-circle',
								'content': '<span> '+file_ext + ' is not allowed file type!</span>',
								'delay' : 5000
							});
						}
					} else {
						document.body.onfocus = function() {
							document.body.onfocus = null;
							setTimeout(function(){
								if ($elem.val().length === 0) {
									$elem.hide();
								}
							}, 200);
						}
					}
				});
				$elem.click();
				keyEvent = false;
				break;
			}

		_this.setElemPosition($elem, $td);

		// Set element CSS styles
		$elem.css({
			width: $td.innerWidth() + 1,	// To cover cell right border
			height: $td.innerHeight() + 1,	// To cover cell bottom border
			minHeight: $td.innerHeight() + 1,
			fontSize: $td.css('font-size'),
			lineHeight: $td.css('line-height')
		});
		// To remove unneeded zeros from end of percent values, e.g. 15.230000000001
		/*if(!formula && $td.data('cell-format-type') == 'percent') {
			value = this._preparePercentValue(value, $td.data('cell-format'));
		}*/
		if(keyEvent) {
			$elem.off('keypress').on('keypress', function(event) {
				if ((event.keyCode || event.which) == 13) {	// Enter button
					event.preventDefault();
					var $this = $(this);
					$this.trigger(triggerEvent);
					if(cellType == 'date') {
						$this.datepicker('hide');
					}
					return true;
				}
			});
			$elem.val(value);
		}
		$elem.off('keydown').on('keydown', function(event) {
			if ((event.keyCode || event.which) == 9) {	// Tab button
				event.preventDefault();
				var $this = $(this),				
					$fields = $(tSettings.selector + ' td[data-front="editable"]'),
					nextIndex = pytNextVisibleIndex($fields, $fields.index($td) + 1);

				$this.trigger(triggerEvent);
				if(cellType == 'date') {
					$this.datepicker('hide');
				}
				if(nextIndex > 0) {
					$fields.eq(nextIndex).trigger('click');
				}
			}
		});
		$elem.show().focus().select();
	}
	TablesFront.constructor.prototype.updateTableCell = function(cellType, $elem, $td, tSettings) {
		var _this = this,
			newValue = cellType == 'file' ? $td.html() : typeof $elem !== 'undefined' ? $.trim($elem.val()) : '',
			oldValue = $td.attr('data-value'),
			formula = typeof $td.data('formula') != 'undefined',
			tableInstance = _this.getTableInstance(tSettings.viewId),
			tableId = tSettings.tableId;

		$td.attr('data-value',newValue);

		$elem.val('');
		$elem.hide();
		$td.html(newValue);
		if (tSettings.markEditableFields) {
			$(tSettings.selector + ' td.pyt-just-edited').removeClass('pyt-just-edited');
			$td.addClass('pyt-just-edited');
		}

		$td.attr('data-not-format', 1);
		var editedCells = _this.formatCells(tSettings);

		editedCells.push($td);

		for(var i = 0; i < editedCells.length; i++) {
			var td = editedCells[i];
			tSettings.editedCells.push({
				row: td.closest('tr').data('id'),
				col: tSettings.colNames[_this.getCellColumn(td)],
				ov: td.attr('data-value'),
				fv: td.html()
			});
		}


		if(tSettings.saveEditableFields && tSettings.editedCells.length > 0) {
			_this.saveEditableFields(tSettings);
		}


		if (app.pytDiagram && app.pytDiagram.dynamicDiagrams[tableId]) {

			var pytDiagram = app.pytDiagram,
				diagrams = pytDiagram.dynamicDiagrams[tableId],
				firstCol = tSettings.autoIndex == 'new' ? 1 : 0;
			for (var id in diagrams) {
				var ranges = diagrams[id],
					minR = 0, maxR = 0, range, rF, rT, cF, cT,
					cntRows = tableInstance.rows().count(),
					cntCols = tableInstance.columns().count(),
					cols = [],
					raw = [];

				for (var i = 0; i < ranges.length; i++) {
					range = ranges[i];
					if (range['from'] && range['to']) {
						rF = range['from']['r'] || 1; 
						rT = range['to']['r'] || cntRows; 
						cF = range['from']['c'] || 1; 
						cT = range['to']['c'] || cntCols; 
						if (minR <= 0 || rF < minR) minR = rF;
						if (maxR <= 0 || rT > maxR) maxR = rT;
						for (var c = cF; c <= cT; c++) {
							cols['c'+c] = [c - 1, rF - 1, rT - 1];
						}
					}
				}
				if (minR > 0 && maxR > 0) {
					for (var r = minR - 1; r < maxR; r++) {
						var row = [], value;
						for (var c in cols) {
							value = '';
							if (cols[c][1] <= r && cols[c][2] >= r) {	
								var node = tableInstance.cell(r, cols[c][0] + firstCol).node();
								if (node) value = $(node).attr('data-value');
							}
							row.push(value);
						}
						raw.push(row);
					}
				}
				pytDiagram.rawData[id] = raw;
			}
			pytDiagram.refreshDynamicDiagrams(tableId);
		}
	}
	TablesFront.constructor.prototype.saveEditableFields = function(tSettings) {
		var _this = this.$obj;
		if(tSettings.saveFieldsTimeout) {
			setTimeout(function() {	_this.saveEditableFields(tSettings); }, 2000);
			return;
		}

		tSettings.saveFieldsTimeout = true;
		var cells = $.extend(true, {}, tSettings.editedCells);
		tSettings.editedCells = [];
		$.sendFormPyt({
			data: {
				mod: 'tablespro',
				action: 'saveEditableFields',
				tableId: tSettings.tableId,
				tableType: tSettings.tableType,
				cells: cells,

			},
			onComplete: function (res) {
				tSettings.saveFieldsTimeout = false;
			}
		});
	}

	TablesFront.constructor.prototype.setTooltipCells = function($table) {
		$table.closest('.pyt-table-wrap').find('td[data-front="tooltip"]').each(function() {
			var $td = $(this);
			if ($td.find('.wpfTooltip').length == 0) {
				var	content = $td.html(),
					icon = $('<i class="fa fa-info-circle wpfTooltip"></i>');

				$td.html(icon);
				icon.tooltipster({
					content: content,
					contentAsHTML: true,
					interactive: true,
					position: 'top-left',
					updateAnimation: true,
					animation: 'swing'
				});
			}
		});
	}

	TablesFront.constructor.prototype.resetProFeatures = function(id) {
		$('#pyt-table-' + id + '-wrapper i.wpfTooltip').each(function() {
			var $td = $(this).closest('td');
			$td.html($td.attr('data-value'));
		});
	}

// ================================================================ //
// ******************* CONDITIONS MODEL *************************** //
	function ConditionsModel() {
		this.types = {};
		this.opers = {};
		this.conditions = {};
		this.cls = 'pyt-cond-';
		this.tableViewId = false;
		this.$obj = this;
		return this.$obj;
	}
	ConditionsModel.prototype.init = function() {
		var _this = this.$obj,
			condParams = PYT_DATA.condParams;
		if (typeof(condParams) == 'object') {
			if (typeof(condParams['types']) == 'object') _this.types = condParams['types'];
			if (typeof(condParams['opers']) == 'object') _this.opers = condParams['opers'];
		}
	}
	ConditionsModel.prototype.setCondTable = function(tbl) {
		this.$obj.tableViewId = tbl;
	}
	ConditionsModel.prototype.setCondsData = function(rules, tbl) {
		var _this = this.$obj,
			tbl = typeof(tbl) == 'undefined' ? _this.tableViewId : tbl;
		_this.conditions[tbl] = rules;
	}
	ConditionsModel.prototype.getCondData = function(rule, tbl) {
		var _this = this.$obj,
			tbl = typeof(tbl) == 'undefined' ? _this.tableViewId : tbl;		
		return (tbl && _this.conditions[tbl] && _this.conditions[tbl][rule]) ? _this.conditions[tbl][rule] : false;
	}
	ConditionsModel.prototype.buildRegexp = function(regexp, args) {
		return regexp.replace(/{(\d+)}/g, function(match, number) {
			return typeof args[number] != 'undefined' ? args[number] : match;
		});
	};
	ConditionsModel.prototype.prepareToEval = function(text) {
		text = text !== undefined && text !== null ? text : '';
		return isNaN(parseFloat(text))
			? '"' + text.replace(/\'/g,"\\'").replace(/\"/g,'\\"') + '"'
			: parseFloat(text);
	};
	ConditionsModel.prototype.setConditionsToCell = function($td, conds) {
		var _this = this.$obj,
			text = $td.html(),
			value = $td.attr('data-value'),
			cls = '',
			classList = $td.attr('class');
		if (classList) {
			classList = classList.split(/\s+/);
			for(var c = 0; c < classList.length; c++) {
				if(classList[c].indexOf(_this.cls) > -1) $td.removeClass(classList[c]);
			}
		}

		conds = conds.split(',');
		for(var i = 0; i < conds.length; i++) {
			var rule = conds[i];
			if (_this.checkCondition(rule, text, value)) cls += _this.cls + rule + ' ';
		}
		if (cls.length) $td.addClass(cls);
		return;
	}
	ConditionsModel.prototype.checkCondition = function(ruleKey, cellText, cellValue) {
		var _this = this.$obj,
			rule = _this.getCondData(ruleKey);
		if (typeof(rule) != 'object' || !rule.type || !rule.oper || !_this.types[rule.type] || !rule.value) return false;

		var oper = rule.oper,
			value = rule.value,
			operValue = this.opers[oper].value;

		switch(rule.oper) {
			case 'begins':
			case 'ends':
				var regexp = new RegExp(this.buildRegexp(operValue, [value]));
				return regexp.exec(cellText);
				break;
			case 'contains':
				if(cellText) {
					cellText = cellText.toString();
					return cellText.indexOf(value) > -1;
				}
				break;
			case 'notContains':
				if(cellText) {
					cellText = cellText.toString();
					return cellText.indexOf(value) == -1;
				}
				break;
			case 'equals':
			case 'notEquals':
			case 'greater':
			case 'greaterOrEquals':
			case 'less':
			case 'lessOrEquals':
				var left = cellValue ? cellValue : cellText,
					right = value;

				right = this.prepareToEval(right);
				left = this.prepareToEval(left);
				if(eval(left + operValue + right)) return true;
				break;
			case 'between':
				cellValue = cellValue ? cellValue : cellText;
				var valuesArr = [cellValue].concat([value, rule.value2]),
					findNaN = false;

				for(var i = 0; i < valuesArr.length; i++) {
					if(isNaN(valuesArr[i])) {
						findNaN = true;
						break;
					} else {
						valuesArr[i] = parseFloat(valuesArr[i]);
					}
				}
				if(!findNaN && eval(this.buildRegexp(operValue, valuesArr))) {
					return true;
				}
				break;
			default:
				break;
		}
	return false;
	};
// ******************* CONDITIONS MODEL *************************** //
// ================================================================ //
	app.pytConditions = new ConditionsModel();

}(window.jQuery, window));
