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
		$this->response = array('status' => 'ERROR', 'info' => '', 'error_type' => 1);
    }

	/*
	* метод вызывается после вызова $this->ajaxRun()
	*/
	public function newcomment()
	{
		$CI = &get_instance();

		if(!isset($this->post['comment'])){
			$this->response['info'] = 'Error form';
			$this->renderResponse();
			return;
		}

		$writeComment = false;

		# проверка рекапчи
		if($cCaptha = app_get_option("comment_captcha", "comments", "")) {
			if(!isset($this->post['g-recaptcha-response']) || !$this->post['g-recaptcha-response']) {
				$this->response['info'] = 'Вы не прошли проверку!';
				$this->renderResponse();
				return;
			} else {
				$secretKey = app_get_option("comment_captcha_skey", "comments", "");
				if(!$secretKey) {
					$this->response['info'] = 'Ошибка настройки формы! Проверьте ключ.';
					$this->renderResponse();
					return;
				}

				$url = 'https://www.google.com/recaptcha/api/siteverify';
				$params = array(
				    'secret' => $secretKey, // это будет $_POST
				    'response' => $this->post['g-recaptcha-response'], // это будет $_POST
				);
				$result = file_get_contents($url, false, stream_context_create(array(
				    'http' => array(
				        'method'  => 'POST',
				        'header'  => 'Content-type: application/x-www-form-urlencoded',
				        'content' => http_build_query($params)
				    )
				)));
				if($result) {
					$resultObject = json_decode($result, true);
					if($resultObject['success']) {
						$writeComment = true;
					} else {
						$this->response['info'] = 'Вы не можете отправить комментарий';
					}
				}

				/*$myCurl = curl_init();
				curl_setopt_array($myCurl, array(
					CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => http_build_query(
							array(
							'secret' => $cCaptha,
							'response' => $this->post['g-recaptcha-response'],
							)
						)
					));
				$response = curl_exec($myCurl);
				curl_close($myCurl);*/

			}
		}

		if(!$writeComment) {
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
		$this->renderResponse();
	}

	private function renderResponse()
	{
		echo json_encode($this->response);
	}

}
