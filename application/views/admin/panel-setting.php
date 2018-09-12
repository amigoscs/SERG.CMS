<?php defined('BASEPATH') OR exit('No direct script access allowed');
//pr($_POST);

$dateFormats = array(
	'DD.MM.YYYY' => 'ДД.ММ.ГГГГ',
	'MM/DD/YYYY' => 'ММ/ДД/ГГГГ',
	'YYYY-MM-DD' => 'ГГГГ-ММ-ДД',
);
?>
<h1><?= $h1 ?></h1>

<div class="admin-tabs">
	<ul>
		<li><a href="#tabs-1"><?= app_lang('tab_main') ?></a></li>
		<li><a href="#tabs-2"><?= app_lang('tab_mail') ?></a></li>
		<li><a href="#tabs-3"><?= app_lang('tab_load') ?></a></li>
        <li><a href="#tabs-4"><?= app_lang('tab_server') ?></a></li>
        <li><a href="#tabs-5">System Files</a></li>
	</ul>
	
    <form action="<?=info('base_url')?>admin/panel_setting" method="post">
    <!--основное-->
    <div id="tabs-1">
    
     <div class="form-group">
     	<label for="select11"><?= app_lang('input_label_langpanel')?></label>
		<?
		$options = array(
			'english'  => 'English',
			'russian'    => 'Русский',
		);
		
		$args = array(
			'class' => 'form-control',
			'id' => 'input11',
			'name' => 'option_update[general][admin_lang]',
			);
	   
	   echo form_dropdown($args, $options, app_get_option('admin_lang', 'general', 'english'));
	   ?>
      </div>
      
      <div class="form-group">
     	<label for="select12"><?=app_lang('input_label_tplpanel')?></label>
        <?
		$options = array(
			'default'  => 'Default',
		);
		
		$args = array(
			'class' => 'form-control',
			'id' => 'select12',
			'name' => 'option_update[general][admin_template]',
			);
	   
	   echo form_dropdown($args, $options, app_get_option('admin_template', 'general', 'default'))
	   ?>
      </div>
      
      <div class="form-group">
			<label for="input11">Попыток входа в систему</label>
			<input type="text" class="form-control" id="input11" placeholder="Укажите здесь число попыток неправильного входа в систему" name="option_update[general][number_attempts_login]" value="<?= app_get_option('number_attempts_login', 'general', '3') ?>">
      </div>
      
      <div class="form-group">
			<label for="select13">Формат даты</label>
			<?
				$args = array(
					'class' => 'form-control',
					'id' => 'select13',
					'name' => 'option_update[general][date_format]',
					);
	   
	  		echo form_dropdown($args, $dateFormats, app_get_option('date_format', 'general', 'YYYY-MM-DD'));
	   		?>
      </div>
      
      <div class="form-group">
			<label for="input12">Иконка панели</label>
			<input type="text" class="form-control" id="input12" placeholder="Относительная ссылка до файла (124x23px)" name="option_update[general][icon_panel]" value="<?= app_get_option('icon_panel', 'general', '') ?>">
      </div>
      
      <div class="form-group">
			<label for="input13">favicon для панели</label>
			<input type="text" class="form-control" id="input13" placeholder="Относительная ссылка до файла" name="option_update[general][favicon_panel]" value="<?= app_get_option('favicon_panel', 'general', '') ?>">
      </div>
      
      <div class="form-group">
			<label for="select14">Регистрация на сайте</label>
			<?
			$options = array(
					0 => 'Запрещена регистрация на сайте',
					1 => 'Разрешена регистрация на сайте',
				);
				$args = array(
					'class' => 'form-control',
					'id' => 'select14',
					'name' => 'option_update[general][register_allowed]',
					);
	   
	  		echo form_dropdown($args, $options, app_get_option('register_allowed', 'general', 0));
	   		?>
      </div>
      
      
	<button type="submit" class="btn btn-primary ng-scope"><?=app_lang('button_save')?></button>
   </div>
   <!--//основное-->
   
   <!--email-->
	<div id="tabs-2">
		<div class="form-group">
			<label for="input21"><?=app_lang('input_label_servermail')?></label>
			<input type="text" class="form-control" id="input21" placeholder="<?=app_lang('input_label_servermail')?>" name="option_update[general][server_mail]" value="<?= app_get_option('server_mail', 'general', '') ?>">
			<span class="help-block sub-little-text">Code option <i>app_get_option('server_mail', 'general', '')</i></span>
		</div>
        <div class="form-group">
			<label for="input22"><?=app_lang('input_label_adminmail')?></label>
			<input type="text" class="form-control" id="input22" placeholder="<?=app_lang('input_label_adminmail')?>" name="option_update[general][admin_mail]" value="<?= app_get_option('admin_mail', 'general', '') ?>">
			<span class="help-block sub-little-text">Code option <i>app_get_option('admin_mail', 'general', '')</i></span>
		</div>
       
        
         <div class="form-group">
			<label for="input26"><?=app_lang('input_label_tpl_new_register_admin')?></label>
			<input type="text" class="form-control" id="input26" placeholder="<?=app_lang('input_label_tpl_new_register_admin')?>" name="option_update[general][tpl_new_register_admin]" value="<?= app_get_option('tpl_new_register_admin', 'general', '') ?>">
            <span class="help-block sub-little-text"><?=app_lang('info_input_file_tpl')?></span>
		</div>
         <div class="form-group">
			<label for="input27"><?=app_lang('input_label_tpl_new_register_user')?></label>
			<input type="text" class="form-control" id="input27" placeholder="<?=app_lang('input_label_tpl_new_register_user')?>" name="option_update[general][tpl_new_register_user]" value="<?= app_get_option('tpl_new_register_user', 'general', '') ?>">
            <span class="help-block sub-little-text"><?=app_lang('info_input_file_tpl')?></span>
		</div>
       
		<div class="form-group">
     	<label for="select21"><?=app_lang('input_label_emailprotokol')?></label>
        <?
		$options = array(
			'mail'  => 'mail',
			'sendmail'  => 'sendmail',
			'smtp'  => 'smtp',
		);
		
		$args = array(
			'class' => 'form-control',
			'id' => 'select21',
			'name' => 'option_update[general][email_protocol]',
			);
	   
	   	echo form_dropdown($args, $options, app_get_option('email_protocol', 'general', 'mail'))
	   	?>
		</div>
       
        <button type="submit" class="btn btn-primary ng-scope"><?=app_lang('button_save')?></button>
	</div>
	<!--//email-->
	<!--loaders-->
	<div id="tabs-3">
		<div class="form-group">
			<label for="input31"><?=app_lang('input_label_allowfiletypes')?></label>
            <?
			$allowed_files_types = 'mp3, gif, jpg, jpeg, png, svg, zip, txt, rar, doc, docx, rtf, pdf, html, htm, css, xml, odt, avi, wmv, flv, swf, wav, xls, 7z, gz, bz2, tgz, csv';
			?>
			<input type="text" class="form-control" id="input31" placeholder="<?=app_lang('input_label_allowfiletypes')?>" name="option_update[general][allowed_files_types]" value="<?= app_get_option('allowed_files_types', 'general', $allowed_files_types) ?>">
            <span class="help-block sub-little-text">Code option <i>app_get_option('allowed_files_types', 'general', '')</i></span> 
		</div>
        <button type="submit" class="btn btn-primary ng-scope"><?=app_lang('button_save')?></button>
    </div>
	<!--loaders-->
    <!--server-->
    <div id="tabs-4">
		<div class="form-group">
			<label for="input41"><?=app_lang('input_label_timeoffset')?></label>
			<input type="text" class="form-control" id="input41" placeholder="<?=app_lang('input_label_timeoffset')?>" name="option_update[general][server_time_offset]" value="<?= app_get_option('server_time_offset', 'general', 0) ?>">
       		<span class="help-block sub-little-text">Code option <i>app_get_option('server_time_offset', 'general', 0)</i></span>
        </div>
        <button type="submit" class="btn btn-primary ng-scope"><?=app_lang('button_save')?></button>
	</div>
    <!--//server-->
    
    
    <!--sitemap и robots.txt-->
    <div id="tabs-5">
		<div class="form-group">
			<label for="input51">Название файла Sitemap</label>
			<input type="text" class="form-control" id="input51" placeholder="Например: sitemap.xml" name="option_update[general][sitemap_file]" value="<?= app_get_option('sitemap_file', 'general', 'sitemap.xml') ?>">   
        </div>
        
        
        <div class="form-group">
			<label for="textarea52">Файл robots.txt</label>
			<textarea placeholder="Содержимое файла robots.txt" class="form-control" id="textarea52"  name="robots_file_content" style="height:450px"><?=$robots_txt?></textarea>
            <? if(!file_exists(info('base_path') . 'robots.txt')): ?>
            	<span class="help-block sub-little-text" style="color:#b20">Файл robots.txt не найден!</span>
            <? endif; ?>
        </div>
        
        <div class="form-group">
			<label for="textarea52">Файл .htaccess</label>
			<textarea placeholder="Содержимое файла robots.txt" class="form-control" id="textarea52"  name="htaccess_file_content" style="height:450px"><?=$htaccess_txt?></textarea>
            <? if(!file_exists(info('base_path') . '.htaccess')): ?>
            	<span class="help-block sub-little-text" style="color:#b20">Файл htaccess не найден!</span>
            <? endif; ?>
        </div>
        
        
        
        
        
        
        
        <button type="submit" class="btn btn-primary ng-scope"><?=app_lang('button_save')?></button>
	</div>
    <!--//sitemap и robots.txt-->
	
    </form>


</div>