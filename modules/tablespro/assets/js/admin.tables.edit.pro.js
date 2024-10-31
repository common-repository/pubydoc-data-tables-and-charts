(function ($, app) {
"use strict";
	var AdminPage = app.pytAdminTablePage,
		OptionsModel = app.pytTabModels.options;

	AdminPage.constructor.prototype.initPro = function () {
		var _this = this.$obj;
			_this.settingsPro = pytParseJSON($('#pytSettingsPro').val());
		if ('standartFonts' in _this.settingsPro) {
			_this.importedFonts = _this.settingsPro['standartFonts'];
		}
	}
	AdminPage.constructor.prototype.importFont = function (font) {
		if (!font || font.length == 0 || font == 'inherit') return;
		var _this = this.$obj;
		if (pytImportedFonts.indexOf(font) != -1) return;
		pytStyleSheetImportFF(pytStyleFontsId, font);
		pytImportedFonts.push(font);
	}

	OptionsModel.constructor.prototype.initPro = function () {
		this.$obj.eventsOptionsModelPro();
	}
	OptionsModel.constructor.prototype.eventsOptionsModelPro = function () {
		var _this = this.$obj,
			$optionsSection = _this.$optionsSection;
		$optionsSection.find('.pyt-icon-select').on('click', function(e){
			e.preventDefault();
			var $button = $(this),
				$wrap = $button.closest('.pyt-option-wrapper'),
				$input = $wrap.find('input.pyt-icon-input'),
				$preview = $wrap.find('.pyt-icon-preview'),
				_custom_media = true;
			wp.media.editor.send.attachment = function(props, attachment){
				wp.media.editor._attachSent = true;
				if (_custom_media) {
					var selectedUrl = attachment.url,
						imgWidth = attachment.width,
						imgHeight = attachment.height;
					if (props && props.size && attachment.sizes && attachment.sizes[props.size] && attachment.sizes[props.size].url) {
						var imgSize = attachment.sizes[props.size];
						selectedUrl = imgSize.url;
						imgWidth = imgSize.width;
						imgHeight = imgSize.height;
					}
					$input.val('background-image:url('+selectedUrl+');width:'+imgWidth+'px!important;height:'+imgHeight+'px!important;');
					$preview.css('background', '');
					$preview.css({'background-image': 'url('+selectedUrl+')', 'width': imgWidth+'px', 'height': imgHeight+'px' });
				} else {
					return _orig_send_attachment.apply( this, [props, attachment] );
				}
			};
			wp.media.editor.insert = function(html) {
				if (_custom_media) {
					if (wp.media.editor._attachSent) {
						wp.media.editor._attachSent = false;
						return;
					}
					if (html && html != "") {
						var selectedUrl = $(html).attr('src');
						if (selectedUrl) {
							$input.val('background-image:url('+selectedUrl+');');
							$preview.css('background', '');
							$preview.css('background-image', 'url('+selectedUrl+')');
						}
					}
				}
			};
			wp.media.editor.open($button);
			return false;
		});
	}
}(window.jQuery, window));
