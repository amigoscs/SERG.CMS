<?php defined('BASEPATH') OR exit('No direct script access allowed');
/*
	*  файл helper плагина
	* version 0.11
	* UPD 2017-08-17

	* version 0.12
	* UPD 2018-03-30
	* Добавлена функция apeditorlink - генератор ссылки на редактирование страницы
*/

# создание урла для страницы исключая дубли
function admin_page_create_uri($uri = '')
{
	$uri = app_translate_uri($uri);
	return $uri;
}

# генератор случайной строки
function admin_page_generate_string($text = ''){
	$length = 8;
	$chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ';
	$numChars = strlen($chars);
	$string = '';
	for ($i = 0; $i < $length; $i++) {
		$string .= substr($chars, rand(1, $numChars) - 1, 1);
	}
	return $string;
}

# генератор ссылки на редактирование страницы
function apeditorlink($nodeID = 0){
	if($nodeID && is_admin()){
		$link = '<a href="'.info('baseUrl').'admin/admin-page/edit?node_id='.$nodeID.'" title="" target="_blank">Edit page</a>';
		return $link;
	}
	return '';
}
