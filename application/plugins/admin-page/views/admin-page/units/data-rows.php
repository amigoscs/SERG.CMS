<?php defined('BASEPATH') OR exit('No direct script access allowed');

foreach($objectDataTypes as $key => $value)
{
	// пустые data пропускаем
	if(!$value['fields']) {
		continue;
	}

	echo '<div class="col-inner-2 show-hide-switch" data-sh-id="apdata_' . $key . '">';
		echo '<h2>DATA: ' . $value['data_types_name'] . '</h2>';

		// вывод полей
		foreach($value['fields'] as $keyField => $optField)
		{
			$optField['types_fields_type'] == 'editor' ? $styleFGroup = ' style="flex-basis:100%"' : $styleFGroup = '';
			// значение поля. Если у объекта его нет, то ставим по умолчанию
			$valueField = $optField['types_fields_default'];
			if(isset($data_fields[$keyField])) {
				$valueField = $data_fields[$keyField]['objects_data_value'];
			}


			echo '<div class="form-group"' . $styleFGroup . '>';
				echo '<label for="data_field_' . $keyField . '">' . $optField['types_fields_name'] . '</label>';


				if($optField['types_fields_type'] == 'text') {
					// если поле ТЕКСТ
					echo '<input type="text" class="form-control" id="data_field_' . $keyField . '" name="data_field[' . $obj_id . '][' . $keyField . ']" value="' . $valueField . '"/>';

				} else if($optField['types_fields_type'] == 'textarea') {
					// если поле БОЛЬШОЙ ТЕКСТ
					echo '<textarea class="form-control" id="data_field_' . $keyField . '" name="data_field[' . $obj_id . '][' . $keyField . ']">' . $valueField . '</textarea>';

				} else if($optField['types_fields_type'] == 'select') {
					// если поле SELECT
					$options = app_parse_select_values($optField['types_fields_values']);
					$args = array(
							'class' => 'form-control',
							'id' => 'data_field_' . $keyField,
							'name' => 'data_field[' . $obj_id . '][' . $keyField . ']',
						);
					echo form_dropdown($args, $options, $valueField);

				} else if($optField['types_fields_type'] == 'image') {
					// если поле ИЗОБРАЖЕНИЕ
					echo '<input type="text" class="form-control field-image" id="data_field_' . $keyField . '" name="data_field[' . $obj_id . '][' . $keyField . ']" value="' . $valueField . '"/>';

				} else if($optField['types_fields_type'] == 'file') {
					// если поле ФАЙЛ
					echo '<input type="text" class="form-control field-image elf-file" id="data_field_' . $keyField . '" name="data_field[' . $obj_id . '][' . $keyField . ']" value="' . $valueField . '"/>';

				} else if($optField['types_fields_type'] == 'date') {
					// если поле ДАТА КАЛЕНДАРЯ
					echo '<input type="text" class="form-control field-date" id="data_field_' . $keyField . '" name="data_field[' . $obj_id . '][' . $keyField . ']" value="' . $valueField . '" data-date-format="' . app_get_option('date_format', 'general', 'YYYY-MM-DD') . '"/>';

				} else if($optField['types_fields_type'] == 'datetime') {
					// если поле ДАТА И ВРЕМЯ
					echo '<input type="text" class="form-control field-date fd-time" id="data_field_' . $keyField . '" name="data_field[' . $obj_id . '][' . $keyField . ']" value="' . $valueField . '" data-date-format="' . app_get_option('date_format', 'general', 'YYYY-MM-DD') . ' HH:mm:ss"/>';
				} else if($optField['types_fields_type'] == 'number') {
					// если поле ЧИСЛО
					echo '<input type="text" class="form-control field-number" id="data_field_' . $keyField . '" name="data_field[' . $obj_id . '][' . $keyField . ']" value="' . $valueField . '"/>';

				} else if($optField['types_fields_type'] == 'numberfloat') {
					// если поле ЧИСЛО ДРОБНОЕ
					echo '<input type="text" class="form-control field-numberfloat" id="data_field_' . $keyField . '" name="data_field[' . $obj_id . '][' . $keyField . ']" value="' . $valueField . '"/>';

				} else if($optField['types_fields_type'] == 'multiselect') {
					// если поле MULTI SELECT
					 	$valueField = explode(',', $valueField);
						$options = app_parse_select_values($optField['types_fields_values']);
						$args = array(
								'class' => 'form-control',
								'id' => 'data_field_' . $keyField,
								'name' => 'data_field[' . $obj_id . '][' . $keyField . '][]',
							);

						echo form_multiselect($args, $options, $valueField);

				} else if($optField['types_fields_type'] == 'editor') {
					echo '<textarea class="form-control editor data-rows-editor" id="data_field_' . $keyField . '" name="data_field[' . $obj_id . '][' . $keyField . ']">' . $valueField . '</textarea>';
				}

			echo '</div>';
		} // end foreach $value['fields']

	echo '</div>';
} // end foreach $objectDataTypes
