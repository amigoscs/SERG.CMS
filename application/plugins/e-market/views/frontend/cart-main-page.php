<?php defined('BASEPATH') OR exit('No direct script access allowed');
# файл вывода корзины
?>

<? if(!isset($userCart['products']) || !$userCart['products']): ?>
<p>Ваша корзина пуста</p>
<? return; ?>
<? endif; ?>


<?
# номер присваивается только для оформленного заказа. По нему ориентируемся
if($userCart['ecart_num']):
  ?>
<p>Ваш заказ успешно отправлен!</p>
<? return; ?>
<? endif; ?>


<table class="cart-products-table">
  <thead>
  <tr>
    <th>Фото</th>
    <th>Наименование</th>
    <th>Количество</th>
    <th>Цена за ед.</th>
    <th>Итого</th>
    <th></th>
  </tr>
  </thead>
  <tbody>
  <?
    $allPrice = array();
    foreach($userCart['products'] as $key => $value):
    if(!$value['object']) {
      continue;
    }
    $PAGE = PAGEINIT($value['object']);
    $ElfinderModel->load($PAGE->data('1'));
    $itemCount = $value['ecartp_count'];
    $itemPrice = $value['ecartp_price'];
    $fullPrice = $itemPrice * $itemCount;
  ?>

  <tr>

    <td>
      <a href="<?= $ElfinderModel->getFile(1) ?>" title="<?= $PAGE->h1 ?>" class="product-gallery" data-lightbox="image-<?= $PAGE->id ?>">
        <img src="<?= $ElfinderModel->getImageThumb(1, 50, 50, 'resize', TRUE) ?>" alt="<?= $PAGE->h1 ?>"/>
      </a>
    </td>
    <td>
      <a href="<?= $PAGE->link ?>" title="<?= $PAGE->h1 ?>" target="_blank" class="product-title"><?= $PAGE->h1 ?></a> <br />
      <input type="hidden" name="price[<?= $PAGE->id ?>]" value="<?= $itemPrice ?>"/>
      </td>
    <td class="change-count">
      <div class="qty-cart-block">
        <button type="button" class="btn num-minus" data-id="<?= $PAGE->id ?>" data-type="1">-</button>
          <input type="text" value="<?= $itemCount ?>" data-price="<?= $itemPrice ?>" class="is-cart" readonly/>
        <button type="button" class="btn num-plus" data-id="<?= $PAGE->id ?>" data-type="1">+</button>
      </div>
    </td>
    <td id="item_price_<?= $PAGE->id ?>">
      <span id="item_single_price_<?= $PAGE->id ?>" class="regular-price"><?= $itemPrice ?></span>
    </td>
    <td>
      <span id="item_total_price_<?= $PAGE->id ?>" class="regular-price"><?= $fullPrice ?></span>
    </td>
    <td class="ddpos">
      <a href="#" class="delete-position" title="Удалить позицию" data-id="<?= $PAGE->id ?>" data-type="1"></a>
    </td>
  </tr>
  <? endforeach ?>
  <tr class="last-row">
    <td colspan="4">Итого</td>
    <td id="cart_all_items_price"><?= $userCart['ecart_summ'] ?></td>
    <td><?= $userCart['ecart_currency_info']['ecartcur_code'] ?></td>
  </tr>
  </tbody>
</table>
