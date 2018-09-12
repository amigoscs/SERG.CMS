<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* EmarketWidjet - виджет e-market

	* Version 1.0
	* UPD 2018-09-07
	* первая версия
*/

class EmarketWidjet extends WidjetAdmModel
{

	// $this->widjetInfoNumber = '';
	// $this->widjetInfoTitle = '';
	// $this->widjetIcon = '';
	// $this->widjetLinkToPage = '';
	// $this->widjetContent = '';

	function __construct()
	{
        parent::__construct();
		$this->reset();
    }

	// сбор виджета
	public function build()
	{
		// если класс виджета не разрешает работу, то ничего не будем делать
		if(!$this->trueBuild) {
			return;
		}
		$this->widjetLinkToPage = '/admin/e-market/order_list';
		$this->getOrders();
		return $this->_render();
	}

	private function getOrders()
	{
		$params = array(
			'status' => 2
		);

		$this->db->limit(6);
		$orders = $this->EmarketModel->EmarketOrderModel->allOrders('', $params);

		$this->widjetInfoNumber = count($orders);
		$this->widjetInfoTitle = 'Новых заказов';
		//pr($orders);
		$content = '';
		$userEmailFieldID = app_get_option('user_email_field', 'e-market', 0);
		foreach($orders as $value) {
			$content .= '<div class="w-row">';
			$content .= '<div><a href="/admin/e-market/order_view?order=' . $value['ecart_id'] . '">' . $value['ecart_num'] . '</a></div>';
			$content .= '<div>' . $value['ecart_summ'] . ' ' . $value['ecart_currency_info']['ecartcur_code'] . '</div>';
			if(isset($value['user_fields_values'][$userEmailFieldID])) {
				$content .= '<div>' . $value['user_fields_values'][$userEmailFieldID]['ecartfield_value'] . '</div>';
			} else {
				$content .= '<div></div>';
			}
			$content .= '</div>';
		}
		$this->widjetContent = $content;
		unset($content);
	}



}
