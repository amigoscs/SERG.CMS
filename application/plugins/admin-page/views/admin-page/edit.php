<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<h1><?=$obj_name?>. ID[<?=$obj_id?>] NodeID[<?=$tree_id?>]</h1>
<?=$message?>

<form action="<?=$form_action?>" method="post" enctype="multipart/form-data" class="page-editor">

<div class="col-inner-2 show-hide-switch" data-sh-id="ap_1">
<h2>Основное</h2>

	 <div class="form-group">
        <label for="input02">URl для страницы</label>
        <input type="text" class="form-control" id="input02" placeholder="Для главной - index" name="tree[<?=$tree_id?>][url]" value="<?= htmlspecialchars($tree_url) ?>">
    </div>

    <div class="form-group">
            <label for="select01">Тип объекта</label>
            <?

			$args = array(
					'class' => 'form-control',
					'id' => 'select01',
					'name' => 'tree['.$tree_id.'][type_object]',
				);

	   		echo form_dropdown($args, $all_types, $tree_type_object)
			?>
    </div>

     <div class="form-group">
            <label for="select01">Тип данных объекта</label>
            <?
			$options = array();
			foreach($all_data_types as $key => $val) {
				$options[$key] = $val['data_types_name'];
			}

			$args = array(
					'class' => 'form-control',
					'id' => 'select02',
					'name' => 'obj['.$obj_id.'][data_type][]',
				);
	   		echo form_multiselect($args, $options, $obj_data_type)
			?>
    </div>

    <div class="form-group">
        <label for="input03">Название страницы</label>
        <input type="text" class="form-control" id="input03" placeholder="Название страницы" name="obj[<?=$obj_id?>][name]" value="<?= htmlspecialchars($obj_name) ?>">
    </div>

    <div class="form-group">
        <label for="input03">Поле H1 страницы</label>
        <input type="text" class="form-control" id="input03" placeholder="H1 страницы" name="obj[<?=$obj_id?>][h1]" value="<?= htmlspecialchars($obj_h1) ?>">
    </div>

    <div class="form-group">
        <label for="input04">Поле TITLE страницы</label>
        <input type="text" class="form-control" id="input04" placeholder="Title страницы" name="obj[<?=$obj_id?>][title]" value="<?= htmlspecialchars($obj_title) ?>">
    </div>

    <div class="form-group">
        <label for="textarea01">Поле meta DESCRIPTION</label>
        <textarea class="form-control" id="textarea01" placeholder="Description страницы" name="obj[<?=$obj_id?>][description]"><?= $obj_description ?></textarea>

    </div>

    <div class="form-group">
        <label for="textarea02">Поле meta KEYWORDS</label>
        <textarea class="form-control" id="textarea02" placeholder="Keywords страницы" name="obj[<?=$obj_id?>][keywords]"><?= $obj_keywords ?></textarea>
    </div>

    <div class="form-group" style="flex-basis:100%">
      	<!--<label for="input05">Сохранить изменения</label><br />-->
        <button type="submit" class="btn btn-success ng-scope" style="width:100%; max-width:800px; display:block; margin:0 auto"><?= app_lang('button_save') ?></button>
    </div>

</div>

<div class="show-hide-switch col-inner-2" data-sh-id="ap_2">
<h2>Дополнительно</h2>
    <div class="form-group">
        <label for="select11">Владелец</label>
        <!--<input type="text" class="form-control" id="input05" placeholder="Пользователь" name="obj[<?=$obj_id?>][user_author]" value="<?=$obj_user_author?>">-->
        <?
		$args = array('class' => 'form-control','id' => 'select11','name' => 'obj['.$obj_id.'][user_author]');
		echo form_dropdown($args, $all_users, $obj_user_author);
		?>
    </div>

    <div class="form-group">
        <label for="select12">Доступ к странице</label>

        <?
		$selectedValue = array();
		$defValue = explode('|', $obj_ugroups_access);
		if(count($defValue) > 1){
			foreach($defValue as $vv){
				if(trim($vv))
					$selectedValue[] = $vv;
			}
		}else{
			$selectedValue = $defValue;
		}
		$allValues = array('ALL' => 'Для всех', 'LOGIN' => 'Только зарегистрированным');
		foreach($all_users_groups as $value){
			if($value['users_group_id'] == 2)
				continue;

			$allValues[$value['users_group_id']] = $value['users_group_name'];
		}

		$args = array('class' => 'form-control','id' => 'select12','name' => 'obj['.$obj_id.'][ugroups_access][]');
		echo form_multiselect($args, $allValues, $selectedValue);
		?>
    </div>

    <div class="form-group">
    	<label for="input06">Дата публикации</label>
    	 <input type="text" class="form-control field-date fd-time" id="input06" name="obj[<?=$obj_id?>][date_publish]" value="<?=$obj_date_publish?>" data-date-format="<?= app_get_option('date_format', 'general', 'YYYY-MM-DD'); ?> HH:mm:ss">
    </div>

    <div class="form-group">
    	<label for="input07">Короткая ссылка</label>
    	<input type="text" class="form-control" id="input07" name="tree[<?=$tree_id?>][short]" value="<?= $tree_short ?>"/>
		<span class="help-block sub-little-text"><?= info('base_url') . $tree_short ?>
   <? if($visibleShortLink): ?>
   		<a href="<?= info('base_url') . $tree_short ?>" title="Открыть в новой вкладке" target="_blank"><i style="font-style: normal" class="fa fa-external-link" aria-hidden="true"></i></a>
   <? endif; ?>
   </span>
    </div>

    <div class="form-group">
    	<input type="checkbox" class="checkbox" id="checkbox01" name="tree[<?=$tree_id?>][folow]" <? if($tree_folow) echo 'checked' ?>/>
    	<label for="checkbox01">Доступен для индексации</label>
    </div>

     <div class="form-group">
    	<input type="checkbox" class="checkbox" id="checkbox02" name="obj[<?=$obj_id?>][obj_status]" <? if($obj_status == 'publish') echo 'checked' ?>/>
    	<label for="checkbox02">Видимость</label>
    </div>
</div>

<div class="show-hide-switch" data-sh-id="ap_3">
<h2>Адреса страницы</h2>
    <div class="form-group">
	<?
	if($all_urls) {
		foreach($all_urls as $key => $value) {
			$tmp = '';
			$prevUrl = '';
			foreach($value['nodes'] as $nKey => $nVal) {
				if($nVal['url'] == 'index') {
					$prevUrl .= info('baseUrlTrim');
				} else {
					$prevUrl .= '/' . $nVal['url'];
				}
				$tmp .= '<li><a href="' . $prevUrl . '" target="_blank">' . $nVal['name'] . '</a></li>';
			}
			echo '<ul class="all-tree-lines '.$value['node_tree_type'] .'">';
				echo $tmp;
			echo '</ul>';
		}
	}
	?>
    </div>

</div>

<div class="show-hide-switch" data-sh-id="ap_4">
<h2>Контент</h2>
    <div class="form-group">
            <label for="textarea04">Анонс</label>
            <textarea class="form-control editor" id="textarea04" placeholder="Краткий анонс страницы" name="obj[<?= $obj_id ?>][anons]"><?= $obj_anons ?></textarea>
        </div>

        <div class="form-group">
            <label for="textarea05">Содержание</label>
            <textarea class="form-control editor" id="textarea05" placeholder="Контент страницы" name="obj[<?= $obj_id ?>][content]"><?= $obj_content ?></textarea>
    </div>
</div>

<div class="col-inner-2 show-hide-switch" data-sh-id="ap_5">
<h2>Шаблон</h2>
    <div class="form-group">
        <label for="select101">Шаблон страницы</label>
            <?

			$args = array(
					'class' => 'form-control',
					'id' => 'select101',
					'name' => 'obj['.$obj_id.'][tpl_page]',
				);

	   		echo form_dropdown($args, $options_tpl_page, $obj_tpl_page)
			?>
    </div>

    <div class="form-group">
        <label for="select201">Шаблон контента</label>
            <?

			$args = array(
					'class' => 'form-control',
					'id' => 'select201',
					'name' => 'obj['.$obj_id.'][tpl_content]',
				);

	   		echo form_dropdown($args, $options_tpl_content, $obj_tpl_content)
			?>
    </div>

</div>

 	<?
	# поля типов данных
	include_once(__DIR__ . '/units/data-rows.php');
	?>
   <div class="form-group" style="flex-basis:100%">
        <button type="submit" class="btn btn-success ng-scope" style="width:100%; max-width:800px; display:block; margin:0 auto"><?= app_lang('button_save') ?></button>
    </div>
</form>
