(function ($, app) {
"use strict";
	$(document).ready(function () {
		var pytDiagrams = $('#pytDiagramsList'),
			openDiagram = false,
			settings = pytParseJSON(pytDiagrams.attr('data-settings')),
			url = typeof(ajaxurl) == 'undefined' || typeof(ajaxurl) !== 'string' ? PYT_DATA.ajaxurl : ajaxurl;
		$.fn.dataTable.ext.classes.sPageButton = 'button button-small pubydoc-paginate';
		$.fn.dataTable.ext.classes.sLengthSelect = 'pubydoc-flat-input';
		
		var table = pytDiagrams.DataTable({
			serverSide: true,
			processing: true,
			ajax: {
				'url': url + '?mod=diagrams&action=getListForTbl&pl=pyt&reqType=ajax',
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
						pytDiagrams.find('.pytCheckOne:checked').each(function() {
							ids.push($(this).attr('data-id'));

						});
						if (ids.length && confirm(pytStrReplace(pytCheckSettings(settings, 'remove-confirm'), '%s', ids.length))) {
							$.sendFormPyt({
							btn: this,
							data: {mod: 'diagrams', action: 'removeGroup', ids: ids},
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
					className: 'button button-small disabled button-secondary pyt-group-export',
					action: function (e, dt, node, config) {
						var ids = [];
						pytDiagrams.find('.pytCheckOne:checked').each(function() {
							ids.push($(this).attr('data-id'));

						});
						if (ids.length) {
							$.sendFormPyt({
							btn: this,
							data: {mod: 'export', action: 'generateUrl', type:'sql', tableId:0, params: JSON.stringify({ids: ids.join(','), mode: 'diagrams'})},
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
					className: 'button button-small button-secondary pyt-group-import',
					action: function (e, dt, node, config) {
						$importDialog.dialog('open');
					}
				},
				{
					text: '<i class="fa fa-fw fa-plus-circle"></i>' + pytCheckSettings(settings, 'btn-add'),
					className: 'button button-small pyt-add-table',
					action: function (e, dt, node, config) {
						if (app.pytDiagramPage) {
							openDiagramOptions(false, true);
						}
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
					className: "dt-center",
					targets: [6,7]
				},
				{
					width: "20px",
					targets: 1
				},
				{
					"orderable": false,
					targets: [0, 5, 6, 7]
				},
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
				$('#pytDiagramsList_wrapper .dataTables_paginate')[0].style.display = $('#pytDiagramsList_wrapper .dataTables_paginate  span .pubydoc-paginate').size() > 1 ? 'block' : 'none';
				pytInitTooltips('#pytDiagramsList');
				pytDiagrams.find('.pytCheckAll').prop('checked', false);
				if (!openDiagram) {
					var id = pytGetUrlParameter('id');
					if (id.length) {
						openDiagramOptions($('#pytDiagramsList .pytCheckOne[data-id="'+id+'"]'), false);
					}
					openDiagram = true;
				}
			}
		});
		pytInitCheckAll(pytDiagrams);
		var groupButtons = $('.pyt-group-delete, .pyt-group-export');
		pytDiagrams.on('change', '.pytCheckAll, .pytCheckOne', function(e) {
			if (pytDiagrams.find('.pytCheckOne:checked').length) {
				groupButtons.removeClass('disabled');
			} else {
				groupButtons.addClass('disabled');
			}
		});

		pytDiagrams.on('click', '.pubydoc-list-actions i', function() {
			var $this = $(this);
			if ($this.hasClass('pyt-options')) {
				openDiagramOptions($this, false);
			}
			else if ($this.hasClass('pyt-clone')) {
				
			}
		});
		pytDiagrams.on('click', '.pyt-edit-link, .pyt-diagrams-preview', function() {
			openDiagramOptions($(this), false);
			return false;
		});
		
		$('body').on('click', '.tooltipster-content button', function () {
			var $this = $(this),
				content = $this.closest('.tooltipster-content');
			if ($this.hasClass('pyt-delete')) {
				var id = content.find('.pytHidden').html();
				if (id.length) {
					$.sendFormPyt({
						icon: $('#pytDiagramsList').find('.pubydoc-list-actions[data-id="' + id +'"] i.pyt-delete'),
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
		var addNew = pytGetUrlParameter('new');
		if (addNew.length) {
			var parts = addNew.split('-');
			if (parts.length == 2) {
				openDiagramOptions(false, true, {table_id: parts[0], table_range: parts[1]});
			}
			openDiagram = true;
		}

		function openDiagramOptions($this, isNew, settings) {
			if (app.pytDiagramPage) {
				app.pytDiagramPage.tableForReload = table;
				if (isNew) {
					app.pytDiagramPage.diagramId = 0;
					app.pytDiagramPage.diagramSettings = (typeof(settings) == 'undefined' ? false : settings);
				} else {
					var $check = $this.closest('tr').find('.pytCheckOne');
					if ($check.length == 0) return;
					app.pytDiagramPage.diagramId = $check.data('id');
					app.pytDiagramPage.diagramSettings = $check.data('settings');
				}
				app.pytDiagramPage.dialogDiagram.dialog('open');
			}
		}
	});
	
}(window.jQuery, window));
