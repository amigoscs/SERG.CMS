<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
	*
	* Контроллер регистрации и авторизации
	* version 0.21
	* UPD 2017-10-23
	*
	* UPD 2017-12-12
	* version 0.22
	* правка редиректа
	*
	* UPD 2018-02-15
	* version 0.3
	* добавлена обработка запрета не регистрацию. Настройка отправки писем о регистрации
	*
	* UPD 2018-09-04
	* version 0.4
	* обработка xss. мелкие правки
	*
 */

class Login extends CI_Controller {

	private $redirectUrl;

	public function __construct()
	{
		parent::__construct();

		define("APP_SITE_TEMPLATE", app_get_option('site_template', 'site', 'default'));
		define("APP_SITE_404_TEMPLATE", app_get_option('not_found_template', 'site', ''));

		$this->lang->load('login', app_get_option('site_lang', 'site', 'russian'));

		if($redirect = $this->input->post('redirect')) {
			$this->redirectUrl = $redirect;
		} elseif($redirect = $this->input->get('redirect')) {
			$this->redirectUrl = $redirect;
		} else {
			$this->redirectUrl = '';
		}
	}

	public function _remap($method, $args = array())
	{
		# залогиненого пользователя редиректим, если не разлогинивание или изменение пароля
		if($method != 'logout' and $method != 'change_pass' and $this->session->userdata('id'))
		{
			if($this->session->userdata('group') == '2') {
				redirect(info('baseUrl') . 'admin', 'refresh');
			} else {
				redirect(info('baseUrl'), 'refresh');
			}
		}

		$page = 'page_' . $method;
		if(method_exists($this, $page)) {
			return $this->$page($args);
		} else {
			redirect(info('baseUrl'), 'refresh');
		}
	}

	private function page_index()
	{
		$data = array();
		$remember = false;

		$this->data['PAGE_TITLE'] = app_lang('ENTER_SITE_TEXT');
		$this->data['PAGE_DESCRIPTION'] = $this->data['PAGE_TITLE'];
		$this->data['PAGE_KEYWORDS'] = $this->data['PAGE_TITLE'];

		$this->data['error'] = false;
		$this->data['message'] = '';
		$this->data['redirect'] = $this->redirectUrl;
		# post
		if($post = $this->input->post())
		{
			$post = $this->input->post(array('login_name', 'login_password', 'remember'));
			$login = $post['login_name'];
			$pass = $post['login_password'];

			if($post['remember']) {
				$remember = true;
			}

			$res = $this->LoginAdmModel->login($login, $pass, 0, $remember);

			if(!$res) {
				$this->data['error'] = true;
				if($res === FALSE) {
					$this->data['message'] = app_lang('INFO_ERROR_ATTEMPTS_EXC');
				} else {
					$this->data['message'] = app_lang('INFO_ERROR_PASS_LOGIN');
				}

				return $this->_render_page();

			} else {
				# обновим время визита
				$this->LoginAdmModel->updateUser($res['id'], array('last_visit' => time()));

				# редирект если все успешно. для админов редирект в панель управления
				if($this->redirectUrl) {
					redirect($this->redirectUrl, 'refresh');
				} else {
					if($res['group'] == 2){
						redirect(info('base_url') . 'admin/', 'refresh');
					}else{
						redirect(info('base_url'), 'refresh');
					}
				}

				return;
			}
		}

		return $this->_render_page();
	}


	/**
	* page_logout
	*
	* разлогинивание
	*
	* @return	string
	*/
	private function page_logout($redirect = '')
	{
		$this->session->sess_destroy();
		delete_cookie('user_login');
		delete_cookie('user_pass');
		delete_cookie('user_key');
		redirect($this->redirectUrl, 'refresh');
	}

	/**
	* page_registration
	*
	* регистрация
	*
	* @return	string
	*/
	private function page_registration($args = array())
	{
		$this->data['PAGE_TITLE'] = app_lang('REGISTER_SITE_TEXT');
		$this->data['PAGE_DESCRIPTION'] = $this->data['PAGE_TITLE'];
		$this->data['PAGE_KEYWORDS'] = $this->data['PAGE_TITLE'];

		$this->data['error'] = false;
		$this->data['message'] = '';

		$this->data['name'] = '';
		$this->data['login'] = '';
		$this->data['password'] = '';
		$this->data['password_retry'] = '';
		$this->data['email'] = '';

		# если регистрация запрещена, то заканчиваем работу
		if(!app_get_option('register_allowed', 'general', 0))
		{
			echo '<h1>' . app_lang('REGISTRATION_FORBIDDEN') . '</h1>';
			//$this->_render_page('login-register');
			return;
		}

		# если есть аргументы, значит подтверждение регистрации
		if($this->input->get('confirm'))
		{
			$userID = $this->input->get('user');
			$userKey = $this->input->get('confirmkey');
			if(!$user = $this->LoginAdmModel->checkUserActivate($userID, $userKey)){
				exit('ERROR');
			}

			if($user['users_activate'] == '0') {
				@$this->LoginAdmModel->updateUser($user['users_id'], array('activate' => '1'));
			}

			return $this->_render_page('login-register-activate');
		}
		else
		{
			# post
			if($post = $this->input->post('register', true))
			{
				$this->data['name'] = $post['name'];
				$this->data['login'] = $post['login'];
				$this->data['password'] = $post['password'];
				$this->data['password_retry'] = $post['password_retry'];
				$this->data['email'] = $post['email'];

				if(
					!$this->data['name'] OR
					!$this->data['login'] OR
					!$this->data['password'] OR
					!$this->data['password_retry'] OR
					!$this->data['email']
					)
				{
					$this->data['message'] = app_lang('INFO_EMPTY_FIELDS');
					$this->data['error'] = true;

				}
				elseif(mb_strlen($this->data['password']) < 6)
				{
					$this->data['message'] = app_lang('INFO_SHORT_PASS');
					$this->data['error'] = true;
				}
				elseif($this->data['password'] !== $this->data['password_retry'])
				{
					$this->data['message'] = app_lang('INFO_PASS_NOT_MATCH');
					$this->data['error'] = true;
				}
				elseif($this->data['login'] == 'admin')
				{
					$this->data['message'] = app_lang('INFO_ERROR_LOGIN');
					$this->data['error'] = true;
					$this->data['login'] = '';
				}
				elseif($this->LoginAdmModel->checkLoginUsers($this->data['login']))
				{
					$this->data['message'] = app_lang('INFO_ERROR_LOGIN');
					$this->data['error'] = true;
					$this->data['login'] = '';
				}
				elseif(!app_valid_email($this->data['email']))
				{
					$this->data['message'] = app_lang('INFO_ERROR_EMAIL');
					$this->data['error'] = true;
				}
				elseif($this->LoginAdmModel->checkEmailUser($this->data['email']))
				{
					$this->data['message'] = app_lang('INFO_ERROR_EMAIL_REG');
					$this->data['error'] = true;
					$this->data['email'] = '';
				}
				else
				{
					$args['name'] = $this->data['name'];
					$args['email'] = $this->data['email'];
					$args['login'] = $this->data['login'];
					$args['password'] = $this->data['password'];

					$stopRegister = false;

					foreach($args as $value) {
						$xss = str_replace(array('"', '<', '>', 'href', 'src'), '_XSS_', $value);
						if(strpos($xss, '_XSS_') !== false) {
							$stopRegister = true;
						}
					}

					if(!$stopRegister) {
						if($registerParams = $this->LoginAdmModel->insertUser($args)) {
							# ОТПРАВКА ПИСЕМ
							$this->LoginAdmModel->loginSendMail($registerParams);
							return $this->_render_page('login-register-complite');
						} else {
							$this->data['message'] = app_lang('INFO_ERROR');
							$this->data['error'] = true;
						}
					} else {
						$this->data = array_map("htmlspecialchars", $this->data);
						$this->data['message'] = app_lang('INFO_ERROR_DATA');
						$this->data['error'] = true;
					}
				}
			}

			$this->_render_page('login-register');
		}
	}

	/**
	* page_forgot
	*
	* восстановление забытого пароля
	*
	* @return	void
	*/
	private function page_forgot($args = array())
	{
		$this->data['PAGE_TITLE'] = app_lang('PASS_FORGOT_TEXT');
		$this->data['PAGE_DESCRIPTION'] = $this->data['PAGE_TITLE'];
		$this->data['PAGE_KEYWORDS'] = $this->data['PAGE_TITLE'];

		$this->data['error'] = false;
		$this->data['message'] = '';
		$this->data['send_mail_true'] = false;

		if($this->input->get('me')){
			$userID = $this->input->get('user');
			$userActivateKey = $this->input->get('confirmkey');
			return $this->pageForgotChangePass($userID, $userActivateKey);
		}
		# post
		if($email = $this->input->post('forgot_email'))
		{
			# если есть такой ящик
			if($user = $this->LoginAdmModel->checkEmailUser($email))
			{
				$this->LoginAdmModel->changePassSendMail($user);
				$this->data['send_mail_true'] = true;
			}
			else
			{
				$this->data['error'] = true;
				$this->data['message'] = app_lang('INFO_ERROR_EMAIL_REG_NOT');
			}
		}

		$this->_render_page('login-forgot');
	}

	/*
	* смена пароля
	*/
	private function pageForgotChangePass($userID = 0, $userActivateKey = '')
	{
		$this->data['change_true'] = false;

		if(!$user = $this->LoginAdmModel->checkUserActivate($userID, $userActivateKey)){
			exit('ERROR');
		}


		# пост на изменение пароля
		if($password = $this->input->post('password'))
		{

			if($password['first'] != $password['second']) {
				$this->data['error'] = true;
				$this->data['message'] = app_lang('INFO_PASS_NOT_MATCH');
				return $this->_render_page('login-forgot-pass');
			}

			if(!$this->LoginAdmModel->qualityPassword($password['first'])) {
				$this->data['error'] = true;
				$this->data['message'] = app_lang('INFO_SHORT_PASS');
				return $this->_render_page('login-forgot-pass');
			}

			$newPass = $this->LoginAdmModel->encryptPassword($password['first']);
			if($this->LoginAdmModel->updateUser($user['users_id'], array('password' => $newPass))){
				$this->data['change_true'] = true;
			}else{
				$this->data['error'] = true;
				$this->data['message'] = app_lang('INFO_ERROR_CHANGE_PASS');
			}

			$this->_render_page('login-forgot-pass');
		}
		else
		{
			$this->_render_page('login-forgot-pass');
		}
	}

	/**
	* page_change_pass
	*
	* изменение пароля
	*
	* @return	void
	*/
	private function page_change_pass()
	{
		$this->data['PAGE_TITLE'] = app_lang('INFO_CHANGE_PASS');
		$this->data['PAGE_DESCRIPTION'] = $this->data['PAGE_TITLE'];
		$this->data['PAGE_KEYWORDS'] = $this->data['PAGE_TITLE'];


		$this->data['error'] = false;
		$this->data['message'] = '';
		$this->data['send_mail_true'] = false;
		$this->data['change_true'] = false;


		# если не залогинен, то отправляем на вход
		if(!$user = $this->LoginAdmModel->checkLogin()) {
			redirect(info('base_url') . 'login', 'refresh');
			return;
		}


		# post
		if($change = $this->input->post('password'))
		{
			if($change['second'] !== $change['third'])
			{
				$this->data['error'] = true;
				$this->data['message'] = app_lang('INFO_PASS_NOT_MATCH');
			}
			elseif(!$this->LoginAdmModel->checkLoginPass($user['email'], $this->LoginAdmModel->encryptPassword($change['first'])))
			{
				$this->data['error'] = true;
				$this->data['message'] = app_lang('INFO_PASS_NOT_MATCH');
			}
			elseif(!$this->LoginAdmModel->qualityPassword($change['third']))
			{
				$this->data['error'] = true;
				$this->data['message'] = app_lang('INFO_SHORT_PASS');
			}
			else
			{
				if($this->LoginAdmModel->updateUser($user['id'], array('password' => $this->LoginAdmModel->encryptPassword($change['third'])))) {
					redirect(info('base_url') . 'login/logout', 'refresh');
				}else{
					$this->data['error'] = true;
					$this->data['message'] = app_lang('INFO_ERROR');
				}
			}
		}

		$this->_render_page('login-change-pass');
	}

	/*
	* генерация страницы
	*/
	private function _render_page($file = '')
	{
		if(!$file) $file = 'login';

		isset($this->data['PAGE_TITLE']) ?: $this->data['PAGE_TITLE'] = 'default title';
		isset($this->data['PAGE_DESCRIPTION']) ?: $this->data['PAGE_DESCRIPTION'] = 'default description';
		isset($this->data['PAGE_KEYWORDS']) ?: $this->data['PAGE_KEYWORDS'] = 'default keywords';
		$this->load->view('login/' . $file, $this->data);
	}
}
