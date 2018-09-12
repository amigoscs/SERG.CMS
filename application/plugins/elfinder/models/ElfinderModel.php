<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  Elfinder Model
	*
	* UPD 2017-11-29
	* version 2.4
	*
	* 2.4 (2017-11-29)
	* Обновлены патчи
	*
	* UPD 2018-06-28
	* version 2.5
	* Добавлен метод getFileName(). Мелкие правки
	*
	* UPD 2018-07-23
	* version 2.6
	* Переделана логика работы по формированию миниатюр для изображений
*/

class ElfinderModel extends CI_Model {

	public $images, $imagesInfo, $index, $siteUrl;

	public $imagesExtensions, $placeholderPath, $placeholderFileName, $placeholderExt, $thumbPath;

	function __construct()
    {
        parent::__construct();
		$this->imagesExtensions = array(
			'jpg' => 'jpg',
			'jpeg' => 'jpeg',
			'png' => 'png',
			'gif' => 'gif',
			'bmp' => 'bmp',
		);
		$this->placeholderPath = app_get_option("el_placehold_path", "elfinder", "uploads/placeholder/");
		$this->thumbPath = app_get_option("el_thumb_path", "elfinder", "uploads/thumb/");
		$this->placeholderFileName = 'placehold';
		$this->placeholderExt = 'jpg';
		$this->siteUrl = info('baseUrl');

		if(!file_exists(APP_BASE_PATH . $this->thumbPath)) {
			@mkdir(APP_BASE_PATH . $this->thumbPath, 0777);
		}

		if(!file_exists(APP_BASE_PATH . $this->placeholderPath)) {
			@mkdir(APP_BASE_PATH . $this->placeholderPath, 0777);
		}
    }

	public function reset()
	{
		$this->images = array();
		$this->imagesInfo = array();
		$this->index = 1;
	}


	# загрузка изображений
	public function load($images)
	{
		$this->reset();

		if(!$images)
			return array();

		if(!is_array($images)){
			$images = app_explode('_ELF_', $images);
		}

		# извлечем описание к файлам
		$i = 1;
		foreach($images as $key => $value){
			$a = $b = '';
			@list($a, $b) = explode('##', $value);
			$this->images[$i] = $a;
			$this->imagesInfo[$i] = $b;
			++$i;
		}
		return $this->images;
	}

	# возвращает URL к файлу с индексом
	public function getFile($index = 1, $fullPath = TRUE)
	{
		if($index < 1)
			$index = 1;

		if(isset($this->images[$index]))
		{
			$this->index = $index;

			if($fullPath)
				return info('base_url') . $this->images[$this->index];
			else
				return $this->images[$this->index];
		}

		return '';
	}

	# возвращает описание файла
	public function getFileInfo($index = 0)
	{
		$out = '';
		if(!$index) {
			$index = $this->index;
		}

		if(isset($this->imagesInfo[$index])) {
			$out = $this->imagesInfo[$index];
		}

		return $out;
	}

	# возвращает расширение файла
	public function getFileExtension($index = 0)
	{
		$out = '';
		if(!$index) {
			$index = $this->index;
		}

		if(isset($this->images[$index])) {
			$info = pathinfo($this->images[$index]);
			$out = $info['extension'];
		}

		return $out;
	}

	# возвращает имя файла (без расширения или с ним)
	public function getFileName($index = 0, $plusExtension = false)
	{
		$out = '';
		if(!$index) {
			$index = $this->index;
		}

		if(isset($this->images[$index])) {
			$info = pathinfo($this->images[$index]);
			$out = $info['filename'];
			if($plusExtension) {
				$out .= '.' . $info['extension'];
			}
		}

		return $out;
	}

	# возвращает превью для картинки
	public function getImageThumb($index = 1, $width = 300, $height = 300, $method = 'resize', $fullPath = TRUE)
	{
		$urlToImage = $thumbFileName = '';

		$prePath = '';
		if($fullPath) {
			$prePath = $this->siteUrl ;
		}

		if(isset($this->images[$index]))
		{
			$this->index = $index;

			# проверка на расширение. Превью только для изображений
			$ext = substr(strrchr($this->images[$index], '.'), 1);
			if(isset($this->imagesExtensions[$ext])) {
				 $urlToImage = $this->images[$index];
			}
			//$fileName -> uploads/folder/folder2/file_image.jpg
			$thumbFileName = $this->thumbFileName($urlToImage, $width, $height, $ext);
		}

		if(!$thumbFileName) {
			return $prePath . $this->placeholderPath . $this->placeholderFileName . '-' . $width . '-' . $height . '.' . $this->placeholderExt;
		}

		# дополнительная проверка. Возможно файл имеет неверное расширение
		//if(@!getimagesize($imagePath))
			//return $this->placeholderPath . $placeholder;

		return $prePath . $this->_createImageThumb($urlToImage, $thumbFileName, $width, $height, $ext, $method);
	}

	# возвращает имя изобращения для превью
	public function thumbFileName($source = '', $width = 0 , $height = 0, $ext = '')
	{
		if(!$source) {
			return false;
		}

		$out = md5($source);

		if($width || $height) {
			$out .= '-' . $width . '-' . $height;
		}

		if($ext) {
			$out .= '.' . $ext;
		}

		return $out;
	}

	# генератор превьюшек
	private function _createImageThumb($urlToImage, $thumbFileName, $size_x = 0, $size_y = 0, $ext, $method = 'resize')
	{
		$thumbImagePath = APP_BASE_PATH . $this->thumbPath . $thumbFileName;
		$thumbImageUrl = $this->thumbPath . $thumbFileName;

		if(file_exists($thumbImagePath)) {
			return $thumbImageUrl;
		}

		$config['image_library'] = 'gd2';
		$config['source_image']	= APP_BASE_PATH . $urlToImage;
		$config['create_thumb'] = TRUE;
		$config['maintain_ratio'] = TRUE;
		$config['width'] = $size_x;
		$config['height'] = $size_y;
		$config['thumb_marker'] = '';
		$config['new_image'] = APP_BASE_PATH . $this->thumbPath . $thumbFileName;

		$this->image_lib->clear();
		switch($method)
		{
			case 'resize':
				$this->image_lib->initialize($config);
				$this->image_lib->resize();
				break;
			case 'crop':
				$this->image_lib->initialize($config);
				$this->image_lib->crop();
				break;
			case 'resize_center_crop':
				$this->image_lib->get_image_properties($config['source_image']);

				$w = $this->image_lib->orig_width / $config['width'];
				$h = $this->image_lib->orig_height / $config['height'];
				//pr($this->image_lib->orig_width);

				if($w > $h) {
					$config['width'] = 0;
					$config['height'] = $size_y;
				} else {
					$config['width'] = $size_x;
					$config['height'] = 0;
				}

				$this->image_lib->clear();
				$this->image_lib->initialize($config);
				$x_axis = round($this->image_lib->width / 2 - $size_x / 2);
				$y_axis = round($this->image_lib->height / 2 - $size_y / 2);
				$this->image_lib->clear();

				$this->image_lib->initialize($config);

				$this->image_lib->resize();
				$pathNewFile = $this->image_lib->full_dst_path;

				$this->image_lib->clear();

				$config['image_library'] = 'gd2';
				$config['source_image']	= $pathNewFile;
				$config['create_thumb'] = TRUE;
				$config['maintain_ratio'] = FALSE; // без пропорций
				$config['width'] = $size_x;
				$config['height'] = $size_y;
				$config['thumb_marker'] = '';
				$config['new_image'] = APP_BASE_PATH . $this->thumbPath . $thumbFileName;;
				$config['x_axis'] = $x_axis;
				$config['y_axis'] = $y_axis;

				$this->image_lib->initialize($config);

				$this->image_lib->crop();
				break;

			default:
				return $urlToImage;
		}

		if(!$this->image_lib->display_errors()) {
			return $this->thumbPath . $thumbFileName;
		}else{
			return $this->placeholderPath . $this->placeholderFileName . '-' . $size_x . '-' . $size_y . '.' . $this->placeholderExt;
		}
	}
}
