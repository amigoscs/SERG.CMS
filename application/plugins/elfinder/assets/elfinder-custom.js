$(document).ready(function() {
	var ELFCONNECTOR = '/admin/elfinder/admin_connector';

	if($('.field-image').length)
	{
		ElfinderViewImgInit();
	}

	/*клик по удалению картинки из списка*/
	$('body').on('click.ELFINDER', '.elfinder-image a.e-image-delete', function(){
		if (confirm("are you sure?")) {
			var $EmagesContainer = $(this).parent().parent();
			$(this).parent('div').remove();
			var ArrImage = [];
			$EmagesContainer.children('div').each(function(indx, elem){
				ArrImage[ArrImage.length] = $(elem).data('img-path') + '##' + $(elem).data('img-info');
			});
			$EmagesContainer.prev('input').val(ArrImage.join('_ELF_'));
		}else{
		  return;
		}
	});

	// клик открыть файловый менеджер
	$('body').on('click.ELFINDER', '.image-dialog', function(){
		if($('#elfinderDialog').length){
			return false;
		}

		$('body').append('<div id="elfinderDialog" class="ui-dialog"><div class="elfinder-dialog-header ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix"><span class="ui-dialog-title">File manager</span> <button class="elfinder-dialog-close" type="button">close</button></div><div id="elfinder-dialog-container"></div></div>');
		$('#elfinderDialog').draggable({
			handle: ".elfinder-dialog-header",
		});

		var WindowWidth = $(window).width(),
			WindowHeight = $(window).height(),
			ElfDialogWidth = 0,
			ElfDialogHeight = $('#elfinderDialog').height(),
			posLeft = 0,
			posTop = 0,
			InputPatch = $('#' + $(this).data('input-path')),
			fileSelected = $(this).data('file-selected');

		if(WindowWidth > 1300){
			ElfDialogWidth = 1200;
		}else if(WindowWidth > 1100){
			ElfDialogWidth = 1000;
		}else{
			ElfDialogWidth = 800;
		}

		posLeft = (WindowWidth / 2) - (ElfDialogWidth / 2);
		posTop = (WindowHeight / 2) - (ElfDialogHeight / 2);

		$('#elfinderDialog').css({'top':posTop + 'px', 'left':posLeft + 'px', 'position':'fixed', 'width': ElfDialogWidth + 'px'});

		setTimeout(function(){
			$('#elfinder-dialog-container').elfinder({
				url : ELFCONNECTOR,
				lang: cmsGetLang('scriptLangShort'),
				getFileCallback: function(data) {
					if(InputPatch.val()) {
						InputPatch.val(InputPatch.val() + '_ELF_' +data.path);
					}else{
						InputPatch.val(data.path);
					}
					ElfinderViewImgInit();
				},
				handlers : {
			        open: function(event, elfinderInstance) {

			        },
					select: function(event, elfinderInstance) {

					}
			    }
			});

			if(fileSelected) {
				fileSelected = 'l1_' + fileSelected;
				setTimeout(function(){
					$('#'+fileSelected).trigger('click');
				}, 500)
			}
		}, 300);

	});

	// закрыть диалог
	$('body').on('click.ELFINDER', '#elfinderDialog .elfinder-dialog-close', function(){
		$('#elfinderDialog').remove();
	});

	// клик по кнопке добавления информации к файлу
	$('body').on('click.ELFINDER', '.e-image-info', function(){
		if($(this).next('').length){
			$('.e-file-info-text').remove();
			return false;
		}
		$('.e-file-info-text').remove();
		var AltText = '';
		AltText = $(this).parent().data('img-info');
		$(this).after('<div class="e-file-info-text"><textarea name="effit">' + AltText + '</textarea><button type="button">save</button></div>');
	});
	// клик по кнопке сохранения информации к файлу
	$('body').on('click.ELFINDER', '.e-file-info-text button', function(){
		var TextInfo = $(this).parent().find('textarea').val();
		var DivImg = $(this).parent().parent();
		DivImg.data('img-info', TextInfo);
		var ArrImage = [];
		DivImg.parent().children('div').each(function(indx, elem){
			ArrImage[ArrImage.length] = $(elem).data('img-path') + '##' + $(elem).data('img-info');
		});
		DivImg.parent().prev('input').val(ArrImage.join('_ELF_'));
		$(this).parent().remove();
		//console.log(ArrImage);

	});

	// клик по кнопке открыть директорию файла
	$('body').on('click.ELFINDER', '.e-image-open', function() {
		var ImagePath = $(this).parent('div').data('img-path');
		var folderPath = ImagePath.split('/');
		folderPath.splice(0, 1);
		var filePath = folderPath.join('/');

		folderPath.splice(-1, 1);
		var folderPath = folderPath.join('/');

		var volumeId = 'elf_l1_';
		location.hash = volumeId + btoa(folderPath).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '.').replace(/\.+$/, '');
		var fileHash = btoa(filePath).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '.').replace(/\.+$/, '');
		var buttonDialog = $(this).parent('div').parent('div.e-image-container').next('button').data('file-selected', fileHash).trigger('click');
		return false;
	});

	/*страница*/
	setTimeout(function(){
		if($('#elfinder').length)
		{
			$('#elfinder').elfinder({
				url : ELFCONNECTOR,
				lang: cmsGetLang('scriptLangShort'),
			}).elfinder('instance');

		};
	}, 1000);



}); // end document ready
