<?php defined('BASEPATH') OR exit('No direct script access allowed');

?>
<h1><?= app_lang('COMMENT_ADM_TITLE_INDEX') ?></h1>

<div class="block-widjet-elements">
<div class="bwe-menu-top">
        <ul>
			<li class="selected"><a href="/admin/comments"><span><?= app_lang('COMMENT_BUTTON_ALL_COMMENTS') ?></span></a></li>
<!--			<li><a href="/admin/to-cart/orders_list?type=1&amp;status=2"><span>Неактивные</span></a></li>-->
		</ul>
    </div>
    <div class="bwe-content">
		<table class="comment-info-table">
		<? foreach($comments as $value): 
			$authorNameRegister = 'anonim';
			$authorName = $value['comments_author'];
			$publish = 'chidden';
			
			$buttonPublish = '<button type="button" class="comment-status btn btn-success" data-status="publish"  data-comment="'.$value['comments_id'] . '">' . app_lang('COMMENT_BUTTON_PUBLISH') . '</button>';
			$buttonPublish .= '<button type="button" class="comment-status btn btn-info" data-status="hidden" data-comment="'. $value['comments_id'] . '">' . app_lang('COMMENT_BUTTON_DEPUBLISH') . '</button>';
			$buttonPublish .= '<button type="button" class="comment-delete btn btn-danger" data-comment="'. $value['comments_id'] . '">' . app_lang('COMMENT_BUTTON_DELETE') . '</button>';
			
			if($value['users_id']){
				$authorNameRegister = 'register';
				$authorName = $value['users_login'];
			}
			if($value['comments_status'] == 'publish'){
				$publish = 'cpublish';
				//$buttonPublish = '<button type="button" class="comment-status btn btn-info" data-status="hidden" data-comment="'. $value['comments_id'] . '">Снять с публикации</button>';
			}
			?>
			<tr class="<?= $publish ?>" id="com<?= $value['comments_id'] ?>">
				<td>
					<div class="crow-title">
						<i><?= $authorNameRegister ?></i> <?= date_convert($dateFormat, $value['comments_date']) ?> <br /> 
						<span><?= $authorName ?></span>, <a href="/<?= $value['obj_canonical'] ?>" target="_blank"><?= $value['obj_h1'] ?></a>
					</div>
					<div class="crow-descr"><a href="/admin/comments/edit_comment?id=<?= $value['comments_id'] ?>" title="Редактировать комментарий"><?= word_limiter($value['comments_content'], 10, '&#8230;') ?></a></div>
				</td>
				<td>
					<?= $buttonPublish ?>
				</td>
			</tr>
		<? endforeach; ?>
		</table>
	</div>
</div>

<script>
	jQuery(document).ready(function($){
		$('.comment-status').on('click.COMMENTS', function(){
			var $Btn = $(this);
			var NewStatus = $Btn.data('status');
			var commentID = $Btn.data('comment');
			
			add_loader();
			$.ajax({
				url: '/admin/comments/ajax?action=changestatus&status=' + NewStatus,
				type: 'POST',
				data: {comment_id: commentID},
				success: function(data){
					console.log(data);
					Res = JSON.parse(data);
					if(Res.status == 'OK'){
						$('.comment-info-table tr#com' + commentID).removeClass('chidden cpublish').addClass('c' + Res.new_status);
						noty_info('information', Res.info);
					}else{
						noty_info('warning', Res.info);
					}
					remove_loader();
				}
			});	
		});
		$('.comment-delete').on('click.COMMENTS', function(){
			var $Btn = $(this);
			var commentID = $Btn.data('comment');
			
			add_loader();
			$.ajax({
				url: '/admin/comments/ajax?action=delete',
				type: 'POST',
				data: {comment_id: commentID},
				success: function(data){
					console.log(data);
					Res = JSON.parse(data);
					if(Res.status == 'OK'){
						$('.comment-info-table tr#com' + commentID).animate({opacity: "0"}, 300, "linear", function(){
							$('.comment-info-table tr#com' + commentID).remove();
						});
						noty_info('information', Res.info);
					}else{
						noty_info('warning', Res.info);
					}
					remove_loader();
				}
			});	
		});
	});
</script>

<? //pr($comments) ?>


