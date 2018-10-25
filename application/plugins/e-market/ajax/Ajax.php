<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  e-market Ajax class
	*
	* UPD 2018-05-17
	* version 1.0
	*
	* UPD 2018-09-07
	* version 2.0
	* правки и переработка логики
	*
	* UPD 2018-10-17
	* version 2.1
	* переделана логика. Добавлен метод _ajaxChangeDescriptionProduct() для изменения описания товара
	*
	* UPD 2018-10-25
	* version 2.2
	* Ошибка в методах - методы не должны гененрировать json-ответ
*/

class Ajax extends CI_Model {


	private $post, $get; // данные POST и GET
	private $response;

	function __construct($post, $get)
	{
		parent::__construct();
		$this->post = $post;
		$this->get = $get;
		$this->response = array('status' => 'ERROR', 'info' => '');

	}

	/*Принимает AJAX*/
	public function emarket()
	{
		try {
			if(!isset($this->get['action'])) {
				throw new Exception('Error response');
			}
			$method = str_replace('_', '-', $this->get['action']);
			$method = ucwords($method, '-');
			$method = '_ajax' . str_replace('-', '', $method);
			if(method_exists($this, $method)) {
				$this->$method();
			} else {
				throw new Exception('Emarket: method is not exists');
			}
			$this->renderResponse();
		} catch (Exception $e) {
			$this->renderResponse($e->getMessage());
		}
	}

	/*
	* метод возвращает корзину по Ajax
	*/
	private function _ajaxCarInfo()
	{
		$CI = &get_instance();
		$this->response['status'] = 'OK';
		$this->response['info'] = 'Complite';
		$this->response['cartInfo'] = $CI->EmarketModel->cartInfo(0, $this->post['cartType']);
	}


	/*
	* метод добавления нового товара в корзину
	*/
	private function _ajaxAddToCart()
	{
		$CI = &get_instance();
		// корзина - число.
		$this->post['cartID'] = $this->post['cartID'] * 1;
		if(!$this->post['cartID'] * 1) {
			$this->post['cartID'] = 0;
		}
		$res = $CI->EmarketModel->addToCart($this->post['productId'], $this->post['productCount'], '', $this->post['cartID'], $this->post['cartType']);
		if($res) {
			$this->response['status'] = 'OK';
			$this->response['info'] = 'Complite';
			$this->response['cartInfo'] = $CI->EmarketModel->cartInfo(0, $this->post['cartType']);
		} else {
			$this->response['info'] = 'Error';
			$this->response['cartInfo'] = array();
		}
	}

	/*
	* метод изменения количесва товара в корзине
	*/
	private function _ajaxChangeCountProduct()
	{
		$CI = &get_instance();

		$this->post['productCount'] = $this->post['productCount'] * 1;
		$this->post['cartID'] = $this->post['cartID'] * 1;

		if(!$this->post['productCount'] * 1) {
			$this->post['productCount'] = 1;
		}
		if(!$this->post['cartID'] * 1) {
			$this->post['cartID'] = 0;
		}

		$data = array('count' => $this->post['productCount']);

		$res = $CI->EmarketModel->editProduct($this->post['productId'], $data, $this->post['cartID'], $this->post['cartType']);
		if($res) {
			$this->response['status'] = 'OK';
			$this->response['info'] = 'Complite';
			$this->response['productEditID'] = $res;
			$this->response['cartInfo'] = $CI->EmarketModel->cartInfo(0, $this->post['cartType']);
		} else {
			$this->response['info'] = 'Error'; // количество товара не изменилось
		}
	}

	/*
	* метод изменения примечания в товара в корзине
	*/
	private function _ajaxChangeDescriptionProduct()
	{
		$CI = &get_instance();
		$cartID = 0;
		$cartType = 1;
		$productID = 0;

		// если не пришел товар
		if(!isset($this->post['productID'])) {
			throw new Exception('Product no id');
		}

		$productID = $this->post['productID'];

		// если есть запрос на корзину
		if(isset($this->post['cartID'])) {
			$cartID = $this->post['cartID'];
		}

		// если пришел тип корзины
		if(isset($this->post['cartType'])) {
			$cartType = $this->post['cartType'];
		}

		// загрузим корзину
		$cartInfo = $CI->EmarketModel->cartInfo($cartID, $cartType);

		if(!$cartInfo) {
			throw new Exception('Cart is not exists');
		}

		// если товара нет, прерываем
		if(!isset($cartInfo['products'][$productID])) {
			throw new Exception('Product is not exists');
		}

		$cartID = $cartInfo['ecart_id'];

		if($res = $CI->EmarketModel->editProduct($productID, array('descr' => strip_tags($this->post['description'])), $cartID, $cartType)) {
			$this->response['status'] = 'OK';
			$this->response['info'] = 'Описание сохранено';
			$this->response['product_id'] = $res;
			$this->response['cartInfo'] = $CI->EmarketModel->cartInfo($cartID);
		} else {
			$this->response['info'] = 'Ошибка изменения параметров';
		}
	}

	/*
	* метод удаления товара из корзины
	*/
	private function _ajaxDeleteProduct()
	{
		$CI = &get_instance();
		if(!$this->post['cartID'] * 1) {
			$this->post['cartID'] = 0;
		}

		$res = $CI->EmarketModel->deleteProduct($this->post['productId'], $this->post['cartID'], $this->post['cartType']);
		if($res) {
			$this->response['status'] = 'OK';
			$this->response['info'] = 'Complite';
			$this->response['productEditID'] = $res;
			$this->response['cartInfo'] = $CI->EmarketModel->cartInfo(0, $this->post['cartType']);
		} else {
			$this->response['info'] = 'Error'; // товар не удален из корзины
		}
	}


	private function renderResponse($info = '')
	{
		if($info) {
			$this->response['info'] = $info;
		}
		echo json_encode($this->response);
	}

}
