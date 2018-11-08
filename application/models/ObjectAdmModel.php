<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* Класс для работы с объектами
	*
	* UPD 2017-09-29
	* Version 21.7
	*
	* UPD 2017-12-04
	* Version 21.8
	* Возможность задать родителя для каждого объекта в выборке
	* $parentsObjects
	*
	* UPD 2017-12-13
	* Version 21.9
	* getChildsObjects теперь сортирует по DATA-полю, если ObjectAdmModel->orderField имеет вид data_IDПОЛЯ
	* $parentsObjects
	*
	* UPD 2017-12-18
	* Version 21.10
	* обновлен метод getObject
	*
	* UPD 2017-12-21
	* Version 21.2
	* обновлен метод getObject. Исправлена ошибка получения ID объекта по его ноде
	*
	* UPD 2017-12-25
	* Version 22.0
	* Доступ к объектам по группам
	*
	* UPD 2018-01-15
	* Version 23.0
	* Дата публикации объекта теперь в таблице objects. Таблицп нод переименована в Tree
	*
	* UPD 2018-01-25
	* Version 24.0
	* Статус объекта перенесен в таблицу objects
	*
	* UPD 2018-02-16
	* Version 24.1
	* теперь при отсутствии ветки URL до страницы, в path_url объекта пишется canonical
	*
	* UPD 2018-03-01
	* Version 24.2
	* исправлены ошибка предыдущего обновления
	*
	* UPD 2018-04-03
	* Version 24.3
	* Исправлена ошибка в SQL-запросе, когда выборка идет по группам
	*
	* UPD 2018-04-04
	* Version 24.4
	* Добавлена проверка на canonical. Если его нет, то он формируется налету
	*
	* UPD 2018-04-09
	* Version 24.5
	* Добавлена возможность выборки опубликованных или нет объектов (getObject)
	*
	* UPD 2018-04-10
	* Version 24.6
	* Добавлен метод deleteObject
	*
	* UPD 2018-05-29
	* Version 24.7
	* Исправлена ошибка отрицательного смещения в выборке (метод getChildsObjects)
	*
	* UPD 2018-07-16
	* Version 24.8
	* для метода getChildsObjects добавлена возможность сделать ключ объекта = obj_id (ObjectAdmModel->keyIdObject = true)
	*
	* UPD 2018-08-01
	* Version 25.0
	* Добавлено obj_link для объектов
	*
	* UPD 2018-08-13
	* Version 25.1
	* Исправлена ошибка обработки доступов к странице при обновлении
	*
	* UPD 2018-11-01
	* Version 25.2
	* Метод updateDataFieldsArray - при сохранении массива выпадала ошибка
	*
	* UPD 2018-11-08
	* Version 25.3
	* Метод getChildsObjects - в пагинации ошибка
	*
*/

class ObjectAdmModel extends CI_Model {

	// смещение
	public $offset;

	// лимит
	public $limit;

	// поле сортировки
	public $orderField;

	// направление сортировки
	public $orderAsc;

	// тип объекта в дереве
	public $objectTypeTree;

	// тип ноды: orig, copy, all
	public $nodeType;

	// стартовый патч урл. При построении дочернего списка надо указать патч родителя
	public $pathUrl;

	// только определенные объекты
	public $onlyObjectsID;

	// только определенные ID в дереве
	public $onlyNodeID;

	// только доступные для индексации
	public $onlyFolow;

	// только опубликованные
	public $onlyPublish;

	// не включать в выборку ID объектов
	public $notObjectsID;

	// не включать в выборку ID ноды объектов
	public $notNodeID;

	// Добавить к объектам выборки Data-поля
	public $includeDataFields;

	// колличество строк пагинации
	public $pagRows;

	// текущая страница пагинации
	public $pagCurrentPage;

	// включить пагинацию
	public $paginationTrue;

	// GET-ключ пагинации
	public $paginationKey;

	// массив пагинации
	public $paginationArray;

	// Массив родительских объектов
	public $parentsObjects;

	// влючить доступ в выборке для групп пользователей
	public $accessTrue;

	// использовать OBJ_ID как ключ объекта в выдаче
	public $keyIdObject;

	public function __construct()
    {
        parent::__construct();
		$this->reset();
    }

	/**
	* reset
	*
	* сброс
	*
	* @return
	*/
	public function reset()
	{
		$this->offset = 0;
		$this->limit = 10;
		$this->orderField = 'obj_date_publish';
		$this->orderAsc = 'asc';
		$this->objectTypeTree = 0; // тип объекта в дереве
		$this->nodeType = 'all'; // orig, copy, all
		$this->pathUrl = ''; // стартовый патч урл. При построении дочернего списка надо указать патч родителя
		$this->onlyObjectsID = array(); // только определенные объекты
		$this->onlyNodeID = array(); // только определенные ID в дереве
		$this->notObjectsID = array(); // не включать ID объектов
		$this->notNodeID = array(); // не включать row_id
		$this->includeDataFields = true;
		$this->onlyPublish = true;
		$this->pagRows = 0;
		$this->pagCurrentPage = 1;
		$this->paginationTrue = false;
		$this->paginationKey = 'page';
		$this->paginationArray = array();
		$this->parentsObjects = array(); // Массив родителей
		$this->accessTrue = true;
		$this->keyIdObject = false;
	}


	/**
	* set values
	*
	* параметры выборки
	*
	* @return
	*/
	public function set($var, $value)
	{
		if(isset($this->$var))
		{
			$this->$var = $value;
		}
	}

	/**
	* get values
	*
	* параметры выборки
	*
	* @return value
	*/
	public function get($var)
	{
		if(isset($this->$var))
		{
			return $this->$var;
		}
	}

	/**
	* getObject
	*
	* получить объект по ID объекта или его позиции в tree
	*
	* @return array
	*/
	public function getObject($objID = 0, $nodeID = 0)
	{
		if((!$nodeID and !$objID) OR ($nodeID and $objID)) return array();

		$this->db->join('objects', 'tree.tree_object = objects.obj_id');

		if($objID){
			$this->db->where('obj_id', $objID);
			$this->db->where('tree_type', 'orig');
		}else{
			$this->db->where('tree_id', $nodeID);
		}

		$query = $this->db->get('tree');

		if(!$query->num_rows())
			return array();

		if(!$objID){
			$objID = $query->row('obj_id');
		}

		$object = $query->row_array();

		//$object['parents'] = array();
		$object['data_fields'] = array();
		$object['path_url'] = '';
		$object['parent'] = array();
		unset($query);


		if($this->pathUrl){
			$object['path_url'] = $this->pathUrl . '/' . $object['tree_url'];
		}else{
			$object['path_url'] = $object['tree_url'];
		}

		# родитель. Если есть родитель, то патч от него
		$object['parent'] = array();
		if($this->parentsObjects && isset($this->parentsObjects[$object['tree_parent_id']])){
			$object['path_url'] = $this->parentsObjects[$object['tree_parent_id']]['path_url'] . '/' . $row['tree_url'];
			$object['parent'] = $this->parentsObjects[$object['tree_parent_id']];
		}


		if(!$this->includeDataFields)
			return $object;

		$this->db->select('objects_data.objects_data_value, objects_data.objects_data_obj, data_types_fields.*');
		$this->db->where('objects_data_obj', $objID);
		$this->db->where('data_types_fields.types_fields_status', 'publish');
		$this->db->join('data_types_fields', 'data_types_fields.types_fields_id = objects_data.objects_data_field');
		$query = $this->db->get('objects_data');
		foreach($query->result_array() as $row)
		{
			$object['data_fields'][$row['types_fields_id']] = $row;
		}

		return $object;
	}


	/**
	* get_childs_objects
	*
	* возвращает дочерние объекты
	*
	* @return array
	*/
	public function getChildsObjects($parentID = 0)
	{
		$this->db->join('tree', 'objects.obj_id = tree.tree_object');

		if($parentID)
		{
			if(is_array($parentID))
				$this->db->where_in('tree_parent_id', $parentID);
			else
				$this->db->where('tree_parent_id', $parentID);
		}

		if($this->onlyPublish) {
			$this->db->where('obj_status', 'publish');
			$this->db->where('obj_date_publish <=', date('Y-m-d H:i:s'));
		}

		# страница разрешена: ALL - всем, LOGIN - авторизованным, |1| только группе 1, |1|3| - только группам 1 и 3
		// для админа страница разрешена всегда

		$userGroup = $this->session->userdata('group');
		!$userGroup ? $userGroup = 0 : 0;
		if($userGroup != 2 && $this->accessTrue)
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

		if($this->objectTypeTree)
		{
			if(is_array($this->objectTypeTree))
				$this->db->where_in('tree_type_object', $this->objectTypeTree);
			else
				$this->db->where('tree_type_object', $this->objectTypeTree);
		}

		if($this->onlyObjectsID){
			$this->db->where_in('tree.tree_object', $this->onlyObjectsID);
			//$this->db->where('tree.tree_type', 'orig');
		}

		if($this->notObjectsID){
			$this->db->where_not_in('tree.tree_object', $this->notObjectsID);
		}

		if($this->onlyNodeID){
			$this->db->where_in('tree.tree_id', $this->onlyNodeID);
		}

		if($this->notNodeID){
			$this->db->where_not_in('tree.tree_id', $this->notNodeID);
		}

		if($this->nodeType != 'all'){
			$this->db->where('tree.tree_type', $this->nodeType);
		}

		if($this->onlyFolow){
			$this->db->where('tree.tree_folow', '1');
		}

		// возможно требуется сортировка по data-полю.
		if(strpos($this->orderField, 'data_') === 0)
		{
			$orderField = str_replace('data_', '', $this->orderField);
			$this->db->join('objects_data', 'objects.obj_id = objects_data.objects_data_obj');
			$this->db->where('objects_data_field', $orderField);
			$this->db->order_by('(objects_data_value+0)', $this->orderAsc);
		}
		else if($this->orderField)
		{
			$this->db->order_by($this->orderField, $this->orderAsc);
		}

		if($this->paginationTrue)
		{
			# pagination
			$offset = $this->offset;
			$activePage = 1;

			$this->pagRows = $this->db->count_all_results('objects', false);
			$this->paginationArray = app_pagination_array($this->limit, $this->pagRows, $this->paginationKey);
			$cntPages = ceil($this->pagRows / $this->limit);

			$getValues = $this->input->get();
			if(isset($getValues[$this->paginationKey]) and $getValues[$this->paginationKey] and is_numeric($getValues[$this->paginationKey]))
			{
				$activePage = intval($getValues[$this->paginationKey]);
				if($activePage > $cntPages){
					//$activePage = count($this->paginationArray);
					$offset = $this->limit * $cntPages - $this->limit;
				}else if($activePage < 2){
					$activePage = 1;
					$offset = 0;
				}else{
					$offset = $this->limit * $activePage - $this->limit;
				}
			}
			$this->pagCurrentPage = $activePage;
			// случается, что смещение становится отрицательным. Тогда делаем 0
			if($offset < 0) {
				$offset = 0;
			}

			$this->db->limit($this->limit, $offset);
		}
		else
		{
			$this->db->from('objects');
			$this->db->limit($this->limit, $this->offset);
		}

		$sql = $this->db->get_compiled_select();
		$query = $this->db->query($sql);

		//return $query->result_array();
		if(!$query->result_array()) return array();

		$objects_id = array();
		$objects = array();
		foreach($query->result_array() as $row)
		{
			$keyID = $row['tree_id'];
			if($this->keyIdObject) {
				$keyID = $row['obj_id'];
			}
			$objects[$keyID] = $row;
			$objects[$keyID]['data_fields'] = array();
			$isLink = FALSE; // флаг, что объект - это ссылка
			# формирование ссылки
			if($this->pathUrl){
				// есть ссылка, патч от него
				$objects[$keyID]['path_url'] = $this->pathUrl . '/' . $row['tree_url'];
			}elseif($objects[$keyID]['tree_type_object'] == 4){
				// объект - это ссылка. В ней path пишется вручную. Добавляем как есть
				$objects[$keyID]['path_url'] = '/' . $row['tree_url'];
				$isLink = TRUE;
			}else{
				// если родителя нет и объект - НЕ ССЫЛКА, то path_url = canonical
				if(!$row['obj_canonical']){
					$url = $this->CommonModel->loadParentsUrlsFromNodeID($row['tree_id']);
					if(isset($url[0]) && $url[0] == 'index'){
						unset($url[0]);
					}
					if($url){
						$objects[$keyID]['path_url'] = '/' . implode('/', $url);
					}
				}else{
					$objects[$keyID]['path_url'] = '/' . $row['obj_canonical'];
				}
			}

			# родитель. Если есть родитель, то патч от него
			$objects[$keyID]['parent'] = array();
			//pr($this->parentsObjects);
			if($this->parentsObjects && isset($this->parentsObjects[$row['tree_parent_id']])){
				// если это не ссылка, то формируем патч
				if(!$isLink){
					$objects[$keyID]['path_url'] = $this->parentsObjects[$row['tree_parent_id']]['path_url'] . '/' . $row['tree_url'];
				}
				$objects[$keyID]['parent'] = $this->parentsObjects[$row['tree_parent_id']];
			}

			$objects_id[] = $row['obj_id'];
		}

		if(!$this->includeDataFields)
			return $objects;

		$data_fields = array();

		$this->db->select('objects_data.objects_data_value, objects_data.objects_data_obj, data_types_fields.*');
		$this->db->where_in('objects_data_obj', $objects_id);
		$this->db->where('data_types_fields.types_fields_status', 'publish');
		$this->db->join('data_types_fields', 'data_types_fields.types_fields_id = objects_data.objects_data_field');
		$query = $this->db->get('objects_data');
		foreach($query->result_array() as $row)
		{
			//$data_fields[$row['objects_data_field']] = $row['objects_data_value'];
			//$data_fields[$row['objects_data_obj']][$row['types_fields_id']] = app_normalize_data_values($row);
			$data_fields[$row['objects_data_obj']][$row['types_fields_id']] = $row;
		}

		$object = array();
		foreach($objects as &$object)
		{
			if(isset($data_fields[$object['obj_id']]))
				$object['data_fields'] = $data_fields[$object['obj_id']];
		}
		unset($object);

		return $objects;
	}

	/**
	* Обновить объект
	*
	* @param array - параметры обновления
	* @param int - id объекта
	*
	* @return bool
	*/
	public function updateObject($par = array(), $obj_id = 0)
	{
		if(!$obj_id) return false;
		$data = array();

		isset($par['data_type']) ? 					$data['obj_data_type'] = $par['data_type'] : 0;
		isset($par['canonical']) ? 					$data['obj_canonical'] = $par['canonical'] : 0;
		(isset($par['name']) and $par['name']) ? 	$data['obj_name'] = $par['name'] : 0;
		(isset($par['h1']) and $par['h1']) ? 		$data['obj_h1'] = $par['h1'] : 0;
		isset($par['title']) ? 						$data['obj_title'] = $par['title'] : 0;
		isset($par['description']) ? 				$data['obj_description'] = $par['description'] : 0;
		isset($par['keywords']) ? 					$data['obj_keywords'] = $par['keywords'] : 0;
		isset($par['anons']) ? 						$data['obj_anons'] = $par['anons'] : 0;
		isset($par['content']) ? 					$data['obj_content'] = $par['content'] : 0;
		isset($par['date_publish']) ? 				$data['obj_date_publish'] = $par['date_publish'] : 0;
		isset($par['cnt_views']) ? 					$data['obj_cnt_views'] = $par['cnt_views'] : 0;
		isset($par['rating_up']) ? 					$data['obj_rating_up'] = $par['rating_up'] : 0;
		isset($par['rating_down']) ? 				$data['obj_rating_down'] = $par['rating_down'] : 0;
		isset($par['rating_count']) ? 				$data['obj_rating_count'] = $par['rating_count'] : 0;
		isset($par['user_author']) ? 				$data['obj_user_author'] = $par['user_author'] : 0;
		isset($par['tpl_content']) ? 				$data['obj_tpl_content'] = $par['tpl_content'] : 0;
		isset($par['tpl_page']) ? 					$data['obj_tpl_page'] = $par['tpl_page'] : 0;
		isset($par['status']) ? 					$data['obj_status'] = $par['status'] : 0;
		isset($par['link']) ? 					$data['obj_link'] = $par['link'] : 0;

		$data['obj_lastmod'] = time();

		# доступы
		if(isset($par['ugroups_access']))
		{
			# если доступы в виде строки, то пишем как есть, иначе обработка
			if(is_array($par['ugroups_access'])) {
				// если в массиве есть ALL, то ставим только его
				if(in_array('ALL', $par['ugroups_access'])){
					$data['obj_ugroups_access'] = 'ALL';
				}else if(in_array('LOGIN', $par['ugroups_access'])){
					// если в массиве зарегистрированные, ставим LOGIN
					$data['obj_ugroups_access'] = 'LOGIN';
				}else{
					$data['obj_ugroups_access'] = implode('|', $par['ugroups_access']);
					$data['obj_ugroups_access'] = '|' . $data['obj_ugroups_access'] . '|';
				}
			} else {
				$data['obj_ugroups_access'] = $par['ugroups_access'];
			}
		}

		$this->db->where('obj_id', $obj_id);
		return $this->db->update('objects', $data);
	}

	/**
	* update data field
	*
	* обновление data-полей объекта
	*
	* @return bool
	*/
	// обновление значения data-поля для объекта
	public function updateDataField($obj_id, $field_id, $value)
	{
		$this->db->where('objects_data_obj', $obj_id);
		$this->db->where('objects_data_field', $field_id);
		$query = $this->db->get('objects_data');

		if($query->num_rows())
		{
			$data = array(
					'objects_data_value' => $value
				);
			$this->db->where('objects_data_obj', $obj_id);
			$this->db->where('objects_data_field', $field_id);
			return $this->db->update('objects_data', $data);
		}
		else
		{
			$data = array(
				'objects_data_obj' => $obj_id,
				'objects_data_field' => $field_id,
				'objects_data_value' => $value,
				);
			return $this->db->insert('objects_data', $data);
		}


	}

	/**
	* edit data field
	*
	* добавление/обновление массива data-полей объекта
	*
	* @return bool
	*/
	public function editDataField($fields = array(), $obj_id = 0){
		return $this->updateDataFieldsArray($fields, $obj_id);
	}
	public function updateDataFieldsArray($fields = array(), $obj_id = 0)
	{
		if(!$obj_id OR !$fields) return false;
		$this->db->where('objects_data_obj', $obj_id);
		$this->db->delete('objects_data');

		foreach($fields as $key => $value)
		{
			if(is_array($value)) {
				$value = implode(',', $value);
			}

			$data = array(
				'objects_data_obj' => $obj_id,
				'objects_data_field' => $key,
				'objects_data_value' => $value,
				);
			$ins = $this->db->insert('objects_data', $data);
		}
		return $ins;
	}

	/**
	* создание объекта
	*
	* @param array - параметры
	*
	* @return int
	*/
	public function addObject($par = array())
	{
		if(!$par) return false;
		$data = array();

		$data['obj_data_type'] = 		isset($par['data_type']) ? $par['data_type'] : 1;
		$data['obj_canonical'] = 		isset($par['canonical']) ? $par['canonical'] : '';
		$data['obj_name'] = 			(isset($par['name']) and $par['name']) ? $par['name'] : 'NO NAME';
		$data['obj_h1'] = 				(isset($par['h1']) and $par['h1']) ? $par['h1'] : 'NO TITLE';
		$data['obj_title'] = 			isset($par['title']) ? $par['title'] : 'NO TITLE';
		$data['obj_description'] = 		isset($par['description']) ? $par['description'] : '';
		$data['obj_keywords'] = 		isset($par['keywords']) ? $par['keywords'] : '';
		$data['obj_anons'] = 			isset($par['anons']) ? $par['anons'] : '';
		$data['obj_content'] = 			isset($par['content']) ? $par['content'] : '';
		$data['obj_cnt_views'] = 		isset($par['cnt_views']) ? $par['cnt_views'] : 0;
		$data['obj_rating_up'] = 		isset($par['rating_up']) ? $par['rating_up'] : 0;
		$data['obj_rating_down'] = 		isset($par['rating_down']) ? $par['rating_down'] : 0;
		$data['obj_rating_count'] = 	isset($par['rating_count']) ? $par['rating_count'] : 0;
		$data['obj_user_author'] = 		isset($par['user_author']) ? $par['user_author'] : 1;
		$data['obj_tpl_content'] = 		isset($par['tpl_content']) ? $par['tpl_content'] : '';
		$data['obj_tpl_page'] = 		isset($par['tpl_page']) ? $par['tpl_page'] : '';
		$data['obj_date_publish'] = 	isset($par['date_publish']) ? $par['date_publish'] : FALSE;
		$data['obj_status'] = 			isset($par['status']) ? $par['status'] : 'publish';
		$data['obj_link'] = 			isset($par['link']) ? $par['link'] : '';

		$data['obj_date_create'] = date('Y-m-d H:i:s');
		$data['obj_lastmod'] = time();

		if(!$data['obj_date_publish'])
			$data['obj_date_publish'] = $data['obj_date_create'];

		# доступы
		if(isset($par['ugroups_access']))
		{
			// если в массиве есть ALL, то ставим только его
			if(in_array('ALL', $par['ugroups_access'])){
				$data['obj_ugroups_access'] = 'ALL';
			}else if(in_array('LOGIN', $par['ugroups_access'])){
				// если в массиве зарегистрированные, ставим LOGIN
				$data['obj_ugroups_access'] = 'LOGIN';
			}else{
				$data['obj_ugroups_access'] = implode('|', $par['ugroups_access']);
				$data['obj_ugroups_access'] = '|' . $data['obj_ugroups_access'] . '|';
			}
		}else{
			$data['obj_ugroups_access'] = 'ALL';
		}

		return 	$this->db->insert('objects', $data) ? $this->db->insert_id() : 0;
	}

	/*
	* get objects from ID array
	*
	* возвращает массвив объектов из массива ID
	*
	* return array()
	*/
	public function getObjectsFromId($ids = array())
	{
		if(!$ids) return array();

		$objects = array();

		$this->db->where_in('obj_id', $ids);
		$query = $this->db->get('objects');
		foreach($query->result_array() as $row)
		{
			$objects[$row['obj_id']] = $row;
		}
		$row = array();
		$data_fields = array();

		//$data_fields[$row['objects_data_obj']][$row['types_fields_id']] = $row;
		$this->db->select('objects_data.objects_data_value, objects_data.objects_data_obj, data_types_fields.*');
		$this->db->join('data_types_fields', 'data_types_fields.types_fields_id = objects_data.objects_data_field');
		$this->db->where_in('objects_data_obj', $ids);
		$query = $this->db->get('objects_data');
		foreach($query->result_array() as $row)
		{
			$data_fields[$row['objects_data_obj']][$row['types_fields_id']] = $row;
		}
		$row = array();

		foreach($objects as $key => &$value)
		{
			if(isset($data_fields[$key])) {
				$value['data_fields'] = $data_fields[$key];
			} else {
				$value['data_fields'] = array();
			}
		}
		unset($value);

		return $objects;
	}

	/*
	* getObjectsDataFields
	*
	* возвращает все data поля для массива объектов
	*
	* param		ID объектов
	* param		Свои условия выборки
	* param 	Вернуть одномерный массив
	*
	* return array
	*/
	public function getObjectsDataFields($objID, $condition = array(), $single = FALSE, $order = 'ASC')
	{
		$out = array();
		$this->db->join('data_types_fields', 'objects_data.objects_data_field = data_types_fields.types_fields_id');

		if($objID)
		{
			if(is_array($objID))
				$this->db->where_in('objects_data_obj', $objID);
			else
				$this->db->where('objects_data_obj', $objID);
		}

		if($condition)
		{
			foreach($condition as $key => $value) {
				$this->db->where($key, $value);
			}
		}
		$this->db->order_by('types_fields_order', $order);
		$query = $this->db->get('objects_data');
		foreach($query->result_array() as $row) {
			if($row['types_fields_type'] == 'select')
					$row['types_fields_values'] = app_parse_select_values($row['types_fields_values']);

			if($single)
				$out[$row['types_fields_id']] = $row;
			else
				$out[$row['objects_data_obj']][$row['types_fields_id']] = $row;
		}
		return $out;
	}

	/**
	*
	* удалить объект из системы
	*
	*/
	public function deleteObject($objectID = 0)
	{
		// проверим объект на существование
		$this->db->select('obj_id');
		$this->db->where('obj_id', $objectID);
		$query = $this->db->get('objects');
		if($query->num_rows()) {
			$objectID = $query->row('obj_id');
			// сначала удалим сам объект
			$this->db->where('obj_id', $objectID);
			$query = $this->db->delete('objects');

			// далее удалим объект в дереве
			$this->db->where('tree_object', $objectID);
			$query = $this->db->delete('tree');

			// и удалим свойства объекта
			$this->db->where('objects_data_obj', $objectID);
			$query = $this->db->delete('objects_data');

			return $objectID;
		} else {
			return FALSE;
		}
	}


}
