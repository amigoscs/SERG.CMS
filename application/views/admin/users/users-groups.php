<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<h1><?= app_lang('H1_USERS_EDIT_USERS_GROUPS') ?></h1>

<? require_once(__DIR__ . '/_menu.php'); ?>

<form action="" method="post">
<? foreach($users_groups as $key => $value): ?>
<? $key < 3 ? $disabled = 'disabled' : $disabled = '' ?>
<div class="col-inner-2 show-hide-switch hide">
	<h2><?= $value['users_group_name']?> [ID = <?=$key?>]</h2>
	<div class="form-group">
		<label for="input1<?=$key?>">Название группы</label>
		<input type="text" class="form-control" id="input1<?=$key?>" placeholder="Название группы" name="update_user_group[<?=$key?>][group_name]" value="<?= $value['users_group_name'] ?>" required <?= $disabled ?>>
    </div>

	<div class="form-group">
    <label for="select1<?=$key?>">Статус</label>
    <?
    $options = array(
		'publish'  => 'Активная',
		'hidden'  => 'Неактивная',
    );
    $args = array(
		'class' => 'form-control',
		'id' => 'select1' . $key,
		'name' => 'update_user_group[' . $key . '][group_status]',
    );
	if($disabled)
		$args['disabled'] = 'disabled';

    echo form_dropdown($args, $options, $value['users_group_status']);
    ?>
    </div>



		<div class="form-group">
	    <label for="select2">Тип группы</label>
	    <?

	    $args = array(
			'class' => 'form-control',
			'id' => 'select2',
			'name' => 'update_user_group[' . $key . '][group_type]',
	    );
			$options = app_get_option('panel_all_roles', 'general', array(
				'1' => 'Root user',
				'2' => 'Пользователи',
				'3'  => 'Администратор',
				'4'  => 'Редактор',
				'5'  => 'Модератор',
			));
			if($disabled){
				$args['disabled'] = 'disabled';
			} else {
				unset($options[1]);
			}

	    echo form_dropdown($args, $options, $value['users_group_type']);
	    ?>
	 </div>



    <div class="form-group">
		<label for="input1<?=$key?>">Описание группы</label>
		<textarea id="input1<?=$key?>" class="form-control" name="update_user_group[<?= $key ?>][group_descr]"><?= $value['users_group_descr'] ?></textarea>
    </div>
    <div class="form-group">
    	<button type="submit" class="btn btn-primary ng-scope"><?=$this->lang->line('button_save')?></button>
    	<? if(!$disabled): ?>
    	<button type="submit" name="delete_user_group[<?=$key?>]" value="1" class="btn btn-danger ng-scope" onClick="return confirm('Are u shure?')">Удалить группу</button>
    	<? endif ?>
	</div>


</div>
<? endforeach ?>
</form>

<form action="" method="post">
<div class="col-inner-2 show-hide-switch green hide">
	<h2>Новая группа пользователей</h2>
	<div class="form-group">
		<label for="input2">Название группы</label>
		<input type="text" class="form-control" id="input2" placeholder="Название группы" name="create_user_group[group_name]" value="" required>
    </div>

	<div class="form-group">
    <label for="select1">Статус</label>
    <?
    $options = array(
		'publish'  => 'Активная',
		'hidden'  => 'Неактивная',
    );
    $args = array(
		'class' => 'form-control',
		'id' => 'select1',
		'name' => 'create_user_group[group_status]',
    );
    echo form_dropdown($args, $options, 'publish');
    ?>
    </div>
		<div class="form-group">
	    <label for="select2">Тип группы</label>
	    <?
	    $options = array(
				'2' => 'Пользователи',
				'3'  => 'Администратор',
				'4'  => 'Редактор',
				'5'  => 'Модератор',
	    );
	    $args = array(
			'class' => 'form-control',
			'id' => 'select2',
			'name' => 'create_user_group[group_type]',
	    );
	    echo form_dropdown($args, $options, 1);
	    ?>
	    </div>
    <div class="form-group">
		<label for="input2">Описание группы</label>
		<textarea id="input2" class="form-control" name="create_user_group[group_descr]"></textarea>
    </div>
    <div class="form-group">
    	<button type="submit" class="btn btn-primary ng-scope"><?=$this->lang->line('button_save')?></button>
	</div>


</div>
</form>
