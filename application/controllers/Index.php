<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends CI_Controller {

	/**
	 * Контроллер front-end
	 *
	 * UPD 2017-11-29
	 * version 9.14
	 *
	 * UPD 2017-12-05
	 * version 9.2
	 * изменения в canonical. Корреккция под обновленный класс urlobjects
	 *
	 * UPD 2017-12-12
	 * version 10.0
	 * Переименована папка Libraries/Template в Libraries/Templates
	 * добавлена обработка файлов библиотек:
	 *		Libraries/Contents/AllContents.php
	 *		Libraries/Templates/AllTemplates.php
	 *
	 * UPD 2017-12-18
	 * version 10.1
	 * Исправлена ошибка проверки запроса AJAX
	 *
	 * Version 11.0
	 * UPD 2018-01-15
	 * изменена таблица tree
	 *
	 * Version 11.1
	 * UPD 2018-02-19
	 * исправлена ошибка обновления canonical
	 *
	 * Version 12.0
	 * UPD 2018-03-30
	 * переименован метод из _render_page в render_template
	 *
	 * Version 13.0
	 * UPD 2018-04-03
	 * переделана логика работы вывода страницы по короткой ссылке. Добавлена возможность отключить стандартный вывод BreakRender
	 *
	 * Version 14.0
	 * UPD 2018-05-11
	 * переделана логика подключения бибиотек шаблона. Добавлена возможность подключать модели.
	 * Модели должны раполагаться в папке PATHTEMPLATE/Models/Contents/ContentsModel.php и PATHTEMPLATE/Models/Templates/TempltaesModel.php
	 * Модели подключаются к файлам: Для файла PATHTEMPLATE/contents/home-page будет подключена модель PATHTEMPLATE/Models/Contents/HomePageModel.php (class HomePageModel)
	 * Та же логика справедлива и для библиотек. Файл должен заканчиваться на Lib.php (HomePageLib.php)
	 * Пока сохранена совместимость со старыми шаблонами (старым методом подключения библиотек)
	 *
	 * Version 14.1
 	 * UPD 2018-05-15
	 * Несколько ошибок подключения файлов библиотек шаблона. Теперь в библиотеках контента можно переопределить значение $this->dataTemplate['CONTENT']
	 *
	 * Version 14.2
	 * UPD 2018-05-17
	 * правки в методе run_ajax
	 *
	 * Version 14.3
	 * UPD 2018-05-23
	 * Методы рендеринга страниц сделаны публичными
	 *
	 * Version 14.31
	 * UPD 2018-08-30
	 * Правки в работе с Ajax
	 *
	 * Version 14.4
	 * UPD 2018-09-14
	 * Поддержка библиотеки сжатия файлов стилей
	 *
	 * Version 14.5
	 * UPD 2018-09-19
	 * Теперь шаблон сайта в файле info.php может содержать массив стилей и скриптов для подключения на сайта
	 * Массив assets теперь: $headAssets и $bodyAssets
	 *
	 * Version 15.0
	 * UPD 2018-10-24
	 * Создан синглетон для перенных шаблона. Теперь перенные в шаблон добавляеются так: Template::getInstance()->VAR_NAME = VAR_VALUE или $CI->Template->VAR_NAME = VAR_VALUE;
	 * Получить значения можно так: Template::getInstance()->VAR_NAME или $CI->Template->VAR_NAME
	 *
	 */

	// Шаблон страницы
	public $page_template;

	// Шаблон контента
	public $content_template;

	// стили, подключаемые в шапке и в футере сайта. Массивы.
	public $headAssets, $bodyAssets;

	// массив сегментов URL. По нему идет ориентир, какую страницу отдавать в браузер
	public $urls;

	// отключение стандарного роутинга. Для использования в плагинах
	public $BreakRemap;

	// отключение стандартного вывода
	public $BreakRender;

	// загруженные страницы
	private $loading;

	// массив хуков плагинов
	private $hooksPlugin;

	// Информация о залогиневшемся пользователе
	public $userInfo;

	// текущий URL без домена $currentUrl и с доменом $currentBaseUrl
	public $currentUrl, $currentBaseUrl;

	// Включить иил отключить короткие ссылки TRUE - включены, FALSE - отключены
	private $activeShortLink;

	// путь до папки views активного шаблона
	public $viewsTemplatePath;

	// Массив данных шаблона (устарело в версии 7.4. Не использовать в шаблонах!)
	public $dataTemplate;

	// Объект данных шаблона
	public $Template;

	public function __construct()
	{
		parent::__construct();
		$this->content_template = $this->page_template = $this->viewsTemplatePath = '';
		$this->BreakRemap = $this->BreakRender = FALSE;
		$this->hooksPlugin = $this->urls = $this->loading = array();
		$this->headAssets = $this->bodyAssets = array();
		$this->dataTemplate = array();

		define("APP_SITE_TEMPLATE", app_get_option('site_template', 'site', 'default'));
		define("APP_SITE_404_TEMPLATE", app_get_option('not_found_template', 'site', ''));
		define("APP_SITE_TEMPLATE_URL", APP_BASE_URL . 'application/views/templates/' . APP_SITE_TEMPLATE);

		$this->LoginAdmModel->userData();

		$this->viewsTemplatePath = 'templates/' . APP_SITE_TEMPLATE . '/';
		$this->currentUrl = $this->uri->uri_string;
		$this->currentBaseUrl = APP_BASE_URL . $this->currentUrl;
		$this->userInfo = is_login();
		$this->activeShortLink = TRUE;

		# язык сайта
		$activeLang = app_get_option('site_lang', 'site', 'english');
		if(isset($this->userInfo['lang']) and file_exists(APPPATH . 'language/' . $this->userInfo['lang'])) {
			$activeLang = $this->userInfo['lang'];
		}

		$this->lang->load('site', $activeLang);

		# assets шаблона
		$pathFile = APPPATH . 'views/' . $this->viewsTemplatePath . 'info.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			if(isset($info['assets']['head'])) {
				$this->headAssets = $info['assets']['head'];
			}

			if(isset($info['assets']['body'])) {
				$this->bodyAssets = $info['assets']['body'];
			}
		}

		# переменные шаблона
		if(file_exists(APPPATH . 'libraries/Template.php')) {
			require_once(APPPATH . 'libraries/Template.php');
			$this->Template = Template::getInstance();
		}

		// автозагрузка плагинов
		if($plugins = plugin_load())
		{
			$dirs = array_keys($plugins);
			foreach($dirs as $dir)
			{
				$path = APP_PLUGINS_DIR_PATH . $dir . '/';
				if(!file_exists($path . 'autoload.php')) continue;
				# установим патч для плагина
				$this->load->add_package_path($path);
				require($path . 'autoload.php');

				if(isset($load_site['model']) and $load_site['model'])
					$this->load->model($load_site['model']);

				if(isset($load_site['helper']) and $load_site['helper'])
					$this->load->helper($load_site['helper']);

				# подключение js и css
				if(isset($load_site['assets']['top']) and $load_site['assets']['top']) {
					$this->headAssets = array_merge($this->headAssets, $load_site['assets']['top']);
				}

				if(isset($load_site['assets']['bottom']) and $load_site['assets']['bottom']) {
					$this->bodyAssets = array_merge($this->bodyAssets, $load_site['assets']['bottom']);
				}

				# подключение js и css для АДМИНА
				if(is_admin())
				{
					if(isset($load_site_admin['model']) and $load_site_admin['model'])
						$this->load->model($load_site_admin['model']);

					if(isset($load_site_admin['helper']) and $load_site_admin['helper'])
						$this->load->helper($load_site_admin['helper']);


					if(isset($load_site_admin['assets']['top']) and $load_site_admin['assets']['top']) {
						$this->headAssets = array_merge($this->headAssets, $load_site_admin['assets']['top']);
					}

					if(isset($load_site_admin['assets']['bottom']) and $load_site_admin['assets']['bottom']) {
						$this->bodyAssets = array_merge($this->bodyAssets, $load_site_admin['assets']['bottom']);
					}
					$load_site_admin = array();
				}

				# загрузка хуков
				if(isset($urlPluginHooks) and $urlPluginHooks) {
					$this->hooksPlugin[] = $urlPluginHooks;
				}

				# загрузка языков плагинов
				$activeLang = app_get_option('site_lang', 'site', 'english');
				if(isset($this->userInfo['lang']) and file_exists($path . 'language/' . $this->userInfo['lang']))
					$activeLang = $this->userInfo['lang'];

				if(file_exists($path . 'language/' . $activeLang . '/' . $dir . '_lang.php')) {
					$langFilePath = $path;
					$this->lang->load($dir, $activeLang, FALSE, TRUE, $langFilePath);
				}

				$urlPluginHooks = array();
				# удалим патч для плагина
				$this->load->remove_package_path($path);
				$load_site = array();
			}
		}
	}

	# routing
	public function _remap($start_page, $args = array())
	{
		if($start_page == 'ajax')
			return $this->run_ajax($args);

		$this->Template->IsHome = false;
		# вывод главной
		if($start_page == 'index' and !$this->uri->uri_to_assoc(1))
		{
			# для главной только одна страница
			$this->urls = array('index');
			$this->Template->IsHome = true;
		}
		else
		{
			$this->urls = array('index', $start_page);
			foreach($args as $value) {
				$this->urls[] = $value;
			}

			# Нет аргументов - возможно запрос на короткую ссылку
			if(!$args && $this->activeShortLink)
			{
				if($nodeShortID = $this->CommonModel->getNodeIdFromShortLink($this->urls[1])){
					$this->urls = $this->CommonModel->loadParentsUrlsFromNodeID($nodeShortID);
				}
			}
		}

		$this->load->library('urlobjects');
		$this->load->library('image_lib');
		$this->load->library('page');

		# загрузка хуков в начале загрузки URL
		$this->loadHooksPlugin('preload');

		if(!$this->BreakRemap)
		{
			$content = $this->CommonModel->loadPagesFromURL($this->urls);
			$this->return_active_page($content, 0);

			# сегменты и страницы не совпадают. Ошибка 404
			if(count($this->urls) != count($this->loading)) {
				return $this->_render_not_found();
			}

			$this->urlobjects->load($this->loading);

			# запись head
			$this->Template->PAGE_TITLE = $this->urlobjects->title;
			$this->Template->PAGE_DESCRIPTION = $this->urlobjects->description;
			$this->Template->PAGE_KEYWORDS = $this->urlobjects->keywords;
			$this->content_template = $this->urlobjects->curTplContent;
			$this->page_template = $this->urlobjects->curTplPage;


			# настройка canonical. Если он не задан, то ищем автоматически и сразу обновляем его для объекта
			// если главная, то каноникал главный
			if($this->Template->IsHome) {
				$this->Template->PAGE_CANONICAL = APP_BASE_URL_NO_SLASH;
			} else {
				$realCanonical = '';
				$this->Template->PAGE_CANONICAL = $this->urlobjects->canonical;
				# если это оригинальный объект и текущая ссылка НЕ короткая, то обновим каноникал если он не совпадает.
				if($this->urlobjects->object('tree_type') == 'orig' && $this->currentUrl != $this->urlobjects->tree_short) {
					$realCanonical = $this->currentUrl;
					// если каноникал не совпадает с записаным, то обновляем
					if($realCanonical != $this->Template->PAGE_CANONICAL) {
						# обновим canonical
						$this->TreelibAdmModel->updateCanonicalOblect(0, $this->urlobjects->nodeID);
					}
					$this->Template->PAGE_CANONICAL = $realCanonical;
				}
				$this->Template->PAGE_CANONICAL = APP_BASE_URL . $this->Template->PAGE_CANONICAL;
			}

			# счет количества просмотров
			app_set_count_view($this->urlobjects->id);
		}

		if(!$this->BreakRender){
			$this->_render_content();
		}
	}

	private function return_active_page($contents, $parent = 0, $level = -1, $url = '')
	{
		$level++;

		if(is_array($contents) and isset($contents[$parent]))
		{
			foreach($contents[$parent] as $row)
			{
				if(isset($this->urls[$level]) and ($this->urls[$level] == $row['tree_url']) and ($parent == $row['tree_parent_id'])) {
					$url .= '/' . $row['tree_url'];
					$this->loading[$level] = $row;
					$this->loading[$level]['path_url'] = str_replace('/index', APP_BASE_URL_NO_SLASH, $url);
					$this->return_active_page($contents, $row['tree_id'], $level, $url);
				}
			}
		}
		return;
	}

	/*
	* загрузка хуков плагинов
	*
	* $key = preload, render_content_start, render_content_end, render_page_start, render_page_end, render_notfound
	*/
	private function loadHooksPlugin($key = 'def')
	{
		# загрузка хуков по URL
		$urlString = implode('/', $this->urls);

		foreach($this->hooksPlugin as $valueHook)
		{
			// если патч не указан, то пропускаем
			if(!isset($valueHook['path'])) continue;
			$path = $valueHook['path'];
			unset($valueHook['path']);
			foreach($valueHook as $urlHook => $vHook)
			{
				$keyHookUrl = '';

				# сначала проверка на множество URL
				$pos = strpos($urlHook, '*');
				if($pos !== FALSE and $pos !== 0){
					$keyHookUrl = substr($urlHook, 0, $pos - 1);
					if(strpos($urlString, $keyHookUrl) === 0 and isset($vHook[$key]))
						$this->_helpLoadHooksPlugin($path, $vHook[$key]['model'], $vHook[$key]['method']);
				}
				elseif($pos === 0)
				{
					if(isset($vHook[$key]))
						$this->_helpLoadHooksPlugin($path, $vHook[$key]['model'], $vHook[$key]['method']);
				}
				else
				{
					if($urlHook == $urlString and isset($vHook[$key]))
						$this->_helpLoadHooksPlugin($path, $vHook[$key]['model'], $vHook[$key]['method']);
				}
			}
		}

	}

	/* впомогательная для loadHooksPlugin */
	private function _helpLoadHooksPlugin($path, $model, $method)
	{
		$path = APP_PLUGINS_DIR_PATH . $path . '/';
		$this->load->add_package_path($path);
		$this->load->model($model);
		$this->load->remove_package_path($path);
		$this->$model->$method($this);
	}

	/*
	* функция срабатывает при запросе ajax
	*/
	private function run_ajax($args)
	{
		# блокировка, если не Ajax
		/*if(!is_ajax()) {
			exit('No direct script access allowed');
		}*/

		$post = $this->input->post();
		$get = $this->input->get();
		$urlArg1 = '';
		$urlArg2 = '';
		# если параметры
		if($args)
		{
			switch($args[0])
			{
				case 'plugin':
					if(isset($args[1]))
					{
						$patch = APP_PLUGINS_DIR_PATH . $args[1] . '/ajax/Ajax.php';
						if(file_exists($patch))
						{
							if(isset($args[2])) {
								$urlArg1 = $args[2];
							}

							if(isset($args[3])) {
								$urlArg2 = $args[3];
							}

							require_once($patch);
							$Ajax = new Ajax($post, $get);

							if(method_exists($Ajax, 'ajaxRun')) {
								$Ajax->ajaxRun();
							} else {
								if($urlArg1 && method_exists($Ajax, $urlArg1)) {
									$Ajax->$urlArg1();
								} else {
									exit('Ajax: Method is not exists');
								}
							}
						}
					}
					return;
					break;
			}

		}
	}

	/*
	* Сжатие файлов
	*/
	private function _compressStyles()
	{
		# если сжатие не включено, отдаем как есть
		if(app_get_option('compress_style', 'site', 'no') === 'no') {
			$this->Template->TOP_ASSETS_ARRAY = $this->headAssets;
			$this->Template->BOTTOM_ASSETS_ARRAY = $this->bodyAssets;
		} else {
			$libName = 'CompressStyles';
			if(file_exists(APPPATH . 'libraries/' . $libName . '.php')) {
				require_once(APPPATH . 'libraries/' . $libName . '.php');
				$CompressClass = new $libName;
				$this->Template->TOP_ASSETS_ARRAY = $CompressClass->compressHeadAssets($this->headAssets);
				$this->Template->BOTTOM_ASSETS_ARRAY = $CompressClass->compressBodyAssets($this->headAssets);
			} else {
				$this->Template->TOP_ASSETS_ARRAY = $this->headAssets;
				$this->Template->BOTTOM_ASSETS_ARRAY = $this->bodyAssets;
			}
		}

	}

	public function _render_content()
	{
		# проверка на наличие шаблона
		if(!file_exists(APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE)) {
			exit('Unable to load site template!');
		}

		$this->Template->CONTENT = '';

		# загрузка хуков в начале генерации контента
		$this->loadHooksPlugin('render_content_start');

		if(!APP_SITE_TEMPLATE or !$this->content_template){
			$this->Template->CONTENT = 'Template error';
		}
		else
		{
			# загрузим главную МОДЕЛЬ контента
			$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Models/Contents/ContentsModel.php';
			if(file_exists($pathFile)) {
				require_once($pathFile);
				$this->ContentsModel = new ContentsModel();
			}

			# загрузим МОДЕЛЬ для файла контента. Например для файла Catalog-products будет поиск модели CatalogProducts
			$className = str_replace(array('-', '_'), ' ', $this->content_template);
			$className = str_replace(' ', '', ucwords($className) . 'Model');
			$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Models/Contents/' . $className . '.php';
			if(file_exists($pathFile)) {
				require_once($pathFile);
				$this->$className = new $className();
			}

			# загрузим главную БИБЛИОТЕКУ контента
			$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Libraries/Contents/ContentsLib.php';
			if(file_exists($pathFile)) {
				require_once($pathFile);
				$this->ContentsLib = new ContentsLib();
			}

			# загрузим БИБЛИОТЕКУ для файла контента. Например для файла Catalog-products будет поиск файла CatalogProductsLib
			$className = str_replace(array('-', '_'), ' ', $this->content_template);
			$className = str_replace(' ', '', ucwords($className) . 'Lib');
			$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Libraries/Contents/' . $className . '.php';
			if(file_exists($pathFile)) {
				require_once($pathFile);
				$this->$className = new $className();
			}

			# совместимость с версиями ниже 7.3 - dataTemplate загружаем в $this->Template
			if($this->dataTemplate) {
				Template::setVars($this->dataTemplate);
			}
		}

		$CONTENT = $this->load->view($this->viewsTemplatePath . 'contents/' . $this->content_template, Template::getVars(), true);

		# загрузка хуков в конце генерации контента
		$this->loadHooksPlugin('render_content_end');
		$this->_render_template($CONTENT);
	}


	public function _render_template($CONTENT = '')
	{
		$this->Template->PAGE_TITLE ?: $this->Template->PAGE_TITLE = app_get_option('site_title_default', 'site', '');
		$this->Template->PAGE_DESCRIPTION ?: $this->Template->PAGE_DESCRIPTION = app_get_option('site_title_default', 'site', '');
		$this->Template->PAGE_KEYWORDS ?: $this->Template->PAGE_KEYWORDS = app_get_option('site_title_default', 'site', '');
		$this->Template->PAGE_CANONICAL ?: $this->Template->PAGE_CANONICAL = app_get_option('site_title_default', 'site', '');

		$this->Template->TEMPLATE_ASSETS_URL = APP_BASE_URL . 'application/views/templates/' . APP_SITE_TEMPLATE . '/assets/';
		$this->Template->TEMPLATE_JS_URL = $this->Template->TEMPLATE_ASSETS_URL . 'js/';
		$this->Template->TEMPLATE_CSS_URL = $this->Template->TEMPLATE_ASSETS_URL . 'css/';
		$this->Template->TEMPLATE_IMG_URL = $this->Template->TEMPLATE_ASSETS_URL . 'img/';

		# загрузка хуков в начале генерации страницы
		$this->loadHooksPlugin('render_page_start');

		# нет шаблона сайта или шаблона страницы
		if(!APP_SITE_TEMPLATE or !$this->page_template) {
			header('HTTP/1.0 404 Not Found');
			exit('Site template or page template not found');
		}

		# загрузим главную МОДЕЛЬ шаблона
		$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Models/Templates/TemplatesModel.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->TemplatesModel = new TemplatesModel();
		}

		# загрузим МОДЕЛЬ для файла шаблона. Например для файла Catalog-products будет поиск модели CatalogProducts
		$className = str_replace(array('-', '_'), ' ', $this->page_template);
		$className = str_replace(' ', '', ucwords($className) . 'Model');
		$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Models/Templates/' . $className . '.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->$className = new $className();
		}

		# загрузим главную БИБЛИОТЕКУ шаблона
		$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Libraries/Templates/TemplatesLib.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->TemplatesLib = new TemplatesLib();
		}

		# загрузим БИБЛИОТЕКУ для файла шаблона. Например для файла Catalog-products будет поиск файла CatalogProductsLib
		$className = str_replace(array('-', '_'), ' ', $this->page_template);
		$className = str_replace(' ', '', ucwords($className) . 'Lib');
		$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Libraries/Templates/' . $className . '.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->$className = new $className();
		}

		$this->_compressStyles();

		# совместимость с версиями ниже 7.3 - dataTemplate загружаем в $this->Template
		if($this->dataTemplate) {
			Template::setVars($this->dataTemplate);
			$this->dataTemplate = array();
		}

		$this->Template->CONTENT = $CONTENT;
		$CONTENT = $this->load->view('templates/' . APP_SITE_TEMPLATE . '/' . $this->page_template, Template::getVars(), TRUE);
		$this->Template->CONTENT = $CONTENT;
		unset($CONTENT);

		# загрузка хуков в конце генерации страницы
		$this->loadHooksPlugin('render_page_end');

		$this->load->view('application', array('CONTENT' => $this->Template->CONTENT));
	}

	public function _render_not_found()
	{
		$this->Template->PAGE_TITLE ?: $this->Template->PAGE_TITLE = '404 - page not found';
		$this->Template->PAGE_DESCRIPTION ?: $this->Template->PAGE_DESCRIPTION = '404 - page not found';
		$this->Template->PAGE_KEYWORDS ?: $this->Template->PAGE_KEYWORDS = '404 - page not found';
		$this->Template->PAGE_CANONICAL ?: $this->Template->PAGE_CANONICAL = '';

		$this->Template->TEMPLATE_ASSETS_URL = APP_BASE_URL . 'application/views/templates/' . APP_SITE_TEMPLATE . '/assets/';
		$this->Template->TEMPLATE_JS_URL = $this->Template->TEMPLATE_ASSETS_URL . 'js/';
		$this->Template->TEMPLATE_CSS_URL = $this->Template->TEMPLATE_ASSETS_URL . 'css/';
		$this->Template->TEMPLATE_IMG_URL = $this->Template->TEMPLATE_ASSETS_URL . 'img/';

		# загрузка хуков генерации 404 страницы
		$this->loadHooksPlugin('render_notfound');

		header('HTTP/1.0 404 Not Found');
		if(!APP_SITE_TEMPLATE or !APP_SITE_404_TEMPLATE) {
			echo 'Template not found error';
			exit('Site template or 404 temlate not found');
		}

		# загрузим главную МОДЕЛЬ контента
		$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Models/Contents/ContentsModel.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->ContentsModel = new ContentsModel();
		}

		# загрузим главную МОДЕЛЬ шаблона
		$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Models/Templates/TemplatesModel.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->TemplatesModel = new TemplatesModel();
		}

		# загрузим МОДЕЛЬ для файла шаблона. Например для файла Catalog-products будет поиск модели CatalogProducts
		$className = str_replace(array('-', '_'), ' ', APP_SITE_404_TEMPLATE);
		$className = str_replace(' ', '', ucwords($className) . 'Model');
		$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Models/Templates/' . $className . '.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->$className = new $className();
		}

		# загрузим главную БИБЛИОТЕКУ контента
		$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Libraries/Contents/ContentsLib.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->ContentsLib = new ContentsLib();
		}

		# загрузим главную БИБЛИОТЕКУ шаблона
		$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Libraries/Templates/TemplatesLib.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->TemplatesLib = new TemplatesLib();
		}


		# загрузим БИБЛИОТЕКУ для файла шаблона. Например для файла Catalog-products будет поиск файла CatalogProductsLib
		$className = str_replace(array('-', '_'), ' ', APP_SITE_404_TEMPLATE);
		$className = str_replace(' ', '', ucwords($className) . 'Lib');
		$pathFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/Libraries/Templates/' . $className . '.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->$className = new $className();
		}

		$this->_compressStyles();

		# совместимость с версиями ниже 7.3 - dataTemplate загружаем в $this->Template
		if($this->dataTemplate) {
			Template::setVars($this->dataTemplate);
			$this->dataTemplate = array();
		}

		$CONTENT = $this->load->view($this->viewsTemplatePath . APP_SITE_404_TEMPLATE, Template::getVars(), TRUE);
		$this->load->view('application', array('CONTENT' => $CONTENT));
	}


}
