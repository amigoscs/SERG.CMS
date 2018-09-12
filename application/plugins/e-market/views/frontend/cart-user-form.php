<?php defined('BASEPATH') OR exit('No direct script access allowed');
# шаблон формы заказа
$elemValue = '';
# формируем форму
echo '<div class="cart-user-form"><form action="' . $formAction .'" method="post" id="emarket_order_form">';
foreach($formFields[0] as $key => $value)
{
	$value['ecartf_required'] ? $required = 'required' : $required = '';
	echo '<div class="form-row line-' . $value['ecartf_type'] . '" id="formrow_'. $key . '">';
	isset($cartfield[$key]) ? $elemValue = $cartfield[$key] : $elemValue = '';

	if($value['ecartf_type'] == 'text'){
		// input text
		echo '<input type="text" id="crtd' . $key . '" name="cartfield[' . $value['ecartf_id'] . ']" value="' . $elemValue . '" onkeydown="if(event.keyCode==13){return false;}" ' . $required . '/>';

	}elseif($value['ecartf_type'] == 'textarea'){
		// textarea
		echo '<textarea id="crtd' . $key . '" name="cartfield[' . $value['ecartf_id'] . ']" ' . $required . '>' . $elemValue . '</textarea>';

	}elseif($value['ecartf_type'] == 'select'){
		// selected
		echo '<select id="crtd' . $key . '" name="cartfield[' . $value['ecartf_id'] . ']" ' . $required . '>';
		// у select должны быть дети
		if(isset($formFields[$key]))
		{
			foreach($formFields[$key] as $chKey => $chValue){
				if(isset($cartfield[$key]) && $cartfield[$key] == $chKey){
					echo '<option value="' . $chValue['ecartf_id'] . '" selected>' . $chValue['ecartf_label'] . '</option>';
				}else{
					echo '<option value="' . $chValue['ecartf_id'] . '">' . $chValue['ecartf_label'] . '</option>';
				}
			}
		}
		echo '</select>';

	}elseif($value['ecartf_type'] == 'checkbox'){
		// checkbox
		isset($cartfield[$key]) ? $checked = 'checked' : $checked = '';
		echo '<input type="checkbox" id="crtd' . $key . '" name="cartfield[' . $value['ecartf_id'] . ']" value="1" ' . $required . ' ' . $checked . '/>';

	}elseif($value['ecartf_type'] == 'radio'){
		// radio
		if(isset($formFields[$key]))
		{
			$i = 0;
			foreach($formFields[$key] as $chKey => $chValue){
				echo '<p>';
				if(isset($cartfield[$key]) && $cartfield[$key] == $chKey){
					echo '<input id="radio' . $chKey . $i . '" type="radio" name="cartfield[' . $value['ecartf_id'] . ']" value="' . $chValue['ecartf_id'] . '" checked>';
				}else if($i == 0){
					echo '<input id="radio' . $chKey . $i . '" type="radio" name="cartfield[' . $value['ecartf_id'] . ']" value="' . $chValue['ecartf_id'] . '" checked>';
				}else{
					echo '<input id="radio' . $chKey . $i . '" type="radio" name="cartfield[' . $value['ecartf_id'] . ']" value="' . $chValue['ecartf_id'] . '">';
				}

				echo '<label class="form-radio" for="radio' . $chKey . $i . '">';
					echo $chValue['ecartf_label'];
				echo '</label>';
				echo '</p>';
				++$i;
			}
		}
	}

	echo '<label for="crtd' . $key . '" class="label-title">' . $value['ecartf_label'] . '</label>';
	echo '</div>';

}# end form foreach


	echo '<div class="form-row row-submit">';
	echo '<button type="submit" class="btn btn-success" name="cart_send_order" value="1">Оформить заказ</button>';
	echo '</div>';

	echo '<p style="text-align:left;font-size:16px;color:#b20">';
	echo 'Указывая свои персональные данные и оформляя заказ на сайте, вы даете согласие на обработку ваших данных. <a href="#" title="Пользовательское соглашение" target="_blank">Подробнее »</a>';
	echo '</p>';

echo '</form></div>';
?>
