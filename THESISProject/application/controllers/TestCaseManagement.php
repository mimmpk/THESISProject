<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Test Case Management Controller
*/
class TestCaseManagement extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->load->library('form_validation', null, 'FValidate');
		$this->load->library('session');
		$this->load->model('Project_model', 'mProject');
		$this->load->model('FunctionalRequirement_model', 'mFnReq');
	}

	public function index(){
		$formObj = (object) array('selectedProjectId' => '', 'selectedFnReqId' => '');
		$data['formData'] = $formObj;
		$data['error_message'] = '';
		$data['searchFlag'] = '';
		$data['result'] = null;
		$this->openView($data, 'search');
	}

	public function reset(){
		$this->index();
	}

	public function search(){
		
	}

	private function openView($data, $page){
		if('search' == $page){
			$data['html'] = 'TestCaseManagement/testCaseSearch_view';
			$data['projectCombo'] = $this->mProject->searchActiveProjectCombobox();
		}else{
			$data['html'] = 'TestCaseManagement/testCaseUpload_view';
		}
		$data['active_title'] = 'master';
		$data['active_page'] = 'mats003';
		$this->load->view('template/header');
		$this->load->view('template/body', $data);
		$this->load->view('template/footer');
	}
}
?>