<?php defined('BASEPATH') OR exit('No direct script access allowed'); 

# ХУК НА СТРАНИЦУ НАСТРОЙКИ ПЛАГИНА




class HookPluginSetting extends CI_Model
{
	private $data;
	
	function __construct($data = array())
    {
        parent::__construct();
		$this->data = $data;
		
    }
	
	public function runHook()
	{
		$allUserGroups = $this->LoginAdmModel->getUsersGroups();
		
		foreach($allUserGroups as $key => $value){
			$this->data['plugin_options']['adst_available_groups']['values'][$key] = $value['users_group_name'] . ' (' . $value['users_group_descr'] . ')';
		}
		return $this->data;
	}
}
	
	
	
?>