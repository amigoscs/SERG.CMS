<?php defined('BASEPATH') OR exit('No direct script access allowed');
$selected = '';
?>
<h1><?=$h1?></h1>

<? require_once(__DIR__ . '/units/menu.php'); ?>

<form action="" method="post">
<div class="col-inner-2">
<div class="form-group">
    <label for="sgeninput1">E-mail сервера</label>
    <input type="text" class="form-control" id="sgeninput1" name="emarket_option[server_email]" value="<?= app_get_option('server_email', 'e-market', 'info@site.ru'); ?>">
    <span class="help-block sub-little-text">Укажите E-mail, с которого будут отправлены письма о заказах</span>
</div>

<div class="form-group">
    <label for="sgeninput2">E-mail администратора</label>
    <input type="text" class="form-control" id="sgeninput2" name="emarket_option[admin_mail]" value="<?= app_get_option('admin_mail', 'e-market', 'ALL || '); ?>">
    <span class="help-block sub-little-text">Укажите E-mail получателей писем. ALL || mail@gmail.com ## 2 || info@gmail.ru. Можно добавить несколько адресов через запятую</span>
</div>

<div class="form-group">
    <label for="sgeninput3">Имя сервера</label>
    <input type="text" class="form-control" id="sgeninput3" name="emarket_option[server_name]" value="<?= app_get_option('server_name', 'e-market', 'ServerName'); ?>">
    <span class="help-block sub-little-text">Имя отправителя писем</span>
</div>

<div class="form-group">
    <label for="sgeninput4">Формат номера заказа</label>
    <input type="text" class="form-control" id="sgeninput4" name="emarket_option[pseudo_cart_num]" value="<?= app_get_option('pseudo_cart_num', 'e-market', '%ID%'); ?>">
    <span class="help-block sub-little-text">Укажите формат номера заказа. ID заказа указывайте так: %ID%. Можно указать дату: %DATE(Y-m-d)%</span>
</div>

<div class="form-group">
    <label for="sgeninput5">ID поля E-mail заказчика</label>
    <input type="text" class="form-control" id="sgeninput5" name="emarket_option[user_email_field]" value="<?= app_get_option('user_email_field', 'e-market', 0); ?>">
    <span class="help-block sub-little-text">Укажите ID поля, в котором заказчик вводит свой E-mail</span>
</div>

<div class="form-group">
    <label for="sgeninput21">ID Data-поля с главным изображением товара</label>
    <input type="text" class="form-control" id="sgeninput21" name="emarket_option[product_image_field]" value="<?= app_get_option('product_image_field', 'e-market', 0); ?>">
    <span class="help-block sub-little-text"></span>
</div>

<div class="form-group">
    <label for="sgeninput6">ID data-поля, в котором хранится код (артикул, SKU) товара</label>
    <input type="text" class="form-control" id="sgeninput6" name="emarket_option[product_code_field]" value="<?= app_get_option('product_code_field', 'e-market', 0); ?>">
    <span class="help-block sub-little-text">Если нет такого, то оставьте 0</span>
</div>

<div class="form-group">
    <label for="sgeninput7">ID data-поля, в котором хранится цена товара</label>
    <input type="text" class="form-control" id="sgeninput7" name="emarket_option[product_price_field]" value="<?= app_get_option('product_price_field', 'e-market', 'ALL || 0') ?>">
    <span class="help-block sub-little-text">ГРУППА_ПОЛЬЗОВАТЕЛЕЙ || ID_ПОЛЯ ## ALL || ID_ПОЛЯ. ALL - значит для всех. Если поле одно, то укажите только ALL || ID_ПОЛЯ</span>
</div>

<div class="form-group">
    <label for="sgeninput7">ID data-полей, в которых хранятся старые цены товара</label>
    <input type="text" class="form-control" id="sgeninput7" name="emarket_option[product_old_price_field]" value="<?= app_get_option('product_old_price_field', 'e-market', 0); ?>">
    <span class="help-block sub-little-text">ГРУППА_ПОЛЬЗОВАТЕЛЕЙ || ID_ПОЛЯ ## ALL || ID_ПОЛЯ. ALL - значит для всех. Если поле одно, то укажите только ALL || ID_ПОЛЯ</span>
</div>

<div class="form-group">
    <label for="sgeninput8">ID data-полей, в которых хранится информация о наличии товара</label>
    <input type="text" class="form-control" id="sgeninput8" name="emarket_option[product_stock]" value="<?= app_get_option('product_stock', 'e-market', 0); ?>">
    <span class="help-block sub-little-text">ГРУППА_ПОЛЬЗОВАТЕЛЕЙ || ID_ПОЛЯ ## ALL || ID_ПОЛЯ. ALL - значит для всех. Если поле одно, то укажите только ALL || ID_ПОЛЯ.<br />
      Создайте функцию emarket_create_stock($stockValue, $userGroup), которая вернет 1 или 0 в завимисти от правила разрешения добавления в корзину.
    </span>
</div>

<div class="form-group">
	<label for="sgeninput10">Отправка писем</label>
  <?
		$values = array('no' => 'Никому', 'admin' => 'Только администраторам', 'all' => 'Администраторам и покупателю');
		$args = array(
			'class' => 'form-control',
			'id' => 'sgeninput10',
			'name' => 'emarket_option[send_email]',
			);

	   echo form_dropdown($args, $values, app_get_option('send_email', 'e-market', 'no'));
	?>
    <span class="help-block sub-little-text">Укажите, кто будет получать письма</span>
</div>

<div class="form-group">
    <label for="sgeninput8">Тема письма</label>
    <input type="text" class="form-control" id="sgeninput8" name="emarket_option[mail_subject]" value="<?= app_get_option('mail_subject', 'e-market', 'New order %ID%'); ?>">
    <span class="help-block sub-little-text">Текст темы письма. Для вставки номера заказа укажите - %ID%.  Указать сумму заказа - %SUMM%.
    </span>
</div>


<div class="form-group">
	<button type="submit" class="btn btn-primary ng-scope">Сохранить настройки</button>
</div>
	</div>
</form>
