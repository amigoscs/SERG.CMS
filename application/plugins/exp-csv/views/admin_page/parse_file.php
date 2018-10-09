<?php defined('BASEPATH') OR exit('No direct script access allowed');

?>
<h1><?= $h1 ?></h1>

<? require(__DIR__ . '/units/menu.php') ?>

<? if($viewUploader): ?>
<form action="" method="post" enctype="multipart/form-data" accept-charset="utf-8">
<div class="form-group">
	<label for="input1"><?= app_lang('EXPCSV_FORM_LABEL_FILE_PARSE') ?></label>
	<input type="file" id="input1" name="file_import"/>
	<span class="help-block sub-little-text"><?= app_lang('EXPCSV_INFO_MAX_FILE_SIZE') ?> 8 Mb. <?= app_lang('EXPCSV_INFO_FILE_ENCODING') ?> - «<?= $infoCharset ?>».</span>
	<span class="help-block sub-little-text">
		<?= app_lang('EXPCSV_INFO_DEL_FIELDS') ?> - « <?= $infoDelimiterField ?> ».
		<?= app_lang('EXPCSV_INFO_ENCLOSURE') ?> - « <?= $infoEnclosure ?> » (<a href="/admin/setting_plugin/exp-csv"><?= app_lang('EXPCSV_INFO_CHANGE_SETTING') ?></a>)
	</span>
</div>
<div class="form-group">
	<button type="submit" class="btn btn-primary ng-scope"><?= app_lang('EXPCSV_BUTTON_LOAD_FILE') ?></button>
</div>
</form>
<p>Парсер файла предназначен для удаления перносов строк в ячейках файла CSV.</p>
<? return ?>
<? endif ?>

<? if($fileDownload): ?>
	<p>Файл сформирован! <a href="<?= $fileDownload ?>" title="" download>Скачать файл</a></p>
<? endif ?>
