<?php defined('BASEPATH') OR exit('No direct script access allowed');
$selected = '';
?>
<h1><?=$h1?></h1>

<? require_once(__DIR__ . '/units/menu.php'); ?>

<form action="" method="post">
<div class="col-inner-2">
	<div class="form-group">
		<label for="sinput1">Валюта, в которой отображается цена на сайте</label>
		<select class="form-control" name="active_currency_site" id="sinput1">
			<? foreach($currencyInfo['all_currency'] as $key => $value): ?>
			<? if($value['ecartcur_site']): ?>
				<option value="<?= $key ?>" selected><?= $value['ecartcur_code'] ?> [ID:<?= $key ?>] <?= $value['ecartcur_name'] ?></option>
			<? else: ?>
				<option value="<?= $key ?>"><?= $value['ecartcur_code'] ?> [ID:<?= $key ?>] <?= $value['ecartcur_name'] ?></option>
			<? endif ?>
			<? endforeach ?>
		</select>
		<span class="help-block sub-little-text">Валюта, в которой отображается цена на сайте</span>
	</div>

	<div class="form-group">
		<label for="sinput2">Валюта товаров</label>
		<select class="form-control" name="active_currency_products" id="sinput2">
			<? foreach($currencyInfo['all_currency'] as $key => $value): ?>
			<? if($value['ecartcur_products']): ?>
				<option value="<?= $key ?>" selected><?= $value['ecartcur_code'] ?> [ID:<?= $key ?>] <?= $value['ecartcur_name'] ?></option>
			<? else: ?>
				<option value="<?= $key ?>"><?= $value['ecartcur_code'] ?> [ID:<?= $key ?>] <?= $value['ecartcur_name'] ?></option>
			<? endif ?>
			<? endforeach ?>
		</select>
		<span class="help-block sub-little-text">Валюта товаров</span>
	</div>

	<div class="form-group">
		<label for="sinput3">ID поля с валютой</label>
		<input type="text" class="form-control" id="sinput3" name="emarket_option[currency_field_id]" value="<?= app_get_option('currency_field_id', 'e-market', 0); ?>">
		<span class="help-block sub-little-text">Если здесь указать ID data-поля, то это значение будет идентификатором валюты товара (ID валюты).</span>
	</div>

	<div class="form-group">
		<label for="sinput4">Правила округления цены</label>
		<select class="form-control" name="emarket_option[price_rules]" id="sinput4">
			<? foreach($allRulesPrice as $key => $value): ?>
			<? if($rulesPrice == $key): ?>
				<option value="<?= $key ?>" selected><?= $value ?></option>
			<? else: ?>
				<option value="<?= $key ?>"><?= $value ?></option>
			<? endif ?>
			<? endforeach ?>
		</select>
		<span class="help-block sub-little-text">По этому правилу будет выводиться цена товара</span>
	</div>


</div>

<div class="form-group" style="border-bottom:1px solid #f50000; padding-bottom:15px">
	<label for="sinput3">Стоимость валют</label>
		<? foreach($currencyInfo['all_currency'] as $key => $value): ?>
		<? if($value['ecartcur_site']): ?>
		<p>Валюта сайта: <?= $value['ecartcur_code'] ?> (<?= $value['ecartcur_name'] ?>). Цена 1</p>
		<input type="hidden" name="currency_rate[<?= $key ?>]" value="1" id="<?= $value['ecartcur_code'] ?>" />
		<? else: ?>
		<p>
			<p><?= $value['ecartcur_code'] ?> (<?= $value['ecartcur_name'] ?>).
			Сохраненный курс - <input type="text" name="currency_rate[<?= $key ?>]" value="<?= $value['ecartcur_rate'] ?>" id="<?= $value['ecartcur_code'] ?>" />
		</p>
		<? endif ?>
		<? endforeach ?>
</div>

<div style="text-align:right">
	<button type="submit" class="btn btn-primary ng-scope">Сохранить</button>
</div>

</form>
