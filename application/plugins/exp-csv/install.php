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
	/*$data = array('test_value' => 'Установка плагина admin-site-tree');
	$CI->db->insert('test', $data);
	*/
	return true;
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
	/*$data = array('test_value' => 'Переустановка плагина admin-site-tree ('.$args['plugin_folder'].')');
	$CI->db->where('test_id', 1);
	$CI->db->update('test', $data);
	*/
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
	/*$data = array('test_value' => 'Удаление плагина admin-site-tree ('.$args['plugin_folder'].')');
	$CI->db->where('test_id', 1);
	$CI->db->update('test', $data);*/
	
	return true;
}