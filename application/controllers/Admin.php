<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
	* Контроллер админ-панели
	*
	* version 14.52
	* UPD 2017-11-30
	* change log
	*
	* 14.51 UPD 2017-11-27
	* переименовано plugins_assets_top to pluginsAssetsTop
	* переименовано plugins_assets_bottom to pluginsAssetsBottom
	* добавлено
	*	$this->data['ADMIN_ASSETS_URL'] = APP_BASE_URL . 'application/views/admin/templates/' . APP_CMS_TEMPLATE . '/assets/';
	*	$this->data['ADMIN_JS_URL'] = $this->data['ADMIN_ASSETS_URL'] . 'js/';
	*	$this->data['ADMIN_CSS_URL'] = $this->data['ADMIN_ASSETS_URL'] . 'css/';
	*	$this->data['ADMIN_IMG_URL'] = $this->data['ADMIN_ASSETS_URL'] . 'img/';
	* переделана логика работы pluginsAssetsTop и pluginsAssetsBottom
	*
	* 14.52 UPD 2017-11-30
	* Добавлена загрузка языковых фалов у плагинов.
	* 	Языковые файлы плагинов хранятся так:
	* 		PLUGIN_FOLDER/language/english/НАЗВАНИЕ_ПАПКИ_ПЛАГИНА_lang.php (английски)
	*		PLUGIN_FOLDER/language/russian/НАЗВАНИЕ_ПАПКИ_ПЛАГИНА_lang.php (русский)
	*
	* 14.6 UPD 2018-02-15
	* Добавлена возможность редактирования .htaccess
	* Удалена настройка ПАРАМЕТРЫ ЗАГРУЗКИ
	*
	* 14.7 UPD 2018-03-29
	* обработка запроса на сортровку
	*
	* UPD 2018-04-16
	* version 15
	* введены доступы
	*
	* UPD 2018-04-19
	* version 15.1
	* Исправлены ошибки
	*
	* UPD 2018-04-26
	* version 15.2
	* Правки в работе с пользователями. Доработки в правах доступа
	*
	* UPD 2018-05-01
	* version 15.3
	* Исправлен отказ в доступе в настройках плагина
	*
	* UPD 2018-05-24
	* version 15.5
	* Добавлены публичные методы _render_not_found() и _render_content()
	* not_found() и to_view() в последствии будут удалены
	*
	* UPD 2018-06-22
	* version 15.6
	* Добавлен метод page_get_field_objects(). Отлавливает работу с ДАТА-полями. Теперь неиспользуемые DATA-поля можно удалить из таблицы объектов
	*
	* UPD 2018-08-02
	* version 16
	* Изменена схема подключения плагинов. Контент должен храниться в переменной $this->pageContent. Title и Description = $this->pageContentTitle и $this->pageContentDescription
	* Сообщения об ошбках и успехах = $this->pageErrorMessage и $this->pageCompliteMessage
	*
	* UPD 2018-08-08
	* version 16.1
	* Ошибка в доступах к настройке сайта
	*
	* UPD 2018-08-29
	* version 16.2
	* Сделана индексация сайта. Во время индексации проводится обновление полей AXIS в структуре и canonical в объектах
	*
	* UPD 2018-08-30
	* version 16.3
	* Орагнизовано подключение языковых файлов для javascript
	* Пример расположения для плагина admin-site-tree для английского: plugins/admin-site-tree/assets/lang/english/admin-site-tree-lang.js
	* Пример расположения для плагина admin-site-tree для русского: plugins/admin-site-tree/assets/lang/russian/admin-site-tree-lang.js
	*
	* UPD 2018-09-04
	* version 16.4
	* мелкие правки
	*
	* UPD 2018-09-10
	* version 16.5
	* переделаны доступы accesses
	*
	* UPD 2018-09-12
	* version 16.6
	* Переделан Ajax сортировщик списков
	*
	* UPD 2018-09-14
	* version 16.61
	* Добавлена метка времени при сбросе кэша
	*
	* UPD 2018-09-19
	* version 16.62
	* Правки
	*
	* UPD 2018-10-09
	* version 16.7
	* Индексация сайта сделана через AJAX (page_index_site())
	*
*/

class Admin extends CI_Controller {

	// установленные плагины
	private $installPlugins;

	// Скрипты и стили у плагинов
	private $pluginsAssetsTop, $pluginsAssetsBottom, $pluginAssetsLang;

	// Патч до файла Robots
	private $pathRobots = '';

	// Патч до файла .htaccess
	private $pathHtaccess = '';

	// системное меню
	private $systemMenu;

	// доступы
	private $accesses;

	// Флаг, что есть обновления
	private $systemUpdate;

	// Сссылка на страницу настройки  плагина
	private $settingPluginLink;

	// Информация о Юзере
	public $userInfo;

	// контент страницы
	public $pageContent;

	// title страницы
	public $pageContentTitle;

	// description страницы
	public $pageContentDescription;

	// сообщение об ошибке
	public $pageErrorMessage;

	// сообщение об успехе
	public $pageCompliteMessage;

	// массив виджетов
	private $WIDJETS;

	public function __construct()
	{
		parent::__construct();
		define("APP_SITE_TEMPLATE", app_get_option('site_template', 'site', 'default'));
		define("APP_SITE_404_TEMPLATE", app_get_option('not_found_template', 'site', ''));
		define("APP_CMS_TEMPLATE", app_get_option('admin_template', 'general', 'default'));
		define("APP_SITE_TEMPLATE_URL", APP_BASE_URL . 'application/views/templates/' . APP_SITE_TEMPLATE);

		$this->LoginAdmModel->userData();
		$this->pathRobots = APP_BASE_PATH . 'robots.txt';
		$this->pathHtaccess = APP_BASE_PATH . '.htaccess';

		$this->systemUpdate = FALSE;
		if(file_exists(APP_BASE_PATH . '_update/updateInfo.php')) {
			$this->systemUpdate = TRUE;
		}

		# если запрещен доступ к панели, то на регистрацию
		if(!$this->userInfo = $this->LoginAdmModel->checkAccessPanel()){
			redirect(APP_BASE_URL . 'login', 'refresh');
			exit('Access denied');
		}

		$this->WIDJETS = array();
		$this->pluginsAssetsTop = array();
		$this->pluginsAssetsBottom = array();
		$this->pluginAssetsLang = array(APP_BASE_URL . 'application/views/admin/templates/' . APP_CMS_TEMPLATE . '/assets/lang/' . $this->userInfo['lang'] . '/panel_lang_script.js');

		$this->pageContent =
		$this->pageContentTitle =
		$this->pageContentDescription =
		$this->pageErrorMessage =
		$this->pageCompliteMessage = '';

		$this->data['content'] = '';

		$this->lang->load('panel', $this->userInfo['lang']);

		$this->systemMenu = array(
			'panel_setting' => app_lang('MENU_SETTING_SYSTEM'),
			'front_setting' => app_lang('MENU_SETTING_SITE'),
			'plugins_install' => app_lang('MENU_SETTING_PLUGINS'),
			'types_data' => app_lang('MENU_SETTING_TYPES_DATA'),
			'types_objects' => app_lang('MENU_SETTING_OBJECTS'),
			'users' => app_lang('MENU_SETTING_USERS')
		);

		# Отлавливает POST для сохранения опций. Ключ - option_update
		# Ключ - option_update[OPTION_GROUP][OPTION_KEY]['value'] - значение опции
		# Ключ - option_update[OPTION_GROUP][OPTION_KEY]['descr'] - описание опции
		if($optionUpdate = $this->input->post('option_update'))
		{
			foreach($optionUpdate as $group => $VALUE)
			{
				foreach($VALUE as $optionKey => $option)
				{
					if(is_array($option) and isset($option['value']))
					{
						if(is_array($option['value'])) {
							$option['value'] = implode(',', $option['value']);
						}

						if(isset($option['descr'])) {
							app_add_option($optionKey, $option['value'], $group, $option['descr']);
						} else {
							app_add_option($optionKey, $option['value'], $group, FALSE);
						}
					}
					else
					{
						if(is_array($option)) {
							$option = implode(',', $option);
						}

						app_add_option($optionKey, $option, $group, FALSE);
					}
				}
			}
			$this->pageCompliteMessage = app_lang('UPDATE_SETTING_COMPLITE');
		}

		# POST на обновление robots.txt
		if($updateRobots = $this->input->post('robots_file_content')) {
			$fp = fopen($this->pathRobots, 'w');
			fwrite($fp, $updateRobots);
			fclose($fp);
			$this->pageCompliteMessage = app_lang('UPDATE_SETTING_COMPLITE');
		}

		# POST на обновление .htacceess
		if($updateHteaccess = $this->input->post('htaccess_file_content')) {
			$fp = fopen($this->pathHtaccess, 'w');
			fwrite($fp, $updateHteaccess);
			fclose($fp);
			$this->pageCompliteMessage = app_lang('UPDATE_SETTING_COMPLITE');
		}

		# POST на подключени плагинов
		if($post = $this->input->post('plugin_install'))
		{
			$pluginsDirMap = app_get_all_plugins();
			foreach($post as $key => $val)
			{
				if(!isset($pluginsDirMap[$key])) continue;
				if($val == 'install')
				{
					$values = array(
						'name' => $pluginsDirMap[$key]['info']['name'],
						'folder' => $key,
						'version' => $pluginsDirMap[$key]['info']['version'],
						'author' => $pluginsDirMap[$key]['info']['author'],
						'group' => '',
						);
					plugin_install($values);
				}
				elseif($val == 'uninstall')
				{
					plugin_uninstall($key);
				}
			}
			$this->pageCompliteMessage = app_lang('UPDATE_SETTING_COMPLITE');
		}

		# POST на сохранение разрешений
		if($result = $this->input->post('accesses')) {
			app_add_option('panel_access', $result, 'general');
			$this->pageCompliteMessage = app_lang('INFO_USERS_ACCESS_COMPLITE_UPDATE');
		}


		# автозагрузка плагинов
		// установленные плагины
		$this->installPlugins = plugin_load();

		# Сформируем доступы. Root User всегда в разрешениях
		$saveAccess = app_get_option('panel_access', 'general', array());
		foreach($this->systemMenu as $key => $value) {
			$this->accesses[$key]['name'] = $this->systemMenu[$key];
			if(isset($saveAccess[$key])) {
				if(in_array(1, $saveAccess[$key])) {
					$this->accesses[$key]['access'] = $saveAccess[$key];
				} else {
					$this->accesses[$key]['access'] = $saveAccess[$key];
					$this->accesses[$key]['access'][] = 1;
				}
			} else {
				$this->accesses[$key]['access'] = array(1);
			}
		}

		# теперь дооступы к установленным плагинам. Root User всегда в разрешениях
		foreach($this->installPlugins as $key => $value) {
			$this->accesses[$key]['name'] = $value['plugins_name'];
			$this->accesses[$key]['access'] = array();
			if(isset($saveAccess[$key])) {
				if(in_array(1, $saveAccess[$key])) {
					$this->accesses[$key]['access'] = $saveAccess[$key];
				} else {
					$this->accesses[$key]['access'] = $saveAccess[$key];
					$this->accesses[$key]['access'][] = 1;
				}
			} else {
				$this->accesses[$key]['access'] = array(1);
			}
		}

		$this->LoginAdmModel->setAccesses($this->accesses);

		// меню для плагинов
		$this->data['left_nav_menu'] = array();
		$menuSegment = $this->uri->segment(2);
		if($plugins = $this->installPlugins)
		{
			$dirs = array_keys($plugins);
			foreach($dirs as $dir)
			{
				$admin_menu = FALSE;
				$path = APP_PLUGINS_DIR_PATH . $dir . '/';
				if(!file_exists($path . 'autoload.php')) continue;
				# установим патч для плагина
				$this->load->add_package_path($path);

				# загрузка языков плагинов
				$activeLang = app_get_option('admin_lang', 'general', 'english');
				if(isset($this->userInfo['lang']) and file_exists($path . 'language/' . $this->userInfo['lang'])) {
					$activeLang = $this->userInfo['lang'];
				}

				# подключим языковые файлы плагина
				// для php
				if(file_exists($path . 'language/' . $activeLang . '/' . $dir . '_lang.php')) {
					$langFilePath = $path;
					$this->lang->load($dir, $activeLang, FALSE, TRUE, $langFilePath);
				}
				// для javascript
				if(file_exists($path . 'assets/lang/' . $this->userInfo['lang'] . '/' . $dir . '-lang.js')) {
					$this->pluginAssetsLang[] = APP_BASE_URL . 'application/plugins/' .$dir . '/assets/lang/' . $this->userInfo['lang'] . '/' . $dir . '-lang.js';
				} else if(file_exists($path . 'assets/lang/english/' . $dir . '-lang.js')) {
					$this->pluginAssetsLang[] = APP_BASE_URL . 'application/plugins/' .$dir . '/assets/lang/english/' . $dir . '-lang.js';
				}

				require($path . 'autoload.php');

				$this->installPlugins[$dir]['plugins_version'] = $info['version'];
				$this->installPlugins[$dir]['plugins_name'] = $info['name'];
				$this->installPlugins[$dir]['plugins_description'] = $info['descr'];
				$this->installPlugins[$dir]['options'] = array();

				# создадим меню с учетом разрешений
				$activeMenu = FALSE;
				if($admin_menu) {
					if(in_array($this->userInfo['group_type'], $this->accesses[$dir]['access'])) {
						$menuSegment == $dir ? $activeMenu = TRUE : $activeMenu = FALSE;
						$this->data['left_nav_menu'][$this->installPlugins[$dir]['plugins_name']]['link'] = $dir;
						$this->data['left_nav_menu'][$this->installPlugins[$dir]['plugins_name']]['active'] = $activeMenu;
					}
				}

				# подключение моделей
				if(isset($load_admin['model']) and $load_admin['model'])
					$this->load->model($load_admin['model']);

				# подключение хелперов
				if(isset($load_admin['helper']) and $load_admin['helper'])
					$this->load->helper($load_admin['helper']);

				# опции плагина
				if(isset($options) and $options){
					$this->installPlugins[$dir]['options'] = $options;
					// значения для опций плагина
					foreach($this->installPlugins[$dir]['options'] as $key => &$value){
						$value['value'] = app_get_option($key, $dir, $value['default']);
					}
					unset($value);
				}

				# подключение js  и css для всей админ-панели
				if(isset($load_admin['assets']['admin']))
				{
					if(isset($load_admin['assets']['admin']['top']) and $load_admin['assets']['admin']['top'])
					{
						$this->pluginsAssetsTop = array_merge($load_admin['assets']['admin']['top'], $this->pluginsAssetsTop);
					}

					if(isset($load_admin['assets']['admin']['bottom']) and $load_admin['assets']['admin']['bottom'])
					{
						$this->pluginsAssetsBottom = array_merge($load_admin['assets']['admin']['bottom'], $this->pluginsAssetsBottom);
					}
				}


				# подключение js и css для страниц плагина
				$this->installPlugins[$dir]['assets']['top'] = array();
				if(isset($load_admin['assets']['plugin']['top']) and $load_admin['assets']['plugin']['top'])
				{
					$this->installPlugins[$dir]['assets']['top'] = $load_admin['assets']['plugin']['top'];
				}

				$this->installPlugins[$dir]['assets']['bottom'] = array();
				if(isset($load_admin['assets']['plugin']['bottom']) and $load_admin['assets']['plugin']['bottom'])
				{
					$this->installPlugins[$dir]['assets']['bottom'] = $load_admin['assets']['plugin']['bottom'];
				}

				# загрузка виджетов
				if(isset($load_admin['widjet']) and $load_admin['widjet']) {
					$this->WIDJETS[$dir]['path'] = $path . 'widjets/';
					$this->WIDJETS[$dir]['class'] = $load_admin['widjet'];
					$this->WIDJETS[$dir]['content'] = '';
				}

				# удалим патч для плагина
				$this->load->remove_package_path($path);
			}
		}
	}

	public function _remap($method, $args = array())
	{
		$page = 'page_' . $method;

		if(method_exists($this, $page))
			return $this->$page($args);
		else
			return $this->other_methods($method, $args);
	}

	private function page_index()
	{
		$data = array();
		$this->pageContentTitle = app_lang('H1_ADMIN_PANEL');

		$data['update_panel'] = '';

		# POST на удаление кэша
		if($post = $this->input->post('delete_cache')) {
			app_delete_cash();
			$this->pageCompliteMessage = app_lang('INFO_DEL_CACHE_DELETE_COMPLITE');
			app_add_option('update_cash_time', time(), 'general');
		}

		# POST на создание sitemap
		if($post = $this->input->post('create_sitemap')) {
			//app_delete_cash();
			$this->load->model('SitemapAdmModel');
			$this->SitemapAdmModel->createSitemapFile();
			$this->pageCompliteMessage = app_lang('INFO_SITEMAP_CREATE_COMPLITE');
		}

		if($this->systemUpdate) {
			$data['update_panel'] = app_lang('UPDATE_INFO');
		}

		$data['CMSVERSION'] = CMSVERSION;
		$data['userLang'] = $this->userInfo['lang'];

		$data['widjets'] = array();
		foreach($this->WIDJETS as $key => &$value) {
			$pathFile = $value['path'] . $value['class'] . '.php';
			if(file_exists($pathFile)) {
				require($pathFile);
				$classWidjet = new $value['class'];
				// права доступа
				$classWidjet->checkAccess($key);
				if($content = $classWidjet->build()) {
					$data['widjets'][] = $content;
				}
			}
		}

		# информация конфигурации
		$data['confPhpVersion'] = PHP_VERSION;
		$data['confMySQLVersion'] = $this->db->conn_id->server_info;

		$this->pageContent = $this->load->view('admin/index', $data, true);
		return $this->_render_content();
	}

	# настройка админ-панели
	private function page_panel_setting()
	{
		# доступ
		if(!$this->LoginAdmModel->checkAccessUser('panel_setting')) {
			return $this->_render_not_found();
		}

		$data = array();

		$this->pageContentTitle = $this->lang->line('title_panel_setting');
		$this->pageContentDescription = $this->lang->line('descr_panel_setting');

		$data['h1'] = $this->lang->line('h1_panel_setting');
		$data['robots_txt'] = '';
		$data['htaccess_txt'] = '';
		if(file_exists($this->pathRobots))
			$data['robots_txt'] = file_get_contents($this->pathRobots);

		if(file_exists($this->pathHtaccess))
			$data['htaccess_txt'] = file_get_contents($this->pathHtaccess);

		$data['SERGCMS'] = &$this;

		$this->pageContent = $this->load->view('admin/panel-setting', $data, true);
		return $this->_render_content();
	}

	# настройка front-end сайта
	private function page_front_setting()
	{
		# доступ
		if(!$this->LoginAdmModel->checkAccessUser('front_setting')) {
			return $this->_render_not_found();
		}

		$data = array();

		$this->pageContentTitle = $this->lang->line('title_site_setting');
		$this->pageContentDescription = $this->lang->line('descr_site_setting');

		// все шаблоны
		$templates_array = app_get_site_templates();

		$data['site_template_options'] = array();
		foreach($templates_array as $key => $value) {
			$data['site_template_options'][$key] = $value['info']['name'];
		}

		$data['SERGCMS'] = &$this;

		$this->pageContent = $this->load->view('admin/front-setting', $data, true);
		return $this->_render_content();
	}

	# подключение плагинов
	private function page_plugins_install($args = array())
	{
		# доступ
		if(!$this->LoginAdmModel->checkAccessUser('plugins_install')) {
			return $this->_render_not_found();
		}

		$data = array();

		$this->pageContentTitle = $this->lang->line('title_plugins_install');
		$this->pageContentDescription = $this->lang->line('descr_plugins_install');

		$dir_map = app_get_all_plugins();

		// нет папки или не нашлись файлы info.php
		if(!$dir_map){
			$this->pageContent = '';
			$this->data['infoerror'] = $this->lang->line('error_no_folder_plugins');
			return $this->_render_content();
		}

		$data['plugins_array'] = $dir_map;
		$data['plugins_install'] = $this->installPlugins;

		$this->pageContent = $this->load->view('admin/plugins-install', $data, true);
		return $this->_render_content();
	}

	# настройка типов данных
	private function page_types_data($args = array())
	{
		# доступ
		if(!$this->LoginAdmModel->checkAccessUser('types_data')) {
			return $this->_render_not_found();
		}

		$data = array();
		$data['message'] = '';
		$this->pageContentTitle = $this->lang->line('h1_types_data');
		$this->pageContentDescription = $this->lang->line('h1_types_data');

		# POST для создания нового типа данных
		if($new_type_data = $this->input->post('new_type'))
		{
			$ins = $this->CommonModel->createDataType($new_type_data);
			if($ins)
				$this->data['infocomplite'] = $this->lang->line('INFO_DT_TYPE_NEW_COMPLITE');
			else
				$this->data['infoerror'] = $this->lang->line('INFO_DT_TYPE_IS_EXISTS');
		}
		# POST для редактирования типов данных
		if($edit_type = $this->input->post('edit_type') and !$this->input->post('delete_type'))
		{
			$upd = false;
			foreach($edit_type as $key => $value) {
				$upd = $this->CommonModel->updateDataType($value, $key);
			}

			if($upd) {
				$this->pageCompliteMessage = $this->lang->line('INFO_DT_TYPE_UPDATE_COMPLITE');
			} else {
				$this->pageErrorMessage = $this->lang->line('INFO_DT_TYPE_UPDATE_ERROR');
			}
		}

		# POST для удаления типов данных
		if($delete_type = $this->input->post('delete_type'))
		{
			$del = false;
			foreach($delete_type as $type_id) {
				if($type_id === '1') {
					continue;
				}

				$del = $this->CommonModel->deleteDataType($type_id);
			}

			if($del) {
				$this->pageCompliteMessage = $this->lang->line('INFO_DT_TYPE_DELETE_COMPLITE');
			} else {
				$this->pageErrorMessage = $this->lang->line('INFO_DT_TYPE_DELETE_ERROR');
			}
		}

		# POST для создания нового поля для типа данных
		if($new_data_field = $this->input->post('new_field'))
		{
			$field_id = $this->CommonModel->createDataTypeField($new_data_field);
			if(isset($new_data_field['fields_data']) and $field_id) {
				$this->CommonModel->createData2Data($new_data_field['fields_data'], $field_id);
			}

			if($field_id) {
				$this->pageCompliteMessage = $this->lang->line('INFO_DTF_CREATE_COMPLITE');
			} else {
				$this->pageErrorMessage = $this->lang->line('INFO_DTF_CREATE_ERROR');
			}
		}

		# POST удаление полей типов данных
		if($delete_fields = $this->input->post('delete_fields')) {
			/*$data['message'] = 'будет удален тип';
			//$res = false;
			foreach($del_type as $type_id)
			{
				$res = $this->CommonModel->deleteDataType($type_id);
			}*/
		}

		# POST на обновление полей
		if($update_data_field =$this->input->post('edit_fields')) {
			foreach($update_data_field as $key_field => $val_field) {
				$upd_field = $this->CommonModel->updateDataTypeField($val_field, $key_field);
			}

			$this->pageCompliteMessage = $this->lang->line('INFO_DTF_UPDATE_COMPLITE');
		}

		# POST на подключение полей
		if($connect_field = $this->input->post('connect_to')) {
			foreach($connect_field as $key => $value) {
				$this->CommonModel->createData2Data($key, $value);
			}
		}

		# POST на отключение полей
		if($disconnect_field = $this->input->post('disconnect_to')) {
			foreach($disconnect_field as $key => $value) {
				$this->CommonModel->deleteData2Data($key, $value);
			}
		}


		$data['types_array'] = $this->CommonModel->getAllDataTypesFull();

		# есть параметры. Указан id типа данных
		if($args)
		{
			$data['type_fields'] = $args[0];
			if(!isset($data['types_array'][$data['type_fields']])) {
				$this->pageContent = '';
				$this->pageErrorMessage = $this->lang->line('INFO_DT_TYPE_NOT_FOUND');
				return $this->_render_content();
			}

			$data['type_info'] = $data['types_array'][$data['type_fields']];
			$data['fields_array'] = $this->CommonModel->getAllDataTypesFields();

			$this->pageContent = $this->load->view('admin/types-data-fields', $data, true);
		}
		else
		{
			$this->pageContent = $this->load->view('admin/types-data', $data, true);
		}
		return $this->_render_content();
	}

	# настройка типов объектов
	private function page_types_objects($args = array())
	{
		# доступ
		if(!$this->LoginAdmModel->checkAccessUser('types_objects')) {
			return $this->_render_not_found();
		}

		$data = array();
		$data['message'] = '';
		$this->pageContentTitle = $this->lang->line('h1_types_data');
		$this->pageContentDescription = $this->lang->line('h1_types_data');

		# update data type
		if($types_objects = $this->input->post('type_objects'))
		{
			foreach($types_objects as $key => $value)
				$this->CommonModel->updateObjType($key, $value);

			$this->pageCompliteMessage = $this->lang->line('UPDATE_SETTING_COMPLITE');
		}

		# add data type
		if($types_objects_new = $this->input->post('type_objects_new'))
		{
			foreach($types_objects_new as $key => $value)
				$this->CommonModel->createObjType($key, $value);

			$this->pageCompliteMessage = $this->lang->line('INFO_TO_CREATE_COMPLITE');
		}

		$data['all_types'] = $this->CommonModel->getAllObjTypes();

		$this->pageContent = $this->load->view('admin/types-object', $data, true);
		return $this->_render_content();
	}

	# настройка пользователей
	private function page_users($args = array())
	{
		# доступ
		if(!$this->LoginAdmModel->checkAccessUser('users')) {
			return $this->_render_not_found();
		}

		$data = array();

		$this->pageContentTitle = $this->lang->line('TITLE_USERS_LIST');
		$this->pageContentDescription = $this->lang->line('TITLE_USERS_LIST');

		# insert user
		if($usernew = $this->input->post('usernew'))
		{
			$this->LoginAdmModel->insertUser($usernew) ?
			$this->pageCompliteMessage = $this->lang->line('INFO_USERS_CREATE_COMPLITE') :
			$this->pageErrorMessage = $this->lang->line('INFO_USERS_CREATE_ERROR');
		}

		# создание новой группы пользователей
		if($createUserGroup = $this->input->post('create_user_group'))
		{
			$this->LoginAdmModel->createUsersGroup($createUserGroup) ?
			$this->pageCompliteMessage = $this->lang->line('INFO_USERS_GROUP_CREATE_COMPLITE') :
			$this->pageErrorMessage = $this->lang->line('INFO_USERS_GROUP_CREATE_ERROR');
		}

		# обновление группы пользователей
		if($updateUserGroup = $this->input->post('update_user_group'))
		{
			$upd = false;
			// если submit на удаление
			if($deleteGroup = $this->input->post('delete_user_group')) {
				foreach($deleteGroup as $key => $value){
					$upd = $this->LoginAdmModel->deleteUsersGroup($key);
				}
			} else {
				foreach($updateUserGroup as $key => $value){
					$upd = $this->LoginAdmModel->updateUsersGroup($key, $value);
				}
			}
			$upd ?
			$this->pageCompliteMessage = $this->lang->line('INFO_USERS_GROUP_UPDATE_COMPLITE') :
			$this->pageErrorMessage = $this->lang->line('INFO_USERS_GROUP_UPDATE_ERROR');

		}

		# обновление типов групп
		if($updateUserGroupTypes = $this->input->post('update_user_group_access_type'))
		{
			$tmp = array();
			foreach($updateUserGroupTypes as $value) {
				$tmp[$value['type_id']] = $value['type_name'];
			}
			app_add_option('panel_all_roles', $tmp, 'general');
			$this->pageCompliteMessage = $this->lang->line('INFO_USERS_ACCESS_ROLES_GROUP_COMPLITE_UPDATE');

		}

		$data['users_groups'] = $this->LoginAdmModel->getUsersGroups();

		if(isset($args[0]) and $args[0] == 'edit')
		{
			$userID = isset($args[1]) ? $args[1] : 0;
			if(!$userID)
			{
				$this->pageContent = '';
				$this->pageErrorMessage = $this->lang->line('INFO_USERS_USER_NOT_FOUND');
				return $this->_render_content();
			}

			# update user
			if($userinfo = $this->input->post('userinfo'))
			{
				if(isset($userinfo['new_password']) and trim($userinfo['new_password'])) {
					$userinfo['password'] = $this->LoginAdmModel->encryptPassword(trim($userinfo['new_password']));
				}

				if($this->LoginAdmModel->updateUser($userID, $userinfo)) {
					$this->pageCompliteMessage = $this->lang->line('INFO_USERS_UPDATE_COMPLITE');
				}else{
					$this->pageErrorMessage = $this->lang->line('INFO_USERS_UPDATE_ERROR');
				}

				# если пользователь изменяет самого себя, то надо обновить значения в сессиии
				$userData = $this->session->userdata();
				if($userData['id'] == $userID)
				{
					isset($userinfo['login']) ? 	$userData['login'] = $userinfo['login'] : 0;
					isset($userinfo['email']) ? 	$userData['email'] = $userinfo['email'] : 0;
					isset($userinfo['phone']) ? 	$userData['phone'] = $userinfo['phone'] : 0;
					isset($userinfo['name']) ? 		$userData['name'] = $userinfo['name'] : 0;
					isset($userinfo['image']) ? 	$userData['image'] = $userinfo['image'] : 0;
					isset($userinfo['datebirth']) ? $userData['datebirth'] = $userinfo['datebirth'] : 0;
					isset($userinfo['lang']) ? 		$userData['lang'] = $userinfo['lang'] : 0;

					$this->session->set_userdata($userData);
				}
			}

			$data['user'] = $this->LoginAdmModel->getAllUsers(0, $userID);
			if(!isset($data['user'][$userID])) {
				$this->pageContent = '';
				$this->pageErrorMessage = $this->lang->line('INFO_USERS_USER_NOT_FOUND');
				return $this->_render_content();
			}
			$data['user'] = $data['user'][$userID];

			$this->pageContent = $this->load->view('admin/users/users-edit', $data, true);
		}
		elseif(isset($args[0]) and $args[0] == 'new_user')
		{
			$this->pageContent = $this->load->view('admin/users/users-new', $data, true);
		}
		elseif(isset($args[0]) and $args[0] == 'users_groups')
		{
			$this->pageContent = $this->load->view('admin/users/users-groups', $data, true);
		}
		elseif(isset($args[0]) and $args[0] == 'groups_access')
		{
			$data['all_roles'] = $this->LoginAdmModel->allRolesGroups;
			$data['accesses'] = $this->LoginAdmModel->accesses;
			$this->pageContent = $this->load->view('admin/users/users-groups-access', $data, true);
		}
		elseif(isset($args[0]) and $args[0] == 'users-groups-access-setting')
		{
			$data['all_roles'] = $this->LoginAdmModel->allRolesGroups;
			$this->pageContent = $this->load->view('admin/users/users-groups-access-setting', $data, true);
		}
		else
		{

			$sortKey = 'users_date_registr';
			$sortAsc = 'DESC';
			if($res = $this->input->get('sort')) {
				$sortKey = $res;
			}
			if($res = $this->input->get('sort_asc')) {
				$sortAsc = $res;
			}

			$data['activeGroup'] = $this->input->get('group') ? $this->input->get('group') : 1;
			$data['sort_key'] = $sortKey;
			$data['sort_asc'] = $sortAsc;
			$getLinkGroup = 'group=' . $data['activeGroup'];

			$data['users'] = array();
			$this->db->where('users_group', $data['activeGroup']);

			$this->db->order_by($sortKey, $sortAsc);
			$query = $this->db->get('users');
			foreach($query->result_array() as $row) {
				$data['users'][$row['users_id']] = $row;
			}

			$data['sortLinkStatus'] = '/admin/users?'. $getLinkGroup . '&sort=users_status&sort_asc=';
			$data['sort_asc'] == 'DESC' ? $data['sortLinkStatus'] .= 'ASC' : $data['sortLinkStatus'] .= 'DESC';
			$sortKey == 'users_status' ? $data['sortLinkStatusActive'] = 'active-sort active-' . strtolower($sortAsc) : $data['sortLinkStatusActive'] = '';

			$data['sortLinkDateReg'] = '/admin/users?'. $getLinkGroup . '&sort=users_date_registr&sort_asc=';
			$data['sort_asc'] == 'DESC' ? $data['sortLinkDateReg'] .= 'ASC' : $data['sortLinkDateReg'] .= 'DESC';
			$sortKey == 'users_date_registr' ? $data['sortLinkDateRegActive'] = 'active-sort active-' . strtolower($sortAsc) : $data['sortLinkDateRegActive'] = '';

			$data['sortLinkListVis'] = '/admin/users?'. $getLinkGroup . '&sort=users_last_visit&sort_asc=';
			$data['sort_asc'] == 'DESC' ? $data['sortLinkListVis'] .= 'ASC' : $data['sortLinkListVis'] .= 'DESC';
			$sortKey == 'users_last_visit' ? $data['sortLinkListVisActive'] = 'active-sort active-' . strtolower($sortAsc) : $data['sortLinkListVisActive'] = '';

			$this->pageContent = $this->load->view('admin/users/users', $data, true);
		}
		return $this->_render_content();
	}

	# настройка свойств плагина
	private function page_setting_plugin($args = array())
	{
		$data = array();
		if(!$args || !isset($this->installPlugins[$args[0]]))
		{
			//$this->pageContent = '';
			//$this->data['infoerror'] = 'Плагин не указан';
			//return $this->_render_content();
			return $this->_render_not_found();
		}

		$data['message'] = '';
		$plugin_folder = $args[0];

		# доступ
		if(!$this->LoginAdmModel->checkAccessUser($plugin_folder)) {
			return $this->_render_not_found();
		}

		$data['plugin_name'] = $this->installPlugins[$plugin_folder]['plugins_name'];
		$data['plugin_name'] .= ' V' . $this->installPlugins[$plugin_folder]['plugins_version'];

		$this->pageContentTitle = $this->lang->line('h1_setting_plugin');
		$this->pageContentDescription = $this->lang->line('h1_setting_plugin');

		# опции плагина
		$data['plugin_options'] = $this->installPlugins[$plugin_folder]['options'];
		$data['plugin_folder'] = $plugin_folder;

		// значения для опций плагина
		foreach($data['plugin_options'] as $key => &$value){
			$value['value'] = app_get_option($key, $plugin_folder, $value['default']);
		}
		unset($value);

		# возможно плагин имеет хук на свои настройки
		$pathHook = info('plugins_dir') . $plugin_folder . '/admin-hooks/Setting-hook.php';
		if(file_exists($pathHook)){
			require_once($pathHook);
			$HookSetting = new HookPluginSetting($data);
			$data = $HookSetting->runHook();
		}

		$this->pageContent = $this->load->view('admin/setting-plugin', $data, true);
		return $this->_render_content();
	}

	/**
	*
	* Страница документации
	*
	*/

	private function page_docs($args = array())
	{
		$data = array();
		$data['message'] = '';
		$this->pageContentTitle = $this->lang->line('H1_DOCS');
		$this->pageContentDescription = $this->lang->line('H1_DOCS');

		if($args)
		{
			$filePath = 'admin/docs/doc-' . $args[0];
			$this->pageContent = $this->load->view($filePath, $data, true);
		}
		else
		{
			$this->pageContent = $this->load->view('admin/docs/doc-index', $data, true);
		}
		return $this->_render_content();
	}

	# обработки сортировщика по AJAX
	public function page_set_sort()
	{
		$response = array('status' => 'ERROR', 'info' => 'Error script');
		$trueUpdate = true;

		if(!$table = $this->input->post('sort_table')) {
			$response['info'] = 'Table undefined';
			$trueUpdate = false;
		}

		if(!$tableField = $this->input->post('sort_field')) {
			$response['info'] = 'Table field undefined';
			$trueUpdate = false;
		}

		if(!$tableIndex = $this->input->post('sort_index')) {
			$response['info'] = 'Index undefined';
			$trueUpdate = false;
		}

		if(!$trueUpdate) {
			echo json_encode($response);
			return;
		}

		// получим автоинкрементное поле
		$autoIncrField = '';
		$this->db->dbprefix;
		$query = $this->db->query('SHOW COLUMNS FROM `' . $this->db->dbprefix . $table . '`');
		foreach($query->result_array() as $row) {
			if($row['Extra'] == 'auto_increment') {
				$autoIncrField = $row['Field'];
				break;
			}
		}

		if(!$autoIncrField) {
			$response['info'] = 'Table error';
			echo json_encode($response);
			return;
		}

		$upd = false;
		foreach($tableIndex as $index => $tableElemID) {
			if(strpos($index, 'INDEX_') === 0) {
				$this->db->where($autoIncrField, $tableElemID);
				$upd = $this->db->update($table, array($tableField => str_replace('INDEX_', '', $index)));
			}
		}

		if($upd) {
			$response['status'] = 'OK';
			$response['info'] = 'Sorted complite';
		} else {
			$response['info'] = 'Sorted error';
		}

		echo json_encode($response);
		exit();
	}

	# AJAX: проверка поля типа данных у объектов
	public function page_get_field_objects()
	{
		$response = array('status' => 'ERROR', 'info' => 'Error script');
		$fieldID = $this->input->post('field_id');

		//$this->db->select();
		$this->db->where('objects_data_field', $fieldID);
		$query = $this->db->get('objects_data');
		$countFields = $query->num_rows();
		$response['status'] = 'OK';
		$response['count'] = $query->num_rows();
		$response['info'] = 'Complite';
		$response['button'] = '<div class="df-found" style="padding: 12px;background: #cbf9cb;margin: 8px 0;">Найдено сохраненных: ' . $response['count'] . '.';
		if($response['count']) {
			$response['button'] .= ' <a href="#" class="delete-all-fields" data-field-id="'.$fieldID.'">Удалить сохраненные</a>';
		}
		$response['button'] .= '</div>';

		if($this->input->get('delete')) {
			if($response['count']) {
				$this->db->where('objects_data_field', $fieldID);
				$query = $this->db->delete('objects_data');
				$response = array('status' => 'OK', 'info' => 'Delete complite');
			} else {
				$response = array('status' => 'ERROR', 'info' => 'Fields is not found');
			}
		}
		echo json_encode($response);
	}

	# AJAX: индексация сайта
	public function page_index_site()
	{
		$response = array('status' => 'ERROR', 'flag' => 'STOP', 'step' => 'stop', 'info' => 'Ajax error');
		try {
			if($step = $this->input->post('step')) {
				$response['status'] = 'OK';
				switch($step) {
					case 'start':
						// прописываем ссылки
						if($this->CommonModel->runIndexesSite()) {
							$response['flag'] = 'CONTINUE';
							$response['info'] = app_lang('INFO_INDEX_SITE_LINKS_COMPLITE');
							$response['step'] = 'second';
						} else {
							throw new Exception(app_lang('INFO_INDEX_SITE_LINKS_ERROR'));
						}
						break;
					case 'second':
					//case 'start':
						if($this->CommonModel->runIndexesDates()) {
							$response['flag'] = 'CONTINUE';
							$response['info'] = app_lang('INFO_INDEX_SITE_DATE_COMPLITE');
							$response['step'] = 'third';
						} else {
							throw new Exception(app_lang('INFO_INDEX_SITE_DATE_ERROR'));
						}
						break;
					/*case 'third':
						$response['flag'] = 'CONTINUE';
						$response['info'] = 'Update step 3';
						$response['step'] = 'fourth';
						break;*/
					default:
						$response['flag'] = 'STOP';
						$response['info'] = app_lang('INFO_INDEX_SITE_COMPLITE');
				}

			} else {
				throw new Exception(app_lang('INFO_INDEX_SITE_PARAMS_ERROR'));
			}
		} catch (Exception $e) {
			$response['status']  = 'ERROR';
			$response['info'] = $e->getMessage();
		}
		echo json_encode($response);
	}


	# подключение прочих страниц (плагины и прочих)
	private function other_methods($method, $args)
	{
		# в плагинах нет метода
		if(!isset($this->installPlugins[$method])) {
			return $this->_render_not_found();
		}

		# доступ
		if(!$this->LoginAdmModel->checkAccessUser($method)) {
			return $this->_render_not_found();
		}

		# ссылка на настройки плагина
		if($this->installPlugins[$method]['options'])
			$this->settingPluginLink = '/admin/setting_plugin/' . $method;

		# страницы в админ-панели плагина - файл библиотеки должен иметь имя IndexFolderLib.php
		# Например: папка my-like_plugin -> IndexMyLikePluginLib.php. Класс должен называться также
		$path = info('plugins_dir') . $method . '/';
		$pathToLib = $path . 'libraries/';

		# установим патч для лоадера
		$this->load->add_package_path($path);

		$className = str_replace(array('-', '_'), ' ', $method);
		$className = 'Index' . str_replace(' ', '', ucwords($className) . 'Lib');
		if(file_exists($pathToLib . $className . '.php')) {
			require_once($pathToLib . $className . '.php');
			$this->$className = new $className();
			if($args && isset($args[0])) {
				$methodLib = $args[0];
				if(method_exists($this->$className, $methodLib)) {
					$this->$className->$methodLib();
				} else {
					$this->$className->index();
				}
			} else {
				$this->$className->index();
			}
		} else {
			// Совместимость со старыми плагинами
			$this->load->library('admin_plugin');

			// есть аргументы в url
			if($args) {
				$method_lib = $args[0];
				$this->pageContent = $this->admin_plugin->$method_lib();
			} else {
				$this->pageContent = $this->admin_plugin->index();
			}
		}

		# удалим патч для лоадера
		$this->load->remove_package_path($path);

		# загрузка css и js для страниц плагина
		if($this->installPlugins[$method]['assets']['top']) {
			$this->pluginsAssetsTop = array_merge($this->pluginsAssetsTop, $this->installPlugins[$method]['assets']['top']);
		}

		if($this->installPlugins[$method]['assets']['bottom']) {
			$this->pluginsAssetsBottom = array_merge($this->pluginsAssetsBottom, $this->installPlugins[$method]['assets']['bottom']);
		}

		return $this->_render_content();
	}

	/*
	* Генерация страницы
	*/
	public function _render_content()
	{
		if(!$this->pageContent) {
			$this->pageContent = $this->data['content'];
		}
		return $this->to_view();
	}

	private function to_view()
	{
		# если ajax запрос, то возвращаем только контент
		if(is_ajax()) {
			echo $this->pageContent;
			return;
		}

		$data = array();

		$this->data['ADMIN_ASSETS_URL'] = APP_BASE_URL . 'application/views/admin/templates/' . APP_CMS_TEMPLATE . '/assets/';
		$this->data['ADMIN_JS_URL'] = $this->data['ADMIN_ASSETS_URL'] . 'js/';
		$this->data['ADMIN_CSS_URL'] = $this->data['ADMIN_ASSETS_URL'] . 'css/';
		$this->data['ADMIN_IMG_URL'] = $this->data['ADMIN_ASSETS_URL'] . 'img/';

		$this->data['LANGS_JS'] = $this->pluginAssetsLang;

		if($this->pageContentTitle) {
			$this->data['PAGE_TITLE'] = $this->pageContentTitle;
		}

		if($this->pageContentDescription) {
			$this->data['PAGE_DESCRIPTION'] = $this->pageContentDescription;
		}

		isset($this->data['PAGE_TITLE']) ?: $this->data['PAGE_TITLE'] = 'default title';
		isset($this->data['PAGE_DESCRIPTION']) ?: $this->data['PAGE_DESCRIPTION'] = 'default description';


		isset($this->data['PAGE_KEYWORDS']) ?: $this->data['PAGE_KEYWORDS'] = 'default keywords';
		isset($this->data['PAGE_CANONICAL']) ?: $this->data['PAGE_CANONICAL'] = '';

		# сообщение успех
		if($this->pageCompliteMessage) {
			$this->data['infocomplite'] = '<div class="adm-complite">' . $this->pageCompliteMessage . '</div>';
		} else {
			$this->data['infocomplite'] = '';
		}

		# сообщение ошибка
		if($this->pageErrorMessage) {
			$this->data['infoerror'] = '<div class="adm-error">' . $this->pageErrorMessage . '</div>';
		} else {
			$this->data['infoerror'] = '';
		}

		# меню плагинов
		isset($this->data['left_nav_menu']) ?: $this->data['left_nav_menu'] = '';

		# системное меню
		$this->data['system_nav_menu'] = array();
		$activeMenu = FALSE;

		foreach($this->systemMenu as $key => $value) {
			if(!in_array($this->userInfo['group_type'], $this->accesses[$key]['access'])) {
				continue;
			}
			$key == $this->uri->segment(2) ? $activeMenu = TRUE : $activeMenu = FALSE;
			$this->data['system_nav_menu'][$value]['link'] = $key;
			$this->data['system_nav_menu'][$value]['active'] = $activeMenu;
		}

		$this->data['ASSETS_TOP'] = '';
		$this->data['ASSETS_BOTTOM'] = '';

		# стили и скрипты
		foreach($this->pluginsAssetsTop as $value)
			$this->data['ASSETS_TOP'] .= $value . NTAB;

		foreach($this->pluginsAssetsBottom as $value)
			$this->data['ASSETS_BOTTOM'] .= $value . NTAB;

		$this->data['userInfo'] = $this->userInfo;
		$this->data['settingPluginLink'] = $this->settingPluginLink;
		$this->data['content'] = $this->pageContent;
		$this->pageContent = '';

		$this->load->view('admin/templates/' . APP_CMS_TEMPLATE . '/template', $this->data);
	}

	/*
	* Генерация 404 страницы
	*/
	public function _render_not_found()
	{
		return $this->not_found();
	}

	private function not_found()
	{
		$this->data['PAGE_TITLE'] = '404';
		$this->data['PAGE_DESCRIPTION'] = '404';
		$this->data['PAGE_KEYWORDS'] = '404';

		$this->data['ADMIN_ASSETS_URL'] = APP_BASE_URL . 'application/views/admin/templates/' . APP_CMS_TEMPLATE . '/assets/';
		$this->data['ADMIN_JS_URL'] = $this->data['ADMIN_ASSETS_URL'] . 'js/';
		$this->data['ADMIN_CSS_URL'] = $this->data['ADMIN_ASSETS_URL'] . 'css/';
		$this->data['ADMIN_IMG_URL'] = $this->data['ADMIN_ASSETS_URL'] . 'img/';

		$this->data['LANGS_JS'] = $this->pluginAssetsLang;

		$this->data['ASSETS_TOP'] = '';
		$this->data['ASSETS_BOTTOM'] = '';
		# стили и скрипты
		foreach($this->pluginsAssetsTop as $value)
			$this->data['ASSETS_TOP'] .= $value . NTAB;

		foreach($this->pluginsAssetsBottom as $value)
			$this->data['ASSETS_BOTTOM'] .= $value . NTAB;

		# сообщение успех
		if(isset($this->data['infocomplite'])) {
			$this->data['infocomplite'] = '<div class="adm-complite">' . $this->data['infocomplite'] . '</div>';
		} else {
			$this->data['infocomplite'] = '';
		}

		# сообщение ошибка
		if(isset($this->data['infoerror'])) {
			$this->data['infoerror'] = '<div class="adm-error">' . $this->data['infoerror'] . '</div>';
		} else {
			$this->data['infoerror'] = '';
		}

		# меню плагинов
		isset($this->data['left_nav_menu']) ?: $this->data['left_nav_menu'] = array();

		# системное меню
		$this->data['system_nav_menu'] = array();
		$activeMenu = FALSE;
		foreach($this->systemMenu as $key => $value) {
			if(!in_array($this->userInfo['group_type'], $this->accesses[$key]['access'])) {
				continue;
			}

			$key == $this->uri->segment(2) ? $activeMenu = TRUE : $activeMenu = FALSE;
			$this->data['system_nav_menu'][$value]['link'] = $key;
			$this->data['system_nav_menu'][$value]['active'] = $activeMenu;
		}

		$this->data['userInfo'] = $this->userInfo;
		$this->data['settingPluginLink'] = $this->settingPluginLink;
		$this->data['content'] = $this->load->view('admin/not-found', array(), TRUE);

		$this->load->view('admin/templates/' . APP_CMS_TEMPLATE . '/template', $this->data);
	}
}
