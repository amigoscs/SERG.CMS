<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
/*
	[users_group] => 1
    [users_login] => Шван
    [users_password] => 15071987
    [users_name] => Иван
    [users_image] => 
    [users_email] => serg87@inbox.ru
    [users_phone] => 
    [users_phone_active] => 0
    [users_date_registr] => 2018-02-15 12:27:23
    [users_date_birth] => 1970-01-01
    [users_last_visit] => 1518686843
    [users_ip_register] => 91.204.149.9
    [users_activate] => 0
    [users_status] => publish
    [users_lang] => russian
    [users_password_encrypt] => 13643cc2a5db54c37ebca0d67a9faf2d
    [users_activate_key] => 77d8321b47ba26bf88ce0ea677f62213
    [users_site_key] => 1cba4770a56ea3c9578a6ee1011bbb05
    [users_id] => 14
	[site_name] => ЭКО ФИШ
    [site_url] => http://eco-fish.ru
*/

?>
<p>Спасибо за регистрацию на сайте <a href="<?= $site_url ?>" title="Перейти на сайт"><?= $site_name ?></a></p>
<p>Для входа на сайт используйте следующую информацию:</p>
<p>Логин: <?= $users_login ?></p>
<p>Пароль: <?= $users_password ?></p>
<p>Подтвердите свою регистрацию перейдя по ссылке <a href="<?= $site_url ?>/login/registration?confirm=1&user=<?= $users_id ?>&confirmkey=<?= $users_activate_key ?>" target="_blank">ПОДТВЕРЖДАЮ</a>.</p>