<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<!DOCTYPE html>
<html lang="ru" prefix="og: http://ogp.me/ns#">
<head>
	<meta charset="UTF-8">
	<title>Elfinder</title>
	<link href="" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/application/plugins/elfinder/common/css/theme.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/redmond/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="/application/plugins/elfinder/common/css/elfinder.min.css">
	<link rel="stylesheet" type="text/css" href="/application/plugins/elfinder/assets/elfinder-style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="/application/plugins/elfinder/common/js/elfinder.min.js"></script>
</head>
<body>

<div id="elfinder"></div>

<? if($type == 'tinymce'): ?>
<script type="text/javascript">
	var tmceCallbackObj = {
		init: function(){

		},
		mySubmit: function(file, elf){
			parent.tinymce.activeEditor.windowManager.getParams().oninsert(file, elf);
			parent.tinymce.activeEditor.windowManager.close();
		}
	}

	var ELF = $('#elfinder').elfinder({
		url : '/admin/elfinder/connector',
		lang: 'ru',
		getFileCallback: function(data) {
			tmceCallbackObj.mySubmit(data, ELF)
		}
	}).elfinder('instance');;

</script>
<? else: ?>

<? endif ?>
</body>
</html>
