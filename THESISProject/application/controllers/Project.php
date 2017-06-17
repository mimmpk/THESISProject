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
		$this->load->model('FunctionalRequirement_model', 'mFR');
		$this->load->model('TestCase_model', 'mTestCase');
		$this->load->model('RTM_model', 'mRTM');
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
			'customer' => '',
			'databaseName' => '',
			'hostname' => '',
			'port' => '',
			'username' => '',
			'password' => ''
		);

		$data['projectInfo'] = $formObj;
		$data['error_message'] = '';
		$this->openMaintenanceView($data, 'new');
	}

	public function viewDetail($projectId){
		if(isset($projectId) && null != $projectId){
			$data['projectInfo'] = $this->Project->searchProjectDetail($projectId);
			$data['error_message'] = '';
			$this->openMaintenanceView($data, 'view');
		}else{
			echo "error";
		}
	}

	public function editDetail($projectId){
		if(isset($projectId) && null != $projectId){
			$data['projectInfo'] = $this->Project->searchProjectDetail($projectId);
			$data['error_message'] = '';
			$this->openMaintenanceView($data, 'edit');
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
		$databaseName = trim($this->input->post('inputDatabaseName'));
		$hostname = trim($this->input->post('inputHostname'));
		$port = trim($this->input->post('inputPort'));
		$username = trim($this->input->post('inputUsername'));
		$password = trim($this->input->post('inputPassword'));

		$this->FValidate->set_rules('inputProjectName', null, 'required|max_length[100]');
		$this->FValidate->set_rules('inputProjectNameAlias', null, 'required|max_length[50]');
		$this->FValidate->set_rules('inputStartDate', null, 'required');
		$this->FValidate->set_rules('inputEndDate', null, 'required');
		$this->FValidate->set_rules('inputCustomer', null, 'required|max_length[100]');

		$this->FValidate->set_rules('inputDatabaseName', null, 'required|max_length[255]');
		$this->FValidate->set_rules('inputHostname', null, 'required|max_length[255]');
		$this->FValidate->set_rules('inputPort', null, 'required|max_length[4]');
		$this->FValidate->set_rules('inputUsername', null, 'required|max_length[50]');
		$this->FValidate->set_rules('inputPassword', null, 'required|max_length[50]');

		if($this->FValidate->run()){
			//**Check StartDate must less than EndDate.
			$startDate = DateTime::createFromFormat($format, $startDateInput);
			$endDate = DateTime::createFromFormat($format, $endDateInput);
			if($startDate > $endDate){
				$error_message = ER_MSG_002;
			}else{
				$user = (null != $this->session->userdata('username'))? $this->session->userdata('username') : 'userDefault';
				$paramObj = (object) array (
					'projectId' => $projectId, 
					'projectName' => $projectName,
					'projectAlias' => $projectNameAlias, 
					'startDate' => $startDate, 
					'endDate' => $endDate, 
					'customer' => $customer, 
					'user' => $user,
					'databaseName' => $databaseName,
					'hostname' => $hostname,
					'port' => $port,
					'username' => $username,
					'password' => $password
				);
				if('new' == $mode){ //save
					$existResult = $this->Project->searchCountProjectInformationByProjectName($projectName);
					//var_dump($existResult);
					if(null != $existResult && 0 == (int)$existResult[0]['counts']){
						//echo "success";
						$result = $this->Project->insertProjectInformation($paramObj);
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
					$rowResult = $this->Project->updateProjectInformation($paramObj);
					if(0 < $rowResult){
						echo "<script type='text/javascript'>alert('Save Successful!')</script>";
						$this->viewDetail($projectId);
						return false;
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
			'customer' => $customer,
			'databaseName' => $databaseName,
			'hostname' => $hostname,
			'port' => $port,
			'username' => $username,
			'password' => $password
		);
		$data['projectInfo'] = $formObj;
		$data['error_message'] = $error_message;
		$this->openMaintenanceView($data, $mode);
	}

	/* When users click 'Start Project' button, they can not initiate data. 
	* (Database Schema, Functional Requirments, Test Cases, RTM)
	*/
	public function startProject($projectId){
		$error_message = '';
		$success_message = '';

		$resultValidate = $this->validateStartProject($projectId, $error_message);
		if($resultValidate == TRUE){
			$user = (null != $this->session->userdata('username'))? $this->session->userdata('username') : 'userDefault';
			$param = (object) array('user' => $user, 'projectId' => $projectId);
			
			$updateRecords = $this->Project->updateProjectInformation_byStartFlag($param);
			if(0 < $updateRecords){
				$success_message = 'Start Project process finished.';
			}else{
				$error_message = 'Start Project proecess failed. Please try again.';
			}
		}

		$data['projectInfo'] = $this->Project->searchProjectDetail($projectId);
		$data['success_message'] = $success_message;
		$data['error_message'] = $error_message;
		$this->openMaintenanceView($data, 'view');
	}

	//Validate
	private function validateStartProject($projectId, &$error_message){
		
		$countFR = $this->mFR->searchExistFunctionalRequirement('', $projectId);
		if(0 == count($countFR)){
			$error_message = ER_IMP_054;
			return FALSE;
		}

		$countTC = $this->mTestCase->searchExistTestCaseHeader($projectId, '');
		if(0 == count($countTC)){
			$error_message = ER_IMP_055;
			return FALSE;
		}

		$countRTM = $this->mRTM->searchExistRTMVersion($projectId);
		if(0 == count($countRTM)){
			$error_message = ER_IMP_056;
			return FALSE;
		}
		return TRUE;
	}

	private function checkNullOrEmpty($varInput){
		return (($varInput == null) || ($varInput == ""));
	}

	private function openSearchView($data){
		$data['html'] = 'ProjectManagement/projectSearch_view';
		$data['active_title'] = 'master';
		$data['active_page'] = 'mats001';
		$this->load->view('template/header');
		$this->load->view('template/body', $data);
		$this->load->view('template/footer');
	}

	private function openMaintenanceView($data, $mode){
		if("view" == $mode){
			$data['html'] = 'ProjectManagement/projectMaintenanceDetail_view';
		}else{
			$data['html'] = 'ProjectManagement/projectMaintenance_view';
		}
		$data['mode'] = $mode;
		$data['active_title'] = 'master';
		$data['active_page'] = 'mats001';
		$this->load->view('template/header');
		$this->load->view('template/body', $data);
		$this->load->view('template/footer');
	}

}
