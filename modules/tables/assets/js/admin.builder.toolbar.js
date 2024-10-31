(function ($, app) {
"use strict";
	function BuilderToolbar(toolbarId, builder) {
		var $container = $(toolbarId);
		this.methods = {
				redo: function() {
					this.getGrid().history({method: 'redo'});
				},
				undo: function() {
					this.getGrid().history({method: 'undo'});
				},
				add_row: function () {
					this.getBuilder().addRows();
				},
				remove_row: function () {
					this.getBuilder().deleteRows();
				},
				add_column: function () {
					this.getBuilder().addColumns(false, -1);
				},	
				remove_col: function () {
					this.getBuilder().deleteColumns();
				},
				column_width: function() {
					this.dialogColumnsWidth.dialog('open');
				},
				bold: function (e) {
					this.setStyleOnSelection('font-weight', $(e.target).parent().hasClass('pyt-toolbar-active') ? 'normal' : 'bold', true);
				},
				italic: function (e) {
					this.setStyleOnSelection('font-style', $(e.target).parent().hasClass('pyt-toolbar-active') ? 'normal' : 'italic', true);
				},
				underline: function (e) {
					this.setStyleOnSelection('text-decoration', $(e.target).parent().hasClass('pyt-toolbar-active') ? 'normal' : 'underline', true);
				},
				color: function (color) {
					this.setStyleOnSelection('color', color);
					$('#tbeTextColor').css({borderBottomColor: color});
				},
				background: function (color) {
					this.setStyleOnSelection('background-color', color);
					$('#tbeBgColor').css({borderBottomColor: color});
				},
				left: function () {
					this.setStyleOnSelection('text-align', 'left');
				},
				right: function () {
					this.setStyleOnSelection('text-align', 'right');
				},
				center: function () {
					this.setStyleOnSelection('text-align', 'center');
				},
				top: function () {
					this.setPropOnSelection('pytValign', 'top');
				},
				middle: function () {
					this.setPropOnSelection('pytValign', 'middle');
				},
				bottom: function () {
					this.setPropOnSelection('pytValign', 'bottom');
				},
				'word-wrap-default': function() {
					this.setPropOnSelection('pytWrap', '');
				},
				'word-wrap-visible': function() {
					this.setPropOnSelection('pytWrap', 'visible');
				},
				'word-wrap-hidden': function() {
					this.setPropOnSelection('pytWrap', 'hidden');
				},
				format: function(e) {
					this.selectedBlock = this.getBuilder().getSelected();
					if (this.selectedBlock) this.dialogDataType.dialog('open');
				},
				size: function (e) {
					this.setStyleOnSelection('font-size', $(e.target).val());
				},
				link: function () {
					this.selectedCell = this.getBuilder().getSelectedFirstCell(true);
					if (this.selectedCell) this.dialogInsertLink.dialog('open');
				},
				media: function (e) {
					var _this = this,
						builder = _this.getBuilder(),
						cell = builder.getSelectedFirstCell(true);

					if (!cell) return;
					var colModel = builder.grid.option('colModel'),
						cellTypeFormat = builder.getCellTypeFormat(cell.row, colModel[cell.c]);
					if (cellTypeFormat && cellTypeFormat.type == 'button') return;
					if (e.ctrlKey) {
						var url = prompt('Enter URL of image file:', 'http://');
						if (url === null) return;
						builder.updateCell(cell.r, cell.dataIndx, _this.getHtmlForAttachment({ url: url, type: 'image' }));

						return;
					}
					builder.mediaMCE = false;
					wp.media.editor.send.attachment = function(props, attachment) {
						if (builder.adminPage.curTabModel == 'builder' && !builder.mediaMCE) {
							wp.media.editor.activeEditor = true;
							builder.updateCell(cell.r, cell.dataIndx, _this.getHtmlForAttachment({url: attachment.url, type: attachment.type}, props, attachment));
						} else {
							return builder.adminPage.wpMediaSendAttachmentOrig.apply( this, [props, attachment] );
						}
					};
					wp.media.editor.open();
					return false;
				},
				merge: function () {
					var grid = this.getGrid(),
						merged = grid.option('mergeCells'),
						first = this.getBuilder().getSelectedFirstCell(true),
						forMerge = true;
					if (!first) return;

					if (merged.length) {
						$.each(merged, function() {
							if (this.r1 == first.r && this.c1 == first.c) {
								forMerge = false;
								return;
							}
						});
					}
					if (forMerge) {
						grid.Selection().merge();
					}
					else grid.Selection().unmerge();
					this.getBuilder().edited = true;
				},
				comment: function () {
					this.selectedCell = this.getBuilder().getSelectedFirstCell(true);
					if (this.selectedCell) this.dialogComment.dialog('open');
				},		
				add_invisible_row: function() {
					this.getBuilder().getRangeRowCols(true).prop('pytVisible', 'invis');
				},
				add_hidden_row: function() {
					this.getBuilder().getRangeRowCols(true).prop('pytVisible', 'hidden');
				},
				remove_invis_row: function() {
					this.getBuilder().getRangeRowCols(true).prop('pytVisible', '');
				},
				add_invisible_col: function() {
					this.setPropOnSelection('pytVisible', 'invis', true);
				},
				add_hidden_col: function() {
					this.setPropOnSelection('pytVisible', 'hidden', true);
				},
				remove_invis_col: function() {
					this.setPropOnSelection('pytVisible', '', true);
				},
				add_shortcode: function() {
					this.setPropOnSelection('pytFront', 'shortcode');
				},
				remove_shortcode: function() {
					this.setPropOnSelection('pytFront', '');
				},
				multiple_sorting: function(){
					var grid = this.getGrid(),
						single = grid.option('sortModel.single');
					grid.option('sortModel.single', !single);
					grid.refresh();
					this.setOnOffButton('tbeMultisort', single);
				},
				column_letters: function(){
					var builder = this.getBuilder();
					if (builder.lettersMode) {
						var names = builder.realColNames;
						$.each(builder.grid.colModel, function() { 
							if (this.dataIndx in names) {
								this.title = names[this.dataIndx];
							}
						});
						builder.lettersMode = false;
						builder.grid.refreshHeader();
					} else {
						var names = [];
						$.each(builder.grid.colModel, function() { 
							names[this.dataIndx] = this.title;
						});
						builder.realColNames = names;
						builder.lettersMode = true;
						builder.setColLetters(builder.grid);
					}
					this.setOnOffButton('tbeColumnLetter', builder.lettersMode);
				},
				show_filter: function(){
					var grid = this.getGrid(),
						filter = !grid.option('filterModel.header');
					grid.option('filterModel.header', filter);
					grid.refresh();
					this.setOnOffButton('tbeShowFilter', filter);
				}				
			};
		this.getContainer = function () {
			return $container;
		};
		this.getBuilder = function () {
			return builder;
		};
		this.getGrid = function () {
			return builder.grid;
		};
		this.getTableModel = function () {
			return app.pytTableModel;
		};
	}
	BuilderToolbar.prototype.setStyleOnSelection = function(key, value, refresh) {
		var builder = this.getBuilder(),
			selection = builder.getSelected();
		if (selection) {
			builder.updateColumnProps(builder.getColsFromSelection(selection), [['style.' + key, value]]);
			selection.style(key, value);
			if (refresh) this.setStylesOnToolbar();
		}
	}
	BuilderToolbar.prototype.setPropOnSelection = function(key, value, forCols) {
		var builder = this.getBuilder(),
			selection = forCols ? builder.getRangeRowCols() : builder.getSelected(),
			cols = builder.getColsFromSelection(selection);
		if (cols.length) builder.updateColumnProps(cols, [[key, value]]);
		if (!forCols) builder.convertSelectedToBlock(selection).prop(key, value);
		if (cols.length) {
			var colModel = builder.grid.getColModel();
			for (var c = 0; c < cols.length; c++) builder.addColumnSettings(colModel[cols[c]]);
		}
		builder.grid.refreshView();
		builder.edited = true;
	}

	BuilderToolbar.prototype.getHtmlForAttachment = function(data, props, attachment) {
		var content = data.url,
			url = data.url,
			fullUrl = data.url,
			type = data.type,
			link = '',
			linkHtml = '',
			classes = 'stbSkipLazy',	// our custom class to skip lazy loading of images by Jetpack
			attrs = 'style="max-width: 100%; height: auto;"',
			isEmbed = false;

		if(props && attachment) {
			if (attachment.sizes) {
				if (attachment.sizes[props.size]) {
					url = attachment.sizes[props.size].url;
					classes += ' align' + props.align + ' size-' + props.size;
				}
				if (attachment.sizes['full']) {
					fullUrl = attachment.sizes['full'].url;
				}
			}
		if (type == 'image') {
			attrs = 'width="' + attachment.sizes[props.size].width + '" height="' + attachment.sizes[props.size].width + '"';
		}
		switch(props.link) {
			case 'file':
				link = attachment.url;
				linkHtml = '<a href="'+link+'">'+attachment.title+'</a>';
				break;
			case 'post':
				link = attachment.link;
				linkHtml = '<a href="'+link+'">'+attachment.title+'</a>';
					break;
				case 'custom':
					link = props.linkUrl;
					break;
				case 'embed':
					isEmbed = true;
					break;
				default:
					break;
			}
		}
		switch(type) {
			case 'image':
				content = '<img src="' + url + '" class="' + classes + '" ' + attrs + ' data-full="' + fullUrl + '" />';
				if(link) {
					content = '<a href="' + link + '">' + content + '</a>';
				}
				break;
			case 'video':
				if(isEmbed) {
					content = '<div class="video-container"><video controls>';
					content += '<source src="' + url + '" ' +
					(typeof attachment.mime != 'undefined' ? 'type="' + attachment.mime + '"' : '') + '>';
					content += '</video></div>';
				} else if(linkHtml) {
					content = linkHtml;
				}
				break;
			case 'audio':
				if(isEmbed) {
					content = '<div class="audio-container"><audio controls>';
					content += '<source src="' + url + '" ' +
					(typeof attachment.mime != 'undefined' ? 'type="' + attachment.mime + '"' : '') + '>';
					content += '</audio></div>';
				} else if(linkHtml) {
					content = linkHtml;
				}
				break;
			case 'application':
				if(linkHtml) {
					content = linkHtml;
				}
				break;
			default:
				break;
		}

		return content;
	}

	BuilderToolbar.prototype.setStyles = function (name, value) {
		switch (name) {
			case 'font-weight': 
				if (value == 'bold') $('#tbeBold').addClass('pyt-toolbar-active');
				break;
			case 'font-style':
				if (value == 'italic') $('#tbeItalic').addClass('pyt-toolbar-active');
				break;
			case 'text-decoration':
				if (value == 'underline') $('#tbeUnderline').addClass('pyt-toolbar-active');
				break;
			case 'color': 
				$('#tbeTextColor').addClass('pyt-toolbar-active').css({borderBottomColor: value});
				break;
			case 'background-color':
				$('#tbeBgColor').addClass('pyt-toolbar-active').css({borderBottomColor: value});
				break;
			case 'font-family':
				$('#tbeFontFamily').val(value == 'inherit' ? '' : value);
				break;
			case 'font-size':
				$('#tbeFontSize').val(value);
				break;
		}
	}

	BuilderToolbar.prototype.setStylesOnToolbar = function (selection) {
		var _this = this,
			container = _this.getContainer(),
			buttons = container.find('button:not(.pyt-toolbar-switcher)').removeClass('pyt-toolbar-active disabled');
		container.find('select').val('');
		$('.tool-container.list a').removeClass('active');
		container.removeClass('disabled');
			
		var builder = _this.getBuilder(),
			first = builder.getSelectedFirstCell(true, selection);
		if (first) {
			var colModel = builder.grid.option('colModel'),
				cellTypeFormat = builder.getCellTypeFormat(first.row, colModel[first.c]);
			if (cellTypeFormat) {
				var typ = cellTypeFormat.type;
				buttons.filter('[data-nottype="'+typ+'"]').addClass('disabled');
				buttons.filter('[data-type]:not([data-type~="'+typ+'"])').addClass('disabled');
			}

			buttons.filter('[data-notselect="'+first.type+'"]').addClass('disabled');

			var row = first.row,
				dataIndx = first.dataIndx,
				colModel = _this.getGrid().colModel[first.c];
			if (colModel.style) {
				$.each(colModel.style, function(name, value) {
					_this.setStyles(name, value)
				});
			}
			if (row.pq_rowstyle) {
				$.each(row.pq_rowstyle, function(name, value) {
					_this.setStyles(name, value);
				});
			}
			if (row.pq_cellstyle) {
				$.each(row.pq_cellstyle[dataIndx], function(name, value) {
					_this.setStyles(name, value);
				});
			}
		}
	}
	BuilderToolbar.prototype.setOnOffButton = function (id, isOn) {
		if (isOn) $('#' + id).addClass('pyt-toolbar-active');
		else $('#' + id).removeClass('pyt-toolbar-active');
	}

	BuilderToolbar.prototype.refreshControlButtons = function () {
		var builder = this.getBuilder(),
			grid = builder.grid;
		this.setOnOffButton('tbeMultisort', !grid.option('sortModel.single'));
		this.setOnOffButton('tbeShowFilter', grid.option('filterModel.header'));
		this.setOnOffButton('tbeColumnLetter', builder.lettersMode);
	}

	BuilderToolbar.prototype.init = function () {
		var _this = this,
			container = _this.getContainer(),
			builder = _this.getBuilder(),
			page = builder.adminPage,
			grid = builder.grid;

		_this.txtColorTimeout = false;
		_this.txtColorLast = '';
		var $textColor = $('#tbeTextColor').ColorPicker({
			onBeforeShow: function (colpkr) {
				$(this).ColorPickerSetColor(pytGetColorWeb($('#tbeTextColor').css('border-bottom-color'), true));
				$('#tbeTextColor').addClass('pyt-toolbar-active');
			},
			onChange: function (hsb, hex, rgb) {
				_this.txtColorLast = hex;
				if(!_this.txtColorTimeout) {
					setTimeout(function(){
						_this.txtColorTimeout = false;
						_this.call('color', '#' + _this.txtColorLast);
					}, 500);
					_this.txtColorTimeout = true;
				}
			}
		});

		_this.bgColorTimeout = false;
		_this.bgColorLast = '';
		var $bgColor = $('#tbeBgColor').ColorPicker({
			onBeforeShow: function (colpkr) {
				$(this).ColorPickerSetColor(pytGetColorWeb($('#tbeBgColor').css('border-bottom-color'), true));
				$('#tbeBgColor').addClass('pyt-toolbar-active');
			},
			onChange: function (hsb, hex, rgb) {
				_this.txtColorLast = hex;
				if(!_this.bgColorTimeout) {
					setTimeout(function(){
						_this.bgColorTimeout = false;
						_this.call('background', _this.txtColorLast.length ? '#' + _this.txtColorLast : '');
					}, 500);
					_this.bgColorTimeout = true;
				}
			}
		});

		// dialog Column width
		var dColumnsWidth = $('#pytDialogColumnsWidth');

		_this.dialogColumnsWidth = dColumnsWidth.dialog({
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
						var form = $(this).find('.dialog-form'),
							cols = grid.Columns(),
							colModel = grid.option('colModel');
						form.find('.column-width input').each(function(){
							var $this = $(this),
								value = $this.val(),
								wrapper = $this.closest('.column-row'),
								i = wrapper.attr('data-index');
							if (!isNumber(value)) value = '';
							colModel[i].prop.pyt.width = {width: value, points: wrapper.find('.column-point input[value="%"]').is(':checked') ? '%' : ''};
						});
						grid.flex();
						builder.updateColModel();
						grid.refreshView();
						builder.edited = true;
						$(this).dialog('close');
					}
				},
				{
					text: dColumnsWidth.data('clear'),
					class: 'button button-secondary',
					click: function() {
						$(this).find('.dialog-form .column-width input').val('');
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
				var form = $(this).find('.dialog-form').empty(),
					defRow = $(this).find('.column-row-default'),
					colModel = grid.option('colModel');
				$.each(colModel, function(i) {
					if (!this.hidden) {
						var col = this,
							row = defRow.clone().removeClass('column-row-default').attr('data-index', i),
							width = col.pytFlex ? '' : col.width,
							isPx = (width+'').indexOf('%') == -1;
						row.find('.column-num').html(i);
						row.find('.column-width input').val(isPx ? width : width.replace('%', ''));
						row.find('.column-point input').attr('name', 'point'+i).filter('[value="' + (isPx ? 'px' : '%') +'"]').prop('checked', true);
						form.append(row);
					}
				});
			},
			create: function( event, ui ) {
				$(this).parent().css('maxWidth', $(window).width()+'px');
			}
		});

		// dialog Data type
		var dDataType = $('#pytDialogDataType');

		_this.dialogDataType = dDataType.dialog({
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
						var form = $(this).find('.dialog-form'),
							isBlock = builder.withBlockSelection(_this.selectedBlock),
							dataType = form.find('#pytDataType').val(),
							fElems = form.find('div[data-parent-value="' + dataType + '"] [data-prop]'),
							dataFormat = '';

						fElems.each(function() {
							var $elem = $(this),
								props = $elem.attr('data-prop').split('.'),
								prop = props[0],
								sub = props.length > 1 ? props[1] : '',
								value = '';

							if (sub == 'format') {
								var subsub = props.length > 2 ? props[2] : '';
								value = $elem.is('input[type="checkbox"]') ? ($elem.is(':checked') ? 1 : '') : $elem.val();

								if (subsub.length) {
									if (typeof(dataFormat) != 'object') dataFormat = {};
									dataFormat[subsub] = value;
								} else dataFormat = value;
							}
						});
						_this.setPropOnSelection('pytType', {type: dataType, format: dataFormat});
						if (!isBlock) {
							builder.changeFormatType = 'column';
						}

						builder.changeFormat = true;
						builder.edited = true;
						$(this).dialog('close');
					}
				},
				{
					text: page.getLangString('builder', 'btn-cancel', 'Cancel'),
					class: 'button button-secondary',
					click: function() {
						$(this).dialog('close');
					}
				}
			],
			open: function(event, ui) {
				var form = $(this).find('.dialog-form'),
					isBlock = builder.withBlockSelection(_this.selectedBlock),
					first = builder.getSelectedFirstCell(true, _this.selectedBlock),
					colModel = builder.grid.option('colModel'),
					row = first.row,
					col = colModel[first.c],
					pytType = isBlock ? (row.pq_cellprop && row.pq_cellprop[col.dataIndx] ? row.pq_cellprop[col.dataIndx].pytType : {}) : col.pytType,
					typeElem = form.find('#pytDataType'),
					curType = pytType ? pytType.type : '',
					curFormat = pytType ? pytType.format : '';
				typeElem.find('option').removeClass('pytHidden');
				typeElem.find('option[data-for="' + (isBlock ? 'col' : 'row') + '"]').addClass('pytHidden');
				form.find('.pyt-warning').addClass('pytHidden');
				
				_this.dialogDataType.dialog('option', 'title', dDataType.data(isBlock ? 'title-cell' : 'title-column'));

				if (typeElem.find('option[value="' + curType + '"]:not(.pytHidden)').length == 0) curType = '';
				typeElem.val(curType).trigger('change');
				var isObj = typeof(curFormat) == 'object',
					fElems = form.find('div[data-parent-value="' + curType + '"] [data-prop]');

				fElems.each(function() {
					var $elem = $(this),
						props = $elem.attr('data-prop').split('.'),
						prop = props[0],
						sub = props.length > 1 ? props[1] : '';
					if (sub == 'format') {
						var subsub = props.length > 2 ? props[2] : '',
							value = subsub.length && isObj && subsub in curFormat ? curFormat[subsub] : curFormat;

						if ($elem.is('input[type="text"]') || $elem.is('select')) $elem.val(value);
						else if($elem.is('input[type="checkbox"]')) $elem.prop('checked', value);
						else $elem.val(value);
						$elem.trigger('change');
					}
				});
			},
			create: function( event, ui ) {
				$(this).parent().css('maxWidth', $(window).width()+'px');
			}
		});
		dDataType.find('select').on('change pytChange', function (e) {
			var $this = $(this),
				childs = dDataType.find('div[data-parent="' + $this.attr('id') + '"]');
			if (childs.length) {
				childs.addClass('pytHidden');
				childs.filter('[data-parent-value~="' + $this.val() + '"]').removeClass('pytHidden');
			}
		});

		// dialog Insert Link
		var dInsertLink = $('#pytDialogInsertLink');

		_this.dialogInsertLink = dInsertLink.dialog({
			position: {my: 'center', at: 'center', of: '.pubydoc-main'},
			maxHeight: 400,
			autoOpen: false,
			width: 400,
			height: 'auto',
			modal: true,
			dialogClass: 'pubydoc-plugin',
			classes: {
				'ui-dialog': 'pubydoc-plugin'
			},
			buttons: [
				{
					text: page.getLangString('builder', 'btn-insert'),
					class: 'button button-secondary',
					click: function() {
						var $this = $(this),
							target = $this.find('.link-target').is(':checked') ? '_blank' : '_self',
							url = $this.find('.url').val(),
							text = $this.find('.link-text').val(),
							insertToField = '<a href="' + url + '" target="' + target + '">' + text + '</a>',
							cell = _this.selectedCell;

						if (cell) builder.updateCell(cell.r, cell.dataIndx, insertToField);
						$this.dialog('close');
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
			open: function() {
				var $this = $(this),
					urlElem = $this.find('.url').val(''),
					textElem = $this.find('.link-text').val(''),
					targetElem = $this.find('.link-target').prop('checked', false),
					cell = _this.selectedCell,
					value = cell ? cell.row[cell.dataIndx] : '';
				if (value && value.length) {
					var link = $(value);
					if (link.length) {
						urlElem.val(link.attr('href'));
						textElem.val(link.html());
						if (textElem.attr('target') == '_blank') targetElem.prop('checked', true);
					}
				}
			},
			create: function( event, ui ) {
				$(this).parent().css('maxWidth', $(window).width()+'px');
			}
		});

		// dialog Insert Comment
		var dComment = $('#pytDialogComment');

		_this.dialogComment = dComment.dialog({
			position: {my: 'center', at: 'center', of: '.pubydoc-main'},
			maxHeight: 400,
			autoOpen: false,
			width: 400,
			height: 'auto',
			modal: true,
			dialogClass: 'pubydoc-plugin',
			classes: {
				'ui-dialog': 'pubydoc-plugin'
			},
			buttons: [
				{
					text: page.getLangString('builder', 'btn-insert'),
					class: 'button button-secondary',
					click: function() {
						var $this = $(this),
							text = $this.find('.cell-comment').val(),
							cell = _this.selectedCell;
						grid.Selection().comment(text);
						$this.dialog('close');
					}
				},
				{
					text: page.getLangString('builder', 'btn-delete'),
					class: 'button button-secondary',
					click: function() {
						grid.Selection().comment(false);
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
			open: function() {
				$(this).find('.cell-comment').val(grid.Selection().comment());
			},
			create: function( event, ui ) {
				$(this).parent().css('maxWidth', $(window).width()+'px');
			}
		});

					
		grid.on('selectEnd', function(e, ui) {
			_this.setStylesOnToolbar(ui.selection);		
		});

		pytClassInitPro(_this);

		// Set methods
		var methods = _this.methods;
		container.find('button, .toolbar-content > a, .tool').each(function () {
			var $button = $(this);

			if ($button.data('method') !== undefined && methods[$button.data('method')] !== undefined) {
				var method = $button.data('method'),
					event = $button.data('event') || 'click';

				$button.unbind(event);
				$button.on(event, function (e) {
					e.preventDefault();
					if (!$(this).hasClass('disabled')) {
						methods[method].apply(_this, [e]);
						// Close toolbar
						$('body').trigger('click');
					}
				});
			}
		});

		container.find('button').each(function () {
			var $button = $(this),
				contentId = $button.data('toolbar');

			if (contentId !== undefined && $(contentId).length) {
				$button.toolbar({
					content: contentId,
					position: 'bottom',
					hideOnClick: true,
					style: $button.data('style') || null
				});
			}
		});
		this.refreshControlButtons();
	}

	BuilderToolbar.prototype.call = function (method) {
		if (this.methods[method] === undefined) {
			throw new Error('The method "' + method + '" is not exists.');
		}
		this.methods[method].apply(this, Array.prototype.slice.call(arguments, 1, arguments.length));
	}
	
	BuilderToolbar.prototype.addMethod = function (name, fn) {
		this.methods[name] = fn;
	}
	app.pytBuilderToolbar = BuilderToolbar;
}(window.jQuery, window));