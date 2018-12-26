<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* Класс для работы с деревом
	*
	* UPD 2018-10-26
	* Version 1.0
	*
	* UPD 2018-12-26
	* Version 1.1
	* Переделан алгоритм формирования короткой ссылки. Добавлен метод $this->shortLinkGenerate()
	*
*/

class AppTreeModel extends CI_Model {

	// буфер
	public $BUFFER;

	// Временный массив
	private $TMP_BUFFER;

	// смещение по axis
	public $startAxis;

	// только опубликованные
	public $publish;

	// загрузить Data-поля
	public $loadData;

	// Только определенный тип объектов
	public $typeObjects;

	// тип ноды
	public $typeNode;

	// включить доступы
	public $accessTrue;

	// лимит выборки
	public $limit;

	// смещение выборки
	public $offset;

	// направление сортировки выборки
	public $orderAsc;

	// направление сортировки выборки
	public $orderField;

	// массив пагинации
	public $pagArray;

	// ключ пагинации
	public $pagKey;

	// количество строк в выборке
	public $numRows;

	public function __construct()
    {
        parent::__construct();
		$this->reset();
    }

	public function reset()
	{
		$this->BUFFER = array();
		$this->TMP_BUFFER = array();
		$this->startAxis = '|1|';
		$this->publish = true;
		$this->loadData = false;
		$this->typeObjects = array(6);
		$this->typeNode = '';
		$this->accessTrue = true;

		$this->limit = 10;
		$this->offset = 0;
		$this->orderAsc = 'ASC';
		$this->orderField = 'tree_order';
		$this->pagArray = array();
		$this->pagKey = 'page';
		$this->numRows = 0;

	}

	# возвращает всех потомков со всех уровней вложенности
	public function childrenAll($parentID = 1)
	{
		$this->db->join('tree', 'objects.obj_id = tree.tree_object');

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

		if(is_array($parentID)) {
			$this->db->where_in('tree_parent_id', $parentID);
		} else {
			$this->db->where('tree_parent_id', $parentID);
		}

		if($this->typeObjects) {
			if(is_array($this->typeObjects)) {
				$this->db->where_in('tree_type_object', $this->typeObjects);
			} else {
				$this->db->where('tree_type_object', $this->typeObjects);
			}
		}

		if($this->typeNode) {
			$this->db->where('tree_type', $this->typeNode);
		}

		if($this->publish) {
			$this->db->where('objects.obj_status', 'publish');
			$this->db->where('objects.obj_date_publish <=', date('Y-m-d H:i:s'));
		}

		$query = $this->db->get('objects');

		if($query->num_rows())
		{
			$parents = array();
			foreach($query->result_array() as $row) {
				$this->TMP_BUFFER[$row['obj_id']][] = $row['tree_id'];
				$this->BUFFER[$row['tree_id']] = $row;
				if(isset($this->BUFFER[$row['tree_parent_id']])) {
					$this->BUFFER[$row['tree_id']]['path_url'] = $this->BUFFER[$row['tree_parent_id']]['path_url'] . '/' . $row['tree_url'];
					$this->BUFFER[$row['tree_id']]['data_fields'] = array();
				} else {
					# Если родители не загружены, надо сделать URL
					$axis = explode('|', trim($row['tree_axis'], '|'));
					$this->db->select('tree_id, tree_url');
					$this->db->where_in('tree_id', $axis);
					$qax = $this->db->get('tree');
					$tmp = array();
					foreach($qax->result_array() as $var) {
						$tmp[$var['tree_id']] = $var['tree_url'];
					}
					$tmpAxis = array();
					foreach($axis as $key => $val) {
						if($tmp[$val] == 'index') {
							$tmpAxis[$key] = APP_BASE_URL_NO_SLASH;
						} else {
							$tmpAxis[$key] = $tmp[$val];
						}
					}
					$this->BUFFER[$row['tree_id']]['path_url'] = implode('/', $tmpAxis);
					$this->BUFFER[$row['tree_id']]['data_fields'] = array();
				}
				$parents[] = $row['tree_id'];
			}
			return $this->childrenAll($parents);
		}

		if($this->loadData && $this->TMP_BUFFER) {
			$sql = "SELECT * FROM `{$this->db->dbprefix}objects_data` ";
			$sql .= "JOIN `{$this->db->dbprefix}data_types_fields` ON `objects_data_field` = `types_fields_id` ";
			$sql .= 'WHERE `objects_data_obj` IN(' . implode(',', array_keys($this->TMP_BUFFER)) . ')';
			$query = $this->db->query($sql);
			if($query->num_rows()) {
				$tmp = array();
				foreach($query->result_array() as $row) {
					if(isset($this->TMP_BUFFER[$row['objects_data_obj']])) {
						foreach($this->TMP_BUFFER[$row['objects_data_obj']] as $treeID) {
							$this->BUFFER[$treeID]['data_fields'][$row['objects_data_field']] = $row;
						}
					}
				}
			}
		}

		$this->TMP_BUFFER = array();
		return $this->BUFFER;
	}

	# возвращает прямых потомков
	public function children($parent = NULL)
	{
		$this->TMP_BUFFER = array();
		$this->BUFFER = array();

		$this->db->join('tree', 'objects.obj_id = tree.tree_object');

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

		if($parent)
		{
			# массив родителей можно либо просто ID либо полноценный массив
			if(is_array($parent)) {
				reset($parent);
				$tmp = current($parent);
				if(isset($tmp['tree_id'])) {
					unset($tmp);
					$this->db->where_in('tree_parent_id', array_keys($parent));
				} else {
					$this->db->where_in('tree_parent_id', $parent);
				}
			} else {
				$this->db->where('tree_parent_id', $parent);
			}
		}

		if($this->typeObjects) {
			if(is_array($this->typeObjects)) {
				$this->db->where_in('tree_type_object', $this->typeObjects);
			} else {
				$this->db->where('tree_type_object', $this->typeObjects);
			}
		}

		if($this->typeNode) {
			$this->db->where('tree_type', $this->typeNode);
		}

		if($this->publish) {
			$this->db->where('objects.obj_status', 'publish');
			$this->db->where('objects.obj_date_publish <=', date('Y-m-d H:i:s'));
		}

		if($this->orderField) {
			$this->db->order_by($this->orderField, $this->orderAsc);
		}

		# зафиксируем количество строк всего и создадим массив пагинации
		$this->numRows = $this->db->count_all_results('objects', false);

		// результатов нет, отдаем пустой массив
		if(!$this->numRows) {
			return array();
		}

		# если есть ключ пагинации
		if($page = $this->input->get($this->pagKey)) {
			$page = $page * 1;
			$countPages = ceil($this->numRows / $this->limit);
			if($page > $countPages) {
				$page = $countPages;
			} else if($page < 1) {
				$page = 1;
			}

			$this->offset = $this->limit * $page - $this->limit;
		}

		if($this->limit) {
			$this->pagArray = app_pagination_array($this->limit, $this->numRows, $this->pagKey);
			$this->db->limit($this->limit, $this->offset);
		}

		$query = $this->db->get();

		if($query->num_rows())
		{
			$parents = array();
			foreach($query->result_array() as $row) {
				$this->TMP_BUFFER[$row['obj_id']][] = $row['tree_id'];
				$this->BUFFER[$row['tree_id']] = $row;
				$this->BUFFER[$row['tree_id']]['path_url'] = $row['obj_canonical'];
				if(isset($parent[$row['tree_parent_id']]['path_url'])) {
					$this->BUFFER[$row['tree_id']]['path_url'] = $parent[$row['tree_parent_id']]['path_url'] . '/' . $row['tree_url'];
				}
				$this->BUFFER[$row['tree_id']]['data_fields'] = array();
			}
		}
		else
		{
			return array();
		}

		# если надо загрузить DATA-поля
		if($this->loadData && $this->TMP_BUFFER) {
			$sql = "SELECT * FROM `{$this->db->dbprefix}objects_data` ";
			$sql .= "JOIN `{$this->db->dbprefix}data_types_fields` ON `objects_data_field` = `types_fields_id` ";
			$sql .= 'WHERE `objects_data_obj` IN(' . implode(',', array_keys($this->TMP_BUFFER)) . ')';
			$query = $this->db->query($sql);
			if($query->num_rows()) {
				$tmp = array();
				foreach($query->result_array() as $row) {
					if(isset($this->TMP_BUFFER[$row['objects_data_obj']])) {
						foreach($this->TMP_BUFFER[$row['objects_data_obj']] as $treeID) {
							$this->BUFFER[$treeID]['data_fields'][$row['objects_data_field']] = $row;
						}
					}
				}
			}
		}

		$this->TMP_BUFFER = array();
		return $this->BUFFER;
	}

	# возвращает ноду по NodeID
	public function node($nodeID = 1)
	{
		if(!$nodeID) {
			return array();
		}

		$this->db->join('tree', 'objects.obj_id = tree.tree_object');

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

		$this->db->where('tree_id', $nodeID);

		if($this->publish) {
			$this->db->where('objects.obj_status', 'publish');
			$this->db->where('objects.obj_date_publish <=', date('Y-m-d H:i:s'));
		}

		$query = $this->db->get('objects');

		if($query->num_rows())
		{
			$parents = array();
			foreach($query->result_array() as $row) {
				$this->TMP_BUFFER[$row['obj_id']][] = $row['tree_id'];
				$this->BUFFER[$row['tree_id']] = $row;
				# сделаем URL
				$axis = explode('|', trim($row['tree_axis'], '|'));
				$this->db->select('tree_id, tree_url');
				$this->db->where_in('tree_id', $axis);
				$qax = $this->db->get('tree');
				$tmp = array();
				foreach($qax->result_array() as $var) {
					$tmp[$var['tree_id']] = $var['tree_url'];
				}
				$tmpAxis = array();
				foreach($axis as $key => $val) {
					if($tmp[$val] == 'index') {
						$tmpAxis[$key] = APP_BASE_URL_NO_SLASH;
					} else {
						$tmpAxis[$key] = $tmp[$val];
					}
				}
				$this->BUFFER[$row['tree_id']]['path_url'] = implode('/', $tmpAxis);
				$this->BUFFER[$row['tree_id']]['data_fields'] = array();
			}
		}
		else
		{
			return array();
		}

		if($this->loadData && $this->TMP_BUFFER) {
			$sql = "SELECT * FROM `{$this->db->dbprefix}objects_data` ";
			$sql .= "JOIN `{$this->db->dbprefix}data_types_fields` ON `objects_data_field` = `types_fields_id` ";
			$sql .= 'WHERE `objects_data_obj` IN(' . implode(',', array_keys($this->TMP_BUFFER)) . ')';
			$query = $this->db->query($sql);
			if($query->num_rows()) {
				$tmp = array();
				foreach($query->result_array() as $row) {
					if(isset($this->TMP_BUFFER[$row['objects_data_obj']])) {
						foreach($this->TMP_BUFFER[$row['objects_data_obj']] as $treeID) {
							$this->BUFFER[$treeID]['data_fields'][$row['objects_data_field']] = $row;
						}
					}
				}
			}
		}

		$this->TMP_BUFFER = array();
		return $this->BUFFER;
	}

	# создать новую позицию в дереве
	public function insert($parentID = 0, $objID = 0, $args = array())
	{
		if(!$parentID && !$objID) {
			throw new Exception('Error: empty params');
		}

		$data = array(
			'tree_parent_id' => $parentID,
			'tree_object' => $objID,
			'tree_url' => 'tree',
			'tree_date_create' => date('Y-m-d H:i:s'),
			'tree_order' => 0,
			'tree_type' => 'orig',
			'tree_folow' => 1,
			'tree_short' => 'treeshort',
			'tree_type_object' => 1,
			'tree_axis' => ''
		);

		isset($args['url']) ? $data['tree_url'] = $args['url'] : 0;
		isset($args['order']) ? $data['tree_order'] = $args['order'] : 0;
		isset($args['type']) ? $data['tree_type'] = $args['type'] : 0;
		isset($args['folow']) ? $data['tree_folow'] = $args['folow'] : 0;
		isset($args['short']) ? $data['tree_short'] = $args['short'] : 0;
		isset($args['type_object']) ? $data['tree_type_object'] = $args['type_object'] : 0;
		isset($args['axis']) ? $data['tree_axis'] = $args['axis'] : 0;

		# проверим URL, возможно такой уже есть
		$this->db->where('tree_parent_id', $parentID);
		$this->db->where('tree_url', $data['tree_url']);
		$query = $this->db->get('tree');
		if($query->num_rows()) {
			$args['url'] = $data['tree_url'] . '-1';
			return $this->insert($parentID, $objID, $args);
		}

		# проверим SHORT LINK, возможно такой уже есть
		$this->db->where('tree_parent_id', $parentID);
		$this->db->where('tree_short', $data['tree_short']);
		$query = $this->db->get('tree');
		if($query->num_rows()) {
			$args['short'] = $data['tree_short'] . '-1';
			return $this->insert($parentID, $objID, $args);
		}

		return $this->db->insert('tree', $data) ? $this->db->insert_id() : 0;
	}

	# обновить ноду в дереве
	public function update($nodeID = 0, $args = array())
	{
		$data = array();

		if(!$nodeID) {
			throw new Exception('Error: empty node');
		}

		isset($args['parent_id']) ? $data['tree_parent_id'] = $args['parent_id'] : 0;
		isset($args['url']) ? $data['tree_url'] = $args['url'] : 0;
		isset($args['order']) ? $data['tree_order'] = $args['order'] : 0;
		isset($args['type']) ? $data['tree_type'] = $args['type'] : 0;
		isset($args['folow']) ? $data['tree_folow'] = $args['folow'] : 0;
		isset($args['short']) ? $data['tree_short'] = $args['short'] : 0;
		isset($args['type_object']) ? $data['tree_type_object'] = $args['type_object'] : 0;
		isset($args['axis']) ? $data['tree_axis'] = $args['axis'] : 0;

		$parentID = 0;
		$this->db->select('tree_parent_id');
		$this->db->where('tree_id', $nodeID);
		$query = $this->db->get('tree');
		$parentID = $query->row('tree_parent_id');

		# проверим URL, возможно такой уже есть
		if(isset($data['tree_url'])) {
			$this->db->select('tree_url');
			$this->db->where('tree_parent_id', $parentID);
			$this->db->where('tree_url', $data['tree_url']);
			$this->db->where('tree_id !=', $nodeID);
			$query = $this->db->get('tree');
			if($query->num_rows()) {
				$args['url'] = $query->row('tree_url') . '-1';
				return $this->update($nodeID, $args);
			}
		}

		# проверим SHORT LINK, возможно такой уже есть
		if(isset($data['tree_short'])) {
			if(!$data['tree_short']) {
				$args['short'] = $this->shortLinkGenerate();
				return $this->update($nodeID, $args);
			}

			$this->db->select('tree_short');
			$this->db->where('tree_parent_id', $parentID);
			$this->db->where('tree_short', $data['tree_short']);
			$this->db->where('tree_id !=', $nodeID);
			$query = $this->db->get('tree');
			if($query->num_rows()) {
				$args['short'] = $this->shortLinkGenerate();
				return $this->update($nodeID, $args);
			}
		}

		if($data) {
			$this->db->where('tree_id', $nodeID);
			return $this->db->update('tree', $data);
		} else {
			return false;
		}
	}

	/*
	* генерирует короткую ссылку
	*/
	public function shortLinkGenerate(){
		$length = 8;
		$chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ';
		$numChars = strlen($chars);
		$string = '';
		for ($i = 0; $i < $length; $i++) {
			$string .= substr($chars, rand(1, $numChars) - 1, 1);
		}
		return $string;
	 }

}
