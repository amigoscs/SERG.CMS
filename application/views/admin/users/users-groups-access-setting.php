<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<h1><?= app_lang('H1_USERS_USERS_GROUPS_ROLES_SETTING') ?></h1>

<? require_once(__DIR__ . '/_menu.php'); ?>

<form action="" method="POST">
  <? foreach($all_roles as $key => $value): ?>
  <div class="col-inner-2">
    <div class="form-group">
  	<label for="input<?=$key?>"><?= app_lang('INPUT_LABEL_USERS_ID_ROLE') ?></label>
  	<input type="text" class="form-control" id="input1<?=$key?>" placeholder="ID типа" name="update_user_group_access_type[<?=$key?>][type_id]" value="<?= $key ?>" readonly>
    </div>
    <div class="form-group">
  	<label for="input<?=$key?>"><?= app_lang('INPUT_LABEL_USERS_NAME_ROLE') ?></label>
  	<input type="text" class="form-control" id="input1<?=$key?>" placeholder="Название типа<" name="update_user_group_access_type[<?=$key?>][type_name]" value="<?= $value ?>" required>
    </div>
  </div>
  <? endforeach ?>
<button type="submit" class="btn btn-primary"><?= app_lang('button_save') ?></button>
</form>
