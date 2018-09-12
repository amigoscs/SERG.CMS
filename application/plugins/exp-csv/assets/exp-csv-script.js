$(document).ready(function(e) {
	var TextareaValue = TextareaNewValue = '';
	$('textarea.edit-value').on('focus focusout', function(e) {

		var TextareaName = $(this).attr('name');
		if(e.type == 'focus') {
			TextareaValue = $(this).val();
		}
		else
		{
			TextareaNewValue = $(this).val();
			if(TextareaNewValue != TextareaValue)
			{
				add_loader();
				$.ajax({
					url: '/admin/exp-csv/ajax/savevalue',
					type: 'POST',
					data: {field_value: TextareaNewValue, field_name: TextareaName},
					success: function(data){
						remove_loader();
						Res = JSON.parse(data);
						if(Res.status == 'complite'){
							noty_info('success', Res.text, 'topRight');
						}else{
							noty_info('warning', Res.text);
						}
					},
					error: function(a, b, c){
						noty_info('warning', 'Connect error');
					}
				});
			}

		}
	});


	$('.block-value').on('dblclick', function(e) {
		var $ObjBlock = $(this);
		var TextareContent = $ObjBlock.html();
		var $Textarea = $(this).next('textarea');
		var SelName = $(this).data('block-name');
		if(!$Textarea.length) {
			return false;
		}
		$Textarea.val(TextareContent).show().focus();
		$ObjBlock.hide();
		$Textarea.on('focusout.Bval', function(e) {
			//$ObjBlock.empty().html($Textarea.val()).show();
			$('.block-value[data-block-name="'+SelName+'"]').empty().html($Textarea.val());
			$ObjBlock.show();
			$Textarea.hide().val('');
			$Textarea.off('focusout.Bval');
		});
	});

	// кнопка старта импорта
	$('#run_import').on('click', function() {
		var FilePath = $(this).data('file-path');
		window.onbeforeunload = function() {
			return true;
		}
		admin_dialog('<p style="margin:25px 0;text-align:center"><img src="/application/plugins/exp-csv/assets/exloader.gif"/></p>', 'Выполняется обновление...', 350);
		$.ajax({
			url: '/admin/exp-csv/ajax/runimport',
			type: 'POST',
			data: {file: FilePath},
			dataType: 'json',
			success: function(Res){
				//console.log(Res);
				admin_dialog('<p>'+Res.text+'</p>', 'Процесс завершен', 350);
				window.onbeforeunload = null;
			},
			error: function(a, b, c){
				admin_dialog('Response error', 'Error', 350);
				window.onbeforeunload = null;
			}
		});
	});
});
