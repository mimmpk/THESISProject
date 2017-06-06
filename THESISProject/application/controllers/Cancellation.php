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

		try{
			$this->FValidate->set_rules('inputReason', null, 'trim|required');
			if($this->FValidate->run()){
				$success_message = IF_MSG_001;

				/** 1. Get Change Details */
				$changeInfo = $this->mChange->getChangeRequestInformation($changeRequestNo);
				$param = (object) array(
					'projectId'  => $projectId,
					'status' 	 => 1,
					'functionId' => $changeInfo->changeFunctionId
					);
				$lastFRInfo = $this->mFR->searchFunctionalRequirementHeaderInfo($param);

				/** 2. Call Change API */
				$param = (object) array(
					'projectId' 	  => $projectId,
					'functionId' 	  => $changeInfo->changeFunctionId,
					'functionNo' 	  => $changeInfo->changeFunctionNo,
					'functionVersion' => $lastFRInfo[0]['functionVersion'],
					'changeRequestNo' => $changeRequestNo,
					'type' 			  => 2 //1 = Change, 2 = Cancel
					);
				$changeResult = $this->callChangeAPI($param);

				/** 3. Control Version */
				

				/** 4. Update Change Request's Status */

				/** 5. Display Result */
				
			}else{
				$error_message = ER_MSG_019;
			}
		}catch(Exception $e) {
			$error_message = $e->getMessage();
		}

		$data['keyParam'] = array(
				'changeRequestNo' => $changeRequestNo, 
				'projectId' 	  => $projectId);

		//Get All Change Request Data
		$this->getAllChangeRequestData($changeRequestNo, $projectId, $error_message, $data);

		$data['error_message'] = $error_message;
		$data['success_message'] = $success_message;
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

	private function callChangeAPI($param){
		$passData = array();
		$allFRHeader = array();
		$allFRDetail = array();
		$allTCHeader = array();
		$allTCDetail = array();
		$allRTM = array();
		$changeList = array();

		$this->load->library('common');

		$passData['callType'] = $param->type;
		//1.Project Information
		$projectInfo = $this->mProject->searchProjectDetail($param->projectId);
		$passData['projectInfo'] = $param->projectId;
		$passData['connectDatabaseInfo'] = array(
			'databaseName' 	=> $projectInfo->databaseName, 
			'hostname' 		=> $projectInfo->hostname, 
			'port' 			=> $projectInfo->port, 
			'username' 		=> $projectInfo->username, 
			'password' 		=> $projectInfo->password);
		
		//2. All Functional Requirements Header data
		$criteria = (object) array('projectId' => $param->projectId, 'status' => '1');
		$frHeaderList = $this->mFR->searchFunctionalRequirementHeaderInfo($criteria);
		foreach($frHeaderList as $value){
			$allFRHeader[$value['functionNo']] = array(
				'functionVersion' 	=> $value['functionVersion'], 
				'functionDesc' 		=> $value['fnDesc']);
		}
		$passData['FRHeader'] = $allFRHeader;

		//3. All Functional Requirements Detail data
		$functionNo = '';
		$frDetailList = $this->mFR->searchFunctionalRequirementDetail($criteria);
		foreach($frDetailList as $value){
			$allFRDetail[$value['functionNo']][$value['inputName']] = array( 
				'dataType' 		=> $value['dataType'],
				'dataLength' 	=> $value['dataLength'],
				'scale' 		=> $value['decimalPoint'],
				'unique' 		=> $value['constraintUnique'],
				'notNull' 		=> $value['constraintNull'],
				'default' 		=> $value['constraintDefault'],
				'min' 			=> $value['constraintMinValue'],
				'max' 			=> $value['constraintMaxValue'],
				'tabelName' 	=> $value['tableName'],
				'columnName' 	=> $value['columnName']);
		}
		$passData['FRDetail'] = $allFRDetail;

		//4. All Test Case Header data
		$tcHeaderList = $this->mTestCase->searchTestCaseInfoByCriteria($param->projectId, '1');
		foreach($tcHeaderList as $value){
			$allTCHeader[$value['testCaseNo']] = array(
				'testCaseVersion' 	=> $value['testCaseVersion'], 
				'testCaseDesc' 	 	=> $value['testCaseDescription'],
				'expectedResult' 	=> $value['expectedResult']);
		}
		$passData['TCHeader'] = $allTCHeader;
		
		//5. All Test Case Detail data
		$tcDetailList = $this->mTestCase->searchExistTestCaseDetail($param->projectId);
		foreach ($tcDetailList as $value) {
			$allTCDetail[$value['testCaseNo']][$value['refInputName']] = $value['testData'];
		}
		$passData['TCDetail'] = $allTCDetail;

		//6. All RTM data
		$rtmList = $this->mRTM->searchRTMInfoByCriteria($param->projectId);
		foreach($rtmList as $value){
			$allRTM = array('functionNo' => $value['functionNo'], 'testCaseNo' => $value['testCaseNo']);
		}
		$passData['RTM'] = $allRTM;

		//7. Change Request Information

		$detailInfo = $this->mChange->getChangeRequestInputList($param->changeRequestNo);
		
		$changeList = array(
			'functionNo' => $param->functionNo, 
			'functionVersion' => $param->functionVersion);
		
		foreach($detailInfo as $value){
			$modifyFlag = EDIT_FLAG_ENABLE;

			$changeType = $this->mMisc->searchMiscellaneous('convertChangeType', $value['changeType']);

			$detail = array();
			if(CHANGE_TYPE_EDIT == $changeType[0]['miscValue2']){
				$detail = $this->mCancellation->getCancelChangeRequestInputDetail($value['sequenceNo']);
			}else{
				$detail = $value;

				if(CHANGE_TYPE_DELETE == $changeType[0]['miscValue2']){
					$param = (object) array(
						'changeRequestNo' => $param->changeRequestNo, 
						'tableName' 	  => $value['refTableName'], 
						'columnName' 	  => $value['refColumnName']);
					$result = $this->mCancellation->searchChangeHistoryDatabaseSchemaByCriteria($param);
					if(empty($result->changeType)){
						$modifyFlag = EDIT_FLAG_DISABLE;
					}
				}
			}

			$changeList['inputs'][] = array(
				'changeType' 	=> $changeType[0]['miscValue2'],
				'inputName' 	=> $detail['inputName'],
				'dataType' 		=> $detail['dataType'],
				'dataLength' 	=> $detail['dataLength'],
				'scale' 		=> $detail['scale'],
				'unique'	 	=> $detail['constraintUnique'],
				'notNull' 		=> $detail['constraintNotNull'],
				'default' 		=> $detail['constraintDefault'],
				'min' 			=> $detail['constraintMin'],
				'max' 			=> $detail['constraintMax'],
				'tableName' 	=> $detail['refTableName'],
				'columnName' 	=> $detail['refColumnName'],
				'modifyFlag' 	=> $modifyFlag
			);
		}
		$passData['changeRequestInfo'] = $changeList;

		$url = 'http://localhost/StubService/ChangeAPI.php';

		$json = json_decode($this->common->postCURL($url, $passData));
	
		return $json;

		//echo '<br><hr><h2>'.$this->postCURL($url, $passData).'</h2><br><hr><br>';
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