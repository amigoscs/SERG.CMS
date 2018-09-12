<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  exp-csv Admin_plugin Lib
	*
	* version 3.2
	* UPD 2017-11-03
	*
	* version 3.3
	* UPD 2017-12-13
	*
	* version 3.4
	* UPD 2018-03-13
	* правки по полям выгрузки
	*
	* version 3.5
	* UPD 2018-04-18
	* правки полей выгрузки и обновления
	*
	* version 3.6
	* UPD 2018-05-08
	* Исправление ошибки при выгрузке большого количества объектов
	*
	* version 4.0
	* UPD 2018-06-25
	* Добавлена выгрузка вложенных объектов
	*
	* version 4.1
	* UPD 2018-07-27
	* Возможность указать путь до файла обновления через GET параметр

*/

class Admin_plugin {

	// разделитель полей
	public $csvDelimiterField;

	// разделитель строк
	public $csvDelimiterRows;

	// разделитель строк (визуальное представление)
	public $csvDelimiterRowsTextInfo;

	// кодировка файла
	public $csvCharset;

	// лимит превью
	public $limitPreview;

	public function __construct()
	{
		$CI = &get_instance();
		$this->csvCharset = 'utf-8';
		$this->csvDelimiterField = $CI->ExpCsvModel->csvDelimiterField;
		$this->csvDelimiterRows = app_get_option("csv_fields_rows", "exp-csv", "");
		$this->limitPreview = app_get_option("csv_count_prev", "exp-csv", "20");
		if(!$this->csvDelimiterRows) {
			$this->csvDelimiterRows = "\r\n";
			$this->csvDelimiterRowsTextInfo = '¶';
		} else {
			$this->csvDelimiterRowsTextInfo = $this->csvDelimiterRows;
		}
	}

	public function __call($method, $par)
	{
		return $this->index();
	}


	public function index()
	{
		$CI = &get_instance();
		$CI->data['PAGE_TITLE'] = app_lang('EXPCSV_TITLE_INDEX');
		$CI->data['PAGE_DESCRIPTION'] = $CI->data['PAGE_TITLE'];
		$CI->data['PAGE_KEYWORDS'] = $CI->data['PAGE_TITLE'];
		$data = array('h1' => app_lang('EXPCSV_TITLE_INDEX'));
		return $CI->load->view('admin_page/index', $data, true);
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
				$isUpdate = FALSE;
				parse_str($post['field_name'] . '=' . $post['field_value'], $fieldParam);
				# обновление data-параметра
				if(isset($fieldParam['data'])) {
					foreach($fieldParam['data'] as $objID => $value)
					{
						foreach($value as $fieldID => $fieldVal) {
							$isUpdate = $CI->ObjectAdmModel->updateDataField($objID, $fieldID, $fieldVal);
						}
					}
				}

				# обновление таблицы объектов
				if(isset($fieldParam['objects']))
				{
					$fieldsAvailable = explode(',', app_get_option('fields_edit', 'exp-csv', 'obj_anons, obj_content'));
					$fieldsAvailable = array_map("trim", $fieldsAvailable);
					$aliasArray = array(
							'obj_data_type' => 'data_type',
							'obj_canonical' => 'canonical',
							'obj_name' => 'name',
							'obj_h1' => 'h1',
							'obj_title' => 'title',
							'obj_description' => 'description',
							'obj_keywords' => 'keywords',
							'obj_anons' => 'anons',
							'obj_content' => 'content',
							'obj_date_publish' => 'date_publish',
							'obj_cnt_views' => 'cnt_views',
							'obj_rating_up' => 'rating_up',
							'obj_rating_down' => 'rating_down',
							'obj_rating_count' => 'rating_count',
							'obj_user_author' => 'user_author',
							'obj_status' => 'status',
							'obj_ugroups_access' => 'ugroups_access',
							'obj_tpl_content' => 'tpl_content',
							'obj_tpl_page' => 'tpl_page'
					);

					foreach($fieldParam['objects'] as $objID => $value)
					{
						foreach($value as $fieldName => $fieldVal) {
							# пробежимся по разрешенным полям и получим их алиасы
							if(in_array($fieldName, $fieldsAvailable)) {
								# если поле есть в списке алиасов
								if(isset($aliasArray[$fieldName]))
									$fieldName = $aliasArray[$fieldName];
							} else {
								continue;
							}
							$isUpdate = $CI->ObjectAdmModel->updateObject(array($fieldName => $fieldVal), $objID);
						}
					}
				}

				if($isUpdate) {
					$report['status'] = 'complite';
					$report['text'] = 'Обновление успешно';
				}else{
					# обновление таблицы структуры сайта
					if(isset($fieldParam['tree'])) {
						$report['text'] = 'Обновлять поля структуры сайта запрещено!';
					} else {
						$report['text'] = 'Ошибка обновления';
					}
				}
				break;
			# старт импорта товара
			case 'runimport':
				$path = base64_decode($post['file']);
				$res = $CI->ExpCsvModel->csvReadFile($path);
				if($res !== FALSE) {
					$report['status'] = 'complite';
					$report['text'] = 'Обновление выполнено успешно. Обновлено - ' . $res;
				} else {
					$report['text'] = 'Ошибка обновления';
				}
				break;
		}
		echo json_encode($report);
	}

	/*
	* создание экспортной таблицы для row_id
	* разделитель для множества row_id - "+"
	*/
	public function export_row_id()
	{
		$CI = &get_instance();
		$CI->data['PAGE_TITLE'] = app_lang('EXPCSV_TITLE_ROW_ID');
		$CI->data['PAGE_DESCRIPTION'] = $CI->data['PAGE_TITLE'];
		$CI->data['PAGE_KEYWORDS'] = $CI->data['PAGE_TITLE'];

		$id = $CI->uri->segment(4, 0);

		if(!$id) {
			return $this->_error(app_lang('EXPCSV_INFO_ERROR'), app_lang('EXPCSV_INFO_ERROR_NO_ID'));
		}

		$idArray = explode('+', $id);
		$data = array('obj_fields' => array(), 'fields' => array());

		# выгружаемые объекты
		$objects = array();
		$objects = $CI->ExpCsvModel->exportRowId($idArray);
		if(!$objects) {
			return $this->_error(app_lang('EXPCSV_INFO_ERROR'), app_lang('EXPCSV_INFO_ERROR_NO_OBJECTS'));
		}

		# получим типы данных для объектов
		// по умолчанию - ID = 1
		$dataTypesID = array(1 => 1);
		$dataTypes = array();
		foreach($objects as $value) {
			$dataTypesID[$value['obj_data_type']] = $value['obj_data_type'];
		}

		// поля для типов данных
		$CI->CommonModel->reset();
		$CI->CommonModel->getOnlyPublish = TRUE;
		$dataTypes = $CI->CommonModel->getAllDataTypesFields($dataTypesID);

		# все активные поля
		$fields = $CI->ExpCsvModel->activeFieldsExport;
		foreach($dataTypes as $key => $value) {
			$fields[$CI->ExpCsvModel->prefixDataFields . $key] = array('table' => 'objects_data', 'name' => $value['types_fields_name']);
		}

		$CI->CommonModel->reset();
		$noValue = $CI->ExpCsvModel->noValue;
		# старт выгрузки
		if($csvOpt = $CI->input->post('run_export'))
		{
			$fieldValue = '';
			$rowFirst = '';
			$rowSecond = '';

			# формируем заголовки
			foreach($fields as $key => $value) {
				$rowFirst .= '"' . trim($value['name']) . '"' . $this->csvDelimiterField;
				$rowSecond .= '"' . trim($key) . '"' . $this->csvDelimiterField;
			}

			$rowFirst = rtrim($rowFirst, $this->csvDelimiterField);
			$rowSecond = rtrim($rowSecond, $this->csvDelimiterField);
			$fileName = app_translate(app_get_option('site_name', 'site', 'export') . '_' . time());
			if(!$fileName) {
				$fileName = 'no_name';
			}

			header("Content-type: application/download; charset=" . $this->csvCharset);
			header("Content-Disposition: attachment; filename*={$this->csvCharset}''{$fileName}.csv");
			header("Pragma: no-cache");
			header("Expires: 0");

			echo $rowFirst . $this->csvDelimiterRows;
			echo $rowSecond . $this->csvDelimiterRows;

			# вывод объектов
			foreach($objects as $Obj)
			{
				$objRow = '';
				foreach($fields as $key => $value)
				{
					if(isset($Obj[$key])) {
						$fieldValue = trim($Obj[$key]);
						$fieldValue = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $fieldValue);
						$fieldValue = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), ' ', $fieldValue);
						$objRow .= '"' . $fieldValue . '"' . $this->csvDelimiterField;
					} else {
						$objRow .= $noValue . $this->csvDelimiterField;
					}
				}
				$objRow = rtrim($objRow, $this->csvDelimiterField);
				echo $objRow . $this->csvDelimiterRows;
			}
			die();
		}
		else
		{
			$data['objects'] = $objects; // объекты
			$data['fields'] = $fields;
			$data['noValue'] = $noValue;

			# массив ключей, доступных для редактирования
			$keysToEdit = explode(',', app_get_option('fields_edit', 'exp-csv', 'tree_order, obj_anons, obj_content'));
			$keysToEdit = array_map("trim", $keysToEdit);
			$data['keysToEdit'] = $keysToEdit;

			$data['exportInfo'] = app_lang('EXPCSV_INFO_DEL_FIELDS') . ' - «' . $this->csvDelimiterField . '». ';
			$data['exportInfo'] .= app_lang('EXPCSV_INFO_DEL_ROWS') . ' - «' . $this->csvDelimiterRowsTextInfo . '». ';
			$data['exportInfo'] .= '<a href="/admin/setting_plugin/exp-csv">' . app_lang('EXPCSV_INFO_CHANGE_SETTING') . ' »</a>.';

			return $CI->load->view('admin_page/export-row', $data, true);
		}
	}

	/*
	* импорт объектов
	*/
	public function import_objects()
	{
		$CI = &get_instance();
		$CI->data['PAGE_TITLE'] = app_lang('EXPCSV_TITLE_IMPORT');
		$CI->data['PAGE_DESCRIPTION'] = $CI->data['PAGE_TITLE'];
		$CI->data['PAGE_KEYWORDS'] = $CI->data['PAGE_TITLE'];

		$data = array('h1' => $CI->data['PAGE_TITLE']);
		$data['importInfo'] = app_lang('EXPCSV_INFO_MAX_FILE_SIZE') . ' 8 Mb. ' . app_lang('EXPCSV_INFO_FILE_ENCODING') . ' - «' . $this->csvCharset . '». <br />';
		$data['importInfo'] .= app_lang('EXPCSV_INFO_DEL_FIELDS') . ' - «' . $this->csvDelimiterField . '». ';
		$data['importInfo'] .= app_lang('EXPCSV_INFO_DEL_ROWS') . ' - «' . $this->csvDelimiterRowsTextInfo . '». ';
		$data['importInfo'] .= '<a href="/admin/setting_plugin/exp-csv">' . app_lang('EXPCSV_INFO_CHANGE_SETTING') . '</a>';

		$data['viewUploader'] = true;

		$config['upload_path'] = './uploads/tmp/';
		$config['allowed_types'] = 'csv';
		$config['max_size']	= '8000';
		$CI->load->library('upload', $config);
		$data['read_file'] = '';
		$data['button_start'] = '';
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

		if($pathFile) {
			$data['viewUploader'] = false;
			$data['read_file'] = '<div class="form-group">';
			$data['read_file'] .= $CI->ExpCsvModel->csvReadFileToTable($pathFile, $this->limitPreview);
			$data['read_file'] .= '</div>';
			$data['button_start'] = '<div class="form-group">';
			$data['button_start'] .= '<button id="run_import" data-file-path="'.base64_encode($pathFile).'" class="btn btn-primary">'.app_lang('EXPCSV_BUTTON_RUN_IMPORT').'</button>';
			$data['button_start'] .= '</div>';
		}

		return $CI->load->view('admin_page/import', $data, true);
	}

	/*
	* выгрузка объектов по типам данных
	*/
	public function export_object_datatypes()
	{
		$CI = &get_instance();
		$data = array();
		$CI->data['PAGE_TITLE'] = app_lang('EXPCSV_TITLE_EXPORT_DATA_TYPES');
		$CI->data['PAGE_DESCRIPTION'] = $CI->data['PAGE_TITLE'];
		$CI->data['PAGE_KEYWORDS'] = $CI->data['PAGE_TITLE'];
		$data['h1'] = $CI->data['PAGE_TITLE'];

		$allTypesData = $CI->CommonModel->getAllDataTypesFull(); // все типы данных
		$data['allTypesDataDropdown'] = array();

		foreach($allTypesData as $key => $value) {
			$data['allTypesDataDropdown'][$key] = $value['data_types_name'];
		}

		# разрешенные поля для выгрузки
		foreach($CI->ExpCsvModel->activeFieldsExport as $key => $value) {
			$fieldsObjectExport[$key] = $value['name'];
		}

		if($typeDataID = $CI->input->post('active_type'))
		{
			$fileFirstRow = $fileSecondRow = '';
			$allObjects = $CI->ExpCsvModel->getObjectsDataType($typeDataID);

			# формируем файл
			$fileName = 'data_type_' . time();

			header("Content-type: application/download; charset=" . $CI->ExpCsvModel->csvCharset);
			header("Content-Disposition: attachment; filename*={$CI->ExpCsvModel->csvCharset}''{$fileName}.csv");
			header("Pragma: no-cache");
			header("Expires: 0");

			# заголовки файла
			// основной таблицы
			foreach($fieldsObjectExport as $key => $value) {
				$fileFirstRow .= '"' . $value . '"' . $CI->ExpCsvModel->csvDelimiterField;
				$fileSecondRow .= '"' . $key . '"' . $CI->ExpCsvModel->csvDelimiterField;
			}
			// таблицы data полей
			foreach($allTypesData[$typeDataID]['fields'] as $key => $value) {
				$fileFirstRow .= '"' . $value['types_fields_name'] . '"' . $CI->ExpCsvModel->csvDelimiterField;
				$fileSecondRow .= '"' . $CI->ExpCsvModel->prefixDataFields . $key . '"' . $CI->ExpCsvModel->csvDelimiterField;
			}

			$fileFirstRow = rtrim($fileFirstRow, $CI->ExpCsvModel->csvDelimiterField);
			$fileSecondRow = rtrim($fileSecondRow, $CI->ExpCsvModel->csvDelimiterField);

			echo $fileFirstRow . $CI->ExpCsvModel->csvDelimiterRows;
			echo $fileSecondRow . $CI->ExpCsvModel->csvDelimiterRows;

			foreach($allObjects as $objValue)
			{
				$tmp = array();
				// поля таблицы objects
				foreach($fieldsObjectExport as $key => $value)
				{
					if(isset($objValue[$key])) {
						$fieldValue = $objValue[$key];
						$fieldValue = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $fieldValue);
						$fieldValue = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    ', '&nbsp;'), ' ', $fieldValue);
						$fieldValue = str_replace('"', '""', $fieldValue);
					} else {
						$fieldValue = $CI->ExpCsvModel->noValue;
					}
					$tmp[] = $fieldValue;
				}
				// поля data полей
				foreach($allTypesData[$typeDataID]['fields'] as $key => $value)
				{
					if(isset($objValue[$CI->ExpCsvModel->prefixDataFields . $key])) {
						$fieldValue = $objValue[$CI->ExpCsvModel->prefixDataFields . $key];
						$fieldValue = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $fieldValue);
						$fieldValue = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    ', '&nbsp;'), ' ', $fieldValue);
						$fieldValue = str_replace('"', '""', $fieldValue);
					} else {
						$fieldValue = $CI->ExpCsvModel->noValue;
					}
					$tmp[] = $fieldValue;
				}

				echo '"' . implode('"' . $CI->ExpCsvModel->csvDelimiterField . '"', $tmp) . '"' . $CI->ExpCsvModel->csvDelimiterRows;
			}
			exit();
		}

		$data['exportInfo'] = app_lang('EXPCSV_INFO_DEL_FIELDS') . ' - «' . $this->csvDelimiterField . '». ';
		$data['exportInfo'] .= app_lang('EXPCSV_INFO_DEL_ROWS') . ' - «' . $this->csvDelimiterRowsTextInfo . '». ';
		$data['exportInfo'] .= '<a href="/admin/setting_plugin/exp-csv">' . app_lang('EXPCSV_INFO_CHANGE_SETTING') . ' ».</a>';
		return $CI->load->view('admin_page/export_type_data', $data, true);
	}

	/*
	* выгрузка объектов по типам объектов
	*/
	public function export_object_types()
	{
		$CI = &get_instance();
		$data = array();
		$CI->data['PAGE_TITLE'] = app_lang('EXPCSV_TITLE_EXPORT_TYPE_OBJECTS');
		$CI->data['PAGE_DESCRIPTION'] = $CI->data['PAGE_TITLE'];
		$CI->data['PAGE_KEYWORDS'] = $CI->data['PAGE_TITLE'];

		# разрешенные поля для выгрузки
		foreach($CI->ExpCsvModel->activeFieldsExport as $key => $value) {
			$fieldsObjectExport[$key] = $value['name'];
		}

		if($typeObjects = $CI->input->post('active_types'))
		{
			$fileFirstRow = $fileSecondRow = '';
			$dataTypes = $CI->input->post('active_data_types');
			$allDataTypes = array();

			// запрошенные типы данных
			if($dataTypes) {
				$allDataTypes = $CI->CommonModel->getAllDataTypesFields(FALSE, $dataTypes);
			}

			$OBJS = $CI->ExpCsvModel->getObjectsFromType($typeObjects, $dataTypes); // объекты

			$fileName = 'type_objects_' . time();

			header("Content-type: application/download; charset=" . $CI->ExpCsvModel->csvCharset);
			header("Content-Disposition: attachment; filename*={$CI->ExpCsvModel->csvCharset}''{$fileName}.csv");
			header("Pragma: no-cache");
			header("Expires: 0");

			# заголовки файла
			// основной таблицы
			foreach($fieldsObjectExport as $key => $value) {
				$fileFirstRow .= '"' . $value . '"' . $CI->ExpCsvModel->csvDelimiterField;
				$fileSecondRow .= '"' . $key . '"' . $CI->ExpCsvModel->csvDelimiterField;
			}

			// таблицы data полей
			foreach($allDataTypes as $key => $value) {
				$fileFirstRow .= '"' . $value['types_fields_name'] . '"' . $CI->ExpCsvModel->csvDelimiterField;
				$fileSecondRow .= '"' . $CI->ExpCsvModel->prefixDataFields . $key . '"' . $CI->ExpCsvModel->csvDelimiterField;
			}

			$fileFirstRow = rtrim($fileFirstRow, $CI->ExpCsvModel->csvDelimiterField);
			$fileSecondRow = rtrim($fileSecondRow, $CI->ExpCsvModel->csvDelimiterField);

			echo $fileFirstRow . $CI->ExpCsvModel->csvDelimiterRows;
			echo $fileSecondRow . $CI->ExpCsvModel->csvDelimiterRows;

			foreach($OBJS as $objValue)
			{
				$tmp = array();
				// поля таблицы objects
				foreach($fieldsObjectExport as $key => $value){
					if(isset($objValue[$key])) {
						$fieldValue = $objValue[$key];
						$fieldValue = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $fieldValue);
						$fieldValue = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    ', '&nbsp;'), ' ', $fieldValue);
						$fieldValue = str_replace('"', '""', $fieldValue);
					} else {
						$fieldValue = $CI->ExpCsvModel->noValue;
					}
					$tmp[] = $fieldValue;
				}
				// поля data полей
				foreach($allDataTypes as $key => $value) {
					if(isset($objValue[$CI->ExpCsvModel->prefixDataFields . $key])) {
						$fieldValue = $objValue[$CI->ExpCsvModel->prefixDataFields . $key];
						$fieldValue = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $fieldValue);
						$fieldValue = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    ', '&nbsp;'), ' ', $fieldValue);
						$fieldValue = str_replace('"', '""', $fieldValue);
					} else {
						$fieldValue = $CI->ExpCsvModel->noValue;
					}
					$tmp[] = $fieldValue;
				}
				echo '"' . implode('"' . $CI->ExpCsvModel->csvDelimiterField . '"', $tmp) . '"' . $CI->ExpCsvModel->csvDelimiterRows;
			}
			exit();
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

		$data['exportInfo'] = app_lang('EXPCSV_INFO_DEL_FIELDS') . ' - «' . $this->csvDelimiterField . '». ';
		$data['exportInfo'] .= app_lang('EXPCSV_INFO_DEL_ROWS') . ' - «' . $this->csvDelimiterRowsTextInfo . '». ';
		$data['exportInfo'] .= '<a href="/admin/setting_plugin/exp-csv">' . app_lang('EXPCSV_INFO_CHANGE_SETTING') . ' ».</a>';
		return $CI->load->view('admin_page/export_type_object', $data, true);
	}

	/*
	* выгрузка всех вложенных объектов
	*/
	public function export_childs_objects()
	{
		$CI = &get_instance();
		$data = array(
			'parentObject' => 0,
			'dataTypesFields' => array(),
			'exportObjects' => array(),
			'objectsFields' => array(),
			'exportInfo' => ''
		);
		$CI->data['PAGE_TITLE'] = app_lang('EXPCSV_TITLE_EXPORT_TYPE_OBJECTS');
		$CI->data['PAGE_DESCRIPTION'] = $CI->data['PAGE_TITLE'];
		$CI->data['PAGE_KEYWORDS'] = $CI->data['PAGE_TITLE'];

		if($parentObject = $CI->input->get('node_id')) {
			$data['parentObject'] = $parentObject;
		}

		if($data['parentObject']) {
			$data['exportObjects'] = $CI->TreelibAdmModel->loadAllChildsTree($data['parentObject'], array(), 0, FALSE, true);
			$data['noValue'] = $CI->ExpCsvModel->noValue;
			if($data['exportObjects']) {
				$data['objectsFields'] = $CI->ExpCsvModel->activeFieldsExport;

				# достанем все типы данных. Тип данных ПО-УМОЛЧАНИЮ тоже включаем
				$objDataTypes = array(1 => 1);
				foreach($data['exportObjects'] as $value) {
					foreach($value as $val) {
						$objDataTypes[$val['obj_data_type']] = $val['obj_data_type'];
					}
				}

				$data['dataTypesFields'] = $CI->CommonModel->getAllDataTypesFields($objDataTypes, array());
				$data['exportInfo'] .= '<p style="color: #b20;font-weight: 600;">Краткий обзор. Показано объектов: ' . app_get_option("csv_count_prev", "exp-csv", "10") . '</p>';
			}

			# заявка на формироване файла выгрузки
			if($CI->input->post('create_file')) {
				$fileName = 'export_childs_' . $data['parentObject'] . '_' . time();

				header("Content-type: application/download; charset=" . $this->csvCharset);
				header("Content-Disposition: attachment; filename*={$this->csvCharset}''{$fileName}.csv");
				header("Pragma: no-cache");
				header("Expires: 0");

				$outHeader = $outKeys = '';
				// сформирекм заголовки. Сначала основные таблицы
				foreach($data['objectsFields'] as $key => $value) {
					$outHeader .= $value['name'] . $this->csvDelimiterField;
					$outKeys .= $key . $this->csvDelimiterField;
				}
				// потом DATA-поля
				foreach($data['dataTypesFields'] as $value) {
					$outHeader .= $value['types_fields_name'] . $this->csvDelimiterField;
					$outKeys .= $CI->ExpCsvModel->prefixDataFields . $value['types_fields_id'] . $this->csvDelimiterField;
				}

				echo rtrim($outHeader, $this->csvDelimiterField) . $this->csvDelimiterRows;
				echo rtrim($outKeys, $this->csvDelimiterField) . $this->csvDelimiterRows;

				# дальше пишем сами объекты
				foreach($data['exportObjects'] as $value) {
					foreach($value as $object) {
						$rowObject = '';
						foreach($data['objectsFields'] as $key => $field) {
							$fieldValue = trim($object[$key]);
							$fieldValue = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $fieldValue);
							$fieldValue = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), ' ', $fieldValue);
							$rowObject .= '"' . $fieldValue . '"' . $this->csvDelimiterField;
						}
						foreach($data['dataTypesFields'] as $field) {
							if(isset($object['data_fields'][$field['types_fields_id']])) {
								$rowObject .= '"' . $object['data_fields'][$field['types_fields_id']]['objects_data_value'] . '"' . $this->csvDelimiterField;
							} else {
								$rowObject .= $data['noValue'] . $this->csvDelimiterField;
							}
						}
						echo rtrim($rowObject, $this->csvDelimiterField) . $this->csvDelimiterRows;
					}
				}
				exit();
			}
		}

		$data['exportInfo'] .= '<p>' . app_lang('EXPCSV_INFO_DEL_FIELDS') . ' - «' . $this->csvDelimiterField . '». ';
		$data['exportInfo'] .= app_lang('EXPCSV_INFO_DEL_ROWS') . ' - «' . $this->csvDelimiterRowsTextInfo . '». ';
		$data['exportInfo'] .= '<a href="/admin/setting_plugin/exp-csv">' . app_lang('EXPCSV_INFO_CHANGE_SETTING') . ' ».</a></p>';

		return $CI->load->view('admin_page/export_childs_objects', $data, true);
	}

	/*
	* Ошибка
	*/
	private function _error($title = 'Error', $text = 'Error')
	{
		$CI = &get_instance();
		$CI->data['infoerror'] = $text;
		return '';
	}
}
