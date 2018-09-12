<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<h1><?=$this->lang->line('H1_DOCS')?></h1>
<?=$message?>

<? require_once(__DIR__ . '/units/doc-menu.php') ?>

<h2>Дерево сайта</h2>

<p>Код PHP в тексте - %{app_get_option('option1', 'site', '')}%</p>
