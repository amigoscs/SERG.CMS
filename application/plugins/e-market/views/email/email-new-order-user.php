<?php defined('BASEPATH') OR exit('No direct script access allowed');
# шаблон письма покупателю о новом заказе
?>
<table width="680" style="margin:0;padding:0;border:1px solid #DDDDDD;font:inherit;vertical-align:baseline;border-collapse:collapse;border-spacing:0;margin-bottom: 24px">
	<thead>
		<tr>
			<th colspan="2" style="background: #EFEFEF;text-align: left;border-bottom:1px solid #DDDDDD;padding: 8px">Информация о заказе</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style="text-align: left; padding: 8px;border-right:1px solid #DDDDDD;width: 339px">
				<strong>Заказ:</strong> <?= $userCart['ecart_num'] ?><br>
				<strong>Дата заказа:</strong> <?= $userCart['ecart_date_order'] ?><br />
				<strong>IP:</strong> <?= $userCart['ecart_ip_mod'] ?><br />
			</td>
			<td style="text-align: left; padding: 8px">
			<? foreach($userCart['user_fields_values'] as $value): ?>
				<strong><?= $value['ecartfield_name'] ?>:</strong> <?= $value['ecartfield_value'] ?><br />
			<? endforeach ?>
			</td>
		</tr>
	</tbody>
</table>

<table width="680" style="margin:0;padding:0;border:1px solid #DDDDDD;font:inherit;vertical-align:baseline;border-collapse:collapse;border-spacing:0;margin-bottom: 24px">
	<thead>
		<tr>
			<th style="background: #EFEFEF;text-align: left;border-bottom:1px solid #DDDDDD;padding: 8px">Инструкции</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style="text-align: left; padding: 8px;border-right:1px solid #DDDDDD;width: 339px">
				Заказ отправлен в обработку. После обработки заказа с вами свяжется менеджер.
			</td>
		</tr>
	</tbody>
</table>

<table width="680" style="margin:0;padding:0;border:1px solid #DDDDDD;font:inherit;vertical-align:baseline;border-collapse:collapse;border-spacing:0;margin-bottom: 24px">
	<thead>
		<tr style="border-bottom: 1px solid #DDDDDD;background: #EFEFEF;">
			<th style="text-align: left;padding: 8px">Товар</th>
			<th style="text-align: left;padding: 8px">Код товара</th>
			<th style="text-align: center;padding: 8px">Количество</th>
			<th style="text-align: center;padding: 8px">Цена за ед, <?= $userCart['ecart_currency_info']['ecartcur_code'] ?></th>
			<th style="text-align: center;padding: 8px">Итого, <?= $userCart['ecart_currency_info']['ecartcur_code'] ?></th>
		</tr>
	</thead>
	<tbody>
	<?
	$allPrice = array();
	foreach($userCart['products'] as $value):
		$itemPrice = $value['ecartp_price'] * $value['ecartp_count'];
		?>
		<tr style="border-bottom: 1px solid #DDDDDD;">
			<td style="text-align: left; padding: 8px;border-right:1px solid #DDDDDD;">
				<p><?= $value['ecartp_object_name'] ?></p>
				<p style="font-size: 10px"><?= $value['ecartp_descr'] ?></p>
			</td>
			<td style="text-align: center; padding: 8px;border-right:1px solid #DDDDDD;width: 100px"><?= $value['ecartp_object_sku'] ?></td>
			<td style="text-align: center; padding: 8px;border-right:1px solid #DDDDDD;width: 100px">
				<?= $value['ecartp_count'] ?>
			</td>
			<td style="text-align: center; padding: 8px;border-right:1px solid #DDDDDD;width: 100px">
				<?= $value['ecartp_price'] ?>
			</td>
			<td style="text-align: center; padding: 8px;width: 100px">
				<?= $itemPrice ?>
			</td>
		</tr>
	<? endforeach ?>
	<tr>
		<td colspan="4" style="text-align: right">Итого</td>
		<td style="text-align: center; font-weight: 700"><?= $userCart['ecart_summ'] ?> <?= $userCart['ecart_currency_info']['ecartcur_code'] ?></td>
	</tbody>
</table>
