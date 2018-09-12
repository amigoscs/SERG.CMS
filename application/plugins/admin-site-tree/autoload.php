<?php defined('BASEPATH') OR exit('No direct script access allowed');
# файл конфигурации автозагрузки плагина
/*
	ссылка на папку плагинов - info('plugins_url')

	* 2.21
	* Исправлена ошибка при изменении статуса видимости объекта
	*
	* 3.0
	* исправлен под версию 4.0
	*
	* 4.0
	* исправлен под версию 5.0
	*
	* 4.1
	* Выполнен перевод на языки.
	*
	* 4.2
	* Исправления в яайлах JS. Запросы подтверждения действий
	*
	* 4.3
	* Добавлена возможность вывести в дереве значения для data-полей
	*
	* 4.31
	* Для заблокированных страниц выводится замочек
	*
	* 4.32
	* Включена поддержка многоязычности


*/

$info = array(
		'name' => 'Дерево сайта',
		'descr' => 'Плагин структуры сайта',
		'version' => '4.32',
		'author' => 'Сергей Будников',
		'url' => '//sergcms.ru',
	);

$admin_menu = TRUE; // отображать в списке меню

# $load_admin - автозагрузка моделей и хелперов для админ панели
$load_admin = array(
	'helper' => array('adst'), // автозагрузка хелперов
	'model' => array('AdstModel'), // автозагрузка моделей
);

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию HEAD
$load_admin['assets']['admin']['top'] = array();

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию BODY
$load_admin['assets']['admin']['bottom'] = array();

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию HEAD
$load_admin['assets']['plugin']['top'] = array(
	'0' => '<link rel="stylesheet" href="'.info('plugins_url').'admin-site-tree/assets/jqtree.css"/>',
	'1' => '<script src="'.info('plugins_url').'admin-site-tree/assets/tree.jquery.js"></script>',
	'2' => '<script src="'.info('plugins_url').'admin-site-tree/assets/jqTreeContextMenu.js"></script>',
);

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию BODY
$load_admin['assets']['plugin']['bottom'] = array(
	'0' => '<script src="'.info('plugins_url').'admin-site-tree/assets/adstreefunc.js"></script>',
	'1' => '<script src="'.info('plugins_url').'admin-site-tree/assets/adstree.js"></script>',
);



# $load_site - автозагрузка моделей и хелперов для сайта
$load_site = array(
		'helper' => array(), // автозагрузка хелперов
		'model' => array(), // автозагрузка моделей
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
$options['adst_close_site']['name'] = 'Закрыть сайт на техническое обслуживание';
$options['adst_close_site']['type'] = 'select';
$options['adst_close_site']['default'] = 'no';
$options['adst_close_site']['values'] = array(
									'no' => 'Сайт работает',
									'yes' => 'Сайт закрыт на обслуживание',
									);
$options['adst_close_site']['description'] = 'Установите состояние сайта';

$options['adst_close_template']['name'] = 'Файл-шаблон заставка';
$options['adst_close_template']['type'] = 'text';
$options['adst_close_template']['default'] = 'website-not-available';
//$options['adst_close_temlate']['values'] = array();
$options['adst_close_template']['description'] = 'Название файла в корне папки шаблона';

$options['adst_available_groups']['name'] = 'Разрешения на вход для групп';
$options['adst_available_groups']['type'] = 'multiselect';
$options['adst_available_groups']['default'] = 2;
$options['adst_available_groups']['values'] = array();
$options['adst_available_groups']['description'] = 'Укажите группы, которые могут получить доступ к закрытому сайту. Группа Admin активирована всегда';

$options['adst_view_data']['name'] = 'ID data-полей, которые вывести в дереве';
$options['adst_view_data']['type'] = 'text';
$options['adst_view_data']['default'] = '';
//$options['adst_close_temlate']['values'] = array();
$options['adst_view_data']['description'] = 'Через запятую ID data-полей, которые будут выведены в дереве сайта';
# ХУКИ
$urlPluginHooks = array();
$urlPluginHooks['path'] = 'admin-site-tree'; // патч до папки плагина
//$urlPluginHooks['index']['preload']['model'] = 'AdstRemap'; // модель для загрузки в начале работы контроллера
//$urlPluginHooks['index']['preload']['method'] = 'indexPage'; // метод в модели в начале работы контроллера

/*$urlPluginHooks['index']['render_content_start']['model'] = 'AdstRemap'; // модель для загрузки в начале работы вывода
$urlPluginHooks['index']['render_content_start']['method'] = 'renderPage'; // метод в модели в начале работы вывода

$urlPluginHooks['index']['render_content_end']['model'] = 'AdstRemap'; // модель для загрузки в конце работы вывода
$urlPluginHooks['index']['render_content_end']['method'] = 'renderPage'; // метод в модели в конце работы вывода

$urlPluginHooks['index']['render_page_start']['model'] = 'AdstRemap'; // модель для загрузки в начале работы вывода
$urlPluginHooks['index']['render_page_start']['method'] = 'renderPage'; // метод в модели в начале работы вывода

$urlPluginHooks['index']['render_page_end']['model'] = 'AdstRemap'; // модель для загрузки в конце работы вывода
$urlPluginHooks['index']['render_page_end']['method'] = 'renderPage'; // метод в модели в конце работы вывода

$urlPluginHooks['index']['render_notfound']['model'] = 'AdstRemap'; // модель для загрузки в начале работы вывода 404
$urlPluginHooks['index']['render_notfound']['method'] = 'notFoundPage'; // метод в модели в начале работы вывода 404

$urlPluginHooks['index/catalog/goods/produt']['preload']['model'] = 'AdstRemap'; // сработает если URL = site.com/catalog/goods/produt
$urlPluginHooks['index/catalog/goods/produt']['preload']['method'] = 'notFoundPage'; // метод в модели в начале работы вывода 404*/

// эксперимент
//$urlPluginHooks['index/letnij-katalog/*']['render_page_start']['model'] = 'AdstRemap'; // сработает если URL = site.com/catalog/goods/produt
//$urlPluginHooks['index/letnij-katalog/*']['render_page_start']['method'] = 'myTest'; // метод в модели в начале работы вывода 404

$urlPluginHooks['*']['preload']['model'] = 'AdstRemap'; // сработает для всех страниц сайта
$urlPluginHooks['*']['preload']['method'] = 'stopNotAuth'; // сработает для всех страниц сайта
