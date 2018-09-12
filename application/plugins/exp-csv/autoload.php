<?php defined('BASEPATH') OR exit('No direct script access allowed');
# файл конфигурации автозагрузки плагина
/*
	ссылка на папку плагинов - info('plugins_url')
	version 5.0 правки и улучшения. Поддержка версии системы 3.4

	version 6.0. Совеместимость с системой 4.0. 2018-01-15
	version 7.0. Совеместимость с системой 5.0. 2018-01-25
	version 7.1. Ошибка импорта. 2018-01-30
	version 7.2. Исправления полей выгрузки. 2018-03-13
	version 7.21. Исправления в описании. Мелкие правки 2018-03-14
	version 7.3. Мелкие правки. Добавлена выгрузка доступов 2018-04-18
	version 8.0. Правки по выгрузке. Перевод. 2018-05-10
	version 8.1. Добавлена выгрузка вложенных объектов. Правки в JS. Перевод. 2018-06-25
	version 8.2. НА странице импорта можно передать путь до файла через GET параметр file
	version 9.0. совместимость с версией 6.0
*/

$info = array(
		'name' => app_lang('EXPCSV_AUTOLOAD_PLUGIN_NAME'),
		'descr' => app_lang('EXPCSV_AUTOLOAD_PLUGIN_DESCRIPTION'),
		'version' => '9.0',
		'author' => app_lang('EXPCSV_AUTOLOAD_AUTHOR'),
		'url' => '//sergcms.ru',
	);

$admin_menu = TRUE; // отображать в списке меню

# $load_admin - автозагрузка моделей и хелперов для админ панели
$load_admin = array(
	'helper' => array('expcsv'), // автозагрузка хелперов
	'model' => array('ExpCsvModel'), // автозагрузка моделей
);

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию HEAD
$load_admin['assets']['admin']['top'] = array();

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию BODY
$load_admin['assets']['admin']['bottom'] = array();

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию HEAD
$load_admin['assets']['plugin']['top'] = array(
	'0' => '<script src="'.info('plugins_url').'exp-csv/assets/exp-csv-func.js"></script>',
	'1' => '<link rel="stylesheet" href="'.info('plugins_url').'exp-csv/assets/exp-csv-style.css">',
	);

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию BODY
$load_admin['assets']['plugin']['bottom'] = array(
	'0' => '<script src="'.info('plugins_url').'exp-csv/assets/exp-csv-script.js?20180625"></script>',
	);


# $load_site - автозагрузка моделей и хелперов для сайта
$load_site = array(
		'helper' => array(), // автозагрузка хелперов
		'model' => array(), // автозагрузка моделей
);

# файлы скриптов и стилей сайта в секцию HEAD
$load_site['assets']['top'] = array(

);

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

$options['fields_edit']['name'] = app_lang('EXPCSV_AUTOLOAD_FIELDS_FAST_EDIT');
$options['fields_edit']['type'] = 'textarea';
$options['fields_edit']['default'] = 'obj_anons, obj_content';
$options['fields_edit']['values'] = array();
$options['fields_edit']['description'] = app_lang('EXPCSV_AUTOLOAD_FIELDS_FAST_EDIT_DESCR');

$options['csv_fields_delimiter']['name'] = app_lang('EXPCSV_AUTOLOAD_FIELDS_DELIMITER');
$options['csv_fields_delimiter']['type'] = 'text';
$options['csv_fields_delimiter']['default'] = ';';
$options['csv_fields_delimiter']['values'] = array();
$options['csv_fields_delimiter']['description'] = app_lang('EXPCSV_AUTOLOAD_FIELDS_DELIMITER_DESCR');

//$options['csv_fields_rows']['name'] = app_lang('EXPCSV_AUTOLOAD_ROWS_DELIMITER');
//$options['csv_fields_rows']['type'] = 'text';
//$options['csv_fields_rows']['default'] = '';
//$options['csv_fields_rows']['values'] = array();
//$options['csv_fields_rows']['description'] = app_lang('EXPCSV_AUTOLOAD_ROWS_DELIMITER_DESCR');

$options['csv_count_prev']['name'] = app_lang('EXPCSV_AUTOLOAD_ROWS_PREVIEW');
$options['csv_count_prev']['type'] = 'text';
$options['csv_count_prev']['default'] = '20';
$options['csv_count_prev']['values'] = array();
$options['csv_count_prev']['description'] = app_lang('EXPCSV_AUTOLOAD_ROWS_PREVIEW_DESCR');
