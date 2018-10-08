<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<h1><?=$obj_name?>. ID[<?=$obj_id?>] NodeID[<?=$tree_id?>]</h1>
<?=$message?>

<form action="<?=$form_action?>" method="post" enctype="multipart/form-data" class="page-editor">

<div class="col-inner-2 show-hide-switch" data-sh-id="ap_1">
<h2><?= app_lang('ADMP_FORM_TITLE_MAIN') ?></h2>

	 <div class="form-group">
        <label for="input01"><?= app_lang('ADMP_FORM_LABEL_URL') ?></label>
        <input type="text" class="form-control" id="input01" placeholder="For home page - index" name="tree[<?=$tree_id?>][url]" value="<?= htmlspecialchars($tree_url) ?>">
    </div>

    <div class="form-group">
            <label for="select01"><?= app_lang('ADMP_FORM_LABEL_TYPE_OBJECT') ?></label>
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
            <label for="select01"><?= app_lang('ADMP_FORM_LABEL_DATA_TYPE_OBJECT') ?></label>
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
        <label for="input02"><?= app_lang('ADMP_FORM_LABEL_NAME_PAGE') ?></label>
        <input type="text" class="form-control" id="input02" name="obj[<?=$obj_id?>][name]" value="<?= htmlspecialchars($obj_name) ?>">
    </div>

    <div class="form-group">
        <label for="input03"><?= app_lang('ADMP_FORM_LABEL_H1_PAGE') ?></label>
        <input type="text" class="form-control" id="input03" name="obj[<?=$obj_id?>][h1]" value="<?= htmlspecialchars($obj_h1) ?>">
    </div>

    <div class="form-group">
        <label for="input04"><?= app_lang('ADMP_FORM_LABEL_TITLE_PAGE') ?></label>
        <input type="text" class="form-control" id="input04" name="obj[<?=$obj_id?>][title]" value="<?= htmlspecialchars($obj_title) ?>">
    </div>

    <div class="form-group">
        <label for="textarea01"><?= app_lang('ADMP_FORM_LABEL_DESCRIPTION_PAGE') ?></label>
        <textarea class="form-control" id="textarea01" name="obj[<?=$obj_id?>][description]"><?= $obj_description ?></textarea>
    </div>

    <div class="form-group">
        <label for="textarea02"><?= app_lang('ADMP_FORM_LABEL_KEYWORDS_PAGE') ?></label>
        <textarea class="form-control" id="textarea02" name="obj[<?=$obj_id?>][keywords]"><?= $obj_keywords ?></textarea>
    </div>

    <div class="form-group" style="flex-basis:100%">
        <button type="submit" class="btn btn-success ng-scope" style="width:100%; max-width:800px; display:block; margin:0 auto"><?= app_lang('button_save') ?></button>
    </div>

</div>

<div class="show-hide-switch col-inner-2" data-sh-id="ap_2">
<h2><?= app_lang('ADMP_FORM_TITLE_ADDITION') ?></h2>
    <div class="form-group">
        <label for="select11"><?= app_lang('ADMP_FORM_LABEL_AUTHOR') ?></label>
        <?
		$args = array('class' => 'form-control','id' => 'select11','name' => 'obj['.$obj_id.'][user_author]');
		echo form_dropdown($args, $all_users, $obj_user_author);
		?>
    </div>

    <div class="form-group">
        <label for="select12"><?= app_lang('ADMP_FORM_LABEL_ACCESS_PAGE') ?></label>

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
		$allValues = array('ALL' => app_lang('ADMP_FORM_OPTION_ACCESS_ALL'), 'LOGIN' => app_lang('ADMP_FORM_OPTION_ACCESS_ONLY_REG'));
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
    	<label for="input06"><?= app_lang('ADMP_FORM_LABEL_DATE_PUB_PAGE') ?></label>
    	 <input type="text" class="form-control field-date fd-time" id="input06" name="obj[<?=$obj_id?>][date_publish]" value="<?=$obj_date_publish?>" data-date-format="<?= app_get_option('date_format', 'general', 'YYYY-MM-DD'); ?> HH:mm:ss">
    </div>

    <div class="form-group">
    	<label for="input07"><?= app_lang('ADMP_FORM_LABEL_SHORT_LINK') ?></label>
    	<input type="text" class="form-control" id="input07" name="tree[<?=$tree_id?>][short]" value="<?= $tree_short ?>"/>
		<span class="help-block sub-little-text"><?= info('base_url') . $tree_short ?>
   <? if($visibleShortLink): ?>
   		<a href="<?= info('base_url') . $tree_short ?>" title="<?= app_lang('ADMP_FORM_LT_OPEN_NEW_WINDOW') ?>" target="_blank"><i style="font-style: normal" class="fa fa-external-link" aria-hidden="true"></i></a>
   <? endif; ?>
   </span>
    </div>

    <div class="form-group">
    	<input type="checkbox" class="checkbox" id="checkbox01" name="tree[<?=$tree_id?>][folow]" <? if($tree_folow) echo 'checked' ?>/>
    	<label for="checkbox01"><?= app_lang('ADMP_FORM_LABEL_INDEXES') ?></label>
    </div>

     <div class="form-group">
    	<input type="checkbox" class="checkbox" id="checkbox02" name="obj[<?=$obj_id?>][obj_status]" <? if($obj_status == 'publish') echo 'checked' ?>/>
    	<label for="checkbox02"><?= app_lang('ADMP_FORM_LABEL_VISIBLE') ?></label>
    </div>

	<div class="form-group">
        <label for="input09"><?= app_lang('ADMP_FORM_LABEL_RAT_UP') ?></label>
        <input type="text" class="form-control field-number" id="input09" placeholder="0" name="obj[<?=$obj_id?>][rating_up]" value="<?= $obj_rating_up ?>">
    </div>
	<div class="form-group">
        <label for="input010"><?= app_lang('ADMP_FORM_LABEL_RAT_DOWN') ?></label>
        <input type="text" class="form-control field-number" id="input010" placeholder="0" name="obj[<?=$obj_id?>][rating_down]" value="<?= $obj_rating_down ?>">
    </div>
	<div class="form-group">
	     <label for="input08"><?= app_lang('ADMP_FORM_LABEL_COUNT_VIEW') ?></label>
	    <input type="text" class="form-control field-number" id="input08" placeholder="0" name="obj[<?=$obj_id?>][cnt_views]" value="<?= $obj_cnt_views ?>">
	</div>
	<div class="form-group">
        <label for="input011"><?= app_lang('ADMP_FORM_LABEL_RAT_COUNT') ?></label>
        <input type="text" class="form-control field-number" id="input011" placeholder="0" name="obj[<?=$obj_id?>][rating_count]" value="<?= $obj_rating_count ?>">
    </div>
</div>

<div class="show-hide-switch" data-sh-id="ap_3">
<h2><?= app_lang('ADMP_FORM_TITLE_LINKS') ?></h2>
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
<h2><?= app_lang('ADMP_FORM_TITLE_CONTENT') ?></h2>
    <div class="form-group">
            <label for="textarea04"><?= app_lang('ADMP_FORM_LABEL_PREVIEW') ?></label>
            <textarea class="form-control editor" id="textarea04" name="obj[<?= $obj_id ?>][anons]"><?= $obj_anons ?></textarea>
        </div>

        <div class="form-group">
            <label for="textarea05"><?= app_lang('ADMP_FORM_LABEL_CONTENT') ?></label>
            <textarea class="form-control editor" id="textarea05" name="obj[<?= $obj_id ?>][content]"><?= $obj_content ?></textarea>
    </div>
</div>

<div class="col-inner-2 show-hide-switch" data-sh-id="ap_5">
<h2><?= app_lang('ADMP_FORM_TITLE_TPL') ?></h2>
    <div class="form-group">
        <label for="select101"><?= app_lang('ADMP_FORM_LABEL_TPL_PAGE') ?></label>
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
        <label for="select201"><?= app_lang('ADMP_FORM_LABEL_TPL_CONTENT') ?></label>
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
