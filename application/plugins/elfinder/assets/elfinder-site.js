$(document).ready(function(){

	if($('.uploads-elfinder').length)
	{
		var Folder = '';
		$('.uploads-elfinder').each(function(idx, elem){
			Folder = $(elem).data('elfinder-folder');
			$(elem).elfinder({
				url : '/ajax/plugin/elfinder/create',
				lang: 'ru',
				requestType: 'post',
				debug : ['error', 'warning', 'event-destroy'],
				customData: {elfFolder: Folder},
				ui: ['toolbar', 'stat'],
				uiOptions: {
                    toolbar : [
                        // toolbar configuration
                        //['back', 'forward'],
						// ['reload'],
						// ['home', 'up'],
						//['mkdir', 'open', 'download', 'mkfile', 'upload'],
						['upload', 'getfile', 'archive', 'download'],
						['info'],
						//['quicklook'],
						//['copy', 'cut', 'paste'],
						['rm'],
						//['duplicate', 'rename', 'edit', 'resize'],
						//['extract', 'archive'],
						//['search'],
						['view'],
						//['help']
                    ]
                },
				contextmenu : {
                    // navbarfolder menu
					navbar : ['open', '|', 'info'],
					// current directory menu
					cwd    : ['reload', 'back', '|', 'upload', 'paste', '|', 'info'],
					// current directory file menu
					files  : ['getfile', '|','open', 'quicklook', '|', 'download', '|', 'info']
                },
			})
		})
	}


}); // end document ready
