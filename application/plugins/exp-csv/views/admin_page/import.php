<?php defined('BASEPATH') OR exit('No direct script access allowed');

?>
<h1><?= $h1 ?></h1>

<? require(__DIR__ . '/units/menu.php') ?>

<? if($viewUploader): ?>
<form action="" method="post" enctype="multipart/form-data" accept-charset="utf-8">
<div class="form-group">
	<label for="input1"><?= app_lang('EXPCSV_FORM_LABEL_FILE_IMPORT') ?></label>
	<input type="file" id="input1" name="file_import"/>
	<span class="help-block sub-little-text"><?= app_lang('EXPCSV_INFO_MAX_FILE_SIZE') ?> 8 Mb. <?= app_lang('EXPCSV_INFO_FILE_ENCODING') ?> - «<?= $infoCharset ?>».</span>
	<span class="help-block sub-little-text">
		<?= app_lang('EXPCSV_INFO_DEL_FIELDS') ?> - « <?= $infoDelimiterField ?>».
		<?= app_lang('EXPCSV_INFO_ENCLOSURE') ?> - « <?= $infoEnclosure ?>» (<a href="/admin/setting_plugin/exp-csv"><?= app_lang('EXPCSV_INFO_CHANGE_SETTING') ?></a>)
	</span>
</div>
<div class="form-group">
	<button type="submit" class="btn btn-primary ng-scope"><?= app_lang('EXPCSV_BUTTON_LOAD_FILE') ?></button>
</div>
</form>
<? return ?>
<? endif ?>

<? if($import_info): ?>
<div class="adm-error"><?= $import_info ?></div>
<? endif ?>

<table class="simple-table">
	<tr>
	<? foreach($tableHeaders as $key => $value): ?>
		<th><?= $value ?></th>
	<? endforeach ?>
	</tr>
	<tr>
	<? foreach($tableHeaders as $key => $value): ?>
		<th><?= $key ?></th>
	<? endforeach ?>
	</tr>

	<? foreach($readResult as $row): ?>
	<tr>
		<? foreach($row as $value): ?>
		<td><?= $value ?></td>
		<? endforeach ?>
	</tr>
	<? endforeach ?>

</table>

<div class="form-group" style="margin-top:24px">
	<p>Предварительный просмотр. Показано <strong><?= $limitPreview ?></strong> строк.</p>
	<p style="margin-bottom:12px">Всего строк для обновления: <strong><?= $countAllRows ?></strong>. Файл будет разбит по <strong><?= app_get_option("csv_limit_import", "exp-csv", 300) ?></strong> строк. (<a href="/admin/setting_plugin/exp-csv"><?= app_lang('EXPCSV_INFO_CHANGE_SETTING') ?></a>)</p>
	<button id="run_import" data-file-path="<?= $pathFile ?>" class="btn btn-primary"><?= app_lang('EXPCSV_BUTTON_RUN_IMPORT') ?></button>
</div>
