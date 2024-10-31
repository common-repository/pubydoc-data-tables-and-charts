(function ($, app) {
"use strict";
	var BuilderToolbar = app.pytBuilderToolbar;
	
	// Add PRO version of subscribe method
	BuilderToolbar.prototype.initPro = function () {
		var _this = this,
			container = _this.getContainer(),
			builder = _this.getBuilder(),
			page = builder.adminPage,
			grid = builder.grid;
		_this.addProMethods();

		// dialog Conditions
		var dConditions = $('#pytDialogConditions');

		_this.dialogСonditions = dConditions.dialog({
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
					text: page.getLangString('builder', 'btn-save'),
					class: 'button button-secondary',
					click: function() {
						var form = $(this).find('.pyt-cond-form'),
							isBlock = builder.withBlockSelection(_this.selectedBlock),
							dataConds = form.find('input[data-prop="pytConds"]').val();

						builder.setConditions(dataConds);
						_this.setPropOnSelection('pytConds', dataConds);
						
						if (!isBlock) {
							builder.changeFormatType = 'column';
						}

						builder.changeFormat = true;
						builder.edited = true;
						$(this).dialog('close');
					}
				},
				{
					text: page.getLangString('builder', 'btn-delete'),
					class: 'button button-secondary',
					click: function() {
						var colNums = builder.getColsFromSelection(_this.selectedBlock),
							colNum = colNums ? colNums[0] : -1,
							dataConds = {};
						_this.setPropOnSelection('pytConds', dataConds);
						builder.setConditions(dataConds);
						grid.refreshView();
						builder.changeFormat = true;
						builder.edited = true;
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
				var form = $(this).find('.pyt-cond-form'),
					isBlock = builder.withBlockSelection(_this.selectedBlock),
					first = builder.getSelectedFirstCell(true, _this.selectedBlock),
					colModel = builder.grid.option('colModel'),
					row = first.row,
					col = colModel[first.c],
					$rulesWrapper = form.find('.pyt-rules-wrapper'),
					conds = isBlock ? (row.pq_cellprop && row.pq_cellprop[col.dataIndx] && row.pq_cellprop[col.dataIndx].pytConds ? row.pq_cellprop[col.dataIndx].pytConds : {}) : col.pytConds;

				_this.dialogСonditions.dialog('option', 'title', dConditions.data(isBlock ? 'title-cell' : 'title-column'));
				builder.setCondSettingsForm(dConditions, conds);
			},
			create: function( event, ui ) {
				$(this).parent().css('maxWidth', $(window).width()+'px');
			}
		});
		builder.addConditionsEvents(dConditions);
	};

	// Add PRO methods
	BuilderToolbar.prototype.addProMethods = function () {
		var _this = this,
			builder = _this.getBuilder(),
			adminPage = builder.adminPage,
			proMethods = {
			family: function(e) {
				var option = $(e.target).find('option:selected'),
					family = option.length ? option.attr('value') : '';

				if (!family || family == '') {
					family = 'inherit';
				} else {
					adminPage.importFont(family);
				} 
				this.setStyleOnSelection('font-family', family);
			},
			add_editable: function() {
				this.setPropOnSelection('pytFront', 'editable');
			},
			remove_editable: function() {
				this.setPropOnSelection('pytFront', '');
			},
			add_tooltip: function() {
				this.setPropOnSelection('pytFront', 'tooltip');
			},
			remove_tooltip: function() {
				this.setPropOnSelection('pytFront', '');
			},
			add_collapsible: function() {
				var rowsRange = this.getBuilder().getRangeRowCols(true);
				rowsRange.prop('pytCollaps', 'rows');
				this.getBuilder().saveCollapsibleRows(rowsRange);
			},
			remove_collapsible: function() {
				var rowsRange = this.getBuilder().getRangeRowCols(true);
				rowsRange.prop('pytCollaps', '');
				this.getBuilder().deleteCollapsibleRows(rowsRange);
			},
			
			сonditions: function() {
				this.selectedBlock = builder.getSelected();
				if (this.selectedBlock) this.dialogСonditions.dialog('open');
				else pytShowAlert(adminPage.getLangString('builder', 'select-cells'));
			},
			add_diagram: function() {
				builder.addDiagramm();
			}
		};
		$.each(proMethods, function(method, fn) {
			_this.addMethod(method, fn);
		});
	}
}(window.jQuery, window));
