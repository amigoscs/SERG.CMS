<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  admin-site-tree Lib

*/

class indexAdminSiteTreeLib {

	private $GET, $POST, $ajaxResponse;

	public function __construct()
	{
		// 200 - OK
		// 400 - ERROR
		$this->ajaxResponse = array('status' => '400', 'info' => 'ERROR');
	}
	public function __call($method, $par)
	{
		return $this->index();
	}

	/*
	* главная
	*/
	public function index()
	{
		$CI = &get_instance();
		$CI->data['PAGE_TITLE'] = app_lang('ADST_TITLE_INDEX');
		$CI->data['PAGE_DESCRIPTION'] = app_lang('ADST_DESCR_INDEX');

		$CI->TreelibAdmModel->maxLevel = 2;
		$CI->TreelibAdmModel->onlyPublish = FALSE;
		$data = array();
		$data['h1'] = $CI->data['PAGE_TITLE'];
		# все типы объектов
		$data['all_obj_types'] = $CI->CommonModel->getAllObjTypes();
		$CI->pageContent = $CI->load->view('admin_page/index', $data, true);
	}

	/*
	* загрузка нод через ajax
	*/
	public function loadNodesData()
	{
		$CI = &get_instance();
		$singleNode = FALSE;

		$get = $CI->input->get();
		if(!isset($get['node'])){
			$parentID = 0;
		}else{
			$parentID = $get['node'];
		}

		if(isset($get['single_load'])){
			$singleNode = TRUE;
		}


		$jsonTree = $CI->AdstModel->TreeReturnNodes($parentID, $singleNode);
		echo json_encode($jsonTree);
	}

	/*
	* принимает ajax
	*/
	public function ajax()
	{
		$CI = &get_instance();
		$this->GET = $CI->input->get();
		$this->POST = $CI->input->post();
		try {

			if(!isset($this->GET['method'])) {
				throw new Exception('Method is not exists');
			}

			$method = str_replace('-','_', $this->GET['method']);
			$mArr = explode('_', $method);
			$mArr = array_map("ucfirst", $mArr);
			$method = '_ajax' . implode('', $mArr);
			if(method_exists($this, $method)){
				$this->$method();
				$this->ajaxResponseJson();
			} else {
				throw new Exception('Method is not exists');
			}
		} catch (Exception $e) {
			$this->ajaxResponseJson($e->getMessage());
		}
	}

	/*
	* AJAX ответ JSON
	*/
	private function ajaxResponseJson($info = '')
	{
		if($info) {
			$this->ajaxResponse['info'] = $info;
		}
		echo json_encode($this->ajaxResponse);
	}


	/*
	* для AJAX. Упорядочивание объектов в дереве. Изменение родительского элемента
	*/
	private function _ajaxSaveOrder()
	{
		$CI = &get_instance();
		$res = $CI->AdstModel->changeParent($this->POST['row_id'], $this->POST['new_parent'], $this->POST['nodes_array']);
		if($res){
			$this->ajaxResponse['status'] = '200';
		 	$this->ajaxResponse['info'] = app_lang('ADST_INFO_COMPLITE');
		}
		else
		{
			throw new Exception( app_lang('ADST_INFO_ERROR'));
		}

		# очистим кэш
		app_delete_cash();

		return true;
	}

	/*
	* для AJAX. изменить статус объектов
	*/
	private function _ajaxChangeStatus()
	{
		$CI = &get_instance();
		$objectsID = $this->POST['objects_id'];

		if($res = $CI->AdstModel->changeStatusObjArray($objectsID)){
			$this->ajaxResponse['status'] = '200';
			//$this->ajaxResponse['info'] = 'sdsadasd';
			$this->ajaxResponse['info'] = app_lang('ADST_INFO_S_COMPLITE');
			$this->ajaxResponse['object_status'] = $res;
		}else{
			throw new Exception( app_lang('ADST_INFO_S_ERROR'));
		}

		# очистим кэш
		app_delete_cash();
		return true;
	}

	/*
	* для AJAX. создание копии объекта
	*/
	private function _ajaxCopyObject()
	{
		$CI = &get_instance();
		// ID объекта, который копируем
		$rowID = $this->POST['row_id'];
		$typeCopy = 'copy';
		$copyChilds = FALSE;

		switch($this->POST['type_copy'])
		{
			case 'copy_copy':
				$typeCopy = 'copy';
				break;
			case 'copy_copy_childs':
				$typeCopy = 'copy';
				$copyChilds = TRUE;
				break;
			case 'copy_obj':
				$typeCopy = 'object';
				break;
			case 'copy_obj_childs':
				$typeCopy = 'object';
				$copyChilds = TRUE;
				break;
			default:
				exit();
		}

		if($result = $CI->AdstModel->copyObject($rowID, $typeCopy, $copyChilds))
		{
			$CI->TreelibAdmModel->reset();
			$this->ajaxResponse['status'] = '200';
			$this->ajaxResponse['info'] = app_lang('ADST_INFO_CO_COMPLITE');
			$newRowID = $result['new_row_id'];
			$parentRowID = $result['new_row_parent_id'];
			$arr_tree = $CI->TreelibAdmModel->loadAllChildsTree($newRowID, array(), 0, TRUE, TRUE);
			$this->ajaxResponse['node'] = $CI->AdstModel->TreeCreateArrayToJson($arr_tree, $parentRowID);
		}
		else
		{
			throw new Exception( app_lang('ADST_INFO_CO_ERROR'));
		}

		# очистим кэш
		app_delete_cash();
		return true;
	}

	/*
	* для AJAX. удаление ноды
	*/
	private function _ajaxDeleteNode()
	{
		$CI = &get_instance();
		$nodeID = $this->POST['row_id'];
		if($CI->AdstModel->DeleteTreeNode($nodeID)){
			$this->ajaxResponse['status'] = '200';
			$this->ajaxResponse['info'] = app_lang('ADST_INFO_DE_COMPLITE');
		}else{
			throw new Exception(app_lang('ADST_INFO_DE_ERROR'));
		}

		# очистим кэш
		app_delete_cash();
		//return $this->ajaxResponseJson();
	}

	/*
	* для AJAX. Изменение родителя для массива нод
	*/
	private function _ajaxChangeParent()
	{
		$CI = &get_instance();
		if(!isset($this->POST['nodes_id']) || !isset($this->POST['parent_id'])){
			throw new Exception('ChangeParent: error params');
		}

		// смена родителей в цикле
		foreach($this->POST['nodes_id'] as $nodeID){
			$CI->AdstModel->changeParent($nodeID, $this->POST['parent_id']);
		}

		$this->ajaxResponse['status'] = '200';
		$this->ajaxResponse['info'] = app_lang('ADST_INFO_MO_COMPLITE');

		# очистим кэш
		app_delete_cash();
		return true;
	}

	/*
	*
	* для AJAX. создание копий нод и присоединение их к новому родителю
	*
	*/
	private function _ajaxCreateCopy()
	{
		$CI = &get_instance();
		if(!isset($this->POST['nodes_id']) || !isset($this->POST['parent_id'])){
			throw new Exception('CreateCopy: error params');
		}

		if($CI->AdstModel->copyObjectArray($this->POST['nodes_id'], 'copy', FALSE, $this->POST['parent_id'])){
			$this->ajaxResponse['status'] = '200';
			$this->ajaxResponse['info'] = app_lang('ADST_INFO_CO_COMPLITE');
		}else{
			throw new Exception( app_lang('ADST_INFO_CO_ERROR'));
		}

		# очистим кэш
		app_delete_cash();
		return true;
	}

	/*
	* для AJAX. присвоение нодам статуса ОРИГИНАЛ
	*/
	private function _ajaxMakeOriginal()
	{
		$CI = &get_instance();
		if(!isset($this->POST['nodes_id'])) {
			throw new Exception( app_lang('ADST_INFO_ERROR'));
		}

		# обновим каноникал в цикле
		foreach($this->POST['nodes_id'] as $nodeID){
			$CI->TreelibAdmModel->changeCanonicalObject(0, $nodeID, $nodeID);
		}

		$this->ajaxResponse['status'] = '200';
		$this->ajaxResponse['info'] = app_lang('ADST_INFO_COMPLITE');

		# очистим кэш
		app_delete_cash();
		return true;
	}

	/*
	* для AJAX. возвращает информацию о достапух к объекту
	*/
	private function _ajaxGetObjectAccess()
	{
		if(!isset($this->POST['object_id'])){
			throw new Exception( app_lang('ADST_INFO_ERROR'));
		}

		$CI = &get_instance();
		$object = array();

		$CI->ObjectAdmModel->reset();
		$CI->ObjectAdmModel->includeDataFields = false;
		$object = $CI->ObjectAdmModel->getObject($this->POST['object_id']);

		if(!$object){
			throw new Exception(app_lang('ADST_INFO_ERROR'));
		}

		$allUserGroups = $CI->LoginAdmModel->getUsersGroups();
		$objectAccess = explode('|', $object['obj_ugroups_access']);

		$out = array();
		foreach($objectAccess as $value) {
			$value = trim($value);
			if(!$value) {
				continue;
			}

			if(isset($allUserGroups[$value])) {
				$out[$value] = $allUserGroups[$value]['users_group_name'];
			}
		}

		if($out) {
			$this->ajaxResponse['status'] = '200';
			$this->ajaxResponse['info'] = 'Доступ для групп: ' . implode(', ', $out);
		} else {
			throw new Exception( app_lang('ADST_INFO_ERROR'));
		}

		return true;
	}

	/*
	* для AJAX. Упорядочивание объектов в дереве. Изменение родительского элемента
	*/
	private function _ajaxSortNodes()
	{
		$CI = &get_instance();
		if(!$CI->AdstModel->treeSortChilds($CI->input->post('node_id'), $CI->input->post('sort_key'), $CI->input->post('sort_asc'))) {
			throw new Exception('Error: unexpected error');
		} else {
			$this->ajaxResponse['status'] = '200';
		 	$this->ajaxResponse['info'] = app_lang('ADST_INFO_SORT_COMPLITE');
		}

		# очистим кэш
		//app_delete_cash();
	}

}
