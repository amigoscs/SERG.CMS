$(document).ready(function(e) {
	var TextareaValue = TextareaNewValue = '';

	$('textarea.edit-value').on('focusout.EXPCSV', function(e) {
		TextareaNewValue = $(this).val();
		$(this).parent('td').removeClass('active');
		var $activeDIV = $(this).parent('td').find('.block-value');
		if(TextareaValue !== TextareaNewValue) {
			var table = $(this).data('table');
			var tableKey = $(this).data('table-key');
			var nodeID = $(this).data('node-id');
			var textValue = $(this).val();
			add_loader();
			$.ajax({
				url: '/admin/exp-csv/ajax/savevalue',
				type: 'POST',
				data: {res_table: table, res_table_key: tableKey, res_node_id: nodeID, res_value: textValue},
				dataType: 'json',
				success: function(DATA){
					//console.log(DATA);
					remove_loader();
					if(DATA.status == 'OK'){
						noty_info('success', DATA.info, 'topRight');
						$activeDIV.text(DATA.new_value);
					}else{
						noty_info('warning', DATA.info);
					}
				},
				error: function(a, b, c){
					remove_loader();
					noty_info('warning', 'Connect error');
				}
			});
		}
	});


	$('.block-value').on('dblclick.EXPCSV', function(e) {
		TextareaValue = $(this).parent('td').addClass('active').find('textarea').focus().val();
	});

	// кнопка старта импорта
	$('#run_import').on('click.EXPCSV', function() {
		var FilePath = $(this).data('file-path');
		window.onbeforeunload = function() {
			return true;
		}
		$(this).remove();
		admin_dialog('<p class="upd-loader"><img src="/application/plugins/exp-csv/assets/exloader.gif"/></p>', 'Выполняется обновление...', 350);
		updOffset = 0;
		expfunc_import(FilePath, updOffset, true);
	});

	// форма экспорта по типам объектов
	$('form#export_type').on('submit.EXPCSV', function(e){
		var values = $(this).serialize();
		admin_dialog('<p class="upd-loader"><img src="/application/plugins/exp-csv/assets/exloader.gif"/></p>', 'Формирование файла...', 350);
		expfunc_export_type_object(values);
		return false;
	});

	// форма экспорта таблицы
	$('form#export_nodes').on('submit.EXPCSV', function(e) {
		var formAction = $(this).attr('action');
		admin_dialog('<p class="upd-loader"><img src="/application/plugins/exp-csv/assets/exloader.gif"/></p>', 'Формирование файла...', 350);
		$.ajax({
			url: formAction,
			type: 'POST',
			data: {run_export: 'run'},
			dataType: 'json',
			success: function(DATA) {
				if(DATA.status == 'OK') {
					admin_dialog('<p class="complite-info">' + DATA.info + '</p>', 'Complite', 350);
				} else {
					admin_dialog('<p class="error-info">' + DATA.info + '</p>', 'Error', 350);
				}
			},
			error: function(a, b, c){
				admin_dialog('<p>Error response', 'Error', 350);
			}
		});
		return false;
	});

	// выделение строки таблицы
	$('.simple-table').on('click.EXPCSV', 'tr', function(e){
		$('.simple-table').find('tr').removeClass('selected');
		$(this).addClass('selected');
	});
});
