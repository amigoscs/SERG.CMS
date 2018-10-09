<?php defined('BASEPATH') OR exit('No direct script access allowed');
# файл helper плагина
/*
	*  exp-csv helper
	*
	* version 1.0
	* UPD 2018-10-09
	* Добавлена функция expcsv_delete_rn() - удаление переносов для парсера
	*
*/

// удаляет переносы в тексте
function expcsv_delete_rn($matches = array())
{
  if(isset($matches[1])) {
    return str_replace(array('&nbsp;', "\r\n", "\r", "\n", "\t", '  ', '    ', '    '), ' ', $matches[1]);
  } else {
    return $matches[0];
  }
}
