<?php defined('BASEPATH') OR exit('No direct script access allowed');
$selected = '';
$dateFormat = app_get_option('date_format', 'general', 'Y-m-d');
$dateFormatPHP = str_replace(array('YYYY', 'MM', 'DD'), array('Y', 'm', 'd'), $dateFormat);
?>
<h1><?= $h1 ?></h1>

<? require_once(__DIR__ . '/units/menu.php'); ?>

<div class="cart-filter-block">
<form action="" method="get">
	<div class="col-inner-2" style="width: 100%;max-width: 600px;margin: 0 auto">
		<div class="form-group">
			<label for="cartinput1">От</label>
			<input id="cartinput1" type="text" class="field-date" name="cart_start_date" value="<?= $filterDateStart ?>" data-date-format="<?= $dateFormat ?>"/>
		</div>
		<div class="form-group">
			<label for="cartinput2">До</label>
			<input id="cartinput2" type="text" class="field-date" name="cart_stop_date" value="<?= $filterDateStop ?>" data-date-format="<?= $dateFormat ?>"/>
		</div>
		<div class="form-group">
			<label for="select1">Статус заказа</label>
			<?
			$args = array(
				'class' => 'form-control',
				'id' => 'select1',
				'name' => 'cart_status',
				);
				$tmp = array(0 => 'Не учитывать');
				foreach($allStatus as $key => $value) {
					$tmp[$key] = $value['ecarts_name'];
				}
		   echo form_dropdown($args, $tmp, $filterCartStatus);
			 ?>
		</div>
		<div class="form-group">
			<label for="select2">Оплата</label>
			<?
			$args = array(
				'class' => 'form-control',
				'id' => 'select2',
				'name' => 'cart_cash_status',
				);
				$tmp = array(0 => 'Не учитывать');
				foreach($allCashStatus as $key => $value) {
					$tmp[$key] = $value;
				}
		   echo form_dropdown($args, $tmp, $filterCartCashStatus);
			 ?>
		</div>

		<div class="form-group">
			<label for="select3">Тип корзины</label>
			<?
			$args = array(
				'class' => 'form-control',
				'id' => 'select3',
				'name' => 'cart_type',
				);
				$tmp = array(0 => 'Не учитывать');
				foreach($allCartTypes as $key => $value) {
					$tmp[$key] = $value['ecarttypes_name'];
				}
		   echo form_dropdown($args, $tmp, $filterCartTypeCart);
			 ?>
		</div>
		<div class="form-group">
			<label>Выполнить поиск</label>
			<button type="submit" class="btn btn-success" style="width: 100%">Поиск</button>
		</div>
	</div>
</form>
</div>

<? if($orders): ?>
<table class="cart-info-table" cellpadding="0" cellspacing="0">
	<tr>
				<th>№ п/п</th>
				<th>Номер заказа</th>
				<th>Тип корзины</th>
				<th>Дата заказа</th>
				<th>Изменение</th>
				<th>Стоимость</th>
				<th>Товаров всего</th>
				<th>Статус</th>
				<th>Оплачено</th>
				<th>Заказчик</th>
				<th></th>
		</tr>
		<? $i = 0 ?>
		<? foreach($orders as $order): ?>
		 <? ++$i ?>
		<tr class="cash-status<?= $order['ecart_cash_status'] ?>">
			<td><?= $i ?></td>
			<td><?= $order['ecart_num'] ?></td>
			<td><?= $allCartTypes[$order['ecart_type']]['ecarttypes_name'] ?></td>
			<td><?= date_convert('d.m.Y в H:i:s', $order['ecart_date_order']) ?></td>
			<td><?= date($dateFormatPHP . ' в H:i:s', $order['ecart_last_mod']) ?></td>
			<td><?= $order['ecart_summ'] ?></td>
			<td><?= $order['products_count'] ?></td>
			<td>
				<?
				if(isset($allStatus[$order['ecart_status']])) {
					echo $allStatus[$order['ecart_status']]['ecarts_name'];
				} else {
					echo 'NA';
				}
				?>
				</td>
			<td>
				<?
				if($order['ecart_cash_status'] == 2){
					echo app_lang('EMARKET_VIEW_STATUS_CASH_ON');
				} else {
					echo app_lang('EMARKET_VIEW_STATUS_CASH_OFF');
				}
				?>
			</td>
			<td>
			<?
			if($order['user_info']){
				echo $order['user_info']['users_name'] . ' (' . $order['user_info']['users_login'] . ')';
			} else {
				echo 'No register';
			}
			?>
			</td>
			<td><a href="/admin/e-market/order_view?order=<?= $order['ecart_id'] ?>" class="btn btn-default"><?= app_lang('EMARKET_VIEW_GET_ORDER') ?></a></td>
		</tr>
		<? endforeach; ?>
</table>

<? else: ?>
<p style="text-align:center;font-size:32px;color:#666">Заказы не найдены</p>
<? endif ?>

<? //pr($orders) ?>
