<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* common model
	* Version 3.0
	* UPD 2017-10-09
	*
	* Version 3.1
	* UPD 2017-12-13
	* getOnlyPublish
	*
	* Version 4.0
	* UPD 2017-12-25
	* запрос по разрешенным страницам (getObjectsFromURL)
	*
	* Version 5.0
	* UPD 2018-01-15
	* изменена таблица tree
	*
	* UPD 2018-01-25
	* Version 6.0
	* Статус объекта перенесен в таблицу objects
 	*
	* UPD 2018-03-29
	* Version 6.1
	* исправлена ошибка в сортировке в методе getAllDataTypes (поле data_types_order)
	*
	* UPD 2018-04-03
	* Version 6.3
	* исправлена ошибка в SQL запросе когда выборка идет по группам пользователя. Добавлены методы работы с нодами
	*
	* UPD 2018-08-29
	* Version 6.4
	* Добавлен метод runIndexesSite() индексации сайта для обновления полей AXIS и CANONICAL
	*
	* UPD 2018-09-24
	* Version 6.5
	* Ошибка вычисления axis при индексации сайта
	*
	* UPD 2018-10-09
	* Version 6.6
	* Добавлен метод runIndexesDates() - проверка корректных дат у объектов и исправление
	*
	* UPD 2018-10-23
	* Version 6.7
	* правки - типы данных получени по статусу
	*
*/

class CommonModel extends CI_Model {

	// выгрузка только опубликованных
	public $getOnlyPublish;

	public function __construct()
    {
        parent::__construct();
		$this->reset();
	}

	public function reset()
	{
		$this->getOnlyPublish = false;
		$this->indexArray = $this->indexOutArray = array();
	}

	# формирование главной
	public function loadIndexPage()
	{
		return $this->getObjectsFromURL(array('index'));
	}

	# формирование страниц по урлу
	public function loadPagesFromURL($URL = array())
	{
		if(!$URL){
			return $this->loadIndexPage();
		}
		return $this->getObjectsFromURL($URL);
	}

	# возвращает объекты по массиву урлов
	private function getObjectsFromURL($URLarray = array())
	{
		//$this->fields_select();
		$this->db->join('tree', 'objects.obj_id = tree.tree_object');
		$this->db->where_in('tree_url', $URLarray);
		$this->db->where('obj_status', 'publish');
		$this->db->where('obj_date_publish <=', date('Y-m-d H:i:s'));

		$userGroup = $this->session->userdata('group');
		!$userGroup ? $userGroup = 0 : 0;

		# страница разрешена: ALL - всем, LOGIN - авторизованным, |1| только группе 1, |1|3| - только группам 1 и 3
		// для админа страница разрешена всегда
		if($userGroup != 2)
		{
			$this->db->group_start();
			$this->db->like('objects.obj_ugroups_access', 'ALL', 'none');
			// если есть группа, то запросим разрешенные страницы
			if($userGroup){
				$this->db->or_like('objects.obj_ugroups_access', 'LOGIN', 'none');
				$this->db->or_like('objects.obj_ugroups_access', '|'.$userGroup.'|', 'both');
			}
			$this->db->group_end();
		}

		$query = $this->db->get('objects');

		if(!$query->result_array()) {
			return array();
		}

		$objects_id = array();
		$objects = array();
		foreach($query->result_array() as $row) {
			$objects[$row['tree_parent_id']][$row['tree_id']] = $row;
			$objects_id[] = $row['obj_id'];
		}

		$data_fields = array();

		$this->db->join('data_types_fields', 'data_types_fields.types_fields_id = objects_data.objects_data_field');
		$this->db->select('objects_data.objects_data_value, objects_data.objects_data_obj, data_types_fields.*');
		$this->db->where_in('objects_data_obj', $objects_id);
		$this->db->where('data_types_fields.types_fields_status', 'publish');
		$this->db->order_by('types_fields_order', 'ASC');

		$query = $this->db->get('objects_data');
		foreach($query->result_array() as $row) {
			$data_fields[$row['objects_data_obj']][$row['types_fields_id']] = $row;
		}

		foreach($objects as &$object)
		{
			foreach($object as &$obj)
			{
				$obj['data_fields'] = array();
				if(isset($data_fields[$obj['obj_id']]))
					$obj['data_fields'] = $data_fields[$obj['obj_id']];
			}
		}
		unset($object, $obj);
		return $objects;
	}

	/**
	* получить все data-fields для объектов
	* $front и $stream - для условий выборки
	*/
	public function getAllDataTypesFields($typeID = FALSE, $onlyID = array(), $flag1 = FALSE, $flag2 = FALSE, $flag3 = FALSE, $flag4 = FALSE)
	{
		$out = array();

		$this->db->join('data2data', 'data_types_fields.types_fields_id = data2data.data2data_field_id', 'left');
		if($typeID) {
			if(is_array($typeID)) {
				$this->db->where_in('data2data_type_id', $typeID);
			} else {
				$this->db->where('data2data_type_id', $typeID);
			}
		}

		if($this->getOnlyPublish){
			//$this->db->where('data_types_status', 'publish');
			$this->db->where('types_fields_status', 'publish');
		}

		if($onlyID) {
			$this->db->where_in('types_fields_id', $onlyID);
		}

		if($flag1) {
			$this->db->where('types_fields_flag1', 1);
		}

		if($flag2) {
			$this->db->where('types_fields_flag2', 1);
		}

		if($flag3) {
			$this->db->where('types_fields_flag3', 1);
		}

		if($flag4) {
			$this->db->where('types_fields_flag4', 1);
		}

		$this->db->order_by('types_fields_order', 'asc');
		$query = $this->db->get('data_types_fields');
		foreach($query->result_array() as $row)
		{
			$out[$row['types_fields_id']]['types_fields_id'] = $row['types_fields_id'];
			$out[$row['types_fields_id']]['types_fields_type'] = $row['types_fields_type'];
			$out[$row['types_fields_id']]['types_fields_name'] = $row['types_fields_name'];
			$out[$row['types_fields_id']]['types_fields_unit'] = $row['types_fields_unit'];
			$out[$row['types_fields_id']]['types_fields_values'] = $row['types_fields_values'];
			$out[$row['types_fields_id']]['types_fields_default'] = $row['types_fields_default'];
			$out[$row['types_fields_id']]['types_fields_order'] = $row['types_fields_order'];
			$out[$row['types_fields_id']]['types_fields_status'] = $row['types_fields_status'];

			$out[$row['types_fields_id']]['types_fields_flag1'] = $row['types_fields_flag1'];
			$out[$row['types_fields_id']]['types_fields_flag2'] = $row['types_fields_flag2'];
			$out[$row['types_fields_id']]['types_fields_flag3'] = $row['types_fields_flag3'];
			$out[$row['types_fields_id']]['types_fields_flag4'] = $row['types_fields_flag4'];

			if($row['data2data_id']) {
				$out[$row['types_fields_id']]['data_types'][$row['data2data_type_id']] = $row['data2data_type_id'];
			} else {
				$out[$row['types_fields_id']]['data_types'] = array();
			}
		}
		return $out;
	}

	/**
	* получить все типы данных
	*/
	public function getAllDataTypes($typeID = 0, $publish = true)
	{
		$out = array();
		if($typeID) {
			$this->db->where('data_types_id', $typeID);
		}

		if($publish) {
			$this->db->where('data_types_status', 'publish');
		}

		$this->db->order_by('data_types_order', 'ASC');
		$query = $this->db->get('data_types');

		if($typeID) {
			return $query->row_array();
		}

		foreach($query->result_array() as $row) {
			$out[$row['data_types_id']] = $row;
		}
		return $out;
	}

	/**
	* получить все типы данных включая их поля
	*/
	public function getAllDataTypesFull($publish = true)
	{
		$data_types = $this->getAllDataTypes();
		$data_types_id = array_keys($data_types);

		$fields = array();
		$this->db->join('data2data', 'data_types_fields.types_fields_id = data2data.data2data_field_id');
		$this->db->where_in('data2data_type_id', $data_types_id);

		if($publish) {
			$this->db->where('types_fields_status', 'publish');
		}

		$this->db->order_by('types_fields_order', 'asc');

		$query = $this->db->get('data_types_fields');
		foreach($query->result_array() as $row) {
			$fields[$row['data2data_type_id']][$row['types_fields_id']] = $row;
		}

		foreach($data_types as $key => &$value) {
			$value['fields'] = array();
			if(isset($fields[$key])) {
				$value['fields'] = $fields[$key];
			}

		}
		unset($value);
		return $data_types;
	}

	/**
	* создать новый тип данных create_new_type_data
	*/
	public function createDataType($args = array())
	{
		$data = array();
		isset($args['types_name']) ? $data['data_types_name'] = $args['types_name'] : $data['data_types_name'] = 'Empty';
		isset($args['types_status']) ? $data['data_types_status'] = $args['types_status'] : $data['data_types_status'] = 'hidden';
		isset($args['types_descr']) ? $data['data_types_descr'] = $args['types_descr'] : $data['data_types_descr'] = '';
		isset($args['types_order']) ? $data['data_types_order'] = $args['types_order'] : $data['data_types_order'] = 0;

		# проверка
		$this->db->where('data_types_name', $data['data_types_name']);
		$query = $this->db->get('data_types');
		if($query->num_rows()) {
			return false;
		}
		return $this->db->insert('data_types', $data);
	}

	/**
	* создать новое поле типа данных create_new_data_field
	*/
	public function createDataTypeField($args = array())
	{
		$data = array();
		//isset($args['fields_data']) ? $data['types_fields_data'] = $args['fields_data'] : $data['types_fields_data'] = 0;
		isset($args['fields_type']) ? $data['types_fields_type'] = $args['fields_type'] : $data['types_fields_type'] = 'text';
		isset($args['fields_name']) ? $data['types_fields_name'] = $args['fields_name'] : $data['types_fields_name'] = 'Empty';
		isset($args['fields_values']) ? $data['types_fields_values'] = $args['fields_values'] : $data['types_fields_values'] = '';
		isset($args['fields_default']) ? $data['types_fields_default'] = $args['fields_default'] : $data['types_fields_default'] = '';
		isset($args['fields_order']) ? $data['types_fields_order'] = $args['fields_order'] : $data['types_fields_order'] = '0';
		isset($args['fields_status']) ? $data['types_fields_status'] = $args['fields_status'] : $data['types_fields_status'] = 'publish';
		isset($args['fields_unit']) ? $data['types_fields_unit'] = $args['fields_unit'] : $data['types_fields_status'] = '';

		isset($args['flag1']) ? $data['types_fields_flag1'] = $args['flag1'] : $data['types_fields_flag1'] = 0;
		isset($args['flag2']) ? $data['types_fields_flag2'] = $args['flag2'] : $data['types_fields_flag2'] = 0;
		isset($args['flag3']) ? $data['types_fields_flag3'] = $args['flag3'] : $data['types_fields_flag3'] = 0;
		isset($args['flag4']) ? $data['types_fields_flag4'] = $args['flag4'] : $data['types_fields_flag4'] = 0;

		# проверка
		$this->db->where('types_fields_name', $data['types_fields_name']);
		$query = $this->db->get('data_types_fields');
		if($query->num_rows()) {
			return false;
		}
		$this->db->insert('data_types_fields', $data);
		return  $this->db->insert_id();
	}

	/**
	* создать связь типа данных и поля create_data2data
	*/
	public function createData2Data($type_id, $field_id)
	{
		$this->db->where('data2data_type_id', $type_id);
		$this->db->where('data2data_field_id', $field_id);
		$query = $this->db->get('data2data');
		if($query->num_rows()) {
			return false;
		}

		$data = array (
			'data2data_type_id' => $type_id,
			'data2data_field_id' => $field_id,
			);
		$this->db->insert('data2data', $data);
		return  $this->db->insert_id();
	}

	/**
	* удалить связь типа данных и поля delete_data2data
	*/
	public function deleteData2Data($type_id, $field_id)
	{
		$this->db->where('data2data_type_id', $type_id);
		$this->db->where('data2data_field_id', $field_id);
		return  $this->db->delete('data2data');
	}

	/**
	* далить тип данных delete_type_data
	*/
	public function deleteDataType($type_id = 0)
	{
		if(!$type_id) {
			return false;
		}
		$this->db->where('data_types_id', $type_id);
		$this->db->delete('data_types');

		$this->db->where('data2data_type_id', $type_id);
		$this->db->delete('data2data');

		$this->db->where('obj_data_type', $type_id);
		$this->db->update('objects', array('obj_data_type' => '1'));
		return true;
	}

	/**
	* удалить поле типа данных delete_type_data_field
	*/
	public function deleteDataTypeField($field_id = 0)
	{
		if(!$field_id) {
			return false;
		}

		$this->db->where('types_fields_id', $field_id);
		$del = $this->db->delete('data_types_fields');
		if($del) {
			# удалим поле у страниц
			$this->db->where('objects_data_field', $field_id);
			$del = $this->db->delete('objects_data');
		}
		return true;
	}

	/**
	* обновить тип данных update_type_data
	*/
	public function updateDataType($args = array(), $type_id = 0)
	{
		if(!$type_id) {
			return false;
		}

		$data = array();
		isset($args['types_name']) ? $data['data_types_name'] = $args['types_name'] : 0;
		isset($args['types_status']) ? $data['data_types_status'] = $args['types_status'] : 0;
		isset($args['types_descr']) ? $data['data_types_descr'] = $args['types_descr'] : 0;
		isset($args['types_order']) ? $data['data_types_order'] = $args['types_order'] : 0;

		if($data) {
			$this->db->where('data_types_id', $type_id);
			return $this->db->update('data_types', $data);
		}
	}

	/**
	* обновить поле типа данных update_type_data_field
	*/
	public function updateDataTypeField($args = array(), $field_id = 0)
	{
		$data = array();
		if(!$field_id) {
			return false;
		}
		// если галочка на удаление
		if(isset($args['delete_field'])) {
			return $this->deleteDataTypeField($field_id);
		}

		//isset($args['fields_data']) ? $data['types_fields_data'] = $args['fields_data'] : 0;
		isset($args['fields_type']) ? $data['types_fields_type'] = $args['fields_type'] : 0;
		isset($args['fields_name']) ? $data['types_fields_name'] = $args['fields_name'] : 0;
		isset($args['fields_values']) ? $data['types_fields_values'] = $args['fields_values'] : 0;
		isset($args['fields_default']) ? $data['types_fields_default'] = $args['fields_default'] : 0;
		isset($args['fields_order']) ? $data['types_fields_order'] = $args['fields_order'] : 0;
		isset($args['fields_status']) ? $data['types_fields_status'] = $args['fields_status'] : 0;
		isset($args['fields_unit']) ? $data['types_fields_unit'] = $args['fields_unit'] : 0;

		isset($args['flag1']) ? $data['types_fields_flag1'] = $args['flag1'] : 0;
		isset($args['flag2']) ? $data['types_fields_flag2'] = $args['flag2'] : 0;
		isset($args['flag3']) ? $data['types_fields_flag3'] = $args['flag3'] : 0;
		isset($args['flag4']) ? $data['types_fields_flag4'] = $args['flag4'] : 0;

		if($data) {
			$this->db->where('types_fields_id', $field_id);
			return $this->db->update('data_types_fields', $data);
		}
	}

	/*
	* возвращает все типы объектов
	*/
	public function getAllObjTypes()
	{
		$out = array();
		$query = $this->db->get('obj_types');
		foreach($query->result_array() as $row) {
			$out[$row['obj_types_id']] = $row;
		}
		return $out;
	}

	/**
	* обновление типов объектов update_types_objects
	*/
	public function updateObjType($typeID, $args = array())
	{
		$data = array();
		isset($args['name']) ? $data['obj_types_name'] = $args['name'] : 0;
		isset($args['icon']) ? $data['obj_types_icon'] = $args['icon'] : 0;
		isset($args['descr']) ? $data['obj_types_descr'] = $args['descr'] : 0;

		if($data) {
			$this->db->where('obj_types_id', $typeID);
			$this->db->update('obj_types', $data);
		}

		return TRUE;
	}

	/**
	* сиздание типов объектов add_types_objects
	*/
	public function createObjType($args = array())
	{
		$data = array();
		isset($args['name']) ? $data['obj_types_name'] = $args['name'] : $data['obj_types_name'] = 'NO NAME';
		isset($args['icon']) ? $data['obj_types_icon'] = $args['icon'] : $data['obj_types_icon'] = '';
		isset($args['descr']) ? $data['obj_types_descr'] = $args['descr'] : $data['obj_types_descr'] = '';

		if($data) {
			$this->db->insert('obj_types', $data);
		}

		return TRUE;
	}

	# загрузка цепочки URI для объекта
	public function loadParentsUrlsFromNodeID($nodeID = 0, $parentID = 0, $urls = array())
	{
		$this->db->select('tree_url, tree_id, tree_parent_id');

		if($parentID) {
			$this->db->where('tree_id', $parentID);
		} else {
			$this->db->where('tree_id', $nodeID);
		}

		$query = $this->db->get('tree');
		if($query->num_rows()) {
			$urls[] = $query->row('tree_url');
			return $this->loadParentsUrlsFromNodeID(0, $query->row('tree_parent_id'), $urls);
		} else {
			if($urls) {
				$urls = array_reverse($urls);
			}

			return $urls;
		}
	}

	# возвращает nodeID для короткой ссылки
	public function getNodeIdFromShortLink($link = '')
	{
		if(!$link) {
			return 0;
		}

		$this->db->where('tree_short', $link);
		$query = $this->db->get('tree');

		if($query->num_rows() == 1) {
			return $query->row('tree_id');
		}else{
			return 0;
		}
	}

	# запуск процесса индексации сайта
	public function runIndexesSite()
	{
		$this->db->select('tree_id, tree_parent_id, tree_url, tree_object, tree_type');
		$this->db->order_by('tree_parent_id', 'DESC');
		// /$this->db->limit(1000);
		$query = $this->db->get('tree');

		$RESULT = array();
		foreach($query->result_array() as $row) {
			$RESULT[$row['tree_id']]['parent_id'] = $row['tree_parent_id'];
			$RESULT[$row['tree_id']]['object_id'] = $row['tree_object'];
			$RESULT[$row['tree_id']]['link'] = $row['tree_url'];
			$RESULT[$row['tree_id']]['tree_type'] = $row['tree_type'];
		}

		$parentsPrepare = array();

		foreach($RESULT as $nodeID => $parentInfo) {
			$parentID = $parentInfo['parent_id'];
			$tmpParent = 0;
			$parentsPrepare[$nodeID]['parents'][] = $parentID;

			# если объект оригинальный, для него надо будет обновить canonical
			if($parentInfo['tree_type'] == 'orig') {
				$parentsPrepare[$nodeID]['urls'][] = $parentInfo['link'];
				$parentsPrepare[$nodeID]['object_id'] = $parentInfo['object_id'];
			}


			if(isset($RESULT[$parentID])) {
				$parentsPrepare[$nodeID]['parents'][] = $RESULT[$parentID]['parent_id'];

				// для оригиналов строим цепочку урлов
				if(isset($parentsPrepare[$nodeID]['object_id'])) {
					$parentsPrepare[$nodeID]['urls'][] = $RESULT[$parentID]['link'];
				}

				$parentID = $RESULT[$parentID]['parent_id'];
				$this->_indexesSitePrep($nodeID, $parentID, $RESULT, $parentsPrepare);
			}
		}

		# теперь собственно обновление таблиц
		foreach($parentsPrepare as $nodeID => $nodeInfo) {
			$axis = array_reverse($nodeInfo['parents']);
			$axis[] = $nodeID;
			unset($axis[0]);

			// сначала обновим AXIS
			$data = array('tree_axis' => '|' . implode('|', $axis) . '|');
			$this->db->where('tree_id', $nodeID);
			$this->db->update('tree', $data);

			// если объект оригинальный, то надо обновить canonical
			if(isset($nodeInfo['object_id'])) {
				$urls = array_reverse($nodeInfo['urls']);
				// для главной index надо оставить
				if(count($urls) > 1 && $nodeInfo['object_id'] != 1) {
					unset($urls[0]);
				}
				$data = array('obj_canonical' => implode('/', $urls));
				$this->db->where('obj_id', $nodeInfo['object_id']);
				$this->db->update('objects', $data);
			}
		}
		return true;
	}

	# обработка массива индекса
	private function _indexesSitePrep($nodeID, $tmpParent, $allObjects = array(), &$tmpUrls)
	{
		if(isset($allObjects[$tmpParent])) {
			$tmpUrls[$nodeID]['parents'][] = $allObjects[$tmpParent]['parent_id'];

			// для оригиналов строим цепочку урлов
			if(isset($tmpUrls[$nodeID]['object_id'])) {
				$tmpUrls[$nodeID]['urls'][] = $allObjects[$tmpParent]['link'];
			}

			$tmpParent = $allObjects[$tmpParent]['parent_id'];
			$this->_indexesSitePrep($nodeID, $tmpParent, $allObjects, $tmpUrls);
		}
	}

	# перепись дат публикации и последнего изменения
	public function runIndexesDates()
	{
		$lastMod = time();
		$dateCreate = date('Y-m-d H:i:s');
		$this->db->select('obj_id, obj_date_create, obj_date_publish, obj_lastmod');
		$query = $this->db->get('objects');
		foreach($query->result_array() as $row) {
			$dataUpd = array();
			if(!$row['obj_lastmod']) {
				$dataUpd['obj_lastmod'] = $lastMod;
			}

			if($row['obj_date_create'] == '0000-00-00 00:00:00') {
				$dataUpd['obj_date_create'] = $dateCreate;
			}

			if($row['obj_date_publish'] == '0000-00-00 00:00:00') {
				if(isset($dataUpd['obj_date_create'])) {
					$dataUpd['obj_date_publish'] = $dataUpd['obj_date_create'];
				} else {
					$dataUpd['obj_date_publish'] = $dateCreate;
				}
			}

			if($dataUpd) {
				$this->db->where('obj_id', $row['obj_id']);
				$this->db->update('objects', $dataUpd);
			}
		}
		return true;
	}
}
