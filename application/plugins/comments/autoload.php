<?php defined('BASEPATH') OR exit('No direct script access allowed');
# файл конфигурации автозагрузки плагина
/*
	ссылка на папку плагинов - info('plugins_url')

	* UPD 2018-03-12
	* v 2.2
	* исправлена проблема текущего URl для формы комментариев (comments_helper)
	*
	* UPD 2018-03-16
	* v 2.3
	* добавлены опции для метода отправки комментариев. Настроена отправка письма. Мелкие правки
	*
	* UPD 2018-09-25
	* v 2.4
	* Введена рекапча
	*
*/

$info = array(
		'name' => 'Комментарии',
		'descr' => 'Плагин позволяет организовать комментарии на сайте',
		'version' => '2.4',
		'author' => 'Сергей Будников',
		'url' => '//sergcms.ru',
	);

$admin_menu = TRUE; // отображать в списке меню

# $load_admin - автозагрузка моделей и хелперов для админ панели
$load_admin = array(
		'helper' => array('comment'), // автозагрузка хелперов
		'model' => array('CommentModel'), // автозагрузка моделей
);

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию HEAD
$load_admin['assets']['plugin']['top'] = array();

# файлы скриптов и стилей для СТРАНИЦЫ ПЛАГИНА в секцию BODY
$load_admin['assets']['plugin']['bottom'] = array(
	0 => '<link rel="stylesheet" href="' . APP_PLUGINS_URL . 'comments/assets/comments-style.css">'
);

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию HEAD
$load_admin['assets']['admin']['top'] = array();

# файлы скриптов и стилей для ВСЕЙ АДМИН-ПАНЕЛИ в секцию BODY
$load_admin['assets']['admin']['bottom'] = array();




# $load_site - автозагрузка моделей и хелперов для сайта
$load_site = array(
		'helper' => array('comment'), // автозагрузка хелперов
		'model' => array('CommentModel'), // автозагрузка моделей
);

# файлы скриптов и стилей сайта в секцию HEAD
$load_site['assets']['top'] = array(
	0 => '<script src="https://www.google.com/recaptcha/api.js"></script>'
);

# файлы скриптов и стилей сайта в секцию BODY
$load_site['assets']['bottom'] = array(
	0 => '<link rel="stylesheet" href="' . APP_PLUGINS_URL . 'comments/assets/comments-site-style.css">',
	1 => '<script src="' . APP_PLUGINS_URL . 'comments/assets/comments.js"></script>'
);




# $load_site_admin - автозагрузка моделей и хелперов для сайта, ЕСЛИ ПОЛЬЗОВАТЕЛЬ ВОШЕЛ КАК Admin
$load_site_admin = array(
		'helper' => array(), // автозагрузка хелперов
		'model' => array(), // автозагрузка моделей
);

# файлы скриптов и стилей сайта в секцию HEAD, ЕСЛИ ПОЛЬЗОВАТЕЛЬ ВОШЕЛ КАК Admin
$load_site_admin['assets']['top'] = array();

# файлы скриптов и стилей сайта в секцию BODY, ЕСЛИ ПОЛЬЗОВАТЕЛЬ ВОШЕЛ КАК Admin
$load_site_admin['assets']['bottom'] = array();



# опции плагина
/*
	опции для настройки плагина. Формат array(key => value)

	$options['key_field']['name'] = 'Название поля';
	$options['key_field']['type'] = 'тип поля (text, textarea, select)';
	$options['key_field']['default'] = 'по умолчанию';
	$options['key_field']['values'] = array(
										'key1' => 'val1',
										'key2' => 'val2',
										);
	$options['key_field']['description'] = 'Подпись к полю';

*/
$options = array();
$options['comment_is_true']['name'] = 'Разрешить комментирование на сайте';
$options['comment_is_true']['type'] = 'select';
$options['comment_is_true']['default'] = 'no';
$options['comment_is_true']['values'] = array(
	'no' => 'Запрещено комментировать',
	'onlyregister' => 'Только зарегистрированные пользователи',
	'all' => 'Все могут оставлять комментарии'
);
$options['comment_is_true']['description'] = 'Выберите способ коммнетирования на сайте';

$options['comment_moderation']['name'] = 'Модерация комментариев';
$options['comment_moderation']['type'] = 'select';
$options['comment_moderation']['default'] = 'yes';
$options['comment_moderation']['values'] = array(
										'yes' => 'Да',
										'no' => 'Нет',
										'noregister' => 'Модерация только незарегистрированных пользователей'
										);
$options['comment_moderation']['description'] = 'Если поставить НЕТ, то комментарии будут сразу опубликованы';

$options['comment_email']['name'] = 'E-mail для уведомлений о новых комментариях';
$options['comment_email']['type'] = 'text';
$options['comment_email']['default'] = '';
$options['comment_email']['values'] = array();
$options['comment_email']['description'] = 'Можно прописать несколько E-mail через запятую';

$options['comment_method']['name'] = 'Метод отправки комментариев';
$options['comment_method']['type'] = 'select';
$options['comment_method']['default'] = 'ajax';
$options['comment_method']['values'] = array(
	'ajax' => 'Отправлять по AJAX',
	'post' => 'Отправлять по POST'
);
$options['comment_method']['description'] = 'Выберите способ отправки комментариев';

$options['comment_captcha']['name'] = 'Ключ reCAPTCHA';
$options['comment_captcha']['type'] = 'text';
$options['comment_captcha']['default'] = '';
$options['comment_captcha']['values'] = array();
$options['comment_captcha']['description'] = 'Вставьте здесь свой ключ reCAPTCHA от google. Подробнее <a href="https://www.google.com/recaptcha/admin" target="_blank" title="">на сайте »</a>';

$options['comment_captcha_skey']['name'] = 'Секретный ключ проверки';
$options['comment_captcha_skey']['type'] = 'text';
$options['comment_captcha_skey']['default'] = '';
$options['comment_captcha_skey']['values'] = array();
$options['comment_captcha_skey']['description'] = 'Вставьте здесь свой ключ проверки запроса reCAPTCHA от google. Подробнее <a href="https://www.google.com/recaptcha/admin" target="_blank" title="">на сайте »</a>';
