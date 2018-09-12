<?php defined('BASEPATH') OR exit('No direct script access allowed');
 ######################
 # Форма для типов корзин
 ######################
?>
<h1><?=$h1?></h1>

<? require_once(__DIR__ . '/units/menu.php'); ?>

<form action="" method="post">
<? foreach($carts as $key => $value): ?>
	<div class="col-inner-2 show-hide-switch hide">
		<h2><?= $value['ecarttypes_name'] ?></h2>
		<div class="form-group">
			<label for="sinput1">Название типа</label>
			<input type="text" class="form-control" id="sinput1" name="editcart[<?=$key?>][name]" value="<?= $value['ecarttypes_name'] ?>">
		</div>
		<div class="form-group">
			<label for="sinput2">Описание типа</label>
			<input type="text" class="form-control" id="sinput2" name="editcart[<?=$key?>][descr]" value="<?=$value['ecarttypes_descr']?>">
		</div>
		<div class="form-group">
			<input type="radio" id="sinput3<?=$key?>" name="editcart[<?=$key?>][status]" value="publish" <? if($value['ecarttypes_status'] == 'publish') echo 'checked'; ?>>
			<label for="sinput3<?=$key?>">Видимый</label>
			<input type="radio" id="sinput4<?=$key?>" name="editcart[<?=$key?>][status]" value="hidden" <? if($value['ecarttypes_status'] == 'hidden') echo 'checked'; ?>>
			<label for="sinput4<?=$key?>">Скрыт</label>
		</div>
		<div class="form-group">
			 <button type="submit" class="btn btn-primary ng-scope">Сохранить изменения</button>
		</div>
	</div>
<? endforeach; ?>
<? $nextKey = $key + 1; ?>
</form>


<form action="" method="post">
	<div class="form-group">
			 <label for="newsinput1">Название нового типа корзин</label>
			 <input type="text" class="form-control" id="sinput1" name="newcart[<?=$nextKey?>][name]" value="">

			 <label for="newsinput2">Описание нового типа корзин</label>
			 <input type="text" class="form-control" id="sinput2" name="newcart[<?=$nextKey?>][descr]" value="">
	</div>
	<div>
	 <button type="submit" class="btn btn-primary ng-scope">Создать новую корзину</button>
</form>


<hr>

<form action="" method="post">
<? foreach($statusCarts as $key => $value): ?>
	<div class="col-inner-2 show-hide-switch hide">
		<h2><?= $value['ecarts_name'] ?></h2>
		<div class="form-group">
			<label for="sinput1">Название статуса</label>
			<input type="text" class="form-control" id="sinput1" name="editcartstatus[<?=$key?>][name]" value="<?= $value['ecarts_name'] ?>">
		</div>
		<div class="form-group">
			<label for="sinput2">Описание статуса</label>
			<input type="text" class="form-control" id="sinput2" name="editcartstatus[<?=$key?>][descr]" value="<?=$value['ecarts_descr']?>">
		</div>
		<div class="form-group">
			<input type="radio" id="sinput3<?=$key?>" name="editcartstatus[<?=$key?>][status]" value="publish" <? if($value['ecarts_status'] == 'publish') echo 'checked'; ?>>
			<label for="sinput3<?=$key?>">Видимый</label>
			<input type="radio" id="sinput4<?=$key?>" name="editcartstatus[<?=$key?>][status]" value="hidden" <? if($value['ecarts_status'] == 'hidden') echo 'checked'; ?>>
			<label for="sinput4<?=$key?>">Скрыт</label>
		</div>
		<div class="form-group">
			 <button type="submit" class="btn btn-primary ng-scope">Сохранить изменения</button>
		</div>
	</div>
<? endforeach; ?>
<? $nextKey = $key + 1; ?>
</form>


<form action="" method="post">
	<div class="form-group">
			 <label for="newsinput1">Название нового статуса</label>
			 <input type="text" class="form-control" id="sinput1" name="newcartstatus[<?=$nextKey?>][name]" value="">

			 <label for="newsinput2">Описание нового статуса</label>
			 <input type="text" class="form-control" id="sinput2" name="newcartstatus[<?=$nextKey?>][descr]" value="">
	</div>
	<div>
	 <button type="submit" class="btn btn-primary ng-scope">Создать новый статус</button>
</form>
