<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('ChangeManagement_model', 'mChange');
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$data['html'] = 'dashboard_view';
		$data['active_title'] = 'dashboard';
		$data['active_page'] = 'dhbs001';


		//1. Get Number of Project, FnReq, TestCase, DatabaseSchema

		//2. Get Latest Change Information
		$data['changeList'] = $this->mChange->searchChangeRequestList();

		$this->load->view('template/header');
		$this->load->view('template/body',$data);
		$this->load->view('template/footer');
	}

}
