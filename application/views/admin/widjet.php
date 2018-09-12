<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="widjet-title">
	<span class="wt-num"><?= $widjetInfoNumber ?></span>
	<span class="wt-info"><?= $widjetInfoTitle ?></span>
	<img src="<?= $widjetIcon ?>" alt=""/>
</div>
<div class="widjet-content">
	<?= $widjetContent ?>
</div>
<div class="widjet-footer">
	<a href="<?= $widjetLinkToPage ?>">Подробнее »</a>
</div>
