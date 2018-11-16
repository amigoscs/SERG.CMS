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
	* version 2.0
	* UPD 2018-10-11
	* Переработана логика. Добавлены методы для работы с формой по ajax
	*
	* version 2.1
	* UPD 2018-10-23
	* В редактировании типы данных получение только опубликованных
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
		$nodeID = 0;
		$obj_name = '';

		$nodeID = $CI->input->get('row_id');

		if(!$nodeID) {
			$nodeID = $CI->input->get('node_id');
		}

		if(!$nodeID) {
			return $CI->_render_not_found();
		}

		if($CI->input->get('new')) {
			$CI->pageCompliteMessage = app_lang('ADMP_INFO_CREATE_COMPLITE');
		}

		$updateParams = array();

		if($CI->input->post()) {
			//pr($CI->input->post());
			//exit();
		}


		# update data fields
		if($data_fields = $CI->input->post('data_field')) {
			foreach($data_fields as $key => $value){
				$updateParams['data_field'][$key] = $value;
			}
		}

		# update object
		if($data_obj = $CI->input->post('obj')) {
			foreach($data_obj as $key => $value) {
				$updateParams['obj'][$key] = $value;
			}
		}

		# update tree
		if($data_obj2obj = $CI->input->post('tree')) {
			foreach($data_obj2obj as $key => $value) {
				$updateParams['tree'][$key] = $value;
			}
		}

		if($updateParams) {
			if($this->_updateObject($updateParams)) {
				$CI->pageCompliteMessage = app_lang('ADMP_INFO_UPDATE_COMPLITE');
			} else {
				$CI->pageErrorMessage = 'Error update';
			}
		}

		$data = array();
		$CI->AppTreeModel->reset();
		$CI->AppTreeModel->loadData = true;
		$data = $CI->AppTreeModel->node($nodeID);
		if(isset($data[$nodeID])) {
			$data = $data[$nodeID];
		} else {
			$CI->pageErrorMessage = app_lang('ADMP_INFO_NOT_FOUND');
			$CI->pageContent = '';
			return;
		}

		/*if(!$data = $CI->AdminPageModel->loadObjectFromRow($nodeID)) {
			$CI->pageErrorMessage = app_lang('ADMP_INFO_NOT_FOUND');
			$CI->pageContent = '';
			return;
		}*/

		//pr($data);

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
		$data['form_action'] = info('base_url') . 'admin/admin-page/edit?node_id=' . $nodeID;

		$tpls = app_get_pages_templates();
		$data['options_tpl_page'] = $tpls['pages'];
		$data['options_tpl_content'] = $tpls['contents'];

		// все типы данных с полями
		$data['all_data_types'] = $CI->CommonModel->getAllDataTypesFull(true);

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

		$CI->pageContent = $CI->load->view('admin-page/edit', $data, true);
	}

	/**
	* ajax_edit_node
	*
	* возвращает ноду для редактирования по ajax
	*
	* @return json
	*/
	public function ajax_edit_node()
	{
		$CI = &get_instance();
		$response = array('status' => 'ERROR', 'info' => '', 'node_info' => array(), 'node_form' => '');

		try {
			if(!$nodeID = $CI->input->get('node_id')) {
				throw new Exception(app_lang('ADMP_INFO_PARAMS_NOT_FOUND'));
			}

			$OBJ = array();
			$OBJ = $CI->AdminPageModel->loadObjectFromRow($nodeID);
			if(!$OBJ) {
				throw new Exception(app_lang('ADMP_INFO_PAGE_NOT_FOUND'));
			}


			# получим все URLs объекта
			$OBJ['all_urls'] = array();
			$OBJ['all_urls'] = $CI->AdminPageModel->getAllTreeObject($OBJ['obj_id']);

			$tpls = app_get_pages_templates();
			$OBJ['options_tpl_page'] = $tpls['pages'];
			$OBJ['options_tpl_content'] = $tpls['contents'];

			// все типы данных с полями
			$OBJ['all_data_types'] = $CI->CommonModel->getAllDataTypesFull();
			$OBJ['obj_data_type'] = explode('|', trim($OBJ['obj_data_type'], '|'));

			// типы данных объекта с полями
			$OBJ['objectDataTypes'] = array();
			foreach($OBJ['obj_data_type'] as $value) {
				if(isset($OBJ['all_data_types'][$value])) {
					$OBJ['objectDataTypes'][$value] = $OBJ['all_data_types'][$value];
				}
			}

			$OBJ['all_types'] = array();
			$types = $CI->CommonModel->getAllObjTypes();
			foreach($types as $key => $value) {
				$OBJ['all_types'][$key] = $value['obj_types_name'];
			}

			unset($key, $value, $types);

			# все пользователи. Только админы
			$value = array();
			$OBJ['all_users'] = $CI->LoginAdmModel->getAllUsers(2);
			$OBJ['all_users'][0] = $this->noUserArray[0];
			$OBJ['all_users_groups'] = $CI->LoginAdmModel->getUsersGroups(true);
			//pr($data['all_users_groups']);
			foreach($OBJ['all_users'] as $key => &$value) {
				$value = $value['users_name'] . ' ('.$value['users_login'].', '.$value['users_email'].')';
			}

			unset($value);

			// ссылка для перехода по короткой ссылке
			$OBJ['visibleShortLink'] = true;
			# если короткая ссылка не создана для объекта
			if(!$OBJ['tree_short']){
				$OBJ['tree_short'] =  $CI->AdminPageModel->createShortLink();
				$OBJ['visibleShortLink'] = false;
			}

			$response['status'] = 'OK';
			$response['info'] = app_lang('ADMP_INFO_FORM_CREATE_OK');
			$response['node_info'] = $OBJ;
			$response['node_form'] = $CI->pageContent = $CI->load->view('admin-page/edit-form-ajax', $OBJ, true);
			//echo $response['node_form'];
			//exit();
		} catch (Exception $e) {
			$response['info'] = $e->getMessage();
		}
		echo json_encode($response);
		exit();
	}

	/**
	* ajax_edit_node
	*
	* возвращает ноду для редактирования по ajax
	*
	* @return json
	*/
	public function ajax_update_node()
	{
		$CI = &get_instance();
		$response = array('status' => 'ERROR', 'info' => '');

		try {

			if(!$nodeID = $CI->input->post('node_id')) {
				throw new Exception(app_lang('ADMP_INFO_PAGE_NOT_FOUND'));
			}

			if(!$CI->input->post('fom_values')) {
				throw new Exception(app_lang('ADMP_INFO_PARAMS_NOT_FOUND'));
			}
			parse_str($CI->input->post('fom_values'), $formValues);

			if($this->_updateObject($formValues)) {
				$response['status'] = 'OK';
				$response['info'] = app_lang('ADMP_INFO_FORM_UPDATE_OK');
			} else {
				throw new Exception(app_lang('ADMP_INFO_UPDATE_ERROR'));
			}
		} catch (Exception $e) {
			$response['info'] = $e->getMessage();
		}
		echo json_encode($response);
		exit();
	}

	// обновляет объект
	private function _updateObject($params)
	{
		$CI = &get_instance();
		# update data fields
		if(isset($params['data_field'])) {
			foreach($params['data_field'] as $key => $value) {
				$CI->AppObjectModel->updateData($key, $value, true);
			}
		}

		# update object
		if(isset($params['obj'])) {
			foreach($params['obj'] as $key => $value)
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

				$upd = $CI->AppObjectModel->update($key, $value);
			}
		}

		# update tree
		if(isset($params['tree'])) {
			foreach($params['tree'] as $key => $value)
			{
				if(!trim($value['url'])) {
					$value['url'] = $obj_name;
				}

				if(isset($value['folow'])) {
					$value['folow'] = 1;
				} else {
					$value['folow'] = 0;
				}

				// для типа ССЫЛКА url оставляем как есть, для других переписываем
				if($value['type_object'] != 4)  {
					$value['url'] = app_translate($value['url']);
				}

				$CI->AppTreeModel->update($key, $value);
			}
		}
		return true;
	}


}
