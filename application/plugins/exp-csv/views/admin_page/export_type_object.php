<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1>Экспорт по типам объектов</h1>

<? require(__DIR__ . '/units/menu.php') ?>

<form action="" method="post" accept-charset="utf-8">

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
					'name' => 'active_data_types[]',
					'style' => 'height: 240px;'
					);

			   echo form_multiselect($args, $allDataFields);
			?>
		</div>
<div class="form-group">
	<span class="help-block sub-little-text" style="margin-bottom:12px;font-size:16px"><?= $exportInfo ?></span>
	<button type="submit" class="btn btn-primary ng-scope"><?= app_lang('EXPCSV_BUTTON_RUN_EXPORT') ?></button>
</div>
</form>
