<?php defined('BASEPATH') OR exit('No direct script access allowed');
# файл конфигурации автозагрузки плагина
/*
	ссылка на папку плагинов - info('plugins_url')
*/

$info = array(
		'name' => 'Менеджер файлов',
		'descr' => 'Менеджер файлов для сайта',
		'version' => '2.7',
		'author' => 'Сергей Будников',
		'url' => 'http://site.ru',
	);

/*
* version
*
* 0.1
* Первая версия.
*
* 0.2
* Добавлена возможность добавить менеджер для front-end сайта
*
* 0.3
* Добавлена сортировка перетаскиванием файлов при добавлении файлов
*
* 0.4
* Возможность добавлять описание к файлам
*
* 1.0
* Добавлена модель ElfinderModel для упрощения работы с файлами
* сохранена совместимость с предыдущими версиями
*
* 2.2
* обновлен elfinder.js
*
* 2.3
* Добавлено удаление миниатюр при удалении основного файла
*
* 2.4
* Добавлена иконка открытия пути к файлу в менеджере
*
* 2.5
* Введена поддержка языков
*
* 2.6
* Правки в коде
*
* 2.7
* Исправлен Баг скачивания файлов из Front-end
*/

$admin_menu = TRUE; // отображать в списке меню

# $load_admin - автозагрузка моделей и хелперов для админ панели
$load_admin = array(
		'helper' => array('elfinger'), // автозагрузка хелперов
		'model' => array('ElfinderModel'), // автозагрузка моделей
	);

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию HEAD
$load_admin['assets']['plugin']['top'] = array();

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию BODY
$load_admin['assets']['plugin']['bottom'] = array();

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию HEAD
$load_admin['assets']['admin']['top'] = array(
	//'0' => '<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.0/themes/smoothness/jquery-ui.css">',
	'1' => '<link rel="stylesheet" type="text/css" href="'.info('plugins_url').'elfinder/common/css/elfinder.min.css">',
	'2' => '<link rel="stylesheet" type="text/css" href="'.info('plugins_url').'elfinder/common/css/theme.css">',
	'3' => '<link rel="stylesheet" type="text/css" href="'.info('plugins_url').'elfinder/assets/elfinder-style.css">',
);

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию BODY
$load_admin['assets']['admin']['bottom'] = array(
	//'0' => '<script src="'.info('plugins_url').'elfinder/common/js/elfinder.min.js"></script>',
	'0' => '<script src="'.info('plugins_url').'elfinder/common/js/elfinder.full.js"></script>',
	'1' => '<script src="'.info('plugins_url').'elfinder/assets/elfinder-func.js"></script>',
	'2' => '<script src="'.info('plugins_url').'elfinder/assets/elfinder-custom.js"></script>',
);



# $load_site  - автозагрузка моделей и хелперов для сайта
$load_site = array(
		'helper' => array('elfinger'), // автозагрузка хелперов
		'model' => array('ElfinderModel'), // автозагрузка моделей
);

# файлы скриптов и стилей сайта в секцию HEAD
$load_site['assets']['top'] = array();

# файлы скриптов и стилей сайта в секцию BODY
$load_site['assets']['bottom'] = array();


# $load_site_admin - автозагрузка моделей и хелперов для сайта, ЕСЛИ ПОЛЬЗОВАТЕЛЬ ВОШЕЛ КАК Admin
$load_site_admin = array(
		'helper' => array(), // автозагрузка хелперов
		'model' => array(), // автозагрузка моделей
);

# файлы скриптов и стилей сайта в секцию HEAD, ЕСЛИ ПОЛЬЗОВАТЕЛЬ ВОШЕЛ КАК Admin
$load_site_admin['assets']['top'] = array();

# файлы скриптов и стилей сайта в секцию BODY, ЕСЛИ ПОЛЬЗОВАТЕЛЬ ВОШЕЛ КАК Admin
$load_site_admin['assets']['bottom'] = array();

# опции плагина
/*
	опции для настройки плагина. Формат array(key => value)

	$options['key_field']['name'] = 'Название поля';
	$options['key_field']['type'] = 'тип поля (text, textarea, select)';
	$options['key_field']['default'] = 'по умолчанию';
	$options['key_field']['values'] = array(
										'key1' => 'val1',
										'key2' => 'val2',
										);
	$options['key_field']['description'] = 'Подпись к полю';

*/
$options = array();
$options['el_thumb_path']['name'] = 'Путь до папки с миниатюрами';
$options['el_thumb_path']['type'] = 'text';
$options['el_thumb_path']['default'] = 'uploads/thumb/';
$options['el_thumb_path']['values'] = array();
$options['el_thumb_path']['description'] = 'Например: « uploads/thumb/ »';

$options['el_placehold_path']['name'] = 'Путь до папки с заглушками';
$options['el_placehold_path']['type'] = 'text';
$options['el_placehold_path']['default'] = 'uploads/placeholder/';
$options['el_placehold_path']['values'] = array();
$options['el_placehold_path']['description'] = 'Например: « uploads/placeholder/ »';

$options['el_userfolder_path']['name'] = 'Название папки для юзеров';
$options['el_userfolder_path']['type'] = 'text';
$options['el_userfolder_path']['default'] = 'tempuserfiles';
$options['el_userfolder_path']['values'] = array();
$options['el_userfolder_path']['description'] = 'Название латиницей! Например: "tempuserfiles". В этой папке будут хранить файлы юзеров, которые могут загружать файлы на сайт';
