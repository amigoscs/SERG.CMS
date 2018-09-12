<?php defined('BASEPATH') OR exit('No direct script access allowed');
 ######################
 # Форма для полей формы
 ######################

?>
<h1><?=$h1?></h1>

<? require_once(__DIR__ . '/units/menu.php'); ?>




<ul class="tc-list-ul-fields list-draggable" data-sort-table="ecart_fields" data-sort-field="ecartf_order">
<?= emarket_create_fields_list($fields) ?>
</ul>

<form action="" method="post">
	<div class="col-inner-2 show-hide-switch hide">
		<h2>Создать новое поле</h2>
		<div class="form-group">
			<label for="sinput1">Название нового поля</label>
       		<input type="text" class="form-control" id="sinput1" name="newfield[0][name]" value="">
		</div>
		<div class="form-group">
			<label for="sinput2">Внешнее имя (label)</label>
       		<input type="text" class="form-control" id="sinput2" name="newfield[0][label]" value="">
		</div>
		<div class="form-group">
			<label for="sinput3">Тип нового поля</label>
			<select class="form-control" name="newfield[0][type]" id="sinput3">
				<option value="text">Строка</option>
				<option value="textarea">Текстовое поле</option>
				<option value="select">Выпадающий список</option>
				<option value="checkbox">Чекбокс</option>
				<option value="radio">Переключатель (radio)</option>
       		</select>
		</div>
		<div class="form-group">
			<label for="sinput4">Обязательный</label>
		   	<select class="form-control" name="newfield[0][required]" id="sinput4">
				<option value="0">Да</option>
				<option value="1">Нет</option>
		   	</select>
		</div>
		<div class="form-group">
			<label for="sinput5">Родитель нового поля</label>
		   <select class="form-control" name="newfield[0][parent]" id="sinput5">
				<option value="0">---Нет родителя</option>
				<? foreach($fields as $key => $value): ?>
					<? foreach($value as $fff): ?>
					<option value="<?= $fff['ecartf_id'] ?>"><?= $fff['ecartf_name'] ?></option>
					<? endforeach ?>
				<? endforeach ?>
		   </select>
		</div>
		<div class="form-group">
			<label for="sinput6">Порядок</label>
       		<input type="text" class="form-control" id="sinput6" name="newfield[0][order]" value="0">
		</div>
		<div class="form-group no-label">
			<button type="submit" class="btn btn-primary ng-scope">Создать поле</button>
		</div>
	</div>


</form>

<? //pr($fields); ?>
