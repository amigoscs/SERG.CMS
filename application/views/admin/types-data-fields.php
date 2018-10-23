<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
	*
	* version 2.0
	* UPD 2017-11-23
	*
	* version 2.1
	* UPD 2018-09-20
	* Правки в коде - неверные ключи для создания нового поля
	*
	* version 2.2
	* UPD 2018-10-23
	* Статус поля
	*
*/

$fieldsTypes = info('typesFields');
$lastOrder = 1;
?>
<script type="text/javascript">
function confirm_delete()
{
	if (confirm('Вы действительно хотите удалить?')) {
		return true;
	} else {
		return false;
	}
}
</script>

<h1><?= app_lang('h1_types_data_fields_setting') ?> - <?=$type_info['data_types_name']?></h1>
<p><a href="/admin/types_data/">Вернуться к типам данных</a>
<?=$message?>


<? //pr($fields_array) ?>
<form action="<?=info('base_url')?>admin/types_data/<?=$type_fields?>" method="post" class="mar30-tb">

<ul class="list-draggable" data-sort-table="data_types_fields" data-sort-field="types_fields_order">
<? foreach($fields_array as $key => $val): ?>
	<li data-id="<?= $key ?>" class="status-<?= $val['types_fields_status'] ?>">
	<? if(isset($val['data_types'][$type_fields])): ?>
     <div class="col-inner-2 show-hide-switch hide green" data-sh-id="tdf_<?= $key ?>">
    <? else: ?>
    <div class="col-inner-2 show-hide-switch hide" data-sh-id="tdf_<?= $key ?>">
    <? endif; ?>

    <h2 class="s-handle"><?=$val['types_fields_name']?> [ID = <?=$key?>]</h2>
    <!--новое поле-->

    <div class="form-group">
    <label for="input1<?=$key?>">Название поля</label>
    <input type="text" class="form-control" id="input1<?=$key?>" placeholder="Название для поля" name="edit_fields[<?=$key?>][fields_name]" value="<?=$val['types_fields_name']?>" required>
    </div>

    <div class="form-group">
    <label for="select1<?=$key?>">Тип поля</label>
    <?
    /*$options = array(
		'text'  => 'Однострочный текст',
		'textarea'  => 'Многострочный текст',
		'select'  => 'Выпадающий список',
		'image'  => 'Изображение',
		'file'  => 'Файл',
		'checkbox' => 'Чекбокс'
    );*/
    $args = array(
    'class' => 'form-control',
    'id' => 'select1' . $key,
    'name' => 'edit_fields[' . $key . '][fields_type]',
    );

    echo form_dropdown($args, $fieldsTypes, $val['types_fields_type']);
    ?>
    </div>

    <div class="form-group">
    <label for="input2<?=$key?>">Значения поля (для select и multiselect)</label>
    <input type="text" class="form-control" id="input2<?=$key?>" placeholder="Пример: КЛЮЧ || ЗНАЧ # КЛЮЧ2 || ЗНАЧ2" name="edit_fields[<?=$key?>][fields_values]" value="<?=$val['types_fields_values']?>">
    </div>

    <div class="form-group">
    <label for="input3<?=$key?>">Значение по умолчанию</label>
    <input type="text" class="form-control" id="input3<?=$key?>" placeholder="Значение по умолчанию" name="edit_fields[<?=$key?>][fields_default]" value="<?=$val['types_fields_default']?>">
    </div>

    <div class="form-group">
    <label for="select2">Видимость</label>
    <?
    $options = array(
		'publish'  => 'Виден',
		'hidden'  => 'Скрыт',
    );
    $args = array(
    'class' => 'form-control',
    'id' => 'select2' . $key,
    'name' => 'edit_fields[' . $key . '][fields_status]',
    );

    echo form_dropdown($args, $options, $val['types_fields_status']);
    ?>
    </div>

    <!--<div class="form-group">
    <label for="input4<?=$key?>">Порядок</label>
    <input type="text" class="form-control" id="input4<?=$key?>" placeholder="Порядок следования в списке" name="edit_fields[<?=$key?>][fields_order]" value="<?= $val['types_fields_order'] ?>">
	</div>-->

    <? $lastOrder = $val['types_fields_order'] ?>

    <div class="form-group">
    <label for="input5<?=$key?>">Обозначение</label>
    <input type="text" class="form-control" id="input5<?=$key?>" placeholder="Кг, грамм, дюйм и прочее" name="edit_fields[<?=$key?>][fields_unit]" value="<?=$val['types_fields_unit']?>">
    </div>

    <div class="form-group">
		<label for="select3">Флажок 1</label>
		<?
		$options = array('0'  => 'Нет', '1'  => 'Да');
		$args = array(
			'class' => 'form-control',
			'id' => 'select3' . $key,
			'name' => 'edit_fields[' . $key . '][flag1]',
		);
		echo form_dropdown($args, $options, $val['types_fields_flag1']);
		?>
    </div>

     <div class="form-group">
		<label for="select3">Флажок 2</label>
		<?
		$options = array('0'  => 'Нет', '1'  => 'Да');
		$args = array(
			'class' => 'form-control',
			'id' => 'select3' . $key,
			'name' => 'edit_fields[' . $key . '][flag2]',
		);
		echo form_dropdown($args, $options, $val['types_fields_flag2']);
		?>
    </div>

     <div class="form-group">
		<label for="select3">Флажок 3</label>
		<?
		$options = array('0'  => 'Нет', '1'  => 'Да');
		$args = array(
			'class' => 'form-control',
			'id' => 'select3' . $key,
			'name' => 'edit_fields[' . $key . '][flag3]',
		);
		echo form_dropdown($args, $options, $val['types_fields_flag3']);
		?>
    </div>

     <div class="form-group">
		<label for="select3">Флажок 4</label>
		<?
		$options = array('0'  => 'Нет', '1'  => 'Да');
		$args = array(
			'class' => 'form-control',
			'id' => 'select3' . $key,
			'name' => 'edit_fields[' . $key . '][flag4]',
		);
		echo form_dropdown($args, $options, $val['types_fields_flag4']);
		?>
    </div>

	 <div class="form-group">
        <label for="input_new_field5">&nbsp;</label> <br />
        <? if(isset($val['data_types'][$type_fields])): ?>
     		<button type="submit" class="btn btn-danger ng-scope" name="disconnect_to[<?=$type_fields?>]" value="<?=$key?>">Отключить от текущего</button>
    	<? else: ?>
   			<button type="submit" class="btn btn-success ng-scope" name="connect_to[<?=$type_fields?>]" value="<?=$key?>">Присоединить к текущему</button>
		<? endif; ?>

        <button type="submit" class="btn btn-success ng-scope"><?=$this->lang->line('button_save')?></button>
        <button type="button" class="btn btn-success ng-scope check-object" data-field-id="<?= $key ?>">Найти у объектов</button>
     </div>

     <div class="form-group info-line info-items" style="flex-basis: 100%">
		<? foreach($val['data_types'] as $tKey => $tVal): ?>
			<? if(isset($types_array[$tKey])): ?>
			<span>
				<?= $types_array[$tKey]['data_types_name'] ?>
			</span>
			<? else: ?>
			<span>
				ERROR ID[<?= $tKey ?>]
			</span>
			<? endif ?>
		<? endforeach; ?>
    </div>
</div>
</li>
<? endforeach; ?>
</ul>
</form>

<form action="<?=info('base_url')?>admin/types_data/<?=$type_fields?>" method="post" class="mar30-tb">
<div class="col-inner-2 show-hide-switch hide green">
<h2>Создать новое поле</h2>
 <!--новое поле-->

    <div class="form-group">
        <label for="input_new_field1">Название поля</label>
        <input type="text" class="form-control" id="input_new_field1" placeholder="Название для поля" name="new_field[fields_name]" value="" required>
    </div>

    <div class="form-group">
        <label for="select_new_field1">Тип поля</label>
        <?
        /*$options = array(
                'text'  => 'Однострочный текст',
                'textarea'  => 'Многострочный текст',
                'select'  => 'Выпадающий список',
                'image'  => 'Изображение',
            );*/
        $args = array(
                'class' => 'form-control',
                'id' => 'select_new_field1',
                'name' => 'new_field[fields_type]',
            );

        echo form_dropdown($args, $fieldsTypes, 'text');
        ?>
    </div>

   <div class="form-group">
      <label for="input_new_field2">Значения поля (для select)</label>
      <input type="text" class="form-control" id="input_new_field2" placeholder="Пример: КЛЮЧ || ЗНАЧ ## КЛЮЧ2 || ЗНАЧ2" name="new_field[fields_values]" value="">
  </div>

  <div class="form-group">
      <label for="input_new_field3">Значение по умолчанию</label>
      <input type="text" class="form-control" id="input_new_field3" placeholder="Значение, которое по умолчанию" name="new_field[fields_default]" value="">
  </div>

  <div class="form-group">
      <label for="select_new_field2">Видимость</label>
      <?
      $options = array(
              'publish'  => 'Виден',
              'hidden'  => 'Скрыт',
          );
      $args = array(
              'class' => 'form-control',
              'id' => 'select_new_field2',
              'name' => 'new_field[fields_status]',
          );

      echo form_dropdown($args, $options,  'publish');
      ?>
  </div>

      <!--<div class="form-group">
          <label for="input_new_field4">Порядок</label>
          <input type="text" class="form-control" id="input_new_field4" placeholder="Порядок следования в списке" name="new_field[fields_order]" value="<?= $lastOrder + 1 ?>">
      </div>-->

      <div class="form-group">
          <label for="input_new_field5">Обозначение</label>
          <input type="text" class="form-control" id="input_new_field5" placeholder="Кг, грамм, дюйм и прочее" name="new_field[fields_unit]" value="">
      </div>


     <div class="form-group">
		<label for="select41">Флажок 1</label>
		<?
		$options = array('0'  => 'Нет', '1'  => 'Да');
		$args = array(
			'class' => 'form-control',
			'id' => 'select41',
			'name' => 'new_field[flag1]',
		);
		echo form_dropdown($args, $options, 0);
		?>
    </div>

     <div class="form-group">
		<label for="select42">Флажок 2</label>
		<?
		$options = array('0'  => 'Нет', '1'  => 'Да');
		$args = array(
			'class' => 'form-control',
			'id' => 'select42',
			'name' => 'new_field[flag2]',
		);
		echo form_dropdown($args, $options, 0);
		?>
    </div>

     <div class="form-group">
		<label for="select43">Флажок 3</label>
		<?
		$options = array('0'  => 'Нет', '1'  => 'Да');
		$args = array(
			'class' => 'form-control',
			'id' => 'select43',
			'name' => 'new_field[flag3]',
		);
		echo form_dropdown($args, $options, 0);
		?>
    </div>

     <div class="form-group">
		<label for="select44">Флажок 4</label>
		<?
		$options = array('0'  => 'Нет', '1'  => 'Да');
		$args = array(
			'class' => 'form-control',
			'id' => 'select44',
			'name' => 'new_field[flag4]',
		);
		echo form_dropdown($args, $options, 0);
		?>
    </div>

       <input type="hidden" name="new_field[fields_data]" value="<?=$type_fields?>">
       <div class="form-group">
          <label for="input_new_field5">&nbsp;</label> <br />
          <button type="submit" class="btn btn-success ng-scope"><?=$this->lang->line('button_create_new_type_data_field')?></button>
      </div>


</div>  <!--// end col-inner-2 show-hide-switch-->

</form>
         <!--// новое поле-->


<? ############################################################# ?>
<? return ?>
