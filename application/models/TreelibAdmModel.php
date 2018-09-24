<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* Модель содержит методы для работы с деревом объекта сайта

	* DATE UPD: 2017-11-22
	* version 2.53

	* DATE UPD: 2017-12-11
	* version 2.54
	* Исправлено - если loadAllChilds загружать несколько копий одного объекта, data-fields терялись

	* DATE UPD: 2017-12-25
	* version 3.0
	* Добавлено accessTrue

	* DATE UPD: 2017-12-28
	* version 3.1
	* Изменены поля выборки selectedFields

	* Version 4.0
	* UPD 2018-01-15
	* изменена таблица tree

	* Version 4.1
	* UPD 2018-03-01
	* Исправлена ошибка выборки страницы по группам пользователей $this->loadAllChildsTree
	* loadAllChildsTree() - при отсутствии родительских URL, в path_url пишется canonical

	* Version 4.2
	* UPD 2018-05-02
	* Возможность отключения лимита выборки и сортировки в методе $this->loadAllChildsTree
	*
	* Version 5
	* UPD 2018-08-01
	* Добавлено tree_axis
	*
	* Version 5.1
	* UPD 2018-09-06
	* исправлена ошибка формирования url в методе loadAllChildsTree
	*
	* Version 5.2
	* UPD 2018-09-24
	* исправлена ошибка в формировании axis
*/

class TreelibAdmModel extends CI_Model {

	public $TreeChilds = array();
	public $TreeParent = array();

	// максимальный уровень вложенности для дочерних
	public $maxLevel = 0;

	// реальный уровень вложенности
	private $realLevel = 0;

	// массив урлов. Временная
	private $urlsArray;

	// только опубликованные
	public $onlyPublish;

	// только разрешенный для индексации
	public $onlyFolow;

	// поля для выборки
	public $selectedFields;

	// поля сортировки
	public $orderField;

	// направление сортировки
	public $orderAsc;

	public $loadObjID;

	// активировать доступ к страницам
	public $accessTrue;

	public $offset;
	public $limit;

	public function __construct()
    {
        parent::__construct();
		$this->reset();
	}

	public function reset()
	{
		$this->urlsArray = $this->TreeChilds = $this->TreeParent = array();
		$this->maxLevel = 0;
		$this->realLevel = 0;
		$this->onlyPublish = TRUE;
		$this->onlyFolow = FALSE;
		$this->orderField = 'tree_order'; // поля сортировки
		$this->orderAsc = 'ASC'; // направление сортировки

		$this->selectedFields = array(
			'tree.*',
			'objects.*',
			'obj_types.obj_types_name',
			'obj_types.obj_types_icon',
			'obj_types.obj_types_id',
		);

		$this->loadObjID = array();

		$this->accessTrue = TRUE;
		$this->offset = 0;
		$this->limit = 1000;

	}
	/*
	*
	* возвращает массив родителей от дочернего
	*
	*/
	public function loadAllParentsTree($rowID = 0)
	{
		$this->db->join('objects', 'objects.obj_id = tree.tree_object');

		$this->db->where('tree_id', $rowID);
		$this->db->order_by('tree_order', 'asc');
		$query = $this->db->get('tree');

		if($query->num_rows())
		{
			foreach($query->result_array() as $row)
			{
				$this->TreeParent[$row['tree_parent_id']][$row['tree_id']] = $row;
				$this->TreeParent[$row['tree_parent_id']][$row['tree_id']]['path_url'] = $row['tree_url'];
			}
			return $this->loadAllParentsTree($row['tree_parent_id']);
		}
		else
		{
			$this->urlsArray = array();
			return $this->_loadAllParentsTree();
		}
	}

	# вспомогательная для loadAllParentsTree
	private function _loadAllParentsTree($parent = 0)
	{
		if(isset($this->TreeParent[$parent]))
		{
			foreach($this->TreeParent[$parent] as $key => &$value)
			{
				if(isset($this->urlsArray[$value['tree_parent_id']]))
				{
					$this->urlsArray[$value['tree_id']] = $this->urlsArray[$value['tree_parent_id']] . '/' . $value['tree_url'];
				}
				else
				{
					$this->urlsArray[$value['tree_id']] = APP_BASE_URL_NO_SLASH;
				}
				 $value['path_url'] = $this->urlsArray[$value['tree_id']];
			}
			unset($value);
			return $this->_loadAllParentsTree($key);
		}
		else
		{
			return $this->TreeParent;
		}
	}

	/*
	*
	* Возвращает массив дерева сайта от родителя до самого низа
	*  $startRowID - если по родителю ПРИ СТАРТЕ находится несколько объектов, то можно указать, по какому строить ветку
	*/
	public function loadAllChildsTree($parentID = 0, $types = array(), $startRowID = 0, $includeParent = FALSE, $loadDataFields = FALSE)
	{
		# если стартовый родитель больше 0, то надо построить его path_url
		if(++$this->realLevel == '1') {
			if(!is_array($parentID) and $parentID > 0)
				$this->loadAllParentsTree($parentID);
			else
				$this->urlsArray = array();
		}

		$this->db->select($this->selectedFields);

		$this->db->join('obj_types', 'tree.tree_type_object = obj_types.obj_types_id');
		$this->db->join('objects', 'objects.obj_id = tree.tree_object');

		# страница разрешена: ALL - всем, LOGIN - авторизованным, |1| только группе 1, |1|3| - только группам 1 и 3
		// для админа страница разрешена всегда,
		$userGroup = $this->session->userdata('group');
		!$userGroup ? $userGroup = 0 : 0;
		if($userGroup != 2 && $this->accessTrue)
		{
			// если есть группа, то запросим разрешенные страницы
			if($userGroup){
				$this->db->group_start('', 'AND ');
				$this->db->like('objects.obj_ugroups_access', 'ALL', 'none');
				$this->db->or_like('objects.obj_ugroups_access', 'LOGIN', 'none');
				$this->db->or_like('objects.obj_ugroups_access', '|'.$userGroup.'|', 'both');
				$this->db->group_end();
			}else{
				$this->db->like('objects.obj_ugroups_access', 'ALL', 'none');
			}

		}

		# если в массив надо включить текущего родителя
		if($includeParent)
		{
			$this->db->where('tree_id', $parentID);
			$query = $this->db->get('tree');
			foreach($query->result_array() as $row)
			{
				$this->loadObjID[$row['tree_id']] = $row['obj_id'];
				$this->TreeChilds[$row['tree_parent_id']][$row['tree_id']] = $row;
				$this->TreeChilds[$row['tree_parent_id']][$row['tree_id']]['level'] = 0;
				$this->TreeChilds[$row['tree_parent_id']][$row['tree_id']]['data_fields'] = array();
				if(isset($this->urlsArray[$row['tree_id']]))
					$this->TreeChilds[$row['tree_parent_id']][$row['tree_id']]['path_url'] = $this->urlsArray[$row['tree_id']];
				else
					$this->TreeChilds[$row['tree_parent_id']][$row['tree_id']]['path_url'] = $row['tree_url'];

			}
			$this->realLevel = 0;
			return $this->loadAllChildsTree($parentID, $types, $startRowID, FALSE, $loadDataFields);
		}


		if($startRowID)
		{
			if(is_array($startRowID))
				$this->db->where_in('tree_id', $startRowID);
			else
				$this->db->where('tree_id', $startRowID);
		}


		if($this->onlyPublish) {
			$this->db->where('obj_status', 'publish');
			$this->db->where('objects.obj_date_publish <=', date('Y-m-d H:i:s'));
		}

		if($this->onlyFolow){
			$this->db->where('tree_folow', 1);
		}

		if($types){
			$this->db->where_in('tree.tree_type_object', $types);
		}

		$this->db->where_in('tree_parent_id', $parentID);

		if($this->orderField) {
			$this->db->order_by($this->orderField, $this->orderAsc);
		}

		if($this->limit) {
			$this->db->limit($this->limit, $this->offset);
		}

		$query = $this->db->get('tree');

		if($query->num_rows())
		{
			$parents = array();
			foreach($query->result_array() as $row)
			{
				$this->loadObjID[$row['tree_id']] = $row['obj_id'];

				$parents[] = $row['tree_id'];

				$this->TreeChilds[$row['tree_parent_id']][$row['tree_id']] = $row;
				$this->TreeChilds[$row['tree_parent_id']][$row['tree_id']]['level'] = $this->realLevel;
				$this->TreeChilds[$row['tree_parent_id']][$row['tree_id']]['data_fields'] = array();

				# если строим url от главной, то index надо исключить из выдачи
				if($row['obj_canonical'] == 'index') {
					$row['obj_canonical'] = '';
				}

				# присвоим url-патч
				if(isset($this->urlsArray[$row['tree_parent_id']])) {
					$this->urlsArray[$row['tree_id']] = $this->urlsArray[$row['tree_parent_id']] . '/' . $row['tree_url'];
				} else {
					$this->urlsArray[$row['tree_id']] = APP_BASE_URL_NO_SLASH . $row['obj_canonical'];
				}

				$this->TreeChilds[$row['tree_parent_id']][$row['tree_id']]['path_url'] = $this->urlsArray[$row['tree_id']];
			}

			# лимит по уровню
			if($this->realLevel == $this->maxLevel)
				return $this->lchTreeData($loadDataFields);


			return $this->loadAllChildsTree($parents, $types, 0, FALSE, $loadDataFields);
		}
		else
		{
			return $this->lchTreeData($loadDataFields);
		}

	}

	# вспомогательная для loadAllChildsTree. Вытаскиваем DATA-поля
	private function lchTreeData($loadData = FALSE)
	{
		if(!$loadData || !$this->TreeChilds)
			return $this->TreeChilds;

		$dataFields = array();

		$this->db->where_in('objects_data_obj', $this->loadObjID);
		$this->db->join('data_types_fields', 'objects_data.objects_data_field = data_types_fields.types_fields_id');
		$query = $this->db->get('objects_data');
		if($query->num_rows())
		{
			foreach($query->result_array() as $row){
				$dataFields[$row['objects_data_obj']][$row['objects_data_field']] = $row;
			}


			foreach($this->TreeChilds as $key => &$value)
			{
				foreach($this->loadObjID as $nodeID => $objectID){
					if(isset($value[$nodeID]) && isset($dataFields[$objectID])){
						$value[$nodeID]['data_fields'] = $dataFields[$objectID];
					}
				}
			}
			$this->loadObjID = array();
			unset($value);
		}
		return $this->TreeChilds;
	}

	/**
	* getOrigRowIdFromRow
	*
	* возвращает row_id оригинального объекта для виртуальных копий по его row_id
	*
	* @param	id объекта
	* @return	int
	*/
	public function getOrigRowIdFromRow($row_id)
	{
		return $this->get_orig_row_id($row_id);
	}
	public function get_orig_row_id($row_id)
	{
		# найдем id объекта
		$this->db->select('tree_object, tree_type');
		$this->db->where('tree_id', $row_id);
		$query = $this->db->get('tree');

		if($query->row('tree_type') == 'orig') {
			return $row_id;
		}

		$object_id = $query->row('tree_object');
		unset($query);

		return $this->getOrigRowIdFromObj($object_id);
	}

	/*
	* getOrigRowIdFromObj
	*
	* Возвращает row_id оригинальной строки у объекта
	*
	* @param	ID объекта
	*
	* return int
	*/
	public function getOrigRowIdFromObj($objID)
	{

		$this->db->select('tree_id');

		if(is_array($objID))
			$this->db->where_in('tree_object', $objID);
		else
			$this->db->where('tree_object', $objID);

		$this->db->where('tree_type', 'orig');
		$query = $this->db->get('tree');

		if(!$query->num_rows())
			return 0;

		if(is_array($objID))
		{
			$out = array();
			foreach($query->result_array() as $row)
				$out[$row['tree_id']] = $row['tree_id'];

			return $out;
		}
		else
		{
			return $query->row('tree_id');
		}
	}

	/*
	* getOrigParentRowIdFromObj
	*
	* Возвращает row_id РОДИТЕЛЯ для оригинальной строки у объекта
	*
	* @param	ID объекта
	*
	* return int
	*/
	public function getOrigParentRowIdFromObj($objID = 0)
	{

		$this->db->select('tree_parent_id, tree_object');

		if(is_array($objID))
			$this->db->where_in('tree_object', $objID);
		else
			$this->db->where('tree_object', $objID);

		$this->db->where('tree_type', 'orig');
		$query = $this->db->get('tree');

		if(!$query->num_rows())
			return 0;

		if(is_array($objID))
		{
			$out = array();
			foreach($query->result_array() as $row)
				$out[$row['tree_object']] = $row['tree_parent_id'];

			return $out;
		}
		else
		{
			return $query->row('tree_parent_id');
		}
	}

	/**
	* add_object_to_tree
	*
	* создать запись в tree для объекта
	*
	* @param	id объекта
	* @param	id родителя (tree_parent_id)
	* @param	id url объекта
	* @param	id дата публикации
	* @param	id тип записи
	* @param	id тип объекта в дереве
	* @param	положение в дереве
	*
	* @return	int
	*/

	###############
	#
	# УСТАРЕЛА!!! Использовать createNodeInTree()
	#
	##############
	public function add_object_to_tree($object_id = 0, $parent_id = 0, $object_url = '', $date_publish = false, $tree_type = 'orig', $tree_type_object = '1', $axis = '')
	{
		return $this->addObjectToTree($object_id, $parent_id, $object_url, $date_publish, $tree_type, $tree_type_object, $axis);
	}


	public function addObjectToTree($object_id = 0, $parent_id = 0, $object_url = '', $date_publish = false, $tree_type = 'orig', $tree_type_object = '1', $axis = '')
	{
		if(!$object_id || !$object_url) return false;
		# сначала проверим урл, можно ли такой здесь ставить
		$this->db->where('tree_parent_id', $parent_id);
		$this->db->where('tree_url', $object_url);
		$query = $this->db->get('tree');
		if($query->num_rows())
		{
			return $this->addObjectToTree($object_id, $parent_id, $object_url . '-1', $date_publish, $tree_type, $tree_type_object, $axis);
		}

		$data = array(
				'tree_parent_id' => $parent_id,
				'tree_object' => $object_id,
				'tree_url' => $object_url,
				'tree_date_create' => date('Y-m-d H:i:s'),
				//'tree_date_publish' => '',
				'tree_order' => '0',
				'tree_type' => $tree_type,
				'tree_folow' => 1,
				'tree_short' => '',
				'tree_type_object' => $tree_type_object,
				'tree_axis' => $axis,
				);

		return $this->db->insert('tree', $data) ? $this->db->insert_id() : 0;
	}

	public function createNodeInTree($objID = 0, $ParentNodeID = 0, $params = array())
	{
		if(!isset($params['url']))
		   $params['url'] = 'default-url';

		if(!$objID) {
			return FALSE;
		}

		# сначала проверим урл, можно ли такой здесь ставить
		$this->db->where('tree_parent_id', $ParentNodeID);
		$this->db->where('tree_url', $params['url']);
		$query = $this->db->get('tree');
		if($query->num_rows()) {
			$params['url'] .= '-1';
			return $this->createNodeInTree($objID, $ParentNodeID, $params);
		}

		$data = array('tree_parent_id' => $ParentNodeID,'tree_object' => $objID);

		$data['tree_url'] = $params['url'];
		$data['tree_date_create'] = date('Y-m-d H:i:s');
		//$data['tree_date_publish'] = isset($params['date_publish']) ? $params['date_publish'] : $data['tree_date_create'];
		$data['tree_order'] = isset($params['order']) ? $params['order'] : 0;
		$data['tree_type'] = isset($params['type']) ? $params['type'] : 'orig';
		$data['tree_folow'] = isset($params['folow']) ? $params['folow'] : 1;
		$data['tree_short'] = isset($params['short']) ? $params['short'] : '';
		$data['tree_type_object'] = isset($params['type_object']) ? $params['type_object'] : 1;
		$data['tree_axis'] = isset($params['axis']) ? $params['axis'] : '';

		return $this->db->insert('tree', $data) ? $this->db->insert_id() : 0;
	}

	/**
	* update tree
	*
	* создать запись в tree
	*
	* @param	id строки
	* @param	значения
	* @param	id объекта
	* @param	id родителя
	*
	* @return	int
	*/
	// обновить запись в tree
	public function updateTree($row_id = 0, $values = array(), $object_id = 0, $parent_id = 0)
	{
		if(!$row_id)
		{
			$this->db->select('tree_id');
			$this->db->where('tree_object', $object_id);
			$this->db->where('tree_parent_id', $parent_id);
			$query = $this->db->get('tree');

			if(!$query->num_rows()) return false;
			$row_id = $query->row('tree_id');
			unset($query);
		}

		$data = array();

		isset($values['parent_id']) ? $data['tree_parent_id'] = $values['parent_id'] : 0;
		isset($values['object']) ? $data['tree_object'] = $values['object'] : 0;
		isset($values['date_create']) ? $data['tree_date_create'] = $values['date_create'] : 0;
		isset($values['order']) ? $data['tree_order'] = $values['order'] : 0;
		isset($values['type']) ? $data['tree_type'] = $values['type'] : 0;
		isset($values['type_object']) ? $data['tree_type_object'] = $values['type_object'] : 0;
		isset($values['folow']) ? $data['tree_folow'] = 1 : $data['tree_folow'] = 0;
		isset($values['short']) ? $data['tree_short'] = $values['short'] : 0;
		isset($values['axis']) ? $data['tree_axis'] = $values['axis'] : 0;

		# Для типа объекта ССЫЛКА переделывать url НЕ НАДО. Он может быть произвольный
		if(isset($data['tree_type_object']) and $data['tree_type_object'] == 4){
			isset($values['url']) ? $data['tree_url'] = $values['url'] : 0;
		}else{
			isset($values['url']) ? $data['tree_url'] = app_translate($values['url']) : 0;
		}

		# уникальный урл
		if(isset($data['tree_url']))
		{
			if(!$parent_id)
			{
				$this->db->select('tree_parent_id');
				$this->db->where('tree_id', $row_id);
				$query = $this->db->get('tree');
				$parent_id = $query->row('tree_parent_id');
				unset($query);
			}


			$this->db->where('tree_url', $data['tree_url']);
			$this->db->where('tree_parent_id', $parent_id);
			$this->db->where('tree_id !=', $row_id);
			$query = $this->db->get('tree');
			if($query->num_rows())
			{
				$values['url'] = $values['url'] . '-1';
				return $this->updateTree($row_id, $values);
			}
		}

		if($data)
		{
			$this->db->where('tree_id', $row_id);
			return $this->db->update('tree', $data);
		}
		else
		{
			return false;
		}
	}
	public function update_tree($row_id = 0, $values = array(), $object_id = 0, $parent_id = 0)
	{
		return $this->updateTree($row_id, $values, $object_id, $parent_id);
	}



	/*
	*
	* возвращает массив всех row_id для объекта
	*
	*
	*/
	public function getAllRowIDObject($objectID = 0)
	{
		$out = array();
		$this->db->select('tree_id, tree_type');
		$this->db->where('tree_object', $objectID);
		$query = $this->db->get('tree');
		foreach($query->result_array() as $row) {
			$out[$row['tree_id']] = $row['tree_type'];
		}
		return $out;
	}

	/*
	* проверка ROW_ID на оригнал
	*/
	public function isOrigRow($rowID)
	{
		$this->db->where('tree_id', $rowID);
		$this->db->where('tree_type', 'orig');
		$query = $this->db->get('tree');
		if($query->num_rows()) {
			return $query->row_array();
		}else{
			return array();
		}
	}


	/*
	* возвращает массив canonical для объекта
	*/
	public function getCanonicalObject($objectID = 0, $rowID = 0)
	{
		if(!$objectID)
		{
			$this->db->select('tree_object');
			$this->db->where('tree_id', $rowID);
			$query = $this->db->get('tree');
			$objectID = $query->row('tree_object');
			unset($query);
		}

		# найдем row_id оригинала
		$this->db->select('tree_id, tree_parent_id, tree_url, obj_name');
		$this->db->join('objects', 'tree.tree_object = objects.obj_id');
		$this->db->where('tree_object', $objectID);
		$this->db->where('tree_type', 'orig');
		$query = $this->db->get('tree');

		//pr($query->row('tree_id'));
		//return;
		$tree = array('id' => array(), 'url' => array());
		$tree['id'] = array('0' => $query->row('tree_id'));
		$tree['url'] = array('0' => $query->row('tree_url'));
		$tree['name'] = array('0' => $query->row('obj_name'));


		$tree = $this->_uco_rec($query->row('tree_id'), $tree);
		array_pop($tree['id']);
		unset($tree['url']['0'], $tree['name']['0']);

		$tree['id'] = array_reverse($tree['id']);
		$tree['url'] = array_reverse($tree['url']);
		$tree['name'] = array_reverse($tree['name']);

		return $tree;
		/*
		Array
			(
				[id] => Array
					(
						[0] => 1
						[1] => 23
						[2] => 5
					)

				[url] => Array
					(
						[0] => index
						[1] => link1
						[2] => link2
					)

				[name] => Array
					(
						[0] => Главная
						[1] => Ссылка 1
						[2] => Ссылка 2
					)

			)
		*/
	}

	/*
	* впомогательная для getCanonicalObject
	*/
	private function _uco_rec($rowID = 0, $tree = array('id' => array(), 'url' => array()))
	{
		$this->db->select('tree_parent_id, tree_url, obj_name');
		$this->db->join('objects', 'tree.tree_object = objects.obj_id');
		$this->db->where('tree_id', $rowID);
		$query = $this->db->get('tree');
		if($query->result_array()) {
			$tree['id'][] = $query->row('tree_parent_id');
			$tree['url'][] = $query->row('tree_url');
			$tree['name'][] = $query->row('obj_name');
			return $this->_uco_rec($query->row('tree_parent_id'), $tree);
		}
		return $tree;
	}

	/*
	* обновить ссылку canonical для объекта
	*/
	public function updateCanonicalOblect($objectID = 0, $nodeID = 0)
	{
		if(!$objectID) {
			$this->db->select('tree_object');
			$this->db->where('tree_id', $nodeID);
			$query = $this->db->get('tree');
			$objectID = $query->row('tree_object');
			unset($query);
		}

		$canonicalInfo = $this->getCanonicalObject($objectID);
		$link = app_create_canonical_links($canonicalInfo);

		if(isset($canonicalInfo['id'])) {
			$axis = '|' . implode('|', $canonicalInfo['id']) . '|';
			if($nodeID) {
				$this->db->where('tree_id', $nodeID);
				$this->db->update('tree', array('tree_axis' => $axis));
			}
		}
		$par['canonical'] = implode('/', $link['single']);
		return $this->ObjectAdmModel->updateObject($par, $objectID);
	}

	/*
	* назначить новый NODE ID оригиналом для объекта
	*/
	public function changeCanonicalObject($objectID = 0, $rowID = 0, $newRowID)
	{
		if(!$objectID) {
			$this->db->select('tree_object');
			$this->db->where('tree_id', $rowID);
			$query = $this->db->get('tree');
			$objectID = $query->row('tree_object');
			unset($query);
		}

		# проверим, существует ли $newRowID для этого объекта, если есть, назначим его
		$this->db->where('tree_id', $newRowID);
		$this->db->where('tree_object', $objectID);
		$query = $this->db->get('tree');
		if($query->num_rows())
		{
			$this->db->where('tree_object', $objectID);
			$updCopy = $this->db->update('tree', array('tree_type' => 'copy'));
			if($updCopy) {
				$this->db->where('tree_id', $newRowID);
				$this->db->update('tree', array('tree_type' => 'orig'));
				# обновим ссылу canonical
				$this->updateCanonicalOblect($objectID);
				return TRUE;
			}

		}
		return FALSE;
	}

}
