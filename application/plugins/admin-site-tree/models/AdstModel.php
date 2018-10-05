<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  admin-site-tree Model

	* UPD 2017-11-09
	* version 14.32

	* UPD 2018-01-15
	* version 15.0
	* переход на версию системы 4.0

	* UPD 2018-02-21
	* version 15.1
	* мелкие правки по коду

	* UPD 2018-05-02
	* version 15.2
	* Отключение LIMIT в выборке для дерева сайта

*/

class AdstModel extends CI_Model {

	private $currentDate;

	function __construct()
    {
        parent::__construct();
		$this->currentDate = new DateTime(date('Y-m-d H:i:s'));

    }



	/**
	* change parent
	*
	* изменить родителя для объекта или упорядочить
	*
	* @param	id объекта (tree_id)
	* @param	id new parent
	* @param	sort, Массив порядка следования объектов
	* @return	string
	*/
	public function change_parent($row_id = 0, $new_parent = 0, $order = array())
	{
		return $this->changeParent($row_id, $new_parent, $order);
	}
	public function changeParent($rowID = 0, $newParentID = 0, $order = array())
	{
		# ЕСли парент равен 999999999, то это самый верхний уровень, т.е. ноль. Сделано из-за особенностей jqTree
		if($newParentID == '999999999') {
			$newParentID = 0;
		}

		// получим id объекта
		$this->db->select('tree_url, tree_parent_id');
		$this->db->where('tree_id', $rowID);
		$query = $this->db->get('tree');
		$current_url = $query->row('tree_url');
		$current_parent = $query->row('tree_parent_id');
		unset($query);

		# возможно, что старый родитель и новый совпадают. Тогда просто сортируем
		if($current_parent == $newParentID)
		{
			if(!$order) return FALSE;

			foreach($order as $index => $el_row_id)
			{
				$data = array('tree_order' => $index);
				$this->db->where('tree_id', $el_row_id);
				$ord = $this->db->update('tree', $data);
			}

			return true;
		}


		$data = array('tree_parent_id' => $newParentID);
		// теперь проверим наличие такого урла у нового родителя
		$this->db->where('tree_url', $current_url);
		$this->db->where('tree_parent_id', $newParentID);
		$query = $this->db->get('tree');
		// если такой урл есть, делаем его уникальным
		if($query->num_rows())
		{
			$data['tree_url'] = $current_url . '-' . time();
		}

		$this->db->where('tree_id', $rowID);
		$upd = $this->db->update('tree', $data);
		if($upd)
		{
			foreach($order as $index => $el_row_id)
			{
				$data = array('tree_order' => $index);
				$this->db->where('tree_id', $el_row_id);
				$ord = $this->db->update('tree', $data);
			}
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* copyObject
	*
	* копирование объекта
	*
	* @param	id объекта (tree_id)
	* @param	тип копирования. Создать запись в tree или еще и в objects
	* @param	включить ли в копию дочерние объекты
	* @return	array
	*/
	public function copy_object($row_id = 0, $type_copy = 'object', $copy_childs = FALSE, $new_parent = 0)
	{
		return $this->copyObject($row_id, $type_copy, $copy_childs, $new_parent);
	}
	public function copyObject($row_id = 0, $type_copy = 'object', $copy_childs = FALSE, $new_parent = 0)
	{

		$report = array();
		$childs = array();
		# если нужны дочение
		if($copy_childs) {
			$childs = $this->TreelibAdmModel->loadAllChildsTree($row_id);
		}

		# объект в tree
		$object = array();
		$this->db->where('tree_id', $row_id);
		$query = $this->db->get('tree');
		if(!$query->num_rows())
		{
			return false;
		}
		$rows = $query->row_array();
		$object_id = $rows['tree_object'];
		//$orig_object_id = $rows['tree_object'];
		unset($query);

		$object = array();
		if($type_copy == 'object')
		{
			$data_fields = array();
			$this->db->where('obj_id', $object_id);
			$query = $this->db->get('objects');
			if(!$query->num_rows())
			{
				return false;
			}

			$object = $query->row_array();
			unset($query);

			# поменяем владельца объекта
			$object['obj_user_author'] = $this->userInfo['id'];

			# загрузить поля типов данных
			$this->db->where('objects_data_obj', $object['obj_id']);
			$query = $this->db->get('objects_data');
			$data_fields = $query->result_array();
			unset($query);

			unset($object['obj_id']);
			$object['obj_date_create'] = date('Y-m-d H:i:s');
			$object['obj_lastmod'] = time();

			$this->db->insert('objects', $object);
			$object_id = $this->db->insert_id();

			if($data_fields)
			{
				$data = array();
				foreach($data_fields as $val)
				{
					$data = $val;
					unset($data['objects_data_id']);
					$data['objects_data_obj'] = $object_id;
					$this->db->insert('objects_data', $data);
				}
				unset($data);
			}
			$report['new_obj_id'] = $object_id;
		}


		# теперь запись в tree
		unset($rows['tree_id']);
		$rows['tree_object'] = $object_id;
		$rows['tree_url'] = $rows['tree_url'];
		//$rows['tree_date_create'] = date('Y-m-d H:i:s');
		if($new_parent)
		{
			$rows['tree_parent_id'] = $new_parent;
			# надо проверить, нет ли такого объекта в этом родителе
			$this->db->where('tree_parent_id', $new_parent);
			$this->db->where('tree_object', $object_id);
			$query = $this->db->get('tree');
			if($query->num_rows())
				return false;
		}

		if($type_copy == 'object')
		{
			$rows['tree_type'] = 'orig';
		}
		else
		{
			$rows['tree_type'] = 'copy';
		}

		$this->db->insert('tree', $rows);
		$new_row_id = $this->db->insert_id();

		$report['new_row_id'] = $new_row_id;
		$report['new_row_parent_id'] = $rows['tree_parent_id'];
		if($childs and isset($childs[$row_id]))
		{
			foreach($childs[$row_id] as $key => $value)
			{
				$this->copyObject($key, $type_copy, TRUE, $new_row_id);
			}

		}
		return $report;

	}


	/*
	*
	* копирование множества объектов
	*/
	public function copyObjectArray($rowsID = array(), $typeCopy = 'copy', $copyChilds = false, $newParent = 0)
	{
		$res = array();
		foreach($rowsID as $RowID) {
			$res = $this->copyObject($RowID, $typeCopy, $copyChilds, $newParent);
		}
		return $res;
	}




	/**
	* change_status_row
	*
	* изменить видимость объекта
	*
	* @param	id объекта (tree_id)
	* @return	array
	*/
	public function changeStatusObj($objectID = 0)
	{
		$this->db->where('obj_id', $objectID);
		$query = $this->db->get('objects');
		if($query->num_rows())
		{
			$realStatus = $query->row('obj_status');
			$newStatus = 'hidden';
			if($realStatus == 'hidden'){
				$newStatus = 'publish';
			}

			$this->db->where('obj_id', $objectID);
			$this->db->update('objects', array('obj_status' => $newStatus));
			return $newStatus;
		}

		return FALSE;
	}

	/*
	* изменить видимость объекта массива объектов
	*/
	public function changeStatusObjArray($objectsID = array())
	{
		$result = array();
		foreach($objectsID as $objID)
		{
			$result[$objID] = $this->changeStatusObj($objID);
		}
		return $result;
	}

	/**
	* delete_object
	*
	* удалить объект
	*
	* @param	id объекта (tree_id)
	* @return	array
	*/
	public function DeleteTreeNode($row_id = 0)
	{
		$this->load->model('TreelibAdmModel');
		$childs = array();

		$this->db->where('tree_id', $row_id);
		$query = $this->db->get('tree');
		if(!$query->num_rows())
		{
			return false;
		}

		$rows = $query->row_array();
		# объект оригинальный. Полная зачистка
		if($rows['tree_type'] == 'orig')
		{
			// таблица objects
			$this->db->where('obj_id', $rows['tree_object']);
			$this->db->delete('objects');
			// таблица objects_data
			$this->db->where('objects_data_obj', $rows['tree_object']);
			$this->db->delete('objects_data');

		}


		$childs = $this->TreelibAdmModel->loadAllChildsTree($row_id);
		if($childs and isset($childs[$row_id]))
		{
			foreach($childs[$row_id] as $key => $value)
			{
				$this->DeleteTreeNode($key);
			}
		}

		if($rows['tree_type'] == 'orig')
		{
			// таблица tree
			$this->db->where('tree_object', $rows['tree_object']);
			$this->db->delete('tree');
		}
		else
		{
			# удаляем строку tree
			$this->db->where('tree_id', $row_id);
			$this->db->delete('tree');
		}
		return TRUE;
	}


	/*
	* установка статуса для видимости для ноды
	*/
	public function setNodeStatusVisible($nodeID, $status)
	{
		$data = array('obj_status' => $status);
		$this->db->join('objects', 'tree.tree_object = objects.obj_id');
		$this->db->where('tree_id', $nodeID);
		return $this->db->update('tree', $data);
	}

	// для дерева. Массив PHP для json
	public function TreeCreateArrayToJson($tree = array(), $parent = 0, $appendDemand = TRUE)
	{
		$out = array();
		if(isset($tree[$parent]))
		{
			//pr($tree[$parent]);
			$dataFields = array();
			if($tmp = app_get_option("adst_view_data", "admin-site-tree", "")) {
				$dataFields = explode(',', $tmp);
				$dataFields = array_map("trim", $dataFields);
				//pr($dataFields);
			}
			$i=0;
			foreach($tree[$parent] as $value)
			{
				$isPublish = 0;
				$datePublish = new DateTime($value['obj_date_publish']);
				//
				if($this->currentDate < $datePublish) {
					$diff = $this->currentDate->diff($datePublish);
					$isPublish = $diff->format("Через %d дней %h:%i часов"); ;
				}


				$out[$i]['name'] = $value['obj_name'];
				$out[$i]['id'] = $value['tree_id'];
				$out[$i]['copyType'] = $value['tree_type'];
				$out[$i]['objType'] = $value['obj_types_id'];
				$out[$i]['objStatus'] = $value['obj_status'];
				$out[$i]['objURL'] = $value['tree_url'];
				$out[$i]['objectID'] = $value['obj_id'];
				$out[$i]['objectAccess'] = $value['obj_ugroups_access'];
				$out[$i]['isPublish'] = $isPublish;
				$out[$i]['dataFields'] = array();

				if($dataFields) {
					foreach($dataFields as $keyField) {
						if(isset($value['data_fields'][$keyField])) {
							$out[$i]['dataFields'][] = $value['data_fields'][$keyField]['objects_data_value'];
						}
					}
				}

				if(isset($tree[$value['tree_id']]) and $appendDemand) {
					$out[$i]['load_on_demand'] = 'true';
				}

				++$i;
			}
		}
		return $out;
	}


	# возвращает ноды для дерева
	public function TreeReturnNodes($parentID = 0, $singleNode = FALSE)
	{
		$includeParent = FALSE;
		$appendDemand = TRUE;

		$loadDataFields = false;
		if(app_get_option("adst_view_data", "admin-site-tree", "")) {
			$loadDataFields = true;
		}

		$this->TreelibAdmModel->reset();
		$this->TreelibAdmModel->limit = 0;
		# если есть single_load, значит надо вернуть только массив одной ноды
		if($singleNode)
		{
			$this->TreelibAdmModel->maxLevel = 1;
			$this->TreelibAdmModel->onlyPublish = FALSE;
			$nodeInfo = $this->TreelibAdmModel->loadAllChildsTree($parentID, array(), 0, TRUE, $loadDataFields);
			// поиск текущей ноды в массиве
			foreach($nodeInfo as $nodeKey => $nodeValue)
			{
				if(isset($nodeValue[$parentID])) {
					$parentID = $nodeKey;
					break;
				}
			}
			$jsonTree = $this->TreeCreateArrayToJson($nodeInfo, $nodeKey, FALSE);
			return $jsonTree;
		}

		$this->TreelibAdmModel->maxLevel = 2;
		$this->TreelibAdmModel->onlyPublish = FALSE;
		$data = array();



		$arr_tree = $this->TreelibAdmModel->loadAllChildsTree($parentID, array(), 0, $includeParent, $loadDataFields);
		$jsonTree = $this->TreeCreateArrayToJson($arr_tree, $parentID, $appendDemand);
		return $jsonTree;
	}

	# сортирует дочерние ноды
	public function treeSortChilds($parentID = 0, $keySort = 'obj_name', $sortAsc = 'ASC')
	{
		// сначала получим объекты в запрошенном порядке
		$this->db->select('tree_id, obj_name');
		$this->db->join('objects', 'tree.tree_object = objects.obj_id');
		$this->db->where('tree_parent_id', $parentID);
		$this->db->order_by($keySort, $sortAsc);
		$query = $this->db->get('tree');
		if(!$query->num_rows()) {
			throw new Exception('Objects not found');
		}

		foreach($query->result_array() as $key => $value) {
			$this->db->where('tree_id', $value['tree_id']);
			if(!$this->db->update('tree', array('tree_order' => $key))) {
				throw new Exception('Error update');
			}
		}

		return true;
	}
}
