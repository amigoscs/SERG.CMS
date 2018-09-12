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
	$sql = "CREATE TABLE {$prefix}comments (
		comments_id INT(8) NOT NULL AUTO_INCREMENT,
		comments_parent INT(8),
		comments_object BIGINT(20),
		comments_user INT(11),
		comments_author VARCHAR(50),
		comments_date DATETIME NOT NULL,
		comments_ip VARCHAR(20),
		comments_content TEXT,
		comments_ratup SMALLINT(3),
		comments_ratdown SMALLINT(3),
		comments_stars SMALLINT(3),
		comments_status ENUM('publish', 'hidden') NOT NULL DEFAULT 'publish',
		comments_new TINYINT(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (comments_id)
	) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
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

	return true;
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
	$sql = "DROP TABLE `{$prefix}comments`";
	//$CI->db->query($sql);
	return true;
}
