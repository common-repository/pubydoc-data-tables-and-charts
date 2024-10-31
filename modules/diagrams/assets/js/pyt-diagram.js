(function ($, app) {
"use strict";
	function DiagramPlotly() {
		this.$obj = this;
		return this.$obj;
	}

	DiagramPlotly.prototype.init = (function () {
		var _this = this.$obj;

		_this.inited = false;
		_this.pageDiagrams = '.pyt-diagram-front';
		_this.dynamicDiagrams = [];
		_this.rawData = [];
		
		_this.drawDiagrams($(_this.pageDiagrams));
		_this.inited = true;
	});

	DiagramPlotly.prototype.drawDiagrams = (function ($diagrams) {
		if (!$diagrams || $diagrams.length == 0) return;

		var _this = this.$obj;

		$diagrams.filter(':not(.pyt-diagram-drawed)').each(function () {
			var $this = $(this);
			if (_this.inited || $this.closest('.pyt-table-wrap').length == 0) { 

				var	id = $this.data('id'),
					config = $this.data('config');
				if (config) {
					var data = config['data'];
					if ($this.attr('data-refresh') == '1') {
						data = _this.prepareData(_this.rawData[id] ? _this.rawData[id] : $this.data('raw'), $this.data('type'), $this.data('options'), [], config['trace']);
					}
					_this.drawDiagram($this.attr('id'), data, config['layout'], config['config'], $this.attr('data-highlighting') == '1');
					
			
					if ($this.data('dynamic') == '1') {
						var tableId = $this.data('table-id');
						if (!_this.dynamicDiagrams[tableId]) {
							_this.dynamicDiagrams[tableId] = [];
						}
						if (!_this.dynamicDiagrams[tableId][id]) {
							_this.dynamicDiagrams[tableId][id] = config['range'];
						}
					}
				}
				$this.prev('.pyt-diagram-loader').hide();
			}
			
		});
	});
	DiagramPlotly.prototype.refreshDynamicDiagrams = (function (tableId) {
		var _this = this.$obj,
			$diagrams = $(_this.pageDiagrams + '[data-table-id="'+tableId+'"][data-dynamic="1"]');
		if ($diagrams.length) {
			$diagrams.attr('data-refresh', 1).removeClass('pyt-diagram-drawed');
			_this.drawDiagrams($diagrams);
		}
	});
	DiagramPlotly.prototype.refreshTableDiagrams = (function ($table) {
		var _this = this.$obj;
		_this.drawDiagrams($table.find(_this.pageDiagrams));
	});

	DiagramPlotly.prototype.prepareData = (function (raw, typ, options, trace, traces) {
		var _this = this.$obj,
			data = [],
			isFront = (trace.length > 0 ? false : true),
			isPie = (typ == 3),
			isMulti = (typ == 5),
			isBubble = isMulti || (typ == 4),
			withLabels = pytCheckSettings(options, 'label_first_col') == '1',
			withHeader = pytCheckSettings(options, 'header_first_row') == '1',
			withValues = pytCheckSettings(options, 'show_values') == '1',
			reverseXY = typ == 2 && pytCheckSettings(options, 'bar_orientation') == 'h',
			xName = isPie ? 'labels' : 'x',
			yName = isPie ? 'values' : 'y',
			x = reverseXY ? yName : xName,
			y = reverseXY ? xName : yName,
			needText = withValues || isBubble;

		for(var i in trace) {
			trace[i][xName] = [];
			trace[i][yName] = [];
			if (needText) trace[i]['text'] = [];
		}

		if (pytCheckSettings(options, 'switch_rows_cols') == '1') {
			var newRaw = [];
			for (var r = 0; r < raw.length; r++) {
				for (var c = 0; c < raw[r].length; c++) {
					if (!newRaw[c]) newRaw[c] = [];
					newRaw[c][r] = raw[r][c];
				}
			}
			raw = newRaw;
		}

		for (var r = withHeader ? 1 : 0; r < raw.length; r++) {
			for (var c = withLabels ? 1 : 0; c < raw[r].length; c++) {
				var n = withLabels ? c - 1 : c;
				if (!data[n]) {
					data[n] = isFront ? (traces[n] ? JSON.parse(JSON.stringify(traces[n])) : []) : JSON.parse(JSON.stringify(trace[trace[n] ? n : 0]));
					if (!isFront && traces[n]) {
						if (traces[n]['name']) data[n]['name'] = traces[n]['name'];
					}
				}
				data[n][x].push(withLabels ? raw[r][0] : r);
				data[n][y].push(raw[r][c]);
				if (needText) data[n]['text'].push(raw[r][c]);
			}
		}
		if (withHeader) {
			for (var c = withLabels ? 1 : 0; c < raw[0].length; c++) {
				var n = withLabels ? c - 1 : c;
				if (data[n]) data[n]['name'] = raw[0][c];
			}
		}
		if (isBubble) {
			for(var i in data) {
				if (data[i]['pyt'] == 4) {
					data[i]['marker']['size'] = data[i]['text'];
				}
			}
		}
		return data;
	});

	DiagramPlotly.prototype.drawDiagram = (function (containerId, data, layout, config, highlighting) {
		var container = document.getElementById(containerId),
			$container = $(container);


		if (layout['width']) {
			$container.addClass('pyt-auto-scroll');
		} else {
			$container.removeClass('pyt-auto-scroll');
		}
		$container.empty();
		
		Plotly.newPlot(container, data, layout, config);
		$container.addClass('pyt-diagram-drawed');

		if (highlighting) {

			container.on('plotly_hover', function (eventdata){
				var points = eventdata.points[0],
					curveNumber = points.curveNumber;
				
				if (points.data.type == 'pie') {
					var colors = [],
						texts = [],
						curColor = points.color,
						pointI = points.i,
						cntVals, num;
					for (var c = 0; c < container.calcdata.length; c++) {
						points = container.calcdata[c];
						cntVals = points.length; 

						for (var i = 0; i < cntVals; i++) {
							num = points[i]['i'];
							if (colors[num]) continue;
							colors[num] = pytHexToRgbA(points[i].color, num == pointI ? 1 : 0.4);
							if (num != pointI) {
								texts[num] = '#cccccc';
							}
						}
					}
					var update = {'marker':{colors: colors}, 'textfont': {color: texts}, 'insidetextfont': {color: texts}, 'outsidetextfont': {color: texts}};
					Plotly.restyle(container, update);
				} else {
					var update = {opacity: 0.4},
						cnt = container.data.length,
						curvers = [];
					for (var i = 0; i < cnt; i++) {
						if (i != curveNumber) curvers.push(i);
					}
					Plotly.restyle(container, update, curvers);
				}
			});
			container.on('plotly_unhover', function (eventdata){
				var points = eventdata.points[0],
					update = points.data.type == 'pie' ? { marker: {}, insidetextfont: {}, outsidetextfont: {}, textfont: {}, opacity: 1 } : { opacity: 1 };
				Plotly.restyle(container, update);
			});
		}
		return container;
	});

	$(document).ready(function () {
		app.pytDiagram = new DiagramPlotly();
		app.pytDiagram.init();
	});

}(window.jQuery, window));
