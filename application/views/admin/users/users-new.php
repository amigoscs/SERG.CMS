<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1><?= app_lang('H1_USERS_CREATE_USER') ?></h1>

<? require_once(__DIR__ . '/_menu.php'); ?>

<form action="" method="post">
<div class="col-inner-2">
	<div class="form-group">
			<label for="input01"><?= app_lang('INPUT_LABEL_USERS_LOGIN') ?></label>
			<input type="text" class="form-control" id="input01" placeholder="" name="usernew[login]" value="">
	</div>
	<div class="form-group">
			<label for="input02"><?= app_lang('INPUT_LABEL_USERS_NAME') ?></label>
			<input type="text" class="form-control" id="input02" placeholder="" name="usernew[name]" value="">
	</div>
	<div class="form-group">
			<label for="input03"><?= app_lang('INPUT_LABEL_USERS_IMAGE') ?></label>
			<input type="text" class="form-control field-image" id="input03" placeholder="" name="usernew[image]" value="">
	</div>
	<div class="form-group">
			<label for="input04"><?= app_lang('INPUT_LABEL_USERS_EMAIL') ?></label>
			<input type="text" class="form-control" id="input04" placeholder="" name="usernew[email]" value="">
	</div>
	<div class="form-group">
			<label for="input05"><?= app_lang('INPUT_LABEL_USERS_PHONE') ?></label>
			<input type="text" class="form-control" id="input05" placeholder="" name="usernew[phone]" value="">
	</div>

	<div class="form-group">
			<label for="input07"><?= app_lang('INPUT_LABEL_USERS_DATEBIRTH') ?></label>
			<input type="text" class="form-control field-date" id="input07" placeholder="" name="usernew[date_birth]" value="1980-12-30" data-date-format="DD.MM.YYYY">
	</div>

	<div class="form-group">
			<label for="select01"><?= app_lang('INPUT_LABEL_USERS_ACTIVE_USER') ?></label>
			<?
			$args = array('class' => 'form-control','id' => 'select01','name' => 'usernew[activate]');
			echo form_dropdown($args, array('0' => 'Нет', '1' => 'Да'), 0);
			?>
	</div>
	<div class="form-group">
			<label for="select02"><?= app_lang('INPUT_LABEL_USERS_STATUS_USER') ?></label>
			<?
			$args = array('class' => 'form-control','id' => 'select02','name' => 'usernew[status]');
			echo form_dropdown($args, array('hidden' => app_lang('INPUT_SELECT_USERS_VALUE_HIDDEN'), 'publish' => app_lang('INPUT_SELECT_USERS_VALUE_VISIBLE')), 'publish');
			?>
	</div>

	<div class="form-group">
			<label for="select03"><?= app_lang('INPUT_LABEL_USERS_GROUP_USER') ?></label>
			<?
			$uGroups = array();
			foreach($users_groups as $value) {
				$uGroups[$value['users_group_id']] = $value['users_group_name'];
			}

			$args = array('class' => 'form-control','id' => 'select03','name' => 'usernew[group]');
			echo form_dropdown($args, $uGroups, 1);
			?>
	</div>

	<div class="form-group">
			<label for="select04"><?= app_lang('INPUT_LABEL_USERS_LANG') ?></label>
			<?
			$args = array('class' => 'form-control','id' => 'select04','name' => 'usernew[lang]');
			echo form_dropdown($args, app_all_lang());
			?>
	</div>

	<div class="form-group">
			<label for="input12"><?= app_lang('INPUT_LABEL_USERS_NEW_PASSWORD') ?></label>
			<input type="text" class="form-control" id="input12" placeholder="" name="usernew[password]" value="">
	</div>
	</div>
<button type="submit" class="btn btn-primary ng-scope"><?=$this->lang->line('button_save')?></button>
</form>
