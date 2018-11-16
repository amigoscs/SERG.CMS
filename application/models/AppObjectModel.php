<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* Класс для работы с объектами (страницами)
	*
	* UPD 2018-11-02
	* Version 1.0
	*
*/

class AppObjectModel extends CI_Model {

	// буфер
	public $BUFFER;

	// Временный массив
	private $TMP_BUFFER;

	// включить доступы
	public $accessTrue;

	// только опубликованные
	public $publish;

	// загрузить Data-поля
	public $loadData;


	public function __construct()
    {
        parent::__construct();
		$this->reset();
    }

	public function reset()
	{
		$this->BUFFER = array();
		$this->TMP_BUFFER = array();
		$this->accessTrue = true;
		$this->publish = true;
		$this->loadData = true;
	}

	# возвращает объект
	public function object($objectID = NULL)
	{
		if(!$objectID) {
			return array();
		}

		$this->TMP_BUFFER = array();

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

		if(is_array($objectID)) {
			$this->db->where_in('objects.obj_id', $objectID);
		} else {
			$this->db->where('objects.obj_id', $objectID);
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
				$this->BUFFER[$row['obj_id']] = $row;
				$this->BUFFER[$row['obj_id']]['data_fields'] = array();
				if($row['obj_canonical'] == 'index') {
					$this->BUFFER[$row['obj_id']]['path_url'] = APP_BASE_URL_NO_SLASH;
				} else {
					$this->BUFFER[$row['obj_id']]['path_url'] = APP_BASE_URL . $row['obj_canonical'];
				}

			}
			$this->TMP_BUFFER = array_keys($this->BUFFER);
		}

		if($this->loadData && $this->TMP_BUFFER) {
			$sql = "SELECT * FROM `{$this->db->dbprefix}objects_data` ";
			$sql .= "JOIN `{$this->db->dbprefix}data_types_fields` ON `objects_data_field` = `types_fields_id` ";
			$sql .= 'WHERE `objects_data_obj` IN(' . implode(',', $this->TMP_BUFFER) . ')';
			$query = $this->db->query($sql);
			if($query->num_rows()) {
				foreach($query->result_array() as $row) {
					if(isset($this->BUFFER[$row['objects_data_obj']])) {
						$this->BUFFER[$row['objects_data_obj']]['data_fields'][$row['objects_data_field']] = $row;
					}
				}
			}
		}

		$this->TMP_BUFFER = array();
		return $this->BUFFER;
	}

	# обновить объект
	public function update($objID = 0, $args = array())
	{
		if(!$objID) {
			return false;
		}
		$data = array();
		isset($args['data_type']) ? 					$data['obj_data_type'] = $args['data_type'] : 0;
		isset($args['canonical']) ? 					$data['obj_canonical'] = $args['canonical'] : 0;
		(isset($args['name']) and $args['name']) ? 	$data['obj_name'] = $args['name'] : 0;
		(isset($args['h1']) and $args['h1']) ? 		$data['obj_h1'] = $args['h1'] : 0;
		isset($args['title']) ? 						$data['obj_title'] = $args['title'] : 0;
		isset($args['description']) ? 				$data['obj_description'] = $args['description'] : 0;
		isset($args['keywords']) ? 					$data['obj_keywords'] = $args['keywords'] : 0;
		isset($args['anons']) ? 						$data['obj_anons'] = $args['anons'] : 0;
		isset($args['content']) ? 					$data['obj_content'] = $args['content'] : 0;
		isset($args['date_publish']) ? 				$data['obj_date_publish'] = $args['date_publish'] : 0;
		isset($args['cnt_views']) ? 					$data['obj_cnt_views'] = $args['cnt_views'] : 0;
		isset($args['rating_up']) ? 					$data['obj_rating_up'] = $args['rating_up'] : 0;
		isset($args['rating_down']) ? 				$data['obj_rating_down'] = $args['rating_down'] : 0;
		isset($args['rating_count']) ? 				$data['obj_rating_count'] = $args['rating_count'] : 0;
		isset($args['user_author']) ? 				$data['obj_user_author'] = $args['user_author'] : 0;
		isset($args['tpl_content']) ? 				$data['obj_tpl_content'] = $args['tpl_content'] : 0;
		isset($args['tpl_page']) ? 					$data['obj_tpl_page'] = $args['tpl_page'] : 0;
		isset($args['status']) ? 					$data['obj_status'] = $args['status'] : 0;
		isset($args['link']) ? 					$data['obj_link'] = $args['link'] : 0;

		$data['obj_lastmod'] = time();

		# доступы
		if(isset($args['ugroups_access']))
		{
			# если доступы в виде строки, то пишем как есть, иначе обработка
			if(is_array($args['ugroups_access'])) {
				// если в массиве есть ALL, то ставим только его
				if(in_array('ALL', $args['ugroups_access'])){
					$data['obj_ugroups_access'] = 'ALL';
				}else if(in_array('LOGIN', $args['ugroups_access'])){
					// если в массиве зарегистрированные, ставим LOGIN
					$data['obj_ugroups_access'] = 'LOGIN';
				}else{
					$data['obj_ugroups_access'] = '|' . implode('|', $args['ugroups_access']) . '|';
				}
			} else {
				$data['obj_ugroups_access'] = $args['ugroups_access'];
			}
		}

		if(!$data) {
			return false;
		}

		$this->db->where('obj_id', $objID);
		return $this->db->update('objects', $data);
	}

	# добавить новые объект
	public function insert($args = array())
	{
		if(!$args) {
			return false;
		}
		$data = array();

		$data['obj_data_type'] = 		isset($args['data_type']) ? $args['data_type'] : 1;
		$data['obj_canonical'] = 		isset($args['canonical']) ? $args['canonical'] : '';
		$data['obj_name'] = 			(isset($args['name']) and $args['name']) ? $args['name'] : 'NO NAME';
		$data['obj_h1'] = 			(isset($args['h1']) and $args['h1']) ? $args['h1'] : 'NO TITLE';
		$data['obj_title'] = 			isset($args['title']) ? $args['title'] : 'NO TITLE';
		$data['obj_description'] = 	isset($args['description']) ? $args['description'] : '';
		$data['obj_keywords'] = 		isset($args['keywords']) ? $args['keywords'] : '';
		$data['obj_anons'] = 			isset($args['anons']) ? $args['anons'] : '';
		$data['obj_content'] = 		isset($args['content']) ? $args['content'] : '';
		$data['obj_cnt_views'] = 		isset($args['cnt_views']) ? $args['cnt_views'] : 0;
		$data['obj_rating_up'] = 		isset($args['rating_up']) ? $args['rating_up'] : 0;
		$data['obj_rating_down'] = 		isset($args['rating_down']) ? $args['rating_down'] : 0;
		$data['obj_rating_count'] = 	isset($args['rating_count']) ? $args['rating_count'] : 0;
		$data['obj_user_author'] = 		isset($args['user_author']) ? $args['user_author'] : 1;
		$data['obj_tpl_content'] = 		isset($args['tpl_content']) ? $args['tpl_content'] : '';
		$data['obj_tpl_page'] = 		isset($args['tpl_page']) ? $args['tpl_page'] : '';
		$data['obj_date_publish'] = 	isset($args['date_publish']) ? $args['date_publish'] : FALSE;
		$data['obj_status'] = 			isset($args['status']) ? $args['status'] : 'publish';
		$data['obj_link'] = 			isset($args['link']) ? $args['link'] : '';

		$data['obj_date_create'] = date('Y-m-d H:i:s');
		$data['obj_lastmod'] = time();

		if(!$data['obj_date_publish']) {
			$data['obj_date_publish'] = $data['obj_date_create'];
		}

		# доступы
		if(isset($args['ugroups_access']))
		{
			// если в массиве есть ALL, то ставим только его
			if(in_array('ALL', $args['ugroups_access'])){
				$data['obj_ugroups_access'] = 'ALL';
			}else if(in_array('LOGIN', $args['ugroups_access'])){
				// если в массиве зарегистрированные, ставим LOGIN
				$data['obj_ugroups_access'] = 'LOGIN';
			}else{
				$data['obj_ugroups_access'] = '|' . implode('|', $args['ugroups_access']) . '|';
			}
		}else{
			$data['obj_ugroups_access'] = 'ALL';
		}

		return $this->db->insert('objects', $data) ? $this->db->insert_id() : 0;
	}

	# обновление DATA-полей
	public function updateData($objID = 0, $args = array(), $deleteEmpty = false)
	{
		if(!$objID) {
			return false;
		}

		$savedData = array();

		// сначала получим все сохраненные DATA для объекта
		$this->db->where('objects_data_obj', $objID);
		$query = $this->db->get('objects_data');
		if($query->num_rows()) {
			foreach($query->result_array() as $row) {
				$savedData[$row['objects_data_field']] = $row['objects_data_value'];
			}
		}
		// теперь сохранение
		if($args) {
			foreach($args as $key => $value) {
				$data = array();
				if(is_array($value)) {
					$value = implode(',', $value);
				}
				// если такое поле есть в сохраненных, то обновляем. Иначе добавляем
				if(isset($savedData[$key])) {
					$data['objects_data_value'] = $value;
					$this->db->where('objects_data_obj', $objID);
					$this->db->where('objects_data_field', $key);
					$this->db->update('objects_data', $data);
					unset($savedData[$key]);
				} else {
					$data['objects_data_obj'] = $objID;
					$data['objects_data_field'] = $key;
					$data['objects_data_value'] = $value;
					$this->db->insert('objects_data', $data);
				}
			}
		}

		// если надо удалить лишние DATA
		if($deleteEmpty && $savedData) {
			$this->db->where('objects_data_obj', $objID);
			$this->db->where_in('objects_data_field', array_keys($savedData));
			$this->db->delete('objects_data');
		}
		return true;
	}
}
