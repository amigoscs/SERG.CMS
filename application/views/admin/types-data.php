<?php defined('BASEPATH') OR exit('No direct script access allowed');
//pr($types_array);
//pr($_POST);

$all_data_types_array = array();
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


<h1><?=$this->lang->line('h1_types_data')?></h1>

<?=$message?>
<? //pr($types_array) ?>
<form action="<?=info('base_url')?>admin/types_data" method="post">
<ul class="list-draggable" data-sort-table="data_types" data-sort-field="data_types_order">
<? foreach($types_array as $key => $val): ?>
<li data-id="<?= $key ?>">
	<div class="show-hide-switch hide">
	<h2 class="s-handle"><?=$val['data_types_name']?> [ID = <?= $key ?> FIELDS = <?=count($val['fields'])?>]</h2>
	    <div class="col-inner-2">
	        <div class="form-group">
	        <label for="inputnew1">Название типа данных</label>
	        <input type="text" class="form-control" id="inputnew1" placeholder="Название для поля" name="edit_type[<?=$key?>][types_name]" value="<?=$val['data_types_name']?>">
	        </div>

	        <div class="form-group">
	        <label for="selectnew">Активность нового типа данных</label>
	        <?
	        $options = array(
	          'publish'  => 'Виден',
	          'hidden'  => 'Скрыт',
	        );
	        $args = array(
	          'class' => 'form-control',
	          'id' => 'selectnew',
	          'name' => 'edit_type[' . $key . '][types_status]',
	        );

	        echo form_dropdown($args, $options, $val['data_types_status'])
	        ?>
	        </div>
	    </div>

	    <div class="form-group">
	    <label for="inputnew2">Описание типа данных</label>
	    <textarea class="form-control" id="inputnew2" placeholder="Описание" name="edit_type[<?=$key?>][types_descr]"><?=$val['data_types_descr']?></textarea>
	    </div>

	    <div class="form-group info-line info-items">

		<? foreach($val['fields'] as $fKey => $fVal): ?>
			<span>
			<?= $fVal['types_fields_name'] ?>
			</span>
		<? endforeach; ?>

	    </div>

	    <button type="submit" class="btn btn-success ng-scope"><?=$this->lang->line('button_save')?></button>
	    <a href="<?=info('base_url')?>admin/types_data/<?=$key?>" class="btn btn-primary ng-scope">Настройка полей</a>

	    <button type="submit" class="btn btn-danger ng-scope" name="delete_type[<?=$key?>]" value="<?=$key?>" onClick="return confirm_delete()">Удалить тип данных</button>


	</div>
</li>
<? endforeach; ?>
</ul>
 </form>


<!--новый тип данных-->

<div class="show-hide-switch green hide">
<h2>Создать новый тип данных</h2>
<form action="<?=info('base_url')?>admin/types_data/" method="post">
<div class="col-inner-2">
    <div class="form-group">
        <label for="inputnew1">Название нового типа данных</label>
        <input type="text" class="form-control" id="inputnew1" placeholder="Название для поля" name="new_type[types_name]" value="">
    </div>

    <div class="form-group">
         <label for="selectnew">Активность нового типа данных</label>
        <?
        $options = array(
                'publish'  => 'Виден',
                'hidden'  => 'Скрыт',
            );
        $args = array(
                'class' => 'form-control',
                'id' => 'selectnew',
                'name' => 'new_type[types_status]',
            );

        echo form_dropdown($args, $options, 'publish')
        ?>
    </div>
</div>

	<div class="form-group">
      <label for="inputnew2">Описание для нового типа данных</label>
       <textarea class="form-control" id="inputnew2" placeholder="Описание" name="new_type[types_descr]"></textarea>
	</div>
       <button type="submit" class="btn btn-success ng-scope"><?=$this->lang->line('button_create_new_type_data')?></button>
</form>
</div>

<!--//--новый тип данных-->





<? ################################################################################ ?>
<? ################################################################################ ?>
<? ################################################################################ ?>
<? ################################################################################ ?>
<? return ?>



<div class="admin-tabs hide-for-new">
	<ul>
    	<? foreach($types_array as $key => $val): ?>
		<li><a href="#tabs-<?=$key?>"><?=$val['data_types_name']?></a></li>
		<!--<li><a href="#tabs-2">Группа 2</a></li>-->
		<? $all_data_types_array[$key] = $val['data_types_name'] ?>
		<? endforeach; ?>
         <? unset($key, $val); ?>
         <li><a href="#tabs-new-fields">Создать тип и поля</a></li>
	</ul>

    <form action="<?=info('base_url')?>admin/types_data/" method="post">


    <? foreach($types_array as $key => $val): ?>
		<div id="tabs-<?=$key?>">
        <div class="col-inner-2">
        	<div class="form-group">
                <label for="input0<?=$key?>">Название типа данных</label>
                <input type="text" class="form-control" id="input0<?=$key?>" placeholder="Название для поля" name="type[<?=$key?>][types_name]" value="<?=$val['data_types_name']?>">
            </div>

            <div class="form-group">
                 <label for="select0<?=$key?>">Активность типа данных</label>
                <?
                $options = array(
                        'publish'  => 'Виден',
                        'hidden'  => 'Скрыт',
                    );
                $args = array(
                        'class' => 'form-control',
                        'id' => 'select0' . $key,
                        'name' => 'type[' . $key . '][types_status]',
                    );

                echo form_dropdown($args, $options, $val['data_types_status'])
                ?>
            </div>
        </div>

        <div class="form-group">
                <label for="input01<?=$key?>">Описание</label>
                <textarea class="form-control" id="input01<?=$key?>" placeholder="Значение, которое по умолчанию" name="type[<?=$key?>][types_descr]"><?=$val['data_types_descr']?></textarea>
        </div>


		<? foreach($val['fields'] as $fkey => $fval): ?>
        <div class="col-inner-2 plugin-row">
            <div class="form-group">
                <label for="input<?=$fkey?>">Название поля</label>
                <input type="text" class="form-control" id="input<?=$fkey?>" placeholder="Название для поля" name="field[<?=$fkey?>][fields_name]" value="<?=$fval['types_fields_name']?>">
            </div>

            <div class="form-group">
                <label for="select<?=$fkey?>">Тип поля</label>
				<?
                $options = array(
                        'text'  => 'Однострочный текст',
                        'textarea'  => 'Многострочный текст',
						'select'  => 'Выпадающий список',
						'image'  => 'Изображение',
                    );
                $args = array(
                        'class' => 'form-control',
                        'id' => 'select' . $fkey,
                        'name' => 'field[' . $fkey . '][fields_type]',
                    );

                echo form_dropdown($args, $options, $fval['types_fields_type'])
                ?>
            </div>

             <div class="form-group">
                <label for="input1<?=$fkey?>">Значения поля (для select)</label>
                <input type="text" class="form-control" id="input1<?=$fkey?>" placeholder="Пример: КЛЮЧ || ЗНАЧ # КЛЮЧ2 || ЗНАЧ2" name="field[<?=$fkey?>][fields_values]" value="<?=$fval['types_fields_values']?>">
            </div>

            <div class="form-group">
                <label for="input2<?=$fkey?>">Значение по умолчанию</label>
                <input type="text" class="form-control" id="input2<?=$fkey?>" placeholder="Значение, которое по умолчанию" name="field[<?=$fkey?>][fields_default]" value="<?=$fval['types_fields_default']?>">
            </div>

            <div class="form-group">
                <label for="select3<?=$fkey?>">Видимость</label>
				<?
                $options = array(
                        'publish'  => 'Виден',
                        'hidden'  => 'Скрыт',
                    );
                $args = array(
                        'class' => 'form-control',
                        'id' => 'select3' . $fkey,
                        'name' => 'field[' . $fkey . '][fields_status]',
                    );

                echo form_dropdown($args, $options, $fval['types_fields_status'])
                ?>
            </div>

            <div class="form-group">
                <label for="input4<?=$fkey?>">Порядок</label>
                <input type="text" class="form-control" id="input4<?=$fkey?>" placeholder="Порядок следования в списке" name="field[<?=$fkey?>][fields_order]" value="<?=$fval['types_fields_order']?>">
            </div>

            <div class="form-group">
                <label for="input5<?=$fkey?>">Обозначение</label>
                <input type="text" class="form-control" id="input5<?=$fkey?>" placeholder="Кг, грамм, дюйм и прочее" name="field[<?=$fkey?>][fields_unit]" value="<?=$fval['types_fields_unit']?>">
            </div>

            <div class="form-group">
                <label class="checkbox-inline custom-checkbox nowrap">
                <input type="checkbox" id="inlineCheckbox02" name="field[<?=$fkey?>][delete_field]" value="delete"><span>Удалить поле</span>
                </label>
            </div>

        </div>
		<? endforeach; ?>
		<button type="submit" class="btn btn-primary ng-scope mar8-r" name="update" value="1"><?=$this->lang->line('button_save')?></button>
        <? if($key != '1'): ?>
        <button type="submit" class="btn btn-danger ng-scope" name="delete_type[<?=$key?>]" value="<?=$key?>" onClick="return confirm_delete()"><?=$this->lang->line('button_delete_type_data')?></button>
        <? endif; ?>
		</div>
     <? endforeach; ?>
	</form>

    <!--создание нового типа данных-->
    <div id="tabs-new-fields">
    	 <!--новый тип данных-->
        <form action="<?=info('base_url')?>admin/types_data/" method="post">
        <div class="col-inner-2">
                    <div class="form-group">
                        <label for="inputnew1">Название нового типа данных</label>
                        <input type="text" class="form-control" id="inputnew1" placeholder="Название для поля" name="new_type[types_name]" value="">
                    </div>

                    <div class="form-group">
                         <label for="selectnew">Активность нового типа данных</label>
                        <?
                        $options = array(
                                'publish'  => 'Виден',
                                'hidden'  => 'Скрыт',
                            );
                        $args = array(
                                'class' => 'form-control',
                                'id' => 'selectnew',
                                'name' => 'new_type[types_status]',
                            );

                        echo form_dropdown($args, $options, 'publish')
                        ?>
                    </div>
        </div>

        <div class="form-group">
              <label for="inputnew2">Описание для нового типа данных</label>
               <textarea class="form-control" id="inputnew2" placeholder="Описание" name="new_type[types_descr]"></textarea>
        </div>
               <button type="submit" class="btn btn-success ng-scope"><?=$this->lang->line('button_create_new_type_data')?></button>
        </form>
        <!--//--новый тип данных-->

         <!--новое поле-->
        <form action="<?=info('base_url')?>admin/types_data/" method="post" class="mar30-tb">
        <div class="col-inner-2 plugin-row">
            <div class="form-group">
                <label for="input_new_field1">Название поля</label>
                <input type="text" class="form-control" id="input_new_field1" placeholder="Название для поля" name="new_field[fields_name]" value="" required>
            </div>



             <div class="form-group">
                <label for="select_new_field0">Поле для типа</label>
				<?
                $args = array(
                        'class' => 'form-control',
                        'id' => 'select_new_field0',
                        'name' => 'new_field[fields_data]',
                    );

                echo form_dropdown($args,  $all_data_types_array);
                ?>
            </div>







            <div class="form-group">
                <label for="select_new_field1">Тип поля</label>
				<?
                $options = array(
                        'text'  => 'Однострочный текст',
                        'textarea'  => 'Многострочный текст',
						'select'  => 'Выпадающий список',
						'image'  => 'Изображение',
                    );
                $args = array(
                        'class' => 'form-control',
                        'id' => 'select_new_field1',
                        'name' => 'new_field[fields_type]',
                    );

                echo form_dropdown($args, $options, 'text');
                ?>
            </div>

             <div class="form-group">
                <label for="input_new_field2">Значения поля (для select)</label>
                <input type="text" class="form-control" id="input_new_field2" placeholder="Пример: КЛЮЧ || ЗНАЧ # КЛЮЧ2 || ЗНАЧ2" name="new_field[fields_values]" value="">
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

            <div class="form-group">
                <label for="input_new_field4">Порядок</label>
                <input type="text" class="form-control" id="input_new_field4" placeholder="Порядок следования в списке" name="new_field[fields_order]" value="0">
            </div>

            <div class="form-group">
                <label for="input_new_field5">Порядок</label>
                <input type="text" class="form-control" id="input_new_field5" placeholder="Кг, грамм, дюйм и прочее" name="new_field[fields_unit]" value="">
            </div>

        </div>
               <button type="submit" class="btn btn-success ng-scope"><?=$this->lang->line('button_create_new_type_data_field')?></button>
        </form>
         <!--//--новое поле-->
    </div>
     <!--//--создание нового типа данных-->


</div>
