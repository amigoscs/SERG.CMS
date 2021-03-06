<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  Роут для стуктуры сайта.
	*
	* UPD 2018-10-25
	* Version 1.1
	* Адаптация под версию 7.4+
	*
*/

class AdstRemap extends CI_Model {

	function __construct()
    {
        parent::__construct();

    }

	/*
	* При включенной опции останаваливает работу сайта если не авторизован пользователь
	*/
	public function stopNotAuth($IndexController)
	{
		$siteClose = app_get_option('adst_close_site', 'admin-site-tree', 'no');
		$fileClose = app_get_option('adst_close_template', 'admin-site-tree', 'website-not-available');
		$fileClosePath = APPPATH . 'views/templates/' . APP_SITE_TEMPLATE . '/' . $fileClose . '.php';

		# настроено на закрытие сайта. Доступ открыт только для админов или отмеченных групп
		if($siteClose == 'yes' and file_exists($fileClosePath)) {
			$userGroupsOpen = explode(',', app_get_option('adst_available_groups', 'admin-site-tree', '2'));
			$IndexController->Template->LINKTOLOGOUT = 0;
			$IndexController->Template->USER = array();

			$userLoginGroup = false;
			if($user = is_login()) {
				$userLoginGroup = $user['group'];
				$IndexController->Template->USER = $user;
			}

			if(!is_admin()) {

				// если пользователь залогинен, то надо проверить, возможно он разрешен в настройках
				if($userLoginGroup){
					if(in_array($userLoginGroup, $userGroupsOpen)) {
						return true;
					} else {
						$IndexController->Template->LINKTOLOGOUT = '1';
					}
				}

				$IndexController->Template->PAGE_TITLE = app_get_option('site_title_default', 'site', '');
				$IndexController->Template->PAGE_DESCRIPTION = app_get_option('site_description_default', 'site', '');
				$IndexController->Template->PAGE_KEYWORDS = app_get_option('site_keywords_default', 'site', '');

				// укажем наш файл как шаблон страницы
				$IndexController->page_template = $fileClose;
				// отключим штатный ремап
				$IndexController->BreakRemap = true;
			}
		}
	}


}
