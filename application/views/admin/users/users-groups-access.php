<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<h1><?= app_lang('H1_USERS_EDIT_USERS_GROUPS') ?></h1>

<? require_once(__DIR__ . '/_menu.php'); ?>

<form action="" method="POST">
<table class="simple-table">
	<thead>
		<tr>
			<th>PAGE</th>
			<? foreach($all_roles as $key => $value): ?>
			<th><?= $value ?></th>
			<? endforeach ?>
	</tr>
</thead>
<tbody>
	<? foreach($accesses as $keyAcc => $valueAcc): ?>
	<tr>
		<td><?= $valueAcc['name'] ?> (<?= $keyAcc ?>)</td>
		<? foreach($all_roles as $key => $value)
		{
			echo '<td>';
			if($key == 1) {
				echo '<input type="hidden" name="accesses[' . $keyAcc . '][]" value="' . $key . '"/>';
				echo '<input type="checkbox" id="sulb' . $keyAcc . $key . '" name="accessesjoker[]" value="' . $key . '" checked onclick="return false;" disabled/><label for="sulb' . $keyAcc . $key . '"></label>';
			} elseif($key == 2) {
				echo '<input type="checkbox" id="sulb' . $keyAcc . $key . '" name="accessesjoker[]" value="' . $key . '" onclick="return false;" disabled/><label for="sulb' . $keyAcc . $key . '"></label>';
			} else {
				if(in_array($key, $valueAcc['access'])) {
					echo '<input type="checkbox" id="sulb' . $keyAcc . $key . '" name="accesses[' . $keyAcc . '][]" value="' . $key . '" checked /><label for="sulb' . $keyAcc . $key . '"></label>';
				} else {
					echo '<input type="checkbox" id="sulb' . $keyAcc . $key . '" name="accesses[' . $keyAcc . '][]" value="' . $key . '"/><label for="sulb' . $keyAcc . $key . '"></label>';
				}
			}
			echo '</td>';
		}
		?>
	</tr>
	<? endforeach ?>

</table>
<button type="submit" class="btn btn-primary">Сохранить</button>
</form>
