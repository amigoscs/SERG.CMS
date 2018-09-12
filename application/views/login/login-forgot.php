<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<? require_once(__DIR__ . '/units/login-head.php') ?>

<body class="login-page">

<? if($send_mail_true): ?>
	<div class="login-box">
    	<p>Вам на почту отправлено письмо с инструкцией по восстановлению пароля</p>
	<p><a href="<?=info('baseUrlTrim')?>">Вернуться на сайт</a></p>
        </div>
    </div>

<? else: ?>

<? if($error): ?>
	<div class="login-box login-error">
<? else: ?>
	<div class="login-box">
<? endif; ?>
    <h1>Восстановление пароля <span><?= app_get_option('site_name', 'site', 'Site Name') ?></span></h1>
    <div class="login-body">
	<a href="<?= info('baseUrlTrim') ?>" title="<?= app_get_option('site_name', 'site', 'Site Name') ?>" class="login-logo">
    <img src="<?= app_get_option('logo_site', 'site', '/uploads/sys/serg-cms-logo.jpg') ?>" alt="<?= app_get_option('site_name', 'site', 'Site Name') ?>" class="login-logo"/>
	</a>
    <p class="error-message"><?=$message?></p>
    <form action="<?=info('base_url')?>login/forgot" method="post">
    <div class="form-group has-feedback">
    	<input type="text" name="forgot_email" value="" placeholder="Введите свой E-mail" required/>
        <span class="glyphicon glyphicon-lock form-control-feedback"><i class="fa fa-at" aria-hidden="true"></i></span>
    </div>
    <div class="form-group has-feedback submit">
    	<button type="submit" class="btn btn-primary ng-scope submit">Отправить инструкцию</button> <br />
    	<ul class="form-links">
			<li><a href="<?= info('baseUrl') ?>login">Вход</a></li>
			<? if(app_get_option('register_allowed', 'general', 0)): ?>
			<li><a href="<?= info('baseUrl') ?>login/registration">Зарегистрироваться</a></li>
			<? endif ?>
			<li><a href="<?=info('baseUrlTrim')?>">Вернуться к сайту</a></li>
		</ul>
    </div>
    </form>

    </div>
</div>

<? endif; ?>
<footer>

</footer>

</body>
</html>
