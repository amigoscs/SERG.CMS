<?php defined('BASEPATH') OR exit('No direct script access allowed');
# файл конфигурации автозагрузки плагина
/*
	ссылка на папку плагинов - info('plugins_url')

VERSION 3.3. Совместимость с 3.41
VERSION 3.4. Совместимость с 3.5
VERSION 4.0. Совместимость с 4.0
VERSION 5.0. Совместимость с 5.0
VERSION 5.1. Добавлена функция генерирующая ссылку на редактирование страницы
VERSION 5.2. Исправлена ошибка при редактировании несуществующего объекта

	* UPD 2018-09-19
	* Version 6.0
	* Теперь можно подключать к страницам несколько типов данных. Подключены языковые файлы
	* Для перехода выполнить SQL комнаду:
	* ALTER TABLE  `{DBPREFIX}objects` CHANGE `obj_data_type` `obj_data_type` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
	*
	* UPD 2018-09-26
	* Version 6.1
	* Исправлена ошибка, если объект имеет ноду без родителя. Она будет автоматически удалена
	*
	* UPD 2018-10-08
	* Version 6.2
	* Показано для редактирования кол-во просмотров, рейтинг. Перевод на английский
	*
	* UPD 2018-10-11
	* Version 6.3
	* Переработана логика. Добавлены методы для работы с формой по ajax
	*
*/

$info = array(
		'name' => 'Редактор страниц',
		'descr' => 'Плагин создания редактирования страниц',
		'version' => '6.3',
		'author' => 'Сергей Будников',
		'url' => '//sergcms.ru',
	);

$admin_menu = false; // отображать в списке меню

# $load_admin - автозагрузка моделей и хелперов для админ панели
$load_admin = array(
		'helper' => array('admpage'), // автозагрузка хелперов
		'model' => array('AdminPageModel'), // автозагрузка моделей
	);

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию HEAD
$load_admin['assets']['plugin']['top'] = array();

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию BODY
$load_admin['assets']['plugin']['bottom'] = array();

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию HEAD
$load_admin['assets']['admin']['top'] = array();

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию BODY
$load_admin['assets']['admin']['bottom'] = array(
	'0' => '<link rel="stylesheet" href="'.info('plugins_url').'admin-page/assets/admpa-stl.css">',
);


# $load_site - автозагрузка моделей и хелперов для сайта
$load_site = array(
		'helper' => array('admpage'), // автозагрузка хелперов
		'model' => array('AdminPageModel'), // автозагрузка моделей
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
$load_site_admin['assets']['top'] = array(
	'0' => '<link rel="stylesheet" href="'.info('plugins_url').'admin-page/assets/admpa-stl.css">',
	);

# файлы скриптов и стилей сайта в секцию BODY, ЕСЛИ ПОЛЬЗОВАТЕЛЬ ВОШЕЛ КАК Admin
$load_site_admin['assets']['bottom'] = array(
	'0' => '<script src="'.info('plugins_url').'admin-page/assets/admpa-spt.js"></script>',
	);

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
