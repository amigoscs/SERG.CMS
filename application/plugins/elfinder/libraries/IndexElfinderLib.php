<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  Библиотека файлового менеджера
	*
	* UPD 2018-09-13
	* Version 1.0

*/

class IndexElfinderLib {


	public function __construct()
	{

	}

	# Страница файлового менеджера
	public function index()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = 'Менеджер файлов';
		$CI->pageContentDescription = $CI->pageContentTitle;
		$data = array('h1' => $CI->pageContentTitle);
		$CI->pageContent = $CI->load->view('admin-page/index', $data, true);
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
		} else {
			exit();
		}

		echo($CI->load->view('admin-page/onlymanager', $data, TRUE));
		exit();
	}

	# коннектор в админ-панели
	public function admin_connector()
	{
		$CI = &get_instance();

		$path_plugin = info('plugins_dir') . 'elfinder/';
		error_reporting(0); // Set E_ALL for debuging

		// elFinder autoload
		require $path_plugin . 'common/php/autoload.php';
		// ===============================================

		// Enable FTP connector netmount
		elFinder::$netDrivers['ftp'] = 'FTP';
		// ===============================================

		// Documentation for connector options:
		// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
		$path = APP_BASE_PATH . 'uploads';
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
					'uploadAllow'   => elfinder_mimeAllowUpload('admin'),// Mimetype `image` and `text/plain` allowed to upload
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

		$connector = new elFinderConnector(new elFinder($opts));
		$connector->run();
	}


}
