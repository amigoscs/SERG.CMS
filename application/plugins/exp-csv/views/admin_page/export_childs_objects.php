<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1>Экспорт вложенных объектов</h1>

<? require(__DIR__ . '/units/menu.php') ?>

<? if(!$parentObject): ?>
<form action="" method="get" accept-charset="utf-8">
	<div class="form-group">
	<label for="sinput1">nodeID родительского объекта</label>
		<input type="text" class="form-control" id="sinput1" name="node_id" value="<?= $parentObject ?>">
	</div>
<div class="form-group">
	<button type="submit" class="btn btn-primary ng-scope"><?= app_lang('EXPCSV_BUTTON_SELECT_NODE') ?></button>
</div>
</form>
<? endif ?>

<? if($parentObject && !$exportObjects): ?>
<form action="" method="post" accept-charset="utf-8">
	<input type="hidden" name="start_export[nodeID]" value="<?= $parentObject ?>"/>
<div class="form-group">
	<span class="help-block sub-little-text" style="margin-bottom:12px;font-size:16px"><?= $exportInfo ?></span>
	<button type="submit" class="btn btn-primary ng-scope"><?= app_lang('EXPCSV_BUTTON_RUN_EXPORT') ?></button>
</div>
</form>
<? endif ?>



<? if($exportObjects) :
$outHeader = $outKeys = '';
// сформирекм заголовки. Сначала основные таблицы
foreach($objectsFields as $key => $value) {
	$outHeader .= '<th>' . $value['name'] . '</th>';
	$outKeys .= '<th>' . $key . '</th>';
}
// потом DATA-поля
foreach($dataTypesFields as $value) {
	$outHeader .= '<th>' . $value['types_fields_name'] . '</th>';
	$outKeys .= '<th>isdata_' . $value['types_fields_id'] . '</th>';
}
?>

<table class="simple-table">
	<thead>
		<tr>
		<?= $outHeader ?>
		</tr>
		<tr>
		<?= $outKeys ?>
		</tr>
	</thead>
	<tbody>
		<?
			$i = 0;
			$maxI = app_get_option("csv_count_prev", "exp-csv", "10");
		foreach($exportObjects as $value) {
			foreach($value as $object){
				++$i;
				if($i > $maxI) {
					break;
				}
				echo '<tr>';
				foreach($objectsFields as $key => $field) {
					echo '<td>' . $object[$key] . '</td>';
				}
				foreach($dataTypesFields as $field) {
					if(isset($object['data_fields'][$field['types_fields_id']])) {
						echo '<td>' . $object['data_fields'][$field['types_fields_id']]['objects_data_value'] . '</td>';
					} else {
						echo '<td>' . $noValue . '</td>';
					}
				}
				echo '</tr>';
			}
		}
		?>
	</tbody>
</table>
<form action="" method="post" accept-charset="utf-8">
	<input type="hidden" name="create_file" value="1"/>
<div class="form-group">
	<span class="help-block sub-little-text" style="margin-bottom:12px;font-size:16px"><?= $exportInfo ?></span>
	<button type="submit" class="btn btn-primary ng-scope"><?= app_lang('EXPCSV_BUTTON_RUN_EXPORT') ?></button>
</div>
</form>
<? endif; ?>
