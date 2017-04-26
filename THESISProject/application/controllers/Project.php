<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project extends CI_Controller {

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

	function __construct(){
		parent::__construct();
		$this->load->model('Project_model', 'Project');
		$this->load->library('form_validation', null, 'FValidate');
		$this->load->library('session');
	}

	public function index(){
		$data['error_message'] = '';
		$data['result'] = null;
		$this->openSearchView($data);
	}

	public function search(){
		$error_message = '';
		$projectName = trim($this->input->post('inputProjectName'));
		$projectNameAlias = trim($this->input->post('inputProjectNameAlias'));
		$startDateFrom = trim($this->input->post('inputStartDateFrom'));
		$startDateTo = trim($this->input->post('inputStartDateTo'));
		$endDateFrom = trim($this->input->post('inputEndDateFrom'));
		$endDateTo = trim($this->input->post('inputEndDateTo'));
		$customer = trim($this->input->post('inputCustomer'));

		/*echo $projectName."test";
		echo $projectNameAlias."<br/>";
		echo $startDate."<br/>";
		echo $endDate."<br/>";
		echo $customer."<br/>";*/

		if(!$this->checkNullOrEmpty($projectName) 
			|| !$this->checkNullOrEmpty($projectNameAlias) 
			|| !$this->checkNullOrEmpty($startDateFrom) 
			|| !$this->checkNullOrEmpty($startDateTo) 
			|| !$this->checkNullOrEmpty($endDateFrom)
			|| !$this->checkNullOrEmpty($endDateTo) 
			|| !$this->checkNullOrEmpty($customer)) {
			$result = $this->Project->searchProjectInformation($projectName, $projectNameAlias, $startDateFrom, $startDateTo, $endDateFrom, $endDateTo, $customer);

			//var_dump($result);
		}else{
			//warning message
			$error_message = ER_MSG_001;
			$result = null;
		}
		$data['error_message'] = $error_message;
		$data['result'] = $result;
		$this->openSearchView($data);
	}

	public function back(){
		//$session_id = $this->session->userdata('session_id');
		//$criteriaSession = "mst001Criteria".$session_id;
		$this->index();
	}

	public function reset(){
		unset($_POST);
		$this->index();
	}

	public function newProject(){
		
		$formObj = (object)array(
			'projectId' => '',
			'projectName' => '', 
			'projectNameAlias' => '', 
			'startDate' => '', 
			'endDate' => '', 
			'customer' => ''
		);

		$data['projectInfo'] = $formObj;
		$data['error_message'] = '';
		$data['mode'] = 'new';
		$this->openMaintenanceView($data);
	}

	public function viewDetail($projectId){
		if(isset($projectId) && null != $projectId){
			$data['projectInfo'] = $this->Project->searchProjectDetail($projectId);
			$data['mode'] = 'view';
			$data['error_message'] = '';
			$this->openMaintenanceView($data);
		}else{
			echo "error";
		}
	}

	public function editDetail($projectId){
		if(isset($projectId) && null != $projectId){
			$data['projectInfo'] = $this->Project->searchProjectDetail($projectId);
			$data['mode'] = 'edit';
			$data['error_message'] = '';
			$this->openMaintenanceView($data);
		}else{
			echo "error";
		}
	}

	public function save(){
		$format = 'd/m/Y';
		$error_message = '';

		$projectId = trim($this->input->post('projectId'));
		$projectName = trim($this->input->post('inputProjectName'));
		$projectNameAlias = trim($this->input->post('inputProjectNameAlias'));
		$startDateInput = trim($this->input->post('inputStartDate'));
		$endDateInput = trim($this->input->post('inputEndDate'));
		$customer = trim($this->input->post('inputCustomer'));
		$mode = trim($this->input->post('mode'));

		$this->FValidate->set_rules('inputProjectName', null, 'required|max_length[100]');
		$this->FValidate->set_rules('inputProjectNameAlias', null, 'required|max_length[50]');
		$this->FValidate->set_rules('inputStartDate', null, 'required');
		$this->FValidate->set_rules('inputEndDate', null, 'required');
		$this->FValidate->set_rules('inputCustomer', null, 'required|max_length[100]');

		if($this->FValidate->run()){
			//**Check StartDate must less than EndDate.
			$startDate = DateTime::createFromFormat($format, $startDateInput);
			$endDate = DateTime::createFromFormat($format, $endDateInput);
			if($startDate > $endDate){
				$error_message = ER_MSG_002;
			}else{
				$user = (null != $this->session->userdata('username'))? $this->session->userdata('username') : 'userDefault';
				if('new' == $mode){ //save
					$existResult = $this->Project->searchCountProjectInformationByProjectName($projectName);
					//var_dump($existResult);
					if(null != $existResult && 0 == (int)$existResult[0]['counts']){
						//echo "success";
						$result = $this->Project->insertProjectInformation($projectName, $projectNameAlias, $startDate, $endDate, $customer, $user);
						if(null != $result){
							echo "<script type='text/javascript'>alert('Save Successful!')</script>";
							$this->viewDetail($result);
						}else{
							$error_message = ER_MSG_003;
						}
					}else{
						$error_message = ER_MSG_004;
					}
				}else{ //update
					$paramObj = (object) array (
						'projectId' => $projectId, 
						'projectAlias' => $projectNameAlias, 
						'startDate' => $startDate, 
						'endDate' => $endDate, 
						'customer' => $customer, 
						'user' => $user);
					$rowResult = $this->Project->updateProjectInformation($paramObj);
					if(0 < $rowResult){
						echo "<script type='text/javascript'>alert('Save Successful!')</script>";
						$mode = "view";
					}else{
						$error_message = ER_MSG_005;
					}
				}
			}
		}

		$formObj = (object)array(
			'projectId' => $projectId,
			'projectName' => $projectName, 
			'projectNameAlias' => $projectNameAlias, 
			'startDate' => $startDateInput, 
			'endDate' => $endDateInput, 
			'customer' => $customer
		);
		//var_dump($formObj);
		$data['projectInfo'] = $formObj;
		$data['mode'] = $mode;
		$data['error_message'] = $error_message;
		$this->openMaintenanceView($data);
	}

	private function checkNullOrEmpty($varInput){
		return (($varInput == null) || ($varInput == ""));
	}

	private function openSearchView($data){
		$data['html'] = 'projectSearch_view';
		$data['active_title'] = 'master';
		$data['active_page'] = 'mats001';
		$this->load->view('template/header');
		$this->load->view('template/body', $data);
		$this->load->view('template/footer');
	}

	private function openMaintenanceView($data){
		$data['html'] = 'projectMaintenance_view';
		$data['active_title'] = 'master';
		$data['active_page'] = 'mats001';
		$this->load->view('template/header');
		$this->load->view('template/body', $data);
		$this->load->view('template/footer');
	}

}
