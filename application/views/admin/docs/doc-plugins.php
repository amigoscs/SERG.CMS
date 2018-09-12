<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<h1>Плагины</h1>
<?=$message?>

<? require_once(__DIR__ . '/units/doc-menu.php') ?>

<h2>Структура папок и файлов плагина</h2>
<ul>
	<li><strong>assets</strong> - папка со стилями и скриптами</li>
	<li><strong>libraries</strong> - папка с файлом плагина. Файл отвечает за вывод информации на странице плагина. Публичные методы плагина - есть страницы плагина</li>
	<li><strong>models</strong> - папка моделей плагина.</li>
	<li><strong>views</strong> - папка видов плагина.</li>
	<li><strong>autoload.php</strong> - файл загрузки плагина.</li>
</ul>

<h2>Файл autoload.php</h2>
<p>Вот как он выглядит:</p>
<pre style="font-family: monospace;background: #f1f0f0;padding: 8px;">
$info = array(
		'name' => 'Дерево сайта',
		'descr' => 'Плагин структуры сайта',
		'version' => '2.1',
		'author' => 'Сергей Будников',
		'url' => 'http://site.ru',
	);

$admin_menu = TRUE; // Вывести пункт меню в админ панели для плагина

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
	'0' => '&#8249;link rel="stylesheet" href="'.info('plugins_url').'admin-site-tree/assets/jqtree.css"/&#8250;',
);

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию BODY
$load_admin['assets']['plugin']['bottom'] = array(
	'0' => '&#8249;script src="'.info('plugins_url').'admin-site-tree/assets/adstreefunc.js"&#8250;&#8249;/script&#8250;',
);

# виджеты
$load_admin['widjet'] = TRUE;


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
	$options['key_field']['type'] = 'тип поля (text, textarea, select, multiselect)';
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

<strong># ХУКИ</strong>
$urlPluginHooks = array();
$urlPluginHooks['path'] = 'admin-site-tree'; // название папки плагина

// <strong>Хук на подключение Модели и запуск Метода этой модели в начале работы контроллера Index.php</strong>
$urlPluginHooks['index']['preload']['model'] = 'AdstRemap'; // модель для загрузки в начале работы контроллера
$urlPluginHooks['index']['preload']['method'] = 'indexPage'; // метод в модели в начале работы контроллера

// <strong>Хук на подключение Модели и запуск Метода этой модели в начале работы вывода контента сайта в контроллере Index.php</strong>
$urlPluginHooks['index']['render_content_start']['model'] = 'AdstRemap'; // модель для загрузки в начале работы вывода
$urlPluginHooks['index']['render_content_start']['method'] = 'renderPage'; // метод в модели в начале работы вывода

// <strong>Хук на подключение Модели и запуск Метода этой модели в конце работы вывода контента сайта в контроллере Index.php</strong>
$urlPluginHooks['index']['render_content_end']['model'] = 'AdstRemap'; // модель для загрузки в конце работы вывода
$urlPluginHooks['index']['render_content_end']['method'] = 'renderPage'; // метод в модели в конце работы вывода

// <strong>Хук на подключение Модели и запуск Метода этой модели в начале работы вывода шаблона сайта в контроллере Index.php</strong>
$urlPluginHooks['index']['render_page_start']['model'] = 'AdstRemap'; // модель для загрузки в начале работы вывода
$urlPluginHooks['index']['render_page_start']['method'] = 'renderPage'; // метод в модели в начале работы вывода

// <strong>Хук на подключение Модели и запуск Метода этой модели в конце работы вывода шаблона сайта в контроллере Index.php</strong>
$urlPluginHooks['index']['render_page_end']['model'] = 'AdstRemap'; // модель для загрузки в конце работы вывода
$urlPluginHooks['index']['render_page_end']['method'] = 'renderPage'; // метод в модели в конце работы вывода

// <strong>Хук на подключение Модели и запуск Метода этой модели в начале работы вывода шаблона 404 - страницы в контроллере Index.php</strong>
$urlPluginHooks['index']['render_notfound']['model'] = 'AdstRemap'; // модель для загрузки в начале работы вывода 404
$urlPluginHooks['index']['render_notfound']['method'] = 'notFoundPage'; // метод в модели в начале работы вывода 404

<p>Первый параметр массива - это адрес:</p>
// <strong>Хук отработает по адресу <em>index/catalog/goods/produсt</em></strong>
$urlPluginHooks['index/catalog/goods/produсt']['preload']['model'] = 'AdstRemap'; // сработает если URL = site.com/catalog/goods/produt
$urlPluginHooks['index/catalog/goods/produсt']['preload']['method'] = 'notFoundPage'; // метод в модели

// <strong>Хук отработает по адресам <em>index/catalog/ЛЮБОЙ АДРЕС</em></strong>
$urlPluginHooks['index/catalog/*']['render_page_start']['model'] = 'AdstRemap'; // сработает если URL = site.com/catalog/*
$urlPluginHooks['index/catalog/*']['render_page_start']['method'] = 'myTest'; // метод в модели

// <strong>Хук отработает на любой странице сайта</strong>
$urlPluginHooks['*']['preload']['model'] = 'AdstRemap'; // сработает если URL = site.com/catalog/goods/produt
$urlPluginHooks['*']['preload']['method'] = 'stopNotAuth'; // метод в модели в начале работы вывода 404
</pre>

<h2>Хуки в админ-панели</h2>
<p>Вы можете включить Хук для его работы на странице настройки плагина (настройки, которые в autoload.php отмечены <strong>$options</strong>)</p>
<p>Для этого в папке плагина создайте папку <strong>admin-hooks</strong> и поместите в нее файл <strong>Setting-hook.php</strong> (название файла с заглавной буквы)</p>
<p>В этом файле напишите конструкцию:</p>
<pre style="font-family: monospace;background: #f1f0f0;padding: 8px;">
class HookPluginSetting extends CI_Model
{
	private $data;
	
	function __construct($data = array())
    {
        parent::__construct();
        
        # <strong>ОПЦИИ НАСТРОЕК ПЛАГИНА</strong>
		$this->data = $data;
		
    }
	
	public function runHook()
	{
		/*
			# <strong>ЗДЕСЬ ВАШ КОД для обработки опций</strong>
		*/
		return $this->data;
	}
}
	
</pre>


