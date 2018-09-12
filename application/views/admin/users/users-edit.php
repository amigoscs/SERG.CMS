<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<h1><?= app_lang('H1_USERS_EDIT_USER') ?></h1>

<? require_once(__DIR__ . '/_menu.php'); ?>


<p><?= app_lang('INPUT_LABEL_USERS_LAST_VIZIT') ?>: <?= date('Y-m-d | H:i:s', $user['users_last_visit']) ?></p>

<form action="" method="post">
<div class="col-inner-2">
	<div class="form-group">
			<label for="input01"><?= app_lang('INPUT_LABEL_USERS_LOGIN') ?></label>
			<input type="text" class="form-control" id="input01" placeholder="" name="userinfo[login]" value="<?=$user['users_login']?>">
	</div>
	<div class="form-group">
			<label for="input02"><?= app_lang('INPUT_LABEL_USERS_NAME') ?></label>
			<input type="text" class="form-control" id="input02" placeholder="" name="userinfo[name]" value="<?=$user['users_name']?>">
	</div>
	<div class="form-group">
			<label for="input03"><?= app_lang('INPUT_LABEL_USERS_IMAGE') ?></label>
			<input type="text" class="form-control field-image" id="input03" placeholder="" name="userinfo[image]" value="<?=$user['users_image']?>">
	</div>
	<div class="form-group">
			<label for="input04"><?= app_lang('INPUT_LABEL_USERS_EMAIL') ?></label>
			<input type="text" class="form-control" id="input04" placeholder="" name="userinfo[email]" value="<?=$user['users_email']?>">
	</div>
	<div class="form-group">
			<label for="input05"><?= app_lang('INPUT_LABEL_USERS_PHONE') ?></label>
			<input type="text" class="form-control" id="input05" placeholder="" name="userinfo[phone]" value="<?=$user['users_phone']?>">
	</div>
	<div class="form-group">
			<label for="input06"><?= app_lang('INPUT_LABEL_USERS_DATEREGISTER') ?></label>
			<input type="text" class="form-control" id="input06" placeholder="" name="userinfo[date_registr]" value="<?=$user['users_date_registr']?>" readonly>
	</div>
	<div class="form-group">
			<label for="input07"><?= app_lang('INPUT_LABEL_USERS_DATEBIRTH') ?></label>
			<input type="text" class="form-control field-date" id="input07" placeholder="" name="userinfo[date_birth]" value="<?=$user['users_date_birth']?>" data-date-format="DD.MM.YYYY">
	</div>

	<div class="form-group">
			<label for="input09"><?= app_lang('INPUT_LABEL_USERS_IP_REGISTER') ?></label>
			<input type="text" class="form-control" id="input09" placeholder="" name="userinfo[ip_register]" value="<?=$user['users_ip_register']?>">
	</div>
	<div class="form-group">
			<label for="input10"><?= app_lang('INPUT_LABEL_USERS_CODE_ACTIVE') ?></label>
			<input type="text" class="form-control" id="input10" placeholder="" name="userinfo[activate_key]" value="<?=$user['users_activate_key']?>">
	</div>
	<div class="form-group">
			<label for="input11"><?= app_lang('INPUT_LABEL_USERS_KEY_SITE') ?></label>
			<input type="text" class="form-control" id="input11" placeholder="" name="userinfo[site_key]" value="<?=$user['users_site_key']?>">
	</div>
	<div class="form-group">
			<label for="select01"><?= app_lang('INPUT_LABEL_USERS_ACTIVE_USER') ?></label>
			<?
			$args = array('class' => 'form-control','id' => 'select01','name' => 'userinfo[activate]');
			echo form_dropdown($args, array('0' => 'No', '1' => 'Yes'), $user['users_activate']);
			?>
	</div>
	<div class="form-group">
			<label for="select02"><?= app_lang('INPUT_LABEL_USERS_STATUS_USER') ?></label>
			<?
			$args = array('class' => 'form-control','id' => 'select02','name' => 'userinfo[status]');
			echo form_dropdown($args, array('hidden' => app_lang('INPUT_SELECT_USERS_VALUE_HIDDEN'), 'publish' => app_lang('INPUT_SELECT_USERS_VALUE_VISIBLE')), $user['users_status']);
			?>
	</div>


	<div class="form-group">
			<label for="select03"><?= app_lang('INPUT_LABEL_USERS_GROUP_USER') ?></label>
			<?
			$uGroups = array();
			foreach($users_groups as $value) {
				$uGroups[$value['users_group_id']] = $value['users_group_name'];
			}

			$args = array('class' => 'form-control','id' => 'select03','name' => 'userinfo[group]');
			echo form_dropdown($args, $uGroups, $user['users_group']);
			?>
	</div>

	<div class="form-group">
			<label for="select04"><?= app_lang('INPUT_LABEL_USERS_LANG') ?></label>
			<?
			$args = array('class' => 'form-control','id' => 'select04','name' => 'userinfo[lang]');
			echo form_dropdown($args, app_all_lang(), $user['users_lang']);
			?>
	</div>
</div>

<div class="form-group">
     	<label for="input12"><?= app_lang('INPUT_LABEL_USERS_NEW_PASSWORD') ?></label>
		<input type="text" class="form-control" id="input12" placeholder="" name="userinfo[new_password]" value="">
</div>

<button type="submit" class="btn btn-primary ng-scope"><?= app_lang('button_save') ?></button>
</form>
