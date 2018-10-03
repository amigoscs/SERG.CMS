<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1><?= $h1 ?></h1>

<? require_once(__DIR__ . '/units/menu.php'); ?>

<form action="" method="post" accept-charset="utf-8" id="export_price">

		<div class="form-group">
			<label for="select1">Выберите типы объектов для выгрузки</label>
			<?
				$args = array(
					'class' => 'form-control',
					'id' => 'select1',
					'name' => 'active_types[]',
					'style' => 'height: 240px;'
					);

			   echo form_multiselect($args, $allTypesObjects, 8);
			?>
		</div>


	<div class="form-group">
			<label for="select2">Поля для выгрузки</label>
			<?
				$args = array(
					'class' => 'form-control',
					'id' => 'select2',
					'name' => 'active_data_types_fields[]',
					'style' => 'height: 240px;'
					);

			   echo form_multiselect($args, $dataFieldsID, array_keys($dataFieldsID));
			?>
	</div>

<div class="form-group">
	<button type="submit" class="btn btn-primary ng-scope">Выгрузить объекты</button>
	<span class="help-block sub-little-text">Лимит выгрузки за один сеанс: <strong><?= $exportLimit ?></strong> строк.</span>
	<span class="help-block sub-little-text">
		Разделитель полей - «<?= $infoDelimiterField ?>».
		Ограничитель полей - «<?= $infoEnclosure ?>»
		(<a href="/admin/setting_plugin/exp-csv">Настройки</a>)
	</span>
</div>
</form>
