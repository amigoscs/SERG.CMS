<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*
	* Модель для работы с авторизацией
	*
	* UPD 2017-11-15
	* version 2.0
	* remove user key postfix
	*
	* UPD 2017-12-12
	* version 2.1
	* испралено формирование user_key при авторизации
	*
	* UPD 2018-02-15
	* version 3.0
	* настроена отправка писем о регистрации. Изменена директория Login (стало views/login). Изменена схема активации пользователя
	*
	* UPD 2018-04-16
	* version 4.0
	* В таблицу ГРУППЫ ПОЛЬЗОВАТЕЛЕЙ добавлен столбец ТИП ГРУППЫ. Добавлена проверка доступа пользователя
	*
	* UPD 2018-09-10
	* version 4.1
	* Переделана проверка доступа пользователя
	*
	* UPD 2018-10-23
	* version 4.2
	* Правки в коде. strip_tags для метода login
	*
*/

class LoginAdmModel extends CI_Model {

	public $allowedNumberAttempts;
	public $numberAttempts;

	// ключ шифрования
	private $encryptionKey = '';

	// постфикс для user key
	private $userKeyPostfix = '';

	// время жизни куки обычной авторизации
	private $timeLoginDefault = 0;

	// время жизни куки с авторизацией на долго
	private $timeLoginRemember = 0;

	// массив доступов
	public $accesses;

	// роли групп пользователей
	public $allRolesGroups;

	public function __construct()
    {
        parent::__construct();
		$this->encryptionKey = $this->config->item('encryption_key');
		$this->timeLoginDefault = 86400; // один день
		$this->timeLoginRemember = 1209600; // 14 дней
		$this->allowedNumberAttempts = app_get_option('number_attempts_login', 'general', 3);
		$this->accesses = array();
		$this->allRolesGroups = app_get_option('panel_all_roles', 'general', array(
			'1' => 'Root user',
			'2' => 'Пользователи',
			'3'  => 'Администратор',
			'4'  => 'Редактор',
			'5'  => 'Модератор',
		));
	}

	# установка доступов
	public function setAccesses($accesses)
	{
		$this->accesses = $accesses;
	}


	/**
	* encryptPassword
	*
	* зашифровка пароля
	*
	* @param	пароль
	* @return	string
	*/
	public function encryptPassword($pass)
	{
		return md5( strrev($this->config->item('encryption_key') . $pass) );
	}

	public function checkLoginPass($login, $pass)
	{
		$login = trim($login);
		$pass = trim($pass);
		$this->load->helper('email');
		if(!$login OR !$pass) return array();
		$this->db->join('users_group', 'users.users_group = users_group.users_group_id');
		# если вход по почте
		if(valid_email($login)) {
			$this->db->where('users_email', $login);
		}else{
			$this->db->where('users_login', $login);
		}
		$this->db->where('users_password', $pass);
		$query = $this->db->get('users');
		return $query->num_rows() ? $query->row_array() : array();
	}

	/**
	* checkLoginUsers
	*
	* проверка на повторяющегося пользователя
	*
	* @param	логин
	* @return	string
	*/
	public function checkLoginUsers($login)
	{
		$this->db->where('users_login', $login);
		$query = $this->db->get('users');
		return $query->num_rows() ? $query->row_array() : array();
	}

	/**
	* checkEmailUser
	*
	* проверка на повторяющегося email
	*
	*/
	public function checkEmailUser($email)
	{
		$email = trim($email);
		if(!app_valid_email($email))
			return array();

		$this->db->where('users_email', $email);
		$query = $this->db->get('users');
		return $query->num_rows() ? $query->row_array() : array();
	}

	/**
	* login
	*
	* залогинивание
	*
	* @param	пароль
	* @param	логин
	* @return	string
	*/
	public function login($login, $pass, $expire = 0, $remember = FALSE)
	{
		if(!$expire) {
			$expire = $this->timeLoginDefault;
		}

		if($this->allowedNumberAttempts > 0)
		{
			if(!$this->session->userdata('attempts_login')) {
				$this->numberAttempts = 0;
			} else {
				$this->numberAttempts = $this->session->userdata('attempts_login');
			}

			# попыток ввода пароля превышает разрешенное количество. Редирект на главную
			if($this->LoginAdmModel->allowedNumberAttempts <= $this->LoginAdmModel->numberAttempts) {
				//redirect(info('base_url'), 'refresh');
				return false;
			}
		}

		// чистка
		$login = strip_tags($login);
		$pass = strip_tags($pass);

		$result = $this->checkLoginPass($login, $this->encryptPassword($pass));
		if($result and $result['users_status'] == 'publish')
		{

			$user_data = array(
				'id'	=> $result['users_id'],
				'login'	=> $result['users_login'],
				'email'	=> $result['users_email'],
				'phone'	=> $result['users_phone'],
				'phone_active'	=> $result['users_phone_active'],
				'group'	=> $result['users_group'],
				'password'	=> $result['users_password'],
				'name'	=> $result['users_name'],
				'image'	=> $result['users_image'],
				'dateregistr'	=> $result['users_date_registr'],
				'datebirth'	=> $result['users_date_birth'],
				'lastvisit'	=> $result['users_last_visit'],
				'user_status'	=> $result['users_status'],
				'group_name'	=> $result['users_group_name'],
				'group_status'	=> $result['users_group_status'],
				'group_type'	=> $result['users_group_type'],
				'lang'	=> $result['users_lang'],
			);
			# ключ для сайта
			$user_data['user_key'] = $result['users_site_key'] . $this->userKeyPostfix;

			set_cookie('user_key', $user_data['user_key'], $expire, '', $path = '/');

			$this->session->set_userdata($user_data);
			$this->session->unset_userdata('attempts_login');

			// если надо запомнить пользователя
			if($remember) {
				set_cookie('user_login', $user_data['login'], $this->timeLoginRemember, '', '/');
				set_cookie('user_pass', $user_data['password'], $this->timeLoginRemember, '', '/');
			}

			return $user_data;
		}

		$this->session->set_userdata('attempts_login', $this->session->userdata('attempts_login') + 1);
		return array();
	}

	/**
	* checkLogin
	*
	* проверка на залогинивание
	*
	* @return	mixed
	*/
	public function checkLogin()
	{
		if($this->session->userdata('id'))
		{
			return $this->session->userdata();
		}
		else
		{
			return false;
		}
	}

	/**
	* check_admin
	*
	* проверка на админа
	*
	* @return	mixed
	*/
	public function checkAdmin()
	{
		if($this->session->userdata('id') and $this->session->userdata('group') == '2') {
			return $this->session->userdata();
		} else {
			return false;
		}
	}

	/**
	* checkAccessPanel
	*
	* проверка пользователя на доступ к панели сайта
	*
	* @return	array
	*/
	public function checkAccessPanel()
	{
		if($this->session->userdata('group_type'))
		{
			switch($this->session->userdata('group_type')) {
				case '2':
					return false;
					break;
				case '1':
					// no break
				case '3':
					// no break
				case '4':
					// no break
				case '5':
					// no break
					return $this->session->userdata();
				default:
					return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* checkAccessUser
	*
	* проверка пользователя на доступ
	*
	* @return	bool
	*/
	public function checkAccessUser($key = '____')
	{
		$tmp = array();
		if(isset($this->accesses[$key])) {
			$tmp = $this->accesses[$key]['access'];
			if(in_array($this->session->userdata('group_type'), $tmp)) {
				return true;
			}
		}
		return false;
	}

	/**
	* update_user
	*
	* обновление информации о пользователе
	*
	* @param	array
	* @return	mixed
	*/
	public function updateUser($user_id = 0, $args = array())
	{
		$data = array();
		isset($args['group']) ? $data['users_group'] = $args['group'] : 0 ;
		isset($args['login']) ? $data['users_login'] = $args['login'] : 0 ;
		isset($args['password']) ? $data['users_password'] = $args['password'] : 0 ;
		isset($args['name']) ? $data['users_name'] = $args['name'] : 0 ;
		isset($args['image']) ? $data['users_image'] = $args['image'] : 0 ;
		isset($args['phone']) ? $data['users_phone'] = $args['phone'] : 0 ;
		isset($args['phone_active']) ? $data['phone_active'] = 1 : 0 ;
		isset($args['email']) ? $data['users_email'] = $args['email'] : 0 ;
		isset($args['date_registr']) ? $data['users_date_registr'] = $args['date_registr'] : 0 ;
		isset($args['date_birth']) ? $data['users_date_birth'] = $args['date_birth'] : 0 ;
		isset($args['last_visit']) ? $data['users_last_visit'] = $args['last_visit'] : 0 ;
		isset($args['ip_register']) ? $data['users_ip_register'] = $args['ip_register'] : 0 ;
		isset($args['activate_key']) ? $data['users_activate_key'] = $args['activate_key'] : 0 ;
		isset($args['site_key']) ? $data['users_site_key'] = $args['site_key'] : 0 ;
		isset($args['activate']) ? $data['users_activate'] = $args['activate'] : 0 ;
		isset($args['status']) ? $data['users_status'] = $args['status'] : 0 ;
		isset($args['lang']) ? $data['users_lang'] = $args['lang'] : 0 ;

		if($data)
		{
			$data = array_map("trim", $data);
			$this->db->where('users_id', $user_id);
			return $this->db->update('users', $data);
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* insert_user
	*
	* добавление нового пользователя
	*
	* @param	array
	* @return	mixed
	*/
	public function insertUser($args = array())
	{
		$data = array();
		$response = array();
		isset($args['group']) ? $data['users_group'] = $args['group'] : $data['users_group'] = 1;
		isset($args['login']) ? $data['users_login'] = trim($args['login']) : $data['users_login'] = '';
		isset($args['password']) ? $data['users_password'] = $args['password'] : $data['users_password'] = '';
		isset($args['name']) ? $data['users_name'] = $args['name'] : $data['users_name'] = '';
		isset($args['image']) ? $data['users_image'] = $args['image'] : $data['users_image'] = '';
		isset($args['email']) ? $data['users_email'] = trim($args['email']) : $data['users_email'] = '';
		isset($args['phone']) ? $data['users_phone'] = $args['phone'] : $data['users_phone'] = '';
		isset($args['phone_active']) ? $data['users_phone_active'] = $args['phone_active'] : $data['users_phone_active'] = 0;
		isset($args['date_registr']) ? $data['users_date_registr'] = $args['date_registr'] : $data['users_date_registr'] = date('Y-m-d H:i:s');
		isset($args['date_birth']) ? $data['users_date_birth'] = $args['date_birth'] : $data['users_date_birth'] = '1970-01-01';
		isset($args['last_visit']) ? $data['users_last_visit'] = $args['last_visit'] : $data['users_last_visit'] = time();
		$data['users_ip_register'] = $_SERVER['REMOTE_ADDR'];
		isset($args['activate']) ? $data['users_activate'] = 1 : $data['users_activate'] = 0;
		isset($args['status']) ? $data['users_status'] = $args['status'] : $data['users_status'] = 'publish';
		isset($args['lang']) ? $data['users_lang'] = $args['lang'] : $data['users_lang'] = app_get_option('site_lang', 'site', 'english');

		if(!$data['users_login'] OR !$data['users_password'] OR !$data['users_email']) {
			return false;
		}

		if($this->checkEmailUser($data['users_email'])) {
			return false;
		}

		$response = $data;

		$data['users_password'] = $this->encryptPassword($data['users_password']);
		$data['users_activate_key'] = md5($this->config->item('encryption_key') . $data['users_email'] . $data['users_login']);
		$data['users_site_key'] = '';

		$data = array_map("trim", $data);

		$ins = $this->db->insert('users', $data);
		if($ins)
		{
			// создадим user_key
			$userInsertID = $this->db->insert_id();
			$userSiteKey = md5($userInsertID . $this->encryptionKey) . $this->userKeyPostfix;
			$this->db->where('users_id', $userInsertID);
			$this->db->update('users', array('users_site_key' => $userSiteKey));

			$response['users_password_encrypt'] = $data['users_password'];
			$response['users_activate_key'] = $data['users_activate_key'];
			$response['users_site_key'] = $userSiteKey;
			$response['users_id'] = $userInsertID;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	* insert_user
	*
	* создание userdata для пользователя по кукам. Установить в конструторах главных контроллеров
	*
	* @param	array
	* @return	mixed
	*/
	public function userData()
	{
		$userKeyCookie = get_cookie('user_key');
		# если в куках числятся логин и зашифрованный пароль, то надо авторизовать браузер
		if(get_cookie('user_login') and get_cookie('user_pass'))
		{
			$this->autoLoginUser(get_cookie('user_login'), get_cookie('user_pass'));
			return;
		}
		# если нет user_key в куках, то это чистый пользователь. Убиваем все сессии и создаем заново
		elseif(!$userKeyCookie)
		{
			$this->session->sess_destroy();
			$userKey = md5($this->session->userdata('__ci_last_regenerate') . time());
			$expire = $this->timeLoginDefault; // создадим куку на время по умолчанию
			set_cookie('user_key', $userKey, $expire, '', '/');
			// сохраним сессию
			$this->session->set_userdata(array('user_key' => $userKey));
		}

		# user_key в куках должен совпадать с user_key в сессиях
		if($userKeyCookie !== $this->session->userdata('user_key')){
			// если пользователь зарегистрирован, то в куки отдаем ключ из сессии
			if(is_login() and $this->session->userdata('user_key')) {
				set_cookie('user_key', $this->session->userdata('user_key'), $this->timeLoginDefault, '', '/');
			} else {
				$this->session->set_userdata(array('user_key' => $userKeyCookie));
			}
		}
	}

	/*
	* автоматическая авторизация
	*/
	public function autoLoginUser($login = 0, $pass = 0)
	{
		# если нет userdata, то надо его создать
		if(!$this->session->userdata('login'))
		{
			$result = $this->checkLoginPass(get_cookie('user_login'), get_cookie('user_pass'));
			if($result)
			{
				$user_data = array(
					'id'	=> $result['users_id'],
					'login'	=> $result['users_login'],
					'email'	=> $result['users_email'],
					'phone'	=> $result['users_phone'],
					'phone_active'	=> $result['users_phone_active'],
					'group'	=> $result['users_group'],
					'password'	=> $result['users_password'],
					'name'	=> $result['users_name'],
					'image'	=> $result['users_image'],
					'dateregistr'	=> $result['users_date_registr'],
					'datebirth'	=> $result['users_date_birth'],
					'lastvisit'	=> $result['users_last_visit'],
					'user_status'	=> $result['users_status'],
					'group_name'	=> $result['users_group_name'],
					'group_status'	=> $result['users_group_status'],
					'group_type'	=> $result['users_group_type'],
					'lang'	=> $result['users_lang'],
				);

				# ключ для сайта
				$user_data['user_key'] = $result['users_site_key'] . $this->userKeyPostfix;

				$expire = $this->timeLoginRemember; // 14 дней
				set_cookie('user_key', $user_data['user_key'], $expire, '', $path = '/');
				$this->session->set_userdata($user_data);
				//return $user_data;
			}
		}
	}

	/*
	* проверка существования userID и users_activate_key
	*/
	public function checkUserActivate($userID = 0, $activateKey = 0)
	{
		$this->db->where('users_id', trim($userID));
		$this->db->where('users_activate_key', trim($activateKey));
		$query = $this->db->get('users');
		return $query->num_rows() ? $query->row_array() : array();
	}

	/*
	* проверка качества пароля
	*/
	public function qualityPassword($pass = '')
	{
		$return = true;
		if(mb_strlen($pass) < 6) {
			$return = false;
		}

		return $return;
	}

	/**
	* получить все группы пользователей
	*/
	public function getUsersGroups($publish = FALSE)
	{
		if($publish){
			$this->db->where('users_group_status', 'publish');
		}

		$query = $this->db->get('users_group');
		$usersGroups = array();
		foreach($query->result_array() as $row) {
			$usersGroups[$row['users_group_id']] = $row;
		}

		return $usersGroups;
	}

	/**
	* обновить группу пользователей
	*/
	public function updateUsersGroup($groupID = 0, $args = array())
	{
		$data = array();
		if(!$groupID) {
			return false;
		}

		isset($args['group_name']) ? $data['users_group_name'] = $args['group_name'] : 0;
		isset($args['group_type']) ? $data['users_group_type'] = $args['group_type'] : 0;
		isset($args['group_descr']) ? $data['users_group_descr'] = $args['group_descr'] : 0;
		isset($args['group_status']) ? $data['users_group_status'] = $args['group_status'] : 0;

		// группы 1 и 2 можно обновлять только описание
		if($groupID < 3){
			if(!isset($data['users_group_descr'])) {
				$data = array();
			} else {
				$data = array('users_group_descr' => $data['users_group_descr']);
			}
		}

		if(!$data) {
			return false;
		}

		$this->db->where('users_group_id', $groupID);
		return $this->db->update('users_group', $data);
	}

	/**
	* создать группу пользователей
	*/
	public function createUsersGroup($args = array())
	{
		$data = array();

		$data['users_group_name'] = isset($args['group_name']) ? $args['group_name'] : 'NO NAME';
		$data['users_group_type'] = isset($args['group_type']) ? $args['group_type'] : 1;
		$data['users_group_descr'] = isset($args['group_descr']) ? $args['group_descr'] : '';
		$data['users_group_status'] = isset($args['group_status']) ? $args['group_status'] : 'publish';

		if($this->db->insert('users_group', $data)) {
			return $this->db->insert_id();
		} else {
			return false;
		}
	}

	/**
	* удалить группу пользователей
	*/
	public function deleteUsersGroup($groupID = 0)
	{
		// группы 1 и 2 удалять нельзя
		if($groupID < 3) {
			return false;
		}

		$this->db->where('users_group_id', $groupID);
		// надо еще сбросить группу у всех пользователей
		if($this->db->delete('users_group')){
			$this->db->where('users_group', $groupID);
			return $this->db->update('users', array('users_group' => 1));
		}
	}

	/**
	* получить всех пользователей
	*/
	public function getAllUsers($userGroup = 0, $userID = 0)
	{
		$users = array();

		if($userID) {
			$this->db->where('users_id', $userID);
		}

		if($userGroup) {
			$this->db->where('users_group', $userGroup);
		}

		$this->db->order_by('users_date_registr', 'DESC');
		$query = $this->db->get('users');

		foreach($query->result_array() as $row) {
			$users[$row['users_id']] = $row;
		}
		return $users;
	}

	/*
	* отправка писем о регистрации
	*/
	public function loginSendMail($args = array())
	{
		$emailProtocol = app_get_option('email_protocol', 'general', 'mail');
		$fromEmail = app_get_option('server_mail', 'general', '');
		$fromName = app_get_option('site_template', 'site', '');
		$adminEmail = app_get_option('admin_mail', 'general', '');
		$args['site_name'] = app_get_option('site_name', 'site', '');
		$args['site_url'] = APP_BASE_URL_NO_SLASH;

		$mailAdminText = $this->load->view('/login/units/mail-template-register-admin', $args, TRUE);
		$mailUserText = $this->load->view('/login/units/mail-template-register-user', $args, TRUE);
		//echo $mailAdminText;
		//return;
		# конфигурация почты
		$this->email->clear();
		$this->email->protocol = $emailProtocol;
		$this->email->from($fromEmail, $fromName);
		$this->email->mailtype = 'html';
		$this->email->reply_to($fromEmail, $fromName);
		$this->email->subject($args['site_name'] . ' - регистрация нового пользователя');
		$this->email->message($mailAdminText);
		$this->email->to(trim($adminEmail));
		$this->email->send();

		if(isset($args['users_email'])) {
			$this->email->clear();
			$this->email->protocol = $emailProtocol;
			$this->email->from($fromEmail, $fromName);
			$this->email->mailtype = 'html';
			$this->email->reply_to($fromEmail, $fromName);
			$this->email->subject($args['site_name'] . ' - регистрация на сайте');
			$this->email->message($mailUserText);
			$this->email->to(trim($args['users_email']));
			$this->email->send();
		}

		return true;
	}

	/*
	* отправка писем о смене пароля
	*/
	public function changePassSendMail($args = array())
	{
		$emailProtocol = app_get_option('email_protocol', 'general', 'mail');
		$fromEmail = app_get_option('server_mail', 'general', '');
		$fromName = app_get_option('site_template', 'site', '');
		$adminEmail = app_get_option('admin_mail', 'general', '');
		$args['site_name'] = app_get_option('site_name', 'site', '');
		$args['site_url'] = APP_BASE_URL_NO_SLASH;
		$args['ip_action'] = $_SERVER['REMOTE_ADDR'];

		$mailAdminText = $this->load->view('/login/units/mail-template-forgot-admin', $args, TRUE);
		$mailUserText = $this->load->view('/login/units/mail-template-forgot-user', $args, TRUE);
		//echo $mailAdminText;
		//return;
		# конфигурация почты
		$this->email->clear();
		$this->email->protocol = $emailProtocol;
		$this->email->from($fromEmail, $fromName);
		$this->email->mailtype = 'html';
		$this->email->reply_to($fromEmail, $fromName);
		$this->email->subject($args['site_name'] . ' - запрос на изменение пароля');
		$this->email->message($mailAdminText);
		$this->email->to(trim($adminEmail));
		$this->email->send();

		if(isset($args['users_email'])) {
			$this->email->clear();
			$this->email->protocol = $emailProtocol;
			$this->email->from($fromEmail, $fromName);
			$this->email->mailtype = 'html';
			$this->email->reply_to($fromEmail, $fromName);
			$this->email->subject($args['site_name'] . ' - изменение пароля');
			$this->email->message($mailUserText);
			$this->email->to(trim($args['users_email']));
			$this->email->send();
		}

		return true;
	}
}
