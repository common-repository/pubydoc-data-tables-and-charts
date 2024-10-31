"use strict";
var pytAdminFormChanged = [];
window.onbeforeunload = function(){
	// If there are at lease one unsaved form - show message for confirnation for page leave
	if(pytAdminFormChanged.length)
		return 'Some changes were not-saved. Are you sure you want to leave?';
};
jQuery(document).ready(function(){
	if(typeof(pytActiveTab) != 'undefined' && pytActiveTab != 'main_page' && jQuery('#toplevel_page_pyt-comparison-slider').hasClass('wp-has-current-submenu')) {
		var subMenus = jQuery('#toplevel_page_pyt-comparison-slider').find('.wp-submenu li');
		subMenus.removeClass('current').each(function(){
			if(jQuery(this).find('a[href$="&tab='+ pytActiveTab+ '"]').size()) {
				jQuery(this).addClass('current');
			}
		});
	}

	var settingsValues = jQuery('.pubydoc-panel');

	settingsValues.on('change pyt-change', 'input[type="checkbox"]', function () {
		var elem = jQuery(this),
			valueWrapper = elem.closest('.options-value'),
			name = elem.attr('name'),
			block = settingsValues,
			childrens = block.find('.row-options-block[data-parent="' + name + '"], .options-value[data-parent="' + name + '"]');
		if(childrens.length > 0) {
			if(elem.is(':checked') && (valueWrapper.length == 0 || !valueWrapper.hasClass('pubydoc-hidden'))) childrens.removeClass('pubydoc-hidden');
			else childrens.addClass('pubydoc-hidden');
			childrens.find('select,input[type="checkbox"]').trigger('pyt-change');
		}
	});
	settingsValues.on('change pyt-change', 'select', function () {
		var elem = jQuery(this),
			value = elem.val(),
			hidden = elem.closest('.options-value').hasClass('pytHidden'),
			name = elem.attr('name'),
			block = settingsValues,
			subOptions = block.find('.row-options-block[data-select="' + name + '"], .options-value[data-select="' + name + '"]');
		if(subOptions.length) {
			subOptions.addClass('pubydoc-hidden');
			if(!hidden) subOptions.filter('[data-select-value*="'+value+'"]').removeClass('pubydoc-hidden');
		}
	});
	
	pytInitStickyItem();

	jQuery('.navigation-bar').on('click', function() {
		var navMenu = jQuery('.pubydoc-navigation');

		if (navMenu.hasClass('pubydoc-navigation-show')) navMenu.removeClass('pubydoc-navigation-show');
		else navMenu.addClass('pubydoc-navigation-show');		
	});
	
	jQuery('.pytFieldsetToggled').each(function(){
		var self = this;
		jQuery(self).find('.pytFieldsetContent').hide();
		jQuery(self).find('.pytFieldsetToggleBtn').click(function(){
			var icon = jQuery(this).find('i')
			,	show = icon.hasClass('fa-plus');
			show ? icon.removeClass('fa-plus').addClass('fa-minus') : icon.removeClass('fa-minus').addClass('fa-plus');
			jQuery(self).find('.pytFieldsetContent').slideToggle( 300, function(){
				if(show) {
					jQuery(this).find('textarea').each(function(i, el){
						if(typeof(this.CodeMirrorEditor) !== 'undefined') {
							this.CodeMirrorEditor.refresh();
						}
					});
				}
			} );
			return false;
		});
	});
	pytInitTooltips();
	jQuery(document.body).on('changeTooltips', function (e) {
		pytInitTooltips(e.target);
	});
	jQuery('.pubydoc-panel').on('click focus', '.pubydoc-shortcode', function(e) {
		e.preventDefault();
		this.setSelectionRange(0, this.value.length);
	});
	jQuery('.pubydoc-namefile').disableSelection();
	jQuery('.pubydoc-inputfile input').on('change', function(e) {
		e.preventDefault();
		jQuery(this).parent('.pubydoc-inputfile').find('.pubydoc-namefile').html(this.files.length ? this.files[0].name : '');
	});

	// Check for showing review notice after a week usage
	pytInitPlugNotices();
	jQuery('.pubydoc-plugin-loader').css('display', 'none');
	jQuery('.pubydoc-main').css('display', 'block');
	var multySelects = jQuery('.pubydoc-panel select.pubydoc-chosen');
	if (multySelects.length) {
		multySelects.chosen({width: "100%"});
		multySelects.on('change', function (e, info) {
			if (info.selected) {
				var allSelected = this.querySelectorAll('option[selected]'),
					lastSelected = allSelected[allSelected.length - 1],
					selected = this.querySelector(`option[value="${info.selected}"]`);
				selected.setAttribute('selected', '');
				if (lastSelected) lastSelected.insertAdjacentElement('afterEnd', selected);
				else this.insertAdjacentElement('afterbegin', selected);
			} else {
				var removed = this.querySelector(`option[value="${info.deselected}"]`);
				removed.setAttribute('selected', false); // this step is required for Edge
				removed.removeAttribute('selected');
			}
			jQuery(this).trigger('chosen:updated');
		});

	}
	jQuery('.pubydoc-plugin .tooltipstered').removeAttr("title");
});
function pytInitTooltips( selector ) {
	var tooltipsterSettings = {
			contentAsHTML: true,
			interactive: true,
			speed: 0,
			delay: 200,
			maxWidth: 450
		},
		findPos = {
			'.pubydoc-tooltip': 'top-left',
			'.pubydoc-tooltip-bottom': 'bottom-left',
			'.pubydoc-tooltip-left': 'left',
			'.pubydoc-tooltip-right': 'right'
		},
		$findIn = selector ? jQuery( selector ) : false;
	for(var k in findPos) {
		if(typeof(k) === 'string') {
			var $tips = $findIn ? $findIn.find( k ) : jQuery( k ).not('.no-tooltip');
			if($tips && $tips.size()) {
				tooltipsterSettings.position = findPos[ k ];
				// Fallback for case if library was not loaded
				if(!$tips.tooltipster) continue;
				$tips.tooltipster( tooltipsterSettings );
			}
		}
	}
	if ($findIn) {
		$findIn.find('.tooltipstered').removeAttr('title');
	}
}
function pytInitCheckAll(elem, preName) {
	if (typeof preName == 'undefined') var preName = 'pytCheck';
	var main = elem.find('.' + preName + 'All');
	if (main.length) {
		main.on('change', function(e) {
			e.preventDefault();
			elem.find('.' + preName + 'One').prop('checked', jQuery(this).is(':checked'));
		});
		elem.on('change', '.' + preName + 'One', function(e){
			e.preventDefault();
			if (!jQuery(this).is(':checked')) {
				main.prop('checked', false);
			}
		});
	}
}
function changeAdminFormPyt(formId) {
	if(jQuery.inArray(formId, pytAdminFormChanged) == -1)
		pytAdminFormChanged.push(formId);
}
function adminFormSavedPyt(formId) {
	if(pytAdminFormChanged.length) {
		for(var i in pytAdminFormChanged) {
			if(pytAdminFormChanged[i] == formId) {
				pytAdminFormChanged.pop(i);
			}
		}
	}
}
function checkAdminFormSaved() {
	if(pytAdminFormChanged.length) {
		if(!confirm('Some changes were not-saved. Are you sure you want to leave?')) {
			return false;
		}
		pytAdminFormChanged = [];	// Clear unsaved forms array - if user wanted to do this
	}
	return true;
}
function isAdminFormChanged(formId) {
	if(pytAdminFormChanged.length) {
		for(var i in pytAdminFormChanged) {
			if(pytAdminFormChanged[i] == formId) {
				return true;
			}
		}
	}
	return false;
}
/*Some items should be always on users screen*/
function pytInitStickyItem() {
	jQuery(window).scroll(function(){
		var stickiItemsSelectors = ['.pubydoc-sticky']
		,	elementsUsePaddingNext = ['.pubydoc-bar']	// For example - if we stick row - then all other should not offest to top after we will place element as fixed
		,	wpTollbarHeight = 32
		,	wndScrollTop = jQuery(window).scrollTop() + wpTollbarHeight
		,	footer = jQuery('.pytAdminFooterShell')
		,	footerHeight = footer && footer.size() ? footer.height() : 0
		,	docHeight = jQuery(document).height()
		,	wasSticking = false
		,	wasUnSticking = false;
		for(var i = 0; i < stickiItemsSelectors.length; i++) {
			jQuery(stickiItemsSelectors[ i ]).each(function(){
				var element = jQuery(this);
				if(element && element.size() && !element.hasClass('sticky-ignore')) {
					var scrollMinPos = element.offset().top
					,	prevScrollMinPos = parseInt(element.data('scrollMinPos'))
					,	useNextElementPadding = toeInArrayPyt(stickiItemsSelectors[ i ], elementsUsePaddingNext) || element.hasClass('sticky-padd-next')
					,	currentScrollTop = wndScrollTop
					,	calcPrevHeight = element.data('prev-height')
					,	currentBorderHeight = wpTollbarHeight
					,	usePrevHeight = 0;
					if(calcPrevHeight) {
						usePrevHeight = jQuery(calcPrevHeight).outerHeight();
						currentBorderHeight += usePrevHeight;
					}
					if(currentScrollTop > scrollMinPos && !element.hasClass('pubydoc-sticky-active')) {	// Start sticking
						if(element.hasClass('sticky-save-width')) {
							element.width( element.width() );
						}
						element.addClass('pubydoc-sticky-active').data('scrollMinPos', scrollMinPos).css({
							'top': currentBorderHeight
						});
						if(useNextElementPadding) {
							var nextElement = element.next();
							if(nextElement && nextElement.size()) {
								nextElement.data('prevPaddingTop', nextElement.css('padding-top'));
								var addToNextPadding = parseInt(element.data('next-padding-add'));
								addToNextPadding = addToNextPadding ? addToNextPadding : 0;
								nextElement.css({
									'padding-top': (element.hasClass('sticky-outer-height') ? element.outerHeight() : element.height()) + usePrevHeight + addToNextPadding
								});
							}
						}
						wasSticking = true;
						element.trigger('startSticky');
					} else if(!isNaN(prevScrollMinPos) && currentScrollTop <= prevScrollMinPos) {	// Stop sticking
						element.removeClass('pubydoc-sticky-active').data('scrollMinPos', 0).css({
							'top': 0
						});
						if(element.hasClass('sticky-save-width')) {
							if(element.hasClass('sticky-base-width-auto')) {
								element.css('width', 'auto');
							}
						}
						if(useNextElementPadding) {
							var nextElement = element.next();
							if(nextElement && nextElement.size()) {
								var nextPrevPaddingTop = parseInt(nextElement.data('prevPaddingTop'));
								if(isNaN(nextPrevPaddingTop))
									nextPrevPaddingTop = 0;
								nextElement.css({
									'padding-top': nextPrevPaddingTop
								});
							}
						}
						element.trigger('stopSticky');
						wasUnSticking = true;
					} else {	// Check new stick position
						if(element.hasClass('pubydoc-sticky-active')) {
							if(footerHeight) {
								var elementHeight = element.height()
								,	heightCorrection = 32
								,	topDiff = docHeight - footerHeight - (currentScrollTop + elementHeight + heightCorrection);
								if(topDiff < 0) {
									element.css({
										'top': currentBorderHeight + topDiff
									});
								} else {
									element.css({
										'top': currentBorderHeight
									});
								}
							}
							// If at least on element is still sticking - count it as all is working
							wasSticking = wasUnSticking = false;
						}
					}
				}
			});
		}
	});
}
function pytGetTxtEditorVal(id) {
	if(typeof(tinyMCE) !== 'undefined' 
		&& tinyMCE.get( id ) 
		&& !jQuery('#'+ id).is(':visible') 
		&& tinyMCE.get( id ).getDoc 
		&& typeof(tinyMCE.get( id ).getDoc) == 'function' 
		&& tinyMCE.get( id ).getDoc()
	)
		return tinyMCE.get( id ).getContent();
	else
		return jQuery('#'+ id).val();
}
function pytSetTxtEditorVal(id, content) {
	if(typeof(tinyMCE) !== 'undefined' 
		&& tinyMCE 
		&& tinyMCE.get( id ) 
		&& !jQuery('#'+ id).is(':visible')
		&& tinyMCE.get( id ).getDoc 
		&& typeof(tinyMCE.get( id ).getDoc) == 'function' 
		&& tinyMCE.get( id ).getDoc()
	)
		tinyMCE.get( id ).setContent(content);
	else
		jQuery('#'+ id).val( content );
}

function prepareToPlotDate(data) {
	if(typeof(data) === 'string') {
		if(data) {
			data = pytStrReplace(data, '/', '-');
			return (new Date(data)).getTime();
		}
	}
	return data;
}
function pytInitPlugNotices() {
	var $notices = jQuery('.pubydoc-admin-notice');
	if($notices && $notices.size()) {
		$notices.each(function(){
			jQuery(this).find('.notice-dismiss').click(function(){
				var $notice = jQuery(this).parents('.pubydoc-admin-notice');
				if(!$notice.data('stats-sent')) {
					// User closed this message - that is his choise, let's respect this and save it's saved status
					jQuery.sendFormPyt({
						data: {mod: 'adminmenu', action: 'addNoticeAction', code: $notice.data('code'), choice: 'hide'}
					});
				}
			});
			jQuery(this).find('[data-statistic-code]').click(function(){
				var href = jQuery(this).attr('href')
				,	$notice = jQuery(this).parents('.pubydoc-admin-notice');
				jQuery.sendFormPyt({
					data: {mod: 'adminmenu', action: 'addNoticeAction', code: $notice.data('code'), choice: jQuery(this).data('statistic-code')}
				});
				$notice.data('stats-sent', 1).find('.notice-dismiss').trigger('click');
				if(!href || href === '' || href === '#')
					return false;
			});
		});
	}
}
