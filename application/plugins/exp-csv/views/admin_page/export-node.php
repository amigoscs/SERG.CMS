<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1>Экспорт объектов</h1>
<? require(__DIR__ . '/units/menu.php') ?>

<table class="simple-table">
<?
$outHeader = $outKeys = '';
foreach($tableHead as $keyField => $nameField){
	$outHeader .= '<th>' . $nameField . '</th>';
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
	foreach($tableHead as $keyField => $nameField) {
		$table = 'objects_data';
		$tableKey = $keyField;
		if(isset($activeFields[$keyField])) {
			$table = $activeFields[$keyField]['table'];
			$tableKey = $keyField;
		}

		echo '<td>';
		if(isset($objValue[$keyField])) {
			echo '<textarea class="edit-value" data-table="' . $table . '" data-table-key="' . $tableKey . '" data-node-id="' . $objValue['tree_id'] . '">' . $objValue[$keyField] . '</textarea>';
			echo '<div class="block-value">' . $objValue[$keyField] . '</div>';
		} else {
			echo '<textarea class="edit-value" data-table="' . $table . '" data-table-key="' . $tableKey . '" data-node-id="' . $objValue['tree_id'] . '">' . $novalueField . '</textarea>';
			echo '<div class="block-value">' . $novalueField . '</div>';
		}
		echo '</td>';
	}

	echo '</tr>';
}
?>
</table>

<form action="<?= $exportFormAction ?>" method="post" accept-charset="utf-8" style="margin-top: 25px" id="export_nodes">
<div class="form-group">
	<p style="margin-bottom:12px">Найдено строк: <strong><?= $countObjects ?></strong></p>
	<button type="submit" name="run_export" value="start" class="btn btn-primary ng-scope"><?= app_lang('EXPCSV_BUTTON_RUN_EXPORT') ?></button>
	<span class="help-block sub-little-text">
		<?= app_lang('EXPCSV_INFO_DEL_FIELDS') ?> - «<?= $infoDelimiterField ?>».
		<?= app_lang('EXPCSV_INFO_ENCLOSURE') ?> - «<?= $infoEnclosure ?>».
		(<a href="/admin/setting_plugin/exp-csv"><?= app_lang('EXPCSV_INFO_CHANGE_SETTING') ?></a>)
	</span>
</div>
</form>

<? //pr($tableHead); ?>
