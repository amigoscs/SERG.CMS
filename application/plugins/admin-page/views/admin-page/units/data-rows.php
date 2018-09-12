<?php defined('BASEPATH') OR exit('No direct script access allowed');
//pr($all_data_types);
?>
<div class="col-inner-2 show-hide-switch">
<h2>Типовые поля</h2>
<?
//pr($all_data_types);
if(isset($all_data_types[1])):
	$i = 1;
	foreach($all_data_types[1]['fields'] as $fval):
		$value = '';
		if($data_fields and isset($data_fields[$fval['types_fields_id']]))
		{
			$value = $data_fields[$fval['types_fields_id']]['objects_data_value'];
		}
	
	?>
	<?
		$styleFGroup = '';
		if($fval['types_fields_type'] == 'editor')
			$styleFGroup = ' style="flex-basis:100%"';
	
	
	?>
		 <div class="form-group"<?= $styleFGroup ?>>
			<label for="data_field_i_<?=$i?>"><?=$fval['types_fields_name']?></label>
			<? if($fval['types_fields_type'] == 'text'): ?>
			<input type="text" class="form-control" id="data_field_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>"/>
			
			<? elseif($fval['types_fields_type'] == 'textarea'): ?>
			<textarea class="form-control" id="data_field_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]"><?=$value?></textarea>
		   
			
			<? elseif($fval['types_fields_type'] == 'select'): ?>
				<?
				$options = app_parse_select_values($fval['types_fields_values']);
				//pr($options);
				$args = array(
						'class' => 'form-control',
						'id' => 'data_field_' . $i,
						'name' => 'data_field[' . $obj_id . '][' . $fval['types_fields_id'] . ']',
					);
		   
				echo form_dropdown($args, $options, $value)
			 ?>
			
			<? // изображение ?>
         <? elseif($fval['types_fields_type'] == 'image'): ?>
         
         	<input type="text" class="form-control field-image" id="data_field_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>"/>
       
			
			<? elseif($fval['types_fields_type'] == 'file'): ?>
			<input type="text" class="form-control field-image elf-file" id="data_field_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>"/>
			
			<? elseif($fval['types_fields_type'] == 'date'): ?>
			<input type="text" class="form-control field-date" id="data_field_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>" data-date-format="<?= app_get_option('date_format', 'general', 'YYYY-MM-DD'); ?>"/>
			
			
			<? elseif($fval['types_fields_type'] == 'datetime'): ?>
			<input type="text" class="form-control field-date fd-time" id="data_field_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>" data-date-format="<?= app_get_option('date_format', 'general', 'YYYY-MM-DD'); ?> HH:mm:ss"/>
			
			<? elseif($fval['types_fields_type'] == 'number'): ?>
			<input type="text" class="form-control field-number" id="data_field_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>"/>
			
			<? elseif($fval['types_fields_type'] == 'numberfloat'): ?>
			<input type="text" class="form-control field-numberfloat" id="data_field_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>"/>
			
			<? elseif($fval['types_fields_type'] == 'multiselect'): ?>
			<?
			 	$value = explode(',', $value);
				$options = app_parse_select_values($fval['types_fields_values']);
				$args = array(
						'class' => 'form-control',
						'id' => 'data_field_' . $i,
						'name' => 'data_field[' . $obj_id . '][' . $fval['types_fields_id'] . '][]',
					);
		   
				echo form_multiselect($args, $options, $value);
			 ?>
			 
			 <? elseif($fval['types_fields_type'] == 'editor'): ?>
			<textarea class="form-control editor data-rows-editor" id="data_field_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]"><?=$value?></textarea>
			 
			 
			
			<? endif; ?>
		</div>
	<?
	$i++;
	endforeach;
endif;
?>
</div>

<? if(isset($all_data_types[$obj_data_type]) and $obj_data_type !== '1'): ?>
<div class="col-inner-2 show-hide-switch">
<h2>Поля типа данных</h2>
<?
	$i = 1;
	if($all_data_types[$obj_data_type]['fields'])
	{
	foreach($all_data_types[$obj_data_type]['fields'] as $fval):
		$value = '';
		if($data_fields and isset($data_fields[$fval['types_fields_id']]))
		{
			$value = $data_fields[$fval['types_fields_id']]['objects_data_value'];
		}
	?>
	 	<?
		$styleFGroup = '';
		if($fval['types_fields_type'] == 'editor')
			$styleFGroup = ' style="flex-basis:100%"';
		
	?>
		 <div class="form-group"<?= $styleFGroup ?>>
			<label for="data_field_i_<?=$i?>"><?= $fval['types_fields_name'] ?>
				<? if($fval['types_fields_unit']) : ?>
					(<?= $fval['types_fields_unit'] ?>)
				<? endif ?>
			</label>
			
			<? if($fval['types_fields_type'] == 'text'): ?>
			<input type="text" class="form-control" id="data_field_i_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>"/>
			
			<? elseif($fval['types_fields_type'] == 'textarea'): ?>
			<textarea class="form-control" id="data_field_i_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]"><?=$value?></textarea>
		   
			
			<? elseif($fval['types_fields_type'] == 'select'): ?>
				<?
				$options = app_parse_select_values($fval['types_fields_values']);
				//pr($options);
				$args = array(
						'class' => 'form-control',
						'id' => 'data_field_i_' . $i,
						'name' => 'data_field[' . $obj_id . '][' . $fval['types_fields_id'] . ']',
					);
		   
				echo form_dropdown($args, $options, $value)
			 ?>
			 
			<? elseif($fval['types_fields_type'] == 'image'): ?>
         
         <input type="text" class="form-control field-image" id="data_field_i_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>"/>
        <!-- <button class="btn btn-danger image-dialog" type="button" data-input-path="data_field_i_<?=$i?>">Загрузить</button>  -->
			
			<? elseif($fval['types_fields_type'] == 'file'): ?>
			<input type="text" class="form-control field-image elf-file" id="data_field_i_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>"/>
			
			<? elseif($fval['types_fields_type'] == 'date'): ?>
			<input type="text" class="form-control field-date" id="data_field_i_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>" data-date-format="<?= app_get_option('date_format', 'general', 'YYYY-MM-DD'); ?>"/>
			
			
			<? elseif($fval['types_fields_type'] == 'datetime'): ?>
			<input type="text" class="form-control field-date fd-time" id="data_field_i_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>" data-date-format="<?= app_get_option('date_format', 'general', 'YYYY-MM-DD'); ?> HH:mm:ss"/>
			
			<? elseif($fval['types_fields_type'] == 'number'): ?>
			<input type="text" class="form-control field-number" id="data_field_i_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>"/>
			
			<? elseif($fval['types_fields_type'] == 'numberfloat'): ?>
			<input type="text" class="form-control field-numberfloat" id="data_field_i_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]" value="<?=$value?>"/>
			
			<? elseif($fval['types_fields_type'] == 'multiselect'): ?>
			<?
				$value = explode(',', $value);
				$options = app_parse_select_values($fval['types_fields_values']);
				$args = array(
						'class' => 'form-control',
						'id' => 'data_field_' . $i,
						'name' => 'data_field[' . $obj_id . '][' . $fval['types_fields_id'] . '][]',
					);
		   
				echo form_multiselect($args, $options, $value)
			 ?>
			<? elseif($fval['types_fields_type'] == 'editor'): ?>
			<textarea class="form-control editor data-rows-editor" id="data_field_i_<?=$i?>" name="data_field[<?=$obj_id?>][<?=$fval['types_fields_id']?>]"><?=$value?></textarea>
			
			<? endif; ?>
			 </div> 
	<?
	$i++;
	endforeach;
	}
	else
	{
		echo '<p>Поля типа данных не заданы</p>';
	}
	?>
</div>
<?
endif;
?>