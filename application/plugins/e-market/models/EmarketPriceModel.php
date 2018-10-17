<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* EmarketPriceModel - модель Emarket для работы с ценами
	*
	* Version 1.0
	* UPD 2018-04-20
	* первая версия
	*
	* Version 1.01
	* UPD 2018-10-17
	* добавлены новые правила округления конечных цен
	*
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

	function __construct()
    {
        parent::__construct();
		$this->init();
    }

	function reset()
	{
		$this->productPrices = array();
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
			5 => 'Оклуглить до 10-ти',
			6 => 'Оклуглить до 50-ти',
			7 => 'Оклуглить до 100',
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

		$this->productPrices = array(
			'productCurrency' => $this->currProductsID,
			'currTransfer' => 0, // флаг, что цена - это перевод валюты
			'currTransferCost' => $rateCurrency, // курс перевода валюты
			'oldPrice' => 0,
			'price' => 0,
			'oldPriceArray' => array(),
			'priceArray' => array()
		);

		// если валюта сайта и валюта товара отличаются, то переводим в валюту
		if($this->currProductsID != $this->currSiteID) {
			$rateCurrency = (float)$this->currArray[$this->currProductsID]['ecartcur_rate'];
			$this->productPrices['currTransfer'] = 1;
			$this->productPrices['currTransferCost'] = $rateCurrency;
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

		// массив всех старых цен товара
		foreach($this->OldPricesFields as $keyGroup => $keyField) {
			if(isset($product['data_fields'][$keyField])) {
				$this->productPrices['oldPriceArray'][$keyGroup] = $product['data_fields'][$keyField]['objects_data_value'] * $rateCurrency;
			}
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

	# возвращает старую цену товара
	public function getOldPrice()
	{
		return $this->rulesPrice($this->productPrices['oldPrice']);
	}

	# возвращает массив цен для групп пользователей
	public function getPriceArray()
	{
		$out = array();
		foreach($this->productPrices['priceArray'] as $key => $value) {
			$out[$key] = $this->rulesPrice($value);
		}
		return $out;
	}

	# возвращает массив старых цен для групп пользователей
	public function getOldPriceArray()
	{
		$out = array();
		foreach($this->productPrices['oldPriceArray'] as $key => $value) {
			$out[$key] = $this->rulesPrice($value);
		}
		return $out;
	}

	# получить id валюты товара
	public function getCurrency()
	{
		return $this->productPrices['productCurrency'];
	}

	# возвращает флаг перевода валюты
	public function getCurTransfer()
	{
		return $this->productPrices['currTransfer'];
	}

	# возвращает курс перевода валюты
	public function getCurTransferCost()
	{
		return $this->productPrices['currTransferCost'];
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
		//5 => 'Оклуглить до 10-ти',
		//6 => 'Оклуглить до 50-ти',
		//7 => 'Оклуглить до 100',

		if($this->rulesPriceID == 2) {
			return ceil($price);
		} else if($this->rulesPriceID == 3) {
			$price = ceil($price * 100);
			return $price / 100;
		} else if($this->rulesPriceID == 4) {
			$price = ceil($price * 10);
			return $price / 10;
		} else if($this->rulesPriceID == 5 || $this->rulesPriceID == 6 || $this->rulesPriceID == 7){
			// если число дробное, то округляем его до целого
			$price = ceil($price);
			$rate = 10;
			if($this->rulesPriceID == 6) {
				$rate = 50;
			} else if($this->rulesPriceID == 7) {
				$rate = 100;
			}

			// разделим число на коэффициент, а результат округлим в большую сторону. Потом умножим число на округленный коэффициент
			$price = ceil($price / $rate);
			$price = $rate * $price;
			return $price;

		} else {
			return $price;
		}
	}
}
