<?php defined('BASEPATH') OR exit('No direct script access allowed');
# файл конфигурации автозагрузки плагина
/*
	ссылка на папку плагинов - info('plugins_url')
*/

$info = array(
		'name' => 'Хлебные крошки',
		'descr' => 'Плагин выводит на странице хлебные крошки',
		'version' => '2.1',
		'author' => 'Сергей Будников',
		'url' => 'http://site.ru',
	);

/*
* 0.1 - первая версия
*
*0.2 - добавлени микроразметка
*
* 1.0
* Изменена логика работы
*
* 1.1
* Возможность задать класс для списка Breadcrumbs->addUlClass($class)
*
* 1.2
* Исправлена ошибка при отсутствии объектов
*
* 1.3
* Исправлена ошибка определения главной страницы. Добавлен метод UlWrap('<div>', '</div>'). Создает обертку для UL хлебных кроше
*
* 2
* Совместимость с версией 4.0
*
* 2.1
* Совместимость с версией 6.0
*/

$admin_menu = FALSE; // отображать в списке меню

# $load_admin - автозагрузка моделей и хелперов для админ панели
$load_admin = array(
		'helper' => array(), // автозагрузка хелперов
		'model' => array(), // автозагрузка моделей
);

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию HEAD
$load_admin['assets']['plugin']['top'] = array();

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию BODY
$load_admin['assets']['plugin']['bottom'] = array();

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию HEAD
$load_admin['assets']['admin']['top'] = array();

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию BODY
$load_admin['assets']['admin']['bottom'] = array();




# $load_site - автозагрузка моделей и хелперов для сайта
$load_site = array(
		'helper' => array(), // автозагрузка хелперов
		'model' => array('breadcrumbs'), // автозагрузка моделей
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

$options['visible_home']['name'] = 'Показывать на главной';
$options['visible_home']['type'] = 'select';
$options['visible_home']['default'] = 'no';
$options['visible_home']['values'] = array('no' => 'Нет', 'yes' => 'Да');
$options['visible_home']['description'] = '';
