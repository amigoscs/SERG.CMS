<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1><?= $h1 ?>. Всего: <?= $totalProducts ?></h1>

<? require_once(__DIR__ . '/units/menu.php'); ?>


<form action="" method="get" style="width: 100%;max-width: 800px;margin: 0 auto">
	<div class="col-inner-2">
	<div class="form-group" style="flex-basis: 100%;">
		<label for="sinput2">Поиск:</label>
		<input type="text" class="form-control" id="sinput2" name="filter[text]" value="<?= $getTextValue ?>">
		<span class="help-block sub-little-text">Для поиска по нескольким значениям используйте точку с запятой (text1; text2...)</span>
	</div>

		<div class="form-group">
			<label for="sgeninput10">Искать в:</label>
			<?
			$values = array('name' => 'Наименование');
			if($fieldSKU) {
				$values['isdata_' . $fieldSKU] = 'Артикул';
			}
			$args = array(
				'class' => 'form-control',
				'id' => 'sgeninput10',
				'name' => 'filter[field]',
				);

		   echo form_dropdown($args, $values, $getFieldValue);
			 ?>
		</div>
		<div class="form-group">
			<label for="sgeninput11">Тип объекта:</label>
			<?
			$args = array(
				'class' => 'form-control',
				'id' => 'sgeninput11',
				'name' => 'filter[type_object]',
				);

		   echo form_dropdown($args, $allTypeObject, $filterTypeObject);
			 ?>
		</div>

		<div class="form-group" style="flex-basis: 100%;">
			<label></label>
			<button type="submit" class="btn btn-success" style="width: 100%">Поиск</button>
		</div>
	</div>
</form>

<? if($products): ?>
<table class="product-list cart-info-table">
<thead>
	<tr>
		<th>Наименование</th>
		<th>Артикул</th>
		<th></th>
	</tr>
</thead>
<tbody>
<? $imageFieldID = app_get_option('product_image_field', 'to-cart', '0'); ?>
<? foreach($products as $key => $value):
	$PAGE = PAGEINIT($value);
	/*if($ElfinderModel){
		$ElfinderModel->load($PAGE->data($imageFieldID));
	}*/

	?>
	<tr class="status-<?= $PAGE->obj_status ?>">
		<td>
			<input id="inn<?= $PAGE->id ?>" type="checkbox" data-id="<?= $PAGE->id ?>"/>
			<label for="inn<?= $PAGE->id ?>"><?= $PAGE->name ?></label>
		</td>
		<td><?= $PAGE->data($fieldSKU) ?></td>
		<td>
			<a href="/admin/admin-page/edit?node_id=<?= $PAGE->nodeID ?>" target="_blank" class="btn btn-default" title="Редактировать объект в отдельном окне">Edit</a>
			<a href="<?= $PAGE->link ?>" target="_blank" class="btn btn-default" target="_blank" title="Посмотреть страницу на сайте">Open</a>
			<button type="button" name="change_status" class="btn btn-default" data-id="<?= $PAGE->id ?>" title="Изменить статус видимости"><span class="hidden">Скрыть</span><span class="publish">Опубликовать</span></button>
		</td>
	</tr>

<? endforeach ?>
</tbody>
</table>
<div class="button-row">
	<button type="button" name="checked_all" class="btn btn-default">Отметить все</button>
	<button type="button" name="unchecked_all" class="btn btn-default">Снять отметки</button>
	<button type="button" name="delete_objects" class="btn btn-danger">Удалить отмеченные</button>
</div>
<? else: ?>
<p>Элементов не найдено</p>
<? endif ?>

<? if($pagination): ?>
<div class="pagination">
<span class="pagination-title">Pages: <?= $pagination['totalPages'] ?></span>
<div class="pagination-pages">
<? if($pagination['firstLink']): ?>
	<a href="/<?= $pagination['firstLink']['link'] ?>" title=""><?= $pagination['firstLink']['name'] ?></a>
	<? endif ?>

	<? if($pagination['prevLink']): ?>
	<a href="/<?= $pagination['prevLink']['link'] ?>" title=""><?= $pagination['prevLink']['name'] ?></a>
	<? endif ?>
<? foreach($pagination['pages'] as $key => $value): ?>
	<? if($value['selected']): ?>
	<a href="/<?= $value['link'] ?>" title="Page <?= $value['name'] ?>" class="selected"><?= $value['name'] ?></a>
	<? else: ?>
	<a href="/<?= $value['link'] ?>" title="Page <?= $value['name'] ?>"><?= $value['name'] ?></a>
	<? endif ?>

<? endforeach ?>
	<? if($pagination['nextLink']): ?>
	<a href="/<?= $pagination['nextLink']['link'] ?>" title=""><?= $pagination['nextLink']['name'] ?></a>
	<? endif ?>

	<? if($pagination['lastLink']): ?>
	<a href="/<?= $pagination['lastLink']['link'] ?>" title=""><?= $pagination['lastLink']['name'] ?></a>
	<? endif ?>
</div>
</div>
<? endif ?>
<? //pr($pagination) ?>
<? //pr($products); ?>