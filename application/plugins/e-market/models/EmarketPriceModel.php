<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* EmarketPriceModel - модель Emarket для работы с ценами

	* Version 1.0
	* UPD 2018-04-20
	* первая версия
*/

class EmarketPriceModel extends CI_Model {

	// ID группы пользователя
	public $userGroup;

	// массив ID полей цен товара по группа пользователей
	public $pricesFields = array();

	// массив ID полей старых цен товара по группа пользователей
	public $OldPricesFields = array();

	// информация о ценах товара
	public $productPrices;

	// id валюты сайта
	public $currSiteID;

	// id валюты товаров
	public $currProductsID;

	// Массив валюты
	public $currArray;

	// правила округления цен
	public $allRulesPrice;

	// сохраненное правило округления
	public $rulesPriceID;

	// очищать массив с ценами при загрузке новой цены
	//private $clearArray = FALSE;

	// ID последнего загруженного товара
	//public $lastObjID = 0;

	// массив цен товара array[PRODUCTID][GROUPID] = array(VALUES)
	//public $productPrices = array();



	// цена, по которой товар идет в корзину
	//public $priceToCart = 0;

	// коэффициент на перевод валюты
	//public $currDiff = 1;

	// массив ID полей с ценами
	//public $productPricesFields = array();

	// массив ID полей старых цен
	//public $productOldPricesFields = array();

	// Здесь хранятся курсы валют для групп пользователей
	//public $priceCurDataField = array();

	// здесь хранятся ID полей с наличием товара
	//public $productStock;





	function __construct()
    {
        parent::__construct();
		$this->init();
    }

	function reset()
	{
		$this->userGroupID = 0;
		$this->productPrices = array();
		//$this->priceToCart = 0;
		//$this->currDiff = 1;
		//$this->productPricesFields = array();
		$this->productOldPricesFields = array();
		$this->priceCurDataField = array();
		$this->productStock = array();
		$this->lastObjID = 0;
		$this->clearArray = FALSE;


		// init
		//$this->init();
	}

	# init
	public function init()
	{
		$this->pricesFields = $this->OldPricesFields = array('ALL' => 0);
		// массив полей с ценами
		if($tmp = app_get_option('product_price_field', 'e-market', 'ALL || 0')) {
			$tmp = explode('##', $tmp);
			foreach($tmp as $value) {
				if(!strpos($value, '||')) {
					continue;
				}
				$tmp = explode('||', $value);
				$this->pricesFields[trim($tmp[0])] = trim($tmp[1]);
			}
		}

		// массив полей со старыми ценами
		if($tmp = app_get_option('product_old_price_field', 'e-market', 'ALL || 0')) {
			$tmp = explode('##', $tmp);
			foreach($tmp as $value) {
				if(!strpos($value, '||')) {
					continue;
				}
				$tmp = explode('||', $value);
				$this->OldPricesFields[trim($tmp[0])] = trim($tmp[1]);
			}
		}

		# создание группы пользователя. Если в массиве сохраненных свойств цен нет текущей группы пользователя, то сбросим ее на "по-умолчанию"
		$this->userGroup = $this->session->userdata('group');
		if(!isset($this->pricesFields[$this->userGroup])) {
			$this->userGroup = 'ALL';
		}

		$curr = $this->getAllCurrency();

		$this->currSiteID = $curr['site_currency']['ecartcur_id'];
		$this->currProductsID = $curr['products_currency']['ecartcur_id'];
		$this->currArray = $curr['all_currency'];

		$this->allRulesPrice = array(
			1 => 'Не использовать правила',
			2 => 'Оклуглить в большую сторону до целого числа',
			3 => 'Оклуглить в большую сторону до сотых',
			4 => 'Оклуглить в большую сторону до десятых',
		);

		$this->rulesPriceID = app_get_option('price_rules', 'e-market', 1);

	}

	# загрузка товара
	public function initProduct($product = array())
	{
		if(!isset($product['data_fields'])){
			return false;
		}

		// множитель для цены для перевода курса валют
		$rateCurrency = 1;

		// ID data-поля, где хранится ID валюты товара
		$currDataID = app_get_option('currency_field_id', 'e-market', 0);

		// если поле задано и оно есть у товара, то установим его значение
		if(isset($product['data_fields'][$currDataID])) {
			$id = $product['data_fields'][$currDataID]['objects_data_value'];

			// проверим, есть ли такая валюта в системе
			if(isset($this->currArray[$id])) {
				$this->currProductsID = $id;
			}
			unset($id);
		}

		$this->productPrices = array('productCurrency' => $this->currProductsID, 'oldPrice' => 0, 'price' => 0, 'priceArray' => array());

		// если валюта сайта и валюта товара отличаются, то переводим в валюту
		if($this->currProductsID != $this->currSiteID) {
			$rateCurrency = $this->currArray[$this->currProductsID]['ecartcur_rate'];
		}

		$userPriceID = $this->pricesFields[$this->userGroup];
		if(isset($product['data_fields'][$userPriceID])) {
			$this->productPrices['price'] = $product['data_fields'][$userPriceID]['objects_data_value'] * $rateCurrency;
		}
		// массив всех текущих цен товара
		foreach($this->pricesFields as $keyGroup => $keyField) {
			if(isset($product['data_fields'][$keyField])) {
				$this->productPrices['priceArray'][$keyGroup] = $product['data_fields'][$keyField]['objects_data_value'] * $rateCurrency;
			}
		}

		$userOldPriceID = 0;
		if(isset($this->OldPricesFields[$this->userGroup])) {
			$userOldPriceID = $this->OldPricesFields[$this->userGroup];
		} else {
			$userOldPriceID = $this->OldPricesFields['ALL'];
		}

		if(isset($product['data_fields'][$userOldPriceID])) {
			$this->productPrices['oldPrice'] = $product['data_fields'][$userOldPriceID]['objects_data_value'] * $rateCurrency;
		}

		# проверка на старые цены. Если старая цена равна текщей, то старую обнуляем
		if($this->productPrices['price'] >= $this->productPrices['oldPrice']) {
			$this->productPrices['oldPrice'] = 0;
		}

		return $this->productPrices;
	}

	# возвращает цену товара
	public function getPrice()
	{
		return $this->rulesPrice($this->productPrices['price']);
	}

	# вовзращает старую цену товара
	public function getOldPrice()
	{
		return $this->rulesPrice($this->productPrices['oldPrice']);
	}

	# возвращает старую цену товара
	public function getPriceArray()
	{
		$out = array();
		foreach($this->productPrices['priceArray'] as $key => $value) {
			$out[$key] = $this->rulesPrice($value);
		}
		return $out;
	}

	# получить информацию о наличии
	/*public function getStock($objID = 0, $groupID = 0)
	{
		if(!$objID)
			$objID = $this->lastObjID;

		if(!$groupID)
			$groupID = $this->userGroupID;

		if(isset($this->productPrices[$objID][$groupID]['STOCK'])){
			return $this->productPrices[$objID][$groupID]['STOCK'];
		}else{
			return 0;
		}
	}*/

	# получить id валюты товара
	public function getCurrency()
	{
		return $this->productPrices['productCurrency'];
	}

	#######
	/*
	* получить курсы валют
	*/
	public function getAllCurrency()
	{
		$out = array();
		$query = $this->db->get('ecart_currency');
		if($query->num_rows()) {
			foreach($query->result_array() as $row) {
				if($row['ecartcur_site']) {
					$out['site_currency'] = $row;
				}

				if($row['ecartcur_products']) {
					$out['products_currency'] = $row;
				}

				$out['all_currency'][$row['ecartcur_id']] = $row;
			}
		}

		return $out;
	}


	# формирует цену по правилам
	public function rulesPrice($price = 0) {
		if(!$price) {
			return 0;
		}

		// 1 => 'Не использовать правила',
		// 2 => 'Оклуглить в большую сторону до целого числа',
		// 3 => 'Оклуглить в большую сторону до сотых',
		// 4 => 'Оклуглить в большую сторону до десятых',

		if($this->rulesPriceID == 2) {
			return ceil($price);
		} else if($this->rulesPriceID == 3) {
			$price = ceil($price * 100);
			return $price / 100;
		} else if($this->rulesPriceID == 4) {
			$price = ceil($price * 10);
			return $price / 10;
		} else {
			return $price;
		}
	}

	###########################
	###########################
	###########################
	###########################
	###########################
	###########################
	###########################
	###########################

	/*
	* формирование скидки
	*/
	public function createSaleDiff($productID, $dataFields){
		return 1;
	}

	/*
	* формирование информации о курсе валют для товара
	*/
	public function productCurrencyInfo($productID, $dataFields){
		# если заданное значение DATA-поля есть у товара, то ориентируемся на него
		if(isset($dataFields[$this->EmarketOptionsModel->currencyField])) {

		} else {
			# поля такого у товара нет, поэтому бурем по-умолчанию
			//$this->productPrices[$productID][$this->userGroupID]['CURRENCY_INFO'] = $this->productsCurrency
		}

		return;

		if(isset($this->EmarketOptionsModel->allCurrency[$this->EmarketOptionsModel->currencyField])) {
			$this->productPrices[$productID][$this->userGroupID]['CURRENCY_INFO'] = $this->EmarketOptionsModel->allCurrency[$this->EmarketOptionsModel->currencyField];
		}

		pr($this->EmarketOptionsModel->currencyField);
		pr($this->userGroupID);
		pr($this->EmarketOptionsModel->allCurrency);

		return 1;

		$this->productPrices;
	}

	# получить цену товара





}
