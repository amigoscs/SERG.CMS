<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
	* Класс для работы со страницами сайта
	*
	*
	* UPD 2018-10-24
	* Version 1.0
	* Первая версия
	*
	* UPD 2019-03-21
	* Version 1.1
	* Нормализованы ДАТА поля. добавлены методы dataValue и dataSaveValue
	*
*/

class PageLib {

	private $PAGES;
	private $currentIndex = 0;


	public function __construct($pages = array(), $index = false)
	{
		if($index === false) {
			$this->currentIndex = count($pages) - 1;
		} else {
			if($index < 0) {
				$this->currentIndex = 0;
			} else {
				$this->currentIndex = $index;
			}
		}

		$val = null;
		foreach($pages[$this->currentIndex]['data_fields'] as &$val) {
			$val = app_normalize_data_values($val);
		}
		unset($val);

		$this->PAGES = $pages;
	}

	# возвращает текущую страницу
	public function this($var = '')
	{
		if($var) {
			return $this->$var;
		} else {
			return $this->PAGES[$this->currentIndex];
		}

	}

	# возвращает родителя
	public function parent()
	{
		$index = $this->currentIndex - 1;
		return new PageLib($this->PAGES, $index);
	}

	# возвращает массив DATA-пареметров страницы
	public function data($id = false)
	{
		if($id) {
			if(isset($this->PAGES[$this->currentIndex]['data_fields'][$id])) {
				return $this->PAGES[$this->currentIndex]['data_fields'][$id];
			} else {
				return array();
			}
		} else {
			return $this->PAGES[$this->currentIndex]['data_fields'];
		}
	}

	# возвращает значение DATA-параметров страницы
	public function dataValue($id = false)
	{
		if($data = $this->data($id)) {
			return $data['value'];
		}
		return false;
	}

	# возвращает значение DATA-параметров (Прямое значение из базы)
	public function dataSaveValue($id = false)
	{
		if($data = $this->data($id)) {
			return $data['orig_value'];
		}
		return false;
	}

	# гетер текущей страницы
	public function __get($var)
	{
		switch($var) {
			case 'H1':
				$var = 'obj_h1';
				break;
			case 'Name':
				$var = 'obj_name';
				break;
			case 'Description':
				$var = 'obj_description';
				break;
			case 'Keywords':
				$var = 'obj_keywords';
				break;
			case 'Title':
				$var = 'obj_title';
				break;
			case 'ShortContent':
			case 'Anons':
				$var = 'obj_anons';
				break;
			case 'Content':
				$var = 'obj_content';
				break;
			case 'ParentNodeID':
				$var = 'tree_parent_id';
				break;
			case 'NodeID':
				$var = 'tree_id';
				break;
			case 'Link':
				$var = 'path_url';
				break;
			case 'Canonical':
				$var = 'obj_canonical';
				break;
		}
		if(isset($this->PAGES[$this->currentIndex][$var])) {
			return $this->PAGES[$this->currentIndex][$var];
		} else {
			return NULL;
		}
	}
}
