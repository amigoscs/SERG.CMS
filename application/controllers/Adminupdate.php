<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Adminupdate extends CI_Controller {

	/**
	 * Контроллер обновлений
	 *
	 * version 0.14
	 * UPD 2018-04-16
	*/
	private $pathFrom, $pathTo, $dirUpdateFiles;
	private $copyArray; // массив патчей для обновления

	private $deletedArray = array();
	private $userInfo;

	public function __construct()
	{
		parent::__construct();

		$this->LoginAdmModel->userData();

		# если запрещен доступ к панели, то на регистрацию
		if(!$this->LoginAdmModel->checkAccessPanel()){
			redirect(APP_BASE_URL . 'login', 'refresh');
			exit('Access denied');
		}

		$this->dirUpdateFiles = APP_BASE_PATH . '_update/';
		$this->userInfo = is_login();
		$this->lang->load('panel', $this->userInfo['lang']);
		//$this->pathFrom = info('base_path') . $this->dirUpdateFiles;
		//$this->pathTo = info('base_path');

		$this->copyArray = array();
	}

	public function _remap($method, $args = array())
	{
		if($this->input->get('download')){
			return $this->_ajaxDownload();
		}
		$page = 'page_' . $method;
		if(method_exists($this, $page))
			return $this->$page();
	}


	private function page_index()
	{
		$this->load->model('UpdateModel');
		$pathUpdate = $this->UpdateModel->pathUpdate;
		$pathToInfo = $pathUpdate . 'updateInfo.php';

		$data = array();
		$data['ERROR'] = FALSE;
		$data['ERROR_TEXT'] = 'Script file not found!';
		$data['COMPLITE'] = FALSE;
		$data['COMPLITE_TEXT'] = 'Your system has been successfully updated! <a href="/admin">Go to admin.</a>';
		$data['version'] = 'NOT';
		$data['textInfo'] = '';
		# прочитаем файл с информацией для обновления
		if(file_exists($pathToInfo))
		{
			require($pathToInfo);
			$data['version'] = $newVersion;
			$data['textInfo'] = $textInfo;
			$data['textTitle'] = 'Обновление системы до версии';
			if(isset($title)){
				$data['textTitle'] = $title;
			}
		}
		else
		{
			$data['ERROR'] = TRUE;
			return $this->load->view('admin/system-update', $data);
		}

		$data['updateFiles'] = $data['deletedFiles'] = array();
		if(isset($scriptUpdate))
			$data['updateFiles'] = $scriptUpdate;

		if(isset($scriptDelete))
			$data['deletedFiles'] = $scriptDelete;

		# старт на обновление
		if($this->input->post('update'))
		{
			foreach($scriptUpdate as $path){
				$resourceFile = $pathUpdate . $path;
				$targetFile = APP_BASE_PATH  . $path;
				$this->UpdateModel->rename($resourceFile, $targetFile);
			}

			foreach($scriptDelete as $path){
				$this->UpdateModel->deleteFile($path);
			}

			# очистим директорию обновления
			$this->UpdateModel->cleanDirectory();
			$data['COMPLITE'] = TRUE;
		}
		$this->load->view('admin/system-update', $data);
	}

	# загрузка фала и распаковка архива
	private function _ajaxDownload()
	{
		$this->load->model('UpdateModel');
		// предварительно очистим директорию от ненужных файлов
		$this->UpdateModel->cleanDirectory();

		$path = $this->input->post('package');
		$this->load->model('UpdateModel');
		if($res = $this->UpdateModel->execute($path)){
			$pathToZip = $this->UpdateModel->pathUpdateZip;
			$pathUpdate = $this->UpdateModel->pathUpdate;
			$zip = new ZipArchive(); //Создаём объект для работы с ZIP-архивами
			if($zip->open($pathToZip) === TRUE) {
				$zip->extractTo($pathUpdate); //Извлекаем файлы в указанную директорию
				$zip->close(); //Завершаем работу с архивом
			}
		}
		$response = array('status' => 200, 'info' => app_lang('UPDATE_INFO'));
		echo json_encode($response);
	}
}
