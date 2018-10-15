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
		$this->data['plugin_options']['e_market_products_table_headers'] = array(
			'name' => 'Вывести в таблице товаров',
			'type' => 'multiselect',
			'default' => '',
			'values' => array(),
			'description' => 'Укажите поля, которые надо вывести в таблице товаров',
			'value' => app_get_option("e_market_products_table_headers", "e-market", "")
		);
		$this->getDataFields();
		return $this->data;
	}

	private function getDataFields()
	{
		$this->CommonModel->getOnlyPublish = true;
		$dataFields = $this->CommonModel->getAllDataTypesFields();
		foreach($dataFields as $value) {
			$this->data['plugin_options']['e_market_products_table_headers']['values'][$value['types_fields_id']] = $value['types_fields_name'];
		}
	}
}



?>
