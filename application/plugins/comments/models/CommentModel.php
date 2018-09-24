<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	*  CommentModel Model

	* UPD 2017-11-30
	* Version 2.11
	*
	* UPD 2018-03-16
	* Version 3.0
	* переделана логика работы
	*
	* UPD 2018-09-24
	* Version 3.1
	* Убраны теги в отправке комментария

*/

class CommentModel extends CI_Model {


	private $commentsArray;
	private $tableName;
	public $newComment;

	/*
	* ID комментария
	*/
	public $commentID;

	/*
	* ID объекта
	*/
	public $objectID;

	function __construct()
    {
        parent::__construct();

		$this->reset();
    }

	# сброс параметров
	public function reset()
	{
		$this->commentsArray = array();
		$this->tableName = 'comments';
		$this->commentID = 0;
		$this->objectID = 0;
		$this->newComment = array();
	}

	# создать новую запись комментария
	function addNewComment($postValue)
	{
		if(!isset($postValue['text']))
			return FALSE;

		if(!isset($postValue['object']) && !$postValue['object'])
			return FALSE;

		# разрещение на комментирование
		$commentsAllowed = app_get_option('comment_is_true', 'comments', 'no');

		# параметры из инпут

		if($commentsAllowed == 'no')
			return FALSE;

		$paramsComment = array();
		$paramsComment['comments_parent'] = isset($postValue['parent']) ? $postValue['parent'] : 0;
		$paramsComment['comments_object'] = $postValue['object'];
		$paramsComment['comments_user'] = 0;
		$paramsComment['comments_author'] = isset($postValue['author']) ? strip_tags($postValue['author']) : '';
		$paramsComment['comments_content'] = htmlspecialchars($postValue['text']);
		$paramsComment['comments_ratup'] = isset($postValue['ratup']) ? $postValue['ratup'] : 0;
		$paramsComment['comments_ratdown'] = isset($postValue['ratdown']) ? $postValue['ratdown'] : 0;
		$paramsComment['comments_stars'] = isset($postValue['stars']) ? $postValue['stars'] : 0;
		$paramsComment['comments_status'] = 'hidden';
		$paramsComment['comments_date'] = date('Y-m-d H:i:s');
		$paramsComment['comments_ip'] = $_SERVER['REMOTE_ADDR'];
		$paramsComment['comments_new'] = 1;


		# пользователь
		$user = is_login();

		// если пользователь есть в POST
		if(isset($postValue['user']))
			$paramsComment['comments_user'] = $postValue['user'];
		elseif($user)
			$paramsComment['comments_user'] = $user['id'];

		# если нет юзера и комменты от анонимов запрещены, то FALSE
		if(!$paramsComment['comments_user'] && $commentsAllowed == 'onlyregister')
			return FALSE;

		# установка статуса видимости
		$modStatus = app_get_option('comment_moderation', 'comments', 'yes');
		if($modStatus == 'no'){
			$paramsComment['comments_status'] = 'publish';
		}elseif($modStatus == 'noregister' && $user){
			$paramsComment['comments_status'] = 'publish';
		}

		if($this->db->insert($this->tableName, $paramsComment)){
			$newCommentID = $this->db->insert_id();
			$paramsComment['comments_id'] = $newCommentID;
			$this->newComment = $paramsComment;
			$this->sendInfoMail($paramsComment);
			return $newCommentID;
		}
		else
			return 0;
	}

	# Обновить комметарий
	public function updateComment($args = array())
	{
		$params = array();
		isset($args['parent']) ? 	$params['comments_parent'] = $args['parent'] : 0;
		isset($args['object']) ? 	$params['comments_object'] = $args['object'] : 0;
		isset($args['user']) ? 		$params['comments_user'] = $args['user'] : 0;
		isset($args['author']) ? 	$params['comments_author'] = $args['author'] : 0;
		isset($args['content']) ? 	$params['comments_content'] = $args['content'] : 0;
		isset($args['ratup']) ? 	$params['comments_ratup'] = $args['ratup'] : 0;
		isset($args['ratdown']) ? 	$params['comments_ratdown'] = $args['ratdown'] : 0;
		isset($args['stars']) ? 	$params['comments_stars'] = $args['stars'] : 0;
		isset($args['status']) ? 	$params['comments_status'] = $args['status'] : 0;
		isset($args['date']) ? 		$params['comments_date'] = $args['date'] : 0;
		isset($args['new']) ? 		$params['comments_new'] = $args['new'] : 0;

		if($params){
			$this->newComment = $params;
			$this->db->where('comments_id', $this->commentID);
			return $this->db->update($this->tableName, $params);
		}else{
			return FALSE;
		}

	}

	# Удалить комметарий
	public function deleteComment()
	{
		return $this->db->delete($this->tableName, array('comments_id' => $this->commentID));
	}


	# возвращает комментарии для объекта
	// $onlyPublish - только опубликованные
	// $isTree - вернуть в структуре дерева
	public function getComments($onlyPublish = TRUE, $isTree = FALSE)
	{
		$out = array();

		$this->db->select('
			comments.*,
			objects.obj_id,
			objects.obj_canonical,
			objects.obj_name,
			objects.obj_h1,
			objects.obj_user_author,
			users.users_id,
			users.users_group,
			users.users_login,
			users.users_ip_register,
			users.users_name,
			users.users_email,
			users.users_lang,
			users.users_status
			');


		$this->db->join('objects', 'objects.obj_id = comments.comments_object');
		$this->db->join('users', 'comments.comments_user = users.users_id', 'left');

		# комментарии к конкретному объекту
		if($this->objectID)
		{
			if(is_array($this->objectID))
				$this->db->where_in('comments_object', $this->objectID);
			else
				$this->db->where('comments_object', $this->objectID);
		}

		if($this->commentID)
		{
			if(is_array($this->commentID))
				$this->db->where_in('comments_id', $this->commentID);
			else
				$this->db->where('comments_id', $this->commentID);
		}

		if($onlyPublish)
			$this->db->where('comments_status', 'publish');

		$this->db->order_by('comments_date', 'DESC');

		$query = $this->db->get($this->tableName);


		if($isTree) {
			foreach($query->result_array() as $row){
				$out[$row['comments_parent']][$row['comments_id']] = $row;
			}
		}else{
			foreach($query->result_array() as $row){
				$out[$row['comments_id']] = $row;
			}
		}
		return $out;
	}

	# отправка уведомления на e-mail
	private function sendInfoMail($commentData = array())
	{
		//pr($commentData);
		$this->load->library('email');
		$emailProtocol = app_get_option('email_protocol', 'general', 'mail');
		$fromEmail = app_get_option('server_mail', 'general', '');
		$adminEmail = app_get_option("comment_email", "comments", "");
		$fromName = $siteName = app_get_option('site_name', 'site', 'SiteName');
		if(!$adminEmail){
			return FALSE;
		}

		$mailText = '<p>Автор - '.$commentData['comments_author'].'</p>';
		$mailText .= '<p>'.$commentData['comments_content'].'</p>';
		$mailText .= '<p>User ID - '.$commentData['comments_user'].'</p>';
		$mailText .= '<p><a href="'.info('baseUrl').'admin/comments/edit_comment?id='.$commentData['comments_id'].'">Edit comment »</a></p>';

		# конфигурация почты
		$this->email->clear();
		$this->email->protocol = $emailProtocol;
		$this->email->from($fromEmail, $fromName);
		$this->email->mailtype = 'html';
		$this->email->reply_to($fromEmail, $fromName);
		$this->email->subject($siteName . ' - ' . app_lang('COMMENT_MAIL_SUBJ'));
		$this->email->message($mailText);
		$this->email->to($adminEmail);
		$this->email->send();
	}

}
