<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Urlobjects {

	/**
	 * Класс объекта URL
	 *
	 * UPD 2017-12-06
	 * version 2.74

	 * UPD 2017-12-15
	 * version 2.75
	 * измненено private $currentIndex на public $currentIndex и метод getParentObject($decrIndex = 0)

	 * Version 3.0
	 * UPD 2018-01-15
	 * изменена таблица tree

	 * Version 3.1
	 * UPD 2018-01-29
	 * исправлена ошибка обработки массива objects

	 * Version 3.2
	 * UPD 2018-04-02
	 * добавлена countChilds - количество объектов в выборке
	 */


	//public $current_tpl_page, $current_tpl_content;
	public $curTplPage, $curTplContent;

	// текущий объект
	public $objects;

	// текущий индекс
	public $currentIndex = 0;

	// хранилище
	protected $customData = array();

	// количество дочерних
	public $countChilds;

	// массив пагинации
	public $paginationArray;

	// если нужна пагинация
	public $pagination;

	// ключ пагинации
	public $paginationKey;

	// текущая страница пагинации
	public $paginationCurrent;

	// поле для сортировки
	public $orderField;

	// порядок сортировки
	public $orderAsc;

	// Количесво объектов на выборку
	public $limit;

	// загруженные дочерние страницы
	public $loadObjects;

	// дети не найдены
	public $yesChilds;

	public function __construct()
    {
		$this->reset();
    }

	# reset class
	public function reset()
	{
		$this->current_path_url = APP_BASE_URL_NO_SLASH;
		$this->customData = array();
		$this->paginationArray = array();
		$this->pagination = TRUE;
		$this->orderField = 'obj_date_create';
		$this->orderAsc = 'DESC';
		$this->limit = 20;
		$this->paginationKey = '';
		$this->loadObjects = array();
		$this->paginationCurrent = 0;
		$this->yesChilds = FALSE;
		$this->objects = array();
		$this->countChilds = 0;
	}

	public function __get($var)
	{
		$CI = &get_instance();
		$CI->page->load($this->thisObject());
		return $CI->page->$var;
	}

	public function load($objects = array())
	{
		$this->objects = $objects;
		unset($objects);

		$cntObj = count($this->objects);
		$this->currentIndex = $cntObj - 1;

		$this->curTplPage = $this->objects[$this->currentIndex]['obj_tpl_page'];
		$this->curTplContent = $this->objects[$this->currentIndex]['obj_tpl_content'];
	}

	# информация о текущем объекте
	public function object($var = false) {
		return $this->thisObject($var);
	}
	# информация о текущем объекте
	public function thisObject($var = FALSE)
	{
		if($var)
		{
			if(isset($this->objects[$this->currentIndex][$var]))
				return $this->objects[$this->currentIndex][$var];
			else
				return '';
		}
		elseif(isset($this->objects[$this->currentIndex]))
		{
			return $this->objects[$this->currentIndex];
		}
		else
		{
			return array();
		}

	}

	# родительский объект определенного уровня от самого верхнего.
	public function this_parent($level = 0){
		return $this->thisParent($level);
	}
	public function thisParent($level = 0)
	{
		if(isset($this->objects[$level]))
		{
			return $this->objects[$level];
		}
		else
		{
			return array();
		}
	}

	# Возвращает родительский объект для текущего
	public function getParentObject($decrIndex = 0)
	{
		if($decrIndex)
			$index = $this->currentIndex - $decrIndex;
		else
			$index = $this->currentIndex - 1;

		if(isset($this->objects[$index])){
			return $this->objects[$index];
		}else{
			return array();
		}
	}

	# хранилище - добавить элемент
	public function setItem($itemName, $itemValue)
	{
		$this->customData[$itemName] = $itemValue;
	}

	# хранилище - получить элемент
	public function item($itemName, $return = FALSE)
	{
		return isset($this->customData[$itemName]) ? $this->customData[$itemName] : $return;
	}

	# возвращает массив row_id-объъетов текущей ветки для текущего объекта
	public function treeID()
	{
		$out = array();
		foreach($this->objects as $value) {
			$out[] = $value['tree_id'];
		}
		return $out;
	}

	# возвращает объекты, вложенные в каталоги
	/*
	* $parentsObjTypes - ID типов родительских объектов
	*/
	public function getInnerInit($parentsObjTypes = array())
	{
		$CI = &get_instance();

		// если нет типов, то берем тип каталог
		if(!$parentsObjTypes)
			$parentsObjTypes = array(2);

		$parentID = $this->objects[$this->currentIndex]['tree_id']; // ID текущей ноды

		# сначала получим потомков, в которых могут быть целевые объекты
		$CI->TreelibAdmModel->reset();
		$objects = $CI->TreelibAdmModel->loadAllChildsTree($parentID, $parentsObjTypes);
		$CI->TreelibAdmModel->reset();

		# из объектов надо построить одномерный массив
		$tmp = array();
		//$objectsID = array();
		foreach($objects as $value){
			foreach($value as $k => $v){
				$tmp[$k] = $v;
				//$objectsID[$k] = $v['path_url'];
			}
		}

		# если parents объектов нет, то текущий объект - есть parents
		if($tmp){
			$this->loadObjects = $tmp;
			$this->yesChilds = TRUE;
		}else{
			$this->loadObjects[$parentID] = $this->objects[$this->currentIndex];
			//$objectsID[$this->objects[$this->currentIndex]['tree_id']] = $this->objects[$this->currentIndex]['path_url'];
		}
		unset($tmp);
		return $objects;
	}

	# возвращает объекты, вложенные в каталоги, полученные в $this->getInnerInit
	/*
	* $targetObjTypes - ID типов целевых объектов
	* $parentsObjTypes - ID типов родительских объектов
	* $typeNode - тип нод (оригиналы, копии или все)
	* $onlyObjectsID - ТОЛЬКО ОПРЕДЕЛЕННЫЕ ID целевых объектов
	*/
	public function getInnerObjects($targetObjTypes = array(), $parentsObjTypes = array(), $typeNode = 'orig', $onlyObjectsID = array())
	{
		$CI = &get_instance();
		$finishObjects = array();
		if(!$this->loadObjects){
			$this->getInnerInit($parentsObjTypes);
		}

		# достанем все целевые объекты
		$CI->ObjectAdmModel->reset();

		if($this->paginationKey)
			$CI->ObjectAdmModel->paginationKey = $this->paginationKey;

		$CI->ObjectAdmModel->parentsObjects = $this->loadObjects;
		$CI->ObjectAdmModel->paginationTrue = $this->pagination;
		$CI->ObjectAdmModel->orderField = $this->orderField;
		$CI->ObjectAdmModel->orderAsc = $this->orderAsc;
		$CI->ObjectAdmModel->objectTypeTree = $targetObjTypes;
		$CI->ObjectAdmModel->onlyObjectsID = $onlyObjectsID;
		$CI->ObjectAdmModel->nodeType = $typeNode;
		$CI->ObjectAdmModel->limit = $this->limit;

		$finishObjects = $CI->ObjectAdmModel->getChildsObjects(array_keys($this->loadObjects));

		$this->paginationArray = $CI->ObjectAdmModel->paginationArray;
		$this->paginationCurrent = $CI->ObjectAdmModel->pagCurrentPage;
		$this->countChilds = $CI->ObjectAdmModel->pagRows;
		$CI->ObjectAdmModel->reset();

		return $finishObjects;
	}

	# возврящает прямых потомков текущего объекта
	public function thisChilds($targetObjTypes = array(), $typeNode = 'orig', $onlyObjectsID = array())
	{
		$CI = &get_instance();
		$Objects = array();
		$parentID = $this->objects[$this->currentIndex]['tree_id']; // ID текущей ноды

		# достанем все целевые объекты
		$CI->ObjectAdmModel->reset();

		$CI->ObjectAdmModel->pathUrl = $this->link;

		if($this->paginationKey)
			$CI->ObjectAdmModel->paginationKey = $this->paginationKey;

		$CI->ObjectAdmModel->paginationTrue = $this->pagination;

		$CI->ObjectAdmModel->orderField = $this->orderField;
		$CI->ObjectAdmModel->orderAsc = $this->orderAsc;
		$CI->ObjectAdmModel->objectTypeTree = $targetObjTypes;

		$CI->ObjectAdmModel->onlyObjectsID = $onlyObjectsID;

		$CI->ObjectAdmModel->nodeType = $typeNode;
		$CI->ObjectAdmModel->limit = $this->limit;
		$Objects = $CI->ObjectAdmModel->getChildsObjects($parentID);

		$this->paginationArray = $CI->ObjectAdmModel->paginationArray;
		$this->paginationCurrent = $CI->ObjectAdmModel->pagCurrentPage;
		$CI->ObjectAdmModel->reset();

		return $Objects;
	}
}
