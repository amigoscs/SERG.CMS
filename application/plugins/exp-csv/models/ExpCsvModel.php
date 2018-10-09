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
	* Совместимость с версией 6.0. tree_axis и obj_link
	*
	* version 4.0
	* UPD 2018-10-02
	* Переделка
	*
	* version 4.1
	* UPD 2018-10-03
	* добавлен параметр limitExport для выгрузки порциями
	*
	* version 4.2
	* UPD 2018-10-08
	* исправлена ошибка last_mod при добавлении позиции
	*
*/

class ExpCsvModel extends CI_Model {

	// разделитель полей
	public $csvDelimiterField;

	// разделитель строк
	public $csvDelimiterRows;

	// ограничитель полей
	public $csvEnclosure;

	// лимит экспорта (при сеансовой выгрузке)
	public $limitExport;

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

	// путь до папки с экспортным файлом
	public $exportFilePath;

	public function __construct()
    {
        parent::__construct();
		$this->csvCharset = 'utf-8';
		$this->csvDelimiterField = app_get_option("csv_fields_delimiter", "exp-csv", ";");
		$this->csvDelimiterRows = '\n';
		$this->csvEnclosure = app_get_option("csv_fields_enclosure", "exp-csv", '"');
		$this->limitExport = app_get_option("csv_limit_import", "exp-csv", 300);

		$this->prefixDataFields = 'isdata_';

		$this->report = array('status' => '400');
		$this->keyField = '';
		$this->delimiter = ';';
		$this->noValue = '_NOTSAVE_';
		$this->exportFilePath = APP_BASE_PATH . 'uploads/tmp/';

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
				'obj_link' 			=> array('table' => 'objects', 'name' => 'Связи'),
				'obj_tpl_content' 		=> array('table' => 'objects', 'name' => 'Шаблон контента'),
				'obj_tpl_page' 			=> array('table' => 'objects', 'name' => 'Шаблон страницы'),
				'obj_status' 			=> array('table' => 'objects', 'name' => 'Статус страницы'),
			);
    }

	/*
	* экспорт объектов по нодам
	*/
	public function exportNodeId($idArray = array())
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
	* Обновление полей по ключам
	*/
	public function _csvReadFileUpdate($keysUpdate = array(), $keysValues = array(), $keyDataField = false, $fieldsAllows = array())
	{
		// ключ, где хранится в строке CSV id объекта
		$keyObjID = false;

		// ключи значений для таблицы объектов
		$dataFieldObjID = array();

		// ключ, где хранится в строке CSV tree_id объекта
		$keyTreeID = false;

		// ключи значений для таблицы tree
		$dataFieldTreeID = array();

		// Ключи для data-полей
		$dataFieldsID = array();

		foreach($keysUpdate as $key => $value) {
			if($value == 'obj_id') {
				$keyObjID = $key;
			}

			if(in_array($value, $fieldsAllows['objects'])) {
				$dataFieldObjID[$key] = $value;
			}

			if($value == 'tree_id') {
				$keyTreeID = $key;
			}

			if(in_array($value, $fieldsAllows['tree'])) {
				$dataFieldTreeID[$key] = $value;
			}

			if(strpos($value, $this->prefixDataFields) === 0) {
				$dataFieldsID[$key] = str_replace($this->prefixDataFields, '', $value);
			}
		}

		$i = 0;
		foreach($keysValues as $value)
		{
			if(!$value) {
				continue;
			}

			++$i;

			$arrayValues = str_getcsv($value, $this->csvDelimiterField);

			##
			# если есть ID объекта и поля для objects, то обновляем
			##
			if($keyObjID !== false && $dataFieldObjID) {
				$objID = $arrayValues[$keyObjID];
				$data = array();
				foreach($dataFieldObjID as $dk => $dv) {
					$insVal = trim($arrayValues[$dk]);
					if($insVal == $this->noValue) {
						continue;
					}
					$data[$dv] = $insVal;
				}
				if($data) {
					$this->db->where('obj_id', $objID);
					$upd = $this->db->update('objects', $data);
				}
			}

			##
			# если есть TREE_ID объекта и поля для tree, то обновляем
			##
			if($keyTreeID !== false && $dataFieldTreeID) {
				$objID = $arrayValues[$keyTreeID];
				$data = array();
				foreach($dataFieldTreeID as $dk => $dv) {
					$insVal = trim($arrayValues[$dk]);
					if($insVal == $this->noValue) {
						continue;
					}
					$data[$dv] = $arrayValues[$dk];
				}

				if($data) {
					$this->db->where('tree_id', $objID);
					$upd =  $this->db->update('tree', $data);
				}
			}

			##
			# если есть data-поля и ID объекта для них, то обновляем
			##
			if($keyObjID !== false && $dataFieldsID) {
				//pr($arrayValues);
				$objID = $arrayValues[$keyObjID];
				foreach($dataFieldsID as $dk => $dv) {
					$insVal = trim($arrayValues[$dk]);
					if($insVal == $this->noValue) {
						continue;
					}
					# проверим существование объекта в базе
					$this->db->where('obj_id', $objID);
					$q = $this->db->get('objects');
					if($q->num_rows())
					{
						# проверим существование строки
						$this->db->where('objects_data_obj', $objID);
						$this->db->where('objects_data_field', $dv);
						$q = $this->db->get('objects_data');
						if($q->num_rows()) {
							$this->db->where('objects_data_obj', $objID);
							$this->db->where('objects_data_field', $dv);
							$this->db->update('objects_data', array('objects_data_value' => $insVal));
						} else {
							$data = array(
								'objects_data_obj' => $objID,
								'objects_data_field' => $dv,
								'objects_data_value' => $insVal
							);
							$this->db->insert('objects_data', $data);
						}
					}
				}
			}

		}

		return $i;
	}

	/*
	* Добавление новых позиций
	*/
	public function _csvReadFileInsert($keysUpdate = array(), $keysValues = array(), $fieldsAllows = array())
	{
		$dateCreate = date('Y-m-d H:i:s');
		$lastMod = time();

		// ключи значений для таблицы объектов
		$dataFieldObjID = array();

		// ключи значений для таблицы tree
		$dataFieldTreeID = array();

		// Ключи для data-полей
		$dataFieldsID = array();

		foreach($keysUpdate as $key => $value)
		{
			if(in_array($value, $fieldsAllows['objects'])) {
				$dataFieldObjID[$key] = $value;
			}

			if(in_array($value, $fieldsAllows['tree'])) {
				$dataFieldTreeID[$key] = $value;
			}

			if(strpos($value, $this->prefixDataFields) === 0) {
				$dataFieldsID[$key] = str_replace($this->prefixDataFields, '', $value);
			}
		}

		if(!$dataFieldObjID || !$dataFieldTreeID) {
			throw new Exception('Ошибка в строке');
		}

		$ins = 0;
		foreach($keysValues as $value)
		{
			if(!$value) {
				continue;
			}

			$arrayValues = str_getcsv($value, $this->csvDelimiterField);
			$objID = 0;
			$objName = '';
			##
			# пишем значения в objects
			##
			$data = array();
			foreach($dataFieldObjID as $dk => $dv) {
				$insVal = trim($arrayValues[$dk]);
				$data[$dv] = $insVal;
			}
			if($data) {
				$objName = $data['obj_name'];
				$data['obj_date_create'] = $dateCreate;
				$data['obj_lastmod'] = $lastMod;

				!isset($data['obj_date_publish']) ? $data['obj_date_publish'] = $data['obj_date_create'] : 0;
				!isset($data['obj_ugroups_access']) ? $data['obj_ugroups_access'] = 'ALL' : 0;
				!isset($data['obj_data_type']) ? $data['obj_data_type'] = '|1|' : 0;
				!isset($data['obj_h1']) ? $data['obj_h1'] = $objName : 0;
				!isset($data['obj_title']) ? $data['obj_title'] = $objName : 0;
				!isset($data['obj_description']) ? $data['obj_description'] = $objName : 0;
				!isset($data['obj_keywords']) ? $data['obj_keywords'] = $objName : 0;
				!isset($data['obj_anons']) ? $data['obj_anons'] = '' : 0;
				!isset($data['obj_content']) ? $data['obj_content'] = '' : 0;
				!isset($data['obj_cnt_views']) ? $data['obj_cnt_views'] = 0 : 0;
				!isset($data['obj_rating_up']) ? $data['obj_rating_up'] = 0 : 0;
				!isset($data['obj_rating_down']) ? $data['obj_rating_down'] = 0 : 0;
				!isset($data['obj_rating_count']) ? $data['obj_rating_count'] = 0 : 0;
				!isset($data['obj_user_author']) ? $data['obj_user_author'] = 1 : 0;
				!isset($data['obj_link']) ? $data['obj_link'] = '' : 0;
				!isset($data['obj_tpl_content']) ? $data['obj_tpl_content'] = '' : 0;
				!isset($data['obj_tpl_page']) ? $data['obj_tpl_page'] = '' : 0;
				!isset($data['obj_status']) ? $data['obj_status'] = 'hidden' : 0;

				$this->db->insert('objects', $data);
				$objID = $this->db->insert_id();
			}

			if(!$objID) {
				throw new Exception('Ошибка добавления строки');
			}

			##
			# пишем значения в tree
			##
			$data = array();
			foreach($dataFieldTreeID as $dk => $dv) {
				$insVal = trim($arrayValues[$dk]);
				$data[$dv] = $insVal;
			}

			if($data) {

				$data['tree_object'] = $objID;
				$data['tree_date_create'] = $dateCreate;

				!isset($data['tree_folow']) ? $data['tree_folow'] = 0 : 0;
				!isset($data['tree_order']) ? $data['tree_order'] = 1 : 0;
				!isset($data['tree_url']) ? $data['tree_url'] = app_translate($objName) : 0;
				!isset($data['tree_short']) ? $data['tree_short'] = '' : 0;
				!isset($data['tree_type_object']) ? $data['tree_type_object'] = 1 : 0;
				!isset($data['tree_type']) ? $data['tree_type'] = 'orig' : 0;

				// если добавляется в несколько разделов
				$parents = $insertData = array();
				if(strpos($data['tree_parent_id'], '|') !== false) {
					$parents = explode('|', $data['tree_parent_id']);
					$i = 0;
					foreach($parents as $parentID) {
						$data['tree_parent_id'] = trim($parentID);
						$i ? $data['tree_type'] = 'copy' : $data['tree_type'] = 'orig';

						$insertData[] = $data;
						++$i;
					}
				} else {
					$insertData[] = $data;
				}
				$this->db->insert_batch('tree', $insertData);
				unset($insertData);
			}

			##
			# пишем значения в objects_data
			##
			if($dataFieldsID) {
				$data = array();
				foreach($dataFieldsID as $dk => $dv) {
					$insVal = trim($arrayValues[$dk]);
					$data[] = array(
						'objects_data_obj' => $objID,
						'objects_data_field' => $dv,
						'objects_data_value' => $insVal,
					);
				}
				$this->db->insert_batch('objects_data', $data);
			}

			++$ins;
		}

		return $ins;
	}

	/*
	* возвращает массив объектов по типам объектов. При необходимости их DATA-поля
	*/
	public function getObjectsFromType($typeObjects, $dataFieldsID = array(), $type = false)
	{

		# сначала из общих таблиц
		$selectListArray = array();
		foreach($this->activeFieldsExport as $key => $value) {
			$selectListArray[] = $value['table'] . '.' . $key;
		}
		$this->db->select(implode(',', $selectListArray));
		$this->db->join('objects', 'objects.obj_id = tree.tree_object');

		if(is_array($typeObjects)) {
			$this->db->where_in('tree_type_object', $typeObjects);
		} else {
			$this->db->where('tree_type_object', $typeObjects);
		}

		if($type) {
			$this->db->where('tree_type', $type);
		}

		$this->db->order_by('objects.obj_id', 'ASC');
		//pr($this->db->get_compiled_select('tree'));

		$query = $this->db->get('tree');

		$rowsExport = array();
		$objIdArray = array();
		foreach($query->result_array() as $row) {
			$rowsExport[$row['tree_id']] = $row;
			$objIdArray[$row['tree_id']] = $row['obj_id'];
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
					$treeIDArray = array_keys($objIdArray, $row['objects_data_obj']);
					foreach($treeIDArray as $keyTree) {
						if(isset($rowsExport[$keyTree])) {
							$rowsExport[$keyTree][$this->prefixDataFields . $row['objects_data_field']] = $row['objects_data_value'];
						}
					}
				}
			}
		}
		return $rowsExport;
	}

	# Возвращает массив всех вложенных объектов
	public function getAllChilds($parentID, $result = array())
	{
		// в массиве много объектов. Прерываем
		if(count($result) > 500) {
			throw new Exception('Слишком много объектов. Измените параметры');
		}

		$this->db->join('objects', 'tree.tree_object = objects.obj_id');
		if(is_array($parentID)) {
			$this->db->where_in('tree_parent_id', $parentID);
		} else {
			$this->db->where('tree_parent_id', $parentID);
		}
		$query = $this->db->get('tree');
		if($cnt = $query->num_rows()) {
			// если количество больше 300, то возможено превышение тайм-лимита или лимита памяти. Отправляем на другой экспорт
			if($cnt > 300) {
				throw new Exception('Превышен лимит вывода. Воспользуйтесь другим экспортом');
			}

			$parents = array();
			$objIdArray = array();
			foreach($query->result_array() as $row) {
				$result[$row['tree_id']] = $row;
				$parents[] = $row['tree_id'];
				$objIdArray[$row['tree_id']] = $row['obj_id'];
			}

			$dataFields = array();
			$this->db->select('types_fields_id, types_fields_name, objects_data_obj, objects_data_value');
			$this->db->join('data_types_fields', 'data_types_fields.types_fields_id = objects_data.objects_data_field');
			$this->db->where_in('objects_data_obj', $objIdArray);
			$this->db->order_by('types_fields_order', 'ASC');
			$query = $this->db->get('objects_data');
			if($query->num_rows())
			{
				foreach($query->result_array() as $row) {
					$treeIDArray = array_keys($objIdArray, $row['objects_data_obj']);
					foreach($treeIDArray as $keyTree) {
						if(isset($result[$keyTree])) {
							$result[$keyTree][$this->prefixDataFields . $row['types_fields_id']] = $row['objects_data_value'];
						}
					}
				}
			}

			return $this->getAllChilds($parents, $result);
		} else {
			return $result;
		}
	}

	# возвращает ID объекта по nodeID
	public function getObjID($nodeID = 0)
	{
		$this->db->select('tree_object');
		$this->db->where('tree_id', $nodeID);
		$query = $this->db->get('tree');
		if($query->num_rows()) {
			return $query->row('tree_object');
		} else {
			return 0;
		}

	}
}
