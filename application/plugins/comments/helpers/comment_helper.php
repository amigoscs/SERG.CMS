<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
	* файл helper плагина комментариев
	* UPD 2017-11-28
	* version 0.2
	*
	*/




# функция вызывает HTML форму комментария
function comment_form_html($objID = 0, $submitUrl = '', $userID = 0)
{
	$CI = &get_instance();

	# если комментарии запрещены
	if(app_get_option('comment_is_true', 'comments', 'no') == 'no')
		return;

	# пользователь
	$userID = 0;
	$userName = '';
	$user = is_login();
	if($user){
		$userID = $user['id'];
		$userName = $user['login'];
	}


	if(!$objID)
		$objID = $CI->urlobjects->id;

	$path = APP_PLUGINS_DIR_PATH . 'comments/';
	$CI->load->add_package_path($path);

	$data = array();
	# если комментировать можно только зарегистрированным пользователям
	if(!$user && (app_get_option('comment_is_true', 'comments', 'no') == 'onlyregister')){
		$CI->load->view('comment_forms/please-register', array(), FALSE);
		$CI->load->remove_package_path($path);
		return;
	}


	if(($commentPost = $CI->input->post('comment')) && app_get_option("comment_method", "comments", "ajax") == 'post')
	{
		if($res = $CI->CommentModel->addNewComment($commentPost)){

			$CI->load->view('comment_forms/comment-complite', array('text' => $commentPost['text']), FALSE);
		}else{
			$CI->load->view('comment_forms/comment-error', array(), FALSE);
		}
	}
	else
	{
		$data['anonim'] = TRUE;
		if($userName)
			$data['anonim'] = FALSE;

		$data['ajaxClass'] = '';
		// если по AJAX
		if(app_get_option("comment_method", "comments", "ajax") == 'ajax'){
			$data['ajaxClass'] = ' form-ajax';
		}

		$data['submitUrl'] = $submitUrl ? $submitUrl : $CI->currentBaseUrl;
		$data['userName'] = $userName;
		$data['objID'] = $objID;
		$CI->load->view('comment_forms/form-main', $data, FALSE);
	}

	$CI->load->remove_package_path($path);
}

# функция выводит дерево комментариев
// $objID - id объекта, к которому показать комментарии
// $isTree - в виде дерева
function comment_tree_html($objID = 0, $isTree = TRUE)
{
	$CI = &get_instance();
	if(!$objID)
		$objID = $CI->urlobjects->id;

	$CI->CommentModel->reset();
	$CI->CommentModel->objectID = $objID;
	$data = $CI->CommentModel->getComments(TRUE, TRUE);
	echo '<ul class="comments">';
	echo comment_create_ul($data);
	echo '</ul>';
}

# функция строит дерево комментариев. Рекурсия
function comment_create_ul($objects = array(), $parent_id = 0, $count = 0)
{
	$tree = '';
	if(is_array($objects) and isset($objects[$parent_id])){

		$link = '';

		if($count != '0')
			$tree .= '<ul class="level'.$count.'">' . N;

		++$count;

		$i = 0;
		foreach($objects[$parent_id] as $object)
		{
			++$i;
			$tree .= '<li>' . N;
			$tree .= '<div class="ctop-item">';
			$tree .= $object['comments_author'];
			$tree .= ' <span class="time">'.date_convert('d.m.Y в H:i', $object['comments_date']).'</span>';
			$tree .= ' <a href="#" class="create-ansver" data-id="'.$object['comments_id'].'">Ответить</a>';
			$tree .= '</div>';
			$tree .= '<div class="cbottom-item">'.$object['comments_content'].'</div>';
			$tree .= '<div class="comment-answer"></div>';
			$tree .= comment_create_ul($objects, $object['comments_id'], $count);
			$tree .= '</li>' . N;
		}
		if($count != '1'){
			$tree .= '</ul>' . N;
		}
		--$count;
	}
	else
	{
		return $tree;
	}
	return $tree;
}
