(function ($, app) {
"use strict";
	function CreateTablePage() {
		this.$obj = this;
		this.$obj.formObj = $('#pytNewTableForm');
		return this.$obj;
	}
	CreateTablePage.prototype.init = (function () {
		var _this = this.$obj;
		_this.eventsAdminPage();
	});

	CreateTablePage.prototype.eventsAdminPage = (function () {
		var _this = this.$obj,
			$form = _this.formObj,
			$createBtn = $('#pytNewTableFormSave'),
			$mainTabsContent = $('.pubydoc-result-section > .block-tab'),
			$mainTabs = $('.pyt-menu-section .button'),
			$importTabsContent = $('#block-tab-import > .block-tab'),
			$importTabs = $('.pyt-import-tabs .button'),
			$inputType = $('#pytTableType'),
			$inputCreation = $('#pytTableCreation'),
			$inputBuilder = $('#pytTableBuilder');

		$mainTabsContent.filter($mainTabs.filter('.current').attr('href')).addClass('active');

		$mainTabs.on('click', function (e) {
			e.preventDefault();
			var $this = $(this),
				$curTab = $this.attr('href');

			$mainTabsContent.removeClass('active');
			$mainTabs.filter('.current').removeClass('current');
			$this.addClass('current');

			var $curTabContent = $mainTabsContent.filter($curTab);
			$curTabContent.addClass('active');
			$inputType.val($this.attr('data-type'));
			$inputCreation.val($this.attr('data-creation'));
			if ($this.hasClass('pubydoc-show-pro')) $createBtn.addClass('disabled');
			else $createBtn.removeClass('disabled');
		});

		$importTabsContent.filter($importTabs.filter('.current').attr('href')).addClass('active');

		$importTabs.on('click', function (e) {
			e.preventDefault();
			var $this = $(this),
				$curTab = $this.attr('href');

			$importTabsContent.removeClass('active');
			$importTabs.filter('.current').removeClass('current');
			$this.addClass('current');

			var $curTabContent = $importTabsContent.filter($curTab);
			$curTabContent.addClass('active');
			$inputCreation.val($this.attr('data-creation'));
		});

		var $columnsBlock = $('#pytColumnsBlock'),
			$inputCols = $('#pytTableCols');

		$inputCols.on('change keyup input', function() {
			var $columns = $columnsBlock.find('.pyt-column-block'),
				delta = _this.getNumericValue($(this)) - $columns.length + 1;

			if (delta == 0) return;
			if (delta > 0) {
				var tpl = $columns.filter('.pubydoc-template');
				for (var c = 1; c <= delta; c++) {
					$columnsBlock.append(tpl.clone().removeClass('pubydoc-template'));
				}
			} else {
				for (var c = delta; c < 0; c++) {
					$columnsBlock.find('.pyt-column-block:not(.pubydoc-template)').last().remove();
				}
			}
		});
		$('#pytTableRows').on('change keyup input', function() {
			var $columns = $columnsBlock.find('.pyt-column-block'),
				tpl = $columns.filter('.pubydoc-template'),
				delta = _this.getNumericValue($(this)) - tpl.find('.pyt-column-row').length;

			if (delta == 0) return;
			var tpl = $columns.find('.pyt-column-row').last();
			$columns.each(function () {
				var $this = $(this);
				if (delta > 0) {
					for (var r = 1; r <= delta; r++) {
						$this.append(tpl.clone());
					}
				} else {
					for (var r = delta; r < 0; r++) {
						$this.find('.pyt-column-row').last().remove();
					}
				}
			});
		});
		$columnsBlock.on('click', '.pyt-column-delete', function() {
			var $columns = $columnsBlock.find('.pyt-column-block'),
				cols = _this.getNumericValue($inputCols);
			if ($columns.length > 1 && cols > 1) {
				$(this).parent('.pyt-column-block').remove();
				$inputCols.val(cols - 1);
			}
		});

		$columnsBlock.sortable({
			cursor: 'move',
			handle: '.pyt-column-header'
		});

		$createBtn.click(function() {
			$form.submit();
			return false;
		});
		$form.submit(function() {
			if ($createBtn.hasClass('disabled')) return false;
			var form = new FormData(),
				creation = $inputCreation.val();
			if ($inputType.val() == '0' && creation == 0) {
				var col = 1;
				$columnsBlock.find('.pyt-column-block:not(.pubydoc-template)').each(function() {
					var $column = $(this),
						row = 1;
					form.append('header' + col, $column.find('.pyt-column-header input').val());
					$column.find('.pyt-column-row input').each(function() {
						var $row = $(this);
						if ($row.val().length > 0) form.append('data-' + col + '-' + row, $row.val());
						row++;
					});
					col++;
				});
			}
			$form.find('.options-for-save, .block-tab.active').find('input, select').each(function() {
				var $input = $(this);
				if (this.name) {
					if ($input.is('input[type="file"]')) {
						form.append(this.name, $input.get(0).files[0]);
					} else if ($input.is('input[type="checkbox"]')) {
						if ($input.is(':checked')) form.append(this.name, $input.val());
					} else {
						form.append(this.name, $input.val());
					}
				}
			});
			
			$.sendFormPyt({
				btn: $createBtn,
					form: form,
					ajax: {
						cache: false,
						contentType: false,
						processData: false
					},
					onSuccess: function(res) {
						if (!res.error && res.data && res.data.edit_link) {
							document.location.href = res.data.edit_link;
						}
					}
				});
			return false;
		});
	});
	CreateTablePage.prototype.getNumericValue = (function ($input) {
		var minVal = parseInt($input.attr('min')),
			maxVal = parseInt($input.attr('max')),
			value = $input.val();
		if (!$.isNumeric(value)) $input.val(minVal);
		else {
			value = parseInt(value);
			if (value < minVal) $input.val(minVal);
			else if (value > maxVal) $input.val(maxVal);
		}
		return parseInt($input.val());
	});

	$(document).ready(function () {
		app.pytCreateTablePage = new CreateTablePage();
		app.pytCreateTablePage.init();
	});

}(window.jQuery, window));
