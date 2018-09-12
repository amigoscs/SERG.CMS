<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  comments plugin
	*
	* UPD 2017-11-30
	* version 2.12

*/

class Admin_plugin {
	
	private $GET, $POST, $ajaxResponse;
	
	
	public function __construct()
	{
		// 200 - OK
		// 400 - ERROR
		$ajaxResponse = array('status' => '400', 'info' => 'ERROR');
		//$this->PAR = $params;
		//$this->CI = & get_instance();
	}
	
	# call
	public function __call($method, $par)
	{
		return $this->index();
	}
	
	public function index()
	{
		$CI = &get_instance();
		$CI->data['PAGE_TITLE'] = 'Плагин комментариев';
		$CI->data['PAGE_DESCRIPTION'] = 'Плагин комментариев';
		$CI->data['PAGE_KEYWORDS'] = 'Плагин комментариев';
		$CI->load->helper('text');
		$data = array();
		
		$data['dateFormat'] = 'd.m.Y в H:i';
		$CI->CommentModel->reset();
		$data['comments'] = $CI->CommentModel->getComments(FALSE, FALSE);
		
		return $CI->load->view('admin_page/index', $data, TRUE);
	}
	
	# редактирование комментария
	public function edit_comment()
	{
		$CI = &get_instance();
		$CI->data['PAGE_TITLE'] = 'Редактирование комментария';
		$CI->data['PAGE_DESCRIPTION'] = 'Редактирование комментария';
		$CI->data['PAGE_KEYWORDS'] = 'Редактирование комментария';
		$data = array();
		$commentID = $CI->input->get('id');
		$CI->CommentModel->reset();
		$CI->CommentModel->commentID = $commentID;
		
		# пост на сохранение
		if($CI->input->post('save_values')){
			$dataUpdate = $CI->input->post('update');
			if($CI->CommentModel->updateComment($dataUpdate)){
				$CI->data['infocomplite'] = app_lang('COMMENT_ACTION_UPDATE');
			}else{
				$CI->data['infoerror'] = app_lang('COMMENT_ERROR_NOTFOUND');
				return '';
			}
		}
		
		# пост на удаление
		if($CI->input->post('delete_comment')){
			
			if($CI->CommentModel->deleteComment()){
				$CI->data['infocomplite'] = app_lang('COMMENT_ACTION_DELETE');
				$CI->data['infocomplite'] .= '. <a href="/admin/comments">' . app_lang('COMMENT_INFO_GO_HOME') . '</a>.';
				return '';
			}else{
				$CI->data['infoerror'] = app_lang('COMMENT_ERROR_NOTFOUND');
				return '';
			}
		}
		
		
		//$CI->CommentModel->reset();
		//$CI->CommentModel->commentID = $commentID;
		$data['COMMENT'] = $CI->CommentModel->getComments(FALSE, FALSE);
		
		if(!$data['COMMENT'] || count($data['COMMENT']) > 1){
			$CI->data['infoerror'] = app_lang('COMMENT_ERROR_NOTFOUND');
			return '';
		}
		
		
		return $CI->load->view('admin_page/comment-edit', $data, TRUE);
	}
	
	
	# все активные комментарии
	public function all_active_comments()
	{
		$CI = &get_instance();
		$CI->data['PAGE_TITLE'] = 'Активные комментарии';
		$CI->data['PAGE_DESCRIPTION'] = 'Активные комментарии';
		$CI->data['PAGE_KEYWORDS'] = 'Активные комментарии';
		
		$data = array();
		$data['comments'] = $CI->CommentModel->getAllComments();
		
		return $CI->load->view('admin_page/comments', $data, TRUE);
	}
	
	
	# принимает ajax
	public function ajax()
	{
		$CI = &get_instance();
		$action = $CI->input->get('action');
		$this->ajaxResponse['status'] = 'error';
		$this->ajaxResponse['info'] = 'Empty';
		
		switch($action)
		{
			case 'changestatus':
				$this->_ajaxChangeStatus($CI->input->get('status'));
				break;
			case 'delete':
				$this->_ajaxDeleteComment();
				break;
			default:
				$this->ajaxResponse['status'] = 'error';
				$this->ajaxResponse['info'] = app_lang('COMMENT_ACTION_WRONGREG');
		}
		
		echo json_encode($this->ajaxResponse);
	}
	
	# ajax. Изменяет статус публикации комментария
	private function _ajaxChangeStatus($status)
	{
		$CI = &get_instance();
		if(!$commentID = $CI->input->post('comment_id')){
			$this->ajaxResponse['status'] = 'error';
			$this->ajaxResponse['info'] = app_lang('COMMENT_ACTION_NOT_ID');
			return;
		}
		
		$CI->CommentModel->reset();
		$CI->CommentModel->commentID = $commentID;
		if($CI->CommentModel->updateComment(array('status' => $status))){
			$this->ajaxResponse['status'] = 'OK';
			$this->ajaxResponse['info'] = app_lang('COMMENT_ACTION_SETSTATUS'). ': ' . $status;
		}else{
			$this->ajaxResponse['status'] = 'error';
			$this->ajaxResponse['info'] = app_lang('COMMENT_ACTION_NOTUPDATE');
			
		}
		$this->ajaxResponse['new_status'] = $status;
	}
	
	# ajax. Удаляет комментарий
	private function _ajaxDeleteComment()
	{
		$CI = &get_instance();
		if(!$commentID = $CI->input->post('comment_id')){
			$this->ajaxResponse['status'] = 'error';
			$this->ajaxResponse['info'] = app_lang('COMMENT_ACTION_NOT_ID');
			return;
		}
		
		$CI->CommentModel->reset();
		$CI->CommentModel->commentID = $commentID;
		if($CI->CommentModel->deleteComment($commentID)){
			$this->ajaxResponse['status'] = 'OK';
			$this->ajaxResponse['info'] = app_lang('COMMENT_ACTION_DELETE');
		}else{
			$this->ajaxResponse['status'] = 'error';
			$this->ajaxResponse['info'] = app_lang('COMMENT_ACTION_ERRORDELETE');
		}
	}
}
