<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<h1><?=$this->lang->line('H1_DOCS')?></h1>
<?=$message?>

<? require_once(__DIR__ . '/units/doc-menu.php') ?>

<h2>Расположение шаблона</h2>
<p>Для того, чтобы шаблон стал доступен для подключения, расположите его в дирректории: <code>application/views/templates/</code></p>
<h2>Подключение шаблона</h2>
<p>В меню <strong>Настройки сайта</strong> выберите свой шаблон в настройке <strong>Шаблон сайта</strong></p>
<p><strong>Важно!</strong> Чтобы ваш шаблон был доступен для выбора и подключения, в корневой папке шаблона должен находиться файл <code>info.php</code>. (Подробнее в разделе <em>структура шаблона</em>)</p>

<h2>Стурктура шаблона</h2>
<p>В шаблоне нет ограничений на количесвов файлов и папок за исключением необходимого минимальной базовой структуры файлов и папок. Базовая структура шаблона должна быть следующей (переменные рядом - переменные со значение URL к указанной папке):</p>
<ul class="doc-file-list">
	<li>assets <code>$TEMPLATE_ASSETS_URL</code>
		<ul>
			<li>css <code>$TEMPLATE_CSS_URL</code></li>
			<li>js <code>$TEMPLATE_JS_URL</code></li>
			<li>img <code>$TEMPLATE_IMG_URL</code></li>
		</ul>
	</li>
	<li>contents</li>
		<ul>
			<li>home-content.php</li>
		</ul>
	<li>Libraries
		<ul>
			<li>Contents
				<ul>
					<li>Home-content.php</li>
				</ul>
			</li>
			<li>Template
				<ul>
					<li>Home-template.php</li>
				</ul>
			</li>
		</ul>
	</li>
	<li>login</li>
	<li>info.php (информация о шаблоне)</li>
	<li>ШАБЛОН_404.php (шаблон страницы 404)</li>
	<li>home-template.php (шаблон главной страницы)</li>
</ul>

<h2>Описание файлов и каталогов шаблона</h2>
<h3>Каталог <em>assets</em></h3>
<p><strong>assets</strong> - каталог, в котором хранятся стили и скрипты шаблона. В переменной <code>$TEMPLATE_ASSETS_URL</code> шаблона содержится абсолютный URL до этого каталога.</p>

<h3>Каталог <em>contents</em></h3>
<p>Содержит в себе файлы-шаблоны динамической части сайта (основного контента). Файлы этого каталога выбираются при редактировании страницы в настройке <strong>Шаблон контента</strong>.</p>

<h3>Файл <em>contents/home-template.php</em></h3>
<p>Файл шаблона динамической части страницы (контент). Количество аналогичных файлов не ограничено. Подключаются они в настройке страницы пункт <strong>Шаблон контента</strong></p>

<h3>Каталог <em>Libraries</em></h3>
<p>Каталог, в котором хранятся библитеки фалов <strong>Шаблона контента</strong> и <strong>Шаблона страницы</strong>, которые подключаются перед обработкой файлов-шаблонов. Внутри каталог подразделяется на два каталога - <strong>Contents</strong>, который содержит библиотеки для файлов шаблона контента, и <strong>Template</strong> - в нем хранятся библиотеки для файлов шаблона страницы. Очень важно, чтобы файлы библиотек имели такое же название, как и у файлов-шаблонов и начинались с большой буквы. Например: для файла <code>contents/home-content.php</code> будет подключена библиотека <code>Libraries/Contents/Home-content.php</code></p>
<p>Файл <code>Libraries/Contents/Home-content.php</code> выглядит так:</p>
<pre>
class ContentLib
{
	private $dataParams;
	
	# конструктор
	public function __construct($par) {
		$this->dataParams = $par;
	}
	
	# обязательный метод
	public function runLib()
	{
		// CodeIgniter
		$CI = &get_instance();
		
		// объявим переменную $myVar для шаблона контента.
		$this->dataParams['myVar'] = 'my value';
		
		return $this->dataParams;
	}
	
	// здесь можно писать свои методы
}
</pre>
<p>Файл <code>Libraries/Template/Home-template.php</code> выглядит так:</p>
<pre>
class TemplateLib
{
	private $dataParams;
	
	# конструктор
	public function __construct($par) {
		$this->dataParams = $par;
	}
	
	# обязательный метод
	public function runLib()
	{
		// CodeIgniter
		$CI = &get_instance();
		
		// объявим переменную $myVar для шаблона страницы.
		$this->dataParams['myVar'] = 'my value';
		
		return $this->dataParams;
	}
	
	// здесь можно писать свои методы
}
</pre>

<h3>Каталог <em>login</em></h3>
<p>Содержит файлы-шаблоны регистрации и авторизации.</p>

<h3>Файл <em>info.php</em></h3>
<p>Информация о шаблоне.</p>
<pre>
$info = array(
	'name' => 'Название шаблона',
	'version' => '1.0', // версия шаблона
);
</pre>

<h3>Файл <em>ШАБЛОН_404.php</em></h3>
<p>Шаблон 404 страницы.</p>

<h3>Файл <em>home-template.php</em></h3>
<p>Файл шаблона статичной части страницы (каркас или сетка). Количество аналогичных файлов не ограничено. Подключаются они в настройке страницы пункт <strong>Шаблон страницы</strong></p>

<h2>Переменные файлов шалонов</h2>




