<?php defined('BASEPATH') OR exit('No direct script access allowed');

?>
<h1><?= app_lang('h1_site_setting') ?></h1>

<div class="admin-tabs">
	<ul>
		<li><a href="#tabs-1"><?= app_lang('tab_main') ?></a></li>
		<li><a href="#tabs-2"><?= app_lang('tab_seo') ?></a></li>
        <li><a href="#tabs-3"><?= app_lang('TAB_CUSTOM') ?></a></li>
        <li><a href="#tabs-4"><?= app_lang('TAB_OTHER') ?></a></li>
	</ul>

    <form action="<?=info('base_url')?>admin/front_setting" method="post">
    <!--основное-->
    <div id="tabs-1">
    <div class="form-group">
     	<label for="select01"><?=$this->lang->line('input_label_site_template')?></label>
	<?
		$args = array(
			'class' => 'form-control',
			'id' => 'select01',
			'name' => 'option_update[site][site_template]',
			);

	   echo form_dropdown($args, $site_template_options, app_get_option('site_template', 'site', 'default'))
	?>
    <span class="help-block sub-little-text">Code option <i>app_get_option('site_template', 'site', '')</i></span>
    </div>

     <div class="form-group">
     	<label for="select02">Язык сайта</label>
	<?
		$args = array(
			'class' => 'form-control',
			'id' => 'select02',
			'name' => 'option_update[site][site_lang]',
			);
	   echo form_dropdown($args, app_all_lang(), app_get_option('site_lang', 'site', 'english'))
	?>
    <span class="help-block sub-little-text">Code option <i>app_get_option('site_lang', 'site', '')</i></span>
    </div>

      <div class="form-group">
     	<label for="input01"><?=$this->lang->line('input_label_not_found_template')?></label>
		<input type="text" class="form-control" id="input01" placeholder="<?=$this->lang->line('input_label_not_found_template')?>" name="option_update[site][not_found_template]" value="<?= app_get_option('not_found_template', 'site', '') ?>">
        <span class="help-block sub-little-text">Code option <i>app_get_option('not_found_template', 'site', '')</i></span>
      </div>

	<div class="form-group">
		<label for="textarea01">Пользовательское соглашение</label>
		<textarea placeholder="Текст соглашения" class="form-control" id="textarea01" name="option_update[site][site_user_agreement]"><?= app_get_option('site_user_agreement', 'site', '') ?></textarea>
		<span class="help-block sub-little-text">Составьте здесь текст о пользовательском соглашении при регистрации на сайте. Простой HTML, включая ссылку на страницу с соглашением при необходимости.<br /> Code option <i>app_get_option('site_user_agreement', 'site', '')</i></span>
	</div>

	<button type="submit" class="btn btn-primary ng-scope"><?=$this->lang->line('button_save')?></button>
   </div>
   <!--//основное-->

   <!--seo-->
	<div id="tabs-2">
		<div class="form-group">
		<label for="input11"><?=$this->lang->line('input_label_sitename')?></label>
		<input type="text" class="form-control" id="input11" placeholder="<?=$this->lang->line('input_label_sitename')?>" name="option_update[site][site_name]" value="<?= app_get_option('site_name', 'site', '') ?>">
        <span class="help-block sub-little-text">Code option <i>app_get_option('site_name', 'site', '')</i></span>
        </div>
        <div class="form-group">
            <label for="input12"><?=$this->lang->line('input_label_sitetitle')?></label>
            <input type="text" class="form-control" id="input12" placeholder="<?=$this->lang->line('input_label_sitetitle')?>" name="option_update[site][site_title_default]" value="<?= app_get_option('site_title_default', 'site', '') ?>">
            <span class="help-block sub-little-text">Code option <i>app_get_option('site_title_default', 'site', '')</i></span>
        </div>
        <div class="form-group">
            <label for="textarea11"><?=$this->lang->line('input_label_sitedescr')?></label>
            <textarea placeholder="<?=$this->lang->line('input_label_sitedescr')?>" class="form-control" id="textarea11" name="option_update[site][site_description_default]"><?= app_get_option('site_description_default', 'site', '') ?></textarea>
            <span class="help-block sub-little-text">Code option <i>app_get_option('site_description_default', 'site', '')</i></span>
        </div>
        <div class="form-group">
            <label for="textarea12"><?=$this->lang->line('input_label_sitekeywords')?></label>
            <textarea placeholder="<?=$this->lang->line('input_label_sitekeywords')?>" class="form-control" id="textarea12"  name="option_update[site][site_keywords_default]"><?= app_get_option('site_keywords_default', 'site', '') ?></textarea>
            <span class="help-block sub-little-text">Code option <i>app_get_option('site_keywords_default', 'site', '')</i></span>
        </div>
        <button type="submit" class="btn btn-primary ng-scope"><?=$this->lang->line('button_save')?></button>

    </div>
	<!--//seo-->

    <!--tab custom-->
    <div id="tabs-3">
    <div class="form-group">
		<label for="input31"><input type="text" name="option_update[site][option1][descr]" value="<?= app_get_option('option1', 'site', 'option1', TRUE) ?>"/></label>
        <textarea class="form-control" id="input31" placeholder="Option 1" name="option_update[site][option1][value]"><?= app_get_option('option1', 'site', '') ?></textarea>
        <span class="help-block sub-little-text">Code option <i>app_get_option('option1', 'site', '')</i></span>
    </div>

    <div class="form-group">
		<label for="input32"><input type="text" name="option_update[site][option2][descr]" value="<?= app_get_option('option2', 'site', 'option2', TRUE) ?>"/></label>
        <textarea class="form-control" id="input32" placeholder="Option 2" name="option_update[site][option2][value]"><?= app_get_option('option2', 'site', '') ?></textarea>
        <span class="help-block sub-little-text">Code option <i>app_get_option('option2', 'site', '')</i></span>
    </div>

    <div class="form-group">
		<label for="input33"><input type="text" name="option_update[site][option3][descr]" value="<?= app_get_option('option3', 'site', 'option3', TRUE) ?>"/></label>
        <textarea class="form-control" id="input33" placeholder="Option 3" name="option_update[site][option3][value]"><?= app_get_option('option3', 'site', '') ?></textarea>
        <span class="help-block sub-little-text">Code option <i>app_get_option('option3', 'site', '')</i></span>
    </div>

    <div class="form-group">
		<label for="input34"><input type="text" name="option_update[site][option4][descr]" value="<?= app_get_option('option4', 'site', 'option4', TRUE) ?>"/></label>
        <textarea class="form-control" id="input34" placeholder="Option 4" name="option_update[site][option4][value]"><?= app_get_option('option4', 'site', '') ?></textarea>
        <span class="help-block sub-little-text">Code option <i>app_get_option('option4', 'site', '')</i></span>
    </div>

    <div class="form-group">
		<label for="input35"><input type="text" name="option_update[site][option5][descr]" value="<?= app_get_option('option5', 'site', 'option5', TRUE) ?>"/></label>
        <textarea class="form-control" id="input35" placeholder="Option 5" name="option_update[site][option5][value]"><?= app_get_option('option5', 'site', '') ?></textarea>
        <span class="help-block sub-little-text">Code option <i>app_get_option('option5', 'site', '')</i></span>
    </div>

    <div class="form-group">
		<label for="input36"><input type="text" name="option_update[site][option6][descr]" value="<?= app_get_option('option6', 'site', 'option6', TRUE) ?>"/></label>
        <textarea class="form-control" id="input36" placeholder="Option 6" name="option_update[site][option6][value]"><?= app_get_option('option6', 'site', '') ?></textarea>
        <span class="help-block sub-little-text">Code option <i>app_get_option('option6', 'site', '')</i></span>
    </div>

    <div class="form-group">
		<label for="input37"><input type="text" name="option_update[site][option7][descr]" value="<?= app_get_option('option7', 'site', 'option7', TRUE) ?>"/></label>
        <textarea class="form-control" id="input37" placeholder="Option 7" name="option_update[site][option7][value]"><?= app_get_option('option7', 'site', '') ?></textarea>
        <span class="help-block sub-little-text">Code option <i>app_get_option('option7', 'site', '')</i></span>
    </div>

    <div class="form-group">
		<label for="input38"><input type="text" name="option_update[site][option8][descr]" value="<?= app_get_option('option8', 'site', 'option8', TRUE) ?>"/></label>
        <textarea class="form-control" id="input38" placeholder="Option 8" name="option_update[site][option8][value]"><?= app_get_option('option8', 'site', '') ?></textarea>
        <span class="help-block sub-little-text">Code option <i>app_get_option('option8', 'site', '')</i></span>
    </div>

    <div class="form-group">
		<label for="input39"><input type="text" name="option_update[site][option9][descr]" value="<?= app_get_option('option9', 'site', 'option9', TRUE) ?>"/></label>
        <textarea class="form-control" id="input39" placeholder="Option 9" name="option_update[site][option9][value]"><?= app_get_option('option9', 'site', '') ?></textarea>
        <span class="help-block sub-little-text">Code option <i>app_get_option('option9', 'site', '')</i></span>
    </div>

     <div class="form-group">
		<label for="input310"><input type="text" name="option_update[site][option10][descr]" value="<?= app_get_option('option10', 'site', 'option10', TRUE) ?>"/></label>
        <textarea class="form-control" id="input310" placeholder="Option 10" name="option_update[site][option10][value]"><?= app_get_option('option10', 'site', '') ?></textarea>
        <span class="help-block sub-little-text">Code option <i>app_get_option('option10', 'site', '')</i></span>
    </div>

    <div class="form-group">
		<label for="input311"><input type="text" name="option_update[site][option11][descr]" value="<?= app_get_option('option11', 'site', 'option11', TRUE) ?>"/></label>
        <textarea class="form-control" id="input311" placeholder="Option 11" name="option_update[site][option11][value]"><?= app_get_option('option11', 'site', '') ?></textarea>
        <span class="help-block sub-little-text">Code option <i>app_get_option('option11', 'site', '')</i></span>
    </div>

    <div class="form-group">
		<label for="input312"><input type="text" name="option_update[site][option12][descr]" value="<?= app_get_option('option12', 'site', 'option12', TRUE) ?>"/></label>
        <textarea class="form-control" id="input312" placeholder="Option 12" name="option_update[site][option12][value]"><?= app_get_option('option12', 'site', '') ?></textarea>
        <span class="help-block sub-little-text">Code option <i>app_get_option('option12', 'site', '')</i></span>
    </div>

        <button type="submit" class="btn btn-primary ng-scope"><?=$this->lang->line('button_save')?></button>
    </div>
     <!--//tab custom-->

      <!--tab other-->
    <div id="tabs-4">
    <div class="form-group">
		<label for="input41">Ссылка на логотип</label>
        <input type="text" class="form-control" id="input41" placeholder="Относительная ссылка до файла 320x120px" name="option_update[site][logo_site]" value="<?= app_get_option('logo_site', 'site', '') ?>">
        <span class="help-block sub-little-text">Code option <i>app_get_option('logo_site', 'site', '')</i>. Ссылка на изображение логотипа. Этот будет использоваться по-умолчанию. Рекомендуемый размер 320px*120px.</span>
    </div>
		<div class="form-group">
			<label for="input42">Свой CSS для страниц авторизации/регистрации</label>
			<textarea class="form-control" id="input42" placeholder="Код CSS" name="option_update[site][login_custom_css]"><?= app_get_option('login_custom_css', 'site', '') ?></textarea>
	        <span class="help-block sub-little-text">Code option <i>app_get_option('login_custom_css', 'site', '')</i>. CSS будет добавлен в тег head.</span>
	    </div>

        <button type="submit" class="btn btn-primary ng-scope"><?=$this->lang->line('button_save')?></button>
    </div>
     <!--//tab other-->

    </form>


</div>
