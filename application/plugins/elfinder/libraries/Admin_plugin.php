<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  elfinder lib

*/

class Admin_plugin {


	public function __construct()
	{
	}


	public function index()
	{
		$CI = &get_instance();
		$CI->data['page_title'] = 'Менеджер файлов';
		$CI->data['page_description'] = 'Менеджер файлов';
		$CI->data['page_keywords'] = 'Менеджер файлов';
		$data = array();
		return $CI->load->view('admin-page/index', $data, true);
	}

	/*
	* отображает только файловый менеджер
	*/
	public function elfinder_only_page()
	{
		$CI = &get_instance();
		$CI->data['page_title'] = 'Менеджер файлов';
		$CI->data['page_description'] = 'Менеджер файлов';
		$CI->data['page_keywords'] = 'Менеджер файлов';

		$data = array();
		if($typeManager = $CI->input->get('type')){
			$data['type'] = $typeManager;
		}
		else
			exit();


		echo($CI->load->view('admin-page/onlymanager', $data, TRUE));
		exit();
	}


	/*
	* разрешенные MIME типы файлов
	*/
	private function mimeAllowUpload($userType = 'default')
	{
		// php/elFinderVolumeDriver.class.php:446

		if($userType == 'default')
		{
			$mimeArray = array(
				'image', // изображения
				'text/plain', // текст
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
				'application/pdf', // pdf
				'application/msword', // doc
				'text/rtf', // rtf
			);
		}

		return $mimeArray;
	}

	public function connector()
	{
		$CI = &get_instance();

		$path_plugin = info('plugins_dir') . 'elfinder/';
		//return;
		error_reporting(0); // Set E_ALL for debuging

		// elFinder autoload
		require $path_plugin . 'common/php/autoload.php';
		// ===============================================

		// Enable FTP connector netmount
		elFinder::$netDrivers['ftp'] = 'FTP';
		// ===============================================

		// Documentation for connector options:
		// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
		$path = APPPATH;
		$path = str_replace('application/', '', $path) . 'uploads/';
		$replaces = elfinder_replaceChars();


		$opts = array(
			/*'debug' => true,*/
			'bind' => array(
				'upload.pre mkdir.pre mkfile.pre rename.pre archive.pre ls.pre' => array(
					'Plugin.Sanitizer.cmdPreprocess'
				),
				'ls' => array(
					'Plugin.Sanitizer.cmdPostprocess'
				),
				'upload.presave' => array(
					'Plugin.Sanitizer.onUpLoadPreSave'
				),
				'rm' => array(
					'Plugin.Deletemythumb.cmdPreprocess'
				),
			),
			'roots' => array(
				array(
					'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
					'path'          => $path,                 // path to files (REQUIRED)
					'URL'           => info('base_url') . 'uploads/', // URL to files (REQUIRED)
					'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
					'uploadAllow'   => array('image', 'text/plain', 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),// Mimetype `image` and `text/plain` allowed to upload
					'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
					'accessControl' => 'elfinder_connector_access' ,                    // disable and hide dot starting files (OPTIONAL)
					'plugin' => array(
								'Sanitizer' => array(
		 						'enable' => true,
								'targets'  => array_keys($replaces), // target chars
		 						'replace'  => array_values($replaces)    // replace to this
							)
		 				)
				)
			)
		);

		//pr($_POST);
		// run elFinder
		$connector = new elFinderConnector(new elFinder($opts));
		$connector->run();

	}


}
