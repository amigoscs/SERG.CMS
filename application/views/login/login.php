<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<? require_once(__DIR__ . '/units/login-head.php') ?>
<body class="login-page">

<? if($error): ?>
	<div class="login-box login-error">
<? else: ?>
	<div class="login-box">
<? endif; ?>
		<h1><?= app_lang('ENTER_SITE_TEXT') ?> <span><?= app_get_option('site_name', 'site', 'Site Name') ?></span></h1>
    <div class="login-body">
		<a href="<?= info('baseUrlTrim') ?>" title="<?= app_get_option('site_name', 'site', 'Site Name') ?>" class="login-logo">
    		<img src="<?= app_get_option('logo_site', 'site', '/uploads/sys/serg-cms-logo.jpg') ?>" alt="<?= app_get_option('site_name', 'site', 'Site Name') ?>" class="login-logo"/>
		</a>
    <p class="error-message"><?=$message?></p>
    <form action="<?=info('baseUrl')?>login" method="post">
    <div class="form-group has-feedback">
    	<input type="text" name="login_name" value="" placeholder="Логин или E-mail" required />
        <span class="glyphicon glyphicon-user form-control-feedback"><i class="fa fa-user" aria-hidden="true"></i></span>
    </div>
    <div class="form-group has-feedback">
    	<input type="password" name="login_password" value="" placeholder="Введите пароль" required />
        <span class="glyphicon glyphicon-lock form-control-feedback"><i class="fa fa-lock" aria-hidden="true"></i></span>
    </div>
    <div class="form-group has-feedback">
    	<label class="label-checkbox">
			<input type="checkbox" name="remember" value="on"/> <span>Запомнить меня</span>
        </label>
    </div>

    <div class="form-group has-feedback submit">
    	<button type="submit" class="btn btn-primary ng-scope submit">Вход</button>
		<ul class="form-links">
			<? if(app_get_option('register_allowed', 'general', 0)): ?>
			<li><a href="<?= info('baseUrl') ?>login/registration">Зарегистрироваться</a></li>
			<? endif ?>
			<li><a href="<?=info('baseUrlTrim')?>">Вернуться к сайту</a></li>
			<li><a href="<?= info('baseUrl') ?>login/forgot">Восстановить пароль</a></li>
		</ul>
    </div>
    </form>

    </div>
</div>


<footer>

</footer>

</body>
</html>
