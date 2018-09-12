<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  elfinder Ajax class

*/

class Ajax extends CI_Model {
	
	/*
	* $checkAjax - проверять ли запрос на AJAX. TRUE - да, FALSE - нет;
	* $post - данные, которые идут по POST
	* $get - данные, которые идут по GET
	* $urlArg1 - сегменты url - первый сегмент (/ajax/plugin/ПАПКА ПЛАГИНА/первый сегмент)
	* $urlArg2 - сегменты url - второй сегмент (/ajax/plugin/ПАПКА ПЛАГИНА/первый сегмент/второй сегмент)
	*
	*/
	public $checkAjax;
	private $post, $get, $urlArg1, $urlArg2; // данные POST и GET
	
	function __construct($post, $get, $urlArg1, $urlArg2)
    {
        parent::__construct();
		$this->post = $post;
		$this->get = $get;
		$this->urlArg1 = $urlArg1;
		$this->urlArg2 = $urlArg2;
		
		# поставьте необходимое значение. Можно написать условие
		$this->checkAjax = FALSE;
    }
	/*
	* функция вызывается для запуска методов
	*/
	public function ajaxRun()
	{
		$CI = &get_instance();
		switch($this->urlArg1)
		{
			case 'create':
				return $this->connectorElfinder();
				break;
		}
	}
	
	/*
	* коннектор файлового менеджера для сайта (front-end)
	*/
	public function connectorElfinder()
	{
		$CI = &get_instance();
		
		$path_plugin = info('plugins_dir') . 'elfinder/';
		error_reporting(0); // Set E_ALL for debuging
		
		$folderFiles = $this->post['elfFolder'];
		$path = APPPATH;
		$pathUrl = '';
		
		if($this->post['cmd'] == 'open')
		{
			if(!$folderFiles){
				echo 'Directory error';
				return;
			}
			
			$path = str_replace('application/', '', $path) . 'uploads/tempuserfiles/' . $folderFiles;
			$pathUrl = info('base_url') . 'uploads/tempuserfiles/' . $folderFiles;

			# если папки нет, пробуем создать
			if(!file_exists($path))
				@mkdir($path, 0777);

			# если  директория не создалась - значит что-то не так. Сворачиваемся
			if(!file_exists($path)){
				echo 'Directory create error. Contact administrator';
				return;
			}
		}
		else
		{
			$path = str_replace('application/', '', $path) . 'uploads/tempuserfiles/' . $folderFiles;
			$pathUrl = info('base_url') . 'uploads/tempuserfiles/' . $folderFiles;
			
		}
		
		
		// elFinder autoload
		require $path_plugin . 'common/php/autoload.php';
		// ===============================================
		
		// Enable FTP connector netmount
		elFinder::$netDrivers['ftp'] = 'FTP';
		// ===============================================
		
		// Documentation for connector options:
		// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
		
		$replaces = elfinder_replaceChars();
		
		$opts = array(
			'debug' => TRUE,
			'bind' => array(
				'upload.pre mkdir.pre mkfile.pre rename.pre archive.pre ls.pre' => array(
					'Plugin.Sanitizer.cmdPreprocess'
				),
				'ls' => array(
					'Plugin.Sanitizer.cmdPostprocess'
				),
				'upload.presave' => array(
					'Plugin.Sanitizer.onUpLoadPreSave'
				)
			),
			'roots' => array(
				array(
					//'allowChmodReadOnly' => TRUE,
					'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
					'path'          => $path,                		// path to files (REQUIRED)
					'URL'           => $pathUrl, 					// URL to files (REQUIRED)
					'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
					'uploadAllow'   => elfinder_mimeAllowUpload('default'),	// Mimetype `image` and `text/plain` allowed to upload
					'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
					'accessControl' => 'elfinder_connector_access',	// disable and hide dot starting files (OPTIONAL)
					//'defaults' => array('read' => true, 'write' => true),
					'plugin' => array(
								'Sanitizer' => array(
		 						'enable' => TRUE,
								'targets'  => array_keys($replaces), // target chars
		 						'replace'  => array_values($replaces)    // replace to this
							)
		 				)
				)
			)
		);
		
		// run elFinder
		$connector = new elFinderConnector(new elFinder($opts));
		$connector->run();
	}
	
	
	/*
	* функция вызывается для возврата результата в front-end контроллере
	*/
	public function getResult(){}
	
}
