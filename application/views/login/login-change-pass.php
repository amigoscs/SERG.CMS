<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<? require_once(__DIR__ . '/units/login-head.php') ?>
<body class="login-page">

<? if($change_true): ?>
	<div class="login-box">
    	<div class="login-body">
    	<p>Пароль успешно изменен</p>
        <a href="<?= info('baseUrl') ?>login">Войти на сайт</a>
        </div>
    </div>

<? else: ?>

<? if($error): ?>
	<div class="login-box login-error">
<? else: ?>
	<div class="login-box">
<? endif; ?>
    <h1>Изменение пароля <span><?= app_get_option('site_name', 'site', 'Site Name') ?></span></h1>
    <div class="login-body">
	<a href="<?= info('baseUrlTrim') ?>" title="<?= app_get_option('site_name', 'site', 'Site Name') ?>" class="login-logo">
     <img src="<?= app_get_option('logo_site', 'site', '/uploads/sys/serg-cms-logo.jpg') ?>" alt="<?= app_get_option('site_name', 'site', 'Site Name') ?>" class="login-logo"/>
	 </a>
    <p class="error-message"><?=$message?></p>
    <form action="" method="post">
    <div class="form-group has-feedback">
    	<input type="password" name="password[first]" value="" placeholder="Введите старый пароль" required/>
        <span class="glyphicon glyphicon-lock form-control-feedback"><i class="fa fa-lock" aria-hidden="true"></i></span>
    </div>

    <div class="form-group has-feedback">
    	<input type="password" name="password[second]" value="" placeholder="Введите новый пароль" required/>
        <span class="glyphicon glyphicon-lock form-control-feedback"><i class="fa fa-lock" aria-hidden="true"></i></span>
    </div>

    <div class="form-group has-feedback">
    	<input type="password" name="password[third]" value="" placeholder="Повторите новый пароль" required/>
        <span class="glyphicon glyphicon-lock form-control-feedback"><i class="fa fa-lock" aria-hidden="true"></i></span>
    </div>


    <div class="form-group has-feedback submit">
    	<button type="submit" class="btn btn-primary ng-scope submit">Изменить пароль</button> <br />
    	<ul class="form-links">
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
