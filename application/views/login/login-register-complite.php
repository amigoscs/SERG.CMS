<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<? require_once(__DIR__ . '/units/login-head.php') ?> 
<body class="login-page">

<div class="login-box complite">
    	<h1>Регистрация на сайте</h1>
    	<div class="login-body">
		<p class="title">Регистрация прошла успешно!</p>
    	<p class="text">Поздравляем! Вы успешно зарегистрировались. Вам на почту выслана ссылка для подтверждения регистрации. Перейдите по ней, чтобы подтвердить свою регистрацию.</p>
    	<p class="link"><a href="<?=info('baseUrlTrim')?>">Перейти на главную страницу сайта</p>
        <p class="link"><a href="<?=info('baseUrl')?>login">Войти на сайт</p>
    	</div>
</div>


<footer>

</footer>

</body>
</html>