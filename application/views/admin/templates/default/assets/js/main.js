jQuery(document).ready(function() {
	/*
	* UPD 2018-06-22
	* Version 2.1
	* Добавлена обработка событий для работы с DATA-полями
	*/
	//cache DOM elements
	var mainContent = $('.cd-main-content'),
		header = $('.cd-main-header'),
		sidebar = $('.cd-side-nav'),
		sidebarTrigger = $('.cd-nav-trigger'),
		topNavigation = $('.cd-top-nav'),
		searchForm = $('.cd-search'),
		accountInfo = $('.account');

	//on resize, move search and top nav position according to window width
	var resizing = false;
	moveNavigation();
	$(window).on('resize', function(){
		if( !resizing ) {
			(!window.requestAnimationFrame) ? setTimeout(moveNavigation, 300) : window.requestAnimationFrame(moveNavigation);
			resizing = true;
		}
	});

	//on window scrolling - fix sidebar nav
	var scrolling = false;
	checkScrollbarPosition();
	$(window).on('scroll', function(){
		if( !scrolling ) {
			(!window.requestAnimationFrame) ? setTimeout(checkScrollbarPosition, 300) : window.requestAnimationFrame(checkScrollbarPosition);
			scrolling = true;
		}
	});

	//mobile only - open sidebar when user clicks the hamburger menu
	sidebarTrigger.on('click', function(event){
		event.preventDefault();
		$([sidebar, sidebarTrigger]).toggleClass('nav-is-visible');
	});

	//click on item and show submenu
	$('.has-children > a').on('click', function(event){
		var mq = checkMQ(),
			selectedItem = $(this);
		if( mq == 'mobile' || mq == 'tablet' ) {
			event.preventDefault();
			if( selectedItem.parent('li').hasClass('selected')) {
				selectedItem.parent('li').removeClass('selected');
			} else {
				sidebar.find('.has-children.selected').removeClass('selected');
				accountInfo.removeClass('selected');
				selectedItem.parent('li').addClass('selected');
			}
		}
	});

	//click on account and show submenu - desktop version only
	accountInfo.children('a').on('click', function(event){
		var mq = checkMQ(),
			selectedItem = $(this);
		if( mq == 'desktop') {
			event.preventDefault();
			accountInfo.toggleClass('selected');
			sidebar.find('.has-children.selected').removeClass('selected');
		}
	});

	$(document).on('click', function(event){
		if( !$(event.target).is('.has-children a') ) {
			sidebar.find('.has-children.selected').removeClass('selected');
			accountInfo.removeClass('selected');
		}
	});

	//on desktop - differentiate between a user trying to hover over a dropdown item vs trying to navigate into a submenu's contents
	sidebar.children('ul').menuAim({
        activate: function(row) {
        	$(row).addClass('hover');
        },
        deactivate: function(row) {
        	$(row).removeClass('hover');
        },
        exitMenu: function() {
        	sidebar.find('.hover').removeClass('hover');
        	return true;
        },
        submenuSelector: ".has-children",
    });

	function checkMQ() {
		//check if mobile or desktop device
		return window.getComputedStyle(document.querySelector('.cd-main-content'), '::before').getPropertyValue('content').replace(/'/g, "").replace(/"/g, "");
	}

	function moveNavigation(){
  		var mq = checkMQ();

        if ( mq == 'mobile' && topNavigation.parents('.cd-side-nav').length == 0 ) {
        	detachElements();
			topNavigation.appendTo(sidebar);
			searchForm.removeClass('is-hidden').prependTo(sidebar);
		} else if ( ( mq == 'tablet' || mq == 'desktop') &&  topNavigation.parents('.cd-side-nav').length > 0 ) {
			detachElements();
			searchForm.insertAfter(header.find('.cd-logo'));
			topNavigation.appendTo(header.find('.cd-nav'));
		}
		checkSelected(mq);
		resizing = false;
	}

	function detachElements() {
		topNavigation.detach();
		searchForm.detach();
	}

	function checkSelected(mq) {
		//on desktop, remove selected class from items selected on mobile/tablet version
		if( mq == 'desktop' ) $('.has-children.selected').removeClass('selected');
	}

	function checkScrollbarPosition() {
		var mq = checkMQ();

		if( mq != 'mobile' ) {
			var sidebarHeight = sidebar.outerHeight(),
				windowHeight = $(window).height(),
				mainContentHeight = mainContent.outerHeight(),
				scrollTop = $(window).scrollTop();

			( ( scrollTop + windowHeight > sidebarHeight ) && ( mainContentHeight - sidebarHeight != 0 ) ) ? sidebar.addClass('is-fixed').css('bottom', 0) : sidebar.removeClass('is-fixed').attr('style', '');
		}
		scrolling = false;
	}

	// tabs
	$('.admin-tabs').tabs({
		 active: localStorage.getItem("adminTabsCurrentTabIndex"),
		 activate:function(event,ui){
			 localStorage.setItem("adminTabsCurrentTabIndex", ui.newTab.index());
		 }
	 });

	 /*show hide*/
	$('body').on('click', '.show-hide-switch > h2', function() {
		var Parent = $(this).parent('.show-hide-switch');
		var shID = Parent.data('sh-id');
		var allItems = null;
		if(shID) {
			allItems = localStorage.getItem(appShSwKey);
			if(!allItems) {
				allItems = {'open': {}, 'closed': {}};
			} else {
				allItems = JSON.parse(allItems);
			}
		}

		if(Parent.hasClass('hide')) {
			Parent.removeClass('hide');
			if(shID) {
				allItems['open'][shID] = true;
				delete allItems['closed'][shID];
			}
		}else{
			Parent.addClass('hide');
			if(shID) {
				allItems['closed'][shID] = true;
				delete allItems['open'][shID];
			}
		}

		if(allItems) {
			localStorage.setItem(appShSwKey, JSON.stringify(allItems));
		}
	});


	if($('ul.list-draggable').length) {
		$('.list-draggable').find('.s-handle').prepend('<i class="fa fa-arrows sort-handle" aria-hidden="true"></i>');

		var parentUlSort, ajaxObject;
		$('.list-draggable, .list-draggable ul').sortable({
			 handle: '.sort-handle',
			 stop: function(event, ui){
				 ajaxObject = {sort_table: '', sort_field: '', sort_index: {}};
				 parentUlSort = $('.list-draggable');
				 ajaxObject.sort_table = parentUlSort.data('sort-table');
				 ajaxObject.sort_field = parentUlSort.data('sort-field');
				 var incr = 0;
				 parentUlSort.find('li').each(function(idx, element){
					incr++;
					ajaxObject.sort_index["INDEX_" + incr] = $(element).data('id');
				});

				add_loader();
				$.ajax({
					url: '/admin/set_sort',
					type: 'POST',
					data: ajaxObject,
					dataType: 'json',
					success: function(Res){
						if(Res.status == 'OK'){
							noty_info('information', Res.info, 'topRight');
						}else{
							noty_info('error', Res.info, 'center');
						}
						remove_loader();
					},
					error: function(a, b, c){
						noty_info('error', 'ajax error', 'center');
						remove_loader();
					}
				});
			 }
		});
	}

	/*
	распарсивание select значений в input
	START
	*/
	var $InputElem, InputText;
	$('body').on('focus.SELECTEDFIELDS', 'input.form-control', function() {
		if($(this).hasClass('date-input')) {
			return false;
		}
		if($(this).attr('readonly')){
			return false;
		}
		if(!$(this).parent('.sfi-wrap').length){
			$(this).wrap('<div class="sfi-wrap"></div>').focus();
		}

		if($(this).next('.input-button-block').length){
			return false;
		}else{
			$(this).after('<div class="input-button-block"><span class="selected-field-delete-value" title="' + cmsGetLang('clearField') + '"></span><span class="selected-field-sel" title="' + cmsGetLang('selectList') + '"></span></div>');
		}
	});

	$('body').on('click.SELECTEDFIELDS', '.selected-field-sel', function() {
		if($(this).parent('.input-button-block').next('.selected-field-selblock').length){
			$(this).parent('.input-button-block').next('.selected-field-selblock').remove();
			return false;
		} else {
			$('.selected-field-selblock').remove();
		}

		// ширину поля установим равную input, но не более 500px
		var $InputElem = $(this).parent('.input-button-block').prev('input');
		InputText = $InputElem.val();
		var sfsWidth = $InputElem.innerWidth();
		if(sfsWidth > 500) {
			sfsWidth = 500;
		}

		$(this).parent('.input-button-block').after('<div class="selected-field-selblock" style="width:'+sfsWidth+'px">Неверное значение</div>');

		var FormText = '';
		FormText += '<div class="sfise-close"><span></span></div>';

		FormText += '<ul>';
		// если значение поля не соответствует шаблону, то выводим пустую форму
		if(InputText.indexOf("||") === -1)
		{
			FormText += '<li>';
			FormText += '<input type="text" value=""> <i class="sep">-</i> ';
			FormText += '<input type="text" value="">';
			FormText += '<span class="sf-line-remove"><i class="fa fa-trash-o" aria-hidden="true"></i></span>';
			FormText += '</li>';
		}
		else
		{
			var InputTextArr = [InputText]
			if(InputText.indexOf("##") !== -1){
				InputTextArr = InputText.split('##');
			}

			var i;
			for(i=0; i < InputTextArr.length; i++){
				FormText += '<li>';
				InputTextArrNext = InputTextArr[i].split("||");
				FormText += '<input type="text" value="'+ InputTextArrNext[0].trim() + '" class="sel-key"> <i class="sep">-</i> ';
				FormText += '<input type="text" value="'+ InputTextArrNext[1].trim() + '" class="sel-value">';
				FormText += '<span class="sf-line-remove"><i class="fa fa-trash-o" aria-hidden="true"></i></span>';
				FormText += '</li>';
			}
		}
		FormText += '</ul>';


		FormText += '<p class="selected-field-selblock-submit-line"><span class="btn btn-primary new-line">'+cmsGetLang('append')+'</span> <span class="btn btn-default sort-list">'+cmsGetLang('sort')+'</span> <span class="btn btn-success submit-sf">'+cmsGetLang('apply')+'</span></p>';

		$('.selected-field-selblock').html(FormText);
		$('.selected-field-selblock ul').sortable({handle: '.sep'});

	});

	$('body').on('change.SELECTEDFIELDS', '.selected-field-selblock .sel-value', function(){
		var $textKeyInput = $(this).parent('li').children('.sel-key');
		if(!$textKeyInput.val()){
			$textKeyInput.val(appJsTranslit($(this).val()));
		};
	});

	$('body').on('click.SELECTEDFIELDS', '.selected-field-selblock .new-line', function(){
		$(this).parent('p').prev('ul').append('<li><input type="text" value="" class="sel-key"> <i class="sep">-</i> <input type="text" value="" class="sel-value"><span class="sf-line-remove"><i class="fa fa-trash-o" aria-hidden="true"></i></span></li>');
	});
	$('body').on('click.SELECTEDFIELDS', '.selected-field-selblock .sort-list', function(){
		var $target = $(this).parent('p').prev('ul');
		var $elements = $target.children('li');

		$elements.sort(function(a, b) {
			var an = $(a).children('.sel-value').val(),
			bn = $(b).children('.sel-value').val();
			if (an && bn) {
				return an.toUpperCase().localeCompare(bn.toUpperCase());
			}
			return 0;
		});
		$elements.detach().appendTo($target);
	});

	$('body').on('click.SELECTEDFIELDS', '.selected-field-selblock .sfise-close span', function(){
		$(this).parent().parent().remove();
	});

	$('body').on('click.SELECTEDFIELDS', '.selected-field-selblock .submit-sf', function(){
		var ElemBlock = $(this).closest('.selected-field-selblock');
		var AllInputs = $(ElemBlock).find('input');
		InputText = '';
		var AllInputsLength = AllInputs.length - 1;
		$.each(AllInputs, function(index, element){
			// четные числа - это ключи, нечетные - значения
			if(index % 2 == 0){
				InputText += $(element).val() + ' || ';
			}else{
				InputText += $(element).val();
				if(AllInputsLength !== index){
					InputText += ' ## ';
				}
			}

		});
		ElemBlock.prev().prev('input').val(InputText);
		ElemBlock.remove();
	});



	$('body').on('click.SELECTEDFIELDS', '.selected-field-selblock .sf-line-remove', function(){
		$(this).parent('li').remove();
	});

	$('body').on('click.SELECTEDFIELDS', '.selected-field-delete-value', function() {
		var $elInput = $(this).parent('.input-button-block').prev('input');
		if($elInput.hasClass('field-number')) {
			$elInput.val('0');
		} else if($elInput.hasClass('field-numberfloat')) {
			$elInput.val('0');
		} else {
			$elInput.val('');
		}
	});


	/*--//--
	распарсивание select значений в input
	END
	*/

	/*информирование о количестве символов в поле textarea*/
	$('body').on('input propertychange', 'textarea', function(){
		$(this).parent().css({'position':'relative'});
		if($(this).next('.charsetLimitCount').length){
			element = $(this).next('.charsetLimitCount');
		}else{
			$(this).after('<span class="charsetLimitCount"></span>');
			element = $(this).next('.charsetLimitCount')
		}
		element.css({'position':'absolute','top':'0', 'right':'0'})
		element.html($(this).val().length);
	});

	/*меню в main*/
	if($('.content-top-menu').length){
		$('.content-top-menu').wrap('<div class="cd-added-menu"></div>');
		$('.content-wrapper').addClass('top-menu-added');
	}

	$('body').on('focusout', 'input.field-numberfloat, input.field-number', function(){
		var reg = /^[-]?\d*(\.\d{1,2})?/, str = $(this).val(), res = '';
		if(str.indexOf(",") >= 0){
			str = str.replace(",",".");
		}
		res =  str.match(reg)? str.match(reg)[0] : "";
		$(this).val(res);
	});

	// клик по кнопке загрузки обновления для системы
	$('body').on('click', '#update_system', function(){
		add_loader();
		var Package = $(this).data('package');
		$.ajax({
			url: '/adminupdate?download=true',
			type: 'POST',
			data: { package: Package},
			success: function(data){
				var Res = JSON.parse(data);
				if(Res.status === 200){
					$('.check-update-info').addClass('error').html(Res.info);
				}else{
					$('.check-update-info').addClass('error').html(Res.info);
				}
				remove_loader();
			}
		});
		return false;
	});

	// клик по кнопке загрузки обновления для плагина
	$('body').on('click', '.update-plugin', function(){
		add_loader();
		var Package = $(this).data('package');
		var pluginName = $(this).data('name');
		$.ajax({
			url: '/adminupdate?download=true',
			type: 'POST',
			data: { package: Package},
			success: function(data){
				//console.log(data);
				var Res = JSON.parse(data);
				if(Res.status === 200){
					$('.plugin-' + pluginName).find('div.error').html(Res.info);
				}else{
					$('.plugin-' + pluginName).find('div.error').html(Res.info);
				}
				remove_loader();
			}
		});
		return false;
	});

	// клик по кнопке проверки наличия поля у объектов
	$('body').on('click', '.check-object', function() {
		var fieldID = $(this).data('field-id');
		var $Btn = $(this);
		add_loader();
		$.ajax({
			url: '/admin/get_field_objects',
			type: 'POST',
			data: { field_id: fieldID},
			dataType: 'json',
			success: function(DATA){
				console.log(DATA);
				if(DATA.status == 'OK') {
					$Btn.after(DATA.button);
				} else {
					noty_info('error', DATA.info, 'center');
				}
				remove_loader();
			},
			error: function(a, b, c) {
				noty_info('error', 'Error response', 'center');
				remove_loader();
			}
		});
		return false;
	});

	$('body').on('click', '.delete-all-fields', function() {
		var fieldID = $(this).data('field-id');
		var $Btn = $(this);
		if (confirm('Are you sure')) {
			add_loader();
			$.ajax({
				url: '/admin/get_field_objects?delete=1',
				type: 'POST',
				data: { field_id: fieldID},
				dataType: 'json',
				success: function(DATA){
					console.log(DATA);
					if(DATA.status == 'OK') {
						$Btn.parent().slideUp(300, function(){
							$Btn.parent().remove();
						});
						noty_info('information', DATA.info, 'center');
					} else {
						noty_info('error', DATA.info, 'center');
					}
					remove_loader();
				},
				error: function(a, b, c) {
					noty_info('error', 'Error response', 'center');
					remove_loader();
				}
			});
		}

		return false;
	});

	/* поля типа DATE*/
	admin_InitFieldsDate();

	/*переключател show-hidden*/
	appShSwitch();


}); // end document ready
