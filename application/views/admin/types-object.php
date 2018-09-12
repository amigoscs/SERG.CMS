<?php defined('BASEPATH') OR exit('No direct script access allowed');
//pr($_POST);
//pr($all_types);


?>


<h1><?=$this->lang->line('h1_types_object')?></h1>
<p class="info mar15-b">При создании типов объектов будьте внимательны! Созданные типы запрещено удалять.</p>

<?=$message?>

<form action="<?=info('base_url')?>admin/types_objects/" method="post">
<? foreach($all_types as $key => $value): ?>


<div class="show-hide-switch hide">
<h2><?=$value['obj_types_name']?> [ID = <?=$key?>]</h2>
<div class="col-inner-2">

    <div class="form-group">
                <label for="input1<?=$key?>"><?=$this->lang->line('input_label_objtypename')?></label>
                <input type="text" class="form-control" id="input<?=$key?>" placeholder="<?=$this->lang->line('input_label_objtypename')?>" name="type_objects[<?=$key?>][name]" value="<?=$value['obj_types_name']?>">
    </div>
    
     <div class="form-group">
                <label for="input2<?=$key?>"><?=$this->lang->line('input_label_objtypeicon')?></label>
                <input type="text" class="form-control" id="input2<?=$key?>" placeholder="<?=$this->lang->line('input_label_objtypeicon')?>" name="type_objects[<?=$key?>][icon]" value="<?=$value['obj_types_icon']?>">
    </div>
</div>	
    
     <div class="form-group">
    	<label for="textarea1<?=$key?>"><?=$this->lang->line('input_label_objtypedescr')?></label>
    	<textarea id="textarea1<?=$key?>" name="type_objects[<?=$key?>][descr]" class="form-control" placeholder="<?=$this->lang->line('input_label_objtypedescr')?>"><?=$value['obj_types_descr']?></textarea>
    </div>
</div>


<? endforeach; ?>
<button type="submit" class="btn btn-primary ng-scope mar35-b"><?=$this->lang->line('button_save')?></button>
</form>




<div class="show-hide-switch green hide">
<h2><?=$this->lang->line('h2_create_new_type_object')?></h2>
<form action="<?=info('base_url')?>admin/types_objects/" method="post">
<div class="col-inner-2">
    <div class="form-group">
        <label for="input4"><?=$this->lang->line('input_label_objtypename')?></label>
        <input type="text" class="form-control" id="input4" placeholder="<?=$this->lang->line('input_label_objtypename')?>" name="type_objects_new[0][name]" value="">
    </div>
    
     <div class="form-group">
        <label for="input5"><?=$this->lang->line('input_label_objtypeicon')?></label>
        <input type="text" class="form-control" id="input5" placeholder="<?=$this->lang->line('input_label_objtypeicon')?>" name="type_objects_new[0][icon]" value="">
    </div>
</div>
<div class="form-group">
    	<label for="textarea6"><?=$this->lang->line('input_label_objtypedescr')?></label>
    	<textarea id="textarea6" name="type_objects_new[0][descr]" class="form-control" placeholder="<?=$this->lang->line('input_label_objtypedescr')?>"></textarea>
</div>
<button type="submit" class="btn btn-success ng-scope"><?=$this->lang->line('button_create_new')?></button>
</form>
</div>

