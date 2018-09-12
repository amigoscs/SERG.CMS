<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  admin-site-tree Model

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
			$IndexController->dataTemplate['LINKTOLOGOUT'] = 0;
			$IndexController->dataTemplate['USER'] = array();

			$userLoginGroup = FALSE;
			if($user = is_login()){
				$userLoginGroup = $user['group'];
				$IndexController->dataTemplate['USER'] = $user;
			}

			if(!is_admin()){

				// если пользователь залогинен, то надо проверить, возможно он разрешен в настройках
				if($userLoginGroup){
					if(in_array($userLoginGroup, $userGroupsOpen))
						return TRUE;
					else
						$IndexController->dataTemplate['LINKTOLOGOUT'] = '1';

				}

				$IndexController->dataTemplate['PAGE_TITLE'] = app_get_option('site_title_default', 'site', '');
				$IndexController->dataTemplate['PAGE_DESCRIPTION'] = app_get_option('site_description_default', 'site', '');
				$IndexController->dataTemplate['PAGE_KEYWORDS'] = app_get_option('site_keywords_default', 'site', '');

				// укажем наш файл как шаблон страницы
				$IndexController->page_template = $fileClose;
				// отключим штатный ремап
				$IndexController->BreakRemap = TRUE;
			}
		}
	}


}
