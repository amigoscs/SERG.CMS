<?php defined('BASEPATH') OR exit('No direct script access allowed');


?>
<h1><?=$this->lang->line('H1_ADMIN_PANEL')?></h1>

<form action="" method="post" id="cms_index_form">
	<button type="submit" name="delete_cache" class="btn btn-info" value="dd">Очистить кеш</button>
	<button type="submit" name="create_sitemap" class="btn btn-info" value="dd">Создать SiteMap</button>
	<button type="button" name="index_site" class="btn btn-info" value="dd">Проиндексировать сайт</button>
</form>

<div class="system-info">
	<p><strong>Версия SERG.CMS:</strong> <span><?= $CMSVERSION ?></span></p>
	<p><strong>Версия PHP:</strong> <span><?= $confPhpVersion ?></span></p>
	<p><strong>Версия MySQL:</strong> <span><?= $confMySQLVersion ?></span></p>
	<p><strong>Версия CI:</strong> <span><?= CI_VERSION ?></span></p>
</div>

<?
	$checkUpdate = FALSE;
	if($update_panel):
	$checkUpdate = TRUE;
?>
<div class="check-update-info error"><?= $update_panel ?></div>
<? else: ?>
<div class="check-update-info"></div>
<? endif ?>


<? if($widjets): ?>
<h2 class="admin-widjets-title">Активные виджеты</h2>
<ul class="widjets-list">
<? foreach($widjets as $key => $widjetContent): ?>
	<li id="wdj<?= $key ?>">
		<?= $widjetContent ?>
	</li>
<? endforeach; ?>
</ul>
<? endif; ?>

<? if(!$checkUpdate): ?>
<script>
	appGetUpdate('<?= $CMSVERSION ?>', 'cms', '<?= $userLang ?>', '', function(data){
		var Res = JSON.parse(data);
		if(Res.status === 200){
			$('.check-update-info').addClass('update').html(Res.info);
		}else{
			$('.check-update-info').addClass('error').html(Res.info);
		}
	});
</script>
<? endif ?>
