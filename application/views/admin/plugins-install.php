<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
	* UPD 2017-12-01
	* version 2.0
	*
	*/

# плагины, которые нельзя отключать
/*$notUninstall = array(
	'admin-page' => 'admin-page',
	'exp-csv' => 'exp-csv',
	'elfinder' => 'elfinder',
	'admin-site-tree' => 'admin-site-tree',
);*/

?>
<h1><?= app_lang('h1_plugins_install') ?></h1>


<div class="plugin-rows-container flex">
	<form action="<?=info('base_url')?>admin/plugins_install" method="post">
	<? foreach($plugins_array as $key => $value):?>
    <div class="plugin-row plugin-<?= $key ?>">
		<h2><?=$value['info']['name']?> <i><?=$value['info']['version']?></i></h2>


       <div class="plugin-buttons">
    <?
	// если плагин нельзя отключать, то кнопка только для обновления
	if(in_array($key, $notUninstall))
	{
		echo '<button type="button" class="btn btn-warning ng-scope" name="check_update" value="update" data-version="' . $value['info']['version'] . '" data-plugin="' . $key .'">Проверить обновление</button>';
	}
	elseif(isset($plugins_install[$key]))
	{
		# если плагин установлен, то можно его деинсталировать
		echo '<button type="submit" class="btn btn-primary ng-scope" name="plugin_install[' . $key. ']" value="uninstall">' . app_lang('button_uninstall') . '</button>';
		# кнопка обновления
		echo '<button type="button" class="btn btn-warning ng-scope" name="check_update" value="update" data-version="' . $value['info']['version'] . '" data-plugin="' . $key .'">Проверить обновление</button>';

		if($plugins_install[$key]['options']){
			echo '<a href="/admin/setting_plugin/' . $key . '" title="Settings plugin" class="pl-setting"><i class="fa fa-cog" aria-hidden="true"></i></a>';
		}
	}
	else
	{
		echo '<button type="submit" class="btn btn-success ng-scope" name="plugin_install[' . $key. ']" value="install">' . app_lang('button_install') . '</button>';
	}
	?>

		</div>
        <p><?=$value['info']['descr']?></p>


    </div>
    <? endforeach;?>
    </form>
</div>

<script>
	jQuery(document).ready(function(){
		//var CmsVersion = '';
		var UserLang = '';

		$('button[name="check_update"]').on('click.PLUGINS', function(){
			if($(this).hasClass('is-check'))
				return false;

			var PluginName = $(this).data('plugin');
			var PluginVersion = $(this).data('version');
			appGetUpdate(PluginVersion , 'plugin', UserLang, PluginName, function(data){
				console.log(data);
				var Res = JSON.parse(data);
				if(Res.status === 200){
					$('.plugin-' + PluginName).append('<div class="update">' + Res.info + '</div>');
				}else{
					$('.plugin-' + PluginName).append('<div class="error">' + Res.info + '</div>');
				}
			});
		});
	});
</script>
