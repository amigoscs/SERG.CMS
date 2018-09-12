<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
	* библиотека работы с деревом сайта
	* version 2.0
	* UPD 2017-08-17
	*/
	
	
class Treelib {
	
	/**
	* максимальный уровень вложенности
	*
	* @var	int
	*/
	public $level_max = 0;
	
	
	/**
	* массив родителей
	*
	* @var	array
	*/
	protected $parents_array = array();
	
	/**
	* массив детей
	*
	* @var	array
	*/
	protected $childs_array = array();
	
	/**
	* url сайта
	*
	* @var	array
	*/
	public $site_url = '';
	
	/**
	* текущий урл сайта
	*
	* @var	array
	*/
	public $this_url = '';
	
	/**
	* типы объектов
	*
	* @var	array
	*/
	public $types = array();
	
	/**
	* объекты
	*
	* @var	array
	*/
	public $objects = array();
	
	
	public function __construct()
    {
		$CI = &get_instance();
		$this->site_url = info('base_url');
    }
	
	
	
	/**
	* menu_for_object
	*
	* создание меню для объкта с вложенностью
	*
	* @return	string
	*/
	public function menu_for_object($obj_id = 0, $parent_inc = false)
	{
		return $this->menuChildsForObject($obj_id);
	}
	
	/*
	* $types - включать только типы объектов
	*/
	public function menuChildsForObject($rowID = 0, $types = array())
	{
		$CI = &get_instance();
		$this->this_url = info('base_url') . $CI->uri->uri_string();
		// дерево ресурсоемкое. Добавляем в кэш
		$cacheKey = 'left_nav_'. $rowID;
		if(!$childs = app_get_cache($cacheKey)){
			$childs = $CI->TreelibAdmModel->loadAllChildsTree($rowID, $types);
			app_add_cache($cacheKey, $childs);
		}
		return $this->createUlListFromArray($childs, $rowID);
	}
	
	
	
	
	
	/**
	* create_ul_list
	*
	* создание меню для объкта с вложенностью
	*
	* @param	объекты
	* @param	родитель
	* @param	счетчик
	* @return	string
	*/
	public function create_ul_list($objects = array(), $parent_id = 0, $count = 0) {
		return $this->createUlListFromArray($objects, $parent_id, $count);
	}
	public function createUlListFromArray($objects = array(), $parent_id = 0, $count = 0)
	{
		//pr($objects);
		$tree = '';
		if(is_array($objects) and isset($objects[$parent_id])){
			
			
			if($count != '0')
			{
				$tree .= '<ul>' . N;
			}
			++$count;
			
			foreach($objects[$parent_id] as $object){
				
				if($this->this_url == $object['path_url'])
					$tree .= '<li class="selected">' . N;
				else
					$tree .= '<li>' . N;
				
				$tree .= '<a href="' . $object['path_url'] . '" title="' . $object['obj_name'] . '">';
				$tree .= '<span>' . $object['obj_name'] . '</span>';
				$tree .= '</a>';
				$tree .= $this->createUlListFromArray($objects, $object['obj2obj_id'], $count);
				$tree .= '</li>';
			}
			
			if($count != '1')
			{
				$tree .= '</ul>' . N;
			}
			--$count;
			
		}
		else
		{
			return $tree;
		}
		return $tree;
	}
}
