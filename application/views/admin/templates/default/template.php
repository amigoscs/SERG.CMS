<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
	 * version 2.0
	 * UPD 2017-11-23
	 *
	 * version 3.0
	 * UPD 2018-08-30
	 * подключение языковых файлов
*/

$CI = &get_instance();

?>
<!doctype html>
<html lang="en" class="no-js">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="<?= info('base_url') . app_get_option('favicon_panel', 'general', '') ?>" type="image/x-icon">

    <title><?= $PAGE_TITLE ?></title>

    <meta name="description" content="<?= $PAGE_DESCRIPTION ?>">
	<meta name="keywords" content="<?= $PAGE_KEYWORDS ?>">

	<link href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700&subset=cyrillic" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/redmond/jquery-ui.css">
	<link rel="stylesheet" href="<?= $ADMIN_CSS_URL ?>reset.css"> <!-- CSS reset -->
    <link rel="stylesheet" href="<?= $ADMIN_CSS_URL ?>style.css"> <!-- CSS units -->
    <link rel="stylesheet" href="<?= $ADMIN_CSS_URL ?>unit.css"> <!-- CSS units -->
    <link rel="stylesheet" href="<?= $ADMIN_CSS_URL ?>jquery.contextMenu.css"> <!-- CSS context menu -->
    <link rel="stylesheet" href="<?= $ADMIN_CSS_URL ?>daterangepicker.min.css"> <!-- CSS datepicker -->

    <style>
		.content-wrapper i.mce-ico {font-style:normal;}
		textarea.editor {height:400px}
	</style>

	<script>
	var LANGUAGE = {};
	var cmsSetLang = function(key, value) {
		LANGUAGE[key] = value;
	}
	var cmsGetLang = function(key) {
		return LANGUAGE[key];
	}
	</script>

	<?
	if($LANGS_JS) {
		foreach($LANGS_JS as $value) {
			echo '<script src="' . $value . '"></script><!-- lang file --> ';
		}
	}
	?>

	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.12.0/moment.min.js" type="text/javascript"></script>
    <script src="<?= $ADMIN_JS_URL ?>jquery.noty.packaged.min.js"></script> <!-- Noty -->
    <script src="<?= $ADMIN_JS_URL ?>jquery.menu-aim.js"></script>
    <script src="<?= $ADMIN_JS_URL ?>moment-with-locales.min.js"></script>
    <script src="<?= $ADMIN_JS_URL ?>jquery.daterangepicker.min.js"></script>
	<script src="<?= $ADMIN_ASSETS_URL ?>/tinymce/tinymce.min.js"></script>

    <script src="<?= $ADMIN_JS_URL ?>inputmask.js"></script>
    <script src="<?= $ADMIN_JS_URL ?>template_func.js"></script>
    <!--assets top-->
    <?= $ASSETS_TOP ?>

    <script>
		tinymceRun();
    </script>
</head>

<body>

	<header class="cd-main-header">
		<a href="<?= APP_BASE_URL ?>" class="cd-logo"><img src="<?= APP_BASE_URL . app_get_option('icon_panel', 'general', '') ?>" alt="Logo"><span>Version <?= $CI->config->item('CMSVERSION') ?></span></a>

		<!--<div class="cd-search is-hidden">
			<form action="#0">
				<input type="search" placeholder="Search...">
			</form>
		</div>--> <!-- cd-search -->

		<a href="#0" class="cd-nav-trigger">Menu<span></span></a>

		<nav class="cd-nav">
			<ul class="cd-top-nav">
				<!--<li><a href="#0">Tour <span class="count">3</span></a></li>-->
				<? if($settingPluginLink): ?>
					<li class="action-btn"><a href="<?= $settingPluginLink ?>" class="cd-top-nav-option" title="Настройки плагина"><i class="fa fa-cog" aria-hidden="true"></i></a></li>
				<? endif; ?>
				<li class="action-btn"><a href="<?= APP_ADMIN_URL ?>/docs" class="cd-top-nav-option" title="Документация"><i class="fa fa-book" aria-hidden="true"></i></a></li>
				<li class="has-children account">
					<a href="#"><?= $userInfo['login'] ?></a>
					<ul>
						<li><a href="<?= APP_ADMIN_URL ?>/users/edit/<?= $userInfo['id'] ?>">Edit</a></li>
						<li><a href="<?= APP_BASE_URL ?>login/logout">Logout</a></li>
					</ul>
				</li>
			</ul>
		</nav>
	</header> <!-- .cd-main-header -->

	<main class="cd-main-content">
		<nav class="cd-side-nav">
			<ul>
				<li class="cd-label"><?= app_lang('MENU_TITLE_ACTION') ?></li>
                <li class="action-btn"><a href="<?= APP_ADMIN_URL ?>"><?= app_lang('BUTTON_START') ?></a></li>
			</ul>

            <ul>
			<li class="cd-label"><?= app_lang('MENU_TITLE_SYSTEM') ?></li>
            <?
				// основное меню
				foreach($system_nav_menu as $key => $value):
					$liClass = 'cl-' . $value['link'];
					$li = $value['active'] ? '<li class="active ' . $liClass . '">' : '<li class="' . $liClass . '">';
		   			echo $li;
		   			echo '<a href="' . APP_ADMIN_URL . '/' . $value['link'] . '">' . $key . '</a>';
		   			echo '</li>';

				endforeach;
			?>
			<li class="cd-label"><?= app_lang('MENU_TITLE_SYSTEM_PLUGINS') ?></li>
			<?
				// меню системных плагинов
				foreach($left_system_plugins_menu as $key => $value):
					$liClass = 'cl-' . $value['link'];
					$li = $value['active'] ? '<li class="active ' . $liClass . '">' : '<li class="' . $liClass . '">';
						echo $li;
						echo '<a href="' . APP_ADMIN_URL . '/' . $value['link'] . '">' . $key . '</a>';
						echo '</li>';

				endforeach;
			?>


			<? if($left_nav_menu): ?>
			<li class="cd-label"><?= app_lang('MENU_TITLE_PLUGINS') ?></li>
            <?
				// меню плагинов
				ksort($left_nav_menu);
				foreach($left_nav_menu as $key => $value):
					$liClass = 'cl-' . $value['link'];
					$li = $value['active'] ? '<li class="active ' . $liClass . '">' : '<li class="' . $liClass . '">';
		   			echo $li;
		   			echo '<a href="' . APP_ADMIN_URL . '/' . $value['link'] . '">' . $key . '</a>';
		   			echo '</li>';
				endforeach;
			endif;
			?>


			</ul>
		</nav>

		<div class="content-wrapper">
        	<?=$infocomplite?>
            <?=$infoerror?>
			<?=$content?>
        </div> <!-- .content-wrapper -->
	</main> <!-- .cd-main-content -->
    <!-- <footer>Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?> SQL - <?=$CI->db->query_count?></footer>-->
	<!--assets bottom-->
    <?= $ASSETS_BOTTOM ?>

	<script src="<?= $ADMIN_JS_URL ?>main.js"></script> <!-- Resource main.js -->
	<div id="ui-dialog"></div>
</body>
</html>
