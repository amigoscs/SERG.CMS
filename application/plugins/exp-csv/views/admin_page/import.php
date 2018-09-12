<?php defined('BASEPATH') OR exit('No direct script access allowed');

?>
<h1><?= $h1 ?></h1>

<? require(__DIR__ . '/units/menu.php') ?>

<? if($viewUploader): ?>
<form action="" method="post" enctype="multipart/form-data" accept-charset="utf-8">
<div class="form-group">
	<label for="input1"><?= app_lang('EXPCSV_FORM_LABEL_FILE_IMPORT') ?></label>
	<input type="file" id="input1" name="file_import"/>
	<span class="help-block sub-little-text"><?= $importInfo ?></span>
</div>
<div class="form-group">
	<button type="submit" class="btn btn-primary ng-scope"><?= app_lang('EXPCSV_BUTTON_LOAD_FILE') ?></button>
</div>
</form>
<? endif ?>


<?
echo $read_file;


echo $button_start;

?>
