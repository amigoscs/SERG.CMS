<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1><?= $h1 ?></h1>

<? require_once(__DIR__ . '/units/menu.php'); ?>
<?
$linkCheckModIP = str_replace('__IP__', $order['ecart_ip_mod'], app_get_option("e_market_ip_loc_url", "e-market", "//www.seogadget.ru/location?addr=__IP__"));
$linkCheckCreateIP = str_replace('__IP__', $order['ecart_ip_create'], app_get_option("e_market_ip_loc_url", "e-market", "//www.seogadget.ru/location?addr=__IP__"));

if(!$editOrder):
?>
	<p style="text-align: center;font-size: 2rem;color: #b20;margin-bottom: 24px;">Заказ находится на стадии наполнения</p>
<? endif ?>



<!--вводная информация-->
<div class="order-view-row-2col">
	<div class="ovr2c-title">Номер заказа</div>
    <div class="ovr2c-content"><?= $order['ecart_num'] ?></div>
</div>


<!--пользовательские данные-->
<? foreach($order['user_fields_values'] as $key => $value): ?>
<div class="order-view-row-2col">
	<div class="ovr2c-title"><?= $value['ecartfield_name'] ?></div>
	<div class="ovr2c-content"><?= $value['ecartfield_value'] ?></div>
</div>
<? endforeach; ?>


<!--состав заказа-->
<div class="order-view-row-2col">
	<div class="ovr2c-title">Состав заказа</div>
    <div class="ovr2c-content">
    <table class="cart-info-table" cellpadding="0" cellspacing="0">
    	<tr>
            <th>Наименование</th>
            <th>Код товара</th>
            <th>Количество</th>
            <th>Цена за шт.</th>
            <th>Стоимость</th>
        </tr>
        <? foreach($order['products'] as $key => $value): ?>
        <tr>
        	<td>
				<p><strong>
					<?
					if(isset($value['object']['path_url'])) {
						echo '<a href="' . $value['object']['path_url'] . '" title="Посмотреть на сайте" target="_blank">' . $value['ecartp_object_name'] . '</a>';
					} else {
						echo $value['ecartp_object_name'];
					}
					?>
					</strong></p>
				<p><?= $value['ecartp_descr'] ?></p>
			</td>
            <td><?= $value['ecartp_object_sku'] ?></td>
            <td><?= $value['ecartp_count'] ?></td>
            <td><?= $value['ecartp_price'] ?></td>
            <td><?= $value['ecartp_count'] * $value['ecartp_price'] ?></td>
        </tr>
        <? endforeach; ?>
    </table>
    </div>
</div>

<!--Сводная информация о заказе-->
<div class="order-view-row-2col" style="background-color:#c5e0f1">
	<div class="ovr2c-title"><strong>Стоимость</strong></div>
    <div class="ovr2c-content"><strong><?= app_format_num($order['ecart_summ']) ?></strong> <?= $order['ecart_currency_info']['ecartcur_code'] ?></div>
</div>

<div class="order-view-row-2col">
	<div class="ovr2c-title">Пользователь</div>
    <div class="ovr2c-content">
		<? if($order['user_info']): ?>
    		<?= $order['user_info']['users_name'] ?>, <?= $order['user_info']['users_login'] ?>, <?= $order['user_info']['users_email'] ?>
		<? else: ?>
            No register
        <? endif; ?>
    &nbsp;&nbsp;&nbsp;Оплачено заказов на сумму: <strong>0</strong>.
    </div>
</div>

<div class="order-view-row-2col">
	<div class="ovr2c-title">IP адреса</div>
    <div class="ovr2c-content">Последнее изменение: <strong><?= $order['ecart_ip_mod'] ?> </strong>(<a href="<?= $linkCheckModIP ?>" target="_blank">проверить</a>),
			Создание: <strong><?= $order['ecart_ip_create'] ?></strong>(<a href="<?= $linkCheckCreateIP ?>" target="_blank">проверить</a>)</div>
</div>

<div class="order-view-row-2col">
	<div class="ovr2c-title">Дата создания корзины</div>
    <div class="ovr2c-content"><?= date_convert('d.m.Y в H:i:s', $order['ecart_date_create']) ?></div>
</div>

<div class="order-view-row-2col">
	<div class="ovr2c-title"><strong>Дата создания заказа</strong></div>
    <div class="ovr2c-content"><?= date_convert('d.m.Y в H:i:s', $order['ecart_date_order']) ?></div>
</div>

<?
// для отправленных в заказ корзин добавляем возможность поменять статусы
if($editOrder):
?>
<div class="order-view-row-2col">
	<div class="ovr2c-title">Статус</div>
    <div class="ovr2c-content">
    <form action="" method="post">
		<?= form_dropdown(array('name' => 'cart_status', 'class' => 'dropdown-list-inline'), $allCartStatus, $order['ecart_status']) ?>
        <button type="submit" class="btn btn-success ng-scope" style="margin-left:12px">Присвоить статус</button>
    </form>
    </div>
</div>

<div class="order-view-row-2col">
	<div class="ovr2c-title">Статус оплаты заказа</div>
    <div class="ovr2c-content">
    <form action="" method="post">
		<?= form_dropdown(array('name' => 'cart_cash_status', 'class' => 'dropdown-list-inline'), $allCashStatus, $order['ecart_cash_status']) ?>
        <button type="submit" class="btn btn-success ng-scope" style="margin-left:12px">Присвоить статус</button>
    </form>
    </div>
</div>
<? endif; // and if($order['ecart_status'] > 1) ?>

<? //pr($order) ?>
