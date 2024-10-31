(function ($, app) {
"use strict";
	var BuilderModel = app.pytTabModels.builder;

	BuilderModel.constructor.prototype.beforeInitGrid = function () {
		var _this = this.$obj,
			tableType = _this.adminPage.tableType;

		_this.sourceEdited = false;
		_this.sourceSettings = false;
		switch (tableType) {
			case 1:
				_this.initGoogleSettings();
				break;
			case 3:
				_this.initDatabaseSettings();
				break;
			default:
				break;
		}
		if (_this.sourceSettings) {
			_this.sourceSettings.find('input, select, textarea').on('change', function() {
				_this.sourceEdited = true;
				_this.edited = true;
			});
		}
	}

	BuilderModel.constructor.prototype.initPro = function () {
		var _this = this.$obj;
		_this.collapsibleRows = _this.getSettingValue('collapsibleRows', []);
		_this.fileTypeExts = PYT_DATA.fileTypeExts;
		_this.table.on('click', '.pyt-file-delete', function() {
			var range = _this.getSelected().address();
			if (range.length) {
				range = range[0];
				if (range.type == 'cell') {
					var row = {};
					row[_this.getColDataIndx(range.c1)] = '';
					_this.grid.updateRow({rowIndx: range.r1, newRow: row});
				}
			}			
		});

		_this.initConditions();
		_this.initImport();
		_this.initExport();
	}
	
	BuilderModel.constructor.prototype.addColumnSettingsPro = function (column) {
		var _this = this.$obj;
		if (_this.condInited && column.pytConds && column.pytConds != '') _this.setConditions(column.pytConds);
		if (column.style) _this.adminPage.importFont(column.style['font-family']);
		if (column.styleHead) _this.adminPage.importFont(column.styleHead['font-family']);
	}
	BuilderModel.constructor.prototype.initGridDataPro = function (data) {
		var page = this.$obj.adminPage;
		if (data.fonts) {
			var fonts = data.fonts;
			for(var f = 0; f < fonts.length; f++) {
				page.importFont(fonts[f]);
			}
		}
	}
	BuilderModel.constructor.prototype.buttonFormatSettings = function (column) {
		var _this = this.$obj,
			format = typeof(column.pytType.format) == 'object' ? column.pytType.format : {},
			colClass = 'pyt-field-' + column.dataIndx,
			colSelector = '.' + colClass;
		column.pytType.format.pytClass = colClass;
		column.pytType.format.target = pytCheckSettings(format, 'blank', false) ? '_blank' : '_self';
		pytSetStyleSheetRules(_this.adminPage.stylesElemId, [
			{selector: colSelector, param: 'background-color', value: pytCheckSettings(format, 'colorBg', false) ? format.colorBg + ' !important' : ''},
			{selector: colSelector, param: 'color', value: pytCheckSettings(format, 'colorText', false) ? format.colorText + ' !important' : ''},
			{selector: colSelector, param: 'border', value: pytCheckSettings(format, 'colorBorder', false) ? '1px solid ' + format.colorBorder + ' !important' : ''},
			{selector: colSelector, param: 'border-radius', value: pytCheckSettings(format, 'rounded', false) ? '10px !important' : ''},
		]);
		return column;
	}
	BuilderModel.constructor.prototype.selectEditor = function(ui, format) {
		var source = (typeof(format) == 'undefined' ? '' : format).split('\n');
		ui.$cell.addClass('ui-front');      
		ui.$editor.autocomplete({
			source: source,
			position: {
				collision: 'flipfit',
				within: ui.$editor.closest('.pq-grid')
			},
			minLength: 0
		}).focus(function () {
			$(this).autocomplete('search', '');
		});
	}
	BuilderModel.constructor.prototype.fileEditor = function(ui) {
		var _this = this.$obj,
			$input = ui.$cell.find('input');
		$input.on('change click', function(e) {
			if ($(this).attr('type') != 'file') return;

			if (e.originalEvent != null) {
				var file = typeof e.target.files[0] !== 'undefined' ? e.target.files[0] : {name: ''},
					fileExt = file.name.slice((Math.max(0, file.name.lastIndexOf(".")) || Infinity) + 1).toLowerCase();
				if (fileExt && _this.fileTypeExts.indexOf(fileExt) != -1) {
					var fileData = new FormData();
						fileData.append('cellFile', file);
						fileData.append('mod', 'tablespro');
						fileData.append('action', 'uploadFileData');
					_this.showLoading();
					$.sendFormPyt({
						form: fileData,
						ajax: {
							cache: false,
							contentType: false,
							processData: false
						},
						onComplete: function (res) {
							_this.adminPage.saving = false;
							_this.hideLoading();
						},
						onSuccess: function(res) {
							if (!res.error && res.html) {
								var row = {};
								row[ui.dataIndx] = res.html;
								_this.grid.updateRow({rowIndx: ui.rowIndx, newRow: row});
								_this.grid.quitEditMode();
								_this.grid.focus({rowIndxPage: ui.rowIndxPage, colIndx: ui.colIndx});
							}
						}
					});
				} else {
					_this.grid.quitEditMode();
					_this.grid.focus({rowIndxPage: ui.rowIndxPage, colIndx: ui.colIndx});
					pytShowAlert(fileExt + ' is not allowed file type!');
				}
			}
			document.body.onfocus = function() {
				document.body.onfocus = null;
				setTimeout(function(){
					if ($input.val().length === 0) {
						_this.grid.quitEditMode();
						_this.grid.focus({rowIndxPage: ui.rowIndxPage, colIndx: ui.colIndx});
					}
				}, 200);
			}
		});
	   $input.attr('type','file').click();
	   _this.grid.focus({rowIndxPage: ui.rowIndxPage, colIndx: ui.colIndx});
	}
	BuilderModel.constructor.prototype.addEditDataEvents = function ($container) {
		var _this = this.$obj;
		$container.on('click', '.pyt-file-delete', function(e) {
			$(this).closest('.pyt-file-wrap').empty();
		});
		$container.find('.pyt-field-file').on('change', function(e) {
			var $this = $(this),
				file = typeof e.target.files[0] !== 'undefined' ? e.target.files[0] : {name: ''},
				fileExt = file.name.slice((Math.max(0, file.name.lastIndexOf(".")) || Infinity) + 1).toLowerCase();
			if (fileExt && _this.fileTypeExts.indexOf(fileExt) != -1) {
				var fileData = new FormData();
					fileData.append('cellFile', file);
					fileData.append('mod', 'tablespro');
					fileData.append('action', 'uploadFileData');
				_this.showLoading();
				$.sendFormPyt({
					form: fileData,
					ajax: {
						cache: false,
						contentType: false,
						processData: false
					},
					onComplete: function (res) {
						_this.hideLoading();
					},
					onSuccess: function(res) {
						if (!res.error && res.html) {
							$this.parent().find('.pyt-file-wrap').html(res.html);
						}
					}
				});
			} else {
				pytShowAlert(fileExt + ' is not allowed file type!');
			}
		});
	}

	
	BuilderModel.constructor.prototype.addTuningParams = function (tuning) {
		var _this = this.$obj;
		tuning['conditions'] = _this.conditions;
		tuning['collapsibleRows'] = _this.collapsibleRows;
		return tuning;
	}
	BuilderModel.constructor.prototype.saveCollapsibleRows = function (rows) {
		var _this = this.$obj,
			collapsible = _this.collapsibleRows,
			ranges = rows.address();

		if (ranges.length) {
			for (var i = 0; i < ranges.length; i++) {
				var range = ranges[i],
					main = range.r1, id,
					mainId = 'id_' + _this.grid.getRowData({rowIndx: main})['id'];
				if (!collapsible[mainId]) collapsible[mainId] = [];

				for (var r = main + 1; r <= range.r2; r++) {
					id = _this.grid.getRowData({rowIndx: r})['id']
					if (collapsible[mainId].indexOf(id) == -1) collapsible[mainId].push(id);
				}
			}
		}
	}
	BuilderModel.constructor.prototype.deleteCollapsibleRows = function (rows) {
		var _this = this.$obj,
			collapsible = _this.collapsibleRows,
			ranges = rows.address();

		if (ranges.length) {
			for (var i = 0; i < ranges.length; i++) {
				var id, idx, range = ranges[i];
				for (var r = range.r1; r <= range.r2; r++) {
					id = _this.grid.getRowData({rowIndx: r})['id'];
					var mainId = 'id_' + id;
					if (mainId in collapsible && collapsible[mainId].length > 0) {
						var newMainId = 'id_' + collapsible[mainId][0];
						if (!collapsible[newMainId]) collapsible[newMainId] = [];
						for (var m = 1; m < collapsible[mainId].length; m++) {
							if (collapsible[newMainId].indexOf(collapsible[mainId][m]) == -1) collapsible[newMainId].push(collapsible[mainId][m]);
						}
						delete(collapsible[mainId]);
					}
					for (var mainId in collapsible) {
						idx = collapsible[mainId].indexOf(id);
						if (idx != -1) collapsible[mainId].splice(idx, 1);
					}

				}
			}
			for (var mainId in collapsible) {
				if (collapsible[mainId].length == 0) delete(collapsible[mainId]);
			}
		}
	}
	BuilderModel.constructor.prototype.renderCellPro = function(result, ui) {
		var _this = this.$obj;
		if (!_this.existConditions) return result;

		var row = ui.rowData,
			conds = _this.getCellConds(ui.rowData, ui.column);
		if (conds.length) {
			var cls = '',
				model = _this.condModel;
			conds = conds.split(',');
			for(var i = 0; i < conds.length; i++) {
				var rule = conds[i];
				if (model.checkCondition(rule, result.text, ui.formatVal)) cls += model.cls + rule + ' ';
			}
			if (cls.length) result.cls = cls;
		}
		return result;
	}

	BuilderModel.constructor.prototype.initConditions = function () {
		var _this = this.$obj,
			maxRuleId = 0,
			condModel = app.pytConditions,
			conditions = _this.getSettingValue('conditions', []);
		$.each(conditions, function(name) {
			var rule = parseInt(typeof this.ruleId == 'undefined' ? name.replace('rule', '') : this.ruleId);
			if (rule > maxRuleId) {
				maxRuleId = rule;
			}
		});
		_this.condRulePrefix = 'rule';
		_this.maxRuleId = maxRuleId;
		_this.conditions = conditions;
		_this.condTableId = 'admin';
		_this.condModel = condModel;
		_this.existConditions = false;
		_this.conditionsTmp = {};
		_this.setConditions();
		_this.addConditionsEvents($('#pytDialogColSettings .pyt-cond-form'));
		_this.condInited = true;
	}
	BuilderModel.constructor.prototype.getNewCondRuleIndex = function () {
		var _this = this.$obj;
		_this.maxRuleId++;
		return _this.maxRuleId;
	}
	BuilderModel.constructor.prototype.setConditions = function (rules) {
		var _this = this.$obj,
			rules = rules && rules.length ? rules.split(',') : false,
			conds = rules ? _this.conditionsTmp : _this.conditions;
		_this.existConditions = false;

		for (var ruleKey in conds) {
			if ((!rules || rules.indexOf(ruleKey) != -1) && typeof(conds[ruleKey]['styles']) == 'object') {
				var list = [],
					selector = '.' + _this.condModel.cls + ruleKey,
					styles = conds[ruleKey]['styles'];
				for (var stKey in styles) {
					if (styles[stKey]) list.push({selector: selector, param: stKey, value: styles[stKey] == '' ? '' : styles[stKey] + ' !important'});
				}
				if (list.length) pytSetStyleSheetRules(_this.adminPage.stylesElemId, list);
				if (rules) _this.conditions[ruleKey] = conds[ruleKey];
			}
			_this.existConditions = true;
		}
		_this.condModel.setCondTable(_this.condTableId);
		_this.condModel.setCondsData(_this.conditions);
	}
	BuilderModel.constructor.prototype.getCellConds = function(row, col) {
		var conds = ( col.pytConds ? col.pytConds : '' );
	
		if (row.pq_cellprop && row.pq_cellprop[col.dataIndx]) {
			var cellConds = row.pq_cellprop[col.dataIndx]['pytConds'];
			if (cellConds && cellConds.length) conds += (conds.length ? ',' : '') + cellConds;
		}
		return conds;
	}
	BuilderModel.constructor.prototype.resetCondForm = function ($wrapper, rules) {
		$wrapper.find('input[data-id="pytDataConds"]').val();
		$wrapper.find('[data-cond]').each(function() {
			var $this = $(this);
			if ($this.is('[type="checkbox"]')) $this.prop('checked', false);
			else $this.val($this.attr('data-default')).trigger('change');
		});
		if (rules) {
			$wrapper.find('.pyt-rules-wrapper li[data-rule-key]').remove();
			$wrapper.find('.pyt-rules-wrapper li.pyt-new-rule').addClass('current');
		}
	}
	BuilderModel.constructor.prototype.setCondLiData = function ($wrapper, $liRule, objRule) {	
		var opType = $wrapper.find('select[data-cond="type"] option[value="' + objRule.type + '"]'),
			opOper = $wrapper.find('select[data-cond="oper"] option[value="' + objRule.oper + '"]'),
			ruleKey = $liRule.attr('data-rule-key');
		$liRule.find('.pyt-rule-cond').html((opType.length ? opType.html() : '') + ' ' + (opOper.length ? opOper.html() : '') + ' ' + objRule.value + (objRule.value2 ? ' & ' + objRule.value2 : ''));
		//$liRule.find('input').attr('data-prop', 'pytConds.' + ruleKey);
		$liRule.find('.pyt-rule-preview').removeAttr('style').css(objRule.styles);
		this.$obj.conditionsTmp[ruleKey] = objRule;
	}
	BuilderModel.constructor.prototype.addConditionsEvents = function ($wrapper) {	
		var _this = this.$obj,
			$condType = $wrapper.find('select[data-cond="type"]'),
			$condOper = $wrapper.find('select[data-cond="oper"]'),
			$condValue = $wrapper.find('input[data-cond="value"]'),
			$condValue2 = $wrapper.find('input[data-cond="value2"]'),
			$rulesWrapper = $wrapper.find('.pyt-rules-wrapper');

		$condType.on('change', function(e) {
			var condType = $(this).val(),
				options = $condOper.find('option').addClass('pytHidden');
			options.filter('[data-type="' + condType + '"]').removeClass('pytHidden');
			$condOper.val(options.filter(':not(.pytHidden):first').attr('value')).trigger('change');
		});
		$condOper.on('change', function() {
			if ($(this).val() == 'between') $condValue2.removeClass('pytHidden');
			else $condValue2.addClass('pytHidden');
		});
		$rulesWrapper.on('click', 'li', function() {
			$rulesWrapper.find('li').removeClass('current');
			var rule = $(this).addClass('current');
			_this.resetCondForm($wrapper, false);
			if (rule.is('[data-rule-key]')) {
				var objRule = _this.conditionsTmp[rule.attr('data-rule-key')];
				if (objRule) {
					$wrapper.find('[data-cond]').each(function() {
						var $elem = $(this),
							props = $elem.attr('data-cond').split('.'),
							prop = props[0],
							sub = props.length > 1 ? props[1] : '',
							curValue = prop in objRule ? objRule[prop] : null;
						if (curValue != null) {
							var isObj = typeof(curValue) == 'object',
								value = sub.length ? (isObj && sub in curValue ? curValue[sub] : '') : (isObj ? '' : curValue);
							if ($elem.is('[type="checkbox"]')) {
								if ($elem.attr('value') == value) $elem.prop('checked', true);
							} else $elem.val(value).trigger('change');
						}
					});
				}
			}			
		});
		$rulesWrapper.on('click', '.pyt-rule-delete', function(e) {
			e.preventDefault();
			$(this).closest('li').remove();
			_this.saveCondRulesList($wrapper);
		});
		$wrapper.find('.pyt-apply-cond').on('click', function() {
			var $liRule = $rulesWrapper.find('li.current[data-rule-key]'),
				objRule = {styles: {}},
				ruleKey = '';
			$wrapper.find('.pyt-warning').addClass('pytHidden');
			
			$wrapper.find('[data-cond]').each(function() {
				var $elem = $(this);
				if (!$elem.hasClass('pytHidden') /*&& $elem.closest('.pytHidden').length == 0*/) {
					var value = $elem.is('input[type="checkbox"]') ? ($elem.is(':checked') ? $elem.val() : '') : $elem.val();
					if (value != '' || $elem.is('[data-required="1"]')) {
						var props = $elem.attr('data-cond').split('.'),
							prop = props[0],
							sub = props.length > 1 ? props[1] : '';
						
						if (sub.length) {
							if (!(prop in objRule) || typeof(objRule[prop]) != 'object') objRule[prop] = {};
							objRule[prop][sub] = value;
						} else objRule[prop] = value;
					}
				}
			});
			var ruleId = _this.getNewCondRuleIndex(),
				ruleKey = _this.condRulePrefix + ruleId;
			if ($liRule.length) {
				$liRule.attr('data-rule-key', ruleKey);
			} else {
				var li = $rulesWrapper.find('.pyt-tpl-rule').clone().attr('data-rule-key', ruleKey).removeClass('pyt-tpl-rule');
				objRule.ruleId = ruleId;
				$rulesWrapper.append(li);
				$liRule = $rulesWrapper.find('li[data-rule-key="' + ruleKey + '"]');
			}
			_this.setCondLiData($wrapper, $liRule, objRule);
			_this.saveCondRulesList($wrapper);

			$rulesWrapper.removeClass('pytHidden');
			$rulesWrapper.find('li').removeClass('current');
			$liRule.addClass('current');
		});


	}

	BuilderModel.constructor.prototype.saveCondRulesList = function ($wrapper) {				
		var rules = [];
		$wrapper.find('li[data-rule-key]').each(function() {
			rules.push($(this).attr('data-rule-key'));
		});
		$wrapper.find('input[data-id="pytDataConds"]').val(rules.join(','));
	}

	BuilderModel.constructor.prototype.addColSettingsForm = function ($wrapper, column) {	
		this.$obj.setCondSettingsForm($wrapper, column ? column.pytConds : '');
	}

	BuilderModel.constructor.prototype.setCondSettingsForm = function ($wrapper, conds) {	
		var _this = this.$obj;
		_this.resetCondForm($wrapper, true);
		_this.conditionsTmp = {};
		if (!conds || conds == '') return;
		$wrapper.find('input[data-id="pytDataConds"]').val(conds);
		if (typeof(conds) == 'object') conds = '';
		conds = conds.split(',');

		var $form = $wrapper.find('.pyt-cond-form'),
			$rulesWrapper = $form.find('.pyt-rules-wrapper'),
			ruleKey = '',
			objRule = {};
		for (var i = 0; i < conds.length; i++) { 
			ruleKey = conds[i];
			if (ruleKey in _this.conditions) { 
				objRule = _this.conditions[ruleKey];
				var $liRule = $rulesWrapper.find('.pyt-tpl-rule').clone().attr('data-rule-key', ruleKey).removeClass('pyt-tpl-rule');
				$rulesWrapper.append($liRule);
				_this.setCondLiData($form, $liRule, objRule);
			}
		}
	}

	//------------- Google Sheet Settings-------------//
	BuilderModel.constructor.prototype.initGoogleSettings = function () {
		var _this = this.$obj,
			googleSettings = $('#pyt-google-settings'),
			googleParams = $('#pyt-google-params');

		_this.sourceSettings = googleSettings;
		googleSettings.find('#pytLoadInBuilder').on('click', function() {
			_this.sourceEdited = true;
			_this.edited = true;

			$.sendFormPyt({
				btn: $('#pytLoadInBuilder'),
				data: {
					mod: 'tablespro',
					action: 'getGoogleColumns',
					tableId: _this.adminPage.tableId,
					source: jsonInputsPyt(googleParams)
				},
				onSuccess: function(res) {
					if (!res.error && res.settings) {
						var columns = res.settings['columns'];
						$.each(_this.grid.option('colModel'), function(i, col) {
							if (columns[i]) {
								columns[i]['prop']['pyt']['title'] = col.title;
							}
						});
						_this.initColumnModel(columns);
						_this.initMergeCells(res.settings['tuning'] && res.settings['tuning']['merge'] ? res.settings['tuning']['merge'] : []);
						_this.reinitGrid();

					}
				}
			});
		});
	}

	//------------- Database Settings-------------//
	BuilderModel.constructor.prototype.initDatabaseSettings = function () {
		var _this = this.$obj,
			dbSettings = $('#pyt-database-settings'),
			dbParams = $('#pyt-db-params');

		_this.sourceSettings = dbSettings;
		
		dbSettings.find('select').on('change pyt-change', function() {
			var $this = $(this),
				value = $this.val(),
				name = $this.attr('name');
			dbSettings.find('[data-parent="'+name+'"]').addClass('pytHidden');
			dbSettings.find('[data-parent="'+name+'"][data-parent-value="'+value+'"],[data-parent="'+name+'"][data-parent-notvalue][data-parent-notvalue!="'+value+'"]').removeClass('pytHidden');
		});

		dbParams.find('input, select').on('change', function() {
			var dbName = dbParams.find('[name="source[db_name]"]'),
				tables = dbSettings.find('#pyt-database-tables'),
				select = tables.find('select'),
				defValue = select.data('default');
			select.find('option[value!="'+defValue+'"]').remove();
			select.val(defValue).trigger('pyt-change');

			if (dbName.val() == dbName.data('external')) {
				var host = dbParams.find('[name="source[db_host_e]"]').val(),
					db = dbParams.find('[name="source[db_name_e]"]').val(),
					login = dbParams.find('[name="source[db_login_e]"]').val(),
					password = dbParams.find('[name="source[db_password_e]"]').val();
				if (host.length == 0 || db.length == 0 || login.length == 0 || password.length == 0) {
					return false;
				}
			}
			$.sendFormPyt({
				elem: tables,
				data: {
					mod: 'tablespro',
					action: 'getDatabaseTables',
					source: jsonInputsPyt(dbParams),
				},
				onSuccess: function(res) {
					if (!res.error && res.tables) {
						for (var i = 0; i < res.tables.length; i++) {
							$('<option>' + res.tables[i] + '</option>').val(res.tables[i]).appendTo(select);
						}
						select.trigger('pyt-change');
					}
				}
			});
		});

		dbSettings.find('#pyt-database-table').on('change', function() {
			var table = $(this),
				fields = dbSettings.find('#pyt-database-fields, #pyt-database-uniq'),
				select = fields.find('select[multiple]');

			select.html('').trigger('chosen:updated');

			if (table.val() != table.data('sql')) {
				$.sendFormPyt({
					elem: fields,
					data: {
						mod: 'tablespro',
						action: 'getTableFields',
						source: jsonInputsPyt(dbSettings),
					},
					onSuccess: function(res) {
						if (!res.error && res.fields) {
							for (var i = 0; i < res.fields.length; i++) {
								$('<option>' + res.fields[i] + '</option>').val(res.fields[i]).appendTo(select);
							}
							select.trigger('chosen:updated');
						}
					}
				});
			}
		});

		dbSettings.find('#pytLoadInBuilder').on('click', function() {
			var table = dbSettings.find('#pyt-database-table'),
				cols = [];
			_this.sourceEdited = true;
			_this.edited = true;

			if (table.val() == table.data('sql') || dbSettings.find('#pyt-database-fields').val() == '') {
				$.sendFormPyt({
					btn: $('#pytLoadInBuilder'),
					data: {
						mod: 'tablespro',
						action: 'getSQLColumns',
						tableId: _this.adminPage.tableId,
						source: jsonInputsPyt(dbSettings)
					},
					onSuccess: function(res) {
						if (!res.error && res.cols) {
							_this.setDatabaseColumns(res.cols);
						}
					}
				});
			} else {
				_this.setDatabaseColumns(dbSettings.find('#pyt-database-fields select').val());
			}
		});
	}

	BuilderModel.constructor.prototype.setDatabaseColumns = function(cols) {
		var _this = this.$obj,
			colModel = _this.grid.option('colModel'),
			oldCopy = $.extend(true, {}, colModel),
			curLen = colModel.length - 2,
			newLen = cols.length;

		_this.grid.Columns().remove(curLen, 1, colModel);
		_this.maxColIndx = 0;
		for (var i = 0; i < newLen; i++) {
			var col = $.extend(true, {}, _this.defaultColumn),
				field = cols[i],
				colInx = i + 1,
				old = false;
			for (var j = 1; j <= curLen; j++) {
				if (oldCopy[j].prop.pyt.dbField == field) {
					col = $.extend(true, {}, oldCopy[j]);
					old = true;
					break;
				}
			}
			if (!old) {
				col.prop.pyt['title'] = field;
				col.prop.pyt['dbField'] = field;
			}
			col['dataIndx'] = _this.getNewColumnIndex();
			col['nameIndx'] = colInx;
			_this.addColumns([colInx], colInx, false, col);
		}

		_this.reinitGrid();
	}

	BuilderModel.constructor.prototype.getSourceSettings = function() {
		return jsonInputsPyt(this.$obj.sourceSettings);
	}

	BuilderModel.constructor.prototype.saveChangesPro = function() {
		var _this = this.$obj;

		if (_this.adminPage.tableType == 0) return;

		var	colModel = _this.grid.option('colModel');
		_this.setColumnModel(colModel.slice(), []);

		var tuning = _this.addTuningParams({sorter: _this.sortModel, merge: _this.mergeCells});

		_this.adminPage.saving = true;
		_this.showLoading();
		_this.adminPage.setNeedPreview();

		$.sendFormPyt({
			btn: $('#pytBtnSave'),
			data: {
				mod: 'tablespro',
				action: 'saveSourceData',
				tableId: _this.adminPage.tableId,
				tableType: _this.adminPage.tableType,
				colModel:  JSON.stringify(_this.colModel),
				source: _this.getSourceSettings(),
				tuning: JSON.stringify(_this.convertPropsToObject(tuning)),
			},
			onComplete: function (res) {
				_this.adminPage.saving = false;
				_this.hideLoading();
			},
			onSuccess: function(res) {
				if (!res.error) {
					_this.saved = true;
				}
			}
		});
		_this.edited = false;
	}

	BuilderModel.constructor.prototype.initImport = function() {
		var _this = this.$obj,
			page = _this.adminPage,
			dImport = $('#pytDialogImport'),
			$importBtn = $('#pytBtnImport'),
			dialogImport = dImport.dialog({
				position: {my: 'center', at: 'center', of: '.pubydoc-main'},
				maxHeight: 700,
				resizable: false,
				height: 'auto',
				width: 600,
				modal: true,
				autoOpen: false,
				dialogClass: 'pubydoc-plugin',
				classes: {
					'ui-dialog': 'pubydoc-plugin'
				},
				buttons: [
					{
						text: page.getLangString('builder', 'btn-import'),
						class: 'button button-secondary',
						click: function(e) {
							var form = new FormData();
							if ($importBtn.hasClass('disabled')) return false;

							$(this).find('.block-tab.active').find('input, select').each(function() {
								var $input = $(this);
								if ($input.is('input[type="file"]')) {
									form.append(this.name, $input.get(0).files[0]);
								} else if ($input.is('input[type="checkbox"]')) {
									if ($input.is(':checked')) form.append(this.name, $input.val());
								} else {
									form.append(this.name, $input.val());
								}
							});
							form.append('mod', 'import');
							form.append('action', 'importTableData');
							form.append('creation', $(this).find('.pyt-import-tabs .button.current').data('creation'));
							form.append('tableId', page.tableId);

							$.sendFormPyt({
								btn: $importBtn,
								form: form,
								ajax: {
									cache: false,
									contentType: false,
									processData: false
								},
								/*onComplete: function (res) {
									page.saving = false;
									_this.hideLoading();
								},*/
								onSuccess: function(res) {
									if (!res.error) {
										setTimeout(function() {
											document.location.reload();
										}, 500);
									}
								}
							});
							$(this).dialog('close');
							$importBtn.blur();
						}
					},
					{
						text: page.getLangString('builder', 'btn-cancel'),
						class: 'button button-secondary',
						click: function() {
							$(this).dialog('close');
							$importBtn.blur();
						}
					}
				],
				create: function( event, ui ) {
					$(this).parent().css('maxWidth', $(window).width()+'px');
				}
			});

		$importBtn.on('click', function (e) {
			e.preventDefault();
			var $this = $(this);
			if ($this.hasClass('disabled')) return false;
			dialogImport.dialog('open');
			
			return false;
		});
		
		var $tabsContent = dialogImport.find('.block-tab'),
			$tabs = dialogImport.find('.pyt-import-tabs .button'),
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
		$tabsContent.find('.pyt-import-append').on('change', function() {
			var $this = $(this),
				$header = $this.closest('.settings-wrapper').find('.pyt-import-header').prop('checked', false);
			if ($(this).is(':checked')) $header.closest('.settings-option').addClass('pytHidden');
			else $header.closest('.settings-option').removeClass('pytHidden');
		});
	}

	BuilderModel.constructor.prototype.initExport = function() {
		var _this = this.$obj,
			page = _this.adminPage,
			dExport = $('#pytDialogExport'),
			$exportBtn = $('#pytBtnExport'),
			dialogExport = dExport.dialog({
				position: {my: 'center', at: 'center', of: '.pubydoc-main'},
				maxHeight: 700,
				resizable: false,
				height: 'auto',
				width: 400,
				modal: true,
				autoOpen: false,
				dialogClass: 'pubydoc-plugin',
				classes: {
					'ui-dialog': 'pubydoc-plugin'
				},
				buttons: [
					{
						text: page.getLangString('builder', 'btn-export'),
						class: 'button button-secondary',
						click: function(e) {
							$.sendFormPyt({
								btn: $exportBtn,
								data: {
									mod: 'export',
									action: 'generateUrl',
									tableId: page.tableId,
									type: $(this).find('.pyt-export-tabs .button.current').data('export'),
									params: jsonInputsPyt($(this).find('.block-tab.active')),

								},
								onSuccess: function(res) {
									if (!res.error && res.url) {
										window.location.href = res.url;
									}
								}
							});
							
							$(this).dialog('close');
							$exportBtn.blur();
						}
					},
					{
						text: page.getLangString('builder', 'btn-cancel'),
						class: 'button button-secondary',
						click: function() {
							$(this).dialog('close');
							$exportBtn.blur();
						}
					}
				],
				create: function( event, ui ) {
					$(this).parent().css('maxWidth', $(window).width()+'px');
				}
			});

		$exportBtn.on('click', function (e) {
			e.preventDefault();
			var $this = $(this);
			if ($this.hasClass('disabled')) return false;
			dialogExport.dialog('open');
			
			return false;
		});
		
		var $tabsContent = dialogExport.find('.block-tab'),
			$tabs = dialogExport.find('.pyt-export-tabs .button'),
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
	}

	BuilderModel.constructor.prototype.addDiagramm = function() {
		var _this = this.$obj,
			range = _this.getSelectedRange();
		if (range != false) window.open(PYT_DATA.diagramUrl + '&new=' + _this.adminPage.tableId + '-' + range ,'_blank');
	}
	
}(window.jQuery, window));
