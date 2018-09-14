<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  elfinder Ajax class

*/

class Ajax extends CI_Model {
	
	private $post, $get; // данные POST и GET

	function __construct($post, $get)
    {
        parent::__construct();
		$this->post = $post;
		$this->get = $get;
    }

	/*
	* коннектор файлового менеджера для сайта (front-end)
	*/
	public function create()
	{
		error_reporting(0); // Set E_ALL for debuging
		$CI = &get_instance();

		//pr($CI);

		$path_plugin = info('plugins_dir') . 'elfinder/';

		# папка пользователя
		$folderFiles = $this->post['elfFolder'];

		if(!$folderFiles){
			exit('Directory error');
		}

		$pathUrl = info('base_url') . 'uploads/'. app_get_option("el_userfolder_path", "elfinder", "tempuserfiles") . '/' . $folderFiles;;
		$path = APP_BASE_PATH . 'uploads/'. app_get_option("el_userfolder_path", "elfinder", "tempuserfiles") . '/' . $folderFiles;

		if($this->post['cmd'] == 'open')
		{
			# если папки нет, пробуем создать
			if(!file_exists($path))
				@mkdir($path, 0777);

			# если  директория не создалась - значит что-то не так. Сворачиваемся
			if(!file_exists($path)){
				exit('Directory create error. Contact administrator');
			}
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
					'uploadAllow'   => elfinder_mimeAllowUpload('users'),	// Mimetype `image` and `text/plain` allowed to upload
					'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
					'accessControl' => 'elfinder_connector_access',	// disable and hide dot starting files (OPTIONAL)
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

		$connector = new elFinderConnector(new elFinder($opts));
		$connector->run();
	}

}
