<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page {

	/**
	 * класс загружает объект и парсит его для упрощенного доступа к значениям
	 *
	 * UPD 2017-12-05
	 *
	 * Version 2.2
	 */
	public $parseSelect = FALSE; // для select возвращать человеческое значение
	
	
	private $object, $object_fields, $parentObject;
	
	
	public function __construct()
    {
		$this->object = $this->object_fields = $this->parentObject = array();
    }
	
	public function load($object)
	{
		$this->object = $this->object_fields = $this->parentObject = array();
		
		if(isset($object['data_fields']))
		{
			$this->object_fields = $object['data_fields'];
			unset($object['data_fields']);

			foreach($this->object_fields as &$val)
				$val = app_normalize_data_values($val);

			unset($val);
		}
		
		if(isset($object['parent']))
			$this->parentObject = $object['parent'];
			
		$this->object = $object;
		return $this;
	}
	
	public function __get($var)
	{
		switch($var)
		{
			case 'h1':
				return isset($this->object['obj_h1']) ? $this->object['obj_h1'] : '';
				break;
			case 'name':
				return isset($this->object['obj_name']) ? $this->object['obj_name'] : '';
				break;
			case 'description':
				return isset($this->object['obj_description']) ? $this->object['obj_description'] : '';
				break;
			case 'keywords':
				return isset($this->object['obj_keywords']) ? $this->object['obj_keywords'] : '';
				break;
			case 'title':
				return isset($this->object['obj_title']) ? $this->object['obj_title'] : '';
				break;		
			case 'teaser':
			case 'anons':
				return isset($this->object['obj_anons']) ? $this->object['obj_anons'] : '';
				break;
			case 'content':
				return isset($this->object['obj_content']) ? $this->object['obj_content'] : '';
				break;
			case 'cnt_views':
				return isset($this->object['obj_cnt_views']) ? $this->object['obj_cnt_views'] : '';
				break;
			case 'user_author':
				return isset($this->object['obj_user_author']) ? $this->object['obj_user_author'] : '';
				break;
			case 'parentNodeID':
			case 'parent_id':
				return isset($this->object['tree_parent_id']) ? $this->object['tree_parent_id'] : '';
				break;
			case 'id':
				return isset($this->object['obj_id']) ? $this->object['obj_id'] : '';
				break;
			case 'row_id':
			case 'nodeID':
				return isset($this->object['tree_id']) ? $this->object['tree_id'] : '';
				break;
			case 'path_url':
			case 'link':
				return isset($this->object['path_url']) ? $this->object['path_url'] : '';
				break;
			case 'canonical':
				return isset($this->object['obj_canonical']) ? $this->object['obj_canonical'] : '';
				break;
			case 'nodeTypeObject':
				return isset($this->object['tree_type_object']) ? $this->object['tree_type_object'] : '';
				break;
			case 'data':
				return $this->object_fields;
				break;
			default:
				if(isset($this->object[$var]))
				{
					return $this->object[$var];
				}
				else
				{
					return '';
				}
		}
	}
	
	# получить значение родителя
	public function parentValue($var = '')
	{
		switch($var)
		{
			case 'h1':
				return isset($this->parentObject['obj_h1']) ? $this->parentObject['obj_h1'] : '';
				break;
			case 'name':
				return isset($this->parentObject['obj_name']) ? $this->parentObject['obj_name'] : '';
				break;
			case 'description':
				return isset($this->parentObject['obj_description']) ? $this->parentObject['obj_description'] : '';
				break;
			case 'keywords':
				return isset($this->parentObject['obj_keywords']) ? $this->parentObject['obj_keywords'] : '';
				break;
			case 'title':
				return isset($this->parentObject['obj_title']) ? $this->parentObject['obj_title'] : '';
				break;		
			case 'teaser':
			case 'anons':
				return isset($this->parentObject['obj_anons']) ? $this->parentObject['obj_anons'] : '';
				break;
			case 'content':
				return isset($this->parentObject['obj_content']) ? $this->parentObject['obj_content'] : '';
				break;
			case 'cnt_views':
				return isset($this->parentObject['obj_cnt_views']) ? $this->parentObject['obj_cnt_views'] : '';
				break;
			case 'user_author':
				return isset($this->parentObject['obj_user_author']) ? $this->parentObject['obj_user_author'] : '';
				break;
			case 'parentNodeID':
				return isset($this->parentObject['tree_parent_id']) ? $this->parentObject['tree_parent_id'] : '';
				break;
			case 'id':
				return isset($this->parentObject['obj_id']) ? $this->parentObject['obj_id'] : '';
				break;
			case 'nodeID':
				return isset($this->parentObject['tree_id']) ? $this->parentObject['tree_id'] : '';
				break;
			case 'path_url':
			case 'link':
				return isset($this->parentObject['path_url']) ? $this->parentObject['path_url'] : '';
				break;
			case 'canonical':
				return isset($this->parentObject['obj_canonical']) ? $this->parentObject['obj_canonical'] : '';
				break;
			case 'nodeTypeObject':
				return isset($this->parentObject['tree_type_object']) ? $this->parentObject['tree_type_object'] : '';
				break;
			case 'data':
				return $this->parentObject['data_fields'];
				break;
			default:
				if(isset($this->object[$var]))
					return $this->object[$var];
				else
					return '';
		}
	}
	
	public function data($id = 0, $return = '')
	{
		/*if($this->parseSelect) {
			if(isset($this->object_fields[$id]['value']) and $this->object_fields[$id]['types_fields_type'] == 'select')
				return app_parse_select_values($this->object_fields[$id]['types_fields_values'], $this->object_fields[$id]['value']);
		}
		//pr($this->object_fields[$id]);
		return isset($this->object_fields[$id]['value']) ? $this->object_fields[$id]['value'] : $return;*/
		if(!isset($this->object_fields[$id])) return $return;
		if($this->parseSelect){
			if(isset($this->object_fields[$id]['value']))
				return $this->object_fields[$id]['value'];
			else
				return $return;
		}else{
			if(isset($this->object_fields[$id]['orig_value']))
				return $this->object_fields[$id]['orig_value'];
			else
				return $return;
		}
	}
	
	public function dataOrigValue($id = 0, $return = '')
	{
		if(isset($this->object_fields[$id]['orig_value']))
			return $this->object_fields[$id]['orig_value'];
		else
			return $return;
	}
	
	public function dataParseValue($id = 0, $return = '')
	{
		if(isset($this->object_fields[$id]['value']))
				return $this->object_fields[$id]['value'];
			else
				return $return;
	}
	
	public function dataDate($id = 0, $return = '')
	{
		if(isset($this->object_fields[$id]['date_time']))
				return $this->object_fields[$id]['date_time'];
			else
				return $return;
	}
	
	# есдиница иземерения свойства
	public function dataUnit($id = 0)
	{
		return isset($this->object_fields[$id]['types_fields_unit']) ? $this->object_fields[$id]['types_fields_unit'] : '';
	}
	
	# название свойства DATA
	public function dataName($id = 0)
	{
		return isset($this->object_fields[$id]['types_fields_name']) ? $this->object_fields[$id]['types_fields_name'] : '';
	}
	
	
	
	
	
}
