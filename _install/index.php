<?php
/**
	* Файл установки SERG.CMS
*/

define('BASEPATH', __DIR__);
define('ROOTPATH', realpath('../') . DIRECTORY_SEPARATOR);
define('SRCPATH', BASEPATH . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR);
define('CMSCONFPATH', ROOTPATH . "application" . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR);

$protocol = 'http://';
if($_SERVER['HTTPS'] == 'on') {
	$protocol = 'https://';
}



# генерация encrypt key
function generate_encrypt_key() {
	$length = 12;
	$chars = 'abdefh7iknr4sty57zAB2DE1F9G00HKN2QRS76TYZ';
	$numChars = strlen($chars);
	$string = '';
	for ($i = 0; $i < $length; $i++) {
		$string .= substr($chars, rand(1, $numChars) - 1, 1);
	}
	return $string;
}

define('SITEURL', $protocol . $_SERVER['HTTP_HOST'] . "/");
define('ENCRYPTKEY', generate_encrypt_key());

?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8">
		<title>SERG.CMS - установка системы</title>
		<link rel="shortcut icon" href="src/assets/serg-cms-icon.png" type="image/x-icon">
		<link rel="stylesheet" href="src/assets/style.css">
	</head>
<body>

<?php

if($_POST)
{

	$DB_replace_array = array(
			'DB_ACTIVEGROUP' 		=> $_POST['mysql']['query_group'],
			'DB_HOST_NAME' 			=> $_POST['mysql']['hostname'],
			'DB_USER_NAME' 			=> $_POST['mysql']['username'],
			'DB_USER_PASSWORD' 		=> $_POST['mysql']['password'],
			'DB_DATABASE' 			=> $_POST['mysql']['database'],
			'DB_DRIVER'				=> $_POST['mysql']['dbdriver'],
			'DB_PREFIX' 			=> $_POST['mysql']['dbprefix'],
	);

	$CONFIG_replace_array = array(
			'CONFIG_BASE_URL' 		=> SITEURL,
			'CONFIG_LANG' 			=> $_POST['config']['lang'],
			'CONFIG_ENCRYPT' 		=> ENCRYPTKEY,
	);

	$userLogin = $_POST['user']['login'];
	$userPassword = $_POST['user']['password'];
	$userEmail = $_POST['user']['email'];
	$userLang = $_POST['config']['lang'];

	# зашифруем пароль для пользователя
	$userPassword = md5( strrev(ENCRYPTKEY . $userPassword) );
	// ключ к сайту для пользователя
	$userSiteKey = md5(ENCRYPTKEY);
	$usersActivateKey = md5(ENCRYPTKEY . $userEmail . $userLogin);

	if($DB_replace_array['DB_DRIVER'] == 'mysqli'){
		require_once(__DIR__ . DIRECTORY_SEPARATOR . 'connectdb' . DIRECTORY_SEPARATOR . 'connectmysqli.php');
	}else if($DB_replace_array['DB_DRIVER'] == 'mysql'){
		require_once(__DIR__ . DIRECTORY_SEPARATOR . 'connectdb' . DIRECTORY_SEPARATOR . 'connectmysql.php');
	}else{
		exit('driver error');
	}

	# создание содержимого файла database
	$databaseFileContent = file_get_contents(CMSCONFPATH . 'database.php');
	$databaseFileContent = str_replace(array_keys($DB_replace_array), array_values($DB_replace_array), $databaseFileContent);

	# создание содержимого файла config
	$configFileContent = file_get_contents(CMSCONFPATH . 'config.php');
	$configFileContent = str_replace(array_keys($CONFIG_replace_array), array_values($CONFIG_replace_array), $configFileContent);


	# содержимое .htaccess
	if($_POST['server']['php'] == 'fastcgi') {
		$htaccessFileContent = file_get_contents(SRCPATH . 'htaccess-fastcgi.txt');
	}else{
		$htaccessFileContent = file_get_contents(SRCPATH . 'htaccess.txt');
	}

	# пишем файлы database, config и htaccess
	file_put_contents(CMSCONFPATH . 'database.php', $databaseFileContent);
	file_put_contents(CMSCONFPATH . 'config.php', $configFileContent);
	file_put_contents(ROOTPATH . '.htaccess', $htaccessFileContent);

	# создадим папки
	$uploadsFolder = ROOTPATH . 'uploads';
	if(!file_exists($uploadsFolder)) {
		mkdir($uploadsFolder, 0777);
	}

	echo '<p><a href="'. SITEURL .'">Complite! Open my site »</a>';
	exit();
}

$error = FALSE;
if(!ini_get('short_open_tag')){
	echo '<p class="error">Включите на сервере короткие php-теги</p>';
	$error = TRUE;
}

if (!version_compare(PHP_VERSION, '5.3', '>=')){
	echo '<p class="error">Версия PHP ниже 5.3. Работа системы невозможна.</p>';
	$error = TRUE;
}

if($error)
	exit('</body></html>');

?>
<div class="main">
		<img src="src/assets/serg-cms-v2-logo.png" alt="SERG.CMS logo" class="system-logo"/>
		<h1>Для установки системы введите необходимые настройки</h1>
	</div>

		<form action="" method="post">

			<input type="hidden" name="mysql[query_group]" value="default"/>

			<input type="hidden" name="config[base_url]" value=""/>
			<input type="hidden" name="config[lang]" value="russian"/>
			<input type="hidden" name="config[encrypt]" value=""/>


			<fieldset>
				<legend>Настройки пользователя сайта</legend>
				<div class="form-control">
					<label>Логин:</label>
					<input type="text" name="user[login]" value="" required/>
				</div>
				<div class="form-control">
					<label>Пароль:</label>
					<input type="text" name="user[password]" value="" required/>
				</div>
				<div class="form-control">
					<label>E-mail:</label>
					<input type="text" name="user[email]" value="" required/>
				</div>
				<div class="form-control">
					<label>Язык:</label>
					<select name="mysql[dbdriver]">
						<option value="russian" selected>Русский</option>
						<option value="english">English</option>
					</select>
				</div>
			</fieldset>

			<fieldset>
				<legend>Соединение с базой MySQL</legend>
				<div class="form-control">
					<label>Имя БД:</label>
					<input type="text" name="mysql[database]" value="" required/>
				</div>
				<div class="form-control">
					<label>Хост:</label>
					<input type="text" name="mysql[hostname]" value="localhost" required/>
				</div>
				<div class="form-control">
					<label>Драйвер:</label>
					<select name="mysql[dbdriver]">
						<option value="mysqli" selected>mysqli</option>
					</select>
				</div>
				<div class="form-control">
					<label>Префикс таблиц:</label>
					<input type="text" name="mysql[dbprefix]" value="default_" required/>
				</div>
				<div class="form-control">
					<label>Пользователь:</label>
					<input type="text" name="mysql[username]" value="" required/>
				</div>
				<div class="form-control">
					<label>Пароль:</label>
					<input type="text" name="mysql[password]" value="" required/>
				</div>
			</fieldset>

			<fieldset>
				<legend>Настройки для сервера</legend>
				<div class="form-control">
					<label>Режим PHP:</label>
					<select name="server[php]">
						<option value="def" selected>Обычный</option>
						<option value="fastcgi">fastCGI</option>
					</select>
				</div>
			</fieldset>
			<div class="form-control" style="justify-content: center">
				<button type="submit" class="but but-success">Сохранить</button>
			</div>
		</form>
</body>
</html>
