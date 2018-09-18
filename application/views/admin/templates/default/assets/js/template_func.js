function noty_info(Type, Text, Layout)
{
	if(!Layout)
		Layout = 'center';
	//type = 'alert';
	//type = 'information';
	//type = 'error';
	//type = 'warning';
	//type = 'notification';
	//type = 'success';

	// layout = 'top';
	// layout = 'topCenter';
	// layout = 'topLeft';
	// layout = 'topRight';
	// layout = 'center';
	// layout = 'centerLeft';
	// layout = 'centerRight';
	// layout = 'bottom';
	// layout = 'bottomCenter';
	// layout = 'bottomLeft';
	// layout = 'bottomRight';
	var n = noty({
		text        : Text,
		type        : Type,
		dismissQueue: true,
		timeout     : 2000,
		closeWith   : ['click'],
		layout      : Layout,
		theme       : 'defaultTheme',
		maxVisible  : 10
	});
	//console.log('html: ' + n.options.id);
}

function noty_comfirm(Text, ButOne, ButTwo, CallBack)
{
	$('body').append('<div id="back_layot" style="position: absolute;top:0; left:0;width:100%; height:100%; background:rgba(8,5,5,0.3);"></div>');
	var n = noty({
            text        : Text,
            type        : 'alert',
            dismissQueue: true,
            layout      : 'center',
            theme       : 'defaultTheme',
            buttons     : [
                {
					addClass: 'btn btn-primary w100 mar8-tb', text: ButOne, onClick: function ($noty) {
                    	$noty.close();
						if(CallBack) CallBack(true);
						$('div#back_layot').remove();
                    //noty({dismissQueue: true, force: true, layout: layout, theme: 'defaultTheme', text: 'You clicked "Ok" button', type: 'success'});
                	}
                },
                {
					addClass: 'btn btn-danger w100 mar8-tb', text: ButTwo, onClick: function ($noty) {
                    	$noty.close();
						if(CallBack) CallBack(false);
                    	$('div#back_layot').remove();
					}
                }
            ]
        });
        console.log('html: ' + n.options.id);
}


function noty_comfirm_sel(Text, ButOne, ButTwo, ButThree, CallBack)
{
	$('body').append('<div id="back_layot" style="position: absolute;top:0; left:0;width:100%; height:100%; background:rgba(8,5,5,0.3);"></div>');
	var n = noty({
            text        : Text,
            type        : 'alert',
            dismissQueue: true,
            layout      : 'center',
            theme       : 'defaultTheme',
            buttons     : [
                {
					addClass: 'btn btn-primary mar8-tb w100', text: ButOne, onClick: function ($noty) {
                    $noty.close();
					$('div#back_layot').remove();
					if(CallBack) CallBack('one');
                    //noty({dismissQueue: true, force: true, layout: layout, theme: 'defaultTheme', text: 'You clicked "Ok" button', type: 'success'});
                	}
                },
                {
					addClass: 'btn btn-primary mar8-tb w100', text: ButTwo, onClick: function ($noty) {
                    $noty.close();
					$('div#back_layot').remove();
					if(CallBack) CallBack('two');
                    //noty({dismissQueue: true, force: true, layout: layout, theme: 'defaultTheme', text: 'You clicked "Cancel" button', type: 'error'});
                	}
                },
				{
					addClass: 'btn btn-danger mar8-tb w100', text: ButThree, onClick: function ($noty) {
                    $noty.close();
					$('div#back_layot').remove();
					if(CallBack) CallBack('three');
                    //noty({dismissQueue: true, force: true, layout: layout, theme: 'defaultTheme', text: 'You clicked "Cancel" button', type: 'error'});
                	}
                }
            ]
        });
        console.log('html: ' + n.options.id);
}

/*включить лоадер*/
function add_loader()
{
	$('body').append('<div id="admin_loader"></div>');
}
/*выключить лоадер*/
function remove_loader()
{
	if($('body #admin_loader').length)
	{
		$('body #admin_loader').remove();
	}
}

function _tnmc_elfinder_browser(callback, value, meta)
{
	tinymce.activeEditor.windowManager.open({
		file: "/admin/elfinder/elfinder_only_page?type=tinymce",
		title: 'File Explorer',
		width: 900,
		height: 450,
		resizable: 'yes'
	},
	{
	oninsert: function(file, elf){
		var url, reg, info;
		url = file.url;
		reg = /\/[^/]+?\/\.\.\//;
		while(url.match(reg)){
			url = url.replace(reg, '/');
		}
		console.log(url);
		// docs https://www.youtube.com/watch?v=WlLrDx_ZVw4
		callback(url, {alt: file.name});
	}
	});
	return false;
}

/*инициализация tonyMCE*/
function tinymceRun()
{
	tinymce.remove("textarea.editor");
	tinymce.init({
		height: '400',
		document_base_url : '/',
		convert_urls : false,
		selector:'textarea.editor',
		plugins: [ "advlist autolink autosave link image lists charmap print preview hr anchor pagebreak spellchecker","searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking","table contextmenu directionality emoticons template textcolor paste textcolor colorpicker textpattern"
		],
		toolbar1: "template pagebreak newdocument fullpage | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
		toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | preview | forecolor backcolor",
		toolbar3: "table | hr removeformat | charmap | fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking restoredraft code image",
		image_advtab: true,
		image_class_list: [
			{title: 'None', value: ''},
			{title: 'Left', value: 'left'},
			{title: 'Right', value: 'right'},
			{title: 'Center', value: 'center'},
		],
		pagebreak_split_block: true,
		templates: [

		],
		relative_urls: false,
		menubar: false,
		toolbar_items_size: 'small',
		style_formats_merge: true,
		style_formats: [
			{title: 'Upper text', inline: 'span', styles: {'text-transform': 'uppercase'}},
		],
		file_picker_callback: _tnmc_elfinder_browser
		/*entity_encoding: 'numeric',*/
	});
}

/*ui диалоговое окно*/
var DialogBlockID = 'ui-dialog';
function admin_dialog(Html, Title, WidthDialog)
{
	/*$('#'+DialogBlockID).dialog( "destroy" );*/
	if(!Title)
		Title = 'Default title';

	if(!WidthDialog)
		WidthDialog = 1000;

	$('#'+DialogBlockID).html(Html);
	$('#'+DialogBlockID).dialog({
		minWidth: WidthDialog,
		title: Title
	});
}

/* инициализация полей типа Date*/
function admin_InitFieldsDate()
{
	if($('input.field-date').length)
	{
		$('input.field-date').after('<input type="text" class="form-control date-input"/>');
		$('input.field-date').each(function(index, elem){
			var Input = $(elem);
			var EnableTime = false;
			var ISOformat = 'YYYY-MM-DD';
			var DateFormat = $(elem).data('date-format');
			var InputValue = Input.val();

			Input.hide();
			// если надо включить время
			if(Input.hasClass('fd-time')){
				EnableTime = true;
				ISOformat = 'YYYY-MM-DD HH:mm:ss';
				InputValue = moment(InputValue, ISOformat).format(DateFormat);
			}else{
				EnableTime = false;
				ISOformat = 'YYYY-MM-DD';
				InputValue = moment(InputValue, ISOformat).format(DateFormat);
			}

			Input.next('.date-input').val(InputValue).dateRangePicker({
				format: DateFormat,
				autoClose: true,
				singleDate : true,
				showShortcuts: false,
				monthSelect: true,
    			yearSelect: true,
				startOfWeek: 'monday',// or sunday
				language: cmsGetLang('scriptLangShort'),
				time: {
					enabled: EnableTime
				},
				setValue: function(s) {
					$(this).prev('input').val(moment(s, DateFormat).format(ISOformat));
					$(this).val(s);
				}
			})
		})

	}
}

/*
* запрос на обновление системы
*/
var appGetUpdate;
appGetUpdate = function(CMSversion, Type, lang, Name, callBack){
	$.ajax({
		url: 'http://upsergcms.baikalinvest.com',
		type: 'POST',
		data: { version: CMSversion, action: 'check', lang: lang, type: Type, name: Name},
		success: function(data){
			if(callBack){
				callBack(data);
			}
		}
	});
};

/*
* Транслит
*/
function appJsTranslit(str) {
	str = str.toLowerCase().replace(/<.+>/, ' ').replace(/\s+/, ' ');
	var c = {
		'а':'a', 'б':'b', 'в':'v', 'г':'g', 'д':'d', 'е':'e', 'ё':'jo', 'ж':'zh', 'з':'z', 'и':'i', 'й':'j', 'к':'k', 'л':'l', 'м':'m', 'н':'n', 'о':'o', 'п':'p', 'р':'r', 'с':'s', 'т':'t', 'у':'u', 'ф':'f', 'х':'h', 'ц':'c', 'ч':'ch', 'ш':'sh', 'щ':'shch', 'ъ':'', 'ы':'y', 'ь':'', 'э':'e', 'ю':'ju', 'я':'ja', ' ':'', ';':'', ':':'', ',':'', '—':'_', '–':'_', '-':'_','.':'', '«':'', '»':'', '"':'', "'":'', '@':''
	}
	var newStr = new String();
	for (var i = 0; i < str.length; i++) {
		ch = str.charAt(i);
		newStr += ch in c ? c[ch] : ch;
	}
	return newStr;
}

/*
	* Обработка show-hide-switch
*/
var appShSwKey = 'shSwitch';
var appShSwitch = function() {
	allItems = localStorage.getItem(appShSwKey);
	if(!allItems) {
		return false;
	}
	allItems = JSON.parse(allItems);
	var Elements = '';

	// раскроем элементы
	$.each(allItems.open, function(elemData, el) {
		Elements += '[data-sh-id="'+elemData+'"],';
	});

	if(Elements) {
		Elements = Elements.substring(0, Elements.length - 1);
		$(Elements).removeClass('hide');
	}

	Elements = '';

	// закроем элементы
	$.each(allItems.closed, function(elemData, el) {
		Elements += '[data-sh-id="'+elemData+'"],';
	});

	if(Elements) {
		Elements = Elements.substring(0, Elements.length - 1);
		$(Elements).addClass('hide');
	}
}
