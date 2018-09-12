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

	/*
	* метод возвращает корзину по Ajax
	*/
	public function carInfo()
	{
		$CI = &get_instance();
		$this->response['status'] = 'OK';
		$this->response['info'] = 'Complite';
		$this->response['cartInfo'] = $CI->EmarketModel->cartInfo(0, $this->post['cartType']);
		$this->renderResponse();
	}


	/*
	* метод добавления нового товара в корзину
	*/
	public function addToCart()
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
		$this->renderResponse();
	}

	/*
	* метод изменения количесва товара в корзине
	*/
	public function changeCountProduct()
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
		$this->renderResponse();
	}

	/*
	* метод удаления товара из корзины
	*/
	public function deleteProduct()
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
		$this->renderResponse();
	}


	private function renderResponse()
	{
		echo json_encode($this->response);
		//pr($this->response);
	}

}
