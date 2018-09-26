<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*
	* admin-page model
	*
	*
	* version 0.21
	* UPD 2017-08-17
	*
	* version 0.22
	* UPD 2017-12-21
	* изменения под версию системы 3.41
	*
	* version 0.23
	* UPD 2018-03-30
	* Мелкие правки
	*
	* version 0.24
	* UPD 2018-09-2018
	* Мелкие правки в коде
	*
*/
class AdminPageModel extends CI_Model {

	public $shortLinkNodeID = 0;

	function __construct()
    {
        parent::__construct();
    }

	public function loadObjectFromRow($row_id = 0)
	{
		$this->ObjectAdmModel->reset();
		$this->ObjectAdmModel->includeDataFields = TRUE;
		return $this->ObjectAdmModel->getObject(0, $row_id);
	}

	# обновление объекта
	public function updateObject($par = array(), $obj_id = 0)
	{
		if(!$obj_id) {
			return false;
		}

		return $this->ObjectAdmModel->updateObject($par, $obj_id);
	}

	# добавление объекта
	public function addObject($par = array())
	{
		if(!$par) {
			return false;
		}

		return $this->ObjectAdmModel->addObject($par);
	}

	# создать запись в tree
	public function addObjectToTree($object_id = 0, $parent_id = 0, $object_url = '', $date_publish = false, $obj2obj_type = 'orig', $obj2obj_type_object = '1')
	{
		return $this->TreelibAdmModel->addObjectToTree($object_id, $parent_id, $object_url, $date_publish, $obj2obj_type, $obj2obj_type_object);
	}

	# обновить запись в obj2obj
	public function update_obj2obj($row_id = 0, $values = array(), $object_id = 0, $parent_id = 0)
	{
		return $this->TreelibAdmModel->updateTree($row_id, $values, $object_id, $parent_id);
	}

	# проверка на повторяющиеся короткие ссылки. return TRUE - ссылка есть, FALSE - ссылки нет
	public function checkShortLink($link = '', $nodeID = 0)
	{
		if(!$link) {
			return false;
		}

		$this->db->where('tree_short', $link);

		# если пришла нода, то проверяем у всех, кроме нее
		if($nodeID) {
			$this->db->where('tree_id !=', $nodeID);
		}

		$query = $this->db->get('tree');

		if($query->num_rows()) {
			$this->shortLinkNodeID = $query->row('tree_id');
			return TRUE;
		} else {
			$this->shortLinkNodeID = 0;
			return FALSE;
		}
	}

	# создать короткую ссылку.
	public function createShortLink($text = '')
	{
		$link = admin_page_generate_string($text);

		// если ссылка такая есть, надо подобрать другую комбинацию
		if($this->checkShortLink($link)) {
			return $this->createShortLink($text);
		}

		// ссылка не обнаружена, можно такую сохранять
		return $link;
	}

	# загрузка URL-ов от текущего до самого верха по короткой ссылке
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
			return $urls;
		}
	}

	# получить все ветки в дереве для объекта
	public function getAllTreeObject($objID = 0, $nodeID = 0)
	{
		if(!$objID) {
			$this->db->select('tree_object');
			$this->db->where('tree_id', $nodeID);
			$query = $this->db->get('tree');
			$objID = $query->row('tree_object');
		}

		$nodesArray = array();

		$this->db->select('tree_id');
		$this->db->where('tree_object', $objID);
		$query = $this->db->get('tree');
		foreach($query->result_array() as $row) {
			$nodesArray[] = $row['tree_id'];
		}

		$treeArray = $tree = $deletedNodes = array();
		$this->TreelibAdmModel->reset();

		$i = 0;
		foreach($nodesArray as $row) {
			$tree = $this->TreelibAdmModel->loadAllParentsTree($row);
			// все ветки ведут к родителю с parentID == 0
			// если в ветке родителей отсутствует $tree[0], то все элементы следует удалить из системы
			if(!isset($tree[0])) {
				$deletedNodes[$i] = array_keys($tree);
				$deletedNodes[$i] = $row;
				continue;
			}

			$treeArray[$row]['node_tree_type'] = 'orig';
			$treeArray[$row]['nodes'] = $this->_helpGetAllTreeObject(0, $tree);

			if($treeArray[$row]['nodes'][$row]['tree_type'] != 'orig') {
				$treeArray[$row]['node_tree_type'] = 'copy';
			}

			$this->TreelibAdmModel->reset();
			$tree = array();
			++$i;
		}

		// если есть ноды на удаление
		if($deletedNodes) {
			$CI = &get_instance();
			$CI->pageErrorMessage = app_lang('ADMP_INFO_DN_DEL_FOUND');
			$CI->pageErrorMessage .= implode(', ', $deletedNodes);

			$this->db->where_in('tree_id', $deletedNodes);
			$this->db->delete('tree');
		}

		return $treeArray;
	}

	# вспомогательная рекурсивная для getAllTreeObject()
	private function _helpGetAllTreeObject($parentID, $array = array(), $res = array())
	{
		if(isset($array[$parentID])) {
			foreach($array[$parentID] as $key => $value) {
				$res[$value['tree_id']]['name'] = $value['obj_name'];
				$res[$value['tree_id']]['url'] = $value['tree_url'];
				$res[$value['tree_id']]['tree_parent_id'] = $value['tree_parent_id'];
				$res[$value['tree_id']]['tree_type'] = $value['tree_type'];

				return $this->_helpGetAllTreeObject($value['tree_id'], $array, $res);
			}
		} else {
			return $res;
		}
	}
}
