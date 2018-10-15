<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  библиотека E-market. Вывод в админ-панели

	* UPD 2018-04-17
	* version 0.1
	*
	* UPD 2018-04-17
	* version 0.2
	* product_list - добалена возможность вывода дополнительных полей

*/

class IndexEMarketLib {

	private $EmarketModel;
	private $ajaxResponse;


	public function __construct()
	{
		$path = APP_PLUGINS_DIR_PATH . '/e-market/models/EmarketModel.php';
		$this->ajaxResponse = array('status' => 'ERROR', 'info' => '');
		if(file_exists($path)) {
			require_once($path);
			$this->EmarketModel = new EmarketModel();
		}
	}

	public function __call($method, $par)
	{
		return $this->index();
	}

	public function index()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('EMARKET_TITLE_INDEX');
		$CI->pageContentDescription = $CI->pageContentTitle;

		$data = array('h1' => 'E-market');
		$CI->pageContent = $CI->load->view('admin-pages/index', $data, true);
	}

	/*
	* Настройки E-market
	*/
	public function setting_emarket()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('EMARKET_TITLE_SETTING');
		$CI->pageContentDescription = $CI->pageContentTitle;
		$data = array('h1' => 'Настройка E-market');

		if($options = $CI->input->post('emarket_option')) {
			foreach($options as $key => $value) {
				app_add_option($key, trim($value), 'e-market');
			}
			$CI->pageCompliteMessage = 'Настройки сохранены';
		}
		$CI->pageContent = $CI->load->view('admin-pages/setting-main', $data, true);
	}

	/*
	* Настройки курсов валют E-market
	*/
	public function setting_currency()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('EMARKET_TITLE_SETTING_CURRENCY');
		$CI->pageContentDescription = $CI->pageContentTitle;
		$data = array('h1' => $CI->pageContentTitle);
		if($CI->input->post())
		{
			# активная валюта для сайта
			if($activeCurrencySite = $CI->input->post('active_currency_site')) {
				$CI->db->update('ecart_currency', array('ecartcur_site' => 0));
				$CI->db->where('ecartcur_id', $activeCurrencySite);
				$CI->db->update('ecart_currency', array('ecartcur_site' => 1));
			}

			# активная валюта для товаров
			if($activeCurrencyProducts = $CI->input->post('active_currency_products')) {
				$CI->db->update('ecart_currency', array('ecartcur_products' => 0));
				$CI->db->where('ecartcur_id', $activeCurrencyProducts);
				$CI->db->update('ecart_currency', array('ecartcur_products' => 1));
			}

			# сохраним курс
			if($currencyRate = $CI->input->post('currency_rate')) {
				foreach($currencyRate as $key => $value) {
					$CI->db->where('ecartcur_id', $key);
					$CI->db->update('ecart_currency', array('ecartcur_rate' => $value));
				}
			}

			# прочие опции
			if($options = $CI->input->post('emarket_option')) {
				foreach($options as $key => $value) {
					app_add_option($key, trim($value), 'e-market');
				}
			}

			$CI->pageCompliteMessage = 'Настройки сохранены';
		}

		$data['currencyInfo'] = $CI->EmarketModel->EmarketPriceModel->getAllCurrency();
		$data['allRulesPrice'] = $CI->EmarketModel->EmarketPriceModel->allRulesPrice;
		$data['rulesPrice'] = app_get_option('price_rules', 'e-market', 1);

		$CI->pageContent = $CI->load->view('admin-pages/setting-currency', $data, true);
	}

	/*
	* Настройки корзин
	*/
	public function setting_carts()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('EMARKET_TITLE_SETTING_CART');
		$CI->pageContentDescription = $CI->pageContentTitle;
		$data = array('h1' => $CI->pageContentTitle);

		$upd = false;
		# пост на обноление типа корзины
		if($options = $CI->input->post('editcart')) {
			foreach($options as $key => $value) {
				$upd = $CI->EmarketModel->EmarketOptionsModel->updateCart($key, $value);
			}
			if($upd) {
				$CI->pageCompliteMessage = 'Изменения сохранены';
			} else {
				$CI->pageErrorMessage = 'Ошибка сохранения';
			}
		}

		# пост на добавление типа корзины
		if($options = $CI->input->post('newcart')) {
			foreach($options as $value) {
				$upd = $CI->EmarketModel->EmarketOptionsModel->createCart($value);
			}
			if($upd) {
				$CI->pageCompliteMessage = 'Корзина создана';
			} else {
				$CI->pageErrorMessage = 'Ошибка создания корзины';
			}
		}

		#
		# пост на обновление статуса корзины
		if($options = $CI->input->post('editcartstatus')) {
			foreach($options as $key => $value) {
				$upd = $CI->EmarketModel->EmarketOptionsModel->updateSatusCart($key, $value);
				//$upd = $CI->EmarketModel->EmarketOptionsModel->createCart($value);
			}
			if($upd) {
				$CI->pageCompliteMessage = 'Статусы обновлены';
			} else {
				$CI->pageErrorMessage = 'Ошибка обновления статусов';
			}
		}

		# пост на добавление статуса корзины
		if($options = $CI->input->post('newcartstatus')) {
			foreach($options as $value) {
				$upd = $CI->EmarketModel->EmarketOptionsModel->createSatusCart($value);
				//$upd = $CI->EmarketModel->EmarketOptionsModel->createCart($value);
			}
			if($upd) {
				$CI->pageCompliteMessage = 'Статус успешно создан';
			} else {
				$CI->pageErrorMessage = 'Ошибка создания статуса';
			}
		}

		$data['carts'] = $CI->EmarketModel->EmarketOptionsModel->getCarts(false);
		$data['statusCarts'] = $CI->EmarketModel->EmarketOptionsModel->getSatusCart(false);

		$CI->pageContent = $CI->load->view('admin-pages/setting-type-carts', $data, true);
	}

	/*
	* Настройка пользовательской формы
	*/
	public function setting_form()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('EMARKET_TITLE_SETTING_FORM');
		$CI->pageContentDescription = $CI->pageContentTitle;
		$data = array('h1' => $CI->pageContentTitle);

		if($options = $CI->input->post('updatefield')) {
			foreach($options as $key => $value) {
				$upd = $CI->EmarketModel->EmarketOptionsModel->updateFieldsFormCart($key, $value);
			}
			if($upd) {
				$CI->pageCompliteMessage = 'Поля успешно обновлены';
			} else {
				$CI->pageErrorMessage = 'Ошибка обновления полей';
			}
		}

		if($options = $CI->input->post('newfield')) {
			foreach($options as $value) {
				$upd = $CI->EmarketModel->EmarketOptionsModel->createFieldsFormCart($value);
			}
			if($upd) {
				$CI->pageCompliteMessage = 'Новое поле создано';
			} else {
				$CI->pageErrorMessage = 'Ошибка создания поля';
			}
		}

		$data['fields'] = $CI->EmarketModel->EmarketOptionsModel->getFieldsFormCart(false);

		$CI->pageContent = $CI->load->view('admin-pages/setting-form', $data, TRUE);
	}

	/*
	* Настройка скидок
	*/
	public function setting_sales()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = 'Скидки';
		$CI->pageContentDescription = $CI->pageContentTitle;
		$data = array('h1' => $CI->pageContentTitle);

		$CI->pageContent = $CI->load->view('admin-pages/setting-sales', $data, TRUE);
	}

	/*
	* просмотр списка заказов
	*/
	public function order_list()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('EMARKET_TITLE_ORDER_LIST');
		$CI->pageContentDescription = $CI->pageContentTitle;
		$data = array('h1' => $CI->pageContentTitle);

		$data['filterDateStart'] = $CI->input->get('cart_start_date') ? $CI->input->get('cart_start_date') : date('Y-m') . '-01';
		$data['filterDateStop'] = $CI->input->get('cart_stop_date') ? $CI->input->get('cart_stop_date') : date('Y-m-d');
		$data['filterCartStatus'] = $CI->input->get('cart_status') ? $CI->input->get('cart_status') : 0;
		$data['filterCartCashStatus'] = $CI->input->get('cart_cash_status') ? $CI->input->get('cart_cash_status') : 0;
		$data['filterCartTypeCart'] = $CI->input->get('cart_type') ? $CI->input->get('cart_type') : 0;

		$paramsOrderSelect = array(
			'date_start' => $data['filterDateStart'],
			'date_stop' => $data['filterDateStop'],
			'status' => $data['filterCartStatus'],
			'cash_status' => $data['filterCartCashStatus'],
			'type' => $data['filterCartTypeCart'],
		);

		$data['allStatus'] = $CI->EmarketModel->EmarketOptionsModel->getSatusCart(false);
		$data['allCashStatus'] = $CI->EmarketModel->EmarketOptionsModel->getCashSatusCart();
		$data['allCartTypes'] = $CI->EmarketModel->EmarketOptionsModel->getCarts();

		$data['orders'] = $CI->EmarketModel->EmarketOrderModel->allOrders('', $paramsOrderSelect);


		$CI->pageContent = $CI->load->view('admin-pages/order-list', $data, true);
	}

	/*
	* просмотр конкретного заказа
	*/
	public function order_view()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = app_lang('EMARKET_TITLE_ORDER_LIST');
		$CI->pageContentDescription = $CI->pageContentTitle;
		$data = array('h1' => $CI->pageContentTitle);
		$upd = false;

		// разрешение изменить заказ
		$data['editOrder'] = false;

		if(!$cartID = $CI->input->get('order')) {
			return $CI->_render_not_found();
		}

		$data['order'] = $CI->EmarketModel->cartInfo($cartID);
		//pr($data['order']);
		if(!$data['order']) {
			return $CI->_render_not_found();
		}

		if($data['order']['ecart_status'] > 1) {
			$data['editOrder'] = true;
		}

		# Изменить статус заказа
		if($newStatus = $CI->input->post('cart_status')) {
			$dataUpdate = array(
				'ecart_status' => $newStatus
			);
			if($newStatus < 2) {
				$CI->data['infoerror'] = 'Нельзя устанавливать статус заказа - НАПОЛНЯЕТСЯ!';
			} else {
				$CI->db->where('ecart_id', $cartID);
				if($upd = $CI->db->update('ecart', $dataUpdate)) {
					$CI->pageCompliteMessage = 'Статус заказа успешно обновлен';
				} else {
					$CI->pageErrorMessage = 'Ошибка обновления статуса заказа';
				}
			}
		}

		# Изменить статус ОПЛАТЫ заказа
		if($newStatus = $CI->input->post('cart_cash_status')) {
			$dataUpdate = array(
				'ecart_cash_status' => $newStatus
			);
			$CI->db->where('ecart_id', $cartID);
			if($upd = $CI->db->update('ecart', $dataUpdate)) {
				$CI->pageCompliteMessage = 'Статус оплаты успешно обновлен';
			} else {
				$CI->pageErrorMessage = 'Ошибка обновления статуса оплаты';
			}
		}

		// если что-то обновлялось, то надо перезагрузить заказ
		if($upd) {
			if(isset($CI->EmarketModel->userCarts[$cartID])) {
				unset($CI->EmarketModel->userCarts[$cartID]);
			}
			$data['order'] = $CI->EmarketModel->cartInfo($cartID);
		}

		$data['allCartStatus'] = array(1 => 'Error');
		$tmp = $CI->EmarketModel->EmarketOptionsModel->getSatusCart();
		foreach($tmp as $key => $value) {
			$data['allCartStatus'][$key] = $value['ecarts_name'];
		}

		$data['allCashStatus'] = $CI->EmarketModel->EmarketOptionsModel->getCashSatusCart();

		$CI->pageContent = $CI->load->view('admin-pages/order-view', $data, true);
	}

	/*
	* страницы печати заказа
	*/
	public function order_print()
	{

	}

	/*
	* просмотр товаров
	*/
	public function product_list()
	{
		$CI = &get_instance();

		$CI->pageContentTitle = app_lang('EMARKET_TITLE_PRODUCT_LIST');
		$CI->pageContentDescription = $CI->pageContentTitle;
		$data = array('h1' => $CI->pageContentTitle);

		$data['filterViewImage'] = 0;
		$data['filterTypeObject'] = 8;
		$data['allTypeObject'] = array(8 => 'Товарная позиция', 11 => 'Группа товаров');
		$data['fieldSKU'] = 0;

		# заголовки таблицы
		$data['tableHeaders'] = array(
			'name' => 'Наименование',
		);

		if($fSku = app_get_option('product_code_field', 'e-market', 0)) {
			$data['tableHeaders'][$fSku] = 'Артикул';
			$data['fieldSKU'] = $fSku;
		}

		if($tmp = app_get_option('e_market_products_table_headers', 'e-market', '')) {
			$tmp = explode(',', $tmp);
			$CI->CommonModel->getOnlyPublish = true;
			$dataFields = $CI->CommonModel->getAllDataTypesFields();
			foreach($tmp as $fKey) {
				$data['tableHeaders'][$fKey] = $dataFields[$fKey]['types_fields_name'];
			}
			unset($tmp);
		}

		$data['getTextValue'] = $data['getFieldValue'] = '';

		if($filterQuery = $CI->input->get('filter')){
			$data['getTextValue'] = $filterQuery['text'];
			$data['getFieldValue'] = $filterQuery['field'];
			$data['filterTypeObject'] = $filterQuery['type_object'];

			if($text = trim($filterQuery['text']))
			{
				$textArray = explode(' ', trim($filterQuery['text']));
				if($filterQuery['field'] == 'name'){
					if(count($textArray) > 1){
						$text = implode('%', $textArray);
						$CI->db->like('obj_name', $text, 'both', false);
						//pr($CI->db->get_compiled_select());
					}else{
						$CI->db->like('obj_name', trim($filterQuery['text']));
					}
				}elseif(strpos($filterQuery['field'], 'isdata_') === 0) {
					$dataFieldID = str_replace('isdata_', '', $filterQuery['field']);
					$CI->db->join('objects_data', 'objects.obj_id = objects_data.objects_data_obj');
					$CI->db->where('objects_data_field', $dataFieldID);
					if(strpos($filterQuery['text'], ';')){
						$textArray = explode(';', $filterQuery['text']);
						$textArray = array_map("trim", $textArray);
						$CI->db->where_in('objects_data_value', $textArray);
					} else {
						$CI->db->where('objects_data_value', $filterQuery['text']);
					}
				}
			}
		}

		$CI->ObjectAdmModel->reset();
		$CI->ObjectAdmModel->nodeType = 'orig';
		$CI->ObjectAdmModel->orderField = 'obj_date_publish';
		$CI->ObjectAdmModel->limit = 50;
		$CI->ObjectAdmModel->orderAsc = 'DESC';
		$CI->ObjectAdmModel->includeDataFields = TRUE;
		$CI->ObjectAdmModel->onlyFolow = FALSE;
		$CI->ObjectAdmModel->onlyPublish = FALSE;
		$CI->ObjectAdmModel->objectTypeTree = $data['filterTypeObject'];
		$CI->ObjectAdmModel->paginationTrue = TRUE;

		$data['products'] = $CI->ObjectAdmModel->getChildsObjects();
		$data['pagination'] = $CI->ObjectAdmModel->paginationArray;
		$data['totalProducts'] = $CI->ObjectAdmModel->pagRows;

		//return $CI->load->view('admin_pages/products-list', $data, TRUE);
		$CI->pageContent = $CI->load->view('admin-pages/products-list', $data, true);
	}

	/*
	* просмотр товаров
	*/
	public function price_export()
	{
		$CI = &get_instance();
		$CI->pageContentTitle = 'Экспорт прайса';
		$CI->pageContentDescription = $CI->pageContentTitle;
		$data['h1'] = $CI->pageContentTitle;
		$data['dataFieldsID'] = array();

		// код товара
		if($sku = app_get_option('product_code_field', 'e-market', 0)) {
			$data['dataFieldsID'][$sku] = 'Код товара';
		}

		// дата поля со старыми ценами
		if(app_get_option('product_old_price_field', 'e-market', 0)) {
			$tmp = app_parse_select_values(app_get_option('product_old_price_field', 'e-market', 0));
			$dataFields = $CI->CommonModel->getAllDataTypesFields(false, $tmp);
			foreach($dataFields as $value) {
				$data['dataFieldsID'][$value['types_fields_id']] = $value['types_fields_name'];
			}
		}

		// дата поля с текущими ценами
		if(app_get_option('product_price_field', 'e-market', 0)) {
			$tmp = app_parse_select_values(app_get_option('product_price_field', 'e-market', 0));
			$dataFields = $CI->CommonModel->getAllDataTypesFields(false, $tmp);
			foreach($dataFields as $value) {
				$data['dataFieldsID'][$value['types_fields_id']] = $value['types_fields_name'];
			}
		}

		// дата поля с наличием товаров
		if(app_get_option('product_stock', 'e-market', 0)) {
			$tmp = app_parse_select_values(app_get_option('product_stock', 'e-market', 0));
			$dataFields = $CI->CommonModel->getAllDataTypesFields(false, $tmp);
			foreach($dataFields as $value) {
				$data['dataFieldsID'][$value['types_fields_id']] = $value['types_fields_name'];
			}
		}

		$data['allTypesObjects'] = $CI->CommonModel->getAllObjTypes(); // все типы объектов
		foreach($data['allTypesObjects'] as $key => &$value) {
			$value = $value['obj_types_name'];
		}
		unset($value);

		# дополнительные поля
		$data['otherFields'] = array(
			'obj_h1' => $CI->ExpCsvModel->activeFieldsExport['obj_h1']['name'],
			'obj_title' => $CI->ExpCsvModel->activeFieldsExport['obj_title']['name'],
			'obj_description' => $CI->ExpCsvModel->activeFieldsExport['obj_description']['name'],
			'obj_keywords' => $CI->ExpCsvModel->activeFieldsExport['obj_keywords']['name'],
			'obj_anons' => $CI->ExpCsvModel->activeFieldsExport['obj_anons']['name'],
			'obj_content' => $CI->ExpCsvModel->activeFieldsExport['obj_content']['name'],
			'obj_ugroups_access' => $CI->ExpCsvModel->activeFieldsExport['obj_ugroups_access']['name'],
		);

		$data['infoDelimiterField'] = $CI->ExpCsvModel->csvDelimiterField;
		$data['infoEnclosure'] = $CI->ExpCsvModel->csvEnclosure;
		$data['exportLimit'] = $CI->ExpCsvModel->limitExport;

		$CI->pageContent = $CI->load->view('admin-pages/price-export', $data, true);
	}

	/*
	* Метод принимает Ajax
	*/
	public function ajax_request()
	{
		$CI = &get_instance();
		$this->ajaxResponse = array('status' => 'ERROR', 'info' => '');
		$method = '_ajax_' . $CI->input->get('request');
		if(method_exists($this, $method)) {
			try {
				$this->$method();
			} catch (Exception $e) {
				$this->ajaxResponse['status'] = 'ERROR';
				$this->ajaxResponse['info'] = $e->getMessage();
			}
		} else {
			$this->ajaxResponse['info'] = 'Method is not exists';
		}
		echo json_encode($this->ajaxResponse);
		exit();
	}

	/*
	* Ajax - Удаление объектов
	*/
	private function _ajax_delete_objects()
	{
		$CI = &get_instance();
		if($objects = $CI->input->post('elements')){
			$res = 0;
			foreach($objects as $objID) {
				$res = $CI->ObjectAdmModel->deleteObject($objID);
			}
			if($res) {
				$this->ajaxResponse['status'] = 'OK';
				$this->ajaxResponse['info'] = 'Объекты успешно удалены';
			} else {
				$this->ajaxResponse['info'] = 'Ошибка удаления объектов';
			}
		} else {
			$this->ajaxResponse['info'] = 'Пустой массив';
		}
	}

	/*
	* AJAX - изменить статус видимости объекта
	*/
	public function _ajax_change_status_object()
	{
		$CI = &get_instance();
		if($objectsID = $CI->input->post('element')){
			# получим текущий статус
			$CI->ObjectAdmModel->reset();
			$CI->ObjectAdmModel->includeDataFields = FALSE;
			$object = $CI->ObjectAdmModel->getObject($objectsID);
			if(!$object) {
				$this->ajaxResponse['info'] = 'Объект не найден';
				return;
			}

			$newStatus = 'hidden';
			if($object['obj_status'] == 'hidden') {
				$newStatus = 'publish';
			}

			$CI->ObjectAdmModel->reset();
			$upd = $CI->ObjectAdmModel->updateObject(array('status' => $newStatus), $object['obj_id']);
			if($upd) {
				$this->ajaxResponse['status'] = 'OK';
				$this->ajaxResponse['info'] = 'Объект успешно обновлен';
				$this->ajaxResponse['new_class'] = 'status-' . $newStatus;
			} else {
				$this->ajaxResponse['info'] = 'Ошибка обновления';
			}
		} else {
			$this->ajaxResponse['info'] = 'Пустой массив';
		}
		return true;
	}

	/*
	* AJAX - экспорт прайса
	*/
	private function _ajax_export_price()
	{
		$CI = &get_instance();

		if(!$formValues = $CI->input->post('form_values')) {
			throw new Exception('Нет параметров');
		}

		parse_str($formValues, $formValues);

		if(!isset($formValues['active_types'])) {
			throw new Exception('Не указан тип объекта');
		}

		if(!isset($formValues['active_data_types_fields'])) {
			throw new Exception('Не указаны типы данных');
		}

		$otherFields = array();
		if(isset($formValues['other_fields'])) {
			foreach($formValues['other_fields'] as $value) {
				if(isset($CI->ExpCsvModel->activeFieldsExport[$value])) {
					$otherFields[$value] = $CI->ExpCsvModel->activeFieldsExport[$value]['name'];
				}

			}
		}

		$exportHeaders = array(
			'obj_id' => 'ID товара',
			'obj_name' => 'Название товара'
		);

		if($otherFields) {
			foreach($otherFields as $key => $value) {
				$exportHeaders[$key] = $value;
			}
		}

		$exportHeaders['link'] = 'Ссылка';
		$exportHeaders['category'] = 'Категория';
		$exportHeaders['site_currency'] = 'Валюта сайта';
		$exportHeaders['product_currency'] = 'Валюта товара';


		$limitExport = $CI->ExpCsvModel->limitExport;
		$sqlOffset = $CI->input->post('sql_offset');

		$OBJS = array();
		$CI->db->limit($limitExport, $sqlOffset);
		$OBJS = $CI->EmarketModel->getProductsExport($formValues['active_types'], array(), $otherFields);

		// файл CSV
		$pathCsvLib = APP_PLUGINS_DIR_PATH . 'exp-csv/libraries/CsvFileLib.php';
		$exportPath = $CI->ExpCsvModel->exportFilePath;
		$fileExportName = $CI->input->post('file_name');
		if(!$fileExportName) {
			$fileExportName = 'price-list-' . date('Y-m-d') . '.csv';
		}

		if(!$OBJS && !$sqlOffset) {
			throw new Exception('Товары не найдены');
		} else if(!$OBJS) {
			$this->ajaxResponse['status'] = 'OK';
			$this->ajaxResponse['flag_update'] = 'STOP';
			$this->ajaxResponse['info'] = 'Файл сформирован. <a href="'. str_replace(APP_BASE_PATH, APP_BASE_URL, $exportPath) . $fileExportName . '" title="" download>Скачать »</a>';
			$this->ajaxResponse['file_name'] = $fileExportName;
			return;
		}


		if(!file_exists($exportPath)) {
			if(!mkdir($exportPath)) {
				throw new Exception('Ошибка: директория не может быть создана');
			}
		}

		if(!file_exists($pathCsvLib)) {
			throw new Exception('Ошибка подключения библиотеки CSV');
		} else {
			require_once($pathCsvLib);
			$CSVLIB = new csvFileLib($CI->ExpCsvModel->csvDelimiterField, $CI->ExpCsvModel->csvEnclosure, $CI->ExpCsvModel->noValue);
		}

		//$this->ajaxResponse['status'] = 'ERROR';
		//$this->ajaxResponse['info'] = $e->getMessage();

		$prefixData = $CI->ExpCsvModel->prefixDataFields;
		$prefixCurr = 'cur_';
		$activeDataTypes = $formValues['active_data_types_fields'];

		// массив валют
		$currArray = $CI->EmarketModel->EmarketPriceModel->currArray;
		// Название валюты сайта
		$currSite = $currArray[$CI->EmarketModel->EmarketPriceModel->currSiteID]['ecartcur_code'];



		// код товара
		if($sku = app_get_option('product_code_field', 'e-market', 0)) {
			if(in_array($sku, $activeDataTypes)) {
				$exportHeaders[$prefixData . $sku] = 'Код товара';
			}
		}

		// дата поля со старыми ценами
		$oldPriceFields = array();
		if(app_get_option('product_old_price_field', 'e-market', 0)) {
			$oldPriceFields = app_parse_select_values(app_get_option('product_old_price_field', 'e-market', 0));
			$dataFields = $CI->CommonModel->getAllDataTypesFields(false, $oldPriceFields);
			foreach($dataFields as $value) {
				if(in_array($value['types_fields_id'], $activeDataTypes)) {
					$exportHeaders[$prefixData . $value['types_fields_id']] = $value['types_fields_name'];
					$exportHeaders[$prefixCurr . $prefixData . $value['types_fields_id']] = 'Валюта: ' . $value['types_fields_name'];
				}
			}
		}

		// дата поля с текущими ценами
		$priceFields = array();
		if(app_get_option('product_price_field', 'e-market', 0)) {
			$priceFields = app_parse_select_values(app_get_option('product_price_field', 'e-market', 0));
			$dataFields = $CI->CommonModel->getAllDataTypesFields(false, $priceFields);
			foreach($dataFields as $value) {
				if(in_array($value['types_fields_id'], $activeDataTypes)) {
					$exportHeaders[$prefixData . $value['types_fields_id']] = $value['types_fields_name'];
					$exportHeaders[$prefixCurr . $prefixData . $value['types_fields_id']] = 'Валюта: ' . $value['types_fields_name'];
				}
			}
		}

		// дата поля с наличием товаров
		$stockFields = array();
		if(app_get_option('product_stock', 'e-market', 0)) {
			$stockFields = app_parse_select_values(app_get_option('product_stock', 'e-market', 0));
			$dataFields = $CI->CommonModel->getAllDataTypesFields(false, $stockFields);
			foreach($dataFields as $value) {
				if(in_array($value['types_fields_id'], $activeDataTypes)) {
					$exportHeaders[$prefixData . $value['types_fields_id']] = $value['types_fields_name'];
				}
			}
		}

		// создадим цены
		$OUT = array();
		foreach($OBJS as $keyProduct => $product) {
			$emInfo = $CI->EmarketModel->initProduct($product);
			foreach($exportHeaders as $keyHead => $value) {
				if(isset($product[$keyHead])) {
					$OUT[$keyProduct][$keyHead] = $product[$keyHead];
				} else if($keyHead == 'category') {
					$cat = $CI->EmarketModel->getCategoryes($product['tree_axis']);
					$OUT[$keyProduct][$keyHead] = implode('/', array_values($cat));
					$OUT[$keyProduct]['link'] = implode('/', array_keys($cat));

				} else if($keyHead == 'product_currency') {
					$OUT[$keyProduct][$keyHead] = $currArray[$emInfo['productCurrency']]['ecartcur_code'];
				} else if($keyHead == 'site_currency') {
					$OUT[$keyProduct][$keyHead] = $currSite;
				}
				else
				{
					# остальное - цены и наличие
					// код товара
					if($sku) {
						$OUT[$keyProduct][$prefixData . $sku] = isset($product['data_fields'][$sku]) ? $product['data_fields'][$sku]['objects_data_value'] : 0;
					}

					// старые цены
					if($oldPriceFields)
					{
						foreach($oldPriceFields as $kf => $vf) {
							if(isset($emInfo['oldPriceArray'][$kf])) {
								$OUT[$keyProduct][$prefixData . $vf] = isset($product['data_fields'][$vf]) ? $product['data_fields'][$vf]['objects_data_value'] : 0;
								$OUT[$keyProduct][$prefixCurr . $prefixData . $vf] = $emInfo['oldPriceArray'][$kf];
							}
						}
					}

					// цены
					if($priceFields)
					{
						foreach($priceFields as $kf => $vf) {
							if(isset($emInfo['priceArray'][$kf])) {
								$OUT[$keyProduct][$prefixData . $vf] = isset($product['data_fields'][$vf]) ? $product['data_fields'][$vf]['objects_data_value'] : 0;
								$OUT[$keyProduct][$prefixCurr . $prefixData . $vf] = $emInfo['priceArray'][$kf];
							}
						}
					}

					// наличие
					if($stockFields)
					{
						foreach($stockFields as $kf => $vf) {
							if(isset($emInfo['inStockValueArray'][$kf])) {
								$OUT[$keyProduct][$prefixData . $vf] = isset($product['data_fields'][$vf]) ? $product['data_fields'][$vf]['objects_data_value'] : 0;
							}
						}
					}
				}
			}
		}
		unset($OBJS);

		# запись файла. Если есть смещение в запросе объектов, то продолжаем писать файл
		if($sqlOffset) {
			$CSVLIB->init($exportPath . $fileExportName, 'a+');
			$exportHeaders = $CSVLIB->getHeadersCSV();
			$CSVLIB->SetOffset(0, true);
			foreach($OUT as $key => $value) {
				$tmp = array();
				foreach($exportHeaders as $keyHead => $valHead) {
					if(isset($value[$keyHead])) {
						$tmp[$keyHead] = $value[$keyHead];
					} else {
						$tmp[$keyHead] = $CI->ExpCsvModel->noValue;
					}
				}
				$CSVLIB->write($tmp);
			}
		} else {
			$CSVLIB->init($exportPath . $fileExportName, 'w');
			// в новом фале пишем заголовки
			$CSVLIB->createFileCSV($exportHeaders, $OUT);
			//$CSVLIB->write(array_values($exportHeaders));
			//$CSVLIB->write(array_keys($exportHeaders));
		}

		$totalCount = count($OUT) + $sqlOffset;
		$this->ajaxResponse['status'] = 'OK';
		$this->ajaxResponse['flag_update'] = 'CONTINUE';
		$this->ajaxResponse['info'] = 'Записано объектов: ' . $totalCount;
		$this->ajaxResponse['offset'] = $limitExport + $sqlOffset;
		$this->ajaxResponse['file_name'] = $fileExportName;


		/*foreach($OUT as $valueObj) {
			$tmp = array();
			foreach($exportHeaders as $keyField => $nameField) {
				if(isset($keyField))
			}
		}*/

		//SetOffset($line = 0, $lastLine = false)

		//$this->ajaxResponse['status'] = 'ERROR';
		//$this->ajaxResponse['info'] = $e->getMessage();

		//pr($oldPriceFields);
		//pr($emInfo);

		//pr($exportHeaders);
		//pr($OUT);
		//pr($OBJS);
	}


}
