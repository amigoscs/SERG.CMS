<?php defined('BASEPATH') OR exit('No direct script access allowed');
# файл helper плагина

/*Массив изображений. Разделитель _ELF_*/
function elfinder_img_explode($string = '')
{
	$images = app_explode('_ELF_', $string);
	$tmp = array();
	foreach($images as $eiKey => &$eiVal){
		$tmp = array();
		$tmp = explode('##', $eiVal);
		$eiVal = $tmp[0];
	}
	return $images;
}

###
# конфигурация elfinder
###
function elfinder_connector_access($attr, $path, $data, $volume) {
	return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}


/*
*
* Таблица замен символов при загрузке и создании фалов и директорий
*
*/
function elfinder_replaceChars()
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

		"@"=>"", "!"=>"", ";"=>"", ":"=>"", "^"=>"", "\""=>"",
		"&"=>"", "="=>"", "№"=>"", "\\"=>"", "/"=>"", "#"=>"",
		"("=>"", ")"=>"", "~"=>"", "|"=>"", "+"=>"", "”"=>"", "“"=>"",
		"'"=>"",

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
		" "=>"-",
		".JPG"=>".jpg", // расширение JPG => jpg
		".PNG"=>".png", // расширение PNG => png
		);
		return $repl;
}

/*
*
* разрешенные MIME типы файлов
*
*/
function elfinder_mimeAllowUpload($mimeType = 'default')
{
	if($mimeType == 'default')
	{
		$mimeArray = array(
			'image', // изображения
			'text/plain', // текст
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
			'application/pdf', // pdf
			'application/msword', // doc
			'text/rtf', // rtf
		);
	}

	return $mimeArray;
}
