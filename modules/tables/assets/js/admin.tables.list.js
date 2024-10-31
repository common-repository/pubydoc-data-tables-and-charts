(function($) {
	"use strict";
	$(document).ready(function () {
		var pytTables = $('#pytTablesList'),
			settings = pytParseJSON(pytTables.attr('data-settings')),
			isPro = PYT_DATA.isPro == '1',
			url = typeof(ajaxurl) == 'undefined' || typeof(ajaxurl) !== 'string' ? PYT_DATA.ajaxurl : ajaxurl;
		$.fn.dataTable.ext.classes.sPageButton = 'button button-small pubydoc-paginate';
		$.fn.dataTable.ext.classes.sLengthSelect = 'pubydoc-flat-input';
		
		var table = pytTables.DataTable({
			serverSide: true,
			processing: true,
			ajax: {
				'url': url + '?mod=tables&action=getListForTbl&pl=pyt&reqType=ajax',
				'type': 'POST',
			},
			lengthChange: true,
			lengthMenu: [ [10, 20, 40, -1], [10, 20, 40, "All"] ],
			paging: true,
			dom: 'B<"pull-right"fl>rtip',
			responsive: {details: {display: $.fn.dataTable.Responsive.display.childRowImmediate, type: ''}},
			autoWidth: false,
			buttons: [
				{
					text: '<i class="fa fa-fw fa-trash-o"></i>' + pytCheckSettings(settings, 'btn-delete'),
					className: 'button button-small button-secondary disabled pyt-group-delete',
					action: function (e, dt, node, config) {
						var ids = [];
						pytTables.find('.pytCheckOne:checked').each(function() {
							ids.push($(this).attr('data-id'));

						});
						if (ids.length && confirm(pytStrReplace(pytCheckSettings(settings, 'remove-confirm'), '%s', ids.length))) {
							$.sendFormPyt({
							btn: this,
							data: {mod: 'tables', action: 'removeGroup', ids: ids},
								onSuccess: function(res) {
									if (!res.error) {
										table.ajax.reload();
									}
								}
							});
						}
					}
				},
				{
					text: '<i class="fa fa-fw fa-upload"></i>' + pytCheckSettings(settings, 'btn-export'),
					className: 'button button-small disabled button-secondary pyt-group-export' + (isPro ? '' : ' pubydoc-show-pro'),
					action: function (e, dt, node, config) {
						var ids = [];
						pytTables.find('.pytCheckOne:checked').each(function() {
							ids.push($(this).attr('data-id'));

						});
						if (ids.length) {
							$.sendFormPyt({
							btn: this,
							data: {mod: 'export', action: 'generateUrl', type:'sql', tableId:0, params: JSON.stringify({ids: ids.join(','), mode: 'tables'})},
								onSuccess: function(res) {
									if (!res.error && res.url) {
										window.location.href = res.url;
									}
								}
							});
						}
					}
				},
				{
					text: '<i class="fa fa-fw fa-download"></i>' + pytCheckSettings(settings, 'btn-import'),
					className: 'button button-small button-secondary pyt-group-import' + (isPro ? '' : ' pubydoc-show-pro disabled'),
					action: function (e, dt, node, config) {
						$importDialog.dialog('open');
					}	
				},
				{
					text: '<i class="fa fa-fw fa-plus-circle"></i>' + pytCheckSettings(settings, 'btn-add'),
					className: 'button button-small pyt-add-table',
					action: function (e, dt, node, config) {
						document.location.href = pytCheckSettings(settings, 'add-url');
					}
				}
			],
			columnDefs: [
				{
					className: "dt-left",
					width: "20px",
					targets: 0
				},
				{
					width: "20px",
					targets: 1
				},
				{
					"orderable": false,
					targets: [0, 4, 5]
				}
			],
			order: [[ 1, 'desc' ]],
			language: {
				emptyTable: pytCheckSettings(settings, 'emptyTable'),
				paginate: {
					next: '<i class="fa fa-fw fa-angle-right">',
					previous: '<i class="fa fa-fw fa-angle-left">'  
				},
				lengthMenu: pytCheckSettings(settings, 'lengthMenu') + ' _MENU_',
				info: pytCheckSettings(settings, 'info') + ' _START_ to _END_ of _TOTAL_',
				search: '_INPUT_'
			},
			fnDrawCallback : function() {
				$('#pytTablesList_wrapper .dataTables_paginate')[0].style.display = $('#pytTablesList_wrapper .dataTables_paginate  span .pubydoc-paginate').size() > 1 ? 'block' : 'none';
				pytInitTooltips('#pytTablesList');
				pytTables.find('.pytCheckAll').prop('checked', false);
			}
		});
		pytInitCheckAll(pytTables);
		var groupButtons = $('.pyt-group-delete, .pyt-group-export:not(.pubydoc-show-pro)');
		pytTables.on('change', '.pytCheckAll, .pytCheckOne', function(e) {
			if (pytTables.find('.pytCheckOne:checked').length) {
				groupButtons.removeClass('disabled');
			} else {
				groupButtons.addClass('disabled');
			}
		});

		$('#pytTablesList').on('click', '.pubydoc-list-actions i', function() {
			var $this = $(this);
			if ($this.hasClass('pyt-edit')) {
				document.location.href = $this.closest('tr').find('.pyt-edit-link').attr('href');
			} else if ($this.hasClass('pyt-options')) {
				document.location.href = $this.closest('tr').find('.pyt-edit-link').attr('href') + '&block=options';
			}
			else if ($this.hasClass('pyt-clone')) {
				$.sendFormPyt({
					icon: $this,
					data: {
						mod: 'tables',
						action: 'cloneTable',
						tableId: $this.closest('.pubydoc-list-actions').attr('data-id'),
					},
					onSuccess: function(res) {
						if (!res.error && res.data && res.data.link) {
							setTimeout(function() {
								window.open(res.data.link ,'_blank');
							}, 500);
						}
					}
				});
			}
		});
		$('body').on('click', '.tooltipster-content button', function () {
			var $this = $(this),
				content = $this.closest('.tooltipster-content');
			if ($this.hasClass('pyt-delete')) {
				var id = content.find('.pytHidden').html();
				if (id.length) {
					$.sendFormPyt({
						icon: $('#pytTablesList').find('.pubydoc-list-actions[data-id="' + id +'"] i.pyt-delete'),
						data: {
							mod: 'tables',
							action: 'removeTable',
							tableId: id
						},
						onSuccess: function(res) {
							if (!res.error) {
								setTimeout(function() {
									table.ajax.reload();
								}, 500);
							}
						}
					});
				}
			}
			content.parent().removeClass('tooltipster-fade-show');
			
		});
		var dImport = $('#pytDialogMigration'),
			$importDialog = dImport.dialog({
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
						text: dImport.data('import'),
						class: 'button button-secondary',
						click: function(e) {
							var form = new FormData(),
								$input = $(this).find('input[type="file"]');

							if ($input.val()) {
								form.append('sql_file', $input.get(0).files[0]);
								form.append('mod', 'import');
								form.append('action', 'importTableData');
								form.append('type', 'sql');
								
								$.sendFormPyt({
									btn: $('.pyt-group-import'),
									form: form,
									ajax: {
										cache: false,
										contentType: false,
										processData: false
									},
									onSuccess: function(res) {
										if (!res.error) {
											setTimeout(function() {
												table.ajax.reload();
											}, 500);
										}
									}
								});
							}
							$(this).dialog('close');
						}
					},
					{
						text: dImport.data('cancel'),
						class: 'button button-secondary',
						click: function() {
							$(this).dialog('close');
						}
					}
				],
				create: function( event, ui ) {
					$(this).parent().css('maxWidth', $(window).width()+'px');
				}
			});
	});
})(jQuery);
