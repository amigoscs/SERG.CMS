<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  библиотека EmarketViewsLib
	* Содержит методы для отображения корзины, формы и прочего

	* UPD 2018-05-15
	* version 0.1

*/

class EmarketViewsLib {

	public $viewsPath;
	public $h1;

	public function __construct()
	{
		$this->h1 = '';
		$this->viewsPath = APP_PLUGINS_DIR_PATH . 'e-market/';

	}

	# корзины товаров - отображение
	// $complite - передайте сюда true, когда заказ успешно оформлен
	public function renderCartTable($cartInfo = array())
	{
		$CI = &get_instance();
		$data = array('userCart' => $cartInfo);

		# фозможно в шаблоне есть кастомный файла
		$templateFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/plugins/e-market/cart-main-page.php';
		if(file_exists($templateFile)) {
			$content = $CI->load->view($CI->viewsTemplatePath . '/plugins/e-market/cart-main-page', $data, true);
		} else {
			$CI->load->add_package_path($this->viewsPath);
			$content = $CI->load->view('frontend/cart-main-page', $data, true);
			$CI->load->remove_package_path($this->viewsPath);
		}
		return $content;
	}

	/*
	* генерация формы заказа
	*/
	public function renderCartForm($render = true)
	{
		$CI = &get_instance();

		if(!$render) {
			return '';
		}

		$data = array(
			'formAction' => '',
			'formFields' => $CI->EmarketModel->EmarketOptionsModel->getFieldsFormCart(),
			'cartfield' => $CI->input->post('cartfield'),
		);

		# фозможно в шаблоне есть кастомный файла
		$templateFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/plugins/e-market/cart-user-form.php';
		if(file_exists($templateFile)) {
			$content = $CI->load->view($CI->viewsTemplatePath . '/plugins/e-market/cart-user-form', $data, true);
		} else {
			$CI->load->add_package_path($this->viewsPath);
			$content = $CI->load->view('frontend/cart-user-form', $data, true);
			$CI->load->remove_package_path($this->viewsPath);
		}
		return $content;
	}

	/*
	* генерация письма о новом заказе ДЛЯ АДМИНА
	*/
	public function renderOrderMailAdmin($cart = array())
	{
		$CI = &get_instance();
		$data = array(
			'userCart' => $cart
		);

		# фозможно в шаблоне есть кастомный файла
		$templateFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/plugins/e-market/email-new-order-admin.php';
		if(file_exists($templateFile)) {
			$content = $CI->load->view($CI->viewsTemplatePath . '/plugins/e-market/email-new-order-admin', $data, true);
		} else {
			$CI->load->add_package_path($this->viewsPath);
			$content = $CI->load->view('email/email-new-order-admin', $data, true);
			$CI->load->remove_package_path($this->viewsPath);
		}
		return $content;
	}

	/*
	* генерация письма о новом заказе ДЛЯ ПОКУПАТЕЛЯ
	*/
	public function renderOrderMailUser($cart = array())
	{
		$CI = &get_instance();
		$data = array(
			'userCart' => $cart
		);

		# фозможно в шаблоне есть кастомный файла
		$templateFile = APP_SITE_TEMPLATES_PATH . APP_SITE_TEMPLATE . '/plugins/e-market/email-new-order-user.php';
		if(file_exists($templateFile)) {
			$content = $CI->load->view($CI->viewsTemplatePath . '/plugins/e-market/email-new-order-user', $data, true);
		} else {
			$CI->load->add_package_path($this->viewsPath);
			$content = $CI->load->view('email/email-new-order-user', $data, true);
			$CI->load->remove_package_path($this->viewsPath);
		}
		return $content;
	}


}
