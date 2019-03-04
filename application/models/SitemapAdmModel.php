<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* Модель содержит методы для работы с файлом sitemap
	* UPD 2017-10-20
	* Version 0.1
	*
	* Version 1.0
	* UPD 2018-09-06
	* Переделана логика построения файла sitemap
	*
	* Version 1.1
	* UPD 2019-03-04
	* Исправлена ошибка - выдача страницы, отключенной на сайте
*/

class SitemapAdmModel extends CI_Model {

	private $cacheKey; // ключ файла кэша
	public $lastTimeUpdate = ''; // последнее обновление сайтмап
	public $nameFile = '';
	private $treeChilds, $resArray ;

	public function __construct()
    {
        parent::__construct();
		$this->nameFile = app_get_option('sitemap_file', 'general', 'sitemap.xml');
		$this->treeChilds = $this->resArray = array();
		//app_delete_cash();
	}


	public function init()
	{
		//$this->lastTimeUpdate = app_get_cache($cacheKey);
	}


	/*
	* создание файла сайтмап
	*/

	public function createSitemapFile()
	{

		$this->db->select('tree_folow, tree_parent_id, tree_id, obj_h1, obj_lastmod, obj_date_publish, obj_canonical');
		$this->db->join('objects', 'tree.tree_object = objects.obj_id');
		$this->db->where('tree_folow', '1');
		$this->db->where('obj_status', 'publish');
		$this->db->where('obj_ugroups_access', 'ALL');
		$query = $this->db->get('tree');

		$arr = array();
		foreach($query->result_array() as $row) {
			$this->treeChilds[$row['tree_parent_id']][$row['tree_id']] = $row;
		}

		$parentID = 0;
		// найдем главную страницу
		foreach($this->treeChilds[0] as $value) {
			if($value['obj_canonical'] == 'index') {
				$parentID = $value['tree_id'];
				$this->resArray[$value['tree_id']] = $value;
			}
			break;
		}

		// подготовим массив для записи в XML
		$this->prepareArray($parentID);
		unset($this->treeChilds);

		return $this->_printXmlSitemap();
	}

	private function _printXmlSitemap()
	{
		$out = '<?xml version="1.0" encoding="UTF-8"?>
		<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$thisTime = time();
		$value = $object = array();
		foreach($this->resArray as &$object) {

			$priority = 0;
			$datetime1 = new DateTime("@$thisTime");
			$datetime2 = new DateTime("@{$object['obj_lastmod']}");
			$lastMod = $datetime2->format('c');
			$diff = $datetime1->diff($datetime2)->d;

			if($diff < 10) {
				$priority = 1;
			} else if($diff < 20) {
				$priority = 0.8;
			} else if($diff < 30) {
				$priority = 0.6;
			} else {
				$priority = 0.5;
			}

			/*	$datetime1 = date_create(time());
    			$datetime2 = date_create($object['obj_lastmod']);
				$interval = date_diff($datetime1, $datetime2);
				pr($interval->format('%d'));*/

			$out .= '<url>';
			if($object['obj_canonical'] == 'index') {
				$out .= '<loc>' .  APP_BASE_URL_NO_SLASH . '</loc>';
			} else {
				$out .= '<loc>' .  APP_BASE_URL . $object['obj_canonical'] . '</loc>';
			}
			//$out .= '<lastmod>2016-07-14T12:10:03+00:00</lastmod>';
			$out .= '<lastmod>' .  $lastMod . '</lastmod>';

			//$out .= '<changefreq>monthly</changefreq>';
			$out .= '<priority>' . $priority . '</priority>';
			$out .= '</url>';

		}
		unset($this->resArray, $object);

		$out .= '</urlset>';
		//echo $out;
		# запись в файл
		$fp = fopen(info('base_path') . $this->nameFile, 'w');
		fwrite($fp, $out);
		fclose($fp);
		unset($out);
		return TRUE;
	}

	# подготавливает массив для записи в sitemap
	public function prepareArray($parentID = 0)
	{
		if(isset($this->treeChilds[$parentID])) {
			foreach($this->treeChilds[$parentID] as $key => $value) {
				$this->resArray[$value['tree_id']] = $value;
				$this->prepareArray($value['tree_id']);
			}
		}
	}

}
