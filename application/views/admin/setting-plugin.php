<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
	*
	* UPD 2017-11-29
	* version 1.3
	*
	* UPD 2018-09-13
	* version 1.4
	* Правки HTML
	*
*/
?>

<h1><?=$this->lang->line('h1_setting_plugin')?> - <?= $plugin_name ?></h1>

<?=$message?>

<form action="<?=info('base_url')?>admin/setting_plugin/<?=$plugin_folder?>" method="post">

<?
	$i=1;
	foreach($plugin_options as $key => $value)
	{
		if($value['type'] == 'text')
		{
			echo '<div class="form-group">';
			echo '<label for="input' . $i . '">' . $value['name'] . '</label>';
			echo '<input type="text" class="form-control" id="input' . $i . '" ';
			echo 'name="option_update[' . $plugin_folder . '][' . $key . ']" ';
			echo 'value="' . $value['value'] . '"/>';
			echo '<span class="help-block sub-little-text"><strong>app_get_option("'.$key.'", "'.$plugin_folder.'", "' . $value['default'] . '")</strong> ';
			if($value['description']) {
				echo '<br />' . $value['description'];
			}
			echo '</span> ';
			echo '</div>';
		}
		elseif($value['type'] == 'textarea')
		{
			echo '<div class="form-group">';
			echo '<label for="textarea' . $i . '">' . $value['name'] . '</label>';
			echo '<textarea class="form-control" id="textarea' . $i . '" ';
			echo 'name="option_update[' . $plugin_folder . '][' . $key . ']">';
			echo $value['value'] . '</textarea>';
			echo '<span class="help-block sub-little-text"><strong>app_get_option("'.$key.'", "'.$plugin_folder.'", "' . $value['default'] . '")</strong> ';
			if($value['description']) {
				echo '<br />' . $value['description'];
			}
			echo '</span> ';
			echo '</div>';
		}
		elseif($value['type'] == 'select')
		{
			$options_select = $value['values'];
			$args = array(
				'class' => 'form-control',
				'id' => 'select' . $i,
				'name' => 'option_update[' . $plugin_folder . '][' . $key . ']',
			);

	  		echo '<div class="form-group">';
			echo '<label for="select' . $i . '">' . $value['name'] . '</label>';
			echo form_dropdown($args, $value['values'], $value['value']);
			echo '<span class="help-block sub-little-text"><strong>app_get_option("'.$key.'", "'.$plugin_folder.'", "' . $value['default'] . '")</strong> ';
			if($value['description']) {
				echo '<br />' . $value['description'];
			}
			echo '</span> ';
			echo '</div>';
		}
		elseif($value['type'] == 'multiselect')
		{
			$options_select = $value['values'];
			$args = array(
				'class' => 'form-control',
				'id' => 'select' . $i,
				'name' => 'option_update[' . $plugin_folder . '][' . $key . '][]',
			);

	  		echo '<div class="form-group">';
			echo '<label for="select' . $i . '">' . $value['name'] . '</label>';
			$value['value'] = explode(',', $value['value']);
			echo form_multiselect($args, $value['values'], $value['value']);
			echo '<span class="help-block sub-little-text"><strong>app_get_option("'.$key.'", "'.$plugin_folder.'", "' . $value['default'] . '")</strong> ';
			if($value['description']) {
				echo '<br />' . $value['description'];
			}
			echo '</span> ';
			echo '</div>';
		}

		++$i;
	}
?>

<button type="submit" class="btn btn-success ng-scope"><?=$this->lang->line('button_save')?></button>
</form>
