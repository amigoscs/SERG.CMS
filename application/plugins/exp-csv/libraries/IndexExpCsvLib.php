<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  exp-csv IndexExpCsvLib
	*
	* version 1.0
	* UPD 2018-08-02
	* Библиотека для версии системы 6.0 +
	*
	* version 2.0
	* UPD 2018-10-02
	* Переделка
	*
	* version 2.01
	* UPD 2018-10-03
	* добавлен параметр limitExport для выгрузки порциями
	*
	* version 2.1
	* UPD 2018-10-09
	* Добавлен парсер файла
	*
	* version 2.2
	* UPD 2018-10-10
	* добавлена информация о файле и проверка на заголовки
	*
*/

class IndexExpCsvLib {

	// кодировка файла
	public $csvCharset;

	// лимит превью
	public $limitPreview;

	// библиотека работы с csv-файлом
	public $CSVLIB;

	// AJAX ответ
	public $ajaxResponse;

	public function __construct()
	{
		$CI = &get_instance();
		try {
			$this->csvCharset = 'utf-8';
			$this->limitPreview = app_get_option("csv_count_prev", "exp-csv", "20");
			$this->ajaxResponse = array('status' => 'ERROR', 'info' => '');

			$pathCsvLib = APP_PLUGINS_DIR_PATH . 'exp-csv/libraries/CsvFileLib.php';
			if(!file_exists($pathCsvLib)) {
				$this->CSVLIB = null;
				throw new Exception('Ошибка подключения библиотеки CSV');
			} else {
				require_once($pathCsvLib);
				$this->CSVLIB = new csvFileLib($CI->ExpCsvModel->csvDelimiterField, $CI->ExpCsvModel->csvEnclosure, $CI->ExpCsvModel->noValue);
			}

			// проверим директорию
			if(!file_exists($CI->ExpCsvModel->exportFilePath)) {
				if(!mkdir($CI->ExpCsvModel->exportFilePath)) {
					throw new Exception('Ошибка: директория не может быть создана');
				}
			}

		} catch (Exception $e) {
			$this->_error($e->getMessage());
		}
	}

	# все незарегистрированные адреса отправляем на index
	public function __call($method, $par)
	{
		return $this->index();
	}

	# главная страница
	public function index()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('EXPCSV_TITLE_INDEX');
		$CI->pageContentDescription = $CI->pageContentTitle;

		$data = array('h1' => app_lang('EXPCSV_TITLE_INDEX'));
		$data['notSave'] = $CI->ExpCsvModel->noValue;
		$data['dataPrefix'] = $CI->ExpCsvModel->prefixDataFields;
		$CI->pageContent = $CI->load->view('admin_page/index', $data, true);
	}

	# принимает ajax
	public function ajax()
	{
		$CI = &get_instance();
		$seg = $CI->uri->uri_to_assoc(3);
		$post = $CI->input->post();
		$report = array('status' => 'error', 'text' => '');

		switch($seg['ajax'])
		{
			case 'savevalue':
				$tableUpdate = $post['res_table'];
				$tableKeyUpdate = $post['res_table_key'];
				$tableValue = $post['res_value'];
				$nodeID = $post['res_node_id'];


				$isUpdate = false;
				//parse_str($post['field_name'] . '=' . $post['field_value'], $fieldParam);
				try
				{
					# обновление data-параметра
					if($tableUpdate == 'objects_data') {
						$objID = $CI->ExpCsvModel->getObjID($nodeID);
						if(!$objID) {
							throw new Exception('Объект не найден');
						}

						$tableKeyUpdate = str_replace($CI->ExpCsvModel->prefixDataFields, '', $tableKeyUpdate);
						$isUpdate = $CI->ObjectAdmModel->updateDataField($objID, $tableKeyUpdate, $tableValue);

					}

					# обновление таблицы объектов
					if($tableUpdate == 'objects') {
						if($tableKeyUpdate == 'obj_id') {
							throw new Exception('Нельзя обновлять ключевые поля');
						}

						$objID = $CI->ExpCsvModel->getObjID($nodeID);
						if(!$objID) {
							throw new Exception('Объект не найден');
						}

						$isUpdate = $CI->ObjectAdmModel->updateObject(array(str_replace('obj_', '', $tableKeyUpdate) => $tableValue), $objID);
					}

					# обновление таблицы структуры
					if($tableUpdate == 'tree') {

						if($tableKeyUpdate == 'tree_folow') {
							if($tableValue != 1 && $tableValue != 0) {
								throw new Exception('Неверное значение');
							}
						}

						if($tableKeyUpdate == 'tree_type') {
							if($tableValue != 'copy' && $tableValue != 'orig') {
								throw new Exception('Неверное значение');
							}
						}

						if($tableKeyUpdate == 'tree_id') {
							throw new Exception('Нельзя обновлять ключевые поля');
						}

						$CI->db->where('tree_id', $nodeID);
						$isUpdate = $CI->db->update('tree', array($tableKeyUpdate => $tableValue));
					}

					if(!$isUpdate) {
						throw new Exception('Ошибка обновления');
					}

					$this->ajaxResponse['status'] = 'OK';
					$this->ajaxResponse['info'] = 'Обновление успешно';
					$this->ajaxResponse['new_value'] = $tableValue;
				}
				catch (Exception $e)
				{
					$this->ajaxResponse['status'] = 'ERROR';
					$this->ajaxResponse['new_value'] = '';
					$this->ajaxResponse['info'] = $e->getMessage();
				}
				$this->_ajaxResponse();
				break;
			# старт импорта товара
			case 'runimport':
				try
				{
					$this->_ajaxImportObjects($post);
				} catch (Exception $e) {
					$this->ajaxResponse['status'] = 'ERROR';
					$this->ajaxResponse['flag_update'] = 'STOP';
					$this->ajaxResponse['info'] = $e->getMessage();
				}

				$this->_ajaxResponse();
				break;
			# старт выгрузки по типам объектов
			case 'exporttype':
				try
				{
					parse_str($post['form_values'], $post['form_values']);
					$this->_ajaxExportObjectsFromType($post);
				} catch (Exception $e) {
					$this->ajaxResponse['status'] = 'ERROR';
					$this->ajaxResponse['flag_update'] = 'STOP';
					$this->ajaxResponse['info'] = $e->getMessage();
				}
				$this->_ajaxResponse();
				break;
		}
	}

	# AJAX - экспорт объектов
	private function _ajaxExportObjectsFromType($post = array())
	{
		$CI = &get_instance();
		$dataTypes = array();
		$dataTypesID = array();
		$limitExport = $CI->ExpCsvModel->limitExport;
		$this->ajaxResponse['file_name'] = $post['file_name'];
		$this->ajaxResponse['info'] = '';
		$formParams = $post['form_values'];
		if(!isset($formParams['active_types'])) {
			throw new Exception("Выберите один или несколько типов объектов");
		}

		// смещение, которое пришло от javascript
		$sqlOffset = $post['sql_offset'];

		// запрошенные типы данных
		if(isset($formParams['active_data_types_fields']) && $formParams['active_data_types_fields']) {
			$dataTypes = $CI->CommonModel->getAllDataTypesFields(false, $formParams['active_data_types_fields']);
			$dataTypesID = array_keys($dataTypes);
		}

		$typeObjects = false;
		if($formParams['active_obj_type']) {
			$typeObjects = $formParams['active_obj_type'];
		}

		$CI->db->limit($limitExport, $sqlOffset);
		$OBJS = $CI->ExpCsvModel->getObjectsFromType($formParams['active_types'], $dataTypesID, $typeObjects); // объекты

		if(!$OBJS && $sqlOffset < 1) {
			throw new Exception('Нет объектов для выгрузки');
		}

		if($OBJS) {
			$this->ajaxResponse['status'] = 'OK';
			$this->ajaxResponse['flag_update'] = 'CONTINUE';
			$this->ajaxResponse['offset'] = $sqlOffset + $limitExport;
			$this->ajaxResponse['count_rows'] = $sqlOffset + count($OBJS);

			$this->ajaxResponse['info'] = 'Записано объектов: ' . $this->ajaxResponse['count_rows'];

			if(!$this->ajaxResponse['file_name']) {
				$this->ajaxResponse['file_name'] = 'expdata_' . time() . '.csv';
			}

			$filePath = $CI->ExpCsvModel->exportFilePath . $this->ajaxResponse['file_name'];

			# сформируем ключи. Массив состоит из разрешенных полей + запрошенные data-поля
			$head = array();
			$writeHeaders = false;
			$stream = false;

			// возможно файл уже есть, который надо дописать. Если он есть, то ключи в нем во 2-й строке
			if(file_exists($filePath)) {
				//$stream = new ReadFileLib($filePath, 'a+');
				$this->CSVLIB->init($filePath, 'a+');
				$this->CSVLIB->SetOffset(0);
				$result = array();
				$result = $this->CSVLIB->Read(2);
				$tmp = array();
				foreach($result as $value) {
					if(!$value) {
						throw new Exception("Empty file");
					}
					$tmp[] = str_getcsv($value, $CI->ExpCsvModel->csvDelimiterField, $CI->ExpCsvModel->csvEnclosure);
				}
				$head = array_combine($tmp[1], $tmp[0]);
			} else {
				//$stream = new ReadFileLib($filePath, 'w');
				$this->CSVLIB->init($filePath, 'w');
				$writeHeaders = true;
				// основные таблицы
				foreach($CI->ExpCsvModel->activeFieldsExport as $key => $value) {
					$head[$key] = $value['name'];
				}
				// data-поля
				if($dataTypesID) {
					foreach($dataTypes as $value) {
						$head[$CI->ExpCsvModel->prefixDataFields . $value['types_fields_id']] = $value['types_fields_name'];
					}
				}

				// в новом файле запишем заголовки
				$this->CSVLIB->write(array_values($head), $CI->ExpCsvModel->csvDelimiterField);
				$this->CSVLIB->write(array_keys($head), $CI->ExpCsvModel->csvDelimiterField);
			}

			// для записи объектов ставим курсор в конец файла
			$this->CSVLIB->SetOffset(0, true);

			foreach($OBJS as $objValue) {
				$tmp = array();
				foreach($head as $keyHead => $valHead) {
					if(isset($objValue[$keyHead])) {
						$tmp[$keyHead] = $objValue[$keyHead];
					} else {
						$tmp[$keyHead] = $CI->ExpCsvModel->noValue;
					}

				}

				$this->CSVLIB->write(array_values($tmp), $CI->ExpCsvModel->csvDelimiterField);
			}
		} else {
			$this->ajaxResponse['status'] = 'OK';
			$this->ajaxResponse['flag_update'] = 'STOP';
			$this->ajaxResponse['info'] = 'Файл успешно создан. <a href="' . str_replace(APP_BASE_PATH, APP_BASE_URL, $CI->ExpCsvModel->exportFilePath) . $this->ajaxResponse['file_name']. '" title="Скачать файл экспорта" download>Скачать »</a>';
		}
	}

	/*
	* AJAX - импорт/обновление объектов
	*
	*/
	private function _ajaxImportObjects($post = array())
	{
		$CI = &get_instance();
		$offset = 0;
		$getCountRows = false;

		if(isset($post['upd_offset'])) {
			$offset = $post['upd_offset'];
		}

		if(isset($post['get_count_rows'])) {
			$getCountRows = $post['get_count_rows'];
		}

		if(!isset($post['file'])) {
			throw new Exception("Не указан файл");
		}

		$path = base64_decode($post['file']);

		if(!file_exists($path)) {
			throw new Exception("Файл не найден");
		}

		# init файла
		$this->CSVLIB->init($path);

		# получим обновляемые ключи
		$arrayKeys = array();
		$this->CSVLIB->SetOffset(1);

		$resultRead = array();
		$resultRead = $this->CSVLIB->Read(1);

		if(!$resultRead) {
			throw new Exception('Некорректный файл');
		}

		$arrayKeys = str_getcsv($resultRead[0], $CI->ExpCsvModel->csvDelimiterField, $CI->ExpCsvModel->csvEnclosure);

		if(!$arrayKeys[0]) {
			throw new Exception('Некорректный файл: ключи не найдены');
		}

		// если true - то обновление, иначе insert
		$isUpdate = false;

		// если true - то обновление по data-полю
		$isUpdateData = false;

		if(in_array('obj_id', $arrayKeys) || in_array('tree_id', $arrayKeys)) {
			$isUpdate = true;
		} else if(!strpos($arrayKeys['0'], $CI->ExpCsvModel->prefixDataFields) !== false) {
			$isUpdateData = true;
		}

		# теперь читаем файл для обновления/добавления позиций. Пропустим 2 строки - там заголовки
		# если нет смещения по POST, то это первое чтение файла. Пропустим заголовки
		if(!$offset) {
			$offset = 2;
		}

		//$getCountRows = false;
		if($getCountRows) {
			$this->CSVLIB->SetOffset($offset);
			$cntRows = $this->CSVLIB->countRows();
			$this->ajaxResponse['status'] = 'OK';
			$this->ajaxResponse['count_rows'] = $cntRows;
			$this->ajaxResponse['info'] = 'Найдено строк в файле: ' . $this->ajaxResponse['count_rows'] . ' (+2)';
			$this->ajaxResponse['flag_update'] = 'CONTINUE';
			return true;
		}

		# читаем позиции в файле
		$limit = $CI->ExpCsvModel->limitExport;
		$this->CSVLIB->SetOffset($offset);
		$resultRead = $this->CSVLIB->Read($limit);

		// результатов нет, а смещение меньше 3-х - файл пуст
		if(isset($resultRead[0]) && !$resultRead[0] && $offset < 3) {
			throw new Exception('Файл не имеет позиций для обновления');
		} else if(!$resultRead) {
			// результата нет, а смещение есть - значить конец файла
			$this->ajaxResponse['status'] = 'OK';
			//$this->ajaxResponse['count_rows'] = $cntRows;
			$this->ajaxResponse['info'] = 'Обновление завершено';
			$this->ajaxResponse['flag_update'] = 'STOP';
			return true;
		}

		$fieldsAllows = array();
		foreach($CI->ExpCsvModel->activeFieldsExport as $key => $value) {
			if($key == 'tree_id' || $key == 'obj_id') {
				continue;
			}
			$fieldsAllows[$value['table']][] = $key;
		}

		if($isUpdate) {
			$updRes = $CI->ExpCsvModel->_csvReadFileUpdate($arrayKeys, $resultRead, $isUpdateData, $fieldsAllows);
		} else {
			$updRes = $CI->ExpCsvModel->_csvReadFileInsert($arrayKeys, $resultRead, $fieldsAllows);
		}

		if(!$updRes) {
			$this->ajaxResponse['status'] = 'OK';
			$this->ajaxResponse['flag_update'] = 'STOP';
			$this->ajaxResponse['offset'] = $offset + $limit;
			$this->ajaxResponse['info'] = 'Обновление завершено';
		} else {
			$totalUpd = $offset + $updRes - 2;
			$this->ajaxResponse['status'] = 'OK';
			$this->ajaxResponse['flag_update'] = 'CONTINUE';
			$this->ajaxResponse['offset'] = $offset + $limit;
			$this->ajaxResponse['info'] = 'Обновлено позиций: ' . $totalUpd;
		}


		return true;
	}

	/*
	* создание экспортной таблицы для row_id
	* разделитель для множества row_id - "+"
	*/
	public function export_node_id()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('EXPCSV_TITLE_ROW_ID');
		$CI->pageContentDescription = $CI->pageContentTitle;
		$data['objects'] = array();
		$data['exportFormAction'] = $_SERVER['REQUEST_URI'];
		try
		{
			if($nodes = $CI->input->get('node_id')) {
				$nodeArray = explode('-', $nodes);
				$data['objects'] = $CI->ExpCsvModel->exportNodeId($nodeArray);

			} else if($parentNode = $CI->input->get('parent_node_id')) {
				$data['objects'] = $CI->ExpCsvModel->getAllChilds($parentNode);
			} else {
				throw new Exception('Нет параметров');
			}

			if(!$data['objects']) {
				throw new Exception('Объекты не найдены');
			}

			// получем все id типов данных
			$dataTypesID = array();
			$i = 0;
			foreach($data['objects'] as $value) {
				$tmp = app_data_type_decode($value['obj_data_type']);
				foreach($tmp as $val) {
					$dataTypesID[$val] = $val;
				}
				++$i;
			}

			$data['countObjects'] = $i;

			// все поля типов данных
			$CI->CommonModel->getOnlyPublish = true;
			$dataTypesFields = $CI->CommonModel->getAllDataTypesFields($dataTypesID);

			// заголовки таблицы
			$data['tableHead'] = $CI->ExpCsvModel->activeFieldsExport;
			foreach($data['tableHead'] as $key => &$value) {
				$value = $value['name'];
			}
			unset($value);

			// добавим к заголовкам DATA-поля
			if($dataTypesFields) {
				foreach($dataTypesFields as $key => $value) {
					// если поле не подключено к типам данных, то пропускаем
					if(!$value['data_types']) {
						continue;
					}
					$data['tableHead'][$CI->ExpCsvModel->prefixDataFields . $key] = $value['types_fields_name'];
				}
			}

			##
			# запрос на создание файла
			##
			if($CI->input->post('run_export')) {
				$filePath = $CI->ExpCsvModel->exportFilePath . 'expnodes_' . time() . '.csv';
				$this->CSVLIB->init($filePath, 'w');
				$this->CSVLIB->createFileCSV($data['tableHead'], $data['objects']);

				$fileURl = str_replace(APP_BASE_PATH, APP_BASE_URL, $filePath);
				$this->ajaxResponse['status'] = 'OK';
				$this->ajaxResponse['info'] = 'Файл сформирован. <a href="' . $fileURl .'" title="Download file" download>Скачать файл »</a>';
				$this->_ajaxResponse();
			}

			$data['activeFields'] = $CI->ExpCsvModel->activeFieldsExport;
			$data['parefixDataField'] = $CI->ExpCsvModel->prefixDataFields;
			$data['novalueField'] = $CI->ExpCsvModel->noValue;

			$data['infoDelimiterField'] = $CI->ExpCsvModel->csvDelimiterField;
			$data['infoEnclosure'] = $CI->ExpCsvModel->csvEnclosure;

			$CI->pageContent = $CI->load->view('admin_page/export-node', $data, true);

		}
		catch (Exception $e)
		{
			$this->_error($e->getMessage());
		}
	}

	/*
	* импорт объектов
	*/
	public function import_objects()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('EXPCSV_TITLE_IMPORT');
		$CI->pageContentDescription = $CI->pageContentTitle;

		$data = array('h1' => $CI->pageContentTitle);

		$data['infoCharset'] = $this->csvCharset;
		$data['infoDelimiterField'] = $CI->ExpCsvModel->csvDelimiterField;
		$data['infoEnclosure'] = $CI->ExpCsvModel->csvEnclosure;
		$data['viewUploader'] = false;
		$data['import_info'] = '';

		$config['upload_path'] = $CI->ExpCsvModel->exportFilePath;
		$config['allowed_types'] = 'csv';
		$config['max_size']	= '8000';
		$CI->load->library('upload', $config);

		$pathFile = '';

		if($CI->upload->do_upload('file_import')) {
			$uploadData = $CI->upload->data();
			$pathFile = $uploadData['full_path'];
		}

		if($pf = $CI->input->get('file')) {
			$pf = base64_decode($pf);
			$pathInfo = pathinfo(APP_BASE_PATH . $pf);
			if($pathInfo['extension'] == 'csv') {
				$pathFile = $pathInfo['dirname'] . '/' . $pathInfo['basename'];
			}
		}

		if(!$pathFile) {
			$data['viewUploader'] = true;
			return $CI->pageContent = $CI->load->view('admin_page/import', $data, true);
		}

		try
		{

			$this->CSVLIB->init($pathFile);
			$data['countAllRows'] = $this->CSVLIB->countRows();
			// из общего числа отнимем две строки - заголовки и ключи
			if($data['countAllRows'] > 2) {
				$data['countAllRows'] = $data['countAllRows'] - 2;
			} else {
				throw new Exception('Позиции в файле не найдены');
			}

			$this->CSVLIB->SetOffset(0);
			$data['tableHeaders'] = $this->CSVLIB->getHeadersCSV();

			// если количество заголовков меньше 2-х, то файл некорректный
			if(count($data['tableHeaders']) < 2) {
				throw new Exception('Ошибка! Некорректный файл. Проверьте разделители полей');
			}

			$data['readResult'] = $this->CSVLIB->Read($this->limitPreview);
			$tmp = array();
			$headersCount = count($data['tableHeaders']);
			$arrEgtCsv = array();
			foreach($data['readResult'] as $value) {
				if(!$value) {
					continue;
				}

				$arrEgtCsv = str_getcsv($value, $CI->ExpCsvModel->csvDelimiterField, $CI->ExpCsvModel->csvEnclosure);

				if($headersCount != count($arrEgtCsv)) {
					throw new Exception('В файле направильно расставлены переносы строк!');
				}

				$tmp[] = $arrEgtCsv;

			}

			if(!$tmp) {
				throw new Exception('Позиции в файле не найдены');
			}

			$data['viewUploader'] = false;
			$data['readResult'] = $tmp;
			unset($tmp);

			$data['pathFile'] = base64_encode($pathFile);
			$data['limitPreview'] = $this->limitPreview;

			if(isset($data['tableHeaders']['obj_id']) || isset($data['tableHeaders']['tree_id'])) {
				$data['import_info'] = 'Для объектов будет выполнено обновление';
			} else {
				$data['import_info'] = 'Внимание! Будут созданы новые объекты';
			}

			$CI->pageContent = $CI->load->view('admin_page/import', $data, true);
		}
		catch(Exception $e)
		{
			$this->_error($e->getMessage());
		}
	}

	/*
	* выгрузка объектов по типам объектов
	*/
	public function export_object_types()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('EXPCSV_TITLE_EXPORT_TYPE_OBJECTS');
		$CI->pageContentDescription = $CI->pageContentTitle;

		$data = array();

		# разрешенные поля для выгрузки
		foreach($CI->ExpCsvModel->activeFieldsExport as $key => $value) {
			$fieldsObjectExport[$key] = $value['name'];
		}

		$data['allTypesObjects'] = $CI->CommonModel->getAllObjTypes(); // все типы объектов

		foreach($data['allTypesObjects'] as $key => &$value)
			$value = $value['obj_types_name'];
		unset($value);

		# все поля типов данных
		$data['allDataFields'] = $CI->CommonModel->getAllDataTypesFields(); // все DATA-поля для объектов
		foreach($data['allDataFields'] as $key => &$value) {
			$value = $value['types_fields_name'];
		}
		unset($value);

		# типы объектов в структуре сайта
		$data['allObjTypes'] = array(0 => 'Все типы', 'orig' => 'Оригиналы', 'copy' => 'Копии');

		$data['infoDelimiterField'] = $CI->ExpCsvModel->csvDelimiterField;
		$data['infoEnclosure'] = $CI->ExpCsvModel->csvEnclosure;
		$data['exportLimit'] = $CI->ExpCsvModel->limitExport;

		$CI->pageContent = $CI->load->view('admin_page/export_type_object', $data, true);
	}

	/*
	* Парсер файла csv
	*/
	public function parse_file()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('EXPCSV_TITLE_PARSER');
		$CI->pageContentDescription = $CI->pageContentTitle;
		$data = array('h1' => $CI->pageContentTitle);
		$data['viewUploader'] = true;
		$data['infoCharset'] = $this->csvCharset;
		$data['infoDelimiterField'] = $CI->ExpCsvModel->csvDelimiterField;
		$data['infoEnclosure'] = $CI->ExpCsvModel->csvEnclosure;
		$data['fileDownload'] = '';

		$config['upload_path'] = $CI->ExpCsvModel->exportFilePath;
		$config['allowed_types'] = 'csv';
		$config['max_size']	= '8000';

		$CI->load->library('upload', $config);
		$pathFile = '';
		if($CI->upload->do_upload('file_import')) {
			$data['viewUploader'] = false;
			$uploadData = $CI->upload->data();
			$pathFile = $uploadData['full_path'];
			$fileContent = file_get_contents($pathFile);
			// уберем экранирование пока, потом вернем
			$fileContent = str_replace('""', '__ECR__', $fileContent);

			$pattern = '/("[^"]*")|[^"]*/i';
			$res = preg_replace_callback($pattern, "expcsv_delete_rn", $fileContent);

			// вернем экранирование
			$res = str_replace('__ECR__', '""', $res);

			if($res) {
				$data['fileDownload'] = $fileOut = $CI->ExpCsvModel->exportFilePath . 'parse_out.csv';
				file_put_contents($fileOut, $res);
				$data['fileDownload'] = str_replace(APP_BASE_PATH, APP_BASE_URL, $data['fileDownload']);
				$CI->pageCompliteMessage = app_lang('EXPCSV_INFO_FILE_PARSE_COMPLITE');
			}
		}

		$CI->pageContent = $CI->load->view('admin_page/parse_file', $data, true);
	}

	/*
	* Ошибка
	*/
	private function _error($text = 'Error')
	{
		$CI = &get_instance();
		$CI->pageErrorMessage = $text;
		$CI->pageContent = $CI->load->view('admin_page/units/menu', array(), true);;
	}

	# вывод ajax
	private function _ajaxResponse()
	{
		echo json_encode($this->ajaxResponse);
		exit();
	}
}
