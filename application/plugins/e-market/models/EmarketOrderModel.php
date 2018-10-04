<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* EmarketOrderModel - класс оформления заказа
	*
	* UPD 2018-05-23
	* Version 0.1
	*
	* UPD 2018-08-02
	* Version 0.2
	* Добавлена запись SKU в массив при оформлении заказа. Тема письма вынесена в настройки

*/

class EmarketOrderModel extends CI_Model
{

	public $adminMails;

	function __construct()
    {
        parent::__construct();
		$this->init();
    }


	public function init()
	{
		$this->adminMails = array();
		// массив полей с адресами почты админов
		if($tmp = app_get_option('admin_mail', 'e-market', 'ALL || ')) {
			$tmp = explode('##', $tmp);
			foreach($tmp as $value) {
				if(!strpos($value, '||')) {
					continue;
				}
				$tmp = explode('||', $value);
				$this->adminMails[trim($tmp[0])] = trim($tmp[1]);
			}
		}
	}

	/*
	* Оформление заказа
	* $postValues - значение $_POST.
	* должен содержать массив ['cartfield'] (поля формы заказа)
	* может содержать массив ['product_description'] (комментарии для товаров)
	*/
	public function createOrder($postValues = array(), $cartID = 0, $typeCart = 1)
	{
		$cart = $this->EmarketModel->cartInfo($cartID, $typeCart);

		if(!$cart) {
			return false;
		}

		$formValues = isset($postValues['cartfield']) ? $postValues['cartfield'] : array();
		$productDescription = isset($postValues['product_description']) ? $postValues['product_description'] : array();
		$userEmail = '';
		$adminEmail = '';

		# сформируем адреса для админов
		// адрес должен соответсвовать группе цен товара. Если не соответствует, то берем общий адрес
		$userEmailGroup = $this->EmarketModel->EmarketPriceModel->userGroup;
		if(isset($this->adminMails[$userEmailGroup])) {
			$adminEmail = $this->adminMails[$userEmailGroup];
		} elseif(isset($this->adminMails['ALL'])) {
			$adminEmail = $this->adminMails['ALL'];
		}

		$cartID = $cart['ecart_id'];
		// удалим корзину из класса
		if(isset($this->EmarketModel->userCarts[$cartID])) {
			unset($this->EmarketModel->userCarts[$cartID]);
		}

		$dataUpdateTable = array(
			'ecart_currency' => $cart['ecart_currency'],
			'ecart_date_order' => date('Y-m-d H:i:s'),
			'ecart_num' => $this->createOrderNum($cartID),
			'ecart_summ' => $cart['ecart_summ'],
			'ecart_status' => 2,
		);

		# обновим таблицу корзины
		$this->db->where('ecart_id', $cartID);
		if(!$this->db->update('ecart', $dataUpdateTable)) {
			return false;
		}
		$cart['ecart_date_order'] = $dataUpdateTable['ecart_date_order'];
		$cart['ecart_num'] = $dataUpdateTable['ecart_num'];
		$cart['ecart_status'] = $dataUpdateTable['ecart_status'];

		# обновим таблицу с товарами
		$value = array();
		foreach($cart['products'] as $key => &$value) {
			$sku = isset($value['object']['data_fields'][$this->EmarketModel->SKUkeyField]) ? $value['object']['data_fields'][$this->EmarketModel->SKUkeyField]['objects_data_value'] : 0;
			$dataUpdateTable = array(
				'ecartp_object_sku' => $sku,
				'ecartp_price' => $value['ecartp_price'],
				'ecartp_descr' => isset($productDescription[$key]) ? $productDescription[$key] : '',
			);
			$this->db->where('ecartp_cart_id', $cartID);
			$this->db->where('ecartp_object_id', $key);
			if(!$this->db->update('ecart_products', $dataUpdateTable)) {
				return false;
			}
			$value['ecartp_object_sku'] = $sku;
		}
		unset($value);

		# запишем поля из формы
		if($formValues) {
			$this->updateUserValues($cartID, $formValues);
			if(isset($formValues[app_get_option('user_email_field', 'e-market', '0')])) {
				$userEmail = $formValues[app_get_option('user_email_field', 'e-market', '0')];
				// проверка E-mail на правильность
				if(!app_valid_email($userEmail)) {
					$userEmail = '';
				}
			}

			# все поля формы
			$allFields = $this->EmarketModel->EmarketOptionsModel->getFieldsFormCart();
			foreach($allFields[0] as $key => $value) {
				if(isset($formValues[$key])) {
					$cart['user_fields_values'][$key]['ecartfield_name'] = $value['ecartf_name'];
					$cart['user_fields_values'][$key]['ecartfield_label'] = $value['ecartf_label'];
					$cart['user_fields_values'][$key]['ecartfield_value'] = $formValues[$key];
					$cart['user_fields_values'][$key]['ecartfield_type'] = $value['ecartf_type'];
					$cart['user_fields_values'][$key]['ecartfield_save_value'] = $formValues[$key];
				} elseif($value['ecartf_type'] == 'checkbox') {
					$cart['user_fields_values'][$key]['ecartfield_name'] = $value['ecartf_name'];
					$cart['user_fields_values'][$key]['ecartfield_label'] = $value['ecartf_label'];
					$cart['user_fields_values'][$key]['ecartfield_value'] = '0';
					$cart['user_fields_values'][$key]['ecartfield_type'] = $value['ecartf_type'];
					$cart['user_fields_values'][$key]['ecartfield_save_value'] = '0';
				} else {
					$cart['user_fields_values'][$key]['ecartfield_name'] = $value['ecartf_name'];
					$cart['user_fields_values'][$key]['ecartfield_label'] = $value['ecartf_label'];
					$cart['user_fields_values'][$key]['ecartfield_value'] = '';
					$cart['user_fields_values'][$key]['ecartfield_type'] = $value['ecartf_type'];
					$cart['user_fields_values'][$key]['ecartfield_save_value'] = '';
				}

				// для выпадающего списка или радио дополнительная обработка
				if($value['ecartf_type'] == 'radio' || $value['ecartf_type'] == 'select') {
					if(isset($formValues[$key])) {
						$selectValue = $formValues[$key];
						if(isset($allFields[$key][$selectValue])) {
							$cart['user_fields_values'][$key]['ecartfield_value'] = $allFields[$key][$selectValue]['ecartf_name'];
						} else {
							$tmp = current($allFields[$key]);
							$cart['user_fields_values'][$key]['ecartfield_value'] = $tmp['ecartf_name'];
						}
					} else {
						$tmp = current($allFields[$key]);
						$cart['user_fields_values'][$key]['ecartfield_value'] = $tmp['ecartf_name'];
					}
				}
			}
		}

		# разошлем письма всем
		$this->sendMail($cart, $adminEmail, $userEmail);
		return $cart;

		//pr($userEmail);
		//pr($adminEmail);

		//pr($this->EmarketModel->EmarketPriceModel->userGroup);
		//pr($this->adminMails);


	}

	/*
	* Создание номера заказа
	*/
	public function createOrderNum($cartID = 0)
	{
		$pseudoNumCart = app_get_option('pseudo_cart_num', 'e-market', '%ID%');
		# если в шаблоне есть дата
		preg_match('|%DATE\((.*?)\)%|sei', $pseudoNumCart, $arr);
		if($arr){
			$pseudoNumCart = str_replace($arr[0], date($arr[1]), $pseudoNumCart);
		}

		$pseudoNumCart = str_replace('%ID%', $cartID, $pseudoNumCart);
		return $pseudoNumCart;
	}

	/*
	* запись пользовательских значений из формы
	*/
	public function updateUserValues($cartID = 0, $values = array())
	{
		if(!$cartID && !$values) {
			return false;
		}

		// очистка возможных старых значений
		$this->db->where('ecartfval_cart_id', $cartID);
		$this->db->delete('ecart_fields_values');

		$upd = false;
		foreach($values as $key => $value) {
			$dataUpdateTable = array(
				'ecartfval_cart_id' => $cartID,
				'ecartfval_field_id' => $key,
				'ecartfval_value' => $value,
			);
			$upd = $this->db->insert('ecart_fields_values', $dataUpdateTable);
		}
		return $upd;
	}

	/*
	* создание писем для админа и покупателя

	public function createMail($cart = array(), $admin = false)
	{
		if($admin) {
			return $this->EmarketModel->EmarketViewsLib->renderOrderMailAdmin();
		}

		pr($cart);
	}*/

	/*
	* отправка писем на адреса
	*/
	public function sendMail($cart = array(), $adminEmail = '', $userEmail = '')
	{
		$sendEmail = app_get_option('send_email', 'e-market', 'no');
		if($sendEmail == 'no') {
			return true;
		}

		$serverEmail = app_get_option('server_email', 'e-market', '');
		$serverName = app_get_option('server_name', 'e-market', 'E-market');
		$emailProtocol = app_get_option('email_protocol', 'general', 'mail');
		$mailSubj = app_get_option('mail_subject', 'e-market', 'New order %ID%');
		$mailSubj = str_replace('%ID%', $cart['ecart_num'], $mailSubj);
		$mailSubj = str_replace('%SUMM%', $cart['ecart_summ'], $mailSubj);

		if(!app_valid_email($serverEmail)) {
			return false;
		}

		$this->load->library('email');

		if($adminEmail)
		{
			$mailBody = $this->EmarketModel->EmarketViewsLib->renderOrderMailAdmin($cart);
			//echo $mailBody;

			# конфигурация почты
			$this->email->clear();
			$this->email->protocol = $emailProtocol;
			$this->email->from($serverEmail, $serverName);
			$this->email->mailtype = 'html';
			$this->email->reply_to($serverEmail, $serverName);
			//$this->email->_safe_mode = true; # иначе CodeIgniter добавляет -f к mail - не будет работать в не safePHP
			$this->email->subject($mailSubj);
			$this->email->message($mailBody);
			$this->email->to($adminEmail);
			if(!$this->email->send()){
				exit('Error send mail');
			}
		}

		if($userEmail && $sendEmail == 'all')
		{
			$mailBody = $this->EmarketModel->EmarketViewsLib->renderOrderMailUser($cart);
			//echo $mailBody;

			# конфигурация почты
			$this->email->clear();
			$this->email->protocol = $emailProtocol;
			$this->email->from($serverEmail, $serverName);
			$this->email->mailtype = 'html';
			$this->email->reply_to($serverEmail, $serverName);
			//$this->email->_safe_mode = true; # иначе CodeIgniter добавляет -f к mail - не будет работать в не safePHP
			$this->email->subject($mailSubj);
			$this->email->message($mailBody);
			$this->email->to($userEmail);
			if(!$this->email->send()){
				exit('Error send mail');
			}
		}
		return true;
	}

	/*
	* Все заказы
	*/
	public function allOrders($userKey = '', $params = array())
	{
		$out = array();

		if($userKey) {
			$this->db->where('ecart_user_key', $userKey);
		}

		if(isset($params['date_start']) && $params['date_start']) {
			$this->db->where('ecart_date_order >=', $params['date_start'] . ' 00:00:00');
		}

		if(isset($params['date_stop']) && $params['date_stop']) {
			$this->db->where('ecart_date_order <=', $params['date_stop'] . ' 23:59:59');
		}

		if(isset($params['status']) && $params['status']) {
			if(is_array($params['status'])) {
				$this->db->where_in('ecart_status', $params['status']);
			} else {
				$this->db->where('ecart_status', $params['status']);
			}
		}

		if(isset($params['cash_status']) && $params['cash_status']) {
			$this->db->where('ecart_cash_status', $params['cash_status']);
		}

		$this->db->order_by('ecart_date_order', 'DESC');
		$query = $this->db->get('ecart');
		if(!$query->num_rows()) {
			return $out;
		}

		$allCartsID = array();
		$allCurrency = $this->EmarketModel->EmarketPriceModel->getAllCurrency();
		$users = array();
		$cartFields = array();
		foreach($query->result_array() as $row) {
			$out[$row['ecart_id']] = $row;
			$out[$row['ecart_id']]['ecart_currency_info'] = isset($allCurrency['all_currency'][$row['ecart_currency']]) ? $allCurrency['all_currency'][$row['ecart_currency']] : array();
			$out[$row['ecart_id']]['products'] = array();
			$out[$row['ecart_id']]['products_count'] = 0;
			$out[$row['ecart_id']]['user_info'] =  array();
			$out[$row['ecart_id']]['user_fields_values'] = array();

			$allCartsID[$row['ecart_id']] = $row['ecart_id'];
			$users[$row['ecart_user_key']] = $row['ecart_user_key'];
		}

		# получим товары
		$this->db->where_in('ecartp_cart_id', $allCartsID);
		$query = $this->db->get('ecart_products');
		foreach($query->result_array() as $row) {
			$out[$row['ecartp_cart_id']]['products'][$row['ecartp_object_id']] = $row;
			$out[$row['ecartp_cart_id']]['products'][$row['ecartp_object_id']]['object'] = array();
		}

		# получим пользоваетелей
		$this->db->select('
			users.users_id,
			users.users_name,
			users.users_site_key,
			users.users_login,
			users.users_group,
			users.users_email,
			users.users_phone,
			users.users_date_registr,
			users.users_lang,
			users.users_status,
			users_group.users_group_name,
			users_group.users_group_type,
			');
		$this->db->where_in('users_site_key', $users);
		$this->db->join('users_group', 'users.users_group = users_group.users_group_id');
		$query = $this->db->get('users');
		foreach($query->result_array() as $row) {
			$usersKeys[$row['users_site_key']] = $row;
		}

		# поля из форм
		$this->db->where_in('ecartfval_cart_id', $allCartsID);
		$query = $this->db->get('ecart_fields_values');
		foreach($query->result_array() as $row) {
			$out[$row['ecartfval_cart_id']]['user_fields_values'][$row['ecartfval_field_id']] = $row['ecartfval_value'];
		}

		# финишная обработка массива $out. Запись информации о пользователе, его полях формы и количества товаров в корзине
		foreach($out as $key => &$value) {
			$value['products_count'] = count($value['products']);
			if(isset($usersKeys[$value['ecart_user_key']])) {
				$value['user_info'] = $usersKeys[$value['ecart_user_key']];
			}
			$value['user_fields_values'] = $this->EmarketModel->EmarketOptionsModel->parseUserFields($value['user_fields_values']);

		}
		unset($value);
		return $out;
	}

}
