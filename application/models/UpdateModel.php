<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * selfUpdate
 *
 * Класс для обновления одного файла файлом из удаленного источника.
 * Адрес источника передается в конструктор. Скрипт загружает файл во временную папку
 * DATE UPD: 2017-11-22
 * version 0.12
 
 * DATE UPD: 2017-12-13
 * version 0.13
 * Исправлена ошибка при очистке директории
 */
class UpdateModel extends CI_Model
{
	public $pathUpdate = '_update/';
	/**
	 * 
	 * @var string директория на сервере, куда загружается обновление, чтобы потом заменить работающий скрипт index.php
	 */
	public $pathUpdateZip	 = '_update/sergcms.zip';
	/**
	 * 
	 * @var string директория для хранения прошлой версии скрипта
	 */
	public $pathBackup	 = '_update/backup/';
	
	// URL, откуда скачать файл
	public $updateUrl;
	
	public $noDeletedFiles, $noDeletedDir;
	
	
	// объекты на удаление
	public $deletedArray;
	/**
	 *
	 * @param $root_path
	 * @return Update
	 */
	function __construct()
	{
		parent::__construct();
		$this->pathUpdate = APP_BASE_PATH . '_update/';
		$this->pathUpdateZip = APP_BASE_PATH . '_update/sergcms.zip';
		$this->pathBackup = APP_BASE_PATH . '_update/backup/';
		$this->updateUrl = '';
		$this->noDeletedFiles = array('sergcms.zip');
		//$this->noDeletedDir = array('application/');
		$this->noDeletedDir = array();
		$this->deletedArray = array();
	}


	/**
	 * Основной метод загрузки и установки обновления
	 *
	 * @throws Exception
	 * @param $update_uri string URI источника обновлений
	 * @param $target string путь относительно корня установки к обновляемому файлу
	 * @return array()
	 */
	public function execute($updateUrl)
	{
		$this->updateUrl = $updateUrl;
		return $this->download();
	}

	/**
	 * Загрузка файла с удаленного сервера.
	 * Определяет подходящий метод загрузки в зависимости от серверного окружения.
	 *
	 * @throws Exception
	 * @return string downloaded file_path
	 */
	private function download()
	{
		if($this->curlAvailable()){
			//при доступности cURL используем его, так как метод более гибкий
			return $this->downloadCurl();
		}elseif($this->fopenAvailable()){
			//иначе, если allow_fopen_url = On, пробуем получить обновление через fopen()
			return $this->downloadFopen();
		}
	}


	/**
	 * Загрузка файла с удаленного сервера через fopen()
	 * Для работы необходимо, чтобы в настройках PHP было allow_fopen_url = On
	 *
	 * @return void
	 */
	private function downloadFopen()
	{
		return FALSE;
		$source_stream = null;
		//по умолчанию таймаут на открытие ресурсов составляет 30 - это слишком много, чтобы узнать, что сети нет, поэтому ставим меньший таймаут
		$default_socket_timeout = ini_set('default_socket_timeout', self::TIMEOUT_SOCKET);
		$source_stream = fopen($this->updateUrl, 'r');
		ini_set('default_socket_timeout', $default_socket_timeout);

		if(!$source_stream){
			pr('ERR SOURCE');
		}

		$this->getStreamInfo($source_stream);

		$retry_counter = 0;
		while(
		($delta=stream_copy_to_stream($source_stream,$this->download_stream,102400))
		//бывает, что последние байты ответа сервер отдает очень неохотно - nginx и т.п.
		||( $this->content_length && ($this->current_content_length<$this->content_length) && (++$retry_counter<20) )
		||( !$this->content_length && (++$retry_counter<3) )
		){
			if($delta){
				$this->current_content_length += $delta;
				$retry_counter = 0;
			}else{
				sleep(3);
			}

		}
		fclose($source_stream);
	}

	/**
	 * Загрузка файла с удаленного сервера с помощью cURL
	 *
	 * @throws Exception
	 * @return void
	 */
	private function downloadCurl()
	{
		set_time_limit(0); // указываем, чтобы скрипт не ограничивался временем по умолчанию
		ignore_user_abort(1); // указываем, чтобы скрипт продолжал работать даже при разрыве
		$path = $this->pathUpdateZip;
		$url = $this->updateUrl;
		$fp = fopen($path, 'w+');

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Устанавливаем параметр, чтобы curl возвращал данные, вместо того, чтобы выводить их в браузер.
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_exec($ch);

		curl_close($ch);
		fclose($fp);
		return TRUE;
	}

	/**
	 * обработчик чтения заголовков для curl
	 *
	 * @param $ch
	 * @param $header
	 * @return int
	 
	private function curlHeaderHandler($ch,$header)
	{
		$header_matches = null;
		if(preg_match('/content-length:\s*(\d+)/i',$header,$header_matches)) {
			$this->content_length = intval($header_matches[1]);
		}elseif(preg_match('/content-md5:\s*([\da-f]{32})/i',$header,$header_matches)){
			$this->content_md5=$header_matches[1];
		}
		return strlen($header);
	}*/

	/**
	 * обработчик записи в файл для curl
	 * 
	 * @param $ch
	 * @param $chunk
	 * @return int
	 
	private function curlWriteHandler($ch,$chunk)
	{
		$size = 0;
		if($this->download_stream&&is_resource($this->download_stream)) {
			$size = fwrite($this->download_stream,$chunk);
			$this->current_content_length += $size;
		}else {
			throw new Exception('Ошибка сохранения файла на сервере');
		}
		return $size;
	}*/

	/**
	 * Replace current version by merged old and updated files
	 * @param $source_path string
	 * @param $target_path string
	 * @return string backup directory paty
	 */
	private function replace($source_path, $target_path)
	{
		pr($source_path);
		pr(file_exists($source_path));
		//$target_path = self::formatPath($target_path);
		//$source_path = self::formatPath($source_path);
		$backup_path = false;
		/*if(file_exists($this->root_path.$target_path)){
			$backup_path = self::PATH_BACKUP;
			$backup_path = self::formatPath($backup_path);
			$this->cleanupPath($backup_path);
			$this->mkdir($backup_path);
			$backup_path .= '/'.basename($target_path);
		}*/
		if($backup_path){
			if(!$this->rename($target_path,$backup_path)){
				throw new Exception("Ошибка создания бекапа {$target_path} в папке {$backup_path}");
			}
		}

		if(!$this->rename($source_path, $target_path)){
			//rollback rename
			if($backup_path){
				$this->rename($backup_path,$target_path);
			}
			throw new Exception("Ошибка обновления {$target_path} в {$source_path}");
		}
		return $backup_path;
	}

	/**
	 * "Настойчивое" переименование
	 *
	 * @param $oldname string path
	 * @param $newname string path
	 * @return boolean
	 */
	public function rename($resourceFile, $targetFile)
	{
		return rename($resourceFile, $targetFile);
		$result = false;
		if(@rename($this->root_path.$oldname,$this->root_path.$newname)
		||sleep(3)
		||@rename($this->root_path.$oldname,$this->root_path.$newname)){
			$result = true;
		}
		return $result;
	}

	/**
	 * Очистка директории от файлов
	 *
	 * @param $paths string|array
	 * @param $skip_directory можно оставлять неудаляемые директории
	 * @return void
	 
	public function cleanupPath($paths, $skip_directory = false)
	{
		foreach((array)$paths as $path){
			if(file_exists($path)){
				$dir = opendir($path);
				while(FALSE !==($current_path = readdir($dir))){
					if(($current_path != '.' ) && ($current_path != '..') && !in_array($current_path, $this->noDeletedFiles)){
						if(is_dir($path . '/' . $current_path)){
							$this->cleanupPath($path . '/' . $current_path, $skip_directory);
						}else{
							if(!@unlink($path . '' . $current_path)){
								//throw new Exception("Не могу удалить файл {$path}/{$current_path}");
							}
						}
					}
				}
				closedir($dir);
				@rmdir($path);
				//pr($path);
				//if(!@rmdir($path) && !$skip_directory){
					//throw new Exception("Не могу удалить директорию {$path}");
				//}
			}
		}
	}*/

	/**
	 * Приводим пути к nix виду
	 *
	 * windows поймет и такие, а в случае использования правил постобработки с использованием регулярных выражения последние упрощаются
	 * @param $path string
	 * @return string
	 */
	private static function formatPath($path)
	{
		$path = preg_replace('@([/\\\\]+)@','/',$path);
		return preg_replace('@/$@','',$path);
	}

	/**
	 * Создание директорий с дополнительными проверками на права записи
	 *
	 * @param $target_path
	 * @param $mode
	 * @return void
	 */
	private function mkdir($target_path,$mode = 0777)
	{
		if(!file_exists($this->root_path.$target_path)){
			if(!mkdir($this->root_path.$target_path,$mode&0777,true)){
				throw new Exception("не могу создать директорию {$target_path}");
			}
		}elseif(!is_dir($this->root_path.$target_path)){
			throw new Exception("Не могу создать директорию {$target_path}, так как есть файл с таким изменем");

		}elseif(!is_writable($this->root_path.$target_path)){
			throw new Exception("{$target_path} должна быть доступна по записи. Установите необходимые права доступа.");
		}
	}

	/**
	 * Проверяем возможность использовать cURL
	 *
	 * @return boolean
	 */
	private function curlAvailable()
	{
		return extension_loaded('curl') && function_exists('curl_init') && preg_match('/https?:\/\//', $this->updateUrl);
	}


	/**
	 * Проверяем возможность использовать fopen
	 *
	 * @return boolean
	 */
	private function fopenAvailable()
	{
		$result = false;
		if(stream_is_local($this->updateUrl)){
			$result = true;
		}else{
			$scheme = parse_url($this->updateUrl, PHP_URL_SCHEME);
			if($scheme == 'https'){
				$scheme = 'http';
			}
			$result = ini_get('allow_url_fopen') && in_array($scheme,stream_get_wrappers());
		}
		return $result;
	}

	/**
	 * Читаем метаданные загружаемого файла
	 *
	 * @param $source_stream resource
	 * @param $download_content_length int
	 * @return void
	
	private function getStreamInfo($source_stream,$download_content_length=4096)
	{
		$stream_meta_data=stream_get_meta_data($source_stream);

		//KNOWHOW без явного чтения потока метаданные потока не всегда доступны
		//read data chunk to determine stream meta data
		$buf = stream_get_contents($source_stream,$download_content_length);

		$this->current_content_length = min($download_content_length,strlen($buf));

		$stream_seekable = isset($stream_meta_data['seekable'])?$stream_meta_data['seekable']:false;

		$headers = array();
		//В зависимости от реализации обертки для http заголовки могут находиться в разных местах
		if(isset($stream_meta_data["wrapper_data"]["headers"])){
			$headers = $stream_meta_data["wrapper_data"]["headers"];
		}elseif(isset($stream_meta_data["wrapper_data"])){
			$headers = $stream_meta_data["wrapper_data"];
		}


		$header_matches = null;
		foreach($headers as $header){
			//ищем информацию о размере передаваемых данных
			if(preg_match('/content-length:\s*(\d+)/i',$header,$header_matches)){
				$this->content_length=intval($header_matches[1]);
				//и md5 хеше
			}elseif(preg_match('/content-md5:\s*([\da-f]{32})/i',$header,$header_matches)){
				$this->content_md5=$header_matches[1];
			}
		}


		if($buf && $this->download_stream){
			fwrite($this->download_stream,$buf);
		}
	} */

	/**
	 * "Настойчивое" открытие файла
	 * на случай если ресурс занят другим процессом или директория еще не создана
	 * @param $filename
	 * @param $mode
	 * @param $retry
	 * @return resource
	 */
	private function fopen($filename,$mode,$retry = 5)
	{
		$path = $this->root_path.$filename;
		if(!file_exists($path)){
			$this->mkdir(dirname($filename));
		}
		while(!($fp = fopen($path,$mode))){
			if(--$retry>0){
				sleep(1);
			}else{
				break;
			}
		}
		return $fp;
	}
	
	private function recursiveDirectory($dirs, $parent = '', $level = 0)
	{
		foreach($dirs as $key => $value){
			if(is_array($value)){
				$this->deletedArray['dir'][] = $parent . $key;
				$this->recursiveDirectory($value, $parent . $key);
			}else{
				$this->deletedArray['files'][] = $parent . $value;
			}
		}
	}
	
	private function recursiveDeleted()
	{
		# сначала удаляем файлы
		if(isset($this->deletedArray['files']) and $this->deletedArray['files'])
		{
			foreach($this->deletedArray['files'] as $filePath){
				$fileName = basename($filePath);
				# пропусувем фалы, которые не надо удалять
				if(in_array($fileName, $this->noDeletedFiles))
					continue;
				
				@unlink($this->pathUpdate . $filePath);
			}
		}
		
		# после фалов удаляем директории
		// отсортируем, чтобы сначала шли на удаление самые глубокие директории
		if(isset($this->deletedArray['dir']) and $this->deletedArray['dir'])
		{
			arsort($this->deletedArray['dir'], SORT_STRING);
			foreach($this->deletedArray['dir'] as $dir){
				if(in_array($dir, $this->noDeletedDir))
					continue;

				@rmdir($this->pathUpdate . $dir);
			}
		}
		return TRUE;
	}
	
	# очистка директории с фалами обновления
	public function cleanDirectory()
	{
		$this->load->helper('directory');
		$map = directory_map($this->pathUpdate);
		# построение списка на удаление
		$this->recursiveDirectory($map);
		# запуск удаления фалов и директорий
		$this->recursiveDeleted();
		return TRUE;
	}
	
	# удаление файла или директории
	public function deleteFile($path)
	{
		$path = APP_BASE_PATH . $path;
		if(file_exists($path))
		{
			if(is_dir($path)){
				@rmdir($path);
			}elseif(is_file($path)){
				@unlink($path);
			}else{
				
			}
		}
		return TRUE;
	}
}
