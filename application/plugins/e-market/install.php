<?php defined('BASEPATH') OR exit('No direct script access allowed');
# файл срабатывает во время установки, переустановки и деинсталяции плагина
	/**
	* Фунция срабатывает при установке плагина
	*
	* @param	array('plugin_folder' => 'string', 'plugin_id' => 'int')
	* @return	bool
	*/
function plugin_plugin_install($args = array())
{
	$CI = &get_instance();
	$prefix = $CI->db->dbprefix;
	// ecart
	$sql = "CREATE TABLE {$prefix}ecart (
		ecart_id BIGINT(20) NOT NULL AUTO_INCREMENT,
		ecart_type INT(2),
		ecart_user_key VARCHAR(255),
		ecart_currency INT(2),
		ecart_date_create DATETIME NOT NULL,
		ecart_date_order DATETIME NOT NULL,
		ecart_last_mod VARCHAR(100),
		ecart_ip_create VARCHAR(20),
		ecart_ip_mod VARCHAR(20),
		ecart_num VARCHAR(100),
		ecart_summ FLOAT(10,2),
		ecart_status INT(2),
		ecart_cash_status TINYINT(1) NOT NULL,
		PRIMARY KEY (ecart_id)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	$CI->db->query($sql);

	// ecartcur
	$sql = "CREATE TABLE {$prefix}ecart_currency (
		ecartcur_id INT(2) NOT NULL AUTO_INCREMENT,
		ecartcur_site TINYINT(1) NOT NULL DEFAULT 0,
		ecartcur_products TINYINT(1) NOT NULL DEFAULT 0,
		ecartcur_name VARCHAR(100),
		ecartcur_code VARCHAR(3),
		ecartcur_rate FLOAT(4,2),
		PRIMARY KEY (ecartcur_id)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	$CI->db->query($sql);

	// ecartf
	$sql = "CREATE TABLE {$prefix}ecart_fields (
		ecartf_id INT(4) NOT NULL AUTO_INCREMENT,
		ecartf_parent INT(4),
		ecartf_type ENUM('text', 'textarea', 'select', 'checkbox', 'radio') NOT NULL DEFAULT 'text',
		ecartf_name VARCHAR(100),
		ecartf_label VARCHAR(100),
		ecartf_descr TEXT,
		ecartf_order INT(3),
		ecartf_required TINYINT(1),
		ecartf_status ENUM('publish', 'hidden') NOT NULL DEFAULT 'publish',
		PRIMARY KEY (ecartf_id)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	$CI->db->query($sql);

	// ecartfval
	$sql = "CREATE TABLE {$prefix}ecart_fields_values (
		ecartfval_id BIGINT(20) NOT NULL AUTO_INCREMENT,
		ecartfval_cart_id BIGINT(20),
		ecartfval_field_id INT(4),
		ecartfval_value TEXT,
		PRIMARY KEY (ecartfval_id)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	$CI->db->query($sql);

	// ecartp - products
	$sql = "CREATE TABLE {$prefix}ecart_products (
		ecartp_id BIGINT(20) NOT NULL AUTO_INCREMENT,
		ecartp_cart_id BIGINT(20),
		ecartp_object_id BIGINT(20),
		ecartp_object_sku VARCHAR(50),
		ecartp_obj_name VARCHAR(255),
		ecartp_price VARCHAR(20),
		ecartp_count INT(4),
		ecartp_descr TEXT,
		PRIMARY KEY (ecartp_id)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	$CI->db->query($sql);

	// cart_status
	$sql = "CREATE TABLE {$prefix}ecart_status (
		ecarts_id INT(2) NOT NULL AUTO_INCREMENT,
		ecarts_name VARCHAR(100),
		ecarts_descr TEXT,
		ecarts_status ENUM('publish', 'hidden') NOT NULL DEFAULT 'publish',
		PRIMARY KEY (ecarts_id)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	$CI->db->query($sql);

	// type carts
	$sql = "CREATE TABLE {$prefix}ecart_carts (
		ecarttypes_id INT(2) NOT NULL AUTO_INCREMENT,
		ecarttypes_name VARCHAR(255),
		ecarttypes_descr VARCHAR(255),
		ecarttypes_status ENUM('publish', 'hidden') NOT NULL DEFAULT 'publish',
		PRIMARY KEY (ecarttypes_id)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	$CI->db->query($sql);

	# insert
	$sql = "INSERT INTO {$prefix}ecart_currency
		(`ecartcur_id`, `ecartcur_site`, `ecartcur_products`, `ecartcur_name`, `ecartcur_code`, `ecartcur_cash`)
		VALUES
		(NULL, '1', '1', 'Российский рубль', 'RUR', '1.00'),
		(NULL, '0', '0', 'Евро', 'EUR', '1.00'),
		(NULL, '0', '0', 'Доллар США', 'USD', '1.00')";
	$CI->db->query($sql);

	# insert
	$sql = "INSERT INTO {$prefix}ecart_status
		(`ecarts_id`, `ecarts_name`, `ecarts_descr`, `ecarts_status`)
		VALUES
		(NULL, 'Наполняется', 'Статус используется для корзин, которые не отправлены в заказ', 'publish'),
		(NULL, 'Создан заказ', 'Корзины с этим статусом ожидают обработки', 'publish')";
	$CI->db->query($sql);

	# insert
	$sql = "INSERT INTO {$prefix}ecart_carts
		(`ecarttypes_id`, `ecarttypes_name`, `ecarttypes_descr`, `ecarttypes_status`)
		VALUES
		(NULL, 'Корзина', 'Default type', 'publish'),
		(NULL, 'Лист желаний', 'Default type', 'publish'),
		(NULL, 'Список сравнения', 'Default type', 'publish')";
	$CI->db->query($sql);
	return TRUE;
}

	/**
	* Фунция срабатывает при переустановке плагина
	*
	* @param	array('plugin_folder' => 'string', 'plugin_id' => 'int')
	* @return	bool
	*/
function plugin_plugin_reinstall($args = array())
{
	$CI = &get_instance();

	return TRUE;
}

	/**
	* Фунция срабатывает при деинсталяции плагина
	*
	* @param	array('plugin_folder' => 'string')
	* @return	bool
	*/
function plugin_plugin_uninstall($args = array())
{
	$CI = &get_instance();
	$prefix = $CI->db->dbprefix;
	$sql = "DROP TABLE `{$prefix}ecart`, `{$prefix}ecart_currency`, `{$prefix}ecart_fields`, `{$prefix}ecart_fields_values`, `{$prefix}ecart_products`, `{$prefix}ecart_status`, `{$prefix}ecart_carts`";
	$CI->db->query($sql);
	return TRUE;
}
