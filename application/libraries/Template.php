<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
	*
	* Класс перенных шаблона
	*
	*
	* Version 1.0
	* UPD 2018-10-24
	* Синглетон шаблона.
	* Перенные в шаблон добавляеются так: Template::getInstance()->VAR_NAME = VAR_VALUE;
	* Получить значения можно так: Template::getInstance()->VAR_NAME;
	*
*/
class Template {

	static private $_instance = null;
	private $_registry;

	private function __construct() {

	}

	static public function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	static public function set($key, $object) {
		self::getInstance()->_registry[$key] = $object;
	}

	static public function get($key) {
		if(isset(self::getInstance()->_registry[$key])) {
			return self::getInstance()->_registry[$key];
		} else {
			return false;
		}
	}

	public function __set($key, $object) {
		$this->_registry[$key] = $object;
	}

	public function __get($key) {
		if(isset($this->_registry[$key])) {
			return $this->_registry[$key];
		} else {
			return false;
		}
	}

	# возвращает массив переменных
	static public function getVars() {
		return self::getInstance()->_registry;
	}

	# добаляет переменные в массив
	static public function setVars($vars = array()) {
		foreach($vars as $key => $value) {
			self::getInstance()->_registry[$key] = $value;
		}
	}

	# очистка шаблона
	static public function clear() {
		return self::getInstance()->_registry = array();
	}



	private function __wakeup() {

	}



	private function __clone() {

	}
}
