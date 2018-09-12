<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div id="commet_form_container">
<form action="<?= $submitUrl ?>" method="post" method="post" class="comments-form<?= $ajaxClass ?>">
	<input type="hidden" name="comment[parent]" value="0" class="fpp"/>
	<input type="hidden" name="comment[object]" value="<?= $objID ?>"/>
	<fieldset>

	<? if($anonim): ?>
	<div class="row">
		 <label class="cd-label" for="cinput1"><?= app_lang('COMMENT_TEXT_USER_NAME') ?></label>
		 <input type="text" name="comment[author]" id="cinput1" value="" class="user" required/>
	</div>
	<? else: ?>
	<input type="hidden" name="comment[author]" value="<?= $userName ?>"/>
	<? endif ?>
		<div class="row">
			 <label class="cd-label" for="comment_textarea_1"><?= app_lang('COMMENT_TEXT_TITLE') ?></label>
			 <textarea class="message" id="comment_textarea_1" name="comment[text]"></textarea>
		</div>
		<div class="row submit-row">
			<button type="submit" class="text-center btn btn-success" style="color: #fff">Оставить комментарий</button>
			<button type="buttn" class="text-center btn btn-default stop-answer">Отмена</button>
		</div>
	</fieldset>
</form>
</div>
<div id="comment_response"></div>
