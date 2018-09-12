<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* ExpCsvModel
	* version 1.2
	* UPD 2017-11-03
	*
	* version 1.3
	* UPD 2017-12-13
	* добавлена возможность писать свой url присваивать несколько родителей, мелкие прочие правки
	*
	* version 2.0
	* UPD 2018-01-15
	* совместимость с системой 5.0
	*
	* version 2.1
	* UPD 2018-01-30
	* правки обновления
	*
	* version 2.2
	* UPD 2018-04-19
	* мелкие правки кода
	*
	* version 2.3
	* UPD 2018-05-08
	* Исправление ошибки при выгрузке большого количества объектов
	*
	* version 3.0
	* UPD 2018-08-01
	* Совменстимость с версией 6.0. tree_axis и obj_link
*/

class ExpCsvModel extends CI_Model {

	// разделитель полей
	public $csvDelimiterField;

	// разделитель строк
	public $csvDelimiterRows;

	// кодировка файла
	public $csvCharset;

	// активные поля
	public $activeFieldsExport;
	public $keyField;

	// разделитель столбцов
	# УСТАРЕЛО - УДАЛИТЬ
	public $delimiter;

	// подпись вместо отсутствующего значения
	public $noValue;

	// префикс для Data-полей
	public $prefixDataFields;

	public function __construct()
    {
        parent::__construct();
		$this->csvCharset = 'utf-8';
		$this->csvDelimiterField = app_get_option("csv_fields_delimiter", "exp-csv", ";");
		$this->csvDelimiterRows = app_get_option("csv_fields_rows", "exp-csv", "");
		if(!$this->csvDelimiterRows) {
			$this->csvDelimiterRows = "\r\n";
		}

		$this->prefixDataFields = 'isdata_';

		$this->report = array('status' => '400');
		$this->keyField = '';
		$this->delimiter = ';';
		$this->noValue = '_NOTSAVE_';
		$this->activeFieldsExport = array(
				'tree_id' 				=> array('table' => 'tree', 'name' => 'ID строки'),
				'tree_parent_id' 		=> array('table' => 'tree', 'name' => 'ID родительской строки'),
				'tree_url' 				=> array('table' => 'tree', 'name' => 'Ссылка'),
				'tree_short'			=> array('table' => 'tree', 'name' => 'Короткая ссылка'),
				'tree_folow'			=> array('table' => 'tree', 'name' => 'Индексация'),
				'tree_date_create' 		=> array('table' => 'tree', 'name' => 'Дата создания'),
				'tree_order' 			=> array('table' => 'tree', 'name' => 'Порядок'),
				'tree_type' 			=> array('table' => 'tree', 'name' => 'Копия/оригинал'),
				'tree_type_object' 		=> array('table' => 'tree', 'name' => 'Тип объекта'),
				'tree_axis' 			=> array('table' => 'tree', 'name' => 'Местоположение'),

				'obj_id' 				=> array('table' => 'objects', 'name' => 'ID объекта'),
				'obj_data_type' 		=> array('table' => 'objects', 'name' => 'Тип дата полей'),
				'obj_canonical' 		=> array('table' => 'objects', 'name' => 'Canonical'),
				'obj_name' 				=> array('table' => 'objects', 'name' => 'Название'),
				'obj_h1' 				=> array('table' => 'objects', 'name' => 'Заголовок H1'),
				'obj_title' 			=> array('table' => 'objects', 'name' => 'Title страницы'),
				'obj_description' 		=> array('table' => 'objects', 'name' => 'Description страницы'),
				'obj_keywords' 			=> array('table' => 'objects', 'name' => 'Keywords страницы'),
				'obj_anons' 			=> array('table' => 'objects', 'name' => 'Анонс'),
				'obj_content' 			=> array('table' => 'objects', 'name' => 'Контент'),
				'obj_date_create' 		=> array('table' => 'objects', 'name' => 'Дата создания'),
				'obj_date_publish' 		=> array('table' => 'objects', 'name' => 'Дата публикации'),
				'obj_cnt_views' 		=> array('table' => 'objects', 'name' => 'Просмотры'),
				'obj_rating_up' 		=> array('table' => 'objects', 'name' => 'Рейтинг +'),
				'obj_rating_down' 		=> array('table' => 'objects', 'name' => 'Рейтинг -'),
				'obj_rating_count' 		=> array('table' => 'objects', 'name' => 'Рейтинг участники'),
				'obj_user_author' 		=> array('table' => 'objects', 'name' => 'ID автора'),
				'obj_ugroups_access' 	=> array('table' => 'objects', 'name' => 'Доступ'),
				'obj_tpl_content' 		=> array('table' => 'objects', 'name' => 'Шаблон контента'),
				'obj_tpl_page' 			=> array('table' => 'objects', 'name' => 'Шаблон страницы'),
				'obj_link' 			=> array('table' => 'objects', 'name' => 'Связи'),
				'obj_status' 			=> array('table' => 'objects', 'name' => 'Статус страницы'),
			);
    }

	/*
	* загрузка всех дата-полей
	*/
	public function loadDataFields()
	{
		# data поля
		$dataFields = array();
		$this->db->select('types_fields_id, types_fields_name, objects_data_obj, objects_data_value');
		$this->db->join('data_types_fields', 'data_types_fields.types_fields_id = objects_data.objects_data_field');
		$this->db->order_by('types_fields_order', 'ASC');
		$query = $this->db->get('objects_data');
		if($query->num_rows())
		{
			foreach($query->result_array() as $row) {
				$this->activeFieldsExport[$this->prefixDataFields . $row['types_fields_id']] = array('table' => 'objects_data', 'name' => $row['types_fields_name']);
				$dataFields[$row['objects_data_obj']][$this->prefixDataFields . $row['types_fields_id']] = $row['objects_data_value'];
			}
		}
		return $dataFields;
	}

	/*
	* экспорт объектов по нодам
	*/
	public function exportRowId($idArray = array())
	{
		$rowsExport = array();
		$this->keyField = 'tree_id';

		# сначала из общих таблиц
		$this->db->join('objects', 'objects.obj_id = tree.tree_object');
		$this->db->where_in('tree_id', $idArray);
		$query = $this->db->get('tree');

		$rowsExport = array();
		$objIdArray = array();
		$objIDtoRowID = array();
		foreach($query->result_array() as $row) {
			$rowsExport[$row['tree_id']] = $row;
			$objIdArray[$row['obj_id']] = $row['obj_id'];
		}

		if(!$rowsExport) {
			return array();
		}

		# data поля
		$dataFields = array();
		$this->db->select('types_fields_id, types_fields_name, objects_data_obj, objects_data_value');
		$this->db->join('data_types_fields', 'data_types_fields.types_fields_id = objects_data.objects_data_field');
		$this->db->where_in('objects_data_obj', $objIdArray);
		$this->db->order_by('types_fields_order', 'ASC');
		$query = $this->db->get('objects_data');
		if($query->num_rows())
		{
			foreach($query->result_array() as $row) {
				$dataFields[$row['objects_data_obj']][$this->prefixDataFields . $row['types_fields_id']] = $row['objects_data_value'];
			}
		}

		# соединяем объекты с дата-полями
		foreach($rowsExport as &$val) {
			if(isset($dataFields[$val['tree_object']])) {
				$val = array_merge($val, $dataFields[$val['tree_object']]);
			}
		}
		unset($val, $dataFields);
		return $rowsExport;
	}


	/*
	* чтение файла и формирование результирующей таблицы
	*/
	public function csvReadFileToTable($filePath = '', $limit = 20)
	{
		$activeFile = fopen($filePath, "r");
		$i = 0; // строки
		$startRow = 0;
		$fieldsKey = array();
		$outHtml = '';
		$strResult = '<h3>' . app_lang('EXPCSV_INFO_TABLE_PREVIEW') . '</h3>';
		$strResult .= '<table class="simple-table">';
		while(($dataCSV = fgetcsv($activeFile, 10000, $this->csvDelimiterField)) !== FALSE)
		{
			++$i;
			if($i < $startRow) // работаем после определенной строки
				continue;

			$strResult .= '<tr>';
			foreach($dataCSV as $key => $val)
			{
				if($i <= 2) {
					$strResult .= '<th>';
						$strResult .= '<input type="text" value="' . htmlspecialchars($val) . '"/>';
						//$strResult .= $val;
					$strResult .= '</th>';

					if($i == 2) { // вторая строка - ключи полей
						@$fieldsKey[$key] = $val;
					}
				}else{

					$strResult .= '<td>';
						$strResult .= '<textarea name="csvrow[' . $i . '][' . @$fieldsKey[$key] . ']">';
							$strResult .= $val;
						$strResult .= '</textarea>';
					$strResult .= '</td>';

				}
			}
			$strResult .= '</tr>';

			# ограничение для HTML вывода
			if($i >= ($limit + 2)) {
				$strResult .= '<tr>';
					$strResult .= '<td colspan="'.count($dataCSV).'">';
						$strResult .= $limit . ' ' . app_lang('EXPCSV_INFO_ROWS_SHOWN');
					$strResult .= '</td>';
				$strResult .= '</tr>';
				break;
			}
		}
		fclose($activeFile);

		$strResult .= '</table>';

		if(!in_array('obj_id', $fieldsKey) and !in_array('tree_id', $fieldsKey)) {
			$outHtml = '<div class="error">' . app_lang('EXPCSV_INFO_CREATE_NEW_OBJECT') . '</div>';
		}else{
			$outHtml = '<div class="update">' . app_lang('EXPCSV_INFO_UPDATE_OBJECT') . '</div>';
		}

		$outHtml .= $strResult;

		return $outHtml;
	}


	/*
	* чтение и обработка файла
	*/
	public function csvReadFile($filePath = '', $limit = 50)
	{
		if(!file_exists($filePath)) {
			return FALSE;
		}

		$activeFile = fopen($filePath, "r");
		$i = $cntObj = 0;
		$keysArr = array();
		while(($keysArr = fgetcsv($activeFile, 10000, $this->csvDelimiterField)) !== FALSE)
		{
			if(++$i == 2) {
				break;
			}
		}

		if(!in_array('obj_id', $keysArr) and !in_array('tree_id', $keysArr))
		{
			// первое поле - data-поле. Скорее всего обновление по нему
			if(strpos($keysArr['0'], $this->prefixDataFields) !== FALSE) {
				# обновление по data полю
				$cntObj = $this->_csvReadFileUpdate($activeFile, $keysArr);
			}else{
				# создание новых позиций
				$cntObj = $this->_csvReadFileInsert($activeFile, $keysArr);
			}
		}
		else
		{
			# обновление существующих
			$cntObj = $this->_csvReadFileUpdate($activeFile, $keysArr);
		}

		fclose($activeFile);

		return $cntObj;
	}

	/*
	* вспомогательная для csvReadFile. Обновление существующих
	*/
	public function _csvReadFileUpdate($openFile, $keys)
	{

		# разрешенные поля для обновления в tree
		$fieldsTree = array(
							'tree_parent_id' => '',
							'tree_url' => '',
							'tree_order' => '',
							'tree_type' => '',
							'tree_type_object' => '',
							'tree_folow' => '',
							'tree_short' => '',
							);

		# разрешенные поля для обновления в objects
		$fieldsObjects = array(
							'obj_data_type' => '',
							'obj_canonical' => '',
							'obj_name' => '',
							'obj_h1' => '',
							'obj_title' => '',
							'obj_description' => '',
							'obj_keywords' => '',
							'obj_anons' => '',
							'obj_content' => '',
							'obj_date_publish' => '',
							'obj_cnt_views' => '',
							'obj_rating_up' => '',
							'obj_rating_down' => '',
							'obj_rating_count' => '',
							'obj_user_author' => '',
							'obj_ugroups_access' => '',
							'obj_tpl_content' => '',
							'obj_tpl_page' => '',
							'obj_link' => '',
							'obj_status' => '',
							);

		# data поля
		$dataData = array();
		$updateTree = FALSE;
		$updateObjects = FALSE;
		$dataFieldID = 0;

		# разрешение обновлять tree
		if(in_array('tree_id', $keys)) {
			$updateTree = TRUE;
		}

		# разрешение обновлять objects и data
		if(in_array('obj_id', $keys)) {
			$updateObjects = TRUE;
		}

		# возможно обновление происходит по какому-то полю из data полей, например - коду товара
		// в этом случае надо вычислить исходя из data-поля ID объекта
		// если нет tree_id и нет obj_id, надо конвертировать значение data в obj_id
		if(!$updateTree and !$updateObjects) {
			$dataFieldID = preg_replace("/".$this->prefixDataFields."/", '', trim($keys['0']));
			// поля нет или что-то не то. Прерываем.
			if(!$dataFieldID) {
				return FALSE;
			}
			unset($keys['0']);
			// возможно что-то придется обновить и в таблице objects
			$updateObjects = TRUE;
		}


		$i = 0;
		while(($dataCSV = fgetcsv($openFile, 10000, $this->csvDelimiterField)) !== FALSE)
		{
			$objID = 0;
			$treeID = 0;

			$dataUpdateTree = array();
			$dataUpdateObject = array();
			$dataUpdateData = array();

			// есть dataFieldID - значит objID надо вычислять
			if($dataFieldID) {
				$this->db->where('objects_data_field', $dataFieldID);
				$this->db->where('objects_data_value', trim($dataCSV['0']));
				$this->db->limit(1);
				$query = $this->db->get('objects_data');
				$objID = $query->row('objects_data_obj');
			}

			# переборка массива файла
			foreach($keys as $IDField => $keyField)
			{
				$valueField = '';
				# обновляем tree
				if($updateTree)
				{
					if($keyField == 'tree_id') {
						$treeID = $dataCSV[$IDField];
					}

					if(isset($fieldsTree[$keyField])) {
						$dataUpdateTree[$keyField] = $dataCSV[$IDField];
					}
				}

				# обновляем objects
				if($updateObjects)
				{
					// получим $objID - сработает, даже если есть $dataFieldID
					if($keyField == 'obj_id') {
						$objID = trim($dataCSV[$IDField]);
					}

					if(isset($fieldsObjects[$keyField])) {
						$valueField = trim($dataCSV[$IDField]);
						if($valueField != $this->noValue) {
							$dataUpdateObject[$keyField] = $dataCSV[$IDField];
						}
					}

					# data поля
					if(strpos($keyField, $this->prefixDataFields) !== FALSE) {
						$valueField = trim($dataCSV[$IDField]);
						if($valueField != $this->noValue) {
							$dataUpdateData[preg_replace("/".$this->prefixDataFields."/", '', $keyField)] = $dataCSV[$IDField];
						}
					}
				}
			}

			# обновление tree
			if($updateTree and $treeID and $dataUpdateTree) {
				$this->db->where('tree_id', $treeID);
				$this->db->update('tree', $dataUpdateTree);
			}

			# обновление objects
			if($updateObjects and $objID and $dataUpdateObject) {
				$this->db->where('obj_id', $objID);
				$this->db->update('objects', $dataUpdateObject);
			}

			if($dataUpdateData and $objID)
			{
				foreach($dataUpdateData as $key => $val) {
					$this->db->where('objects_data_obj', $objID);
					$this->db->where('objects_data_field', $key);
					$qData = $this->db->get('objects_data');
					if($qData->num_rows()) {
						$this->db->where('objects_data_obj', $objID);
						$this->db->where('objects_data_field', $key);
						$this->db->update('objects_data', array('objects_data_value' => $val));
					} else {
						$dIns = array('objects_data_obj' => $objID);
						$dIns['objects_data_field'] = $key;
						$dIns['objects_data_value'] = $val;
						$this->db->insert('objects_data', $dIns);
					}
				}
			}
			++$i;
		}# endwile

		return $i;
	}

	/*
	* вспомогательная для csvReadFile. Добавление новых
	*/
	public function _csvReadFileInsert($openFile, $keys)
	{
		$dateCreate = date('Y-m-d H:i:s');
		$i = 0;
		while(($dataCSV = fgetcsv($openFile, 10000, $this->csvDelimiterField)) !== FALSE)
		{
			# разрешенные поля для импорта в tree
			$fieldsTree = array(
								'tree_parent_id' => 0,
								'tree_object' => 0,
								'tree_url' => '',
								'tree_date_create' => $dateCreate,
								'tree_order' => 0,
								'tree_type' => 'orig',
								'tree_type_object' => '1',
								'tree_folow' => '1',
								'tree_short' => '',
								'tree_axis' => '',
								);

			# разрешенные поля для импорта в objects
			$fieldsObjects = array(
								'obj_data_type' => 0,
								'obj_canonical' => '',
								'obj_name' => 'NO NAME',
								'obj_h1' => '',
								'obj_title' => '',
								'obj_description' => '',
								'obj_keywords' => '',
								'obj_anons' => '',
								'obj_content' => '',
								'obj_date_create' => $dateCreate,
								'obj_date_publish' => $dateCreate,
								'obj_lastmod' => time(),
								'obj_cnt_views' => 0,
								'obj_rating_up' => '0',
								'obj_rating_down' => '0',
								'obj_rating_count' => 0,
								'obj_user_author' => 1,
								'obj_ugroups_access' => 'ALL',
								'obj_tpl_content' => 'default',
								'obj_tpl_page' => 'default',
								'obj_link' => '',
								'obj_status' => 'publish',
								);

			# data поля
			$dataData = array();

			# переборка массива файла
			foreach($keys as $IDField => $keyField)
			{
				# tree поля
				if(isset($fieldsTree[$keyField])) {
					$fieldsTree[$keyField] = $dataCSV[$IDField];
				}

				# objects поля
				if(isset($fieldsObjects[$keyField])) {
					$fieldsObjects[$keyField] = $dataCSV[$IDField];
				}

				# data поля
				if(strpos($keyField, $this->prefixDataFields) !== FALSE) {
					$dataData[preg_replace("/".$this->prefixDataFields."/", '', $keyField)] = $dataCSV[$IDField];
				}
			}

			# сначала вставляем объект
			$this->db->insert('objects', $fieldsObjects);
			$objID = $this->db->insert_id();

			# вставляем data
			$dIns = array('objects_data_obj' => $objID);
			foreach($dataData as $key => $val) {
				$dIns['objects_data_field'] = $key;
				$dIns['objects_data_value'] = $val;
				$this->db->insert('objects_data', $dIns);
			}


			# вставляем tree
			$fieldsTree['tree_object'] = $objID;

			// если нет ссылки, то делаем ее автоматически
			$fieldsTree['tree_url'] = str_replace(' ', '', $fieldsTree['tree_url']);
			if(!$fieldsTree['tree_url']){
				$fieldsTree['tree_url'] = app_translate($fieldsObjects['obj_name']);
			}

			// далее формируем родителей. Если в tree_parent_id есть запятая, то надо объект приставить к нескольким родителям
			if(strpos($fieldsTree['tree_parent_id'], ',') !== FALSE)
			{
				$parents = explode(',', $fieldsTree['tree_parent_id']);
				$cnt = 1;
				foreach($parents as $pID)
				{
					$pID = trim($pID);

					if(!$pID)
						continue;

					if($cnt < 2) {
						$fieldsTree['tree_type'] = 'orig';
					} else {
						$fieldsTree['tree_type'] = 'copy';
					}
					$fieldsTree['tree_parent_id'] = $pID;

					// запись в БД
					$this->db->insert('tree', $fieldsTree);
					++$cnt;
				}
			}
			else
			{
				$this->db->insert('tree', $fieldsTree);
			}
			++$i;
		} # endwile

		return $i;
	}

	/*
	* возвращает массив объектов по типам данных
	*/
	public function getObjectsDataType($dataTypeID = 0)
	{
		# сначала из общих таблиц
		$this->db->select('tree.*, objects.*');
		$this->db->join('objects', 'objects.obj_id = tree.tree_object');
		$this->db->where('tree_type', 'orig');
		$this->db->where('obj_data_type', $dataTypeID);
		$query = $this->db->get('tree');

		$rowsExport = array();
		$objIdArray = array();
		foreach($query->result_array() as $row) {
			$rowsExport[$row['obj_id']] = $row;
			$objIdArray[$row['obj_id']] = $row['obj_id'];
		}

		if(!$rowsExport) {
			return array();
		}

		# data поля. Простой SQL для обхода ограничения большого WHERE IN
		$sql = "SELECT * ";
		$sql .= "FROM {$this->db->dbprefix}objects_data ";
		$sql .= "WHERE `objects_data_obj` IN (" . implode(',', $objIdArray) . ")";
		$query = $this->db->query($sql);
		if($query->num_rows()) {
			foreach($query->result_array() as $row) {
				$rowsExport[$row['objects_data_obj']][$this->prefixDataFields . $row['objects_data_field']] = $row['objects_data_value'];
			}
		}

		if($query->num_rows()) {
			foreach($query->result_array() as $row) {
				$rowsExport[$row['objects_data_obj']][$this->prefixDataFields . $row['objects_data_field']] = $row['objects_data_value'];
			}
		}
		return $rowsExport;
	}

	/*
	* возвращает массив объектов по типам объектов. При необходимости их DATA-поля
	*/
	public function getObjectsFromType($typeObjects, $dataFieldsID = array())
	{

		# сначала из общих таблиц
		$this->db->select('tree.*, objects.*');
		$this->db->join('objects', 'objects.obj_id = tree.tree_object');

		if(is_array($typeObjects)) {
			$this->db->where_in('tree_type_object', $typeObjects);
		} else {
			$this->db->where('tree_type_object', $typeObjects);
		}

		$this->db->where('tree_type', 'orig');
		$query = $this->db->get('tree');

		$rowsExport = array();
		$objIdArray = array();
		foreach($query->result_array() as $row) {
			$rowsExport[$row['obj_id']] = $row;
			$objIdArray[$row['obj_id']] = $row['obj_id'];
		}

		if(!$rowsExport) {
			return array();
		}

		# data поля. Простой SQL для обхода ограничения большого WHERE IN
		if($dataFieldsID)
		{
			$sql = "SELECT `objects_data_obj`, `objects_data_value`, `objects_data_field` ";
			$sql .= "FROM {$this->db->dbprefix}objects_data ";
			$sql .= "WHERE `objects_data_obj` IN (" . implode(',', $objIdArray) . ") ";
			$sql .= "AND `objects_data_field` IN (" . implode(',', $dataFieldsID) . ") ";
			$query = $this->db->query($sql);
			if($query->num_rows()) {
				foreach($query->result_array() as $row) {
					$rowsExport[$row['objects_data_obj']][$this->prefixDataFields . $row['objects_data_field']] = $row['objects_data_value'];
				}
			}
		}
		return $rowsExport;
	}
}
