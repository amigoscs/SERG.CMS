<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* EmarketOptionsModel
	*
	* UPD 2018-04-17
	* Version 1.0

*/

class EmarketOptionsModel extends CI_Model
{

	public $TEMP_ARRAY;

	function __construct()
    {
        parent::__construct();
		$this->init();
    }

	/*
	* инициализация
	*/
	public function init()
	{
		$this->TEMP_ARRAY = array();

	}

	/*
	* сброс
	*/
	public function reset()
	{
		$this->TEMP_ARRAY = array();

	}

	/*
	* возвращает все типы корзин
	*/
	public function getCarts($onlyPublish = true)
	{
		$out = array();
		if($onlyPublish) {
			$this->db->where('ecarttypes_status', 'publish');
		}
		$query = $this->db->get('ecart_carts');
		foreach($query->result_array() as $row) {
			$out[$row['ecarttypes_id']] = $row;
		}
		return $out;
	}

	/*
	* обновить тип корзины
	*/
	public function updateCart($cartId, $values = array())
	{
		$data = array();
		isset($values['name']) ? $data['ecarttypes_name'] = $values['name'] : 0;
		isset($values['descr']) ? $data['ecarttypes_descr'] = $values['descr'] : 0;
		isset($values['status']) ? $data['ecarttypes_status'] = $values['status'] : 0;

		if($data) {
			$this->db->where('ecarttypes_id', $cartId);
			return $this->db->update('ecart_carts', $data);
		} else {
			return false;
		}
	}

	/*
	* Создать новый тип корзины
	*/
	public function createCart($values = array())
	{
		$data = array();
		$data['ecarttypes_name'] = isset($values['name']) ? $values['name'] : '';
		$data['ecarttypes_descr'] = isset($values['descr']) ? $values['descr'] : '';
		$data['ecarttypes_status'] = isset($values['status']) ? $values['status'] : 'hidden';

		if(!$data['ecarttypes_name']) {
			return false;
		}
		return $this->db->insert('ecart_carts', $data);

	}

	/*
	* возвращает все поля формы
	*/
	public function getFieldsFormCart($onlyPublish = true)
	{
		$out = array();
		if($onlyPublish) {
			$this->db->where('ecartf_status', 'publish');
		}
		$this->db->order_by('ecartf_order', 'ASC');
		$query = $this->db->get('ecart_fields');
		foreach($query->result_array() as $row) {
			$out[$row['ecartf_parent']][$row['ecartf_id']] = $row;
		}
		return $out;
	}

	/*
	* обновить поле для формы
	*/
	public function updateFieldsFormCart($fieldID, $values = array())
	{
		$data = array();
		isset($values['parent']) ? $data['ecartf_parent'] = $values['parent'] : 0;
		isset($values['type']) ? $data['ecartf_type'] = $values['type'] : 0;
		isset($values['name']) ? $data['ecartf_name'] = $values['name'] : 0;
		isset($values['label']) ? $data['ecartf_label'] = $values['label'] : 0;
		isset($values['descr']) ? $data['ecartf_descr'] = $values['descr'] : 0;
		isset($values['order']) ? $data['ecartf_order'] = $values['order'] : 0;
		isset($values['required']) ? $data['ecartf_required'] = $values['required'] : 0;
		isset($values['status']) ? $data['ecartf_status'] = $values['status'] : 0;

		if($data) {
			$this->db->where('ecartf_id', $fieldID);
			return $this->db->update('ecart_fields', $data);
		} else {
			return false;
		}
	}

	/*
	* создать новое поле для формы
	*/
	public function createFieldsFormCart($values = array())
	{
		$data['ecartf_parent'] = isset($values['parent']) ? $values['parent'] : 0;
		$data['ecartf_type'] = isset($values['type']) ? $values['type'] : 'text';
		$data['ecartf_name'] = isset($values['name']) ? $values['name'] : '';
		$data['ecartf_label'] = isset($values['label']) ? $values['label'] : '';
		$data['ecartf_descr'] = isset($values['descr']) ? $values['descr'] : '';
		$data['ecartf_order'] = isset($values['order']) ? $values['order'] : 0;
		$data['ecartf_required'] = isset($values['required']) ? $values['required'] : 0;
		$data['ecartf_status'] = isset($values['status']) ? $values['status'] : 'hidden';

		if(!$data['ecartf_name']) {
			return false;
		}

		return $this->db->insert('ecart_fields', $data);
	}

	/*
	* обработка полей формы корзины (При селекте из базы надо поля привести в человеческий вид)
	*/
	public function parseUserFields($fieldsArray = array())
	{
		if(!$this->TEMP_ARRAY) {
			$this->TEMP_ARRAY = $this->getFieldsFormCart();
		}

		$out = array();
		foreach($this->TEMP_ARRAY[0] as $key => $value) {

			if(isset($fieldsArray[$key])) {
				$out[$key]['ecartfield_name'] = $value['ecartf_name'];
				$out[$key]['ecartfield_label'] = $value['ecartf_label'];
				$out[$key]['ecartfield_value'] = $fieldsArray[$key];
				$out[$key]['ecartfield_type'] = $value['ecartf_type'];
				$out[$key]['ecartfield_save_value'] = $fieldsArray[$key];
			} elseif($value['ecartf_type'] == 'checkbox') {
				$out[$key]['ecartfield_name'] = $value['ecartf_name'];
				$out[$key]['ecartfield_label'] = $value['ecartf_label'];
				$out[$key]['ecartfield_value'] = '0';
				$out[$key]['ecartfield_type'] = $value['ecartf_type'];
				$out[$key]['ecartfield_save_value'] = '0';
			} else {
				$out[$key]['ecartfield_name'] = $value['ecartf_name'];
				$out[$key]['ecartfield_label'] = $value['ecartf_label'];
				$out[$key]['ecartfield_value'] = 'Empty';
				$out[$key]['ecartfield_type'] = $value['ecartf_type'];
				$out[$key]['ecartfield_save_value'] = 'Empty';
			}

			$tmp = array();
			// для выпадающего списка или радио дополнительная обработка
			if($value['ecartf_type'] == 'radio' || $value['ecartf_type'] == 'select') {
				if(isset($fieldsArray[$key])) {
					$selectValue = $fieldsArray[$key];
					if(isset($this->TEMP_ARRAY[$key][$selectValue])) {
						$out[$key]['ecartfield_value'] = $this->TEMP_ARRAY[$key][$selectValue]['ecartf_name'];
					} else {
						$tmp = current($this->TEMP_ARRAY[$key]);
						$out[$key]['ecartfield_value'] = $tmp['ecartf_name'];
					}
				} else {
					$tmp = current($this->TEMP_ARRAY[$key]);
					$out[$key]['ecartfield_value'] = $tmp['ecartf_name'];
				}
			}
		}
		return $out;
	}





	/*
	* возвращает все статусы корзин
	*/
	public function getSatusCart($onlyPublish = true)
	{
		$out = array();
		if($onlyPublish) {
			$this->db->where('ecarts_status', 'publish');
		}
		$query = $this->db->get('ecart_status');
		foreach($query->result_array() as $row) {
			$out[$row['ecarts_id']] = $row;
		}
		return $out;
	}

	/*
	* обновить статус корзины
	*/
	public function updateSatusCart($statusId, $values = array())
	{
		$data = array();
		isset($values['name']) ? $data['ecarts_name'] = $values['name'] : 0;
		isset($values['descr']) ? $data['ecarts_descr'] = $values['descr'] : 0;
		isset($values['status']) ? $data['ecarts_status'] = $values['status'] : 0;

		if($data) {
			$this->db->where('ecarts_id', $statusId);
			return $this->db->update('ecart_status', $data);
		} else {
			return false;
		}
	}

	/*
	* Создать статус корзины
	*/
	public function createSatusCart($values = array())
	{
		$data = array();
		$data['ecarts_name'] = isset($values['name']) ? $values['name'] : '';
		$data['ecarts_descr'] = isset($values['descr']) ? $values['descr'] : '';
		$data['ecarts_status'] = isset($values['status']) ? $values['status'] : 'hidden';

		if(!$data['ecarts_name']) {
			return false;
		}
		return $this->db->insert('ecart_status', $data);

	}

	/*
	* возвращает все статусы оплаты
	*/
	public function getCashSatusCart()
	{
		return array(1 => 'Не оплачено', 2 => 'Оплачено');
	}

	/*
	* обновление таблицы с валютой

	private function updateCurrency()
	{
		$query = $this->db->get('ecart_currency');
		if($query->num_rows()) {
			foreach($query->result_array() as $row) {
				if($row['ecartcur_site']) {
					$this->siteCurrency = $row;
				}

				if($row['ecartcur_products']) {
					$this->productsCurrency = $row;
				}

				$this->allCurrency[$row['ecartcur_id']] = $row;
			}
		} else {
			return FALSE;
		}
	}*/



	/*
	* загрузка валюты по ID

	public function loadCurrency($currencyID = 0)
	{
		$this->db->where('ecartcur_id', $currencyID);
		$query = $this->db->get('ecart_currency');
		if($query->num_rows()) {
			return $query->row_array();
		} else {
			return array();
		}
	}*/

	/*
	* возвращает пользователя корзины
	*
	*/
}
