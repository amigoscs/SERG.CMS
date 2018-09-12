<?php defined('BASEPATH') OR exit('No direct script access allowed');

?>
<h1>Активные комментарии</h1>


<div class="block-widjet-elements">
<div class="bwe-menu-top">
        <ul>
			<li class="selected"><a href="/admin/to-cart/orders_list?type=1&amp;status=1"><span>Активные</span></a></li>
			<li><a href="/admin/to-cart/orders_list?type=1&amp;status=2"><span>Неактивные</span></a></li>
		</ul>
    </div>
    <div class="bwe-content">
<? foreach($comments as $value): ?>
	<? $COMMENT = PAGEINIT($value);
    
    
    	КОНЕТНТ
	


<? endforeach; ?>
	</div>
</div>

<? pr($comments) ?>

