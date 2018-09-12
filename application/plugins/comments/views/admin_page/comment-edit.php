<?php defined('BASEPATH') OR exit('No direct script access allowed');

$publishDropdownValues = array('hidden' => 'Скрыт', 'publish' => 'Опубликован');
?>

<ul class="content-top-menu">
	<li><a href="/admin/comments"><span><?= app_lang('COMMENT_INFO_GO_HOME') ?></span></a></li>
</ul>

<h1><?= app_lang('COMMENT_ADM_TITLE_EDIT') ?></h1>

<? if(!$COMMENT || count($COMMENT) > 1) {
	echo 'error';
	return;
}
	?>
<? foreach($COMMENT as $info): ?>	
<div class="comment-info">
	<form action="" method="POST">
		<div class="descr-row flex"><strong>ID комментария</strong> <span><?= $info['comments_id'] ?></span></div>
		<div class="descr-row flex"><strong>ID родительского комментария</strong> <span><?= $info['comments_parent'] ?></span></div>
		<div class="descr-row flex"><strong>Дата</strong> <span><?= date_convert('d.m.Y в H:i', $info['comments_date']) ?></span></div>
		<div class="descr-row flex"><strong>IP автора</strong> <span><?= $info['comments_ip'] ?></span></div>
		<div class="descr-row flex"><strong>Страница</strong> <a href="/<?= $info['obj_canonical'] ?>" target="_blank"><?= $info['obj_h1'] ?></a></div>
		<? if($info['users_id']): ?>
		<div class="descr-row flex"><strong>Автор (регистр.)</strong> <span><?= $info['users_login'] ?> (<?= $info['users_email'] ?>)</span></div>
		<? else: ?>
		<div class="descr-row flex"><strong>Автор (аноним)</strong> <span><?= $info['comments_author'] ?></span></div>
		<? endif ?>
		<div class="descr-row flex"><strong>Текущий статус</strong> 
			<span>
			<?
			$args = array(
				'class' => 'form-control',
				'id' => 'select01',
				'name' => 'update[status]',
				);

		   echo form_dropdown($args, $publishDropdownValues, $info['comments_status'])
			?>
			</span>
		</div>
		<div class="descr-row flex"><strong>Текст комментария</strong>
			<div><textarea name="update[content]" class="form-control"><?= $info['comments_content'] ?></textarea></div>
		</div>
		<div class="descr-row flex btns">
			<button type="submit" name="save_values" class="btn btn-success" value="save">Сохранить изменения</button>
			<button type="submit" name="delete_comment" class="btn btn-danger" value="delete">Удалить комментарий</button>
		</div>
	</form>
</div>

<? endforeach; ?>

<? // pr($COMMENT) ?>

