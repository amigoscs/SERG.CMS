<?php defined('BASEPATH') OR exit('No direct script access allowed');
# файл конфигурации автозагрузки плагина
/*
	*
	* ссылка на папку плагинов - info('plugins_url')
	* UPD 2018-04-17
	* 0.1
	* Плагин маркет
	*
	* UPD 2018-09-07
	* 1.0
	* Первая версия плагина. Тестовая
	*
	* UPD 2018-09-07
	* 1.1
	* сделана выгрузка прайс-листа. Обязательно плагин exp-csv версии 10+
	*
	* UPD 2018-10-04
	* 1.2
	* Правки в коде
	*
	* UPD 2018-10-15
	* 1.3
	* В таблице товаров теперь можно выводить дополнительные поля
	*
*/

$info = array(
		'name' => 'E-market',
		'descr' => 'Плагин электронной торговли',
		'version' => '1.3',
		'author' => 'Сергей Будников',
		'url' => '//sergcms.ru',
	);

$admin_menu = true; // отображать в списке меню


# $load_admin - автозагрузка моделей и хелперов для админ панели
$load_admin = array(
	'helper' => array('emarket'), // автозагрузка хелперов
	'model' => array('EmarketModel'), // автозагрузка моделей
);

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию HEAD
$load_admin['assets']['admin']['top'] = array();

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию BODY
$load_admin['assets']['admin']['bottom'] = array(
	//'0' => '<link href="'.info('plugins_url').'to-cart/assets/tocart-style.css" rel="stylesheet" type="text/css">',
	);

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию HEAD
$load_admin['assets']['plugin']['top'] = array();

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию BODY
$load_admin['assets']['plugin']['bottom'] = array(
	'0' => '<link href="'.info('plugins_url').'e-market/assets/emarket-admin-style.css" rel="stylesheet" type="text/css">',
	'1' => '<script src="'.info('plugins_url').'e-market/assets/emarket-admin-script.js"></script>',
);

####
# виджеты
####
# виджет админ панели
$load_admin['widjet'] = 'EmarketWidjet'; // Класс виджета, через который будет доступен виджет

# $load_site - автозагрузка моделей и хелперов для сайта
$load_site = array(
		'helper' => array('emarket'), // автозагрузка хелперов
		'model' => array('EmarketModel'), // автозагрузка моделей
);

# файлы скриптов и стилей сайта в секцию HEAD
$load_site['assets']['top'] = array(
	'0' => '<script src="'.info('plugins_url').'e-market/assets/emarket-plugin.js"></script>',
);

# файлы скриптов и стилей сайта в секцию BODY
$load_site['assets']['bottom'] = array(
	'0' => '<script src="'.info('plugins_url').'e-market/assets/emarket-script.js"></script>',
);




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
$options['e_market_ip_loc_url']['name'] = 'Ссылка на поиск по IP';
$options['e_market_ip_loc_url']['type'] = 'text';
$options['e_market_ip_loc_url']['default'] = '//www.seogadget.ru/location?addr=__IP__';
$options['e_market_ip_loc_url']['values'] = array();
$options['e_market_ip_loc_url']['description'] = 'Укажите в нужном месте ссылки «__IP__». В это место подставится ip для определения местоположения';
