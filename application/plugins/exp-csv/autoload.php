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

	* UPD 2018-09-19
	* Version 9.1
	* Совместимость с возможностью подключения нескольких типов данных
	*
	* UPD 2018-10-02
	* Version 10.0
	* Переписана логика работы экспорта. Экспорт теперь лимитируется
	*
	* UPD 2018-10-09
	* Version 10.1
	* Добавлена функция парсера CSV-файла, с помощью которого можно удалить переносы строк в ячейках
	*
	* UPD 2018-10-10
	* Version 10.2
	* Теперь через CSV можно менять родительские ноды
	*
*/

$info = array(
		'name' => app_lang('EXPCSV_AUTOLOAD_PLUGIN_NAME'),
		'descr' => app_lang('EXPCSV_AUTOLOAD_PLUGIN_DESCRIPTION'),
		'version' => '10.2',
		'author' => app_lang('EXPCSV_AUTOLOAD_AUTHOR'),
		'url' => '//sergcms.ru',
	);

$admin_menu = true; // отображать в списке меню

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
	'0' => '<link rel="stylesheet" href="'.info('plugins_url').'exp-csv/assets/exp-csv-style.css?10">',
	'1' => '<script src="'.info('plugins_url').'exp-csv/assets/exp-csv-func.js?10"></script>',
	);

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию BODY
$load_admin['assets']['plugin']['bottom'] = array(
	'0' => '<script src="'.info('plugins_url').'exp-csv/assets/exp-csv-script.js?10"></script>',
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

$options['csv_fields_delimiter']['name'] = app_lang('EXPCSV_AUTOLOAD_FIELDS_DELIMITER');
$options['csv_fields_delimiter']['type'] = 'text';
$options['csv_fields_delimiter']['default'] = ';';
$options['csv_fields_delimiter']['values'] = array();
$options['csv_fields_delimiter']['description'] = app_lang('EXPCSV_AUTOLOAD_FIELDS_DELIMITER_DESCR');

$options['csv_fields_enclosure']['name'] = app_lang('EXPCSV_AUTOLOAD_ROWS_ENCLOSURE');
$options['csv_fields_enclosure']['type'] = 'text';
$options['csv_fields_enclosure']['default'] = '"';
$options['csv_fields_enclosure']['values'] = array();
$options['csv_fields_enclosure']['description'] = app_lang('EXPCSV_AUTOLOAD_ROWS_ENCLOSURE_DESCR');

$options['csv_count_prev']['name'] = app_lang('EXPCSV_AUTOLOAD_ROWS_PREVIEW');
$options['csv_count_prev']['type'] = 'text';
$options['csv_count_prev']['default'] = '20';
$options['csv_count_prev']['values'] = array();
$options['csv_count_prev']['description'] = app_lang('EXPCSV_AUTOLOAD_ROWS_PREVIEW_DESCR');

$options['csv_limit_import']['name'] = app_lang('EXPCSV_AUTOLOAD_LIMIT_IMPORT');
$options['csv_limit_import']['type'] = 'text';
$options['csv_limit_import']['default'] = '300';
$options['csv_limit_import']['values'] = array();
$options['csv_limit_import']['description'] = app_lang('EXPCSV_AUTOLOAD_LIMIT_IMPORT_DESCR');
