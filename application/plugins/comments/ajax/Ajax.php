<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  to-cart Ajax class
	*
	* UPD 2018-02-06
	* version 2.0 В РАБОТЕ
*/

class Ajax extends CI_Model {


	private $post, $get; // данные POST и GET
	private $response;

	function __construct($post, $get)
    {
        parent::__construct();
		$this->post = $post;
		$this->get = $get;
		$this->response = array('status' => 'ERROR', 'info' => '');
    }
	/*
	* функция вызывается для запуска методов
	*/
	public function ajaxRun()
	{
		$CI = &get_instance();
		if(!isset($this->get['action'])){
			$this->response['info'] = 'Error action';
			$this->renderResponse();
			return;
		}

		switch($this->get['action'])
		{
			case 'newcomment':
				if(!isset($this->post['comment'])){
					$this->response['info'] = 'Error form';
					$this->renderResponse();
					return;
				}

				if($CI->CommentModel->addNewComment($this->post['comment'])){
					$this->response['status'] = 'OK';
					$this->response['info'] = app_lang('COMMENT_CREATE_COMPLITE');
					$this->response['comment'] = $CI->CommentModel->newComment;
				}else{
					$this->response['info'] = app_lang('COMMENT_CEATE_ERROR');
				}
			break;
			default:
				$this->response['info'] = 'Wrong action';
		}
		$this->renderResponse();
	}


	/*
	* метод вызывается после вызова $this->ajaxRun()
	*/
	public function getResult()
	{

	}

	private function renderResponse()
	{
		echo json_encode($this->response);
	}

}
