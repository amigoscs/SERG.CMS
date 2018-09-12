<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0"> 

		<title><?= $textTitle ?> <?= $version ?></title>
		<style>
			* {line-height: 1.4em;color: #000}
			body {background: #fff}
			h1 {text-align: center;font-size: 28px;}
			h2 {color: #c8d5ef;margin: 24px 0 12px 0;;text-align: center}
			#container {width: 100%;max-width: 1000px;margin: 0 auto;padding: 12px;background: #5e7194}
			code {font-family: monospace;display: block;margin: 4px 0;background: #fff;padding: 2px 8px;font-size: 14px}
			.description {padding: 12px;font-size: 14px;background: #fff;margin: 24px 0}
			.description * {font-size: inherit;line-height: inherit}
			p {margin: 0.4em 0}
			form {display: block}
			form button[type="submit"] {display: block; width: 250px;text-align: center;background:#2f7917;border: none;color: #fff;font-size: 18px;margin: 12px auto;padding: 8px 0;border-radius: 4px;cursor: pointer}
			form button[type="submit"]:hover {background: #3d9c1e}
		</style>
	</head>
	<body>
	<? 
	if($ERROR)
	{
		echo '<div class="error">' . $ERROR_TEXT . '</div>';
		echo '</body></html>';
		return;
	}
	?>
	
	<? 
	if($COMPLITE)
	{
		echo '<div class="update">' . $COMPLITE_TEXT . '</div>';
		echo '</body></html>';
		return;
	}
	?>
		<h1><?= $textTitle ?> <?= $version ?></h1>
		<div id="container">
			<form action="<?= info('baseUrl') ?>adminupdate" method="post">
			<h2>Файлы, которые будут обновлены</h2>
			<? foreach($updateFiles as $value): ?>
			<code><?= $value ?></code>
			<? endforeach ?>

			<h2>Файлы, которые будут удалены</h2>
			<? foreach($deletedFiles as $value): ?>
			<code><?= $value ?></code>
			<? endforeach ?>

			
				<button type="submit" name="update" value="1">Обновить</button>
			</form>
			<div class="description">
				<?= $textInfo ?>
			</div>
		</div>
	</body>
</html>