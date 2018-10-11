<?php

$tablePrefix = $DB_replace_array['DB_PREFIX'];

$tablesArray = array();

// data2data
$tablesArray[$tablePrefix . 'data2data']['create'] = "CREATE TABLE __TABLE__ (
		data2data_id INT(8) NOT NULL AUTO_INCREMENT COMMENT 'main id',
		data2data_type_id INT(3),
		data2data_field_id INT(3),
		PRIMARY KEY  (data2data_id),
		INDEX (data2data_type_id),
		INDEX (data2data_field_id)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
$tablesArray[$tablePrefix . 'data2data']['insert'] = "";

// data_types
$tablesArray[$tablePrefix . 'data_types']['create'] = "CREATE TABLE __TABLE__ (
		data_types_id INT(3) NOT NULL AUTO_INCREMENT,
		data_types_name VARCHAR(100),
		data_types_descr TEXT,
		data_types_order INT(3),
		data_types_status ENUM('publish', 'hidden') NOT NULL DEFAULT 'publish',
		PRIMARY KEY  (data_types_id)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
$tablesArray[$tablePrefix . 'data_types']['insert'] = "INSERT INTO __TABLE__ (`data_types_id`, `data_types_name`, `data_types_descr`, `data_types_status`) VALUES (NULL, 'По умолчанию', 'Дефолтный тип данных', 'publish');";

// data_types_fields
$tablesArray[$tablePrefix . 'data_types_fields']['create'] = "CREATE TABLE __TABLE__ (
		types_fields_id INT(8) NOT NULL AUTO_INCREMENT,
		types_fields_type ENUM('text','textarea','select','image','file','checkbox','date','datetime','multiselect','number','numberfloat', 'editor') NOT NULL,
		types_fields_name VARCHAR(100),
		types_fields_unit VARCHAR(50),
		types_fields_values TEXT,
		types_fields_default TEXT,
		types_fields_flag1 TINYINT(1) NOT NULL,
		types_fields_flag2 TINYINT(1) NOT NULL,
		types_fields_flag3 TINYINT(1) NOT NULL,
		types_fields_flag4 TINYINT(1) NOT NULL,
		types_fields_order INT(5),
		types_fields_status ENUM('publish', 'hidden') NOT NULL DEFAULT 'publish',
		PRIMARY KEY  (types_fields_id),
		INDEX (types_fields_flag1),
		INDEX (types_fields_flag2),
		INDEX (types_fields_flag3),
		INDEX (types_fields_flag4)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
$tablesArray[$tablePrefix . 'data2data']['insert'] = "";

// tree
$tablesArray[$tablePrefix . 'tree']['create'] = "CREATE TABLE __TABLE__ (
		tree_id BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'node id',
		tree_parent_id BIGINT(20) NOT NULL,
		tree_object BIGINT(20) NOT NULL,
		tree_url VARCHAR(255),
		tree_date_create DATETIME NOT NULL,
		tree_order INT(8) NOT NULL,
		tree_type ENUM('orig', 'copy') NOT NULL,
		tree_type_object INT(3),
		tree_folow TINYINT(1) NOT NULL,
		tree_short VARCHAR(50),
		tree_axis VARCHAR(100),
		PRIMARY KEY  (tree_id),
		INDEX (tree_parent_id),
		INDEX (tree_object),
		INDEX (tree_url),
		INDEX (tree_type),
		INDEX (tree_folow),
		INDEX (tree_short),
		INDEX (tree_axis)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
$tablesArray[$tablePrefix . 'tree']['insert'] = "INSERT INTO __TABLE__
		(`tree_id`, `tree_parent_id`, `tree_object`, `tree_url`, `tree_date_create`, `tree_order`, `tree_type`, `tree_type_object`, `tree_folow`, `tree_short`, `tree_axis`)
		VALUES
		(NULL, '0', '1', 'index', '" . date('Y-m-d H:i:s') . "', '1', 'orig', '1', '1', 'indExp', '');";

// objects
$tablesArray[$tablePrefix . 'objects']['create'] = "CREATE TABLE __TABLE__ (
		obj_id BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'object id',
		obj_data_type VARCHAR(15),
		obj_canonical TEXT,
		obj_name VARCHAR(255),
		obj_h1 VARCHAR(255),
		obj_title TEXT,
		obj_description TEXT,
		obj_keywords TEXT,
		obj_anons TEXT,
		obj_content LONGTEXT,
		obj_date_create DATETIME NOT NULL,
		obj_date_publish DATETIME NOT NULL,
		obj_lastmod VARCHAR(255),
		obj_cnt_views VARCHAR(10),
		obj_rating_up VARCHAR(10),
		obj_rating_down VARCHAR(10),
		obj_rating_count VARCHAR(10),
		obj_user_author INT(8),
		obj_ugroups_access VARCHAR(50) NOT NULL DEFAULT 'ALL',
		obj_tpl_content VARCHAR(100),
		obj_tpl_page VARCHAR(100),
		obj_link VARCHAR(15),
		obj_status ENUM('publish', 'hidden') NOT NULL DEFAULT 'publish',
		PRIMARY KEY  (obj_id)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
$tablesArray[$tablePrefix . 'objects']['insert'] = "INSERT INTO __TABLE__
		(`obj_id`, `obj_data_type`, `obj_canonical`, `obj_name`, `obj_h1`, `obj_title`, `obj_description`, `obj_keywords`, `obj_anons`, `obj_content`, `obj_date_create`, `obj_date_publish`, `obj_lastmod`, `obj_cnt_views`, `obj_rating_up`, `obj_rating_down`, `obj_rating_count`, `obj_user_author`, `obj_ugroups_access`, `obj_tpl_content`, `obj_tpl_page`, `obj_link`, `obj_status`)
		VALUES
		(NULL, '1', '', 'Home page', 'Home page h1', 'Home page title', 'Home page description', 'Home page keywords', 'Home page anons', 'Home page content', '" . date('Y-m-d H:i:s') . "', '" . date('Y-m-d H:i:s') . "', '" . time() . "', '0', '0', '0', '0', '1', 'ALL', 'home-page', 'no-sidebar', '', 'publish');";


// objects_data
$tablesArray[$tablePrefix . 'objects_data']['create'] = "CREATE TABLE __TABLE__ (
		objects_data_id BIGINT(20) NOT NULL AUTO_INCREMENT,
		objects_data_obj BIGINT(20) NOT NULL,
		objects_data_field INT(8) NOT NULL,
		objects_data_value LONGTEXT,
		PRIMARY KEY  (objects_data_id),
		INDEX (objects_data_obj),
		INDEX (objects_data_field)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
$tablesArray[$tablePrefix . 'objects_data']['insert'] = "";


//obj_types
$tablesArray[$tablePrefix . 'obj_types']['create'] = "CREATE TABLE __TABLE__ (
		obj_types_id INT(3) NOT NULL AUTO_INCREMENT,
		obj_types_name VARCHAR(255) NOT NULL,
		obj_types_icon VARCHAR(255) NOT NULL,
		obj_types_descr VARCHAR(255),
		PRIMARY KEY  (obj_types_id)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
$tablesArray[$tablePrefix . 'obj_types']['insert'] = "INSERT INTO __TABLE__
		(`obj_types_id`, `obj_types_name`, `obj_types_icon`, `obj_types_descr`)
		VALUES
		(NULL, 'Страница', 'tree-icon-page.png', 'Тип страница'),
		(NULL, 'Каталог', 'tree-icon-catalog.png', 'Тип Каталог'),
		(NULL, 'Комментарий', 'tree-icon-comment.png', 'Тип Комментарий'),
		(NULL, 'Ссылка', 'tree-icon-link.png', 'Тип Ссылка'),
		(NULL, 'Меню', 'tree-icon-menu.png', 'Тип Меню'),
		(NULL, 'Подкаталог', 'tree-icon-undcatalog.png', 'Тип Подкаталог'),
		(NULL, 'Родительская страница', 'tree-icon-page-parent.png', 'Тип Родительская страница'),
		(NULL, 'Товарная позиция', 'tree-icon-product.png', 'Тип Товарная позиция'),
		(NULL, 'Статья', 'tree-icon-page-blog.png', 'Тип Статья'),
		(NULL, 'Группа товаров', 'tree-icon-product-group.png', 'Тип Группа товаров'),
		(NULL, 'Производитель', 'tree-icon-vendor.png', 'Тип Производитель');";

// options
$tablesArray[$tablePrefix . 'options']['create'] = "CREATE TABLE __TABLE__ (
		options_id INT(8) NOT NULL AUTO_INCREMENT,
		options_key VARCHAR(255) NOT NULL,
		options_value TEXT,
		options_group VARCHAR(100),
		options_last_mod VARCHAR(100),
		options_descr VARCHAR(255),
		PRIMARY KEY  (options_id),
		INDEX (options_key),
		INDEX (options_group)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
$tablesArray[$tablePrefix . 'options']['insert'] = "INSERT INTO __TABLE__
		(`options_id`, `options_key`, `options_value`, `options_group`, `options_last_mod`, `options_descr`)
		VALUES
		(NULL, 'admin_template', 'default', 'general', '" . time() . "', 'Шаблон админ-панели'),
		(NULL, 'admin_lang', 'russian', 'general', '" . time() . "', 'Язык админ-панели'),
		(NULL, 'site_template', 'default', 'site', '" . time() . "', 'Шаблон сайта'),
		(NULL, 'site_lang', 'russian', 'site', '" . time() . "', 'Язык сайта');";

// plugins
$tablesArray[$tablePrefix . 'plugins']['create'] = "CREATE TABLE __TABLE__ (
		plugins_id INT(8) NOT NULL AUTO_INCREMENT,
		plugins_name VARCHAR(255) NOT NULL,
		plugins_folder VARCHAR(255) NOT NULL,
		plugins_version VARCHAR(10) NOT NULL,
		plugins_author VARCHAR(255) NOT NULL,
		plugins_group VARCHAR(10),
		PRIMARY KEY  (plugins_id),
		INDEX (plugins_folder)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
$tablesArray[$tablePrefix . 'plugins']['insert'] = "INSERT INTO __TABLE__
		(`plugins_id`, `plugins_name`, `plugins_folder`, `plugins_version`, `plugins_author`, `plugins_group`)
		VALUES
		(NULL, 'Дерево сайта', 'admin-site-tree', '', '', ''),
		(NULL, 'Редактор страниц', 'admin-page', '', '', ''),
		(NULL, 'Импорт/экспорт CSV', 'exp-csv', '', '', ''),
		(NULL, 'Менеджер файлов', 'elfinder', '', '', '');";

// users_group
$tablesArray[$tablePrefix . 'users_group']['create'] = "CREATE TABLE __TABLE__ (
		users_group_id INT(3) NOT NULL AUTO_INCREMENT,
		users_group_name VARCHAR(255),
		users_group_type ENUM('1', '2', '3', '4', '5') NOT NULL DEFAULT '1',
		users_group_descr VARCHAR(255),
		users_group_status ENUM('publish', 'hidden') NOT NULL DEFAULT 'hidden',
		PRIMARY KEY  (users_group_id)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
$tablesArray[$tablePrefix . 'users_group']['insert'] = "INSERT INTO __TABLE__
		(`users_group_id`, `users_group_name`, `users_group_type`, `users_group_descr`, `users_group_status`)
		VALUES
		(NULL, 'Default', '2', 'Default group', 'publish'),
		(NULL, 'Admin', '1', 'Admin group', 'publish');";

// users
$tablesArray[$tablePrefix . 'users']['create'] = "CREATE TABLE __TABLE__ (
		users_id INT(3) NOT NULL AUTO_INCREMENT,
		users_group INT(3) NOT NULL,
		users_login VARCHAR(255) NOT NULL,
		users_password VARCHAR(255) NOT NULL,
		users_name VARCHAR(255),
		users_image VARCHAR(255),
		users_email VARCHAR(100),
		users_phone VARCHAR(100),
		users_phone_active TINYINT NOT NULL,
		users_date_registr DATETIME NOT NULL,
		users_date_birth DATE NOT NULL,
		users_last_visit VARCHAR(50),
		users_ip_register VARCHAR(50),
		users_activate_key VARCHAR(255),
		users_site_key VARCHAR(255),
		users_activate TINYINT NOT NULL,
		users_lang VARCHAR(20),
		users_status ENUM('publish', 'hidden') NOT NULL DEFAULT 'hidden',
		PRIMARY KEY  (users_id),
		INDEX (users_group)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
$tablesArray[$tablePrefix . 'users']['insert'] = "INSERT INTO __TABLE__
		(`users_id`, `users_group`, `users_login`, `users_password`, `users_name`, `users_image`, `users_email`, `users_phone`, `users_phone_active`, `users_date_registr`, `users_date_birth`, `users_last_visit`, `users_ip_register`, `users_activate_key`, `users_site_key`, `users_activate`, `users_lang`, `users_status`)
		VALUES
		(NULL, '2', '".$userLogin."', '". $userPassword ."', '".$userLogin."', '', '" . $userEmail . "', '', '0', '" . date('Y-m-d H:i:s') . "', '1980-01-01', '" . time() . "', '" . $_SERVER['HTTP_X_REAL_IP'] . "', '". $usersActivateKey . "', '" . $userSiteKey . "', '1', '" . $userLang . "', 'publish');";


//print_r($tablesArray);
?>
