<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
	* SERG.CMS
	*
	* UPD 2017-12-14
	* Version 2.3
	*
	* Хелпер содержит функции для упрощения отображения
	*
	* функция для отладки
	* function pr($par);
	*
	* функция, аналогичная pr, только завершающаяся die()
	* function _pr($par);
	*
	* 2.1
	* Переделана логика работы app_image_thumb
	*
	* 2.2 (2017-12-08)
	* Исправлена обработка multiselect (app_normalize_data_values())
	*
	* 2.3 (2017-12-14)
	* Решена проблема обработки файла с неверным расширением в app_image_thumb
	*
	* 2.4 (2017-12-15)
	* Исправлена ошибка подсчетов количесва просмотров объекта
	*
	* 2.41 (2018-01-11)
	* сохранение и получения кэша идет по группе пользователя
	*
	* 2.5 (2018-01-15)
	* переход на версию 4.0
	*
	* 2.51 (2018-02-15)
	* исправлена ошибка получения кэша при пустом файле
	*
	* 2.52 (2018-04-02)
	* сериализация значения при добавлении опции или извлечении (app_get_option, app_add_option)
	*
	* 2.53 (2018-04-09)
	* правки в функции пагинации
	*
	* 2.54 (2018-04-16)
	* check_admin -> checkAdmin
	*
	* 2.55 (2018-05-11)
	* Доработана PAGEINIT()
	*
	* 2.56 (2018-06-08)
	* Правка app_get_cache()
	*
	* 2.57 (2018-09-07)
	* app_date_convert() вместо date_convert()
*/

$CI = &get_instance();

define("N", "\n"); // перенос строки
define("NN", "\n\n"); // двойной перенос строки
define("TAB", "\t"); // табулятор
define("NTAB", "\n\t"); // перенос + табулятор

define("APP_BASE_PATH", realpath(APPPATH . '../') . '/');

define("APP_BASE_URL", $CI->config->item('base_url'));
define("APP_BASE_URL_NO_SLASH", rtrim(APP_BASE_URL, '/'));

define("APP_SITE_TEMPLATES_PATH", APPPATH . 'views/templates/');

define("APP_CMS_TEMPLATES_PATH", APPPATH . 'views/admin/templates/');

define("APP_PLUGINS_DIR_PATH", APPPATH . 'plugins/');
define("APP_PLUGINS_URL", APP_BASE_URL . 'application/plugins/');

define("APP_CACHE_DIR_PATH", APPPATH . $CI->config->item('cache_path') . 'app/');

define("APP_ADMIN_URL", APP_BASE_URL . 'admin');

define("CMSVERSION", $CI->config->item('CMSVERSION'));

# функция для отладки
function pr($var, $html = false, $echo = true)
{
	if (!$echo) ob_start();
		else echo '<pre>';
	if (is_bool($var))
	{
		if ($var) echo 'TRUE';
			else echo 'FALSE';
	}
	else
	{
		if ( is_scalar($var) )
		{
			if (!$html) echo $var;
				else
				{
					$var = str_replace('<br />', "<br>", $var);
					$var = str_replace('<br>', "<br>\n", $var);
					$var = str_replace('</p>', "</p>\n", $var);
					$var = str_replace('<ul>', "\n<ul>", $var);
					$var = str_replace('<li>', "\n<li>", $var);
					$var = htmlspecialchars($var);
					$var = wordwrap($var, 300);
					echo $var;
				}
		}
			else print_r ($var);
	}
	if (!$echo)
	{
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}
	else echo '</pre>';
}

# используется для отладки с помощью прерывания
function _pr($var, $html = false, $echo = true)
{
	pr($var, $html, $echo);
	die();
}

# языковая функция
function app_lang($item = '')
{
	$CI = &get_instance();
	return $CI->lang->line($item);
}

# доступные языки приложения
function app_all_lang()
{
	$CI = &get_instance();
	$langArray = array();
	$CI->load->helper('directory');
	$mapDir = directory_map(APPPATH . 'language', 1);

	foreach($mapDir as $key => $value)
	{
		if(strpos($value, '/'))
			$langArray[str_replace('/', '', $value)] = str_replace('/', '', $value);
	}
	return $langArray;
}

# возвращает ссылки
function info($key = '')
{
	if(!$key) return '';
	$CI = &get_instance();

	switch($key)
	{
		case 'timeZone':
		case 'time_zone':
			return app_get_option('server_time_offset', 'general', 0);
			break;

		case 'baseUrl':
		case 'base_url':
			return APP_BASE_URL;
			break;

		case 'baseUrlTrim':
			return APP_BASE_URL_NO_SLASH;
			break;
		case 'base_path':
			return APP_BASE_PATH;
			break;

		case 'adminUrl':
		case 'admin_url':
			return APP_ADMIN_URL;
			break;

		case 'pluginsDir':
		case 'plugins_dir':
			return APP_PLUGINS_DIR_PATH;
			break;

		case 'pluginsUrl':
		case 'plugins_url':
			return APP_PLUGINS_URL;
			break;

		case 'adminTemplate':
		case 'admin_template':
			return app_get_option('admin_template', 'general', 'default');
			break;

		case 'siteTemplate':
		case 'site_template':
			return app_get_option('site_template', 'site', 'default');
			break;

		case 'notFoundTemplate':
		case 'not_found_template':
			return app_get_option('not_found_template', 'site', '');
			break;

		case 'templatesDir':
		case 'templates_dir':
			return APPPATH . 'views/templates/';
			break;

		case 'templateTir':
		case 'template_dir':
			return APPPATH . 'views/templates/' . app_get_option('site_template', 'site', 'default') . '/';
			break;

		case 'cacheDir':
		case 'app_cache_dir':
			return APP_CACHE_DIR_PATH;
			break;

		// массив типов полей
		case 'typesFields':
			return array(
					'text'  => 'Однострочный текст',
					'textarea'  => 'Многострочный текст',
					'select'  => 'Выпадающий список',
					'multiselect'  => 'Множественный список',
					'number'  => 'Число целое',
					'numberfloat'  => 'Число дробное',
					'image'  => 'Изображение',
					'file'  => 'Файл',
					'checkbox' => 'Чекбокс',
					'date' => 'Дата',
					'datetime' => 'Дата и время',
					'editor' => 'Визуальный редактор'
			);
			break;

		default: return '';
	}
}

# формирует меню из массива. Рекурсивная
/* принимает параметр - массив меню
'0' => array(
			'name' => 'Ссылка 1',
			'link' => 'link1',
			'children' => array(),
			),
'1' => array(
			'name' => 'Ссылка 2',
			'link' => 'link2',
			'children' => array(),
			),
'2' => array(
			'name' => 'Ссылка 3',
			'link' => 'link3',
			'children' => array(),
			),
),
*/
/*function create_menu_rec($par = array())
{
	$out = '';
	foreach($par as $val)
	{
		$out .= '<li>';
		$out .= '<a href="'.$val['link'].'">';
		$out .= $val['name'];
		$out .= '</a>';
		if($val['children'])
		{
			$out .= '<ul class="child">';
			$out .= create_menu_rec($val['children']);
			$out .= '</ul>';
		}

		$out .= '</li>';

	}
	return $out;
}*/

# функция преобразования MySql-даты (ГГГГ-ММ-ДД ЧЧ:ММ:СС) в указанный формат date
# идея - http://dimoning.ru/archives/31
# $days и $month - массивы или строка (через пробел) названия дней недели и месяцев
function app_date_convert($format = 'Y-m-d H:i:s', $data, $timezone = true, $days = false, $month = false) {
	return date_convert($format, $data, $timezone, $days, $month);
}
function date_convert($format = 'Y-m-d H:i:s', $data, $timezone = true, $days = false, $month = false)
{
	$res = '';
	$part = explode(' ' , $data);

	if (isset($part[0])) $ymd = explode ('-', $part[0]);
		else $ymd = array (0,0,0);

	if (isset($part[1])) $hms = explode (':', $part[1]);
		else $hms = array (0,0,0);

	$y = $ymd[0];
	$m = $ymd[1];
	$d = $ymd[2];
	$h = $hms[0];
	$n = $hms[1];
	$s = $hms[2];

	$time = mktime($h, $n, $s, $m, $d, $y);

	if ($timezone)
	{
		if ($timezone === -1) // в случаях, если нужно убрать таймзону
			$time = $time - info('time_zone') * 60 * 60;
		else
			$time = $time + info('time_zone') * 60 * 60;
	}

	$out = date($format, $time);

	if ($days)
	{
		if (!is_array($days)) $days = explode(' ', trim($days));

		$day_en = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
		$out = str_replace($day_en, $days, $out);

		$day_en = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
		$out = str_replace($day_en, $days, $out);
	}
	if ($month)
	{
		if (!is_array($month)) $month = explode(' ', trim($month));

		//$out = str_replace(' ', '_', $out);

		$month_en = array('January', 'February', 'March', 'April', 'May', 'June', 'July',	'August', 'September', 'October', 'November', 'December');
		$out = str_replace($month_en, $month, $out);

		# возможна ситуация, когда стоит английский язык, поэтому следующая замена приведет к ошибке
		# поэтому заменим $month_en на что-то своё


		$out = str_replace($month_en, array('J_anuary', 'F_ebruary', 'M_arch', 'A_pril', 'M_ay', 'J_une', 'J_uly',	'A_ugust', 'S_eptember', 'O_ctober', 'N_ovember', 'D_ecember'), $out);

		$month_en = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$out = str_replace($month_en, $month, $out);

		# теперь назад
		$out = str_replace(
				array('J_anuary', 'F_ebruary', 'M_arch', 'A_pril', 'M_ay', 'J_une', 'J_uly',	'A_ugust', 'S_eptember', 'O_ctober', 'N_ovember', 'D_ecember'),
				array('January', 'February', 'March', 'April', 'May', 'June', 'July',	'August', 'September', 'October', 'November', 'December'), $out);


	}

	return $out;
}

# функция определяет, что идет запрос ajax
function is_ajax()
{
	$CI = &get_instance();
	if (!$CI->input->is_ajax_request())
		return FALSE;

	return TRUE;

	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			return true;
		}
		else
		{
			return false;
		}

}

# возвращает опции
function app_get_option($key, $group, $return = '', $description = FALSE)
{
	$options = app_get_option_array();
	if(isset($options[$group][$key]))
	{
		if($description)
		{
			if($options[$group][$key]['description'])
				return $options[$group][$key]['description'];
			else
				return $return;
		}
		else
		{
			if($options[$group][$key]['value'])
			{
				$result = $options[$group][$key]['value'];
				// проверяем на сериализацию
				if (@preg_match( '|_serialize_|A', $result))
				{
					$result = preg_replace( '|_serialize_|A', '', $result, 1 );
					$result = @unserialize($result);
				}
				return $result;
			}
			else
			{
				return $return;
			}
		}
	}
	else
	{
		return $return;
	}
}
# возвращает массив опций
function app_get_option_array()
{
	if($res = app_get_cache('app-options'))
		return $res;

	$options = array();
	$CI = &get_instance();
	$query = $CI->db->get('options');
	foreach($query->result_array() as $row)
	{
		$options[$row['options_group']][$row['options_key']]['value'] = $row['options_value'];
		$options[$row['options_group']][$row['options_key']]['description'] = $row['options_descr'];
	}
	app_add_cache('app-options', $options);
	return $options;
}

# добавляет опцию
function app_add_option($key, $value, $group, $descr = FALSE)
{
	$CI = &get_instance();
	$CI->db->where('options_key', $key);
	$CI->db->where('options_group', $group);
	$query = $CI->db->get('options');

	# если value массив или объект, то серилизуем его в строку
	if(!is_scalar($value)){
		$value = '_serialize_' . serialize($value);
	}

	// такой уже есть, тогда просто обновить
	if($query->num_rows())
	{
		$data = array(
			'options_value' => trim($value),
			'options_last_mod' => time(),
			);
		if($descr !== FALSE)
			$data['options_descr'] = $descr;

		$CI->db->where('options_key', $key);
		$CI->db->where('options_group', $group);
		$CI->db->update('options', $data);

	}
	else
	{
		$data = array(
			'options_key' => trim($key),
			'options_value' => trim($value),
			'options_group' => trim($group),
			'options_last_mod' => time(),
			);
		if($descr === FALSE)
			$data['options_descr'] = '';
		else
			$data['options_descr'] = $descr;

		$CI->db->insert('options', $data);
	}
	// очистим кеш
	app_delete_cash();
	return TRUE;
}

// возвращает массив шаблонов для сайта
function app_get_site_templates($level = 2)
{
	$CI = &get_instance();
	$templates = array();
	$CI->load->helper('directory');
	$map_dir_templates = directory_map(info('templates_dir'), $level);
	
	if(!$map_dir_templates) {
		return array();
	}

	foreach($map_dir_templates as $key => $value)
	{
		if(!is_array($value)) {
			continue;
		}

		if(!in_array('info.php', $value)) {
			continue;
		}

		require(info('templates_dir') . '' . $key . 'info.php');
		$key = str_replace('/', '', $key);
		$templates[$key] = $value;
		$templates[$key]['info'] = $info;
	}
	return $templates;
}

// возвращает массив шаблонов страниц для объекта
function app_get_pages_templates()
{
	$out = array('pages' => array(), 'contents' => array());
	$CI = &get_instance();
	$CI->load->helper('directory');
	$active_template = app_get_option('site_template', 'site', 'default');
	$map_dir = directory_map(info('templates_dir') . $active_template . '/', 2);
	//pr($map_dir);
	foreach($map_dir as $key => $value)
	{
		if(!is_array($value) and preg_replace("/(.*)\.(.*)/i", '$2', $value) == 'php')
		{
			if($value != 'info.php')
				$out['pages'][preg_replace("/(.*)\.(.*)/i", '$1', $value)] = $value;
		}
		elseif($key == 'contents/')
		{
			foreach($value as $val)
			{
				if(strpos($val, '/') != FALSE)
					continue;

				$out['contents'][preg_replace("/(.*)\.(.*)/i", '$1', $val)] = $val;
			}
		}
	}

	ksort($out['pages']);
	ksort($out['contents']);

	return $out;
}

// возвращает список всех плагинов из папки plugins с информацией
function app_get_all_plugins($notLoadPlugins = array())
{
	$CI = &get_instance();
	$plugins = array();
	$CI->load->helper('directory');
	$map_dir_plugins = directory_map(info('plugins_dir'), 2);
	if(!$map_dir_plugins) return array();

	foreach($map_dir_plugins as $key => $value)
	{
		if(!in_array('autoload.php', $value))
			continue;

		require(info('plugins_dir') . '' . $key . 'autoload.php');
		$key = str_replace('/', '', $key);
		$plugins[$key] = $value;
		$plugins[$key]['info'] = $info;
	}
	return $plugins;
}

# установка плагина
function plugin_install($args = array())
{
	$CI = &get_instance();
	$data = array();
	$data['plugins_name'] = isset($args['name']) ? $args['name'] : 'not plugin';
	$data['plugins_folder'] = isset($args['folder']) ? $args['folder'] : 'not_folder';
	$data['plugins_version'] = isset($args['version']) ? $args['version'] : 0;
	$data['plugins_author'] = isset($args['author']) ? $args['author'] : '';
	$data['plugins_group'] = isset($args['group']) ? $args['group'] : '';

	// проверка
	$CI->db->select('plugins_id');
	$CI->db->where('plugins_folder', $data['plugins_folder']);
	$query = $CI->db->get('plugins');


	# плагин установлен. Значит переустановка
	if($query->num_rows())
	{
		$plugin_reinstall_file = info('plugins_dir') . $data['plugins_folder'] . '/_reinstall/reinstall.php';
		$reInstallFolderPath = info('plugins_dir') . $data['plugins_folder'] . '/_reinstall';

		$plugin_id = $query->row('plugins_id');;
		$folder = $data['plugins_folder'];
		unset($data['plugins_folder']);

		$CI->db->where('plugins_id', $plugin_id);
		$upd = $CI->db->update('plugins', $data);
		$upd ? 1 : 0;
		if(file_exists($plugin_reinstall_file))
		{
			require($plugin_reinstall_file);
			if(function_exists('plugin_plugin_reinstall'))
				plugin_plugin_reinstall(
					array(
						'plugin_id' => $plugin_id,
						'plugin_folder' => $folder
						)
			);
			# удалим папку с переустановочными файлами
			app_rdir($reInstallFolderPath);
		}
		return $upd;
	}
	else
	{
		$plugin_install_file = info('plugins_dir') . $data['plugins_folder'] . '/install.php';
		# читая установка плагина
		$ins = $CI->db->insert('plugins', $data);
		$ins ? 1 : 0;

		if(file_exists($plugin_install_file))
		{
			require($plugin_install_file);
			if(function_exists('plugin_plugin_install'))
				plugin_plugin_install(
					array(
						'plugin_id' => $CI->db->insert_id(),
						'plugin_folder' => $data['plugins_folder']
						)
			);
		}
		return $ins;
	}
}

# деинсталяция плагина
function plugin_uninstall($plugin_folder = '')
{
	$CI = &get_instance();
	$data = array();
	// проверка
	$CI->db->where('plugins_folder', $plugin_folder);
	$query = $CI->db->get('plugins');
	if($query->num_rows())
	{
		$plugin_install_file = info('plugins_dir') . $plugin_folder . '/install.php';
		$CI->db->delete('plugins', array('plugins_folder' => $plugin_folder));
		if(file_exists($plugin_install_file))
		{
			require($plugin_install_file);
			if(function_exists('plugin_plugin_uninstall'))
				plugin_plugin_uninstall(
					array(
						'plugin_folder' => $plugin_folder
					)
			);
		}
	}
	else
	{
		return false;
	}
}


# загрузка установленных плагинов
function plugin_load()
{
	$CI = &get_instance();
	$out = array();
	$CI->db->select('plugins_id, plugins_name, plugins_folder');
	$query = $CI->db->get('plugins');
	foreach($query->result_array() as $row)
	{
		$out[$row['plugins_folder']] = $row;
	}
	return $out;
}

# полное удаление директории
function app_rdir($path) {
	 // если путь существует и это папка
	 if ( file_exists( $path ) AND is_dir( $path ) ) {
	   // открываем папку
		$dir = opendir($path);
		while ( false !== ( $element = readdir( $dir ) ) ) {
		  // удаляем только содержимое папки
		  if ( $element != '.' AND $element != '..' )  {
			$tmp = $path . '/' . $element;
			chmod( $tmp, 0777 );
		   // если элемент является папкой, то
		   // удаляем его используя нашу функцию RDir
			if ( is_dir( $tmp ) ) {
			 app_rdir( $tmp );
		   // если элемент является файлом, то удаляем файл
			} else {
			  unlink( $tmp );
		   }
		 }
	   }
	   // закрываем папку
		closedir($dir);
		// удаляем саму папку
	   if ( file_exists( $path ) ) {
		 rmdir( $path );
	   }
	 }
}

# копирование директории
function app_copydirect($source, $dest, $over=false)
{
    if(!is_dir($dest))
        mkdir($dest);
    if($handle = opendir($source))
    {
        while(false !== ($file = readdir($handle)))
        {
            if($file != '.' && $file != '..')
            {
                $path = $source . '/' . $file;
                if(is_file($path))
                {
                    if(!is_file($dest . '/' . $file || $over))
                        if(!@copy($path, $dest . '/' . $file))
                        {
                            echo "('.$path.') Ошибка!!! ";
                        }
                }
                elseif(is_dir($path))
                {
                    if(!is_dir($dest . '/' . $file))
                        mkdir($dest . '/' . $file);
                    app_copydirect($path, $dest . '/' . $file, $over);
                }
            }
        }
        closedir($handle);
    }
}

# парсит строчное представление массива для селекта
/*function parse_str_sel_to_arr($value = '')
{
	if(!$value) return array();
	$out = array();
	$args = explode('##', $value);

	foreach($args as $val)
	{
		$tmp = array();
		$tmp = explode('||', $val);
		$out[trim($tmp[0])] = trim($tmp[1]);
	}
	return $out;
}*/

# функция преобразует русские и украинские буквы в английские
# также удаляются все служебные символы
function app_translate($text)
{
	// таблица замены
	$repl = array(
	"А"=>"a", "Б"=>"b",  "В"=>"v",  "Г"=>"g",   "Д"=>"d",
	"Е"=>"e", "Ё"=>"jo", "Ж"=>"zh",
	"З"=>"z", "И"=>"i",  "Й"=>"j",  "К"=>"k",   "Л"=>"l",
	"М"=>"m", "Н"=>"n",  "О"=>"o",  "П"=>"p",   "Р"=>"r",
	"С"=>"s", "Т"=>"t",  "У"=>"u",  "Ф"=>"f",   "Х"=>"h",
	"Ц"=>"c", "Ч"=>"ch", "Ш"=>"sh", "Щ"=>"shh", "Ъ"=>"",
	"Ы"=>"y", "Ь"=>"",   "Э"=>"e",  "Ю"=>"u", "Я"=>"ja",

	"а"=>"a", "б"=>"b",  "в"=>"v",  "г"=>"g",   "д"=>"d",
	"е"=>"e", "ё"=>"jo", "ж"=>"zh",
	"з"=>"z", "и"=>"i",  "й"=>"j",  "к"=>"k",   "л"=>"l",
	"м"=>"m", "н"=>"n",  "о"=>"o",  "п"=>"p",   "р"=>"r",
	"с"=>"s", "т"=>"t",  "у"=>"u",  "ф"=>"f",   "х"=>"h",
	"ц"=>"c", "ч"=>"ch", "ш"=>"sh", "щ"=>"shh", "ъ"=>"",
	"ы"=>"y", "ь"=>"",   "э"=>"e",  "ю"=>"u",  "я"=>"ja",

	# украина
	"Є" => "ye", "є" => "ye", "І" => "i", "і" => "i",
	"Ї" => "yi", "ї" => "yi", "Ґ" => "g", "ґ" => "g",

	# беларусь
	"Ў"=>"u", "ў"=>"u", "'"=>"",

	# румынский
	"ă"=>'a', "î"=>'i', "ş"=>'sh', "ţ"=>'ts', "â"=>'a',

	"«"=>"", "»"=>"", "—"=>"-", "`"=>"", " "=>"-",
	"["=>"", "]"=>"", "{"=>"", "}"=>"", "<"=>"", ">"=>"",

	"?"=>"", ","=>"", "*"=>"", "%"=>"", "$"=>"",

	"@"=>"-", "!"=>"-", ";"=>"-", ":"=>"", "^"=>"", "\""=>"-",
	"&"=>"-", "="=>"-", "№"=>"", "\\"=>"-", "/"=>"-", "#"=>"-",
	"("=>"-", ")"=>"-", "~"=>"-", "|"=>"-", "+"=>"-", "”"=>"-", "“"=>"-",
	"'"=>"-",

	"’"=>"",
	"—"=>"-", // mdash (длинное тире)
	"–"=>"-", // ndash (короткое тире)
	"™"=>"tm", // tm (торговая марка)
	"©"=>"c", // (c) (копирайт)
	"®"=>"r", // (R) (зарегистрированная марка)
	"…"=>"", // (многоточие)
	"“"=>"",
	"”"=>"",
	"„"=>"",

	);

	$text = strtr(trim($text), $repl);
	$text = htmlentities($text); // если есть что-то из юникода
	$text = strtr(trim($text), $repl);
	$text = strtolower($text);

	# разрешим расширение .html
	$text = str_replace('.htm', '@HTM@', $text);
	$text = str_replace('.', '', $text);
	$text = str_replace('@HTM@', '.htm', $text);

	$text = str_replace('---', '-', $text);
	$text = str_replace('--', '-', $text);

	$text = str_replace('-', ' ', $text);
	$text = str_replace(' ', '-', trim($text));
	return $text;
}


# сохранить в кэш
function app_add_cache($id, $value)
{
	$CI = & get_instance();
	$cache_path = 'application/cache/app/';

	if ( !is_dir($cache_path) OR ! is_writable($cache_path))
		return false;

	# кэш хранится для каждой группы пользователей свой
	if($userGroup = $CI->session->userdata('group')){
		$id = $id . $userGroup;
	}

	$file_path = $cache_path . 'app_' . md5($id);
	$fp = @fopen($file_path, 'wb');
	flock($fp, LOCK_EX);
	fwrite($fp, serialize($value));
	flock($fp, LOCK_UN);
	fclose($fp);
}

# полчить из кэша
function app_get_cache($id, $return = FALSE)
{
	$CI = & get_instance();
	# кэш хранится для каждой группы пользователей свой
	if($userGroup = $CI->session->userdata('group')){
		$id = $id . $userGroup;
	} else {
		$id = $id . 'all';
	}


	$cache_path = 'application/cache/app/';
	$file_path = $cache_path . 'app_' . md5($id);

	if (!$fp = @fopen($file_path, 'rb')) return $return;

	flock($fp, LOCK_SH);


	if(filesize($file_path) > 0)
	{
		$cache = fread($fp, filesize($file_path));
		flock($fp, LOCK_UN);
		fclose($fp);
		return unserialize($cache);
	}
	else
	{
		flock($fp, LOCK_UN);
		fclose($fp);
		return $return;
	}
}

# очистить кеш
function app_delete_cash($id = FALSE)
{
	$cache_path = 'application/cache/app/';
	$scanned_directory = array_slice(scandir($cache_path), 2);
	foreach($scanned_directory as $val)
	{
		if(is_file($cache_path . $val) and $val != '.htaccess' and $val != 'index.html')
		{
			unlink($cache_path . $val);
		}
	}
}


#  валидность email
function app_valid_email($email = '')
{
	# из helpers/email_helper.php
	return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) ? FALSE : TRUE;
}

# проверка на логин
function is_login()
{
	$CI = & get_instance();
	return $CI->LoginAdmModel->checkLogin();
}

# проверка на админа
function is_admin()
{
	$CI = & get_instance();
	return $CI->LoginAdmModel->checkAdmin();
}



# генератор превьюшек
/*
	$source - относительный путь к картинке без ведущего слэша
*/
function app_image_thumb($source = '', $size_x = 0, $size_y = 0, $placeholder = '', $method = 'resize', $thumbPath = 'uploads/thumb/', $placeholderPath = 'uploads/placeholder/')
{

	$CI = & get_instance();
	if(!$source)
		return $placeholderPath . $placeholder;

	// расширение файла
	//$ext = substr(strrchr($source, DIRECTORY_SEPARATOR), 1);
	$ext = strtolower(substr(strrchr($source, "."), 1));

	# если расширение не от картинки
	if($ext != 'jpg' and $ext != 'jpeg' and $ext != 'png' and $ext != 'gif' and $ext != 'bmp')
		return $placeholderPath . $placeholder;

	$imagePath = APP_BASE_PATH . $source;
	$fileResizeName = md5($source) . '-' . $size_x . '-' . $size_y . '.' . $ext;

	if(file_exists(APP_BASE_PATH . $thumbPath . $fileResizeName)) {
		return $thumbPath . $fileResizeName;
	}

	# дополнительная проверка. Возможно файл имеет неверное расширение
	if(@!getimagesize($imagePath))
		return $placeholderPath . $placeholder;

	$config['image_library'] = 'gd2';
	$config['source_image']	= $imagePath;
	$config['create_thumb'] = TRUE;
	$config['maintain_ratio'] = TRUE;
	$config['width'] = $size_x;
	$config['height'] = $size_y;
	$config['thumb_marker'] = '';
	$config['new_image'] = $thumbPath . $fileResizeName;


	$CI->image_lib->clear();
	switch($method)
	{
		case 'resize':
			$CI->image_lib->initialize($config);
			$CI->image_lib->resize();
			break;
		case 'crop':
			$CI->image_lib->initialize($config);
			$CI->image_lib->crop();
			break;
		case 'resize_center_crop':
			$CI->image_lib->get_image_properties($config['source_image']);

			$w = $CI->image_lib->orig_width / $config['width'];
			$h = $CI->image_lib->orig_height / $config['height'];

			if($w > $h) {
				$config['width'] = 0;
				$config['height'] = $size_y;
			} else {
				$config['width'] = $size_x;
				$config['height'] = 0;
			}

			$CI->image_lib->clear();
			$CI->image_lib->initialize($config);

			$x_axis = round($CI->image_lib->width / 2 - $size_x / 2);
			$y_axis = round($CI->image_lib->height / 2 - $size_y / 2);

			$CI->image_lib->clear();
			$CI->image_lib->initialize($config);

			$CI->image_lib->resize();
			$pathNewFile = $CI->image_lib->full_dst_path;

			$CI->image_lib->clear();

			$config['image_library'] = 'gd2';
			$config['source_image']	= $pathNewFile;
			$config['create_thumb'] = TRUE;
			$config['maintain_ratio'] = FALSE; // без пропорций
			$config['width'] = $size_x;
			$config['height'] = $size_y;
			$config['thumb_marker'] = '';
			$config['new_image'] = $pathNewFile;
			$config['x_axis'] = $x_axis;
			$config['y_axis'] = $y_axis;

			$CI->image_lib->initialize($config);

			$CI->image_lib->crop();
			break;

		default:
			return $source;
	}

	if(!$CI->image_lib->display_errors()) {
		return $thumbPath . $fileResizeName;
	}else{
		return $placeholderPath . $placeholder;
	}
}

/*аналог explode*/
function app_explode($sep = '/', $string = '')
{
	$string = trim($string);
	if(!$string) return array(0 => '');

	$out = explode($sep, $string);
	return array_map('trim', $out);
}



# отделение пробелом тысячи, миллионы и т.д.
function app_format_num($s) {
	// если есть разделитель копеек (разделитель точка)
	if(strpos($s, '.') !== FALSE) {
		list($c_a,$c_b) = explode('.',$s);
		$s = $c_a;
		$flag = TRUE;
	}else{
		$flag = FALSE;
	}

	$m = '';
    $c  = strlen($s);
    $ar = preg_split('//', $s, -1, PREG_SPLIT_NO_EMPTY);
    $i  = 0;
    while($c--){if($i == 3){ $m .=' '.$ar[$c]; $i = 0;}else{ $m .= $ar[$c];}$i++;}

    $t_out = strrev($m);
	if($flag) {
		$out = $t_out.'.'.$c_b;
	}else{
		$out = $t_out;
	}


	return $out;
	//return strrev($m);
}

# распарсивание строковых значений select в массив
// no || Нет ## yes || Да ВЕРНЕТ array('no' => 'Нет', 'yes' => 'Да')
function app_parse_select_values($string = '', $selectValue = '')
{
	if(!$string)
		return array();

	$args = explode('##', $string);
	$out = array();
	foreach($args as $sVal)
	{
		list($a, $b) = explode('||', $sVal);
		$out[trim($a)] = trim($b);
	}

	if($selectValue and isset($out[$selectValue]))
	{
		return $out[$selectValue];
	}
	else
	{
		return $out;
	}
}

# создание flash-data
function app_flash_data_set($key, $value)
{
	$CI = & get_instance();
	$CI->session->set_flashdata($key, $value);
}

# получение flash-data
function app_flash_data($key, $return)
{
	$CI = & get_instance();
	if($res = $CI->session->flashdata($key)) {
		return $res;
	}else{
		return $return;
	}
}

# преобразует массив canonical в массив ссылок
function app_create_canonical_links($canonicalArray = array(), $blank = FALSE)
{
	$out = array('links' => array(), 'single' => array());
	if(!isset($canonicalArray['url']) and !isset($canonicalArray['name']))
		return $out;

	$blank ? $blankSt = ' target = "_blank" ' : $blankSt = ' ';

	$link = '';
	foreach($canonicalArray['url'] as $key => $value) {
		if($value == 'index')
			continue;

		$link .= '/' . $value;
		$out['links'][$key] = '<a' . $blankSt . 'href="'.$link.'">' . $canonicalArray['name'][$key] . '</a>';
		$out['single'][$key] = $value;
	}
	return $out;
}

# функция фиксирует количество просмотров страницы
function app_set_count_view($objectID = 0, $rowID = 0)
{
	$CI = & get_instance();
	$allObjsView = $CI->session->userdata('view_objects');

	if(!$objectID)
	{
		$CI->db->select('tree_object');
		$CI->db->where('tree_id', $rowID);
		$query = $CI->db->get('tree');
		$objectID = $query->row('tree_object');
		unset($query);
	}

	if(!$allObjsView)
	{
		$allObjsView = array();
		$allObjsView[$objectID] = $objectID;
		$CI->session->set_userdata('view_objects', $allObjsView);
	}
	else
	{
		if(!isset($allObjsView[$objectID]))
		{
			$sel = "UPDATE `{$CI->db->dbprefix}objects` SET `obj_cnt_views` = `obj_cnt_views` + 1 WHERE `obj_id` = '{$objectID}'";
			$CI->db->query($sel);

			$allObjsView[$objectID] = $objectID;
			$CI->session->set_userdata('view_objects', $allObjsView);
		}
	}
	return TRUE;
}

# нормализация значений DATA полей для объекта
function app_normalize_data_values($dataValues = array())
{
	if(!$dataValues)
		return array();
	//parse_str_sel_to_arr($value = '')
	$dataValues['select_values'] = array();
	$dataValues['orig_value'] = $dataValues['objects_data_value'];
	$dataValues['value'] = $dataValues['objects_data_value'];
	$dataValues['date_time'] = array('date' => '', 'time' => '');
	if($dataValues['types_fields_type'] == 'select')
	{
		$dataValues['select_values'] = app_parse_select_values($dataValues['types_fields_values']);
		if(isset($dataValues['select_values'][$dataValues['orig_value']]))
			$dataValues['value'] = $dataValues['select_values'][$dataValues['orig_value']];
	}
	if($dataValues['types_fields_type'] == 'multiselect')
	{
		$dataValues['select_values'] = app_parse_select_values($dataValues['types_fields_values']);
		$dataValues['value'] = explode(',', $dataValues['objects_data_value']);
	}
	if($dataValues['types_fields_type'] == 'date')
	{
		$formatDate = app_get_option('date_format', 'general', 'Y-m-d');
		if($formatDate == 'DD.MM.YYYY')
			$formatDate = 'd.m.Y';
		elseif($formatDate == 'MM/DD/YYYY')
			$formatDate = 'm/d/Y';
		else
			$formatDate = 'Y-m-d';

		$date = new DateTime($dataValues['objects_data_value']);
		$dataValues['value'] = $dataValues['date_time']['date'] = $date->format($formatDate);
		$dataValues['date_time']['time'] = $date->format('H:i:s');
	}

	if($dataValues['types_fields_type'] == 'datetime')
	{
		$formatDate = app_get_option('date_format', 'general', 'Y-m-d');
		if($formatDate == 'DD.MM.YYYY')
			$formatDate = 'd.m.Y H:i:s';
		elseif($formatDate == 'MM/DD/YYYY')
			$formatDate = 'm/d/Y H:i:s';
		else
			$formatDate = 'Y-m-d H:i:s';

		$date = new DateTime($dataValues['objects_data_value']);
		$dataValues['value'] = $dataValues['date_time']['date'] = $date->format($formatDate);
		$dataValues['date_time']['time'] = $date->format('H:i:s');
	}

	return $dataValues;
}

# Возвращает объект страницы из массива параметров страницы
function PAGEINIT($objValues = array())
{
	$CI = & get_instance();
	if($objValues) {
		return $CI->page->load($objValues);
	} else {
		return $CI->page->load($CI->urlobjects->thisObject());
	}
}

# генерирует массив пагинации страниц и смещение выборки
function app_pagination_array($limit = 10, $countRows = 20, $paginationKey = 'page', $countPagesViews = 5)
{
	$CI = &get_instance();
	# всего страниц пагинации
	$paginationPagesCount = ceil($countRows / $limit);
	$currentUrl = $CI->uri->uri_string;
	$getValues = $CI->input->get();
	$pagActivePage = 1;
	if($paginationPage = $CI->input->get($paginationKey))
		$pagActivePage = $paginationPage;

	if($pagActivePage > $paginationPagesCount)
		$pagActivePage = $paginationPagesCount;

	$paginationArray = array();
	$i = 0;
	while ($i < $paginationPagesCount)
	{
		$i++; // Увеличение счетчика
		# для первой страницы не ставим ключ пагинации
		if($i == 1)
			unset($getValues[$paginationKey]);
		else
			$getValues[$paginationKey] = $i;

		if($getValues)
			$paginationArray[$i]['link'] = $currentUrl . '?' . http_build_query($getValues);
		else
			$paginationArray[$i]['link'] = $currentUrl;


		$paginationArray[$i]['selected'] = 0;
		if($i == $pagActivePage)
			$paginationArray[$i]['selected'] = 1;
	}

	if(isset($getValues[$paginationKey])){
		unset($getValues[$paginationKey]);
	}

	return app_pagination_create($paginationArray, $countPagesViews, $getValues);
}

# обработка массива пагинации
function app_pagination_create($pagArray = array(), $countPagesViews = 5, $getValues = '')
{
	$CI = &get_instance();

	if(!$pagArray || count($pagArray) < 2)
		return array();

	$out = array();
	$out['totalPages'] = $totalPages = count($pagArray);
	$out['firstLink'] = array();
	$out['lastLink'] = array();
	$out['prevLink'] = array();
	$out['nextLink'] = array();

	# если страниц меньше лимита, то без изменений
	if($totalPages <= $countPagesViews){
		foreach($pagArray as $key => &$value){
			$value['name'] = $key;
		}
		unset($value);
		$out['pages'] = $pagArray;
		return $out;
	}

	$selectedPage = 0;
	$balans = 0;
	$prevKey = $nextKey = 0;
	# вычислим номер активной страницы
	foreach($pagArray as $key => $value){
		if($value['selected']){
			$selectedPage = $key;
		}
		$balans = $selectedPage - $countPagesViews;
	}

	$prevKey = $selectedPage - 1;
	$nextKey = $selectedPage + 1;

	if(isset($pagArray[$prevKey])){
		$out['firstLink']['link'] = $CI->uri->uri_string;
		if($getValues){
			$out['firstLink']['link'] .= '?' . http_build_query($getValues);
		}
		$out['firstLink']['selected'] = 0;
		$out['firstLink']['name'] = '&laquo&laquo';

		$out['prevLink'] = $pagArray[$prevKey];
		$out['prevLink']['name'] = '&laquo';
	}
	// если баланс в ноле, значит мы посередине
	$startKey = $endKey = 0;
	if($balans >= 0)
	{
		$deg = floor($countPagesViews / 2);
		$startKey = $selectedPage - $deg;
		$endKey = $selectedPage + $deg;

		if($endKey > $totalPages){
			$deg = $endKey - $totalPages;
			$startKey = $startKey - $deg;
			$endKey = $endKey - $deg;
		}
	}
	// если баланс меньше ноля, то смещение вправо
	elseif($balans < 0)
	{
		$startKey = 1;
		$endKey = $selectedPage + abs($balans);
	}
	else
	{
		$startKey = 1;
		$endKey = $totalPages;
	}

	# соберем массив пагинации
	foreach($pagArray as $key => $value){
		if($key < $startKey || $key > $endKey)
			continue;

		$out['pages'][$key] = $value;
		$out['pages'][$key]['name'] = $key;
	}

	if(isset($pagArray[$nextKey])){
		$out['lastLink']['link'] = $pagArray[$totalPages]['link'];
		$out['lastLink']['selected'] = 0;
		$out['lastLink']['name'] = '&raquo&raquo';

		$out['nextLink'] = $pagArray[$nextKey];
		$out['nextLink']['name'] = '&raquo';
	}
	return $out;
}
