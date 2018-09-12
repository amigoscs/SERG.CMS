<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1>Экспорт объектов</h1>
<? require(__DIR__ . '/units/menu.php') ?>
<table class="simple-table">
<?
$outHeader = $outKeys = '';
foreach($fields as $keyField => $value){
	$outHeader .= '<th>' . $value['name'] . '</th>';
	$outKeys .= '<th>' . $keyField . '</th>';
}
?>
<tr>
<?= $outHeader ?>
</tr>
<tr>
<?= $outKeys ?>
</tr>
<?
foreach($objects as $objValue)
{
	echo '<tr>';
		foreach($fields as $keyField => $value) {
			$pos = strpos($keyField, 'isdata');
			if($pos === FALSE)
			{
				echo '<td>';
				if($value['table'] == 'tree')
				{
					echo '<div class="block-value" data-block-name="'.$value['table'].'['.$objValue['tree_id'].'][' . $keyField . ']">' .$objValue[$keyField] . '</div>';
					if(in_array($keyField, $keysToEdit)) {
						echo '<textarea class="edit-value" name="'.$value['table'].'['.$objValue['tree_id'].'][' . $keyField . ']"></textarea>';
					}
				}
				else
				{
					echo '<div class="block-value" data-block-name="'.$value['table'].'['.$objValue['tree_object'].'][' . $keyField . ']">' .$objValue[$keyField] . '</div>';
					if(in_array($keyField, $keysToEdit)) {
						echo '<textarea class="edit-value" name="'.$value['table'].'['.$objValue['tree_object'].'][' . $keyField . ']"></textarea>';
					}
				}
				echo '</td>';
			}
			else
			{
				# data поля доступны для редактирования
				list($a, $b) = explode('_', $keyField);
				if(isset($objValue[$keyField]))
				{
					echo '<td>';
					echo '<div class="block-value" data-block-name="data['.$objValue['tree_object'].']['.$b.']">' . $objValue[$keyField] . '</div>';
					echo '<textarea class="edit-value" name="data['.$objValue['tree_object'].']['.$b.']"></textarea>';
					echo '</td>';
				}
				else
				{
					echo '<td>';
					echo '<div class="block-value" data-block-name="data['.$objValue['tree_object'].']['.$b.']">' . $noValue . '</div>';
					echo '<textarea class="edit-value" name="data['.$objValue['tree_object'].']['.$b.']"></textarea>';
					echo '</td>';
				}
			}
		}
	echo '</tr>';
}
?>
</table>

<form action="" method="post" accept-charset="utf-8" style="margin-top: 25px">
<div class="form-group">
	<span class="help-block sub-little-text" style="margin-bottom:12px;font-size:16px"><?= $exportInfo ?></span>
	<button type="submit" name="run_export" value="start" class="btn btn-primary ng-scope">Выгрузить</button>
</div>
</form>
