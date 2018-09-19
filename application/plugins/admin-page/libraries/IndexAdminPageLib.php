<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*
	* admin-page lib
	*
	*
	* version 0.6
	* UPD 2017-08-17
	*
	* version 0.7
	* UPD 2018-04-10
	* исправлена ошибка в редактировании при отсутствии объекта
	*
	* version 1.0
	* UPD 2018-09-19
	* Подключены языковые файлы. Подключение нескольких типов данных к объекту
	*
*/

class IndexAdminPageLib {

	private $noUserArray = array(); // нулевой пользователь

	public function __construct()
	{
		$this->noUserArray[0] = array(
					'users_id' => '0',
					'users_group' => '1',
					'users_login' => 'deflogin',
					'users_password' => '0000',
					'users_name' => 'No name',
					'users_image' => '',
					'users_email' => '',
					'users_phone' => '',
					'users_phone_active' => '',
					'users_date_registr' => '2000-01-01 07:00:00',
					'users_date_birth' => '2000-01-01 07:00:00',
					'users_last_visit' => '',
					'users_ip_register' => '',
					'users_activate_key' => '',
					'users_site_key' => '',
					'users_activate' => '1',
					'users_lang' => 'english',
					'users_status' => 'publish'
		);
	}
	public function __call($method, $par)
	{
		return $this->index();
	}

	public function index()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('ADMP_TITLE_INDEX');
		$CI->pageContentDescription = $CI->pageContentTitle;
		$data = array();
		$CI->pageContent = $CI->load->view('admin-page/index', $data, true);
	}

	/**
	* edit
	*
	* редактирование страницы
	*
	* @return html
	*/
	public function edit()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('ADMP_TITLE_EDIT');
		$CI->pageContentDescription = $CI->pageContentTitle;

		$message = '';
		$ins = false;
		$row_id = 0;
		$obj_name = '';
		$updateCanonical = false;

		$row_id = $CI->input->get('row_id');

		if(!$row_id)
			$row_id = $CI->input->get('node_id');

		if(!$row_id) {
			return $CI->_render_not_found();
		}


		if($CI->input->get('new')) {
			$CI->pageCompliteMessage = app_lang('ADMP_INFO_CREATE_COMPLITE');
		}

		# update data fields
		if($data_fields = $CI->input->post('data_field')) {
			foreach($data_fields as $key => $value)
			{
				$ins = $CI->ObjectAdmModel->updateDataFieldsArray($value, $key);
			}
		}

		# update object
		if($data_obj = $CI->input->post('obj')) {
			foreach($data_obj as $key => $value)
			{
				if(isset($value['obj_status'])) {
					$value['status'] = 'publish';
				} else {
					$value['status'] = 'hidden';
				}

				$obj_name = $value['name'];
				if(isset($value['data_type']) && $value['data_type']) {
					$value['data_type'] = '|' . implode('|', $value['data_type']) . '|';
				} else {
					$value['data_type'] = '|' . 1 . '|';;
				}

				$ins = $CI->ObjectAdmModel->updateObject($value, $key);
			}
		}

		# update tree
		if($data_obj2obj = $CI->input->post('tree')) {
			foreach($data_obj2obj as $key => $value)
			{
				# если url пустой, то отправим название объекта
				if(!trim($value['url'])) {
					$value['url'] = $obj_name;
				}
				# проверим короткую ссылку. Если такая существует в БД, надо менять
				if($CI->AdminPageModel->checkShortLink($value['short'], $key)) {
					$value['short'] = $CI->AdminPageModel->createShortLink();
				}

				$ins = $CI->TreelibAdmModel->updateTree($key, $value);
			}
			$updateCanonical = true;
		}


		if($updateCanonical){
			$CI->TreelibAdmModel->updateCanonicalOblect(0, $row_id);
		}

		$data = $CI->AdminPageModel->loadObjectFromRow($row_id);
		if(!$data) {
			$CI->pageErrorMessage = app_lang('ADMP_INFO_NOT_FOUND');
			return;
		}

		if($ins) {
			$CI->pageCompliteMessage = app_lang('ADMP_INFO_UPDATE_COMPLITE');
		}

		$data['obj_data_type'] = explode('|', trim($data['obj_data_type'], '|'));

		# получим все URLs объекта
		$data['all_urls'] = array();
		$data['all_urls'] = $CI->AdminPageModel->getAllTreeObject($data['obj_id']);

		$data['visibleShortLink'] = true; // ссылка для перехода по короткой ссылке
		# если короткая ссылка не создана для объекта
		if(!$data['tree_short']){
			$data['tree_short'] =  $CI->AdminPageModel->createShortLink();
			$data['visibleShortLink'] = false;
		}

		$data['message'] = $message;
		$data['form_action'] = info('base_url') . 'admin/admin-page/edit?node_id=' . $row_id;

		$tpls = app_get_pages_templates();
		$data['options_tpl_page'] = $tpls['pages'];
		$data['options_tpl_content'] = $tpls['contents'];

		// все типы данных с полями
		$data['all_data_types'] = $CI->CommonModel->getAllDataTypesFull();

		// типы данных объекта с полями
		$data['objectDataTypes'] = array();
		foreach($data['obj_data_type'] as $value) {
			if(isset($data['all_data_types'][$value])) {
				$data['objectDataTypes'][$value] = $data['all_data_types'][$value];
			}
		}

		$data['all_types'] = array();
		$types = $CI->CommonModel->getAllObjTypes();
		foreach($types as $key => $value) {
			$data['all_types'][$key] = $value['obj_types_name'];
		}

		unset($key, $value, $types);

		# запрет на изменение родителей
		if($CI->input->get('parents')) {
			$data['edit_parents'] = false;
		}

		# все пользователи. Только админы
		$value = array();
		$data['all_users'] = $CI->LoginAdmModel->getAllUsers(2);
		$data['all_users'][0] = $this->noUserArray[0];
		$data['all_users_groups'] = $CI->LoginAdmModel->getUsersGroups(true);
		//pr($data['all_users_groups']);
		foreach($data['all_users'] as $key => &$value) {
			$value = $value['users_name'] . ' ('.$value['users_login'].', '.$value['users_email'].')';
		}

		unset($value);

		# если требуется вернуть в JSON
		if($CI->input->get('jsontrue')) {
			$out = array();
			if($ins) {
				$out['status'] = 'complite';
				$out['info'] = app_lang('ADMP_INFO_UPDATE_COMPLITE');
			} else {
				$out['status'] = 'error';
				$out['info'] = app_lang('ADMP_INFO_UPDATE_ERROR');
			}
			$out['content'] = $CI->load->view('admin-page/edit', $data, true);
			echo json_encode($out);
		} else {
			$CI->pageContent = $CI->load->view('admin-page/edit', $data, true);
		}
	}
}
