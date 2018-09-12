<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* emarketModel
	*
	* UPD 2018-04-17
	* Version 1.0

*/

class EmarketModel extends CI_Model
{
	public $userKey, $userCarts, $SKUkeyField;
	// класс цен
	public $EmarketPriceModel;
	// класс опций
	public $EmarketOptionsModel;
	// класс оформления закза
	public $EmarketOrderModel;
	// библиотека шаблонов
	public $EmarketViewsLib;
	// загруженный товар
	private $PRODUCT;


	function __construct()
    {
        parent::__construct();
		$this->init();
    }

	/*
	* сброс параметров

	public function reset()
	{
		$this->userKey = $this->session->userdata('user_key');
		//$this->userGroupID = $this->session->userdata('group');

		$this->userCarts = array();


		$this->init();
	}*/


	public function init()
	{
		$this->userKey = $this->session->userdata('user_key');
		$this->userCarts = array();
		$this->PRODUCT = array();

		$this->SKUkeyField = app_get_option('product_code_field', 'e-market', '0');

		$pathFile = __DIR__ . '/EmarketPriceModel.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->EmarketPriceModel = new EmarketPriceModel();
		}

		$pathFile = __DIR__ . '/EmarketOptionsModel.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->EmarketOptionsModel = new EmarketOptionsModel();
		}

		$pathFile = __DIR__ . '/EmarketOrderModel.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->EmarketOrderModel = new EmarketOrderModel();
		}

		$pathFile = APP_PLUGINS_DIR_PATH . 'e-market/libraries/EmarketViewsLib.php';
		if(file_exists($pathFile)) {
			require_once($pathFile);
			$this->EmarketViewsLib = new EmarketViewsLib();
		}


		/*if(isset($this->EmarketOptionsModel->pricesFields[$userGroup]) && $this->EmarketOptionsModel->pricesFields[$userGroup]) {
			$this->userGroupID = $userGroup;
		} else {
			$this->userGroupID = 'ALL';
		}*/
		//pr($pathFile);
		//$this->priceModel
	}

	/*
	* загрузка товара и обработка цен его
	*/
	public function initProduct($product = array())
	{
		if(!isset($product['data_fields'])){
			return false;
		}
		// название товара
		$this->PRODUCT['name'] = $product['obj_name'];
		// изображение товара app_get_option('product_image_field', 'e-market', 0);
		$image = isset($product['data_fields'][app_get_option('product_image_field', 'e-market', 0)])
			? $product['data_fields'][app_get_option('product_image_field', 'e-market', 0)]['objects_data_value'] : '';
		// очистим от elfinder
		$image = str_replace(array('##', '_ELF_'), '__EXPLODE__', $image);
		if(strpos($image, '__EXPLODE__')) {
			$image = explode('__EXPLODE__', $image);
			$image = $image[0];
		}

		$this->PRODUCT['image'] = $image;
		// SKU товара
		$this->PRODUCT['SKU'] = isset($product['data_fields'][$this->SKUkeyField]) ? $product['data_fields'][$this->SKUkeyField]['objects_data_value'] : 0;
		// старая цена товара
		$this->PRODUCT['oldPrice'] = 0;
		// текущая цена товара
		$this->PRODUCT['price'] = 0;
		// валюта товара
		$this->PRODUCT['productCurrency'] = 0;
		// доступность к заказу
		$this->PRODUCT['inStock'] = 0;
		// значение поля НАЛИЧИЕ
		$this->PRODUCT['inStockValue'] = 0;
		// вычисленная группа текущего пользователя
		$this->PRODUCT['userGroup'] = '';
		// массив всех цен товара
		$this->PRODUCT['priceArray'] = array();
		// массив значений поля НАЛИЧИЕ
		$this->PRODUCT['inStockValueArray'] = array();


		$this->EmarketPriceModel->initProduct($product);

		$this->PRODUCT['productCurrency'] = $this->EmarketPriceModel->getCurrency();
		$this->PRODUCT['price'] = $this->EmarketPriceModel->getPrice();
		$this->PRODUCT['oldPrice'] = $this->EmarketPriceModel->getOldPrice();
		$this->PRODUCT['priceArray'] = $this->EmarketPriceModel->getPriceArray();
		$this->PRODUCT['userGroup'] = $this->EmarketPriceModel->userGroup;


		// массив полей с информацией о доступности товара к заказу
		$dataFieldStockInfoArray = array();
		if($tmp = app_get_option('product_stock', 'e-market', 'ALL || 0')) {
			$tmp = explode('##', $tmp);
			foreach($tmp as $value) {
				if(!strpos($value, '||')) {
					continue;
				}
				$tmp = explode('||', $value);
				$dataFieldStockInfoArray[trim($tmp[0])] = trim($tmp[1]);
			}
		}

		$stockFieldID = 0;
		if(isset($dataFieldStockInfoArray[$this->PRODUCT['userGroup']])) {
			$stockFieldID = $dataFieldStockInfoArray[$this->PRODUCT['userGroup']];
		}

		if(isset($product['data_fields'][$stockFieldID]))
		{
			$this->PRODUCT['inStockValue'] = $product['data_fields'][$stockFieldID]['objects_data_value'];
			if(function_exists("emarket_create_stock")) {
				$this->PRODUCT['inStock'] = emarket_create_stock($product['data_fields'][$stockFieldID]['objects_data_value'], $this->EmarketPriceModel->userGroup);
			}
		}

		# пропишем массив значений наличия для всех групп
		foreach($dataFieldStockInfoArray as $key => $value) {
			if(isset($product['data_fields'][$value])) {
				$this->PRODUCT['inStockValueArray'][$key] = $product['data_fields'][$value]['objects_data_value'];
			}
		}
		
		return $this->PRODUCT;
	}


	/*
	* возвращает информацию о текущей корзине
	* если нет ID корзины, то поиск ведется по ее типу и userKey. Статус корзины для поиска - НАПОЛНЯЕТСЯ
	*/
	public function cartInfo($cartID = 0, $typeID = 0)
	{
		// если нет типа - то тип - КОРЗИНА ТОВАРОВ
		if(!$typeID) {
			$typeID = 1;
		}

		if(!$cartID) {
			$cartID = $this->getCartFromType($typeID);
		}

		if(!$cartID) {
			return false;
		}

		// если корзина уже загружена, то вернем ее
		if(isset($this->userCarts[$cartID])) {
			return $this->userCarts[$cartID];
		}

		# получим корзину
		$this->db->where('ecart_id', $cartID);
		$query = $this->db->get('ecart');
		if(!$query->num_rows()) {
			return false;
		}

		$this->userCarts[$cartID] = $query->row_array();

		// дополнительные поля
		//$this->userCarts[$cartID]['ecart_total_price'] = 0;
		$this->userCarts[$cartID]['ecart_currency_info'] = array();
		$this->userCarts[$cartID]['products_count'] = 0;
		$this->userCarts[$cartID]['products'] = array();
		$this->userCarts[$cartID]['user_info'] =  array();
		$this->userCarts[$cartID]['user_fields_values'] = array();

		# если валюта не указана, то загрузим по-умолчанию
		$this->userCarts[$cartID]['ecart_currency'] ? $this->db->where('ecartcur_id', $this->userCarts[$cartID]['ecart_currency']) : $this->db->where('ecartcur_site', 1);
		$query = $this->db->get('ecart_currency');
		$this->userCarts[$cartID]['ecart_currency'] = $query->row('ecartcur_id');
		$this->userCarts[$cartID]['ecart_currency_info']['ecartcur_id'] = $query->row('ecartcur_id');
		$this->userCarts[$cartID]['ecart_currency_info']['ecartcur_name'] = $query->row('ecartcur_name');
		$this->userCarts[$cartID]['ecart_currency_info']['ecartcur_code'] = $query->row('ecartcur_code');
		$this->userCarts[$cartID]['ecart_currency_info']['ecartcur_rate'] = $query->row('ecartcur_rate');

		# пользователь корзины
		$this->db->select('
			users.users_id,
			users.users_name,
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
		$this->db->where('users_site_key', $this->userCarts[$cartID]['ecart_user_key']);
		$this->db->join('users_group', 'users.users_group = users_group.users_group_id');
		$query = $this->db->get('users');
		if($query->num_rows()) {
			$this->userCarts[$cartID]['user_info'] = $query->row_array();
		}

		// пользовательские поля
		$userFields = array();
		$this->db->where('ecartfval_cart_id', $cartID);
		$query = $this->db->get('ecart_fields_values');
		foreach($query->result_array() as $row) {
			$userFields[$row['ecartfval_field_id']] = $row['ecartfval_value'];
		}
		if($userFields) {
			$this->userCarts[$cartID]['user_fields_values'] = $this->EmarketModel->EmarketOptionsModel->parseUserFields($userFields);
		}

		# Товары
		$loadObjects = array();
		$this->db->where('ecartp_cart_id', $cartID);
		$query = $this->db->get('ecart_products');
		if($query->num_rows())
		{
			$this->userCarts[$cartID]['products_count'] = $query->num_rows();
			foreach($query->result_array() as $row) {
				$loadObjects[$row['ecartp_object_id']] = $row['ecartp_object_id'];
				$this->userCarts[$cartID]['products'][$row['ecartp_object_id']] = $row;
				$this->userCarts[$cartID]['products'][$row['ecartp_object_id']]['total_price'] = 0;
				$this->userCarts[$cartID]['products'][$row['ecartp_object_id']]['object'] = array();
			}


			$objects = $this->_loadObjects($loadObjects, $cartID);
			$deleteObjects = array();

			// корзина новая (наполняется)
			$cartIsNew = false;
			if($this->userCarts[$cartID]['ecart_status'] == 1) {
				$cartIsNew = true;
			}

			# подключем к корзине загруженные товары, а также пропишем текущую стоимость корзины
			$totalPriceArray = array();
			foreach($loadObjects as $objectID) {
				$priceCart = $this->userCarts[$cartID]['ecart_summ'];
				$priceProduct = $this->userCarts[$cartID]['products'][$objectID]['ecartp_price'];
				$countProduct = $this->userCarts[$cartID]['products'][$objectID]['ecartp_count'];

				# если объект загружен, то все норм. Иначе отправляем на удаление из корзины
				if(isset($objects[$objectID])) {
					$this->userCarts[$cartID]['products'][$objectID]['object'] = $objects[$objectID];

					# если корзина наполняется, то пишем текущие цены товара
					if($cartIsNew) {
						$priceInfo = $this->EmarketPriceModel->initProduct($objects[$objectID]);
						$priceProduct = $priceInfo['price'];
					}

					$this->userCarts[$cartID]['products'][$objectID]['total_price'] = $priceProduct * $countProduct;

				} else {
					$deleteObjects[$objectID] = $objectID;
				}

				$this->userCarts[$cartID]['products'][$objectID]['ecartp_price'] = $priceProduct;
				$totalPriceArray[] = $priceProduct * $countProduct;
			}

			// если корзина наполняется, то стоимость корзины берем рассчитаную на лету
			if($cartIsNew) {
				$this->userCarts[$cartID]['ecart_summ'] = array_sum($totalPriceArray);
			}


			# если в списке на удаление есть товары, значит они недоступны для пользователя (удалены или скрыты)
			# удалим это товары из корзины.
			# ВАЖНО! Удалять автоматически товар только из корзин со статусом НАПОЛНЯЕТСЯ (ecart_status == 1)
			if($deleteObjects && $cartIsNew) {
				foreach($deleteObjects as $objectID) {
					$this->db->where('ecartp_cart_id', $cartID);
					$this->db->where('ecartp_object_id', $objectID);
					$this->db->delete('ecart_products');
					if(isset($this->userCarts[$cartID]['products'][$objectID])) {
						unset($this->userCarts[$cartID]['products'][$objectID]);
					}
				}
			}

		}

		//pr($this->userCarts[$cartID]);
		return $this->userCarts[$cartID];

		return $cartID;


	}

	/*
	* возвращает информацию о текущей корзине по типу
	* Вернет только наполняющуюся
	* @return CartID
	*/
	public function getCartFromType($typeID = 0)
	{
		if(!$typeID) {
			return 0;
		}
		$this->db->select('ecart_id');
		$this->db->where('ecart_user_key', $this->userKey);
		$this->db->where('ecart_type', $typeID);
		$this->db->where('ecart_status', 1);
		$query = $this->db->get('ecart');
		if($query->num_rows()) {
			return $query->row('ecart_id');
		} else {
			return 0;
			// если корзины не существует, то возвращает 0
			//return $this->createCart($typeID);
			$currency = $this->EmarketPriceModel->getAllCurrency();
			$dateCreateCart = date('Y-m-d H:i:s');
			return array(
				'ecart_id' => 0,
				'ecart_type' => $typeID,
				'ecart_user_key' => $this->userKey,
				'ecart_currency' => '1',
				'ecart_date_create' => $dateCreateCart,
				'ecart_date_order' => $dateCreateCart,
				'ecart_last_mod' => time(),
				'ecart_ip_create' => '',
				'ecart_ip_mod' => '',
				'ecart_num' => '',
				'ecart_summ' => '',
				'ecart_status' => 1,
				'ecart_cash_status' => 0,
				'ecart_currency_info' => array(),
				'products' => array(),
				'user_info' => array(),
				'user_fields_values' => array(),
			);
		}
	}

	/*
	* создает корзину для пользователя
	* @return CartID
	*/
	public function createCart($typeID)
	{
		$timeNow = date('Y-m-d H:i:s');
		$data = array(
				'ecart_type' => $typeID,
				'ecart_user_key' => $this->userKey,
				'ecart_currency' => 0, // валюту записываем только при создании заказа
				'ecart_date_create' => $timeNow,
				'ecart_date_order' => $timeNow,
				'ecart_last_mod' => time(),
				'ecart_ip_create' => $this->input->ip_address(),
				'ecart_ip_mod' => $this->input->ip_address(),
				'ecart_num' => 0,
				'ecart_summ' => 0,
				'ecart_status' => 1,
				'ecart_cash_status' => 1, // статус НЕ ОПЛАЧЕНО (2 - оплачено)
		);
		$this->db->insert('ecart', $data);
		return $this->db->insert_id();
	}

	/*
	* загружает объекты по ID
	* @return array
	*/
	private function _loadObjects($objectsID = array(), $cartID)
	{
		$this->ObjectAdmModel->reset();
		$this->ObjectAdmModel->onlyObjectsID = $objectsID;
		$this->ObjectAdmModel->limit = 99999;
		$result = $this->ObjectAdmModel->getChildsObjects();
		$out = array();
		foreach($result as $value) {
			$out[$value['obj_id']] = $value;
		}
		return $out;
	}

	/*
	* возвращает товары в корзине
	* @return array
	*/
	public function getProducts($cartID = 0, $typeCart = 0)
	{
		$cart = $this->cartInfo($cartID, $typeCart);
		if($cart && isset($cart['products'])) {
			return $cart['products'];
		} else {
			return array();
		}
	}

	/*
	* возвращает информацию о товаре, если он есть в корзине
	* @return array
	*/
	public function getProductInfo($productID = 0, $cartID = 0, $typeCart = 0)
	{
		$products = $this->getProducts($cartID, $typeCart);
		if(isset($products[$productID])) {
			return $products[$productID];
		} else {
			return array();
		}
	}


	/*
	* добавляет товар в корзину
	* Если корзина для пользователя не создана в БД, то создает ее.
	$productID - id товара (объекта)
	$productCount - количество товара
	$productDescription - описание
	$typeCart - тип корзины (корзина (1), лист желаний (2), список сравнения (3))
	$cartID - id корзины. Если есть, то cartType не учитывается
	*
	* @return array
	*/
	public function addToCart($productID = 0, $productCount = 1, $productDescription = '', $cartID = 0, $typeCart = 0)
	{
		// проверка на существование такого товара
		$this->db->where('obj_id', $productID);
		$query = $this->db->get('objects');
		if($product = $query->row_array())
		{
			if(!$cart = $this->cartInfo($cartID, $typeCart)) {
				// корзины нет. Пытаемся создать ее.
				// ВАЖНО! Создавать следует, только если нет cartID
				if(!$cartID) {
					if($cartID = $this->createCart($typeCart)) {
						// если корзины создалась, вызовем снова текущий метод
						return $this->addToCart($productID, $productCount, $productDescription, $cartID);
					}
				}
				return false;
			}

			$cartID = $cart['ecart_id'];

			$productCount = $productCount * 1;
			if(!$productCount) {
				$productCount = 1;
			}

			// проверка на наличие товара в корзине. Если он есть, то просто перезаписываем количество
			if(isset($cart['products'][$productID])) {
				$this->editProduct($productID, array('count' => $productCount), $cartID);
				return $cart['products'][$productID]['ecartp_id'];
			}

			$data = array(
				'ecartp_cart_id' => $cartID,
				'ecartp_object_id' => $productID,
				'ecartp_object_sku' => '', // заполняется при оформлении заказа (изменение статуса корзины с 1 на другой)
				//'ecartp_object_currency' => 1,
				'ecartp_object_name' => $product['obj_name'],
				'ecartp_price' => 0, // цена фиксируется при оформлении заказа (изменение статуса корзины с 1 на другой)
				'ecartp_count' => $productCount,
				'ecartp_descr' => $productDescription,
			);

			if($this->db->insert('ecart_products', $data)) {
				if(isset($this->userCarts[$cartID])) {
					unset($this->userCarts[$cartID]);
				}
				return $this->db->insert_id();
			} else {
				return false;
			}
		}
		else
		{
			//$this->log('Item does not exist');
			return false;
		}
	}

	/*
	* удаляет товар из корзины
	*/
	public function deleteProduct($productID = 0, $cartID = 0, $typeCart = 0)
	{
		if(!$cart = $this->cartInfo($cartID, $typeCart)) {
			return false;
		}

		// проверка на существование такого товара
		if(!isset($cart['products'][$productID])) {
			return false;
		}

		$cartID = $cart['ecart_id'];
		$rowID = $cart['products'][$productID]['ecartp_id'];

		$this->db->where('ecartp_id', $rowID);
		if($this->db->delete('ecart_products')) {
			if(isset($this->userCarts[$cartID])) {
				unset($this->userCarts[$cartID]);
			}
			return $productID;
		} else {
			return false;
		}
	}

	/*
	* изменяет значение у товара в корзине
	*/
	public function editProduct($productID = 0, $values = array(), $cartID = 0, $typeCart = 0)
	{
		if(!$cart = $this->cartInfo($cartID, $typeCart)) {
			return false;
		}

		if(!isset($cart['products'][$productID])) {
			return false;
		}

		$cartID = $cart['ecart_id'];
		$rowID = $cart['products'][$productID]['ecartp_id'];

		$data = array();
		isset($values['object_sku']) ? $data['ecartp_object_sku'] = $values['object_sku'] : 0;
		isset($values['object_name']) ? $data['ecartp_object_name'] = $values['object_name'] : 0;
		isset($values['price']) ? $data['ecartp_price'] = $values['price'] : 0;
		isset($values['count']) ? $data['ecartp_count'] = $values['count'] : 0;
		isset($values['descr']) ? $data['ecartp_descr'] = $values['descr'] : 0;

		if($data) {
			$this->db->where('ecartp_id', $rowID);
			if($this->db->update('ecart_products', $data)) {
				if(isset($this->userCarts[$cartID])) {
					unset($this->userCarts[$cartID]);
				}
				return $productID;
			} else {
				return false;
			}

		} else {
			return false;
		}
	}
}
