<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  Breadcrumbs Model
	*
	* UPD 2017-12-29
	* Version 2.1
	*
	* UPD 2018-07-10
	* Version 2.2
	* Добавлен тег title к ссылке в крошках
	*
	* UPD 2018-10-24
	* Version 2.3
	* Совместимость с версией CMS 7.4
	*
*/

class Breadcrumbs extends CI_Model {

	private $patch_array = array();

	private $base_url;
	private $cut, $last_cut;

	private $prev_url = '';
	private $current_url = '';
	private $visibleCurrent = FALSE; // отображать ссылку на текущую страницу
	private $lastElemClass = ''; // класс последнего элемента
	private $addUlClass;
	private $wrapHtml; // обертка для крошек

	function __construct()
    {
        parent::__construct();
		$this->base_url = info('base_url');
		$this->cut = ''; // разделитель
		$this->last_cut = '';
		$this->visibleCurrent = TRUE;
		$this->lastElemClass = ' class="last"';
		$this->addUlClass = '';
    }

	public function UlWrap($before = '', $after = '')
	{
		if($before && $after){
			$this->wrapHtml = $before;
			$this->wrapHtml .= '_WRAPUL_';
			$this->wrapHtml .= $after;

		}
	}

	public function addUlClass($class)
	{
		$tmp = '';
		if(is_array($class))
		{
			foreach($class as $value){
				$tmp .= $value . ' ';
			}
		}else
			$tmp = $class;

		$this->addUlClass = trim($tmp);
	}

	public function row()
	{
		$CI = &get_instance();
		$viewHomePage = app_get_option('visible_home', 'breadcrumbs', 'no');
		# показывать на главной
		if($CI->Template->IsHome && $viewHomePage == 'no'){
			return '';
		}

		$this->current_url = $this->urlobjects->link;
		$this->create_path();

		if(!$this->patch_array){
			return '';
		}

		$lastPathID = count($this->patch_array) - 1;
		$this->patch_array[$lastPathID] = str_replace('__MARKER__', $this->lastElemClass, $this->patch_array[$lastPathID]);
		foreach($this->patch_array as $key => &$value){
			$value = str_replace('__MARKER__', '', $value);
			$value = str_replace('__PCONTENT__', $key + 1, $value);
		}

		unset($value);

		$breadcrumbs = '<ol itemscope itemtype="http://schema.org/BreadcrumbList" class="_REPLACE_">';
		$breadcrumbs = str_replace('_REPLACE_', $this->addUlClass, $breadcrumbs);
		$breadcrumbs .= implode($this->cut, $this->patch_array) . $this->last_cut;
		$breadcrumbs .= '</ol>';

		if($this->wrapHtml)
			$breadcrumbs = str_replace('_WRAPUL_', $breadcrumbs, $this->wrapHtml);

		return $breadcrumbs;
	}

	private function create_path()
	{
		$baseUrl = rtrim($this->base_url, '/');
		if(!$this->urlobjects->objects){
			return;
		}
		foreach($this->urlobjects->objects as $value)
		{
			$url = $value['tree_url'];
			if(!$this->visibleCurrent and $url == $this->current_url) {
				continue;
			}

			if($url == 'index')
			{
				$url = $baseUrl;
				$this->prev_url = $this->prev_url . $url;
			}
			else
			{
				$this->prev_url .= '/' . $url;
			}
			$this->patch_array[] = '<li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"__MARKER__><a href="'.$this->prev_url.'" itemscope itemtype="http://schema.org/Thing" itemprop="item" title="' . $value['obj_name'] . '"><span itemprop="name">' . $value['obj_name'] . '</span></a><meta itemprop="position" content="__PCONTENT__" /></li>';
		}
		$this->prev_url = '';
	}


}
