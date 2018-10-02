<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  библиотека для работы с CVS файлами
	*
	* version 1.0
	* UPD 2018-10-02

 // Пример использования

 $file = "file.txt";
 $line = 1000000;
 $count_line = 5;

 $stream = new ReadFileLib($file);

 // Укажем, что читать надо с $line строки
 $stream->SetOffset($line);

 // Получаем содержимое $count_line строк
 $result = $stream->Read($count_line);

 print_r("<pre>");
 print_r($result);
 print_r("</pre>");


*/
final class csvFileLib
{
	protected $handler = null;
	protected $fbuffer = array();
	private $csvDelimiter, $csvEnclosure, $notSaveValue;

	/**
	* Конструктор класса, открывающий файл для работы
	*
	*/
	public function __construct($delimiter = ';', $enclosure = '"', $notSaveValue = '_NOTSAVE_')
	{
		$this->csvDelimiter = $delimiter;
		$this->csvEnclosure = $enclosure;
		$this->notSaveValue = $notSaveValue;
	}

	/**
	* Инициализация. Открытие файла
	*
	* @param string $filename
	* @param string $flag
	*
	*/
	public function init($filename, $flag = 'rb')
	{
		$this->handler = null;
		if(!($this->handler = fopen($filename, $flag))) {
			throw new Exception("Cannot open or create the file");
		}
	}

	/*
	* Количество строк в файле
	*/
	public function countRows()
	{
		if(!$this->handler) {
			throw new Exception("Invalid file pointer");
		}
		$i = 0;
		while(!feof($this->handler)) {
			if(fgets($this->handler)) {
				++$i;
			}
		}
		return $i;
	}

	/**
	* Построчное чтение $count_line строк файла с учетом сдвига
	*
	* @param int  $count_line
	*
	* @return array
	*/
    public function Read($count_line = 10)
	{
		$buffer = array();
		if(!$this->handler) {
			throw new Exception("Invalid file pointer");
		}

		while(!feof($this->handler))
		{
			$buffer[] = fgets($this->handler);
			$count_line--;
			if($count_line == 0) break;
		}

		return $buffer;
	}

	/**
	* Установить строку, с которой производить чтение файла
	*
	* @param int  $line
	* @param bool $lastLine - поставить курсор в конец файла
	*/
	public function SetOffset($line = 0, $lastLine = false)
	{
		if(!$this->handler) {
			throw new Exception("Invalid file pointer");
		}

		// сбросим указатель
		if($lastLine) {
			fseek($this->handler, 0, SEEK_END);
			return true;
		} else {
			fseek($this->handler, 0);
		}

		while(!feof($this->handler) && $line--) {
			fgets($this->handler);
		}
		return true;
	}

	/*
	* запись строки в файл CSV
	*/
	public function write(array $values)
	{
		if(!$this->handler) {
			throw new Exception("Invalid file pointer");
		}
		foreach($values as &$value) {
			$value = trim($value);
			//$value = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $value);
			$value = str_replace(array('&nbsp;', "\r\n", "\r", "\n", "\t", '  ', '    ', '    '), ' ', $value);
		}
		unset($value);
		fputcsv($this->handler, $values, $this->csvDelimiter, $this->csvEnclosure);
	}

	/*
	* Создание файла csv с объектами
	*/
	public function createFileCSV(array $keys, array $objects)
	{
		if(!$this->handler) {
			throw new Exception('Файл не определен');
		}

		# сначала запишем заголовки
		$this->write(array_values($keys));
		$this->write(array_keys($keys));

		if(!$objects) {
			return true;
		}

		$value = array();
		foreach($objects as $valueObject)
		{
			foreach($keys as $key => &$value) {
				if(isset($valueObject[$key])) {
					$value = $valueObject[$key];
				} else {
					$value = $this->notSaveValue;
				}
			}
			$this->write(array_values($keys));
		}
		unset($value);
		return true;
	}

	/*
	* Возвращает массив заголовков CSV файла
	*/
	public function getHeadersCSV()
	{
		$this->SetOffset(0);
		$result = $this->Read(2);

		if(count($result) < 2) {
			throw new Exception('Некорректный файл');
		}

		$arrNames = str_getcsv($result[0], $this->csvDelimiter, $this->csvEnclosure);
		$arrKeys= str_getcsv($result[1], $this->csvDelimiter, $this->csvEnclosure);

		$result = array_combine($arrKeys, $arrNames);
		if(!$result) {
			throw new Exception('Некорректный файл');
		}

		return $result;
	}




};
