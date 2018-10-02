<?php defined('BASEPATH') OR exit('No direct script access allowed');

?>
<h1><?= $h1 ?></h1>

<? require(__DIR__ . '/units/menu.php') ?>



<p>Плагин позволяет выгружать, загружать и обновлять объекты структуры сайта через <strong>*.csv</strong> файлы. Важно - <strong>все файлы выгружаются в кодировке UTF-8</strong>. Для импорта используйте ту же кодировку (UTF-8)</p>
<p>Для продолжения работы, выберите нужный пункт меню.</p>
<p><strong>Общие требования к файлу:</strong></p>
<ul>
	<li>Кодировка файла строго UTF-8</li>
	<li>Первая строка - название полей</li>
	<li>Вторая строка - ID полей</li>
</ul>
<p><strong>Список допустимых ID полей:</strong></p>
<ul>
	<li><strong>tree_id</strong> - ID ноды. Использовать, если требуется обновление полей в структуре</li>
	<li>Параметры, на которые влияет:
		<ul>
			<li><strong>tree_parent_id</strong> - ID родительской ноды. Можно указать несколько через разделитель |. Например: 5|25|31</li>
			<li><strong>tree_url</strong> - URL объекта</li>
			<li><strong>tree_date_create</strong> - дата создания ноды</li>
			<li><strong>tree_order</strong> - порядок в списке</li>
			<li><strong>tree_type</strong> - тип ноды в структуре (оригинал или копия orig, copy)</li>
			<li><strong>tree_type_object</strong> - тип объекта (<a href="/admin/types_objects" target="_blank">все типы</a>)</li>
			<li><strong>tree_folow</strong> - разрешен для индексации</li>
			<li><strong>tree_short</strong> - короткая ссылка</li>
			<li><strong>tree_axis</strong> - местоположение в дереве</li>
		</ul>
	</li>

	<li><strong>obj_id</strong> - ID объекта. Использовать, если требуется обновление полей конкретного объекта или его DATA-полей</li>
	<li>Параметры, на которые влияет:
		<ul>
			<li><strong>obj_data_type</strong> - Data-тип объекта (<a href="/admin/types_data" target="_blank">все типы данных</a>)</li>
			<li><strong>obj_canonical</strong> - canonical страницы</li>
			<li><strong>obj_name</strong> - название страницы</li>
			<li><strong>obj_h1</strong> - H1 страницы</li>
			<li><strong>obj_title</strong> - TITLE страницы</li>
			<li><strong>obj_description</strong> - DESCRIPTION страницы</li>
			<li><strong>obj_keywords</strong> - KEYWORDS страницы</li>
			<li><strong>obj_anons</strong> - анонс страницы</li>
			<li><strong>obj_content</strong> - контент страницы</li>
			<li><strong>obj_date_create</strong> - дата создания объекта</li>
			<li><strong>obj_date_publish</strong> - дата публикации</li>
			<li><strong>obj_cnt_views</strong> - количество просмотров</li>
			<li><strong>obj_rating_up</strong> - рейтинг вверх (число)</li>
			<li><strong>obj_rating_down</strong> - рейтинг вниз (число)</li>
			<li><strong>obj_rating_count</strong> - всего установок рейтинга</li>
			<li><strong>obj_user_author</strong> - ID пользователя-создателя</li>
			<li><strong>obj_ugroups_access</strong> - доступ для групп пользователей (ALL - для всех)</li>
			<li><strong>obj_link</strong> - связи между объектами</li>
			<li><strong>obj_tpl_content</strong> - файл шаблона контента</li>
			<li><strong>obj_tpl_page</strong> - файл шаблона страницы</li>
			<li><strong>obj_status</strong> - статус страницы (publish, hidden)</li>
		</ul>
	</li>
	<li><strong>isdata_N</strong> - где <strong>N</strong> - ID дата поля (<a href="/admin/types_data/1">все поля</a>). Обновляется, если есть obj_id или ведущее поле <?= $dataPrefix ?>N. Обновляет поля объекта и его DATA-поля.</li>
</ul>

<p>В файле допускаются информационные столбцы, которые не будут использоваться при обновлении. Просто задайте для столбца ключ, который не перечислен в вешестоящем списке. Например - <em>info</em>.</p>

<p>Если требуется обновить поля по определенному DATA-полю, то следует взять его ID и поставить его первым столбцом в файле с ключом <em><?= $dataPrefix ?>ID</em>, где ID - id DATA-поля.</p>

<p>Для того, чтобы пропустить обновление конкретного поля у конкретного объекта, поставьте для него значение - <strong><?= $notSave ?></strong></p>


<p>Примеры файлов: <a href="/application/plugins/exp-csv/views/admin_page/img/upd1.jpg" target="_blank">Скрин 1</a>, <a href="/application/plugins/exp-csv/views/admin_page/img/upd2.jpg" target="_blank">Скрин 2</a></p>
