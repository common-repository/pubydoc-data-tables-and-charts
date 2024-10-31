function pytClassInitPro(_this) {
	if (typeof(_this.initPro) == 'function') _this.initPro();
}

(function ($, app) {
"use strict";
	if (typeof app.pytTabModels == 'undefined') app.pytTabModels = {};

// ================================================================ //
// *********************** ADMIN PAGE ***************************** //
	function AdminPage() {
		this.$obj = this;
		return this.$obj;
	}
	AdminPage.prototype.init = function () {
		var _this = this.$obj;
		_this.isPro = PYT_DATA.isPro == '1';
		_this.saving = false;
		_this.previewEnabled = true;
		 
		_this.formObj = $('#pytEditTableForm');
		_this.tableId = _this.formObj.data('id');
		_this.tableType = _this.formObj.data('type');

		_this.langStrings = pytParseJSON($('#pytLangStrings').val());
		_this.ajaxurl = typeof(ajaxurl) == 'undefined' || typeof(ajaxurl) !== 'string' ? PYT_DATA.ajaxurl : ajaxurl;
		_this.tabModels = (typeof app.pytTabModels == 'undefined') ? {} : app.pytTabModels;
		_this.importedFonts = [];
		_this.curTabModel = '';

		_this.eventsAdminPage();
		_this.stylesElemId = 'pytTableStyle';
		_this.addStylesElemId = 'pytTableAddStyle';
		pytCreateStyleElem(_this.stylesElemId);
		_this.wpMediaSendAttachmentOrig = wp.media.editor.send.attachment;

		for(var model in _this.tabModels) {
			_this.tabModels[model].init();
		}
		pytClassInitPro(_this);
		_this.refreshCurrentTab();
	}
	AdminPage.prototype.getTabModel = function (model) {
		var _this = this.$obj;
		return model in _this.tabModels ? _this.tabModels[model] : false;
	}
	AdminPage.prototype.setNeedPreview = function () {
		this.$obj.getTabModel('options').needPreview = true;
	}
	AdminPage.prototype.refreshCurrentTab = function () {
		var _this = this.$obj,
			$curTab = $('.pyt-main-tabs .button.current'),
			model = $curTab.attr('data-model');

		_this.curTabModel = $curTab.attr('data-model');
		if (model in _this.tabModels) {
			_this.tabModels[model].refresh();
		}
	}
	AdminPage.prototype.fullSave = function (condition) {
		var _this = this.$obj,
			isFull = typeof (condition) == 'undefined';

		if (!_this.saving) {
			for (var model in _this.tabModels) {
				var modelObj = _this.tabModels[model];
				if (isFull || modelObj[condition]) {
					modelObj.save();
				}
			}
		}
	}
	AdminPage.prototype.eventsAdminPage = function () {
		var _this = this.$obj,
			$form = _this.formObj,
			$mainTabsContent = $('.pyt-main-tab-content > .block-tab'),
			$mainTabs = $('.pyt-main-tabs .button'),
			$curTab = $mainTabs.filter('.current');

		$mainTabsContent.filter($curTab.attr('href')).addClass('active');
		_this.curTabModel = $curTab.attr('data-model');

		$mainTabs.on('click', function (e) {
			e.preventDefault();
			var $this = $(this),
				$curTab = $this.attr('href');

			$mainTabsContent.removeClass('active');
			$mainTabs.filter('.current').removeClass('current');
			$this.addClass('current');

			var $curTabContent = $mainTabsContent.filter($curTab);
			$curTabContent.addClass('active');
			if($curTab == '#block-tab-builder') $('.pyt-builder-switcher').removeClass('pytHidden');
			else $('.pyt-builder-switcher').addClass('pytHidden');
			_this.refreshCurrentTab();
		});

		$('#pytBtnDelete').on('click', function (e) {
			e.preventDefault();
			var btn = $(this);
			pytShowConfirm(btn.attr('data-confirm'), 'pytAdminTablePage', 'deleteTable');

			return false;
		});
		$('#pytBtnClear').on('click', function (e) {
			e.preventDefault();
			var btn = $(this);
			pytShowConfirm(btn.attr('data-confirm'), 'pytAdminTablePage', 'clearTable');

			return false;
		});
		$('#pytBtnClone').on('click', function (e) {
			e.preventDefault();
			var btn = $(this);
			pytShowConfirm(btn.attr('data-confirm'), 'pytAdminTablePage', 'cloneTable');

			return false;
		});
		$('#pytBtnSave').on('click', function (e) {
			e.preventDefault();
			if (!$(this).is(':disabled')) {
				_this.fullSave();
			}			
			return false;
		});		

		// Work with shortcode copy text
		$('#pytShortcodes').on('change', function (e) {
			var key = jQuery(this).val();
			$('.pyt-shortcode-shell').addClass('pytHidden');
			$('.pyt-shortcode-shell[data-key="' + key + '"]').removeClass('pytHidden');
		});

		//-- Work with title --//
		var titleShell = $('#pytTitleShell'),
			titleText = titleShell.find('.pyt-title-text'),
			titleInput = titleShell.find('input'),
			titleIcon = titleShell.find('i');
		titleShell.on('click', function() {
			titleText.addClass('pytHidden');
			titleInput.removeClass('pytHidden');
			titleIcon.addClass('pytHidden');
		});
		_this.$titleText = titleText;
		titleInput.on('focusout keypress', function(e) {
			if (e.type == 'focusout' || e.keyCode == 13) {

				var title = $(this).val();
				titleInput.addClass('pytHidden');
				titleText.text(title).removeClass('pytHidden');
				titleIcon.removeClass('pytHidden');
				$.sendFormPyt({
					data: {
						mod: 'tables',
						action: 'saveTableTitle',
						id: _this.tableId,
						title: title
					}
				});
			}
		});
		//-- Work with title --//

		$('.pubydoc-color-picker').each(function() {
			var $this = $(this),
				colorArea = $this.find('.pubydoc-color-preview'),
				colorInput = $this.find('.pubydoc-color-input'),
				curColor = colorInput.val(),
				timeoutSet = false;

			colorArea.ColorPicker({
				//color: curColor,
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
		$('.pro-notify').on('click', function (e) {
			e.preventDefault();
			var proDesc = $('.pubydoc-pro-desc');
			if (proDesc.length)	pytShowAlert(proDesc.eq(0).html());

			return false;
		});
	}
	AdminPage.prototype.deleteTable = (function () {
		$.sendFormPyt({
			btn: $('#pytBtnDelete'),
			data: {
				mod: 'tables',
				action: 'removeTable',
				tableId: this.$obj.tableId,
			},
			onSuccess: function(res) {
				if (!res.error && res.data && res.data.link) {
					setTimeout(function() {
						document.location.href = res.data.link;
					}, 500);
				}
			}
		});
	});
	AdminPage.prototype.clearTable = (function () {
		$.sendFormPyt({
			btn: $('#pytBtnClear'),
			data: {
				mod: 'tables',
				action: 'clearData',
				tableId: this.$obj.tableId,
			},
			onSuccess: function(res) {
				if (!res.error) {
					setTimeout(function() {
						document.location.reload();
					}, 500);
				}
			}
		});
	});
	AdminPage.prototype.cloneTable = (function () {
		$.sendFormPyt({
			btn: $('#pytBtnClone'),
			data: {
				mod: 'tables',
				action: 'cloneTable',
				tableId: this.$obj.tableId,
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


	AdminPage.prototype.getLangString = function (tab, key, def) {
		if (tab in this.langStrings && key in this.langStrings[tab]) return this.langStrings[tab][key];
		return typeof(def) == 'undefined' ? false : def;
	}
// *********************** ADMIN PAGE ***************************** //
// ================================================================ //

// ================================================================ //
// ********************** OPTIONS MODEL *************************** //

	function OptionsModel() {
		this.$obj = this;
		return this.$obj;
	}
	OptionsModel.prototype.init = (function () {
		var _this = this.$obj;
		_this.adminPage = app.pytAdminTablePage;
		_this.$optionsSection = $('#block-tab-options .pyt-options-section');
		_this.$previewSection = $('#block-tab-options .pyt-preview-section');
		_this.$previewContainer = $('#pytPreviewContainer');
		_this.previewWrapSelector = '#pyt-table-preview-wrapper';
		_this.previewing = false;
		_this.needPreview = true;
		_this.previewTimeout = 0;
		_this.windowHeight = 0;
		_this.tablesFront = app.pytTables;

		_this.previewStyleId = 'pytPreviewStyle';
		pytCreateStyleElem(_this.previewStyleId);
		
		_this.eventsOptionsModel();
		pytClassInitPro(_this);

	});
	OptionsModel.prototype.refresh = function () {
		$(window).trigger('resize');
		var _this = this.$obj,
			$optionsSection = _this.$optionsSection,
			windowHeight = $(window).width() > 810 ? $(window).height() * 0.7 : $(window).height();

		_this.$previewContainer.find('.dataTables_processing').show();
		_this.adminPage.fullSave('saveBeforePreview');
		_this.showPreview();
		if (_this.windowHeight == windowHeight) return;
		_this.windowHeight = windowHeight;
		_this.linksOyPositions = [];

		var offsetTop2 = Math.floor($('#pyt-tab-options-main').offset().top),
			$optionsTabs = $optionsSection.find('.pyt-options-tabs a');
		_this.linksOyPositions.push({
			'id': '#pyt-tab-options-main',
			'offset': 0,
		});
		_this.linksOyPositions.push({
			'id': '#pyt-tab-options-features',
			'offset': Math.abs(Math.floor($('#pyt-tab-options-features').offset().top) - offsetTop2 - 40),
		});
		_this.linksOyPositions.push({
			'id': '#pyt-tab-options-appearance',
			'offset': Math.abs(Math.floor($('#pyt-tab-options-appearance').offset().top) - offsetTop2 - 40),
		});
		_this.linksOyPositions.push({
			'id': '#pyt-tab-options-text',
			'offset': Math.abs(Math.floor($('#pyt-tab-options-text').offset().top) - offsetTop2 - 40),
		});

		$optionsSection.find('.pyt-options-wrap').slimScroll({'height': windowHeight + 'px'}).off('slimscrolling')
			.on('slimscrolling', null, { 'oy': _this.linksOyPositions }, function(e, pos){
				if(e && e.data && e.data.oy) {
					var ind1 = 0,
						$activeItem = $optionsTabs.filter('.current'),
						isFind = false;
					while (ind1 < (e.data.oy.length - 1) && !isFind) {
						if (e.data.oy[ind1].offset <= pos && e.data.oy[ind1+1].offset > pos) {
							isFind = ind1;
							ind1 = e.data.oy.length;
						}
						ind1++;
					}
					if (isFind == false && ind1 == 3) {
						isFind = ind1;
					}
					var activeId = $activeItem.attr('href');
					$optionsTabs.removeClass('current').blur();
					$optionsTabs.filter('[href="' + e.data.oy[isFind].id + '"]').addClass('current');
				}
		});
	}

	OptionsModel.prototype.save = function() {
		var _this = this.$obj;
		if(_this.adminPage.saving) {
			setTimeout(function() {	_this.save(); }, 2000);
			return;
		}

		var _this = this.$obj,
			adminPage = _this.adminPage,
			builder = adminPage.getTabModel('builder');
		adminPage.saving = true;

		var css = pytGetStyleSheetRules(adminPage.stylesElemId, '#__pyt ');
		if (!_this.isDisablePreviewCss()) {
			var re = new RegExp(_this.previewWrapSelector, 'g');
			css += pytGetStyleSheetRules(_this.previewStyleId, '#__pyt ', re);
		}
		css += pytGetStyleSheetRules(adminPage.addStylesElemId, '#__pyt ', false, false);

		$.sendFormPyt({
			btn: $('#pytBtnSave'),
			data: {
				mod: 'tables',
				action: 'saveTableOptions',
				tableId: adminPage.tableId,
				builder: builder ? builder.builderType : 0,
				options: jsonInputsPyt(_this.$optionsSection),
				customCss: css
			},
			onComplete: function (res) {
				_this.adminPage.saving = false;
				_this.needSave = false;
			},
			onSuccess: function(res) {
				if (!res.error) {
					//builder.edited = false;
					//builder.saved = true;
				}
			}
		});
	}

	OptionsModel.prototype.eventsOptionsModel = function () {
		var _this = this.$obj,
			front = _this.tablesFront,
			$previewSection = _this.$previewSection,
			$optionsSection = _this.$optionsSection,
			$optionsWrap = $optionsSection.find('.pyt-options-wrap');

		
		$optionsSection.find('.pyt-options-tabs a').on('click', function(e, funcParams) {
			e.preventDefault();
			var $linkItem = $($(this).attr('href')),
				$topItem = $("#pyt-tab-options-main");
			if ($linkItem.length) {
				var offsetLink = $linkItem.offset().top,
					offsetTop = $topItem.offset().top,
						offsetAbs = Math.abs(offsetLink - offsetTop);
				// if need to set start position
				if (funcParams && funcParams.offsetScTop) {
					offsetAbs = funcParams.offsetScTop;
				}
				if (!isNaN(offsetAbs)) {
					$optionsWrap.slimScroll({ scrollTo: offsetAbs + 'px' });
				}
			}
		});

		$previewSection.find('.pyt-preview-styling a').on('click', function(e, funcParams) {
			e.preventDefault();
			var $this = $(this);
			$('.pyt-preview-styling a').removeClass('current');
			$this.addClass('current');
			front.mobileMode = false;
			switch($this.data('preview')) {
				case 'desktop':
					_this.$previewContainer.css('max-width', 'none');
					break;
				case 'tablet':
					_this.$previewContainer.css('max-width', '768px');
					break;
				case 'mobile':
					_this.$previewContainer.css('max-width', '320px');
					front.mobileMode = true;
					break;
				default:
					break;
			}
			var iTable = front.getTableInstance('preview');
			front.setTableWidthMode();
			$(window).trigger('resize');
			if (iTable) iTable.columns.adjust().draw(false);
			_this.tableNeedPreview = true;
			_this.showPreview();
		});

		$optionsSection.find('input[type="checkbox"]').on('change pyt-change', function () {
			var $this = $(this),
				check = $this.is(':checked'),
				name = $this.attr('name'),
				hidden = $this.closest('.pyt-option-wrapper').hasClass('pytHidden'),
				subOptions = $optionsSection.find('.pyt-option-wrapper[data-parent~="' + name + '"]'),
				subOptionsR = $optionsSection.find('.pyt-option-wrapper[data-parent-reverse="' + name + '"]');
			if(subOptions.length) {
				if(check && !hidden) subOptions.removeClass('pytHidden');
				else {
					subOptions.each(function() {
						var $el = $(this),
							check = false,
							parents = $el.attr('data-parent').split(' ');
						if (parents.length > 1) {
							for (var i = 0; i < parents.length; i++) {
								if ($optionsSection.find('input[name="'+parents[i]+'"]').is(':checked')) {
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
		$optionsSection.find('input[name^="options[formats]"], select[name^="options[formats]"]').on('change', function () {
			var builder = _this.adminPage.getTabModel('builder');
			if (builder) {
				builder.setFormats();
				builder.changeFormat = true;
				builder.changeFormatType = 'column';
				builder.edited = true;
			}
		});
		$optionsSection.find('select').on('change pyt-change', function () {
			var $this = $(this),
				value = $this.val(),
				hidden = $this.closest('.pyt-option-wrapper').hasClass('pytHidden'),
				subOptions = $optionsSection.find('.pyt-option-wrapper[data-parent="'+$this.attr('name')+'"]');
			if(subOptions.length) {
				subOptions.addClass('pytHidden');
				if(!hidden) subOptions.filter('[data-parent-value~="'+value+'"]').removeClass('pytHidden');
				subOptions.find('input[type="checkbox"]').trigger('pyt-change');
			}
		});
		$optionsSection.find('input, select, textarea').on('change', function () {
			var $this = $(this);
			if ($this.closest('[data-not-preview=1]').length == 0) { 
				_this.needPreview = true;
				if ($this.closest('[data-need-save=1]').length) _this.needSave = true;
				_this.showPreview();
			}
		});

		// Loader settings
		var $loaderPreview = $optionsSection.find('.pyt-loader-preview'),
			$loaderName = $optionsSection.find('input[name="options[loader_name]"]'),
			$loaderCount = $optionsSection.find('input[name="options[loader_count]"]'),
			$loaderColor = $optionsSection.find('input[name="options[loader_color]"]'),
			$loaderPopup = $('#pytLoaderIconDialog'),
			dialogLoader = $loaderPopup.dialog({
				modal: true,
				autoOpen: false,
				width: 900,
				height: 'auto',
				dialogClass: 'pubydoc-plugin',
				classes: {
					'ui-dialog': 'pubydoc-plugin'
				},
				buttons: [
					{
						text: $loaderPopup.data('cancel'),
						class: 'button button-secondary',
						click: function() {
							$(this).dialog('close');
						}
					}
				],
				open: function(event, ui) {
					var color = $loaderColor.val();
					$loaderPopup.find('.spinner').css('backgroundColor', color);
					$loaderPopup.find('.pubydoc-table-loader').css('color', color);
				},
				create: function( event, ui ) {
					$(this).parent().css('maxWidth', $(window).width()+'px');
				}
			});
		$loaderPopup.find('.item-inner').on('click', function (e) {
			e.preventDefault();
			var el = $(this);

			$loaderName.val(el.find('.preicon_img').attr('data-name'));
			$loaderCount.val(el.find('.preicon_img').attr('data-items'));
			setLoaderPreview();
			dialogLoader.dialog('close');
		});
		function setLoaderPreview() {
			var name = $loaderName.val(),
				color = $loaderColor.val(),
				dataItems = $loaderCount.val();
			$loaderPreview.html('');
			if (color.length == 0) color = '#000000';

			if (name === 'spinner') {
				$loaderPreview.html('<div class="pubydoc-table-loader spinner" style="background-color:' + color + '"></div>');
			} else {
				var htmlIcon = ' <div class="pubydoc-table-loader la-' + name + ' la-2x" style="color: ' + color + ';">';
				for (var i = 0; i < dataItems; i++) {
					htmlIcon += '<div></div>';
				}
				htmlIcon += '</div>';
				$loaderPreview.html(htmlIcon);
			}
		}

		$optionsSection.find('.pyt-loader-select').on('click', function (e) {
			e.preventDefault();		
			dialogLoader.dialog('open');
		});
		$loaderColor.on('change', function() {
			setLoaderPreview();
		});
		setLoaderPreview();

		// Table Styling
		var styleId = _this.previewStyleId,
			wrapSelector = _this.previewWrapSelector,
			tableSelector = wrapSelector + ' .pyt-table';

		$optionsWrap.find('input[name="options[custom_css]"]').on('change pyt-init', function() {
			pytSetStyleSheetRules(styleId, [{selector: wrapSelector + ' table', param: 'border-collapse', value: 'collapse'}]);
			_this.disablePreviewCss(!$(this).is(':checked'));
			var iTable = front.getTableInstance('preview');
			$(window).trigger('resize');
			if (iTable) iTable.columns.adjust().draw(false);
		}).trigger('pyt-init');
		
		$optionsWrap.find('input[name="options[styles][external_border_width]"]').on('change pyt-init', function(e) {
			pytSetExternalBorder(e.type);
		});
		$optionsWrap.find('input[name="options[styles][external_border_color]"]').on('change', function() {
			pytSetExternalBorder('change');
		});
		function pytSetExternalBorder(typ) {
			var cBorder = $optionsWrap.find('input[name="options[styles][external_border_color]"]').val(),
				wBorder = $optionsWrap.find('input[name="options[styles][external_border_width]"]').val(),
				isFill = wBorder.length && cBorder.length;
			if (isFill || typ == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: tableSelector, param: 'border', value: isFill ? wBorder + 'px solid ' + cBorder + ' !important' : ''},
					{selector: wrapSelector + ' .dataTables_scroll', param: 'border', value: isFill ? wBorder + 'px solid ' + cBorder + ' !important' : ''},
					{selector: wrapSelector + ' .DTFC_ScrollWrapper', param: 'border', value: isFill ? wBorder + 'px solid ' + cBorder + ' !important' : ''},
					{selector: wrapSelector + ' .DTFC_ScrollWrapper .dataTables_scroll', param: 'border', value: isFill ? 'none !important' : ''},
					{selector: wrapSelector + ' .dataTables_scrollBody table', param: 'border', value: isFill ? 'none !important' : ''},
				]);
			}
		}

		$optionsWrap.find('input[name="options[styles][header_border_width]"]').on('change pyt-init', function(e) {
			pytSetHeaderBorder(e.type);
		});
		$optionsWrap.find('input[name="options[styles][header_border_color]"]').on('change', function() {
			pytSetHeaderBorder('change');
		});
		function pytSetHeaderBorder(typ) {
			var cBorder = $optionsWrap.find('input[name="options[styles][header_border_color]"]').val(),
				wBorder = $optionsWrap.find('input[name="options[styles][header_border_width]"]').val(),
				isFill = wBorder.length && cBorder.length;
			if (isFill || typ == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: wrapSelector + ' th', param: 'border', value: isFill ? wBorder + 'px solid ' + cBorder + ' !important' : ''},
					{selector: wrapSelector + ' .dataTables_scrollBody th', param: 'border-bottom', value: isFill ? 'none !important' : ''},
					{selector: wrapSelector + ' .dataTables_scrollBody th', param: 'border-top', value: isFill ? 'none !important' : ''},
					{selector: wrapSelector + ' .DTFC_LeftBodyWrapper th', param: 'border-bottom', value: isFill ? 'none !important' : ''},
					{selector: wrapSelector + ' .DTFC_LeftBodyWrapper th', param: 'border-top', value: isFill ? 'none !important' : ''},
					{selector: wrapSelector + ' .DTFC_RightBodyWrapper th', param: 'border-bottom', value: isFill ? 'none !important' : ''},
					{selector: wrapSelector + ' .DTFC_RightBodyWrapper th', param: 'border-top', value: isFill ? 'none !important' : ''},
					{selector: wrapSelector + ' .child table', param: 'border-collapse', value: isFill ? 'collapse' : ''},
				]);
			}
		}

		$optionsWrap.find('input[name="options[styles][row_border_width]"]').on('change pyt-init', function(e) {
			pytSetRowBorder(e.type);
			
		});
		$optionsWrap.find('input[name="options[styles][row_border_color]"]').on('change', function() {
			pytSetRowBorder('change');
		});
		function pytSetRowBorder(typ) {
			var wBorder = $optionsWrap.find('input[name="options[styles][row_border_width]"]').val(),
				cBorder = $optionsWrap.find('input[name="options[styles][row_border_color]"]').val(),
				isFill = wBorder.length && cBorder.length;
			if (isFill || typ == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: wrapSelector + ' td', param: 'border-top', value: isFill ? wBorder + 'px solid ' + cBorder + ' !important' : ''},
					{selector: wrapSelector + ' tbody tr:first-child td', param: 'border-top', value: isFill ? 'none' : ''},
					{selector: wrapSelector + ' tbody tr:last-child td', param: 'border-bottom', value: isFill ? wBorder + 'px solid ' + cBorder + ' !important' : ''},
					{selector: wrapSelector + ' .child table', param: 'border-collapse', value: isFill ? 'collapse' : ''}
				]);
			}
		}

		$optionsWrap.find('input[name="options[styles][col_border_width]"]').on('change pyt-init', function(e) {
			pytSetColBorder(e.type);
		});
		$optionsWrap.find('input[name="options[styles][col_border_color]"]').on('change', function() {
			pytSetColBorder('change');
		});
		function pytSetColBorder(typ) {
			var cBorder = $optionsWrap.find('input[name="options[styles][col_border_color]"]').val(),
				wBorder = $optionsWrap.find('input[name="options[styles][col_border_width]"]').val(),
				isFill = wBorder.length && cBorder.length;
			if (isFill || typ == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: wrapSelector + ' td', param: 'border-left', value: isFill ? wBorder + 'px solid ' + cBorder + ' !important' : ''},
					{selector: wrapSelector + ' td', param: 'border-right', value: isFill ? wBorder + 'px solid ' + cBorder + ' !important' : ''},
					{selector: wrapSelector + ' .child table', param: 'border-collapse', value: isFill ? 'collapse' : ''},
					{selector: wrapSelector + ' tbody tr:first-child td', param: 'border-top', value: isFill ? 'none' : ''}
				]);
			}
		}

		$optionsWrap.find('input[name="options[styles][header_bg_color]"]').on('change pyt-init', function(e) {
			var color = $(this).val();
			if (color.length || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: wrapSelector + ' th', param: 'background-color', value: color + ' !important'}
				]);
			}
		});
		$optionsWrap.find('input[name="options[styles][header_font_color]"]').on('change pyt-init', function(e) {
			var color = $(this).val();
			if (color.length || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: wrapSelector + ' th', param: 'color', value: color}
				]);
			}
		});
		$optionsWrap.find('input[name="options[styles][header_font_size]"]').on('change pyt-init', function(e) {
			var size = $(this).val();
			if (size.length || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: wrapSelector + ' th', param: 'font-size', value: size.length ? size + 'px' : ''}
				]);
			}
		});
		$optionsWrap.find('input[name="options[styles][cell_bg_color]"]').on('change pyt-init', function(e) {
			var color = $(this).val();
			if (color.length || e.type == 'change') {
				var even = pytLightenDarkenColor(color, -20),
					hover = pytLightenDarkenColor(color, -40);
				pytSetStyleSheetRules(styleId, [
					{selector: wrapSelector + ' table.dataTable tbody tr', param: 'background-color', value: color},
					{selector: wrapSelector + ' table.dataTable.stripe tbody tr.odd', param: 'background-color', value: color},
					{selector: wrapSelector + ' table.dataTable.stripe tbody tr.even', param: 'background-color', value: even},
					{selector: wrapSelector + ' table.dataTable.order-column tbody tr > .sorting_1', param: 'background-color', value: even},
					{selector: wrapSelector + ' table.dataTable.stripe.order-column tbody tr.odd > .sorting_1', param: 'background-color', value: even},
					{selector: wrapSelector + ' table.dataTable.hover tbody tr:hover', param: 'background-color', value: hover},
					{selector: wrapSelector + ' table.dataTable.stripe.order-column tbody tr.even > .sorting_1', param: 'background-color', value: hover},
					{selector: wrapSelector + ' table.dataTable.order-column.stripe tbody tr.even>.sorting_1', param: 'background-color', value: even},
					{selector: wrapSelector + ' table.dataTable.hover.order-column tbody tr:hover > .sorting_1', param: 'background-color', value: pytLightenDarkenColor(color, -60)},
					{selector: wrapSelector + ' table.dataTable tbody tr td', param: 'background-color', value: 'inherit'},
				]);
			}
		});
		$optionsWrap.find('input[name="options[styles][cell_font_color]"]').on('change pyt-init', function(e) {
			var color = $(this).val();
			if (color.length || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: wrapSelector + ' td', param: 'color', value: color}
				]);
			}
		});
		$optionsWrap.find('input[name="options[styles][cell_font_size]"]').on('change pyt-init', function(e) {
			var size = $(this).val();
			if (size.length || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: wrapSelector + ' td', param: 'font-size', value: size.length ? size + 'px' : ''}
				]);
			}
		});
		$optionsWrap.find('select[name="options[styles][header_font_family]"]').on('change pyt-init', function(e) {
			var family = $(this).val();
			if (family.length || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: wrapSelector + ' th', param: 'font-family', value: family}
				]);
				if(family.length) _this.adminPage.importFont(family);
			}
		});
		$optionsWrap.find('select[name="options[styles][cell_font_family]"]').on('change pyt-init', function(e) {
			var family = $(this).val();
			if (family.length || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: wrapSelector + ' td', param: 'font-family', value: family}
				]);
				if(family.length) _this.adminPage.importFont(family);
			}
		});

		var searchSelector = wrapSelector + ' .dataTables_filter input, ' + wrapSelector + ' .pyt-col-search-wrap input';
		$optionsWrap.find('input[name="options[styles][search_bg_color]"]').on('change pyt-init', function(e) {
			var color = $(this).val();
			if (color.length || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: searchSelector, param: 'background-color',	value: color.length ? color + ' !important' : ''}
				]);
			}
		});
		$optionsWrap.find('input[name="options[styles][search_font_color]"]').on('change pyt-init', function(e) {
			var color = $(this).val();
			if (color.length || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: searchSelector, param: 'color',	value: color.length ? color + ' !important' : ''}
				]);
			}
		});
		$optionsWrap.find('input[name="options[styles][search_border_color]"]').on('change pyt-init', function(e) {
			var color = $(this).val();
			if (color.length || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: searchSelector, param: 'border',	value: color.length ? '1px solid ' + color + ' !important' : ''}
				]);
			}
		});
		$optionsWrap.find('input[name="options[styles][fixed_layout]"]').on('change pyt-init', function(e) {
			var checked = $(this).is(':checked');
			if (checked || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: tableSelector, param: 'table-layout', value: checked ? 'fixed !important' : ''},
					{selector: tableSelector, param: 'overflow-wrap', value: checked ? 'break-word' : ''},
					{selector: wrapSelector + ' .dataTables_scroll table', param: 'table-layout', value: checked ? 'fixed !important' : ''},
					{selector: wrapSelector + ' .dataTables_scroll table', param: 'overflow-wrap', value: checked ? 'break-word' : ''},
				]);
			}		
		});
		$optionsWrap.find('select[name="options[styles][vertical_alignment]"]').on('change pyt-init', function(e) {
			var align = $(this).val();
			if (align.length || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: tableSelector + ' th, ' + tableSelector + ' td', param: 'vertical-align', value: align}
				]);
			}
		});
		$optionsWrap.find('select[name="options[styles][horizontal_alignment]"]').on('change pyt-init', function(e) {
			var align = $(this).val();
			if (align.length || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: tableSelector + ' th, ' + tableSelector + ' td', param: 'text-align', value: align}
				]);
			}
		});
		$optionsWrap.find('select[name="options[styles][paging_position]"]').on('change pyt-init', function(e) {
			var position = $(this).val();
			if (position.length || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: wrapSelector + ' .dataTables_paginate', param: 'text-align', value: position},
					{selector: wrapSelector + ' .dataTables_paginate', param: 'float', value: position.length ? 'none' : ''}
				]);
			}
		});
		$optionsWrap.find('input[name="options[styles][sorting_hover]"]').on('change pyt-init', function(e) {
			var checked = $(this).is(':checked');
			if (checked || e.type == 'change') {
				pytSetStyleSheetRules(styleId, [
					{selector: wrapSelector + ' table .sorting', param: 'background-image', value: checked ? 'none !important' : ''},
					{selector: wrapSelector + ' table th.sorting:hover', param: 'background-image', value: checked ? 'url("'+PYT_DATA.libPath.replace(PYT_DATA.siteUrl, '/')+'datatables/images/sort_both.png") !important' : ''}
				]);
			}
		});
		$optionsWrap.find('[name^="options[styles]["]').trigger('pyt-init');
	}
	OptionsModel.prototype.disablePreviewCss = function (mode) {
		var obj = document.getElementById(this.$obj.previewStyleId),
			sheet = obj.sheet || obj.styleSheet;
		sheet.disabled = mode;
	}
	OptionsModel.prototype.isDisablePreviewCss = function () {
		var obj = document.getElementById(this.$obj.previewStyleId),
			sheet = obj.sheet || obj.styleSheet;
		return sheet.disabled;
	}
	OptionsModel.prototype.switchPreviewNotice = function (typ) {
		$('.pyt-preview-notice').addClass('pytHidden');
		if (typ) $('.pyt-preview-notice[data-type="' + typ + '"]').removeClass('pytHidden');
	}

	OptionsModel.prototype.showPreview = function () {
		var _this = this.$obj,
			page = _this.adminPage,
			previewContainer = _this.$previewContainer;
		if(!page.previewEnabled || !_this.needPreview) {
			previewContainer.find('.dataTables_processing').hide();
			return;
		}
		
		if(_this.previewTimeout && Date.now() - _this.previewTimeout < 5000) {
			setTimeout(function() {	_this.showPreview(); }, 50);
			return;
		}
		if(_this.previewing || page.saving) {
			if(!_this.previewTimeout) _this.previewTimeout = Date.now();
			setTimeout(function() {	_this.showPreview(); }, 500);
			return;
		}
		
		_this.switchPreviewNotice('loading');
		if(_this.needSave) {
			$('#pytBtnSave').trigger('click');
			setTimeout(function() {	_this.showPreview(); }, 500);
		}

		_this.previewTimeout = false;
		_this.previewing = true;
		_this.needPreview = false;
		var front = _this.tablesFront;
		previewContainer.find('.dataTables_processing').show();

		$.sendFormPyt({
			data: {
				mod: 'tables',
				action: 'getTablePreview',
				tableId: page.tableId,
				options: jsonInputsPyt(_this.$optionsSection),
				customCss: pytGetStyleSheetRules(page.stylesElemId, '#__pyt ') + pytGetStyleSheetRules(page.addStylesElemId, '#__pyt ', false, false)
			},
			onComplete: function (res) {
				_this.previewing = false;
			},
			onSuccess: function(res) {
				if (!res.error && res.html.length > 0) {
					previewContainer.addClass('pytHidden');
					front.destroyTable('preview');
					previewContainer.html('').append($(res.css + res.html));
					front.initTablesOnPage('preview');
					previewContainer.removeClass('pytHidden');
					front.refreshTable('preview');
					_this.switchPreviewNotice('finished');
					jQuery(window).trigger('resize');
				} else {
					_this.switchPreviewNotice('empty');
				}
			}
		});
	}


// ********************* OPTIONS MODEL **************************** //
// ================================================================ //


// ================================================================ //
// ********************** ADDCSS MODEL *************************** //

	function CssModel() {
		this.$obj = this;
		return this.$obj;
	}
	CssModel.prototype.init = (function () {
		var _this = this.$obj;
		_this.adminPage = app.pytAdminTablePage;
		_this.cssText = $('#pytCssEditor').get(0);
		_this.addStyleId = _this.adminPage.addStylesElemId;
		pytCreateStyleElem(_this.addStyleId);
		_this.setAddStyles(_this.cssText.value);

		_this.edited = false;
	});
	CssModel.prototype.refresh = function () {
		var _this = this.$obj,
			cssText = _this.cssText;

		if(typeof(cssText.CodeMirrorEditor) === 'undefined') {
			if(typeof(CodeMirror) !== 'undefined') {
				var cssEditor = CodeMirror.fromTextArea(cssText, {
					mode: 'css',
					lineWrapping: true,
					lineNumbers: true,
					matchBrackets: true,
					autoCloseBrackets: true
				});
				cssEditor.on('change', function(cm) {
					_this.edited = true;
				});
				cssEditor.on('blur', function(cm) {
					if (_this.edited) {
						_this.setAddStyles(cm.getValue());
						_this.adminPage.setNeedPreview();
					}
				});
				cssText.CodeMirrorEditor = cssEditor;
			}
		} else {
			cssText.CodeMirrorEditor.refresh();
		}
	}
	CssModel.prototype.setAddStyles = function (value) {
		var id = this.$obj.addStyleId;
		$('style#' + id).html(value);
		pytDisableStyleElem(id, true);
	}

	CssModel.prototype.save = function() {
		var _this = this.$obj;
		if(_this.adminPage.saving) {
			setTimeout(function() {	_this.save(); }, 2000);
			return;
		}

		if (!_this.edited) return;
		_this.adminPage.saving = true;

		$.sendFormPyt({
			btn: $('#pytBtnSave'),
			data: {
				mod: 'tables',
				action: 'saveTableAddCss',
				tableId: _this.adminPage.tableId,
				add_css: typeof(_this.cssText.CodeMirrorEditor) !== 'undefined' ? _this.cssText.CodeMirrorEditor.getValue() : ''
			},
			onComplete: function (res) {
				_this.adminPage.saving = false;
			},
			onSuccess: function(res) {
				if (!res.error) {
					_this.edited = false;
				}
			}
		});
	}

// ********************* ADDCSS MODEL **************************** //
// ================================================================ //

// ================================================================ //
// ********************** ADDJS MODEL *************************** //

	function JsModel() {
		this.$obj = this;
		//this.saveBeforePreview = true;
		return this.$obj;
	}
	JsModel.prototype.init = (function () {
		var _this = this.$obj;
		_this.adminPage = app.pytAdminTablePage;
		_this.jsText = $('#pytJsEditor').get(0);

		_this.edited = false;
	});
	JsModel.prototype.refresh = function () {
		var _this = this.$obj,
			jsText = _this.jsText;

		if(typeof(jsText.CodeMirrorEditor) === 'undefined') {
			if(typeof(CodeMirror) !== 'undefined') {
				var jsEditor = CodeMirror.fromTextArea(jsText, {
					mode: 'JavaScript',
					lineWrapping: true,
					lineNumbers: true,
					matchBrackets: true,
					autoCloseBrackets: true
				});
				jsEditor.on('change', function(cm) {
					_this.edited = true;
				});
				jsText.CodeMirrorEditor = jsEditor;
			}
		} else {
			jsText.CodeMirrorEditor.refresh();
		}
	}
	JsModel.prototype.save = function() {
		var _this = this.$obj;
		if(_this.adminPage.saving) {
			setTimeout(function() {	_this.save(); }, 2000);
			return;
		}

		if (!_this.edited) return;
		_this.adminPage.saving = true;

		$.sendFormPyt({
			btn: $('#pytBtnSave'),
			data: {
				mod: 'tables',
				action: 'saveTableAddJs',
				tableId: _this.adminPage.tableId,
				add_js: typeof(_this.jsText.CodeMirrorEditor) !== 'undefined' ? _this.jsText.CodeMirrorEditor.getValue() : ''
			},
			onComplete: function (res) {
				_this.adminPage.saving = false;
			},
		});
	}

// ********************* ADDJS MODEL **************************** //
// ================================================================ //
	
	app.pytTabModels.css = new CssModel();
	app.pytTabModels.js = new JsModel();
	app.pytTabModels.options = new OptionsModel();

	app.pytAdminTablePage = new AdminPage();

	jQuery(document).ready(function () {
		app.pytAdminTablePage.init();
	});

}(window.jQuery, window));
