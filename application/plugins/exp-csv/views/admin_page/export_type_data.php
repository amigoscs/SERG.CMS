<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1><?= $h1 ?></h1>

<? require(__DIR__ . '/units/menu.php') ?>

<form action="" method="post" accept-charset="utf-8">
	<div class="col-inner-2">
		<div class="form-group">
			<label for="select1"><?= app_lang('EXPCSV_FORM_LABEL_ID_FIELD') ?></label>
			<?
				$args = array(
					'class' => 'form-control',
					'id' => 'select1',
					'name' => 'active_type',
					);

			   echo form_dropdown($args, $allTypesDataDropdown);
			?>
		</div>
	</div>
<div class="form-group">
	<span class="help-block sub-little-text" style="margin-bottom:12px;font-size:16px"><?= $exportInfo ?></span>
	<button type="submit" class="btn btn-primary ng-scope"><?= app_lang('EXPCSV_BUTTON_RUN_EXPORT') ?></button>
</div>
</form>
