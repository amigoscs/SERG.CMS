<?php defined('BASEPATH') OR exit('No direct script access allowed');
# файл helper плагина

/*
	*  библиотека корзины

	* UPD 2018-01-22
	* version 1.3
*/

/*
* возвращает пользовательскую форму

function emarket_user_form($data = array(), $formAction = '')
{
	$CI = &get_instance();
	$form = $CI->CartCommonModel->createCartForm($data, $formAction);
	return $form;
}*/


/*
* возвращает информацию о корзине

function emarket_info($type = 1)
{
	$CI = &get_instance();
	$cart = $CI->CartCommonModel->cartInfo(0, '', $type);
	return $cart;
}*/

/*
* возвращает количество товара в корзине

function emarket_count($type = 1, $key_count = 'count_products')
{
	$CI = &get_instance();
	$arr = $CI->CartCommonModel->cartInfo(0, '', $type);
	if($arr) {
		return $arr[$key_count];
	}else{
		return '0';
	}
}*/

/*
* возвращает стоимость корзины

function emarket_sum($type = 1)
{
	$CI = &get_instance();
	$arr = $CI->CartCommonModel->cartInfo(0, '', $type);
	if($arr) {
		return $arr['full_price'];
	}else{
		return '0';
	}
}*/

/*
* проверяет товар в корзине

function emarket_check_product($id = 0, $type = 1)
{
	$CI = &get_instance();
	return $CI->CartCommonModel->checkObjInCart(0, $id, $type);
}*/

/*
* формирует спичсок полей в админ панели
*/
function emarket_create_fields_list($fieldsArray = array(), $index = 0)
{
	$out = '';
	if(!isset($fieldsArray[$index]))
		return '';

	foreach($fieldsArray[$index] as $key => $value)
	{
		$out .= '<li data-id="' . $key . '" class="status-' . $value['ecartf_status'] . '" data-field-id="' . $key . '">';

		$out .= '<form action="" method="post">';

		$out .= '<div class="col-inner-2 show-hide-switch hide">';
		$out .= '<h2 class="s-handle">' . $value['ecartf_name'] . ' [ID=' . $key . ']</h2>';

		$selected = '';


		$out .= '<div class="form-group">';
			$out .= '<label for="">Название поля</label>';
			$out .= '<input type="text" name="updatefield[' . $key . '][name]" value="' . $value['ecartf_name'] . '" class="form-control">';
		$out .= '</div>';

		$out .= '<div class="form-group">';
			$out .= '<label for="">Внешнее название (label)</label>';
			$out .= '<input type="text" name="updatefield[' . $key . '][label]" value="' . $value['ecartf_label'] . '" class="form-control">';
		$out .= '</div>';

		$out .= '<div class="form-group">';
			$out .= '<label for="">Тип поля</label>';
			$out .= '<select name="updatefield[' . $key . '][type]" class="form-control">';

			$value['ecartf_type'] == 'text' ? $selected = ' selected' : $selected = '';
			$out .= '<option value="text"' . $selected . '>Строка</option>';

			$value['ecartf_type'] == 'textarea' ? $selected = ' selected' : $selected = '';
			$out .= '<option value="textarea"' . $selected . '>Текстовое поле</option>';

			$value['ecartf_type'] == 'select' ? $selected = ' selected' : $selected = '';
			$out .= '<option value="select"' . $selected . '>Выпадающий список</option>';

			$value['ecartf_type'] == 'checkbox' ? $selected = ' selected' : $selected = '';
			$out .= '<option value="checkbox"' . $selected . '>Чекбокс</option>';

			$value['ecartf_type'] == 'radio' ? $selected = ' selected' : $selected = '';
			$out .= '<option value="radio"' . $selected . '>Переключатель (radio)</option>';



			$out .= '</select>';

		$out .= '</div>';

		$out .= '<div class="form-group">';
			$out .= '<label for="">Видимость</label>';
			$out .= '<select name="updatefield[' . $key . '][status]" class="form-control">';
				$value['ecartf_status'] == 'publish' ? $selected = ' selected' : $selected = '';
				$out .= '<option value="publish"' . $selected . '>Видимый</option>';

				$value['ecartf_status'] == 'hidden' ? $selected = ' selected' : $selected = '';
				$out .= '<option value="hidden"' . $selected . '>Скрыт</option>';
			$out .= '</select>';
		$out .= '</div>';

		$out .= '<div class="form-group">';
			$out .= '<label for="">Обязательный</label>';
			$out .= '<select name="updatefield[' . $key . '][required]" class="form-control">';
				$value['ecartf_required'] == '0' ? $selected = ' selected' : $selected = '';
				$out .= '<option value="0"' . $selected . '>Нет</option>';

				$value['ecartf_required'] == '1' ? $selected = ' selected' : $selected = '';
				$out .= '<option value="1"' . $selected . '>Да</option>';
			$out .= '</select>';
		$out .= '</div>';

		$out .= '<div class="form-group">';
			$out .= '<label for="">ID родителя</label>';
			$out .= '<input type="text" name="updatefield[' . $key . '][parent]" value="' . $value['ecartf_parent'] . '" class="form-control">';
		$out .= '</div>';

		$out .= '<div class="form-group">';
			$out .= '<label for="">Сортировка</label>';
			$out .= '<input type="text" name="updatefield[' . $key . '][order]" value="' . $value['ecartf_order'] . '" class="form-control">';
		$out .= '</div>';

		$out .= '<div class="form-group">';
			$out .= '<button type="submit" class="btn btn-primary ng-scope">Сохранить</button>';
		$out .= '</div>';

		$out .= '</div>';
    	$out .= '</form>';
    	if(isset($fieldsArray[$key])) {
			$out .= '<ul>';
			$out .= emarket_create_fields_list($fieldsArray, $key);
			$out .= '</ul>';
		}
		$out .= '</li>';
	}
	return $out;
}

/*
* Конвертация цены товара в активную валюту

function emarket_price_convert($price, $currCode = 'USD')
{
	$CI = &get_instance();
	return $CI->CartOptsModel->convertPriceCurrency($price, $currCode);
}*/
