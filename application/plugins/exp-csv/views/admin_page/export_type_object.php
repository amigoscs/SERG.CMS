<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1>Экспорт по типам объектов</h1>

<? require(__DIR__ . '/units/menu.php') ?>

<form action="" method="post" accept-charset="utf-8" id="export_type">

		<div class="form-group">
			<label for="select1"><?= app_lang('EXPCSV_FORM_LABEL_TYPE_OBJECT') ?></label>
			<?
				$args = array(
					'class' => 'form-control',
					'id' => 'select1',
					'name' => 'active_types[]',
					'style' => 'height: 240px;'
					);

			   echo form_multiselect($args, $allTypesObjects, 1);
			?>
		</div>


	<div class="form-group">
			<label for="select2"><?= app_lang('EXPCSV_FORM_LABEL_TYPE_FIELDS') ?></label>
			<?
				$args = array(
					'class' => 'form-control',
					'id' => 'select2',
					'name' => 'active_data_types_fields[]',
					'style' => 'height: 240px;'
					);

			   echo form_multiselect($args, $allDataFields);
			?>
	</div>

	<div class="form-group">
			<label for="select3">Тип объекта в дереве</label>
			<?
				$args = array(
					'class' => 'form-control',
					'id' => 'select3',
					'name' => 'active_obj_type',
					);

			   echo form_dropdown($args, $allObjTypes);
			?>
	</div>

<div class="form-group">
	<button type="submit" class="btn btn-primary ng-scope"><?= app_lang('EXPCSV_BUTTON_RUN_EXPORT') ?></button>
	<span class="help-block sub-little-text">Лимит выгрузки за один сеанс: <strong><?= $exportLimit ?></strong> строк.</span>
	<span class="help-block sub-little-text">
		<?= app_lang('EXPCSV_INFO_DEL_FIELDS') ?> - «<?= $infoDelimiterField ?>».
		<?= app_lang('EXPCSV_INFO_ENCLOSURE') ?> - «<?= $infoEnclosure ?>»
		(<a href="/admin/setting_plugin/exp-csv"><?= app_lang('EXPCSV_INFO_CHANGE_SETTING') ?></a>)
	</span>
</div>
</form>
