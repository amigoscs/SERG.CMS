<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	* Модель виджета

	* DATE UPD: 2017-11-22
	* version 0.12
	*
	* DATE UPD: 2018-09-07
	* version 1.0
*/

class WidjetAdmModel extends CI_Model {

	protected  $widjetInfoNumber, $widjetInfoTitle, $widjetIcon, $widjetContent, $widjetLinkToPage;
	protected $trueBuild;

	public function __construct()
    {
        parent::__construct();
		$this->reset();
	}

	public function checkAccess($pluginFolder = '')
	{
		# доступ
		if($this->LoginAdmModel->checkAccessUser($pluginFolder)) {
			$this->trueBuild = true;
		}
	}

	protected function reset()
	{
		$this->widjetInfoNumber = '';
		$this->widjetInfoTitle = '';
		$this->widjetIcon = '';
		$this->widjetLinkToPage = '';
		$this->widjetContent = '';
		$this->trueBuild = false;
	}

	public function build () {
		return;
	}

	public function _render()
	{
		if(!$this->trueBuild) {
			return '';
		}
		$data['widjetInfoNumber'] = $this->widjetInfoNumber;
		$data['widjetInfoTitle'] = $this->widjetInfoTitle;
		$data['widjetIcon'] = $this->widjetIcon;
		$data['widjetLinkToPage'] = $this->widjetLinkToPage;
		$data['widjetContent'] = $this->widjetContent;

		return $this->load->view('admin/widjet', $data, true);
	}
}
