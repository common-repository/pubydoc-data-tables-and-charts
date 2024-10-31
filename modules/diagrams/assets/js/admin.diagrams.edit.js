(function ($, app) {
"use strict";
	function DiagramPage() {
		this.$obj = this;
		this.$obj.diagramId = 0;
		this.$obj.diagramSettings = false;
		this.$obj.canPreview = false;
		this.$obj.previewinf = false;
		this.$obj.resetXY = false;
		this.$obj.resetLegent = false;
		this.$obj.needRefresh = false;

		return this.$obj;
	}
	DiagramPage.prototype.init = (function () {
		var _this = this.$obj,
			wWidth = $('.pubydoc-container').width();


		_this.dialogObj = $('#pytDialogDiagramSettings');
		_this.settingsSection = _this.dialogObj.find('.pyt-diagram-settings');
		_this.diagramsTypeElem = $('#pytDiagramsType');
		_this.diagramPlotly = app.pytDiagram;
		_this.diagramContainerId = 'pytDiagramPreview';
		_this.diagramContainer = $('#' + _this.diagramContainerId);
		_this.diagramTypes = $('.pyt-diagrams-types');
		_this.diagramConfig = $('#pytDiagramConfig');
		_this.diagramData = {};
		_this.diagramTitles = {};

		if (wWidth > 1000) wWidth = 1000;

		var dDiagram = _this.dialogObj;

		_this.dialogDiagram = dDiagram.dialog({
			position: {my: 'center top', at: 'top', of: '.pubydoc-main-container'},
			maxHeight: 700,
			resizable: true,
			height: 'auto',
			width: wWidth,
			modal: true,
			autoOpen: false,
			dialogClass: 'pubydoc-plugin',
			classes: {
				'ui-dialog': 'pubydoc-plugin'
			},
			buttons: [
				{
					text: dDiagram.data('save'),
					class: 'button button-secondary',
					click: function(e) {
						if (_this.needRefresh) {
							jQuery.sNotify({
								'icon': 'fa fa-exclamation-circle',
								'error': true,
								'content': '<span> '+dDiagram.data('refresh')+'</span>',
								'delay' : 2500
							});
							return false;
						}
						var container = _this.diagramContainer.find('.svg-container');
						if (!container || container.height() == 0) container = _this.diagramContainer;
						var w = container.width();
						Plotly.toImage(_this.diagramContainerId, {format: 'png', width: w, height: container.height(), scale: 200/w, imageDataOnly: 1}).then(function(dataUrl) {
							var byteString = atob(dataUrl.split(',')[1]),
								mimeString = dataUrl.split(',')[0].split(':')[1].split(';')[0],
								ab = new ArrayBuffer(byteString.length),
								ia = new Uint8Array(ab);
							for (var i = 0; i < byteString.length; i++) {
								ia[i] = byteString.charCodeAt(i);
							}
							var file = new Blob([ab], {type: mimeString}),
								data = new FormData();
							data.append('imgFile', file, 'image.png');
							data.append('mod', 'diagrams');
							data.append('action', 'saveDiagram');
							data.append('diagramId', _this.diagramId);
							data.append('settings', jsonInputsPyt(_this.settingsSection));
							data.append('config', _this.diagramConfig.val());
							data.append('status', $('#pytDiagramsTableId option:selected').data('type') == 0 ? 1 : 0);
							
							$.sendFormPyt({
								btn: $(e.target),
								form: data,
								ajax: {
									cache: false,
									contentType: false,
									processData: false
								},
								onComplete: function (res) {
									_this.dialogDiagram.dialog('close');
									if (_this.tableForReload) {
										setTimeout(function() {
											_this.tableForReload.ajax.reload();
										}, 500);
									}
								}
							});
						});
					}
				},
				{
					text: dDiagram.data('clone'),
					class: 'button button-secondary pyt-diagram-clone',
					click: function() {
						var btn = $(this);
						pytShowConfirm(dDiagram.data('clone-confirm'), 'pytDiagramPage', 'cloneDiagram');
					}
				},
				{
					text: dDiagram.data('cancel'),
					class: 'button button-secondary',
					click: function() {
						$(this).dialog('close');
					}
				}
			],
			open: function(event, ui) {
				$('.ui-widget-overlay').addClass('pubydoc-wrap-overlay');
				_this.resetXY = false;
				_this.resetLegent = false;
				_this.previewing = false;
				_this.canPreview = false;
				_this.emptyPreview();
				var buttons = _this.dialogDiagram.parent().find('.ui-dialog-buttonset button'),
					settings = _this.diagramSettings,
					isNew = _this.diagramId == 0;
				if (buttons.length) {
					buttons.eq(0).html(dDiagram.data(isNew ? 'create' : 'save'));
					var cloneBtn = buttons.filter('.pyt-diagram-clone');
					if (cloneBtn.length) {
						if (isNew) {
							cloneBtn.addClass('pytHidden');
						} else {
							cloneBtn.removeClass('pytHidden');
							cloneBtn.html(dDiagram.data('clone'));
						}
					}
				}


				_this.settingsSection.find('select, input').each(function() {
					var $elem = $(this),
						value = '',
						name = $elem.attr('name'),
						props = name.split('['),
						values = settings;
					if (props.length > 1) {
						values = pytCheckSettings(settings, props[0]);
						name = props[1].substr(0, props[1].length - 1);
					}
					value = pytCheckSettings(values, name, isNew ? $elem.attr('data-default') : '');
					if (value.length == 0) value = $elem.attr('data-empty');
					if ($elem.is('input[type="radio"]') || $elem.is('input[type="checkbox"]')) $elem.prop('checked', $elem.is('[value="' + value + '"]'));
					else $elem.val(value);
					$elem.trigger('pyt-change');					
				});	

				_this.settingTabs.eq(0).trigger('click');
				_this.settingsSection.find('.pubydoc-color-input').trigger('change');

				_this.canPreview = true;
				_this.showDiagram();
				_this.settingsSection.find('.pyt-options-wrap').slimScroll({'height': '355px'});
				_this.needRefresh = false;
			},
		});
		_this.eventsDiagramPage();
	});
	DiagramPage.prototype.emptyPreview = (function () {

		this.$obj.diagramContainer.html('<div id="pytPreviewEmpty">' + this.$obj.diagramContainer.data('empty') + '</dv>');
	});
	DiagramPage.prototype.showDiagram = (function () {
		var _this = this.$obj,
			settings = _this.diagramSettings,
			config = pytCheckSettings(settings, 'config');
		if (pytCheckSettings(settings, 'status') == "1") {
			var range = pytCheckSettings(config, 'range'),
				data = pytCheckSettings(config, 'data'),
				layout = pytCheckSettings(config, 'layout');
			if (typeof(range) == 'object' && typeof(data) == 'object' && typeof(layout) == 'object') {
				_this.drawDiagram(range, data, layout);
				return;
			}
		}
		_this.diagramConfig.val(JSON.stringify(config));
		_this.refreshDiagram();
		
	});

	DiagramPage.prototype.refreshDiagram = (function () {
		var _this = this.$obj;
		if (!_this.canPreview || _this.previewing) return;

		_this.emptyPreview();

		var	settings = jsonInputsPyt(_this.settingsSection, true),
			range = pytCheckSettings(settings, 'table_range'),
			rangeObj = _this.getRange(range),
			tableId = pytCheckSettings(settings, 'table_id');

		_this.diagramSettings = settings;
		if (rangeObj && tableId) {
			_this.previewing = true;
			if (typeof (_this.diagramData[tableId]) == 'undefined') {
				_this.diagramData[tableId] = {};
				_this.diagramTitles[tableId] = {};
			}
			if (_this.diagramData[tableId][range]) {
				_this.renderDiagram(_this.diagramData[tableId][range], rangeObj, _this.diagramTitles[tableId][range]);
			} else {

				$.sendFormPyt({
					btn: _this.dialogDiagram.find('#pytDiagramRefresh'),
					data: {
						mod: 'tablespro',
						action: 'getRangeData',
						tableId: tableId,
						range: rangeObj,
					},
					onComplete: function (res) {
						_this.previewing = false;
					},
					onSuccess: function(res) {
						if (!res.error && res.data) {
							if (res.data.values) {
								_this.diagramData[tableId][range] = res.data.values;
								_this.diagramTitles[tableId][range] = res.data.titles;
								_this.renderDiagram(res.data.values, rangeObj, res.data.titles);
							}
						}
					}
				});
			}
		}
	});
	
	DiagramPage.prototype.renderDiagram = (function (data, range, titles) {
		var _this = this.$obj,
			container = document.getElementById(_this.diagramContainerId),
			options = pytCheckSettings(_this.diagramSettings, 'options'),
			config = pytParseJSON(_this.diagramConfig.val()),
			typ = pytCheckSettings(_this.diagramSettings, 'type'),
			layout = pytCheckSettings(config, 'layout'),
			isMulti = (typ == 5), trace,
			traceNames = typeof(titles) == 'undefined' || !titles.length || pytCheckSettings(options, 'switch_rows_cols') == '1' ?  [] : titles.slice(pytCheckSettings(options, 'label_first_col') == '1' ? 1 : 0);

		if (isMulti) {
			trace = [
				_this.getTypeData(typ, pytCheckSettings(options, 'multi_trace1')),
				_this.getTypeData(typ, pytCheckSettings(options, 'multi_trace2')),
				_this.getTypeData(typ, pytCheckSettings(options, 'multi_trace3')),
				_this.getTypeData(typ, pytCheckSettings(options, 'multi_trace4'))
				];
		}
		else trace = [_this.getTypeData(typ)];
		
		var data = _this.diagramPlotly.prepareData(data, typ, options, trace, _this.resetLegent ? traceNames : pytCheckSettings(config, 'trace'));
			
		if (typeof(layout) != 'object') layout = {xaxis: {}, yaxis: {}};
		if (typeof(layout['title']) != 'object') layout['title'] = {text: _this.dialogDiagram.find('#pytDiagramsTitle').val()};
		delete(layout['width']);
		delete(layout['height']);
		delete(layout['title']['font']);
		delete(layout['grid']);

		layout['autosize'] = true;
		layout['margin'] = {t: pytCheckSettings(options, 'margin_t'), r: pytCheckSettings(options, 'margin_r'), b: pytCheckSettings(options, 'margin_b'), l: pytCheckSettings(options, 'margin_l')};
		
		if (pytCheckSettings(options, 'show_title') != "1") {
			layout['margin'] = {t: 30};
			layout['title']['font'] = {size:1, color:'transparent'};
		} else {
			layout['title']['font'] = {size: pytCheckSettings(options, 'title_size'), color: pytCheckSettings(options, 'title_color')};
		}
		if (pytCheckSettings(options, 'custom_colors') == "1") {
			var colors = [];
			_this.settingsSection.find('#pytColorWay input.pubydoc-color-input').each(function() {
				var $this = $(this);
				colors.push($this.val().length ? $this.val() : $this.data('empty'));
			});
			layout['colorway'] = colors;

		} else delete(layout['colorway']);
		if (pytCheckSettings(options, 'auto_size') != "1") {
			layout['width'] = options['width'];
			layout['height'] = options['height'];
		}

		layout['showlegend'] = (pytCheckSettings(options, 'show_legend') == "1");
		
		if (typeof(layout['legend']) != 'object') layout['legend'] = {};
		layout['legend']['orientation'] = pytCheckSettings(options, 'legend_orientation');

		layout['font'] = 18;
		if (typ == 3) {
		
			var columns = parseInt(pytCheckSettings(options, 'pie_columns'));
			if (columns > 1) {
				var cnt = data.length,
					rows = Math.ceil(cnt / columns),
					c = 0, r = 0;
				for(var i in data) {
					data[i]['domain'] = {row: r, column: c};
					c++;
					if (c >= columns) {
						c = 0;
						r++;
					}
				}
				layout['grid'] = {rows: rows, columns: columns};

			}
		}		

		if (_this.resetXY) {
			if (layout['xaxis'] && layout['xaxis']['title']) layout['xaxis'] = {title: layout['xaxis']['title']};
			else layout['xaxis'] = {};
			if (layout['yaxis'] && layout['yaxis']['title']) layout['yaxis'] = {title: layout['yaxis']['title']};
			else layout['yaxis'] = {};
		}
		layout['xaxis']['automargin'] = true;
		layout['yaxis']['automargin'] = true;

		layout = _this.addTypeLayout(pytCheckSettings(_this.diagramSettings, 'type'), layout);

		_this.drawDiagram(range, data, layout);
		
		_this.previewing = false;
		_this.resetXY = false;
		_this.resetLegent = false;
		_this.needRefresh = false;
	});

	DiagramPage.prototype.drawDiagram = (function (range, data, layout) {
		var _this = this.$obj,
			title = _this.dialogDiagram.find('#pytDiagramsTitle').val(),
			config = {
				responsive: true,
				scrollZoom: true,
				displaylogo: false,
				autosizable: true,
				editable: true,
				autoexpand: true,
				modeBarButtonsToRemove: ['select2d', 'lasso2d'],
				toImageButtonOptions: {
    				format: 'png', // one of png, svg, jpeg, webp
    				filename: title.length ? title.replace(/[ &\/\\#,+()$~%.'":*?<>{}]/g, "-") : 'diagram',
    				/*height: 500,
    				width: 700,
    				scale: 1*/
  				}
			},
			container = _this.diagramPlotly.drawDiagram(_this.diagramContainerId, data, layout, config, _this.dialogDiagram.find('#pytDiagramsHighlighting').is(':checked'));
		
		container.on('plotly_relayout', function(){
			_this.saveDiagramConfig(range, container.data, container.layout, config);
		});
		container.on('plotly_restyle', function(){
			_this.saveDiagramConfig(range, container.data, container.layout, config);
		});
		_this.saveDiagramConfig(range, container.data, container.layout, config);

	});

	DiagramPage.prototype.saveDiagramConfig = (function (range, data, layout, config) {
		config['editable'] = false;
		var trace = [],
			dataKeys = ['x', 'y', 'text', 'values', 'labels'];

		for (var i = 0; i < data.length; i++) {
			trace[i] = {};
			for (var key in data[i]) {
				trace[i][key] = toeInArrayPyt(key, dataKeys) ? [] : data[i][key];
			}
		}
		this.$obj.diagramConfig.val(JSON.stringify({range: range, data: data, layout: layout, config: config, trace: trace}));
	});

	

	DiagramPage.prototype.getTypeData = (function (typ, multi) {
		var _this = this.$obj,
			isMulty = (typ == 5 && typeof(multi) != 'undefined'),
			data = {},
			hoverinfo = [],
			options = pytCheckSettings(_this.diagramSettings, 'options'),
			lineMode = pytCheckSettings(options, 'lines_mode');
		typ = parseInt(typ);

		if (isMulty) {
			if (multi == 'bar') typ = 2;
			else if (multi == 'bubble') typ = 4;
			else {
				typ = 0;
				lineMode = multi;
			}
		}

		if (typ == 0 || typ == 1 || typ == 2 || typ == 4) {
			if (pytCheckSettings(options, 'hover_x') == "1") hoverinfo.push('x');
			if (pytCheckSettings(options, 'hover_y') == "1") hoverinfo.push('y');
			if (pytCheckSettings(options, 'hover_name') == "1") hoverinfo.push('name');
		}

		switch (typ) {
			case 0:
				data = {
					type: 'scatter',
					mode: lineMode + (pytCheckSettings(options, 'show_values') == '1' ? '+text' : ''), 
					textposition: pytCheckSettings(options, 'textposition'),
					marker: {size: pytCheckSettings(options, 'markers_size'), symbol: pytCheckSettings(options, 'markers_symbol')},
					line: {width: pytCheckSettings(options, 'lines_width'), shape: pytCheckSettings(options, 'lines_shape'), dash: pytCheckSettings(options, 'lines_dash')},
				};
				data['hoverinfo'] = hoverinfo.length == 0 ? 'none' : hoverinfo.join('+');

				break;
			case 1:
				data = {
					type: 'scatter', 
					fill: 'tozeroy', 
					mode: lineMode + (pytCheckSettings(options, 'show_values') == '1' ? '+text' : ''), 
					textposition: pytCheckSettings(options, 'textposition'),
					marker: {size: pytCheckSettings(options, 'markers_size'), symbol: pytCheckSettings(options, 'markers_symbol')},
					line: {width: pytCheckSettings(options, 'lines_width'), shape: pytCheckSettings(options, 'lines_shape'), dash: pytCheckSettings(options, 'lines_dash')},
				};
				data['hoverinfo'] = hoverinfo.length == 0 ? 'none' : hoverinfo.join('+');

				if (pytCheckSettings(options, 'area_stacked') == '1') data['stackgroup'] = 'one';
				break;
			case 2:
				data = {
					type: 'bar', 
					orientation: isMulty ? 'v' : pytCheckSettings(options, 'bar_orientation'),
					textposition: pytCheckSettings(options, 'bar_textposition'),
				};
				data['hoverinfo'] = hoverinfo.length == 0 ? 'none' : hoverinfo.join('+');
				
				break;
			case 3:
				data['type'] = 'pie';
				if (pytCheckSettings(options, 'pie_hover_label') == "1") hoverinfo.push('label');
				if (pytCheckSettings(options, 'pie_hover_value') == "1") hoverinfo.push('value');
				if (pytCheckSettings(options, 'pie_hover_percent') == "1") hoverinfo.push('percent');
				if (pytCheckSettings(options, 'pie_hover_name') == "1") hoverinfo.push('name');
				data['hoverinfo'] = hoverinfo.length == 0 ? 'none' : hoverinfo.join('+');

				var textinfo = [];
				if (pytCheckSettings(options, 'pie_text_label') == "1") textinfo.push('label');
				if (pytCheckSettings(options, 'pie_text_value') == "1") textinfo.push('value');
				if (pytCheckSettings(options, 'pie_text_percent') == "1") textinfo.push('percent');
				if (pytCheckSettings(options, 'pie_text_text') == "1") textinfo.push('text');
				data['textinfo'] = textinfo.length == 0 ? 'none' : textinfo.join('+');
				data['hole'] = pytCheckSettings(options, 'pie_hole');
				data['automargin'] = true;
				data['textposition'] = pytCheckSettings(options, 'pie_textposition');
				data['direction'] = pytCheckSettings(options, 'pie_direction');
				data['insidetextorientation'] = pytCheckSettings(options, 'pie_textorientation');
				
			
				break;
			case 4:
				data = {
					type: 'scatter', 
					mode: 'markers' + (pytCheckSettings(options, 'show_values') == '1' ? '+text' : ''), 
					textposition: pytCheckSettings(options, 'textposition'),
					marker: {symbol: pytCheckSettings(options, 'bubble_symbol'), opacity: pytCheckSettings(options, 'bubble_opacity')},
				};
				data['hoverinfo'] = hoverinfo.length == 0 ? 'none' : hoverinfo.join('+');

				break;
			case 5:
				data['type'] = 'bar';
				break;
			default:
				data['type'] = 'scatter';
				break;
		}
		data['pyt'] = typ;

		return data;
	});

	DiagramPage.prototype.addTypeLayout = (function (typ, layout) {
		var _this = this.$obj,
			options = pytCheckSettings(_this.diagramSettings, 'options');
		typ = parseInt(typ);
		switch (typ) {
			case 0:

				break;
			case 1:

				break;
			case 2:
				layout['barmode'] = pytCheckSettings(options, 'bar_stacked');
				break;
			case 3:

				break;
			case 4:

				break;
			case 5:

				break;
			default:
				break;
		}

		return layout;
	});
	

	DiagramPage.prototype.eventsDiagramPage = (function () {
		var _this = this.$obj,
			dialogObj = _this.dialogObj,
			$tabsContent = dialogObj.find('.block-tab'),
			$tabs = dialogObj.find('.tbs-col-tabs .button'),
			$currentTab = $tabs.filter('.current').attr('href'),
			$settingsSection = _this.settingsSection;
		
		$tabsContent.filter($currentTab).addClass('active');
		_this.settingTabs = $tabs;

		$tabs.off('click').on('click', function (e) {
			e.preventDefault();
			var $this = $(this),
				$curTab = $this.attr('href');

			$tabsContent.removeClass('active');
			$tabs.filter('.current').removeClass('current');
			$this.addClass('current');

			$tabsContent.filter($curTab).addClass('active');
		});
		$settingsSection.find('input[type="checkbox"]').on('change pyt-change', function () {
			var $this = $(this),
				check = $this.is(':checked'),
				name = $this.attr('name'),
				hidden = $this.closest('.pyt-option-wrapper').hasClass('pytHidden'),
				subOptions = $settingsSection.find('.pyt-option-wrapper[data-parent~="' + name + '"]'),
				subOptionsR = $settingsSection.find('.pyt-option-wrapper[data-parent-reverse="' + name + '"]');
			if(subOptions.length) {
				if(check && !hidden) subOptions.removeClass('pytHidden');
				else {
					subOptions.each(function() {
						var $el = $(this),
							check = false,
							parents = $el.attr('data-parent').split(' ');
						if (parents.length > 1) {
							for (var i = 0; i < parents.length; i++) {
								if ($settingsSection.find('input[name="'+parents[i]+'"]').is(':checked')) {
									check = true;
									break;
								}
							}
						}
						if (!check) $el.addClass('pytHidden');
					});
				}
				subOptions.find('input[type="checkbox"], select').trigger('pyt-change');
			}
			if(subOptionsR.length) {
				if(check || hidden) subOptionsR.addClass('pytHidden');
				else subOptionsR.removeClass('pytHidden');
				subOptionsR.find('input[type="checkbox"], select').trigger('pyt-change');
			}
		});
		$settingsSection.find('select,input[type="hidden"]').on('change pyt-change', function () {
			var $this = $(this),
				value = $this.val(),
				hidden = $this.closest('.pyt-option-wrapper').hasClass('pytHidden'),
				subOptions = $settingsSection.find('.pyt-option-wrapper[data-parent="'+$this.attr('name')+'"]');
			if(subOptions.length) {
				subOptions.addClass('pytHidden');
				if(!hidden) subOptions.filter('[data-parent-value~="'+value+'"]').removeClass('pytHidden');
				subOptions.find('input[type="checkbox"]').trigger('pyt-change');
			}
		});

		_this.diagramsTypeElem.on('pyt-change', function (e) {
			_this.diagramTypes.find('img[data-type="'+$(this).val()+'"]').trigger('pyt-click');
		});
		_this.diagramTypes.find('img').on('click pyt-click', function (e) {
			_this.diagramTypes.find('img').removeClass('custom');
			$(this).addClass('custom');
			if (e.type == 'click') {
				_this.diagramsTypeElem.val($(this).data('type')).trigger('change');
				_this.refreshDiagram();
			}
		});

		_this.dialogObj.find('#pytDiagramRefresh').on('click', function () {
			_this.refreshDiagram();
		});
		$settingsSection.find('select, input').on('change', function (e) {
			var $this = $(this);
			_this.needRefresh = true;
			if ($this.data('reset-xy') == '1') _this.resetXY = true;
			if ($this.data('reset-legend') == '1') _this.resetLegent = true;
			if ($this.data('not-preview') != '1') _this.refreshDiagram();
		});
		
		$('.pubydoc-color-picker').each(function() {
			var $this = $(this),
				colorArea = $this.find('.pubydoc-color-preview'),
				colorInput = $this.find('.pubydoc-color-input'),
				curColor = colorInput.val(),
				timeoutSet = false;

			colorArea.ColorPicker({
				flat: false,
				onShow: function (colpkr) {
					$(this).ColorPickerSetColor(colorInput.val());
					$(colpkr).fadeIn(500);
					return false;
				},
				onHide: function (colpkr) {
					$(colpkr).fadeOut(500);
					return false;
				},
				onChange: function (hsb, hex, rgb) {
					var self = this;
					curColor = hex;
					if(!timeoutSet) {
						setTimeout(function(){
							timeoutSet = false;
							$(self).find('.colorpicker_submit').trigger('click');
						}, 500);
						timeoutSet = true;
					}
				},
				onSubmit: function(hsb, hex, rgb, el) {
					setColorPickerPreview(colorArea, '#' + curColor);
					colorInput.val('#' + curColor).trigger('change');					
				}
			});
			setColorPickerPreview(colorArea, colorInput.val());
		});
		$('.pubydoc-color-input').on('change', function() {
			setColorPickerPreview($(this).parent().find('.pubydoc-color-preview'), $(this).val());
		});
		function setColorPickerPreview(area, col) {
			area.css({'backgroundColor': col, 'border-color': pytGetColorPickerBorder(col)});
		}

	});
	DiagramPage.prototype.getCell = (function (str) {
		var parts = str.match(/\$?([A-Z]+)?\$?(\d+)?/),
			col = parts[1] ? parts[1] : null,
			row = parts[2] ? parts[2] : null;
		if (col != null) {
			var i, s, l;
			for (var n = col.length, d = -1, r = 0; n > r; r++) {
				i = col[r];
				l = i.charCodeAt(0) - 64;
				s = n - r - 1;
				d += l * Math.pow(26, s);
			}
			col = d + 1;
		}
		return {
			c: parseInt(col),
			r: parseInt(row)
		}
	});
	DiagramPage.prototype.getRange = (function (str) {
		var _this = this.$obj,
			blocks = str.split(';'),
			result = [];
		if (blocks.length == 0) return false;

		for (var i = 0; i < blocks.length; i++) {
			if (blocks[i].length == 0) continue;
			var parts = blocks[i].split(':');
			if (parts.length != 2) continue;
			result.push({from: _this.getCell(parts[0]), to: _this.getCell(parts[1])});
		}
		return result.length ? result : false;
	});
	DiagramPage.prototype.cloneDiagram = (function () {
		var _this = this.$obj;
		if (_this.diagramId == 0) return;
		$.sendFormPyt({
			btn: _this.dialogDiagram.parent().find('.ui-dialog-buttonset .pyt-diagram-clone'),
			data: {
				mod: 'diagrams',
				action: 'cloneDiagram',
				diagramId: _this.diagramId,
			},
			onSuccess: function(res) {
				if (!res.error && res.data && res.data.link) {
					setTimeout(function() {
						window.open(res.data.link ,'_blank');
					}, 500);
				}
			}
		});
	});

	$(document).ready(function () {
		app.pytDiagramPage = new DiagramPage();
		app.pytDiagramPage.init();
	});

}(window.jQuery, window));
