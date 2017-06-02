<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* Cancellation Change Controller
*/
class Cancellation extends CI_Controller{
	
	function __construct(){
		parent::__construct();

		$this->load->model('Project_model', 'mProject');
		$this->load->model('ChangeManagement_model', 'mChange');
		$this->load->model('Cancellation_model', 'mCancellation');

		$this->load->library('form_validation', null, 'FValidate');
	}

	public function index(){
		$data['error_message'] = '';
		$this->openView($data, 'search');
	}

	/* Method for searching All Changes Information*/
	public function search(){
		$error_message = '';
		$changeList = null;

		$projectId = $this->input->post('inputProject');
		$this->FValidate->set_rules('inputProject', null, 'required');
		if($this->FValidate->run()){
			$changeList = $this->mCancellation->searchChangesInformationForCancelling($projectId);
			if(0 == count($changeList)){
				$error_message = ER_MSG_006;
			}

			$data['selectedProjectId'] = $projectId;
		}
		$formObj = (object) array('projectId' => $projectId);
		$data['criteria'] = $formObj;
		$data['changeList'] = $changeList;
		$data['error_message'] = $error_message;
		$this->openView($data, 'search');
	}

	public function viewDetail($projectId = '', $changeRequestNo = ''){
		$error_message = '';
		
		$headerInfo = array();
		$detailInfo = array();
		
		$affectedFnReqList = array();
		$affectedTCList = array();
		$affectedSchemaList = array();
		$affectedRTMList = array();

		if(!empty($changeRequestNo) && !empty($projectId)){
			
			$data['keyParam'] = array(
				'changeRequestNo' => $changeRequestNo, 'projectId' => $projectId);

			//Get All Change Request Data
			$this->getAllChangeRequestData($changeRequestNo, $projectId, $error_message, $data);

		}else{
			$error_message = ER_MSG_011;
		}

		$data['error_message'] = $error_message;
		$this->openView($data, 'view');
	}

	public function cancel(){
		$error_message = '';
		$success_message = '';

		$changeRequestNo = $this->input->post('changeRequestNo');
		$projectId = $this->input->post('projectId');
		$reason = $this->input->post('inputReason');

		$this->FValidate->set_rules('inputReason', null, 'trim|required');
		if($this->FValidate->run()){
			$success_message = IF_MSG_001;

			

			
		}else{
			$error_message = ER_MSG_019;
		}


		$data['keyParam'] = array(
				'changeRequestNo' => $changeRequestNo, 
				'projectId' 	  => $projectId);

		//Get All Change Request Data
		$this->getAllChangeRequestData($changeRequestNo, $projectId, $error_message, $data);

		$data['error_message'] = $error_message;
		$this->openView($data, 'view');
	}

	private function getAllChangeRequestData($changeRequestNo, $projectId, &$error_message, &$data){

		$resultList = array();

		$changeHeaderResult = $this->mCancellation->searchChangesInformationForCancelling($projectId, $changeRequestNo);
			if(0 < count($changeHeaderResult)){
				$headerInfo = array(
					'changeRequestNo' 	=> $changeHeaderResult[0]['changeRequestNo'],
					'changeUser' 		=> $changeHeaderResult[0]['changeUser'],
					'changeDate' 		=> $changeHeaderResult[0]['changeDate'],
					'fnReqNo' 			=> $changeHeaderResult[0]['changeFunctionNo'],
					'fnReqVer' 			=> $changeHeaderResult[0]['changeFunctionVersion'],
					'fnReqDesc' 		=> $changeHeaderResult[0]['functionDescription'],
					'isLatestChange'	=> $changeHeaderResult[0]['isLatestChange']	);

				//search change detail
				$detailInfo = $this->mChange->getChangeRequestInputList($changeRequestNo);

				$affectedFnReqList = $this->mChange->getChangeHistoryFnReqHeaderList($changeRequestNo);

				$affectedTCList = $this->mChange->getChangeHistoryTestCaseList($changeRequestNo);

				$affectedSchemaList = $this->mChange->getChangeHistoryDatabaseSchemaList($changeRequestNo);

				$affectedRTMList = $this->mChange->getChangeHistoryRTM($changeRequestNo);
			}else{
				$error_message = ER_MSG_017;
				return false;
			}

		$data['headerInfo'] = $headerInfo;
		$data['detailInfo'] = $detailInfo;

		$data['affectedFnReqList'] = $affectedFnReqList;
		$data['affectedTCList'] = $affectedTCList;
		$data['affectedSchemaList'] = $affectedSchemaList;
		$data['affectedRTMList'] = $affectedRTMList;
		return true;
	}

	private function openView($data, $view){
		if('search' == $view){
			$data['html'] = 'CancellationManagement/cancellationSearch_view';
			$data['projectCombo'] = $this->mProject->searchStartProjectCombobox();
		}else if('view' == $view){
			$data['html'] = 'CancellationManagement/cancellationDetail_view';
		}else{
			$data['html'] = 'CancellationManagement/cancellationResult_view';
		}
		
		$data['active_title'] = 'changeManagement';
		$data['active_page'] = 'trns002';
		$this->load->view('template/header');
		$this->load->view('template/body', $data);
		$this->load->view('template/footer');
	}


}

?>