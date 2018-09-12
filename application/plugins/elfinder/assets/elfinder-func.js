/*
	* подгрузка изображения в блок предпросмотра возле инпута
*/

var ViewImgIconsOnExt = {
	nofiledetect: 'application/plugins/elfinder/assets/files-icons/elf-icon-file-not.jpg',
	jpg: 'img',
	png: 'img',
	pdf: 'application/plugins/elfinder/assets/files-icons/elf-icon-file-pdf.jpg',
}


function ElfinderViewImgInit()
{
	// если на странице есть поля изображений
	if($('.field-image').length)
	{
		$('.field-image').parent('div').children('.e-image-container').remove();
		$('.field-image').parent('div').children('.image-dialog').remove();
		var $Container, $ImageContainer, InputId, InputValue, ArrImage, ImgSpl, FileExt;
		$('.field-image').each(function(index, element) {
			InputId = $(this).attr('id');
			InputValue = $(this).val();
			$Container = $(this).parent('div').addClass('elfinder-image').append('<div class="e-image-container" data-input-path="'+InputId+'"></div><button class="btn btn-success image-dialog" type="button" data-input-path="'+InputId+'" data-file-selected="">Загрузить</button>');
			// изображения
			ArrImage = InputValue.split('_ELF_');
			var InfoFile = '', AltInfo;
			$.each(ArrImage, function(idx, img) {
				if(img)
				{
					InfoFile = '';
					// описание файла в конце патча к нему
					AltInfo = img.split('##');
					img = AltInfo[0];
					// если есть описание, то ставим в ALT
					if(AltInfo[1]){
						InfoFile = AltInfo[1];
					}else{
						InfoFile = img.split('/');
						InfoFile = InfoFile[InfoFile.length-1];
					}

					// подстановка изображений от расширения в контейнер
					ImgSpl = img.split('.');
					FileExt = ImgSpl[ImgSpl.length-1];


					if(ViewImgIconsOnExt[FileExt])
					{
						if(ViewImgIconsOnExt[FileExt].length && ViewImgIconsOnExt[FileExt] == 'img')
							FileExt = img;
						else if(ViewImgIconsOnExt[FileExt].length && ViewImgIconsOnExt[FileExt] != 'img')
							FileExt = ViewImgIconsOnExt[FileExt];
						else
							FileExt = ViewImgIconsOnExt.nofiledetect;
					}
					else
					{
						FileExt = ViewImgIconsOnExt.nofiledetect;
					}

					$Container
					.children('.e-image-container')
					.append('<div data-img-path="'+img+'" data-img-info="'+InfoFile+'"><a href="/'+img+'" class="lightbox" target="_blank" title="'+InfoFile+'"><img src="/'+FileExt+'" alt="'+InfoFile+'" width="60" height="60"/></a><a class="e-image-delete" title="Delete this file">delete</a><a class="e-image-open" title="File folder">f</a><a class="e-image-info" title="File info">i</a></div>');
				}
			});
			//console.log('elfinder plugin init');
		});
		$('.e-image-container').sortable({
				helper: 'clone',
				revert: true,
				placeholder: 'elfinnder-ui-sortable-placeholder',
				distance:20,
				tolerance: 'pointer',
				stop: function(e, ui) {
					var $ContainerImg = ui.item.parent();
					var InputID = $ContainerImg.data('input-path');
					ArrImage = [];
					$ContainerImg.children('div').each(function(indx, elem){
						ArrImage[ArrImage.length] = $(elem).data('img-path') + '##' + $(elem).data('img-info');
					});
					$('#'+InputID).val(ArrImage.join('_ELF_'));
				}
			}).disableSelection();
	}


}
